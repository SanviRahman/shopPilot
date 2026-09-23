<div class="modal fade" id="couponFormModal" tabindex="-1" role="dialog" aria-labelledby="couponFormModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="couponForm" method="POST" action="{{ route('admin.coupons.store') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="couponFormMethod" value="POST">
                
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="couponFormModalLabel">
                        <i class="fas fa-percent text-primary mr-2"></i> <span id="couponModalTitle">Add New Coupon</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Form fields here -->
                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="coupon_code" class="font-weight-bold text-muted small text-uppercase">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="coupon_code" class="form-control" placeholder="e.g. SAVE20" required maxlength="50">
                            <span class="invalid-feedback" data-error="code"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="coupon_type" class="font-weight-bold text-muted small text-uppercase">Discount Type <span class="text-danger">*</span></label>
                            <select name="type" id="coupon_type" class="custom-select" required>
                                <option value="fixed">Fixed Amount (৳)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                            <span class="invalid-feedback" data-error="type"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="coupon_value" class="font-weight-bold text-muted small text-uppercase">Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="value" id="coupon_value" class="form-control" placeholder="0.00" required>
                            <span class="invalid-feedback" data-error="value"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="coupon_min_order" class="font-weight-bold text-muted small text-uppercase">Min Order Amount (৳)</label>
                            <input type="number" step="0.01" min="0" name="min_order_amount" id="coupon_min_order" class="form-control" placeholder="Optional">
                            <span class="invalid-feedback" data-error="min_order_amount"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="coupon_expires" class="font-weight-bold text-muted small text-uppercase">Expires At</label>
                            <input type="datetime-local" name="expires_at" id="coupon_expires" class="form-control">
                            <span class="invalid-feedback" data-error="expires_at"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="coupon_status" class="font-weight-bold text-muted small text-uppercase">Status <span class="text-danger">*</span></label>
                            <select name="status" id="coupon_status" class="custom-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="expired">Expired</option>
                            </select>
                            <span class="invalid-feedback" data-error="status"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="couponSubmitBtn">Save Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>