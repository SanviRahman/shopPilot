@php
    $isTrash = $isTrash ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th>Configuration</th>
                <th>Pixel IDs</th>
                <th>Scripts</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Lifecycle</th>
                    <th>Schedule</th>
                    <th>Tracking</th>
                @endif
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pixels as $pixel)
                @php
                    $entries = collect($pixel->pixelEntries());
                    $scriptCount = $entries->filter(function ($entry) {
                        return filled($entry['script'] ?? null);
                    })->count();
                @endphp
                <tr>
                    <td>
                        <strong class="d-block text-dark">{{ $pixel->name }}</strong>
                        <small class="text-muted">#{{ $pixel->id }}</small>
                    </td>
                    <td>
                        @foreach($entries as $entry)
                            <code class="d-block">{{ $entry['pixel_id'] }}</code>
                        @endforeach
                    </td>
                    <td>
                        <span class="badge badge-{{ $scriptCount > 0 ? 'dark' : 'light' }} border">
                            {{ $scriptCount }} custom {{ \Illuminate\Support\Str::plural('script', $scriptCount) }}
                        </span>
                    </td>
                    @if($isTrash)
                        <td><small class="text-muted">{{ $pixel->deleted_at?->format('d M Y, h:i A') }}</small></td>
                    @else
                        <td>
                            <span class="badge badge-{{ $pixel->lifecycle_status === 'active' ? 'success' : ($pixel->lifecycle_status === 'testing' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($pixel->lifecycle_status) }}
                            </span>
                        </td>
                        <td>
                            <small>
                                {{ $pixel->starts_at?->format('d M Y H:i') ?? 'Immediately' }}<br>
                                {{ $pixel->ends_at?->format('d M Y H:i') ?? 'No expiry' }}
                            </small>
                        </td>
                        <td>
                            <span class="badge badge-light border">PageView: {{ $pixel->track_page_view ? 'On' : 'Off' }}</span><br>
                            <span class="badge badge-light border mt-1">Ecommerce: {{ $pixel->track_ecommerce ? 'On' : 'Off' }}</span>
                        </td>
                    @endif
                    <td class="text-right text-nowrap">
                        @if($isTrash)
                            @can('meta-pixels.restore')
                                <button
                                    type="button"
                                    class="btn btn-outline-success btn-sm btn-meta-pixel-action"
                                    data-url="{{ route('admin.meta-pixels.restore', $pixel->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Meta Pixel?"
                                    data-confirm-text="This configuration will return to the active list."
                                    title="Restore"
                                >
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                            @endcan
                            @can('meta-pixels.force-delete')
                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm btn-meta-pixel-action"
                                    data-url="{{ route('admin.meta-pixels.force-delete', $pixel->id) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Permanently delete?"
                                    data-confirm-text="This action cannot be undone."
                                    title="Permanent Delete"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <button
                                type="button"
                                class="btn btn-default btn-sm btn-view-meta-pixel"
                                data-url="{{ route('admin.meta-pixels.show', $pixel) }}"
                                title="View"
                            >
                                <i class="fas fa-eye text-info"></i>
                            </button>
                            @can('meta-pixels.update')
                                <button
                                    type="button"
                                    class="btn btn-default btn-sm btn-edit-meta-pixel"
                                    data-url="{{ route('admin.meta-pixels.edit', $pixel) }}"
                                    data-update-url="{{ route('admin.meta-pixels.update', $pixel) }}"
                                    title="Edit"
                                >
                                    <i class="fas fa-pen text-primary"></i>
                                </button>
                            @endcan
                            @can('meta-pixels.delete')
                                <button
                                    type="button"
                                    class="btn btn-default btn-sm btn-meta-pixel-action"
                                    data-url="{{ route('admin.meta-pixels.destroy', $pixel) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Move to trash?"
                                    data-confirm-text="The Meta Pixel configuration can be restored later."
                                    title="Move to Trash"
                                >
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            @endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isTrash ? 5 : 7 }}" class="text-center text-muted py-5">
                        {{ $isTrash ? 'Trash is empty.' : 'No Meta Pixel configurations found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
