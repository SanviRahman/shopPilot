<script>
$(function () {
    const fetchUrl = @json($fetchUrl);
    const isTrashPage = @json((bool) ($isTrashPage ?? false));
    const storeUrl = @json(route('admin.meta-pixels.store'));
    const csrfToken = @json(csrf_token());
    let pixelEntryIndex = 0;
    let searchTimeout = null;

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function reloadMetaPixelTable(url = fetchUrl) {
        $('#metaPixelTableOverlay').removeClass('d-none');

        $.ajax({
            url: url,
            method: 'GET',
            data: $('#metaPixelFilterForm').serialize(),
            dataType: 'json',
            success: function (res) {
                $('#metaPixelTableContainer').html(res.html || '');
                $('#metaPixelPaginationContainer').html(res.pagination || '');
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Failed to refresh Meta Pixel table.', 'error');
            },
            complete: function () {
                $('#metaPixelTableOverlay').addClass('d-none');
            }
        });
    }

    function clearFormErrors() {
        $('#metaPixelAjaxForm .is-invalid').removeClass('is-invalid');
        $('#metaPixelAjaxForm .ajax-validation-error').remove();
    }

    function fieldNameFromValidationKey(key) {
        const parts = String(key).split('.');
        if (parts.length === 1) return key;
        return parts[0] + parts.slice(1).map(part => `[${part}]`).join('');
    }

    function showValidationErrors(errors) {
        clearFormErrors();

        Object.entries(errors || {}).forEach(([key, messages]) => {
            const fieldName = fieldNameFromValidationKey(key);
            const field = document.getElementsByName(fieldName)[0];
            if (!field) return;

            $(field).addClass('is-invalid');
            $('<div>', {
                class: 'invalid-feedback d-block ajax-validation-error',
                text: Array.isArray(messages) ? messages[0] : String(messages)
            }).insertAfter(field);
        });
    }

    function updateRemoveButtons() {
        const rows = $('#metaPixelEntriesContainer .meta-pixel-entry-row');
        rows.find('.btn-remove-pixel-entry').prop('disabled', rows.length <= 1);
    }

    function addPixelEntry(entry = {}) {
        const index = pixelEntryIndex++;
        const row = $(
            `<div class="meta-pixel-entry-row border rounded p-3 mb-3 bg-light" data-entry-index="${index}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <strong class="text-dark"><i class="fas fa-code mr-1 text-primary"></i> Pixel Entry <span class="pixel-entry-number"></span></strong>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-pixel-entry" title="Remove this Pixel ID and script">
                        <i class="fas fa-times mr-1"></i> Remove
                    </button>
                </div>
                <div class="form-group mb-3">
                    <label class="font-weight-bold">Pixel ID *</label>
                    <input type="text" name="pixel_entries[${index}][pixel_id]" class="form-control pixel-id-input" inputmode="numeric" maxlength="30" placeholder="e.g. 123456789012345" required>
                    <small class="form-text text-muted">Digits only, 5-30 characters.</small>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold">Pixel Script <span class="text-muted font-weight-normal">(optional)</span></label>
                    <textarea name="pixel_entries[${index}][script]" rows="8" class="form-control font-monospace pixel-script-input" placeholder="Paste the script for this Pixel ID here..."></textarea>
                </div>
            </div>`
        );

        row.find('.pixel-id-input').val(entry.pixel_id || '');
        row.find('.pixel-script-input').val(entry.script || '');
        $('#metaPixelEntriesContainer').append(row);
        renumberPixelEntries();
        updateRemoveButtons();
    }

    function renumberPixelEntries() {
        $('#metaPixelEntriesContainer .meta-pixel-entry-row').each(function (position) {
            $(this).find('.pixel-entry-number').text(position + 1);
        });
    }

    function resetMetaPixelForm() {
        const form = $('#metaPixelAjaxForm')[0];
        if (!form) return;

        form.reset();
        clearFormErrors();
        $('#metaPixelFormMethod').val('POST');
        $('#metaPixelAjaxForm').attr('action', storeUrl);
        $('#metaPixelFormModalTitle span').text('Create Meta Pixel');
        $('#meta-pixel-lifecycle').val('draft');
        $('#metaPixelTrackPageView').prop('checked', true);
        $('#metaPixelTrackEcommerce').prop('checked', true);
        $('#metaPixelEntriesContainer').empty();
        pixelEntryIndex = 0;
        addPixelEntry();
    }

    function populateMetaPixelForm(pixel, updateUrl) {
        resetMetaPixelForm();
        $('#metaPixelFormMethod').val('PUT');
        $('#metaPixelAjaxForm').attr('action', updateUrl);
        $('#metaPixelFormModalTitle span').text(`Update Meta Pixel: ${pixel.name}`);
        $('#meta-pixel-name').val(pixel.name || '');
        $('#meta-pixel-lifecycle').val(pixel.lifecycle_status || 'draft');
        $('#meta-pixel-starts-at').val(pixel.starts_at || '');
        $('#meta-pixel-ends-at').val(pixel.ends_at || '');
        $('#metaPixelTrackPageView').prop('checked', Boolean(pixel.track_page_view));
        $('#metaPixelTrackEcommerce').prop('checked', Boolean(pixel.track_ecommerce));

        $('#metaPixelEntriesContainer').empty();
        pixelEntryIndex = 0;
        const entries = Array.isArray(pixel.pixel_entries) && pixel.pixel_entries.length
            ? pixel.pixel_entries
            : [{ pixel_id: '', script: '' }];
        entries.forEach(addPixelEntry);
    }

    function renderShowEntries(entries) {
        const container = $('#showMetaPixelEntries').empty();

        if (!Array.isArray(entries) || entries.length === 0) {
            container.append('<p class="text-muted mb-0">No Pixel entries found.</p>');
            return;
        }

        entries.forEach((entry, index) => {
            const card = $('<div>', { class: 'border rounded p-3 mb-3 bg-light' });
            const head = $('<div>', { class: 'd-flex justify-content-between align-items-center mb-2' });
            head.append($('<strong>').text(`Pixel ${index + 1}`));
            head.append($('<code>').text(entry.pixel_id || ''));
            card.append(head);

            if (entry.script) {
                card.append($('<pre>', {
                    class: 'bg-dark text-light rounded p-3 mb-0',
                    css: { maxHeight: '240px', overflow: 'auto', whiteSpace: 'pre-wrap' }
                }).append($('<code>').text(entry.script)));
            } else {
                card.append('<small class="text-muted">No custom script saved for this Pixel ID.</small>');
            }

            container.append(card);
        });
    }

    function renderShowEvents(events) {
        const body = $('#showMetaPixelEvents').empty();
        if (!Array.isArray(events) || events.length === 0) {
            body.append('<tr><td colspan="3" class="text-center text-muted py-3">No recent events.</td></tr>');
            return;
        }

        events.forEach(event => {
            const row = $('<tr>');
            row.append($('<td>').append($('<code>').text(event.event_name || '')));
            row.append($('<td>').text(event.delivery_status || ''));
            row.append($('<td>').append($('<small>').text(event.occurred_at || '')));
            body.append(row);
        });
    }

    $('#meta-pixel-search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => reloadMetaPixelTable(), 350);
    });

    $('#meta-pixel-status-filter').on('change', function () {
        reloadMetaPixelTable();
    });

    $('#btnResetMetaPixelFilter').on('click', function () {
        $('#metaPixelFilterForm')[0]?.reset();
        reloadMetaPixelTable();
    });

    $(document).on('click', '#metaPixelPaginationContainer a.page-link', function (event) {
        event.preventDefault();
        const url = $(this).attr('href');
        if (url) reloadMetaPixelTable(url);
    });

    $(document)
        .off('click.metaPixelCreate', '#btnCreateMetaPixel')
        .on('click.metaPixelCreate', '#btnCreateMetaPixel', function (event) {
            event.preventDefault();
            resetMetaPixelForm();
            $('#metaPixelFormModal').modal('show');
        });

    $(document)
        .off('click.metaPixelAdd', '#btnAddPixelEntry')
        .on('click.metaPixelAdd', '#btnAddPixelEntry', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const container = $('#metaPixelEntriesContainer');
            const entryCount = container.find('.meta-pixel-entry-row').length;

            if (entryCount >= 20) {
                showAlert('A maximum of 20 Pixel entries is allowed.', 'error');
                return;
            }

            addPixelEntry();

            // The modal body is the scroll container. Bring the newly-added row into view
            // so Add More gives immediate visual feedback even with long scripts.
            const modalBody = $('#metaPixelFormModal .modal-body');
            const lastEntry = container.find('.meta-pixel-entry-row').last();

            if (modalBody.length && lastEntry.length) {
                const targetTop = modalBody.scrollTop()
                    + lastEntry.position().top
                    - 16;

                modalBody.stop(true).animate({ scrollTop: targetTop }, 180);
            }
        });

    $(document).off('click.metaPixelRemove', '.btn-remove-pixel-entry').on('click.metaPixelRemove', '.btn-remove-pixel-entry', function () {
        const rows = $('#metaPixelEntriesContainer .meta-pixel-entry-row');
        if (rows.length <= 1) return;
        $(this).closest('.meta-pixel-entry-row').remove();
        renumberPixelEntries();
        updateRemoveButtons();
    });

    $(document).on('click', '.btn-edit-meta-pixel', function (event) {
        event.preventDefault();
        const button = $(this);

        $.ajax({
            url: button.data('url'),
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) return;
                populateMetaPixelForm(res.pixel, button.data('update-url'));
                $('#metaPixelFormModal').modal('show');
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Could not load Meta Pixel for editing.', 'error');
            }
        });
    });

    $(document)
        .off('submit.metaPixel', '#metaPixelAjaxForm')
        .on('submit.metaPixel', '#metaPixelAjaxForm', function (event) {
        event.preventDefault();
        clearFormErrors();

        const form = $(this);
        const submitButton = $('#btnSubmitMetaPixelForm');
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
                $('#metaPixelFormModal').modal('hide');
                showAlert(res.message || 'Meta Pixel saved successfully.', 'success');
                reloadMetaPixelTable();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    showValidationErrors(xhr.responseJSON?.errors || {});
                    showAlert('Please fix the highlighted fields.', 'error');
                    return;
                }
                showAlert(xhr.responseJSON?.message || 'Could not save Meta Pixel.', 'error');
            },
            complete: function () {
                submitButton.prop('disabled', false).html(originalHtml);
            }
        });
    });

    $(document).on('click', '.btn-view-meta-pixel', function (event) {
        event.preventDefault();

        $.ajax({
            url: $(this).data('url'),
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) return;
                const pixel = res.pixel;
                $('#showMetaPixelName').text(pixel.name || 'Meta Pixel Details');
                $('#showMetaPixelMeta').text(`Created ${pixel.created_at || '-'} · Updated ${pixel.updated_at || '-'}`);
                $('#showMetaPixelLifecycle').text(pixel.lifecycle_status ? pixel.lifecycle_status.charAt(0).toUpperCase() + pixel.lifecycle_status.slice(1) : '-');
                $('#showMetaPixelPageView').text(pixel.track_page_view ? 'Enabled' : 'Disabled');
                $('#showMetaPixelEcommerce').text(pixel.track_ecommerce ? 'Enabled' : 'Disabled');
                $('#showMetaPixelStarts').text(pixel.starts_at || 'Immediately');
                $('#showMetaPixelEnds').text(pixel.ends_at || 'No expiry');
                renderShowEntries(pixel.pixel_entries || []);
                renderShowEvents(res.events || []);
                $('#metaPixelShowModal').modal('show');
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Could not load Meta Pixel details.', 'error');
            }
        });
    });

    function executeMetaPixelAction(button) {
        $.ajax({
            url: button.data('url'),
            method: button.data('method') || 'POST',
            data: { _token: csrfToken },
            dataType: 'json',
            success: function (res) {
                showAlert(res.message || 'Operation completed successfully.', 'success');
                reloadMetaPixelTable();
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Operation failed.', 'error');
            }
        });
    }

    $(document).on('click', '.btn-meta-pixel-action', function (event) {
        event.preventDefault();
        const button = $(this);
        const execute = () => executeMetaPixelAction(button);

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

    // Defensive initialization: if this partial is loaded/reloaded dynamically, ensure
    // the modal always has at least one entry when it becomes visible.
    $(document)
        .off('shown.bs.modal.metaPixel', '#metaPixelFormModal')
        .on('shown.bs.modal.metaPixel', '#metaPixelFormModal', function () {
            if ($('#metaPixelEntriesContainer .meta-pixel-entry-row').length === 0) {
                pixelEntryIndex = 0;
                addPixelEntry();
            }
        });

    if (!isTrashPage) {
        const query = new URLSearchParams(window.location.search);
        const editId = Number(query.get('edit') || 0);
        const showId = Number(query.get('show') || 0);

        if (editId > 0) {
            const button = $(`.btn-edit-meta-pixel[data-update-url$="/${editId}"]`).first();
            if (button.length) button.trigger('click');
        } else if (showId > 0) {
            const button = $(`.btn-view-meta-pixel[data-url$="/${showId}"]`).first();
            if (button.length) button.trigger('click');
        }
    }
});
</script>
