<div class="modal fade" id="productShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="productShowModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold" id="productShowModalTitle">
                    <i class="fas fa-box text-primary mr-2"></i>Product Details & SEO
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div id="showProdImgWrapper" class="mr-3">
                        <img id="showProdImg" src="" alt="" class="img-thumbnail rounded" style="width: 60px; height: 60px; object-fit: cover;">
                    </div>
                    <div>
                        <h4 class="font-weight-bold mb-0 text-dark" id="showProdName"></h4>
                        <span class="text-muted small" id="showProdSku"></span>
                    </div>
                    <div class="ml-auto text-right">
                        <span id="showProdStatus"></span>
                        <div class="small text-muted mt-1">Price: <strong id="showProdPrice"></strong></div>
                    </div>
                </div>

                <div class="card card-outline card-secondary mb-3 shadow-none border">
                    <div class="card-header bg-white py-2">
                        <span class="font-weight-600 text-dark small text-uppercase"><i class="fas fa-globe text-primary mr-1"></i> Search Engine (SEO) Preview</span>
                    </div>
                    <div class="card-body p-3 bg-light">
                        <h6 class="text-primary mb-1 font-weight-bold" id="showSeoTitle"></h6>
                        <small class="text-success d-block mb-1" id="showSeoCanonical"></small>
                        <p class="text-muted small mb-1" id="showSeoDesc"></p>
                        <small class="text-muted">Keywords: <span id="showSeoKeywords" class="font-weight-bold"></span></small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="border rounded p-3 h-100 bg-white shadow-xs">
                            <span class="badge badge-light border text-primary mb-2"><i class="fab fa-facebook mr-1"></i> Facebook OpenGraph</span>
                            <div class="font-weight-bold small mb-1" id="showOgTitle"></div>
                            <div class="text-muted small mb-2" id="showOgDesc"></div>
                            <img id="showOgImage" src="" alt="" class="img-fluid rounded d-none" style="max-height: 100px;">
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="border rounded p-3 h-100 bg-white shadow-xs">
                            <span class="badge badge-light border text-info mb-2"><i class="fab fa-twitter mr-1"></i> Twitter Card</span>
                            <div class="font-weight-bold small mb-1" id="showTwitterTitle"></div>
                            <div class="text-muted small mb-2" id="showTwitterDesc"></div>
                            <img id="showTwitterImage" src="" alt="" class="img-fluid rounded d-none" style="max-height: 100px;">
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