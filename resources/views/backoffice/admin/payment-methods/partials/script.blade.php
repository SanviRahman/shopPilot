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
        } else {
            alert(message);
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
                showAlert('Failed to load table data. Try again.', 'danger');
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
        if (pageUrl) {
            reloadTable(pageUrl);
        }
    });

    $(document).on('change', '[data-select-all]', function () {
        const target = $(this).data('selectAll');
        $(`${target} [data-row-checkbox]`).prop('checked', $(this).is(':checked'));
    });

    function clearFormErrors() {
        $('.invalid-feedback').text('');$('.form-control, .custom-select').removeClass('is-invalid');
    }

    $('#btnCreateMethod').on('click', function (e) {
        e.preventDefault();
        clearFormErrors();
        $('#methodAjaxForm')[0].reset();
        $('#formMethod').val('POST');
        $('#methodAjaxForm').attr('action', '{{ route("admin.payment-methods.store") }}');
        $('#methodFormModalTitle span').text('Add Payment Method');
        $('#methodFormModal').modal('show');
    });

    $(document).on('click', '.btn-edit-method', function (e) {
        e.preventDefault();
        const methodId = $(this).data('id');
        clearFormErrors();

        $.ajax({
            url: `/admin/payment-methods/${methodId}/edit`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const m = res.method;
                    $('#methodAjaxForm')[0].reset();
                    $('#formMethod').val('PUT');
                    $('#methodAjaxForm').attr('action', `/admin/payment-methods/${methodId}`);
                    $('#methodFormModalTitle span').text('Update Payment Method: ' + m.name);

                    $('#method-name').val(m.name);
                    $('#method-code').val(m.code);
                    $('#method-account_number').val(m.account_number);
                    $('#method-account_type').val(m.account_type);
                    $('#method-instruction').val(m.instruction);
                    $('#method-status').val(m.status);

                    $('#methodFormModal').modal('show');
                }
            },
            error: function () {
                showAlert('Failed to retrieve method details.', 'danger');
            }
        });
    });

    $('#methodAjaxForm').on('submit', function (e) {
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
                    $('#methodFormModal').modal('hide');
                    showAlert(res.message, 'success');
                    reloadTable();
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, messages) {
                        const cleanKey = key.replace('.', '_');
                        $(`#err-${cleanKey}`).text(messages[0]);
                        $(`[name="${key}"]`).addClass('is-invalid');
                    });
                } else {
                    showAlert(xhr.responseJSON?.message || 'Something went wrong.', 'danger');
                }
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Changes');
            }
        });
    });

    $(document).on('click', '.btn-view-method', function (e) {
        e.preventDefault();
        const methodId = $(this).data('id');

        $.ajax({
            url: `/admin/payment-methods/${methodId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const m = res.method;
                    $('#show-name').text(m.name);
                    $('#show-code').text(m.code);
                    $('#show-account_number').text(m.account_number);
                    $('#show-account_type').text(m.account_type);
                    $('#show-instruction').text(m.instruction);
                    $('#show-status').html(`<span class="badge badge-${m.status === 'Active' ? 'success' : 'secondary'}">${m.status}</span>`);
                    $('#show-created').text(m.created_at);
                    $('#methodShowModal').modal('show');
                }
            },
            error: function () {
                showAlert('Could not load details.', 'danger');
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

    $('#bulkActionForm').on('submit', function (e) {
        e.preventDefault();
        const action = $(this).find('[name="action"]').val();
        const selected = $('[data-row-checkbox]:checked').map(function () {
            return $(this).val();
        }).get();

        if (!action || selected.length === 0) {
            showAlert('Please select at least one method and choose a bulk action.', 'danger');
            return;
        }

        const labels = {
            'delete': 'move selected methods to trash',
            'restore': 'restore selected methods',
            'force-delete': 'permanently delete selected methods'
        };

        pendingAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                method_ids: selected
            }
        };

        $('#confirmModalTitle').text('Confirm Bulk Action');
        $('#confirmModalText').text(`Are you sure you want to ${labels[action] || 'process'}?`);
        $('#confirmModal').modal('show');
    });
});
</script>