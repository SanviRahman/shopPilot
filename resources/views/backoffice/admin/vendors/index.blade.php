@extends('layouts.admin')

@section('meta_title', 'Vendor Management')

@section('page_content')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header bg-white">
        <form method="GET" action="{{ route('admin.vendors.index') }}" class="row align-items-center">
            <div class="col-md-3 mb-2 mb-md-0">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All statuses</option>
                    @foreach(['pending_review', 'approved', 'rejected', 'suspended', 'closed'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-2 mb-md-0">
                <input type="search" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Search business, store, email or phone">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.vendors.index') }}" class="btn btn-light btn-sm border">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Business</th>
                    <th>Store</th>
                    <th>Owner</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td><strong>{{ $vendor->business_name }}</strong><small class="d-block text-muted">#{{ $vendor->id }}</small></td>
                        <td>{{ $vendor->store?->name ?? '—' }}</td>
                        <td>{{ $vendor->users->first()?->name ?? '—' }}</td>
                        <td>{{ $vendor->email }}<small class="d-block text-muted">{{ $vendor->phone }}</small></td>
                        <td><span class="badge badge-{{ $vendor->status === 'approved' ? 'success' : ($vendor->status === 'rejected' || $vendor->status === 'suspended' ? 'danger' : 'warning') }}">{{ str_replace('_', ' ', ucfirst($vendor->status)) }}</span></td>
                        <td>{{ $vendor->created_at?->format('d M Y') }}</td>
                        <td class="text-right"><a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No vendors found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vendors->hasPages())
        <div class="card-footer bg-white">{{ $vendors->links() }}</div>
    @endif
</div>
@endsection
