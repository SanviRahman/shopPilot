@extends('website.layouts.app')
@section('title', 'My Wishlist | ShopPilot')
@section('meta_description', 'Manage the products you saved to your ShopPilot wishlist.')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">@endpush

@section('content')
<section class="account-page wishlist-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.dashboard') }}">My Account</a><i class="fas fa-chevron-right"></i><strong>Wishlist</strong></div>

    <div class="account-layout">
        @include('website.account.partials.sidebar')

        <main class="account-main" data-wishlist-page>
            <header class="account-page-heading account-heading-actions" data-account-reveal>
                <div><span>SAVED PRODUCTS</span><h1>My Wishlist</h1><p>Save your favorite products and move them to your cart whenever you're ready.</p></div>
                <div class="account-heading-buttons">
                    <form action="{{ route('website.account.wishlist.add-all-to-cart') }}" method="POST" data-wishlist-add-all>@csrf
                        <button type="submit" class="primary-account-button" {{ $wishlistInStockCount < 1 ? 'disabled' : '' }}><i class="fas fa-cart-plus"></i> Add All to Cart</button>
                    </form>
                    <form action="{{ route('website.account.wishlist.clear') }}" method="POST" data-wishlist-clear>@csrf @method('DELETE')
                        <button type="submit" class="danger-outline-button" {{ $wishlistCount < 1 ? 'disabled' : '' }}><i class="far fa-trash-alt"></i> Clear Wishlist</button>
                    </form>
                </div>
            </header>

            <div class="wishlist-stat-grid" data-account-reveal>
                <article class="wishlist-stat"><span class="wishlist-stat-icon pink"><i class="fas fa-heart"></i></span><div><strong data-wishlist-page-count>{{ $wishlistCount }}</strong><small>Wishlist Items</small></div></article>
                <article class="wishlist-stat"><span class="wishlist-stat-icon green"><i class="fas fa-box"></i></span><div><strong data-wishlist-in-stock-count>{{ $wishlistInStockCount }}</strong><small>In Stock</small></div></article>
                <article class="wishlist-stat"><span class="wishlist-stat-icon orange"><i class="fas fa-exclamation-circle"></i></span><div><strong data-wishlist-out-stock-count>{{ $wishlistOutOfStockCount }}</strong><small>Out of Stock</small></div></article>
            </div>

            <section class="wishlist-products-section" data-account-reveal>
                @if($wishlistItems->isNotEmpty())
                    <div class="wishlist-grid" data-wishlist-grid>
                        @foreach($wishlistItems as $wishlist)
                            @include('website.account.wishlist.partials.card', ['wishlist' => $wishlist])
                        @endforeach
                    </div>
                @else
                    <div class="wishlist-empty" data-wishlist-empty>
                        <span><i class="far fa-heart"></i></span><h2>Your wishlist is empty</h2><p>Save products you love and they'll appear here for easy access later.</p><a href="{{ route('website.shop') }}" class="primary-account-button"><i class="fas fa-shopping-bag"></i> Explore Products</a>
                    </div>
                @endif
            </section>

            @if($recommendedProducts->isNotEmpty())
                <section class="wishlist-recommendations" data-account-reveal>
                    <div class="panel-heading"><div><span>DISCOVER MORE</span><h2>You May Also Like</h2></div><a href="{{ route('website.shop') }}">View All <i class="fas fa-arrow-right"></i></a></div>
                    <div class="wishlist-recommendation-grid">
                        @foreach($recommendedProducts as $product)
                            @include('website.partials.product-card', ['product' => $product])
                        @endforeach
                    </div>
                </section>
            @endif
        </main>
    </div>
</div></section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
