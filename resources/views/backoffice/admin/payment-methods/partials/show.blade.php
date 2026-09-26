<div class="modal fade" id="methodShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="methodShowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold" id="methodShowModalLabel">
                    <i class="fas fa-credit-card text-info mr-2"></i>Payment Method Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <h5 class="font-weight-bold mb-0" id="show-name"></h5>
                    <code class="badge badge-light border mt-1" id="show-code"></code>
                </div>
                <ul class="list-group list-group-flush border-top">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Account Number:</span>
                        <span class="font-weight-bold" id="show-account_number"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Account Type:</span>
                        <span class="font-weight-bold" id="show-account_type"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Status:</span>
                        <span class="font-weight-bold" id="show-status"></span>
                    </li>
                    <li class="list-group-item px-0">
                        <span class="text-muted d-block mb-1">Instruction:</span>
                        <p class="small text-dark mb-0 bg-light p-2 rounded" id="show-instruction"></p>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Created At:</span>
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