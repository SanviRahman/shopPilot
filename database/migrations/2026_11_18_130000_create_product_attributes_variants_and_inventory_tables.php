
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_attributes')) {
            Schema::create('product_attributes', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80);
                $table->string('slug', 100)->unique();
                $table->string('display_type', 20)->default('button');
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_attribute_values')) {
            Schema::create('product_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_attribute_id')
                    ->constrained()
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('label', 80);
                $table->string('slug', 100);
                $table->string('color_code', 7)->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['product_attribute_id', 'slug']);
            });
        }

        if (! Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')
                    ->constrained()
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('sku', 100)->unique();
                $table->decimal('regular_price', 12, 2);
                $table->decimal('sale_price', 12, 2)->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['product_id', 'is_active']);
            });
        }

        if (! Schema::hasTable('product_variant_attribute_values')) {
            Schema::create('product_variant_attribute_values', function (Blueprint $table) {
                $table->id();

                $table->foreignId('product_variant_id');
                $table->foreignId('product_attribute_value_id');

                $table->unique(
                    ['product_variant_id', 'product_attribute_value_id'],
                    'variant_attribute_value_unique'
                );

                $table->index(
                    'product_attribute_value_id',
                    'pvav_attribute_value_idx'
                );

                $table->foreign('product_variant_id', 'pvav_variant_fk')
                    ->references('id')
                    ->on('product_variants')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreign('product_attribute_value_id', 'pvav_attribute_fk')
                    ->references('id')
                    ->on('product_attribute_values')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('inventory_balances')) {
            Schema::create('inventory_balances', function (Blueprint $table) {
                $table->id();

                $table->foreignId('product_variant_id')
                    ->unique()
                    ->constrained('product_variants')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('on_hand_quantity')->default(0);
                $table->unsignedInteger('reserved_quantity')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('inventory_movements')) {
            Schema::create('inventory_movements', function (Blueprint $table) {
                $table->id();

                $table->foreignId('product_variant_id')
                    ->constrained('product_variants')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('movement_type', 30);
                $table->integer('quantity_change');
                $table->unsignedInteger('on_hand_before');
                $table->unsignedInteger('on_hand_after');
                $table->string('reference_type', 50)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('note')->nullable();

                $table->foreignId('created_by_admin_id')
                    ->nullable()
                    ->constrained('admins')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->timestamps();

                $table->index(
                    ['product_variant_id', 'created_at'],
                    'inventory_variant_created_index'
                );

                $table->index(
                    ['reference_type', 'reference_id'],
                    'inventory_reference_index'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_balances');
    }
};
