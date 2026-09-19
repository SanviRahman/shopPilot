<div class="modal fade" id="roleShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="roleShowModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold" id="roleShowModalTitle">
                    <i class="fas fa-shield-alt text-primary mr-2"></i>Role Details & Permissions
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3 pb-3 border-bottom">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Role Name</small>
                        <h5 class="font-weight-bold mb-0 text-dark" id="showRoleName"></h5>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Guard</small>
                        <span class="badge badge-light border text-uppercase" id="showRoleGuard"></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Assigned Staff</small>
                        <span class="badge badge-success" id="showRoleUsersCount"></span>
                    </div>
                    <div class="col-md-2 text-md-right">
                        <small class="text-muted d-block">Protection</small>
                        <span id="showRoleProtected"></span>
                    </div>
                </div>

                <label class="font-weight-600 mb-2">Attached Permissions (<span id="showRolePermsCount">0</span>)</label>
                <div id="showRolePermsList" class="border rounded p-3 bg-light d-flex flex-wrap gap-1" style="max-height: 300px; overflow-y: auto;">
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>