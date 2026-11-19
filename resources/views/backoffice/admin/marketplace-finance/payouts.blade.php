@extends('layouts.admin')

@section('meta_title', 'Vendor Payouts')

@section('page_content')
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Payout requests</h3><div class="card-tools"><form method="GET"><select name="status" class="form-control form-control-sm" onchange="this.form.submit()"><option value="">All statuses</option>@foreach(['requested','approved','rejected','paid'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></form></div></div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0"><thead><tr><th>Vendor</th><th>Destination</th><th>Amount</th><th>Status</th><th>Requested</th><th style="min-width:260px">Action</th></tr></thead><tbody>
                @forelse($payouts as $payout)
                    <tr>
                        <td><strong>{{ $payout->vendor?->business_name }}</strong><small class="d-block text-muted">#{{ $payout->vendor_id }}</small></td>
                        <td>{{ strtoupper($payout->payoutAccount?->channel ?? '') }}<small class="d-block text-muted">{{ $payout->payoutAccount?->account_holder }}</small><small class="d-block text-muted">{{ $payout->payoutAccount?->account_number ? '•••• ' . substr($payout->payoutAccount->account_number, -4) : '—' }}</small></td>
                        <td><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $payout->amount, 2) }}</strong></td>
                        <td><span class="badge badge-{{ $payout->status === 'paid' ? 'success' : ($payout->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($payout->status) }}</span></td>
                        <td>{{ $payout->requested_at?->format('d M Y') }}</td>
                        <td>
                            @if($payout->status === 'requested')
                                <form method="POST" action="{{ route('admin.marketplace-finance.payouts.approve', $payout) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-xs btn-success">Approve</button></form>
                                <form method="POST" action="{{ route('admin.marketplace-finance.payouts.reject', $payout) }}" class="mt-2">@csrf @method('PATCH')<input class="form-control form-control-sm mb-1" name="review_note" placeholder="Reason for rejection" required><button class="btn btn-xs btn-outline-danger">Reject</button></form>
                            @elseif($payout->status === 'approved')
                                <form method="POST" action="{{ route('admin.marketplace-finance.payouts.paid', $payout) }}">@csrf @method('PATCH')<input class="form-control form-control-sm mb-1" name="transfer_reference" placeholder="Bank / MFS transfer reference" required><button class="btn btn-xs btn-primary">Confirm transfer paid</button></form>
                            @else
                                <small class="text-muted">{{ $payout->review_note ?? $payout->transactions->first()?->transfer_reference ?? '—' }}</small>
                            @endif
                        </td>
                    </tr>
                @empty<tr><td colspan="6" class="text-center text-muted py-4">No payout requests found.</td></tr>@endforelse
            </tbody></table>
        </div>
        @if($payouts->hasPages())<div class="card-footer bg-white">{{ $payouts->links() }}</div>@endif
    </div>

    <div class="card card-outline card-warning shadow-sm">
        <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Payout accounts awaiting verification</h3></div>
        <div class="card-body p-0 table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Vendor</th><th>Channel</th><th>Account holder</th><th>Account number</th><th>Action</th></tr></thead><tbody>
            @forelse($accounts as $account)
                <tr><td>{{ $account->vendor?->business_name }}</td><td>{{ strtoupper($account->channel) }}</td><td>{{ $account->account_holder }}</td><td>{{ $account->account_number ? '•••• ' . substr($account->account_number, -4) : '—' }}</td><td><form method="POST" action="{{ route('admin.marketplace-finance.accounts.verify', $account) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Verify account</button></form></td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-3">No pending payout accounts.</td></tr>@endforelse
        </tbody></table></div>
        @if($accounts->hasPages())<div class="card-footer bg-white">{{ $accounts->links() }}</div>@endif
    </div>
@endsection
