<div class="product-attribute-page attribute-manager-content">
    <div class="attribute-intro-card mb-3">
        <div class="attribute-intro-icon"><i class="fas fa-sliders-h"></i></div>
        <div>
            <h2>Build a better product catalogue</h2>
            <p>Create reusable options such as Color, Size and Material. Changes will be available in this product form.</p>
        </div>
        <div class="attribute-intro-count"><strong>{{ $attributes->count() }}</strong><span>Attributes</span></div>
    </div>

    @php $canManageAttributes = auth('admin')->user()?->can('product-attributes.create') || auth('admin')->user()?->can('product-attributes.update'); @endphp
    <div class="row">
        @canany(['product-attributes.create', 'product-attributes.update'])
            <div class="col-12 col-xl-5 mb-3">
                <div class="card attribute-editor-card h-100">
                    <div class="card-header">
                        <div>
                            <span class="attribute-eyebrow">ATTRIBUTE SETUP</span>
                            <h3 id="attributeManagerFormTitle">Create an attribute</h3>
                        </div>
                        @can('product-attributes.create')
                            <button type="button" class="btn btn-sm btn-light border" id="attributeManagerNew">New</button>
                        @endcan
                    </div>
                    <form id="attributeManagerForm"
                          action="{{ route('admin.product-attributes.store') }}"
                          data-store-url="{{ route('admin.product-attributes.store') }}"
                          data-update-url="{{ route('admin.product-attributes.update', ['product_attribute' => '__ATTRIBUTE_ID__']) }}"
                          data-create-allowed="{{ auth('admin')->user()?->can('product-attributes.create') ? '1' : '0' }}"
                          data-update-allowed="{{ auth('admin')->user()?->can('product-attributes.update') ? '1' : '0' }}">
                        @csrf
                        <input type="hidden" name="_method" id="attributeManagerMethod" value="POST">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="attributeManagerName">Attribute name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="attributeManagerName" class="form-control" placeholder="e.g. Color" required>
                            </div>
                            <div class="form-group">
                                <label for="attributeManagerSlug">Slug</label>
                                <input type="text" name="slug" id="attributeManagerSlug" class="form-control" placeholder="Auto-generated from name">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-8">
                                    <label for="attributeManagerDisplayType">Display style</label>
                                    <select name="display_type" id="attributeManagerDisplayType" class="custom-select">
                                        <option value="button">Buttons (Size, Material)</option>
                                        <option value="color">Color swatches</option>
                                        <option value="select">Dropdown</option>
                                    </select>
                                </div>
                                <div class="form-group col-4 d-flex align-items-end">
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" id="attributeManagerActive" class="custom-control-input" checked>
                                        <label class="custom-control-label" for="attributeManagerActive">Active</label>
                                    </div>
                                </div>
                            </div>

                            <div class="attribute-values-heading">
                                <div><strong>Attribute values</strong><span>Add every option customers can choose.</span></div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addManagerAttributeValue"><i class="fas fa-plus mr-1"></i> Add value</button>
                            </div>
                            <div class="attribute-value-list" id="attributeManagerValueList">
                                <div class="attribute-value-row" data-attribute-value-row>
                                    <input type="text" name="values[0][label]" class="form-control" placeholder="e.g. Red" required>
                                    <input type="color" name="values[0][color_code]" class="form-control attribute-color-input" value="#2563eb" aria-label="Color value">
                                    <input type="hidden" name="values[0][is_active]" value="0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="values[0][is_active]" value="1" class="custom-control-input" id="manager-value-active-0" checked>
                                        <label class="custom-control-label" for="manager-value-active-0">Active</label>
                                    </div>
                                    <button type="button" class="btn btn-light text-danger" data-remove-manager-value aria-label="Remove value"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <div class="attribute-form-note"><i class="fas fa-lightbulb"></i><span>Values used by existing product variations cannot be removed or deactivated.</span></div>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-light btn-sm border d-none" id="cancelAttributeEdit">Cancel edit</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4" id="saveAttributeManagerForm" @disabled(!auth('admin')->user()?->can('product-attributes.create'))><i class="fas fa-save mr-1"></i> Create attribute</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcanany

        <div class="col-12 {{ $canManageAttributes ? 'col-xl-7' : '' }} mb-3">
            <div class="card attribute-list-card h-100">
                <div class="card-header">
                    <div><span class="attribute-eyebrow">CATALOGUE OPTIONS</span><h3>Saved attributes</h3></div>
                    <span class="attribute-total-pill">{{ $attributes->count() }} total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table attribute-table mb-0">
                            <thead><tr><th>Attribute</th><th>Values</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                                @forelse($attributes as $attribute)
                                    @php
                                        $attributeData = [
                                            'id' => $attribute->id,
                                            'name' => $attribute->name,
                                            'slug' => $attribute->slug,
                                            'display_type' => $attribute->display_type,
                                            'is_active' => $attribute->is_active,
                                            'values' => $attribute->values->map(fn ($value) => [
                                                'id' => $value->id,
                                                'label' => $value->label,
                                                'color_code' => $value->color_code,
                                                'is_active' => $value->is_active,
                                            ])->values(),
                                        ];
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $attribute->name }}</strong><small>{{ $attribute->slug }} · {{ ucfirst($attribute->display_type) }}</small></td>
                                        <td>
                                            <div class="attribute-value-chips">
                                                @foreach($attribute->values->take(5) as $value)
                                                    <span class="attribute-value-chip">@if($attribute->display_type === 'color')<i style="--value-color: {{ $value->color_code ?: '#dbe4ef' }}"></i>@endif{{ $value->label }}</span>
                                                @endforeach
                                                @if($attribute->values_count > 5)<span class="attribute-more-chip">+{{ $attribute->values_count - 5 }}</span>@endif
                                            </div>
                                        </td>
                                        <td><span class="attribute-status {{ $attribute->is_active ? 'is-active' : 'is-inactive' }}">{{ $attribute->is_active ? 'Active' : 'Inactive' }}</span></td>
                                        <td class="text-right text-nowrap">
                                            @can('product-attributes.update')
                                                <button type="button" class="btn btn-sm btn-light border" data-edit-attribute="{{ rawurlencode(json_encode($attributeData)) }}" title="Edit"><i class="fas fa-pen text-primary"></i></button>
                                            @endcan
                                            @can('product-attributes.delete')
                                                <button type="button" class="btn btn-sm btn-light border text-danger" data-delete-attribute data-url="{{ route('admin.product-attributes.destroy', $attribute) }}" data-name="{{ $attribute->name }}" title="Delete"><i class="fas fa-trash"></i></button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted"><i class="fas fa-sliders-h fa-2x d-block mb-2"></i>No attributes yet. Create Color or Size to get started.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
