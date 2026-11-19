@extends('vendor-portal.layouts.app')

@section('title', 'Account settings')
@section('breadcrumb_label', 'Account settings')
@section('page_heading', 'Account settings')
@section('page_description', 'Your personal details and seller account information.')

@section('content')
    <div class="vp-settings-grid">
        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">PERSONAL DETAILS</span><h2>Your account</h2><p>These details identify the person managing this store.</p></div></div>
            <form method="POST" action="{{ route('vendor.profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="vp-form-grid">
                    <label class="vp-field"><span>Full name</span><input name="name" value="{{ old('name', auth('web')->user()?->name ?? '') }}" maxlength="180" required></label>
                    <label class="vp-field"><span>Email address</span><input value="{{ auth('web')->user()?->email ?? '' }}" readonly><small>Contact support to change your login email.</small></label>
                    <label class="vp-field"><span>Phone number</span><input name="phone_number" value="{{ old('phone_number', auth('web')->user()?->phone_number ?? '') }}" maxlength="30"></label>
                </div>
                <div class="vp-form-actions"><button type="submit" class="vp-button vp-button-primary">Save account details</button></div>
            </form>
        </section>
        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">SELLER MEMBERSHIP</span><h2>Store access</h2><p>Your access is tied to an approved vendor membership.</p></div></div>
            <div class="vp-membership-card">
                <span class="vp-membership-icon"><i class="fas fa-user-shield"></i></span>
                <div><strong>{{ $vendor?->business_name ?? 'Seller account' }}</strong><small>{{ $vendor?->status ? str_replace('_', ' ', ucfirst($vendor->status)) : 'Membership details' }}</small></div>
            </div>
            <p class="vp-muted-note">Vendor team invitations and additional staff roles are not shown until the seller-staff scope is approved.</p>
        </section>
    </div>
@endsection
