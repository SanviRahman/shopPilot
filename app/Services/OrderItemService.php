<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderItemService
{
    public function create(array $data): OrderItem
    {
        return DB::transaction(function () use ($data): OrderItem {
            $unitPrice = (float) $data['unit_price'];
            $quantity = (int) $data['quantity'];
            $data['subtotal'] = round($unitPrice * $quantity, 2);

            $orderItem = OrderItem::create($data);

            $this->syncOrderTotals($orderItem->order_id);

            return $orderItem->load('order');
        });
    }

    public function update(OrderItem $orderItem, array $data): OrderItem
    {
        return DB::transaction(function () use ($orderItem, $data): OrderItem {
            $unitPrice = (float) ($data['unit_price'] ?? $orderItem->unit_price);
            $quantity = (int) ($data['quantity'] ?? $orderItem->quantity);
            $data['subtotal'] = round($unitPrice * $quantity, 2);

            $orderItem->update($data);

            $this->syncOrderTotals($orderItem->order_id);

            return $orderItem->refresh()->load('order');
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

                $affectedOrderIds[] = $orderItem->order_id;

                try {
                    match ($action) {
                        'delete'       => $orderItem->delete(),
                        'restore'      => $orderItem->restore(),
                        'force-delete' => $orderItem->forceDelete(),
                        default        => throw ValidationException::withMessages([
                            'action' => 'Invalid bulk action.',
                        ]),
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
     * Recalculates parent order subtotal and grand_total strictly
     */
    public function syncOrderTotals(int $orderId): void
    {
        $order = Order::find($orderId);
        if (! $order) {
            return;
        }

        $newSubtotal = (float) OrderItem::where('order_id', $orderId)->sum('subtotal');
        $discount = (float) $order->discount;
        $shipping = (float) $order->shipping;

        $newGrandTotal = max(0, round($newSubtotal - $discount + $shipping, 2));

        $order->update([
            'subtotal'    => $newSubtotal,
            'grand_total' => $newGrandTotal,
        ]);
    }
}