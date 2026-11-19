<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductModerationHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductModerationService
{
    public function review(Product $product, Admin $admin, string $decision, ?string $reason = null): Product
    {
        return DB::transaction(function () use ($product, $admin, $decision, $reason): Product {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);

            if (! $product->vendor_id) {
                throw ValidationException::withMessages(['product' => 'Only vendor-owned products require marketplace moderation.']);
            }

            $from = $product->moderation_status;
            $to = match ($decision) {
                'approve' => $from === 'pending_review' ? 'approved' : null,
                'reject' => $from === 'pending_review' ? 'rejected' : null,
                'suspend' => $from === 'approved' ? 'suspended' : null,
                default => null,
            };

            if (! $to) {
                throw ValidationException::withMessages(['decision' => 'This moderation action is not allowed for the current product state.']);
            }

            $product->update([
                'moderation_status' => $to,
                'status' => 'inactive',
            ]);

            ProductModerationHistory::create([
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'changed_by_admin_id' => $admin->id,
                'from_status' => $from,
                'to_status' => $to,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            return $product->refresh();
        });
    }
}
