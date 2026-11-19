<?php

namespace App\Services\Vendor;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendorInventoryService
{
    public function rows(Vendor $vendor, ?string $search = null): array
    {
        $products = Product::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('product_type', ['simple', 'variable'])
            ->with([
                'inventoryBalance',
                'variants' => fn ($query) => $query->where('is_active', true)
                    ->with(['inventoryBalance', 'attributeValues.attribute']),
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('variants', fn ($variantQuery) => $variantQuery->where('sku', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->get();

        $rows = [];

        foreach ($products as $product) {
            if ($product->product_type === 'simple') {
                $balance = $product->inventoryBalance;
                $onHand = (int) ($balance?->on_hand_quantity ?? $product->stock_quantity);
                $reserved = (int) ($balance?->reserved_quantity ?? 0);

                $rows[] = $this->row(
                    $product->id,
                    null,
                    $product->name,
                    null,
                    $product->sku,
                    $onHand,
                    $reserved,
                );

                continue;
            }

            foreach ($product->variants as $variant) {
                $options = $variant->attributeValues
                    ->map(fn ($value) => $value->attribute->name.': '.$value->label)
                    ->implode(' / ');
                $balance = $variant->inventoryBalance;

                $rows[] = $this->row(
                    $product->id,
                    $variant->id,
                    $product->name,
                    $options,
                    $variant->sku,
                    (int) ($balance?->on_hand_quantity ?? 0),
                    (int) ($balance?->reserved_quantity ?? 0),
                );
            }
        }

        return $rows;
    }

    public function summary(array $rows): array
    {
        $threshold = max(0, (int) config('shop.inventory.low_stock_threshold', 5));

        return [
            'tracked_items' => count($rows),
            'low_stock' => collect($rows)->filter(fn ($row) => $row['available_quantity'] > 0 && $row['available_quantity'] <= $threshold)->count(),
            'out_of_stock' => collect($rows)->where('available_quantity', 0)->count(),
        ];
    }

    public function ensureSimpleBalance(Product $product): InventoryBalance
    {
        return InventoryBalance::query()->firstOrCreate(
            ['product_id' => $product->id],
            [
                'on_hand_quantity' => (int) $product->stock_quantity,
                'reserved_quantity' => 0,
            ],
        );
    }

    public function syncSimpleProduct(Product $product, int $quantity, int $userId): void
    {
        $balance = InventoryBalance::query()
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        if (! $balance) {
            $balance = new InventoryBalance([
                'product_id' => $product->id,
                'on_hand_quantity' => 0,
                'reserved_quantity' => 0,
            ]);
        }

        $before = (int) $balance->on_hand_quantity;
        $reserved = (int) $balance->reserved_quantity;

        if ($quantity < $reserved) {
            throw ValidationException::withMessages([
                'stock_quantity' => 'On-hand stock cannot be lower than the quantity already reserved.',
            ]);
        }

        $balance->on_hand_quantity = $quantity;
        $balance->save();
        $product->update(['stock_quantity' => $balance->available_quantity]);

        if ($before !== $quantity) {
            InventoryMovement::create([
                'inventory_balance_id' => $balance->id,
                'product_id' => $product->id,
                'movement_type' => $before === 0 && $balance->wasRecentlyCreated ? 'opening' : 'adjustment',
                'quantity_change' => $quantity - $before,
                'on_hand_before' => $before,
                'on_hand_after' => $quantity,
                'note' => 'Vendor stock adjustment.',
                'created_by_user_id' => $userId,
            ]);
        }
    }

    public function adjustProduct(Product $product, int $quantity, int $userId): void
    {
        DB::transaction(function () use ($product, $quantity, $userId): void {
            $product = Product::query()
                ->whereKey($product->id)
                ->where('vendor_id', $product->vendor_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->product_type !== 'simple') {
                throw ValidationException::withMessages(['quantity' => 'Stock for variable products must be adjusted per variation.']);
            }

            $balance = InventoryBalance::query()
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = InventoryBalance::create([
                    'product_id' => $product->id,
                    'on_hand_quantity' => (int) $product->stock_quantity,
                    'reserved_quantity' => 0,
                ]);
            }

            $this->applyAdjustment($balance, $quantity, $userId, $product, null);
            $product->update(['stock_quantity' => $balance->available_quantity]);
        });
    }

    public function adjustVariant(ProductVariant $variant, int $quantity, int $userId, Vendor $vendor): void
    {
        DB::transaction(function () use ($variant, $quantity, $userId, $vendor): void {
            $product = Product::query()
                ->whereKey($variant->product_id)
                ->where('vendor_id', $vendor->id)
                ->lockForUpdate()
                ->firstOrFail();

            $variant = ProductVariant::query()
                ->whereKey($variant->id)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $balance = InventoryBalance::query()
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = InventoryBalance::create([
                    'product_variant_id' => $variant->id,
                    'on_hand_quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $this->applyAdjustment($balance, $quantity, $userId, $product, $variant);

            $stock = $product->variants()
                ->where('is_active', true)
                ->with('inventoryBalance')
                ->get()
                ->sum(fn (ProductVariant $item) => $item->available_quantity);

            $product->update(['stock_quantity' => $stock]);
        });
    }

    public function removeSimpleBalance(Product $product): void
    {
        $balance = InventoryBalance::query()
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        if ($balance && $balance->reserved_quantity > 0) {
            throw ValidationException::withMessages(['product_type' => 'This product has reserved stock and cannot change product type yet.']);
        }

        $balance?->delete();
    }

    private function applyAdjustment(
        InventoryBalance $balance,
        int $quantity,
        int $userId,
        Product $product,
        ?ProductVariant $variant,
    ): void {
        $before = (int) $balance->on_hand_quantity;
        $reserved = (int) $balance->reserved_quantity;

        if ($quantity < $reserved) {
            throw ValidationException::withMessages([
                'quantity' => 'On-hand stock cannot be lower than the quantity already reserved.',
            ]);
        }

        $balance->on_hand_quantity = $quantity;
        $balance->save();

        if ($before !== $quantity) {
            InventoryMovement::create([
                'inventory_balance_id' => $balance->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'movement_type' => 'adjustment',
                'quantity_change' => $quantity - $before,
                'on_hand_before' => $before,
                'on_hand_after' => $quantity,
                'note' => 'Vendor stock adjustment.',
                'created_by_user_id' => $userId,
            ]);
        }
    }

    private function row(
        int $productId,
        ?int $variantId,
        string $productName,
        ?string $variantName,
        string $sku,
        int $onHand,
        int $reserved,
    ): array {
        return [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'product_name' => $productName,
            'variant_name' => $variantName,
            'sku' => $sku,
            'on_hand_quantity' => $onHand,
            'reserved_quantity' => $reserved,
            'available_quantity' => max(0, $onHand - $reserved),
        ];
    }
}
