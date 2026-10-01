<div class="modal fade" id="historyFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="historyFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="historyAjaxForm" action="" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="historyFormMethod" value="POST">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="historyFormModalTitle">
                        <i class="fas fa-history text-primary mr-2"></i><span>Add Audit Note</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">
                    <!-- Create Mode: Order Selector Dropdown -->
                    <div class="form-group mb-3" id="orderSelectContainer">
                        <label for="modal_order_id" class="font-weight-600">Select Order <span class="text-danger">*</span></label>
                        <select name="order_id" id="modal_order_id" class="custom-select custom-select-sm" required>
                            <option value="">-- Choose Order --</option>
                            @foreach($orders as $ord)
                                <option value="{{ $ord->id }}">#{{ $ord->order_number }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-block" id="err-order_id"></div>
                    </div>

                    <!-- Edit Mode: Order Number Display (Read Only) -->
                    <div class="form-group mb-3 d-none" id="orderDisplayContainer">
                        <label class="font-weight-600">Order Number</label>
                        <input type="text" id="edit_order_number" class="form-control form-control-sm bg-light font-weight-bold text-primary font-monospace" readonly>
                    </div>

                    <!-- Status Transition Dropdown -->
                    <div class="form-group mb-3" id="statusTransitionContainer">
                        <label for="to_status" class="font-weight-600">Change Status To (Optional)</label>
                        <select name="to_status" id="to_status" class="custom-select custom-select-sm">
                            <option value="">-- Keep Current Status --</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <small class="form-text text-muted">Selecting a status will automatically update the parent order status.</small>
                        <div class="invalid-feedback d-block" id="err-to_status"></div>
                    </div>

                    <!-- Note / Remarks -->
                    <div class="form-group mb-0">
                        <label for="note" class="font-weight-600">Audit / Event Note <span class="text-danger">*</span></label>
                        <textarea id="note" name="note" rows="4" class="form-control form-control-sm" placeholder="Provide detailed remarks about customer communication, courier updates, or dispute reasons..." required></textarea>
                        <div class="invalid-feedback d-block" id="err-note"></div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitHistoryForm">
                        <i class="fas fa-save mr-1"></i> Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>