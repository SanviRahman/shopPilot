@extends('website.layouts.app')

@section('title', 'Checkout | ShopPilot')
@section('meta_description', 'Complete your ShopPilot order with secure delivery details and manual payment submission.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/checkout.css') }}">
@endpush

@php
    $selectedDelivery = old('delivery_method', 'standard');
    $selectedPaymentId = (int) old('payment_method_id', $paymentMethods->first()?->id ?? 0);
    $initialStep = ($errors->has('payment_method_id') || $errors->has('transaction_id')) ? 2 : 1;
@endphp

@section('content')
<section class="checkout-page" data-checkout-root data-initial-step="{{ $initialStep }}">
    <div class="container">
        <nav class="checkout-breadcrumb" aria-label="Breadcrumb" data-checkout-reveal>
            <a href="{{ route('website.home') }}">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="{{ route('website.cart.index') }}">Cart</a>
            <i class="fas fa-chevron-right"></i>
            <span>Checkout</span>
        </nav>

        <div class="checkout-heading" data-checkout-reveal>
            <div>
                <span class="checkout-eyebrow">Secure Checkout</span>
                <h1>Complete Your <em>Order</em></h1>
                <p>Enter delivery details, submit your payment information and review everything before placing the order.</p>
            </div>

            <ol class="checkout-steps" aria-label="Checkout progress" data-checkout-progress>
                <li class="active" data-progress-step="1"><span>1</span><strong>Shipping</strong></li>
                <li data-progress-step="2"><span>2</span><strong>Payment</strong></li>
                <li data-progress-step="3"><span>3</span><strong>Review</strong></li>
                <li data-progress-step="4"><span>4</span><strong>Order Placed</strong></li>
            </ol>
        </div>

        @if($errors->any())
            <div class="checkout-alert" data-checkout-reveal>
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Please review your checkout information.</strong>
                    <span>{{ $errors->first() }}</span>
                </div>
            </div>
        @endif

        <form action="{{ route('website.checkout.store') }}" method="POST" class="checkout-form" data-checkout-form novalidate>
            @csrf

            <div class="checkout-layout">
                <div class="checkout-main-column">
                    <section class="checkout-step-panel active" data-checkout-step="1" data-checkout-reveal="left">
                        <div class="checkout-card checkout-shipping-card">
                            <div class="checkout-card-title">
                                <span><i class="fas fa-shipping-fast"></i></span>
                                <div>
                                    <h2>Shipping Information</h2>
                                    <p>Enter the final delivery details for this order.</p>
                                </div>
                            </div>

                            <div class="checkout-field-grid two">
                                <label class="checkout-field">
                                    <span>Full Name <b>*</b></span>
                                    <div class="input-shell"><i class="far fa-user"></i><input type="text" name="buyer_name" value="{{ old('buyer_name', $buyerDefaults['name']) }}" placeholder="Enter your full name" maxlength="150" required autocomplete="name"></div>
                                    @error('buyer_name')<small class="field-error">{{ $message }}</small>@enderror
                                </label>

                                <label class="checkout-field">
                                    <span>Phone Number <b>*</b></span>
                                    <div class="input-shell"><i class="fas fa-phone-alt"></i><input type="tel" name="buyer_phone" value="{{ old('buyer_phone', $buyerDefaults['phone'] ?? '') }}" placeholder="017XXXXXXXX" maxlength="30" required autocomplete="tel"></div>
                                    @error('buyer_phone')<small class="field-error">{{ $message }}</small>@enderror
                                </label>
                            </div>

                            <label class="checkout-field">
                                <span>Email Address <b>*</b></span>
                                <div class="input-shell"><i class="far fa-envelope"></i><input type="email" name="buyer_email" value="{{ old('buyer_email', $buyerDefaults['email']) }}" placeholder="Enter your email address" maxlength="191" required autocomplete="email"></div>
                                @error('buyer_email')<small class="field-error">{{ $message }}</small>@enderror
                            </label>

                            <div class="checkout-subhead">
                                <h3>Delivery Address</h3>
                                <p>Your address is stored as an order snapshot and will not change if your profile changes later.</p>
                            </div>

                            <div class="checkout-address-type">
                                <span>Address Type</span>
                                <label><input type="radio" name="address_type" value="home" {{ old('address_type', 'home') === 'home' ? 'checked' : '' }}><span></span>Home</label>
                                <label><input type="radio" name="address_type" value="office" {{ old('address_type') === 'office' ? 'checked' : '' }}><span></span>Office</label>
                            </div>

                            <label class="checkout-field">
                                <span>Full Address <b>*</b></span>
                                <div class="input-shell"><i class="fas fa-map-marker-alt"></i><input type="text" name="shipping_address" value="{{ old('shipping_address', $buyerDefaults['address'] ?? '') }}" placeholder="House no, road no, area, landmark" required autocomplete="street-address"></div>
                                @error('shipping_address')<small class="field-error">{{ $message }}</small>@enderror
                            </label>

                            <div class="checkout-field-grid three">
                                <label class="checkout-field">
                                    <span>Division <b>*</b></span>
                                    <div class="input-shell select-shell"><i class="fas fa-map"></i><select name="division" required>
                                        <option value="">Select division</option>
                                        @foreach(['Dhaka','Chattogram','Rajshahi','Khulna','Barishal','Sylhet','Rangpur','Mymensingh'] as $division)
                                            <option value="{{ $division }}" {{ old('division', $buyerDefaults['division'] ?? '') === $division ? 'selected' : '' }}>{{ $division }}</option>
                                        @endforeach
                                    </select></div>
                                    @error('division')<small class="field-error">{{ $message }}</small>@enderror
                                </label>

                                <label class="checkout-field">
                                    <span>District <b>*</b></span>
                                    <div class="input-shell"><i class="fas fa-city"></i><input type="text" name="district" value="{{ old('district', $buyerDefaults['district'] ?? '') }}" placeholder="Enter district" required></div>
                                    @error('district')<small class="field-error">{{ $message }}</small>@enderror
                                </label>

                                <label class="checkout-field">
                                    <span>Upazila / Thana <b>*</b></span>
                                    <div class="input-shell"><i class="fas fa-map-pin"></i><input type="text" name="upazila" value="{{ old('upazila', $buyerDefaults['upazila'] ?? '') }}" placeholder="Enter upazila / thana" required></div>
                                    @error('upazila')<small class="field-error">{{ $message }}</small>@enderror
                                </label>
                            </div>

                            <div class="checkout-field-grid two">
                                <label class="checkout-field">
                                    <span>Postal Code <small>(Optional)</small></span>
                                    <div class="input-shell"><i class="far fa-calendar-alt"></i><input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="Enter postal code" maxlength="20"></div>
                                </label>

                                <label class="checkout-field">
                                    <span>Order Note <small>(Optional)</small></span>
                                    <div class="input-shell"><i class="far fa-sticky-note"></i><input type="text" name="customer_note" value="{{ old('customer_note') }}" placeholder="Delivery instruction or note" maxlength="1000"></div>
                                </label>
                            </div>
                        </div>

                        <div class="checkout-card delivery-method-card">
                            <div class="checkout-card-title">
                                <span><i class="fas fa-truck"></i></span>
                                <div><h2>Delivery Method</h2><p>Choose your preferred delivery option.</p></div>
                            </div>

                            <div class="delivery-method-grid">
                                @foreach($shippingMethods as $key => $method)
                                    <label class="delivery-option {{ $selectedDelivery === $key ? 'selected' : '' }}" data-delivery-option>
                                        <input type="radio" name="delivery_method" value="{{ $key }}" data-delivery-radio data-fee="{{ (float) $method['fee'] }}" {{ $selectedDelivery === $key ? 'checked' : '' }} required>
                                        <span class="delivery-radio"></span>
                                        <span class="delivery-icon"><i class="fas {{ $method['icon'] ?? 'fa-truck' }}"></i></span>
                                        <span class="delivery-copy"><strong>{{ $method['label'] }}</strong><small>{{ $method['description'] }}</small></span>
                                        <span class="delivery-fee">{{ (float) $method['fee'] > 0 ? '৳'.number_format((float) $method['fee'], 0) : 'Free' }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('delivery_method')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="checkout-step-actions">
                            <a href="{{ route('website.cart.index') }}" class="checkout-back-link"><i class="fas fa-arrow-left"></i> Back to Cart</a>
                            <button type="button" class="checkout-primary-button" data-go-step="2">Continue to Payment <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </section>

                    <section class="checkout-step-panel" data-checkout-step="2" aria-hidden="true">
                        <div class="checkout-card payment-card">
                            <div class="checkout-card-title">
                                <span><i class="fas fa-wallet"></i></span>
                                <div><h2>Payment Information</h2><p>Select an active manual payment method and submit your transaction ID.</p></div>
                            </div>

                            <div class="payment-method-grid">
                                @foreach($paymentMethods as $method)
                                    <label class="payment-method-option {{ $selectedPaymentId === $method->id ? 'selected' : '' }}" data-payment-option>
                                        <input type="radio" name="payment_method_id" value="{{ $method->id }}" {{ $selectedPaymentId === $method->id ? 'checked' : '' }} required data-method-radio>
                                        <span class="payment-method-mark">{{ strtoupper(substr($method->name, 0, 1)) }}</span>
                                        <span><strong>{{ $method->name }}</strong><small>{{ ucfirst(str_replace('_', ' ', $method->account_type)) }}</small></span>
                                        <i class="fas fa-check-circle"></i>
                                    </label>
                                @endforeach
                            </div>
                            @error('payment_method_id')<small class="field-error">{{ $message }}</small>@enderror

                            @foreach($paymentMethods as $method)
                                <div class="payment-instruction {{ $selectedPaymentId === $method->id ? 'active' : '' }}" data-payment-instruction="{{ $method->id }}">
                                    <div class="payment-number-block">
                                        <span>Send payment to</span>
                                        <strong>{{ $method->account_number }}</strong>
                                        <small>{{ $method->name }} · {{ ucfirst(str_replace('_', ' ', $method->account_type)) }}</small>
                                    </div>
                                    <p><i class="fas fa-info-circle"></i> {{ $method->instruction }}</p>
                                </div>
                            @endforeach

                            <label class="checkout-field transaction-field">
                                <span>Transaction ID <b>*</b></span>
                                <div class="input-shell"><i class="fas fa-receipt"></i><input type="text" name="transaction_id" value="{{ old('transaction_id') }}" placeholder="Enter the transaction ID after payment" maxlength="100" required autocomplete="off"></div>
                                <small>Submission does not mean verified payment. ShopPilot staff will verify it after the order is placed.</small>
                                @error('transaction_id')<small class="field-error">{{ $message }}</small>@enderror
                            </label>
                        </div>

                        <div class="checkout-step-actions">
                            <button type="button" class="checkout-secondary-button" data-go-step="1"><i class="fas fa-arrow-left"></i> Back to Shipping</button>
                            <button type="button" class="checkout-primary-button" data-go-step="3">Review Order <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </section>

                    <section class="checkout-step-panel" data-checkout-step="3" aria-hidden="true">
                        <div class="checkout-card review-card">
                            <div class="checkout-card-title">
                                <span><i class="fas fa-clipboard-check"></i></span>
                                <div><h2>Review Your Order</h2><p>Confirm the final delivery and payment information before placing your order.</p></div>
                            </div>

                            <div class="review-grid">
                                <div class="review-block"><span>Deliver To</span><strong data-review-name>—</strong><p data-review-contact>—</p></div>
                                <div class="review-block"><span>Delivery Address</span><strong data-review-area>—</strong><p data-review-address>—</p></div>
                                <div class="review-block"><span>Delivery Method</span><strong data-review-delivery>—</strong><p data-review-delivery-fee>—</p></div>
                                <div class="review-block"><span>Payment</span><strong data-review-payment>—</strong><p data-review-transaction>—</p></div>
                            </div>

                            <div class="review-notice"><i class="fas fa-shield-alt"></i><p><strong>Payment verification happens after submission.</strong><span>Placing this order creates a submitted payment record; it does not mark the payment as verified.</span></p></div>
                        </div>

                        <div class="checkout-step-actions">
                            <button type="button" class="checkout-secondary-button" data-go-step="2"><i class="fas fa-arrow-left"></i> Back to Payment</button>
                            <button type="submit" class="checkout-primary-button place-order-button" data-place-order><i class="fas fa-lock"></i> Place Order Securely</button>
                        </div>
                    </section>
                </div>

                <aside class="checkout-summary-column" data-checkout-reveal="right">
                    <section class="checkout-summary-card" data-checkout-summary>
                        <button type="button" class="checkout-summary-toggle" data-summary-toggle aria-expanded="true"><span><i class="fas fa-shopping-bag"></i> Order Summary</span><i class="fas fa-chevron-up"></i></button>
                        <div class="checkout-summary-body" data-summary-body>
                            <div class="summary-title"><h2>Order Summary</h2><span>{{ $count }} {{ \Illuminate\Support\Str::plural('item', $count) }}</span></div>

                            <div class="checkout-summary-items">
                                @foreach($items as $item)
                                    @php $product = $item['product']; $image = $product->thumbnail_url ?: asset('assets/website/images/product-placeholder.svg'); @endphp
                                    <article class="checkout-summary-item">
                                        <div class="summary-item-image">@if($item['discount_percent'] > 0)<span>-{{ $item['discount_percent'] }}%</span>@endif<img src="{{ $image }}" alt="{{ $product->name }}"></div>
                                        <div><strong>{{ $product->name }}</strong><small>{{ $product->category?->name ?? 'ShopPilot' }} · Qty: {{ $item['quantity'] }}</small></div>
                                        <div class="summary-item-price"><strong>৳{{ number_format($item['line_total'], 0) }}</strong>@if($item['has_sale'])<del>৳{{ number_format($item['regular_price'] * $item['quantity'], 0) }}</del>@endif</div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="checkout-coupon-box" data-checkout-coupon>
                                <button type="button" class="checkout-coupon-toggle" data-checkout-coupon-toggle aria-expanded="{{ $coupon ? 'true' : 'false' }}"><span><i class="fas fa-ticket-alt"></i> Have a Coupon Code?</span><i class="fas fa-chevron-down"></i></button>
                                <div class="checkout-coupon-body {{ $coupon ? 'open' : '' }}" data-checkout-coupon-body>
                                    <div class="checkout-coupon-applied {{ $coupon ? '' : 'hidden' }}" data-coupon-applied>
                                        <p><span>Applied</span><strong data-coupon-code>{{ $coupon?->code }}</strong><small>You saved ৳<b data-coupon-saving>{{ number_format($discount, 0) }}</b></small></p>
                                        <button type="button" data-coupon-remove>Remove</button>
                                    </div>
                                    <div class="checkout-coupon-form {{ $coupon ? 'hidden' : '' }}" data-coupon-form>
                                        <input type="text" maxlength="80" placeholder="Enter coupon code" data-coupon-input>
                                        <button type="button" data-coupon-apply data-url="{{ route('website.checkout.coupon.apply') }}">Apply</button>
                                    </div>
                                    <small class="coupon-feedback" data-coupon-feedback></small>
                                </div>
                            </div>

                            <div class="checkout-totals" data-subtotal="{{ $subtotal }}" data-discount="{{ $discount }}">
                                <div><span>Subtotal ({{ $count }} items)</span><strong data-total-subtotal>৳{{ number_format($subtotal, 0) }}</strong></div>
                                <div class="discount"><span>Discount</span><strong data-total-discount>-৳{{ number_format($discount, 0) }}</strong></div>
                                <div><span>Shipping Fee</span><strong data-total-shipping>—</strong></div>
                                <div class="checkout-grand-total"><span>Total Amount</span><strong data-total-grand>৳{{ number_format($grandTotal, 0) }}</strong></div>
                            </div>
                            <div class="checkout-savings {{ $discount > 0 ? '' : 'hidden' }}" data-savings-line><i class="fas fa-check-circle"></i> You save ৳<strong data-savings>{{ number_format($discount, 0) }}</strong> on this order</div>
                        </div>
                    </section>

                    <section class="checkout-trust-card">
                        <h3>Why Shop With Us?</h3>
                        <div><span><i class="fas fa-lock"></i></span><p><strong>Secure Payment</strong><small>Protected manual payment workflow</small></p></div>
                        <div><span><i class="fas fa-truck"></i></span><p><strong>Fast & Reliable Delivery</strong><small>Choose the delivery option that fits you</small></p></div>
                        <div><span><i class="fas fa-sync-alt"></i></span><p><strong>Easy Returns</strong><small>7-day return policy</small></p></div>
                        <div><span><i class="fas fa-headset"></i></span><p><strong>24/7 Support</strong><small>We are here to help</small></p></div>
                    </section>
                </aside>
            </div>
        </form>
    </div>
</section>

<section class="quality-strip-section checkout-quality-section">
    <div class="container"><div class="quality-strip">
        <div><span><i class="far fa-gem"></i></span><p><strong>Quality Products</strong><small>Carefully selected catalog</small></p></div>
        <div><span><i class="fas fa-user-shield"></i></span><p><strong>Trusted Checkout</strong><small>Server-authoritative totals</small></p></div>
        <div><span><i class="fas fa-truck"></i></span><p><strong>Reliable Delivery</strong><small>Clear delivery choices</small></p></div>
        <div><span><i class="fas fa-headset"></i></span><p><strong>Dedicated Support</strong><small>Help when you need it</small></p></div>
    </div></div>
</section>
@endsection

@push('scripts')
<script>
window.ShopPilotCheckout = {
    removeCouponUrl: @json(route('website.checkout.coupon.remove')),
    csrf: @json(csrf_token()),
};
</script>
<script src="{{ asset('assets/website/js/checkout.js') }}" defer></script>
@endpush
