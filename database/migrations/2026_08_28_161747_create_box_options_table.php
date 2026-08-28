<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Build-a-Box size/price configuration. Database-driven rather than
     * hard-coded so admins can change box sizes and pricing without a deploy.
     */
    public function up(): void
    {
        Schema::create('box_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('size')->unique(); // number of cookies, e.g. 4, 6, 8, 12
            $table->unsignedInteger('price'); // cents, flat price for the whole box
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('box_options');
    }
};
