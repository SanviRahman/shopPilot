@extends('layouts.admin')
@section('meta_title','Customer Trash')
@section('page_content')
<div class="row align-items-center justify-content-between mb-3">
    <div class="col-auto"><a href="{{ route('admin.customers.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left mr-1"></i> Back to Customers</a></div>
    <div class="col-auto"><form id="customerBulkForm" action="{{ route('admin.customers.bulk-action') }}" method="POST" class="form-inline">@csrf<div class="input-group input-group-sm"><select name="action" class="custom-select" required><option value="">Bulk Actions</option>@can('customers.restore')<option value="restore">Restore</option>@endcan @can('customers.force-delete')<option value="force-delete">Permanent Delete</option>@endcan</select><div class="input-group-append"><button class="btn btn-secondary">Apply</button></div></div></form></div>
</div>
<div class="card card-outline card-danger shadow-sm">
    <div class="card-header bg-white"><form id="customerFilterForm" method="GET" action="{{ route('admin.customers.trash') }}" class="row"><div class="col-md-9"><input type="search" name="search" id="customer-search" class="form-control form-control-sm" placeholder="Search trashed customers..."></div><div class="col-md-3 text-right"><button type="button" id="btnResetCustomerFilter" class="btn btn-light btn-sm border">Reset</button></div></form></div>
    <div class="card-body p-0 position-relative"><div id="customerTableOverlay" class="overlay d-none"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div><div id="customerTableContainer">@include('backoffice.admin.customers.partials.table',['isTrash'=>true])</div></div>
    <div class="card-footer bg-white" id="customerPagination">@if($customers->hasPages()){{ $customers->links() }}@endif</div>
</div>
@include('backoffice.admin.customers.partials.confirm')
@endsection
@section('plugins.Sweetalert2',true)
@push('js')@include('backoffice.admin.customers.partials.script',['fetchUrl'=>route('admin.customers.trash')])@endpush
