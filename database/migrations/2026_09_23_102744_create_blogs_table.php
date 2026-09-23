<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('author'); // User ebong Admin dujoneri polymorphic relation
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->softDeletes(); // deleted_at column
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};