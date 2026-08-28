<?php

namespace Database\Seeders;

use App\Models\BoxOption;
use Illuminate\Database\Seeder;

/** Mirrors the flat box pricing already used by the Build-a-Box UI in the React frontend. */
class BoxOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['size' => 4, 'price' => 18.00, 'sort_order' => 1],
            ['size' => 6, 'price' => 25.00, 'sort_order' => 2],
            ['size' => 8, 'price' => 32.00, 'sort_order' => 3],
            ['size' => 12, 'price' => 46.00, 'sort_order' => 4],
        ];

        foreach ($options as $option) {
            BoxOption::query()->updateOrCreate(
                ['size' => $option['size']],
                [...$option, 'is_active' => true],
            );
        }

        $this->command->info('Box options ready.');
    }
}
