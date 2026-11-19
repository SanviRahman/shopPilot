<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarketplaceInventoryService
{
    public function consumeOrderReservations(Order $order): void
    {
        Product::withTrashed()->whereIn('id', $order->items()->pluck('product_id')->unique())->orderBy('id')->lockForUpdate()->get();

        $reservations = InventoryReservation::query()
            ->whereHas('orderItem', fn ($query) => $query->where('order_id', $order->id))
            ->where('status', 'active')
            ->with('orderItem')
            ->orderBy('inventory_balance_id')
            ->lockForUpdate()
            ->get();

        $expectedItems = $order->items()->count();
        if ($reservations->count() !== $expectedItems || $expectedItems === 0) {
            throw ValidationException::withMessages(['inventory' => 'This marketplace order does not have a complete active stock reservation. Payment cannot be verified until it is reviewed.']);
        }

        foreach ($reservations as $reservation) {
            $balance = InventoryBalance::query()->lockForUpdate()->findOrFail($reservation->inventory_balance_id);
            $quantity = (int) $reservation->quantity;

            if ($balance->reserved_quantity < $quantity || $balance->on_hand_quantity < $quantity) {
                throw ValidationException::withMessages(['inventory' => 'Reserved inventory is no longer available. Review this order before verifying payment.']);
            }

            $before = (int) $balance->on_hand_quantity;
            $balance->on_hand_quantity -= $quantity;
            $balance->reserved_quantity -= $quantity;
            $balance->save();

            $reservation->update(['status' => 'consumed', 'consumed_at' => now()]);

            InventoryMovement::create([
                'inventory_balance_id' => $balance->id,
                'product_id' => $reservation->product_id,
                'product_variant_id' => $reservation->product_variant_id,
                'movement_type' => 'sale',
                'quantity_change' => -$quantity,
                'on_hand_before' => $before,
                'on_hand_after' => (int) $balance->on_hand_quantity,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'note' => 'Reserved stock consumed after payment verification.',
            ]);
        }

        $this->syncProductStock($order);
    }

    public function releaseOrderReservations(Order $order, string $note): void
    {
        Product::withTrashed()->whereIn('id', $order->items()->pluck('product_id')->unique())->orderBy('id')->lockForUpdate()->get();

        $reservations = InventoryReservation::query()
            ->whereHas('orderItem', fn ($query) => $query->where('order_id', $order->id))
            ->where('status', 'active')
            ->with('orderItem')
            ->orderBy('inventory_balance_id')
            ->lockForUpdate()
            ->get();

        $expectedItems = $order->items()->count();
        if ($reservations->count() !== $expectedItems || $expectedItems === 0) {
            throw ValidationException::withMessages(['inventory' => 'This unpaid marketplace order does not have a complete active stock reservation. Review inventory before cancelling it.']);
        }

        foreach ($reservations as $reservation) {
            $balance = InventoryBalance::query()->lockForUpdate()->findOrFail($reservation->inventory_balance_id);
            $quantity = (int) $reservation->quantity;
            if ((int) $balance->reserved_quantity < $quantity) {
                throw ValidationException::withMessages(['inventory' => 'The reserved quantity is inconsistent. Review inventory before cancelling this order.']);
            }
            $balance->reserved_quantity -= $quantity;
            $balance->save();
            $reservation->update(['status' => 'released', 'released_at' => now()]);

            InventoryMovement::create([
                'inventory_balance_id' => $balance->id,
                'product_id' => $reservation->product_id,
                'product_variant_id' => $reservation->product_variant_id,
                'movement_type' => 'release',
                'quantity_change' => 0,
                'on_hand_before' => (int) $balance->on_hand_quantity,
                'on_hand_after' => (int) $balance->on_hand_quantity,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'note' => $note,
            ]);
        }

        $this->syncProductStock($order);
    }

    public function restockRefundItem(OrderItem $item, int $quantity, int $refundId): void
    {
        Product::withTrashed()->lockForUpdate()->findOrFail($item->product_id);

        $balance = InventoryBalance::query()
            ->where(function ($query) use ($item) {
                $item->product_variant_id
                    ? $query->where('product_variant_id', $item->product_variant_id)
                    : $query->where('product_id', $item->product_id);
            })
            ->lockForUpdate()
            ->first();

        if (! $balance) {
            $balance = InventoryBalance::create([
                'product_id' => $item->product_variant_id ? null : $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'on_hand_quantity' => 0,
                'reserved_quantity' => 0,
            ]);
        }

        $before = (int) $balance->on_hand_quantity;
        $balance->increment('on_hand_quantity', $quantity);

        InventoryMovement::create([
            'inventory_balance_id' => $balance->id,
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'movement_type' => 'return_restock',
            'quantity_change' => $quantity,
            'on_hand_before' => $before,
            'on_hand_after' => $before + $quantity,
            'reference_type' => 'refund',
            'reference_id' => $refundId,
            'note' => 'Returned item restocked after inspection.',
        ]);

        $this->syncProduct($item->product_id);
    }

    private function syncProductStock(Order $order): void
    {
        $order->items()->pluck('product_id')->unique()->each(fn ($id) => $this->syncProduct((int) $id));
    }

    private function syncProduct(int $productId): void
    {
        $product = Product::withTrashed()->find($productId);
        if (! $product) {
            return;
        }

        if ($product->product_type === 'simple') {
            $available = InventoryBalance::query()->where('product_id', $productId)->value('on_hand_quantity');
            $reserved = InventoryBalance::query()->where('product_id', $productId)->value('reserved_quantity');
            if ($available !== null) {
                $product->update(['stock_quantity' => max(0, (int) $available - (int) $reserved)]);
            }

            return;
        }

        $available = InventoryBalance::query()
            ->whereIn('product_variant_id', $product->variants()->where('is_active', true)->pluck('id'))
            ->selectRaw('COALESCE(SUM(on_hand_quantity - reserved_quantity), 0) as available')
            ->value('available');

        $product->update(['stock_quantity' => max(0, (int) $available)]);
    }
}
