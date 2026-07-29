<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Setting;
use App\Services\ItemService;
use App\Services\PaymentMethodService;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Tests\TestCase;

class StorefrontPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_landing_page_returns_its_ranked_item_collections(): void
    {
        $this->mock(ItemService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getHighestViewCountItems')
                ->once()
                ->andReturn(new Collection);
            $mock->shouldReceive('getHighestSoldCountItems')
                ->once()
                ->andReturn(new Collection);
        });

        $this->get(route('shop.landing.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/landing/LandingPage')
                ->has('highestViewCountItems', 0)
                ->has('highestSoldCountItems', 0));
    }

    public function test_storefront_pages_share_the_free_shipping_configuration(): void
    {
        Setting::query()->create([
            'name' => SettingService::FREE_SHIPPING_IS_ACTIVATED,
            'value' => '1',
            'desc' => 'Free shipping enabled',
        ]);
        Setting::query()->create([
            'name' => SettingService::FREE_SHIPPING_THRESHOLD,
            'value' => '75.5',
            'desc' => 'Free shipping threshold',
        ]);

        $this->get(route('shop.payment-method.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/payment-method/PaymentMethodPage')
                ->where('shop.freeShipping.isActivated', true)
                ->where('shop.freeShipping.threshold', 75.5));
    }

    public function test_item_catalog_page_is_available(): void
    {
        $this->get(route('shop.item.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/item-list/ItemListPage'));
    }

    public function test_listed_item_page_is_available(): void
    {
        $item = Item::query()->create([
            'name' => 'Listed item',
            'name_en' => 'Listed item',
            'is_listed' => true,
        ]);

        $this->get(route('shop.item.show', ['item' => $item->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/item/ItemPage')
                ->where('item.id', $item->getKey())
                ->where('item.slug', 'listed-item'));

        $this->get("/item/{$item->getKey()}")
            ->assertNotFound();
    }

    public function test_unlisted_item_page_is_not_accessible(): void
    {
        $item = Item::query()->create([
            'name' => 'Unlisted item',
            'is_listed' => false,
            'view_count' => 12,
        ]);

        $this->get(route('shop.item.show', ['item' => $item->slug]))
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error/Shop')
                ->where('status', 404));

        $this->assertSame(12, $item->refresh()->view_count);
    }

    public function test_unlisted_item_ajax_endpoint_is_not_accessible(): void
    {
        $item = Item::query()->create([
            'name' => 'Unlisted item',
            'is_listed' => false,
        ]);

        $this->get(route('shop.ajax.item.show', ['item' => $item->slug]))
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error/Shop')
                ->where('status', 404));
    }

    public function test_item_slugs_use_english_or_transliterated_chinese_names_and_stay_unique(): void
    {
        $englishItem = Item::query()->create([
            'name' => '茉莉花茶',
            'name_en' => 'Jasmine Flower Tea',
        ]);
        $chineseItem = Item::query()->create([
            'name' => '茉莉花茶',
        ]);
        $duplicateChineseItem = Item::query()->create([
            'name' => '茉莉花茶',
        ]);

        $this->assertSame('jasmine-flower-tea', $englishItem->slug);
        $this->assertSame('mo-li-hua-cha', $chineseItem->slug);
        $this->assertSame('mo-li-hua-cha-2', $duplicateChineseItem->slug);
    }

    public function test_cart_page_is_available(): void
    {
        $this->get(route('shop.cart.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/cart/CartPage'));
    }

    public function test_checkout_page_is_available(): void
    {
        $this->get(route('shop.cart.checkout-page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/checkout/CheckoutPage'));
    }

    public function test_payment_method_page_returns_enabled_methods(): void
    {
        $this->mock(PaymentMethodService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getPaymentMethods')
                ->once()
                ->andReturn(new Collection);
        });

        $this->get(route('shop.payment-method.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/payment-method/PaymentMethodPage')
                ->has('paymentMethods', 0));
    }

    public function test_unknown_storefront_page_returns_the_shop_error_component(): void
    {
        $this->get('/page-that-does-not-exist')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error/Shop')
                ->where('status', 404));
    }
}
