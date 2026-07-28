<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setting(
            SettingService::SHIPPING_FEE,
            '3',
            'Shipping fee',
        );
        $this->setting(
            SettingService::FREE_SHIPPING_IS_ACTIVATED,
            '1',
            'Free shipping enabled',
        );
        $this->setting(
            SettingService::FREE_SHIPPING_THRESHOLD,
            '50',
            'Free shipping threshold',
        );
        $this->setting(
            SettingService::FREE_SHIPPING_DESCRIPTION,
            'Orders over RM50 ship free',
            'Free shipping description',
        );
    }

    public function test_settings_page_includes_the_current_shipping_configuration(): void
    {
        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->get(route('admin.setting.page'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->component('admin/setting/SettingPage')
                    ->where('shipping.fee', 3)
                    ->where('shipping.freeShipping.isActivated', true)
                    ->where('shipping.freeShipping.threshold', 50)
                    ->where(
                        'shipping.freeShipping.description',
                        'Orders over RM50 ship free',
                    ),
            );
    }

    public function test_supervisor_can_update_shipping_settings(): void
    {
        $supervisor = $this->user(AccessLevel::SUPERVISOR);

        $this->actingAs($supervisor)
            ->from(route('admin.setting.page'))
            ->patch(route('admin.setting.shipping.update'), [
                'shipping_fee' => 7.25,
            ])
            ->assertRedirect(route('admin.setting.page'));

        $this->actingAs($supervisor)
            ->from(route('admin.setting.page'))
            ->patch(route('admin.setting.free-shipping.update'), [
                'is_activated' => false,
                'threshold' => 75.50,
                'description' => 'Free delivery over RM75.50',
            ])
            ->assertRedirect(route('admin.setting.page'));

        $this->assertSame(
            '7.25',
            Setting::query()->findOrFail(SettingService::SHIPPING_FEE)->value,
        );
        $this->assertSame(
            '0',
            Setting::query()
                ->findOrFail(SettingService::FREE_SHIPPING_IS_ACTIVATED)
                ->value,
        );
        $this->assertSame(
            '75.5',
            Setting::query()
                ->findOrFail(SettingService::FREE_SHIPPING_THRESHOLD)
                ->value,
        );
        $this->assertSame(
            'Free delivery over RM75.50',
            Setting::query()
                ->findOrFail(SettingService::FREE_SHIPPING_DESCRIPTION)
                ->value,
        );
    }

    public function test_zero_values_and_an_empty_description_are_valid(): void
    {
        $supervisor = $this->user(AccessLevel::SUPERVISOR);

        $this->actingAs($supervisor)
            ->patch(route('admin.setting.shipping.update'), [
                'shipping_fee' => 0,
            ])
            ->assertRedirect();

        $this->actingAs($supervisor)
            ->patch(route('admin.setting.free-shipping.update'), [
                'is_activated' => true,
                'threshold' => 0,
                'description' => null,
            ])
            ->assertRedirect();

        $this->assertSame(
            '0',
            Setting::query()->findOrFail(SettingService::SHIPPING_FEE)->value,
        );
        $this->assertSame(
            '',
            Setting::query()
                ->findOrFail(SettingService::FREE_SHIPPING_DESCRIPTION)
                ->value,
        );
    }

    public function test_invalid_shipping_settings_are_rejected(): void
    {
        $supervisor = $this->user(AccessLevel::SUPERVISOR);

        $this->actingAs($supervisor)
            ->patch(route('admin.setting.shipping.update'), [
                'shipping_fee' => -0.01,
            ])
            ->assertSessionHasErrors('shipping_fee');

        $this->actingAs($supervisor)
            ->patch(route('admin.setting.free-shipping.update'), [
                'is_activated' => 'yes',
                'threshold' => -1,
                'description' => str_repeat('a', 256),
            ])
            ->assertSessionHasErrors([
                'is_activated',
                'threshold',
                'description',
            ]);

        $this->assertSame(
            '3',
            Setting::query()->findOrFail(SettingService::SHIPPING_FEE)->value,
        );
        $this->assertSame(
            '50',
            Setting::query()
                ->findOrFail(SettingService::FREE_SHIPPING_THRESHOLD)
                ->value,
        );
    }

    public function test_viewer_cannot_update_shipping_settings(): void
    {
        $viewer = $this->user(AccessLevel::VIEWER);

        $this->actingAs($viewer)
            ->patch(route('admin.setting.shipping.update'), [
                'shipping_fee' => 9,
            ])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->patch(route('admin.setting.free-shipping.update'), [
                'is_activated' => false,
                'threshold' => 100,
                'description' => 'Changed',
            ])
            ->assertForbidden();

        $this->assertSame(
            '3',
            Setting::query()->findOrFail(SettingService::SHIPPING_FEE)->value,
        );
        $this->assertSame(
            '1',
            Setting::query()
                ->findOrFail(SettingService::FREE_SHIPPING_IS_ACTIVATED)
                ->value,
        );
    }

    private function user(AccessLevel $accessLevel): User
    {
        return User::factory()->create([
            'access_level' => $accessLevel->value,
        ]);
    }

    private function setting(string $name, string $value, string $description): Setting
    {
        return Setting::query()->updateOrCreate(
            ['name' => $name],
            [
                'value' => $value,
                'desc' => $description,
            ],
        );
    }
}
