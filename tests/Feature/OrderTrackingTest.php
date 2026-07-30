<?php

namespace Tests\Feature;

use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\Image;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
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

    public function test_tracking_page_accepts_an_initial_reference_number(): void
    {
        $this->withoutVite();

        $this->get(route('shop.order-tracking.page', [
            'reference' => ' ECOLLA-1001 ',
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('shop/order-tracking/OrderTrackingPage')
                ->where('initialReferenceNumber', 'ECOLLA-1001'));
    }

    public function test_tracking_page_ignores_a_non_string_initial_reference_number(): void
    {
        $this->withoutVite();

        $this->get(route('shop.order-tracking.page', [
            'reference' => ['ECOLLA-1001'],
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('shop/order-tracking/OrderTrackingPage')
                ->where('initialReferenceNumber', ''));
    }

    public function test_customer_can_track_an_order_with_matching_reference_and_phone(): void
    {
        $order = $this->order([
            'reference_num' => 'ECOLLA-TRACK-1001',
            'tracking_no' => null,
            'shipping_fee' => 4.5,
            'cus_phone' => '+60 12-345 6789',
        ]);
        $order->items()->createMany([
            [
                'name' => '促销商品',
                'name_en' => 'Sale item',
                'barcode' => 'SALE-001',
                'price' => 12,
                'sale_price' => 10,
                'quantity' => 2,
            ],
            [
                'name' => '原价商品',
                'name_en' => 'Regular item',
                'barcode' => 'REGULAR-001',
                'price' => 5,
                'sale_price' => null,
                'quantity' => 1,
            ],
        ]);

        $this->postJson(route('shop.ajax.order-tracking.lookup'), [
            'reference_num' => 'ecolla-track-1001',
            'phone' => '012 345-6789',
        ])
            ->assertOk()
            ->assertJsonPath('reference_num', 'ECOLLA-TRACK-1001')
            ->assertJsonPath('status', Status::PENDING->value)
            ->assertJsonPath('tracking_no', null)
            ->assertJsonPath('subtotal', 25)
            ->assertJsonPath('shipping_fee', 4.5)
            ->assertJsonPath('total', 29.5)
            ->assertJsonPath('items.0.unit_price', 10)
            ->assertJsonPath('items.0.line_total', 20)
            ->assertJsonMissingPath('cus_phone')
            ->assertJsonMissingPath('cus_address')
            ->assertJsonMissingPath('receipt_image');
    }

    public function test_tracking_lookup_does_not_reveal_an_order_for_wrong_details(): void
    {
        $this->order([
            'reference_num' => 'ECOLLA-PRIVATE-1001',
            'cus_phone' => '0123456789',
        ]);

        $this->postJson(route('shop.ajax.order-tracking.lookup'), [
            'reference_num' => 'ECOLLA-PRIVATE-1001',
            'phone' => '0199999999',
        ])
            ->assertNotFound()
            ->assertJsonMissingPath('status')
            ->assertJsonMissingPath('items');

        $this->postJson(route('shop.ajax.order-tracking.lookup'), [
            'reference_num' => 'ECOLLA-UNKNOWN',
            'phone' => '0123456789',
        ])->assertNotFound();
    }

    public function test_tracking_lookup_can_match_an_older_duplicate_legacy_reference(): void
    {
        $referenceNumber = 'ECOLLA-LEGACY-DUPLICATE';
        $this->order([
            'reference_num' => $referenceNumber,
            'cus_phone' => '0123456789',
        ]);

        foreach (range(1, 6) as $suffix) {
            $this->order([
                'reference_num' => $referenceNumber,
                'cus_phone' => "01999999{$suffix}",
            ]);
        }

        $this->postJson(route('shop.ajax.order-tracking.lookup'), [
            'reference_num' => $referenceNumber,
            'phone' => '0123456789',
        ])
            ->assertOk()
            ->assertJsonPath('reference_num', $referenceNumber);
    }

    public function test_tracking_lookup_validates_required_customer_details(): void
    {
        $this->postJson(route('shop.ajax.order-tracking.lookup'), [
            'reference_num' => '',
            'phone' => 'not-a-phone',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reference_num', 'phone']);
    }

    public function test_tracking_lookup_rejects_non_string_customer_details(): void
    {
        $this->postJson(route('shop.ajax.order-tracking.lookup'), [
            'reference_num' => ['ECOLLA-1001'],
            'phone' => ['0123456789'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reference_num', 'phone']);
    }

    public function test_soft_deleted_orders_cannot_be_tracked(): void
    {
        $order = $this->order([
            'reference_num' => 'ECOLLA-DELETED-1001',
            'cus_phone' => '0123456789',
        ]);
        $order->delete();

        $this->postJson(route('shop.ajax.order-tracking.lookup'), [
            'reference_num' => 'ECOLLA-DELETED-1001',
            'phone' => '0123456789',
        ])->assertNotFound();
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
            'tracking_no' => null,
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
}
