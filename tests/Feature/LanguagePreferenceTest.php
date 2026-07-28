<?php

namespace Tests\Feature;

use App\Enums\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LanguagePreferenceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_update_the_admin_language_preference(): void
    {
        $this->put(route('admin.lang.update'), [
            'lang' => Language::EN->value,
        ])
            ->assertRedirect(route('login.page'));
    }

    public function test_authenticated_language_preference_is_stored_for_the_user(): void
    {
        $user = User::factory()->create([
            'lang' => Language::ZH->value,
        ]);

        $this->actingAs($user)
            ->from(route('admin.dashboard.page'))
            ->put(route('admin.lang.update'), [
                'lang' => Language::EN->value,
            ])
            ->assertRedirect(route('admin.dashboard.page'));

        $this->assertSame(Language::EN->value, $user->refresh()->lang);
    }

    public function test_unsupported_language_is_rejected(): void
    {
        $user = User::factory()->create([
            'lang' => Language::ZH->value,
        ]);

        $this->actingAs($user)
            ->from(route('admin.dashboard.page'))
            ->put(route('admin.lang.update'), [
                'lang' => 'fr',
            ])
            ->assertRedirect(route('admin.dashboard.page'))
            ->assertSessionHasErrors('lang');

        $this->assertSame(Language::ZH->value, $user->refresh()->lang);
    }

    public function test_changing_log_page_includes_notes_for_every_supported_language(): void
    {
        $user = User::factory()->create([
            'lang' => Language::EN->value,
        ]);

        $this->actingAs($user)
            ->get(route('admin.changing-log.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/changing-log/ChangingLogPage')
                ->where('auth.user.lang', Language::EN->value)
                ->where('notes.en.versionLabel', 'v4.1.1 Public Release')
                ->where('notes.en.logs.0.groupName', 'v4.1 Public Release')
                ->where(
                    'notes.en.logs.0.subGroups.0.details.0.desc.0',
                    'Refreshed error pages with clearer branded styling, responsive layouts, and improved navigation.',
                )
                ->where(
                    'notes.en.logs.0.subGroups.0.details.0.desc.1',
                    'Prevented duplicate active variation barcodes while allowing deleted barcodes to be reused.',
                )
                ->where(
                    'notes.en.logs.0.subGroups.0.details.0.desc.2',
                    'Restored category and origin management in settings, including creating, editing, and deleting entries.',
                )
                ->where(
                    'notes.en.logs.0.subGroups.0.details.0.desc.3',
                    'Ensured predefined categories and origins are available in production.',
                )
                ->where(
                    'notes.en.logs.0.subGroups.1.details.0.desc.0',
                    'Added the ability for users to update their password from their profile.',
                )
                ->where('notes.en.logs.1.groupName', 'v4.0 Public Release')
                ->where('notes.en.logs.2.groupName', 'v3.0 Never Released')
                ->where(
                    'notes.en.logs.2.subGroups.0.details.0.desc.0',
                    'Version 3 was never released. Its planned improvements were carried forward into v4.',
                )
                ->where('notes.zh.versionLabel', 'v4.1.1 正式版')
                ->where('notes.zh.logs.0.groupName', 'v4.1 正式版')
                ->where(
                    'notes.zh.logs.0.subGroups.0.details.0.desc.0',
                    '更新了错误页面，采用更清晰的品牌样式、响应式布局和导航',
                )
                ->where(
                    'notes.zh.logs.0.subGroups.0.details.0.desc.1',
                    '防止有效的商品规格条码重复，并允许重新使用已删除规格的条码',
                )
                ->where(
                    'notes.zh.logs.0.subGroups.0.details.0.desc.2',
                    '恢复了设定页面中的类别和产地管理功能，包括创建、编辑和删除',
                )
                ->where(
                    'notes.zh.logs.0.subGroups.0.details.0.desc.3',
                    '确保正式环境中提供预设类别和产地',
                )
                ->where(
                    'notes.zh.logs.0.subGroups.1.details.0.desc.0',
                    '添加了用户在个人资料页面更新密码的功能',
                )
                ->where('notes.zh.logs.1.groupName', 'v4.0 正式版')
                ->where('notes.zh.logs.2.groupName', 'v3.0 未发布版本')
                ->where(
                    'notes.zh.logs.2.subGroups.0.details.0.desc.0',
                    'v3 从未正式发布，原计划推出的改进已整合至 v4',
                ));
    }
}
