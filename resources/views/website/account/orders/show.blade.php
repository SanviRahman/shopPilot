@extends('website.layouts.app')
@section('title', 'Order Details | ShopPilot')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/website/css/account.css') }}">
<style>
    .seller-order-card {
        border: 1px solid #d9e4f2;
        border-radius: 24px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        padding: 24px;
        margin-bottom: 18px;
    }

    .seller-order-card:last-child {
        margin-bottom: 0;
    }

    .seller-order-card__head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .seller-order-card__store {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .seller-order-card__store strong {
        font-size: 1.05rem;
        color: #10213d;
    }

    .seller-order-card__store small {
        color: #71829e;
        font-size: 0.88rem;
    }

    .seller-order-card__meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .seller-order-meta-box {
        border: 1px solid #e4ebf5;
        border-radius: 16px;
        padding: 12px 14px;
        background: #fff;
    }

    .seller-order-meta-box span {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7d8da8;
        margin-bottom: 6px;
    }

    .seller-order-meta-box strong,
    .seller-order-meta-box p {
        margin: 0;
        color: #10213d;
        font-size: 0.95rem;
    }

    .seller-order-meta-box p a {
        margin-left: 8px;
        font-weight: 700;
        color: #1463ff;
        text-decoration: none;
    }

    .refund-status-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0 0 16px;
    }

    .refund-status-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #f6f9ff;
        border: 1px solid #dce8ff;
        color: #21406e;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .refund-request-card {
        border: 1px solid #dbe6f5;
        border-radius: 20px;
        background: #fff;
        padding: 20px;
    }

    .refund-request-card__head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 18px;
    }

    .refund-request-card__head h3 {
        margin: 0 0 6px;
        font-size: 1.05rem;
        color: #10213d;
    }

    .refund-request-card__head p {
        margin: 0;
        color: #71829e;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .refund-eligibility-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #ebfff3;
        color: #0d8a4c;
        font-size: 0.78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .refund-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .refund-form-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .refund-form-field--full {
        grid-column: 1 / -1;
    }

    .refund-form-field label {
        font-size: 0.82rem;
        font-weight: 800;
        color: #3d4f6c;
        letter-spacing: 0.02em;
    }

    .refund-form-field select,
    .refund-form-field input,
    .refund-form-field textarea {
        width: 100%;
        border: 1px solid #d2deef;
        border-radius: 14px;
        background: #fbfdff;
        min-height: 48px;
        padding: 12px 14px;
        color: #10213d;
        font-size: 0.95rem;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .refund-form-field textarea {
        min-height: 108px;
        resize: vertical;
    }

    .refund-form-field select:focus,
    .refund-form-field input:focus,
    .refund-form-field textarea:focus {
        border-color: #1463ff;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(20, 99, 255, 0.10);
    }

    .refund-form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .refund-submit-button {
        border: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, #1463ff 0%, #0a49c9 100%);
        color: #fff;
        font-weight: 800;
        font-size: 0.92rem;
        padding: 13px 20px;
        box-shadow: 0 16px 30px rgba(20, 99, 255, 0.22);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .refund-submit-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 20px 34px rgba(20, 99, 255, 0.28);
    }

    @media (max-width: 767.98px) {
        .seller-order-card,
        .refund-request-card {
            padding: 16px;
            border-radius: 18px;
        }

        .seller-order-card__head,
        .refund-request-card__head {
            flex-direction: column;
            align-items: flex-start;
        }

        .seller-order-card__meta,
        .refund-form-grid {
            grid-template-columns: 1fr;
        }

        .refund-form-actions {
            justify-content: stretch;
        }

        .refund-submit-button {
            width: 100%;
        }
    }
</style>
@endpush
@section('content')
<section class="account-page"><div class="container">
    <div class="account-breadcrumb"><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.dashboard') }}">My Account</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.account.orders') }}">My Orders</a><i class="fas fa-chevron-right"></i><strong>Order Details</strong></div>
    <div class="account-layout">@include('website.account.partials.sidebar')<div class="account-main">
        <header class="account-page-heading order-detail-heading" data-account-reveal><div><span>ORDER DETAILS</span><h1>#{{ $order->order_number }}</h1><p>Placed on {{ $order->created_at?->format('M d, Y \a\t h:i A') }}</p></div><span class="status-pill status-{{ $order->order_status }}">{{ ucfirst($order->order_status) }}</span></header>

        <div class="order-detail-top-grid">
            <section class="account-panel" data-account-reveal><div class="panel-heading"><div><span>SUMMARY</span><h2>Order Information</h2></div></div>
                <dl class="detail-list">
                    <div><dt>Order Number</dt><dd>#{{ $order->order_number }}</dd></div>
                    <div><dt>Order Date</dt><dd>{{ $order->created_at?->format('M d, Y h:i A') }}</dd></div>
                    <div><dt>Order Status</dt><dd><span class="status-pill status-{{ $order->order_status }}">{{ ucfirst($order->order_status) }}</span></dd></div>
                    <div><dt>Payment Status</dt><dd><span class="payment-pill payment-{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span></dd></div>
                    <div><dt>Total Amount</dt><dd><strong class="money-highlight">{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $order->grand_total, 0) }}</strong></dd></div>
                </dl>
            </section>
            <section class="account-panel" data-account-reveal><div class="panel-heading"><div><span>DELIVERY</span><h2>Delivery Address</h2></div></div>
                <div class="delivery-card"><i class="fas fa-map-marker-alt"></i><div><strong>{{ $order->buyer_name }}</strong><span>{{ $order->buyer_phone }}</span><span>{{ $order->shipping_address }}</span><span>{{ $order->city_or_area }}</span></div><b>Home</b></div>
            </section>
        </div>

        <section class="account-panel payment-highlight-panel" data-account-reveal>
            <div class="panel-heading"><div><span>PAYMENT</span><h2>Payment Information</h2></div>
                @if(in_array($order->payment_status, [\App\Models\Order::PAYMENT_UNPAID, \App\Models\Order::PAYMENT_REJECTED], true))<a href="{{ route('website.account.payments', ['order' => $order->id]) }}">Submit Payment</a>@endif
            </div>
            @if($order->paymentSubmission)
                <div class="payment-detail-grid">
                    <div><span>Method</span><strong>{{ $order->paymentSubmission->paymentMethod?->name ?? 'Manual Payment' }}</strong></div>
                    <div><span>Transaction ID</span><strong>{{ $order->paymentSubmission->transaction_id }}</strong></div>
                    <div><span>Amount</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $order->paymentSubmission->amount, 0) }}</strong></div>
                    <div><span>Status</span><strong class="payment-pill payment-{{ $order->paymentSubmission->status }}">{{ ucfirst($order->paymentSubmission->status) }}</strong></div>
                </div>
                @if($order->paymentSubmission->rejection_note)<div class="payment-rejection-note"><i class="fas fa-exclamation-triangle"></i><div><strong>Payment was rejected</strong><p>{{ $order->paymentSubmission->rejection_note }}</p></div></div>@endif
            @else
                <div class="account-empty-mini">No payment submission has been recorded for this order yet.</div>
            @endif
        </section>

        <section class="account-panel" data-account-reveal>
            <div class="panel-heading"><div><span>ITEMS</span><h2>Order Items ({{ $order->items->sum('quantity') }})</h2></div></div>
            <div class="order-item-table">
                <div class="order-item-table-head"><span>Product</span><span>Price</span><span>Quantity</span><span>Total</span></div>
                @foreach($order->items as $item)
                    @php $image = $item->variant?->image_url ?: ($item->product?->thumbnail_url ?: asset('assets/website/images/product-placeholder.svg')); @endphp
                    <div class="order-item-row"><div class="order-item-product"><img src="{{ $image }}" alt="{{ $item->product_name }}"><div><strong>{{ $item->product_name }}</strong><span>SKU: {{ $item->sku }}</span>@if($item->variant_name)<small>{{ $item->variant_name }}</small>@endif</div></div><span>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $item->unit_price, 0) }}</span><span>{{ $item->quantity }}</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $item->line_total, 0) }}</strong></div>
                @endforeach
            </div>
            <div class="order-total-box"><div><span>Subtotal</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $order->subtotal, 0) }}</strong></div><div><span>Discount</span><strong>-{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $order->discount, 0) }}</strong></div><div><span>Shipping</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $order->shipping, 0) }}</strong></div><div class="grand"><span>Total Amount</span><strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $order->grand_total, 0) }}</strong></div></div>
        </section>

        @if($order->vendorOrders->isNotEmpty())
        <section class="account-panel" data-account-reveal>
            <div class="panel-heading"><div><span>SELLER ORDERS</span><h2>Delivery by seller</h2></div></div>
            @foreach($order->vendorOrders as $vendorOrder)
                <div class="seller-order-card">
                    <div class="seller-order-card__head">
                        <div class="seller-order-card__store">
                            <strong>{{ $vendorOrder->vendor?->store?->name ?? 'ShopPilot Store' }}</strong>
                            <small>{{ $vendorOrder->suborder_number }}</small>
                        </div>
                        <span class="status-pill status-{{ $vendorOrder->status }}">{{ str_replace('_', ' ', ucfirst($vendorOrder->status)) }}</span>
                    </div>

                    <div class="seller-order-card__meta">
                        <div class="seller-order-meta-box">
                            <span>Payment Status</span>
                            <strong>{{ ucfirst($vendorOrder->payment_status) }}</strong>
                        </div>
                        <div class="seller-order-meta-box">
                            <span>Seller Total</span>
                            <strong>{{ config('shop.marketplace.currency_symbol') }}{{ number_format((float) $vendorOrder->grand_total, 2) }}</strong>
                        </div>
                        @if($vendorOrder->tracking_number)
                            <div class="seller-order-meta-box">
                                <span>Courier</span>
                                <p>{{ $vendorOrder->shipping_provider ?: 'Courier details available' }}</p>
                            </div>
                            <div class="seller-order-meta-box">
                                <span>Tracking</span>
                                <p><strong>{{ $vendorOrder->tracking_number }}</strong>@if($vendorOrder->tracking_url)<a href="{{ $vendorOrder->tracking_url }}" target="_blank" rel="noopener">Track parcel</a>@endif</p>
                            </div>
                        @endif
                    </div>

                    @if($vendorOrder->refunds->isNotEmpty())
                        <div class="refund-status-list">
                            @foreach($vendorOrder->refunds as $refund)
                                <span class="refund-status-tag">Refund #{{ $refund->id }} · {{ ucfirst($refund->status) }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($vendorOrder->status === 'delivered')
                        <form method="POST" action="{{ route('website.account.orders.refunds.store', [$order->order_number, $vendorOrder]) }}" class="refund-request-card">
                            @csrf
                            <div class="refund-request-card__head">
                                <div>
                                    <h3>Request return / refund</h3>
                                    <p>Select the delivered item, choose the quantity, and explain the issue clearly so the seller can review your request faster.</p>
                                </div>
                                <span class="refund-eligibility-badge"><i class="fas fa-check-circle"></i> Delivered order eligible</span>
                            </div>

                            <div class="refund-form-grid">
                                <div class="refund-form-field">
                                    <label for="refund-item-{{ $vendorOrder->id }}">Item to return</label>
                                    <select id="refund-item-{{ $vendorOrder->id }}" name="items[0][order_item_id]" required>
                                        <option value="">Choose item</option>
                                        @foreach($vendorOrder->items as $vendorItem)
                                            <option value="{{ $vendorItem->id }}">{{ $vendorItem->product_name }} · {{ $vendorItem->variant_name ?? 'Standard' }} ({{ $vendorItem->quantity }} purchased)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="refund-form-field">
                                    <label for="refund-qty-{{ $vendorOrder->id }}">Quantity</label>
                                    <input id="refund-qty-{{ $vendorOrder->id }}" type="number" name="items[0][quantity]" min="1" value="1" required>
                                </div>
                                <div class="refund-form-field refund-form-field--full">
                                    <label for="refund-reason-{{ $vendorOrder->id }}">Reason</label>
                                    <textarea id="refund-reason-{{ $vendorOrder->id }}" name="reason" maxlength="2000" placeholder="Tell us what went wrong" required></textarea>
                                </div>
                            </div>

                            <div class="refund-form-actions">
                                <button type="submit" class="refund-submit-button">Request return / refund</button>
                            </div>
                        </form>
                    @endif
                </div>
            @endforeach
        </section>
        @endif

        @if($order->histories->isNotEmpty())
        <section class="account-panel" data-account-reveal><div class="panel-heading"><div><span>TIMELINE</span><h2>Order Activity</h2></div></div><div class="order-timeline">
            @foreach($order->histories as $history)<div class="timeline-row"><span class="timeline-dot"></span><div><strong>{{ $history->to_status ? ucfirst($history->to_status) : 'Update' }}</strong><p>{{ $history->note ?: 'Order information updated.' }}</p><small>{{ $history->created_at?->format('M d, Y h:i A') }}</small></div></div>@endforeach
        </div></section>
        @endif

        <div class="account-bottom-actions">
            <a href="{{ route('website.account.orders') }}" class="secondary-account-button"><i class="fas fa-arrow-left"></i> Back to Orders</a>
            <a href="{{ route('website.track-order', ['order_number' => $order->order_number]) }}" class="secondary-account-button"><i class="fas fa-search-location"></i> Track Order</a>
            @if(in_array($order->payment_status, [\App\Models\Order::PAYMENT_UNPAID, \App\Models\Order::PAYMENT_REJECTED], true))<a href="{{ route('website.account.payments', ['order' => $order->id]) }}" class="primary-account-button">Submit Payment <i class="fas fa-arrow-right"></i></a>@endif
        </div>
    </div></div>
</div></section>
@endsection
@push('scripts')<script src="{{ asset('assets/website/js/account.js') }}" defer></script>@endpush
