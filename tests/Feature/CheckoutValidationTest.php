<?php

namespace Tests\Feature;

use App\Enums\DeliveryMode;
use App\Models\Image;
use App\Models\Item;
use App\Services\ItemVariationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckoutValidationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_checkout_rejects_an_empty_payload(): void
    {
        $this->postJson(route('shop.ajax.cart.checkout'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'cart.deliveryMode',
                'cart.items',
                'checkoutForm.cus_phone',
                'checkoutForm.receipt_image',
                'checkoutForm.payment_method',
            ]);
    }

    public function test_checkout_rejects_invalid_cart_values(): void
    {
        $this->postJson(route('shop.ajax.cart.checkout'), [
            'cart' => [
                'deliveryMode' => 'teleport',
                'items' => [],
                'shippingFee' => 'free',
            ],
            'checkoutForm' => [
                'cus_phone' => '0123456789',
                'receipt_image' => ['id' => 1],
                'payment_method' => ['id' => 1],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'cart.deliveryMode',
                'cart.items',
                'cart.shippingFee',
            ]);
    }

    public function test_cart_verification_accepts_an_empty_cart(): void
    {
        $this->mock(ItemVariationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getItemVariationsByBarcode')
                ->once()
                ->with([])
                ->andReturn(new Collection);
        });

        $this->postJson(route('shop.ajax.cart.verify'), [
            'deliveryMode' => DeliveryMode::DELIVERY->value,
            'items' => [],
        ])
            ->assertOk()
            ->assertExactJson([
                'deliveryMode' => DeliveryMode::DELIVERY->value,
                'items' => [],
            ]);
    }

    public function test_cart_verification_removes_items_that_no_longer_exist(): void
    {
        $this->mock(ItemVariationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getItemVariationsByBarcode')
                ->once()
                ->with(['REMOVED-001'])
                ->andReturn(new Collection);
        });

        $this->postJson(route('shop.ajax.cart.verify'), [
            'deliveryMode' => DeliveryMode::SELF_PICKUP->value,
            'items' => [
                [
                    'item' => [
                        'id' => 1,
                        'name' => 'Removed product',
                    ],
                    'variation' => [
                        'id' => 2,
                        'barcode' => 'REMOVED-001',
                        'name' => 'Default',
                        'price' => 12,
                    ],
                    'quantity' => 1,
                ],
            ],
        ])
            ->assertOk()
            ->assertExactJson([
                'deliveryMode' => DeliveryMode::SELF_PICKUP->value,
                'items' => [],
            ]);
    }

    public function test_cart_verification_caps_quantity_and_refreshes_catalog_data(): void
    {
        $item = Item::query()->create([
            'name' => 'Current product name',
        ]);
        $variation = $item->variations()->create([
            'barcode' => 'CURRENT-001',
            'name' => 'Current variation name',
            'price' => 10.50,
            'stock' => 2,
        ])->load('item');

        $this->mock(ItemVariationService::class, function (MockInterface $mock) use ($variation): void {
            $mock->shouldReceive('getItemVariationsByBarcode')
                ->once()
                ->with(['CURRENT-001'])
                ->andReturn(new Collection([$variation]));
        });

        $this->postJson(route('shop.ajax.cart.verify'), [
            'deliveryMode' => DeliveryMode::DELIVERY->value,
            'items' => [
                [
                    'item' => [
                        'id' => $item->getKey(),
                        'name' => 'Stale product name',
                    ],
                    'variation' => [
                        'id' => $variation->getKey(),
                        'barcode' => 'CURRENT-001',
                        'name' => 'Stale variation name',
                        'price' => 999,
                    ],
                    'quantity' => 5,
                ],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('items.0.item.name', 'Current product name')
            ->assertJsonPath('items.0.variation.name', 'Current variation name')
            ->assertJsonPath('items.0.variation.price', 10.5)
            ->assertJsonPath('items.0.quantity', 2);
    }

    public function test_cart_verification_refreshes_the_variation_image_and_item_cover_image(): void
    {
        $item = Item::query()->create([
            'name' => 'Current product name',
        ]);
        $itemCoverImage = Image::query()->create([
            'name' => 'item-cover.png',
            'mime_type' => 'image/png',
            'size' => 100,
            'url' => '/images/item-cover.png',
        ]);
        $variationImage = Image::query()->create([
            'name' => 'variation.png',
            'mime_type' => 'image/png',
            'size' => 100,
            'url' => '/images/variation.png',
        ]);
        $item->images()->attach($itemCoverImage);
        $variation = $item->variations()->create([
            'barcode' => 'CURRENT-IMAGE-001',
            'name' => 'Current variation name',
            'price' => 10.50,
            'stock' => 2,
        ]);
        $variation->image()->associate($variationImage)->save();

        $this->postJson(route('shop.ajax.cart.verify'), [
            'deliveryMode' => DeliveryMode::DELIVERY->value,
            'items' => [
                [
                    'item' => [
                        'id' => $item->getKey(),
                        'name' => 'Stale product name',
                        'cover_image' => '/images/stale-item-cover.png',
                    ],
                    'variation' => [
                        'id' => $variation->getKey(),
                        'barcode' => 'CURRENT-IMAGE-001',
                        'name' => 'Stale variation name',
                        'price' => 999,
                        'image' => [
                            'src' => '/images/stale-variation.png',
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
        ])
            ->assertOk()
            ->assertJsonPath(
                'items.0.variation.image.src',
                '/images/variation.png',
            )
            ->assertJsonPath(
                'items.0.item.cover_image',
                '/images/item-cover.png',
            );
    }
}
