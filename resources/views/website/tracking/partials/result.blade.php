@php
    $statusClass = 'status-' . $order->order_status;
    $paymentMethod = $order->paymentSubmission?->paymentMethod;
@endphp
<section class="track-result" data-track-result data-track-reveal>
    <div class="track-result-heading">
        <div><span>ORDER TRACKING RESULT</span><h2>Order #{{ $order->order_number }}</h2></div>
        <div class="track-result-meta"><span class="status-pill {{ $statusClass }}">{{ ucfirst($order->order_status) }}</span><small>Order Date: {{ $order->created_at?->format('M d, Y') }}</small><button type="button" data-track-print><i class="fas fa-print"></i> Print</button></div>
    </div>

    <div class="track-result-grid">
        <section class="track-panel track-timeline-panel">
            <div class="track-panel-title"><div><span><i class="fas fa-route"></i></span><div><h3>Delivery Timeline</h3><p>Estimated delivery updates from your order history.</p></div></div></div>
            <div class="track-timeline">
                @foreach($timeline as $step)
                    <article class="track-timeline-step {{ $step['completed'] ? 'completed' : '' }} {{ $step['current'] ? 'current' : '' }} {{ !empty($step['danger']) ? 'danger' : '' }}">
                        <span class="timeline-dot"><i class="fas {{ $step['icon'] }}"></i></span>
                        <div><strong>{{ $step['label'] }}</strong><p>{{ $step['description'] }}</p>@if($step['date'])<small>{{ $step['date']->format('M d, Y · h:i A') }}</small>@else<small>Waiting for update</small>@endif</div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="track-panel track-summary-panel">
            <div class="track-panel-title"><div><span><i class="fas fa-box-open"></i></span><div><h3>Order Summary</h3><p>{{ $order->items->sum('quantity') }} item(s) in this order</p></div></div></div>
            <div class="track-order-items">
                @foreach($order->items as $item)
                    @php $image = $item->product?->thumbnail_url ?: asset('assets/website/images/product-placeholder.svg'); @endphp
                    <article><img src="{{ $image }}" alt="{{ $item->product_name }}"><div><strong>{{ $item->product_name }}</strong><small>Qty: {{ $item->quantity }}</small></div><b>৳{{ number_format((float) $item->line_total, 0) }}</b></article>
                @endforeach
            </div>
            <div class="track-totals"><div><span>Subtotal</span><strong>৳{{ number_format((float) $order->subtotal, 0) }}</strong></div><div><span>Discount</span><strong class="discount">-৳{{ number_format((float) $order->discount, 0) }}</strong></div><div><span>Shipping Fee</span><strong>৳{{ number_format((float) $order->shipping, 0) }}</strong></div><div class="total"><span>Total Amount</span><strong>৳{{ number_format((float) $order->grand_total, 0) }}</strong></div></div>
        </section>

        <aside class="track-side-stack">
            <section class="track-panel track-info-card">
                <div class="track-panel-title compact"><div><span><i class="fas fa-map-marker-alt"></i></span><div><h3>Delivery Information</h3></div></div></div>
                <ul><li><i class="far fa-user"></i><span>{{ $order->buyer_name }}</span></li><li><i class="fas fa-phone-alt"></i><span>{{ $order->buyer_phone }}</span></li><li><i class="far fa-envelope"></i><span>{{ $order->buyer_email }}</span></li><li><i class="fas fa-map-marker-alt"></i><span>{{ $order->shipping_address }}@if($order->city_or_area), {{ $order->city_or_area }}@endif</span></li></ul>
            </section>

            <section class="track-panel track-info-card payment">
                <div class="track-panel-title compact"><div><span><i class="fas fa-wallet"></i></span><div><h3>Payment Information</h3></div></div></div>
                <div class="track-payment-method"><span><i class="fas fa-money-check-alt"></i></span><div><strong>{{ $paymentMethod?->name ?? 'Payment' }}</strong><small>{{ $order->paymentSubmission?->transaction_id ? 'Transaction: ' . $order->paymentSubmission->transaction_id : 'Transaction not submitted' }}</small></div></div>
                <div class="track-payment-status"><span>Payment Status</span><b class="payment-pill payment-{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</b></div>
            </section>
        </aside>
    </div>
</section>
