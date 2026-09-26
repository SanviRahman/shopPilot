<div class="modal fade" id="orderShowModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="orderShowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title font-weight-bold" id="orderShowModalLabel">
                    <i class="fas fa-file-invoice text-info mr-2"></i>Order Details: <span id="show-order-number" class="text-primary font-monospace"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted font-weight-bold text-uppercase small mb-2"><i class="fas fa-user mr-1"></i> Buyer Information</h6>
                        <div class="border rounded p-3 bg-light">
                            <p class="mb-1 font-weight-bold text-dark" id="show-buyer-name"></p>
                            <p class="mb-1 text-muted small"><i class="fas fa-phone mr-1"></i> <span id="show-buyer-phone"></span></p>
                            <p class="mb-1 text-muted small"><i class="fas fa-envelope mr-1"></i> <span id="show-buyer-email"></span></p>
                            <p class="mb-0 text-muted small"><i class="fas fa-map-marker-alt mr-1"></i> <span id="show-shipping-address"></span>, <span id="show-city-or-area"></span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted font-weight-bold text-uppercase small mb-2"><i class="fas fa-info-circle mr-1"></i> Order Summary</h6>
                        <div class="border rounded p-3 bg-light">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Order Status:</span>
                                <span id="show-order-badge"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Payment Status:</span>
                                <span id="show-payment-badge"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Assigned Agent:</span>
                                <span class="font-weight-bold text-dark small" id="show-agent-name"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Date Placed:</span>
                                <span class="text-dark small" id="show-created-at"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted font-weight-bold text-uppercase small mb-2"><i class="fas fa-calculator mr-1"></i> Billing Calculation</h6>
                        <ul class="list-group list-group-flush border rounded">
                            <li class="list-group-item d-flex justify-content-between py-2 small">
                                <span>Subtotal:</span>
                                <span class="font-weight-600">৳<span id="show-subtotal"></span></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2 small">
                                <span>Discount (<span id="show-coupon-code"></span>):</span>
                                <span class="text-danger">-৳<span id="show-discount"></span></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2 small">
                                <span>Shipping:</span>
                                <span>+৳<span id="show-shipping"></span></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2 font-weight-bold bg-light">
                                <span>Grand Total:</span>
                                <span class="text-primary h6 mb-0">৳<span id="show-grand-total"></span></span>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted font-weight-bold text-uppercase small mb-2"><i class="fas fa-sticky-note mr-1"></i> Order Notes</h6>
                        <div class="border rounded p-3 h-100 bg-white">
                            <div class="mb-2">
                                <small class="font-weight-bold text-dark d-block">Customer Note:</small>
                                <small class="text-muted" id="show-customer-note"></small>
                            </div>
                            <hr class="my-2">
                            <div>
                                <small class="font-weight-bold text-dark d-block">Internal Staff Note:</small>
                                <small class="text-info" id="show-internal-note"></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>