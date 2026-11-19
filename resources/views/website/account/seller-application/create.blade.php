@extends('website.layouts.app')

@section('title', 'Become a Seller | ShopPilot')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">
<link rel="stylesheet" href="{{ asset('assets/website/css/seller-application.css') }}">
@endpush

@section('content')
<section class="account-page seller-application-page">
    <div class="container">
        <div class="account-layout">
            @include('website.account.partials.sidebar')

            <div class="account-main">
                <header class="account-page-heading seller-application-heading">
                    <div class="seller-heading-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="seller-heading-copy">
                        <span>SELLER PROGRAM</span>
                        <h1>Open your ShopPilot store</h1>
                        <p>Tell us about your business and the store you want to build. Our team will review your application.</p>
                    </div>
                    <div class="seller-heading-note">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure application</span>
                    </div>
                </header>

                <section class="account-panel seller-application-card">
                    <form class="seller-application-form" method="POST" action="{{ route('website.account.seller.apply.store') }}" enctype="multipart/form-data">
                        @csrf

                        <section class="seller-form-section">
                            <div class="seller-section-heading">
                                <span class="seller-section-icon"><i class="fas fa-building"></i></span>
                                <div>
                                    <h2>Business information</h2>
                                    <p>Share the details that identify your business.</p>
                                </div>
                                <span class="seller-section-number">01</span>
                            </div>

                            <div class="seller-form-grid">
                                <div class="seller-field">
                                    <label for="business_name">Business name <b>*</b></label>
                                    <input id="business_name" type="text" name="business_name" value="{{ old('business_name') }}" placeholder="e.g. Shanto Electronics" required maxlength="180" autocomplete="organization" class="@error('business_name') is-invalid @enderror">
                                    @error('business_name')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field">
                                    <label for="legal_name">Legal name</label>
                                    <input id="legal_name" type="text" name="legal_name" value="{{ old('legal_name') }}" placeholder="e.g. Md. Shanto Ahmed" maxlength="180" autocomplete="name" class="@error('legal_name') is-invalid @enderror">
                                    @error('legal_name')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field">
                                    <label for="business_type">Business type</label>
                                    <input id="business_type" type="text" name="business_type" value="{{ old('business_type') }}" maxlength="80" placeholder="e.g. Individual, Partnership, Limited Company" class="@error('business_type') is-invalid @enderror">
                                    @error('business_type')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field">
                                    <label for="business_email">Business email <b>*</b></label>
                                    <input id="business_email" type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="name@business.com" required maxlength="191" autocomplete="email" class="@error('email') is-invalid @enderror">
                                    @error('email')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field">
                                    <label for="business_phone">Phone number <b>*</b></label>
                                    <input id="business_phone" type="tel" name="phone" value="{{ old('phone', $user->phone_number) }}" placeholder="+8801XXXXXXXXX" required maxlength="30" autocomplete="tel" class="@error('phone') is-invalid @enderror">
                                    @error('phone')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field">
                                    <label for="business_website">Website</label>
                                    <input id="business_website" type="url" name="website" value="{{ old('website') }}" maxlength="255" placeholder="https://yourbusiness.com" autocomplete="url" class="@error('website') is-invalid @enderror">
                                    @error('website')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field">
                                    <label for="tax_identifier">Tax / Business ID</label>
                                    <input id="tax_identifier" type="text" name="tax_identifier" value="{{ old('tax_identifier') }}" placeholder="Enter your Trade License / BIN / Tax ID" maxlength="100" class="@error('tax_identifier') is-invalid @enderror">
                                    @error('tax_identifier')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field">
                                    <label for="business_city">City</label>
                                    <input id="business_city" type="text" name="city" value="{{ old('city') }}" placeholder="e.g. Dhaka" maxlength="100" autocomplete="address-level2" class="@error('city') is-invalid @enderror">
                                    @error('city')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field">
                                    <label for="business_country">Country</label>
                                    <input id="business_country" type="text" name="country" value="{{ old('country') }}" placeholder="e.g. Bangladesh" maxlength="100" autocomplete="country-name" class="@error('country') is-invalid @enderror">
                                    @error('country')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field seller-field-full">
                                    <label for="business_address">Business address <b>*</b></label>
                                    <textarea id="business_address" name="address" rows="3" required maxlength="2000" autocomplete="street-address" class="@error('address') is-invalid @enderror" placeholder="House, road, area, city and postal code">{{ old('address') }}</textarea>
                                    @error('address')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </section>

                        <section class="seller-form-section">
                            <div class="seller-section-heading">
                                <span class="seller-section-icon"><i class="fas fa-store"></i></span>
                                <div>
                                    <h2>Store information</h2>
                                    <p>Give your storefront a name and a short introduction.</p>
                                </div>
                                <span class="seller-section-number">02</span>
                            </div>

                            <div class="seller-form-grid">
                                <div class="seller-field">
                                    <label for="store_name">Store name <b>*</b></label>
                                    <input id="store_name" type="text" name="store_name" value="{{ old('store_name') }}" placeholder="e.g. Shanto Mart" required maxlength="180" autocomplete="organization" class="@error('store_name') is-invalid @enderror">
                                    @error('store_name')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field">
                                    <label for="store_slug">Store URL slug</label>
                                    <input id="store_slug" type="text" name="store_slug" value="{{ old('store_slug') }}" maxlength="180" placeholder="e.g. shanto-mart (leave blank to auto-generate)" class="@error('store_slug') is-invalid @enderror">
                                    <small class="seller-field-hint">This will be used in your public store URL.</small>
                                    @error('store_slug')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field seller-field-full">
                                    <label for="store_description">Store description</label>
                                    <textarea id="store_description" name="store_description" rows="4" maxlength="3000" placeholder="Tell customers what you sell, what makes your store special, and what they can expect." class="@error('store_description') is-invalid @enderror">{{ old('store_description') }}</textarea>
                                    @error('store_description')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </section>

                        <section class="seller-form-section seller-document-section">
                            <div class="seller-section-heading">
                                <span class="seller-section-icon"><i class="fas fa-file-alt"></i></span>
                                <div>
                                    <h2>Verification documents</h2>
                                    <p>Upload documents so our team can verify your application.</p>
                                </div>
                                <span class="seller-section-number">03</span>
                            </div>

                            <div class="seller-form-grid">
                                <div class="seller-field seller-field-full">
                                    <label for="document_type">Document type <b>*</b></label>
                                    <input id="document_type" type="text" name="document_type" value="{{ old('document_type', 'Business verification') }}" placeholder="e.g. Trade License, NID, Business Certificate" required maxlength="100" class="@error('document_type') is-invalid @enderror">
                                    @error('document_type')<small class="seller-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="seller-field seller-field-full">
                                    <label for="seller_documents">Upload documents <b>*</b></label>
                                    <div class="seller-upload-box">
                                        <span class="seller-upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                        <div class="seller-upload-copy">
                                            <strong>Choose verification files</strong>
                                            <span>PDF, JPG or PNG · Up to 5 files · Maximum 5 MB per file</span>
                                        </div>
                                        <input id="seller_documents" type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" multiple required class="@error('documents') is-invalid @enderror">
                                    </div>
                                    @error('documents')<small class="seller-field-error">{{ $message }}</small>@enderror
                                    @foreach($errors->get('documents.*') as $messages)
                                        @foreach($messages as $message)<small class="seller-field-error">{{ $message }}</small>@endforeach
                                    @endforeach
                                </div>
                            </div>
                        </section>

                        <div class="seller-form-footer">
                            <div class="seller-form-security">
                                <i class="fas fa-lock"></i>
                                <span>Your business information and documents are submitted securely for review.</span>
                            </div>
                            <button type="submit" class="seller-submit-button">
                                Submit seller application
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/account.js') }}" defer></script>
@endpush
