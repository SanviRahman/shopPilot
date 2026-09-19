<div class="modal fade" id="permissionShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="permissionShowModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold" id="permissionShowModalTitle">
                    <i class="fas fa-key text-info mr-2"></i>Permission Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="avatar bg-light border text-info font-weight-bold rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 55px; height: 55px; font-size: 22px;">
                        <i class="fas fa-key"></i>
                    </div>
                    <h5 class="font-weight-bold mb-0" id="showPermName"></h5>
                    <p class="text-muted small mb-0" id="showPermId"></p>
                </div>
                <ul class="list-group list-group-flush border-top">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Group:</span>
                        <span class="font-weight-bold" id="showPermGroup"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Guard:</span>
                        <span class="badge badge-secondary text-uppercase" id="showPermGuard"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Created:</span>
                        <span class="text-dark" id="showPermCreated"></span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>