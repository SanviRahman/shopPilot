@extends('website.layouts.app')

@section('title', 'Seller Application | ShopPilot')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">
<link rel="stylesheet" href="{{ asset('assets/website/css/seller-application.css') }}">
@endpush

@section('content')
@php
    $statusLabel = str_replace('_', ' ', ucfirst($vendor->status));
    $statusTone = $vendor->status === 'approved' ? 'success' : (in_array($vendor->status, ['rejected', 'suspended'], true) ? 'danger' : 'pending');
    $rejection = $vendor->statusHistories->firstWhere('to_status', 'rejected');
@endphp

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
                        <h1>Seller application</h1>
                        <p>Track your application status and review the information you submitted.</p>
                    </div>
                </header>

                <section class="account-panel seller-status-card seller-status-{{ $statusTone }}">
                    <div class="seller-status-top">
                        <div class="seller-status-icon">
                            <i class="fas {{ $vendor->status === 'approved' ? 'fa-check' : ($vendor->status === 'rejected' ? 'fa-times' : ($vendor->status === 'suspended' ? 'fa-pause' : 'fa-clock')) }}"></i>
                        </div>
                        <div class="seller-status-copy">
                            <span>APPLICATION STATUS</span>
                            <h2>{{ $statusLabel }}</h2>
                        </div>
                        <span class="seller-status-badge">{{ $statusLabel }}</span>
                    </div>

                    @if($vendor->status === 'pending_review')
                        <p class="seller-status-message">Your application is waiting for marketplace review. We will update your status after the verification process is complete.</p>
                    @elseif($vendor->status === 'approved')
                        <p class="seller-status-message">Your store has been approved. You can now manage your products, orders and store settings from your seller dashboard.</p>
                        <a href="{{ route('vendor.dashboard') }}" class="seller-dashboard-link">Open vendor dashboard <i class="fas fa-arrow-right"></i></a>
                    @elseif($vendor->status === 'rejected')
                        <p class="seller-status-message">Your application was not approved. Review the feedback below and contact marketplace support if you need help.</p>
                        @if($rejection?->reason)
                            <div class="seller-rejection-note">
                                <strong>Review feedback</strong>
                                <p>{{ $rejection->reason }}</p>
                            </div>
                        @endif
                    @elseif($vendor->status === 'suspended')
                        <p class="seller-status-message">Your store is currently suspended. Existing orders and financial obligations remain subject to marketplace review.</p>
                    @endif
                </section>

                <section class="account-panel seller-summary-card">
                    <div class="seller-section-heading">
                        <span class="seller-section-icon"><i class="fas fa-clipboard-list"></i></span>
                        <div>
                            <h2>Application details</h2>
                            <p>Business and storefront information submitted for review.</p>
                        </div>
                    </div>

                    <div class="seller-summary-grid">
                        <div><span>Business name</span><strong>{{ $vendor->business_name }}</strong></div>
                        <div><span>Store name</span><strong>{{ $vendor->store?->name ?? '—' }}</strong></div>
                        <div><span>Business email</span><strong>{{ $vendor->email }}</strong></div>
                        <div><span>Contact number</span><strong>{{ $vendor->phone }}</strong></div>
                    </div>
                </section>

                <section class="account-panel seller-documents-card">
                    <div class="seller-section-heading">
                        <span class="seller-section-icon"><i class="fas fa-folder-open"></i></span>
                        <div>
                            <h2>Submitted documents</h2>
                            <p>Files attached to your seller application.</p>
                        </div>
                        <span class="seller-document-count">{{ $vendor->documents->count() }} files</span>
                    </div>

                    <div class="seller-document-list">
                        @forelse($vendor->documents as $document)
                            <div class="seller-document-row">
                                <span class="seller-document-icon"><i class="far fa-file-alt"></i></span>
                                <div class="seller-document-copy">
                                    <strong>{{ $document->document_type }}</strong>
                                    <small>{{ $document->original_name }}</small>
                                </div>
                                <span class="seller-document-status">{{ ucfirst($document->status) }}</span>
                            </div>
                        @empty
                            <div class="seller-documents-empty">
                                <i class="far fa-folder-open"></i>
                                <strong>No documents submitted</strong>
                                <span>There are no verification documents attached to this application.</span>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/website/js/account.js') }}" defer></script>
@endpush
