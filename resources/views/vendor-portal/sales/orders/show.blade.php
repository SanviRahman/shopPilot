@extends('vendor-portal.layouts.app')

@section('title', 'Order details')
@section('breadcrumb_label', 'Order details')
@section('page_heading', 'Order details')
@section('page_description', 'Review customer information, payment and fulfilment for this seller order.')

@section('page_actions')
    <a href="{{ route('vendor.orders.index') }}" class="vp-button vp-button-light"><i class="fas fa-arrow-left"></i><span>Back to orders</span></a>
@endsection

@section('content')
    <div class="vp-order-detail-grid">
        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">SELLER ORDER</span><h2>{{ $vendorOrder->suborder_number }}</h2><p>Master order: {{ $vendorOrder->order?->order_number }} · {{ $vendorOrder->created_at?->format('d M Y, h:i A') }}</p></div><span class="vp-status-pill is-info">{{ str_replace('_', ' ', ucfirst($vendorOrder->status)) }}</span></div>
            <div class="vp-detail-list">
                <div><span>Customer</span><strong>{{ $vendorOrder->order?->buyer_name }}</strong></div>
                <div><span>Contact</span><strong>{{ $vendorOrder->order?->buyer_phone }}</strong></div>
                <div><span>Delivery address</span><strong>{{ $vendorOrder->order?->shipping_address }}, {{ $vendorOrder->order?->city_or_area }}</strong></div>
                <div><span>Payment</span><strong>{{ ucfirst($vendorOrder->payment_status) }}</strong></div>
                <div><span>Seller earning</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $vendorOrder->vendor_earning, 2) }}</strong></div>
            </div>
        </section>

        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">FULFILMENT</span><h2>Update order progress</h2><p>Payment verification is managed by marketplace administration.</p></div></div>
            @if($vendorOrder->payment_status !== 'verified')
                <div class="vp-alert vp-alert-warning">Fulfilment actions become available after payment verification.</div>
            @elseif(in_array($vendorOrder->status, ['confirmed', 'processing', 'ready_to_ship'], true))
                <form method="POST" action="{{ route('vendor.orders.status', $vendorOrder) }}" class="vp-form-grid">
                    @csrf @method('PATCH')
                    @if($vendorOrder->status === 'confirmed')
                        <input type="hidden" name="status" value="processing">
                        <div class="vp-field vp-field-full"><span>Next step</span><strong>Start preparing this order.</strong></div>
                    @elseif($vendorOrder->status === 'processing')
                        <input type="hidden" name="status" value="ready_to_ship">
                        <div class="vp-field vp-field-full"><span>Next step</span><strong>Mark the order as packed and ready to ship.</strong></div>
                    @else
                        <input type="hidden" name="status" value="shipped">
                        <label class="vp-field"><span>Courier / delivery provider</span><input name="shipping_provider" maxlength="100" required></label>
                        <label class="vp-field"><span>Tracking number</span><input name="tracking_number" maxlength="120" required></label>
                        <label class="vp-field vp-field-full"><span>Tracking URL (optional)</span><input type="url" name="tracking_url" maxlength="500" placeholder="https://"></label>
                    @endif
                    <div class="vp-form-actions vp-field-full"><button type="submit" class="vp-button vp-button-primary">{{ $vendorOrder->status === 'ready_to_ship' ? 'Mark as shipped' : 'Update fulfilment' }}</button></div>
                </form>
            @else
                <div class="vp-detail-list">
                    <div><span>Courier</span><strong>{{ $vendorOrder->shipping_provider ?? '—' }}</strong></div>
                    <div><span>Tracking number</span><strong>{{ $vendorOrder->tracking_number ?? '—' }}</strong></div>
                    <div><span>Shipped at</span><strong>{{ $vendorOrder->shipped_at?->format('d M Y, h:i A') ?? '—' }}</strong></div>
                    <div><span>Delivered at</span><strong>{{ $vendorOrder->delivered_at?->format('d M Y, h:i A') ?? 'Awaiting confirmation' }}</strong></div>
                    @if($vendorOrder->tracking_url)<div><span>Tracking</span><strong><a href="{{ $vendorOrder->tracking_url }}" target="_blank" rel="noopener">Open tracking</a></strong></div>@endif
                </div>
            @endif
        </section>
    </div>

    <section class="vp-panel">
        <div class="vp-panel-heading"><div><span class="vp-section-kicker">ORDER CONTENTS</span><h2>Items ({{ $vendorOrder->items->sum('quantity') }})</h2></div></div>
        <div class="vp-table-wrap">
            <table class="vp-table">
                <thead><tr><th>Product</th><th>SKU</th><th>Options</th><th>Quantity</th><th>Unit price</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach($vendorOrder->items as $item)
                        <tr><td><strong>{{ $item->product_name }}</strong></td><td class="vp-mono">{{ $item->sku }}</td><td>{{ $item->variant_name ?? '—' }}</td><td>{{ $item->quantity }}</td><td>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $item->unit_price, 2) }}</td><td><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $item->line_total, 2) }}</strong></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="vp-detail-list">
            <div><span>Subtotal</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $vendorOrder->subtotal, 2) }}</strong></div>
            <div><span>Discount</span><strong>-{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $vendorOrder->discount, 2) }}</strong></div>
            <div><span>Shipping</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $vendorOrder->shipping_fee, 2) }}</strong></div>
            <div><span>Commission ({{ number_format((float) $vendorOrder->commission_rate, 2) }}%)</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $vendorOrder->commission_amount, 2) }}</strong></div>
            <div><span>Your earning</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $vendorOrder->vendor_earning, 2) }}</strong></div>
        </div>
    </section>

    @if($vendorOrder->histories->isNotEmpty())
        <section class="vp-panel">
            <div class="vp-panel-heading"><div><span class="vp-section-kicker">ORDER ACTIVITY</span><h2>Fulfilment timeline</h2></div></div>
            <div class="vp-detail-list">
                @foreach($vendorOrder->histories as $history)
                    <div><span>{{ $history->created_at?->format('d M Y, h:i A') }}</span><strong>{{ $history->note }}</strong></div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
