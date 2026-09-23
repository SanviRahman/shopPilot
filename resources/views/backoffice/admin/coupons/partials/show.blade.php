<div class="modal fade" id="showCouponModal" tabindex="-1" role="dialog" aria-labelledby="showCouponModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="showCouponModalLabel">
                    <i class="fas fa-percent text-primary mr-2"></i> Coupon Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label class="text-muted small text-uppercase font-weight-bold">Coupon Code</label>
                        <p id="modal-coupon-code" class="font-weight-bold text-dark h5 mb-0"></p>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-muted small text-uppercase font-weight-bold">Status</label>
                        <p class="mb-0"><span id="modal-coupon-status" class="badge"></span></p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-4 form-group">
                        <label class="text-muted small text-uppercase font-weight-bold">Discount Type</label>
                        <p id="modal-coupon-type" class="mb-0 text-dark"></p>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="text-muted small text-uppercase font-weight-bold">Value</label>
                        <p id="modal-coupon-value" class="mb-0 text-dark font-weight-bold"></p>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="text-muted small text-uppercase font-weight-bold">Min Order Amount</label>
                        <p id="modal-coupon-min-order" class="mb-0 text-dark"></p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label class="text-muted small text-uppercase font-weight-bold">Expires At</label>
                        <p id="modal-coupon-expires" class="text-muted mb-0"></p>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-muted small text-uppercase font-weight-bold">Created At</label>
                        <p id="modal-coupon-created" class="text-muted mb-0"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>