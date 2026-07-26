<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\ImageUploadOption;
use App\Models\Image;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminItemImageManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_an_image_can_be_uploaded_and_processed(): void
    {
        Storage::fake('public');

        $response = $this->postJson(route('image.upload'), [
            'image' => UploadedFile::fake()->image('product.jpg', 800, 600),
            'option' => ImageUploadOption::WHITE_EDGE->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('name', 'product.jpg')
            ->assertJsonPath('mime_type', 'image/jpeg');

        $image = Image::query()->findOrFail($response->json('id'));
        $storagePath = Str::after($image->url, '/storage/');

        Storage::disk('public')->assertExists($storagePath);

        $dimensions = getimagesize(Storage::disk('public')->path($storagePath));

        $this->assertIsArray($dimensions);
        $this->assertSame($dimensions[0], $dimensions[1]);
    }

    public function test_image_upload_requires_a_supported_image_with_a_valid_option(): void
    {
        Storage::fake('public');

        $this->postJson(route('image.upload'), [
            'image' => UploadedFile::fake()->create(
                'document.txt',
                10,
                'text/plain',
            ),
            'option' => ImageUploadOption::ORIGINAL->value,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');

        $this->postJson(route('image.upload'), [
            'image' => UploadedFile::fake()->image('product.jpg'),
            'option' => 'unsupported-option',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('option');

        $this->assertSame(0, Image::query()->count());
        Storage::disk('public')->assertDirectoryEmpty('/');
    }

    public function test_image_upload_rejects_files_too_large_for_the_php_request_limit(): void
    {
        Storage::fake('public');

        $this->postJson(route('image.upload'), [
            'image' => UploadedFile::fake()
                ->image('large-product.jpg')
                ->size(1800),
            'option' => ImageUploadOption::ORIGINAL->value,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');

        $this->assertSame(0, Image::query()->count());
    }

    public function test_editor_can_attach_an_uploaded_image_to_an_item(): void
    {
        $item = Item::query()->create(['name' => 'Image item']);
        $image = Image::factory()->create();

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->postJson(route('admin.ajax.item.image.store', [$item, $image]))
            ->assertOk()
            ->assertJsonPath('id', $item->id)
            ->assertJsonPath('images.0.id', $image->id);

        $this->assertTrue($item->images()->whereKey($image->id)->exists());
    }

    public function test_editor_can_remove_an_item_image_and_its_stored_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('item-images/removable.jpg', 'image');

        $item = Item::query()->create(['name' => 'Image item']);
        $image = Image::query()->create([
            'name' => 'removable.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 5,
            'url' => '/storage/item-images/removable.jpg',
        ]);
        $item->images()->attach($image);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->deleteJson(route('admin.ajax.item.image.destroy', [$item, $image]))
            ->assertOk()
            ->assertJsonPath('id', $item->id)
            ->assertJsonCount(0, 'images');

        $this->assertModelMissing($image);
        $this->assertFalse($item->images()->whereKey($image->id)->exists());
        Storage::disk('public')->assertMissing('item-images/removable.jpg');
    }

    public function test_removing_a_shared_image_keeps_the_image_and_stored_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('item-images/shared.jpg', 'image');

        $item = Item::query()->create(['name' => 'First item']);
        $otherItem = Item::query()->create(['name' => 'Second item']);
        $image = Image::query()->create([
            'name' => 'shared.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 5,
            'url' => '/storage/item-images/shared.jpg',
        ]);
        $item->images()->attach($image);
        $otherItem->images()->attach($image);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->deleteJson(route('admin.ajax.item.image.destroy', [$item, $image]))
            ->assertOk();

        $this->assertModelExists($image);
        $this->assertTrue(
            $otherItem->images()->whereKey($image->id)->exists(),
        );
        Storage::disk('public')->assertExists('item-images/shared.jpg');
    }

    public function test_an_item_cannot_remove_another_items_image(): void
    {
        $item = Item::query()->create(['name' => 'First item']);
        $otherItem = Item::query()->create(['name' => 'Second item']);
        $image = Image::factory()->create();
        $otherItem->images()->attach($image);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->deleteJson(route('admin.ajax.item.image.destroy', [$item, $image]))
            ->assertNotFound();

        $this->assertModelExists($image);
        $this->assertTrue(
            $otherItem->images()->whereKey($image->id)->exists(),
        );
    }

    public function test_viewer_cannot_attach_or_remove_item_images(): void
    {
        $item = Item::query()->create(['name' => 'Protected item']);
        $attachedImage = Image::factory()->create();
        $unattachedImage = Image::factory()->create();
        $item->images()->attach($attachedImage);

        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->postJson(route('admin.ajax.item.image.store', [
                $item,
                $unattachedImage,
            ]))
            ->assertForbidden();

        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->deleteJson(route('admin.ajax.item.image.destroy', [
                $item,
                $attachedImage,
            ]))
            ->assertForbidden();

        $this->assertFalse(
            $item->images()->whereKey($unattachedImage->id)->exists(),
        );
        $this->assertTrue(
            $item->images()->whereKey($attachedImage->id)->exists(),
        );
    }

    public function test_guest_cannot_attach_or_remove_item_images(): void
    {
        $item = Item::query()->create(['name' => 'Protected item']);
        $attachedImage = Image::factory()->create();
        $unattachedImage = Image::factory()->create();
        $item->images()->attach($attachedImage);

        $this->postJson(route('admin.ajax.item.image.store', [
            $item,
            $unattachedImage,
        ]))
            ->assertUnauthorized();

        $this->deleteJson(route('admin.ajax.item.image.destroy', [
            $item,
            $attachedImage,
        ]))
            ->assertUnauthorized();
    }

    private function user(AccessLevel $accessLevel): User
    {
        return User::factory()->create([
            'access_level' => $accessLevel->value,
        ]);
    }
}
