<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OrderItemService
{
    public function create(array $data): OrderItem
    {
        return DB::transaction(function () use ($data): OrderItem {
            $order = Order::query()->findOrFail((int) $data['order_id']);
            if ($order->vendorOrders()->exists()) {
                throw ValidationException::withMessages(['order_id' => 'Marketplace order items are managed through seller fulfilment and refund workflows.']);
            }

            $product = Product::query()
                ->with(['variants.inventoryBalance', 'variants.attributeValues.attribute'])
                ->findOrFail((int) $data['product_id']);
            $snapshot = $this->productSnapshot($product, isset($data['product_variant_id']) ? (int) $data['product_variant_id'] : null);
            $unitPrice = round((float) $data['unit_price'], 2);
            $quantity = (int) $data['quantity'];

            $orderItem = OrderItem::create([
                'order_id' => (int) $data['order_id'],
                'product_id' => $product->id,
                ...$snapshot,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => round($unitPrice * $quantity, 2),
            ]);

            $this->syncOrderTotals($orderItem->order_id);

            return $orderItem->load(['order', 'product', 'variant']);
        });
    }

    public function update(OrderItem $orderItem, array $data): OrderItem
    {
        return DB::transaction(function () use ($orderItem, $data): OrderItem {
            if ($orderItem->vendor_order_id) {
                throw ValidationException::withMessages(['order_item' => 'Marketplace order items cannot be edited directly.']);
            }

            $productId = (int) $data['product_id'];
            $variantId = array_key_exists('product_variant_id', $data)
                ? ($data['product_variant_id'] ? (int) $data['product_variant_id'] : null)
                : $orderItem->product_variant_id;
            $unitPrice = round((float) $data['unit_price'], 2);
            $quantity = (int) $data['quantity'];
            $snapshot = [
                'product_id' => $orderItem->product_id,
                'product_variant_id' => $orderItem->product_variant_id,
                'product_name' => $orderItem->product_name,
                'sku' => $orderItem->sku,
                'variant_name' => $orderItem->variant_name,
                'variant_attributes' => $orderItem->variant_attributes,
            ];

            if ($productId !== (int) $orderItem->product_id || $variantId !== $orderItem->product_variant_id) {
                $product = Product::query()
                    ->with(['variants.inventoryBalance', 'variants.attributeValues.attribute'])
                    ->findOrFail($productId);
                $snapshot = [
                    'product_id' => $product->id,
                    ...$this->productSnapshot($product, $variantId),
                ];
            }

            $orderItem->update([
                ...$snapshot,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => round($unitPrice * $quantity, 2),
            ]);

            $this->syncOrderTotals($orderItem->order_id);

            return $orderItem->refresh()->load(['order', 'product', 'variant']);
        });
    }

    private function productSnapshot(Product $product, ?int $variantId): array
    {
        if ($product->vendor_id && config('shop.vendor_products.require_moderation', true) && $product->moderation_status !== 'approved') {
            throw ValidationException::withMessages(['product_id' => 'This vendor product has not been approved by the marketplace.']);
        }

        if ($product->product_type !== 'variable') {
            if ($variantId) {
                throw ValidationException::withMessages(['product_variant_id' => 'A simple product does not have variations.']);
            }

            return [
                'product_variant_id' => null,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'variant_name' => null,
                'variant_attributes' => null,
            ];
        }

        $variant = $variantId
            ? $product->variants->firstWhere('id', $variantId)
            : null;

        if (! $variant || ! $variant->is_active) {
            throw ValidationException::withMessages(['product_variant_id' => 'Choose an active variation for this product.']);
        }

        $attributes = $variant->attributeValues->map(fn ($value) => [
            'attribute' => $value->attribute->name,
            'value' => $value->label,
        ])->values()->all();

        return [
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'sku' => $variant->sku,
            'variant_name' => collect($attributes)->map(fn ($item) => $item['attribute'] . ': ' . $item['value'])->implode(' / '),
            'variant_attributes' => $attributes,
        ];
    }

    public function delete(OrderItem $orderItem): void
    {
        DB::transaction(function () use ($orderItem): void {
            if ($orderItem->vendor_order_id) {
                throw ValidationException::withMessages(['order_item' => 'Marketplace order items cannot be deleted directly.']);
            }
            $orderId = $orderItem->order_id;
            $orderItem->delete();
            $this->syncOrderTotals($orderId);
        });
    }

    public function restore(OrderItem $orderItem): void
    {
        DB::transaction(function () use ($orderItem): void {
            if ($orderItem->vendor_order_id) {
                throw ValidationException::withMessages(['order_item' => 'Marketplace order items cannot be restored directly.']);
            }
            $orderItem->restore();
            $this->syncOrderTotals($orderItem->order_id);
        });
    }

    public function forceDelete(OrderItem $orderItem): void
    {
        DB::transaction(function () use ($orderItem): void {
            if ($orderItem->vendor_order_id) {
                throw ValidationException::withMessages(['order_item' => 'Marketplace order items cannot be permanently deleted.']);
            }
            $orderId = $orderItem->order_id;
            $orderItem->forceDelete();
            $this->syncOrderTotals($orderId);
        });
    }

    /** @return array{processed:int, skipped:int} */
    public function bulk(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids): array {
            $processed = 0;
            $skipped = 0;
            $affectedOrderIds = [];

            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $orderItem = OrderItem::withTrashed()->find($id);

                if (! $orderItem) {
                    $skipped++;
                    continue;
                }

                $orderItem->loadMissing('order');
                $ability = match ($action) {
                    'delete' => 'delete',
                    'restore' => 'restore',
                    'force-delete' => 'forceDelete',
                    default => throw ValidationException::withMessages(['action' => 'Invalid bulk action.']),
                };

                $admin = auth('admin')->user();
                if (! $admin || ! $orderItem->order || Gate::forUser($admin)->denies($ability, $orderItem->order)) {
                    $skipped++;
                    continue;
                }

                if ($orderItem->vendor_order_id) {
                    $skipped++;
                    continue;
                }

                $affectedOrderIds[] = $orderItem->order_id;

                try {
                    match ($action) {
                        'delete' => $orderItem->trashed() ? null : $orderItem->delete(),
                        'restore' => $orderItem->trashed() ? $orderItem->restore() : null,
                        'force-delete' => $orderItem->trashed() ? $orderItem->forceDelete() : null,
                    };
                    $processed++;
                } catch (\Throwable) {
                    $skipped++;
                }
            }

            foreach (array_unique($affectedOrderIds) as $orderId) {
                $this->syncOrderTotals($orderId);
            }

            return compact('processed', 'skipped');
        });
    }

    public function syncOrderTotals(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if (! $order || $order->vendorOrders()->exists()) {
            return;
        }

        $newSubtotal = round((float) OrderItem::query()
            ->where('order_id', $orderId)
            ->sum('line_total'), 2);
        $discount = (float) $order->discount;
        $shipping = (float) $order->shipping;

        $order->update([
            'subtotal' => $newSubtotal,
            'grand_total' => max(0, round(($newSubtotal - $discount) + $shipping, 2)),
        ]);
    }
}
