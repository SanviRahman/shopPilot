<?php

namespace App\Services;

use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Vendor;
use App\Models\VendorOrder;
use App\Models\VendorOrderHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarketplaceOrderService
{
    public function __construct(
        private readonly MarketplaceInventoryService $inventoryService,
        private readonly MarketplaceLedgerService $ledgerService,
    ) {
    }

    public function paymentVerified(Order $order): void
    {
        if ($order->order_status === Order::STATUS_CANCELLED) {
            throw ValidationException::withMessages(['payment' => 'A cancelled order cannot be payment-verified.']);
        }

        $vendorOrders = $order->vendorOrders()->lockForUpdate()->get();
        if ($vendorOrders->isEmpty()) {
            return;
        }

        $this->inventoryService->consumeOrderReservations($order);

        foreach ($vendorOrders as $vendorOrder) {
            $previousStatus = $vendorOrder->status;
            $nextStatus = $previousStatus === VendorOrder::STATUS_AWAITING_PAYMENT
                ? VendorOrder::STATUS_CONFIRMED
                : $previousStatus;
            $vendorOrder->update([
                'payment_status' => Order::PAYMENT_VERIFIED,
                'status' => $nextStatus,
            ]);
            if ($previousStatus !== $nextStatus) {
                $this->recordHistory($vendorOrder, $previousStatus, $nextStatus, 'Payment verified. Seller fulfilment may begin.');
            }
            $vendorOrder->refresh();
            $vendorOrder->loadMissing('vendor');
            $this->ledgerService->postOrderEarning($vendorOrder);
        }

        $this->syncMasterOrder($order);
    }

    public function paymentRejected(Order $order): void
    {
        foreach ($order->vendorOrders as $vendorOrder) {
            $vendorOrder->update(['payment_status' => Order::PAYMENT_REJECTED]);
            $this->recordHistory($vendorOrder, null, null, 'Payment submission rejected. Stock remains reserved until payment is resubmitted or the order is cancelled.');
        }
    }

    public function cancelUnpaidOrder(Order $order, string $reason): void
    {
        if ($order->payment_status === Order::PAYMENT_VERIFIED) {
            throw ValidationException::withMessages([
                'order_status' => 'A paid marketplace order must be handled through the refund workflow.',
            ]);
        }

        $this->inventoryService->releaseOrderReservations($order, 'Stock reservation released because the order was cancelled.');
        foreach ($order->vendorOrders as $vendorOrder) {
            $previousStatus = $vendorOrder->status;
            $vendorOrder->update([
                'status' => VendorOrder::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);
            $this->recordHistory($vendorOrder, $previousStatus, VendorOrder::STATUS_CANCELLED, $reason, auth('admin')->id());
        }
    }

    public function updateVendorFulfilment(Vendor $vendor, VendorOrder $vendorOrder, array $data): VendorOrder
    {
        return DB::transaction(function () use ($vendor, $vendorOrder, $data): VendorOrder {
            $order = Order::query()->lockForUpdate()->findOrFail($vendorOrder->order_id);
            $vendorOrder = VendorOrder::query()
                ->whereKey($vendorOrder->id)
                ->where('vendor_id', $vendor->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($vendorOrder->payment_status !== Order::PAYMENT_VERIFIED) {
                throw ValidationException::withMessages(['status' => 'Payment must be verified before fulfilment starts.']);
            }

            $status = (string) $data['status'];
            if ($status === $vendorOrder->status
                || ! in_array($status, [VendorOrder::STATUS_PROCESSING, VendorOrder::STATUS_READY_TO_SHIP, VendorOrder::STATUS_SHIPPED], true)
                || ! $vendorOrder->canTransitionTo($status)) {
                throw ValidationException::withMessages(['status' => 'This fulfilment status transition is not allowed.']);
            }

            $previousStatus = $vendorOrder->status;
            $payload = ['status' => $status];
            if ($status === VendorOrder::STATUS_SHIPPED) {
                $payload += [
                    'shipping_provider' => $data['shipping_provider'],
                    'tracking_number' => $data['tracking_number'],
                    'tracking_url' => $data['tracking_url'] ?? null,
                    'shipped_at' => now(),
                ];
            }

            $vendorOrder->update($payload);
            $this->recordHistory($vendorOrder, $previousStatus, $status, $status === VendorOrder::STATUS_SHIPPED ? 'Seller marked the order as shipped.' : 'Seller updated fulfilment status.', null, auth('web')->id());
            $this->syncMasterOrder($order);

            return $vendorOrder->refresh()->load(['order', 'items']);
        });
    }

    public function markDelivered(VendorOrder $vendorOrder): VendorOrder
    {
        return DB::transaction(function () use ($vendorOrder): VendorOrder {
            $order = Order::query()->lockForUpdate()->findOrFail($vendorOrder->order_id);
            $vendorOrder = VendorOrder::query()->lockForUpdate()->findOrFail($vendorOrder->id);
            if ($vendorOrder->vendor_id) {
                Vendor::withTrashed()->lockForUpdate()->findOrFail($vendorOrder->vendor_id);
            }

            if ($vendorOrder->status !== VendorOrder::STATUS_SHIPPED) {
                throw ValidationException::withMessages(['status' => 'Only shipped orders can be marked as delivered.']);
            }

            $previousStatus = $vendorOrder->status;
            $vendorOrder->update([
                'status' => VendorOrder::STATUS_DELIVERED,
                'delivered_at' => now(),
            ]);
            $this->recordHistory($vendorOrder, $previousStatus, VendorOrder::STATUS_DELIVERED, 'Marketplace administration confirmed delivery.', auth('admin')->id());

            $vendorOrder->loadMissing('vendor');
            $this->ledgerService->releaseOrderEarning($vendorOrder);
            $this->syncMasterOrder($order);

            return $vendorOrder->refresh();
        });
    }

    public function createRefundRequest(Order $order, VendorOrder $vendorOrder, array $items, string $reason, ?int $userId): Refund
    {
        return DB::transaction(function () use ($order, $vendorOrder, $items, $reason, $userId): Refund {
            $vendorOrder = VendorOrder::query()
                ->whereKey($vendorOrder->id)
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($vendorOrder->status !== VendorOrder::STATUS_DELIVERED || $vendorOrder->payment_status !== Order::PAYMENT_VERIFIED) {
                throw ValidationException::withMessages(['refund' => 'A refund can be requested only for a delivered and paid seller order.']);
            }

            $requested = [];
            $amount = 0.0;
            $itemIds = collect($items)->pluck('order_item_id')->map(fn ($id) => (int) $id);
            if ($itemIds->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages(['items' => 'Each item can only appear once in a refund request.']);
            }

            foreach ($items as $row) {
                $item = $vendorOrder->items()->whereKey((int) $row['order_item_id'])->lockForUpdate()->firstOrFail();
                $quantity = (int) $row['quantity'];
                $previousRefundItems = RefundItem::query()
                    ->where('order_item_id', $item->id)
                    ->whereHas('refund', fn ($query) => $query->whereIn('status', ['requested', 'approved', 'succeeded']));
                $alreadyRequested = (int) (clone $previousRefundItems)->sum('quantity');
                $alreadyRequestedAmount = (float) (clone $previousRefundItems)->sum('amount');
                $available = (int) $item->quantity - $alreadyRequested;

                if ($quantity < 1 || $quantity > $available) {
                    throw ValidationException::withMessages(['items' => "The requested quantity for {$item->product_name} is not available for refund."]);
                }

                $netTotal = round((float) $item->line_total - (float) $item->discount_amount, 2);
                $unitNet = $netTotal / max(1, (int) $item->quantity);
                $lineAmount = $quantity === $available
                    ? round($netTotal - $alreadyRequestedAmount, 2)
                    : round($unitNet * $quantity, 2);
                $requested[] = ['item' => $item, 'quantity' => $quantity, 'amount' => $lineAmount];
                $amount += $lineAmount;
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages(['items' => 'Select at least one refundable item.']);
            }

            $refund = Refund::create([
                'order_id' => $order->id,
                'vendor_order_id' => $vendorOrder->id,
                'user_id' => $userId,
                'requested_amount' => round($amount, 2),
                'approved_amount' => 0,
                'refunded_amount' => 0,
                'currency_code' => config('shop.marketplace.currency_code'),
                'status' => 'requested',
                'reason' => trim($reason),
            ]);

            foreach ($requested as $row) {
                RefundItem::create([
                    'refund_id' => $refund->id,
                    'order_item_id' => $row['item']->id,
                    'quantity' => $row['quantity'],
                    'amount' => $row['amount'],
                ]);
            }

            return $refund->load('items.orderItem', 'vendorOrder');
        });
    }

    public function syncMasterOrder(Order $order): void
    {
        $order = Order::query()->lockForUpdate()->findOrFail($order->id);
        $statuses = $order->vendorOrders()->pluck('status');
        if ($statuses->isEmpty()) {
            return;
        }

        $activeStatuses = $statuses->reject(fn ($status) => $status === VendorOrder::STATUS_CANCELLED);
        $next = match (true) {
            $activeStatuses->isEmpty() => Order::STATUS_CANCELLED,
            $activeStatuses->every(fn ($status) => $status === VendorOrder::STATUS_DELIVERED) => Order::STATUS_DELIVERED,
            $activeStatuses->every(fn ($status) => in_array($status, [VendorOrder::STATUS_SHIPPED, VendorOrder::STATUS_DELIVERED], true)) => Order::STATUS_SHIPPED,
            $activeStatuses->contains(fn ($status) => in_array($status, [VendorOrder::STATUS_PROCESSING, VendorOrder::STATUS_READY_TO_SHIP, VendorOrder::STATUS_SHIPPED, VendorOrder::STATUS_DELIVERED], true)) => Order::STATUS_PROCESSING,
            $activeStatuses->contains(VendorOrder::STATUS_CONFIRMED) => Order::STATUS_CONFIRMED,
            default => Order::STATUS_PENDING,
        };

        if ($order->order_status !== $next) {
            $previous = $order->order_status;
            $order->update(['order_status' => $next]);
            app(OrderHistoryService::class)->recordSystemEvent(
                order: $order,
                fromStatus: $previous,
                toStatus: $next,
                note: 'Master order status updated from seller fulfilment progress.',
            );
        }
    }

    private function recordHistory(
        VendorOrder $vendorOrder,
        ?string $fromStatus,
        ?string $toStatus,
        string $note,
        ?int $adminId = null,
        ?int $userId = null,
    ): void {
        VendorOrderHistory::create([
            'vendor_order_id' => $vendorOrder->id,
            'admin_id' => $adminId,
            'user_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
        ]);
    }

}
