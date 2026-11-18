@extends('layouts.admin')

@section('meta_title', 'Vendor Review')

@section('page_content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">{{ $vendor->business_name }}</h3>
                <span class="badge badge-{{ $vendor->status === 'approved' ? 'success' : ($vendor->status === 'rejected' || $vendor->status === 'suspended' ? 'danger' : 'warning') }}">{{ str_replace('_', ' ', ucfirst($vendor->status)) }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Legal name</small>{{ $vendor->legal_name ?: '—' }}</div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Business type</small>{{ $vendor->business_type ?: '—' }}</div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Email</small>{{ $vendor->email }}</div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Phone</small>{{ $vendor->phone }}</div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Website</small>{{ $vendor->website ?: '—' }}</div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Tax / Business ID</small>{{ $vendor->tax_identifier ?: '—' }}</div>
                    <div class="col-12 mb-3"><small class="text-muted d-block">Business address</small>{{ $vendor->address }}{{ $vendor->city ? ', '.$vendor->city : '' }}{{ $vendor->country ? ', '.$vendor->country : '' }}</div>
                </div>
                <hr>
                <h5 class="font-weight-bold">Store profile</h5>
                <p class="mb-1"><strong>{{ $vendor->store?->name }}</strong> <span class="text-muted">({{ $vendor->store?->slug }})</span></p>
                <p class="text-muted mb-0">{{ $vendor->store?->description ?: 'No store description provided.' }}</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Verification documents</h3></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Type</th><th>File</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                    <tbody>
                        @forelse($vendor->documents as $document)
                            <tr>
                                <td>{{ $document->document_type }}</td>
                                <td>{{ $document->original_name }}</td>
                                <td><span class="badge badge-secondary">{{ ucfirst($document->status) }}</span></td>
                                <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.vendors.documents.download', [$vendor, $document]) }}">Download</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No documents uploaded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Review actions</h3></div>
            <div class="card-body">
                @if($vendor->status === 'pending_review')
                    @can('vendors.approve')
                        <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}" class="mb-3">
                            @csrf @method('PATCH')
                            <button class="btn btn-success btn-block" onclick="return confirm('Approve this vendor?')">Approve vendor</button>
                        </form>
                    @endcan
                    @can('vendors.reject')
                        <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}">
                            @csrf @method('PATCH')
                            <label>Rejection reason *</label>
                            <textarea name="reason" class="form-control mb-2" rows="3" required>{{ old('reason') }}</textarea>
                            @error('reason')<small class="text-danger">{{ $message }}</small>@enderror
                            <button class="btn btn-outline-danger btn-block">Reject application</button>
                        </form>
                    @endcan
                @elseif($vendor->status === 'approved')
                    @can('vendors.suspend')
                        <form method="POST" action="{{ route('admin.vendors.suspend', $vendor) }}">
                            @csrf @method('PATCH')
                            <label>Suspension reason *</label>
                            <textarea name="reason" class="form-control mb-2" rows="3" required>{{ old('reason') }}</textarea>
                            @error('reason')<small class="text-danger">{{ $message }}</small>@enderror
                            <button class="btn btn-outline-danger btn-block">Suspend vendor</button>
                        </form>
                    @endcan
                @elseif($vendor->status === 'suspended')
                    @can('vendors.reactivate')
                        <form method="POST" action="{{ route('admin.vendors.reactivate', $vendor) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-success btn-block" onclick="return confirm('Reactivate this vendor?')">Reactivate vendor</button>
                        </form>
                    @endcan
                @else
                    <p class="text-muted mb-0">No actions are available for this status.</p>
                @endif
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Status history</h3></div>
            <div class="card-body">
                @forelse($vendor->statusHistories as $history)
                    <div class="border-bottom pb-2 mb-2">
                        <strong>{{ str_replace('_', ' ', ucfirst($history->from_status ?: 'new')) }} &rarr; {{ str_replace('_', ' ', ucfirst($history->to_status)) }}</strong>
                        <small class="d-block text-muted">{{ $history->created_at?->format('d M Y, h:i A') }}</small>
                        @if($history->reason)<small>{{ $history->reason }}</small>@endif
                    </div>
                @empty
                    <p class="text-muted mb-0">No status history.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
