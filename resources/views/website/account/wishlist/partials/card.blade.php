@php
    $product = $wishlist->product;
    $regularPrice = (float) $product->regular_price;
    $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
    $hasSale = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
    $currentPrice = $hasSale ? $salePrice : $regularPrice;
    $discountPercent = $hasSale && $regularPrice > 0 ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100) : 0;
    $inStock = (int) $product->stock_quantity > 0 && $product->status === 'active';
@endphp
<article class="wishlist-product-card" data-wishlist-card="{{ $product->id }}" data-stock="{{ $inStock ? '1' : '0' }}" data-account-reveal>
    <div class="wishlist-product-image">
        @if($discountPercent > 0)<span class="discount-badge">-{{ $discountPercent }}%</span>@endif
        <button
            type="button"
            class="wishlist-card-remove active"
            data-wishlist-toggle
            data-product-id="{{ $product->id }}"
            data-wishlisted="1"
            data-wishlist-store-url="{{ route('website.account.wishlist.store') }}"
            data-wishlist-destroy-url="{{ route('website.account.wishlist.destroy', $product->id) }}"
            aria-label="Remove {{ $product->name }} from wishlist"
        ><i class="fas fa-heart"></i></button>
        <a href="{{ route('website.products.show', $product->slug) }}"><img src="{{ $product->thumbnail_url ?: asset('assets/website/images/product-placeholder.svg') }}" alt="{{ $product->name }}"></a>
    </div>
    <div class="wishlist-product-body">
        <small>{{ $product->category?->name ?? 'ShopPilot' }}</small>
        <h3><a href="{{ route('website.products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <div class="wishlist-rating"><span><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span></div>
        <div class="wishlist-price"><strong>৳{{ number_format($currentPrice, 0) }}</strong>@if($hasSale)<del>৳{{ number_format($regularPrice, 0) }}</del>@endif</div>
        <div class="stock-note {{ $inStock ? 'in-stock' : 'out-of-stock' }}"><i class="fas {{ $inStock ? 'fa-check-circle' : 'fa-times-circle' }}"></i> {{ $inStock ? 'In stock' : 'Out of stock' }}</div>
        @if($inStock)
            <form action="{{ route('website.cart.items.store') }}" method="POST" class="product-card-cart-form">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><input type="hidden" name="quantity" value="1"><button type="submit" class="wishlist-cart-button"><i class="fas fa-cart-plus"></i> Add to Cart</button></form>
        @else
            <a href="{{ route('website.products.show', $product->slug) }}" class="wishlist-view-button"><i class="far fa-eye"></i> View Product</a>
        @endif
    </div>
</article>
