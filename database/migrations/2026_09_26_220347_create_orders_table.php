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
            $table->string('order_number', 40)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete()->cascadeOnUpdate();
            $table->string('coupon_code', 80)->nullable();
            $table->string('buyer_name', 150);
            $table->string('buyer_phone', 30);
            $table->string('buyer_email', 191);

            $table->text('shipping_address');
            $table->string('city_or_area', 150);

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('shipping', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2);

            $table->string('payment_status', 20)->default('unpaid')->index();

            $table->string('order_status', 20)->default('pending')->index();

            $table->text('customer_note')->nullable();
            $table->text('internal_note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
            $table->index(['assigned_agent_id', 'order_status']);
            $table->index('coupon_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};