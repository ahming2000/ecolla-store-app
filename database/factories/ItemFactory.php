<?php

namespace Database\Factories;

use App\Models\Origin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = $this->faker->name();
        $origin_ids = array_column(Origin::all('id')->toArray(), 'id');
        return [
            'name' => $name,
            'name_en' => $name,
            'desc' => $this->faker->text(),
            'is_listed' => false,
            'view_count' => $this->faker->numberBetween(0, 100),
            'sold' => $this->faker->numberBetween(0, 100),
            'origin_id' => $this->faker->randomElement($origin_ids),
        ];
    }
}
