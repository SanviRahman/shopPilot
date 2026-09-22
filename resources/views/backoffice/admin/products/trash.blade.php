@extends('layouts.admin')

@section('meta_title', 'Product Trash')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <a href="{{ route('admin.products.index') }}" class="btn btn-light border shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Products
            </a>
        </div>

        <div class="col-12 col-md-auto">
            <form id="bulkActionForm" action="{{ route('admin.products.bulk-action') }}" method="POST" class="form-inline">
                @csrf
                <div class="input-group">
                    <select name="action" class="custom-select custom-select-sm" required>
                        <option value="">Bulk Actions</option>
                        @can('products.restore')<option value="restore">Restore</option>@endcan
                        @can('products.force-delete')<option value="force-delete">Permanent Delete</option>@endcan
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
            <form id="filterForm" method="GET" action="{{ route('admin.products.trash') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" name="search" id="product-search" class="form-control border-left-0" placeholder="Search trashed products...">
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
                @include('backoffice.admin.products.partials.table', ['isTrash' => true])
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($products->hasPages())
                {{ $products->links() }}
            @endif
        </div>
    </div>

@endsection

@push('js')
    @include('backoffice.admin.products.partials.script', ['fetchUrl' => route('admin.products.trash')])
@endpush