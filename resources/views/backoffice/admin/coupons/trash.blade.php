@extends('layouts.admin')

@section('meta_title', 'Coupon Trash')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <h4 class="font-weight-bold mb-0">
                <i class="fas fa-trash-alt text-danger mr-2"></i> Coupon Trash Bin
            </h4>
        </div>
        <div class="col-12 col-md-auto">
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-light border btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Coupons
            </a>
        </div>
    </div>

    <div class="card card-outline card-danger shadow-sm">
        <div class="card-header bg-white">
            <form id="couponFilterForm" method="GET" action="{{ route('admin.coupons.trash') }}" class="form-row align-items-end">
                <div class="col-12 col-md-8 mb-2 mb-md-0">
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Search deleted coupons...">
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
                    <select name="action" class="custom-select custom-select-sm mr-2" style="width: 200px;">
                        <option value="">Bulk Actions</option>
                        <option value="restore">Restore Selected</option>
                        <option value="force-delete">Permanent Delete</option>
                    </select>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-check mr-1"></i> Apply</button>
                </div>
            </div>
        </form>

        <div class="card-body p-0 position-relative">
            <div id="couponTableOverlay" class="overlay d-none">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="couponTableContainer">
                @include('backoffice.admin.coupons.partials.table', ['coupons' => $coupons, 'isTrash' => true])
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($coupons->hasPages())
                {{ $coupons->links() }}
            @endif
        </div>
    </div>
@endsection

@push('js')
    @include('backoffice.admin.coupons.partials.script', ['fetchUrl' => route('admin.coupons.trash')])
@endpush