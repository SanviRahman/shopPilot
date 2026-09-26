<div class="modal fade" id="itemShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="itemShowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="itemShowModalLabel">
                    <i class="fas fa-info-circle text-info mr-2"></i>Order Item Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="avatar bg-light border text-primary font-weight-bold rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; font-size: 20px;">
                        <i class="fas fa-box"></i>
                    </div>
                    <h5 class="font-weight-bold mb-1" id="show-product-name"></h5>
                    <p class="text-muted small mb-0">Variant: <span id="show-variant-name" class="font-weight-600 text-dark"></span></p>
                </div>

                <ul class="list-group list-group-flush border rounded">
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Associated Order:</span>
                        <span class="font-weight-bold text-primary font-monospace" id="show-order-number"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Customer Name:</span>
                        <span class="font-weight-600 text-dark" id="show-buyer-name"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">SKU:</span>
                        <code id="show-sku"></code>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Unit Price:</span>
                        <span class="text-dark">৳<span id="show-unit-price"></span></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Quantity:</span>
                        <span class="badge badge-secondary" id="show-quantity"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 font-weight-bold bg-light">
                        <span class="text-dark">Line Subtotal:</span>
                        <span class="text-primary h6 mb-0">৳<span id="show-subtotal"></span></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 small">
                        <span class="text-muted">Added On:</span>
                        <span class="text-muted" id="show-created-at"></span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>