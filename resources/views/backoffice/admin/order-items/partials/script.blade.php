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

            Toast.fire({ icon: type, title: message });
            return;
        }

        alert(message);
    }

    function calculateItemLineTotal() {
        const unitPrice = parseFloat($('#unit_price').val()) || 0;
        const quantity = parseInt($('#quantity').val(), 10) || 0;
        const lineTotal = Math.max(0, unitPrice * quantity);
        $('#item_line_total').val(lineTotal.toFixed(2));
    }

    function syncProductPreview(updatePrice = true) {
        const option = $('#product_id option:selected');
        const productId = option.val();

        if (!productId) {
            $('#product_name_preview').val('');
            $('#sku_preview').val('');
            if (updatePrice) {
                $('#unit_price').val('0.00');
            }
            calculateItemLineTotal();
            return;
        }

        $('#product_name_preview').val(option.data('name') || option.text().trim());
        $('#sku_preview').val(option.data('sku') || '');

        if (updatePrice) {
            const price = parseFloat(option.data('price')) || 0;
            $('#unit_price').val(price.toFixed(2));
        }

        calculateItemLineTotal();
    }

    $(document).on('input', '.item-calc-field', calculateItemLineTotal);
    $(document).on('change', '#product_id', function () {
        syncProductPreview(true);
    });

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

    let searchTimeout = null;
    $('#search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => reloadTable(), 400);
    });

    $('#order_id').on('change', function () {
        reloadTable();
    });

    $('#btnResetFilter').on('click', function () {
        $('#filterForm')[0].reset();
        reloadTable();
    });

    $(document).on('click', '#paginationContainer a.page-link', function (e) {
        e.preventDefault();
        const pageUrl = $(this).attr('href');
        if (pageUrl) {
            reloadTable(pageUrl);
        }
    });

    $(document).on('change', '[data-select-all]', function () {
        const target = $(this).data('selectAll');
        $(`${target} [data-row-checkbox]`).prop('checked', $(this).is(':checked'));
    });

    function clearFormErrors() {
        $('.invalid-feedback').text('');
        $('.form-control, .custom-select').removeClass('is-invalid');
    }

    $('#btnCreateItem').on('click', function (e) {
        e.preventDefault();
        clearFormErrors();
        $('#itemAjaxForm')[0].reset();
        $('#itemFormMethod').val('POST');
        $('#itemAjaxForm').attr('action', '{{ route("admin.order-items.store") }}');
        $('#itemFormModalTitle span').text('Add Item to Order');
        $('#orderSelectContainer').removeClass('d-none');
        $('#modal_order_id').prop('required', true);
        $('#product_id').val('');
        $('#product_name_preview').val('');
        $('#sku_preview').val('');
        $('#unit_price').val('0.00');
        $('#quantity').val('1');
        $('#item_line_total').val('0.00');
        $('#itemFormModal').modal('show');
    });

    $(document).on('click', '.btn-edit-item', function (e) {
        e.preventDefault();
        const itemId = $(this).data('id');
        clearFormErrors();

        $.ajax({
            url: `/admin/order-items/${itemId}/edit`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    return;
                }

                const item = res.item;
                $('#itemAjaxForm')[0].reset();
                $('#itemFormMethod').val('PUT');
                $('#itemAjaxForm').attr('action', `/admin/order-items/${itemId}`);
                $('#itemFormModalTitle span').text('Edit Item: ' + item.product_name);

                $('#orderSelectContainer').addClass('d-none');
                $('#modal_order_id').prop('required', false);

                if ($(`#product_id option[value="${item.product_id}"]`).length === 0) {
                    $('#product_id').append(
                        $('<option>', {
                            value: item.product_id,
                            text: `${item.product_name} (${item.sku})`
                        })
                        .attr('data-name', item.product_name)
                        .attr('data-sku', item.sku)
                        .attr('data-price', item.unit_price)
                    );
                }

                $('#product_id').val(String(item.product_id));
                syncProductPreview(false);
                $('#unit_price').val(parseFloat(item.unit_price || 0).toFixed(2));
                $('#quantity').val(item.quantity);
                calculateItemLineTotal();

                $('#itemFormModal').modal('show');
            },
            error: function () {
                showToast('Failed to retrieve item details.', 'error');
            }
        });
    });

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
                    const errors = xhr.responseJSON?.errors || {};
                    $.each(errors, function (key, messages) {
                        $(`#err-${key}`).text(messages[0]);
                        $(`[name="${key}"]`).addClass('is-invalid');
                    });
                    return;
                }

                showToast(xhr.responseJSON?.message || 'Something went wrong.', 'error');
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Item');
            }
        });
    });

    $(document).on('click', '.btn-view-item', function (e) {
        e.preventDefault();
        const itemId = $(this).data('id');

        $.ajax({
            url: `/admin/order-items/${itemId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    return;
                }

                const item = res.item;
                $('#show-product-name').text(item.product_name);
                $('#show-order-number').text(item.order_number);
                $('#show-buyer-name').text(item.buyer_name);
                $('#show-sku').text(item.sku);
                $('#show-unit-price').text(item.unit_price);
                $('#show-quantity').text(item.quantity);
                $('#show-line-total').text(item.line_total);
                $('#show-created-at').text(item.created_at);
                $('#itemShowModal').modal('show');
            },
            error: function () {
                showToast('Could not load item details.', 'error');
            }
        });
    });

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
        if (!pendingAction) {
            return;
        }

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
        $('#confirmModalText').text(`Are you sure you want to ${labels[action] || 'process selected items'}?`);
        $('#confirmModal').modal('show');
    });
});
</script>
