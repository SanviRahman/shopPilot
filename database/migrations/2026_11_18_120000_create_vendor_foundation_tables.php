<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('business_name', 180);
            $table->string('legal_name', 180)->nullable();
            $table->string('business_type', 80)->nullable();
            $table->string('email', 191);
            $table->string('phone', 30);
            $table->string('website', 255)->nullable();
            $table->string('tax_identifier', 100)->nullable();
            $table->text('address');
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('status', 30)->default('pending_review')->index();
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->unique()->constrained('vendors')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('name', 180);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 191)->nullable();
            $table->text('address')->nullable();
            $table->text('shipping_policy')->nullable();
            $table->text('return_policy')->nullable();
            $table->string('status', 20)->default('inactive')->index();
            $table->boolean('vacation_mode')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('role', 30)->default('owner');
            $table->string('status', 20)->default('active')->index();
            $table->json('permissions')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('vendor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->string('document_type', 100);
            $table->string('disk', 40)->default('local');
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('status', 20)->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['vendor_id', 'document_type']);
        });

        Schema::create('vendor_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('changed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['vendor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_status_histories');
        Schema::dropIfExists('vendor_documents');
        Schema::dropIfExists('vendor_users');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('vendors');
    }
};
