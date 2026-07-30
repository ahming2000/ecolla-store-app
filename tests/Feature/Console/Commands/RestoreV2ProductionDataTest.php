<?php

namespace Tests\Feature\Console\Commands;

use App\Enums\AccessLevel;
use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\Category;
use App\Models\Image;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderedItem;
use App\Models\Origin;
use App\Models\User;
use Database\Seeders\ProdSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class RestoreV2ProductionDataTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $legacyImagesPath;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(ProdSeeder::class);

        config()->set('database.connections.legacy_v2', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('legacy_v2');

        $this->createLegacySchema();

        $this->legacyImagesPath = storage_path(
            'framework/testing/legacy-v2-'.Str::uuid(),
        );
        File::ensureDirectoryExists($this->legacyImagesPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->legacyImagesPath);
        DB::purge('legacy_v2');

        parent::tearDown();
    }

    public function test_it_restores_v2_data_and_uuid_named_public_images(): void
    {
        $this->seedLegacyData();

        $command = $this->artisan('legacy:restore-v2', [
            '--legacy-connection' => 'legacy_v2',
            '--images' => $this->legacyImagesPath,
            '--force' => true,
        ]);

        if (! $command instanceof PendingCommand) {
            $this->fail('Unable to start the legacy restoration command.');
        }

        $command
            ->expectsOutputToContain('The v2 production data was restored.')
            ->assertSuccessful()
            ->execute();

        $this->assertSame(19, Category::query()->count());
        $this->assertSame(6, Origin::query()->count());
        $this->assertSame(2, Item::query()->count());

        $chinaItem = Item::query()->findOrFail(2);
        $indonesianItem = Item::query()->findOrFail(3);

        $this->assertSame(
            '中国',
            $chinaItem->origin()->value('name'),
        );
        $this->assertNull($indonesianItem->origin_id);
        $this->assertSame(
            ['包点与点心'],
            $chinaItem->categories()->pluck('name')->all(),
        );
        $this->assertSame(
            ['火锅料'],
            $indonesianItem->categories()->pluck('name')->all(),
        );

        $this->assertDatabaseHas('item_variations', [
            'id' => 3,
            'item_id' => 2,
            'barcode' => 'TEST-BARCODE',
            'price' => 10,
            'sale_price' => null,
            'stock' => 5,
        ]);

        $images = Image::query()->with('thumbnail')->orderBy('id')->get();
        $originalImages = $images->where('id', '<=', 3);

        $this->assertCount(5, $images);
        $this->assertCount(3, $originalImages);
        $this->assertCount(2, $originalImages->whereNotNull('thumbnail_id'));
        $this->assertCount(
            2,
            $images->filter(
                fn (Image $image): bool => $image->mime_type === 'image/webp',
            ),
        );

        foreach ($originalImages as $image) {
            $this->assertMatchesRegularExpression(
                '#^/storage/[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.png$#',
                (string) $image->url,
            );
            Storage::disk('public')->assertExists(
                Str::after((string) $image->url, '/storage/'),
            );
            $this->assertStringNotContainsString(
                '/',
                Str::after((string) $image->url, '/storage/'),
            );
        }

        $order = Order::query()->findOrFail(1);

        $this->assertNull($order->receiptImage->thumbnail_id);
        $this->assertSame(DeliveryMode::DELIVERY, $order->delivery_mode);
        $this->assertSame(Status::COMPLETED, $order->status);
        $this->assertSame('Touch \'n Go', $order->paymentMethod->name);
        $this->assertSame('Customer', $order->cus_name);
        $this->assertSame(
            'Line 1, Line 2, 31900, Kampar, Perak, Malaysia',
            $order->cus_address,
        );
        $this->assertNull($order->tracking_no);
        $this->assertNull(Order::query()->findOrFail(2)->tracking_no);
        $this->assertNull(Order::query()->findOrFail(3)->tracking_no);

        $orderedItem = OrderedItem::query()->findOrFail(1);

        $this->assertSame(10.0, $orderedItem->price);
        $this->assertSame(8.0, $orderedItem->sale_price);

        $admin = User::withTrashed()->findOrFail(1);
        $deletedEditor = User::withTrashed()->findOrFail(7);

        $this->assertSame('admin', $admin->username);
        $this->assertSame(AccessLevel::ADMIN->value, $admin->access_level);
        $this->assertTrue($admin->is_enabled);
        $this->assertSame('editor', $deletedEditor->username);
        $this->assertSame(
            AccessLevel::EDITOR->value,
            $deletedEditor->access_level,
        );
        $this->assertFalse($deletedEditor->is_enabled);
        $this->assertNotNull($deletedEditor->deleted_at);

        $this->assertDatabaseHas('settings', [
            'name' => 'shipping_fee',
            'value' => '5',
        ]);
    }

    public function test_it_refuses_to_restore_into_a_non_empty_target(): void
    {
        Image::factory()->create();

        $command = $this->artisan('legacy:restore-v2', [
            '--legacy-connection' => 'legacy_v2',
            '--images' => $this->legacyImagesPath,
            '--force' => true,
        ]);

        if (! $command instanceof PendingCommand) {
            $this->fail('Unable to start the legacy restoration command.');
        }

        $command
            ->expectsOutputToContain(
                'The target images table is not empty.',
            )
            ->assertFailed()
            ->execute();
    }

    private function createLegacySchema(): void
    {
        $schema = Schema::connection('legacy_v2');

        $schema->create('categories', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('name_en')->nullable();
        });
        $schema->create('category_item', function (Blueprint $table): void {
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('category_id');
        });
        $schema->create('items', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->text('desc')->nullable();
            $table->string('origin')->nullable();
            $table->string('origin_en')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        $schema->create('item_utils', function (Blueprint $table): void {
            $table->unsignedBigInteger('item_id')->primary();
            $table->boolean('is_listed');
            $table->unsignedBigInteger('view_count');
            $table->unsignedBigInteger('sold');
        });
        $schema->create('item_images', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('item_id');
            $table->text('image')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        $schema->create('variations', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('barcode');
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->double('price');
            $table->double('weight');
            $table->text('image')->nullable();
            $table->integer('stock');
            $table->unsignedBigInteger('item_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        $schema->create('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code');
            $table->string('mode');
            $table->string('delivery_id')->nullable();
            $table->double('shipping_fee');
            $table->string('payment_method');
            $table->string('status');
            $table->string('receipt_image');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('free_shipping_note')->nullable();
        });
        $schema->create('customers', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_id')->primary();
            $table->string('name');
            $table->string('phone');
            $table->string('addressLine1');
            $table->string('addressLine2')->nullable();
            $table->string('postal_code');
            $table->string('area');
            $table->string('state');
            $table->string('country');
        });
        $schema->create('order_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('order_id');
            $table->string('name');
            $table->string('name_en');
            $table->string('barcode');
            $table->double('price');
            $table->double('discount_rate');
            $table->integer('quantity');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        $schema->create('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->string('status');
            $table->string('remember_token')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        $schema->create(
            'user_permissions',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->primary();
                $table->boolean('item_create')->default(false);
                $table->boolean('item_update')->default(false);
                $table->boolean('item_delete')->default(false);
                $table->boolean('item_list')->default(false);
                $table->boolean('order_update')->default(false);
                $table->boolean('order_delete')->default(false);
                $table->boolean('order_receipt_view')->default(false);
                $table->boolean('order_invoice_download')->default(false);
                $table->boolean('order_item_create')->default(false);
                $table->boolean('order_item_update')->default(false);
                $table->boolean('order_item_delete')->default(false);
                $table->boolean('setting_item')->default(false);
                $table->boolean('setting_order')->default(false);
                $table->boolean('setting_pagination')->default(false);
                $table->boolean('setting_account')->default(false);
            },
        );
        $schema->create('system_configs', function (Blueprint $table): void {
            $table->string('name')->primary();
            $table->string('value');
            $table->text('desc');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    private function seedLegacyData(): void
    {
        $legacy = DB::connection('legacy_v2');
        $timestamp = '2023-03-26 12:00:00';
        $imageContents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        );

        $this->assertIsString($imageContents);

        foreach ([
            'items/2/gallery.png',
            'uploads/variation.png',
            'receipts/receipt.png',
        ] as $relativePath) {
            $path = $this->legacyImagesPath.'/'.$relativePath;
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $imageContents);
        }

        $legacy->table('categories')->insert([
            ['id' => 11, 'name' => '包点与点心', 'name_en' => 'Buns'],
            [
                'id' => 16,
                'name' => '火锅配料&冷藏食品',
                'name_en' => 'Hot Pot',
            ],
        ]);
        $legacy->table('items')->insert([
            [
                'id' => 2,
                'name' => '测试商品',
                'name_en' => 'Test Item',
                'desc' => 'Description',
                'origin' => '中国',
                'origin_en' => 'China',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'id' => 3,
                'name' => '印尼商品',
                'name_en' => 'Indonesian Item',
                'desc' => null,
                'origin' => '印尼',
                'origin_en' => 'Indonesia',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
        $legacy->table('item_utils')->insert([
            [
                'item_id' => 2,
                'is_listed' => true,
                'view_count' => 20,
                'sold' => 4,
            ],
            [
                'item_id' => 3,
                'is_listed' => false,
                'view_count' => 5,
                'sold' => 1,
            ],
        ]);
        $legacy->table('category_item')->insert([
            ['item_id' => 2, 'category_id' => 11],
            ['item_id' => 3, 'category_id' => 16],
        ]);
        $legacy->table('item_images')->insert([
            'id' => 1,
            'item_id' => 2,
            'image' => 'https://management.example.com/img/items/2/gallery.png',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        $legacy->table('variations')->insert([
            'id' => 3,
            'barcode' => 'TEST-BARCODE',
            'name' => '测试规格',
            'name_en' => 'Test Variation',
            'price' => 10,
            'weight' => 0.5,
            'image' => 'https://management.example.com/storage/uploads/variation.png',
            'stock' => 5,
            'item_id' => 2,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        $legacy->table('orders')->insert([
            [
                'id' => 1,
                'code' => 'ECOLLA-1',
                'mode' => 'delivery',
                'delivery_id' => 'ECOLLA-1',
                'shipping_fee' => 3,
                'payment_method' => 'tng',
                'status' => 'completed',
                'receipt_image' => 'https://example.com/uploads/receipts/receipt.png',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'free_shipping_note' => null,
            ],
            [
                'id' => 2,
                'code' => 'ECOLLA-2',
                'mode' => 'delivery',
                'delivery_id' => 'TRACK-2',
                'shipping_fee' => 3,
                'payment_method' => 'tng',
                'status' => 'completed',
                'receipt_image' => 'https://example.com/uploads/receipts/receipt.png',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'free_shipping_note' => null,
            ],
            [
                'id' => 3,
                'code' => 'ECOLLA-3',
                'mode' => 'pickup',
                'delivery_id' => '0123456789',
                'shipping_fee' => 0,
                'payment_method' => 'tng',
                'status' => 'completed',
                'receipt_image' => 'https://example.com/uploads/receipts/receipt.png',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'free_shipping_note' => null,
            ],
        ]);
        $legacy->table('customers')->insert([
            [
                'order_id' => 1,
                'name' => 'Customer',
                'phone' => '0123456789',
                'addressLine1' => 'Line 1',
                'addressLine2' => 'Line 2',
                'postal_code' => '31900',
                'area' => 'Kampar',
                'state' => 'Perak',
                'country' => 'Malaysia',
            ],
            [
                'order_id' => 2,
                'name' => 'Customer 2',
                'phone' => '0123456788',
                'addressLine1' => 'Line 1',
                'addressLine2' => null,
                'postal_code' => '31900',
                'area' => 'Kampar',
                'state' => 'Perak',
                'country' => 'Malaysia',
            ],
        ]);
        $legacy->table('order_items')->insert([
            'id' => 1,
            'order_id' => 1,
            'name' => '测试规格',
            'name_en' => 'Test Variation',
            'barcode' => 'TEST-BARCODE',
            'price' => 10,
            'discount_rate' => 0.8,
            'quantity' => 2,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        $legacy->table('users')->insert([
            [
                'id' => 1,
                'email' => 'admin@newrainbowmarket.com',
                'password' => '$2y$10$example',
                'role' => 'admin',
                'status' => 'enabled',
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'id' => 7,
                'email' => 'editor@newrainbowmarket.com',
                'password' => '$2y$10$example',
                'role' => 'employee',
                'status' => 'deleted',
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
        $legacy->table('user_permissions')->insert([
            'user_id' => 7,
            'item_update' => true,
        ]);
        $legacy->table('system_configs')->insert([
            'name' => 'clt_o_shippingFeeKampar',
            'value' => '5',
            'desc' => 'Shipping fee',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
