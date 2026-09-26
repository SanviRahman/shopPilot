<div class="modal fade" id="itemFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="itemFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="itemAjaxForm" action="" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="itemFormMethod" value="POST">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="itemFormModalTitle">
                        <i class="fas fa-box text-primary mr-2"></i><span>Add Item to Order</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">
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

                    <div class="form-group mb-3">
                        <label for="product_name" class="font-weight-600">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="product_name" name="product_name" class="form-control form-control-sm" placeholder="e.g. Classic Cotton T-Shirt" required>
                        <div class="invalid-feedback d-block" id="err-product_name"></div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6 mb-3">
                            <label for="variant_name" class="font-weight-600">Variant Name</label>
                            <input type="text" id="variant_name" name="variant_name" class="form-control form-control-sm" placeholder="e.g. Black / XL">
                            <div class="invalid-feedback d-block" id="err-variant_name"></div>
                        </div>

                        <div class="form-group col-md-6 mb-3">
                            <label for="sku" class="font-weight-600">SKU Code</label>
                            <input type="text" id="sku" name="sku" class="form-control form-control-sm" placeholder="TSHIRT-BLK-XL">
                            <div class="invalid-feedback d-block" id="err-sku"></div>
                        </div>
                    </div>

                    <div class="row bg-light rounded p-2 mb-0 border">
                        <div class="form-group col-md-4 mb-2">
                            <label for="unit_price" class="font-weight-600">Unit Price <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" step="0.01" min="0" id="unit_price" name="unit_price" class="form-control item-calc-field" placeholder="0.00" required>
                            </div>
                            <div class="invalid-feedback d-block" id="err-unit_price"></div>
                        </div>

                        <div class="form-group col-md-4 mb-2">
                            <label for="quantity" class="font-weight-600">Quantity <span class="text-danger">*</span></label>
                            <input type="number" min="1" id="quantity" name="quantity" class="form-control form-control-sm item-calc-field" value="1" required>
                            <div class="invalid-feedback d-block" id="err-quantity"></div>
                        </div>

                        <div class="form-group col-md-4 mb-2">
                            <label class="font-weight-600">Subtotal</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text font-weight-bold">৳</span></div>
                                <input type="text" id="item_subtotal" class="form-control font-weight-bold text-primary bg-white" readonly value="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitItemForm">
                        <i class="fas fa-save mr-1"></i> Save Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>