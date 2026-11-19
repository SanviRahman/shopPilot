@extends('layouts.admin')

@section('meta_title', 'Meta Pixel')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            @can('meta-pixels.create')
                <button type="button" class="btn btn-primary shadow-sm" id="btnCreateMetaPixel">
                    <i class="fas fa-plus-circle mr-1"></i> Add Meta Pixel
                </button>
            @endcan
            <a href="{{ route('admin.meta-pixels.trash') }}" class="btn btn-outline-danger shadow-sm ml-2">
                <i class="fas fa-trash-alt mr-1"></i> Trash Bin
            </a>
        </div>

        <div class="col-12 col-md-auto">
            <form id="metaPixelFilterForm" method="GET" action="{{ route('admin.meta-pixels.index') }}" class="form-inline">
                <input
                    type="search"
                    name="search"
                    id="meta-pixel-search"
                    value="{{ request('search') }}"
                    class="form-control form-control-sm mr-2"
                    placeholder="Search pixel..."
                >
                <select name="lifecycle_status" id="meta-pixel-status-filter" class="custom-select custom-select-sm mr-2">
                    <option value="">All lifecycle</option>
                    @foreach(\App\Models\MetaPixel::LIFECYCLE as $state)
                        <option value="{{ $state }}" @selected(request('lifecycle_status') === $state)>{{ ucfirst($state) }}</option>
                    @endforeach
                </select>
                <button type="button" id="btnResetMetaPixelFilter" class="btn btn-light btn-sm border">
                    <i class="fas fa-redo-alt mr-1"></i> Reset
                </button>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body p-0 position-relative">
            <div id="metaPixelTableOverlay" class="overlay d-none">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="metaPixelTableContainer">
                @include('backoffice.admin.meta-pixels.partials.table', ['isTrash' => false])
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2" id="metaPixelPaginationContainer">
            @if($pixels->hasPages())
                {{ $pixels->links() }}
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title font-weight-bold mb-0 text-primary">
                <i class="fab fa-facebook mr-2"></i> Meta Pixel + Conversions API (CAPI) Integration
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="p-3 bg-light rounded border">
                        <small class="text-uppercase text-muted font-weight-bold d-block">Tracking Architecture</small>
                        <h6 class="font-weight-bold mt-1 text-dark">Browser Pixel + Server CAPI</h6>
                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Active Dual Dispatch</span>
                        <p class="small text-muted mt-2 mb-0">Browser Pixel and Server CAPI send matching <code>event_id</code> for automatic Meta deduplication.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="p-3 bg-light rounded border">
                        <small class="text-uppercase text-muted font-weight-bold d-block">Database Footprint</small>
                        <h6 class="font-weight-bold mt-1 text-dark">Zero DB Writes</h6>
                        <span class="badge badge-info"><i class="fas fa-database mr-1"></i> No Database Bloat</span>
                        <p class="small text-muted mt-2 mb-0">Events are dispatched directly via Meta Graph API without storing bulky event logs in the MySQL database.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded border">
                        <small class="text-uppercase text-muted font-weight-bold d-block">Conversions API Configuration</small>
                        <h6 class="font-weight-bold mt-1 text-dark">{{ config('meta-pixel.capi.api_version', 'v21.0') }}</h6>
                        @if(filled(config('meta-pixel.capi.access_token')))
                            <span class="badge badge-success"><i class="fas fa-key mr-1"></i> Access Token Set</span>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i> Token in .env (META_CAPI_ACCESS_TOKEN)</span>
                        @endif
                        <p class="small text-muted mt-2 mb-0">Inspect and debug live events in real-time in your <a href="https://business.facebook.com/events_manager" target="_blank" rel="noopener">Meta Events Manager <i class="fas fa-external-link-alt"></i></a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('backoffice.admin.meta-pixels.partials.form')
    @include('backoffice.admin.meta-pixels.partials.show')
@endsection

@push('js')
    @include('backoffice.admin.meta-pixels.partials.script', [
        'fetchUrl' => route('admin.meta-pixels.index'),
        'isTrashPage' => false,
    ])
@endpush
