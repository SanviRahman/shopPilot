@extends('website.layouts.app')
@section('title', 'Reset Password | ShopPilot')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/auth.css') }}">@endpush
@section('content')
<section class="customer-auth-page compact-auth-page"><div class="container"><div class="auth-card compact-auth-card" data-auth-reveal>
    <div class="auth-heading"><span class="auth-heading-icon"><i class="fas fa-shield-alt"></i></span><div><h1>Create New Password</h1><p>Use a strong password for your ShopPilot account.</p></div></div>
    <form method="POST" action="{{ route('website.password.update') }}" class="auth-form">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="field-label">Email Address <span>*</span></label><div class="auth-input"><i class="far fa-envelope"></i><input type="email" name="email" value="{{ old('email', $email) }}" required></div>
        @error('email')<small class="field-error">{{ $message }}</small>@enderror
        <label class="field-label">New Password <span>*</span></label><div class="auth-input"><i class="fas fa-lock"></i><input type="password" name="password" required data-password-input><button type="button" class="password-toggle" data-password-toggle><i class="far fa-eye"></i></button></div>
        @error('password')<small class="field-error">{{ $message }}</small>@enderror
        <label class="field-label">Confirm Password <span>*</span></label><div class="auth-input"><i class="fas fa-lock"></i><input type="password" name="password_confirmation" required data-password-input></div>
        <button class="auth-primary-button" type="submit">Reset Password <i class="fas fa-check"></i></button>
    </form>
</div></div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/auth.js') }}" defer></script>@endpush
