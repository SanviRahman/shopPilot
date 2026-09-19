@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-categories-list' : 'categories-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                @unless($isTrash)
                    <th width="30" class="text-center" title="Drag to reorder"><i class="fas fa-arrows-alt text-muted"></i></th>
                @endunless
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-categories-list' : 'categories-list' }}">
                </th>
                <th>Category</th>
                <th>Slug</th>
                <th>Products</th>
                <th>Sort Order</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Status</th>
                @endif
                <th width="150" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="{{ $isTrash ? 'trashSortable' : 'sortableCategoryBody' }}">
            @forelse($categories as $category)
                <tr data-id="{{ $category->id }}" class="{{ ! $isTrash ? 'sortable-row' : '' }}">
                    @unless($isTrash)
                        <td class="text-center align-middle text-muted drag-handle" style="cursor: grab;">
                            <i class="fas fa-grip-vertical"></i>
                        </td>
                    @endunless
                    <td class="text-center align-middle">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" data-row-checkbox>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($category->getImageUrlAttribute())
                                <img src="{{ $category->getImageUrlAttribute() }}" alt="{{ $category->name }}" class="img-thumbnail rounded mr-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="avatar bg-light border text-primary font-weight-bold rounded d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px;">
                                    <i class="fas fa-tags"></i>
                                </div>
                            @endif
                            <div>
                                <span class="font-weight-600 text-dark">{{ $category->name }}</span>
                                @if($category->meta_title)
                                    <span class="badge badge-light border text-xs ml-1" title="Has SEO Configuration"><i class="fas fa-search text-success"></i> SEO</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        <code>{{ $category->slug }}</code>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-light border text-dark px-2 py-1"><i class="fas fa-box text-muted mr-1"></i>{{ $category->products_count ?? 0 }} Items</span>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-info px-2 py-1 order-index">{{ $category->sort_order }}</span>
                    </td>
                    <td class="align-middle">
                        @if($isTrash)
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($category->deleted_at)->format('d M Y, h:i A') }}</small>
                        @else
                            @if($category->isActive())
                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Active</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1"><i class="fas fa-ban mr-1"></i>Inactive</span>
                            @endif
                        @endif
                    </td>
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('categories.restore')
                                <button type="button" class="btn btn-outline-success btn-sm btn-action"
                                    data-url="{{ route('admin.categories.restore', $category->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Category?"
                                    data-confirm-text="Category '{{ $category->name }}' will be restored to active list."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                            @endcan
                            @can('categories.force-delete')
                                <button type="button" class="btn btn-outline-danger btn-sm btn-action"
                                    data-url="{{ route('admin.categories.force-delete', $category->id) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Permanently Delete Category?"
                                    data-confirm-text="Category '{{ $category->name }}' and attached media will be permanently deleted."
                                    title="Permanent Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('categories.view')
                                    <button type="button" class="btn btn-default btn-view-category" data-id="{{ $category->id }}" title="View Details & SEO">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('categories.update')
                                    <button type="button" class="btn btn-default btn-edit-category" data-id="{{ $category->id }}" title="Edit Category">
                                        <i class="fas fa-pen text-primary"></i>
                                    </button>
                                @endcan
                                @can('categories.delete')
                                    <button type="button" class="btn btn-default btn-action"
                                        data-url="{{ route('admin.categories.destroy', $category) }}"
                                        data-method="DELETE"
                                        data-confirm-title="Move to Trash?"
                                        data-confirm-text="Category '{{ $category->name }}' will be moved to trash bin."
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
                        <i class="fas fa-tags fa-3x text-light mb-3 d-block"></i>
                        {{ $isTrash ? 'Trash bin is completely empty.' : 'No categories found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>