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

    public function subPrice(): float|int
    {
        return $this->variation->price * $this->quantity * $this->rate();
    }

    public function rate(): float
    {
        return $this->variation->discount ? $this->variation->getRate() : 1.0;
    }
}
