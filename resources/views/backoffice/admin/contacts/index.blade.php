@extends('layouts.admin')

@section('meta_title', 'Contacts')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            @can('contacts.create')
                <button type="button" class="btn btn-primary shadow-sm" id="btnCreateContact">
                    <i class="fas fa-plus-circle mr-1"></i> Add Contact
                </button>
            @endcan
            <a href="{{ route('admin.contacts.trash') }}" class="btn btn-outline-danger shadow-sm ml-2">
                <i class="fas fa-trash-alt mr-1"></i> Trash Bin
            </a>
        </div>

        <div class="col-12 col-md-auto">
            <form id="contactFilterForm" method="GET" action="{{ route('admin.contacts.index') }}" class="form-inline">
                <input type="search" name="search" id="contact-search" value="{{ request('search') }}" class="form-control form-control-sm mr-2" placeholder="Search contact...">
                <select name="status" id="contact-status-filter" class="custom-select custom-select-sm mr-2">
                    <option value="">All status</option>
                    @foreach(\App\Models\Contact::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="button" id="btnResetContactFilter" class="btn btn-light btn-sm border">
                    <i class="fas fa-redo-alt mr-1"></i> Reset
                </button>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body p-0 position-relative">
            <div id="contactTableOverlay" class="overlay d-none"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
            <div id="contactTableContainer">
                @include('backoffice.admin.contacts.partials.table', ['isTrash' => false])
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2" id="contactPaginationContainer">
            @if($contacts->hasPages())
                {{ $contacts->links() }}
            @endif
        </div>
    </div>

    @include('backoffice.admin.contacts.partials.form')
    @include('backoffice.admin.contacts.partials.show')
@endsection

@push('js')
    @include('backoffice.admin.contacts.partials.script', [
        'fetchUrl' => route('admin.contacts.index'),
        'isTrashPage' => false,
    ])
@endpush
