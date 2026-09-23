@extends('layouts.admin')

@section('meta_title', 'Coupon Management')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <h4 class="font-weight-bold mb-0">
                <i class="fas fa-percent text-primary mr-2"></i> Coupon Management
            </h4>
        </div>
        <div class="col-12 col-md-auto">
            @can('coupons.create')
                <button type="button" class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#couponFormModal" id="btnAddCoupon">
                    <i class="fas fa-plus mr-1"></i> Add New Coupon
                </button>
            @endcan
            <a href="{{ route('admin.coupons.trash') }}" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash-alt mr-1"></i> Trash Bin
            </a>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white">
            <form id="couponFilterForm" method="GET" action="{{ route('admin.coupons.index') }}" class="form-row align-items-end">
                <div class="col-12 col-md-5 mb-2 mb-md-0">
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Search coupon code...">
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <select name="status" class="custom-select custom-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="expired" {{ ($filters['status'] ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 text-md-right">
                    <button type="button" id="btnResetFilter" class="btn btn-light border btn-sm"><i class="fas fa-redo-alt mr-1"></i> Reset</button>
                </div>
            </form>
        </div>

        <form id="couponBulkForm" method="POST" action="{{ route('admin.coupons.bulk-action') }}">
            @csrf
            <div class="card-body border-bottom py-2">
                <div class="d-flex align-items-center">
                    <select name="action" class="custom-select custom-select-sm mr-2" style="width: 180px;">
                        <option value="">Bulk Actions</option>
                        <option value="delete">Move to Trash</option>
                    </select>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt mr-1"></i> Apply</button>
                </div>
            </div>
        </form>

        <div class="card-body p-0 position-relative">
            <div id="couponTableOverlay" class="overlay d-none">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="couponTableContainer">
                @include('backoffice.admin.coupons.partials.table', ['coupons' => $coupons, 'isTrash' => false])
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($coupons->hasPages())
                {{ $coupons->links() }}
            @endif
        </div>
    </div>

    @include('backoffice.admin.coupons.partials.form')
    @include('backoffice.admin.coupons.partials.show')
@endsection

@push('js')
    @include('backoffice.admin.coupons.partials.script', ['fetchUrl' => route('admin.coupons.index')])
@endpush