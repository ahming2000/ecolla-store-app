<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemVariation;
use App\Models\Origin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminItemManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_created_item_is_returned_with_the_relationships_used_by_the_management_list(): void
    {
        Category::query()->forceCreate([
            'id' => Category::DEFAULT_CATEGORY_ID,
            'name' => 'Uncategorized',
            'name_en' => 'Uncategorized',
        ]);

        $response = $this->postJson(route('admin.ajax.item.store'), [
            'name' => 'New snack',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('name', 'New snack')
            ->assertJsonPath('slug', 'new-snack')
            ->assertJsonPath('categories.0.id', Category::DEFAULT_CATEGORY_ID)
            ->assertJsonCount(0, 'images')
            ->assertJsonCount(0, 'variations');

        $item = Item::query()->where('name', 'New snack')->firstOrFail();

        $this->assertModelExists($item);
        $this->assertTrue(
            $item->categories()
                ->whereKey(Category::DEFAULT_CATEGORY_ID)
                ->exists(),
        );
    }

    public function test_management_items_are_returned_in_creation_date_descending_order(): void
    {
        Item::query()->forceCreate([
            'name' => 'Older snack',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        Item::query()->forceCreate([
            'name' => 'Newest snack',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson(route('admin.ajax.item.index'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Newest snack')
            ->assertJsonPath('data.1.name', 'Older snack')
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('per_page', 50)
            ->assertJsonPath('total', 2);
    }

    public function test_management_items_are_searched_across_item_origin_and_variation_fields(): void
    {
        $origin = Origin::query()->forceCreate([
            'name' => 'Needle origin',
            'name_en' => 'Origin',
        ]);

        $matchingItems = collect([
            Item::query()->forceCreate([
                'name' => 'Needle name',
            ]),
            Item::query()->forceCreate([
                'name' => 'Item',
                'name_en' => 'Needle English name',
            ]),
            Item::query()->forceCreate([
                'name' => 'Item',
                'desc' => 'Contains needle in description',
            ]),
            Item::query()->forceCreate([
                'name' => 'Item',
                'origin_id' => $origin->id,
            ]),
            Item::query()->forceCreate([
                'name' => 'Item',
            ]),
        ]);

        ItemVariation::query()->forceCreate([
            'item_id' => $matchingItems->last()->id,
            'barcode' => 'NEEDLE-SKU',
            'name' => 'Variation',
        ]);
        Item::query()->forceCreate([
            'name' => 'Unrelated item',
            'name_en' => 'Other item',
            'desc' => 'Nothing relevant',
        ]);

        $response = $this->getJson(route('admin.ajax.item.index', [
            'keyword' => 'needle',
        ]))->assertOk();

        $this->assertEqualsCanonicalizing(
            $matchingItems->pluck('id')->all(),
            collect($response->json('data'))->pluck('id')->all(),
        );
    }

    public function test_management_items_can_be_filtered_by_any_selected_category(): void
    {
        $snacks = Category::query()->forceCreate([
            'name' => 'Snacks',
            'name_en' => 'Snacks',
        ]);
        $drinks = Category::query()->forceCreate([
            'name' => 'Drinks',
            'name_en' => 'Drinks',
        ]);
        $snack = Item::query()->forceCreate(['name' => 'Snack']);
        $drink = Item::query()->forceCreate(['name' => 'Drink']);
        $other = Item::query()->forceCreate(['name' => 'Other']);

        $snack->categories()->attach($snacks);
        $drink->categories()->attach($drinks);

        $response = $this->getJson(route('admin.ajax.item.index', [
            'category_ids' => [$snacks->id, $drinks->id],
        ]))->assertOk();

        $this->assertEqualsCanonicalizing(
            [$snack->id, $drink->id],
            collect($response->json('data'))->pluck('id')->all(),
        );
        $this->assertNotContains(
            $other->id,
            collect($response->json('data'))->pluck('id')->all(),
        );
    }

    public function test_management_items_can_be_filtered_by_stock_and_listing_status(): void
    {
        $matchingItem = Item::query()->forceCreate([
            'name' => 'Unlisted and out of stock',
            'is_listed' => false,
        ]);
        $inStockItem = Item::query()->forceCreate([
            'name' => 'Unlisted but in stock',
            'is_listed' => false,
        ]);
        $listedItem = Item::query()->forceCreate([
            'name' => 'Listed and out of stock',
            'is_listed' => true,
        ]);

        ItemVariation::query()->forceCreate([
            'item_id' => $matchingItem->id,
            'barcode' => 'ZERO-STOCK',
            'name' => 'Zero stock',
            'stock' => 0,
        ]);
        ItemVariation::query()->forceCreate([
            'item_id' => $inStockItem->id,
            'barcode' => 'IN-STOCK',
            'name' => 'In stock',
            'stock' => 1,
        ]);
        ItemVariation::query()->forceCreate([
            'item_id' => $listedItem->id,
            'barcode' => 'LISTED-ZERO-STOCK',
            'name' => 'Zero stock',
            'stock' => 0,
        ]);

        $this->getJson(route('admin.ajax.item.index', [
            'out_of_stock' => true,
            'not_listed' => true,
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingItem->id);
    }

    public function test_management_items_can_be_sorted_and_paginated_by_the_backend(): void
    {
        Item::query()->forceCreate([
            'name' => 'Zulu',
            'sold_count' => 5,
        ]);
        Item::query()->forceCreate([
            'name' => 'Alpha',
            'sold_count' => 10,
        ]);

        $this->getJson(route('admin.ajax.item.index', [
            'sort_by' => 'name',
            'sort_direction' => 'asc',
            'per_page' => 50,
        ]))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alpha')
            ->assertJsonPath('data.1.name', 'Zulu');

        $items = collect(range(1, 49))->map(fn (int $index): array => [
            'name' => "Paginated item {$index}",
            'slug' => "paginated-item-{$index}",
            'created_at' => now()->subMinutes($index),
            'updated_at' => now()->subMinutes($index),
        ]);
        Item::query()->insert($items->all());

        $this->getJson(route('admin.ajax.item.index', [
            'page' => 2,
            'per_page' => 50,
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('total', 51);
    }

    public function test_management_item_filters_reject_invalid_values(): void
    {
        $this->getJson(route('admin.ajax.item.index', [
            'category_ids' => [999_999],
            'sort_by' => 'deleted_at',
            'sort_direction' => 'sideways',
            'page' => 0,
            'per_page' => 10,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'category_ids.0',
                'sort_by',
                'sort_direction',
                'page',
                'per_page',
            ]);
    }
}
