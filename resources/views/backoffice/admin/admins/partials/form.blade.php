<div class="modal fade" id="adminFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="adminFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="adminAjaxForm" action="" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="adminFormModalTitle">
                        <i class="fas fa-user-shield text-primary mr-2"></i><span>Create Admin</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">
                    <div id="modalAlertContainer"></div>

                    <div class="row">
                        <div class="form-group col-md-6 mb-3">
                            <label for="admin-name" class="font-weight-600">Name <span class="text-danger">*</span></label>
                            <input type="text" id="admin-name" name="name" class="form-control form-control-sm" placeholder="Full Name" required>
                            <div class="invalid-feedback d-block" id="err-name"></div>
                        </div>

                        <div class="form-group col-md-6 mb-3">
                            <label for="admin-email" class="font-weight-600">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="admin-email" name="email" class="form-control form-control-sm" placeholder="email@example.com" required>
                            <div class="invalid-feedback d-block" id="err-email"></div>
                        </div>

                        <div class="form-group col-md-6 mb-3">
                            <label for="admin-password" class="font-weight-600">Password <span class="text-danger pwd-required">*</span></label>
                            <input type="password" id="admin-password" name="password" class="form-control form-control-sm" placeholder="Minimum 8 characters">
                            <small class="form-text text-muted d-none" id="pwdHint">Leave blank to keep existing password.</small>
                            <div class="invalid-feedback d-block" id="err-password"></div>
                        </div>

                        <div class="form-group col-md-6 mb-3">
                            <label for="admin-password-confirmation" class="font-weight-600">Confirm Password <span class="text-danger pwd-required">*</span></label>
                            <input type="password" id="admin-password-confirmation" name="password_confirmation" class="form-control form-control-sm" placeholder="Re-type password">
                        </div>

                        <div class="form-group col-md-6 mb-3">
                            <label for="admin-status" class="font-weight-600">Account Status <span class="text-danger">*</span></label>
                            <select id="admin-status" name="status" class="custom-select custom-select-sm" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback d-block" id="err-status"></div>
                        </div>

                        <div class="form-group col-12 mb-0">
                            <label class="font-weight-600">Assign Roles <span class="text-danger">*</span></label>
                            <div class="border rounded p-3 bg-light">
                                <div class="row">
                                    @foreach($roles as $role)
                                        <div class="col-md-4 col-sm-6 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input role-checkbox" id="role-{{ $role->id }}" name="roles[]" value="{{ $role->id }}">
                                                <label class="custom-control-label font-weight-normal text-dark" for="role-{{ $role->id }}">{{ $role->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="invalid-feedback d-block" id="err-roles"></div>
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