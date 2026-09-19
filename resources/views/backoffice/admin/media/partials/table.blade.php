@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40"></th>
                <th width="90">Preview</th>
                <th>File</th>
                <th>Collection</th>
                <th>Owner</th>
                <th>Size</th>
                <th>{{ $isTrash ? 'Deleted At' : 'Created At' }}</th>
                <th width="150" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($media as $item)
                @php($isImage = str_starts_with((string) $item->mime_type, 'image/'))
                <tr>
                    <td><input type="checkbox" name="media_ids[]" value="{{ $item->id }}" data-media-checkbox></td>
                    <td>
                        @if($isImage)
                            <img src="{{ $item->getUrl() }}" alt="{{ $item->name }}" class="img-thumbnail" style="width:70px;height:55px;object-fit:cover;">
                        @else
                            <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:70px;height:55px;">
                                <i class="fas fa-file text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="font-weight-bold text-dark text-truncate" style="max-width:260px;" title="{{ $item->name }}">{{ $item->name }}</div>
                        <small class="text-muted">{{ $item->file_name }}</small>
                        <br><span class="badge badge-light border">{{ $item->mime_type ?: 'unknown' }}</span>
                    </td>
                    <td><span class="badge badge-info">{{ $item->collection_name }}</span></td>
                    <td><small class="text-muted">{{ class_basename($item->model_type) }} #{{ $item->model_id }}</small></td>
                    <td>{{ number_format($item->size / 1024, 1) }} KB</td>
                    <td><small class="text-muted">{{ optional($isTrash ? $item->deleted_at : $item->created_at)->format('d M Y, h:i A') }}</small></td>
                    <td class="text-right text-nowrap">
                        @if($isTrash)
                            @can('media.restore')
                                <form method="POST" action="{{ route('admin.media.restore', $item->id) }}" class="d-inline media-confirm-form" data-confirm-title="Restore Media?" data-confirm-text="This file will return to the active media list." data-confirm-type="question">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline-success btn-sm" title="Restore"><i class="fas fa-trash-restore"></i></button>
                                </form>
                            @endcan
                            @can('media.force-delete')
                                <form method="POST" action="{{ route('admin.media.force-delete', $item->id) }}" class="d-inline media-confirm-form" data-confirm-title="Permanently Delete?" data-confirm-text="The database record and physical file will be removed permanently." data-confirm-type="warning">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" title="Permanent Delete"><i class="fas fa-times"></i></button>
                                </form>
                            @endcan
                        @else
                            @can('media.delete')
                                <form method="POST" action="{{ route('admin.media.destroy', $item->id) }}" class="d-inline media-confirm-form" data-confirm-title="Move Media to Trash?" data-confirm-text="The file can be restored later." data-confirm-type="warning">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" title="Move to Trash"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            @endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-images fa-3x text-light mb-2 d-block"></i>No media found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
