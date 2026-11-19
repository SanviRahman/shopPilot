<?php

namespace App\Services\Vendor;

use App\Models\Product;
use App\Models\ProductModerationHistory;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ProductService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendorProductService
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly VendorInventoryService $inventoryService,
    ) {
    }

    public function create(Vendor $vendor, User $user, array $data): Product
    {
        return DB::transaction(function () use ($vendor, $user, $data): Product {
            $moderationStatus = config('shop.vendor_products.require_moderation', true) ? 'draft' : 'approved';
            $data['vendor_id'] = $vendor->id;
            $data['moderation_status'] = $moderationStatus;
            $data['status'] = 'inactive';
            $product = $this->productService->create($data, $user->id);

            if ($product->product_type === 'simple') {
                $this->inventoryService->syncSimpleProduct($product, (int) $product->stock_quantity, $user->id);
            }

            $this->recordModeration($product, $vendor, $user->id, null, $moderationStatus);

            return $product->refresh();
        });
    }

    public function update(Vendor $vendor, User $user, Product $product, array $data): Product
    {
        return DB::transaction(function () use ($vendor, $user, $product, $data): Product {
            if ((int) $product->vendor_id !== (int) $vendor->id) {
                throw ValidationException::withMessages(['product' => 'This product does not belong to your store.']);
            }

            $oldType = $product->product_type;
            $oldModerationStatus = $product->moderation_status;
            if ($oldType === 'simple') {
                $this->inventoryService->ensureSimpleBalance($product);
            }

            $moderationStatus = ($product->moderation_status === 'suspended'
                || config('shop.vendor_products.require_moderation', true))
                ? 'draft'
                : 'approved';

            $data['vendor_id'] = $vendor->id;
            $data['moderation_status'] = $moderationStatus;
            $data['status'] = 'inactive';

            $updated = $this->productService->update($product, $data, $user->id);

            if ($updated->product_type === 'simple') {
                $this->inventoryService->syncSimpleProduct($updated, (int) $updated->stock_quantity, $user->id);
            } elseif ($oldType === 'simple') {
                $this->inventoryService->removeSimpleBalance($updated);
            }

            if ($oldModerationStatus !== $moderationStatus) {
                $this->recordModeration($updated, $vendor, $user->id, $oldModerationStatus, $moderationStatus);
            }

            return $updated->refresh();
        });
    }

    public function submitForReview(Vendor $vendor, User $user, Product $product): Product
    {
        return DB::transaction(function () use ($vendor, $user, $product): Product {
            $product = $vendor->products()->lockForUpdate()->findOrFail($product->id);

            if ($product->moderation_status !== 'draft') {
                throw ValidationException::withMessages(['product' => 'Only draft products can be submitted for review.']);
            }

            $from = $product->moderation_status;
            $product->update(['moderation_status' => 'pending_review', 'status' => 'inactive']);
            $this->recordModeration($product, $vendor, $user->id, $from, 'pending_review');

            return $product->refresh();
        });
    }

    public function publish(Vendor $vendor, Product $product): Product
    {
        if ((int) $product->vendor_id !== (int) $vendor->id || $product->moderation_status !== 'approved') {
            throw ValidationException::withMessages(['product' => 'Only approved products can be published.']);
        }

        $product->update(['status' => 'active']);

        return $product->refresh();
    }

    public function unpublish(Vendor $vendor, Product $product): Product
    {
        if ((int) $product->vendor_id !== (int) $vendor->id) {
            throw ValidationException::withMessages(['product' => 'This product does not belong to your store.']);
        }

        $product->update(['status' => 'inactive']);

        return $product->refresh();
    }

    private function recordModeration(
        Product $product,
        Vendor $vendor,
        int $userId,
        ?string $from,
        string $to,
    ): void {
        ProductModerationHistory::create([
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'changed_by_user_id' => $userId,
            'from_status' => $from,
            'to_status' => $to,
            'created_at' => now(),
        ]);
    }
}
