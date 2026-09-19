@extends('layouts.admin')

@section('meta_title', 'Media Management')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <h4 class="font-weight-bold mb-0">
                <i class="fas fa-photo-video text-primary mr-2"></i>
                All Media
            </h4>
        </div>

        <div class="col-12 col-md-auto">
            <a
                href="{{ route('admin.media.index') }}"
                class="btn btn-primary btn-sm mr-1"
            >
                <i class="fas fa-images mr-1"></i> Active Media
            </a>

            <a
                href="{{ route('admin.media.trash') }}"
                class="btn btn-outline-danger btn-sm"
            >
                <i class="fas fa-trash-alt mr-1"></i> Trash Bin
            </a>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <i class="fas fa-info-circle mr-1"></i>
        All files managed by Spatie Media Library are listed here. Delete moves files to Trash; permanent delete removes the database record and physical file.
    </div>

    <div class="row mb-3">
        @foreach([
            ['icon' => 'fa-folder-open', 'color' => 'primary', 'label' => 'Total Media', 'value' => $stats['total']],
            ['icon' => 'fa-image', 'color' => 'success', 'label' => 'Images', 'value' => $stats['images']],
            ['icon' => 'fa-video', 'color' => 'warning', 'label' => 'Videos', 'value' => $stats['videos']],
            ['icon' => 'fa-database', 'color' => 'secondary', 'label' => 'Storage Used', 'value' => number_format($stats['storage'] / 1048576, 2).' MB'],
        ] as $stat)
            <div class="col-6 col-lg-3 mb-2">
                <div class="small-box bg-white shadow-sm mb-0 border-left border-{{ $stat['color'] }}">
                    <div class="inner py-3">
                        <p class="text-muted mb-1">{{ $stat['label'] }}</p>
                        <h4 class="font-weight-bold mb-0">{{ $stat['value'] }}</h4>
                    </div>
                    <div class="icon text-{{ $stat['color'] }}">
                        <i class="fas {{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white">
            <form
                method="GET"
                action="{{ route('admin.media.index') }}"
                class="form-row align-items-end"
            >
                <div class="col-12 col-md-5 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Search</label>
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control form-control-sm"
                        placeholder="File name, collection, model..."
                    >
                </div>

                <div class="col-6 col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Type</label>
                    <select name="type" class="custom-select custom-select-sm">
                        <option value="">All Types</option>
                        <option
                            value="image"
                            @selected(($filters['type'] ?? '') === 'image')
                        >Images</option>
                        <option
                            value="video"
                            @selected(($filters['type'] ?? '') === 'video')
                        >Videos</option>
                        <option
                            value="other"
                            @selected(($filters['type'] ?? '') === 'other')
                        >Other</option>
                    </select>
                </div>

                <div class="col-6 col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Disk</label>
                    <select name="disk" class="custom-select custom-select-sm">
                        <option value="">All Disks</option>
                        @foreach($media->getCollection()->pluck('disk')->unique() as $disk)
                            <option
                                value="{{ $disk }}"
                                @selected(($filters['disk'] ?? '') === $disk)
                            >{{ $disk }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 text-md-right">
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a
                        href="{{ route('admin.media.index') }}"
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

                    @can('media.delete')
                        <select
                            name="action"
                            class="custom-select custom-select-sm mr-2"
                            style="width: 190px;"
                        >
                            <option value="">Bulk Actions</option>
                            <option value="delete">
                                Move Selected to Trash
                            </option>
                        </select>

                        <button
                            type="submit"
                            class="btn btn-danger btn-sm"
                        >
                            <i class="fas fa-trash-alt mr-1"></i>
                            Apply
                        </button>
                    @endcan
                </div>
            </div>
        </form>

        {{-- Must stay outside #mediaBulkForm because table has individual forms. --}}
        @include('backoffice.admin.media.partials.table', [
            'isTrash' => false,
        ])

        @if($media->hasPages())
            <div class="card-footer bg-white">
                {{ $media->links() }}
            </div>
        @endif
    </div>
@endsection

@push('js')
    @include('backoffice.admin.media.partials.script')
@endpush
