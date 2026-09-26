<div class="modal fade" id="methodFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="methodFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="methodAjaxForm" action="" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="methodFormModalTitle">
                        <i class="fas fa-credit-card text-primary mr-2"></i><span>Add Payment Method</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">
                    <div class="row">
                        <div class="form-group col-md-6 mb-3">
                            <label for="method-name" class="font-weight-600">Method Name <span class="text-danger">*</span></label>
                            <input type="text" id="method-name" name="name" class="form-control form-control-sm" placeholder="e.g. bKash, Nagad" required>
                            <div class="invalid-feedback d-block" id="err-name"></div>
                        </div>

                        <div class="form-group col-md-6 mb-3">
                            <label for="method-code" class="font-weight-600">Method Code <span class="text-danger">*</span></label>
                            <input type="text" id="method-code" name="code" class="form-control form-control-sm" placeholder="e.g. bkash, nagad" required>
                            <div class="invalid-feedback d-block" id="err-code"></div>
                        </div>

                        <div class="form-group col-md-6 mb-3">
                            <label for="method-account_number" class="font-weight-600">Account Number <span class="text-danger">*</span></label>
                            <input type="text" id="method-account_number" name="account_number" class="form-control form-control-sm" placeholder="e.g. 01700000000" required>
                            <div class="invalid-feedback d-block" id="err-account_number"></div>
                        </div>

                        <div class="form-group col-md-6 mb-3">
                            <label for="method-account_type" class="font-weight-600">Account Type <span class="text-danger">*</span></label>
                            <input type="text" id="method-account_type" name="account_type" class="form-control form-control-sm" placeholder="e.g. Personal, Merchant" required>
                            <div class="invalid-feedback d-block" id="err-account_type"></div>
                        </div>

                        <div class="form-group col-md-12 mb-3">
                            <label for="method-instruction" class="font-weight-600">Instructions <span class="text-danger">*</span></label>
                            <textarea id="method-instruction" name="instruction" class="form-control form-control-sm" rows="3" placeholder="Steps for buyers to submit payment details..." required></textarea>
                            <div class="invalid-feedback d-block" id="err-instruction"></div>
                        </div>

                        <div class="form-group col-md-6 mb-3">
                            <label for="method-status" class="font-weight-600">Status <span class="text-danger">*</span></label>
                            <select id="method-status" name="status" class="custom-select custom-select-sm" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback d-block" id="err-status"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitForm">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>