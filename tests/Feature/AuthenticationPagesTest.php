<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthenticationPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_view_the_login_page(): void
    {
        $this->get(route('login.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/auth/Login'));
    }

    public function test_guest_is_redirected_from_the_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard.page'))
            ->assertRedirect(route('login.page'));

        $this->assertGuest();
    }

    public function test_login_requires_a_username_and_password(): void
    {
        $this->post(route('login'), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['username', 'password']);

        $this->assertGuest();
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'password',
        ])
            ->assertRedirect(route('admin.dashboard.page', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_log_in_with_an_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'incorrect-password',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['is_enabled' => false]);

        $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'password',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_deactivated_authenticated_user_is_logged_out(): void
    {
        $user = User::factory()->create(['is_enabled' => false]);

        $this->actingAs($user)
            ->get(route('admin.dashboard.page'))
            ->assertRedirect(route('login.page'));

        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_unknown_admin_page_returns_the_admin_error_component(): void
    {
        $this->get('/admin/page-that-does-not-exist')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error/Admin')
                ->where('status', 404));
    }
}
