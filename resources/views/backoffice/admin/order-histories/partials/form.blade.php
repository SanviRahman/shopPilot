<div class="modal fade" id="historyFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="historyFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="historyAjaxForm" action="{{ route('admin.order-histories.store') }}" method="POST" autocomplete="off">
                @csrf

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="historyFormModalTitle">
                        <i class="fas fa-history text-primary mr-2"></i><span>Add Audit Note</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-light border small text-muted py-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        This adds an append-only audit note. Change the Order status from Order Management so the transition is recorded automatically.
                    </div>

                    <div class="form-group mb-3">
                        <label for="modal_order_id" class="font-weight-600">Select Order <span class="text-danger">*</span></label>
                        <select name="order_id" id="modal_order_id" class="custom-select custom-select-sm" required>
                            <option value="">-- Choose Order --</option>
                            @foreach($orders as $ord)
                                <option value="{{ $ord->id }}">#{{ $ord->order_number }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-block" id="err-order_id"></div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="note" class="font-weight-600">Audit / Event Note <span class="text-danger">*</span></label>
                        <textarea id="note" name="note" rows="4" class="form-control form-control-sm" placeholder="Provide remarks about customer communication, courier updates, or another operational event..." required></textarea>
                        <div class="invalid-feedback d-block" id="err-note"></div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitHistoryForm">
                        <i class="fas fa-save mr-1"></i> Add Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
