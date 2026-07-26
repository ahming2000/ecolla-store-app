<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Origin;
use App\Models\PaymentMethod;
use App\Services\CategoryService;
use App\Services\OriginService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Tests\TestCase;

class StorefrontApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_category_endpoint_returns_categories_with_listed_item_counts(): void
    {
        $category = new Category(['name' => 'Tea']);
        $category->forceFill(['id' => 10, 'items_count' => 4]);

        $this->mock(CategoryService::class, function (MockInterface $mock) use ($category): void {
            $mock->shouldReceive('getCategoriesWithItemCount')
                ->once()
                ->withNoArgs()
                ->andReturn(new Collection([$category]));
        });

        $this->getJson(route('shop.ajax.category.index'))
            ->assertOk()
            ->assertJson([
                [
                    'id' => 10,
                    'name' => 'Tea',
                    'items_count' => 4,
                ],
            ]);
    }

    public function test_origin_endpoint_returns_origins_with_listed_item_counts(): void
    {
        $origin = new Origin(['name' => 'Malaysia']);
        $origin->forceFill(['id' => 20, 'items_count' => 7]);

        $this->mock(OriginService::class, function (MockInterface $mock) use ($origin): void {
            $mock->shouldReceive('getOriginsWithItemCount')
                ->once()
                ->withNoArgs()
                ->andReturn(new Collection([$origin]));
        });

        $this->getJson(route('shop.ajax.origin.index'))
            ->assertOk()
            ->assertJson([
                [
                    'id' => 20,
                    'name' => 'Malaysia',
                    'items_count' => 7,
                ],
            ]);
    }

    public function test_missing_item_returns_the_storefront_error_component(): void
    {
        $this->get(route('shop.ajax.item.show', ['item' => 'missing-item']))
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error/Shop')
                ->where('status', 404));
    }

    public function test_item_endpoint_returns_only_listed_items_and_filters_by_variation_barcode(): void
    {
        $matchingItem = Item::query()->create([
            'name' => 'Matching item',
            'is_listed' => true,
        ]);
        $matchingItem->variations()->create([
            'barcode' => 'MATCH-001',
            'name' => 'Matching variation',
            'price' => 10,
            'stock' => 5,
        ]);
        $otherItem = Item::query()->create([
            'name' => 'Other listed item',
            'is_listed' => true,
        ]);
        $otherItem->variations()->create([
            'barcode' => 'OTHER-001',
            'name' => 'Other variation',
            'price' => 12,
            'stock' => 5,
        ]);
        $unlistedItem = Item::query()->create([
            'name' => 'Unlisted item',
            'is_listed' => false,
        ]);
        $unlistedItem->variations()->create([
            'barcode' => 'MATCH-002',
            'name' => 'Hidden variation',
            'price' => 8,
            'stock' => 5,
        ]);

        $this->getJson(route('shop.ajax.item.index'))
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $matchingItem->getKey()])
            ->assertJsonFragment(['id' => $otherItem->getKey()])
            ->assertJsonMissing(['id' => $unlistedItem->getKey()]);

        $this->getJson(route('shop.ajax.item.index', [
            'barcodes' => ['MATCH-001', 'MATCH-002'],
        ]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $matchingItem->getKey());
    }

    public function test_item_endpoint_rejects_invalid_barcode_filters(): void
    {
        $this->getJson(route('shop.ajax.item.index', [
            'barcodes' => ['DUPLICATE', 'DUPLICATE'],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('barcodes.1');

        $this->getJson(route('shop.ajax.item.index', [
            'barcodes' => 'NOT-AN-ARRAY',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('barcodes');
    }

    public function test_payment_method_endpoint_excludes_disabled_methods(): void
    {
        $enabledPaymentMethod = PaymentMethod::query()->create([
            'name' => 'Enabled payment',
            'icon_img_path' => '/images/enabled.png',
            'qr_code_img_path' => '/images/enabled-qr.png',
            'is_enabled' => true,
        ]);
        $disabledPaymentMethod = PaymentMethod::query()->create([
            'name' => 'Disabled payment',
            'icon_img_path' => '/images/disabled.png',
            'qr_code_img_path' => '/images/disabled-qr.png',
            'is_enabled' => false,
        ]);

        $this->getJson(route('shop.ajax.payment-method.index'))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $enabledPaymentMethod->getKey())
            ->assertJsonMissing(['id' => $disabledPaymentMethod->getKey()]);
    }
}
