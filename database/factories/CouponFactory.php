<?php

namespace Database\Factories;

use App\Enums\ActiveStatus;
use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('SAVE##??')),
            'type' => CouponType::Percentage,
            'value' => fake()->numberBetween(5, 25),
            'min_order_amount' => null,
            'max_discount' => null,
            'starts_at' => null,
            'expires_at' => null,
            'usage_limit' => null,
            'per_customer_limit' => null,
            'usage_count' => 0,
            'status' => ActiveStatus::Active,
        ];
    }
}
