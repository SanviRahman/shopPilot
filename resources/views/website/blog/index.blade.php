@extends('website.layouts.app')
@section('title', 'ShopPilot Blog | Shopping Guides & Stories')
@section('meta_description', 'Read ShopPilot articles, product guides, shopping tips and ecommerce stories.')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/content-pages.css') }}">@endpush
@section('content')
<section class="content-page blog-page"><div class="container"><nav class="content-breadcrumb" data-content-reveal><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><strong>Blogs</strong></nav>@include('website.blog.partials.ajax-region')</div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/content-pages.js') }}" defer></script>@endpush
