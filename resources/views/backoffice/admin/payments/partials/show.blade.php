<div class="modal fade" id="paymentShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-receipt text-info mr-2"></i>Payment Submission Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Order Number:</span>
                        <span class="font-weight-bold text-dark" id="show-order-number"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Payment Method:</span>
                        <span class="font-weight-bold text-uppercase" id="show-method"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Transaction ID:</span>
                        <span class="font-monospace text-primary font-weight-bold" id="show-trx-id"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Amount:</span>
                        <span class="font-weight-bold text-success" id="show-amount"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Status:</span>
                        <span id="show-status"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Verified By:</span>
                        <span class="text-dark" id="show-verifier"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Verified At:</span>
                        <span class="text-dark" id="show-verified-at"></span>
                    </li>
                    <li class="list-group-item flex-column align-items-start px-0 d-none" id="rejectionNoteContainer">
                        <span class="text-muted mb-1">Rejection Note:</span>
                        <p class="text-danger small bg-light p-2 rounded mb-0 w-100" id="show-rejection-note"></p>
                    </li>
                </ul>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>