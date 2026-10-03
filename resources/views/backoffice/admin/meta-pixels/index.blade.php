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

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header"><h3 class="card-title font-weight-bold">Event Summary</h3></div>
                <div class="card-body">
                    @forelse($eventCounts as $name => $count)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>{{ $name }}</span>
                            <span class="badge badge-primary">{{ number_format($count) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No tracked events yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header"><h3 class="card-title font-weight-bold">Recent Tracking Events</h3></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Event</th><th>User</th><th>Status</th><th>Occurred</th></tr></thead>
                            <tbody>
                                @forelse($events as $event)
                                    <tr>
                                        <td><code>{{ $event->event_name }}</code></td>
                                        <td>{{ $event->user_id ? '#'.$event->user_id : 'Guest' }}</td>
                                        <td><span class="badge badge-info">{{ $event->delivery_status }}</span></td>
                                        <td><small>{{ $event->occurred_at?->format('d M, H:i:s') }}</small></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No events captured.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
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
