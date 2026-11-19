<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'product_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('product_type', 20)->default('simple')->after('sku')->index();
            });
        }

        if (! Schema::hasColumn('order_items', 'product_variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete()->cascadeOnUpdate();
            });
        }

        if (! Schema::hasColumn('order_items', 'variant_name')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('variant_name', 255)->nullable()->after('sku');
            });
        }

        if (! Schema::hasColumn('order_items', 'variant_attributes')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->json('variant_attributes')->nullable()->after('variant_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'variant_attributes')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('variant_attributes');
            });
        }

        if (Schema::hasColumn('order_items', 'variant_name')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('variant_name');
            });
        }

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['product_variant_id']);
                $table->dropColumn('product_variant_id');
            });
        }

        if (Schema::hasColumn('products', 'product_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['product_type']);
                $table->dropColumn('product_type');
            });
        }
    }
};
