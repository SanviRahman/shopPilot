@extends('website.layouts.app')

@section('title', 'Customer Login | ShopPilot')
@section('meta_description', 'Login to your ShopPilot customer account to manage orders and payments.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/auth.css') }}">
@endpush

@section('content')
<section class="customer-auth-page">
    <div class="container">
        <div class="auth-shell">
            <div class="auth-card" data-auth-reveal>
                <div class="auth-heading">
                    <span class="auth-heading-icon"><i class="fas fa-shopping-bag"></i></span>
                    <div><h1>Welcome Back</h1><p>Login to your account to continue</p></div>
                </div>

                <form method="POST" action="{{ route('website.login.store') }}" class="auth-form">
                    @csrf
                    <label class="field-label">Email Address <span>*</span></label>
                    <div class="auth-input {{ $errors->has('email') ? 'has-error' : '' }}">
                        <i class="far fa-envelope"></i>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="Enter your email address" required autofocus>
                    </div>
                    @error('email')<small class="field-error">{{ $message }}</small>@enderror

                    <div class="auth-label-row">
                        <label class="field-label">Password <span>*</span></label>
                        <a href="{{ route('website.password.request') }}">Forgot Password?</a>
                    </div>
                    <div class="auth-input {{ $errors->has('password') ? 'has-error' : '' }}">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required data-password-input>
                        <button type="button" class="password-toggle" aria-label="Show password" data-password-toggle><i class="far fa-eye"></i></button>
                    </div>
                    @error('password')<small class="field-error">{{ $message }}</small>@enderror

                    <label class="auth-check"><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}><span>Remember me</span></label>

                    <button class="auth-primary-button" type="submit">Login to Account <i class="fas fa-arrow-right"></i></button>
                </form>

                <p class="auth-switch">Don't have an account? <a href="{{ route('website.register') }}">Create an Account</a></p>
            </div>

            <aside class="auth-visual" data-auth-reveal data-auth-delay="120">
                <span class="auth-visual-badge"><i class="fas fa-heart"></i></span>
                <div class="auth-bag-art"><i class="fas fa-shopping-bag"></i></div>
                <h2>Shop Smarter<br><span>Live Better</span></h2>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Track your orders</li>
                    <li><i class="fas fa-check-circle"></i> Manage payment status</li>
                    <li><i class="fas fa-check-circle"></i> Faster checkout</li>
                    <li><i class="fas fa-check-circle"></i> View complete order history</li>
                </ul>
                <div class="auth-shopping-scene"><span></span><span></span><span></span></div>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/auth.js') }}" defer></script>
@endpush
