@extends('website.layouts.app')

@section('title', 'Shop Products | ShopPilot')
@section('meta_description', 'Browse active ShopPilot products by category, price and availability. Find quality products with fast delivery and secure shopping.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/shop.css') }}">
@endpush

@section('content')
<section class="shop-hero-section">
    <div class="container">
        <div class="shop-hero" data-shop-reveal>
            <div class="shop-hero-copy">
                <span class="shop-kicker">ShopPilot Marketplace</span>
                <h1>Discover Amazing <span>Products</span></h1>
                <p>Find exactly what you need from our collection of quality products, smart essentials and everyday favourites.</p>
                <div class="shop-hero-meta">
                    <span data-shop-total-count><i class="fas fa-box-open"></i> {{ number_format($products->total()) }} products found</span>
                    <span><i class="fas fa-shield-alt"></i> Secure shopping</span>
                </div>
            </div>
            <div class="shop-hero-art" aria-hidden="true">
                <span class="shop-hero-orb orb-one"></span>
                <span class="shop-hero-orb orb-two"></span>
                <img src="{{ asset('assets/website/images/heroes/hero-tech.svg') }}" alt="" width="620" height="420">
            </div>
        </div>
    </div>
</section>

<section class="shop-breadcrumb-section">
    <div class="container">
        @include('website.shop.partials.breadcrumb')
    </div>
</section>

<section class="shop-main-section">
    <div class="container">
        @include('website.shop.partials.ajax-region')
    </div>
</section>

<section class="quality-strip-section shop-quality-section">
    <div class="container">
        <div class="quality-strip">
            <div><span><i class="far fa-gem"></i></span><p><strong>Quality Products</strong><small>Carefully selected items</small></p></div>
            <div><span><i class="fas fa-award"></i></span><p><strong>Trusted by Customers</strong><small>Built for a reliable experience</small></p></div>
            <div><span><i class="fas fa-truck"></i></span><p><strong>Fast &amp; Reliable Delivery</strong><small>On-time delivery nationwide</small></p></div>
            <div><span><i class="fas fa-headset"></i></span><p><strong>Dedicated Support</strong><small>We are here to help</small></p></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/shop.js') }}" defer></script>
@endpush
