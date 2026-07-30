<?php

namespace App\Services;

use App\Enums\DashboardPeriod;
use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\Order;
use App\Models\OrderedItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\JoinClause;
use LogicException;

class DashboardSalesService
{
    /**
     * @return array{
     *     filter: array{
     *         period: string,
     *         selected_date: string,
     *         starts_at: string,
     *         ends_at: string,
     *         timezone: string
     *     },
     *     summary: array{
     *         completed_order_count: int,
     *         items_sold: int,
     *         sales_revenue: string,
     *         canceled_order_value: string
     *     },
     *     trend: list<array{
     *         starts_at: string,
     *         ends_at: string,
     *         completed_order_count: int,
     *         sales_revenue: string
     *     }>,
     *     distributions: array{
     *         status: array{
     *             pending: int,
     *             ready: int,
     *             completed: int,
     *             refunded: int,
     *             canceled: int
     *         },
     *         delivery_mode: array{
     *             delivery: int,
     *             self_pickup: int
     *         }
     *     }
     * }
     */
    public function getOverview(
        DashboardPeriod $period,
        CarbonImmutable $selectedDate,
        string $timezone,
    ): array {
        [$startsAt, $endsAt] = $this->periodBoundaries(
            $period,
            $selectedDate->setTimezone($timezone),
        );

        $ordersTable = (new Order)->getTable();
        $orderedItemsTable = (new OrderedItem)->getTable();
        $completedStatus = Status::COMPLETED->value;
        $canceledStatus = Status::CANCELED->value;
        $refundedStatus = Status::REFUNDED->value;
        $trendBuckets = $this->trendBuckets($period, $startsAt, $endsAt);
        $databaseTimezone = (string) config('app.timezone');
        $databaseStartsAt = $startsAt->setTimezone($databaseTimezone);
        $databaseEndsAt = $endsAt->setTimezone($databaseTimezone);

        $overviewQuery = Order::query()
            ->leftJoin(
                $orderedItemsTable,
                function (JoinClause $join) use (
                    $ordersTable,
                    $orderedItemsTable,
                ): void {
                    $join
                        ->on(
                            "{$orderedItemsTable}.order_id",
                            '=',
                            "{$ordersTable}.id",
                        )
                        ->whereNull("{$orderedItemsTable}.deleted_at");
                },
            )
            ->where(
                "{$ordersTable}.created_at",
                '>=',
                $databaseStartsAt,
            )
            ->where(
                "{$ordersTable}.created_at",
                '<',
                $databaseEndsAt,
            )
            ->selectRaw(
                '
                    COUNT(DISTINCT CASE
                        WHEN orders.status = ? THEN orders.id
                    END) AS completed_order_count,
                    COALESCE(SUM(CASE
                        WHEN orders.status = ? THEN ordered_items.quantity
                        ELSE 0
                    END), 0) AS items_sold,
                    COALESCE(SUM(CASE
                        WHEN orders.status = ? THEN
                            COALESCE(
                                ordered_items.sale_price,
                                ordered_items.price
                            ) * ordered_items.quantity
                        ELSE 0
                    END), 0) AS sales_revenue,
                    COALESCE(SUM(CASE
                        WHEN orders.status IN (?, ?) THEN
                            COALESCE(
                                ordered_items.sale_price,
                                ordered_items.price
                            ) * ordered_items.quantity
                        ELSE 0
                    END), 0) AS canceled_order_value,
                    COUNT(DISTINCT CASE
                        WHEN orders.status = ? THEN orders.id
                    END) AS status_pending_order_count,
                    COUNT(DISTINCT CASE
                        WHEN orders.status = ? THEN orders.id
                    END) AS status_ready_order_count,
                    COUNT(DISTINCT CASE
                        WHEN orders.status = ? THEN orders.id
                    END) AS status_completed_order_count,
                    COUNT(DISTINCT CASE
                        WHEN orders.status = ? THEN orders.id
                    END) AS status_refunded_order_count,
                    COUNT(DISTINCT CASE
                        WHEN orders.status = ? THEN orders.id
                    END) AS status_canceled_order_count,
                    COUNT(DISTINCT CASE
                        WHEN orders.delivery_mode = ? THEN orders.id
                    END) AS delivery_order_count,
                    COUNT(DISTINCT CASE
                        WHEN orders.delivery_mode = ? THEN orders.id
                    END) AS self_pickup_order_count
                ',
                [
                    $completedStatus,
                    $completedStatus,
                    $completedStatus,
                    $canceledStatus,
                    $refundedStatus,
                    Status::PENDING->value,
                    Status::READY->value,
                    $completedStatus,
                    $refundedStatus,
                    $canceledStatus,
                    DeliveryMode::DELIVERY->value,
                    DeliveryMode::SELF_PICKUP->value,
                ],
            );

        foreach ($trendBuckets as $index => $trendBucket) {
            $databaseBucketStartsAt = $trendBucket['starts_at']->setTimezone(
                $databaseTimezone,
            );
            $databaseBucketEndsAt = $trendBucket['ends_at']->setTimezone(
                $databaseTimezone,
            );

            $completedOrderCountSelect = match ($index) {
                0 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_0',
                1 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_1',
                2 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_2',
                3 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_3',
                4 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_4',
                5 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_5',
                6 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_6',
                7 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_7',
                8 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_8',
                9 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_9',
                10 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_10',
                11 => 'COUNT(DISTINCT CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN orders.id END) AS trend_completed_order_count_11',
                default => throw new LogicException('Unsupported dashboard trend bucket.'),
            };
            $salesRevenueSelect = match ($index) {
                0 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_0',
                1 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_1',
                2 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_2',
                3 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_3',
                4 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_4',
                5 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_5',
                6 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_6',
                7 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_7',
                8 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_8',
                9 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_9',
                10 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_10',
                11 => 'COALESCE(SUM(CASE WHEN orders.status = ? AND orders.created_at >= ? AND orders.created_at < ? THEN COALESCE(ordered_items.sale_price, ordered_items.price) * ordered_items.quantity ELSE 0 END), 0) AS trend_sales_revenue_11',
            };
            $bindings = [
                $completedStatus,
                $databaseBucketStartsAt,
                $databaseBucketEndsAt,
            ];

            $overviewQuery
                ->selectRaw($completedOrderCountSelect, $bindings)
                ->selectRaw($salesRevenueSelect, $bindings);
        }

        /** @var object{
         *     completed_order_count: int|string,
         *     items_sold: int|string,
         *     sales_revenue: float|int|string,
         *     canceled_order_value: float|int|string,
         *     status_pending_order_count: int|string,
         *     status_ready_order_count: int|string,
         *     status_completed_order_count: int|string,
         *     status_refunded_order_count: int|string,
         *     status_canceled_order_count: int|string,
         *     delivery_order_count: int|string,
         *     self_pickup_order_count: int|string
         * } $overview
         */
        $overview = $overviewQuery->toBase()->firstOrFail();
        $overviewData = (array) $overview;

        $trend = [];

        foreach ($trendBuckets as $index => $trendBucket) {
            $completedOrderCountProperty =
                "trend_completed_order_count_{$index}";
            $salesRevenueProperty = "trend_sales_revenue_{$index}";

            $trend[] = [
                'starts_at' => $trendBucket['starts_at']->toIso8601String(),
                'ends_at' => $trendBucket['ends_at']->toIso8601String(),
                'completed_order_count' => (int) $overviewData[$completedOrderCountProperty],
                'sales_revenue' => number_format(
                    (float) $overviewData[$salesRevenueProperty],
                    2,
                    '.',
                    '',
                ),
            ];
        }

        return [
            'filter' => [
                'period' => $period->value,
                'selected_date' => $selectedDate->format('Y-m-d'),
                'starts_at' => $startsAt->toIso8601String(),
                'ends_at' => $endsAt->toIso8601String(),
                'timezone' => $timezone,
            ],
            'summary' => [
                'completed_order_count' => (int) $overview->completed_order_count,
                'items_sold' => (int) $overview->items_sold,
                'sales_revenue' => number_format(
                    (float) $overview->sales_revenue,
                    2,
                    '.',
                    '',
                ),
                'canceled_order_value' => number_format(
                    (float) $overview->canceled_order_value,
                    2,
                    '.',
                    '',
                ),
            ],
            'trend' => $trend,
            'distributions' => [
                'status' => [
                    'pending' => (int) $overview->status_pending_order_count,
                    'ready' => (int) $overview->status_ready_order_count,
                    'completed' => (int) $overview->status_completed_order_count,
                    'refunded' => (int) $overview->status_refunded_order_count,
                    'canceled' => (int) $overview->status_canceled_order_count,
                ],
                'delivery_mode' => [
                    'delivery' => (int) $overview->delivery_order_count,
                    'self_pickup' => (int) $overview->self_pickup_order_count,
                ],
            ],
        ];
    }

    /**
     * @return list<array{
     *     starts_at: CarbonImmutable,
     *     ends_at: CarbonImmutable
     * }>
     */
    private function trendBuckets(
        DashboardPeriod $period,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
    ): array {
        $buckets = [];
        $bucketStartsAt = $startsAt;

        while ($bucketStartsAt->lessThan($endsAt)) {
            $bucketEndsAt = match ($period) {
                DashboardPeriod::DAILY => $bucketStartsAt->addHours(4),
                DashboardPeriod::WEEKLY => $bucketStartsAt->addDay(),
                DashboardPeriod::MONTHLY => $bucketStartsAt->addWeek(),
                DashboardPeriod::YEARLY => $bucketStartsAt->addMonth(),
            };

            if ($bucketEndsAt->greaterThan($endsAt)) {
                $bucketEndsAt = $endsAt;
            }

            $buckets[] = [
                'starts_at' => $bucketStartsAt,
                'ends_at' => $bucketEndsAt,
            ];
            $bucketStartsAt = $bucketEndsAt;
        }

        return $buckets;
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    private function periodBoundaries(
        DashboardPeriod $period,
        CarbonImmutable $selectedDate,
    ): array {
        $startsAt = match ($period) {
            DashboardPeriod::DAILY => $selectedDate->startOfDay(),
            DashboardPeriod::WEEKLY => $selectedDate->startOfWeek(),
            DashboardPeriod::MONTHLY => $selectedDate->startOfMonth(),
            DashboardPeriod::YEARLY => $selectedDate->startOfYear(),
        };

        $endsAt = match ($period) {
            DashboardPeriod::DAILY => $startsAt->addDay(),
            DashboardPeriod::WEEKLY => $startsAt->addWeek(),
            DashboardPeriod::MONTHLY => $startsAt->addMonth(),
            DashboardPeriod::YEARLY => $startsAt->addYear(),
        };

        return [$startsAt, $endsAt];
    }
}
