<script>
$(function () {
    const fetchUrl = '{{ $fetchUrl }}';

    // Helper: Show Alert
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
                showAlert('Failed to load table data. Try again.', 'danger');
            },
            complete: function () {
                $('#tableOverlay').addClass('d-none');
            }
        });
    }

    // Filter & Search (Live with Debounce)
    let searchTimeout = null;
    $('#search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { reloadTable(); }, 400);
    });

    $('#role_id').on('change', function () {
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

    // Check All functionality
    $(document).on('change', '[data-select-all]', function () {
        const target = $(this).data('selectAll');
        $(`${target} [data-row-checkbox]`).prop('checked', $(this).is(':checked'));
    });

    // Reset Form Errors
    function clearFormErrors() {
        $('.invalid-feedback').text('');
        $('.form-control, .custom-select').removeClass('is-invalid');
    }

    // Open Create Modal
    $('#btnCreateAdmin').on('click', function (e) {
        e.preventDefault();
        clearFormErrors();
        $('#adminAjaxForm')[0].reset();
        $('#formMethod').val('POST');
        $('#adminAjaxForm').attr('action', '{{ route("admin.admins.store") }}');
        $('#adminFormModalTitle span').text('Create Admin');
        $('.pwd-required').removeClass('d-none');
        $('#pwdHint').addClass('d-none');
        $('#admin-password').prop('required', true);
        $('#admin-password-confirmation').prop('required', true);
        $('.role-checkbox').prop('checked', false);
        $('#adminFormModal').modal('show');
    });

    // Open Edit Modal via AJAX
    $(document).on('click', '.btn-edit-admin', function (e) {
        e.preventDefault();
        const adminId = $(this).data('id');
        clearFormErrors();

        $.ajax({
            url: `/admin/admins/${adminId}/edit`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const admin = res.admin;
                    $('#adminAjaxForm')[0].reset();
                    $('#formMethod').val('PUT');
                    $('#adminAjaxForm').attr('action', `/admin/admins/${adminId}`);
                    $('#adminFormModalTitle span').text('Update Admin: ' + admin.name);

                    $('#admin-name').val(admin.name);
                    $('#admin-email').val(admin.email);
                    $('#admin-status').val(admin.status);

                    // Passwords optional on edit
                    $('.pwd-required').addClass('d-none');
                    $('#pwdHint').removeClass('d-none');
                    $('#admin-password').prop('required', false);
                    $('#admin-password-confirmation').prop('required', false);

                    // Sync Roles
                    $('.role-checkbox').prop('checked', false);
                    if (admin.roles && Array.isArray(admin.roles)) {
                        admin.roles.forEach(roleId => {
                            $(`#role-${roleId}`).prop('checked', true);
                        });
                    }

                    $('#adminFormModal').modal('show');
                }
            },
            error: function () {
                showAlert('Failed to retrieve admin details.', 'danger');
            }
        });
    });

    // Submit Create/Edit Form via AJAX
    $('#adminAjaxForm').on('submit', function (e) {
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
                    $('#adminFormModal').modal('hide');
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

    // View Admin Details via AJAX
    $(document).on('click', '.btn-view-admin', function (e) {
        e.preventDefault();
        const adminId = $(this).data('id');

        $.ajax({
            url: `/admin/admins/${adminId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const a = res.admin;
                    $('#show-name').text(a.name);
                    $('#show-email').text(a.email);
                    $('#show-avatar').text(a.name.charAt(0).toUpperCase());
                    $('#show-status').html(`<span class="badge badge-${a.status === 'Active' ? 'success' : 'secondary'}">${a.status}</span>`);
                    $('#show-roles').text(a.roles.length ? a.roles.join(', ') : 'No role');
                    $('#show-created').text(a.created_at);
                    $('#adminShowModal').modal('show');
                }
            },
            error: function () {
                showAlert('Could not load details.', 'danger');
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
            showAlert('Please select at least one admin and choose a bulk action.', 'danger');
            return;
        }

        const labels = {
            'delete': 'move selected admins to trash',
            'restore': 'restore selected admins',
            'force-delete': 'permanently delete selected admins'
        };

        pendingAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                admin_ids: selected
            }
        };

        $('#confirmModalTitle').text('Confirm Bulk Action');
        $('#confirmModalText').text(`Are you sure you want to ${labels[action] || 'process'}?`);
        $('#confirmModal').modal('show');
    });
});
</script>