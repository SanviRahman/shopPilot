@extends('website.layouts.app')
@section('title', 'Forgot Password | ShopPilot')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/auth.css') }}">@endpush
@section('content')
<section class="customer-auth-page compact-auth-page"><div class="container"><div class="auth-card compact-auth-card" data-auth-reveal>
    <div class="auth-heading"><span class="auth-heading-icon"><i class="fas fa-key"></i></span><div><h1>Forgot Password?</h1><p>We'll email you a secure reset link.</p></div></div>
    <form method="POST" action="{{ route('website.password.email') }}" class="auth-form">@csrf
        <label class="field-label">Email Address <span>*</span></label>
        <div class="auth-input {{ $errors->has('email') ? 'has-error' : '' }}"><i class="far fa-envelope"></i><input type="email" name="email" value="{{ old('email') }}" required placeholder="Enter your account email"></div>
        @error('email')<small class="field-error">{{ $message }}</small>@enderror
        <button class="auth-primary-button" type="submit">Send Reset Link <i class="fas fa-paper-plane"></i></button>
    </form>
    <p class="auth-switch"><a href="{{ route('website.login') }}"><i class="fas fa-arrow-left"></i> Back to Login</a></p>
</div></div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/auth.js') }}" defer></script>@endpush
