@extends('layouts.admin')

@section('meta_title', 'Admin Trash')

@section('page_content')
    <div id="ajaxAlertContainer"></div>

    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <a href="{{ route('admin.admins.index') }}" class="btn btn-light border shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Admins
            </a>
        </div>

        <div class="col-12 col-md-auto">
            <form id="bulkActionForm" action="{{ route('admin.admins.bulk-action') }}" method="POST" class="form-inline">
                @csrf
                <div class="input-group">
                    <select name="action" class="custom-select custom-select-sm" required>
                        <option value="">Bulk Actions</option>
                        @can('staff.restore')<option value="restore">Restore</option>@endcan
                        @can('staff.force-delete')<option value="force-delete">Permanent Delete</option>@endcan
                    </select>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-secondary btn-sm px-3">Apply</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-danger shadow-sm">
        <div class="card-header bg-white py-3">
            <form id="filterForm" method="GET" action="{{ route('admin.admins.trash') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" name="search" id="search" class="form-control border-left-0" placeholder="Search trashed admins by name or email...">
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-right">
                    <button type="button" id="btnResetFilter" class="btn btn-light btn-sm border"><i class="fas fa-redo-alt mr-1"></i> Reset</button>
                </div>
            </form>
        </div>

        <div class="card-body p-0 position-relative">
            <div id="tableOverlay" class="overlay d-none">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="tableContainer">
                @include('backoffice.admin.admins.partials.table', ['isTrash' => true])
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($admins->hasPages())
                {{ $admins->links() }}
            @endif
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div class="modal fade" id="confirmModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-3">
                        <i class="fas fa-trash-alt fa-3x"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2" id="confirmModalTitle">Are you sure?</h5>
                    <p class="text-muted small mb-4" id="confirmModalText">Confirm this action.</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light btn-sm px-3 mr-2" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger btn-sm px-4" id="confirmModalBtn">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    @include('backoffice.admin.admins.partials.script', ['fetchUrl' => route('admin.admins.trash')])
@endpush