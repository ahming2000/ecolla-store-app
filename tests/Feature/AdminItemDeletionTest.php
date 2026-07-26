<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminItemDeletionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_supervisor_can_delete_an_item(): void
    {
        $supervisor = User::factory()->create([
            'access_level' => AccessLevel::SUPERVISOR->value,
        ]);
        $item = Item::query()->create(['name' => 'Disposable item']);

        $this->actingAs($supervisor)
            ->deleteJson(route('admin.ajax.item.destroy', $item))
            ->assertNoContent();

        $this->assertSoftDeleted($item);
    }

    public function test_editor_cannot_delete_an_item(): void
    {
        $editor = User::factory()->create([
            'access_level' => AccessLevel::EDITOR->value,
        ]);
        $item = Item::query()->create(['name' => 'Protected item']);

        $this->actingAs($editor)
            ->deleteJson(route('admin.ajax.item.destroy', $item))
            ->assertForbidden();

        $this->assertNotSoftDeleted($item);
    }

    public function test_guest_cannot_delete_an_item(): void
    {
        $item = Item::query()->create(['name' => 'Protected item']);

        $this->deleteJson(route('admin.ajax.item.destroy', $item))
            ->assertUnauthorized();

        $this->assertNotSoftDeleted($item);
    }
}
