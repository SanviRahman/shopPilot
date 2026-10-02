@extends('website.layouts.app')

@php
    $regularPrice = (float) $product->regular_price;
    $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
    $hasSale = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
    $currentPrice = $hasSale ? $salePrice : $regularPrice;
    $savingAmount = $hasSale ? $regularPrice - $salePrice : 0;
    $discountPercent = $hasSale && $regularPrice > 0
        ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100)
        : 0;
    $inStock = (int) $product->stock_quantity > 0;
    $soldQuantity = (int) ($product->sold_quantity ?? 0);
    $images = $galleryImages->isNotEmpty()
        ? $galleryImages
        : collect([asset('assets/website/images/product-placeholder.svg')]);
    $primaryImage = $images->first();
@endphp

@section('title', $product->meta_title ?: ($product->name . ' | ShopPilot'))
@section('meta_description', $product->meta_description ?: ($product->short_description ?: 'View product details, price and availability on ShopPilot.'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/product-details.css') }}">
@endpush

@section('content')
<section class="product-details-page">
    <div class="container">
        <nav class="product-breadcrumb" aria-label="Breadcrumb" data-product-reveal>
            <a href="{{ route('website.home') }}">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="{{ route('website.shop') }}">Shop</a>
            @if($product->category)
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('website.shop', ['category' => [$product->category->slug]]) }}">{{ $product->category->name }}</a>
            @endif
            <i class="fas fa-chevron-right"></i>
            <span>{{ $product->name }}</span>
        </nav>

        <div class="product-primary-grid">
            <section class="product-gallery-panel" data-product-reveal="left">
                <div class="product-thumbnail-column" data-product-thumbnails>
                    @foreach($images as $index => $imageUrl)
                        <button
                            type="button"
                            class="product-thumb {{ $index === 0 ? 'active' : '' }}"
                            data-product-thumb
                            data-image="{{ $imageUrl }}"
                            aria-label="View image {{ $index + 1 }}"
                        >
                            <img src="{{ $imageUrl }}" alt="{{ $product->name }} image {{ $index + 1 }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                        </button>
                    @endforeach
                </div>

                <div class="product-main-visual">
                    @if($discountPercent > 0)
                        <span class="product-detail-discount">-{{ $discountPercent }}%</span>
                    @endif
                    <img
                        src="{{ $primaryImage }}"
                        alt="{{ $product->name }}"
                        data-product-main-image
                        data-product-zoom-image
                    >
                    <button type="button" class="product-zoom-button" data-product-zoom aria-label="Zoom product image">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
            </section>

            <section class="product-info-panel" data-product-reveal>
                <div class="product-status-row">
                    <span class="product-category-kicker">{{ $product->category?->name ?? 'ShopPilot Product' }}</span>
                    <span class="availability-pill {{ $inStock ? 'in-stock' : 'out-of-stock' }}">
                        <i class="fas {{ $inStock ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                        {{ $inStock ? 'In Stock' : 'Out of Stock' }}
                    </span>
                </div>

                <h1>{{ $product->name }}</h1>
                <p class="product-subtitle">{{ $product->short_description ?: 'Quality product selected for a reliable ShopPilot shopping experience.' }}</p>

                <div class="product-meta-line">
                    <span><i class="fas fa-barcode"></i> SKU: <strong>{{ $product->sku }}</strong></span>
                    @if($soldQuantity > 0)
                        <span><i class="fas fa-shopping-bag"></i> Sold <strong>{{ number_format($soldQuantity) }}</strong></span>
                    @endif
                    @if($product->featured)
                        <span class="featured-meta"><i class="fas fa-bolt"></i> Featured</span>
                    @endif
                </div>

                <div class="product-price-block">
                    <strong>৳{{ number_format($currentPrice, 0) }}</strong>
                    @if($hasSale)
                        <del>৳{{ number_format($regularPrice, 0) }}</del>
                        <span>Save ৳{{ number_format($savingAmount, 0) }} ({{ $discountPercent }}%)</span>
                    @endif
                </div>

                <div class="product-key-facts">
                    <div><span><i class="fas fa-layer-group"></i></span><p><strong>{{ $product->category?->name ?? 'General' }}</strong><small>Category</small></p></div>
                    <div><span><i class="fas fa-cubes"></i></span><p><strong>{{ number_format((int) $product->stock_quantity) }}</strong><small>Units in stock</small></p></div>
                    <div><span><i class="fas fa-award"></i></span><p><strong>{{ $product->featured ? 'Featured' : 'Standard' }}</strong><small>Product type</small></p></div>
                    <div><span><i class="fas fa-shield-alt"></i></span><p><strong>ShopPilot</strong><small>Quality checked</small></p></div>
                </div>

                <div class="product-note-box">
                    <i class="fas fa-info-circle"></i>
                    <p><strong>Simple product setup</strong><span>This product has one SKU and no variant options in the current ShopPilot P0 schema.</span></p>
                </div>

                <div class="product-inline-benefits">
                    <div><i class="fas fa-truck"></i><span><strong>Fast Delivery</strong><small>Nationwide service</small></span></div>
                    <div><i class="fas fa-undo-alt"></i><span><strong>Easy Returns</strong><small>7 days return policy</small></span></div>
                    <div><i class="fas fa-lock"></i><span><strong>Secure Payment</strong><small>Protected checkout</small></span></div>
                </div>
            </section>

            <aside class="product-order-card" data-product-reveal="right">
                <div class="order-card-head">
                    <span>Order Now</span>
                    @if($inStock && $product->stock_quantity <= 5)
                        <small>Only {{ $product->stock_quantity }} left</small>
                    @elseif($inStock)
                        <small>Ready to order</small>
                    @else
                        <small class="danger">Currently unavailable</small>
                    @endif
                </div>

                <div class="quantity-control" data-product-quantity data-max="{{ max(1, (int) $product->stock_quantity) }}">
                    <button type="button" data-qty-minus aria-label="Decrease quantity"><i class="fas fa-minus"></i></button>
                    <input type="number" value="1" min="1" max="{{ max(1, (int) $product->stock_quantity) }}" data-qty-input aria-label="Quantity">
                    <button type="button" data-qty-plus aria-label="Increase quantity"><i class="fas fa-plus"></i></button>
                </div>

                <button type="button" class="product-primary-action" data-coming-soon="Cart" {{ $inStock ? '' : 'disabled' }}>
                    <i class="fas fa-cart-plus"></i> {{ $inStock ? 'Add to Cart' : 'Out of Stock' }}
                </button>
                <button type="button" class="product-secondary-action" data-coming-soon="Buy Now" {{ $inStock ? '' : 'disabled' }}>
                    Buy Now
                </button>
                <button type="button" class="product-wishlist-action" data-coming-soon="Wishlist">
                    <i class="far fa-heart"></i> Add to Wishlist
                </button>

                <div class="order-assurance-list">
                    <div><span><i class="fas fa-truck"></i></span><p><strong>Free Delivery</strong><small>On orders over ৳1,000</small></p></div>
                    <div><span><i class="fas fa-lock"></i></span><p><strong>Secure Payment</strong><small>Protected payment flow</small></p></div>
                    <div><span><i class="fas fa-sync-alt"></i></span><p><strong>Easy Returns</strong><small>7 days return policy</small></p></div>
                    <div><span><i class="fas fa-headset"></i></span><p><strong>24/7 Support</strong><small>Dedicated customer care</small></p></div>
                </div>
            </aside>
        </div>

        <section class="product-details-tabs" data-product-reveal>
            <div class="product-tab-nav" role="tablist">
                <button type="button" class="active" data-product-tab="description" role="tab" aria-selected="true">Product Details</button>
                <button type="button" data-product-tab="specifications" role="tab" aria-selected="false">Specifications</button>
                <button type="button" data-product-tab="shipping" role="tab" aria-selected="false">Shipping &amp; Returns</button>
            </div>

            <div class="product-tab-panels">
                <article class="product-tab-panel active" data-product-panel="description">
                    <div class="product-description-copy">
                        <span class="section-mini-label">Product Description</span>
                        <h2>Everything you need to know</h2>
                        <p>{!! nl2br(e($product->description ?: ($product->short_description ?: 'Detailed product information will be available here.'))) !!}</p>
                        <ul>
                            <li><i class="fas fa-check"></i> Active ShopPilot catalog product</li>
                            <li><i class="fas fa-check"></i> Current stock visibility</li>
                            <li><i class="fas fa-check"></i> Transparent regular and sale pricing</li>
                            <li><i class="fas fa-check"></i> Secure checkout flow ready for the next frontend module</li>
                        </ul>
                    </div>
                    <div class="product-story-card">
                        <img src="{{ $primaryImage }}" alt="{{ $product->name }} detail visual" loading="lazy">
                        <div>
                            <span>{{ $product->category?->name ?? 'ShopPilot Selection' }}</span>
                            <strong>Quality selected.<br>Simple shopping.</strong>
                            <small>ShopPilot</small>
                        </div>
                    </div>
                </article>

                <article class="product-tab-panel" data-product-panel="specifications" hidden>
                    <div class="specification-grid">
                        <div><span>Product Name</span><strong>{{ $product->name }}</strong></div>
                        <div><span>SKU</span><strong>{{ $product->sku }}</strong></div>
                        <div><span>Category</span><strong>{{ $product->category?->name ?? 'Uncategorized' }}</strong></div>
                        <div><span>Availability</span><strong>{{ $inStock ? 'In Stock' : 'Out of Stock' }}</strong></div>
                        <div><span>Stock Quantity</span><strong>{{ number_format((int) $product->stock_quantity) }}</strong></div>
                        <div><span>Product Type</span><strong>{{ $product->featured ? 'Featured' : 'Standard' }}</strong></div>
                        <div><span>Regular Price</span><strong>৳{{ number_format($regularPrice, 0) }}</strong></div>
                        <div><span>Current Price</span><strong>৳{{ number_format($currentPrice, 0) }}</strong></div>
                    </div>
                </article>

                <article class="product-tab-panel" data-product-panel="shipping" hidden>
                    <div class="shipping-policy-grid">
                        <div><span><i class="fas fa-truck"></i></span><h3>Fast Delivery</h3><p>Delivery timing depends on destination and order processing status.</p></div>
                        <div><span><i class="fas fa-box-open"></i></span><h3>Careful Packaging</h3><p>Orders are prepared for safe handling before dispatch.</p></div>
                        <div><span><i class="fas fa-undo-alt"></i></span><h3>7-Day Returns</h3><p>Return eligibility is subject to the store return policy and product condition.</p></div>
                        <div><span><i class="fas fa-headset"></i></span><h3>Need Help?</h3><p>ShopPilot support is available to help with ordering and delivery questions.</p></div>
                    </div>
                </article>
            </div>
        </section>

        @if($relatedProducts->isNotEmpty())
            <section class="related-products-section" data-product-reveal>
                <div class="related-section-head">
                    <div>
                        <span>You may also like</span>
                        <h2>Related Products</h2>
                    </div>
                    <div class="related-controls">
                        <a href="{{ route('website.shop', $product->category ? ['category' => [$product->category->slug]] : []) }}">View All <i class="fas fa-arrow-right"></i></a>
                        <button type="button" data-related-prev aria-label="Previous related products"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" data-related-next aria-label="Next related products"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <div class="related-products-track" data-related-track>
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="related-product-item">
                            @include('website.partials.product-card', ['product' => $relatedProduct])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</section>

<div class="product-lightbox" data-product-lightbox aria-hidden="true">
    <button type="button" class="product-lightbox-close" data-product-lightbox-close aria-label="Close image preview"><i class="fas fa-times"></i></button>
    <img src="{{ $primaryImage }}" alt="{{ $product->name }} enlarged preview" data-product-lightbox-image>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/product-details.js') }}" defer></script>
@endpush
