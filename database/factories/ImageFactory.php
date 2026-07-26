<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    private const SAMPLE_IMAGE_URLS = [
        '/images/example-items/assorted-drinks.png',
        '/images/example-items/noodles-and-hotpot.png',
        '/images/example-items/savory-snacks.png',
        '/images/example-items/sweet-snacks.png',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var string $imageUrl */
        $imageUrl = fake()->randomElement(self::SAMPLE_IMAGE_URLS);
        $imagePath = public_path(Str::after($imageUrl, '/'));

        return [
            'name' => Str::afterLast($imageUrl, '/'),
            'mime_type' => 'image/png',
            'size' => File::size($imagePath),
            'url' => $imageUrl,
            'data_uri' => null,
        ];
    }
}
