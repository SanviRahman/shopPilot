@extends('website.layouts.app')

@section('title', 'Frequently Asked Questions | ShopPilot')
@section('meta_description', 'Find answers to common ShopPilot questions about orders, shipping, payments, returns, accounts and support.')

@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/content-pages.css') }}">@endpush

@section('content')
<section class="content-page faq-page">
    <div class="container">
        <nav class="content-breadcrumb" data-content-reveal><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><strong>FAQ</strong></nav>

        <section class="faq-hero" data-content-reveal>
            <div><span class="content-kicker">HELP CENTER</span><h1>Frequently Asked <em>Questions</em></h1><p>Find quick answers about orders, shipping, payments, returns, accounts and support.</p></div>
            <div class="faq-support-art"><span><i class="fas fa-headset"></i></span><div><strong>We're here to help</strong><small>Support information is one click away.</small></div></div>
        </section>

        <div class="faq-search-shell" data-content-reveal><i class="fas fa-search"></i><input type="search" placeholder="Search FAQs... e.g. shipping, return, payment" data-faq-search><button type="button" data-faq-search-button>Search</button></div>

        <div class="faq-category-tabs" data-content-reveal>
            <button type="button" class="active" data-faq-category="all"><i class="fas fa-th-large"></i> All Questions</button>
            @foreach($faqGroups as $key => $group)<button type="button" data-faq-category="{{ $key }}"><i class="fas {{ $group['icon'] }}"></i> {{ $group['label'] }}</button>@endforeach
        </div>

        <div class="faq-layout">
            <main class="faq-main" data-content-reveal>
                <div class="faq-list-heading"><div><span>KNOWLEDGE BASE</span><h2>All Questions</h2></div><small><span data-faq-visible-count></span> answers available</small></div>
                <div class="faq-list" data-faq-list>
                    @foreach($faqGroups as $key => $group)
                        @foreach($group['items'] as $index => $item)
                            <article class="faq-item" data-faq-item data-category="{{ $key }}" data-search="{{ \Illuminate\Support\Str::lower($item['q'].' '.$item['a']) }}">
                                <button type="button" class="faq-question" data-faq-toggle aria-expanded="{{ $loop->parent->first && $loop->first ? 'true' : 'false' }}"><span class="faq-q-icon">Q</span><strong>{{ $item['q'] }}</strong><i class="fas fa-chevron-down"></i></button>
                                <div class="faq-answer {{ $loop->parent->first && $loop->first ? 'open' : '' }}"><p>{{ $item['a'] }}</p></div>
                            </article>
                        @endforeach
                    @endforeach
                </div>
                <div class="premium-empty-state faq-empty d-none" data-faq-empty><span class="empty-visual"><i class="fas fa-search"></i></span><h2>No matching questions</h2><p>Try another keyword or reset the FAQ filters.</p><button type="button" class="content-btn primary" data-faq-reset>Show All Questions</button></div>
            </main>

            <aside class="faq-help-card" data-content-reveal>
                <span class="side-card-kicker">NEED MORE HELP?</span><h2>Talk to our support team</h2><p>If the FAQ does not answer your question, use the Contact page to reach the active ShopPilot support information.</p>
                <div class="support-method"><span><i class="fas fa-phone"></i></span><div><small>Phone support</small><strong>Contact ShopPilot</strong></div></div>
                <div class="support-method"><span><i class="far fa-envelope"></i></span><div><small>Email support</small><strong>Send us a message</strong></div></div>
                <div class="support-method"><span><i class="fas fa-map-marker-alt"></i></span><div><small>Location</small><strong>View our map</strong></div></div>
                <a href="{{ route('website.contact') }}" class="content-btn primary full">Still Need Help? <i class="fas fa-arrow-right"></i></a>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/content-pages.js') }}" defer></script>@endpush
