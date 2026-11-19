<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('vendor_id')->nullable()->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->string('seller_key', 60);
            $table->string('suborder_number', 50)->unique();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->decimal('commission_rate', 7, 4)->default(0);
            $table->decimal('commission_basis_amount', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('vendor_earning', 12, 2)->default(0);
            $table->string('currency_code', 3)->default('BDT');
            $table->string('payment_status', 20)->default('submitted')->index();
            $table->string('status', 30)->default('awaiting_payment')->index();
            $table->string('shipping_provider', 100)->nullable();
            $table->string('tracking_number', 120)->nullable();
            $table->string('tracking_url', 500)->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'seller_key']);
            $table->index(['vendor_id', 'status', 'created_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'vendor_order_id')) {
                $table->foreignId('vendor_order_id')->nullable()->after('order_id')->constrained('vendor_orders')->restrictOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('order_items', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('line_total');
            }
        });

        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'shipping_fee')) {
                $table->decimal('shipping_fee', 12, 2)->default(0)->after('return_policy');
            }
        });

        Schema::create('vendor_order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_order_id')->constrained('vendor_orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['vendor_order_id', 'created_at']);
        });

        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->unique()->constrained('order_items')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('inventory_balance_id')->constrained('inventory_balances')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('quantity');
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type', 30)->default('marketplace');
            $table->decimal('rate_value', 7, 4)->default(0);
            $table->string('commission_basis', 40)->default('net_merchandise');
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->index(['scope_type', 'is_active', 'effective_from']);
        });

        DB::table('commission_rules')->insert([
            'scope_type'       => 'marketplace',
            'rate_value'       => 0,
            'commission_basis' => 'net_merchandise',
            'effective_from'   => now(),
            'is_active'        => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        Schema::create('commission_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_order_id')->unique()->constrained('vendor_orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('commission_rule_id')->nullable()->constrained('commission_rules')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('basis_amount', 12, 2);
            $table->decimal('rate_value', 7, 4);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('vendor_earning', 12, 2);
            $table->string('currency_code', 3);
            $table->json('rule_snapshot')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('vendor_payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->string('channel', 30);
            $table->string('account_holder', 150);
            $table->text('account_number');
            $table->string('bank_name', 150)->nullable();
            $table->string('branch_name', 150)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('is_primary')->default(false);
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
        });

        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('payout_account_id')->constrained('vendor_payout_accounts')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('amount', 12, 2);
            $table->string('currency_code', 3);
            $table->string('status', 20)->default('requested')->index();
            $table->timestamp('requested_at');
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'status', 'created_at']);
        });

        Schema::create('payout_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_request_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('amount', 12, 2);
            $table->string('channel', 30);
            $table->string('transfer_reference', 120)->nullable();
            $table->string('proof_path', 500)->nullable();
            $table->string('status', 20)->default('processing')->index();
            $table->foreignId('processed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('processed_at')->nullable();
            $table->text('failure_note')->nullable();
            $table->timestamps();
            $table->index(['payout_request_id', 'created_at']);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('vendor_order_id')->constrained('vendor_orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('requested_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->default(0);
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->string('currency_code', 3);
            $table->string('status', 20)->default('requested')->index();
            $table->text('reason');
            $table->text('admin_note')->nullable();
            $table->boolean('restock_items')->default(false);
            $table->string('transfer_reference', 120)->nullable();
            $table->foreignId('processed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['vendor_order_id', 'status', 'created_at']);
        });

        Schema::create('refund_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_id')->constrained('refunds')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('order_item_id')->constrained('order_items')->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('quantity');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['refund_id', 'order_item_id']);
        });

        Schema::create('vendor_financial_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('vendor_order_id')->nullable()->constrained('vendor_orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('payout_request_id')->nullable()->constrained('payout_requests')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('refund_id')->nullable()->constrained('refunds')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('entry_type', 40);
            $table->string('bucket', 20);
            $table->decimal('amount', 12, 2);
            $table->string('currency_code', 3);
            $table->string('idempotency_key', 191)->unique();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('posted_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['vendor_id', 'bucket', 'currency_code', 'posted_at'], 'vendor_ledger_vendor_bucket_posted_idx');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_financial_ledger');
        Schema::dropIfExists('refund_items');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payout_transactions');
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('vendor_payout_accounts');
        Schema::dropIfExists('commission_entries');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('inventory_reservations');
        Schema::dropIfExists('vendor_order_histories');
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'shipping_fee')) {
                $table->dropColumn('shipping_fee');
            }
        });
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'vendor_order_id')) {
                $table->dropForeign(['vendor_order_id']);
                $table->dropColumn('vendor_order_id');
            }
            if (Schema::hasColumn('order_items', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
        Schema::dropIfExists('vendor_orders');
    }
};
