<script>
$(function () {
    const fetchUrl = '{{ $fetchUrl }}';

    // Helper: Show Alert
    function showAlert(message, type = 'success', container = '#ajaxAlertContainer') {
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        const html = `
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-${icon} mr-1"></i> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`;
        $(container).html(html);
        if (type === 'success') {
            setTimeout(() => { $(container).find('.alert').alert('close'); }, 4000);
        }
    }

    // Reload Table via AJAX
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
                showAlert('Failed to refresh permissions table.', 'danger');
            },
            complete: function () {
                $('#tableOverlay').addClass('d-none');
            }
        });
    }

    // Debounced Search Live Filtering
    let searchTimeout = null;
    $('#permission-search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { reloadTable(); }, 400);
    });

    $('#permission-group').on('change', function () {
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

    // Checkbox Master
    $(document).on('change', '[data-select-all]', function () {
        const target = $(this).data('selectAll');
        $(`${target} [data-row-checkbox]`).prop('checked', $(this).is(':checked'));
    });

    // Clear Errors
    function clearFormErrors() {
        $('.invalid-feedback').text('');
        $('.form-control').removeClass('is-invalid');
        $('#modalAlertContainer').html('');
    }

    // Open Create Modal
    $('#btnCreatePermission').on('click', function (e) {
        e.preventDefault();
        clearFormErrors();
        $('#permissionAjaxForm')[0].reset();
        $('#permissionFormMethod').val('POST');
        $('#permissionAjaxForm').attr('action', '{{ route("admin.permissions.store") }}');
        $('#permissionFormModalTitle span').text('Create Permission');
        $('#permissionFormModal').modal('show');
    });

    // Open Edit Modal via AJAX
    $(document).on('click', '.btn-edit-permission', function (e) {
        e.preventDefault();
        const permissionId = $(this).data('id');
        clearFormErrors();

        $.ajax({
            url: `/admin/permissions/${permissionId}/edit`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const p = res.permission;
                    $('#permissionAjaxForm')[0].reset();
                    $('#permissionFormMethod').val('PUT');
                    $('#permissionAjaxForm').attr('action', `/admin/permissions/${permissionId}`);
                    $('#permissionFormModalTitle span').text('Update Permission: ' + p.name);

                    $('#permission-name').val(p.name);
                    $('#permission-group-name').val(p.group_name);
                    $('#permission-guard').val(p.guard_name);

                    $('#permissionFormModal').modal('show');
                }
            },
            error: function () {
                showAlert('Failed to retrieve permission details.', 'danger');
            }
        });
    });

    // Submit Create/Edit Form via AJAX
    $('#permissionAjaxForm').on('submit', function (e) {
        e.preventDefault();
        clearFormErrors();

        const form = $(this);
        const submitBtn = $('#btnSubmitPermissionForm');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#permissionFormModal').modal('hide');
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
                    showAlert(xhr.responseJSON?.message || 'Something went wrong.', 'danger', '#modalAlertContainer');
                }
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Permission');
            }
        });
    });

    // View Permission Details via AJAX
    $(document).on('click', '.btn-view-permission', function (e) {
        e.preventDefault();
        const permissionId = $(this).data('id');

        $.ajax({
            url: `/admin/permissions/${permissionId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const p = res.permission;
                    $('#showPermName').text(p.name);
                    $('#showPermId').text('Permission ID: #' + p.id);
                    $('#showPermGroup').text(p.group_name);
                    $('#showPermGuard').text(p.guard_name);
                    $('#showPermCreated').text(p.created_at);
                    $('#permissionShowModal').modal('show');
                }
            },
            error: function () {
                showAlert('Could not load permission details.', 'danger');
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

    // Bulk Actions via AJAX
    $('#bulkActionForm').on('submit', function (e) {
        e.preventDefault();
        const action = $(this).find('[name="action"]').val();
        const selected = $('[data-row-checkbox]:checked').map(function () {
            return $(this).val();
        }).get();

        if (!action || selected.length === 0) {
            showAlert('Please select at least one permission and choose a bulk action.', 'danger');
            return;
        }

        const labels = {
            'delete': 'move selected permissions to trash',
            'restore': 'restore selected permissions',
            'force-delete': 'permanently delete selected permissions'
        };

        pendingAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                permission_ids: selected
            }
        };

        $('#confirmModalTitle').text('Confirm Bulk Action');
        $('#confirmModalText').text(`Are you sure you want to ${labels[action] || 'process'}?`);
        $('#confirmModal').modal('show');
    });
});
</script>