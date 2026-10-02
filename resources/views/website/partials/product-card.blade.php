@php
    $regularPrice = (float) $product->regular_price;
    $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
    $hasSale = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
    $currentPrice = $hasSale ? $salePrice : $regularPrice;
    $discountPercent = $hasSale && $regularPrice > 0
        ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100)
        : 0;
    $inStock = (int) $product->stock_quantity > 0;
@endphp

<article class="product-card">
    <div class="product-image-wrap">
        @if($discountPercent > 0)
            <span class="discount-badge">-{{ $discountPercent }}%</span>
        @endif

        <button type="button" class="wishlist-button" data-coming-soon="Wishlist" aria-label="Add {{ $product->name }} to wishlist">
            <i class="far fa-heart"></i>
        </button>

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

        <button
            type="button"
            class="add-to-cart-button"
            data-coming-soon="Cart"
            {{ $inStock ? '' : 'disabled' }}
        >
            <i class="fas fa-cart-plus"></i>
            {{ $inStock ? 'Add to Cart' : 'Out of Stock' }}
        </button>
    </div>
</article>