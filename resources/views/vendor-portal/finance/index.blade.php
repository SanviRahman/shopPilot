@extends('vendor-portal.layouts.app')

@section('title', 'Finance & payouts')
@section('breadcrumb_label', 'Finance')
@section('page_heading', 'Finance & payouts')
@section('page_description', 'Follow your seller earnings, verified payout account and transfer requests.')

@section('content')
    <div class="vp-stat-grid">
        @foreach(['pending' => 'Pending earnings', 'available' => 'Available balance', 'held' => 'Payouts on hold', 'paid_out' => 'Paid out'] as $key => $label)
            <section class="vp-stat-card"><span>{{ $label }}</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $balances[$key], 2) }}</strong></section>
        @endforeach
    </div>

    <div class="vp-order-detail-grid">
        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">PAYOUT DESTINATION</span><h2>Add payout account</h2><p>Account details are encrypted before they are stored. Admin verification is required before requesting a payout.</p></div></div>
            <form method="POST" action="{{ route('vendor.finance.accounts.store') }}" class="vp-form-grid">
                @csrf
                <label class="vp-field"><span>Channel</span><select name="channel" required><option value="">Select channel</option><option value="bank">Bank transfer</option><option value="bkash">bKash</option><option value="nagad">Nagad</option><option value="rocket">Rocket</option></select>@error('channel')<small>{{ $message }}</small>@enderror</label>
                <label class="vp-field"><span>Account holder</span><input name="account_holder" maxlength="150" required value="{{ old('account_holder') }}">@error('account_holder')<small>{{ $message }}</small>@enderror</label>
                <label class="vp-field"><span>Account number</span><input name="account_number" maxlength="150" required value="{{ old('account_number') }}">@error('account_number')<small>{{ $message }}</small>@enderror</label>
                <label class="vp-field"><span>Bank name (if applicable)</span><input name="bank_name" maxlength="150" value="{{ old('bank_name') }}"></label>
                <label class="vp-field"><span>Branch (if applicable)</span><input name="branch_name" maxlength="150" value="{{ old('branch_name') }}"></label>
                <div class="vp-form-actions vp-field-full"><button class="vp-button vp-button-primary" type="submit">Submit account for verification</button></div>
            </form>
            @if($accounts->isNotEmpty())
                <div class="vp-detail-list mt-3">
                    @foreach($accounts as $account)
                        <div><span>{{ strtoupper($account->channel) }} · {{ $account->account_holder }}</span><strong>{{ $account->status === 'verified' ? 'Verified' : 'Pending verification' }} @if($account->is_primary) · Primary @endif</strong></div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">REQUEST TRANSFER</span><h2>Request a payout</h2><p>Only verified payout accounts and available ledger balance can be used.</p></div></div>
            <form method="POST" action="{{ route('vendor.finance.payouts.store') }}" class="vp-form-grid">
                @csrf
                <label class="vp-field vp-field-full"><span>Verified payout account</span><select name="payout_account_id" required><option value="">Select account</option>@foreach($accounts->where('status', 'verified') as $account)<option value="{{ $account->id }}">{{ strtoupper($account->channel) }} · {{ $account->account_holder }}</option>@endforeach</select>@error('payout_account_id')<small>{{ $message }}</small>@enderror</label>
                <label class="vp-field vp-field-full"><span>Amount ({{ config('shop.marketplace.currency_symbol') }})</span><input type="number" name="amount" min="0.01" step="0.01" max="{{ min((float) config('shop.marketplace.maximum_payout_amount'), max(0, $balances['available'])) }}" required>@error('amount')<small>{{ $message }}</small>@enderror</label>
                <div class="vp-form-actions vp-field-full"><button class="vp-button vp-button-primary" type="submit" @disabled($accounts->where('status', 'verified')->isEmpty() || $balances['available'] <= 0)>Submit payout request</button></div>
            </form>
        </section>
    </div>

    <section class="vp-panel">
        <div class="vp-panel-heading"><div><span class="vp-section-kicker">PAYOUT HISTORY</span><h2>Your payout requests</h2></div></div>
        <div class="vp-table-wrap"><table class="vp-table"><thead><tr><th>Requested</th><th>Account</th><th>Amount</th><th>Status</th><th>Admin note</th></tr></thead><tbody>
            @forelse($payouts as $payout)
                <tr><td>{{ $payout->requested_at?->format('d M Y') }}</td><td>{{ strtoupper($payout->payoutAccount?->channel ?? '') }}</td><td>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $payout->amount, 2) }}</td><td><span class="vp-status-pill is-info">{{ ucfirst($payout->status) }}</span></td><td>{{ $payout->review_note ?? '—' }}</td></tr>
            @empty
                <tr><td colspan="5"><div class="vp-empty-state"><strong>No payout requests yet</strong><p>When your earnings become available, you can request a transfer here.</p></div></td></tr>
            @endforelse
        </tbody></table></div>
        @if($payouts->hasPages())<div class="vp-pagination">{{ $payouts->links() }}</div>@endif
    </section>

    <section class="vp-panel">
        <div class="vp-panel-heading"><div><span class="vp-section-kicker">ACCOUNT ACTIVITY</span><h2>Recent ledger entries</h2></div></div>
        <div class="vp-table-wrap"><table class="vp-table"><thead><tr><th>Date</th><th>Description</th><th>Bucket</th><th>Amount</th></tr></thead><tbody>
            @forelse($entries as $entry)
                <tr><td>{{ $entry->posted_at?->format('d M Y, h:i A') }}</td><td>{{ $entry->description ?? ucfirst(str_replace('_', ' ', $entry->entry_type)) }}</td><td>{{ ucfirst($entry->bucket) }}</td><td class="{{ (float) $entry->amount < 0 ? 'text-danger' : 'text-success' }}">{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $entry->amount, 2) }}</td></tr>
            @empty
                <tr><td colspan="4"><div class="vp-empty-state"><strong>No ledger activity yet</strong><p>Verified and delivered orders will appear here as your earnings are posted.</p></div></td></tr>
            @endforelse
        </tbody></table></div>
    </section>
@endsection
