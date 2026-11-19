@extends('layouts.admin')

@section('meta_title', 'Product Attributes')

@section('page_content')
<div class="product-attribute-page">
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm small">
            <strong>Please review the following:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="row mb-3">
        <div class="col-12">
            <div class="attribute-intro-card">
                <div class="attribute-intro-icon"><i class="fas fa-sliders-h"></i></div>
                <div>
                    <h2>Build a better product catalogue</h2>
                    <p>Create reusable options such as Color, Size and Material. These options will be available when creating variable products.</p>
                </div>
                <div class="attribute-intro-count"><strong>{{ $attributes->total() }}</strong><span>Attributes</span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-5 mb-3">
            <div class="card attribute-editor-card h-100">
                <div class="card-header">
                    <div>
                        <span class="attribute-eyebrow">ATTRIBUTE SETUP</span>
                        <h3>{{ $editingAttribute ? 'Edit attribute' : 'Create an attribute' }}</h3>
                    </div>
                    @if($editingAttribute)
                        <a href="{{ route('admin.product-attributes.index') }}" class="btn btn-sm btn-light border">Cancel edit</a>
                    @endif
                </div>
                <form action="{{ $editingAttribute ? route('admin.product-attributes.update', $editingAttribute) : route('admin.product-attributes.store') }}" method="POST" id="attributeForm">
                    @csrf
                    @if($editingAttribute) @method('PUT') @endif
                    <div class="card-body">
                        <div class="form-group">
                            <label for="attribute-name">Attribute name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="attribute-name" class="form-control" value="{{ old('name', $editingAttribute?->name) }}" placeholder="e.g. Color" required>
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="attribute-slug">Slug</label>
                            <input type="text" name="slug" id="attribute-slug" class="form-control" value="{{ old('slug', $editingAttribute?->slug) }}" placeholder="auto-generated-from-name">
                            @error('slug')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group col-8">
                                <label for="attribute-display-type">Display style</label>
                                <select name="display_type" id="attribute-display-type" class="custom-select">
                                    <option value="button" @selected(old('display_type', $editingAttribute?->display_type ?? 'button') === 'button')>Buttons (Size, Material)</option>
                                    <option value="color" @selected(old('display_type', $editingAttribute?->display_type) === 'color')>Color swatches</option>
                                    <option value="select" @selected(old('display_type', $editingAttribute?->display_type) === 'select')>Dropdown</option>
                                </select>
                            </div>
                            <div class="form-group col-4 d-flex align-items-end">
                                <div class="custom-control custom-switch mb-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" id="attribute-active" class="custom-control-input" @checked(old('is_active', $editingAttribute?->is_active ?? true))>
                                    <label class="custom-control-label" for="attribute-active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="attribute-values-heading">
                            <div><strong>Attribute values</strong><span>Add every option customers can choose.</span></div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addAttributeValue"><i class="fas fa-plus mr-1"></i> Add value</button>
                        </div>
                        <div class="attribute-value-list" id="attributeValueList">
                            @php
                                $formValues = old('values', $editingAttribute?->values?->map(fn ($value) => [
                                    'id' => $value->id,
                                    'label' => $value->label,
                                    'color_code' => $value->color_code,
                                    'is_active' => $value->is_active,
                                ])->all() ?? [['label' => '', 'color_code' => '#2563eb', 'is_active' => true]]);
                            @endphp
                            @foreach($formValues as $index => $value)
                                <div class="attribute-value-row" data-value-row>
                                    @if(!empty($value['id']))<input type="hidden" name="values[{{ $index }}][id]" value="{{ $value['id'] }}">@endif
                                    <input type="text" name="values[{{ $index }}][label]" class="form-control" value="{{ $value['label'] ?? '' }}" placeholder="e.g. Red" required>
                                    <input type="color" name="values[{{ $index }}][color_code]" class="form-control attribute-color-input" value="{{ $value['color_code'] ?: '#2563eb' }}" aria-label="Color value">
                                    <input type="hidden" name="values[{{ $index }}][is_active]" value="0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="values[{{ $index }}][is_active]" value="1" class="custom-control-input" id="value-active-{{ $index }}" @checked($value['is_active'] ?? true)>
                                        <label class="custom-control-label" for="value-active-{{ $index }}">Active</label>
                                    </div>
                                    <button type="button" class="btn btn-light text-danger" data-remove-value aria-label="Remove value"><i class="fas fa-times"></i></button>
                                </div>
                            @endforeach
                        </div>
                        @error('values')<small class="text-danger d-block mt-2">{{ $message }}</small>@enderror
                        <div class="attribute-form-note"><i class="fas fa-lightbulb"></i><span>Values used by existing variations cannot be removed or deactivated. Add a new value for future products.</span></div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Changes apply to future product edits.</span>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> {{ $editingAttribute ? 'Save changes' : 'Create attribute' }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-7 mb-3">
            <div class="card attribute-list-card">
                <div class="card-header">
                    <div><span class="attribute-eyebrow">CATALOGUE OPTIONS</span><h3>Saved attributes</h3></div>
                    <span class="attribute-total-pill">{{ $attributes->total() }} total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table attribute-table mb-0">
                            <thead><tr><th>Attribute</th><th>Values</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                            @forelse($attributes as $attribute)
                                <tr>
                                    <td><strong>{{ $attribute->name }}</strong><small>{{ $attribute->slug }} · {{ ucfirst($attribute->display_type) }}</small></td>
                                    <td><div class="attribute-value-chips">
                                        @foreach($attribute->values->take(5) as $value)
                                            <span class="attribute-value-chip">@if($attribute->display_type === 'color')<i style="--value-color: {{ $value->color_code ?: '#dbe4ef' }}"></i>@endif{{ $value->label }}</span>
                                        @endforeach
                                        @if($attribute->values_count > 5)<span class="attribute-more-chip">+{{ $attribute->values_count - 5 }}</span>@endif
                                    </div></td>
                                    <td><span class="attribute-status {{ $attribute->is_active ? 'is-active' : 'is-inactive' }}">{{ $attribute->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="text-right text-nowrap">
                                        @can('product-attributes.update')<a href="{{ route('admin.product-attributes.index', ['edit' => $attribute->id]) }}" class="btn btn-sm btn-light border" title="Edit"><i class="fas fa-pen text-primary"></i></a>@endcan
                                        @can('product-attributes.delete')
                                            <form action="{{ route('admin.product-attributes.destroy', $attribute) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this attribute? Attributes used by product variations cannot be deleted.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
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
                @if($attributes->hasPages())<div class="card-footer bg-white">{{ $attributes->links() }}</div>@endif
            </div>
        </div>
    </div>
</div>

<template id="attributeValueTemplate">
    <div class="attribute-value-row" data-value-row>
        <input type="text" class="form-control" placeholder="e.g. Red" required>
        <input type="color" class="form-control attribute-color-input" value="#2563eb" aria-label="Color value">
        <input type="hidden" value="0">
        <div class="custom-control custom-checkbox"><input type="checkbox" value="1" class="custom-control-input"><label class="custom-control-label">Active</label></div>
        <button type="button" class="btn btn-light text-danger" data-remove-value aria-label="Remove value"><i class="fas fa-times"></i></button>
    </div>
</template>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/admin/css/product-management.css') }}">
@endpush

@push('js')
<script>
$(function () {
    let valueIndex = {{ count($formValues) }};
    const list = $('#attributeValueList');
    const template = document.getElementById('attributeValueTemplate');
    const syncColorFields = () => $('.attribute-color-input').toggle($('#attribute-display-type').val() === 'color');

    $('#addAttributeValue').on('click', function () {
        const row = template.content.firstElementChild.cloneNode(true);
        row.querySelector('input[type="text"]').name = `values[${valueIndex}][label]`;
        row.querySelector('input[type="color"]').name = `values[${valueIndex}][color_code]`;
        row.querySelector('input[type="hidden"]').name = `values[${valueIndex}][is_active]`;
        const checkbox = row.querySelector('input[type="checkbox"]');
        checkbox.name = `values[${valueIndex}][is_active]`;
        checkbox.id = `value-active-${valueIndex}`;
        row.querySelector('label').htmlFor = checkbox.id;
        list.append(row);
        valueIndex++;
        syncColorFields();
    });

    $(document).on('click', '[data-remove-value]', function () {
        const row = $(this).closest('[data-value-row]');
        if (row.find('input[name$="[id]"]').length) {
            row.find('input[type="checkbox"]').prop('checked', false);
            row.addClass('opacity-50');
            row.find('input[type="text"]').prop('readonly', true);
            $(this).prop('disabled', true).attr('title', 'Existing value will be deactivated');
            return;
        }
        if (list.find('[data-value-row]').length > 1) row.remove();
    });

    $('#attribute-display-type').on('change', syncColorFields);
    syncColorFields();
});
</script>
@endpush
