@extends('layouts.admin')

@section('meta_title', 'Contact Trash')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline-primary shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Contacts
            </a>
        </div>
        <div class="col-12 col-md-auto">
            <form id="contactFilterForm" method="GET" action="{{ route('admin.contacts.trash') }}" class="form-inline">
                <input type="search" name="search" id="contact-search" value="{{ request('search') }}" class="form-control form-control-sm mr-2" placeholder="Search trash...">
                <button type="button" id="btnResetContactFilter" class="btn btn-light btn-sm border">
                    <i class="fas fa-redo-alt mr-1"></i> Reset
                </button>
            </form>
        </div>
    </div>

    <div class="card card-outline card-danger shadow-sm">
        <div class="card-body p-0 position-relative">
            <div id="contactTableOverlay" class="overlay d-none"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
            <div id="contactTableContainer">
                @include('backoffice.admin.contacts.partials.table', ['isTrash' => true])
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2" id="contactPaginationContainer">
            @if($contacts->hasPages())
                {{ $contacts->links() }}
            @endif
        </div>
    </div>
@endsection

@push('js')
    @include('backoffice.admin.contacts.partials.script', [
        'fetchUrl' => route('admin.contacts.trash'),
        'isTrashPage' => true,
    ])
@endpush
