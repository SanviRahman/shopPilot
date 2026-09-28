<div class="modal fade" id="paymentRejectModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content border-0 shadow">
            <form id="paymentRejectForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-3">
                        <i class="fas fa-ban fa-3x"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2">Reject Payment</h5>
                    <p class="text-muted small mb-3">Please provide a reason for rejecting this payment submission.</p>
                    
                    <div class="form-group text-left mb-3">
                        <textarea name="rejection_note" id="rejection_note" class="form-control form-control-sm" rows="3" placeholder="Enter rejection reason..." required></textarea>
                    </div>

                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light btn-sm px-3 mr-2" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm px-4" id="btnSubmitReject">Reject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>