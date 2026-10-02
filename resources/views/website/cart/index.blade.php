@extends('website.layouts.app')

@section('title', 'Shopping Cart | ShopPilot')
@section('meta_description', 'Review your ShopPilot cart, update quantities, apply a coupon and prepare for checkout.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/cart.css') }}">
@endpush

@section('content')
<section class="cart-page">
    <div class="container">
        <nav class="cart-breadcrumb" aria-label="Breadcrumb" data-cart-reveal>
            <a href="{{ route('website.home') }}">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Cart</span>
        </nav>

        <div class="cart-page-heading" data-cart-reveal>
            <div>
                <span class="cart-eyebrow">Your Bag</span>
                <h1>Your Shopping <em>Cart</em></h1>
                <p>Review your items, update quantities and get ready for checkout.</p>
            </div>

            <ol class="checkout-progress" aria-label="Checkout progress">
                <li class="active"><span>1</span><strong>Cart</strong></li>
                <li><span>2</span><strong>Checkout</strong></li>
                <li><span>3</span><strong>Payment</strong></li>
                <li><span>4</span><strong>Order Placed</strong></li>
            </ol>
        </div>

        @if($errors->any())
            <div class="cart-alert error" data-cart-reveal>
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Please check your cart</strong>
                    <span>{{ $errors->first() }}</span>
                </div>
            </div>
        @elseif($couponInvalidMessage)
            <div class="cart-alert warning" data-cart-reveal>
                <i class="fas fa-ticket-alt"></i>
                <div>
                    <strong>Coupon removed</strong>
                    <span>{{ $couponInvalidMessage }}</span>
                </div>
            </div>
        @endif

        @if($items->isEmpty())
            <section class="cart-empty-state" data-cart-reveal>
                <div class="cart-empty-icon"><i class="fas fa-shopping-bag"></i></div>
                <span>Your cart is waiting</span>
                <h2>Start adding products you love</h2>
                <p>Browse our catalog and add products to your cart. Your cart is stored in your current session.</p>
                <a href="{{ route('website.shop') }}" class="cart-primary-link"><i class="fas fa-arrow-left"></i> Start Shopping</a>
            </section>
        @else
            <div class="cart-layout">
                <section class="cart-items-panel" data-cart-reveal="left">
                    <div class="cart-items-toolbar">
                        <label class="cart-select-all">
                            <input type="checkbox" data-cart-select-all checked>
                            <span class="cart-checkbox"><i class="fas fa-check"></i></span>
                            <strong>Select All</strong>
                            <small>({{ $count }} {{ \Illuminate\Support\Str::plural('item', $count) }})</small>
                        </label>

                        <button type="submit" form="cartRemoveSelectedForm" class="remove-selected-button" data-remove-selected>
                            <i class="far fa-trash-alt"></i> Remove Selected
                        </button>
                    </div>

                    <form id="cartRemoveSelectedForm" action="{{ route('website.cart.items.remove-selected') }}" method="POST" data-confirm-form="Remove the selected products from your cart?">
                        @csrf
                        @method('DELETE')
                    </form>

                    <div class="cart-items-list">
                        @foreach($items as $index => $item)
                            @php
                                $product = $item['product'];
                                $image = $product->thumbnail_url ?: asset('assets/website/images/product-placeholder.svg');
                            @endphp
                            <article class="cart-item" data-cart-item data-cart-reveal style="--cart-delay: {{ min($index * 65, 320) }}ms">
                                <label class="cart-item-check" aria-label="Select {{ $product->name }}">
                                    <input
                                        type="checkbox"
                                        name="product_ids[]"
                                        value="{{ $product->id }}"
                                        form="cartRemoveSelectedForm"
                                        data-cart-item-check
                                        checked
                                    >
                                    <span class="cart-checkbox"><i class="fas fa-check"></i></span>
                                </label>

                                <a href="{{ route('website.products.show', $product->slug) }}" class="cart-item-image">
                                    @if($item['discount_percent'] > 0)
                                        <span>-{{ $item['discount_percent'] }}%</span>
                                    @endif
                                    <img src="{{ $image }}" alt="{{ $product->name }}" loading="lazy">
                                </a>

                                <div class="cart-item-copy">
                                    <span>{{ $product->category?->name ?? 'ShopPilot Product' }}</span>
                                    <h2><a href="{{ route('website.products.show', $product->slug) }}">{{ $product->name }}</a></h2>
                                    <div class="cart-item-meta">
                                        <span><i class="fas fa-barcode"></i> {{ $product->sku }}</span>
                                        <span class="{{ $item['in_stock'] ? 'available' : 'unavailable' }}">
                                            <i class="fas {{ $item['in_stock'] ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                            {{ $item['in_stock'] ? 'In Stock' : 'Out of Stock' }}
                                        </span>
                                    </div>
                                    @if($item['in_stock'] && $item['stock_quantity'] <= 5)
                                        <small class="cart-low-stock">Only {{ $item['stock_quantity'] }} left in stock</small>
                                    @endif
                                </div>

                                <div class="cart-item-price">
                                    <strong>৳{{ number_format($item['unit_price'], 0) }}</strong>
                                    @if($item['has_sale'])
                                        <del>৳{{ number_format($item['regular_price'], 0) }}</del>
                                    @endif
                                    <small>each</small>
                                </div>

                                <form
                                    action="{{ route('website.cart.items.update', $product->id) }}"
                                    method="POST"
                                    class="cart-quantity-form"
                                    data-cart-quantity-form
                                    data-max="{{ max(1, $item['stock_quantity']) }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" data-cart-qty-minus aria-label="Decrease {{ $product->name }} quantity"><i class="fas fa-minus"></i></button>
                                    <input type="number" name="quantity" min="1" max="{{ max(1, $item['stock_quantity']) }}" value="{{ $item['quantity'] }}" data-cart-qty-input aria-label="Quantity for {{ $product->name }}">
                                    <button type="button" data-cart-qty-plus aria-label="Increase {{ $product->name }} quantity"><i class="fas fa-plus"></i></button>
                                </form>

                                <div class="cart-item-line-total">
                                    <small>Line total</small>
                                    <strong>৳{{ number_format($item['line_total'], 0) }}</strong>
                                </div>

                                <div class="cart-item-actions">
                                    <button type="button" class="cart-heart" data-coming-soon="Wishlist" aria-label="Move {{ $product->name }} to wishlist"><i class="far fa-heart"></i></button>
                                    <form action="{{ route('website.cart.items.destroy', $product->id) }}" method="POST" data-confirm-form="Remove {{ $product->name }} from your cart?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cart-remove" aria-label="Remove {{ $product->name }}"><i class="far fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="cart-items-footer">
                        <a href="{{ route('website.shop') }}"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                        <form action="{{ route('website.cart.clear') }}" method="POST" data-confirm-form="Clear every product from your cart?">
                            @csrf
                            @method('DELETE')
                            <button type="submit"><i class="far fa-trash-alt"></i> Clear Cart</button>
                        </form>
                    </div>
                </section>

                <aside class="cart-summary-column" data-cart-reveal="right">
                    <section class="cart-summary-card" data-cart-summary>
                        <button type="button" class="cart-summary-mobile-toggle" data-cart-summary-toggle aria-expanded="true">
                            <span><i class="fas fa-receipt"></i> Order Summary</span>
                            <i class="fas fa-chevron-up"></i>
                        </button>

                        <div class="cart-summary-body" data-cart-summary-body>
                            <h2>Order Summary</h2>
                            <div class="summary-line"><span>Subtotal ({{ $count }} {{ \Illuminate\Support\Str::plural('item', $count) }})</span><strong>৳{{ number_format($subtotal, 0) }}</strong></div>
                            <div class="summary-line discount"><span>Discount</span><strong>-৳{{ number_format($discount, 0) }}</strong></div>
                            <div class="summary-line shipping"><span>Shipping</span><strong>{{ $shippingLabel }}</strong></div>
                            <div class="summary-total"><span>Total</span><strong>৳{{ number_format($grandTotal, 0) }}</strong></div>

                            <button
                                type="button"
                                class="proceed-checkout-button"
                                data-coming-soon="Checkout"
                                {{ $canCheckout ? '' : 'disabled' }}
                            >
                                <i class="fas fa-lock"></i>
                                Proceed to Checkout
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <small class="secure-checkout-note"><i class="fas fa-shield-alt"></i> 100% secure checkout flow</small>

                            @if($hasUnavailableItems)
                                <div class="checkout-block-note"><i class="fas fa-exclamation-triangle"></i> Remove unavailable items before checkout.</div>
                            @endif
                        </div>
                    </section>

                    <section class="cart-coupon-card">
                        <button type="button" class="coupon-toggle" data-coupon-toggle aria-expanded="{{ $coupon ? 'true' : 'false' }}">
                            <span><i class="fas fa-ticket-alt"></i> Have a Coupon Code?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="coupon-body {{ $coupon ? 'open' : '' }}" data-coupon-body>
                            @if($coupon)
                                <div class="applied-coupon">
                                    <div><span>Applied</span><strong>{{ $coupon->code }}</strong><small>You saved ৳{{ number_format($discount, 0) }}</small></div>
                                    <form action="{{ route('website.cart.coupon.remove') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Remove</button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('website.cart.coupon.apply') }}" method="POST" class="coupon-form">
                                    @csrf
                                    <input type="text" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="Enter coupon code" maxlength="80" required>
                                    <button type="submit">Apply</button>
                                </form>
                                <small>One valid coupon can be used per order.</small>
                            @endif
                        </div>
                    </section>

                    <section class="cart-assurance-card">
                        <div><span><i class="fas fa-truck"></i></span><p><strong>Fast Delivery</strong><small>Delivery options confirmed at checkout</small></p></div>
                        <div><span><i class="fas fa-lock"></i></span><p><strong>Secure Payment</strong><small>Protected payment workflow</small></p></div>
                        <div><span><i class="fas fa-sync-alt"></i></span><p><strong>Easy Returns</strong><small>7 days return policy</small></p></div>
                        <div><span><i class="fas fa-headset"></i></span><p><strong>24/7 Support</strong><small>Dedicated customer support</small></p></div>
                    </section>
                </aside>
            </div>
        @endif

        @if($recommendedProducts->isNotEmpty())
            <section class="cart-recommendations" data-cart-reveal>
                <div class="cart-section-head">
                    <div>
                        <span>You may also like</span>
                        <h2>Recommended Products</h2>
                    </div>
                    <a href="{{ route('website.shop') }}">View All <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="cart-related-track" data-cart-related-track>
                    @foreach($recommendedProducts as $product)
                        <div class="cart-related-item">
                            @include('website.partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</section>

<section class="quality-strip-section cart-quality-section">
    <div class="container">
        <div class="quality-strip">
            <div><span><i class="far fa-gem"></i></span><p><strong>Quality Products</strong><small>Carefully selected items</small></p></div>
            <div><span><i class="fas fa-award"></i></span><p><strong>Trusted Shopping</strong><small>Clear and reliable cart totals</small></p></div>
            <div><span><i class="fas fa-truck"></i></span><p><strong>Delivery Options</strong><small>Confirmed during checkout</small></p></div>
            <div><span><i class="fas fa-headset"></i></span><p><strong>Dedicated Support</strong><small>We are here to help</small></p></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/cart.js') }}" defer></script>
@endpush
