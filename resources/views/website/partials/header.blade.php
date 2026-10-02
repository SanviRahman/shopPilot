@php
    $cartCount = (int) ($headerCartSummary['count'] ?? 0);
    $cartTotal = (float) ($headerCartSummary['grandTotal'] ?? 0);
@endphp

<header class="site-header" id="siteHeader">
    <div class="topbar">
        <div class="container topbar-inner">
            <div class="topbar-benefits">
                <span><i class="fas fa-truck"></i> Free delivery on orders over ৳1,000</span>
                <span><i class="fas fa-lock"></i> Secure Payment</span>
                <span><i class="fas fa-undo-alt"></i> Easy Returns</span>
                <span><i class="fas fa-headset"></i> 24/7 Support</span>
            </div>
            <div class="topbar-links">
                <a href="{{ auth('web')->check() ? route('website.account.orders') : route('website.login') }}">Track Order</a>
                <span class="divider">|</span>
                <a href="#contact">Help &amp; Support</a>
            </div>
        </div>
    </div>

    <div class="main-header">
        <div class="container main-header-inner">
            <a href="{{ route('website.home') }}" class="brand" aria-label="ShopPilot home">
                <span class="brand-mark"><i class="fas fa-shopping-bag"></i></span>
                <span class="brand-copy">
                    <strong>Shop<span>Pilot</span></strong>
                    <small>Shop Smart. Live Better</small>
                </span>
            </a>

            <form class="header-search" action="{{ route('website.shop') }}" method="GET" role="search">
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search for products, categories..."
                    aria-label="Search products"
                >
                <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
            </form>

            <div class="header-actions">
                <a href="{{ auth('web')->check() ? route('website.account.dashboard') : route('website.login') }}" class="header-action {{ request()->routeIs('website.account.*') || request()->routeIs('website.login') || request()->routeIs('website.register') ? 'active' : '' }}">
                    <span class="action-icon"><i class="fas fa-user"></i></span>
                    <span class="action-copy">
                        <strong>{{ auth('web')->check() ? auth('web')->user()->name : 'Account' }}</strong>
                        <small>{{ auth('web')->check() ? 'My Account' : 'Login / Register' }}</small>
                    </span>
                </a>

                <a href="#" class="header-action compact" data-coming-soon="Wishlist">
                    <span class="action-icon badge-holder">
                        <i class="far fa-heart"></i>
                        <span class="mini-badge">0</span>
                    </span>
                    <span class="action-copy"><strong>Wishlist</strong><small>Wishlist</small></span>
                </a>

                <a href="{{ route('website.cart.index') }}" class="header-action compact {{ request()->routeIs('website.cart.*') ? 'active' : '' }}">
                    <span class="action-icon badge-holder">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="mini-badge">{{ $cartCount }}</span>
                    </span>
                    <span class="action-copy"><strong>Cart</strong><small>৳{{ number_format($cartTotal, 2) }}</small></span>
                </a>

                <button class="mobile-menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-mobile-menu-toggle>
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    <nav class="primary-nav" aria-label="Primary navigation">
        <div class="container nav-inner">
            <div class="category-menu-wrap">
                <button type="button" class="category-menu-button" data-category-toggle aria-expanded="false">
                    <i class="fas fa-th-large"></i>
                    <span>All Categories</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </button>

                <div class="category-dropdown" data-category-menu>
                    @forelse(($navigationCategories ?? collect()) as $category)
                        <a href="{{ route('website.shop', ['category' => [$category->slug]]) }}">
                            <span class="category-dot"><i class="fas fa-angle-right"></i></span>
                            {{ $category->name }}
                        </a>
                    @empty
                        <span class="category-dropdown-empty">No active categories yet.</span>
                    @endforelse
                </div>
            </div>

            <div class="desktop-nav-links">
                <a href="{{ route('website.home') }}" class="{{ request()->routeIs('website.home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('website.shop') }}" class="{{ request()->routeIs('website.shop') || request()->routeIs('website.products.*') ? 'active' : '' }}">Shop</a>
                <a href="{{ route('website.shop', ['sort' => 'newest']) }}">New Arrivals</a>
                <a href="{{ route('website.shop', ['sort' => 'best_selling']) }}">Best Sellers</a>
                <a href="{{ route('website.shop', ['featured' => 1]) }}">Offers</a>
                <a href="#" data-coming-soon="Frontend blog">Blogs</a>
                <a href="#contact">Contact</a>
            </div>
        </div>
    </nav>

    <div class="mobile-menu" data-mobile-menu>
        <a href="{{ route('website.home') }}">Home</a>
        <a href="{{ route('website.shop') }}">Categories</a>
        <a href="{{ route('website.shop') }}">Shop</a>
        <a href="{{ route('website.cart.index') }}">Cart ({{ $cartCount }})</a>
        <a href="{{ auth('web')->check() ? route('website.account.dashboard') : route('website.login') }}">{{ auth('web')->check() ? 'My Account' : 'Login / Register' }}</a>
        @if(auth('web')->check())<a href="{{ route('website.account.orders') }}">My Orders</a>@endif
        <a href="{{ route('website.shop', ['sort' => 'best_selling']) }}">Best Sellers</a>
        <a href="{{ route('website.shop', ['featured' => 1]) }}">Offers</a>
        <a href="#contact">Contact</a>
    </div>
</header>
