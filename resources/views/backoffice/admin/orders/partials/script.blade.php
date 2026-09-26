<script>
$(function () {
    const fetchUrl = '{{ $fetchUrl }}';

    function showToast(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: type,
                title: message
            });
        } else {
            alert(message);
        }
    }

    // Live Calculation for Grand Total
    function calculateGrandTotal() {
        const subtotal = parseFloat($('#order-subtotal').val()) || 0;
        const discount = parseFloat($('#order-discount').val()) || 0;
        const shipping = parseFloat($('#order-shipping').val()) || 0;

        const grandTotal = Math.max(0, subtotal - discount + shipping);
        $('#order-grand-total').val(grandTotal.toFixed(2));
    }

    $(document).on('input', '.calc-field', function () {
        calculateGrandTotal();
    });

    // Load Table Content via AJAX
    function reloadTable(url = fetchUrl) {
        $('#tableOverlay').removeClass('d-none');
        const formData = $('#filterForm').serialize();

        $.ajax({
            url: url,
            method: 'GET',
            data: formData,
            dataType: 'json',
            success: function (res) {
                $('#tableContainer').html(res.html);
                $('#paginationContainer').html(res.pagination);
            },
            error: function () {
                showToast('Failed to load orders data. Try again.', 'error');
            },
            complete: function () {
                $('#tableOverlay').addClass('d-none');
            }
        });
    }

    // Live Debounce Search
    let searchTimeout = null;
    $('#search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { reloadTable(); }, 400);
    });

    $('#order_status, #payment_status, #agent_id').on('change', function () {
        reloadTable();
    });

    $('#btnResetFilter').on('click', function () {
        $('#filterForm')[0].reset();
        reloadTable();
    });

    // Pagination Click Intercept
    $(document).on('click', '#paginationContainer a.page-link', function (e) {
        e.preventDefault();
        const pageUrl = $(this).attr('href');
        if (pageUrl) {
            reloadTable(pageUrl);
        }
    });

    // Check All Checkboxes
    $(document).on('change', '[data-select-all]', function () {
        const target = $(this).data('selectAll');
        $(`${target} [data-row-checkbox]`).prop('checked', $(this).is(':checked'));
    });

    function clearFormErrors() {
        $('.invalid-feedback').text('');
        $('.form-control, .custom-select').removeClass('is-invalid');
    }

    // Open Create Order Modal
    $('#btnCreateOrder').on('click', function (e) {
        e.preventDefault();
        clearFormErrors();
        $('#orderAjaxForm')[0].reset();
        $('#orderFormMethod').val('POST');
        $('#orderAjaxForm').attr('action', '{{ route("admin.orders.store") }}');
        $('#orderFormModalTitle span').text('Create New Order');
        $('#order-subtotal').val('0.00');
        $('#order-discount').val('0.00');
        $('#order-shipping').val('0.00');
        $('#order-grand-total').val('0.00');
        $('#order-status').val('pending');
        $('#payment-status').val('unpaid');
        $('#assigned-agent-id').val('');
        $('#orderFormModal').modal('show');
    });

    // Open Edit Order Modal via AJAX
    $(document).on('click', '.btn-edit-order', function (e) {
        e.preventDefault();
        const orderId = $(this).data('id');
        clearFormErrors();

        $.ajax({
            url: `/admin/orders/${orderId}/edit`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const order = res.order;
                    $('#orderAjaxForm')[0].reset();
                    $('#orderFormMethod').val('PUT');
                    $('#orderAjaxForm').attr('action', `/admin/orders/${orderId}`);
                    $('#orderFormModalTitle span').text('Update Order #' + order.order_number);

                    $('#buyer-name').val(order.buyer_name);
                    $('#buyer-phone').val(order.buyer_phone);
                    $('#buyer-email').val(order.buyer_email);
                    $('#shipping-address').val(order.shipping_address);
                    $('#city-or-area').val(order.city_or_area);

                    $('#order-subtotal').val(order.subtotal);
                    $('#order-discount').val(order.discount);
                    $('#order-shipping').val(order.shipping);
                    $('#order-grand-total').val(order.grand_total);

                    $('#order-status').val(order.order_status);
                    $('#payment-status').val(order.payment_status);
                    $('#assigned-agent-id').val(order.assigned_agent_id);
                    $('#customer-note').val(order.customer_note);
                    $('#internal-note').val(order.internal_note);

                    calculateGrandTotal();
                    $('#orderFormModal').modal('show');
                }
            },
            error: function () {
                showToast('Failed to retrieve order details.', 'error');
            }
        });
    });

    // Submit Form (Create / Edit) via AJAX
    $('#orderAjaxForm').on('submit', function (e) {
        e.preventDefault();
        clearFormErrors();

        const form = $(this);
        const submitBtn = $('#btnSubmitForm');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#orderFormModal').modal('hide');
                    showToast(res.message, 'success');
                    reloadTable();
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, messages) {
                        $(`#err-${key}`).text(messages[0]);
                        $(`[name="${key}"]`).addClass('is-invalid');
                    });
                } else {
                    showToast(xhr.responseJSON?.message || 'Something went wrong.', 'error');
                }
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Order');
            }
        });
    });

    // View Order Details Modal
    $(document).on('click', '.btn-view-order', function (e) {
        e.preventDefault();
        const orderId = $(this).data('id');

        $.ajax({
            url: `/admin/orders/${orderId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const o = res.order;
                    $('#show-order-number').text(o.order_number);
                    $('#show-buyer-name').text(o.buyer_name + (o.is_guest ? ' (Guest)' : ''));
                    $('#show-buyer-phone').text(o.buyer_phone);
                    $('#show-buyer-email').text(o.buyer_email);
                    $('#show-shipping-address').text(o.shipping_address);
                    $('#show-city-or-area').text(o.city_or_area);

                    $('#show-order-badge').html(o.order_badge);
                    $('#show-payment-badge').html(o.payment_badge);
                    $('#show-agent-name').text(o.agent_name);
                    $('#show-created-at').text(o.created_at);

                    $('#show-subtotal').text(o.subtotal);
                    $('#show-discount').text(o.discount);
                    $('#show-shipping').text(o.shipping);
                    $('#show-grand-total').text(o.grand_total);
                    $('#show-coupon-code').text(o.coupon_code);

                    $('#show-customer-note').text(o.customer_note);
                    $('#show-internal-note').text(o.internal_note);

                    $('#orderShowModal').modal('show');
                }
            },
            error: function () {
                showToast('Could not load order details.', 'error');
            }
        });
    });

    // Confirmation Modal (Trash, Restore, Force Delete)
    let pendingAction = null;

    $(document).on('click', '.btn-action', function (e) {
        e.preventDefault();
        const btn = $(this);
        pendingAction = {
            url: btn.data('url'),
            method: btn.data('method'),
            data: { _token: '{{ csrf_token() }}' }
        };

        $('#confirmModalTitle').text(btn.data('confirm-title') || 'Are you sure?');
        $('#confirmModalText').text(btn.data('confirm-text') || 'This action cannot be undone.');
        $('#confirmModal').modal('show');
    });

    $('#confirmModalBtn').on('click', function () {
        if (!pendingAction) return;

        const confirmBtn = $(this);
        confirmBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

        $.ajax({
            url: pendingAction.url,
            method: pendingAction.method,
            data: pendingAction.data,
            dataType: 'json',
            success: function (res) {
                $('#confirmModal').modal('hide');
                showToast(res.message, 'success');
                reloadTable();
            },
            error: function (xhr) {
                $('#confirmModal').modal('hide');
                showToast(xhr.responseJSON?.message || 'Operation failed.', 'error');
            },
            complete: function () {
                confirmBtn.prop('disabled', false).text('Confirm');
                pendingAction = null;
            }
        });
    });

    // Bulk Actions via AJAX
    $('#bulkActionForm').on('submit', function (e) {
        e.preventDefault();
        const action = $(this).find('[name="action"]').val();
        const selected = $('[data-row-checkbox]:checked').map(function () {
            return $(this).val();
        }).get();

        if (!action || selected.length === 0) {
            showToast('Please select at least one order and choose a bulk action.', 'error');
            return;
        }

        const labels = {
            'delete': 'move selected orders to trash',
            'restore': 'restore selected orders',
            'force-delete': 'permanently delete selected orders'
        };

        pendingAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                order_ids: selected
            }
        };

        $('#confirmModalTitle').text('Confirm Bulk Action');
        $('#confirmModalText').text(`Are you sure you want to ${labels[action] || 'process'}?`);
        $('#confirmModal').modal('show');
    });
});
</script>