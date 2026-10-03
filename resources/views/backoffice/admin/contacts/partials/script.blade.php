<script>
$(function () {
    const fetchUrl = @json($fetchUrl);
    const isTrashPage = @json((bool) ($isTrashPage ?? false));
    const storeUrl = @json(route('admin.contacts.store'));
    const csrfToken = @json(csrf_token());
    let searchTimeout = null;

    function reloadContactTable(url = fetchUrl) {
        $('#contactTableOverlay').removeClass('d-none');

        $.ajax({
            url: url,
            method: 'GET',
            data: $('#contactFilterForm').serialize(),
            dataType: 'json',
            success: function (res) {
                $('#contactTableContainer').html(res.html || '');
                $('#contactPaginationContainer').html(res.pagination || '');
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Failed to refresh contact table.', 'error');
            },
            complete: function () {
                $('#contactTableOverlay').addClass('d-none');
            }
        });
    }

    function clearFormErrors() {
        $('#contactAjaxForm .is-invalid').removeClass('is-invalid');
        $('#contactAjaxForm .ajax-validation-error').remove();
    }

    function showValidationErrors(errors) {
        clearFormErrors();

        Object.entries(errors || {}).forEach(([key, messages]) => {
            const field = document.getElementsByName(key)[0];
            if (!field) return;

            $(field).addClass('is-invalid');
            $('<div>', {
                class: 'invalid-feedback d-block ajax-validation-error',
                text: Array.isArray(messages) ? messages[0] : String(messages)
            }).insertAfter(field);
        });
    }

    function resetContactForm() {
        const form = $('#contactAjaxForm')[0];
        if (!form) return;

        form.reset();
        clearFormErrors();
        $('#contactFormMethod').val('POST');
        $('#contactAjaxForm').attr('action', storeUrl);
        $('#contactFormModalTitle span').text('Create Contact');
        $('#contact-status').val('active');
    }

    function populateContactForm(contact, updateUrl) {
        resetContactForm();
        $('#contactFormMethod').val('PUT');
        $('#contactAjaxForm').attr('action', updateUrl);
        $('#contactFormModalTitle span').text(`Update Contact: ${contact.name || ''}`);
        $('#contact-name').val(contact.name || '');
        $('#contact-email').val(contact.email || '');
        $('#contact-phone').val(contact.phone || '');
        $('#contact-map-url').val(contact.map_url || '');
        $('#contact-status').val(contact.status || 'active');
    }

    $('#contact-search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => reloadContactTable(), 350);
    });

    $('#contact-status-filter').on('change', function () {
        reloadContactTable();
    });

    $('#btnResetContactFilter').on('click', function () {
        $('#contactFilterForm')[0]?.reset();
        reloadContactTable();
    });

    $(document).on('click', '#contactPaginationContainer a.page-link', function (event) {
        event.preventDefault();
        const url = $(this).attr('href');
        if (url) reloadContactTable(url);
    });

    $(document).on('click', '#btnCreateContact', function () {
        resetContactForm();
        $('#contactFormModal').modal('show');
    });

    $(document).on('click', '.btn-edit-contact', function (event) {
        event.preventDefault();
        const button = $(this);

        $.ajax({
            url: button.data('url'),
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) return;
                populateContactForm(res.contact, button.data('update-url'));
                $('#contactFormModal').modal('show');
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Could not load contact for editing.', 'error');
            }
        });
    });

    $(document)
        .off('submit.contactAjax', '#contactAjaxForm')
        .on('submit.contactAjax', '#contactAjaxForm', function (event) {
            event.preventDefault();
            clearFormErrors();

            const form = $(this);
            const submitButton = $('#btnSubmitContactForm');
            const originalHtml = submitButton.html();

            submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (res) {
                    if (!res.success) return;
                    $('#contactFormModal').modal('hide');
                    showAlert(res.message || 'Contact saved successfully.', 'success');
                    reloadContactTable();
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        showValidationErrors(xhr.responseJSON?.errors || {});
                        showAlert('Please fix the highlighted fields.', 'error');
                        return;
                    }

                    showAlert(xhr.responseJSON?.message || 'Could not save contact.', 'error');
                },
                complete: function () {
                    submitButton.prop('disabled', false).html(originalHtml);
                }
            });
        });

    $(document).on('click', '.btn-view-contact', function (event) {
        event.preventDefault();

        $.ajax({
            url: $(this).data('url'),
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) return;
                const contact = res.contact;
                $('#showContactName').text(contact.name || '—');
                $('#showContactEmail').text(contact.email || '—');
                $('#showContactPhone').text(contact.phone || '—');
                $('#showContactStatus')
                    .text(contact.status ? contact.status.charAt(0).toUpperCase() + contact.status.slice(1) : '—')
                    .toggleClass('badge-success', contact.status === 'active')
                    .toggleClass('badge-secondary', contact.status !== 'active');
                $('#showContactMapUrl').attr('href', contact.map_url || '#').text(contact.map_url || 'Open map');
                $('#showContactCreated').text(contact.created_at || '—');
                $('#showContactUpdated').text(contact.updated_at || '—');
                $('#contactShowModal').modal('show');
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Could not load contact details.', 'error');
            }
        });
    });

    function executeContactAction(button) {
        $.ajax({
            url: button.data('url'),
            method: button.data('method') || 'POST',
            data: { _token: csrfToken },
            dataType: 'json',
            success: function (res) {
                showAlert(res.message || 'Operation completed successfully.', 'success');
                reloadContactTable();
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Operation failed.', 'error');
            }
        });
    }

    $(document).on('click', '.btn-contact-action', function (event) {
        event.preventDefault();
        const button = $(this);
        const execute = () => executeContactAction(button);

        if (!window.Swal || typeof window.Swal.fire !== 'function') {
            execute();
            return;
        }

        window.Swal.fire({
            title: button.data('confirm-title') || 'Are you sure?',
            text: button.data('confirm-text') || 'Please confirm this action.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.value) execute();
        });
    });

    if (!isTrashPage) {
        const query = new URLSearchParams(window.location.search);
        const editId = Number(query.get('edit') || 0);
        const showId = Number(query.get('show') || 0);

        if (editId > 0) {
            const button = $(`.btn-edit-contact[data-update-url$="/${editId}"]`).first();
            if (button.length) button.trigger('click');
        } else if (showId > 0) {
            const button = $(`.btn-view-contact[data-url$="/${showId}"]`).first();
            if (button.length) button.trigger('click');
        }
    }
});
</script>
