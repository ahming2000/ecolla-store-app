<?php

namespace App\Util;

use App\Enum\OrderMode;
use App\Models\SystemConfig;
use App\Models\Variation;

class Cart
{
    public int $orderMode;
    public array $cartItems;

    public function __construct()
    {
        $this->orderMode = OrderMode::$SELF_PICKUP;
        $this->cartItems = [];
    }

    public static function useSession(): Cart
    {
        $cart = new self();
        $cart->orderMode = session('cart')->orderMode;
        $cart->cartItems = session('cart')->cartItems;

        self::safetyCheck($cart);

        return $cart;
    }

    private static function safetyCheck(Cart $cart): void
    {
        for ($i = 0; $i < sizeof($cart->cartItems); $i++) {
            // Fetch latest data
            $variation = Variation::query()
                ->find($cart->cartItems[$i]->variation->barcode);

            // #1: Check if the variation is still existed
            if ($variation == null) {
                $cart->remove($cart->cartItems[$i]->variation->barcode);
                continue;
            }

            // Replace latest data
            $cart->cartItems[$i]->variation = $variation;

            // #2: Check if the quantity is exceeded the stock
            if ($variation->stock < $cart->cartItems[$i]->quantity) {
                $cart->adjust($cart->cartItems[$i]->variation->barcode, $variation->stock);
            }
        }
    }

    public function saveSession(): void
    {
        session()->put('cart', $this);
    }

    public function indexOf(string $barcode): int
    {
        for ($i = 0; $i < sizeof($this->cartItems); $i++) {
            if ($this->cartItems[$i]->variation->barcode == $barcode) {
                return $i;
            }
        }

        return -1;
    }

    public function add(CartItem $cartItem): void
    {
        $cartItemIndex = $this->indexOf($cartItem->variation->barcode);

        if ($cartItemIndex == -1) {
            $this->cartItems[] = $cartItem;
        } else {
            $stock = $this->cartItems[$cartItemIndex]->variation->stock;
            $finalQuantity = $this->cartItems[$cartItemIndex]->quantity + $cartItem->quantity;

            // If quantity to set exceed the stock, set quantity as the stock count
            $this->cartItems[$cartItemIndex]->quantity = min($finalQuantity, $stock);
        }
    }

    public function remove(string $barcode): void
    {
        $this->cartItems = array_filter($this->cartItems, function ($cartItem) use ($barcode) {
            return $cartItem->variation->barcode != $barcode;
        });
    }

    public function adjust(string $barcode, int $quantity): bool
    {
        $cartItemIndex = $this->indexOf($barcode);
        if ($cartItemIndex == -1) {
            return false;
        } else {
            $this->cartItems[$cartItemIndex]->quantity = $quantity;
            return true;
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

    public function shippingFee(): float
    {
        if ($this->orderMode == OrderMode::$SELF_PICKUP) return 0.0;

        if (SystemConfig::shippingDiscountIsActivated()) {
            if ($this->subtotal() >= SystemConfig::getShippingDiscountThreshold()) {
                return 0.0;
            } else {
                return SystemConfig::getShippingFee();
            }
        }

        return SystemConfig::getShippingFee();
    }
}
