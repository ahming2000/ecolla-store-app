<?php

namespace Tests\Feature;

use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\Image;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardSalesTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Image $receiptImage;

    private PaymentMethod $paymentMethod;

    private int $orderSequence = 0;

    private int $orderedItemSequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receiptImage = Image::query()->create([
            'name' => 'receipt.png',
            'mime_type' => 'image/png',
            'size' => 1,
            'url' => '/images/ecolla.png',
            'data_uri' => null,
        ]);

        $this->paymentMethod = new PaymentMethod;
        $this->paymentMethod->forceFill([
            'name' => 'Test payment',
            'icon_img_path' => '/images/ecolla.png',
            'qr_code_img_path' => '/images/ecolla.png',
            'is_enabled' => true,
        ])->save();
    }

    public function test_dashboard_uses_daily_sales_in_the_users_timezone(): void
    {
        $user = User::factory()->create([
            'timezone' => 'Asia/Kuala_Lumpur',
        ]);

        $this->createOrder(
            Status::COMPLETED,
            CarbonImmutable::parse('2026-07-26 01:00:00', 'UTC'),
            [
                ['price' => 10, 'sale_price' => 8, 'quantity' => 3],
                ['price' => 5, 'sale_price' => null, 'quantity' => 2],
            ],
            shippingFee: 99,
        );
        $this->createOrder(
            Status::COMPLETED,
            CarbonImmutable::parse('2026-07-26 15:59:59', 'UTC'),
            [
                ['price' => 2, 'sale_price' => null, 'quantity' => 1],
            ],
        );
        $this->createOrder(
            Status::CANCELED,
            CarbonImmutable::parse('2026-07-26 10:00:00', 'UTC'),
            [
                ['price' => 12, 'sale_price' => 10, 'quantity' => 4],
            ],
        );
        $this->createOrder(
            Status::REFUNDED,
            CarbonImmutable::parse('2026-07-26 11:00:00', 'UTC'),
            [
                ['price' => 20, 'sale_price' => 15, 'quantity' => 2],
            ],
        );
        $this->createOrder(
            Status::PENDING,
            CarbonImmutable::parse('2026-07-26 09:00:00', 'UTC'),
            [
                ['price' => 100, 'sale_price' => null, 'quantity' => 10],
            ],
        );
        $this->createOrder(
            Status::READY,
            CarbonImmutable::parse('2026-07-26 09:00:00', 'UTC'),
            [
                ['price' => 100, 'sale_price' => null, 'quantity' => 10],
            ],
        );
        $this->createOrder(
            Status::COMPLETED,
            CarbonImmutable::parse('2026-07-25 15:59:59', 'UTC'),
            [
                ['price' => 100, 'sale_price' => null, 'quantity' => 10],
            ],
        );
        $this->createOrder(
            Status::COMPLETED,
            CarbonImmutable::parse('2026-07-26 16:00:00', 'UTC'),
            [
                ['price' => 100, 'sale_price' => null, 'quantity' => 10],
            ],
        );

        $completedOrderWithDeletedItem = $this->createOrder(
            Status::COMPLETED,
            CarbonImmutable::parse('2026-07-26 08:00:00', 'UTC'),
            [
                ['price' => 50, 'sale_price' => null, 'quantity' => 2],
            ],
        );
        $completedOrderWithDeletedItem->items()->firstOrFail()->delete();

        $deletedOrder = $this->createOrder(
            Status::COMPLETED,
            CarbonImmutable::parse('2026-07-26 08:00:00', 'UTC'),
            [
                ['price' => 50, 'sale_price' => null, 'quantity' => 2],
            ],
        );
        $deletedOrder->delete();

        $this->actingAs($user)
            ->get(route('admin.dashboard.page', [
                'period' => 'daily',
                'date' => '2026-07-26',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/dashboard/DashboardPage')
                ->where('dashboard.filter.period', 'daily')
                ->where('dashboard.filter.selected_date', '2026-07-26')
                ->where(
                    'dashboard.filter.starts_at',
                    '2026-07-26T00:00:00+08:00',
                )
                ->where(
                    'dashboard.filter.ends_at',
                    '2026-07-27T00:00:00+08:00',
                )
                ->where(
                    'dashboard.filter.timezone',
                    'Asia/Kuala_Lumpur',
                )
                ->where('dashboard.summary.completed_order_count', 3)
                ->where('dashboard.summary.items_sold', 6)
                ->where('dashboard.summary.sales_revenue', '36.00')
                ->where('dashboard.summary.canceled_order_value', '70.00')
                ->has('dashboard.trend', 6)
                ->where('dashboard.trend.2', [
                    'starts_at' => '2026-07-26T08:00:00+08:00',
                    'ends_at' => '2026-07-26T12:00:00+08:00',
                    'completed_order_count' => 1,
                    'sales_revenue' => '34.00',
                ])
                ->where('dashboard.trend.4', [
                    'starts_at' => '2026-07-26T16:00:00+08:00',
                    'ends_at' => '2026-07-26T20:00:00+08:00',
                    'completed_order_count' => 1,
                    'sales_revenue' => '0.00',
                ])
                ->where('dashboard.trend.5', [
                    'starts_at' => '2026-07-26T20:00:00+08:00',
                    'ends_at' => '2026-07-27T00:00:00+08:00',
                    'completed_order_count' => 1,
                    'sales_revenue' => '2.00',
                ])
                ->where('dashboard.distributions.status', [
                    'pending' => 1,
                    'ready' => 1,
                    'completed' => 3,
                    'refunded' => 1,
                    'canceled' => 1,
                ])
                ->where('dashboard.distributions.delivery_mode', [
                    'delivery' => 0,
                    'self_pickup' => 7,
                ]));
    }

    public function test_dashboard_returns_expected_period_boundaries(): void
    {
        $user = User::factory()->create([
            'timezone' => 'Asia/Kuala_Lumpur',
        ]);

        $expectedBoundaries = [
            'daily' => [
                '2026-07-26T00:00:00+08:00',
                '2026-07-27T00:00:00+08:00',
                6,
            ],
            'weekly' => [
                '2026-07-20T00:00:00+08:00',
                '2026-07-27T00:00:00+08:00',
                7,
            ],
            'monthly' => [
                '2026-07-01T00:00:00+08:00',
                '2026-08-01T00:00:00+08:00',
                5,
            ],
            'yearly' => [
                '2026-01-01T00:00:00+08:00',
                '2027-01-01T00:00:00+08:00',
                12,
            ],
        ];

        foreach (
            $expectedBoundaries as $period => [$startsAt, $endsAt, $trendPointCount]
        ) {
            $this->actingAs($user)
                ->get(route('admin.dashboard.page', [
                    'period' => $period,
                    'date' => '2026-07-26',
                ]))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->where('dashboard.filter.period', $period)
                    ->where('dashboard.filter.starts_at', $startsAt)
                    ->where('dashboard.filter.ends_at', $endsAt)
                    ->where('dashboard.summary.completed_order_count', 0)
                    ->where('dashboard.summary.items_sold', 0)
                    ->where('dashboard.summary.sales_revenue', '0.00')
                    ->where('dashboard.summary.canceled_order_value', '0.00')
                    ->has('dashboard.trend', $trendPointCount)
                    ->where('dashboard.distributions.status', [
                        'pending' => 0,
                        'ready' => 0,
                        'completed' => 0,
                        'refunded' => 0,
                        'canceled' => 0,
                    ])
                    ->where('dashboard.distributions.delivery_mode', [
                        'delivery' => 0,
                        'self_pickup' => 0,
                    ]));
        }
    }

    public function test_dashboard_defaults_to_the_current_local_day(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2026-07-26 09:30:00', 'Asia/Kuala_Lumpur'),
        );

        try {
            $user = User::factory()->create([
                'timezone' => 'Asia/Kuala_Lumpur',
            ]);

            $this->actingAs($user)
                ->get(route('admin.dashboard.page'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->where('dashboard.filter.period', 'daily')
                    ->where('dashboard.filter.selected_date', '2026-07-26'));
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_dashboard_rejects_invalid_filters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.dashboard.page'))
            ->get(route('admin.dashboard.page', [
                'period' => 'quarterly',
                'date' => 'not-a-date',
            ]))
            ->assertRedirect(route('admin.dashboard.page'))
            ->assertSessionHasErrors(['period', 'date']);
    }

    /**
     * @param  list<array{price: int|float, sale_price: int|float|null, quantity: int}>  $items
     */
    private function createOrder(
        Status $status,
        CarbonImmutable $createdAt,
        array $items,
        int|float $shippingFee = 0,
    ): Order {
        $this->orderSequence++;

        $order = Order::query()->create([
            'reference_num' => "DASHBOARD-{$this->orderSequence}",
            'delivery_mode' => DeliveryMode::SELF_PICKUP,
            'status' => $status,
            'tracking_no' => null,
            'shipping_fee' => $shippingFee,
            'payment_method_id' => $this->paymentMethod->id,
            'receipt_image_id' => $this->receiptImage->id,
            'note' => null,
            'cus_name' => 'Dashboard customer',
            'cus_phone' => '0123456789',
            'cus_address' => null,
        ]);

        $order->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        foreach ($items as $item) {
            $this->orderedItemSequence++;

            $order->items()->create([
                'name' => "商品 {$this->orderedItemSequence}",
                'name_en' => "Item {$this->orderedItemSequence}",
                'barcode' => "DASH-{$this->orderedItemSequence}",
                ...$item,
            ]);
        }

        return $order;
    }
}
