<script>
$(function () {
    $('#mediaSelectAll').on('change', function () {
        $('[data-media-checkbox]').prop('checked', this.checked);
    });

    $(document).on('change', '[data-media-checkbox]', function () {
        if (!this.checked) $('#mediaSelectAll').prop('checked', false);
    });

    $(document).on('submit', '.media-confirm-form', function (event) {
        event.preventDefault();
        const form = this;
        const submit = function () { form.submit(); };

        if (!window.Swal || typeof window.Swal.fire !== 'function') {
            submit();
            return;
        }

        window.Swal.fire({
            title: form.dataset.confirmTitle || 'Are you sure?',
            text: form.dataset.confirmText || 'This action cannot be undone.',
            type: form.dataset.confirmType || 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.value) submit();
        });
    });

    $('#mediaBulkForm').on('submit', function (event) {
        event.preventDefault();
        const form = this;
        const action = $(form).find('[name="action"]').val();
        const selected = $('[data-media-checkbox]:checked').length;

        if (!action || !selected) {
            if (typeof showAlert === 'function') showAlert('Select at least one media file and choose an action.', 'error');
            return;
        }

        const submit = function () { form.submit(); };
        if (!window.Swal || typeof window.Swal.fire !== 'function') {
            submit();
            return;
        }

        window.Swal.fire({
            title: 'Confirm Bulk Action',
            text: `${selected} media file(s) will be processed.`,
            type: action === 'force-delete' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.value) submit();
        });
    });
});
</script>
