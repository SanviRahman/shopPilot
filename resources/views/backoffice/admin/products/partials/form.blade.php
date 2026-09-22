<div class="modal fade" id="productFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="productFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="productAjaxForm" action="" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="productFormMethod" value="POST">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="productFormModalTitle">
                        <i class="fas fa-box text-primary mr-2"></i><span>Create Product</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                {{-- Added max-height and overflow-y: auto for vertical scrollbar --}}
                <div class="modal-body p-3" style="max-height: 70vh; overflow-y: auto;">
                    <ul class="nav nav-tabs mb-3" id="productTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-600" id="general-tab" data-toggle="tab" href="#tab-general" role="tab"><i class="fas fa-info-circle mr-1"></i> General</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-600" id="seo-tab" data-toggle="tab" href="#tab-seo" role="tab"><i class="fas fa-globe mr-1"></i> General SEO</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-600" id="og-tab" data-toggle="tab" href="#tab-og" role="tab"><i class="fab fa-facebook text-primary mr-1"></i> OpenGraph</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-600" id="twitter-tab" data-toggle="tab" href="#tab-twitter" role="tab"><i class="fab fa-twitter text-info mr-1"></i> Twitter Card</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="productTabContent">
                        {{-- Tab 1: General --}}
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                            <div class="row">
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-name" class="font-weight-600">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" id="prod-name" name="name" class="form-control form-control-sm" placeholder="e.g. Wireless Headphones" required>
                                    <div class="invalid-feedback d-block" id="err-name"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-slug" class="font-weight-600">Slug (URL)</label>
                                    <input type="text" id="prod-slug" name="slug" class="form-control form-control-sm" placeholder="auto-generated-if-blank">
                                    <div class="invalid-feedback d-block" id="err-slug"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-sku" class="font-weight-600">SKU <span class="text-danger">*</span></label>
                                    <input type="text" id="prod-sku" name="sku" class="form-control form-control-sm" placeholder="e.g. WH-1029" required>
                                    <div class="invalid-feedback d-block" id="err-sku"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-category" class="font-weight-600">Category <span class="text-danger">*</span></label>
                                    <select id="prod-category" name="category_id" class="custom-select custom-select-sm" required>
                                        <option value="">Select Category</option>
                                        @foreach(\App\Models\Category::where('status', 'active')->get() as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback d-block" id="err-category_id"></div>
                                </div>
                                <div class="form-group col-md-4 mb-3">
                                    <label for="prod-regular-price" class="font-weight-600">Regular Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" id="prod-regular-price" name="regular_price" class="form-control form-control-sm" placeholder="0.00" required>
                                    <div class="invalid-feedback d-block" id="err-regular_price"></div>
                                </div>
                                <div class="form-group col-md-4 mb-3">
                                    <label for="prod-sale-price" class="font-weight-600">Sale Price ($)</label>
                                    <input type="number" step="0.01" id="prod-sale-price" name="sale_price" class="form-control form-control-sm" placeholder="0.00">
                                    <div class="invalid-feedback d-block" id="err-sale_price"></div>
                                </div>
                                <div class="form-group col-md-4 mb-3">
                                    <label for="prod-stock" class="font-weight-600">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" id="prod-stock" name="stock_quantity" class="form-control form-control-sm" placeholder="0" min="0" required>
                                    <div class="invalid-feedback d-block" id="err-stock_quantity"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="prod-status" class="font-weight-600">Status <span class="text-danger">*</span></label>
                                    <select id="prod-status" name="status" class="custom-select custom-select-sm" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback d-block" id="err-status"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3 d-flex align-items-center">
                                    <div class="custom-control custom-checkbox mt-4">
                                        <input type="checkbox" class="custom-control-input" id="prod-featured" name="featured" value="1">
                                        <label class="custom-control-label font-weight-600" for="prod-featured">Featured Product</label>
                                    </div>
                                </div>
                                <div class="form-group col-12 mb-3">
                                    <label for="prod-short-desc" class="font-weight-600">Short Description</label>
                                    <textarea id="prod-short-desc" name="short_description" rows="2" class="form-control form-control-sm" placeholder="Brief product summary..."></textarea>
                                    <div class="invalid-feedback d-block" id="err-short_description"></div>
                                </div>
                                <div class="form-group col-12 mb-3">
                                    <label for="prod-description" class="font-weight-600">Description</label>
                                    <textarea id="prod-description" name="description" rows="3" class="form-control form-control-sm" placeholder="Detailed description..."></textarea>
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

                        {{-- Tab 2: General SEO --}}
                        <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                            <div class="form-group mb-3">
                                <label for="seo-meta-title" class="font-weight-600">Meta Title</label>
                                <input type="text" id="seo-meta-title" name="meta_title" class="form-control form-control-sm" placeholder="Custom SEO title">
                                <div class="invalid-feedback d-block" id="err-meta_title"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="seo-meta-description" class="font-weight-600">Meta Description</label>
                                <textarea id="seo-meta-description" name="meta_description" rows="3" class="form-control form-control-sm" placeholder="Meta description..."></textarea>
                                <div class="invalid-feedback d-block" id="err-meta_description"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="seo-meta-keywords" class="font-weight-600">Meta Keywords</label>
                                <input type="text" id="seo-meta-keywords" name="meta_keywords" class="form-control form-control-sm" placeholder="e.g. headphones, electronics">
                                <div class="invalid-feedback d-block" id="err-meta_keywords"></div>
                            </div>
                            <div class="form-group mb-0">
                                <label for="seo-canonical-url" class="font-weight-600">Canonical URL</label>
                                <input type="url" id="seo-canonical-url" name="canonical_url" class="form-control form-control-sm" placeholder="https://example.com/product-url">
                                <div class="invalid-feedback d-block" id="err-canonical_url"></div>
                            </div>
                        </div>

                        {{-- Tab 3: Facebook OpenGraph --}}
                        <div class="tab-pane fade" id="tab-og" role="tabpanel">
                            <div class="form-group mb-3">
                                <label for="seo-og-title" class="font-weight-600">Facebook OG Title</label>
                                <input type="text" id="seo-og-title" name="og_title" class="form-control form-control-sm" placeholder="OG title">
                                <div class="invalid-feedback d-block" id="err-og_title"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="seo-og-description" class="font-weight-600">Facebook OG Description</label>
                                <textarea id="seo-og-description" name="og_description" rows="2" class="form-control form-control-sm" placeholder="OG description..."></textarea>
                                <div class="invalid-feedback d-block" id="err-og_description"></div>
                            </div>
                            <div class="form-group mb-0">
                                <x-backoffice.media-picker
                                    name="og_image"
                                    input-id="seo-og-image"
                                    label="Facebook Share Image"
                                    choose-label="Choose OG image..."
                                    remove-name="remove_og_image"
                                    media-id-name="og_image_media_id"
                                    picker-url="{{ route('admin.media.picker') }}"
                                />
                                <div class="invalid-feedback d-block" id="err-og_image"></div>
                            </div>
                        </div>

                        {{-- Tab 4: Twitter Card --}}
                        <div class="tab-pane fade" id="tab-twitter" role="tabpanel">
                            <div class="form-group mb-3">
                                <label for="seo-twitter-title" class="font-weight-600">Twitter Card Title</label>
                                <input type="text" id="seo-twitter-title" name="twitter_title" class="form-control form-control-sm" placeholder="Twitter title">
                                <div class="invalid-feedback d-block" id="err-twitter_title"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="seo-twitter-description" class="font-weight-600">Twitter Card Description</label>
                                <textarea id="seo-twitter-description" name="twitter_description" rows="2" class="form-control form-control-sm" placeholder="Twitter description..."></textarea>
                                <div class="invalid-feedback d-block" id="err-twitter_description"></div>
                            </div>
                            <div class="form-group mb-0">
                                <x-backoffice.media-picker
                                    name="twitter_image"
                                    input-id="seo-twitter-image"
                                    label="Twitter Share Image"
                                    choose-label="Choose Twitter image..."
                                    remove-name="remove_twitter_image"
                                    media-id-name="twitter_image_media_id"
                                    picker-url="{{ route('admin.media.picker') }}"
                                />
                                <div class="invalid-feedback d-block" id="err-twitter_image"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitProductForm">
                        <i class="fas fa-save mr-1"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('backoffice.admin.media.partials.picker-modal')