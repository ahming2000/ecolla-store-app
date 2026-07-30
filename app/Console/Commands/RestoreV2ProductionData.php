<?php

namespace App\Console\Commands;

use App\Enums\AccessLevel;
use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use stdClass;
use Throwable;
use UnexpectedValueException;

#[Signature('legacy:restore-v2
    {--legacy-connection= : Use an already configured legacy database connection}
    {--host=127.0.0.1 : Legacy MariaDB host}
    {--port=33077 : Legacy MariaDB port}
    {--database=laravel : Legacy MariaDB database}
    {--username=root : Legacy MariaDB username}
    {--password= : Legacy MariaDB password}
    {--images=storage/tmp/Images : Legacy image backup directory}
    {--skip-files : Require already restored UUID files instead of copying the image backup}
    {--force : Run without an interactive confirmation}')]
#[Description('Restore the v2 production database and public images into the v4 schema')]
class RestoreV2ProductionData extends Command
{
    private const LEGACY_CONNECTION = 'legacy_v2';

    private const LEGACY_USERNAME_SUFFIX = '@newrainbowmarket.com';

    public function __construct(
        private readonly ImageService $imageService,
    ) {
        parent::__construct();
    }

    /**
     * @var array<string, string>
     */
    private const CATEGORY_NAME_MAP = [
        '火锅配料&冷藏食品' => '火锅料',
        '无' => '未分类',
    ];

    /**
     * @var array<string, string>
     */
    private const PAYMENT_METHOD_NAME_MAP = [
        'tng' => 'Touch \'n Go',
        'boost' => 'Boost Pay',
        'online-banking' => 'Online Banking',
        'maybank-qr-pay' => 'Maybank QR Pay',
        'quin-pay' => 'Quin Pay',
    ];

    /**
     * @var array<string, string>
     */
    private const SETTING_NAME_MAP = [
        'clt_o_shippingFeeKampar' => 'shipping_fee',
        'clt_o_shipping_discount' => 'freeShipping_isActivated',
        'clt_o_shipping_discount_threshold' => 'freeShipping_threshold',
        'clt_o_shipping_discount_desc' => 'freeShipping_desc',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')
            && ! $this->confirm(
                'Restore the v2 production data into the current database?',
            )
        ) {
            $this->components->info('The restoration was cancelled.');

            return self::SUCCESS;
        }

        try {
            $legacyConnection = $this->legacyConnection();
            $legacyConnection->getPdo();
            $this->assertTargetIsReady();

            $legacyItems = $this->legacyItems($legacyConnection);
            $legacyItemImages = $this->legacyItemImages($legacyConnection);
            $legacyVariations = $this->legacyVariations($legacyConnection);
            $legacyOrders = $this->legacyOrders($legacyConnection);
            $legacyOrderedItems = $this->legacyOrderedItems($legacyConnection);
            $legacyUsers = $this->legacyUsers($legacyConnection);
            $legacyCategories = $this->legacyCategories($legacyConnection);
            $legacySettings = $legacyConnection
                ->table('system_configs')
                ->orderBy('name')
                ->get();

            [
                'files' => $legacyFiles,
                'item_image_keys' => $itemImageKeys,
                'variation_image_keys' => $variationImageKeys,
                'receipt_image_keys' => $receiptImageKeys,
            ] = $this->prepareLegacyFiles(
                $legacyItemImages,
                $legacyVariations,
                $legacyOrders,
            );

            $targetCategoryIds = DB::table('categories')
                ->pluck('id', 'name');
            $targetOriginIds = DB::table('origins')
                ->pluck('id', 'name');
            $targetPaymentMethodIds = DB::table('payment_methods')
                ->pluck('id', 'name');
            $unmappedOriginCounts = [];

            DB::connection()->transaction(function () use (
                $legacyItems,
                $legacyItemImages,
                $legacyVariations,
                $legacyOrders,
                $legacyOrderedItems,
                $legacyUsers,
                $legacyCategories,
                $legacySettings,
                &$legacyFiles,
                $itemImageKeys,
                $variationImageKeys,
                $receiptImageKeys,
                $targetCategoryIds,
                $targetOriginIds,
                $targetPaymentMethodIds,
                &$unmappedOriginCounts,
            ): void {
                $this->restoreSettings($legacySettings);
                $this->restoreUsers($legacyUsers);
                $this->restoreItems(
                    $legacyItems,
                    $targetOriginIds,
                    $unmappedOriginCounts,
                );
                $this->restoreImages($legacyFiles);
                $this->synchronizeSequences(['images']);
                $this->restoreImageThumbnails($legacyFiles);
                $this->restoreItemCategories(
                    $legacyCategories,
                    $targetCategoryIds,
                );
                $this->restoreItemImages(
                    $legacyItemImages,
                    $itemImageKeys,
                    $legacyFiles,
                );
                $this->restoreVariations(
                    $legacyVariations,
                    $variationImageKeys,
                    $legacyFiles,
                );
                $this->restoreOrders(
                    $legacyOrders,
                    $receiptImageKeys,
                    $legacyFiles,
                    $targetPaymentMethodIds,
                );
                $this->restoreOrderedItems($legacyOrderedItems);

                $this->synchronizeSequences([
                    'users',
                    'images',
                    'items',
                    'item_variations',
                    'orders',
                    'ordered_items',
                ]);
            });

            $this->newLine();
            $this->components->info('The v2 production data was restored.');
            $this->table(
                ['Data', 'Restored'],
                [
                    ['Users', $legacyUsers->count()],
                    ['Items', $legacyItems->count()],
                    ['Item variations', $legacyVariations->count()],
                    ['Item gallery images', $legacyItemImages->count()],
                    ['Public image files', count($legacyFiles)],
                    ['Orders', $legacyOrders->count()],
                    ['Ordered items', $legacyOrderedItems->count()],
                ],
            );

            if ($unmappedOriginCounts !== []) {
                $this->components->warn(
                    'The following legacy origins are not in the v4 seeder and were left unassigned: '
                    .collect($unmappedOriginCounts)
                        ->map(
                            fn (int $count, string $origin): string => "{$origin} ({$count})",
                        )
                        ->join(', '),
                );
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function legacyConnection(): Connection
    {
        $configuredConnection = trim(
            (string) $this->option('legacy-connection'),
        );

        if ($configuredConnection !== '') {
            return DB::connection($configuredConnection);
        }

        config()->set('database.connections.'.self::LEGACY_CONNECTION, [
            'driver' => 'mysql',
            'host' => (string) $this->option('host'),
            'port' => (string) $this->option('port'),
            'database' => (string) $this->option('database'),
            'username' => (string) $this->option('username'),
            'password' => (string) $this->option('password'),
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'timezone' => '+00:00',
        ]);

        DB::purge(self::LEGACY_CONNECTION);

        return DB::connection(self::LEGACY_CONNECTION);
    }

    private function assertTargetIsReady(): void
    {
        $tablesThatMustBeEmpty = [
            'images',
            'items',
            'item_variations',
            'item_images',
            'item_categories',
            'orders',
            'ordered_items',
        ];

        foreach ($tablesThatMustBeEmpty as $table) {
            if (DB::table($table)->exists()) {
                throw new RuntimeException(
                    "The target {$table} table is not empty. Run the restoration against a freshly migrated and production-seeded database.",
                );
            }
        }

        $requiredSeedCounts = [
            'categories' => 19,
            'origins' => 6,
            'payment_methods' => 5,
        ];

        foreach ($requiredSeedCounts as $table => $expectedCount) {
            $actualCount = DB::table($table)->count();

            if ($actualCount !== $expectedCount) {
                throw new RuntimeException(
                    "The target {$table} table must contain the v4 production seed data ({$expectedCount} rows found: {$actualCount}).",
                );
            }
        }

        $targetUsers = DB::table('users')->get(['username']);

        if ($targetUsers->count() !== 1
            || $targetUsers->first()?->username !== 'admin'
        ) {
            throw new RuntimeException(
                'The target users table must contain only the production-seeded admin account.',
            );
        }
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function legacyItems(Connection $connection): Collection
    {
        return $connection
            ->table('items')
            ->join('item_utils', 'item_utils.item_id', '=', 'items.id')
            ->select([
                'items.id',
                'items.name',
                'items.name_en',
                'items.desc',
                'items.origin',
                'items.origin_en',
                'items.created_at',
                'items.updated_at',
                'item_utils.is_listed',
                'item_utils.view_count',
                'item_utils.sold',
            ])
            ->orderBy('items.id')
            ->get();
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function legacyItemImages(Connection $connection): Collection
    {
        return $connection
            ->table('item_images')
            ->select([
                'id',
                'item_id',
                'image',
                'created_at',
                'updated_at',
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function legacyVariations(Connection $connection): Collection
    {
        return $connection
            ->table('variations')
            ->select([
                'id',
                'barcode',
                'name',
                'name_en',
                'price',
                'weight',
                'image',
                'stock',
                'item_id',
                'created_at',
                'updated_at',
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function legacyOrders(Connection $connection): Collection
    {
        return $connection
            ->table('orders')
            ->leftJoin('customers', 'customers.order_id', '=', 'orders.id')
            ->select([
                'orders.id',
                'orders.code',
                'orders.mode',
                'orders.shipping_fee',
                'orders.payment_method',
                'orders.status',
                'orders.receipt_image',
                'orders.created_at',
                'orders.updated_at',
                'orders.free_shipping_note',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'customers.addressLine1 as customer_address_line_1',
                'customers.addressLine2 as customer_address_line_2',
                'customers.postal_code as customer_postal_code',
                'customers.area as customer_area',
                'customers.state as customer_state',
                'customers.country as customer_country',
            ])
            ->orderBy('orders.id')
            ->get();
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function legacyOrderedItems(Connection $connection): Collection
    {
        return $connection
            ->table('order_items')
            ->select([
                'id',
                'order_id',
                'name',
                'name_en',
                'barcode',
                'price',
                'discount_rate',
                'quantity',
                'created_at',
                'updated_at',
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function legacyUsers(Connection $connection): Collection
    {
        return $connection
            ->table('users')
            ->leftJoin(
                'user_permissions',
                'user_permissions.user_id',
                '=',
                'users.id',
            )
            ->select([
                'users.id',
                'users.email',
                'users.password',
                'users.role',
                'users.status',
                'users.remember_token',
                'users.created_at',
                'users.updated_at',
                'user_permissions.item_create',
                'user_permissions.item_update',
                'user_permissions.item_delete',
                'user_permissions.item_list',
                'user_permissions.order_update',
                'user_permissions.order_delete',
                'user_permissions.order_receipt_view',
                'user_permissions.order_invoice_download',
                'user_permissions.order_item_create',
                'user_permissions.order_item_update',
                'user_permissions.order_item_delete',
                'user_permissions.setting_item',
                'user_permissions.setting_order',
                'user_permissions.setting_pagination',
                'user_permissions.setting_account',
            ])
            ->orderBy('users.id')
            ->get();
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function legacyCategories(Connection $connection): Collection
    {
        return $connection
            ->table('category_item')
            ->join(
                'categories',
                'categories.id',
                '=',
                'category_item.category_id',
            )
            ->select([
                'category_item.item_id',
                'categories.name as category_name',
            ])
            ->orderBy('category_item.item_id')
            ->orderBy('category_item.category_id')
            ->get();
    }

    /**
     * @param  Collection<int, stdClass>  $legacyItemImages
     * @param  Collection<int, stdClass>  $legacyVariations
     * @param  Collection<int, stdClass>  $legacyOrders
     * @return array{
     *     files: array<string, array{
     *         source_key: string,
     *         storage_name: string,
     *         name: string,
     *         mime_type: string,
     *         size: int,
     *         created_at: mixed,
     *         updated_at: mixed,
     *         is_receipt: bool,
     *         id?: int
     *     }>,
     *     item_image_keys: array<int, string>,
     *     variation_image_keys: array<int, string>,
     *     receipt_image_keys: array<int, string>
     * }
     */
    private function prepareLegacyFiles(
        Collection $legacyItemImages,
        Collection $legacyVariations,
        Collection $legacyOrders,
    ): array {
        $legacyFiles = [];
        $itemImageKeys = [];
        $variationImageKeys = [];
        $receiptImageKeys = [];

        foreach ($legacyItemImages as $legacyItemImage) {
            $sourceKey = $this->registerLegacyFile(
                $legacyFiles,
                (string) $legacyItemImage->image,
                'item',
                $legacyItemImage->created_at,
                $legacyItemImage->updated_at,
            );
            $itemImageKeys[(int) $legacyItemImage->id] = $sourceKey;
        }

        foreach ($legacyVariations as $legacyVariation) {
            if ($legacyVariation->image === null
                || (string) $legacyVariation->image === ''
            ) {
                continue;
            }

            $sourceKey = $this->registerLegacyFile(
                $legacyFiles,
                (string) $legacyVariation->image,
                'item',
                $legacyVariation->created_at,
                $legacyVariation->updated_at,
            );
            $variationImageKeys[(int) $legacyVariation->id] = $sourceKey;
        }

        foreach ($legacyOrders as $legacyOrder) {
            $sourceKey = $this->registerLegacyFile(
                $legacyFiles,
                (string) $legacyOrder->receipt_image,
                'receipt',
                $legacyOrder->created_at,
                $legacyOrder->updated_at,
            );
            $receiptImageKeys[(int) $legacyOrder->id] = $sourceKey;
        }

        ksort($legacyFiles);

        $imagesRoot = $this->absoluteImagesRoot();
        $skipFiles = (bool) $this->option('skip-files');
        $progressBar = $this->output->createProgressBar(count($legacyFiles));
        $progressBar->setMessage(
            $skipFiles
                ? 'Validating restored public images'
                : 'Restoring public images',
        );
        $progressBar->start();

        foreach ($legacyFiles as &$legacyFile) {
            $storageName = $this->storageName($legacyFile['source_key']);
            $metadataPath = $skipFiles
                ? Storage::disk('public')->path($storageName)
                : $imagesRoot.DIRECTORY_SEPARATOR.$legacyFile['source_key'];

            if (! File::isFile($metadataPath)) {
                throw new RuntimeException(
                    "The required legacy image is missing: {$metadataPath}",
                );
            }

            if (! $skipFiles) {
                $sourceStream = fopen($metadataPath, 'rb');

                if ($sourceStream === false) {
                    throw new RuntimeException(
                        "Unable to open the legacy image: {$metadataPath}",
                    );
                }

                try {
                    if (! Storage::disk('public')->put(
                        $storageName,
                        $sourceStream,
                    )) {
                        throw new RuntimeException(
                            "Unable to restore the public image: {$storageName}",
                        );
                    }
                } finally {
                    fclose($sourceStream);
                }
            }

            $mimeType = File::mimeType($metadataPath);
            $size = File::size($metadataPath);

            if (! is_string($mimeType)
                || ! Str::startsWith($mimeType, 'image/')
            ) {
                throw new RuntimeException(
                    "The legacy file is not a recognized image: {$metadataPath}",
                );
            }

            $legacyFile['storage_name'] = $storageName;
            $legacyFile['name'] = basename($legacyFile['source_key']);
            $legacyFile['mime_type'] = $mimeType;
            $legacyFile['size'] = $size;
            $progressBar->advance();
        }
        unset($legacyFile);

        $progressBar->finish();
        $this->newLine(2);

        return [
            'files' => $legacyFiles,
            'item_image_keys' => $itemImageKeys,
            'variation_image_keys' => $variationImageKeys,
            'receipt_image_keys' => $receiptImageKeys,
        ];
    }

    /**
     * @param  array<string, array{
     *     source_key: string,
     *     storage_name: string,
     *     name: string,
     *     mime_type: string,
     *     size: int,
     *     created_at: mixed,
     *     updated_at: mixed,
     *     is_receipt: bool,
     *     id?: int
     * }>  $legacyFiles
     */
    private function registerLegacyFile(
        array &$legacyFiles,
        string $legacyUrl,
        string $type,
        mixed $createdAt,
        mixed $updatedAt,
    ): string {
        $sourceKey = $this->legacySourceKey($legacyUrl, $type);

        $legacyFiles[$sourceKey] ??= [
            'source_key' => $sourceKey,
            'storage_name' => '',
            'name' => '',
            'mime_type' => '',
            'size' => 0,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'is_receipt' => $type === 'receipt',
        ];

        return $sourceKey;
    }

    private function legacySourceKey(string $legacyUrl, string $type): string
    {
        $urlPath = parse_url($legacyUrl, PHP_URL_PATH);

        if (! is_string($urlPath)) {
            throw new UnexpectedValueException(
                "Unable to parse the legacy image URL: {$legacyUrl}",
            );
        }

        $decodedPath = rawurldecode($urlPath);
        $filename = basename($decodedPath);

        if ($filename === '' || $filename === '.' || $filename === '..') {
            throw new UnexpectedValueException(
                "Unable to determine the legacy image filename: {$legacyUrl}",
            );
        }

        if ($type === 'receipt') {
            return 'receipts/'.$filename;
        }

        if (Str::contains($decodedPath, '/storage/uploads/')) {
            return 'uploads/'.$filename;
        }

        if (preg_match(
            '#/img/items/([0-9]+)/[^/]+$#',
            $decodedPath,
            $matches,
        ) === 1) {
            return "items/{$matches[1]}/{$filename}";
        }

        throw new UnexpectedValueException(
            "Unsupported legacy image URL: {$legacyUrl}",
        );
    }

    private function storageName(string $sourceKey): string
    {
        $extension = Str::lower(pathinfo($sourceKey, PATHINFO_EXTENSION));

        if ($extension === ''
            || preg_match('/^[a-z0-9]{1,10}$/', $extension) !== 1
        ) {
            throw new UnexpectedValueException(
                "Unsupported legacy image extension: {$sourceKey}",
            );
        }

        $uuid = Uuid::uuid5(
            Uuid::NAMESPACE_URL,
            'https://ecolla.local/legacy-v2/'.$sourceKey,
        );

        return "{$uuid->toString()}.{$extension}";
    }

    private function absoluteImagesRoot(): string
    {
        $configuredPath = trim((string) $this->option('images'));

        if ($configuredPath === '') {
            throw new RuntimeException(
                'The legacy image backup directory is required.',
            );
        }

        if (Str::startsWith($configuredPath, DIRECTORY_SEPARATOR)) {
            return $configuredPath;
        }

        return base_path($configuredPath);
    }

    /**
     * @param  Collection<int, stdClass>  $legacySettings
     */
    private function restoreSettings(Collection $legacySettings): void
    {
        foreach ($legacySettings as $legacySetting) {
            $targetName = self::SETTING_NAME_MAP[$legacySetting->name] ?? null;

            if ($targetName === null) {
                continue;
            }

            DB::table('settings')
                ->where('name', $targetName)
                ->update([
                    'value' => (string) $legacySetting->value,
                    'updated_at' => $legacySetting->updated_at,
                ]);
        }
    }

    /**
     * @param  Collection<int, stdClass>  $legacyUsers
     */
    private function restoreUsers(Collection $legacyUsers): void
    {
        DB::table('sessions')->delete();
        DB::table('users')->delete();

        $rows = $legacyUsers
            ->map(fn (stdClass $legacyUser): array => [
                'id' => (int) $legacyUser->id,
                'username' => Str::chopEnd(
                    Str::lower(trim((string) $legacyUser->email)),
                    self::LEGACY_USERNAME_SUFFIX,
                ),
                'password' => (string) $legacyUser->password,
                'lang' => 'zh',
                'timezone' => 'Asia/Kuala_Lumpur',
                'access_level' => $this->accessLevel($legacyUser)->value,
                'is_enabled' => $legacyUser->status === 'enabled',
                'remember_token' => $legacyUser->remember_token,
                'created_at' => $legacyUser->created_at,
                'updated_at' => $legacyUser->updated_at,
                'deleted_at' => $legacyUser->status === 'deleted'
                    ? $legacyUser->updated_at
                    : null,
            ])
            ->values()
            ->all();

        $this->insertInChunks('users', array_values($rows));
    }

    private function accessLevel(stdClass $legacyUser): AccessLevel
    {
        if ($legacyUser->role === 'admin') {
            return AccessLevel::ADMIN;
        }

        if ($this->hasLegacyPermission($legacyUser, [
            'item_delete',
            'item_list',
            'order_delete',
            'setting_item',
            'setting_order',
            'setting_account',
        ])) {
            return AccessLevel::SUPERVISOR;
        }

        if ($this->hasLegacyPermission($legacyUser, [
            'item_create',
            'item_update',
            'order_update',
            'order_receipt_view',
            'order_invoice_download',
            'order_item_create',
            'order_item_update',
            'order_item_delete',
            'setting_pagination',
        ])) {
            return AccessLevel::EDITOR;
        }

        return AccessLevel::VIEWER;
    }

    /**
     * @param  list<string>  $permissions
     */
    private function hasLegacyPermission(
        stdClass $legacyUser,
        array $permissions,
    ): bool {
        foreach ($permissions as $permission) {
            if ((bool) $legacyUser->{$permission}) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Collection<int, stdClass>  $legacyItems
     * @param  Collection<string, int>  $targetOriginIds
     * @param  array<string, int>  $unmappedOriginCounts
     */
    private function restoreItems(
        Collection $legacyItems,
        Collection $targetOriginIds,
        array &$unmappedOriginCounts,
    ): void {
        $usedSlugs = [];
        $rows = [];

        foreach ($legacyItems as $legacyItem) {
            $originName = $this->normalizedOriginName($legacyItem);
            $originId = $originName === null
                ? null
                : $targetOriginIds->get($originName);

            if ($originName !== null && $originId === null) {
                $unmappedOriginCounts[$originName] = (
                    $unmappedOriginCounts[$originName] ?? 0
                ) + 1;
            }

            $rows[] = [
                'id' => (int) $legacyItem->id,
                'name' => (string) $legacyItem->name,
                'name_en' => $legacyItem->name_en,
                'slug' => $this->uniqueSlug($legacyItem, $usedSlugs),
                'desc' => $legacyItem->desc,
                'is_listed' => (bool) $legacyItem->is_listed,
                'view_count' => (int) $legacyItem->view_count,
                'sold_count' => (int) $legacyItem->sold,
                'origin_id' => $originId,
                'created_at' => $legacyItem->created_at,
                'updated_at' => $legacyItem->updated_at,
                'deleted_at' => null,
            ];
        }

        $this->insertInChunks('items', $rows);
    }

    private function normalizedOriginName(stdClass $legacyItem): ?string
    {
        $originName = trim((string) $legacyItem->origin);

        if ($originName === '') {
            return null;
        }

        return match ($originName) {
            '中国·' => '中国',
            default => $originName,
        };
    }

    /**
     * @param  array<string, true>  $usedSlugs
     */
    private function uniqueSlug(
        stdClass $legacyItem,
        array &$usedSlugs,
    ): string {
        $englishName = trim((string) $legacyItem->name_en);
        $slugSource = $englishName !== ''
            ? $englishName
            : (string) $legacyItem->name;
        $language = $englishName !== '' ? 'en' : 'zh';
        $baseSlug = Str::limit(
            Str::slug($slugSource, language: $language) ?: 'item',
            240,
            '',
        );
        $slug = $baseSlug;
        $suffix = 2;

        while (isset($usedSlugs[$slug])) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $usedSlugs[$slug] = true;

        return $slug;
    }

    /**
     * @param  array<string, array{
     *     source_key: string,
     *     storage_name: string,
     *     name: string,
     *     mime_type: string,
     *     size: int,
     *     created_at: mixed,
     *     updated_at: mixed,
     *     is_receipt: bool,
     *     id?: int
     * }>  $legacyFiles
     */
    private function restoreImages(array &$legacyFiles): void
    {
        $rows = [];
        $imageId = 1;

        foreach ($legacyFiles as &$legacyFile) {
            $legacyFile['id'] = $imageId;
            $rows[] = [
                'id' => $imageId,
                'name' => $legacyFile['name'],
                'mime_type' => $legacyFile['mime_type'],
                'size' => $legacyFile['size'],
                'url' => '/storage/'.$legacyFile['storage_name'],
                'data_uri' => null,
                'created_at' => $legacyFile['created_at'],
                'updated_at' => $legacyFile['updated_at'],
            ];
            $imageId++;
        }
        unset($legacyFile);

        $this->insertInChunks('images', $rows);
    }

    /**
     * @param  array<string, array{is_receipt: bool, id?: int}>  $legacyFiles
     */
    private function restoreImageThumbnails(array $legacyFiles): void
    {
        $catalogFiles = collect($legacyFiles)
            ->reject(
                fn (array $legacyFile): bool => $legacyFile['is_receipt'],
            );
        $progressBar = $this->output->createProgressBar(
            $catalogFiles->count(),
        );
        $progressBar->setMessage('Generating restored image thumbnails');
        $progressBar->start();

        foreach ($catalogFiles as $legacyFile) {
            $imageId = $legacyFile['id'] ?? null;

            if (! is_int($imageId)) {
                throw new RuntimeException(
                    'A restored catalog image is missing its target ID.',
                );
            }

            $this->imageService->generateThumbnail(
                Image::query()->findOrFail($imageId),
            );
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * @param  Collection<int, stdClass>  $legacyCategories
     * @param  Collection<string, int>  $targetCategoryIds
     */
    private function restoreItemCategories(
        Collection $legacyCategories,
        Collection $targetCategoryIds,
    ): void {
        $rows = [];

        foreach ($legacyCategories as $legacyCategory) {
            $targetName = self::CATEGORY_NAME_MAP[
                $legacyCategory->category_name
            ] ?? $legacyCategory->category_name;
            $targetCategoryId = $targetCategoryIds->get($targetName);

            if ($targetCategoryId === null) {
                throw new UnexpectedValueException(
                    "Unable to map the legacy category: {$legacyCategory->category_name}",
                );
            }

            $key = "{$legacyCategory->item_id}:{$targetCategoryId}";
            $rows[$key] = [
                'item_id' => (int) $legacyCategory->item_id,
                'category_id' => (int) $targetCategoryId,
            ];
        }

        $this->insertInChunks('item_categories', array_values($rows));
    }

    /**
     * @param  Collection<int, stdClass>  $legacyItemImages
     * @param  array<int, string>  $itemImageKeys
     * @param  array<string, array{id?: int}>  $legacyFiles
     */
    private function restoreItemImages(
        Collection $legacyItemImages,
        array $itemImageKeys,
        array $legacyFiles,
    ): void {
        $rows = $legacyItemImages
            ->map(function (stdClass $legacyItemImage) use (
                $itemImageKeys,
                $legacyFiles,
            ): array {
                $sourceKey = $itemImageKeys[(int) $legacyItemImage->id];

                return [
                    'item_id' => (int) $legacyItemImage->item_id,
                    'image_id' => $this->legacyImageId(
                        $legacyFiles,
                        $sourceKey,
                    ),
                ];
            })
            ->values()
            ->all();

        $this->insertInChunks('item_images', array_values($rows));
    }

    /**
     * @param  Collection<int, stdClass>  $legacyVariations
     * @param  array<int, string>  $variationImageKeys
     * @param  array<string, array{id?: int}>  $legacyFiles
     */
    private function restoreVariations(
        Collection $legacyVariations,
        array $variationImageKeys,
        array $legacyFiles,
    ): void {
        $rows = $legacyVariations
            ->map(function (stdClass $legacyVariation) use (
                $variationImageKeys,
                $legacyFiles,
            ): array {
                $sourceKey = $variationImageKeys[
                    (int) $legacyVariation->id
                ] ?? null;

                return [
                    'id' => (int) $legacyVariation->id,
                    'barcode' => (string) $legacyVariation->barcode,
                    'name' => (string) $legacyVariation->name,
                    'name_en' => $legacyVariation->name_en,
                    'price' => round((float) $legacyVariation->price, 2),
                    'sale_price' => null,
                    'weight' => round((float) $legacyVariation->weight, 2),
                    'stock' => (int) $legacyVariation->stock,
                    'image_id' => $sourceKey === null
                        ? null
                        : $this->legacyImageId(
                            $legacyFiles,
                            $sourceKey,
                        ),
                    'item_id' => (int) $legacyVariation->item_id,
                    'created_at' => $legacyVariation->created_at,
                    'updated_at' => $legacyVariation->updated_at,
                    'deleted_at' => null,
                ];
            })
            ->values()
            ->all();

        $this->insertInChunks('item_variations', array_values($rows));
    }

    /**
     * @param  Collection<int, stdClass>  $legacyOrders
     * @param  array<int, string>  $receiptImageKeys
     * @param  array<string, array{id?: int}>  $legacyFiles
     * @param  Collection<string, int>  $targetPaymentMethodIds
     */
    private function restoreOrders(
        Collection $legacyOrders,
        array $receiptImageKeys,
        array $legacyFiles,
        Collection $targetPaymentMethodIds,
    ): void {
        $rows = $legacyOrders
            ->map(function (stdClass $legacyOrder) use (
                $receiptImageKeys,
                $legacyFiles,
                $targetPaymentMethodIds,
            ): array {
                $paymentMethodName = self::PAYMENT_METHOD_NAME_MAP[
                    $legacyOrder->payment_method
                ] ?? null;
                $paymentMethodId = $paymentMethodName === null
                    ? null
                    : $targetPaymentMethodIds->get($paymentMethodName);

                if ($paymentMethodId === null) {
                    throw new UnexpectedValueException(
                        "Unable to map the legacy payment method: {$legacyOrder->payment_method}",
                    );
                }

                $receiptKey = $receiptImageKeys[(int) $legacyOrder->id];

                return [
                    'id' => (int) $legacyOrder->id,
                    'reference_num' => (string) $legacyOrder->code,
                    'delivery_mode' => $this->deliveryMode(
                        (string) $legacyOrder->mode,
                    )->value,
                    'status' => $this->status(
                        (string) $legacyOrder->status,
                    )->value,
                    'payment_method_id' => (int) $paymentMethodId,
                    'tracking_no' => null,
                    'shipping_fee' => round(
                        (float) $legacyOrder->shipping_fee,
                        2,
                    ),
                    'receipt_image_id' => $this->legacyImageId(
                        $legacyFiles,
                        $receiptKey,
                    ),
                    'note' => $legacyOrder->free_shipping_note,
                    'cus_name' => $legacyOrder->customer_name,
                    'cus_phone' => (string) (
                        $legacyOrder->customer_phone ?? ''
                    ),
                    'cus_address' => $this->customerAddress($legacyOrder),
                    'created_at' => $legacyOrder->created_at,
                    'updated_at' => $legacyOrder->updated_at,
                    'deleted_at' => null,
                ];
            })
            ->values()
            ->all();

        $this->insertInChunks('orders', array_values($rows));
    }

    private function deliveryMode(string $legacyMode): DeliveryMode
    {
        return match ($legacyMode) {
            'delivery' => DeliveryMode::DELIVERY,
            'pickup' => DeliveryMode::SELF_PICKUP,
            default => throw new UnexpectedValueException(
                "Unsupported legacy delivery mode: {$legacyMode}",
            ),
        };
    }

    private function status(string $legacyStatus): Status
    {
        return match ($legacyStatus) {
            'pending' => Status::PENDING,
            'ready' => Status::READY,
            'completed' => Status::COMPLETED,
            'refunded' => Status::REFUNDED,
            'canceled', 'cancelled' => Status::CANCELED,
            default => throw new UnexpectedValueException(
                "Unsupported legacy order status: {$legacyStatus}",
            ),
        };
    }

    private function customerAddress(stdClass $legacyOrder): ?string
    {
        $address = collect([
            $legacyOrder->customer_address_line_1,
            $legacyOrder->customer_address_line_2,
            $legacyOrder->customer_postal_code,
            $legacyOrder->customer_area,
            $legacyOrder->customer_state,
            $legacyOrder->customer_country,
        ])
            ->filter(
                fn (mixed $part): bool => is_string($part)
                    && trim($part) !== '',
            )
            ->map(fn (string $part): string => trim($part))
            ->join(', ');

        return $address === ''
            ? null
            : Str::limit($address, 255, '');
    }

    /**
     * @param  Collection<int, stdClass>  $legacyOrderedItems
     */
    private function restoreOrderedItems(
        Collection $legacyOrderedItems,
    ): void {
        $rows = $legacyOrderedItems
            ->map(function (stdClass $legacyOrderedItem): array {
                $price = round((float) $legacyOrderedItem->price, 2);
                $discountRate = (float) $legacyOrderedItem->discount_rate;

                return [
                    'id' => (int) $legacyOrderedItem->id,
                    'name' => (string) $legacyOrderedItem->name,
                    'name_en' => (string) $legacyOrderedItem->name_en,
                    'barcode' => (string) $legacyOrderedItem->barcode,
                    'price' => $price,
                    'sale_price' => $discountRate < 1
                        ? round($price * $discountRate, 2)
                        : null,
                    'quantity' => (int) $legacyOrderedItem->quantity,
                    'order_id' => (int) $legacyOrderedItem->order_id,
                    'created_at' => $legacyOrderedItem->created_at,
                    'updated_at' => $legacyOrderedItem->updated_at,
                    'deleted_at' => null,
                ];
            })
            ->values()
            ->all();

        $this->insertInChunks('ordered_items', array_values($rows));
    }

    /**
     * @param  array<string, array{id?: int}>  $legacyFiles
     */
    private function legacyImageId(
        array $legacyFiles,
        string $sourceKey,
    ): int {
        $imageId = $legacyFiles[$sourceKey]['id'] ?? null;

        if (! is_int($imageId)) {
            throw new RuntimeException(
                "No restored image ID exists for: {$sourceKey}",
            );
        }

        return $imageId;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function insertInChunks(string $table, array $rows): void
    {
        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    /**
     * @param  list<string>  $tables
     */
    private function synchronizeSequences(array $tables): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($tables as $table) {
            DB::statement(
                "SELECT setval(
                    pg_get_serial_sequence('{$table}', 'id'),
                    COALESCE(MAX(id), 1),
                    MAX(id) IS NOT NULL
                ) FROM {$table}",
            );
        }
    }
}
