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

        @include('website.cart.partials.ajax-content')
    </div>
</section>

<div class="cart-confirm-modal" data-cart-confirm-modal aria-hidden="true">
    <div class="cart-confirm-backdrop" data-cart-confirm-close></div>
    <section
        class="cart-confirm-dialog"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="cartConfirmTitle"
        aria-describedby="cartConfirmMessage"
        tabindex="-1"
    >
        <button type="button" class="cart-confirm-close" data-cart-confirm-close aria-label="Close confirmation">
            <i class="fas fa-times"></i>
        </button>

        <div class="cart-confirm-icon">
            <i class="far fa-trash-alt"></i>
        </div>

        <span class="cart-confirm-eyebrow">Please confirm</span>
        <h2 id="cartConfirmTitle" data-cart-confirm-title>Clear your shopping cart?</h2>
        <p id="cartConfirmMessage" data-cart-confirm-message>This action will update the products in your cart.</p>
        <div class="cart-confirm-note" data-cart-confirm-note>
            <i class="fas fa-shield-alt"></i>
            <span>Your account and checkout information stay safe.</span>
        </div>

        <div class="cart-confirm-actions">
            <button type="button" class="cart-confirm-cancel" data-cart-confirm-close>Keep shopping</button>
            <button type="button" class="cart-confirm-submit" data-cart-confirm-submit>Yes, continue</button>
        </div>
    </section>
</div>

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
