@extends('website.layouts.app')

@section('title', 'ShopPilot - Shop Smart, Live Better')
@section('meta_description', 'Discover quality products, featured deals, popular categories and best sellers at ShopPilot.')

@section('content')
<section class="hero-section">
    <div class="container">
        @php
            $heroSlides = [
                [
                    'eyebrow' => 'Smart Tech. Better Everyday.',
                    'title' => 'Upgrade Your Everyday',
                    'highlight' => 'With Smarter Technology',
                    'description' => 'Explore practical electronics and everyday tech selected to make work, study and entertainment easier.',
                    'image' => 'assets/website/images/heroes/hero-tech.svg',
                    'offer_top' => 'UP TO',
                    'offer_value' => '30%',
                    'offer_bottom' => 'OFF',
                    'meta' => 'Electronics & smart essentials',
                ],
                [
                    'eyebrow' => 'Style That Moves With You.',
                    'title' => 'Refresh Your Look',
                    'highlight' => 'With Everyday Essentials',
                    'description' => 'Discover comfortable fashion, accessories and daily essentials curated for a simple, confident lifestyle.',
                    'image' => 'assets/website/images/heroes/hero-fashion.svg',
                    'offer_top' => 'SAVE',
                    'offer_value' => '20%',
                    'offer_bottom' => 'TODAY',
                    'meta' => 'Fashion & lifestyle picks',
                ],
                [
                    'eyebrow' => 'Comfort Starts At Home.',
                    'title' => 'Make Every Space',
                    'highlight' => 'Feel More Like Home',
                    'description' => 'Shop useful home and living products that bring more comfort, organization and warmth to your everyday spaces.',
                    'image' => 'assets/website/images/heroes/hero-home.svg',
                    'offer_top' => 'HOME',
                    'offer_value' => '25%',
                    'offer_bottom' => 'DEALS',
                    'meta' => 'Home & living favourites',
                ],
            ];
        @endphp

        <div class="hero-slider" data-hero-slider data-autoplay-ms="4200">
            @foreach($heroSlides as $slideIndex => $slide)
                @php($heroProduct = $heroProducts->get($slideIndex))
                <article
                    class="hero-slide {{ $slideIndex === 0 ? 'active' : '' }}"
                    data-hero-slide
                    aria-hidden="{{ $slideIndex === 0 ? 'false' : 'true' }}"
                >
                    <div class="hero-copy">
                        <span class="eyebrow">{{ $slide['eyebrow'] }}</span>
                        <h1>{{ $slide['title'] }} <span>{{ $slide['highlight'] }}</span></h1>
                        <p>{{ $heroProduct?->short_description ? \Illuminate\Support\Str::limit($heroProduct->short_description, 130) : $slide['description'] }}</p>

                        <div class="hero-actions">
                            <a href="{{ route('website.shop') }}" class="btn btn-primary">Shop Now <i class="fas fa-arrow-right"></i></a>
                            <a href="{{ route('website.shop', ['featured' => 1]) }}" class="btn btn-outline">View Offers</a>
                        </div>

                        <div class="hero-meta">
                            <span><i class="fas fa-check-circle"></i> {{ $heroProduct?->name ?? $slide['meta'] }}</span>
                            <span><i class="fas fa-box"></i> {{ $heroProduct ? $heroProduct->stock_quantity.' in stock' : 'Ready to explore' }}</span>
                        </div>
                    </div>

                    <div class="hero-visual">
                        <div class="hero-product-glow"></div>
                        <img
                            src="{{ asset($slide['image']) }}"
                            alt="{{ $slide['meta'] }}"
                            width="620"
                            height="420"
                            {{ $slideIndex === 0 ? 'fetchpriority=high' : 'loading=lazy' }}
                        >
                        <div class="hero-offer-badge">
                            <small>{{ $slide['offer_top'] }}</small>
                            <strong>{{ $slide['offer_value'] }}</strong>
                            <span>{{ $slide['offer_bottom'] }}</span>
                        </div>
                    </div>
                </article>
            @endforeach

            <button type="button" class="hero-arrow prev" data-hero-prev aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="hero-arrow next" data-hero-next aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>

            <div class="hero-dots" data-hero-dots aria-label="Hero slides">
                @foreach($heroSlides as $slideIndex => $slide)
                    <button
                        type="button"
                        class="{{ $slideIndex === 0 ? 'active' : '' }}"
                        data-hero-dot="{{ $slideIndex }}"
                        aria-label="Go to slide {{ $slideIndex + 1 }}"
                        aria-current="{{ $slideIndex === 0 ? 'true' : 'false' }}"
                    ></button>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="benefit-strip-section">
    <div class="container">
        <div class="benefit-strip">
            <div class="benefit-item"><span><i class="fas fa-shipping-fast"></i></span><div><strong>Fast Delivery</strong><small>Get your order quickly</small></div></div>
            <div class="benefit-item"><span><i class="fas fa-shield-alt"></i></span><div><strong>Secure Payment</strong><small>100% safe &amp; secure</small></div></div>
            <div class="benefit-item"><span><i class="fas fa-sync-alt"></i></span><div><strong>Easy Returns</strong><small>Simple return policy</small></div></div>
            <div class="benefit-item"><span><i class="fas fa-headset"></i></span><div><strong>Customer Support</strong><small>Dedicated support</small></div></div>
        </div>
    </div>
</section>

<section class="home-section" id="categories">
    <div class="container">
        <div class="section-heading">
            <div><h2>Shop by Category</h2><p>Explore our top categories and find exactly what you need.</p></div>
            <a href="{{ route('website.shop') }}" class="section-link">View All Categories <i class="fas fa-arrow-right"></i></a>
        </div>

        @if($categories->isNotEmpty())
            <div class="category-grid">
                @foreach($categories as $category)
                    @include('website.partials.category-card', ['category' => $category])
                @endforeach
            </div>
        @else
            <div class="empty-state compact-empty">
                <i class="fas fa-layer-group"></i>
                <div><strong>No active categories yet</strong><p>Add categories from the admin panel to populate this section.</p></div>
            </div>
        @endif
    </div>
</section>

@if($searchTerm !== '')
<section class="home-section search-results-section">
    <div class="container">
        <div class="section-heading">
            <div><h2>Search Results</h2><p>Results for “{{ $searchTerm }}”</p></div>
            <a href="{{ route('website.home') }}" class="section-link">Clear Search <i class="fas fa-times"></i></a>
        </div>
        @if($searchResults->isNotEmpty())
            <div class="product-grid">
                @foreach($searchResults as $product)
                    @include('website.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div class="empty-state"><i class="fas fa-search"></i><div><strong>No products found</strong><p>Try another product name, SKU or keyword.</p></div></div>
        @endif
    </div>
</section>
@endif

<section class="home-section" id="featured">
    <div class="container">
        <div class="section-heading">
            <div><h2>Featured Products</h2><p>Handpicked products just for you.</p></div>
            <a href="{{ route('website.shop') }}" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>

        @php($displayFeatured = $featuredProducts->isNotEmpty() ? $featuredProducts : $latestProducts->take(6))
        @if($displayFeatured->isNotEmpty())
            <div class="product-grid">
                @foreach($displayFeatured as $product)
                    @include('website.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div class="empty-state"><i class="fas fa-box-open"></i><div><strong>No active products yet</strong><p>Add products from the admin panel and they will appear here automatically.</p></div></div>
        @endif
    </div>
</section>

<section class="promo-section" id="offers">
    <div class="container">
        <div class="promo-banner">
            <div class="promo-copy">
                <span>SPECIAL OFFER</span>
                <h2>Up to 30% Off</h2>
                <h3>On Selected Products</h3>
                <a href="{{ route('website.shop', ['featured' => 1]) }}" class="btn btn-primary">Shop Offers <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="promo-art">
                @php($promoProduct = $latestProducts->skip(1)->first() ?? $latestProducts->first())
                @if($promoProduct?->thumbnail_url)
                    <img src="{{ $promoProduct->thumbnail_url }}" alt="{{ $promoProduct->name }}">
                @else
                    <img src="{{ asset('assets/website/images/promo-shopping.svg') }}" alt="ShopPilot special offer">
                @endif
                <span class="limited-badge">LIMITED<br>TIME<br>OFFER</span>
            </div>
            <div class="promo-side-copy">
                <strong>Better Products<br>Better Living</strong>
                <small>Top brands at amazing prices</small>
            </div>
        </div>
    </div>
</section>

<section class="home-section" id="best-sellers">
    <div class="container">
        <div class="section-heading">
            <div><h2>Best Sellers</h2><p>Popular products based on customer orders.</p></div>
            <a href="{{ route('website.shop', ['sort' => 'best_selling']) }}" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>

        @if($bestSellers->isNotEmpty())
            <div class="product-grid">
                @foreach($bestSellers as $product)
                    @include('website.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div class="empty-state"><i class="fas fa-chart-line"></i><div><strong>Best sellers will appear here</strong><p>Order activity will automatically rank your most popular products.</p></div></div>
        @endif
    </div>
</section>

<section class="quality-strip-section">
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
