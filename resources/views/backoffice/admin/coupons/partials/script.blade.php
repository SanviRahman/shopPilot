<script>
(function ($) {
    'use strict';

    const fetchUrl = '{{ $fetchUrl ?? request()->url() }}';

    function showAlert(message, type = 'success') {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            const Toast = window.Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', window.Swal.stopTimer);
                    toast.addEventListener('mouseleave', window.Swal.resumeTimer);
                }
            });

            Toast.fire({
                icon: type,
                title: message
            });
            return;
        }
        alert(message);
    }

    function confirmAction(options, callback) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: options.title || 'Are you sure?',
                text: options.text || '',
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: options.confirmText || 'Yes, continue',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed || result.value) {
                    callback();
                }
            });
            return;
        }

        if (window.confirm(options.text || 'Are you sure?')) {
            callback();
        }
    }

    function reloadCouponTable(url = fetchUrl) {
        $('#couponTableOverlay').removeClass('d-none');
        const formData = $('#couponFilterForm').serialize();

        $.ajax({
            url: url,
            method: 'GET',
            data: formData,
            dataType: 'json',
            success: function (res) {
                $('#couponTableContainer').html(res.html);
                if (res.pagination) {
                    $('#paginationContainer').html(res.pagination).show();
                } else {
                    $('#paginationContainer').html('').hide();
                }
            },
            error: function () {
                showAlert('Failed to refresh coupons list.', 'error');
            },
            complete: function () {
                $('#couponTableOverlay').addClass('d-none');
                $('#couponSelectAll').prop('checked', false);
            }
        });
    }

    // Reset Form Modal state
    function resetCouponForm() {
        const $form = $('#couponForm');
        $form[0].reset();
        $('#couponFormMethod').val('POST');
        $form.attr('action', '{{ route('admin.coupons.store') }}');
        $('#couponModalTitle').text('Add New Coupon');
        $('#couponSubmitBtn').text('Save Coupon');
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');
    }

    $(document).on('click', '#btnAddCoupon', function () {
        resetCouponForm();
        // Static Backdrop Modal Open
        $('#couponFormModal').modal({
            backdrop: 'static',
            keyboard: false
        });
    });

    $(document).off('submit', '#couponFilterForm').on('submit', '#couponFilterForm', function (e) {
        e.preventDefault();
        reloadCouponTable(fetchUrl);
    });

    let searchTimeout = null;
    $(document).off('input', '#couponFilterForm input[name="search"]').on('input', '#couponFilterForm input[name="search"]', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            reloadCouponTable(fetchUrl);
        }, 400);
    });

    $(document).off('change', '#couponFilterForm select[name="status"]').on('change', '#couponFilterForm select[name="status"]', function () {
        reloadCouponTable(fetchUrl);
    });

    $(document).off('click', '#btnResetFilter').on('click', '#btnResetFilter', function (e) {
        e.preventDefault();
        $('#couponFilterForm')[0].reset();
        reloadCouponTable(fetchUrl);
    });

    // Select all checkbox
    $(document).off('change', '#couponSelectAll').on('change', '#couponSelectAll', function () {
        $('[data-coupon-checkbox]').prop('checked', this.checked);
    });

    // Show Coupon Details (Static Backdrop Modal)
    $(document).off('click', '.btn-show-coupon').on('click', '.btn-show-coupon', function (e) {
        e.preventDefault();
        const url = $(this).data('url');

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success && res.coupon) {
                    const c = res.coupon;
                    $('#modal-coupon-code').text(c.code);
                    $('#modal-coupon-type').text(c.type);
                    $('#modal-coupon-value').text(c.type === 'Fixed' ? '৳ ' + parseFloat(c.value).toFixed(2) : c.value + '%');
                    $('#modal-coupon-min-order').text(c.min_order_amount !== 'N/A' ? '৳ ' + parseFloat(c.min_order_amount).toFixed(2) : 'N/A');
                    $('#modal-coupon-expires').text(c.expires_at);
                    $('#modal-coupon-created').text(c.created_at);

                    const $statusBadge = $('#modal-coupon-status');
                    $statusBadge.text(c.status);
                    $statusBadge.attr('class', 'badge ' + (c.status === 'Active' ? 'badge-success' : (c.status === 'Inactive' ? 'badge-warning' : 'badge-danger')));

                    // Static Backdrop Modal Open
                    $('#showCouponModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Failed to fetch coupon details.', 'error');
            }
        });
    });

    // Edit Coupon (Static Backdrop Modal)
    $(document).off('click', '.btn-edit-coupon').on('click', '.btn-edit-coupon', function (e) {
        e.preventDefault();
        const url = $(this).data('url');

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success && res.coupon) {
                    const c = res.coupon;
                    resetCouponForm();
                    const $form = $('#couponForm');

                    $form.attr('action', '{{ url('admin/coupons') }}/' + c.id);
                    $('#couponFormMethod').val('PUT');
                    $('#couponModalTitle').text('Edit Coupon: ' + c.code);
                    $('#couponSubmitBtn').text('Update Coupon');

                    $('#coupon_code').val(c.code);
                    $('#coupon_type').val(c.type);
                    $('#coupon_value').val(c.value);
                    $('#coupon_min_order').val(c.min_order_amount);
                    $('#coupon_expires').val(c.expires_at);
                    $('#coupon_status').val(c.status);

                    // Static Backdrop Modal Open
                    $('#couponFormModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Failed to fetch coupon data.', 'error');
            }
        });
    });

    // Store / Update Form AJAX Submission
    $(document).off('submit', '#couponForm').on('submit', '#couponForm', function (e) {
        e.preventDefault();
        const $form = $(this);
        const url = $form.attr('action');
        const method = $('#couponFormMethod').val() === 'PUT' ? 'PUT' : 'POST';

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        $.ajax({
            url: url,
            method: method,
            data: $form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showAlert(res.message, 'success');
                    $('#couponFormModal').modal('hide');
                    reloadCouponTable(fetchUrl);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        const $input = $form.find('[name="' + key + '"]');
                        $input.addClass('is-invalid');
                        $form.find('[data-error="' + key + '"]').text(errors[key][0]);
                    });
                } else {
                    showAlert(xhr.responseJSON?.message || 'Operation failed.', 'error');
                }
            }
        });
    });

    // Individual Action (Delete / Restore / Force-Delete)
    $(document).off('submit', '.coupon-action-form').on('submit', '.coupon-action-form', function (e) {
        e.preventDefault();
        const $form = $(this);

        confirmAction({
            title: $form.data('confirm-title') || 'Are you sure?',
            text: $form.data('confirm-text') || 'Proceed with action?',
            icon: 'warning'
        }, function () {
            $.ajax({
                url: $form.attr('action'),
                method: $form.find('input[name="_method"]').val() || 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function (res) {
                    showAlert(res.message, 'success');
                    reloadCouponTable(fetchUrl);
                },
                error: function (xhr) {
                    showAlert(xhr.responseJSON?.message || 'Operation failed.', 'error');
                }
            });
        });
    });

    // Bulk Action
    $(document).off('submit', '#couponBulkForm').on('submit', '#couponBulkForm', function (e) {
        e.preventDefault();
        const $form = $(this);
        const action = $form.find('[name="action"]').val();
        const ids = $('[data-coupon-checkbox]:checked').map(function () { return this.value; }).get();

        if (!action || !ids.length) {
            showAlert('Please select coupon items and an action.', 'error');
            return;
        }

        const isForce = action === 'force-delete';

        confirmAction({
            title: isForce ? 'Permanently delete selected?' : 'Confirm bulk action',
            text: ids.length + ' coupon(s) will be processed.',
            icon: isForce ? 'warning' : 'question'
        }, function () {
            const formData = $form.serialize() + '&' + $.param({ coupon_ids: ids });
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function (res) {
                    showAlert(res.message, 'success');
                    reloadCouponTable(fetchUrl);
                },
                error: function (xhr) {
                    showAlert(xhr.responseJSON?.message || 'Bulk operation failed.', 'error');
                }
            });
        });
    });

    // Pagination link intercept
    $(document).on('click', '#paginationContainer a.page-link', function (e) {
        e.preventDefault();
        const pageUrl = $(this).attr('href');
        if (pageUrl) {
            reloadCouponTable(pageUrl);
        }
    });
})(jQuery);
</script>