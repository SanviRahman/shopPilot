@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-list' : 'payments-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-list' : 'payments-list' }}">
                </th>
                <th>Order Number</th>
                <th>Method</th>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th>Status</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Verifier / Date</th>
                @endif
                <th width="160" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}" data-row-checkbox>
                    </td>
                    <td class="align-middle font-weight-600">
                        <a href="#" class="text-dark">{{ $payment->order?->order_number ?? 'N/A' }}</a>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-light border text-uppercase">{{ $payment->paymentMethod?->name ?? 'N/A' }}</span>
                    </td>
                    <td class="align-middle font-monospace text-primary">
                        {{ $payment->transaction_id }}
                    </td>
                    <td class="align-middle font-weight-bold">
                        ৳{{ number_format($payment->amount, 2) }}
                    </td>
                    <td class="align-middle">
                        @if($payment->status === 'verified')
                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Verified</span>
                        @elseif($payment->status === 'rejected')
                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Rejected</span>
                        @else
                            <span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-clock mr-1"></i>Submitted</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        @if($isTrash)
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($payment->deleted_at)->format('d M Y, h:i A') }}</small>
                        @else
                            <small class="text-muted">
                                {{ $payment->verifier?->name ?? 'Pending' }}<br>
                                {{ optional($payment->verified_at)->format('d M Y, h:i A') }}
                            </small>
                        @endif
                    </td>
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('payments.verify')
                                <button type="button" class="btn btn-outline-success btn-sm btn-action" 
                                    data-url="{{ route('admin.payments.restore', $payment->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Payment?"
                                    data-confirm-text="Payment submission for order '{{ $payment->order?->order_number }}' will be restored."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-action"
                                    data-url="{{ route('admin.payments.force-delete', $payment->id) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Permanently Delete?"
                                    data-confirm-text="This payment record will be deleted forever."
                                    title="Permanent Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('payments.view')
                                    <button type="button" class="btn btn-default btn-view-payment" data-id="{{ $payment->id }}" title="View Details">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('payments.verify')
                                    @if($payment->status === 'submitted')
                                        <button type="button" class="btn btn-default btn-verify-payment text-success" data-url="{{ route('admin.payments.verify', $payment->id) }}" title="Verify Payment">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-default btn-reject-payment text-danger" data-url="{{ route('admin.payments.reject', $payment->id) }}" title="Reject Payment">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-default btn-action"
                                        data-url="{{ route('admin.payments.destroy', $payment->id) }}"
                                        data-method="DELETE"
                                        data-confirm-title="Move to Trash?"
                                        data-confirm-text="Move payment submission to trash bin?"
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
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="fas fa-wallet fa-3x text-light mb-3 d-block"></i>
                        {{ $isTrash ? 'Trash bin is completely empty.' : 'No payment submissions found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>