<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('moderation_status', 30)->default('approved')->after('status')->index();
            $table->index(['vendor_id', 'status', 'moderation_status'], 'products_vendor_visibility_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_vendor_visibility_index');
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'moderation_status']);
        });
    }
};
