@extends('errors.layout')
@section('title', 'Access Denied')
@section('code', '403')
@section('icon', 'fa-lock')
@section('heading', 'Access Denied')
@section('message', 'You do not have permission to access this page. Sign in with the correct account or return to a safe page.')
@section('actions')<a href="/" class="error-btn error-btn-primary"><i class="fas fa-home"></i> Go to Homepage</a><a href="/login" class="error-btn error-btn-secondary"><i class="fas fa-user"></i> Customer Login</a>@endsection
