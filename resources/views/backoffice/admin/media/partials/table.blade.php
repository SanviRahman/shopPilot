@php
    $isTrash = $isTrash ?? false;
@endphp

<div class="row">
    @forelse ($media as $item)
        @php
            $isImage = str_starts_with(
                (string) $item->mime_type,
                'image/'
            );

            $isVideo = str_starts_with(
                (string) $item->mime_type,
                'video/'
            );
        @endphp

        <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-3">
            <div class="card media-card border-0 shadow-sm h-100">
                <div class="position-relative">
                    <input
                        type="checkbox"
                        name="media_ids[]"
                        value="{{ $item->id }}"
                        class="media-checkbox"
                        data-media-checkbox
                        data-media-id="{{ $item->id }}"
                        aria-label="Select {{ $item->file_name }}"
                    >

                    <div class="media-preview">
                        @if ($isImage)
                            <img
                                src="{{ $item->getUrl() }}"
                                alt="{{ $item->name }}"
                                loading="lazy"
                            >
                        @elseif ($isVideo)
                            <i class="fas fa-video fa-3x text-warning"></i>
                        @else
                            <i class="fas fa-file fa-3x text-muted"></i>
                        @endif
                    </div>

                    <span class="badge badge-dark media-type-badge">
                        {{ $item->mime_type ?: 'Unknown' }}
                    </span>
                </div>

                <div class="card-body p-2">
                    <h6
                        class="font-weight-bold text-truncate mb-1"
                        title="{{ $item->name }}"
                    >
                        {{ $item->name }}
                    </h6>

                    <small class="text-muted d-block text-truncate">
                        {{ $item->file_name }}
                    </small>

                    <div class="mt-2">
                        <span class="badge badge-info">
                            {{ $item->collection_name ?: 'Uncategorized' }}
                        </span>

                        <span class="badge badge-light border">
                            {{ number_format($item->size / 1024, 1) }} KB
                        </span>
                    </div>

                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-cube mr-1"></i>
                        {{ class_basename($item->model_type) }}
                        #{{ $item->model_id }}
                    </small>

                    <small class="text-muted d-block">
                        <i class="far fa-clock mr-1"></i>

                        {{
                            optional(
                                $isTrash
                                    ? $item->deleted_at
                                    : $item->created_at
                            )->format('d M Y, h:i A')
                        }}
                    </small>
                </div>

                <div class="card-footer bg-white border-top p-1 text-right">
                    @if ($isTrash)
                        @can('media.restore')
                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.media.restore',
                                    $item->id
                                ) }}"
                                class="d-inline media-confirm-form"
                                data-confirm-title="Restore Media?"
                                data-confirm-text="This media will be restored."
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="btn btn-outline-success btn-sm"
                                    title="Restore"
                                >
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                            </form>
                        @endcan

                        @can('media.force-delete')
                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.media.force-delete',
                                    $item->id
                                ) }}"
                                class="d-inline media-confirm-form"
                                data-confirm-title="Permanent Delete?"
                                data-confirm-text="This file cannot be recovered."
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-outline-danger btn-sm"
                                    title="Permanent Delete"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        @endcan
                    @else
                        @can('media.delete')
                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.media.destroy',
                                    $item->id
                                ) }}"
                                class="d-inline media-confirm-form"
                                data-confirm-title="Move to Trash?"
                                data-confirm-text="This media can be restored later."
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-outline-danger btn-sm"
                                    title="Move to Trash"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center text-muted py-5">
                <i class="fas fa-images fa-3x text-light mb-3 d-block"></i>
                No media files found.
            </div>
        </div>
    @endforelse
</div>

<style>
    .media-card {
        border-radius: 12px;
        overflow: hidden;
        transition: all .2s ease;
    }

    .media-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, .12) !important;
    }

    .media-preview {
        height: 130px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .media-checkbox {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 2;
        width: 18px;
        height: 18px;
    }

    .media-type-badge {
        position: absolute;
        top: 10px;
        right: 10px;
    }
</style>