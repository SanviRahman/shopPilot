<?php

namespace App\Services;

use App\Models\PayoutRequest;
use App\Models\Refund;
use App\Models\Vendor;
use App\Models\VendorFinancialLedger;
use App\Models\VendorOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarketplaceLedgerService
{
    public function balance(Vendor $vendor, string $bucket = 'available'): float
    {
        return (float) VendorFinancialLedger::query()
            ->where('vendor_id', $vendor->id)
            ->where('bucket', $bucket)
            ->where('currency_code', config('shop.marketplace.currency_code'))
            ->sum('amount');
    }

    public function postOrderEarning(VendorOrder $vendorOrder): void
    {
        if (! $vendorOrder->vendor_id || (float) $vendorOrder->vendor_earning <= 0) {
            return;
        }

        $this->post(
            vendor: $vendorOrder->vendor,
            type: 'order_earning',
            bucket: 'pending',
            amount: (float) $vendorOrder->vendor_earning,
            key: "vendor-order:{$vendorOrder->id}:earning:pending",
            vendorOrder: $vendorOrder,
            description: "Pending earning for {$vendorOrder->suborder_number}.",
        );
    }

    public function releaseOrderEarning(VendorOrder $vendorOrder): void
    {
        if (! $vendorOrder->vendor_id || (float) $vendorOrder->vendor_earning <= 0) {
            return;
        }

        $amount = (float) $vendorOrder->vendor_earning;
        $this->post(
            vendor: $vendorOrder->vendor,
            type: 'earning_release',
            bucket: 'pending',
            amount: -$amount,
            key: "vendor-order:{$vendorOrder->id}:earning-release:pending",
            vendorOrder: $vendorOrder,
            description: "Release pending earning for {$vendorOrder->suborder_number}.",
        );
        $this->post(
            vendor: $vendorOrder->vendor,
            type: 'earning_release',
            bucket: 'available',
            amount: $amount,
            key: "vendor-order:{$vendorOrder->id}:earning-release:available",
            vendorOrder: $vendorOrder,
            description: "Earning available for {$vendorOrder->suborder_number}.",
        );
    }

    public function holdPayout(PayoutRequest $payout): void
    {
        $vendor = $payout->vendor;
        $amount = (float) $payout->amount;

        if ($this->balance($vendor) < $amount) {
            throw ValidationException::withMessages(['amount' => 'The requested amount exceeds your available balance.']);
        }

        $this->post($vendor, 'payout_hold', 'available', -$amount, "payout:{$payout->id}:hold:available", payout: $payout, description: 'Funds held for payout request.');
        $this->post($vendor, 'payout_hold', 'held', $amount, "payout:{$payout->id}:hold:held", payout: $payout, description: 'Funds held for payout request.');
    }

    public function completePayout(PayoutRequest $payout): void
    {
        $amount = (float) $payout->amount;
        $this->post($payout->vendor, 'payout_paid', 'held', -$amount, "payout:{$payout->id}:paid:held", payout: $payout, description: 'Payout transfer confirmed.');
        $this->post($payout->vendor, 'payout_paid', 'paid_out', $amount, "payout:{$payout->id}:paid:paid-out", payout: $payout, description: 'Payout transfer confirmed.');
    }

    public function releasePayout(PayoutRequest $payout): void
    {
        $amount = (float) $payout->amount;
        $this->post($payout->vendor, 'payout_released', 'held', -$amount, "payout:{$payout->id}:released:held", payout: $payout, description: 'Payout hold released.');
        $this->post($payout->vendor, 'payout_released', 'available', $amount, "payout:{$payout->id}:released:available", payout: $payout, description: 'Payout hold released.');
    }

    public function debitRefund(Refund $refund): void
    {
        $vendorOrder = $refund->vendorOrder;
        if (! $vendorOrder->vendor_id) {
            return;
        }

        $basis = (float) $refund->refunded_amount;
        $commissionReversal = round($basis * ((float) $vendorOrder->commission_rate / 100), 2);
        $vendorDebit = round(max(0, $basis - $commissionReversal), 2);

        if ($vendorDebit <= 0) {
            return;
        }

        $this->post(
            vendor: $vendorOrder->vendor,
            type: 'refund_debit',
            bucket: 'available',
            amount: -$vendorDebit,
            key: "refund:{$refund->id}:vendor-debit",
            vendorOrder: $vendorOrder,
            refund: $refund,
            description: "Seller earning reversed for refund #{$refund->id}.",
            metadata: ['refund_basis' => $basis, 'commission_reversal' => $commissionReversal],
        );
    }

    private function post(
        Vendor $vendor,
        string $type,
        string $bucket,
        float $amount,
        string $key,
        ?VendorOrder $vendorOrder = null,
        ?PayoutRequest $payout = null,
        ?Refund $refund = null,
        ?string $description = null,
        array $metadata = [],
    ): VendorFinancialLedger {
        return VendorFinancialLedger::query()->firstOrCreate(
            ['idempotency_key' => $key],
            [
                'vendor_id' => $vendor->id,
                'vendor_order_id' => $vendorOrder?->id,
                'payout_request_id' => $payout?->id,
                'refund_id' => $refund?->id,
                'entry_type' => $type,
                'bucket' => $bucket,
                'amount' => $amount,
                'currency_code' => config('shop.marketplace.currency_code'),
                'reference_type' => $vendorOrder ? VendorOrder::class : ($payout ? PayoutRequest::class : ($refund ? Refund::class : null)),
                'reference_id' => $vendorOrder?->id ?? $payout?->id ?? $refund?->id,
                'description' => $description,
                'metadata' => $metadata ?: null,
                'posted_at' => now(),
            ],
        );
    }
}
