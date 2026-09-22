@extends('layouts.admin')

@section('meta_title', 'Media Trash')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <h4 class="font-weight-bold mb-0">
                <i class="fas fa-trash-alt text-danger mr-2"></i>
                Media Trash
            </h4>
        </div>

        <div class="col-12 col-md-auto">
            <a
                href="{{ route('admin.media.index') }}"
                class="btn btn-light border btn-sm"
            >
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Media
            </a>
        </div>
    </div>

    <div class="alert alert-warning border-0 shadow-sm">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Restore keeps the physical file. Permanent delete removes the physical file and cannot be undone.
    </div>

    <div class="card card-outline card-danger shadow-sm">
        <div class="card-header bg-white">
            <form
                method="GET"
                action="{{ route('admin.media.trash') }}"
                class="form-row align-items-end"
            >
                <div class="col-12 col-md-7 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Search</label>
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control form-control-sm"
                        placeholder="File name, collection, model..."
                    >
                </div>

                <div class="col-12 col-md-5 text-md-right">
                    <button class="btn btn-danger btn-sm">
                        <i class="fas fa-search mr-1"></i>
                        Filter
                    </button>
                    <a
                        href="{{ route('admin.media.trash') }}"
                        class="btn btn-light border btn-sm"
                    >Reset</a>
                </div>
            </form>
        </div>

        {{-- Keep this form separate from the media cards. --}}
        <form
            id="mediaBulkForm"
            method="POST"
            action="{{ route('admin.media.bulk-action') }}"
        >
            @csrf

            <div class="card-body border-bottom py-2">
                <div class="d-flex flex-wrap align-items-center">
                    <label class="mb-0 mr-3">
                        <input
                            type="checkbox"
                            id="mediaSelectAll"
                            class="mr-1"
                        >
                        Select All
                    </label>

                    <select
                        name="action"
                        class="custom-select custom-select-sm mr-2"
                        style="width: 220px;"
                    >
                        <option value="">Bulk Actions</option>

                        @can('media.restore')
                            <option value="restore">
                                Restore Selected
                            </option>
                        @endcan

                        @can('media.force-delete')
                            <option value="force-delete">
                                Permanent Delete Selected
                            </option>
                        @endcan
                    </select>

                    <button
                        type="submit"
                        class="btn btn-danger btn-sm"
                    >
                        <i class="fas fa-check mr-1"></i>
                        Apply
                    </button>
                </div>
            </div>
        </form>

        {{-- Must stay outside #mediaBulkForm because table has individual forms. --}}
      <div class="card-body p-0 position-relative">
            <div id="mediaTableOverlay" class="overlay d-none">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="mediaTableContainer">
                @include('backoffice.admin.media.partials.table', ['isTrash' => false]) {{-- trash-e true hobe --}}
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($media->hasPages())
                {{ $media->links() }}
            @endif
        </div>
    </div>
@endsection

@push('js')
    @include('backoffice.admin.media.partials.script')
@endpush
