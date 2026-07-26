<?php

namespace Tests\Feature;

use App\Enums\Language;
use App\Models\User;
use Database\Seeders\BaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserPreferenceDefaultsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_factory_has_language_and_timezone_defaults(): void
    {
        $user = User::factory()->make();

        $this->assertSame(Language::ZH->value, $user->lang);
        $this->assertSame('Asia/Kuala_Lumpur', $user->timezone);
    }

    public function test_base_seeder_sets_default_admin_language_and_timezone(): void
    {
        $this->seed(BaseSeeder::class);

        $admin = User::query()
            ->where('username', 'admin')
            ->firstOrFail();

        $this->assertSame(Language::ZH->value, $admin->lang);
        $this->assertSame('Asia/Kuala_Lumpur', $admin->timezone);
    }
}
