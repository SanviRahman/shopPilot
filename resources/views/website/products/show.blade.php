@extends('website.layouts.app')

@php
    $initialVariant = collect($variantOptions)->first(fn ($variant) => (int) $variant['stock_quantity'] > 0)
        ?? collect($variantOptions)->first();
    $regularPrice = (float) ($initialVariant['regular_price'] ?? $product->regular_price);
    $salePrice = $initialVariant !== null ? ($initialVariant['sale_price'] !== null ? (float) $initialVariant['sale_price'] : null) : ($product->sale_price !== null ? (float) $product->sale_price : null);
    $hasSale = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
    $currentPrice = $hasSale ? $salePrice : $regularPrice;
    $savingAmount = $hasSale ? $regularPrice - $salePrice : 0;
    $discountPercent = $hasSale && $regularPrice > 0
        ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100)
        : 0;
    $inStock = $product->product_type === 'variable'
        ? collect($variantOptions)->sum('stock_quantity') > 0
        : (int) $product->stock_quantity > 0;
    $initialStock = $product->product_type === 'variable'
        ? (int) ($initialVariant['stock_quantity'] ?? 0)
        : (int) $product->stock_quantity;
    $soldQuantity = (int) ($product->sold_quantity ?? 0);
    $images = $galleryImages->isNotEmpty()
        ? $galleryImages
        : collect([asset('assets/website/images/product-placeholder.svg')]);
    if (!empty($initialVariant['image_url'])) {
        $images = collect([$initialVariant['image_url']])->merge($images)->unique()->values();
    }
    $primaryImage = $images->first();
    $isWishlisted = in_array((int) $product->id, array_map('intval', $wishlistProductIds ?? []), true);
    $attributeGroups = collect($variantOptions)
        ->flatMap(fn ($variant) => $variant['attributes'])
        ->groupBy('attribute_id')
        ->map(fn ($values) => [
            'id' => $values->first()['attribute_id'],
            'name' => $values->first()['attribute_name'],
            'display_type' => $values->first()['display_type'],
            'values' => $values->unique('value_id')->values(),
        ])
        ->values();
    $initialAttributeValues = collect($initialVariant['attributes'] ?? [])->pluck('value_id')->map(fn ($id) => (int) $id)->all();
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
                        <span class="product-detail-discount" data-product-detail-discount>-{{ $discountPercent }}%</span>
                    @endif
                    <img
                        src="{{ $primaryImage }}"
                        alt="{{ $product->name }}"
                        data-product-main-image
                        data-product-base-image="{{ $galleryImages->first() ?? asset('assets/website/images/product-placeholder.svg') }}"
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
                    <span class="availability-pill {{ $inStock ? 'in-stock' : 'out-of-stock' }}" data-product-availability>
                        <i class="fas {{ $inStock ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                        {{ $inStock ? 'In Stock' : 'Out of Stock' }}
                    </span>
                </div>

                <h1>{{ $product->name }}</h1>
                <p class="product-subtitle">{{ $product->short_description ?: 'Quality product selected for a reliable ShopPilot shopping experience.' }}</p>

                <div class="product-meta-line">
                    <span><i class="fas fa-barcode"></i> SKU: <strong data-product-sku>{{ $initialVariant['sku'] ?? $product->sku }}</strong></span>
                    @if($soldQuantity > 0)
                        <span><i class="fas fa-shopping-bag"></i> Sold <strong>{{ number_format($soldQuantity) }}</strong></span>
                    @endif
                    @if($product->featured)
                        <span class="featured-meta"><i class="fas fa-bolt"></i> Featured</span>
                    @endif
                </div>

                <div class="product-price-block" data-product-price>
                    <strong data-product-current-price>৳{{ number_format($currentPrice, 0) }}</strong>
                    <del data-product-regular-price class="{{ $hasSale ? '' : 'd-none' }}">৳{{ number_format($regularPrice, 0) }}</del>
                    <span data-product-saving class="{{ $hasSale ? '' : 'd-none' }}">Save ৳{{ number_format($savingAmount, 0) }} ({{ $discountPercent }}%)</span>
                </div>

                @if($product->product_type === 'variable' && $attributeGroups->isNotEmpty())
                    <div class="product-variant-options" data-product-variant-options>
                        @foreach($attributeGroups as $group)
                            @php($selectedGroupValue = collect($initialVariant['attributes'] ?? [])->firstWhere('attribute_id', $group['id'])['value'] ?? 'Choose an option')
                            <div class="product-variant-group" data-variant-group data-attribute-id="{{ $group['id'] }}">
                                <div class="variant-group-heading"><strong>{{ $group['name'] }}</strong><span data-variant-selected-label>{{ $selectedGroupValue }}</span></div>
                                <div class="variant-option-list">
                                    @if($group['display_type'] === 'select')
                                        <select class="form-control product-variant-select" data-variant-select>
                                            <option value="">Choose {{ strtolower($group['name']) }}</option>
                                            @foreach($group['values'] as $value)
                                                <option value="{{ $value['value_id'] }}" data-value-label="{{ $value['value'] }}" @selected(in_array((int) $value['value_id'], $initialAttributeValues, true))>{{ $value['value'] }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        @foreach($group['values'] as $value)
                                            <button type="button"
                                                class="variant-option-button {{ in_array((int) $value['value_id'], $initialAttributeValues, true) ? 'active' : '' }} {{ $group['display_type'] === 'color' ? 'is-color' : '' }}"
                                                data-variant-option
                                                data-value-id="{{ $value['value_id'] }}"
                                                data-value-label="{{ $value['value'] }}"
                                                aria-pressed="{{ in_array((int) $value['value_id'], $initialAttributeValues, true) ? 'true' : 'false' }}"
                                                @if($group['display_type'] === 'color') style="--variant-color: {{ $value['color_code'] ?: '#dbe4ef' }}" @endif
                                            >
                                                @if($group['display_type'] === 'color')<i class="variant-color-swatch"></i><span>{{ $value['value'] }}</span>@else{{ $value['value'] }}@endif
                                            </button>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <p class="variant-selection-message" data-variant-message>{{ $initialVariant ? 'Your selected combination is ready.' : 'Choose your preferred options to see availability.' }}</p>
                    </div>
                @endif

                <div class="product-key-facts">
                    <div><span><i class="fas fa-layer-group"></i></span><p><strong>{{ $product->category?->name ?? 'General' }}</strong><small>Category</small></p></div>
                    <div><span><i class="fas fa-cubes"></i></span><p><strong data-product-stock>{{ number_format($initialStock) }}</strong><small>Units available</small></p></div>
                    <div><span><i class="fas fa-award"></i></span><p><strong>{{ $product->product_type === 'variable' ? 'Variable' : ($product->featured ? 'Featured' : 'Standard') }}</strong><small>Product type</small></p></div>
                    <div><span><i class="fas fa-shield-alt"></i></span><p><strong>ShopPilot</strong><small>Quality checked</small></p></div>
                </div>

                @if($product->product_type === 'variable')
                    <div class="product-note-box">
                        <i class="fas fa-info-circle"></i>
                        <p><strong>Choose your preferred fit</strong><span>Prices and availability update when you select a product option.</span></p>
                    </div>
                @endif

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
                        <small data-order-stock-label>Only {{ $initialStock }} left</small>
                    @elseif($inStock)
                        <small data-order-stock-label>Ready to order</small>
                    @else
                        <small class="danger" data-order-stock-label>Currently unavailable</small>
                    @endif
                </div>

                <form action="{{ route('website.cart.items.store') }}" method="POST" class="product-order-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if($product->product_type === 'variable')
                        <input type="hidden" name="variant_id" value="{{ $initialVariant['id'] ?? '' }}" data-selected-variant-id>
                    @endif

                    <div class="quantity-control" data-product-quantity data-max="{{ max(1, $initialStock) }}">
                        <button type="button" data-qty-minus aria-label="Decrease quantity"><i class="fas fa-minus"></i></button>
                        <input type="number" name="quantity" value="1" min="1" max="{{ max(1, $initialStock) }}" data-qty-input aria-label="Quantity">
                        <button type="button" data-qty-plus aria-label="Increase quantity"><i class="fas fa-plus"></i></button>
                    </div>

                    <button type="submit" name="purchase_mode" value="cart" class="product-primary-action" data-product-add-to-cart {{ $inStock ? '' : 'disabled' }}>
                        <i class="fas fa-cart-plus"></i> <span>{{ $inStock ? 'Add to Cart' : 'Out of Stock' }}</span>
                    </button>
                    <button type="submit" name="purchase_mode" value="buy_now" class="product-secondary-action" data-product-buy-now {{ $inStock ? '' : 'disabled' }}>
                        Buy Now
                    </button>
                </form>
                @auth('web')
                    <button
                        type="button"
                        class="product-wishlist-action {{ $isWishlisted ? 'active' : '' }}"
                        data-wishlist-toggle
                        data-product-id="{{ $product->id }}"
                        data-wishlisted="{{ $isWishlisted ? '1' : '0' }}"
                        data-wishlist-store-url="{{ route('website.account.wishlist.store') }}"
                        data-wishlist-destroy-url="{{ route('website.account.wishlist.destroy', $product->id) }}"
                    >
                        <i class="{{ $isWishlisted ? 'fas' : 'far' }} fa-heart"></i>
                        <span data-wishlist-label>{{ $isWishlisted ? 'Saved to Wishlist' : 'Add to Wishlist' }}</span>
                    </button>
                @else
                    <a href="{{ route('website.login') }}" class="product-wishlist-action"><i class="far fa-heart"></i> Login to Save</a>
                @endauth

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
                        <div><span>SKU</span><strong>{{ $initialVariant['sku'] ?? $product->sku }}</strong></div>
                        <div><span>Category</span><strong>{{ $product->category?->name ?? 'Uncategorized' }}</strong></div>
                        <div><span>Availability</span><strong>{{ $inStock ? 'In Stock' : 'Out of Stock' }}</strong></div>
                        <div><span>Stock Quantity</span><strong>{{ number_format((int) $product->stock_quantity) }}</strong></div>
                        <div><span>Product Type</span><strong>{{ ucfirst($product->product_type ?? 'simple') }}</strong></div>
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

<script type="application/json" id="product-variant-data">@json($variantOptions)</script>

<div class="product-lightbox" data-product-lightbox aria-hidden="true">
    <button type="button" class="product-lightbox-close" data-product-lightbox-close aria-label="Close image preview"><i class="fas fa-times"></i></button>
    <img src="{{ $primaryImage }}" alt="{{ $product->name }} enlarged preview" data-product-lightbox-image>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/product-details.js') }}" defer></script>
@endpush
