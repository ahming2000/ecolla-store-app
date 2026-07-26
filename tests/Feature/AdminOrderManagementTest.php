<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\Image;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Image $receiptImage;

    private PaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receiptImage = Image::query()->create([
            'name' => 'receipt.png',
            'mime_type' => 'image/png',
            'size' => 68,
            'url' => '/images/ecolla.png',
        ]);
        $this->paymentMethod = PaymentMethod::query()->forceCreate([
            'name' => 'Online Banking',
            'icon_img_path' => '/images/payment-methods/icons/online-banking.png',
            'qr_code_img_path' => '/images/payment-methods/qr-codes/online-banking-qr-code.jpeg',
            'is_enabled' => true,
        ]);
    }

    public function test_orders_are_filtered_by_staff_local_date_and_delivery_mode(): void
    {
        $viewer = $this->user(AccessLevel::VIEWER);
        $viewer->forceFill(['timezone' => 'Asia/Kuala_Lumpur'])->save();

        $matchingOrder = $this->order([
            'reference_num' => 'MATCHING-ORDER',
            'delivery_mode' => DeliveryMode::DELIVERY,
            'created_at' => '2026-07-25 16:30:00',
            'updated_at' => '2026-07-25 16:30:00',
        ]);
        $this->order([
            'reference_num' => 'WRONG-MODE',
            'delivery_mode' => DeliveryMode::SELF_PICKUP,
            'created_at' => '2026-07-25 17:00:00',
            'updated_at' => '2026-07-25 17:00:00',
        ]);
        $this->order([
            'reference_num' => 'PREVIOUS-LOCAL-DATE',
            'delivery_mode' => DeliveryMode::DELIVERY,
            'created_at' => '2026-07-25 15:59:59',
            'updated_at' => '2026-07-25 15:59:59',
        ]);

        $this->actingAs($viewer)
            ->getJson(route('admin.ajax.order.index', [
                'order_date' => '2026-07-26',
                'delivery_mode' => DeliveryMode::DELIVERY->value,
                'page' => 1,
                'per_page' => 50,
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $matchingOrder->id)
            ->assertJsonPath(
                'data.0.created_at',
                '2026-07-26T00:30:00+08:00',
            );
    }

    public function test_orders_are_paginated_in_stable_latest_first_order(): void
    {
        $viewer = $this->user(AccessLevel::VIEWER);
        $oldestOrder = null;

        foreach (range(1, 51) as $index) {
            $order = $this->order([
                'reference_num' => "PAGINATED-{$index}",
                'created_at' => '2026-07-26 04:00:00',
                'updated_at' => '2026-07-26 04:00:00',
            ]);
            $oldestOrder ??= $order;
        }

        $this->actingAs($viewer)
            ->getJson(route('admin.ajax.order.index', [
                'page' => 2,
                'per_page' => 50,
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 51)
            ->assertJsonPath('data.0.id', $oldestOrder?->id);
    }

    public function test_order_listing_rejects_invalid_filters_and_pagination(): void
    {
        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->getJson(route('admin.ajax.order.index', [
                'order_date' => '26-07-2026',
                'delivery_mode' => 'Courier',
                'page' => 0,
                'per_page' => 25,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'order_date',
                'delivery_mode',
                'page',
                'per_page',
            ]);
    }

    public function test_supervisor_can_update_delivery_tracking_and_status(): void
    {
        $order = $this->order();
        $supervisor = $this->user(AccessLevel::SUPERVISOR);

        $this->actingAs($supervisor)
            ->patchJson(
                route('admin.ajax.order.tracking-number.update', $order),
                ['tracking_no' => '  TRACK-002  '],
            )
            ->assertOk()
            ->assertJson([
                'id' => $order->id,
                'status' => Status::PENDING->value,
                'tracking_no' => 'TRACK-002',
            ]);

        $this->actingAs($supervisor)
            ->patchJson(
                route('admin.ajax.order.status.update', $order),
                [
                    'status' => Status::READY->value,
                    'tracking_no' => 'TRACK-002',
                ],
            )
            ->assertOk()
            ->assertJson([
                'id' => $order->id,
                'status' => Status::READY->value,
                'tracking_no' => 'TRACK-002',
            ]);

        $order->refresh();

        $this->assertSame(Status::READY, $order->status);
        $this->assertSame('TRACK-002', $order->tracking_no);
    }

    public function test_supervisor_can_mark_an_order_as_refunded(): void
    {
        $order = $this->order();

        $this->actingAs($this->user(AccessLevel::SUPERVISOR))
            ->patchJson(
                route('admin.ajax.order.status.update', $order),
                [
                    'status' => Status::REFUNDED->value,
                    'tracking_no' => 'TRACK-REFUNDED',
                ],
            )
            ->assertOk()
            ->assertJson([
                'id' => $order->id,
                'status' => Status::REFUNDED->value,
                'tracking_no' => 'TRACK-REFUNDED',
            ]);

        $this->assertSame(Status::REFUNDED, $order->refresh()->status);
    }

    public function test_delivery_status_requires_a_valid_status_and_tracking_number(): void
    {
        $order = $this->order();
        $supervisor = $this->user(AccessLevel::SUPERVISOR);

        $this->actingAs($supervisor)
            ->patchJson(
                route('admin.ajax.order.status.update', $order),
                ['status' => 'Not a status'],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->actingAs($supervisor)
            ->patchJson(
                route('admin.ajax.order.status.update', $order),
                [
                    'status' => Status::READY->value,
                    'tracking_no' => ' ',
                ],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tracking_no');

        $this->actingAs($supervisor)
            ->patchJson(
                route('admin.ajax.order.tracking-number.update', $order),
                ['tracking_no' => str_repeat('A', 256)],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tracking_no');

        $this->assertSame(Status::PENDING, $order->refresh()->status);
    }

    public function test_pickup_status_does_not_require_tracking_and_cannot_receive_tracking_updates(): void
    {
        $order = $this->order([
            'delivery_mode' => DeliveryMode::SELF_PICKUP,
            'tracking_no' => null,
        ]);
        $supervisor = $this->user(AccessLevel::SUPERVISOR);

        $this->actingAs($supervisor)
            ->patchJson(
                route('admin.ajax.order.status.update', $order),
                ['status' => Status::READY->value],
            )
            ->assertOk()
            ->assertJson([
                'status' => Status::READY->value,
                'tracking_no' => null,
            ]);

        $this->actingAs($supervisor)
            ->patchJson(
                route('admin.ajax.order.tracking-number.update', $order),
                ['tracking_no' => 'NOT-APPLICABLE'],
            )
            ->assertForbidden();
    }

    public function test_editor_can_only_update_pending_order_fulfilment(): void
    {
        $order = $this->order();
        $editor = $this->user(AccessLevel::EDITOR);

        $this->actingAs($editor)
            ->patchJson(
                route('admin.ajax.order.tracking-number.update', $order),
                ['tracking_no' => 'TRACK-EDITOR'],
            )
            ->assertOk();

        $this->actingAs($editor)
            ->patchJson(
                route('admin.ajax.order.status.update', $order),
                [
                    'status' => Status::COMPLETED->value,
                    'tracking_no' => 'TRACK-EDITOR',
                ],
            )
            ->assertOk();

        $this->actingAs($editor)
            ->patchJson(
                route('admin.ajax.order.tracking-number.update', $order),
                ['tracking_no' => 'TOO-LATE'],
            )
            ->assertForbidden();

        $this->assertSame('TRACK-EDITOR', $order->refresh()->tracking_no);
    }

    public function test_viewer_cannot_update_order_fulfilment(): void
    {
        $order = $this->order();
        $viewer = $this->user(AccessLevel::VIEWER);

        $this->actingAs($viewer)
            ->patchJson(
                route('admin.ajax.order.status.update', $order),
                [
                    'status' => Status::READY->value,
                    'tracking_no' => 'TRACK-VIEWER',
                ],
            )
            ->assertForbidden();

        $this->actingAs($viewer)
            ->patchJson(
                route('admin.ajax.order.tracking-number.update', $order),
                ['tracking_no' => 'TRACK-VIEWER'],
            )
            ->assertForbidden();
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function test_viewer_can_download_a_complete_printable_order_document(): void
    {
        $order = $this->order();
        $order->items()->createMany([
            [
                'name' => '测试商品',
                'name_en' => 'Test item',
                'barcode' => 'SKU-001',
                'price' => 10,
                'sale_price' => 8,
                'quantity' => 3,
            ],
            [
                'name' => '原价商品',
                'name_en' => 'Regular item',
                'barcode' => 'SKU-002',
                'price' => 5,
                'sale_price' => null,
                'quantity' => 1,
            ],
        ]);

        $response = $this->actingAs($this->user(AccessLevel::VIEWER))
            ->get(route('admin.order.download', $order));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload('ECOLLA-1001.pdf');

        $document = $response->getContent();

        $this->assertIsString($document);
        $this->assertStringStartsWith('%PDF-', $document);
        $this->assertStringContainsString('%%EOF', $document);
        $this->assertStringContainsString('NotoSansSC-Regular', $document);
        $this->assertStringContainsString('NotoSansSC-Bold', $document);
        $this->assertStringContainsString('/Subtype /Image', $document);
        $this->assertGreaterThan(1_000, strlen($document));
    }

    public function test_order_management_endpoints_require_authentication(): void
    {
        $order = $this->order();

        $this->getJson(route('admin.ajax.order.index'))
            ->assertUnauthorized();

        $this->patchJson(
            route('admin.ajax.order.status.update', $order),
            [
                'status' => Status::READY->value,
                'tracking_no' => 'TRACK-GUEST',
            ],
        )->assertUnauthorized();

        $this->get(route('admin.order.download', $order))
            ->assertRedirect(route('login.page'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function order(array $attributes = []): Order
    {
        return Order::query()->forceCreate([
            'reference_num' => 'ECOLLA-1001',
            'delivery_mode' => DeliveryMode::DELIVERY,
            'status' => Status::PENDING,
            'tracking_no' => 'TRACK-001',
            'shipping_fee' => 3,
            'payment_method_id' => $this->paymentMethod->id,
            'receipt_image_id' => $this->receiptImage->id,
            'note' => 'Leave at the door',
            'cus_name' => 'Test Customer',
            'cus_phone' => '0123456789',
            'cus_address' => '1 Test Street',
            ...$attributes,
        ]);
    }

    private function user(AccessLevel $accessLevel): User
    {
        return User::factory()->create([
            'access_level' => $accessLevel->value,
        ]);
    }
}
