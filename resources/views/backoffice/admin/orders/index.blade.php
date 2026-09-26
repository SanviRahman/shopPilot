@extends('layouts.admin')

@section('meta_title', 'Orders Management')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0 d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary shadow-sm" id="btnCreateOrder">
                <i class="fas fa-plus-circle mr-1"></i> Add New Order
            </button>
            <a href="{{ route('admin.orders.trash') }}" class="btn btn-outline-danger shadow-sm ml-2">
                <i class="fas fa-trash-alt mr-1"></i> Trash Bin
            </a>
        </div>

        <div class="col-12 col-md-auto">
            <form id="bulkActionForm" action="{{ route('admin.orders.bulk-action') }}" method="POST" class="form-inline">
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
            <form id="filterForm" method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" name="search" id="search" class="form-control border-left-0" placeholder="Order #, Buyer, Phone, Email...">
                    </div>
                </div>

                <div class="col-12 col-md-2 mb-2 mb-md-0">
                    <select name="order_status" id="order_status" class="custom-select custom-select-sm">
                        <option value="">All Order Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 mb-2 mb-md-0">
                    <select name="payment_status" id="payment_status" class="custom-select custom-select-sm">
                        <option value="">All Payment Status</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="partially_paid">Partially Paid</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <select name="agent_id" id="agent_id" class="custom-select custom-select-sm">
                        <option value="">All Assigned Agents</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
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
                @include('backoffice.admin.orders.partials.table', ['isTrash' => false])
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($orders->hasPages())
                {{ $orders->links() }}
            @endif
        </div>
    </div>

    @include('backoffice.admin.orders.partials.form', ['agents' => $agents])
    @include('backoffice.admin.orders.partials.show')

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
    @include('backoffice.admin.orders.partials.script', ['fetchUrl' => route('admin.orders.index')])
@endpush