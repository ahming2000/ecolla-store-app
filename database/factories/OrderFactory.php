<?php

namespace Database\Factories;

use App\Enums\DeliveryMode;
use App\Enums\Status;
use App\Models\Image;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imageIds = Image::query()->pluck('id')->all();
        $paymentMethodIds = PaymentMethod::query()->pluck('id')->all();

        $orderDeliveryModeList = collect(DeliveryMode::cases())->map(fn ($s) => $s->value)->toArray();
        $statusList = collect(Status::cases())->map(fn ($s) => $s->value)->toArray();

        return [
            'reference_num' => Str::random(),
            'delivery_mode' => fake()->randomElement($orderDeliveryModeList),
            'status' => fake()->randomElement($statusList),

            'payment_method_id' => fake()->randomElement($paymentMethodIds),

            'tracking_no' => Str::random(),
            'shipping_fee' => 3.0,

            'receipt_image_id' => fake()->randomElement($imageIds),

            'note' => '',

            'cus_name' => fake()->name(),
            'cus_phone' => fake()->phoneNumber(),
            'cus_address' => fake()->address(),
        ];
    }
}
