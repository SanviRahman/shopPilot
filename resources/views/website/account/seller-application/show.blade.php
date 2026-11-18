@extends('website.layouts.app')

@section('title', 'Seller Application | ShopPilot')

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
                    <h1>Seller application</h1>
                    <p>Your application and store information are shown below.</p>
                </header>

                <section class="account-panel">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted">APPLICATION STATUS</small>
                            <h2 class="mb-0">{{ str_replace('_', ' ', ucfirst($vendor->status)) }}</h2>
                        </div>
                        <span class="badge badge-{{ $vendor->status === 'approved' ? 'success' : ($vendor->status === 'rejected' || $vendor->status === 'suspended' ? 'danger' : 'warning') }} px-3 py-2">
                            {{ str_replace('_', ' ', ucfirst($vendor->status)) }}
                        </span>
                    </div>
                    <p class="text-muted">Business: <strong>{{ $vendor->business_name }}</strong></p>
                    <p class="text-muted">Store: <strong>{{ $vendor->store?->name }}</strong></p>
                    @if($vendor->status === 'pending_review')
                        <p class="mb-0">Your application is waiting for marketplace review. You will be able to manage your store after approval.</p>
                    @elseif($vendor->status === 'approved')
                        <p class="mb-0">Your store has been approved. Vendor product and order management will be available in your seller dashboard.</p>
                    @elseif($vendor->status === 'rejected')
                        <p>Your application was not approved. Review the reason below and contact marketplace support about resubmission.</p>
                        @php($rejection = $vendor->statusHistories->firstWhere('to_status', 'rejected'))
                        @if($rejection?->reason)<div class="alert alert-warning mb-0"><strong>Review reason:</strong> {{ $rejection->reason }}</div>@endif
                    @elseif($vendor->status === 'suspended')
                        <p class="mb-0">Your store is currently suspended. Existing orders and financial obligations remain subject to marketplace review.</p>
                    @endif
                </section>

                <section class="account-panel mt-3">
                    <h2>Submitted documents</h2>
                    @forelse($vendor->documents as $document)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $document->document_type }}</strong>
                                <small class="d-block text-muted">{{ $document->original_name }}</small>
                            </div>
                            <span class="badge badge-secondary">{{ ucfirst($document->status) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No documents submitted.</p>
                    @endforelse
                </section>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/account.js') }}" defer></script>
@endpush
