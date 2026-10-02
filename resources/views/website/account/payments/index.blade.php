@extends('website.layouts.app')
@section('title', 'Payment Submissions | ShopPilot')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">@endpush
@section('content')
<section class="account-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.dashboard') }}">My Account</a><i class="fas fa-chevron-right"></i><strong>Payment Submissions</strong></div>
    <div class="account-layout">@include('website.account.partials.sidebar')<div class="account-main">
        <header class="account-page-heading" data-account-reveal><span>PAYMENTS</span><h1>Payment Submissions</h1><p>Track payment verification and resubmit payment details when an order requires action.</p></header>
        @include('website.account.payments.partials.ajax-region')
    </div></div>
</div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
