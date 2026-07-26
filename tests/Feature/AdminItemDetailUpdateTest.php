<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\Category;
use App\Models\Item;
use App\Models\Origin;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminItemDetailUpdateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editor_can_update_item_details_origin_and_categories(): void
    {
        $defaultCategory = $this->category(
            Category::DEFAULT_CATEGORY_ID,
            'Uncategorized',
        );
        $oldCategory = $this->category(null, 'Drinks');
        $newCategory = $this->category(null, 'Snacks');
        $oldOrigin = $this->origin('Malaysia');
        $newOrigin = $this->origin('Japan');
        $item = Item::query()->create([
            'name' => 'Old item name',
            'name_en' => 'Old item name',
            'origin_id' => $oldOrigin->id,
        ]);
        $item->categories()->attach($oldCategory);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->putJson(route('admin.ajax.item.update', $item), [
                'name' => '  Updated item  ',
                'name_en' => ' Updated item English ',
                'desc' => ' Updated description ',
                'origin_id' => $newOrigin->id,
                'category_ids' => [
                    $defaultCategory->id,
                    $newCategory->id,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Updated item')
            ->assertJsonPath('name_en', 'Updated item English')
            ->assertJsonPath('slug', 'updated-item-english')
            ->assertJsonPath('desc', 'Updated description')
            ->assertJsonPath('origin.id', $newOrigin->id)
            ->assertJsonPath('categories.0.id', $newCategory->id)
            ->assertJsonCount(1, 'categories');

        $item->refresh();

        $this->assertSame('Updated item', $item->name);
        $this->assertSame('updated-item-english', $item->slug);
        $this->assertSame($newOrigin->id, $item->origin_id);
        $this->assertTrue($item->categories()->whereKey($newCategory)->exists());
        $this->assertFalse($item->categories()->whereKey($oldCategory)->exists());
        $this->assertFalse(
            $item->categories()->whereKey($defaultCategory)->exists(),
        );
    }

    public function test_empty_category_selection_uses_the_default_category(): void
    {
        $defaultCategory = $this->category(
            Category::DEFAULT_CATEGORY_ID,
            'Uncategorized',
        );
        $oldCategory = $this->category(null, 'Drinks');
        $item = Item::query()->create(['name' => 'Tea']);
        $item->categories()->attach($oldCategory);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->putJson(route('admin.ajax.item.update', $item), [
                ...$this->validItemData(),
                'category_ids' => [],
            ])
            ->assertOk()
            ->assertJsonPath('categories.0.id', $defaultCategory->id)
            ->assertJsonCount(1, 'categories');

        $this->assertTrue(
            $item->categories()->whereKey($defaultCategory)->exists(),
        );
        $this->assertFalse($item->categories()->whereKey($oldCategory)->exists());
    }

    public function test_item_detail_input_is_validated_before_updating(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->putJson(route('admin.ajax.item.update', $item), [
                'name' => '   ',
                'name_en' => str_repeat('a', 256),
                'desc' => [],
                'origin_id' => 999999,
                'category_ids' => [999999, 999999],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'name_en',
                'desc',
                'origin_id',
                'category_ids.0',
                'category_ids.1',
            ]);

        $this->assertSame('Tea', $item->refresh()->name);
    }

    public function test_item_detail_update_requires_an_editor(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);

        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->putJson(
                route('admin.ajax.item.update', $item),
                $this->validItemData(),
            )
            ->assertForbidden();

        Auth::logout();

        $this->putJson(
            route('admin.ajax.item.update', $item),
            $this->validItemData(),
        )->assertUnauthorized();
    }

    /**
     * @return array<string, mixed>
     */
    private function validItemData(): array
    {
        return [
            'name' => 'Tea',
            'name_en' => null,
            'desc' => null,
            'origin_id' => null,
            'category_ids' => [],
        ];
    }

    private function category(?int $id, string $name): Category
    {
        return Category::query()->forceCreate([
            ...($id === null ? [] : ['id' => $id]),
            'name' => $name,
            'name_en' => $name,
        ]);
    }

    private function origin(string $name): Origin
    {
        return Origin::query()->create([
            'name' => $name,
            'name_en' => $name,
        ]);
    }

    private function user(AccessLevel $accessLevel): User
    {
        return User::factory()->create([
            'access_level' => $accessLevel->value,
        ]);
    }
}
