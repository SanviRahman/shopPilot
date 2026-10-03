@php
    $cartCount = (int) ($headerCartSummary['count'] ?? 0);
    $cartTotal = (float) ($headerCartSummary['grandTotal'] ?? 0);

    $isShopPage = request()->routeIs('website.shop');
    $isProductPage = request()->routeIs('website.products.*');
    $headerShopContext = 'shop';

    if ($isShopPage) {
        $headerShopContext = match (true) {
            request('sort') === 'newest' => 'new_arrivals',
            request('sort') === 'best_selling' => 'best_sellers',
            request()->boolean('featured') => 'offers',
            default => 'shop',
        };
    }
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
                <a href="{{ route('website.track-order') }}">Track Order</a>
                <span class="divider">|</span>
                <a href="{{ route('website.contact') }}">Help &amp; Support</a>
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

            <form class="header-search" action="{{ route('website.shop') }}" method="GET" role="search" data-shop-search-form>
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
                        <strong data-profile-header-name>{{ auth('web')->check() ? auth('web')->user()->name : 'Account' }}</strong>
                        <small>{{ auth('web')->check() ? 'My Account' : 'Login / Register' }}</small>
                    </span>
                </a>

                <a href="{{ auth('web')->check() ? route('website.account.wishlist') : route('website.login') }}" class="header-action compact {{ request()->routeIs('website.account.wishlist*') ? 'active' : '' }}">
                    <span class="action-icon badge-holder">
                        <i class="far fa-heart"></i>
                        <span class="mini-badge" data-header-wishlist-count>{{ (int) ($headerWishlistCount ?? 0) }}</span>
                    </span>
                    <span class="action-copy"><strong>Wishlist</strong><small>Wishlist</small></span>
                </a>

                <a href="{{ route('website.cart.index') }}" class="header-action compact {{ request()->routeIs('website.cart.*') ? 'active' : '' }}">
                    <span class="action-icon badge-holder">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="mini-badge" data-header-cart-count>{{ $cartCount }}</span>
                    </span>
                    <span class="action-copy"><strong>Cart</strong><small data-header-cart-total>৳{{ number_format($cartTotal, 2) }}</small></span>
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

            <div class="desktop-nav-links" data-shop-nav>
                <a href="{{ route('website.home') }}" class="{{ request()->routeIs('website.home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('website.shop') }}" data-shop-nav-context="shop" class="{{ ($isProductPage || ($isShopPage && $headerShopContext === 'shop')) ? 'active' : '' }}">Shop</a>
                <a href="{{ route('website.shop', ['sort' => 'newest']) }}" data-shop-nav-context="new_arrivals" class="{{ $isShopPage && $headerShopContext === 'new_arrivals' ? 'active' : '' }}">New Arrivals</a>
                <a href="{{ route('website.shop', ['sort' => 'best_selling']) }}" data-shop-nav-context="best_sellers" class="{{ $isShopPage && $headerShopContext === 'best_sellers' ? 'active' : '' }}">Best Sellers</a>
                <a href="{{ route('website.shop', ['featured' => 1]) }}" data-shop-nav-context="offers" class="{{ $isShopPage && $headerShopContext === 'offers' ? 'active' : '' }}">Offers</a>
                <a href="{{ route('website.blogs.index') }}" class="{{ request()->routeIs('website.blogs.*') ? 'active' : '' }}">Blogs</a>
                <a href="{{ route('website.about') }}" class="{{ request()->routeIs('website.about') ? 'active' : '' }}">About</a>
                <a href="{{ route('website.contact') }}" class="{{ request()->routeIs('website.contact*') ? 'active' : '' }}">Contact</a>
            </div>
        </div>
    </nav>

    <div class="mobile-menu" data-mobile-menu data-shop-mobile-nav>
        <a href="{{ route('website.home') }}">Home</a>
        <a href="{{ route('website.shop') }}">Categories</a>
        <a href="{{ route('website.shop') }}" data-shop-nav-context="shop" class="{{ ($isProductPage || ($isShopPage && $headerShopContext === 'shop')) ? 'active' : '' }}">Shop</a>
        <a href="{{ route('website.shop', ['sort' => 'newest']) }}" data-shop-nav-context="new_arrivals" class="{{ $isShopPage && $headerShopContext === 'new_arrivals' ? 'active' : '' }}">New Arrivals</a>
        <a href="{{ route('website.cart.index') }}">Cart (<span data-mobile-cart-count>{{ $cartCount }}</span>)</a>
        <a href="{{ auth('web')->check() ? route('website.account.dashboard') : route('website.login') }}">{{ auth('web')->check() ? 'My Account' : 'Login / Register' }}</a>
        @if(auth('web')->check())<a href="{{ route('website.account.orders') }}">My Orders</a>@endif
        @if(auth('web')->check())<a href="{{ route('website.account.wishlist') }}">Wishlist (<span data-header-wishlist-count>{{ (int) ($headerWishlistCount ?? 0) }}</span>)</a>@endif
        <a href="{{ route('website.track-order') }}">Track Order</a>
        <a href="{{ route('website.shop', ['sort' => 'best_selling']) }}" data-shop-nav-context="best_sellers" class="{{ $isShopPage && $headerShopContext === 'best_sellers' ? 'active' : '' }}">Best Sellers</a>
        <a href="{{ route('website.shop', ['featured' => 1]) }}" data-shop-nav-context="offers" class="{{ $isShopPage && $headerShopContext === 'offers' ? 'active' : '' }}">Offers</a>
        <a href="{{ route('website.blogs.index') }}">Blogs</a>
        <a href="{{ route('website.about') }}">About</a>
        <a href="{{ route('website.contact') }}">Contact</a>
    </div>
</header>
