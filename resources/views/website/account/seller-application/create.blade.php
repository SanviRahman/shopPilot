@extends('website.layouts.app')

@section('title', 'Become a Seller | ShopPilot')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">
@endpush

@section('content')
<section class="account-page">
    <div class="container">
        <div class="account-layout">
            @include('website.account.partials.sidebar')
            <div class="account-main">
                <header class="account-page-heading">
                    <span>SELLER PROGRAM</span>
                    <h1>Open your ShopPilot store</h1>
                    <p>Submit your business information and verification documents. Our team will review your application.</p>
                </header>

                <section class="account-panel">
                    <form method="POST" action="{{ route('website.account.seller.apply.store') }}" enctype="multipart/form-data">
                        @csrf
                        <h2>Business information</h2>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Business name *</label>
                                <input class="form-control" name="business_name" value="{{ old('business_name') }}" required maxlength="180">
                                @error('business_name')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Legal name</label>
                                <input class="form-control" name="legal_name" value="{{ old('legal_name') }}" maxlength="180">
                                @error('legal_name')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Business type</label>
                                <input class="form-control" name="business_type" value="{{ old('business_type') }}" maxlength="80" placeholder="Individual, company, partnership...">
                                @error('business_type')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Business email *</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required maxlength="191">
                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Phone number *</label>
                                <input class="form-control" name="phone" value="{{ old('phone', $user->phone_number) }}" required maxlength="30">
                                @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Website</label>
                                <input type="url" class="form-control" name="website" value="{{ old('website') }}" placeholder="https://">
                                @error('website')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Tax / Business ID</label>
                                <input class="form-control" name="tax_identifier" value="{{ old('tax_identifier') }}" maxlength="100">
                                @error('tax_identifier')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>City</label>
                                <input class="form-control" name="city" value="{{ old('city') }}" maxlength="100">
                                @error('city')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-12 form-group">
                                <label>Business address *</label>
                                <textarea class="form-control" name="address" rows="3" required maxlength="2000">{{ old('address') }}</textarea>
                                @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <hr>
                        <h2>Store information</h2>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Store name *</label>
                                <input class="form-control" name="store_name" value="{{ old('store_name') }}" required maxlength="180">
                                @error('store_name')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Store URL slug</label>
                                <input class="form-control" name="store_slug" value="{{ old('store_slug') }}" maxlength="180" placeholder="Generated from store name if blank">
                                @error('store_slug')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-12 form-group">
                                <label>Store description</label>
                                <textarea class="form-control" name="store_description" rows="3" maxlength="3000">{{ old('store_description') }}</textarea>
                                @error('store_description')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <hr>
                        <h2>Verification documents</h2>
                        <div class="form-group">
                            <label>Document type *</label>
                            <input class="form-control" name="document_type" value="{{ old('document_type', 'Business verification') }}" required maxlength="100">
                            @error('document_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label>Upload documents * (PDF, JPG or PNG; maximum 5 files, 5 MB each)</label>
                            <input type="file" class="form-control-file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" multiple required>
                            @error('documents')<small class="text-danger">{{ $message }}</small>@enderror
                            @foreach($errors->get('documents.*') as $messages)
                                @foreach($messages as $message)<small class="d-block text-danger">{{ $message }}</small>@endforeach
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary">Submit seller application</button>
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
