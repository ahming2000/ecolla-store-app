<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminItemCounterResetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editor_can_reset_an_item_view_count_without_changing_its_sold_count(): void
    {
        $item = Item::query()->create([
            'name' => 'Viewed item',
            'view_count' => 42,
            'sold_count' => 17,
        ]);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->patchJson(route('admin.ajax.item.view-count.reset', $item))
            ->assertOk()
            ->assertExactJson([
                'id' => $item->id,
                'view_count' => 0,
                'sold_count' => 17,
            ]);

        $item->refresh();

        $this->assertSame(0, $item->view_count);
        $this->assertSame(17, $item->sold_count);
    }

    public function test_editor_can_reset_an_item_sold_count_without_changing_its_view_count(): void
    {
        $item = Item::query()->create([
            'name' => 'Sold item',
            'view_count' => 42,
            'sold_count' => 17,
        ]);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->patchJson(route('admin.ajax.item.sold-count.reset', $item))
            ->assertOk()
            ->assertExactJson([
                'id' => $item->id,
                'view_count' => 42,
                'sold_count' => 0,
            ]);

        $item->refresh();

        $this->assertSame(42, $item->view_count);
        $this->assertSame(0, $item->sold_count);
    }

    public function test_viewer_cannot_reset_item_counters(): void
    {
        $item = Item::query()->create([
            'name' => 'Protected item',
            'view_count' => 42,
            'sold_count' => 17,
        ]);

        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->patchJson(route('admin.ajax.item.view-count.reset', $item))
            ->assertForbidden();

        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->patchJson(route('admin.ajax.item.sold-count.reset', $item))
            ->assertForbidden();

        $item->refresh();

        $this->assertSame(42, $item->view_count);
        $this->assertSame(17, $item->sold_count);
    }

    public function test_guest_cannot_reset_item_counters(): void
    {
        $item = Item::query()->create([
            'name' => 'Protected item',
            'view_count' => 42,
            'sold_count' => 17,
        ]);

        $this->patchJson(route('admin.ajax.item.view-count.reset', $item))
            ->assertUnauthorized();

        $this->patchJson(route('admin.ajax.item.sold-count.reset', $item))
            ->assertUnauthorized();

        $item->refresh();

        $this->assertSame(42, $item->view_count);
        $this->assertSame(17, $item->sold_count);
    }

    private function user(AccessLevel $accessLevel): User
    {
        return User::factory()->create([
            'access_level' => $accessLevel->value,
        ]);
    }
}
