@extends('vendor-portal.layouts.app')

@section('title', 'Store profile')
@section('breadcrumb_label', 'Store profile')
@section('page_heading', 'Your store profile')
@section('page_description', 'Make it easy for customers to recognise and trust your business.')

@section('content')
    <form method="POST" action="{{ route('vendor.store.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="vp-settings-grid">
            <section class="vp-panel">
                <div class="vp-panel-heading"><div><span class="vp-section-kicker">STORE IDENTITY</span><h2>Public store details</h2><p>These details shape how customers recognise your store.</p></div></div>
                <div class="vp-form-grid">
                    <label class="vp-field vp-field-full"><span>Store name <b>*</b></span><input name="name" value="{{ old('name', $vendor?->store?->name ?? '') }}" maxlength="180" required>@error('name')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field vp-field-full"><span>Store URL slug <b>*</b></span><input name="slug" value="{{ old('slug', $vendor?->store?->slug ?? '') }}" maxlength="180" required><small>Changing a store URL may affect existing links.</small>@error('slug')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field vp-field-full"><span>Store description</span><textarea name="description" rows="4" maxlength="3000">{{ old('description', $vendor?->store?->description ?? '') }}</textarea>@error('description')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field"><span>Store phone</span><input name="phone" value="{{ old('phone', $vendor?->store?->phone ?? '') }}" maxlength="30">@error('phone')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field"><span>Store email</span><input type="email" name="email" value="{{ old('email', $vendor?->store?->email ?? '') }}" maxlength="191">@error('email')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field"><span>Seller shipping fee ({{ config('shop.marketplace.currency_symbol') }})</span><input type="number" name="shipping_fee" min="0" step="0.01" value="{{ old('shipping_fee', $vendor?->store?->shipping_fee ?? 0) }}" required>@error('shipping_fee')<small>{{ $message }}</small>@enderror<small>Added to the selected delivery method for each seller order.</small></label>
                    <label class="vp-field vp-field-full"><span>Store address</span><textarea name="address" rows="3" maxlength="2000">{{ old('address', $vendor?->store?->address ?? '') }}</textarea>@error('address')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field"><span>Store logo</span>@if($vendor?->store?->getFirstMediaUrl('store_logo'))<img class="vp-store-preview" src="{{ $vendor->store->getFirstMediaUrl('store_logo') }}" alt="Store logo">@endif<input type="file" name="store_logo" accept=".jpg,.jpeg,.png,.webp"><small>Square images work best · Max 2 MB.</small>@error('store_logo')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field"><span>Store banner</span>@if($vendor?->store?->getFirstMediaUrl('store_banner'))<img class="vp-store-preview vp-store-preview-wide" src="{{ $vendor->store->getFirstMediaUrl('store_banner') }}" alt="Store banner">@endif<input type="file" name="store_banner" accept=".jpg,.jpeg,.png,.webp"><small>Use a wide image · Max 4 MB.</small>@error('store_banner')<small>{{ $message }}</small>@enderror</label>
                </div>
            </section>

            <aside class="vp-panel">
                <div class="vp-panel-heading"><div><span class="vp-section-kicker">STORE STATUS</span><h2>At a glance</h2></div></div>
                <div class="vp-detail-list">
                    <div><span>Business name</span><strong>{{ $vendor?->business_name ?? '—' }}</strong></div>
                    <div><span>Application status</span><strong>{{ $vendor?->status ? str_replace('_', ' ', ucfirst($vendor->status)) : '—' }}</strong></div>
                    <div><span>Store status</span><strong>{{ $vendor?->store?->status ? ucfirst($vendor->store->status) : '—' }}</strong></div>
                </div>
                <label class="vp-checkbox"><input type="checkbox" name="vacation_mode" value="1" @checked(old('vacation_mode', $vendor?->store?->vacation_mode ?? false))><span>Enable vacation mode</span></label>
                <p class="vp-muted-note">Vacation mode is saved with your store settings. Public storefront behavior will be connected in a later marketplace phase.</p>
                <div class="vp-form-actions">
                    <button type="submit" class="vp-button vp-button-primary">Save store profile</button>
                </div>
            </aside>
        </div>

        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">CUSTOMER EXPECTATIONS</span><h2>Store policies</h2><p>Clear policies help customers know what to expect.</p></div></div>
            <div class="vp-form-grid">
                <label class="vp-field"><span>Shipping policy</span><textarea name="shipping_policy" rows="4">{{ old('shipping_policy', $vendor?->store?->shipping_policy ?? '') }}</textarea>@error('shipping_policy')<small>{{ $message }}</small>@enderror</label>
                <label class="vp-field"><span>Return policy</span><textarea name="return_policy" rows="4">{{ old('return_policy', $vendor?->store?->return_policy ?? '') }}</textarea>@error('return_policy')<small>{{ $message }}</small>@enderror</label>
            </div>
        </section>
    </form>
@endsection
