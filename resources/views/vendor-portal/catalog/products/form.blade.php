@extends('vendor-portal.layouts.app')

@php
    $isEditing = isset($product);
    $selectedValueIds = collect(old('selected_attributes', []))->flatten()->map(fn ($id) => (int) $id);
    $variantInput = old('variants', $initialVariants ?? []);
    if ($selectedValueIds->isEmpty()) {
        $selectedValueIds = collect($variantInput)->pluck('attribute_value_ids')->flatten()->map(fn ($id) => (int) $id);
    }
    $selectedAttributeIds = $attributes->filter(fn ($attribute) => $attribute->values->contains(fn ($value) => $selectedValueIds->contains((int) $value->id)))->pluck('id')->map(fn ($id) => (int) $id);
@endphp

@section('title', $isEditing ? 'Edit product' : 'Add product')
@section('breadcrumb_label', $isEditing ? 'Edit product' : 'Add product')
@section('page_heading', $isEditing ? 'Update your product' : 'Add a product')
@section('page_description', 'Create a clear, accurate listing that helps customers choose with confidence.')

@section('content')
    <form class="vp-form-layout" method="POST" action="{{ $isEditing ? route('vendor.products.update', $product) : route('vendor.products.store') }}" enctype="multipart/form-data" data-vp-product-form>
        @csrf
        @if($isEditing) @method('PUT') @endif

        <div class="vp-form-main">
            <section class="vp-panel">
                <div class="vp-panel-heading"><div><span class="vp-section-kicker">PRODUCT DETAILS</span><h2>Basic information</h2><p>Start with the essentials customers will see first.</p></div></div>
                <div class="vp-form-grid">
                    <label class="vp-field vp-field-full"><span>Product name <b>*</b></span><input name="name" value="{{ old('name', $product?->name ?? '') }}" maxlength="180" required placeholder="e.g. Everyday cotton shirt">@error('name')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field"><span>Category <b>*</b></span><select name="category_id" required><option value="">Choose a category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select>@error('category_id')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field"><span>Product type <b>*</b></span><select name="product_type" data-vp-product-type required><option value="simple" @selected(old('product_type', $product?->product_type ?? 'simple') === 'simple')>Simple product</option><option value="variable" @selected(old('product_type', $product?->product_type ?? 'simple') === 'variable')>Variable product</option></select><small>Choose variable when customers need options such as size or colour.</small></label>
                    <label class="vp-field vp-field-full"><span>Short description</span><textarea name="short_description" rows="3" maxlength="500" placeholder="A concise introduction to your product.">{{ old('short_description', $product?->short_description ?? '') }}</textarea>@error('short_description')<small>{{ $message }}</small>@enderror</label>
                    <label class="vp-field vp-field-full"><span>Full description</span><textarea name="description" rows="6" placeholder="Describe the materials, features and care details.">{{ old('description', $product?->description ?? '') }}</textarea>@error('description')<small>{{ $message }}</small>@enderror</label>
                </div>
            </section>

            <section class="vp-panel">
                <div class="vp-panel-heading"><div><span class="vp-section-kicker">PRICING & INVENTORY</span><h2>Price and availability</h2><p>Keep prices and stock accurate before submitting your listing.</p></div></div>
                <div class="vp-form-grid">
                    <label class="vp-field vp-field-full"><span>Parent SKU <b>*</b></span><input name="sku" value="{{ old('sku', $product?->sku ?? '') }}" required maxlength="100" placeholder="e.g. SHIRT-001">@error('sku')<small>{{ $message }}</small>@enderror</label>
                    <div class="vp-field-full" data-vp-simple-fields>
                        <div class="vp-form-grid">
                            <label class="vp-field"><span>Regular price <b>*</b></span><input type="number" min="0" step="0.01" name="regular_price" value="{{ old('regular_price', $product?->regular_price ?? '') }}" placeholder="0.00">@error('regular_price')<small>{{ $message }}</small>@enderror</label>
                            <label class="vp-field"><span>Sale price</span><input type="number" min="0" step="0.01" name="sale_price" value="{{ old('sale_price', $product?->sale_price ?? '') }}" placeholder="0.00">@error('sale_price')<small>{{ $message }}</small>@enderror</label>
                            <label class="vp-field"><span>Stock quantity <b>*</b></span><input type="number" min="0" step="1" name="stock_quantity" value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}" placeholder="0">@error('stock_quantity')<small>{{ $message }}</small>@enderror</label>
                        </div>
                    </div>
                </div>
            </section>

            <section class="vp-panel" data-vp-variation-panel hidden>
                <div class="vp-panel-heading"><div><span class="vp-section-kicker">OPTIONS</span><h2>Product variations</h2><p>Select attributes and generate the combinations customers can choose from.</p></div><span class="vp-soft-tag">Variable product</span></div>
                <div class="vp-attribute-grid" data-vp-attribute-choices>
                    @forelse($attributes as $attribute)
                        <fieldset class="vp-attribute-card" data-vp-attribute-card data-attribute-id="{{ $attribute->id }}">
                            <legend><label><input type="checkbox" data-vp-attribute-toggle @checked($selectedAttributeIds->contains((int) $attribute->id))> {{ $attribute->name }}</label></legend>
                            <div class="vp-option-list">
                                @foreach($attribute->values as $value)
                                    <label class="vp-option-chip">
                                        <input type="checkbox" name="selected_attributes[{{ $attribute->id }}][]" data-vp-attribute-value value="{{ $value->id }}" @checked($selectedValueIds->contains((int) $value->id)) @disabled(! $selectedAttributeIds->contains((int) $attribute->id))>
                                        @if($attribute->display_type === 'color')<span class="vp-color-dot" style="background: {{ $value->color_code ?: '#dbe4ef' }}"></span>@endif
                                        <span>{{ $value->label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @empty
                        <div class="vp-empty-inline"><i class="fas fa-sliders-h"></i><span>No active attributes are available. Ask the marketplace administrator to add Color, Size or other options.</span></div>
                    @endforelse
                </div>
                <div class="vp-variant-tools">
                    <p>Select one or more values under each attribute, then generate the variations.</p>
                    <button type="button" class="vp-button vp-button-light" data-vp-generate-variants><i class="fas fa-sync-alt"></i> Generate variations</button>
                </div>
                <div class="vp-variant-error" data-vp-variant-error hidden></div>
                @error('variants')<div class="vp-variant-error">{{ $message }}</div>@enderror
                <div class="vp-variant-summary" data-vp-variant-summary hidden></div>
                <div class="vp-table-wrap vp-variant-table-wrap">
                    <table class="vp-table vp-variant-table">
                        <thead><tr><th>Option combination</th><th>Variant SKU</th><th>Regular price</th><th>Sale price</th><th>Stock</th><th>Image</th><th></th></tr></thead>
                        <tbody data-vp-variant-rows><tr><td colspan="7" class="vp-table-empty">Choose attribute values and generate variations.</td></tr></tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="vp-form-aside">
            <section class="vp-panel">
                <div class="vp-panel-heading"><div><span class="vp-section-kicker">PUBLISHING</span><h2>Listing status</h2></div></div>
                <div class="vp-moderation-card">
                    <span class="vp-status-pill {{ $product?->moderation_status === 'approved' ? 'is-success' : ($product?->moderation_status === 'pending_review' ? 'is-info' : 'is-muted') }}">{{ str_replace('_', ' ', ucfirst($product?->moderation_status ?? 'draft')) }}</span>
                    <p>{{ config('shop.vendor_products.require_moderation', true) ? 'New and edited listings are saved as drafts. Submit them for marketplace review before publishing.' : 'Your listings can be published after saving. You can control availability from the product list.' }}</p>
                    @if($product?->moderation_status === 'rejected' && $product->moderationHistories->first()?->reason)<small class="vp-field-error">Review note: {{ $product->moderationHistories->first()->reason }}</small>@endif
                </div>
            </section>

            <section class="vp-panel">
                <div class="vp-panel-heading"><div><span class="vp-section-kicker">PRODUCT MEDIA</span><h2>Product image</h2><p>Use a clear image with a clean background.</p></div></div>
                @if($product?->thumbnail_url)<img class="vp-current-product-image" src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">@endif
                <label class="vp-upload-box">
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                    <i class="fas fa-cloud-upload-alt"></i><strong>Choose an image</strong><small>JPG, PNG or WebP · Max 2 MB</small>
                </label>
                @error('image')<small class="vp-field-error">{{ $message }}</small>@enderror
            </section>
            <div class="vp-form-actions">
                <a href="{{ route('vendor.products.index') }}" class="vp-button vp-button-light">Cancel</a>
                <button type="submit" class="vp-button vp-button-primary">{{ $isEditing ? 'Save changes' : 'Save draft' }}</button>
            </div>
        </aside>
    </form>

    <script type="application/json" data-vp-product-attributes>@json($attributes->map(fn ($attribute) => ['id' => $attribute->id, 'name' => $attribute->name, 'values' => $attribute->values->map(fn ($value) => ['id' => $value->id, 'label' => $value->label, 'slug' => $value->slug, 'color_code' => $value->color_code])->values()])->values())</script>
    <script type="application/json" data-vp-initial-variants>@json($variantInput)</script>
@endsection
