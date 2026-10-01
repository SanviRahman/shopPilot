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
            $table->string('code', 80)->unique();
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 12, 2);
            $table->decimal('minimum_order_amount', 12, 2)->default(0);
            $table->dateTime('start_date')->index();
            $table->dateTime('end_date')->index();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};