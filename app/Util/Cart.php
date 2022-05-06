<?php

namespace App\Util;

use App\Enum\Language;
use App\Enum\OrderMode;
use App\Models\Customer;

class Cart
{
    public int $lang;
    public int $orderMode;
    public array $cartItems;
    public Customer $customer;

    public function __construct()
    {
        $this->lang = Language::$CH;
        $this->orderMode = OrderMode::$SELF_PICKUP;
        $this->cartItems = [];
        $this->customer = new Customer();
    }

    public function insert($cart): void
    {
        $this->lang = $cart->lang;
        $this->orderMode = $cart->orderMode;
        $this->cartItems = $cart->cartItems;
        $this->customer = $cart->customer;
    }

    public function add(CartItem $cartItem): void
    {
        $this->cartItems[] = $cartItem;
    }

    public function remove(string $barcode): void
    {
        $this->cartItems = array_filter($this->cartItems, function ($cartItem) use ($barcode) {
            return $cartItem->variation->barcode != $barcode;
        });
    }

    public function adjust(string $barcode, int $quantity): void
    {
        foreach ($this->cartItems as $cartItem){
            if($cartItem->variation->barcode === $barcode && $cartItem->quantity + $quantity >= 1){
                $cartItem->quantity += $quantity;
            }
        }
    }

    public function count(): int
    {
        return sizeof($this->cartItems) ?? 0;
    }

    public function subtotal(): float
    {
        $total = 0.0;
        foreach($this->cartItems as $cartItem){
            $total += $cartItem->subPrice();
        }
        return $total;
    }

    public function reset(): void
    {
        $this->cartItems = [];
    }
}
