<div class="modal fade" id="metaPixelShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="metaPixelShowModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <div>
                    <h5 class="modal-title font-weight-bold" id="metaPixelShowModalTitle">
                        <i class="fab fa-facebook text-primary mr-2"></i><span id="showMetaPixelName">Meta Pixel Details</span>
                    </h5>
                    <small class="text-muted" id="showMetaPixelMeta"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="card card-outline card-primary shadow-none border">
                            <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Pixel IDs & Scripts</h3></div>
                            <div class="card-body" id="showMetaPixelEntries"></div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card card-outline card-info shadow-none border mb-3">
                            <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Tracking Settings</h3></div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-5">Lifecycle</dt><dd class="col-7" id="showMetaPixelLifecycle"></dd>
                                    <dt class="col-5">PageView</dt><dd class="col-7" id="showMetaPixelPageView"></dd>
                                    <dt class="col-5">Ecommerce</dt><dd class="col-7" id="showMetaPixelEcommerce"></dd>
                                    <dt class="col-5">Starts</dt><dd class="col-7" id="showMetaPixelStarts"></dd>
                                    <dt class="col-5">Ends</dt><dd class="col-7" id="showMetaPixelEnds"></dd>
                                </dl>
                            </div>
                        </div>

                        <div class="card shadow-none border mb-0">
                            <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Recent Events</h3></div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead><tr><th>Event</th><th>Status</th><th>Time</th></tr></thead>
                                        <tbody id="showMetaPixelEvents"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
