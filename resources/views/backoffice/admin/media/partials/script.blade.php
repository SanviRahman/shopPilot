<script>
(function ($) {
    'use strict';

    const fetchUrl = window.location.href;

    // Reload Media Grid via AJAX
    function reloadMediaGrid(url = fetchUrl) {
        $('#mediaTableOverlay').removeClass('d-none');
        const formData = $('#filterForm').serialize();

        $.ajax({
            url: url,
            method: 'GET',
            data: formData,
            dataType: 'json',
            success: function (res) {
                $('#mediaTableContainer').html(res.html);
                if (res.pagination) {
                    $('#paginationContainer').html(res.pagination).show();
                } else {
                    $('#paginationContainer').hide();
                }
                // Update stats if available
                if (res.stats) {
                    // Update stats blocks dynamically if needed
                }
            },
            error: function () {
                showAlert('Failed to refresh media grid.', 'error');
            },
            complete: function () {
                $('#mediaTableOverlay').addClass('d-none');
            }
        });
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

    function selectedMainMediaIds() {
        return $('[data-media-checkbox]:checked')
            .map(function () {
                return String($(this).attr('data-media-id') || this.value);
            })
            .get()
            .filter(Boolean);
    }

    function updateSelectAllState() {
        const $checkboxes = $('[data-media-checkbox]');
        const checked = $checkboxes.filter(':checked').length;
        $('#mediaSelectAll').prop('checked', $checkboxes.length > 0 && checked === $checkboxes.length);
    }

    $(document).off('change.mediaSelectAll', '#mediaSelectAll').on('change.mediaSelectAll', '#mediaSelectAll', function () {
        $('[data-media-checkbox]').prop('checked', this.checked);
        updateSelectAllState();
    });

    $(document).off('change.mediaCheckbox', '[data-media-checkbox]').on('change.mediaCheckbox', '[data-media-checkbox]', updateSelectAllState);

    // Individual Action via AJAX (Delete, Restore, Force Delete)
    $(document).off('submit.mediaConfirm', '.media-confirm-form').on('submit.mediaConfirm', '.media-confirm-form', function (event) {
        event.preventDefault();

        const form = this;
        const $form = $(form);

        confirmAction({
            title: form.dataset.confirmTitle,
            text: form.dataset.confirmText,
            icon: form.dataset.confirmType || 'warning',
            confirmText: 'Yes, continue'
        }, function () {
            $.ajax({
                url: $form.attr('action'),
                method: $form.find('input[name="_method"]').val() || 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        showAlert(res.message, 'success');
                        reloadMediaGrid();
                    }
                },
                error: function (xhr) {
                    showAlert(xhr.responseJSON?.message || 'Operation failed.', 'error');
                }
            });
        });
    });

    // Bulk Action via AJAX
    $(document).off('submit.mediaBulk', '#mediaBulkForm').on('submit.mediaBulk', '#mediaBulkForm', function (event) {
        event.preventDefault();

        const form = this;
        const $form = $(form);
        const action = $form.find('[name="action"]').val();
        const ids = selectedMainMediaIds();

        if (!action || !ids.length) {
            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: 'Action Required',
                    text: 'Select media files and choose an action.',
                    icon: 'error'
                });
            }
            return;
        }

        const isPermanentDelete = action === 'force-delete';

        confirmAction({
            title: isPermanentDelete ? 'Permanently delete selected media?' : 'Confirm bulk action',
            text: ids.length + ' media file(s) will be processed.',
            icon: isPermanentDelete ? 'warning' : 'question',
            confirmText: 'Yes, continue'
        }, function () {
            const formData = $form.serialize() + '&' + $.param({ media_ids: ids });

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        showAlert(res.message, 'success');
                        reloadMediaGrid();
                        $('#mediaSelectAll').prop('checked', false);
                    }
                },
                error: function (xhr) {
                    showAlert(xhr.responseJSON?.message || 'Bulk operation failed.', 'error');
                }
            });
        });
    });

    // Pagination Click Intercept for AJAX
    $(document).on('click', '#paginationContainer a.page-link', function (e) {
        e.preventDefault();
        const pageUrl = $(this).attr('href');
        if (pageUrl) {
            reloadMediaGrid(pageUrl);
        }
    });

    updateSelectAllState();
})(jQuery);
</script>