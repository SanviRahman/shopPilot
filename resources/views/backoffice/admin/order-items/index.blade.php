@extends('layouts.admin')

@section('meta_title', 'Order Items')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0 d-flex flex-wrap gap-2">
            @can('orders.update')
                <button type="button" class="btn btn-primary shadow-sm" id="btnCreateItem">
                    <i class="fas fa-plus-circle mr-1"></i> Add Item to Order
                </button>
            @endcan
            <a href="{{ route('admin.order-items.trash') }}" class="btn btn-outline-danger shadow-sm ml-2">
                <i class="fas fa-trash-alt mr-1"></i> Trash Bin
            </a>
        </div>

        <div class="col-12 col-md-auto">
            <form id="bulkActionForm" action="{{ route('admin.order-items.bulk-action') }}" method="POST" class="form-inline">
                @csrf
                <div class="input-group">
                    <select name="action" class="custom-select custom-select-sm" required>
                        <option value="">Bulk Actions</option>
                        @can('orders.update')<option value="delete">Move to Trash</option>@endcan
                        @can('orders.restore')<option value="restore">Restore</option>@endcan
                        @can('orders.force-delete')<option value="force-delete">Permanent Delete</option>@endcan
                    </select>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-secondary btn-sm px-3">Apply</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white py-3">
            <form id="filterForm" method="GET" action="{{ route('admin.order-items.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <select name="order_id" id="order_id" class="custom-select custom-select-sm">
                        <option value="">All Orders</option>
                        @foreach($orders as $ord)
                            <option value="{{ $ord->id }}">{{ $ord->order_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" name="search" id="search" class="form-control border-left-0" placeholder="Search by Product Name, Variant, SKU or Order Number...">
                    </div>
                </div>

                <div class="col-12 col-md-2 text-md-right">
                    <button type="button" id="btnResetFilter" class="btn btn-light btn-sm border"><i class="fas fa-redo-alt mr-1"></i> Reset</button>
                </div>
            </form>
        </div>

        <div class="card-body p-0 position-relative">
            <div id="tableOverlay" class="overlay d-none">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="tableContainer">
                @include('backoffice.admin.order-items.partials.table', ['isTrash' => false])
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($orderItems->hasPages())
                {{ $orderItems->links() }}
            @endif
        </div>
    </div>

    @include('backoffice.admin.order-items.partials.form', ['orders' => $orders])
    @include('backoffice.admin.order-items.partials.show')

    {{-- Confirmation Modal --}}
    <div class="modal fade" id="confirmModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="text-warning mb-3">
                        <i class="fas fa-exclamation-circle fa-3x"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2" id="confirmModalTitle">Are you sure?</h5>
                    <p class="text-muted small mb-4" id="confirmModalText">This action cannot be undone.</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light btn-sm px-3 mr-2" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger btn-sm px-4" id="confirmModalBtn">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('plugins.Sweetalert2', true)

@push('js')
    @include('backoffice.admin.order-items.partials.script', ['fetchUrl' => route('admin.order-items.index')])
@endpush