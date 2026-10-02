@extends('website.layouts.app')

@section('title', 'Order Placed | ShopPilot')
@section('meta_description', 'Your ShopPilot order has been placed and payment information submitted for verification.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/checkout.css') }}">
@endpush

@section('content')
<section class="thankyou-page">
    <div class="container">
        <div class="thankyou-card" data-checkout-reveal>
            <div class="thankyou-icon"><i class="fas fa-check"></i></div>
            <span>Order submitted successfully</span>
            <h1>Thank you for your order!</h1>
            <p>Your payment information has been submitted for verification. This does not mean the payment is verified yet.</p>

            <div class="thankyou-order-number"><small>Order Number</small><strong>{{ $order->order_number }}</strong></div>

            <div class="thankyou-grid">
                <div><span>Total</span><strong>৳{{ number_format((float) $order->grand_total, 0) }}</strong></div>
                <div><span>Order Status</span><strong>{{ ucfirst($order->order_status) }}</strong></div>
                <div><span>Payment Status</span><strong>{{ ucfirst($order->payment_status) }}</strong></div>
                <div><span>Payment Method</span><strong>{{ $order->paymentSubmission?->paymentMethod?->name ?? 'Manual Payment' }}</strong></div>
            </div>

            <div class="thankyou-actions">
                <a href="{{ route('website.shop') }}" class="checkout-secondary-button"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                @if(auth('web')->check() && (int) $order->user_id === (int) auth('web')->id())
                    <a href="{{ route('website.account.orders.show', $order->order_number) }}" class="checkout-primary-button"><i class="fas fa-box"></i> View My Order</a>
                @else
                    <a href="{{ route('website.home') }}" class="checkout-primary-button"><i class="fas fa-home"></i> Back to Home</a>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/checkout.js') }}" defer></script>
@endpush
