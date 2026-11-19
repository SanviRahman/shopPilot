<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRule;
use App\Models\PayoutRequest;
use App\Models\Refund;
use App\Models\VendorOrder;
use App\Models\VendorPayoutAccount;
use App\Services\MarketplaceOrderService;
use App\Services\MarketplacePayoutService;
use App\Services\MarketplaceRefundService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarketplaceFinanceController extends Controller
{
    public function __construct(
        private readonly MarketplacePayoutService $payoutService,
        private readonly MarketplaceRefundService $refundService,
        private readonly MarketplaceOrderService $orderService,
    ) {
    }

    public function index(): View
    {
        $this->authorizeAction('marketplace-finance.view');
        $rule = CommissionRule::activeMarketplaceRule();

        return view('backoffice.admin.marketplace-finance.index', [
            'title' => 'Marketplace Finance',
            'breadcrumb' => [['text' => 'Marketplace Finance', 'url' => null]],
            'rule' => $rule,
            'pendingPayouts' => PayoutRequest::query()->whereIn('status', ['requested', 'approved'])->count(),
            'pendingRefunds' => Refund::query()->whereIn('status', ['requested', 'approved'])->count(),
            'verifiedAccounts' => VendorPayoutAccount::query()->where('status', 'verified')->count(),
        ]);
    }

    public function updateCommission(Request $request): RedirectResponse
    {
        $this->authorizeAction('marketplace-finance.manage');
        $data = $request->validate(['rate_value' => ['required', 'numeric', 'min:0', 'max:100']]);

        DB::transaction(function () use ($data): void {
            CommissionRule::query()
                ->where('scope_type', 'marketplace')
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->each(fn (CommissionRule $rule) => $rule->update(['is_active' => false, 'effective_to' => now()]));

            CommissionRule::create([
                'scope_type' => 'marketplace',
                'rate_value' => round((float) $data['rate_value'], 4),
                'commission_basis' => 'net_merchandise',
                'effective_from' => now(),
                'is_active' => true,
                'created_by_admin_id' => auth('admin')->id(),
            ]);
        });

        return back()->with('success', 'Marketplace commission rule updated. New orders will use this rate.');
    }

    public function verifyPayoutAccount(VendorPayoutAccount $account): RedirectResponse
    {
        $this->authorizeAction('marketplace-finance.manage');
        if ($account->status !== 'pending') {
            return back()->withErrors(['account' => 'Only pending payout accounts can be verified.']);
        }

        $account->update([
            'status' => 'verified',
            'verified_by_admin_id' => auth('admin')->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Vendor payout account verified.');
    }

    public function sellerOrders(): View
    {
        $this->authorizeAction('marketplace-finance.view');
        $sellerOrders = VendorOrder::query()
            ->with(['order', 'vendor.store'])
            ->when(request('status'), fn ($query) => $query->where('status', request('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backoffice.admin.marketplace-finance.seller-orders', [
            'title' => 'Seller Orders',
            'breadcrumb' => [['text' => 'Marketplace Finance', 'url' => route('admin.marketplace-finance.index')], ['text' => 'Seller Orders', 'url' => null]],
            'sellerOrders' => $sellerOrders,
        ]);
    }

    public function payouts(): View
    {
        $this->authorizeAction('marketplace-finance.view');
        $payouts = PayoutRequest::query()
            ->with(['vendor', 'payoutAccount', 'reviewer', 'transactions'])
            ->when(request('status'), fn ($query) => $query->where('status', request('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $accounts = VendorPayoutAccount::query()->with('vendor')->where('status', 'pending')->latest()->paginate(10, ['*'], 'accounts_page');

        return view('backoffice.admin.marketplace-finance.payouts', [
            'title' => 'Vendor Payouts',
            'breadcrumb' => [['text' => 'Marketplace Finance', 'url' => route('admin.marketplace-finance.index')], ['text' => 'Payouts', 'url' => null]],
            'payouts' => $payouts,
            'accounts' => $accounts,
        ]);
    }

    public function approvePayout(PayoutRequest $payout): RedirectResponse
    {
        $this->authorizeAction('marketplace-finance.manage');
        $this->payoutService->approve($payout, (int) auth('admin')->id());

        return back()->with('success', 'Payout request approved.');
    }

    public function rejectPayout(Request $request, PayoutRequest $payout): RedirectResponse
    {
        $this->authorizeAction('marketplace-finance.manage');
        $data = $request->validate(['review_note' => ['required', 'string', 'max:1000']]);
        $this->payoutService->reject($payout, (int) auth('admin')->id(), $data['review_note']);

        return back()->with('success', 'Payout request rejected and held balance released.');
    }

    public function markPayoutPaid(Request $request, PayoutRequest $payout): RedirectResponse
    {
        $this->authorizeAction('marketplace-finance.manage');
        $data = $request->validate(['transfer_reference' => ['required', 'string', 'max:120']]);
        $this->payoutService->markPaid($payout, (int) auth('admin')->id(), $data['transfer_reference']);

        return back()->with('success', 'Payout marked as paid and recorded in the ledger.');
    }

    public function refunds(): View
    {
        $this->authorizeAction('marketplace-finance.view');
        $refunds = Refund::query()
            ->with(['order', 'vendorOrder.vendor', 'user', 'items.orderItem'])
            ->when(request('status'), fn ($query) => $query->where('status', request('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backoffice.admin.marketplace-finance.refunds', [
            'title' => 'Marketplace Refunds',
            'breadcrumb' => [['text' => 'Marketplace Finance', 'url' => route('admin.marketplace-finance.index')], ['text' => 'Refunds', 'url' => null]],
            'refunds' => $refunds,
        ]);
    }

    public function approveRefund(Request $request, Refund $refund): RedirectResponse
    {
        $this->authorizeAction('marketplace-finance.manage');
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:1000']]);
        $this->refundService->approve($refund, (int) auth('admin')->id(), $data['admin_note'] ?? null);

        return back()->with('success', 'Refund approved. Record the manual transfer after it is completed.');
    }

    public function rejectRefund(Request $request, Refund $refund): RedirectResponse
    {
        $this->authorizeAction('marketplace-finance.manage');
        $data = $request->validate(['admin_note' => ['required', 'string', 'max:1000']]);
        $this->refundService->reject($refund, (int) auth('admin')->id(), $data['admin_note']);

        return back()->with('success', 'Refund request rejected.');
    }

    public function completeRefund(Request $request, Refund $refund): RedirectResponse
    {
        $this->authorizeAction('marketplace-finance.manage');
        $data = $request->validate([
            'transfer_reference' => ['required', 'string', 'max:120'],
            'restock_items' => ['nullable', 'boolean'],
        ]);
        $this->refundService->complete(
            $refund,
            (int) auth('admin')->id(),
            $data['transfer_reference'],
            (bool) ($data['restock_items'] ?? false),
        );

        return back()->with('success', 'Refund transfer recorded and ledger updated.');
    }

    public function markDelivered(VendorOrder $vendorOrder): RedirectResponse
    {
        $this->authorizeAction('marketplace-finance.manage');
        $this->orderService->markDelivered($vendorOrder);

        return back()->with('success', 'Seller order marked as delivered and earnings released.');
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
