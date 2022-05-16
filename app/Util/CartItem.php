<?php

namespace App\Util;

use App\Models\Variation;

class CartItem
{
    public Variation $variation;
    public int $quantity;

    public function __construct($variation, int $quantity)
    {
        $this->variation = $variation;
        $this->quantity = $quantity;
    }

    public function originalSubPrice(): float|int
    {
        return $this->variation->price * $this->quantity;
    }

    public function originalSubPriceLabel(): string
    {
        return 'RM' . number_format($this->originalSubPrice(), 2, '.', '');
    }

    public function subPrice(): float|int
    {
        return $this->variation->price * $this->quantity * $this->variation->getDiscountRate();
    }

    public function subPriceLabel(): string
    {
        return 'RM' . number_format($this->subPrice(), 2, '.', '');
    }

    public function weight(): float
    {
        return $this->variation->weight * $this->quantity;
    }

    public function weightLabel(): string
    {
        return number_format($this->weight(), 3, '.', '') . 'kg';
    }
}
