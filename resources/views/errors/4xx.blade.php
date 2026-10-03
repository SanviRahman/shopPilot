@extends('errors.layout')
@section('title', 'Request Error')
@section('code', '4xx')
@section('icon', 'fa-exclamation-circle')
@section('heading', 'We Could Not Open This Page')
@section('message', 'The request could not be completed. Please check the address or return to a safe ShopPilot page.')
@section('actions')<a href="/" class="error-btn error-btn-primary"><i class="fas fa-home"></i> Go to Homepage</a><a href="/shop" class="error-btn error-btn-secondary"><i class="fas fa-shopping-cart"></i> Continue Shopping</a>@endsection
