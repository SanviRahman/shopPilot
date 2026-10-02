@extends('website.layouts.app')
@section('title', 'Change Password | ShopPilot')
@section('meta_description', 'Securely change your ShopPilot account password.')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">@endpush

@section('content')
<section class="account-page password-settings-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.dashboard') }}">My Account</a><i class="fas fa-chevron-right"></i><strong>Change Password</strong></div>

    <div class="account-layout">
        @include('website.account.partials.sidebar')

        <main class="account-main">
            <header class="account-page-heading" data-account-reveal><span>ACCOUNT SECURITY</span><h1>Change Password</h1><p>Use a strong, unique password to keep your ShopPilot account secure.</p></header>

            <div class="password-layout-grid">
                <form action="{{ route('website.account.password.update') }}" method="POST" class="account-panel password-change-form" data-password-form data-account-reveal>
                    @csrf @method('PUT')
                    <div class="security-tips-card">
                        <span class="security-tips-icon"><i class="fas fa-lock"></i></span>
                        <div><h3>Security Tips</h3><ul><li>Use at least 8 characters.</li><li>Mix uppercase and lowercase letters.</li><li>Include numbers and a special character.</li><li>Never reuse an old or shared password.</li></ul></div>
                    </div>

                    <label class="profile-field password-field"><span>Current Password <b>*</b></span><div class="password-input-shell"><input type="password" name="current_password" autocomplete="current-password" required placeholder="Enter your current password"><button type="button" data-password-toggle aria-label="Show password"><i class="far fa-eye"></i></button></div>@error('current_password')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="profile-field password-field"><span>New Password <b>*</b></span><div class="password-input-shell"><input type="password" name="password" autocomplete="new-password" required placeholder="Create a strong new password" data-new-password><button type="button" data-password-toggle aria-label="Show password"><i class="far fa-eye"></i></button></div><div class="password-strength" data-password-strength><span></span><span></span><span></span><span></span></div>@error('password')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="profile-field password-field"><span>Confirm New Password <b>*</b></span><div class="password-input-shell"><input type="password" name="password_confirmation" autocomplete="new-password" required placeholder="Confirm your new password"><button type="button" data-password-toggle aria-label="Show password"><i class="far fa-eye"></i></button></div></label>

                    <div class="password-submit-row"><small><i class="fas fa-info-circle"></i> You will remain signed in on this device after changing your password.</small><button type="submit" class="primary-account-button"><i class="fas fa-shield-alt"></i> Update Password</button></div>
                </form>

                <aside class="password-security-visual" data-account-reveal="right" aria-hidden="true">
                    <div class="security-orbit one"></div><div class="security-orbit two"></div>
                    <div class="security-shield"><i class="fas fa-shield-alt"></i><span class="security-lock"><i class="fas fa-lock"></i></span><span class="security-check"><i class="fas fa-check"></i></span></div>
                    <div class="security-dots"><span></span><span></span><span></span><span></span><span></span></div>
                    <h3>Security first</h3><p>A unique password is one of the simplest ways to protect your account.</p>
                </aside>
            </div>
        </main>
    </div>
</div></section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
