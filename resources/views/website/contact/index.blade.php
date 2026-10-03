@extends('website.layouts.app')

@section('title', 'Contact Us | ShopPilot')
@section('meta_description', 'Contact ShopPilot support by phone, email or the secure customer message form.')

@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/content-pages.css') }}">@endpush

@section('content')
@php
    $displayName = $contact?->name ?: 'ShopPilot Support';
    $displayEmail = $contact?->email ?: 'support@shoppilot.com';
    $displayPhone = $contact?->phone ?: '+880 1712-345678';
    $displayMapUrl = $contact?->map_url;
@endphp
<section class="content-page contact-page">
    <div class="container">
        <nav class="content-breadcrumb" data-content-reveal><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><strong>Contact Us</strong></nav>

        <section class="contact-hero" data-content-reveal>
            <div><span class="content-kicker">LET'S TALK</span><h1>Contact <em>Us</em></h1><p>Have a question about products, orders, payments or anything else? Our support experience is designed to make getting help simple.</p></div>
            <div class="contact-hero-art"><span class="contact-headset"><i class="fas fa-headset"></i></span><div><strong>We're here to help</strong><small>Send a message or use the active contact details below.</small></div></div>
        </section>

        <section class="contact-info-grid" data-content-reveal>
            <article><span><i class="fas fa-phone"></i></span><div><small>Call Us</small><a href="tel:{{ preg_replace('/[^+0-9]/', '', $displayPhone) }}">{{ $displayPhone }}</a><p>Use the active support number.</p></div></article>
            <article><span><i class="far fa-envelope"></i></span><div><small>Email Us</small><a href="mailto:{{ $displayEmail }}">{{ $displayEmail }}</a><p>We will review your support message.</p></div></article>
            <article><span><i class="fas fa-user-shield"></i></span><div><small>Support Contact</small><strong>{{ $displayName }}</strong><p>Official active contact configuration.</p></div></article>
            <article><span><i class="far fa-clock"></i></span><div><small>Customer Support</small><strong>Online Support</strong><p>Use the form any time.</p></div></article>
        </section>

        <div class="contact-layout">
            <section class="contact-form-card premium-panel" data-content-reveal>
                <div class="content-heading compact"><div><span>SEND A MESSAGE</span><h2>How can we help?</h2><p>Complete the form and your message will be stored for the ShopPilot support team.</p></div></div>
                <form action="{{ route('website.contact.store') }}" method="POST" class="contact-form" data-contact-form novalidate>
                    @csrf
                    <input type="text" name="company" value="" class="contact-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <label class="contact-field"><span>Full Name *</span><div><i class="far fa-user"></i><input type="text" name="name" value="{{ old('name', auth('web')->user()?->name) }}" maxlength="100" placeholder="Enter your full name" required></div></label>
                    <label class="contact-field"><span>Email Address *</span><div><i class="far fa-envelope"></i><input type="email" name="email" value="{{ old('email', auth('web')->user()?->email) }}" maxlength="191" placeholder="Enter your email address" required></div></label>
                    <label class="contact-field"><span>Phone Number</span><div><i class="fas fa-phone-alt"></i><input type="text" name="phone" value="{{ old('phone', auth('web')->user()?->phone_number) }}" maxlength="30" placeholder="Enter your phone number"></div></label>
                    <label class="contact-field"><span>Subject *</span><div><i class="fas fa-list-ul"></i><select name="subject" required><option value="">Select a subject</option><option value="Order Support">Order Support</option><option value="Payment Support">Payment Support</option><option value="Product Question">Product Question</option><option value="Return or Refund">Return or Refund</option><option value="Account Support">Account Support</option><option value="Other">Other</option></select></div></label>
                    <label class="contact-field full"><span>Message *</span><div class="textarea-shell"><i class="far fa-comment-alt"></i><textarea name="message" rows="7" maxlength="1500" placeholder="Type your message here..." required data-contact-message>{{ old('message') }}</textarea><small><span data-contact-character-count>0</span>/1500</small></div></label>
                    <div class="contact-submit-row full"><div><i class="fas fa-shield-alt"></i><span>Your message is protected by Laravel validation and request throttling.</span></div><button type="submit" class="content-btn primary" data-contact-submit><i class="far fa-paper-plane"></i> Send Message</button></div>
                </form>
            </section>

            <aside class="contact-side" data-content-reveal>
                <section class="map-card"><div class="map-card-head"><div><span>OUR LOCATION</span><h2>Find us on the map</h2></div>@if($displayMapUrl)<a href="{{ $displayMapUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Open map in a new tab"><i class="fas fa-external-link-alt"></i></a>@endif</div>@if($mapEmbedUrl)<iframe src="{{ $mapEmbedUrl }}" title="ShopPilot map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe><div class="map-fallback"><i class="fas fa-map-marker-alt"></i><span>Map preview loaded from your saved map link. Use Open Map for the full map.</span></div>@elseif($displayMapUrl)<div class="map-placeholder map-external-only"><span><i class="fas fa-map-marked-alt"></i></span><h3>Open the saved map location</h3><p>The saved map link could not be converted to an embeddable preview on this server. The link still opens normally. For an always-live preview, save the Google Maps Embed URL or copied Embed iframe from Admin → Contacts.</p><a href="{{ $displayMapUrl }}" target="_blank" rel="noopener noreferrer" class="content-btn primary"><i class="fas fa-map-marker-alt"></i> Open Map</a></div>@else<div class="map-placeholder"><span><i class="fas fa-map-marked-alt"></i></span><h3>Map URL not configured yet</h3><p>Add an active map URL from Admin → Contacts and it will appear here.</p></div>@endif</section>
                <section class="quick-help-card"><span><i class="far fa-question-circle"></i></span><div><strong>Need a quick answer?</strong><p>Browse common questions before sending a message.</p></div><a href="{{ route('website.faq') }}">View FAQs <i class="fas fa-arrow-right"></i></a></section>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/content-pages.js') }}" defer></script>@endpush
