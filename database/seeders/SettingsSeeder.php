<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'store_name' => ["DESERV'D", 'string'],
            'store_email' => ['info@deservdcookies.com', 'string'],
            'store_phone_primary' => ['786-214-0582', 'string'],
            'store_phone_secondary' => ['786-402-7810', 'string'],
            'currency' => ['USD', 'string'],
            'tax_rate_percent' => [0, 'integer'],
            'free_shipping_threshold_cents' => [4500, 'integer'],
            'local_pickup_enabled' => [true, 'boolean'],
            'store_open' => [true, 'boolean'],
        ];

        foreach ($defaults as $key => [$value, $type]) {
            if (Setting::query()->where('key', $key)->doesntExist()) {
                Setting::set($key, $value, $type);
            }
        }

        $this->command->info('Store settings ready.');
    }
}
