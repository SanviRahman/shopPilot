@extends('layouts.admin')

@section('meta_title', 'Category Management')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0 d-flex flex-wrap gap-2">
            @can('categories.create')
                <button type="button" class="btn btn-primary shadow-sm" id="btnCreateCategory">
                    <i class="fas fa-plus-circle mr-1"></i> Add New Category
                </button>
            @endcan
            <a href="{{ route('admin.categories.trash') }}" class="btn btn-outline-danger shadow-sm ml-2">
                <i class="fas fa-trash-alt mr-1"></i> Trash Bin
            </a>
        </div>

        <div class="col-12 col-md-auto">
            <form id="bulkActionForm" action="{{ route('admin.categories.bulk-action') }}" method="POST" class="form-inline">
                @csrf
                <div class="input-group">
                    <select name="action" class="custom-select custom-select-sm" required>
                        <option value="">Bulk Actions</option>
                        @can('categories.delete')<option value="delete">Move to Trash</option>@endcan
                        @can('categories.restore')<option value="restore">Restore</option>@endcan
                        @can('categories.force-delete')<option value="force-delete">Permanent Delete</option>@endcan
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
            <form id="filterForm" method="GET" action="{{ route('admin.categories.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-toggle-on text-muted"></i></span>
                        </div>
                        <select name="status" id="category-status-filter" class="form-control border-left-0">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" name="search" id="category-search" class="form-control border-left-0" placeholder="Search by category name or slug...">
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
                @include('backoffice.admin.categories.partials.table', ['isTrash' => false])
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($categories->hasPages())
                {{ $categories->links() }}
            @endif
        </div>
    </div>

    @include('backoffice.admin.categories.partials.form')
    @include('backoffice.admin.categories.partials.show')

@endsection

@push('js')
    @include('backoffice.admin.categories.partials.script', ['fetchUrl' => route('admin.categories.index')])
@endpush