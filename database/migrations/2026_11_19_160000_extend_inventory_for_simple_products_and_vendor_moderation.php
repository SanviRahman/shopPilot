<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('inventory_balances', 'product_id')) {
            Schema::table('inventory_balances', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->after('id');
                $table->unique('product_id', 'inventory_balances_product_id_unique');
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            });
        }

        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id')->nullable()->change();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_movements', 'inventory_balance_id')) {
                $table->foreignId('inventory_balance_id')->nullable()->after('id')->constrained('inventory_balances')->nullOnDelete();
            }

            if (! Schema::hasColumn('inventory_movements', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('product_variant_id')->constrained('products')->nullOnDelete();
            }

            if (! Schema::hasColumn('inventory_movements', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            }

            $table->unsignedBigInteger('product_variant_id')->nullable()->change();
        });

        if (! Schema::hasTable('product_moderation_histories')) {
            Schema::create('product_moderation_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreignId('changed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
                $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
                $table->string('from_status', 30)->nullable();
                $table->string('to_status', 30);
                $table->text('reason')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['product_id', 'created_at']);
                $table->index(['vendor_id', 'to_status', 'created_at'], 'product_moderation_vendor_status_index');
            });
        }
    }

    public function down(): void
    {
        if (DB::table('inventory_balances')->whereNotNull('product_id')->exists()
            || DB::table('inventory_movements')->whereNull('product_variant_id')->whereNotNull('product_id')->exists()
            || DB::table('inventory_movements')->whereNotNull('created_by_user_id')->exists()
            || DB::table('product_moderation_histories')->exists()) {
            throw new \RuntimeException('Vendor inventory or moderation history exists. This migration cannot be rolled back without losing audit data.');
        }

        Schema::dropIfExists('product_moderation_histories');

        Schema::table('inventory_movements', function (Blueprint $table) {
            foreach (['inventory_balance_id', 'product_id', 'created_by_user_id'] as $column) {
                if (Schema::hasColumn('inventory_movements', $column)) {
                    $table->dropForeign([$column]);
                    $table->dropColumn($column);
                }
            }

            $table->unsignedBigInteger('product_variant_id')->nullable(false)->change();
        });

        Schema::table('inventory_balances', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_balances', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropUnique('inventory_balances_product_id_unique');
                $table->dropColumn('product_id');
            }

            $table->unsignedBigInteger('product_variant_id')->nullable(false)->change();
        });
    }
};
