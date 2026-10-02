@php
    $regularPrice = (float) $product->regular_price;
    $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
    $hasSale = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
    $currentPrice = $hasSale ? $salePrice : $regularPrice;
    $discountPercent = $hasSale && $regularPrice > 0
        ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100)
        : 0;
    $inStock = (int) $product->stock_quantity > 0;
    $isWishlisted = in_array((int) $product->id, array_map('intval', $wishlistProductIds ?? []), true);
@endphp

<article class="product-card">
    <div class="product-image-wrap">
        @if($discountPercent > 0)
            <span class="discount-badge">-{{ $discountPercent }}%</span>
        @endif

        @auth('web')
            <button
                type="button"
                class="wishlist-button {{ $isWishlisted ? 'active' : '' }}"
                data-wishlist-toggle
                data-product-id="{{ $product->id }}"
                data-wishlisted="{{ $isWishlisted ? '1' : '0' }}"
                data-wishlist-store-url="{{ route('website.account.wishlist.store') }}"
                data-wishlist-destroy-url="{{ route('website.account.wishlist.destroy', $product->id) }}"
                aria-label="{{ $isWishlisted ? 'Remove' : 'Add' }} {{ $product->name }} {{ $isWishlisted ? 'from' : 'to' }} wishlist"
            >
                <i class="{{ $isWishlisted ? 'fas' : 'far' }} fa-heart"></i>
            </button>
        @else
            <a href="{{ route('website.login') }}" class="wishlist-button" aria-label="Login to save {{ $product->name }} to wishlist"><i class="far fa-heart"></i></a>
        @endauth

        <a href="{{ route('website.products.show', $product->slug) }}" class="product-image-link" aria-label="View {{ $product->name }} details">
            @if($product->thumbnail_url)
                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <img src="{{ asset('assets/website/images/product-placeholder.svg') }}" alt="{{ $product->name }}" loading="lazy">
            @endif
        </a>
    </div>

    <div class="product-card-body">
        <div class="product-category">{{ $product->category?->name ?? 'Uncategorized' }}</div>
        <h3><a href="{{ route('website.products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <p class="product-description">{{ \Illuminate\Support\Str::limit($product->short_description ?: 'Quality product selected for ShopPilot customers.', 52) }}</p>

        <div class="rating-row" aria-label="Product rating placeholder">
            <span class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span>
            <span class="rating-count">({{ (int) (($product->id * 7) % 41 + 8) }})</span>
        </div>

        <div class="product-price-row">
            <strong>৳{{ number_format($currentPrice, 0) }}</strong>
            @if($hasSale)
                <del>৳{{ number_format($regularPrice, 0) }}</del>
            @endif
        </div>

        <div class="stock-note {{ $inStock ? 'in-stock' : 'out-of-stock' }}">
            <i class="fas {{ $inStock ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
            {{ $inStock ? 'In stock' : 'Out of stock' }}
        </div>

        <form action="{{ route('website.cart.items.store') }}" method="POST" class="product-card-cart-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="redirect_to" value="back">
            <button
                type="submit"
                class="add-to-cart-button"
                {{ $inStock ? '' : 'disabled' }}
            >
                <i class="fas fa-cart-plus"></i>
                {{ $inStock ? 'Add to Cart' : 'Out of Stock' }}
            </button>
        </form>
    </div>
</article>