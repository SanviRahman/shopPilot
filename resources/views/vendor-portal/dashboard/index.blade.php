@extends('vendor-portal.layouts.app')

@section('title', 'Overview')
@section('breadcrumb_label', 'Overview')
@section('eyebrow', 'YOUR BUSINESS, IN ONE PLACE')
@section('page_heading', 'Good to have you back')
@section('page_description', 'A calm, clear view of your catalogue, stock and next steps.')

@section('page_actions')
    <a class="vp-button vp-button-primary" href="{{ route('vendor.products.create') }}"><i class="fas fa-plus"></i><span>Add a product</span></a>
@endsection

@section('content')
    <section class="vp-welcome-panel">
        <div class="vp-welcome-copy">
            <span class="vp-welcome-kicker">YOUR SHOP, YOUR MOMENTUM</span>
            <h2>{{ $vendor?->store?->name ?? 'Your ShopPilot store' }}</h2>
            <p>Keep your catalogue thoughtful, your stock accurate and your store details ready for customers.</p>
            <a href="{{ route('vendor.store.edit') }}" class="vp-text-link">Review store profile <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="vp-welcome-art" aria-hidden="true"><span class="vp-art-circle vp-art-circle-one"></span><span class="vp-art-circle vp-art-circle-two"></span><i class="fas fa-store"></i><span class="vp-art-spark vp-art-spark-one">✦</span><span class="vp-art-spark vp-art-spark-two">✧</span></div>
    </section>

    <section class="vp-stat-grid" aria-label="Store summary">
        <article class="vp-stat-card"><span class="vp-stat-icon is-blue"><i class="fas fa-box-open"></i></span><div><span class="vp-stat-label">Total products</span><strong>{{ number_format($stats['products_total']) }}</strong><small>Items in your catalogue</small></div></article>
        <article class="vp-stat-card"><span class="vp-stat-icon is-green"><i class="fas fa-check-circle"></i></span><div><span class="vp-stat-label">Published products</span><strong>{{ number_format($stats['products_active']) }}</strong><small>Approved and available</small></div></article>
        <article class="vp-stat-card"><span class="vp-stat-icon is-orange"><i class="fas fa-clipboard-check"></i></span><div><span class="vp-stat-label">Awaiting review</span><strong>{{ number_format($stats['products_pending_review']) }}</strong><small>Submitted to the marketplace</small></div></article>
        <article class="vp-stat-card"><span class="vp-stat-icon is-purple"><i class="fas fa-exclamation-triangle"></i></span><div><span class="vp-stat-label">Low stock items</span><strong>{{ number_format($stats['low_stock']) }}</strong><small>Worth checking soon</small></div></article>
    </section>

    <section class="vp-stat-grid vp-stat-grid-three" aria-label="Sales summary">
        <article class="vp-stat-card"><span class="vp-stat-icon is-blue"><i class="fas fa-shopping-bag"></i></span><div><span class="vp-stat-label">Seller orders</span><strong>{{ number_format($stats['orders_total']) }}</strong><small>Orders containing your products</small></div></article>
        <article class="vp-stat-card"><span class="vp-stat-icon is-orange"><i class="fas fa-clipboard-list"></i></span><div><span class="vp-stat-label">To fulfil</span><strong>{{ number_format($stats['orders_to_process']) }}</strong><small>Confirmed and in progress</small></div></article>
        <article class="vp-stat-card"><span class="vp-stat-icon is-green"><i class="fas fa-wallet"></i></span><div><span class="vp-stat-label">Available earnings</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format($stats['available_earnings'], 2) }}</strong><small>Eligible for payout request</small></div></article>
    </section>

    <div class="vp-dashboard-grid">
        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">LATEST CATALOGUE</span><h2>Recently updated products</h2><p>Keep product details and availability up to date.</p></div><a href="{{ route('vendor.products.index') }}" class="vp-text-link">View all <i class="fas fa-arrow-right"></i></a></div>
            @forelse($recentProducts as $product)
                <a class="vp-product-row" href="{{ route('vendor.products.edit', $product) }}">
                    <span class="vp-product-thumb">@if($product->thumbnail_url)<img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">@else<i class="fas fa-box"></i>@endif</span>
                    <span class="vp-product-row-main"><strong>{{ $product->name }}</strong><small>{{ $product->sku }} · {{ $product->category?->name ?? 'Uncategorised' }}</small></span>
                    <span class="vp-status-pill {{ $product->moderation_status === 'approved' ? 'is-success' : ($product->moderation_status === 'pending_review' ? 'is-info' : 'is-muted') }}">{{ str_replace('_', ' ', ucfirst($product->moderation_status ?? 'approved')) }}</span>
                    <i class="fas fa-chevron-right vp-row-chevron"></i>
                </a>
            @empty
                <div class="vp-empty-state vp-empty-compact"><span class="vp-empty-icon"><i class="fas fa-box-open"></i></span><strong>Your catalogue starts here</strong><p>Add your first product and build a store customers can trust.</p><a class="vp-button vp-button-primary" href="{{ route('vendor.products.create') }}">Add your first product</a></div>
            @endforelse
        </section>

        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">A GOOD PLACE TO START</span><h2>Store checklist</h2><p>A few thoughtful steps make a stronger first impression.</p></div></div>
            <div class="vp-checklist">
                <a href="{{ route('vendor.store.edit') }}" class="vp-checklist-item"><span class="vp-check-icon"><i class="fas fa-store"></i></span><span><strong>Complete your store profile</strong><small>Add your store details and policies.</small></span><i class="fas fa-arrow-right"></i></a>
                <a href="{{ route('vendor.products.create') }}" class="vp-checklist-item"><span class="vp-check-icon"><i class="fas fa-box-open"></i></span><span><strong>List your first product</strong><small>Include helpful photos and accurate details.</small></span><i class="fas fa-arrow-right"></i></a>
                <a href="{{ route('vendor.inventory.index') }}" class="vp-checklist-item"><span class="vp-check-icon"><i class="fas fa-layer-group"></i></span><span><strong>Review your inventory</strong><small>Keep availability accurate and up to date.</small></span><i class="fas fa-arrow-right"></i></a>
            </div>
        </section>
    </div>

    <section class="vp-panel"><div class="vp-panel-heading"><div><span class="vp-section-kicker">RECENT SALES</span><h2>Latest seller orders</h2><p>Keep an eye on the orders that need your attention.</p></div><a href="{{ route('vendor.orders.index') }}" class="vp-text-link">View orders <i class="fas fa-arrow-right"></i></a></div>
        @forelse($recentOrders as $order)
            <a class="vp-product-row" href="{{ route('vendor.orders.show', $order) }}"><span class="vp-product-thumb"><i class="fas fa-receipt"></i></span><span class="vp-product-row-main"><strong>{{ $order->suborder_number }}</strong><small>{{ $order->order?->buyer_name }} · {{ $order->created_at?->format('d M Y') }}</small></span><span class="vp-status-pill is-info">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $order->grand_total, 2) }}</strong></a>
        @empty
            <div class="vp-empty-state vp-empty-compact"><strong>No seller orders yet</strong><p>New orders will appear here after customers check out.</p></div>
        @endforelse
    </section>
@endsection
