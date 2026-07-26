<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Origin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();
        $originIds = Origin::query()->pluck('id')->all();

        return [
            'name' => $name,
            'name_en' => $name,
            'desc' => fake()->text(),

            'is_listed' => false,
            'view_count' => fake()->numberBetween(0, 100),
            'sold_count' => fake()->numberBetween(0, 100),

            'origin_id' => fake()->randomElement($originIds),
        ];
    }
}
