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

    // Live Calculation for Subtotal
    function calculateItemSubtotal() {
        const unitPrice = parseFloat($('#unit_price').val()) || 0;
        const qty = parseInt($('#quantity').val()) || 0;
        const subtotal = Math.max(0, unitPrice * qty);
        $('#item_subtotal').val(subtotal.toFixed(2));
    }

    $(document).on('input', '.item-calc-field', function () {
        calculateItemSubtotal();
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
                showToast('Failed to load items. Try again.', 'error');
            },
            complete: function () {
                $('#tableOverlay').addClass('d-none');
            }
        });
    }

    // Debounced Search & Filter
    let searchTimeout = null;
    $('#search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { reloadTable(); }, 400);
    });

    $('#order_id').on('change', function () {
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

    // Open Create Modal
    $('#btnCreateItem').on('click', function (e) {
        e.preventDefault();
        clearFormErrors();
        $('#itemAjaxForm')[0].reset();
        $('#itemFormMethod').val('POST');
        $('#itemAjaxForm').attr('action', '{{ route("admin.order-items.store") }}');
        $('#itemFormModalTitle span').text('Add Item to Order');
        $('#orderSelectContainer').removeClass('d-none');
        $('#modal_order_id').prop('required', true);
        $('#unit_price').val('0.00');
        $('#quantity').val('1');
        $('#item_subtotal').val('0.00');
        $('#itemFormModal').modal('show');
    });

    // Open Edit Modal via AJAX
    $(document).on('click', '.btn-edit-item', function (e) {
        e.preventDefault();
        const itemId = $(this).data('id');
        clearFormErrors();

        $.ajax({
            url: `/admin/order-items/${itemId}/edit`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const item = res.item;
                    $('#itemAjaxForm')[0].reset();
                    $('#itemFormMethod').val('PUT');
                    $('#itemAjaxForm').attr('action', `/admin/order-items/${itemId}`);
                    $('#itemFormModalTitle span').text('Edit Item: ' + item.product_name);

                    $('#orderSelectContainer').addClass('d-none');
                    $('#modal_order_id').prop('required', false);

                    $('#product_name').val(item.product_name);
                    $('#variant_name').val(item.variant_name);
                    $('#sku').val(item.sku);
                    $('#unit_price').val(item.unit_price);
                    $('#quantity').val(item.quantity);

                    calculateItemSubtotal();
                    $('#itemFormModal').modal('show');
                }
            },
            error: function () {
                showToast('Failed to retrieve item details.', 'error');
            }
        });
    });

    // Submit Create/Edit Form via AJAX
    $('#itemAjaxForm').on('submit', function (e) {
        e.preventDefault();
        clearFormErrors();

        const form = $(this);
        const submitBtn = $('#btnSubmitItemForm');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#itemFormModal').modal('hide');
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
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Item');
            }
        });
    });

    // View Item Details via AJAX
    $(document).on('click', '.btn-view-item', function (e) {
        e.preventDefault();
        const itemId = $(this).data('id');

        $.ajax({
            url: `/admin/order-items/${itemId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const item = res.item;
                    $('#show-product-name').text(item.product_name);
                    $('#show-variant-name').text(item.variant_name);
                    $('#show-order-number').text(item.order_number);
                    $('#show-buyer-name').text(item.buyer_name);
                    $('#show-sku').text(item.sku);
                    $('#show-unit-price').text(item.unit_price);
                    $('#show-quantity').text(item.quantity);
                    $('#show-subtotal').text(item.subtotal);
                    $('#show-created-at').text(item.created_at);

                    $('#itemShowModal').modal('show');
                }
            },
            error: function () {
                showToast('Could not load item details.', 'error');
            }
        });
    });

    // Universal Action Confirmation Modal (Delete, Restore, Force Delete)
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
            showToast('Please select at least one item and choose an action.', 'error');
            return;
        }

        const labels = {
            'delete': 'move selected items to trash and update order totals',
            'restore': 'restore selected items and adjust order totals',
            'force-delete': 'permanently delete selected items'
        };

        pendingAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                item_ids: selected
            }
        };

        $('#confirmModalTitle').text('Confirm Bulk Action');
        $('#confirmModalText').text(`Are you sure you want to ${labels[action] || 'process'}?`);
        $('#confirmModal').modal('show');
    });
});
</script>