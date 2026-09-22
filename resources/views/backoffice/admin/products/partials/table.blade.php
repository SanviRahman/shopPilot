@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-products-list' : 'products-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-products-list' : 'products-list' }}">
                </th>
                <th>Product</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Status</th>
                @endif
                <th width="150" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" data-row-checkbox>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($product->getThumbnailUrlAttribute())
                                <img src="{{ $product->getThumbnailUrlAttribute() }}" alt="{{ $product->name }}" class="img-thumbnail rounded mr-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="avatar bg-light border text-primary font-weight-bold rounded d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px;">
                                    <i class="fas fa-box"></i>
                                </div>
                            @endif
                            <div>
                                <span class="font-weight-600 text-dark">{{ $product->name }}</span>
                                @if($product->featured)
                                    <span class="badge badge-warning text-xs ml-1">Featured</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="align-middle"><code>{{ $product->sku }}</code></td>
                    <td class="align-middle">{{ $product->category?->name ?? 'N/A' }}</td>
                    <td class="align-middle">
                        @if($product->sale_price)
                            <span class="text-success font-weight-bold">${{ number_format($product->sale_price, 2) }}</span>
                            <small class="text-muted text-strike ml-1"><s>${{ number_format($product->regular_price, 2) }}</s></small>
                        @else
                            <span class="font-weight-bold">${{ number_format($product->regular_price, 2) }}</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-light border text-dark px-2 py-1">{{ $product->stock_quantity }} in stock</span>
                    </td>
                    <td class="align-middle">
                        @if($isTrash)
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($product->deleted_at)->format('d M Y, h:i A') }}</small>
                        @else
                            @if($product->isActive())
                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Active</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1"><i class="fas fa-ban mr-1"></i>Inactive</span>
                            @endif
                        @endif
                    </td>
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('products.restore')
                                <button type="button" class="btn btn-outline-success btn-sm btn-action"
                                    data-url="{{ route('admin.products.restore', $product->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Product?"
                                    data-confirm-text="Product '{{ $product->name }}' will be restored."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                            @endcan
                            @can('products.force-delete')
                                <button type="button" class="btn btn-outline-danger btn-sm btn-action"
                                    data-url="{{ route('admin.products.force-delete', $product->id) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Permanently Delete?"
                                    data-confirm-text="Product '{{ $product->name }}' will be permanently deleted."
                                    title="Permanent Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('products.view')
                                    <button type="button" class="btn btn-default btn-view-product" data-id="{{ $product->id }}" title="View Details">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('products.update')
                                    <button type="button" class="btn btn-default btn-edit-product" data-id="{{ $product->id }}" title="Edit Product">
                                        <i class="fas fa-pen text-primary"></i>
                                    </button>
                                @endcan
                                @can('products.delete')
                                    <button type="button" class="btn btn-default btn-action"
                                        data-url="{{ route('admin.products.destroy', $product) }}"
                                        data-method="DELETE"
                                        data-confirm-title="Move to Trash?"
                                        data-confirm-text="Product '{{ $product->name }}' will be moved to trash."
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
                    <td colspan="{{ $isTrash ? 7 : 8 }}" class="text-center text-muted py-5">
                        <i class="fas fa-box fa-3x text-light mb-3 d-block"></i>
                        {{ $isTrash ? 'Trash bin is completely empty.' : 'No products found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>