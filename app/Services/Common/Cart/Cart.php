<?php

namespace App\Services\Common\Cart;

use App\Enums\DeliveryMode;
use Illuminate\Support\Collection;

final class Cart
{
    /**
     * @param  Collection<int, CartItem>  $cartItems
     */
    public function __construct(
        public DeliveryMode $deliveryMode,
        public Collection $cartItems,
        public float $shippingFee = 0,
    ) {}

    /**
     * @param  list<array{
     *     item: array{id: int, ...},
     *     variation: array{id: int, ...},
     *     quantity: int
     * }>  $cartItems
     */
    public static function from(
        ?string $deliveryMode = null,
        array $cartItems = [],
        float $shippingFee = 0,
    ): self {
        return new self(
            deliveryMode: DeliveryMode::tryFrom($deliveryMode) ?? DeliveryMode::SELF_PICKUP,
            cartItems: collect($cartItems)->map(function (array $cartItem): CartItem {
                return CartItem::from(
                    item: $cartItem['item'],
                    variation: $cartItem['variation'],
                    quantity: $cartItem['quantity'],
                );
            }),
            shippingFee: $shippingFee,
        );
    }

    /**
     * @return array{
     *     deliveryMode: DeliveryMode,
     *     items: list<array{
     *         item: array<string, mixed>,
     *         variation: array<string, mixed>,
     *         quantity: int
     *     }>
     * }
     */
    public function toArray(): array
    {
        return [
            'deliveryMode' => $this->deliveryMode,
            'items' => array_values(
                $this->cartItems
                    ->map(fn (CartItem $cartItem): array => $cartItem->toArray())
                    ->all(),
            ),
        ];
    }
}
