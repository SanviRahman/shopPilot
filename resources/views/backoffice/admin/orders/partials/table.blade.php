@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-orders-list' : 'orders-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-orders-list' : 'orders-list' }}">
                </th>
                <th>Order</th>
                <th>Customer / Buyer</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Assigned Agent</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Date</th>
                @endif
                <th width="120" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" data-row-checkbox>
                    </td>
                    <td class="align-middle">
                        <span class="font-weight-bold text-primary font-monospace">{{ $order->order_number }}</span>
                        @if($order->isGuest())
                            <span class="badge badge-light border text-muted ml-1" title="Placed as Guest">Guest</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        <div class="font-weight-600 text-dark">{{ $order->buyer_name }}</div>
                        <small class="text-muted d-block"><i class="fas fa-phone mr-1"></i>{{ $order->buyer_phone }}</small>
                    </td>
                    <td class="align-middle font-weight-bold text-dark">
                        ৳{{ number_format((float) $order->grand_total, 2) }}
                    </td>
                    <td class="align-middle">
                        {!! $order->payment_status_badge !!}
                    </td>
                    <td class="align-middle">
                        {!! $order->order_status_badge !!}
                    </td>
                    <td class="align-middle">
                        @if($order->assignedAgent)
                            <span class="badge badge-light border"><i class="fas fa-user-tie text-secondary mr-1"></i>{{ $order->assignedAgent->name }}</span>
                        @else
                            <span class="text-muted small"><i class="far fa-user mr-1"></i>Unassigned</span>
                        @endif
                    </td>
                    <td class="align-middle text-nowrap">
                        @if($isTrash)
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($order->deleted_at)->format('d M Y, h:i A') }}</small>
                        @else
                            <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i>{{ optional($order->created_at)->format('d M Y, h:i A') }}</small>
                        @endif
                    </td>
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('orders.restore')
                                <button type="button" class="btn btn-outline-success btn-sm btn-action"
                                    data-url="{{ route('admin.orders.restore', $order->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Order?"
                                    data-confirm-text="Order #{{ $order->order_number }} will be restored to active list."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                            @endcan
                            @can('orders.force-delete')
                                <button type="button" class="btn btn-outline-danger btn-sm btn-action"
                                    data-url="{{ route('admin.orders.force-delete', $order->id) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Permanently Delete?"
                                    data-confirm-text="Order #{{ $order->order_number }} will be deleted forever."
                                    title="Permanent Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('orders.view')
                                    <button type="button" class="btn btn-default btn-view-order" data-id="{{ $order->id }}" title="View Order">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('orders.update')
                                    <button type="button" class="btn btn-default btn-edit-order" data-id="{{ $order->id }}" title="Edit / Update Status">
                                        <i class="fas fa-pen text-primary"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-action"
                                        data-url="{{ route('admin.orders.destroy', $order) }}"
                                        data-method="DELETE"
                                        data-confirm-title="Move to Trash?"
                                        data-confirm-text="Order #{{ $order->order_number }} will be moved to trash bin."
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
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-box-open fa-3x text-light mb-3 d-block"></i>
                        {{ $isTrash ? 'No trashed orders found.' : 'No orders found matching criteria.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>