@extends('layouts.admin')

@section('meta_title', 'Permissions')

@section('page_content')
    <div id="ajaxAlertContainer"></div>

    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0 d-flex flex-wrap gap-2">
            @can('permissions.manage')
                <button type="button" class="btn btn-primary shadow-sm" id="btnCreatePermission">
                    <i class="fas fa-plus-circle mr-1"></i> Add New Permission
                </button>
            @endcan
            <a href="{{ route('admin.permissions.trash') }}" class="btn btn-outline-danger shadow-sm ml-2">
                <i class="fas fa-trash-alt mr-1"></i> Trash Bin
            </a>
        </div>

        <div class="col-12 col-md-auto">
            <form id="bulkActionForm" action="{{ route('admin.permissions.bulk-action') }}" method="POST" class="form-inline">
                @csrf
                <div class="input-group">
                    <select name="action" class="custom-select custom-select-sm" required>
                        <option value="">Bulk Actions</option>
                        @can('permissions.manage')
                            <option value="delete">Move to Trash</option>
                            <option value="restore">Restore</option>
                            <option value="force-delete">Permanent Delete</option>
                        @endcan
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
            <form id="filterForm" method="GET" action="{{ route('admin.permissions.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-layer-group text-muted"></i></span>
                        </div>
                        <select name="group_name" id="permission-group" class="form-control border-left-0">
                            <option value="">All Groups</option>
                            @foreach($groups as $group)
                                <option value="{{ $group }}">{{ $group }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" name="search" id="permission-search" class="form-control border-left-0" placeholder="Search permission name...">
                    </div>
                </div>
                <div class="col-12 col-md-3 text-md-right">
                    <button type="button" id="btnResetFilter" class="btn btn-light btn-sm border"><i class="fas fa-redo-alt mr-1"></i> Reset</button>
                </div>
            </form>
        </div>

        <div class="card-body p-0 position-relative">
            <div id="tableOverlay" class="overlay d-none">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="tableContainer">
                @include('backoffice.admin.permissions.partials.table', ['isTrash' => false])
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($permissions->hasPages())
                {{ $permissions->links() }}
            @endif
        </div>
    </div>

    @include('backoffice.admin.permissions.partials.form', ['groups' => $groups])
    @include('backoffice.admin.permissions.partials.show')

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
@section('plugins.Select2', true)



@push('js')
    @include('backoffice.admin.permissions.partials.script', ['fetchUrl' => route('admin.permissions.index')])
@endpush