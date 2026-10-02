@extends('layouts.admin')
@section('meta_title','Customers')
@section('page_content')
<div class="row align-items-center justify-content-between mb-3">
    <div class="col-auto">
        @can('customers.create')<button class="btn btn-primary shadow-sm" id="btnCreateCustomer"><i class="fas fa-user-plus mr-1"></i> Add Customer</button>@endcan
        <a href="{{ route('admin.customers.trash') }}" class="btn btn-outline-danger shadow-sm ml-2"><i class="fas fa-trash-alt mr-1"></i> Trash Bin</a>
    </div>
    <div class="col-auto">
        <form id="customerBulkForm" action="{{ route('admin.customers.bulk-action') }}" method="POST" class="form-inline">@csrf
            <div class="input-group input-group-sm"><select name="action" class="custom-select" required><option value="">Bulk Actions</option>@can('customers.delete')<option value="delete">Move to Trash</option>@endcan</select><div class="input-group-append"><button class="btn btn-secondary">Apply</button></div></div>
        </form>
    </div>
</div>
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header bg-white">
        <form id="customerFilterForm" method="GET" action="{{ route('admin.customers.index') }}" class="row align-items-center">
            <div class="col-md-3 mb-2 mb-md-0"><select name="status" id="customer-filter-status" class="custom-select custom-select-sm"><option value="">All Statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div class="col-md-6 mb-2 mb-md-0"><input type="search" name="search" id="customer-search" class="form-control form-control-sm" placeholder="Search customer name or email..."></div>
            <div class="col-md-3 text-md-right"><button type="button" id="btnResetCustomerFilter" class="btn btn-light btn-sm border"><i class="fas fa-redo-alt mr-1"></i> Reset</button></div>
        </form>
    </div>
    <div class="card-body p-0 position-relative"><div id="customerTableOverlay" class="overlay d-none"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div><div id="customerTableContainer">@include('backoffice.admin.customers.partials.table',['isTrash'=>false])</div></div>
    <div class="card-footer bg-white" id="customerPagination">@if($customers->hasPages()){{ $customers->links() }}@endif</div>
</div>
@include('backoffice.admin.customers.partials.form')
@include('backoffice.admin.customers.partials.show')
@include('backoffice.admin.customers.partials.confirm')
@endsection
@section('plugins.Sweetalert2',true)
@push('js')@include('backoffice.admin.customers.partials.script',['fetchUrl'=>route('admin.customers.index')])@endpush
