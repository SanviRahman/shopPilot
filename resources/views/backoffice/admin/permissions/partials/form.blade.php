<div class="modal fade" id="permissionFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="permissionFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="permissionAjaxForm" action="" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="permissionFormMethod" value="POST">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="permissionFormModalTitle">
                        <i class="fas fa-key text-primary mr-2"></i><span>Create Permission</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">
                    <div id="modalAlertContainer"></div>

                    <div class="form-group mb-3">
                        <label for="permission-name" class="font-weight-600">Permission Name (Slug format) <span class="text-danger">*</span></label>
                        <input type="text" id="permission-name" name="name" class="form-control form-control-sm" placeholder="e.g. staff.create, reports.view" required>
                        <small class="text-muted">Allowed lowercase letters, numbers, hyphens, and dots.</small>
                        <div class="invalid-feedback d-block" id="err-name"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="permission-group-name" class="font-weight-600">Group Name</label>
                        <input type="text" id="permission-group-name" name="group_name" class="form-control form-control-sm" placeholder="e.g. Staff Management, Products" list="existingGroups">
                        <datalist id="existingGroups">
                            @foreach($groups ?? [] as $grp)
                                <option value="{{ $grp }}">
                            @endforeach
                        </datalist>
                        <small class="text-muted">Used for grouping permissions together in role manager.</small>
                        <div class="invalid-feedback d-block" id="err-group_name"></div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="permission-guard" class="font-weight-600">Guard Name <span class="text-danger">*</span></label>
                        <input type="text" id="permission-guard" name="guard_name" value="admin" class="form-control form-control-sm bg-light" readonly required>
                        <div class="invalid-feedback d-block" id="err-guard_name"></div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitPermissionForm">
                        <i class="fas fa-save mr-1"></i> Save Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>