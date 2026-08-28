<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Real, required data — safe and idempotent to run on every deploy.
        $this->call([
            AdminUserSeeder::class,
            SettingsSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            BoxOptionSeeder::class,
            ShippingMethodSeeder::class,
        ]);

        // Demo data for local development only — fake customers and orders
        // so the admin dashboard/analytics have something to show. No fake
        // reviews are ever seeded, per the brief.
        if (app()->environment('local', 'testing')) {
            $this->call([
                CustomerSeeder::class,
                DemoOrderSeeder::class,
            ]);
        }
    }
}
