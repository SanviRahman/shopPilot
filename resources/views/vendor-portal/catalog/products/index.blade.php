@extends('vendor-portal.layouts.app')

@section('title', 'Products')
@section('breadcrumb_label', 'Products')
@section('page_heading', 'Your products')
@section('page_description', 'Manage your catalogue, review status and product availability.')

@section('page_actions')
    <a class="vp-button vp-button-primary" href="{{ route('vendor.products.create') }}"><i class="fas fa-plus"></i><span>Add product</span></a>
@endsection

@section('content')
    <section class="vp-panel">
        <div class="vp-toolbar">
            <div class="vp-filter-tabs">
                <a href="{{ route('vendor.products.index') }}" class="{{ request('status') || request('moderation') ? '' : 'is-active' }}">All products</a>
                <a href="{{ route('vendor.products.index', ['status' => 'active']) }}" class="{{ request('status') === 'active' ? 'is-active' : '' }}">Published</a>
                <a href="{{ route('vendor.products.index', ['moderation' => 'draft']) }}" class="{{ request('moderation') === 'draft' ? 'is-active' : '' }}">Drafts</a>
                <a href="{{ route('vendor.products.index', ['moderation' => 'pending_review']) }}" class="{{ request('moderation') === 'pending_review' ? 'is-active' : '' }}">In review</a>
            </div>
            <form class="vp-search-form" method="GET" action="{{ route('vendor.products.index') }}">
                <i class="fas fa-search"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search your products" aria-label="Search products">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="vp-table-wrap">
            <table class="vp-table">
                <thead><tr><th>Product</th><th>Type</th><th>SKU</th><th>Price</th><th>Stock</th><th>Review</th><th>Availability</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="vp-product-cell">
                                    <span class="vp-product-thumb">
                                        @if($product->thumbnail_url)<img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">@else<i class="fas fa-box"></i>@endif
                                    </span>
                                    <span><strong>{{ $product->name }}</strong><small>{{ $product->category?->name ?? 'Uncategorised' }}</small></span>
                                </div>
                            </td>
                            <td><span class="vp-soft-tag">{{ ucfirst($product->product_type ?? 'simple') }}</span></td>
                            <td class="vp-mono">{{ $product->sku }}</td>
                            <td>{{ number_format((float) $product->regular_price, 2) }}</td>
                            <td>{{ $product->product_type === 'variable' ? ($product->variants_count ?? 0).' variants' : number_format((int) $product->stock_quantity) }}</td>
                            <td><span class="vp-status-pill {{ $product->moderation_status === 'approved' ? 'is-success' : ($product->moderation_status === 'pending_review' ? 'is-info' : ($product->moderation_status === 'rejected' ? 'is-danger' : 'is-muted')) }}">{{ str_replace('_', ' ', ucfirst($product->moderation_status ?? 'approved')) }}</span>@if($product->moderation_status === 'rejected' && $product->moderationHistories->first()?->reason)<small class="vp-cell-sub">{{ $product->moderationHistories->first()->reason }}</small>@endif</td>
                            <td><span class="vp-status-pill {{ $product->status === 'active' ? 'is-success' : 'is-muted' }}">{{ $product->status === 'active' ? 'Published' : 'Unpublished' }}</span></td>
                            <td class="vp-product-actions">
                                <a class="vp-icon-button" href="{{ route('vendor.products.edit', $product) }}" aria-label="Edit {{ $product->name }}"><i class="fas fa-pen"></i></a>
                                @if($product->moderation_status === 'draft')
                                    <form method="POST" action="{{ route('vendor.products.submit-review', $product) }}">@csrf @method('PATCH')<button class="vp-button vp-button-light vp-button-compact" type="submit">Submit review</button></form>
                                @elseif($product->moderation_status === 'approved' && $product->status === 'inactive')
                                    <form method="POST" action="{{ route('vendor.products.publish', $product) }}">@csrf @method('PATCH')<button class="vp-button vp-button-primary vp-button-compact" type="submit">Publish</button></form>
                                @elseif($product->moderation_status === 'approved' && $product->status === 'active')
                                    <form method="POST" action="{{ route('vendor.products.unpublish', $product) }}">@csrf @method('PATCH')<button class="vp-button vp-button-light vp-button-compact" type="submit">Unpublish</button></form>
                                @endif
                                <form method="POST" action="{{ route('vendor.products.destroy', $product) }}" onsubmit="return confirm('Move this product to trash?')">@csrf @method('DELETE')<button class="vp-icon-button vp-icon-button-danger" type="submit" aria-label="Delete {{ $product->name }}"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">
                            <div class="vp-empty-state">
                                <span class="vp-empty-icon"><i class="fas fa-box-open"></i></span>
                                <strong>Your catalogue starts here</strong>
                                <p>Add your first product with clear details, helpful photos and accurate stock.</p>
                                <a class="vp-button vp-button-primary" href="{{ route('vendor.products.create') }}"><i class="fas fa-plus"></i> Add your first product</a>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())<div class="vp-pagination">{{ $products->links() }}</div>@endif
    </section>
@endsection
