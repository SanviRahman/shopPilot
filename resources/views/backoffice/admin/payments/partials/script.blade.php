<script>
$(function () {
    const fetchUrl = '{{ $fetchUrl }}';

    function showAlert(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000
            });
        }
    }

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
                showAlert('Failed to load table data.', 'danger');
            },
            complete: function () {
                $('#tableOverlay').addClass('d-none');
            }
        });
    }

    let searchTimeout = null;
    $('#search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { reloadTable(); }, 400);
    });

    $('#status').on('change', function () {
        reloadTable();
    });

    $('#btnResetFilter').on('click', function () {
        $('#filterForm')[0].reset();
        reloadTable();
    });

    $(document).on('click', '#paginationContainer a.page-link', function (e) {
        e.preventDefault();
        const pageUrl = $(this).attr('href');
        if (pageUrl) { reloadTable(pageUrl); }
    });

    $(document).on('change', '[data-select-all]', function () {
        const target = $(this).data('selectAll');$(`${target} [data-row-checkbox]`).prop('checked', $(this).is(':checked'));
    });

    // View Details
    $(document).on('click', '.btn-view-payment', function (e) {
        e.preventDefault();
        const paymentId = $(this).data('id');

        $.ajax({
            url: `/admin/payments/${paymentId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const p = res.payment;
                    $('#show-order-number').text(p.order_number);
                    $('#show-method').text(p.method_name);
                    $('#show-trx-id').text(p.transaction_id);
                    $('#show-amount').text('৳' + p.amount);
                    $('#show-status').html(`<span class="badge badge-${p.status === 'Verified' ? 'success' : (p.status === 'Rejected' ? 'danger' : 'warning')}">${p.status}</span>`);
                    $('#show-verifier').text(p.verifier_name);
                    $('#show-verified-at').text(p.verified_at || 'N/A');
                    
                    if (p.rejection_note) {
                        $('#rejectionNoteContainer').removeClass('d-none');
                        $('#show-rejection-note').text(p.rejection_note);
                    } else {
                        $('#rejectionNoteContainer').addClass('d-none');
                    }

                    $('#paymentShowModal').modal('show');
                }
            },
            error: function () {
                showAlert('Could not load payment details.', 'danger');
            }
        });
    });

    // Verify Payment Direct Action
    $(document).on('click', '.btn-verify-payment', function (e) {
        e.preventDefault();
        const url = $(this).data('url');

        Swal.fire({
            title: 'Verify Payment?',
            text: "This will approve the payment and update order status.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Verify!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'PATCH',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            showAlert(res.message, 'success');
                            reloadTable();
                        }
                    },
                    error: function (xhr) {
                        showAlert(xhr.responseJSON?.message || 'Verification failed.', 'danger');
                    }
                });
            }
        });
    });

    // Reject Payment Modal Open
    $(document).on('click', '.btn-reject-payment', function (e) {
        e.preventDefault();
        const url = $(this).data('url');$('#paymentRejectForm').attr('action', url);
        $('#rejection_note').val('');
        $('#paymentRejectModal').modal('show');
    });

    // Submit Rejection
    $('#paymentRejectForm').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#btnSubmitReject');

        submitBtn.prop('disabled', true).text('Rejecting...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#paymentRejectModal').modal('hide');
                    showAlert(res.message, 'success');
                    reloadTable();
                }
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Rejection failed.', 'danger');
            },
            complete: function () {
                submitBtn.prop('disabled', false).text('Reject');
            }
        });
    });

    // Universal Action Confirmation (Trash / Restore / Delete)
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
                showAlert(res.message, 'success');
                reloadTable();
            },
            error: function (xhr) {
                $('#confirmModal').modal('hide');
                showAlert(xhr.responseJSON?.message || 'Operation failed.', 'danger');
            },
            complete: function () {
                confirmBtn.prop('disabled', false).text('Confirm');
                pendingAction = null;
            }
        });
    });

    // Bulk Actions
    $('#bulkActionForm').on('submit', function (e) {
        e.preventDefault();
        const action = $(this).find('[name="action"]').val();
        const selected = $('[data-row-checkbox]:checked').map(function () {
            return $(this).val();
        }).get();

        if (!action || selected.length === 0) {
            showAlert('Please select at least one item and choose a bulk action.', 'danger');
            return;
        }

        pendingAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                payment_ids: selected
            }
        };

        $('#confirmModalTitle').text('Confirm Bulk Action');
        $('#confirmModalText').text(`Are you sure you want to process ${selected.length} item(s)?`);
        $('#confirmModal').modal('show');
    });
});
</script>