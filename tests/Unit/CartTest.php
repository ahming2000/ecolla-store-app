<?php

namespace Tests\Unit;

use App\Enums\DeliveryMode;
use App\Services\Common\Cart\Cart;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function test_it_uses_safe_defaults_for_an_empty_cart(): void
    {
        $cart = Cart::from(deliveryMode: 'unsupported');

        $this->assertSame(DeliveryMode::SELF_PICKUP, $cart->deliveryMode);
        $this->assertTrue($cart->cartItems->isEmpty());
        $this->assertSame(0.0, $cart->shippingFee);
    }

    public function test_it_hydrates_cart_items_from_frontend_payloads(): void
    {
        $cart = Cart::from(
            deliveryMode: DeliveryMode::DELIVERY->value,
            cartItems: [
                [
                    'item' => [
                        'id' => 12,
                        'name' => 'Green Tea',
                    ],
                    'variation' => [
                        'id' => 34,
                        'barcode' => 'TEA-001',
                        'name' => 'Large',
                        'price' => 18.90,
                    ],
                    'quantity' => 3,
                ],
            ],
            shippingFee: 8.50,
        );

        $cartItem = $cart->cartItems->sole();

        $this->assertSame(DeliveryMode::DELIVERY, $cart->deliveryMode);
        $this->assertSame(8.50, $cart->shippingFee);
        $this->assertSame(12, $cartItem->item->id);
        $this->assertSame('Green Tea', $cartItem->item->name);
        $this->assertSame(34, $cartItem->variation->id);
        $this->assertSame('TEA-001', $cartItem->variation->barcode);
        $this->assertSame(3, $cartItem->quantity);
    }

    public function test_delivery_modes_expose_the_values_expected_by_the_frontend(): void
    {
        $this->assertSame([
            DeliveryMode::SELF_PICKUP->value,
            DeliveryMode::DELIVERY->value,
        ], DeliveryMode::caseValues());
    }
}
