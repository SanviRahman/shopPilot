<script>
(function ($) {
    'use strict';

    function confirmAction(options, callback) {
        if (
            window.Swal &&
            typeof window.Swal.fire === 'function'
        ) {
            window.Swal.fire({
                title: options.title || 'Are you sure?',
                text: options.text || '',
                icon: options.icon || 'warning',
                type: options.type || options.icon || 'warning',
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
                return String(
                    $(this).attr('data-media-id') || this.value
                );
            })
            .get()
            .filter(Boolean);
    }

    function updateSelectAllState() {
        const $checkboxes = $('[data-media-checkbox]');
        const checked = $checkboxes.filter(':checked').length;

        $('#mediaSelectAll').prop(
            'checked',
            $checkboxes.length > 0 &&
            checked === $checkboxes.length
        );
    }

    function addMediaIdsToForm(form, ids) {
        form.querySelectorAll('input[name="media_ids[]"]')
            .forEach(function (input) {
                input.remove();
            });

        ids.forEach(function (id) {
            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'media_ids[]';
            input.value = id;

            form.appendChild(input);
        });
    }

    $(document)
        .off('change.mediaSelectAll', '#mediaSelectAll')
        .on(
            'change.mediaSelectAll',
            '#mediaSelectAll',
            function () {
                $('[data-media-checkbox]').prop(
                    'checked',
                    this.checked
                );

                updateSelectAllState();
            }
        );

    $(document)
        .off('change.mediaCheckbox', '[data-media-checkbox]')
        .on(
            'change.mediaCheckbox',
            '[data-media-checkbox]',
            updateSelectAllState
        );

    $(document)
        .off('submit.mediaConfirm', '.media-confirm-form')
        .on(
            'submit.mediaConfirm',
            '.media-confirm-form',
            function (event) {
                event.preventDefault();

                const form = this;

                confirmAction({
                    title: form.dataset.confirmTitle,
                    text: form.dataset.confirmText,
                    icon: form.dataset.confirmType || 'warning',
                    confirmText: 'Yes, continue'
                }, function () {
                    form.submit();
                });
            }
        );

    $(document)
        .off('submit.mediaBulk', '#mediaBulkForm')
        .on(
            'submit.mediaBulk',
            '#mediaBulkForm',
            function (event) {
                event.preventDefault();

                const form = this;
                const action = $(form)
                    .find('[name="action"]')
                    .val();
                const ids = selectedMainMediaIds();

                if (! action || ! ids.length) {
                    if (
                        window.Swal &&
                        typeof window.Swal.fire === 'function'
                    ) {
                        window.Swal.fire({
                            title: 'Action Required',
                            text: 'Select media files and choose an action.',
                            icon: 'error',
                            type: 'error'
                        });
                    }

                    return;
                }

                const isPermanentDelete = action === 'force-delete';

                confirmAction({
                    title: isPermanentDelete
                        ? 'Permanently delete selected media?'
                        : 'Confirm bulk action',
                    text: ids.length +
                        ' media file(s) will be processed.',
                    icon: isPermanentDelete ? 'warning' : 'question',
                    confirmText: 'Yes, continue'
                }, function () {
                    addMediaIdsToForm(form, ids);
                    form.submit();
                });
            }
        );

    updateSelectAllState();
})(jQuery);
</script>
