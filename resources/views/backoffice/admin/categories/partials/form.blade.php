<div class="modal fade" id="categoryFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="categoryFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="categoryAjaxForm" action="" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="categoryFormMethod" value="POST">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="categoryFormModalTitle">
                        <i class="fas fa-tags text-primary mr-2"></i><span>Create Category</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-3">
                    <div id="modalAlertContainer"></div>

                    {{-- Form Nav Tabs --}}
                    <ul class="nav nav-tabs mb-3" id="categoryTab" role="tablist">
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

                    <div class="tab-content" id="categoryTabContent">
                        {{-- Tab 1: General --}}
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                            <div class="row">
                                <div class="form-group col-md-6 mb-3">
                                    <label for="cat-name" class="font-weight-600">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" id="cat-name" name="name" class="form-control form-control-sm" placeholder="e.g. Mens Fashion" required>
                                    <div class="invalid-feedback d-block" id="err-name"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="cat-slug" class="font-weight-600">Slug (URL)</label>
                                    <input type="text" id="cat-slug" name="slug" class="form-control form-control-sm" placeholder="auto-generated-if-blank">
                                    <div class="invalid-feedback d-block" id="err-slug"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="cat-status" class="font-weight-600">Status <span class="text-danger">*</span></label>
                                    <select id="cat-status" name="status" class="custom-select custom-select-sm" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback d-block" id="err-status"></div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="cat-sort-order" class="font-weight-600">Sort Order</label>
                                    <input type="number" id="cat-sort-order" name="sort_order" class="form-control form-control-sm" placeholder="0" min="0">
                                    <div class="invalid-feedback d-block" id="err-sort_order"></div>
                                </div>
                                <div class="form-group col-12 mb-3">
                                    <label for="cat-description" class="font-weight-600">Description</label>
                                    <textarea id="cat-description" name="description" rows="3" class="form-control form-control-sm" placeholder="Brief category description..."></textarea>
                                    <div class="invalid-feedback d-block" id="err-description"></div>
                                </div>
                                <div class="form-group col-12 mb-0">
                                    <label class="font-weight-600">Category Image</label>
                                    <div class="custom-file mb-2">
                                        <input type="file" class="custom-file-input" id="cat-image" name="image" accept="image/*">
                                        <label class="custom-file-label custom-file-label-sm" for="cat-image">Choose image file...</label>
                                    </div>
                                    <div id="imagePreviewContainer" class="d-none mt-2">
                                        <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-height: 80px;">
                                    </div>
                                    <div class="invalid-feedback d-block" id="err-image"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 2: General SEO --}}
                        <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                            <div class="form-group mb-3">
                                <label for="seo-meta-title" class="font-weight-600">Meta Title</label>
                                <input type="text" id="seo-meta-title" name="meta_title" class="form-control form-control-sm" placeholder="Custom SEO title (fallback to category name)">
                                <div class="invalid-feedback d-block" id="err-meta_title"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="seo-meta-description" class="font-weight-600">Meta Description</label>
                                <textarea id="seo-meta-description" name="meta_description" rows="3" class="form-control form-control-sm" placeholder="Brief description for search engine results..."></textarea>
                                <div class="invalid-feedback d-block" id="err-meta_description"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="seo-meta-keywords" class="font-weight-600">Meta Keywords</label>
                                <input type="text" id="seo-meta-keywords" name="meta_keywords" class="form-control form-control-sm" placeholder="e.g. fashion, clothes, men">
                                <div class="invalid-feedback d-block" id="err-meta_keywords"></div>
                            </div>
                            <div class="form-group mb-0">
                                <label for="seo-canonical-url" class="font-weight-600">Canonical URL</label>
                                <input type="url" id="seo-canonical-url" name="canonical_url" class="form-control form-control-sm" placeholder="https://example.com/category-url">
                                <div class="invalid-feedback d-block" id="err-canonical_url"></div>
                            </div>
                        </div>

                        {{-- Tab 3: Facebook OpenGraph --}}
                        <div class="tab-pane fade" id="tab-og" role="tabpanel">
                            <div class="form-group mb-3">
                                <label for="seo-og-title" class="font-weight-600">Facebook OG Title</label>
                                <input type="text" id="seo-og-title" name="og_title" class="form-control form-control-sm" placeholder="OpenGraph title">
                                <div class="invalid-feedback d-block" id="err-og_title"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="seo-og-description" class="font-weight-600">Facebook OG Description</label>
                                <textarea id="seo-og-description" name="og_description" rows="2" class="form-control form-control-sm" placeholder="OpenGraph description..."></textarea>
                                <div class="invalid-feedback d-block" id="err-og_description"></div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-600">Facebook Share Image</label>
                                <div class="custom-file mb-2">
                                    <input type="file" class="custom-file-input" id="seo-og-image" name="og_image" accept="image/*">
                                    <label class="custom-file-label custom-file-label-sm" for="seo-og-image">Choose OG image...</label>
                                </div>
                                <div id="ogImagePreviewContainer" class="d-none mt-2">
                                    <img id="ogImagePreview" src="" alt="OG Preview" class="img-thumbnail" style="max-height: 80px;">
                                </div>
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
                                <textarea id="seo-twitter-description" name="twitter_description" rows="2" class="form-control form-control-sm" placeholder="Twitter card description..."></textarea>
                                <div class="invalid-feedback d-block" id="err-twitter_description"></div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-600">Twitter Share Image</label>
                                <div class="custom-file mb-2">
                                    <input type="file" class="custom-file-input" id="seo-twitter-image" name="twitter_image" accept="image/*">
                                    <label class="custom-file-label custom-file-label-sm" for="seo-twitter-image">Choose Twitter image...</label>
                                </div>
                                <div id="twitterImagePreviewContainer" class="d-none mt-2">
                                    <img id="twitterImagePreview" src="" alt="Twitter Preview" class="img-thumbnail" style="max-height: 80px;">
                                </div>
                                <div class="invalid-feedback d-block" id="err-twitter_image"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitCategoryForm">
                        <i class="fas fa-save mr-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>