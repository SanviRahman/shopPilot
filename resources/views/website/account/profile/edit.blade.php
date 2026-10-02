@extends('website.layouts.app')
@section('title', 'Profile Settings | ShopPilot')
@section('meta_description', 'Update your ShopPilot customer profile and saved delivery information.')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">@endpush
@php
    $profilePhone = preg_replace('/\D+/', '', (string) $user->phone_number) ?: '';
    if (str_starts_with($profilePhone, '880')) $profilePhone = substr($profilePhone, 3);
    if (str_starts_with($profilePhone, '0')) $profilePhone = substr($profilePhone, 1);
@endphp

@section('content')
<section class="account-page profile-settings-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.dashboard') }}">My Account</a><i class="fas fa-chevron-right"></i><strong>Profile Settings</strong></div>

    <div class="account-layout">
        @include('website.account.partials.sidebar')

        <main class="account-main">
            <header class="account-page-heading account-heading-actions" data-account-reveal>
                <div><span>ACCOUNT DETAILS</span><h1>Profile Settings</h1><p>Keep your personal details and delivery information up to date.</p></div>
                <a href="{{ route('website.account.dashboard') }}" class="secondary-account-button"><i class="far fa-eye"></i> View Dashboard</a>
            </header>

            <form action="{{ route('website.account.profile.update') }}" method="POST" enctype="multipart/form-data" class="account-panel profile-settings-form" data-profile-form data-account-reveal>
                @csrf @method('PATCH')

                <div class="profile-section-heading"><div><span><i class="fas fa-user"></i></span><div><h2>Personal Information</h2><p>Basic account information used for orders and communication.</p></div></div></div>

                <div class="profile-form-grid three">
                    <label class="profile-field"><span>Full Name <b>*</b></span><input type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="120" autocomplete="name" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="profile-field"><span>Email Address <b>*</b></span><input type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="191" autocomplete="email" required>@error('email')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <label class="profile-field phone-field"><span>Phone Number</span><div class="profile-phone-shell"><span>+880</span><input type="tel" name="phone_number" value="{{ old('phone_number', $profilePhone) }}" maxlength="10" placeholder="1712345678" autocomplete="tel"></div>@error('phone_number')<small class="field-error">{{ $message }}</small>@enderror</label>
                </div>

                <div class="profile-form-grid three profile-secondary-row">
                    <label class="profile-field"><span>Gender</span><select name="gender"><option value="">Select gender</option><option value="male" @selected(old('gender', $user->gender) === 'male')>Male</option><option value="female" @selected(old('gender', $user->gender) === 'female')>Female</option><option value="other" @selected(old('gender', $user->gender) === 'other')>Other</option><option value="prefer_not_to_say" @selected(old('gender', $user->gender) === 'prefer_not_to_say')>Prefer not to say</option></select></label>
                    <label class="profile-field"><span>Date of Birth</span><input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}">@error('date_of_birth')<small class="field-error">{{ $message }}</small>@enderror</label>
                    <div class="profile-avatar-field">
                        <span>Profile Photo</span>
                        <div class="profile-avatar-control">
                            <div class="profile-avatar-preview" data-avatar-preview>
                                @if($user->avatar_url)<img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">@else<span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>@endif
                            </div>
                            <div><label class="profile-file-button"><i class="fas fa-camera"></i> Choose Photo<input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" data-avatar-input></label><small>JPG, PNG or WEBP · Max 2MB</small>@if($user->avatar_url)<label class="profile-remove-photo"><input type="checkbox" name="remove_avatar" value="1"> Remove current photo</label>@endif</div>
                        </div>
                        @error('avatar')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="profile-divider"></div>
                <div class="profile-section-heading"><div><span><i class="fas fa-map-marker-alt"></i></span><div><h2>Address Information</h2><p>Saved details can be reused automatically during checkout.</p></div></div></div>

                <div class="profile-form-grid three">
                    <label class="profile-field"><span>Division</span><select name="division"><option value="">Select division</option>@foreach(['Dhaka','Chattogram','Rajshahi','Khulna','Barishal','Sylhet','Rangpur','Mymensingh'] as $division)<option value="{{ $division }}" @selected(old('division', $user->division) === $division)>{{ $division }}</option>@endforeach</select></label>
                    <label class="profile-field"><span>District</span><input type="text" name="district" value="{{ old('district', $user->district) }}" maxlength="100" placeholder="e.g. Dhaka"></label>
                    <label class="profile-field"><span>Upazila / Thana</span><input type="text" name="upazila" value="{{ old('upazila', $user->upazila) }}" maxlength="100" placeholder="e.g. Dhanmondi"></label>
                </div>

                <label class="profile-field full"><span>Full Address</span><textarea name="address" rows="3" maxlength="1000" placeholder="House, road, area and nearby landmark">{{ old('address', $user->address) }}</textarea>@error('address')<small class="field-error">{{ $message }}</small>@enderror</label>

                <div class="profile-form-actions"><span class="profile-save-note"><i class="fas fa-shield-alt"></i> Your information is stored securely and used only for your ShopPilot account.</span><button type="submit" class="primary-account-button"><i class="fas fa-save"></i> Update Profile</button></div>
            </form>
        </main>
    </div>
</div></section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
