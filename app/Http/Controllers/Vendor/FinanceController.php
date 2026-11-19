<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\VendorFinancialLedger;
use App\Models\VendorPayoutAccount;
use App\Services\MarketplaceLedgerService;
use App\Services\MarketplacePayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __construct(
        private readonly MarketplaceLedgerService $ledgerService,
        private readonly MarketplacePayoutService $payoutService,
    ) {
    }

    public function index(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');
        $accounts = $vendor->payoutAccounts()->latest()->get();
        $payouts = $vendor->payoutRequests()->with('payoutAccount')->latest()->paginate(10);
        $entries = VendorFinancialLedger::query()->where('vendor_id', $vendor->id)->latest('posted_at')->limit(20)->get();

        return view('vendor-portal.finance.index', [
            'vendor' => $vendor,
            'accounts' => $accounts,
            'payouts' => $payouts,
            'entries' => $entries,
            'balances' => [
                'pending' => $this->ledgerService->balance($vendor, 'pending'),
                'available' => $this->ledgerService->balance($vendor, 'available'),
                'held' => $this->ledgerService->balance($vendor, 'held'),
                'paid_out' => $this->ledgerService->balance($vendor, 'paid_out'),
            ],
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'channel' => ['required', Rule::in(['bank', 'bkash', 'nagad', 'rocket'])],
            'account_holder' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:150'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'branch_name' => ['nullable', 'string', 'max:150'],
        ]);

        DB::transaction(function () use ($vendor, $data): void {
            $vendor->payoutAccounts()->update(['is_primary' => false]);
            $vendor->payoutAccounts()->create([
                ...$data,
                'status' => 'pending',
                'is_primary' => true,
            ]);
        });

        return back()->with('success', 'Payout account submitted for verification.');
    }

    public function requestPayout(Request $request): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'payout_account_id' => ['required', 'integer', Rule::exists('vendor_payout_accounts', 'id')->where(fn ($query) => $query->where('vendor_id', $vendor->id)->where('status', 'verified'))],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . config('shop.marketplace.maximum_payout_amount')],
        ]);

        $account = VendorPayoutAccount::query()->where('vendor_id', $vendor->id)->findOrFail($data['payout_account_id']);
        $this->payoutService->request($vendor, $account, (float) $data['amount']);

        return back()->with('success', 'Payout request submitted successfully.');
    }
}
