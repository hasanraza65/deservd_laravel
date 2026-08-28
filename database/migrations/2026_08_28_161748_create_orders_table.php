<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Bakery fulfilment workflow status (see App\Enums\OrderStatus).
            $table->string('status', 20)->default('new')->index();
            // Separate from the workflow status, per spec.
            $table->string('payment_status', 20)->default('pending')->index();
            // Coarse shipped/unshipped flag, distinct from the granular workflow status above.
            $table->string('fulfillment_status', 20)->default('unfulfilled')->index();

            $table->unsignedInteger('subtotal'); // cents
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('shipping_cost')->default(0);
            $table->unsignedInteger('tax_amount')->default(0);
            $table->unsignedInteger('total');

            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code')->nullable(); // snapshot, survives coupon deletion

            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete();
            $table->string('shipping_method_name')->nullable(); // snapshot
            $table->string('payment_method', 50)->nullable();

            $table->text('notes')->nullable();

            // Snapshots: orders must not depend on the addresses table surviving edits/deletes.
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();

            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 30)->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
