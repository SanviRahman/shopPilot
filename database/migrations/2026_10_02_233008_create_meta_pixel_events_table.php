<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_pixel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('session_id', 120)->nullable()->index();
            $table->string('event_name', 80)->index();
            $table->uuid('event_id')->unique();
            $table->text('page_url')->nullable();
            $table->text('referrer')->nullable();
            $table->json('pixel_ids')->nullable();
            $table->json('payload')->nullable();
            $table->string('delivery_status', 20)->default('captured')->index();
            $table->string('ip_hash', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_pixel_events');
    }
};
