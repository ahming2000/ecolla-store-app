<?php

namespace Tests\Feature;

use App\Enums\DeliveryMode;
use App\Enums\ImageUploadOption;
use App\Enums\Status;
use App\Models\Image;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImageThumbnailTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_catalog_upload_preserves_original_format_and_creates_a_webp_thumbnail(): void
    {
        Storage::fake('public');

        $response = $this->postJson(route('image.upload'), [
            'image' => UploadedFile::fake()->image(
                'catalog-image.png',
                1200,
                800,
            ),
            'option' => ImageUploadOption::ORIGINAL->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('mime_type', 'image/png')
            ->assertJsonPath('thumbnail.mime_type', 'image/webp');

        $image = Image::query()
            ->with('thumbnail')
            ->findOrFail($response->json('id'));
        $thumbnail = $image->thumbnail;

        $this->assertNotNull($thumbnail);
        $this->assertSame('image/png', $image->mime_type);
        $this->assertStringEndsWith('.png', (string) $image->url);
        $this->assertSame('image/webp', $thumbnail->mime_type);
        $this->assertStringEndsWith('.webp', (string) $thumbnail->url);
        $this->assertStringStartsWith(
            '/storage/thumbnails/',
            (string) $thumbnail->url,
        );

        $thumbnailPath = Storage::disk('public')->path(
            Str::after((string) $thumbnail->url, '/storage/'),
        );
        $dimensions = getimagesize($thumbnailPath);

        $this->assertIsArray($dimensions);
        $this->assertLessThanOrEqual(480, max($dimensions[0], $dimensions[1]));
    }

    public function test_receipt_upload_does_not_create_a_thumbnail(): void
    {
        Storage::fake('public');

        $response = $this->postJson(route('image.upload'), [
            'image' => UploadedFile::fake()->image('receipt.jpg', 600, 800),
            'option' => ImageUploadOption::ORIGINAL->value,
            'with_thumbnail' => false,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('mime_type', 'image/jpeg')
            ->assertJsonPath('thumbnail_id', null);

        $this->assertSame(1, Image::query()->count());
        Storage::disk('public')->assertCount('/', 1);
    }

    public function test_deleting_an_unreferenced_image_also_deletes_its_thumbnail(): void
    {
        Storage::fake('public');
        $service = app(ImageService::class);
        $image = $service->upload(
            UploadedFile::fake()->image('removable.jpg'),
            ImageUploadOption::ORIGINAL,
        );
        $thumbnail = $image->thumbnail;

        $this->assertNotNull($thumbnail);

        $service->deleteIfUnreferenced($image);

        $this->assertModelMissing($image);
        $this->assertModelMissing($thumbnail);
        Storage::disk('public')->assertDirectoryEmpty('/');
    }

    public function test_thumbnail_command_skips_receipt_images(): void
    {
        Storage::fake('public');
        $service = app(ImageService::class);
        $catalogImage = $service->upload(
            UploadedFile::fake()->image('catalog.jpg'),
            ImageUploadOption::ORIGINAL,
            withThumbnail: false,
        );
        $receiptImage = $service->upload(
            UploadedFile::fake()->image('receipt.jpg'),
            ImageUploadOption::ORIGINAL,
            withThumbnail: false,
        );
        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Test payment',
            'icon_img_path' => '/images/test-payment.png',
            'qr_code_img_path' => '/images/test-payment-qr.png',
            'is_enabled' => true,
        ]);

        Order::query()->create([
            'reference_num' => 'THUMBNAIL-TEST',
            'delivery_mode' => DeliveryMode::DELIVERY,
            'status' => Status::PENDING,
            'payment_method_id' => $paymentMethod->getKey(),
            'receipt_image_id' => $receiptImage->getKey(),
            'shipping_fee' => 0,
            'cus_name' => 'Test customer',
            'cus_phone' => '0123456789',
        ]);

        $this->artisan('images:generate-thumbnails')
            ->expectsOutputToContain('Generated 1 image thumbnails.')
            ->assertSuccessful();

        $this->assertNotNull($catalogImage->refresh()->thumbnail_id);
        $this->assertNull($receiptImage->refresh()->thumbnail_id);
    }
}
