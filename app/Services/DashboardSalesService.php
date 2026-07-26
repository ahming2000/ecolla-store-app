<?php

namespace App\Services;

use App\Enums\DashboardPeriod;
use App\Enums\Status;
use App\Models\Order;
use App\Models\OrderedItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\JoinClause;

class DashboardSalesService
{
    /**
     * @return array{
     *     filter: array{
     *         period: string,
     *         selected_date: string,
     *         starts_at: string,
     *         ends_at: string
     *     },
     *     summary: array{
     *         completed_order_count: int,
     *         items_sold: int,
     *         sales_revenue: string,
     *         canceled_order_value: string
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

        /** @var object{
         *     completed_order_count: int|string,
         *     items_sold: int|string,
         *     sales_revenue: float|int|string,
         *     canceled_order_value: float|int|string
         * } $summary
         */
        $summary = Order::query()
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
            ->whereIn("{$ordersTable}.status", [
                $completedStatus,
                $canceledStatus,
                $refundedStatus,
            ])
            ->where(
                "{$ordersTable}.created_at",
                '>=',
                $startsAt->setTimezone((string) config('app.timezone')),
            )
            ->where(
                "{$ordersTable}.created_at",
                '<',
                $endsAt->setTimezone((string) config('app.timezone')),
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
                            COALESCE(ordered_items.sale_price, ordered_items.price)
                            * ordered_items.quantity
                        ELSE 0
                    END), 0) AS sales_revenue,
                    COALESCE(SUM(CASE
                        WHEN orders.status IN (?, ?) THEN
                            COALESCE(ordered_items.sale_price, ordered_items.price)
                            * ordered_items.quantity
                        ELSE 0
                    END), 0) AS canceled_order_value
                ',
                [
                    $completedStatus,
                    $completedStatus,
                    $completedStatus,
                    $canceledStatus,
                    $refundedStatus,
                ],
            )
            ->toBase()
            ->firstOrFail();

        return [
            'filter' => [
                'period' => $period->value,
                'selected_date' => $selectedDate->format('Y-m-d'),
                'starts_at' => $startsAt->toIso8601String(),
                'ends_at' => $endsAt->toIso8601String(),
            ],
            'summary' => [
                'completed_order_count' => (int) $summary->completed_order_count,
                'items_sold' => (int) $summary->items_sold,
                'sales_revenue' => number_format(
                    (float) $summary->sales_revenue,
                    2,
                    '.',
                    '',
                ),
                'canceled_order_value' => number_format(
                    (float) $summary->canceled_order_value,
                    2,
                    '.',
                    '',
                ),
            ],
        ];
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
