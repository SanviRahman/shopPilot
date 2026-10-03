@extends('layouts.admin')

@section('meta_title', 'Contact Messages')

@section('page_content')
<div class="row align-items-center justify-content-between mb-3">
    <div class="col-12 col-md-auto"><h4 class="font-weight-bold mb-0"><i class="fas fa-inbox text-primary mr-2"></i> Contact Messages</h4><small class="text-muted">Customer enquiries submitted from the frontend Contact page.</small></div>
    <div class="col-12 col-md-auto mt-2 mt-md-0"><a href="{{ route('admin.contact-messages.trash') }}" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt mr-1"></i> Trash Bin</a></div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header bg-white">
        <form id="contactMessageFilterForm" method="GET" action="{{ route('admin.contact-messages.index') }}" class="form-row align-items-end">
            <div class="col-lg-7 col-md-6 mb-2 mb-md-0"><label class="small text-muted font-weight-bold">Search</label><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Name, email, phone, subject or message..."></div>
            <div class="col-lg-3 col-md-3 mb-2 mb-md-0"><label class="small text-muted font-weight-bold">Status</label><select name="status" class="custom-select custom-select-sm"><option value="">All Statuses</option><option value="new" @selected(($filters['status'] ?? '') === 'new')>New</option><option value="read" @selected(($filters['status'] ?? '') === 'read')>Read</option><option value="resolved" @selected(($filters['status'] ?? '') === 'resolved')>Resolved</option></select></div>
            <div class="col-lg-2 col-md-3 text-md-right"><button type="button" id="btnResetContactMessageFilter" class="btn btn-light border btn-sm btn-block"><i class="fas fa-redo-alt mr-1"></i> Reset</button></div>
        </form>
    </div>

    <form id="contactMessageBulkForm" method="POST" action="{{ route('admin.contact-messages.bulk-action') }}">
        @csrf
        <div class="card-body border-bottom py-2"><div class="d-flex align-items-center flex-wrap"><select name="action" class="custom-select custom-select-sm mr-2 mb-1" style="width: 210px;"><option value="">Bulk Actions</option>@can('contact-messages.update')<option value="mark-read">Mark as Read</option><option value="mark-resolved">Mark as Resolved</option>@endcan @can('contact-messages.delete')<option value="delete">Move to Trash</option>@endcan</select><button type="submit" class="btn btn-primary btn-sm mb-1"><i class="fas fa-check mr-1"></i> Apply</button></div></div>
    </form>

    <div class="card-body p-0 position-relative"><div id="contactMessageTableOverlay" class="overlay d-none"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div><div id="contactMessageTableContainer">@include('backoffice.admin.contact-messages.partials.table', ['messages' => $messages, 'isTrash' => false])</div></div>
    <div class="card-footer bg-white border-top py-2" id="contactMessagePaginationContainer">@if($messages->hasPages()){{ $messages->links() }}@endif</div>
</div>

@include('backoffice.admin.contact-messages.partials.form')
@include('backoffice.admin.contact-messages.partials.show')
@endsection

@push('js')
@include('backoffice.admin.contact-messages.partials.script', ['fetchUrl' => route('admin.contact-messages.index'), 'isTrashPage' => false])
@endpush
