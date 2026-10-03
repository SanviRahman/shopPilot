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
            $product = Product::query()->findOrFail((int) $data['product_id']);
            $unitPrice = round((float) $data['unit_price'], 2);
            $quantity = (int) $data['quantity'];

            $orderItem = OrderItem::create([
                'order_id' => (int) $data['order_id'],
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => round($unitPrice * $quantity, 2),
            ]);

            $this->syncOrderTotals($orderItem->order_id);

            return $orderItem->load(['order', 'product']);
        });
    }

    public function update(OrderItem $orderItem, array $data): OrderItem
    {
        return DB::transaction(function () use ($orderItem, $data): OrderItem {
            $productId = (int) $data['product_id'];
            $unitPrice = round((float) $data['unit_price'], 2);
            $quantity = (int) $data['quantity'];

            $snapshot = [
                'product_id' => $orderItem->product_id,
                'product_name' => $orderItem->product_name,
                'sku' => $orderItem->sku,
            ];

            if ($productId !== (int) $orderItem->product_id) {
                $product = Product::query()->findOrFail($productId);
                $snapshot = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                ];
            }

            $orderItem->update([
                ...$snapshot,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => round($unitPrice * $quantity, 2),
            ]);

            $this->syncOrderTotals($orderItem->order_id);

            return $orderItem->refresh()->load(['order', 'product']);
        });
    }

    public function delete(OrderItem $orderItem): void
    {
        DB::transaction(function () use ($orderItem): void {
            $orderId = $orderItem->order_id;
            $orderItem->delete();
            $this->syncOrderTotals($orderId);
        });
    }

    public function restore(OrderItem $orderItem): void
    {
        DB::transaction(function () use ($orderItem): void {
            $orderItem->restore();
            $this->syncOrderTotals($orderItem->order_id);
        });
    }

    public function forceDelete(OrderItem $orderItem): void
    {
        DB::transaction(function () use ($orderItem): void {
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
                    default => throw ValidationException::withMessages([
                        'action' => 'Invalid bulk action.',
                    ]),
                };

                $admin = auth('admin')->user();
                if (! $admin || ! $orderItem->order || Gate::forUser($admin)->denies($ability, $orderItem->order)) {
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

    /**
     * Keep the parent Order commercial totals synchronized with active OrderItems.
     */
    public function syncOrderTotals(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
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
