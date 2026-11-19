@extends('layouts.admin')

@section('meta_title', 'Seller Orders')

@section('page_content')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Seller fulfilment orders</h3><div class="card-tools"><form method="GET"><select name="status" class="form-control form-control-sm" onchange="this.form.submit()"><option value="">All statuses</option>@foreach(['awaiting_payment','confirmed','processing','ready_to_ship','shipped','delivered','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>@endforeach</select></form></div></div>
        <div class="card-body p-0 table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Seller order</th><th>Store</th><th>Customer</th><th>Payment</th><th>Fulfilment</th><th>Total</th><th>Action</th></tr></thead><tbody>
            @forelse($sellerOrders as $sellerOrder)
                <tr>
                    <td><strong>{{ $sellerOrder->suborder_number }}</strong><small class="d-block text-muted">{{ $sellerOrder->order?->order_number }}</small></td>
                    <td>{{ $sellerOrder->vendor?->store?->name ?? 'Marketplace' }}</td>
                    <td>{{ $sellerOrder->order?->buyer_name }}</td>
                    <td>{{ ucfirst($sellerOrder->payment_status) }}</td>
                    <td><span class="badge badge-info">{{ str_replace('_', ' ', ucfirst($sellerOrder->status)) }}</span></td>
                    <td>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $sellerOrder->grand_total, 2) }}</td>
                    <td>@if($sellerOrder->status === 'shipped')<form method="POST" action="{{ route('admin.marketplace-finance.seller-orders.delivered', $sellerOrder) }}">@csrf @method('PATCH')<button class="btn btn-xs btn-success">Confirm delivered</button></form>@else<span class="text-muted small">{{ $sellerOrder->delivered_at?->format('d M Y') ?? '—' }}</span>@endif</td>
                </tr>
            @empty<tr><td colspan="7" class="text-center text-muted py-4">No seller orders found.</td></tr>@endforelse
        </tbody></table></div>
        @if($sellerOrders->hasPages())<div class="card-footer bg-white">{{ $sellerOrders->links() }}</div>@endif
    </div>
@endsection
