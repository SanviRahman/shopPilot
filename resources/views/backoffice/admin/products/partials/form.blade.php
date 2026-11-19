<div class="modal fade" id="productFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="productFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <form id="productAjaxForm" action="" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="productFormMethod" value="POST">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="productFormModalTitle">
                        <i class="fas fa-box text-primary mr-2"></i><span>Create Product</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="modal-body p-3">
                    <ul class="nav nav-tabs mb-3" id="productTab" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="general-tab" data-toggle="tab" href="#tab-general" role="tab"><i class="fas fa-info-circle mr-1"></i> General</a></li>
                        <li class="nav-item d-none" id="variantTabNavItem"><a class="nav-link" id="variants-tab" data-toggle="tab" href="#tab-variations" role="tab"><i class="fas fa-layer-group mr-1"></i> Variations</a></li>
                        <li class="nav-item"><a class="nav-link" id="seo-tab" data-toggle="tab" href="#tab-seo" role="tab"><i class="fas fa-globe mr-1"></i> General SEO</a></li>
                        <li class="nav-item"><a class="nav-link" id="og-tab" data-toggle="tab" href="#tab-og" role="tab"><i class="fab fa-facebook text-primary mr-1"></i> OpenGraph</a></li>
                        <li class="nav-item"><a class="nav-link" id="twitter-tab" data-toggle="tab" href="#tab-twitter" role="tab"><i class="fab fa-twitter text-info mr-1"></i> Twitter Card</a></li>
                    </ul>

                    <div class="tab-content" id="productTabContent">
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                            <div class="row">
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-name" class="font-weight-bold">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" id="prod-name" name="name" class="form-control" placeholder="e.g. Premium Cotton T-Shirt" required>
                                    <div class="invalid-feedback d-block" id="err-name"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-slug" class="font-weight-bold">Slug (URL)</label>
                                    <input type="text" id="prod-slug" name="slug" class="form-control" placeholder="Auto-generated if blank">
                                    <div class="invalid-feedback d-block" id="err-slug"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-type" class="font-weight-bold">Product Type <span class="text-danger">*</span></label>
                                    <select id="prod-type" name="product_type" class="custom-select" required>
                                        <option value="simple">Simple Product</option>
                                        <option value="variable">Variable Product (Color, Size etc.)</option>
                                    </select>
                                    <small class="form-text text-muted">Choose Variable Product when customers need to select options before ordering.</small>
                                    <div class="invalid-feedback d-block" id="err-product_type"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-sku" class="font-weight-bold">Product SKU / Parent SKU <span class="text-danger">*</span></label>
                                    <input type="text" id="prod-sku" name="sku" class="form-control" placeholder="e.g. TSHIRT-001" required>
                                    <small class="form-text text-muted">Each variation will have its own unique SKU.</small>
                                    <div class="invalid-feedback d-block" id="err-sku"></div>
                                </div>

                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-category" class="font-weight-bold">Category <span class="text-danger">*</span></label>
                                    <select id="prod-category" name="category_id" class="custom-select" required>
                                        <option value="">Select Category</option>
                                        @foreach(\App\Models\Category::getTreeSelectOptions(null, true) as $cat)
                                            <option value="{{ $cat['id'] }}">{{ $cat['indent'] }}{{ $cat['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback d-block" id="err-category_id"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-status" class="font-weight-bold">Status <span class="text-danger">*</span></label>
                                    <select id="prod-status" name="status" class="custom-select" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback d-block" id="err-status"></div>
                                </div>

                                <div class="col-12" id="simpleProductFields">
                                    <div class="row">
                                        <div class="form-group col-md-4 mb-3">
                                            <label for="prod-regular-price" class="font-weight-bold">Regular Price (৳) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="0" id="prod-regular-price" name="regular_price" class="form-control" placeholder="0.00" required>
                                            <div class="invalid-feedback d-block" id="err-regular_price"></div>
                                        </div>
                                        <div class="form-group col-md-4 mb-3">
                                            <label for="prod-sale-price" class="font-weight-bold">Sale Price (৳)</label>
                                            <input type="number" step="0.01" min="0" id="prod-sale-price" name="sale_price" class="form-control" placeholder="0.00">
                                            <div class="invalid-feedback d-block" id="err-sale_price"></div>
                                        </div>
                                        <div class="form-group col-md-4 mb-3">
                                            <label for="prod-stock" class="font-weight-bold">Stock Quantity <span class="text-danger">*</span></label>
                                            <input type="number" id="prod-stock" name="stock_quantity" class="form-control" placeholder="0" min="0" required>
                                            <div class="invalid-feedback d-block" id="err-stock_quantity"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 d-none" id="variableProductNotice">
                                    <div class="product-type-card mb-3">
                                        <span class="type-icon"><i class="fas fa-layer-group"></i></span>
                                        <div><strong class="d-block text-dark small">Variation pricing and stock</strong><span class="text-muted small">Set SKU, price, image and stock separately for every generated combination in the Variations tab.</span></div>
                                        <button type="button" class="btn btn-sm btn-primary ml-auto" id="openVariationsTab">Configure variations <i class="fas fa-arrow-right ml-1"></i></button>
                                    </div>
                                </div>

                                <div class="form-group col-md-6 mb-3 d-flex align-items-center">
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="hidden" name="featured" value="0">
                                        <input type="checkbox" class="custom-control-input" id="prod-featured" name="featured" value="1">
                                        <label class="custom-control-label font-weight-bold" for="prod-featured">Featured Product</label>
                                    </div>
                                </div>
                                <div class="form-group col-12 mb-3">
                                    <label for="prod-short-desc" class="font-weight-bold">Short Description</label>
                                    <textarea id="prod-short-desc" name="short_description" rows="2" class="form-control" placeholder="A short product summary..."></textarea>
                                    <div class="invalid-feedback d-block" id="err-short_description"></div>
                                </div>
                                <div class="form-group col-12 mb-3">
                                    <label for="prod-description" class="font-weight-bold">Description</label>
                                    <textarea id="prod-description" name="description" rows="3" class="form-control" placeholder="Detailed product information..."></textarea>
                                    <div class="invalid-feedback d-block" id="err-description"></div>
                                </div>
                                <div class="form-group col-12 mb-0">
                                    <x-backoffice.media-picker
                                        name="image"
                                        input-id="prod-image"
                                        label="Product Thumbnail"
                                        choose-label="Choose image file..."
                                        remove-name="remove_image"
                                        media-id-name="image_media_id"
                                        picker-url="{{ route('admin.media.picker') }}"
                                    />
                                    <div class="invalid-feedback d-block" id="err-image"></div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-variations" role="tabpanel">
                            <div class="variation-builder">
                                <div class="variation-builder-heading">
                                    <div><h6>Configure product options</h6><p>Select the attributes and values customers can choose from.</p></div>
                                    @can('product-attributes.view')
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="manageProductAttributes"><i class="fas fa-sliders-h mr-1"></i> Manage attributes</button>
                                    @endcan
                                </div>
                                <div class="attribute-choice-grid" id="variantAttributeChoices">
                                    @forelse($productAttributes as $attribute)
                                        <div class="attribute-choice-card" data-attribute-card data-attribute-id="{{ $attribute->id }}">
                                            <div class="attribute-choice-title">
                                                <label class="mb-0"><input type="checkbox" data-attribute-toggle> {{ $attribute->name }}</label>
                                                <small class="text-muted">{{ $attribute->values->count() }} values</small>
                                            </div>
                                            <div class="attribute-choice-values">
                                                @foreach($attribute->values as $value)
                                                    <label class="attribute-choice-value">
                                                        <input type="checkbox" data-attribute-value value="{{ $value->id }}" disabled>
                                                        @if($attribute->display_type === 'color')<i class="swatch" style="--swatch-color: {{ $value->color_code ?: '#dbe4ef' }}"></i>@endif
                                                        {{ $value->label }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        <div class="variant-empty-state w-100">No active attributes found. Create Color or Size first from Manage attributes.</div>
                                    @endforelse
                                </div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 mb-3">
                                    <div class="small text-muted">Choose at least one value from each selected attribute.</div>
                                    <button type="button" class="btn btn-primary btn-sm mt-2 mt-sm-0" id="generateVariants"><i class="fas fa-magic mr-1"></i> Generate variations</button>
                                </div>
                                <div class="variation-table-wrap">
                                    <table class="table variation-table">
                                        <thead><tr><th>Variation</th><th>SKU</th><th>Regular (৳)</th><th>Sale (৳)</th><th>Stock</th><th>Image</th><th></th></tr></thead>
                                        <tbody id="variantRows"><tr><td colspan="7"><div class="variant-empty-state"><i class="fas fa-layer-group"></i>Select attributes, then generate your product variations.</div></td></tr></tbody>
                                    </table>
                                </div>
                                <div class="variant-summary" id="variantSummary" hidden></div>
                                <div class="invalid-feedback d-block mt-2" id="err-variants"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                            <div class="form-group mb-3"><label for="seo-meta-title" class="font-weight-bold">Meta Title</label><input type="text" id="seo-meta-title" name="meta_title" class="form-control" placeholder="Custom SEO title"><div class="invalid-feedback d-block" id="err-meta_title"></div></div>
                            <div class="form-group mb-3"><label for="seo-meta-description" class="font-weight-bold">Meta Description</label><textarea id="seo-meta-description" name="meta_description" rows="3" class="form-control" placeholder="Meta description..."></textarea><div class="invalid-feedback d-block" id="err-meta_description"></div></div>
                            <div class="form-group mb-3"><label for="seo-meta-keywords" class="font-weight-bold">Meta Keywords</label><input type="text" id="seo-meta-keywords" name="meta_keywords" class="form-control" placeholder="e.g. t-shirt, clothing"><div class="invalid-feedback d-block" id="err-meta_keywords"></div></div>
                            <div class="form-group mb-0"><label for="seo-canonical-url" class="font-weight-bold">Canonical URL</label><input type="url" id="seo-canonical-url" name="canonical_url" class="form-control" placeholder="https://example.com/product-url"><div class="invalid-feedback d-block" id="err-canonical_url"></div></div>
                        </div>

                        <div class="tab-pane fade" id="tab-og" role="tabpanel">
                            <div class="form-group mb-3"><label for="seo-og-title" class="font-weight-bold">Facebook OG Title</label><input type="text" id="seo-og-title" name="og_title" class="form-control" placeholder="OG title"><div class="invalid-feedback d-block" id="err-og_title"></div></div>
                            <div class="form-group mb-3"><label for="seo-og-description" class="font-weight-bold">Facebook OG Description</label><textarea id="seo-og-description" name="og_description" rows="2" class="form-control" placeholder="OG description..."></textarea><div class="invalid-feedback d-block" id="err-og_description"></div></div>
                            <div class="form-group mb-0"><x-backoffice.media-picker name="og_image" input-id="seo-og-image" label="Facebook Share Image" choose-label="Choose OG image..." remove-name="remove_og_image" media-id-name="og_image_media_id" picker-url="{{ route('admin.media.picker') }}"/><div class="invalid-feedback d-block" id="err-og_image"></div></div>
                        </div>

                        <div class="tab-pane fade" id="tab-twitter" role="tabpanel">
                            <div class="form-group mb-3"><label for="seo-twitter-title" class="font-weight-bold">Twitter Card Title</label><input type="text" id="seo-twitter-title" name="twitter_title" class="form-control" placeholder="Twitter title"><div class="invalid-feedback d-block" id="err-twitter_title"></div></div>
                            <div class="form-group mb-3"><label for="seo-twitter-description" class="font-weight-bold">Twitter Card Description</label><textarea id="seo-twitter-description" name="twitter_description" rows="2" class="form-control" placeholder="Twitter description..."></textarea><div class="invalid-feedback d-block" id="err-twitter_description"></div></div>
                            <div class="form-group mb-0"><x-backoffice.media-picker name="twitter_image" input-id="seo-twitter-image" label="Twitter Share Image" choose-label="Choose Twitter image..." remove-name="remove_twitter_image" media-id-name="twitter_image_media_id" picker-url="{{ route('admin.media.picker') }}"/><div class="invalid-feedback d-block" id="err-twitter_image"></div></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitProductForm"><i class="fas fa-save mr-1"></i> Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('backoffice.admin.media.partials.picker-modal')
