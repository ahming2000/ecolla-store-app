<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Variation>
 */
class VariationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = $this->faker->name();
        $item_ids = array_column(Item::all('id')->toArray(), 'id');
        return [
            'barcode' => $this->faker->unique()->numberBetween(1000000000),
            'name' => $name,
            'name_en' => $name,
            'price' => $this->faker->randomFloat(2,1.0, 50.0),
            'weight' => $this->faker->randomFloat(3, 0.0, 5.0),
            'image' => $this->faker->imageUrl(500, 500, 'food'),
            'stock' => $this->faker->numberBetween(0, 100),
            'item_id' => $this->faker->randomElement($item_ids),
        ];
    }
}
