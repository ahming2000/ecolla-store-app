<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_deactivate_a_staff_account(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create(['is_enabled' => true]);

        $this->actingAs($admin)
            ->patchJson(route('admin.ajax.user.deactivate', $staff))
            ->assertOk()
            ->assertJsonPath('id', $staff->id)
            ->assertJsonPath('is_enabled', false);

        $this->assertFalse($staff->refresh()->is_enabled);
        $this->assertNotSoftDeleted($staff);
    }

    public function test_admin_can_reactivate_a_deactivated_staff_account(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create(['is_enabled' => false]);

        $this->actingAs($admin)
            ->patchJson(route('admin.ajax.user.reactivate', $staff))
            ->assertOk()
            ->assertJsonPath('id', $staff->id)
            ->assertJsonPath('is_enabled', true);

        $this->assertTrue($staff->refresh()->is_enabled);
        $this->assertNotSoftDeleted($staff);
    }

    public function test_admin_can_soft_delete_a_staff_account(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create(['is_enabled' => true]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.ajax.user.destroy', $staff))
            ->assertNoContent();

        $this->assertSoftDeleted($staff);
        $this->assertFalse($staff->refresh()->is_enabled);
    }

    public function test_non_admin_cannot_change_status_or_delete_a_staff_account(): void
    {
        $viewer = User::factory()->create([
            'access_level' => AccessLevel::VIEWER->value,
        ]);
        $staff = User::factory()->create();

        $this->actingAs($viewer)
            ->patchJson(route('admin.ajax.user.deactivate', $staff))
            ->assertForbidden();

        $staff->update(['is_enabled' => false]);

        $this->actingAs($viewer)
            ->patchJson(route('admin.ajax.user.reactivate', $staff))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->deleteJson(route('admin.ajax.user.destroy', $staff))
            ->assertForbidden();

        $this->assertFalse($staff->refresh()->is_enabled);
        $this->assertNotSoftDeleted($staff);
    }

    public function test_admin_cannot_change_status_or_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patchJson(route('admin.ajax.user.deactivate', $admin))
            ->assertForbidden();

        $this->actingAs($admin)
            ->patchJson(route('admin.ajax.user.reactivate', $admin))
            ->assertForbidden();

        $this->actingAs($admin)
            ->deleteJson(route('admin.ajax.user.destroy', $admin))
            ->assertForbidden();

        $this->assertTrue($admin->refresh()->is_enabled);
        $this->assertNotSoftDeleted($admin);
    }

    public function test_deleted_username_can_be_reused_for_an_enabled_account(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create(['username' => 'reusable-account']);

        $this->actingAs($admin)
            ->deleteJson(route('admin.ajax.user.destroy', $staff))
            ->assertNoContent();

        $this->actingAs($admin)
            ->postJson(route('admin.ajax.user.create'), [
                'username' => 'reusable-account',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
                'access_level' => AccessLevel::EDITOR->value,
            ])
            ->assertOk()
            ->assertJsonPath('username', 'reusable-account')
            ->assertJsonPath('access_level', AccessLevel::EDITOR->value)
            ->assertJsonPath('is_enabled', true);

        $this->assertSame(
            2,
            User::withTrashed()->where('username', 'reusable-account')->count(),
        );
        $this->assertSame(
            1,
            User::query()->where('username', 'reusable-account')->count(),
        );
    }

    public function test_active_username_unique_index_rejects_duplicates(): void
    {
        User::factory()->create(['username' => 'unique-account']);

        $this->expectException(QueryException::class);

        User::factory()->create(['username' => 'unique-account']);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'access_level' => AccessLevel::ADMIN->value,
            'is_enabled' => true,
        ]);
    }
}
