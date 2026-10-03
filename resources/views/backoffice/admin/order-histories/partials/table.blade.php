@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-histories-list' : 'order-histories-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-histories-list' : 'order-histories-list' }}">
                </th>
                <th>Order #</th>
                <th>Actor</th>
                <th>Status Transition</th>
                <th>Note / Event Summary</th>
                <th>{{ $isTrash ? 'Deleted At' : 'Timestamp' }}</th>
                <th width="100" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($histories as $history)
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="history_ids[]" value="{{ $history->id }}" data-row-checkbox>
                    </td>
                    <td class="align-middle">
                        @if($history->order)
                            <a href="{{ route('admin.orders.index') }}?search={{ urlencode($history->order->order_number) }}" class="font-weight-bold text-primary font-monospace">
                                {{ $history->order->order_number }}
                            </a>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td class="align-middle">{!! $history->actor_badge !!}</td>
                    <td class="align-middle">{!! $history->status_transition_badge !!}</td>
                    <td class="align-middle">
                        <span class="d-inline-block text-truncate" style="max-width: 280px;" title="{{ $history->note }}">
                            {{ $history->note ?: '—' }}
                        </span>
                    </td>
                    <td class="align-middle text-nowrap">
                        @if($isTrash)
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($history->deleted_at)->format('d M Y, h:i A') }}</small>
                        @else
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($history->created_at)->format('d M Y, h:i A') }}</small>
                        @endif
                    </td>
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('restore', $history->order)
                                <button type="button" class="btn btn-outline-success btn-sm btn-action"
                                    data-url="{{ route('admin.order-histories.restore', $history->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore History?"
                                    data-confirm-text="This history record will be restored."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('view', $history->order)
                                    <button type="button" class="btn btn-default btn-view-history" data-id="{{ $history->id }}" title="View Full Details">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('delete', $history->order)
                                    <button type="button" class="btn btn-default btn-action"
                                        data-url="{{ route('admin.order-histories.destroy', $history) }}"
                                        data-method="DELETE"
                                        data-confirm-title="Move to Trash?"
                                        data-confirm-text="This history record will be moved to the recovery trash."
                                        title="Move to Trash">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                @endcan
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="fas fa-history fa-3x text-light mb-3 d-block"></i>
                        {{ $isTrash ? 'Trash bin is completely empty.' : 'No audit history records found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
