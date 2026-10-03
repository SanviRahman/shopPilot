@extends('layouts.admin')

@section('meta_title', 'Meta Pixel Trash')

@section('page_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.meta-pixels.index') }}" class="btn btn-light border shadow-sm mb-2 mb-md-0">
            <i class="fas fa-arrow-left mr-1"></i> Back to Meta Pixels
        </a>

        <form id="metaPixelFilterForm" method="GET" action="{{ route('admin.meta-pixels.trash') }}" class="form-inline">
            <input
                type="search"
                name="search"
                id="meta-pixel-search"
                value="{{ request('search') }}"
                class="form-control form-control-sm mr-2"
                placeholder="Search trash..."
            >
            <button type="button" id="btnResetMetaPixelFilter" class="btn btn-light btn-sm border">
                <i class="fas fa-redo-alt mr-1"></i> Reset
            </button>
        </form>
    </div>

    <div class="card card-outline card-danger shadow-sm">
        <div class="card-body p-0 position-relative">
            <div id="metaPixelTableOverlay" class="overlay d-none">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="metaPixelTableContainer">
                @include('backoffice.admin.meta-pixels.partials.table', ['isTrash' => true])
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2" id="metaPixelPaginationContainer">
            @if($pixels->hasPages())
                {{ $pixels->links() }}
            @endif
        </div>
    </div>
@endsection

@push('js')
    @include('backoffice.admin.meta-pixels.partials.script', [
        'fetchUrl' => route('admin.meta-pixels.trash'),
        'isTrashPage' => true,
    ])
@endpush
