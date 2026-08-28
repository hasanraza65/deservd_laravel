<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();

            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();

            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_at_price')->nullable();

            $table->string('weight', 30)->nullable();
            $table->unsignedInteger('protein_grams')->nullable();
            $table->unsignedInteger('calories')->nullable();
            $table->unsignedDecimal('carbohydrates_grams', 6, 2)->nullable();
            $table->unsignedDecimal('fat_grams', 6, 2)->nullable();
            $table->unsignedDecimal('fiber_grams', 6, 2)->nullable();
            $table->unsignedDecimal('sugar_grams', 6, 2)->nullable();

            $table->json('ingredients')->nullable();
            $table->json('allergens')->nullable();
            $table->text('storage_info')->nullable();
            $table->text('shipping_info')->nullable();

            $table->string('product_type', 30)->default('standard')->index();
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();

            $table->integer('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(10);

            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
