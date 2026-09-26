@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-items-list' : 'order-items-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-items-list' : 'order-items-list' }}">
                </th>
                <th>Order #</th>
                <th>Product & Variant</th>
                <th>SKU</th>
                <th>Unit Price</th>
                <th class="text-center">Qty</th>
                <th>Subtotal</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Added Date</th>
                @endif
                <th width="120" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orderItems as $item)
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" data-row-checkbox>
                    </td>
                    <td class="align-middle">
                        @if($item->order)
                            <a href="{{ route('admin.orders.index') }}?search={{ $item->order->order_number }}" class="font-weight-bold text-primary font-monospace">
                                {{ $item->order->order_number }}
                            </a>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        <div class="font-weight-600 text-dark">{{ $item->product_name }}</div>
                        @if($item->variant_name)
                            <small class="badge badge-light border text-secondary">{{ $item->variant_name }}</small>
                        @endif
                    </td>
                    <td class="align-middle">
                        <code>{{ $item->sku ?: '—' }}</code>
                    </td>
                    <td class="align-middle">
                        ৳{{ number_format((float) $item->unit_price, 2) }}
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge badge-secondary px-2 py-1">{{ $item->quantity }}</span>
                    </td>
                    <td class="align-middle font-weight-bold text-dark">
                        ৳{{ number_format((float) $item->subtotal, 2) }}
                    </td>
                    <td class="align-middle text-nowrap">
                        @if($isTrash)
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($item->deleted_at)->format('d M Y, h:i A') }}</small>
                        @else
                            <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i>{{ optional($item->created_at)->format('d M Y, h:i A') }}</small>
                        @endif
                    </td>
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('orders.restore')
                                <button type="button" class="btn btn-outline-success btn-sm btn-action"
                                    data-url="{{ route('admin.order-items.restore', $item->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Item?"
                                    data-confirm-text="Item '{{ $item->product_name }}' will be restored and parent order totals will update."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                            @endcan
                            @can('orders.force-delete')
                                <button type="button" class="btn btn-outline-danger btn-sm btn-action"
                                    data-url="{{ route('admin.order-items.force-delete', $item->id) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Permanently Delete?"
                                    data-confirm-text="Item '{{ $item->product_name }}' will be deleted forever."
                                    title="Permanent Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('orders.view')
                                    <button type="button" class="btn btn-default btn-view-item" data-id="{{ $item->id }}" title="View Item Details">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('orders.update')
                                    <button type="button" class="btn btn-default btn-edit-item" data-id="{{ $item->id }}" title="Edit Item">
                                        <i class="fas fa-pen text-primary"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-action"
                                        data-url="{{ route('admin.order-items.destroy', $item) }}"
                                        data-method="DELETE"
                                        data-confirm-title="Move to Trash?"
                                        data-confirm-text="Item '{{ $item->product_name }}' will be removed and order totals will adjust."
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
                        {{ $isTrash ? 'Trash bin is completely empty.' : 'No order items found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>