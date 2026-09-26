@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-list' : 'methods-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-list' : 'methods-list' }}">
                </th>
                <th>Method</th>
                <th>Account Details</th>
                <th>Type</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Status</th>
                @endif
                <th width="150" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($methods as $method)
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="method_ids[]" value="{{ $method->id }}" data-row-checkbox>
                    </td>
                    <td>
                        <div class="font-weight-600 text-dark">{{ $method->name }}</div>
                        <code class="badge badge-light border text-uppercase">{{ $method->code }}</code>
                    </td>
                    <td class="align-middle">
                        <span class="font-weight-bold">{{ $method->account_number }}</span>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-light border text-capitalize">{{ $method->account_type }}</span>
                    </td>
                    <td class="align-middle">
                        @if($isTrash)
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($method->deleted_at)->format('d M Y, h:i A') }}</small>
                        @else
                            @if($method->status === 'active')
                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Active</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1"><i class="fas fa-ban mr-1"></i>Inactive</span>
                            @endif
                        @endif
                    </td>
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('payment-methods.restore')
                                <button type="button" class="btn btn-outline-success btn-sm btn-action" 
                                    data-url="{{ route('admin.payment-methods.restore', $method->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Payment Method?"
                                    data-confirm-text="Method '{{ $method->name }}' will be restored to active list."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                            @endcan
                            @can('payment-methods.force-delete')
                                <button type="button" class="btn btn-outline-danger btn-sm btn-action"
                                    data-url="{{ route('admin.payment-methods.force-delete', $method->id) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Permanently Delete?"
                                    data-confirm-text="Payment method '{{ $method->name }}' will be deleted forever."
                                    title="Permanent Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('payment-methods.view')
                                    <button type="button" class="btn btn-default btn-view-method" data-id="{{ $method->id }}" title="View Method">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('payment-methods.manage')
                                    <button type="button" class="btn btn-default btn-edit-method" data-id="{{ $method->id }}" title="Edit Method">
                                        <i class="fas fa-pen text-primary"></i>
                                    </button>
                                @endcan
                                @can('payment-methods.delete')
                                    <button type="button" class="btn btn-default btn-action"
                                        data-url="{{ route('admin.payment-methods.destroy', $method) }}"
                                        data-method="DELETE"
                                        data-confirm-title="Move to Trash?"
                                        data-confirm-text="Payment method '{{ $method->name }}' will be moved to trash bin."
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
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x text-light mb-3 d-block"></i>
                        {{ $isTrash ? 'Trash bin is completely empty.' : 'No payment methods found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>