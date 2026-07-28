<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\Category;
use App\Models\Item;
use App\Models\Origin;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AdminCatalogSettingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_settings_page_includes_origins_and_categories_with_item_counts(): void
    {
        $origin = Origin::query()->create([
            'name' => '马来西亚',
            'name_en' => 'Malaysia',
        ]);
        $category = Category::query()->create([
            'name' => '饮料',
            'name_en' => 'Beverage',
        ]);
        $item = Item::query()->create([
            'name' => 'Tea',
            'origin_id' => $origin->id,
        ]);
        $item->categories()->attach($category);

        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->get(route('admin.setting.page'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->component('admin/setting/SettingPage')
                    ->has('origins', 1)
                    ->where('origins.0.id', $origin->id)
                    ->where('origins.0.items_count', 1)
                    ->has('categories', 1)
                    ->where('categories.0.id', $category->id)
                    ->where('categories.0.items_count', 1),
            );
    }

    public function test_admin_catalog_endpoints_include_unlisted_item_counts(): void
    {
        $origin = Origin::query()->create([
            'name' => '马来西亚',
            'name_en' => 'Malaysia',
        ]);
        $category = Category::query()->create([
            'name' => '饮料',
            'name_en' => 'Beverage',
        ]);
        $item = Item::query()->create([
            'name' => 'Unlisted tea',
            'origin_id' => $origin->id,
            'is_listed' => false,
        ]);
        $item->categories()->attach($category);

        $this->getJson(route('admin.ajax.origin.index'))
            ->assertOk()
            ->assertJsonPath('0.items_count', 1);

        $this->getJson(route('admin.ajax.category.index'))
            ->assertOk()
            ->assertJsonPath('0.items_count', 1);
    }

    public function test_supervisor_can_create_update_and_delete_catalog_settings(): void
    {
        $supervisor = $this->user(AccessLevel::SUPERVISOR);
        Category::query()->create([
            'name' => '未分类',
            'name_en' => 'Uncategorized',
        ]);

        $this->actingAs($supervisor)
            ->from(route('admin.setting.page'))
            ->post(route('admin.setting.origin.store'), [
                'name' => ' 日本 ',
                'name_en' => ' Japan ',
            ])
            ->assertRedirect(route('admin.setting.page'));

        $this->actingAs($supervisor)
            ->from(route('admin.setting.page'))
            ->post(route('admin.setting.category.store'), [
                'name' => ' 零食 ',
                'name_en' => ' Snack ',
            ])
            ->assertRedirect(route('admin.setting.page'));

        $origin = Origin::query()->where('name_en', 'Japan')->firstOrFail();
        $category = Category::query()->where('name_en', 'Snack')->firstOrFail();

        $this->actingAs($supervisor)
            ->from(route('admin.setting.page'))
            ->patch(route('admin.setting.origin.update', $origin), [
                'name' => '马来西亚',
                'name_en' => 'Malaysia',
            ])
            ->assertRedirect(route('admin.setting.page'));

        $this->actingAs($supervisor)
            ->from(route('admin.setting.page'))
            ->patch(route('admin.setting.category.update', $category), [
                'name' => '饮料',
                'name_en' => 'Beverage',
            ])
            ->assertRedirect(route('admin.setting.page'));

        $this->assertSame('Malaysia', $origin->refresh()->name_en);
        $this->assertSame('Beverage', $category->refresh()->name_en);

        $this->actingAs($supervisor)
            ->from(route('admin.setting.page'))
            ->delete(route('admin.setting.origin.destroy', $origin))
            ->assertRedirect(route('admin.setting.page'));

        $this->actingAs($supervisor)
            ->from(route('admin.setting.page'))
            ->delete(route('admin.setting.category.destroy', $category))
            ->assertRedirect(route('admin.setting.page'));

        $this->assertSoftDeleted($origin);
        $this->assertSoftDeleted($category);
    }

    public function test_catalog_names_are_required_and_limited(): void
    {
        $supervisor = $this->user(AccessLevel::SUPERVISOR);

        $this->actingAs($supervisor)
            ->post(route('admin.setting.origin.store'), [
                'name' => '',
                'name_en' => str_repeat('a', 256),
            ])
            ->assertSessionHasErrors(['name', 'name_en']);

        $this->actingAs($supervisor)
            ->post(route('admin.setting.category.store'), [
                'name' => '',
                'name_en' => null,
            ])
            ->assertSessionHasErrors(['name', 'name_en']);

        $this->assertSame(0, Origin::query()->count());
        $this->assertSame(0, Category::query()->count());
    }

    public function test_editor_cannot_manage_catalog_settings(): void
    {
        $editor = $this->user(AccessLevel::EDITOR);
        $origin = Origin::query()->create([
            'name' => '日本',
            'name_en' => 'Japan',
        ]);
        $category = Category::query()->create([
            'name' => '零食',
            'name_en' => 'Snack',
        ]);

        $this->actingAs($editor)
            ->post(route('admin.setting.origin.store'), [
                'name' => '中国',
                'name_en' => 'China',
            ])
            ->assertForbidden();

        $this->actingAs($editor)
            ->patch(route('admin.setting.category.update', $category), [
                'name' => '饮料',
                'name_en' => 'Beverage',
            ])
            ->assertForbidden();

        $this->actingAs($editor)
            ->delete(route('admin.setting.origin.destroy', $origin))
            ->assertForbidden();

        $this->assertSame('Japan', $origin->refresh()->name_en);
        $this->assertSame('Snack', $category->refresh()->name_en);
    }

    public function test_default_and_in_use_catalog_settings_cannot_be_deleted(): void
    {
        $supervisor = $this->user(AccessLevel::SUPERVISOR);
        $defaultCategory = Category::query()->forceCreate([
            'id' => Category::DEFAULT_CATEGORY_ID,
            'name' => '未分类',
            'name_en' => 'Uncategorized',
        ]);
        $usedCategory = Category::query()->create([
            'name' => '饮料',
            'name_en' => 'Beverage',
        ]);
        $usedOrigin = Origin::query()->create([
            'name' => '马来西亚',
            'name_en' => 'Malaysia',
        ]);
        $item = Item::query()->create([
            'name' => 'Tea',
            'origin_id' => $usedOrigin->id,
        ]);
        $item->categories()->attach($usedCategory);

        $this->actingAs($supervisor)
            ->delete(route('admin.setting.category.destroy', $defaultCategory))
            ->assertSessionHasErrors('category');

        $this->actingAs($supervisor)
            ->delete(route('admin.setting.category.destroy', $usedCategory))
            ->assertSessionHasErrors('category');

        $this->actingAs($supervisor)
            ->delete(route('admin.setting.origin.destroy', $usedOrigin))
            ->assertSessionHasErrors('origin');

        $this->assertNotSoftDeleted($defaultCategory);
        $this->assertNotSoftDeleted($usedCategory);
        $this->assertNotSoftDeleted($usedOrigin);
    }

    private function user(AccessLevel $accessLevel): User
    {
        return User::factory()->create([
            'access_level' => $accessLevel->value,
        ]);
    }
}
