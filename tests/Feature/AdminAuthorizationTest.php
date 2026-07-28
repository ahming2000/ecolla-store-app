<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    public function test_viewer_can_open_management_pages_but_not_user_management(): void
    {
        $viewer = $this->user(AccessLevel::VIEWER);

        $this->actingAs($viewer)
            ->get(route('admin.item.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/item/ItemPage'));

        $this->actingAs($viewer)
            ->get(route('admin.order.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/order/OrderPage'));

        $this->actingAs($viewer)
            ->get(route('admin.user.page'))
            ->assertForbidden();
    }

    public function test_supervisor_still_cannot_open_user_management(): void
    {
        $this->actingAs($this->user(AccessLevel::SUPERVISOR))
            ->get(route('admin.user.page'))
            ->assertForbidden();
    }

    public function test_forbidden_admin_page_uses_the_admin_error_component_in_production(): void
    {
        config(['app.debug' => false]);

        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->get(route('admin.user.page'))
            ->assertForbidden()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error/Admin')
                ->where('status', 403));
    }

    public function test_admin_can_open_user_management(): void
    {
        $this->mock(UserService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getAllUsers')
                ->once()
                ->andReturn(new Collection);
        });

        $this->actingAs($this->user(AccessLevel::ADMIN))
            ->get(route('admin.user.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/user/UserPage')
                ->has('users', 0));
    }

    private function user(AccessLevel $accessLevel): User
    {
        $user = new User;
        $user->forceFill([
            'id' => $accessLevel->value + 100,
            'username' => strtolower($accessLevel->name),
            'access_level' => $accessLevel->value,
            'is_enabled' => true,
        ]);
        $user->exists = true;

        return $user;
    }
}
