<?php

namespace App\Services;

use App\Models\PayoutRequest;
use App\Models\PayoutTransaction;
use App\Models\Vendor;
use App\Models\VendorPayoutAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarketplacePayoutService
{
    public function __construct(private readonly MarketplaceLedgerService $ledgerService)
    {
    }

    public function request(Vendor $vendor, VendorPayoutAccount $account, float $amount): PayoutRequest
    {
        return DB::transaction(function () use ($vendor, $account, $amount): PayoutRequest {
            $vendor = Vendor::withTrashed()->lockForUpdate()->findOrFail($vendor->id);
            $amount = round($amount, 2);
            $account = VendorPayoutAccount::query()
                ->whereKey($account->id)
                ->where('vendor_id', $vendor->id)
                ->where('status', 'verified')
                ->lockForUpdate()
                ->firstOrFail();

            if ($amount <= 0
                || $amount < (float) config('shop.marketplace.minimum_payout_amount', 0)
                || $amount > (float) config('shop.marketplace.maximum_payout_amount', 9999999999)) {
                throw ValidationException::withMessages(['amount' => 'Enter a valid payout amount.']);
            }

            $payout = PayoutRequest::create([
                'vendor_id' => $vendor->id,
                'payout_account_id' => $account->id,
                'amount' => round($amount, 2),
                'currency_code' => config('shop.marketplace.currency_code'),
                'status' => 'requested',
                'requested_at' => now(),
            ]);

            $this->ledgerService->holdPayout($payout->load('vendor'));

            return $payout->load('payoutAccount');
        });
    }

    public function approve(PayoutRequest $payout, int $adminId): PayoutRequest
    {
        return DB::transaction(function () use ($payout, $adminId): PayoutRequest {
            $payout = PayoutRequest::query()->lockForUpdate()->findOrFail($payout->id);
            if ($payout->status !== 'requested') {
                throw ValidationException::withMessages(['status' => 'Only requested payouts can be approved.']);
            }

            $payout->update(['status' => 'approved', 'reviewed_by_admin_id' => $adminId, 'reviewed_at' => now()]);

            return $payout->refresh();
        });
    }

    public function reject(PayoutRequest $payout, int $adminId, string $note): PayoutRequest
    {
        return DB::transaction(function () use ($payout, $adminId, $note): PayoutRequest {
            $payout = PayoutRequest::query()->lockForUpdate()->findOrFail($payout->id);
            Vendor::withTrashed()->lockForUpdate()->findOrFail($payout->vendor_id);
            if (! in_array($payout->status, ['requested', 'approved'], true)) {
                throw ValidationException::withMessages(['status' => 'This payout can no longer be rejected.']);
            }

            $payout->load('vendor');
            $this->ledgerService->releasePayout($payout);
            $payout->update([
                'status' => 'rejected',
                'reviewed_by_admin_id' => $adminId,
                'reviewed_at' => now(),
                'review_note' => trim($note),
            ]);

            return $payout->refresh();
        });
    }

    public function markPaid(PayoutRequest $payout, int $adminId, string $reference): PayoutTransaction
    {
        return DB::transaction(function () use ($payout, $adminId, $reference): PayoutTransaction {
            $payout = PayoutRequest::query()->lockForUpdate()->with('payoutAccount', 'vendor')->findOrFail($payout->id);
            Vendor::withTrashed()->lockForUpdate()->findOrFail($payout->vendor_id);
            if ($payout->status !== 'approved') {
                throw ValidationException::withMessages(['status' => 'Only approved payouts can be marked as paid.']);
            }

            $transaction = PayoutTransaction::create([
                'payout_request_id' => $payout->id,
                'amount' => $payout->amount,
                'channel' => $payout->payoutAccount->channel,
                'transfer_reference' => trim($reference),
                'status' => 'paid',
                'processed_by_admin_id' => $adminId,
                'processed_at' => now(),
            ]);

            $this->ledgerService->completePayout($payout);
            $payout->update(['status' => 'paid']);

            return $transaction;
        });
    }
}
