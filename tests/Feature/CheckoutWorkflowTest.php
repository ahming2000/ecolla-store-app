<?php

namespace Tests\Feature;

use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\Image;
use App\Models\Item;
use App\Models\ItemVariation;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CheckoutWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Image $receiptImage;

    private Item $item;

    private ItemVariation $variation;

    private PaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = Item::query()->create([
            'name' => 'Current item name',
            'name_en' => 'Current item name',
            'is_listed' => true,
        ]);
        $this->variation = $this->item->variations()->create([
            'barcode' => 'CHECKOUT-001',
            'name' => 'Current variation name',
            'name_en' => 'Current variation name',
            'price' => 12.50,
            'sale_price' => 10,
            'weight' => 0.5,
            'stock' => 5,
        ]);
        $this->receiptImage = Image::query()->create([
            'name' => 'receipt.png',
            'mime_type' => 'image/png',
            'size' => 1,
            'data_uri' => 'data:image/png;base64,AA==',
        ]);
        $this->paymentMethod = PaymentMethod::query()->create([
            'name' => 'Test transfer',
            'icon_img_path' => '/images/payment-method.png',
            'qr_code_img_path' => '/images/payment-method-qr.png',
            'is_enabled' => true,
        ]);
    }

    public function test_checkout_uses_server_prices_creates_item_snapshots_and_reduces_stock(): void
    {
        $response = $this->postJson(
            route('shop.ajax.cart.checkout'),
            $this->checkoutPayload(quantity: 2),
        );

        $response
            ->assertOk()
            ->assertJsonPath('delivery_mode', DeliveryMode::SELF_PICKUP->value)
            ->assertJsonPath('status', Status::PENDING->value)
            ->assertJsonPath('shipping_fee', 0)
            ->assertJsonPath('cus_phone', '0123456789')
            ->assertSessionHas(
                'checkout_order_ids',
                fn (array $orderIds): bool => count($orderIds) === 1,
            );

        $order = Order::query()->with('items')->sole();

        $this->assertSame($order->getKey(), $response->json('id'));
        $this->assertCount(1, $order->items);
        $this->assertSame('Current variation name', $order->items->first()->name);
        $this->assertSame('CHECKOUT-001', $order->items->first()->barcode);
        $this->assertSame(12.5, $order->items->first()->price);
        $this->assertSame(10.0, $order->items->first()->sale_price);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(3, $this->variation->refresh()->stock);

        $this->get(route('shop.cart.successful-page', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/checkout/Successful')
                ->where('order.id', $order->getKey())
                ->where('order.reference_num', $order->reference_num));
    }

    public function test_checkout_rejects_invalid_quantities_and_references_without_writes(): void
    {
        $disabledPaymentMethod = PaymentMethod::query()->create([
            'name' => 'Disabled transfer',
            'icon_img_path' => '/images/disabled.png',
            'qr_code_img_path' => '/images/disabled-qr.png',
            'is_enabled' => false,
        ]);
        $payload = $this->checkoutPayload(quantity: 0);
        $payload['checkoutForm']['receipt_image']['id'] = 999_999;
        $payload['checkoutForm']['payment_method']['id'] = $disabledPaymentMethod->getKey();

        $this->postJson(route('shop.ajax.cart.checkout'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'cart.items.0.quantity',
                'checkoutForm.receipt_image.id',
                'checkoutForm.payment_method.id',
            ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $this->variation->refresh()->stock);
    }

    public function test_checkout_rejects_a_variation_that_does_not_belong_to_the_cart_item(): void
    {
        $otherItem = Item::query()->create([
            'name' => 'Other item',
            'is_listed' => true,
        ]);
        $payload = $this->checkoutPayload(quantity: 1);
        $payload['cart']['items'][0]['item']['id'] = $otherItem->getKey();

        $this->postJson(route('shop.ajax.cart.checkout'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart.items.0.variation.id');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $this->variation->refresh()->stock);
    }

    public function test_checkout_rejects_quantities_above_current_stock(): void
    {
        $this->postJson(
            route('shop.ajax.cart.checkout'),
            $this->checkoutPayload(quantity: 6),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart.items.0.quantity');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $this->variation->refresh()->stock);
    }

    public function test_order_confirmation_is_not_exposed_to_an_unrelated_session(): void
    {
        $order = Order::query()->create([
            'reference_num' => 'PRIVATE-ORDER',
            'delivery_mode' => DeliveryMode::DELIVERY,
            'payment_method_id' => $this->paymentMethod->getKey(),
            'receipt_image_id' => $this->receiptImage->getKey(),
            'cus_name' => 'Private Customer',
            'cus_phone' => '0123456789',
            'cus_address' => 'Private Address',
        ]);

        $this->get(route('shop.cart.successful-page', $order))
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error/Shop')
                ->where('status', 404));
    }

    /**
     * @return array<string, mixed>
     */
    private function checkoutPayload(int $quantity): array
    {
        return [
            'cart' => [
                'deliveryMode' => DeliveryMode::SELF_PICKUP->value,
                'items' => [
                    [
                        'item' => [
                            'id' => $this->item->getKey(),
                            'name' => 'Tampered item name',
                        ],
                        'variation' => [
                            'id' => $this->variation->getKey(),
                            'barcode' => $this->variation->barcode,
                            'name' => 'Tampered variation name',
                            'price' => 999,
                            'sale_price' => null,
                        ],
                        'quantity' => $quantity,
                    ],
                ],
            ],
            'checkoutForm' => [
                'cus_name' => null,
                'cus_phone' => '0123456789',
                'cus_address' => null,
                'receipt_image' => [
                    'id' => $this->receiptImage->getKey(),
                ],
                'payment_method' => [
                    'id' => $this->paymentMethod->getKey(),
                ],
            ],
        ];
    }
}
