@extends('errors.layout')
@section('title', 'Page Not Found')
@section('code', '404')
@section('icon', 'fa-search')
@section('heading', 'Oops! Page Not Found')
@section('message', 'The page you are looking for does not exist or may have been moved. Use search, continue shopping, or return to the homepage.')
@section('actions')<a href="/" class="error-btn error-btn-primary"><i class="fas fa-home"></i> Go to Homepage</a><a href="/shop" class="error-btn error-btn-secondary"><i class="fas fa-shopping-cart"></i> Continue Shopping</a>@endsection
