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
                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="coupon_code" class="font-weight-bold text-muted small text-uppercase">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="coupon_code" class="form-control text-uppercase" placeholder="e.g. SAVE20" required maxlength="80">
                            <span class="invalid-feedback" data-error="code"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="coupon_discount_type" class="font-weight-bold text-muted small text-uppercase">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type" id="coupon_discount_type" class="custom-select" required>
                                <option value="fixed">Fixed Amount (৳)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                            <span class="invalid-feedback" data-error="discount_type"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="coupon_discount_value" class="font-weight-bold text-muted small text-uppercase">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="discount_value" id="coupon_discount_value" class="form-control" placeholder="0.00" required>
                            <small class="form-text text-muted" id="couponDiscountHelp">Enter the fixed discount amount.</small>
                            <span class="invalid-feedback" data-error="discount_value"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="coupon_minimum_order_amount" class="font-weight-bold text-muted small text-uppercase">Minimum Order Amount (৳)</label>
                            <input type="number" step="0.01" min="0" name="minimum_order_amount" id="coupon_minimum_order_amount" class="form-control" value="0" placeholder="0.00">
                            <span class="invalid-feedback" data-error="minimum_order_amount"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="coupon_start_date" class="font-weight-bold text-muted small text-uppercase">Start Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_date" id="coupon_start_date" class="form-control" required>
                            <span class="invalid-feedback" data-error="start_date"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="coupon_end_date" class="font-weight-bold text-muted small text-uppercase">End Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_date" id="coupon_end_date" class="form-control" required>
                            <span class="invalid-feedback" data-error="end_date"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group mb-md-0">
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
