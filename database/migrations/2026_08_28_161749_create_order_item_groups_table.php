<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Groups a Build-a-Box purchase's flavour lines under one row, so an order
     * can show "6 Cookie Box" with its flavour breakdown indented beneath it
     * instead of six indistinguishable product lines.
     */
    public function up(): void
    {
        Schema::create('order_item_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('type', 20)->default('box');
            $table->unsignedInteger('box_size')->nullable();
            $table->unsignedInteger('price'); // cents, flat box price charged
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_groups');
    }
};
