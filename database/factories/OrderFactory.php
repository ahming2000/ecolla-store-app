<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::random(),
            'mode' => $this->faker->numberBetween(0, 1),
            'tracking_id' => Str::random(),
            'shipping_fee' => 3.0,
            'payment_method' => $this->faker->numberBetween(0, 4),
            'status' => $this->faker->numberBetween(0, 2),
            'receipt_image' => $this->faker->imageUrl(),
            'note' => '',
        ];
    }
}
