<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_pixels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->json('pixel_ids');
            $table->longText('full_script')->nullable();
            $table->string('lifecycle_status', 20)->default('draft')->index();
            $table->boolean('track_page_view')->default(true);
            $table->boolean('track_ecommerce')->default(true);
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_pixels');
    }
};
