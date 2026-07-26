<?php

namespace Tests\Feature;

use App\Enums\DeliveryMode;
use App\Models\Image;
use App\Models\Item;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CheckoutShippingFeeTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setting(SettingService::SHIPPING_FEE, '7.25');
        $this->setting(SettingService::FREE_SHIPPING_IS_ACTIVATED, '1');
        $this->setting(SettingService::FREE_SHIPPING_THRESHOLD, '50');
        $this->setting(
            SettingService::FREE_SHIPPING_DESCRIPTION,
            'Orders over RM50 ship free',
        );
    }

    public function test_checkout_uses_the_configured_shipping_fee_instead_of_client_input(): void
    {
        $response = $this->postJson(
            route('shop.ajax.cart.checkout'),
            $this->checkoutPayload(
                DeliveryMode::DELIVERY,
                price: 10,
                quantity: 2,
                clientShippingFee: 999,
            ),
        );

        $response->assertOk();

        $this->assertSame(
            7.25,
            (float) Order::query()->latest('id')->firstOrFail()->shipping_fee,
        );
    }

    public function test_checkout_applies_free_shipping_at_the_configured_threshold(): void
    {
        $response = $this->postJson(
            route('shop.ajax.cart.checkout'),
            $this->checkoutPayload(
                DeliveryMode::DELIVERY,
                price: 25,
                quantity: 2,
            ),
        );

        $response->assertOk();

        $this->assertSame(
            0.0,
            (float) Order::query()->latest('id')->firstOrFail()->shipping_fee,
        );
    }

    public function test_checkout_never_charges_shipping_for_self_pickup(): void
    {
        $response = $this->postJson(
            route('shop.ajax.cart.checkout'),
            $this->checkoutPayload(
                DeliveryMode::SELF_PICKUP,
                price: 10,
                quantity: 1,
            ),
        );

        $response->assertOk();

        $this->assertSame(
            0.0,
            (float) Order::query()->latest('id')->firstOrFail()->shipping_fee,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkoutPayload(
        DeliveryMode $deliveryMode,
        float $price,
        int $quantity,
        ?float $clientShippingFee = null,
    ): array {
        $item = Item::query()->create([
            'name' => 'Shipping test item',
            'name_en' => 'Shipping test item',
        ]);
        $variation = $item->variations()->create([
            'barcode' => fake()->unique()->numerify('SHIPPING-####'),
            'name' => 'Standard',
            'name_en' => 'Standard',
            'price' => $price,
            'sale_price' => null,
            'weight' => 1,
            'stock' => 10,
        ]);
        $receiptImage = Image::query()->create([
            'name' => 'receipt.png',
            'mime_type' => 'image/png',
            'size' => 1,
            'data_uri' => 'data:image/png;base64,AA==',
        ]);
        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Test transfer',
            'icon_img_path' => '/images/ecolla.png',
            'qr_code_img_path' => '/images/ecolla.png',
        ]);

        $cart = [
            'deliveryMode' => $deliveryMode->value,
            'items' => [
                [
                    'item' => $item->toArray(),
                    'variation' => $variation->toArray(),
                    'quantity' => $quantity,
                ],
            ],
        ];

        if ($clientShippingFee !== null) {
            $cart['shippingFee'] = $clientShippingFee;
        }

        return [
            'cart' => $cart,
            'checkoutForm' => [
                'cus_name' => 'Test Customer',
                'cus_phone' => '0123456789',
                'cus_address' => 'Test Address',
                'receipt_image' => ['id' => $receiptImage->getKey()],
                'payment_method' => ['id' => $paymentMethod->getKey()],
            ],
        ];
    }

    private function setting(string $name, string $value): Setting
    {
        return Setting::query()->updateOrCreate(
            ['name' => $name],
            [
                'value' => $value,
                'desc' => $name,
            ],
        );
    }
}
