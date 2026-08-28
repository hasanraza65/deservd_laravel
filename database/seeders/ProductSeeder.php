<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

/**
 * The real DESERV'D catalogue — matches the eight products already live in
 * the React frontend's mock data, not throwaway demo/test content.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::query()->pluck('id', 'slug');

        $signatureNutrition = [
            'calories' => 410, 'carbohydrates_grams' => 45, 'fat_grams' => 17, 'fiber_grams' => 4, 'sugar_grams' => 13,
        ];
        $xxNutrition = [
            'calories' => 600, 'carbohydrates_grams' => 66, 'fat_grams' => 26, 'fiber_grams' => 6, 'sugar_grams' => 19,
        ];

        $products = [
            [
                'slug' => 'classic-chocolate-chip', 'sku' => 'DES-CCC-120', 'name' => 'Classic Chocolate Chip',
                'category' => 'classic', 'type' => ProductType::Standard, 'price' => 4.50, 'protein' => 13, 'weight' => '120g',
                'short_description' => 'The one everything else is measured against.',
                'description' => 'Brown butter notes, sea salt and a generous load of chocolate chips in a soft, thick centre.',
                'ingredients' => ['Whey protein blend', 'Oats', 'Butter', 'Brown sugar', 'Eggs', 'Chocolate chips', 'Sea salt', 'Vanilla'],
                'allergens' => ['Milk', 'Eggs', 'Wheat', 'Soy'],
                'nutrition' => $signatureNutrition, 'stock' => 100, 'featured' => true,
            ],
            [
                'slug' => 'triple-chocolate', 'sku' => 'DES-TCH-120', 'name' => 'Triple Chocolate',
                'category' => 'chocolate', 'type' => ProductType::Standard, 'price' => 4.75, 'protein' => 13, 'weight' => '120g',
                'short_description' => 'Rich cocoa base, chocolate chips and melted dark chocolate.',
                'description' => 'Our darkest cookie. A rich cocoa base loaded with chocolate chips, folded chunks and a molten dark chocolate centre.',
                'ingredients' => ['Whey protein blend', 'Cocoa powder', 'Oats', 'Butter', 'Eggs', 'Dark chocolate', 'Chocolate chips'],
                'allergens' => ['Milk', 'Eggs', 'Wheat', 'Soy'],
                'nutrition' => $signatureNutrition, 'stock' => 90, 'featured' => true,
            ],
            [
                'slug' => 'marble-cookie', 'sku' => 'DES-MRB-120', 'name' => 'Marble Cookie',
                'category' => 'chocolate', 'type' => ProductType::Standard, 'price' => 4.75, 'protein' => 13, 'weight' => '120g',
                'short_description' => 'Vanilla and cocoa doughs, marbled by hand.',
                'description' => 'Two doughs — vanilla and dark cocoa — marbled by hand so no two cookies bake the same.',
                'ingredients' => ['Whey protein blend', 'Oats', 'Cocoa powder', 'Butter', 'Eggs', 'Vanilla', 'Chocolate chunks'],
                'allergens' => ['Milk', 'Eggs', 'Wheat', 'Soy'],
                'nutrition' => $signatureNutrition, 'stock' => 80, 'featured' => false,
            ],
            [
                'slug' => 'cookies-and-cream', 'sku' => 'DES-CNC-120', 'name' => 'Cookies & Cream',
                'category' => 'specialty', 'type' => ProductType::Standard, 'price' => 4.95, 'protein' => 13, 'weight' => '120g',
                'short_description' => 'Vanilla base, sandwich cookie pieces, cream drizzle.',
                'description' => 'A vanilla protein base loaded with crushed chocolate sandwich cookie pieces and finished with a cream drizzle.',
                'ingredients' => ['Whey protein blend', 'Oats', 'Butter', 'Eggs', 'Vanilla', 'Chocolate sandwich cookie pieces', 'Cream drizzle'],
                'allergens' => ['Milk', 'Eggs', 'Wheat', 'Soy'],
                'nutrition' => $signatureNutrition, 'stock' => 70, 'featured' => false,
            ],
            [
                'slug' => 'smores', 'sku' => 'DES-SMR-120', 'name' => "S'mores",
                'category' => 'specialty', 'type' => ProductType::Standard, 'price' => 4.95, 'protein' => 13, 'weight' => '120g',
                'short_description' => 'Marble base, toasted marshmallow and melted chocolate.',
                'description' => 'A marble base loaded with chocolate chips and topped with torched marshmallow.',
                'ingredients' => ['Whey protein blend', 'Oats', 'Cocoa powder', 'Butter', 'Eggs', 'Marshmallow', 'Milk chocolate', 'Graham crumble'],
                'allergens' => ['Milk', 'Eggs', 'Wheat', 'Soy'],
                'nutrition' => $signatureNutrition, 'stock' => 85, 'featured' => true,
            ],
            [
                'slug' => 'red-velvet', 'sku' => 'DES-RDV-120', 'name' => 'Red Velvet',
                'category' => 'specialty', 'type' => ProductType::Standard, 'price' => 4.95, 'protein' => 13, 'weight' => '120g',
                'short_description' => 'Cocoa base, red velvet swirl, cream cheese drizzle.',
                'description' => 'A light cocoa base swirled with red velvet and finished with a cream cheese drizzle.',
                'ingredients' => ['Whey protein blend', 'Oats', 'Cocoa powder', 'Butter', 'Eggs', 'Cream cheese drizzle', 'Vanilla'],
                'allergens' => ['Milk', 'Eggs', 'Wheat', 'Soy'],
                'nutrition' => $signatureNutrition, 'stock' => 60, 'featured' => false,
            ],
            [
                'slug' => 'xx-chocolate-chip', 'sku' => 'DES-XCC-150', 'name' => "DESERV'D XX Chocolate Chip",
                'category' => '20g-protein', 'type' => ProductType::DeservdXX, 'price' => 6.95, 'protein' => 20, 'weight' => '150g',
                'short_description' => '150g. 20g protein. Same cookie, more of it.',
                'description' => 'Our Classic Chocolate Chip scaled up to 150g and reformulated to carry 20g of protein.',
                'ingredients' => ['Whey protein blend', 'Oats', 'Butter', 'Brown sugar', 'Eggs', 'Chocolate chips', 'Sea salt'],
                'allergens' => ['Milk', 'Eggs', 'Wheat', 'Soy'],
                'nutrition' => $xxNutrition, 'stock' => 50, 'featured' => false,
            ],
            [
                'slug' => 'xx-triple-chocolate', 'sku' => 'DES-XTC-150', 'name' => "DESERV'D XX Triple Chocolate",
                'category' => '20g-protein', 'type' => ProductType::DeservdXX, 'price' => 6.95, 'protein' => 20, 'weight' => '150g',
                'short_description' => '150g. 20g protein. Bigger. Bolder. Darker.',
                'description' => 'The Triple Chocolate at 150g with 20g of protein.',
                'ingredients' => ['Whey protein blend', 'Cocoa powder', 'Oats', 'Butter', 'Eggs', 'Dark chocolate', 'Chocolate chips'],
                'allergens' => ['Milk', 'Eggs', 'Wheat', 'Soy'],
                'nutrition' => $xxNutrition, 'stock' => 50, 'featured' => false,
            ],
        ];

        foreach ($products as $p) {
            Product::query()->updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'category_id' => $categoryIds[$p['category']] ?? null,
                    'name' => $p['name'],
                    'sku' => $p['sku'],
                    'description' => $p['description'],
                    'short_description' => $p['short_description'],
                    'price' => $p['price'],
                    'compare_at_price' => null,
                    'weight' => $p['weight'],
                    'protein_grams' => $p['protein'],
                    'calories' => $p['nutrition']['calories'],
                    'carbohydrates_grams' => $p['nutrition']['carbohydrates_grams'],
                    'fat_grams' => $p['nutrition']['fat_grams'],
                    'fiber_grams' => $p['nutrition']['fiber_grams'],
                    'sugar_grams' => $p['nutrition']['sugar_grams'],
                    'ingredients' => $p['ingredients'],
                    'allergens' => $p['allergens'],
                    'storage_info' => 'Best fresh within 5 days at room temperature, or up to 3 weeks refrigerated. Freezes well for up to 2 months.',
                    'shipping_info' => 'Baked to order and shipped within 1-2 business days.',
                    'product_type' => $p['type'],
                    'status' => ProductStatus::Active,
                    'is_featured' => $p['featured'],
                    'stock_quantity' => $p['stock'],
                    'low_stock_threshold' => 10,
                ],
            );
        }

        $this->attachImages();

        $this->command->info('Product catalogue ready (' . count($products) . ' products).');
    }

    /**
     * Six of the eight products have a real photo (see IMAGE-TODO.md in the
     * React frontend for which ones, and why the other two are deliberately
     * imageless rather than showing a mismatched stock photo). The files
     * already live in public/images/products — this just links them up.
     */
    private function attachImages(): void
    {
        $filesBySlug = [
            'classic-chocolate-chip' => 'classic-chocolate-chip.jpg',
            'triple-chocolate' => 'triple-chocolate.jpg',
            'marble-cookie' => 'marble-cookie.jpg',
            'smores' => 'smores.jpg',
            'xx-chocolate-chip' => 'xx-chocolate-chip.jpg',
            'xx-triple-chocolate' => 'xx-triple-chocolate.jpg',
        ];

        foreach ($filesBySlug as $slug => $filename) {
            $product = Product::query()->where('slug', $slug)->first();
            if (!$product || !file_exists(public_path("images/products/{$filename}"))) {
                continue;
            }

            ProductImage::query()->firstOrCreate(
                ['product_id' => $product->id, 'path' => $filename],
                ['disk' => 'product_images', 'is_primary' => true, 'sort_order' => 0],
            );
        }
    }
}
