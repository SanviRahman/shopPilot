@extends('website.layouts.app')

@section('title', 'Create Account | ShopPilot')
@section('meta_description', 'Create your ShopPilot customer account for faster shopping and order tracking.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/auth.css') }}">
@endpush

@section('content')
<section class="customer-auth-page">
    <div class="container">
        <div class="auth-shell register-shell">
            <div class="auth-card" data-auth-reveal>
                <div class="auth-heading">
                    <span class="auth-heading-icon"><i class="fas fa-user-plus"></i></span>
                    <div><h1>Create an Account</h1><p>Join ShopPilot and start your shopping journey</p></div>
                </div>

                <form method="POST" action="{{ route('website.register.store') }}" class="auth-form">
                    @csrf
                    <label class="field-label">Full Name <span>*</span></label>
                    <div class="auth-input {{ $errors->has('name') ? 'has-error' : '' }}"><i class="far fa-user"></i><input type="text" name="name" value="{{ old('name') }}" maxlength="120" placeholder="Enter your full name" required autofocus></div>
                    @error('name')<small class="field-error">{{ $message }}</small>@enderror

                    <label class="field-label">Email Address <span>*</span></label>
                    <div class="auth-input {{ $errors->has('email') ? 'has-error' : '' }}"><i class="far fa-envelope"></i><input type="email" name="email" value="{{ old('email') }}" maxlength="191" placeholder="Enter your email address" required></div>
                    @error('email')<small class="field-error">{{ $message }}</small>@enderror

                    <label class="field-label">Password <span>*</span></label>
                    <div class="auth-input {{ $errors->has('password') ? 'has-error' : '' }}"><i class="fas fa-lock"></i><input type="password" name="password" autocomplete="new-password" placeholder="Create a strong password" required data-password-input><button type="button" class="password-toggle" data-password-toggle><i class="far fa-eye"></i></button></div>
                    @error('password')<small class="field-error">{{ $message }}</small>@enderror

                    <label class="field-label">Confirm Password <span>*</span></label>
                    <div class="auth-input"><i class="fas fa-lock"></i><input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Confirm your password" required data-password-input><button type="button" class="password-toggle" data-password-toggle><i class="far fa-eye"></i></button></div>

                    <label class="auth-check terms-check"><input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}><span>I agree to the Terms &amp; Conditions and Privacy Policy</span></label>
                    @error('terms')<small class="field-error">{{ $message }}</small>@enderror

                    <button class="auth-primary-button" type="submit">Create Account <i class="fas fa-arrow-right"></i></button>
                </form>

                <p class="auth-switch">Already have an account? <a href="{{ route('website.login') }}">Login</a></p>
            </div>

            <aside class="auth-visual register-visual" data-auth-reveal data-auth-delay="120">
                <span class="auth-visual-badge"><i class="fas fa-percentage"></i></span>
                <div class="auth-bag-art"><i class="fas fa-user-check"></i></div>
                <h2>Join ShopPilot<br><span>Today!</span></h2>
                <p>Create an account and enjoy a better shopping experience.</p>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Track every order</li>
                    <li><i class="fas fa-check-circle"></i> Monitor payment verification</li>
                    <li><i class="fas fa-check-circle"></i> Faster future checkout</li>
                    <li><i class="fas fa-check-circle"></i> Secure account access</li>
                </ul>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/auth.js') }}" defer></script>
@endpush
