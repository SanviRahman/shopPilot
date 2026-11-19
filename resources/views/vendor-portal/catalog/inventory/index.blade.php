@extends('vendor-portal.layouts.app')

@section('title', 'Inventory')
@section('breadcrumb_label', 'Inventory')
@section('page_heading', 'Inventory')
@section('page_description', 'Keep stock accurate across your simple products and every variation.')

@section('content')
    <section class="vp-stat-grid vp-stat-grid-three">
        <article class="vp-stat-card"><span class="vp-stat-icon is-blue"><i class="fas fa-cubes"></i></span><div><span class="vp-stat-label">Tracked items</span><strong>{{ number_format($stats['tracked_items']) }}</strong><small>Products and variations</small></div></article>
        <article class="vp-stat-card"><span class="vp-stat-icon is-orange"><i class="fas fa-exclamation-triangle"></i></span><div><span class="vp-stat-label">Low stock</span><strong>{{ number_format($stats['low_stock']) }}</strong><small>Items to review</small></div></article>
        <article class="vp-stat-card"><span class="vp-stat-icon is-red"><i class="fas fa-ban"></i></span><div><span class="vp-stat-label">Out of stock</span><strong>{{ number_format($stats['out_of_stock']) }}</strong><small>Currently unavailable</small></div></article>
    </section>

    <section class="vp-panel">
        <div class="vp-toolbar">
            <div><span class="vp-section-kicker">STOCK OVERVIEW</span><h2 class="vp-toolbar-title">Your stock levels</h2></div>
            <form class="vp-search-form" method="GET" action="{{ route('vendor.inventory.index') }}">
                <i class="fas fa-search"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Search product or SKU" aria-label="Search inventory"><button type="submit">Search</button>
            </form>
        </div>
        <div class="vp-table-wrap">
            <table class="vp-table vp-inventory-table">
                <thead><tr><th>Product / Variation</th><th>SKU</th><th>On hand</th><th>Reserved</th><th>Available</th><th>Stock status</th><th>Adjust stock</th></tr></thead>
                <tbody>
                    @forelse($inventoryRows as $row)
                        <tr>
                            <td><strong>{{ $row['product_name'] }}</strong><small class="vp-cell-sub">{{ $row['variant_name'] ?: 'Simple product' }}</small></td>
                            <td class="vp-mono">{{ $row['sku'] }}</td>
                            <td>{{ number_format($row['on_hand_quantity']) }}</td>
                            <td>{{ number_format($row['reserved_quantity']) }}</td>
                            <td><strong>{{ number_format($row['available_quantity']) }}</strong></td>
                            <td><span class="vp-status-pill {{ $row['available_quantity'] > 0 ? 'is-success' : 'is-danger' }}">{{ $row['available_quantity'] > 0 ? 'In stock' : 'Out of stock' }}</span></td>
                            <td>
                                <form class="vp-stock-adjust-form" method="POST" action="{{ $row['variant_id'] ? route('vendor.inventory.variants.update', $row['variant_id']) : route('vendor.inventory.products.update', $row['product_id']) }}">
                                    @csrf @method('PATCH')
                                    <input type="number" name="quantity" min="0" step="1" value="{{ $row['on_hand_quantity'] }}" aria-label="New on hand quantity for {{ $row['sku'] }}" required>
                                    <button class="vp-button vp-button-light vp-button-compact" type="submit">Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="vp-empty-state"><span class="vp-empty-icon"><i class="fas fa-layer-group"></i></span><strong>Your inventory will show here</strong><p>Add products and variations to start managing stock in one place.</p><a class="vp-button vp-button-primary" href="{{ route('vendor.products.create') }}">Add a product</a></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
