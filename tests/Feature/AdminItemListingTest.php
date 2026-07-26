<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\Item;
use App\Models\Origin;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminItemListingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_supervisor_can_list_and_unlist_each_item(): void
    {
        $supervisor = User::factory()->create([
            'access_level' => AccessLevel::SUPERVISOR->value,
        ]);
        $items = collect([
            $this->listableItem('First item'),
            $this->listableItem('Second item'),
        ]);

        foreach ($items as $item) {
            $this->actingAs($supervisor)
                ->patchJson(route('admin.ajax.item.listing.update', $item), [
                    'is_listed' => true,
                ])
                ->assertOk()
                ->assertJson([
                    'id' => $item->id,
                    'is_listed' => true,
                ]);

            $this->assertTrue($item->refresh()->is_listed);

            $this->actingAs($supervisor)
                ->patchJson(route('admin.ajax.item.listing.update', $item), [
                    'is_listed' => false,
                ])
                ->assertOk()
                ->assertJson([
                    'id' => $item->id,
                    'is_listed' => false,
                ]);

            $this->assertFalse($item->refresh()->is_listed);
        }
    }

    public function test_item_details_must_be_complete_before_listing(): void
    {
        $incompleteDetails = [
            ['name' => ''],
            ['name_en' => null],
            ['desc' => null],
            ['origin_id' => null],
        ];

        foreach ($incompleteDetails as $index => $attributes) {
            $item = $this->listableItem("Incomplete details {$index}");
            $item->forceFill($attributes)->save();

            $this->actingAs($this->supervisor())
                ->patchJson(route('admin.ajax.item.listing.update', $item), [
                    'is_listed' => true,
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('is_listed');

            $this->assertFalse($item->refresh()->is_listed);
        }
    }

    public function test_item_requires_at_least_one_variation_before_listing(): void
    {
        $item = $this->listableItem('Item without variations');
        $item->variations()->delete();

        $this->actingAs($this->supervisor())
            ->patchJson(route('admin.ajax.item.listing.update', $item), [
                'is_listed' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('is_listed');

        $this->assertFalse($item->refresh()->is_listed);
    }

    public function test_every_variation_requires_a_barcode_and_bilingual_names_before_listing(): void
    {
        $incompleteVariationDetails = [
            ['barcode' => ''],
            ['name' => ''],
            ['name_en' => null],
        ];

        foreach ($incompleteVariationDetails as $index => $attributes) {
            $item = $this->listableItem("Incomplete variation {$index}");
            $variation = $item->variations()->firstOrFail();
            $variation->forceFill($attributes)->save();

            $this->actingAs($this->supervisor())
                ->patchJson(route('admin.ajax.item.listing.update', $item), [
                    'is_listed' => true,
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('is_listed');

            $this->assertFalse($item->refresh()->is_listed);
        }
    }

    public function test_incomplete_item_can_always_be_unlisted(): void
    {
        $item = Item::query()->create([
            'name' => 'Incomplete listed item',
            'is_listed' => true,
        ]);

        $this->actingAs($this->supervisor())
            ->patchJson(route('admin.ajax.item.listing.update', $item), [
                'is_listed' => false,
            ])
            ->assertOk()
            ->assertJsonPath('is_listed', false);

        $this->assertFalse($item->refresh()->is_listed);
    }

    public function test_listing_status_requires_a_strict_boolean(): void
    {
        $item = Item::query()->create(['name' => 'Invalid listing item']);

        $this->actingAs($this->supervisor())
            ->patchJson(route('admin.ajax.item.listing.update', $item), [
                'is_listed' => 'true',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('is_listed');

        $this->assertFalse($item->refresh()->is_listed);
    }

    public function test_editor_cannot_change_an_item_listing_status(): void
    {
        $editor = User::factory()->create([
            'access_level' => AccessLevel::EDITOR->value,
        ]);
        $item = Item::query()->create(['name' => 'Protected item']);

        $this->actingAs($editor)
            ->patchJson(route('admin.ajax.item.listing.update', $item), [
                'is_listed' => true,
            ])
            ->assertForbidden();

        $this->assertFalse($item->refresh()->is_listed);
    }

    private function supervisor(): User
    {
        return User::factory()->create([
            'access_level' => AccessLevel::SUPERVISOR->value,
        ]);
    }

    private function listableItem(string $name): Item
    {
        $origin = Origin::query()->create([
            'name' => '马来西亚',
            'name_en' => 'Malaysia',
        ]);
        $item = Item::query()->create([
            'name' => $name,
            'name_en' => "{$name} English",
            'desc' => "{$name} description",
        ]);
        $item->origin()->associate($origin)->save();
        $item->variations()->create([
            'barcode' => "SKU-{$item->id}",
            'name' => "{$name} variation",
            'name_en' => "{$name} variation English",
        ]);

        return $item;
    }
}
