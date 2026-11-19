@extends('vendor-portal.layouts.app')

@section('title', 'Orders')
@section('breadcrumb_label', 'Orders')
@section('page_heading', 'Store orders')
@section('page_description', 'Manage fulfilment for orders that include products from your store.')

@section('content')
    <section class="vp-panel">
        <div class="vp-toolbar vp-toolbar-stack">
            <div class="vp-filter-tabs">
                <a href="{{ route('vendor.orders.index') }}" class="{{ request('status') ? '' : 'is-active' }}">All</a>
                @foreach(['awaiting_payment' => 'Awaiting payment', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'ready_to_ship' => 'Ready to ship', 'shipped' => 'Shipped', 'delivered' => 'Delivered'] as $status => $label)
                    <a href="{{ route('vendor.orders.index', ['status' => $status]) }}" class="{{ request('status') === $status ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <form class="vp-search-form" method="GET" action="{{ route('vendor.orders.index') }}">
                <i class="fas fa-search"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Search order number" aria-label="Search orders"><button type="submit">Search</button>
            </form>
        </div>
        <div class="vp-table-wrap">
            <table class="vp-table">
                <thead><tr><th>Seller order</th><th>Customer</th><th>Items</th><th>Fulfilment</th><th>Payment</th><th>Total</th><th>Date</th><th class="vp-th-action">Details</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><strong>{{ $order->suborder_number }}</strong><small class="vp-cell-sub">{{ $order->order?->order_number }}</small></td>
                            <td>{{ $order->order?->buyer_name ?? 'Customer' }}</td>
                            <td>{{ $order->items_count }}</td>
                            <td><span class="vp-status-pill is-info">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span></td>
                            <td><span class="vp-status-pill {{ $order->payment_status === 'verified' ? 'is-success' : 'is-muted' }}">{{ ucfirst($order->payment_status) }}</span></td>
                            <td><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $order->grand_total, 2) }}</strong></td>
                            <td>{{ $order->created_at?->format('d M Y') }}</td>
                            <td class="vp-th-action"><a class="vp-icon-button" href="{{ route('vendor.orders.show', $order) }}" aria-label="View order"><i class="fas fa-arrow-right"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="vp-empty-state"><span class="vp-empty-icon"><i class="fas fa-shopping-bag"></i></span><strong>No store orders yet</strong><p>Orders containing your products will appear here after customers check out.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())<div class="vp-pagination">{{ $orders->links() }}</div>@endif
    </section>
@endsection
