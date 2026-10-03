@extends('website.layouts.app')

@section('title', $policy['title'].' | ShopPilot')
@section('meta_description', $policy['intro'])

@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/content-pages.css') }}">@endpush

@section('content')
<section class="content-page policy-page">
    <div class="container">
        <nav class="content-breadcrumb" data-content-reveal><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><span>Policies</span><i class="fas fa-chevron-right"></i><strong>{{ $policy['title'] }}</strong></nav>
        <div class="policy-layout">
            <aside class="policy-sidebar" data-content-reveal>
                <div class="policy-nav-card"><span class="side-card-kicker">CUSTOMER CARE</span><h2>Our Policies</h2>@foreach($policies as $slug => $item)<a href="{{ route('website.policy', $slug) }}" class="{{ $slug === $policySlug ? 'active' : '' }}"><span><i class="fas {{ $item['icon'] }}"></i>{{ $item['title'] }}</span><i class="fas fa-chevron-right"></i></a>@endforeach<a href="{{ route('website.faq') }}"><span><i class="far fa-question-circle"></i>FAQ</span><i class="fas fa-chevron-right"></i></a></div>
                <div class="policy-help-card"><span><i class="fas fa-headset"></i></span><h3>Need more help?</h3><p>Our support team can help with questions about orders, payments or store policies.</p><a href="{{ route('website.contact') }}" class="content-btn primary full">Contact Us</a></div>
            </aside>

            <main class="policy-content premium-panel" data-content-reveal>
                <header class="policy-heading"><span class="content-kicker">SHOPPILOT POLICY</span><h1>{{ $policy['title'] }}</h1><small>Last updated: {{ $policy['updated'] }}</small><p>{{ $policy['intro'] }}</p></header>
                <div class="policy-sections">
                    @foreach($policy['sections'] as $index => $section)
                        <section class="policy-section" data-content-reveal><div class="policy-number">{{ $index + 1 }}</div><div class="policy-section-body"><div class="policy-section-title"><span class="policy-icon {{ $section['tone'] }}"><i class="fas {{ $section['icon'] }}"></i></span><div><h2>{{ $section['title'] }}</h2><p>{{ $section['text'] }}</p></div></div><ul>@foreach($section['points'] as $point)<li><i class="fas fa-check-circle"></i><span>{{ $point }}</span></li>@endforeach</ul></div></section>
                    @endforeach
                </div>
                <div class="policy-note"><i class="fas fa-info-circle"></i><div><strong>Need clarification?</strong><p>Contact ShopPilot support before placing an order if you have a question about a product, delivery option or policy condition.</p></div><a href="{{ route('website.contact') }}">Contact Support <i class="fas fa-arrow-right"></i></a></div>
            </main>
        </div>
    </div>
</section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/content-pages.js') }}" defer></script>@endpush
