@extends('website.layouts.app')

@section('title', 'About ShopPilot | ShopPilot')
@section('meta_description', 'Learn about ShopPilot, our mission, our shopping experience and the values behind our ecommerce platform.')

@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/content-pages.css') }}">@endpush

@section('content')
<section class="content-page about-page">
    <div class="container">
        <nav class="content-breadcrumb" data-content-reveal><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><strong>About Us</strong></nav>

        <section class="about-hero premium-panel" data-content-reveal>
            <div class="about-hero-copy">
                <span class="content-kicker">OUR STORY</span>
                <h1>About <em>ShopPilot</em></h1>
                <h2>Shop Smart, Live Better</h2>
                <p>ShopPilot is built around one simple idea: online shopping should feel clear, secure and enjoyable from discovery to delivery. Our demo storefront brings products, checkout, order tracking and customer tools together in one polished experience.</p>
                <div class="content-actions"><a href="{{ route('website.shop') }}" class="content-btn primary"><i class="fas fa-shopping-bag"></i> Start Shopping</a><a href="{{ route('website.contact') }}" class="content-btn secondary"><i class="far fa-envelope"></i> Contact Us</a></div>
            </div>
            <div class="about-hero-art" aria-hidden="true"><span class="floating-chip chip-one"><i class="fas fa-shield-alt"></i> Secure</span><span class="floating-chip chip-two"><i class="fas fa-truck"></i> Reliable</span><span class="hero-orbit orbit-one"></span><span class="hero-orbit orbit-two"></span><img src="{{ asset('assets/website/images/heroes/hero-tech.svg') }}" alt="" width="620" height="420"></div>
        </section>

        <section class="about-stats" data-content-reveal>
            <article class="metric-card"><span><i class="fas fa-users"></i></span><div><strong>{{ number_format($customerCount) }}+</strong><h3>Registered Customers</h3><p>Growing with every account.</p></div></article>
            <article class="metric-card"><span><i class="fas fa-box-open"></i></span><div><strong>{{ number_format($productCount) }}+</strong><h3>Active Products</h3><p>Ready to browse and order.</p></div></article>
            <article class="metric-card"><span><i class="fas fa-layer-group"></i></span><div><strong>{{ number_format($categoryCount) }}+</strong><h3>Product Categories</h3><p>Organized for easier discovery.</p></div></article>
        </section>

        <section class="mission-grid">
            <article class="mission-card" data-content-reveal><span class="mission-icon"><i class="fas fa-bullseye"></i></span><div><small>OUR MISSION</small><h2>Make shopping simpler</h2><p>Deliver a fast and understandable storefront with useful products, transparent checkout steps, secure account tools and reliable order visibility.</p></div></article>
            <article class="mission-card" data-content-reveal><span class="mission-icon vision"><i class="far fa-eye"></i></span><div><small>OUR VISION</small><h2>Build trust through clarity</h2><p>Create an ecommerce experience where customers always understand what they are buying, how they are paying and what happens after an order is placed.</p></div></article>
        </section>

        <section class="content-section" data-content-reveal>
            <div class="content-heading"><div><span>WHY SHOPPILOT</span><h2>Why shop with us?</h2><p>We focus on the moments that make an online order feel effortless.</p></div></div>
            <div class="feature-card-grid five">
                <article class="mini-feature-card"><span><i class="fas fa-th-large"></i></span><h3>Wide Selection</h3><p>Categories and products organized for easy browsing.</p></article>
                <article class="mini-feature-card"><span><i class="fas fa-lock"></i></span><h3>Secure Shopping</h3><p>Validation, protected sessions and safer checkout flows.</p></article>
                <article class="mini-feature-card"><span><i class="fas fa-shipping-fast"></i></span><h3>Fast Workflow</h3><p>From cart to checkout with fewer unnecessary steps.</p></article>
                <article class="mini-feature-card"><span><i class="fas fa-sync-alt"></i></span><h3>Easy Returns</h3><p>Policy information is easy to find before and after purchase.</p></article>
                <article class="mini-feature-card"><span><i class="fas fa-headset"></i></span><h3>Helpful Support</h3><p>Contact and FAQ tools are always close by.</p></article>
            </div>
        </section>

        <section class="content-section" data-content-reveal>
            <div class="content-heading"><div><span>THE EXPERIENCE</span><h2>Built around customer confidence</h2><p>ShopPilot connects discovery, checkout and post-purchase tools in one consistent interface.</p></div></div>
            <div class="team-grid">
                <article class="team-card"><span class="team-avatar blue"><i class="fas fa-search"></i></span><div><small>DISCOVER</small><h3>Find what you need</h3><p>Search, filters and categories help customers move quickly from browsing to the right product.</p></div></article>
                <article class="team-card"><span class="team-avatar green"><i class="fas fa-credit-card"></i></span><div><small>CHECKOUT</small><h3>Order with confidence</h3><p>Clear totals, delivery details and payment status reduce uncertainty before an order is submitted.</p></div></article>
                <article class="team-card"><span class="team-avatar purple"><i class="fas fa-route"></i></span><div><small>AFTER PURCHASE</small><h3>Stay informed</h3><p>Account pages, payment submissions and tracking keep important order information within reach.</p></div></article>
            </div>
        </section>

        <section class="content-cta" data-content-reveal><div><span>READY WHEN YOU ARE</span><h2>Ready to start shopping?</h2><p>Explore active products and experience the complete ShopPilot storefront.</p></div><a href="{{ route('website.shop') }}" class="content-btn primary">Shop Now <i class="fas fa-arrow-right"></i></a><div class="cta-art" aria-hidden="true"><i class="fas fa-headphones-alt"></i><i class="fas fa-laptop"></i><i class="fas fa-shopping-bag"></i></div></section>
    </div>
</section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/content-pages.js') }}" defer></script>@endpush
