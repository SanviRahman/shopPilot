<div class="modal fade" id="historyShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="historyShowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="historyShowModalLabel">
                    <i class="fas fa-info-circle text-info mr-2"></i>Audit History Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <ul class="list-group list-group-flush border rounded mb-3">
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Associated Order:</span>
                        <span class="font-weight-bold text-primary font-monospace" id="show-order-number"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Customer Name:</span>
                        <span class="font-weight-600 text-dark" id="show-buyer-name"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Triggered By:</span>
                        <span id="show-actor-badge"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Status Transition:</span>
                        <span id="show-transition-badge"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Logged Timestamp:</span>
                        <span class="text-dark" id="show-created-at"></span>
                    </li>
                </ul>

                <h6 class="font-weight-bold text-uppercase small text-muted mb-2">Audit Remarks:</h6>
                <div class="border rounded p-3 bg-light">
                    <p class="mb-0 text-dark small" id="show-note" style="white-space: pre-wrap;"></p>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>