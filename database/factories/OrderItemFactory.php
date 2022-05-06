<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Order;
use App\Models\Variation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $order_ids = array_column(Order::all('id')->toArray(), 'id');
        $variation = $this->faker->randomElement(Variation::all());

        return [
            'order_id' => $this->faker->randomElement($order_ids),
            'name' => $variation->name,
            'name_en' => $variation->name_en,
            'barcode' => $variation->barcode,
            'price' => $variation->price,
            'discount_rate' => $this->faker->randomFloat(2, 0.5, 1.0),
            'quantity' => $this->faker->numberBetween(1, 10),
        ];
    }
}
