<?php

namespace Database\Seeders;

use App\Enums\ActiveStatus;
use App\Models\Category;
use Illuminate\Database\Seeder;

/** Real catalogue data (mirrors the flavour families used by the storefront's shop filters), not demo/test data. */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Classic', 'slug' => 'classic', 'description' => 'The benchmark flavours.', 'sort_order' => 1],
            ['name' => 'Chocolate', 'slug' => 'chocolate', 'description' => 'Chocolate-forward cookies.', 'sort_order' => 2],
            ['name' => 'Specialty', 'slug' => 'specialty', 'description' => 'Seasonal and signature flavours.', 'sort_order' => 3],
            ['name' => '20g Protein', 'slug' => '20g-protein', 'description' => "The DESERV'D XX range.", 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'status' => ActiveStatus::Active],
            );
        }

        $this->command->info('Categories ready.');
    }
}
