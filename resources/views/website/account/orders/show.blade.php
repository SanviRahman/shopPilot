@extends('website.layouts.app')
@section('title', 'Order Details | ShopPilot')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">@endpush
@section('content')
<section class="account-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.dashboard') }}">My Account</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.orders') }}">My Orders</a><i class="fas fa-chevron-right"></i><strong>Order Details</strong></div>
    <div class="account-layout">@include('website.account.partials.sidebar')<div class="account-main">
        <header class="account-page-heading order-detail-heading" data-account-reveal><div><span>ORDER DETAILS</span><h1>#{{ $order->order_number }}</h1><p>Placed on {{ $order->created_at?->format('M d, Y \a\t h:i A') }}</p></div><span class="status-pill status-{{ $order->order_status }}">{{ ucfirst($order->order_status) }}</span></header>

        <div class="order-detail-top-grid">
            <section class="account-panel" data-account-reveal><div class="panel-heading"><div><span>SUMMARY</span><h2>Order Information</h2></div></div>
                <dl class="detail-list">
                    <div><dt>Order Number</dt><dd>#{{ $order->order_number }}</dd></div>
                    <div><dt>Order Date</dt><dd>{{ $order->created_at?->format('M d, Y h:i A') }}</dd></div>
                    <div><dt>Order Status</dt><dd><span class="status-pill status-{{ $order->order_status }}">{{ ucfirst($order->order_status) }}</span></dd></div>
                    <div><dt>Payment Status</dt><dd><span class="payment-pill payment-{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span></dd></div>
                    <div><dt>Total Amount</dt><dd><strong class="money-highlight">৳{{ number_format((float) $order->grand_total, 0) }}</strong></dd></div>
                </dl>
            </section>
            <section class="account-panel" data-account-reveal><div class="panel-heading"><div><span>DELIVERY</span><h2>Delivery Address</h2></div></div>
                <div class="delivery-card"><i class="fas fa-map-marker-alt"></i><div><strong>{{ $order->buyer_name }}</strong><span>{{ $order->buyer_phone }}</span><span>{{ $order->shipping_address }}</span><span>{{ $order->city_or_area }}</span></div><b>Home</b></div>
            </section>
        </div>

        <section class="account-panel payment-highlight-panel" data-account-reveal>
            <div class="panel-heading"><div><span>PAYMENT</span><h2>Payment Information</h2></div>
                @if(in_array($order->payment_status, [\App\Models\Order::PAYMENT_UNPAID, \App\Models\Order::PAYMENT_REJECTED], true))<a href="{{ route('website.account.payments', ['order' => $order->id]) }}">Submit Payment</a>@endif
            </div>
            @if($order->paymentSubmission)
                <div class="payment-detail-grid">
                    <div><span>Method</span><strong>{{ $order->paymentSubmission->paymentMethod?->name ?? 'Manual Payment' }}</strong></div>
                    <div><span>Transaction ID</span><strong>{{ $order->paymentSubmission->transaction_id }}</strong></div>
                    <div><span>Amount</span><strong>৳{{ number_format((float) $order->paymentSubmission->amount, 0) }}</strong></div>
                    <div><span>Status</span><strong class="payment-pill payment-{{ $order->paymentSubmission->status }}">{{ ucfirst($order->paymentSubmission->status) }}</strong></div>
                </div>
                @if($order->paymentSubmission->rejection_note)<div class="payment-rejection-note"><i class="fas fa-exclamation-triangle"></i><div><strong>Payment was rejected</strong><p>{{ $order->paymentSubmission->rejection_note }}</p></div></div>@endif
            @else
                <div class="account-empty-mini">No payment submission has been recorded for this order yet.</div>
            @endif
        </section>

        <section class="account-panel" data-account-reveal>
            <div class="panel-heading"><div><span>ITEMS</span><h2>Order Items ({{ $order->items->sum('quantity') }})</h2></div></div>
            <div class="order-item-table">
                <div class="order-item-table-head"><span>Product</span><span>Price</span><span>Quantity</span><span>Total</span></div>
                @foreach($order->items as $item)
                    @php $image = $item->product?->thumbnail_url ?: asset('assets/website/images/product-placeholder.svg'); @endphp
                    <div class="order-item-row"><div class="order-item-product"><img src="{{ $image }}" alt="{{ $item->product_name }}"><div><strong>{{ $item->product_name }}</strong><span>SKU: {{ $item->sku }}</span></div></div><span>৳{{ number_format((float) $item->unit_price, 0) }}</span><span>{{ $item->quantity }}</span><strong>৳{{ number_format((float) $item->line_total, 0) }}</strong></div>
                @endforeach
            </div>
            <div class="order-total-box"><div><span>Subtotal</span><strong>৳{{ number_format((float) $order->subtotal, 0) }}</strong></div><div><span>Discount</span><strong>-৳{{ number_format((float) $order->discount, 0) }}</strong></div><div><span>Shipping</span><strong>৳{{ number_format((float) $order->shipping, 0) }}</strong></div><div class="grand"><span>Total Amount</span><strong>৳{{ number_format((float) $order->grand_total, 0) }}</strong></div></div>
        </section>

        @if($order->histories->isNotEmpty())
        <section class="account-panel" data-account-reveal><div class="panel-heading"><div><span>TIMELINE</span><h2>Order Activity</h2></div></div><div class="order-timeline">
            @foreach($order->histories as $history)<div class="timeline-row"><span class="timeline-dot"></span><div><strong>{{ $history->to_status ? ucfirst($history->to_status) : 'Update' }}</strong><p>{{ $history->note ?: 'Order information updated.' }}</p><small>{{ $history->created_at?->format('M d, Y h:i A') }}</small></div></div>@endforeach
        </div></section>
        @endif

        <div class="account-bottom-actions"><a href="{{ route('website.account.orders') }}" class="secondary-account-button"><i class="fas fa-arrow-left"></i> Back to Orders</a>@if(in_array($order->payment_status, [\App\Models\Order::PAYMENT_UNPAID, \App\Models\Order::PAYMENT_REJECTED], true))<a href="{{ route('website.account.payments', ['order' => $order->id]) }}" class="primary-account-button">Submit Payment <i class="fas fa-arrow-right"></i></a>@endif</div>
    </div></div>
</div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
