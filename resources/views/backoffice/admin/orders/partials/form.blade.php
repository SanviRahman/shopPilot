<div class="modal fade" id="orderFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="orderFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="orderAjaxForm" action="" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="orderFormMethod" value="POST">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="orderFormModalTitle">
                        <i class="fas fa-shopping-bag text-primary mr-2"></i><span>Create New Order</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">
                    <!-- Customer Information Section -->
                    <h6 class="font-weight-bold text-uppercase small text-muted border-bottom pb-2 mb-3">
                        <i class="fas fa-user mr-1"></i> Customer & Delivery Details
                    </h6>
                    <div class="row">
                        <div class="form-group col-md-4 mb-3">
                            <label for="buyer-name" class="font-weight-600">Buyer Name <span class="text-danger">*</span></label>
                            <input type="text" id="buyer-name" name="buyer_name" class="form-control form-control-sm" placeholder="Full name" required>
                            <div class="invalid-feedback d-block" id="err-buyer_name"></div>
                        </div>

                        <div class="form-group col-md-4 mb-3">
                            <label for="buyer-phone" class="font-weight-600">Buyer Phone <span class="text-danger">*</span></label>
                            <input type="text" id="buyer-phone" name="buyer_phone" class="form-control form-control-sm" placeholder="017xxxxxxxx" required>
                            <div class="invalid-feedback d-block" id="err-buyer_phone"></div>
                        </div>

                        <div class="form-group col-md-4 mb-3">
                            <label for="buyer-email" class="font-weight-600">Buyer Email <span class="text-danger">*</span></label>
                            <input type="email" id="buyer-email" name="buyer_email" class="form-control form-control-sm" placeholder="customer@example.com" required>
                            <div class="invalid-feedback d-block" id="err-buyer_email"></div>
                        </div>

                        <div class="form-group col-md-4 mb-3">
                            <label for="city-or-area" class="font-weight-600">City / Area <span class="text-danger">*</span></label>
                            <input type="text" id="city-or-area" name="city_or_area" class="form-control form-control-sm" placeholder="Dhaka, Chittagong..." required>
                            <div class="invalid-feedback d-block" id="err-city_or_area"></div>
                        </div>

                        <div class="form-group col-md-8 mb-3">
                            <label for="shipping-address" class="font-weight-600">Full Shipping Address <span class="text-danger">*</span></label>
                            <input type="text" id="shipping-address" name="shipping_address" class="form-control form-control-sm" placeholder="House, Road, Area..." required>
                            <div class="invalid-feedback d-block" id="err-shipping_address"></div>
                        </div>
                    </div>

                    <!-- Billing & Pricing Section -->
                    <h6 class="font-weight-bold text-uppercase small text-muted border-bottom pb-2 mb-3 mt-2">
                        <i class="fas fa-calculator mr-1"></i> Order Pricing (BDT)
                    </h6>
                    <div class="row bg-light rounded p-2 mb-3 mx-0 border">
                        <div class="form-group col-md-3 mb-2">
                            <label for="order-subtotal" class="font-weight-600">Subtotal <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" step="0.01" min="0" id="order-subtotal" name="subtotal" class="form-control form-control-sm calc-field" placeholder="0.00" required>
                            </div>
                            <div class="invalid-feedback d-block" id="err-subtotal"></div>
                        </div>

                        <div class="form-group col-md-3 mb-2">
                            <label for="order-discount" class="font-weight-600">Discount</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" step="0.01" min="0" id="order-discount" name="discount" class="form-control form-control-sm calc-field" value="0.00">
                            </div>
                            <div class="invalid-feedback d-block" id="err-discount"></div>
                        </div>

                        <div class="form-group col-md-3 mb-2">
                            <label for="order-shipping" class="font-weight-600">Shipping</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" step="0.01" min="0" id="order-shipping" name="shipping" class="form-control form-control-sm calc-field" value="0.00">
                            </div>
                            <div class="invalid-feedback d-block" id="err-shipping"></div>
                        </div>

                        <div class="form-group col-md-3 mb-2">
                            <label class="font-weight-600">Grand Total</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text font-weight-bold">৳</span></div>
                                <input type="text" id="order-grand-total" class="form-control form-control-sm font-weight-bold text-primary bg-white" readonly value="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Status & Assignment Section -->
                    <h6 class="font-weight-bold text-uppercase small text-muted border-bottom pb-2 mb-3 mt-2">
                        <i class="fas fa-tasks mr-1"></i> Status & Agent
                    </h6>
                    <div class="row">
                        <div class="form-group col-md-4 mb-3">
                            <label for="order-status" class="font-weight-600">Order Status <span class="text-danger">*</span></label>
                            <select id="order-status" name="order_status" class="custom-select custom-select-sm" required>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
                                <option value="failed">Failed</option>
                            </select>
                            <div class="invalid-feedback d-block" id="err-order_status"></div>
                        </div>

                        <div class="form-group col-md-4 mb-3">
                            <label for="payment-status" class="font-weight-600">Payment Status <span class="text-danger">*</span></label>
                            <select id="payment-status" name="payment_status" class="custom-select custom-select-sm" required>
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="partially_paid">Partially Paid</option>
                                <option value="refunded">Refunded</option>
                                <option value="failed">Failed</option>
                            </select>
                            <div class="invalid-feedback d-block" id="err-payment_status"></div>
                        </div>

                        <div class="form-group col-md-4 mb-3">
                            <label for="assigned-agent-id" class="font-weight-600">Assigned Agent</label>
                            <select id="assigned-agent-id" name="assigned_agent_id" class="custom-select custom-select-sm">
                                <option value="">-- Unassigned --</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-block" id="err-assigned_agent_id"></div>
                        </div>

                        <div class="form-group col-md-6 mb-0">
                            <label for="customer-note" class="font-weight-600">Customer Note</label>
                            <textarea id="customer-note" name="customer_note" rows="2" class="form-control form-control-sm" placeholder="Special delivery instructions from buyer..."></textarea>
                            <div class="invalid-feedback d-block" id="err-customer_note"></div>
                        </div>

                        <div class="form-group col-md-6 mb-0">
                            <label for="internal-note" class="font-weight-600">Internal Audit Note (Staff Only)</label>
                            <textarea id="internal-note" name="internal_note" rows="2" class="form-control form-control-sm" placeholder="Call center/warehouse internal remarks..."></textarea>
                            <div class="invalid-feedback d-block" id="err-internal_note"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitForm">
                        <i class="fas fa-save mr-1"></i> Save Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>