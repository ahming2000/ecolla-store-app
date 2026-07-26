<?php

namespace App\Services;

use App\Enums\DeliveryMode;
use App\Models\Order;
use App\Models\OrderedItem;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function getAdminOrders(
        ?CarbonImmutable $orderDate,
        ?DeliveryMode $deliveryMode,
        int $page,
        int $perPage,
    ): LengthAwarePaginator {
        $query = Order::query()
            ->with(['items', 'paymentMethod', 'receiptImage']);

        if ($orderDate !== null) {
            $query
                ->where('created_at', '>=', $orderDate->utc())
                ->where('created_at', '<', $orderDate->addDay()->utc());
        }

        if ($deliveryMode !== null) {
            $query->where('delivery_mode', $deliveryMode);
        }

        return $query
            ->latest('created_at')
            ->latest('id')
            ->paginate(
                perPage: $perPage,
                page: $page,
            )
            ->withQueryString();
    }

    public function getOrderItemsSubtotal(Order $order): string
    {
        return number_format(
            $this->getOrderItemsSubtotalValue($order),
            2,
            '.',
            '',
        );
    }

    public function getOrderItemsSubtotalValue(Order $order): float
    {
        return $order->items->sum(
            fn (OrderedItem $item): float => ($item->sale_price ?? $item->price) * $item->quantity,
        );
    }

    public function generateReferenceNum(): string
    {
        return 'ECOLLA'.now()->format('YmdHis');
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  iterable<int, array{
     *     name: string,
     *     name_en: string|null,
     *     barcode: string,
     *     price: float,
     *     sale_price: float|null,
     *     quantity: int
     * }>  $cartItems
     */
    public function createOrder(array $data, iterable $cartItems): Order
    {
        $data['reference_num'] = $this->generateReferenceNum();

        $order = new Order($data);
        $order->save();

        $orderedItems = collect($cartItems)
            ->map(function (array $cartItem): OrderedItem {
                return new OrderedItem($cartItem);
            });

        $order->items()->saveMany($orderedItems);

        return $order->refresh()->load('items');
    }
}
