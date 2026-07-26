<?php

namespace App\Services\Common\Cart;

use App\Models\Item;
use App\Models\ItemVariation;

final class CartItem
{
    public function __construct(
        public Item $item,
        public ItemVariation $variation,
        public int $quantity,
    ) {}

    /**
     * @param  array{id: int, ...}  $item
     * @param  array{id: int, ...}  $variation
     */
    public static function from(
        array $item,
        array $variation,
        int $quantity,
    ): self {
        $itemModel = new Item($item);
        $itemModel->id = $item['id'];

        $variationModel = new ItemVariation($variation);
        $variationModel->id = $variation['id'];

        return new self(
            item: $itemModel,
            variation: $variationModel,
            quantity: $quantity,
        );
    }

    /**
     * @return array{
     *     item: array<string, mixed>,
     *     variation: array<string, mixed>,
     *     quantity: int
     * }
     */
    public function toArray(): array
    {
        return [
            'item' => $this->item->toArray(),
            'variation' => $this->variation->toArray(),
            'quantity' => $this->quantity,
        ];
    }
}
