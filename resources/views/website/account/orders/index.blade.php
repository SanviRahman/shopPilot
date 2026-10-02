@extends('website.layouts.app')
@section('title', 'My Orders | ShopPilot')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">@endpush
@section('content')
<section class="account-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.dashboard') }}">My Account</a><i class="fas fa-chevron-right"></i><strong>My Orders</strong></div>
    <div class="account-layout">@include('website.account.partials.sidebar')<div class="account-main">
        <header class="account-page-heading" data-account-reveal><span>ORDER HISTORY</span><h1>My Orders</h1><p>View and track all orders placed while logged into your ShopPilot account.</p></header>
        <div class="order-toolbar" data-account-reveal>
            <div class="order-filter-tabs"><a href="{{ route('website.account.orders') }}" class="{{ !$status ? 'active' : '' }}">All Orders <b>{{ $allCount }}</b></a>@foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $itemStatus)<a href="{{ route('website.account.orders', ['status' => $itemStatus]) }}" class="{{ $status === $itemStatus ? 'active' : '' }}">{{ ucfirst($itemStatus) }} <b>{{ $statusCounts[$itemStatus] ?? 0 }}</b></a>@endforeach</div>
            <form action="{{ route('website.account.orders') }}" method="GET" class="order-search">@if($status)<input type="hidden" name="status" value="{{ $status }}">@endif<input type="search" name="q" value="{{ $search }}" placeholder="Search order number..."><button><i class="fas fa-search"></i></button></form>
        </div>
        <div class="order-list" data-account-reveal>
            @forelse($orders as $order)
                @php $firstItem = $order->items->first(); $image = $firstItem?->product?->thumbnail_url ?: asset('assets/website/images/product-placeholder.svg'); @endphp
                <article class="order-list-card">
                    <img src="{{ $image }}" alt="{{ $firstItem?->product_name ?? 'Order product' }}">
                    <div class="order-list-identity"><strong>#{{ $order->order_number }}</strong><span>{{ $order->created_at?->format('M d, Y') }}</span></div>
                    <div class="order-list-meta"><span>{{ $order->items->sum('quantity') }} item(s)</span><strong>৳{{ number_format((float) $order->grand_total, 0) }}</strong></div>
                    <span class="status-pill status-{{ $order->order_status }}">{{ ucfirst($order->order_status) }}</span>
                    <a class="order-view-button" href="{{ route('website.account.orders.show', $order->order_number) }}">View Details</a>
                </article>
            @empty
                <div class="account-empty"><i class="fas fa-box-open"></i><h3>No matching orders</h3><p>When you place an order while logged in, it will appear here.</p><a href="{{ route('website.shop') }}">Start Shopping</a></div>
            @endforelse
        </div>
        @if($orders->hasPages())<div class="account-pagination">{{ $orders->links() }}</div>@endif
    </div></div>
</div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
