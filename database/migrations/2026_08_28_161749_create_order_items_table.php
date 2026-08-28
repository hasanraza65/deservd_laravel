<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            // Nullable + ON DELETE SET NULL: a deleted product must never delete
            // historical order data. product_name/sku below are the source of
            // truth for what was actually sold.
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('order_item_group_id')->nullable()->constrained('order_item_groups')->cascadeOnDelete();

            $table->string('product_name'); // snapshot
            $table->string('product_sku')->nullable(); // snapshot
            $table->string('product_image_path')->nullable(); // snapshot

            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price'); // cents, price at time of purchase
            $table->unsignedInteger('total_price'); // cents, unit_price * quantity

            $table->json('metadata')->nullable(); // e.g. flavour notes, protein grams at time of sale

            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
