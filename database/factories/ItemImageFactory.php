<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemImage>
 */
class ItemImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $item_ids = array_column(Item::all('id')->toArray(), 'id');
        return [
            'image' => $this->faker->imageUrl(500, 500, 'food'),
            'item_id' => $this->faker->randomElement($item_ids),
        ];
    }
}
