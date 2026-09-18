<div class="modal fade" id="adminShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="adminShowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold" id="adminShowModalLabel">
                    <i class="fas fa-user-circle text-info mr-2"></i>Admin Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div id="show-avatar" class="avatar bg-primary text-white font-weight-bold rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 55px; height: 55px; font-size: 22px;"></div>
                    <h5 class="font-weight-bold mb-0" id="show-name"></h5>
                    <p class="text-muted small mb-0" id="show-email"></p>
                </div>
                <ul class="list-group list-group-flush border-top">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Account Status:</span>
                        <span class="font-weight-bold" id="show-status"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Assigned Roles:</span>
                        <span class="font-weight-bold" id="show-roles"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Joined On:</span>
                        <span class="text-dark" id="show-created"></span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>