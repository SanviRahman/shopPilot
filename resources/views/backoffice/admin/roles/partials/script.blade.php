<script>
$(function () {
    const fetchUrl = '{{ $fetchUrl }}';

    // Helper: Show Alert Box
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
                showAlert('Failed to refresh roles table.', 'danger');
            },
            complete: function () {
                $('#tableOverlay').addClass('d-none');
            }
        });
    }

    // Debounced Search Live Filtering
    let searchTimeout = null;
    $('#role-search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { reloadTable(); }, 400);
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
    }

    // Permissions check toggles
    $('#btnCheckAllPermissions').on('click', function () {
        $('.perm-checkbox').prop('checked', true);
    });

    $('#btnUncheckAllPermissions').on('click', function () {
        $('.perm-checkbox').prop('checked', false);
    });

    $(document).on('click', '.toggle-group-perms', function () {
        const grp = $(this).data('group');
        const checkboxes = $(`.${grp}`);
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        checkboxes.prop('checked', !allChecked);
    });

    // Open Create Modal
    $('#btnCreateRole').on('click', function (e) {
        e.preventDefault();
        clearFormErrors();
        $('#roleAjaxForm')[0].reset();
        $('#roleFormMethod').val('POST');
        $('#roleAjaxForm').attr('action', '{{ route("admin.roles.store") }}');
        $('#roleFormModalTitle span').text('Create Role');
        $('.perm-checkbox').prop('checked', false);
        $('#roleFormModal').modal('show');
    });

    // Open Edit Modal via AJAX
    $(document).on('click', '.btn-edit-role', function (e) {
        e.preventDefault();
        const roleId = $(this).data('id');
        clearFormErrors();

        $.ajax({
            url: `/admin/roles/${roleId}/edit`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const role = res.role;
                    $('#roleAjaxForm')[0].reset();
                    $('#roleFormMethod').val('PUT');
                    $('#roleAjaxForm').attr('action', `/admin/roles/${roleId}`);
                    $('#roleFormModalTitle span').text('Update Role: ' + role.name);
                    $('#role-name').val(role.name);

                    $('.perm-checkbox').prop('checked', false);
                    if (role.permissions && Array.isArray(role.permissions)) {
                        role.permissions.forEach(pId => {
                            $(`#perm-${pId}`).prop('checked', true);
                        });
                    }

                    $('#roleFormModal').modal('show');
                }
            },
            error: function () {
                showAlert('Failed to retrieve role details.', 'danger');
            }
        });
    });

    // Submit Create/Edit Form via AJAX
    $('#roleAjaxForm').on('submit', function (e) {
        e.preventDefault();
        clearFormErrors();

        const form = $(this);
        const submitBtn = $('#btnSubmitRoleForm');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#roleFormModal').modal('hide');
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
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Role');
            }
        });
    });

    // View Role Details via AJAX
    $(document).on('click', '.btn-view-role', function (e) {
        e.preventDefault();
        const roleId = $(this).data('id');

        $.ajax({
            url: `/admin/roles/${roleId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const r = res.role;
                    $('#showRoleName').text(r.name);
                    $('#showRoleGuard').text(r.guard_name);
                    $('#showRoleUsersCount').text(`${r.admins_count} Staff Assigned`);
                    $('#showRoleProtected').html(r.is_protected ? '<span class="badge badge-danger">Protected</span>' : '<span class="badge badge-light border">Standard</span>');
                    $('#showRolePermsCount').text(r.permissions.length);

                    let permsHtml = '';
                    if (r.permissions && r.permissions.length > 0) {
                        r.permissions.forEach(p => {
                            permsHtml += `<span class="badge badge-white border text-dark shadow-xs m-1 px-2 py-1"><i class="fas fa-check text-success mr-1"></i>${p}</span>`;
                        });
                    } else {
                        permsHtml = '<span class="text-muted small">No permissions granted to this role.</span>';
                    }
                    $('#showRolePermsList').html(permsHtml);
                    $('#roleShowModal').modal('show');
                }
            },
            error: function () {
                showAlert('Could not load role details.', 'danger');
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
            showAlert('Please select at least one role and choose a bulk action.', 'danger');
            return;
        }

        const labels = {
            'delete': 'move selected roles to trash',
            'restore': 'restore selected roles',
            'force-delete': 'permanently delete selected roles'
        };

        pendingAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                role_ids: selected
            }
        };

        $('#confirmModalTitle').text('Confirm Bulk Action');
        $('#confirmModalText').text(`Are you sure you want to ${labels[action] || 'process'}?`);
        $('#confirmModal').modal('show');
    });
});
</script>