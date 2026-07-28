<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminWikiTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.wiki.page'))
            ->assertRedirect(route('login.page'));
    }

    public function test_authenticated_staff_can_open_the_wiki(): void
    {
        $user = new User;
        $user->forceFill([
            'id' => 100,
            'username' => 'viewer',
            'access_level' => AccessLevel::VIEWER->value,
            'is_enabled' => true,
        ]);
        $user->exists = true;

        $this->actingAs($user)
            ->get(route('admin.wiki.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/wiki/WikiPage'));
    }
}
