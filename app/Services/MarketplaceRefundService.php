<?php

namespace App\Services;

use App\Models\Refund;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarketplaceRefundService
{
    public function __construct(
        private readonly MarketplaceLedgerService $ledgerService,
        private readonly MarketplaceInventoryService $inventoryService,
    ) {
    }

    public function approve(Refund $refund, int $adminId, ?string $note = null): Refund
    {
        return DB::transaction(function () use ($refund, $adminId, $note): Refund {
            $refund = Refund::query()->lockForUpdate()->findOrFail($refund->id);
            if ($refund->status !== 'requested') {
                throw ValidationException::withMessages(['status' => 'Only requested refunds can be approved.']);
            }

            $refund->update([
                'status' => 'approved',
                'approved_amount' => $refund->requested_amount,
                'processed_by_admin_id' => $adminId,
                'admin_note' => $note,
            ]);

            return $refund->refresh();
        });
    }

    public function reject(Refund $refund, int $adminId, string $note): Refund
    {
        return DB::transaction(function () use ($refund, $adminId, $note): Refund {
            $refund = Refund::query()->lockForUpdate()->findOrFail($refund->id);
            if (! in_array($refund->status, ['requested', 'approved'], true)) {
                throw ValidationException::withMessages(['status' => 'This refund can no longer be rejected.']);
            }

            $refund->update([
                'status' => 'rejected',
                'processed_by_admin_id' => $adminId,
                'processed_at' => now(),
                'admin_note' => trim($note),
            ]);

            return $refund->refresh();
        });
    }

    public function complete(Refund $refund, int $adminId, string $reference, bool $restock): Refund
    {
        return DB::transaction(function () use ($refund, $adminId, $reference, $restock): Refund {
            $refund = Refund::query()->with('items.orderItem', 'vendorOrder.vendor')->lockForUpdate()->findOrFail($refund->id);
            if ($refund->status !== 'approved') {
                throw ValidationException::withMessages(['status' => 'Only approved refunds can be completed.']);
            }

            $refund->update([
                'status' => 'succeeded',
                'refunded_amount' => $refund->approved_amount,
                'restock_items' => $restock,
                'transfer_reference' => trim($reference),
                'processed_by_admin_id' => $adminId,
                'processed_at' => now(),
            ]);

            $refund->refresh();
            if ($refund->vendorOrder->vendor_id) {
                Vendor::withTrashed()->lockForUpdate()->findOrFail($refund->vendorOrder->vendor_id);
            }
            if ($restock) {
                foreach ($refund->items as $item) {
                    $this->inventoryService->restockRefundItem($item->orderItem, (int) $item->quantity, $refund->id);
                }
            }

            $this->ledgerService->debitRefund($refund->load('vendorOrder.vendor'));

            return $refund->refresh();
        });
    }
}
