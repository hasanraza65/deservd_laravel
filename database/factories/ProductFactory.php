<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Not used by the real catalogue (see ProductSeeder) — available for tests
 * or ad-hoc extra demo products.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->words(2, true)) . ' Cookie';

        return [
            'category_id' => null,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'sku' => 'DEMO-' . strtoupper(fake()->bothify('??###')),
            'description' => fake()->paragraph(),
            'short_description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 3.5, 7.5),
            'compare_at_price' => null,
            'weight' => '120g',
            'protein_grams' => 13,
            'calories' => fake()->numberBetween(350, 450),
            'carbohydrates_grams' => fake()->randomFloat(2, 30, 50),
            'fat_grams' => fake()->randomFloat(2, 10, 20),
            'fiber_grams' => fake()->randomFloat(2, 2, 6),
            'sugar_grams' => fake()->randomFloat(2, 8, 16),
            'ingredients' => ['Whey protein blend', 'Oats', 'Butter', 'Eggs'],
            'allergens' => ['Milk', 'Eggs', 'Wheat'],
            'product_type' => ProductType::Standard,
            'status' => ProductStatus::Active,
            'is_featured' => false,
            'stock_quantity' => fake()->numberBetween(20, 100),
            'low_stock_threshold' => 10,
        ];
    }
}
