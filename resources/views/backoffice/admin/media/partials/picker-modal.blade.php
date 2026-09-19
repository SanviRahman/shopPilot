<div
    class="modal fade"
    id="mediaPickerModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="mediaPickerModalTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            {{-- Header --}}
            <div class="modal-header bg-white">
                <div>
                    <h5
                        class="modal-title font-weight-bold mb-1"
                        id="mediaPickerModalTitle"
                    >
                        <i class="fas fa-photo-video text-primary mr-2"></i>
                        Select Existing Media
                    </h5>

                    <small class="text-muted">
                        Select one image to use in the current field.
                    </small>
                </div>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-light">
                {{-- Search and Filters --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="form-row align-items-end">
                            <div class="col-12 col-md-5 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-muted">
                                    Search Media
                                </label>

                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>

                                    <input
                                        type="search"
                                        id="mediaPickerSearch"
                                        class="form-control"
                                        placeholder="Search file name or collection..."
                                    >
                                </div>
                            </div>

                            <div class="col-6 col-md-3 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-muted">
                                    Collection
                                </label>

                                <select
                                    id="mediaPickerCollection"
                                    class="custom-select custom-select-sm"
                                >
                                    <option value="">
                                        All Collections
                                    </option>
                                </select>
                            </div>

                            <div class="col-6 col-md-2 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-muted">
                                    Disk
                                </label>

                                <select
                                    id="mediaPickerDisk"
                                    class="custom-select custom-select-sm"
                                >
                                    <option value="">
                                        All Disks
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2 mb-2 mb-md-0">
                                <div class="d-flex">
                                    <button
                                        type="button"
                                        id="mediaPickerRefresh"
                                        class="btn btn-primary btn-sm flex-fill mr-1"
                                    >
                                        <i class="fas fa-sync-alt mr-1"></i>
                                        Refresh
                                    </button>

                                    <button
                                        type="button"
                                        id="mediaPickerClearFilters"
                                        class="btn btn-light border btn-sm"
                                        title="Clear Filters"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Selection Toolbar --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap align-items-center">
                            <span class="text-muted small mr-3">
                                <i class="fas fa-images mr-1"></i>
                                <span id="mediaPickerResultCount">0</span>
                                result(s)
                            </span>

                            <span class="text-muted small mr-3">
                                <i class="fas fa-check-square mr-1"></i>
                                <span id="mediaPickerBulkCount">0</span>
                                selected
                            </span>

                            <button
                                type="button"
                                id="mediaPickerUseSelected"
                                class="btn btn-success btn-sm mr-2"
                                disabled
                            >
                                <i class="fas fa-check mr-1"></i>
                                Use Selected Image
                            </button>

                            @can('media.delete')
                                <button
                                    type="button"
                                    id="mediaPickerBulkDelete"
                                    class="btn btn-outline-danger btn-sm"
                                    disabled
                                >
                                    <i class="fas fa-trash-alt mr-1"></i>
                                    Delete Selected
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>

                {{-- Loading --}}
                <div
                    id="mediaPickerLoading"
                    class="text-center py-5 d-none"
                >
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>

                    <p class="text-muted mt-2 mb-0">
                        Loading media library...
                    </p>
                </div>

                {{-- Empty State --}}
                <div
                    id="mediaPickerEmpty"
                    class="text-center text-muted py-5 d-none"
                >
                    <i class="fas fa-images fa-3x mb-3 d-block"></i>

                    <h6 class="font-weight-bold">
                        No reusable images found
                    </h6>

                    <small>
                        Try another search keyword or filter.
                    </small>
                </div>

                {{-- Media Grid --}}
                <div
                    id="mediaPickerGrid"
                    class="row"
                ></div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-white">
                <small class="text-muted mr-auto">
                    Click an image to select it. Use checkboxes for bulk actions.
                </small>

                <button
                    type="button"
                    class="btn btn-light border btn-sm"
                    data-dismiss="modal"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Delete Form --}}
@can('media.delete')
    <form
        id="mediaPickerBulkForm"
        method="POST"
        action="{{ route('admin.media.bulk-action') }}"
        class="d-none"
    >
        @csrf

        <input
            type="hidden"
            name="action"
            value="delete"
        >
    </form>
@endcan

@push('css')
    <style>
        .media-picker-card {
            cursor: pointer;
            transition: all .2s ease;
            border: 2px solid transparent !important;
        }

        .media-picker-card:hover {
            transform: translateY(-3px);
            border-color: #007bff !important;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .12);
        }

        .media-picker-card.is-active {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, .15);
        }

        .media-picker-image-wrapper {
            height: 145px;
            background: #f8f9fa;
            overflow: hidden;
            position: relative;
        }

        .media-picker-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .media-picker-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 18px;
            height: 18px;
            z-index: 2;
        }

        .media-picker-selected-badge {
            position: absolute;
            right: 10px;
            top: 10px;
            display: none;
        }

        .media-picker-card.is-active .media-picker-selected-badge {
            display: inline-block;
        }
    </style>
@endpush