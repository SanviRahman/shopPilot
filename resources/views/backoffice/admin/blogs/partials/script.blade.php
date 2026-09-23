<script>
(function ($) {
    'use strict';

    const fetchUrl = '{{ $fetchUrl ?? request()->url() }}';

    // Top-Right Floating Toast Notification (Like Product Module)
    function showAlert(message, type = 'success') {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            const Toast = window.Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', window.Swal.stopTimer);
                    toast.addEventListener('mouseleave', window.Swal.resumeTimer);
                }
            });

            Toast.fire({
                icon: type,
                title: message
            });
            return;
        }
        alert(message);
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

    function reloadBlogTable(url = fetchUrl) {
        $('#blogTableOverlay').removeClass('d-none');
        const formData = $('#blogFilterForm').serialize();

        $.ajax({
            url: url,
            method: 'GET',
            data: formData,
            dataType: 'json',
            success: function (res) {
                $('#blogTableContainer').html(res.html);
                if (res.pagination) {
                    $('#paginationContainer').html(res.pagination).show();
                } else {
                    $('#paginationContainer').html('').hide();
                }
            },
            error: function () {
                showAlert('Failed to refresh blogs list.', 'error');
            },
            complete: function () {
                $('#blogTableOverlay').addClass('d-none');
                $('#blogSelectAll').prop('checked', false);
            }
        });
    }

    $(document).off('submit', '#blogFilterForm').on('submit', '#blogFilterForm', function (e) {
        e.preventDefault();
        reloadBlogTable(fetchUrl);
    });

    let searchTimeout = null;
    $(document).off('input', '#blogFilterForm input[name="search"]').on('input', '#blogFilterForm input[name="search"]', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            reloadBlogTable(fetchUrl);
        }, 400);
    });

    $(document).off('click', '#btnResetFilter').on('click', '#btnResetFilter', function (e) {
        e.preventDefault();
        $('#blogFilterForm')[0].reset();
        $('#blogFilterForm input[name="search"]').val('');
        reloadBlogTable(fetchUrl);
    });

    // Select all checkbox
    $(document).off('change', '#blogSelectAll').on('change', '#blogSelectAll', function () {
        $('[data-blog-checkbox]').prop('checked', this.checked);
    });

    // Static Modal Show Blog Details Handler
    $(document).off('click', '.btn-show-blog').on('click', '.btn-show-blog', function (e) {
        e.preventDefault();
        const $btn = $(this);

        $('#modal-blog-title').text($btn.data('title'));
        $('#modal-blog-slug').text($btn.data('slug'));
        $('#modal-blog-author').text($btn.data('author'));
        $('#modal-blog-created').text($btn.data('created'));
        $('#modal-blog-updated').text($btn.data('updated'));
        $('#modal-blog-content').text($btn.data('content') || 'No content provided.');

        $('#showBlogModal').modal('show');
    });

    // Individual Action (Delete / Restore / Force-Delete)
    $(document).off('submit', '.blog-action-form').on('submit', '.blog-action-form', function (e) {
        e.preventDefault();
        const $form = $(this);

        confirmAction({
            title: $form.data('confirm-title') || 'Are you sure?',
            text: $form.data('confirm-text') || 'Proceed with action?',
            icon: 'warning'
        }, function () {
            $.ajax({
                url: $form.attr('action'),
                method: $form.find('input[name="_method"]').val() || 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function (res) {
                    showAlert(res.message, 'success');
                    reloadBlogTable(fetchUrl);
                },
                error: function (xhr) {
                    showAlert(xhr.responseJSON?.message || 'Operation failed.', 'error');
                }
            });
        });
    });

    // Bulk Action
    $(document).off('submit', '#blogBulkForm').on('submit', '#blogBulkForm', function (e) {
        e.preventDefault();
        const $form = $(this);
        const action = $form.find('[name="action"]').val();
        const ids = $('[data-blog-checkbox]:checked').map(function () { return this.value; }).get();

        if (!action || !ids.length) {
            showAlert('Please select blog items and an action.', 'error');
            return;
        }

        const isForce = action === 'force-delete';

        confirmAction({
            title: isForce ? 'Permanently delete selected?' : 'Confirm bulk action',
            text: ids.length + ' blog(s) will be processed.',
            icon: isForce ? 'warning' : 'question'
        }, function () {
            const formData = $form.serialize() + '&' + $.param({ blog_ids: ids });
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function (res) {
                    showAlert(res.message, 'success');
                    reloadBlogTable(fetchUrl);
                },
                error: function (xhr) {
                    showAlert(xhr.responseJSON?.message || 'Bulk operation failed.', 'error');
                }
            });
        });
    });

    // Pagination link intercept
    $(document).on('click', '#paginationContainer a.page-link', function (e) {
        e.preventDefault();
        const pageUrl = $(this).attr('href');
        if (pageUrl) {
            reloadBlogTable(pageUrl);
        }
    });
})(jQuery);
</script>