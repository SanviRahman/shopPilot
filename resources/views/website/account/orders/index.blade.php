@extends('website.layouts.app')
@section('title', 'My Orders | ShopPilot')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">@endpush
@section('content')
<section class="account-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.dashboard') }}">My Account</a><i class="fas fa-chevron-right"></i><strong>My Orders</strong></div>
    <div class="account-layout">@include('website.account.partials.sidebar')<div class="account-main">
        <header class="account-page-heading" data-account-reveal><span>ORDER HISTORY</span><h1>My Orders</h1><p>View and track all orders placed while logged into your ShopPilot account.</p></header>
        @include('website.account.orders.partials.ajax-region')
    </div></div>
</div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
