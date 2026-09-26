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

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('variant_id')->nullable()->index();

            $table->string('product_name', 255);
            $table->string('variant_name', 255)->nullable();
            $table->string('sku', 100)->nullable();

            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'created_at']);
            $table->index('sku');

            // Safe foreign key assignment if tables exist
            if (Schema::hasTable('products')) {
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete()->cascadeOnUpdate();
            }
            if (Schema::hasTable('product_variants')) {
                $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete()->cascadeOnUpdate();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};