<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Item;
use App\Models\ItemVariation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImageDisplayTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_image_factory_uses_a_bundled_sample_image(): void
    {
        $image = Image::factory()->create();
        $imageUrl = $image->url;

        $this->assertIsString($imageUrl);
        $this->assertStringStartsWith('/images/example-items/', $imageUrl);
        $this->assertSame('image/png', $image->mime_type);
        $this->assertNull($image->data_uri);

        $imagePath = public_path(ltrim($imageUrl, '/'));

        $this->assertTrue(File::exists($imagePath));
        $this->assertSame(File::size($imagePath), $image->size);
    }

    public function test_item_cover_image_uses_an_attached_images_source(): void
    {
        $dataUri = 'data:image/png;base64,example-image';
        $image = new Image([
            'name' => 'example.png',
            'mime_type' => 'image/png',
            'size' => 13,
            'data_uri' => $dataUri,
        ]);
        $item = new Item;
        $item->setRelation('images', new Collection([$image]));
        $item->setRelation('variations', new Collection);

        $this->assertSame($dataUri, $item->cover_image);
    }

    public function test_item_cover_image_uses_a_variation_images_source(): void
    {
        $image = new Image([
            'name' => 'variation.png',
            'mime_type' => 'image/png',
            'size' => 13,
            'url' => '/images/example-items/savory-snacks.png',
        ]);
        $variation = new ItemVariation;
        $variation->image_id = 1;
        $variation->setRelation('image', $image);
        $item = new Item;
        $item->setRelation('images', new Collection);
        $item->setRelation('variations', new Collection([$variation]));

        $this->assertSame($image->src, $item->cover_image);
    }

    public function test_item_cover_image_is_null_without_an_image(): void
    {
        $item = new Item;
        $item->setRelation('images', new Collection);
        $item->setRelation('variations', new Collection);

        $this->assertNull($item->cover_image);
    }
}
