<div class="modal fade" id="customerFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="customerAjaxForm" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="customerFormMethod" value="POST">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user text-primary mr-2"></i><span id="customerFormTitle">Create Customer</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="customer-name" name="name" class="form-control" required>
                            <div class="invalid-feedback d-block" id="err-name"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="customer-email" name="email" class="form-control" required>
                            <div class="invalid-feedback d-block" id="err-email"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Password <span class="text-danger customer-pwd-required">*</span></label>
                            <input type="password" id="customer-password" name="password" class="form-control">
                            <small class="form-text text-muted d-none" id="customerPwdHint">Leave blank to keep the existing password.</small>
                            <div class="invalid-feedback d-block" id="err-password"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Confirm Password <span class="text-danger customer-pwd-required">*</span></label>
                            <input type="password" id="customer-password-confirmation" name="password_confirmation" class="form-control">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label>Account Status <span class="text-danger">*</span></label>
                            <select id="customer-status" name="status" class="custom-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback d-block" id="err-status"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitCustomer"><i class="fas fa-save mr-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
