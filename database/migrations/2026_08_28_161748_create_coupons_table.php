<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type', 20); // fixed | percentage
            $table->unsignedInteger('value'); // cents if fixed, whole percent (0-100) if percentage
            $table->unsignedInteger('min_order_amount')->nullable(); // cents
            $table->unsignedInteger('max_discount')->nullable(); // cents, caps a percentage discount
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable(); // total uses allowed, null = unlimited
            $table->unsignedInteger('per_customer_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
