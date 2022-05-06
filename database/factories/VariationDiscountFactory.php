<?php

namespace Database\Factories;

use App\Models\Variation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VariationDiscount>
 */
class VariationDiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $variation_ids = array_column(Variation::all('barcode')->toArray(), 'barcode');
        return [
            'barcode' => $this->faker->unique()->randomElement($variation_ids),
            'start' => $this->faker->date(),
            'end' => Date::now(),
            'rate' => $this->faker->randomFloat(2, 0.5, 1.0),
        ];
    }
}
