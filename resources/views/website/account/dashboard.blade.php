@extends('website.layouts.app')
@section('title', 'My Account Dashboard | ShopPilot')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">@endpush
@section('content')
<section class="account-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><span>My Account</span><i class="fas fa-chevron-right"></i><strong>Dashboard</strong></div>
    <div class="account-layout">
        @include('website.account.partials.sidebar')
        <div class="account-main">
            <header class="account-page-heading" data-account-reveal><span>MY ACCOUNT</span><h1>Dashboard</h1><p>Welcome back, {{ strtok($user->name, ' ') }}! Here's an overview of your ShopPilot account.</p></header>

            <div class="account-stat-grid">
                <article class="account-stat-card" data-account-reveal><span class="stat-icon blue"><i class="fas fa-box"></i></span><small>Total Orders</small><strong>{{ $totalOrders }}</strong></article>
                <article class="account-stat-card" data-account-reveal><span class="stat-icon orange"><i class="far fa-clock"></i></span><small>Active Orders</small><strong>{{ $pendingOrders }}</strong></article>
                <article class="account-stat-card" data-account-reveal><span class="stat-icon green"><i class="fas fa-check"></i></span><small>Completed Orders</small><strong>{{ $completedOrders }}</strong></article>
                <article class="account-stat-card" data-account-reveal><span class="stat-icon pink"><i class="fas fa-heart"></i></span><small>Wishlist Items</small><strong>{{ $wishlistCount }}</strong></article>
            </div>

            <div class="dashboard-grid">
                <section class="account-panel" data-account-reveal>
                    <div class="panel-heading"><div><span>ORDERS</span><h2>Recent Orders</h2></div><a href="{{ route('website.account.orders') }}">View All <i class="fas fa-arrow-right"></i></a></div>
                    <div class="recent-order-list">
                        @forelse($recentOrders as $order)
                            <a href="{{ route('website.account.orders.show', $order->order_number) }}" class="recent-order-row">
                                <div><strong>#{{ $order->order_number }}</strong><small>{{ $order->created_at?->format('M d, Y') }}</small></div>
                                <span class="status-pill status-{{ $order->order_status }}">{{ ucfirst($order->order_status) }}</span>
                                <b>৳{{ number_format((float) $order->grand_total, 0) }}</b>
                            </a>
                        @empty
                            <div class="account-empty-mini">No orders yet. <a href="{{ route('website.shop') }}">Start shopping</a>.</div>
                        @endforelse
                    </div>
                </section>

                <section class="account-panel" data-account-reveal>
                    <div class="panel-heading"><div><span>PROFILE</span><h2>Account Information</h2></div><a href="{{ route('website.account.profile.edit') }}">Edit <i class="fas fa-arrow-right"></i></a></div>
                    <div class="account-info-list">
                        <div><i class="far fa-user"></i><span>{{ $user->name }}</span></div>
                        <div><i class="far fa-envelope"></i><span>{{ $user->email }}</span></div>
                        <div><i class="fas fa-phone-alt"></i><span>{{ $user->phone_number ?: ($latestOrder?->buyer_phone ?: 'Add your phone in Profile Settings') }}</span></div>
                        <div><i class="fas fa-map-marker-alt"></i><span>{{ $user->address ? $user->address . ($user->district ? ', ' . $user->district : '') : ($latestOrder ? $latestOrder->shipping_address . ', ' . $latestOrder->city_or_area : 'Add your address in Profile Settings') }}</span></div>
                    </div>
                </section>
            </div>

            <section class="account-offer-banner" data-account-reveal><div><span>SHOPPILOT OFFERS</span><h2>Explore our latest products &amp; deals</h2><p>Discover fresh arrivals, featured products and special prices.</p></div><a href="{{ route('website.shop', ['featured' => 1]) }}">Shop Now <i class="fas fa-arrow-right"></i></a></section>
        </div>
    </div>
</div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
