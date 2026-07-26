<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Item;
use App\Models\ItemVariation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemVariation>
 */
class ItemVariationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();
        $itemIds = Item::query()->pluck('id')->all();
        $imageIds = Image::query()->pluck('id')->all();

        return [
            'barcode' => fake()->unique()->numberBetween(1000000000),
            'name' => $name,
            'name_en' => $name,

            'price' => fake()->randomFloat(2, 40.0, 50.0),
            'sale_price' => fake()->randomFloat(2, 30.0, 40.0),

            'weight' => fake()->randomFloat(3, 0.0, 5.0),
            'stock' => fake()->numberBetween(0, 100),

            'image_id' => fake()->randomElement($imageIds),
            'item_id' => fake()->randomElement($itemIds),
        ];
    }
}
