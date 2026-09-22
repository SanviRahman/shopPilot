<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete()->cascadeOnUpdate();

            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('sku', 100)->unique();

            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();

            $table->decimal('regular_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();

            $table->unsignedInteger('stock_quantity')->default(0)->index();
            $table->string('status', 20)->default('active')->index();
            $table->boolean('featured')->default(false)->index();

            // General SEO
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 255)->nullable();
            $table->string('canonical_url', 255)->nullable();

            // OpenGraph (Facebook)
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();

            // Twitter Card
            $table->string('twitter_title', 255)->nullable();
            $table->text('twitter_description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
            $table->index(['status', 'featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};