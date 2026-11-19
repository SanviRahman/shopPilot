@extends('layouts.admin')

@section('meta_title', 'Marketplace Refunds')

@section('page_content')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Refund requests</h3><div class="card-tools"><form method="GET"><select name="status" class="form-control form-control-sm" onchange="this.form.submit()"><option value="">All statuses</option>@foreach(['requested','approved','rejected','succeeded'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></form></div></div>
        <div class="card-body p-0 table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Refund</th><th>Order / Seller</th><th>Customer</th><th>Items</th><th>Amount</th><th>Status</th><th style="min-width:280px">Action</th></tr></thead><tbody>
            @forelse($refunds as $refund)
                @php
                    $displayAmount = in_array($refund->status, ['approved', 'succeeded'], true)
                        ? $refund->approved_amount
                        : $refund->requested_amount;
                @endphp
                <tr>
                    <td>#{{ $refund->id }}<small class="d-block text-muted">{{ $refund->created_at?->format('d M Y') }}</small><small class="d-block text-muted">{{ $refund->reason }}</small></td>
                    <td>{{ $refund->order?->order_number }}<small class="d-block text-muted">{{ $refund->vendorOrder?->suborder_number }}</small><small class="d-block text-muted">{{ $refund->vendorOrder?->vendor?->business_name ?? 'Marketplace' }}</small></td>
                    <td>{{ $refund->order?->buyer_name }}</td>
                    <td>@foreach($refund->items as $item)<small class="d-block">{{ $item->orderItem?->product_name }} × {{ $item->quantity }}</small>@endforeach</td>
                    <td><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $displayAmount, 2) }}</strong></td>
                    <td><span class="badge badge-{{ $refund->status === 'succeeded' ? 'success' : ($refund->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($refund->status) }}</span></td>
                    <td>
                        @if($refund->status === 'requested')
                            <form method="POST" action="{{ route('admin.marketplace-finance.refunds.approve', $refund) }}" class="mb-2">@csrf @method('PATCH')<input name="admin_note" class="form-control form-control-sm mb-1" placeholder="Admin note (optional)"><button class="btn btn-xs btn-success">Approve requested amount</button></form>
                            <form method="POST" action="{{ route('admin.marketplace-finance.refunds.reject', $refund) }}">@csrf @method('PATCH')<input name="admin_note" class="form-control form-control-sm mb-1" placeholder="Reason for rejection" required><button class="btn btn-xs btn-outline-danger">Reject</button></form>
                        @elseif($refund->status === 'approved')
                            <form method="POST" action="{{ route('admin.marketplace-finance.refunds.complete', $refund) }}" class="mb-2">@csrf @method('PATCH')<input name="transfer_reference" class="form-control form-control-sm mb-1" placeholder="Refund transfer reference" required><label class="small mb-1"><input type="checkbox" name="restock_items" value="1"> Items received and inspected; restock</label><button class="btn btn-xs btn-primary">Confirm refund paid</button></form>
                            <form method="POST" action="{{ route('admin.marketplace-finance.refunds.reject', $refund) }}">@csrf @method('PATCH')<input name="admin_note" class="form-control form-control-sm mb-1" placeholder="Reason to cancel approval" required><button class="btn btn-xs btn-outline-danger">Reject approval</button></form>
                        @else
                            <small class="text-muted">{{ $refund->transfer_reference ?? $refund->admin_note ?? '—' }}</small>
                        @endif
                    </td>
                </tr>
            @empty<tr><td colspan="7" class="text-center text-muted py-4">No refund requests found.</td></tr>@endforelse
        </tbody></table></div>
        @if($refunds->hasPages())<div class="card-footer bg-white">{{ $refunds->links() }}</div>@endif
    </div>
@endsection
