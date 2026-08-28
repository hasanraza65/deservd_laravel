<?php

namespace Database\Seeders;

use App\Enums\ShippingMethodType;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Standard Shipping',
                'type' => ShippingMethodType::Shipping,
                'price' => 6.99,
                'estimated_delivery_text' => '3-5 business days',
                'sort_order' => 1,
            ],
            [
                'name' => 'Local Pickup',
                'type' => ShippingMethodType::Pickup,
                'price' => 0.00,
                'estimated_delivery_text' => 'Ready same day in Miami',
                'sort_order' => 2,
            ],
        ];

        foreach ($methods as $method) {
            ShippingMethod::query()->updateOrCreate(
                ['name' => $method['name']],
                [...$method, 'is_active' => true],
            );
        }

        $this->command->info('Shipping methods ready.');
    }
}
