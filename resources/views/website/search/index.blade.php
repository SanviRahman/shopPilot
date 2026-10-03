@extends('website.layouts.app')

@section('title', 'Search Results for '.$searchTerm.' | ShopPilot')
@section('meta_description', 'Search ShopPilot products and refine results by category, price, availability and sorting.')

@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/shop.css') }}"><link rel="stylesheet" href="{{ asset('assets/website/css/content-pages.css') }}">@endpush

@section('content')
<section class="search-page" data-search-page>
    <div class="container">
        <nav class="content-breadcrumb search-breadcrumb" data-shop-breadcrumb data-shop-reveal><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.shop') }}">Shop</a><i class="fas fa-chevron-right"></i><strong>Search Results</strong></nav>
        <header class="search-page-heading" data-shop-reveal><div><span class="content-kicker">PRODUCT SEARCH</span><h1>Search Results for <em data-search-title>“{{ $searchTerm }}”</em></h1><p><strong data-shop-total-count>{{ number_format($products->total()) }} products found</strong> — refine the results using the filters below.</p></div><a href="{{ route('website.shop') }}" class="content-btn secondary"><i class="fas fa-store"></i> Browse All Products</a></header>
        @if($categories->isNotEmpty())<div class="related-searches" data-shop-reveal><span><i class="fas fa-search"></i> Explore Categories</span>@foreach($categories->take(6) as $category)<a href="{{ route('website.shop', ['q' => $searchTerm, 'category' => [$category->slug]]) }}">{{ $category->name }}</a>@endforeach</div>@endif
    </div>
    <section class="shop-main-section search-results-main"><div class="container">@include('website.shop.partials.ajax-region')</div></section>
    <section class="quality-strip-section shop-quality-section"><div class="container"><div class="quality-strip"><div><span><i class="far fa-gem"></i></span><p><strong>Quality Products</strong><small>Carefully selected items</small></p></div><div><span><i class="fas fa-award"></i></span><p><strong>Trusted Shopping</strong><small>Clear product information</small></p></div><div><span><i class="fas fa-truck"></i></span><p><strong>Fast Delivery</strong><small>Delivery options at checkout</small></p></div><div><span><i class="fas fa-headset"></i></span><p><strong>Dedicated Support</strong><small>We are here to help</small></p></div></div></div></section>
</section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/shop.js') }}" defer></script><script src="{{ asset('assets/website/js/content-pages.js') }}" defer></script>@endpush
