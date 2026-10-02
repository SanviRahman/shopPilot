@extends('website.layouts.app')
@section('title', 'Track Order | ShopPilot')
@section('meta_description', 'Track your ShopPilot order using your order number and email or phone number.')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">
<link rel="stylesheet" href="{{ asset('assets/website/css/track-order.css') }}">
@endpush

@section('content')
<section class="track-order-page"><div class="container">
    <div class="account-breadcrumb track-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><strong>Track Order</strong></div>

    <section class="track-search-hero" data-track-reveal>
        <div class="track-search-copy">
            <div class="track-title-row"><span class="track-title-icon"><i class="fas fa-shopping-bag"></i></span><div><span class="track-eyebrow">DELIVERY STATUS</span><h1>Track Your Order</h1><p>Enter your order details to see the latest delivery and payment status.</p></div></div>

            <form action="{{ route('website.track-order.lookup') }}" method="POST" class="track-order-form" data-track-order-form>
                @csrf
                <label><span>Order Number <b>*</b></span><div class="track-input-shell"><i class="fas fa-receipt"></i><input type="text" name="order_number" value="{{ old('order_number', request('order_number')) }}" placeholder="e.g. ORD-20261003-ABC123" maxlength="40" required autocomplete="off"></div>@error('order_number')<small class="field-error">{{ $message }}</small>@enderror</label>
                <label><span>Email or Phone Number <b>*</b></span><div class="track-input-shell"><i class="far fa-user"></i><input type="text" name="contact" value="{{ old('contact', auth('web')->user()?->email) }}" placeholder="Enter order email or phone" maxlength="191" required autocomplete="email"></div>@error('contact')<small class="field-error">{{ $message }}</small>@enderror</label>
                <button type="submit" class="track-submit-button"><i class="fas fa-search-location"></i> Track Order <i class="fas fa-arrow-right"></i></button>
            </form>

            <div class="track-privacy-note"><i class="fas fa-lock"></i><span>For privacy, the order number must match the email address or phone number used at checkout.</span></div>
        </div>

        <div class="track-hero-art" aria-hidden="true">
            <div class="track-map-pin"><i class="fas fa-map-marker-alt"></i></div>
            <div class="track-road"><span></span><span></span></div>
            <div class="track-parcel"><i class="fas fa-box"></i></div>
            <div class="track-card-art"><i class="fas fa-clipboard-list"></i></div>
            <div class="track-tree tree-one"></div><div class="track-tree tree-two"></div>
        </div>
    </section>

    <div data-track-result-region>
        @isset($order)
            @include('website.tracking.partials.result', ['order' => $order, 'timeline' => $timeline])
        @else
            <section class="track-empty-state" data-track-reveal>
                <span><i class="fas fa-route"></i></span><div><h2>Your delivery journey will appear here</h2><p>Use the form above to securely load your order timeline, items and payment status.</p></div>
            </section>
        @endisset
    </div>
</div></section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/track-order.js') }}" defer></script>@endpush
