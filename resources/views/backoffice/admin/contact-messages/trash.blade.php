@extends('layouts.admin')

@section('meta_title', 'Contact Message Trash')

@section('page_content')
<div class="row align-items-center justify-content-between mb-3"><div class="col-12 col-md-auto"><h4 class="font-weight-bold mb-0"><i class="fas fa-trash-alt text-danger mr-2"></i> Contact Message Trash</h4></div><div class="col-12 col-md-auto mt-2 mt-md-0"><a href="{{ route('admin.contact-messages.index') }}" class="btn btn-light border btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back to Inbox</a></div></div>
<div class="card card-outline card-danger shadow-sm">
    <div class="card-header bg-white"><form id="contactMessageFilterForm" method="GET" action="{{ route('admin.contact-messages.trash') }}" class="form-row align-items-end"><div class="col-md-9"><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Search deleted contact messages..."></div><div class="col-md-3 text-md-right mt-2 mt-md-0"><button type="button" id="btnResetContactMessageFilter" class="btn btn-light border btn-sm"><i class="fas fa-redo-alt mr-1"></i> Reset</button></div></form></div>
    <form id="contactMessageBulkForm" method="POST" action="{{ route('admin.contact-messages.bulk-action') }}">@csrf<div class="card-body border-bottom py-2"><div class="d-flex align-items-center"><select name="action" class="custom-select custom-select-sm mr-2" style="width: 220px;"><option value="">Bulk Actions</option>@can('contact-messages.restore')<option value="restore">Restore Selected</option>@endcan @can('contact-messages.force-delete')<option value="force-delete">Permanent Delete</option>@endcan</select><button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-check mr-1"></i> Apply</button></div></div></form>
    <div class="card-body p-0 position-relative"><div id="contactMessageTableOverlay" class="overlay d-none"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div><div id="contactMessageTableContainer">@include('backoffice.admin.contact-messages.partials.table', ['messages' => $messages, 'isTrash' => true])</div></div>
    <div class="card-footer bg-white border-top py-2" id="contactMessagePaginationContainer">@if($messages->hasPages()){{ $messages->links() }}@endif</div>
</div>
@include('backoffice.admin.contact-messages.partials.show')
@endsection

@push('js')
@include('backoffice.admin.contact-messages.partials.script', ['fetchUrl' => route('admin.contact-messages.trash'), 'isTrashPage' => true])
@endpush
