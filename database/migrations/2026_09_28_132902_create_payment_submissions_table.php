<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('payment_method_id')->constrained('payment_methods') ->restrictOnDelete()->cascadeOnUpdate();
            $table->string('transaction_id', 100)->index();
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('submitted')->index();
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
    }
};