<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminProfilePasswordTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_open_their_profile_page(): void
    {
        $this->actingAs($this->user())
            ->get(route('admin.profile.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/profile/ProfilePage'));
    }

    public function test_authenticated_user_can_update_their_password(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->from(route('admin.profile.page'))
            ->patch(route('admin.profile.password.update'), [
                'old_password' => 'password',
                'password' => 'updated-password',
                'password_confirmation' => 'updated-password',
            ])
            ->assertRedirect(route('admin.profile.page'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(
            'updated-password',
            $user->refresh()->password,
        ));
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = $this->user();
        $originalPassword = $user->password;

        $this->actingAs($user)
            ->from(route('admin.profile.page'))
            ->patch(route('admin.profile.password.update'), [
                'old_password' => 'incorrect-password',
                'password' => 'updated-password',
                'password_confirmation' => 'updated-password',
            ])
            ->assertRedirect(route('admin.profile.page'))
            ->assertSessionHasErrors('old_password');

        $this->assertSame($originalPassword, $user->refresh()->password);
    }

    public function test_new_password_must_be_confirmed(): void
    {
        $user = $this->user();
        $originalPassword = $user->password;

        $this->actingAs($user)
            ->from(route('admin.profile.page'))
            ->patch(route('admin.profile.password.update'), [
                'old_password' => 'password',
                'password' => 'updated-password',
                'password_confirmation' => 'different-password',
            ])
            ->assertRedirect(route('admin.profile.page'))
            ->assertSessionHasErrors('password');

        $this->assertSame($originalPassword, $user->refresh()->password);
    }

    public function test_guest_cannot_update_a_password(): void
    {
        $this->patch(route('admin.profile.password.update'), [
            'old_password' => 'password',
            'password' => 'updated-password',
            'password_confirmation' => 'updated-password',
        ])->assertRedirect(route('login.page'));
    }

    private function user(): User
    {
        return User::factory()->create([
            'password' => 'password',
            'access_level' => AccessLevel::VIEWER->value,
            'is_enabled' => true,
        ]);
    }
}
