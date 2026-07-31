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
                ->where('notes.en.versionLabel', 'v4.2.0 Public Release')
                ->where('notes.en.logs.0.groupName', 'v4.2 Public Release')
                ->where(
                    'notes.en.logs.0.subGroups.0.details.0.desc.0',
                    'Enhanced product pages with full-screen image previews, variation-image shortcuts, category filter links, stock details, and sold-out purchase controls.',
                )
                ->where(
                    'notes.en.logs.0.subGroups.0.details.0.desc.3',
                    'Added staged order editing for customer details, delivery mode, shipping fees, notes, item quantities, prices, and item removal.',
                )
                ->where(
                    'notes.en.logs.0.subGroups.0.details.0.desc.4',
                    'Added storefront order tracking using the order reference and checkout phone number.',
                )
                ->where(
                    'notes.en.logs.0.subGroups.0.details.1.desc.0',
                    'Improved image loading with lazy-loaded WebP thumbnails and full-quality previews, plus reliable cart and checkout fallbacks from variation images to product images.',
                )
                ->where(
                    'notes.en.logs.0.subGroups.0.details.1.desc.8',
                    'Kept the item editor open when closing an image preview with the Escape key.',
                )
                ->where('notes.en.logs.1.groupName', 'v4.1 Public Release')
                ->where(
                    'notes.en.logs.1.subGroups.0.details.0.desc.0',
                    'Refreshed error pages with clearer branded styling, responsive layouts, and improved navigation.',
                )
                ->where(
                    'notes.en.logs.1.subGroups.0.details.0.desc.1',
                    'Prevented duplicate active variation barcodes while allowing deleted barcodes to be reused.',
                )
                ->where(
                    'notes.en.logs.1.subGroups.0.details.0.desc.2',
                    'Restored category and origin management in settings, including creating, editing, and deleting entries.',
                )
                ->where(
                    'notes.en.logs.1.subGroups.0.details.0.desc.3',
                    'Ensured predefined categories and origins are available in production.',
                )
                ->where(
                    'notes.en.logs.1.subGroups.1.details.0.desc.0',
                    'Added the ability for users to update their password from their profile.',
                )
                ->where('notes.en.logs.2.groupName', 'v4.0 Public Release')
                ->where('notes.en.logs.3.groupName', 'v3.0 Never Released')
                ->where(
                    'notes.en.logs.3.subGroups.0.details.0.desc.0',
                    'Version 3 was never released. Its planned improvements were carried forward into v4.',
                )
                ->where('notes.zh.versionLabel', 'v4.2.0 正式版')
                ->where('notes.zh.logs.0.groupName', 'v4.2 正式版')
                ->where(
                    'notes.zh.logs.0.subGroups.0.details.0.desc.0',
                    '商品详情页新增图片全屏预览、规格图片快捷定位、类别筛选链接、各规格库存显示及缺货购买限制',
                )
                ->where(
                    'notes.zh.logs.0.subGroups.0.details.0.desc.3',
                    '新增管理员订单编辑功能，可调整顾客资料、订单模式、运费、备注、商品数量和价格，移除商品后再统一保存所有修改',
                )
                ->where(
                    'notes.zh.logs.0.subGroups.0.details.0.desc.4',
                    '新增顾客订单查询，可使用订单编号和结账时填写的电话号码查看订单进度',
                )
                ->where(
                    'notes.zh.logs.0.subGroups.0.details.1.desc.0',
                    '通过延迟加载 WebP 缩略图及保留原图预览来提升图片加载速度，并修正购物车与结账页面的规格和商品图片回退显示',
                )
                ->where(
                    'notes.zh.logs.0.subGroups.0.details.1.desc.8',
                    '使用 Escape 键关闭图片预览时，商品编辑窗口会保持开启',
                )
                ->where('notes.zh.logs.1.groupName', 'v4.1 正式版')
                ->where(
                    'notes.zh.logs.1.subGroups.0.details.0.desc.0',
                    '更新了错误页面，采用更清晰的品牌样式、响应式布局和导航',
                )
                ->where(
                    'notes.zh.logs.1.subGroups.0.details.0.desc.1',
                    '防止有效的商品规格条码重复，并允许重新使用已删除规格的条码',
                )
                ->where(
                    'notes.zh.logs.1.subGroups.0.details.0.desc.2',
                    '恢复了设定页面中的类别和产地管理功能，包括创建、编辑和删除',
                )
                ->where(
                    'notes.zh.logs.1.subGroups.0.details.0.desc.3',
                    '确保正式环境中提供预设类别和产地',
                )
                ->where(
                    'notes.zh.logs.1.subGroups.1.details.0.desc.0',
                    '添加了用户在个人资料页面更新密码的功能',
                )
                ->where('notes.zh.logs.2.groupName', 'v4.0 正式版')
                ->where('notes.zh.logs.3.groupName', 'v3.0 未发布版本')
                ->where(
                    'notes.zh.logs.3.subGroups.0.details.0.desc.0',
                    'v3 从未正式发布，原计划推出的改进已整合至 v4',
                ));
    }
}
