<div class="modal fade" id="metaPixelFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="metaPixelFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <form id="metaPixelAjaxForm" action="{{ route('admin.meta-pixels.store') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="_method" id="metaPixelFormMethod" value="POST">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold" id="metaPixelFormModalTitle">
                        <i class="fab fa-facebook text-primary mr-2"></i><span>Create Meta Pixel</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card card-outline card-primary shadow-none border mb-3">
                                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                                    <h3 class="card-title font-weight-bold mb-0">Pixel IDs & Scripts</h3>
                                    <button type="button" class="btn btn-primary btn-sm" id="btnAddPixelEntry">
                                        <i class="fas fa-plus mr-1"></i> Add More
                                    </button>
                                </div>
                                <div class="card-body" id="metaPixelEntriesContainer"></div>
                                <div class="card-footer bg-light py-2">
                                    <small class="text-warning">
                                        <i class="fas fa-shield-alt mr-1"></i>
                                        Custom scripts are rendered raw on the storefront only for live Meta Pixel configurations. Keep this permission limited to trusted admins.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card card-outline card-info shadow-none border mb-0">
                                <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Configuration</h3></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="meta-pixel-name">Name *</label>
                                        <input type="text" id="meta-pixel-name" name="name" class="form-control" maxlength="120" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="meta-pixel-lifecycle">Lifecycle *</label>
                                        <select id="meta-pixel-lifecycle" name="lifecycle_status" class="custom-select" required>
                                            @foreach(\App\Models\MetaPixel::LIFECYCLE as $state)
                                                <option value="{{ $state }}">{{ ucfirst($state) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="meta-pixel-starts-at">Starts At</label>
                                        <input type="datetime-local" id="meta-pixel-starts-at" name="starts_at" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label for="meta-pixel-ends-at">Ends At</label>
                                        <input type="datetime-local" id="meta-pixel-ends-at" name="ends_at" class="form-control">
                                    </div>

                                    <div class="custom-control custom-switch mb-3">
                                        <input type="checkbox" class="custom-control-input" id="metaPixelTrackPageView" name="track_page_view" value="1">
                                        <label class="custom-control-label" for="metaPixelTrackPageView">Track PageView</label>
                                    </div>

                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="metaPixelTrackEcommerce" name="track_ecommerce" value="1">
                                        <label class="custom-control-label" for="metaPixelTrackEcommerce">Track Ecommerce Events</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitMetaPixelForm">
                        <i class="fas fa-save mr-1"></i> Save Meta Pixel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
