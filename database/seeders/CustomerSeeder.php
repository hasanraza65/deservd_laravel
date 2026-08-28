<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DEMO DATA — fake customers for exercising the admin customer list, order
 * history and dashboard stats locally. Distinct from AdminUserSeeder, which
 * creates the one real account this application ships with.
 */
class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->customers()->count() >= 12) {
            $this->command->info('Demo customers already present, skipping.');

            return;
        }

        User::factory()->count(12)->create();

        $this->command->info('Demo customers ready (12).');
    }
}
