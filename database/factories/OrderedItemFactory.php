<?php

namespace Database\Factories;

use App\Models\ItemVariation;
use App\Models\Order;
use App\Models\OrderedItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderedItem>
 */
class OrderedItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderIds = Order::query()->pluck('id')->all();
        $variation = ItemVariation::query()->inRandomOrder()->firstOrFail();

        return [
            'name' => $variation->name,
            'name_en' => $variation->name_en,
            'barcode' => $variation->barcode,
            'price' => $variation->price,
            'sale_price' => $variation->sale_price,
            'quantity' => fake()->numberBetween(1, 10),
            'order_id' => fake()->randomElement($orderIds),
        ];
    }
}
