@extends('website.layouts.app')
@section('title', 'Payment Submissions | ShopPilot')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">@endpush
@section('content')
<section class="account-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.dashboard') }}">My Account</a><i class="fas fa-chevron-right"></i><strong>Payment Submissions</strong></div>
    <div class="account-layout">@include('website.account.partials.sidebar')<div class="account-main">
        <header class="account-page-heading" data-account-reveal><span>PAYMENTS</span><h1>Payment Submissions</h1><p>Track payment verification and resubmit payment details when an order requires action.</p></header>
        <div class="payment-tabs" data-account-reveal><a href="{{ route('website.account.payments') }}" class="{{ !$status ? 'active' : '' }}">All <b>{{ $allCount }}</b></a>@foreach(['submitted','verified','rejected'] as $value)<a href="{{ route('website.account.payments', ['status' => $value]) }}" class="{{ $status === $value ? 'active' : '' }}">{{ $value === 'submitted' ? 'Pending' : ucfirst($value) }} <b>{{ $statusCounts[$value] ?? 0 }}</b></a>@endforeach</div>

        <section class="account-panel payment-table-panel" data-account-reveal><div class="payment-table-scroll"><table class="payment-table"><thead><tr><th>ID</th><th>Order</th><th>Method</th><th>Transaction ID</th><th>Amount</th><th>Status</th><th>Submitted</th><th>Action</th></tr></thead><tbody>
        @forelse($payments as $payment)<tr><td>#PS-{{ str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT) }}</td><td><a href="{{ route('website.account.orders.show', $payment->order->order_number) }}">#{{ $payment->order->order_number }}</a></td><td>{{ $payment->paymentMethod?->name ?? 'Manual' }}</td><td><code>{{ $payment->transaction_id }}</code></td><td>৳{{ number_format((float) $payment->amount, 0) }}</td><td><span class="payment-pill payment-{{ $payment->status }}">{{ $payment->status === 'submitted' ? 'Pending' : ucfirst($payment->status) }}</span></td><td>{{ $payment->created_at?->format('M d, Y') }}</td><td><a class="mini-action" href="{{ route('website.account.orders.show', $payment->order->order_number) }}">View</a></td></tr>
        @empty<tr><td colspan="8"><div class="account-empty-mini">No payment submissions found for this filter.</div></td></tr>@endforelse
        </tbody></table></div></section>
        @if($payments->hasPages())<div class="account-pagination">{{ $payments->links() }}</div>@endif

        <section class="account-panel payment-submit-panel" id="submit-payment" data-account-reveal>
            <div class="panel-heading"><div><span>ACTION REQUIRED</span><h2>Submit Payment for Your Order</h2><p>Use this form only for unpaid or rejected orders. Verified/submitted payments cannot be overwritten.</p></div></div>
            @if($errors->any())<div class="payment-rejection-note"><i class="fas fa-exclamation-circle"></i><div><strong>Please check the payment form</strong><p>{{ $errors->first() }}</p></div></div>@endif
            @if($eligibleOrders->isNotEmpty() && $paymentMethods->isNotEmpty())
                <form method="POST" action="{{ route('website.account.payments.store') }}" class="payment-submit-form">@csrf
                    <label><span>Order Number *</span><select name="order_id" required data-payment-order>@foreach($eligibleOrders as $eligibleOrder)<option value="{{ $eligibleOrder->id }}" {{ (string) old('order_id', request('order')) === (string) $eligibleOrder->id ? 'selected' : '' }}>#{{ $eligibleOrder->order_number }} — ৳{{ number_format((float) $eligibleOrder->grand_total, 0) }} — {{ ucfirst($eligibleOrder->payment_status) }}</option>@endforeach</select></label>
                    <div class="payment-method-picker"><span>Payment Method *</span><div class="payment-method-grid">@foreach($paymentMethods as $method)<label class="payment-method-option"><input type="radio" name="payment_method_id" value="{{ $method->id }}" {{ (string) old('payment_method_id', $paymentMethods->first()?->id) === (string) $method->id ? 'checked' : '' }} required><span><b>{{ $method->name }}</b><small>{{ $method->account_number }}</small></span></label>@endforeach</div></div>
                    <label><span>Transaction ID *</span><input type="text" name="transaction_id" value="{{ old('transaction_id') }}" maxlength="100" placeholder="Enter your payment transaction ID" required></label>
                    <div class="payment-security-note"><i class="fas fa-info-circle"></i><span>The submitted amount is always taken from the Order total on the server. ShopPilot staff will verify your transaction before the payment becomes verified.</span></div>
                    <button class="primary-account-button" type="submit">Submit Payment <i class="fas fa-arrow-right"></i></button>
                </form>
            @elseif($eligibleOrders->isEmpty())
                <div class="account-empty-mini"><i class="fas fa-check-circle"></i> None of your orders currently require a new payment submission.</div>
            @else
                <div class="account-empty-mini">No active manual payment method is available right now.</div>
            @endif
        </section>
    </div></div>
</div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
