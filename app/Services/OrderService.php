<?php

namespace App\Services;

use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\ItemVariation;
use App\Models\Order;
use App\Models\OrderedItem;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        return 'ECOLLA'.now()->format('YmdHis').Str::upper(Str::random(6));
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

    /**
     * @param  array{
     *     delivery_mode: string,
     *     shipping_fee: float,
     *     note: string|null,
     *     cus_name: string|null,
     *     cus_phone: string,
     *     cus_address: string|null,
     *     items: list<array{
     *         id: int,
     *         quantity: int,
     *         effective_price: float
     *     }>,
     *     cancel_when_empty: bool
     * }  $data
     */
    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data): Order {
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $existingItems = $lockedOrder->items()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $updatedItems = collect($data['items'])->keyBy('id');

            foreach ($updatedItems as $itemData) {
                if (! $existingItems->has($itemData['id'])) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more order items are no longer available.',
                    ]);
                }
            }

            $this->synchronizeVariationStock($existingItems, $updatedItems);

            foreach ($updatedItems as $itemData) {
                $orderedItem = $existingItems->get($itemData['id']);

                if (! $orderedItem instanceof OrderedItem) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more order items are no longer available.',
                    ]);
                }

                $effectivePrice = round(
                    (float) $itemData['effective_price'],
                    2,
                );
                $priceAttributes = $effectivePrice < $orderedItem->price
                    ? [
                        'sale_price' => $effectivePrice,
                    ]
                    : [
                        'price' => $effectivePrice,
                        'sale_price' => null,
                    ];

                $orderedItem->update([
                    ...$priceAttributes,
                    'quantity' => $itemData['quantity'],
                ]);
            }

            $removedItemIds = $existingItems->keys()->diff(
                $updatedItems->keys(),
            );

            if ($removedItemIds->isNotEmpty()) {
                $lockedOrder->items()
                    ->whereKey($removedItemIds->all())
                    ->delete();
            }

            $isDelivery = $data['delivery_mode']
                === DeliveryMode::DELIVERY->value;
            $isEmpty = $updatedItems->isEmpty();

            $lockedOrder->update([
                'delivery_mode' => $data['delivery_mode'],
                'shipping_fee' => $isDelivery
                    ? round((float) $data['shipping_fee'], 2)
                    : 0,
                'tracking_no' => $isDelivery
                    ? $lockedOrder->tracking_no
                    : null,
                'note' => $data['note'],
                'cus_name' => $data['cus_name'],
                'cus_phone' => $data['cus_phone'],
                'cus_address' => $data['cus_address'],
                'status' => $isEmpty && $data['cancel_when_empty']
                    ? Status::CANCELED
                    : $lockedOrder->status,
            ]);

            return $lockedOrder->refresh()->load([
                'items',
                'paymentMethod',
                'receiptImage',
            ]);
        }, attempts: 3);
    }

    /**
     * @param  Collection<int, OrderedItem>  $existingItems
     * @param  Collection<int, array{
     *     id: int,
     *     quantity: int,
     *     effective_price: float
     * }>  $updatedItems
     */
    private function synchronizeVariationStock(
        Collection $existingItems,
        Collection $updatedItems,
    ): void {
        $existingQuantityByBarcode = $existingItems
            ->groupBy('barcode')
            ->map(
                fn (Collection $items): int => $items->sum('quantity'),
            );
        $updatedQuantityByBarcode = $updatedItems
            ->groupBy(
                fn (array $itemData): string => $existingItems
                    ->get($itemData['id'])
                    ->barcode,
            )
            ->map(
                fn (Collection $items): int => $items->sum('quantity'),
            );
        $barcodes = $existingQuantityByBarcode->keys()
            ->filter()
            ->values();
        $variations = ItemVariation::query()
            ->whereIn('barcode', $barcodes)
            ->lockForUpdate()
            ->get()
            ->keyBy('barcode');

        foreach ($barcodes as $barcode) {
            $variation = $variations->get($barcode);

            if (! $variation instanceof ItemVariation) {
                continue;
            }

            $newStock = $variation->stock
                + $existingQuantityByBarcode->get($barcode, 0)
                - $updatedQuantityByBarcode->get($barcode, 0);

            if ($newStock < 0) {
                $itemIndex = $updatedItems
                    ->values()
                    ->search(
                        fn (array $itemData): bool => $existingItems
                            ->get($itemData['id'])
                            ?->barcode === $barcode,
                    );
                $errorKey = $itemIndex === false
                    ? 'items'
                    : "items.{$itemIndex}.quantity";

                throw ValidationException::withMessages([
                    $errorKey => 'The requested quantity exceeds the available stock.',
                ]);
            }

            $variation->update(['stock' => $newStock]);
        }
    }
}
