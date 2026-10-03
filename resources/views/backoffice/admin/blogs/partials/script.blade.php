<script>
$(function () {
    const fetchUrl = @json($fetchUrl ?? request()->url());
    const isTrashPage = @json((bool) ($isTrashPage ?? false));
    const storeUrl = @json(route('admin.blogs.store'));
    const csrfToken = @json(csrf_token());
    let searchTimeout = null;

    function reloadBlogTable(url = fetchUrl) {
        $('#blogTableOverlay').removeClass('d-none');
        $.ajax({url: url, method: 'GET', data: $('#blogFilterForm').serialize(), dataType: 'json', success: function (res) { $('#blogTableContainer').html(res.html || ''); $('#paginationContainer').html(res.pagination || ''); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Failed to refresh blogs list.', 'error'); }, complete: function () { $('#blogTableOverlay').addClass('d-none'); $('#blogSelectAll').prop('checked', false); }});
    }

    function clearBlogErrors() {
        $('#blogAjaxForm .is-invalid').removeClass('is-invalid');
        $('#blogAjaxForm .ajax-validation-error').remove();
    }

    function showBlogErrors(errors) {
        clearBlogErrors();
        Object.entries(errors || {}).forEach(([key, messages]) => { const field = document.getElementsByName(key)[0]; if (!field) return; $(field).addClass('is-invalid'); $('<div>', {class: 'invalid-feedback d-block ajax-validation-error', text: Array.isArray(messages) ? messages[0] : String(messages)}).insertAfter(field); });
    }

    function updateDescriptionCount() {
        $('#blogDescriptionCount').text(($('#blog-description').val() || '').length);
    }

    function resetBlogForm() {
        const form = $('#blogAjaxForm')[0];
        if (!form) return;
        form.reset();
        clearBlogErrors();
        $('#blogFormMethod').val('POST');
        $('#blogAjaxForm').attr('action', storeUrl);
        $('#blogFormModalTitle span').text('Create Blog');
        updateDescriptionCount();
    }

    function populateBlogForm(blog, updateUrl) {
        resetBlogForm();
        $('#blogFormMethod').val('PUT');
        $('#blogAjaxForm').attr('action', updateUrl);
        $('#blogFormModalTitle span').text(`Update Blog: ${blog.title || ''}`);
        $('#blog-title').val(blog.title || '');
        $('#blog-slug').val(blog.slug || '');
        $('#blog-description').val(blog.description || '');
        $('#blog-content').val(blog.content || '');
        updateDescriptionCount();
    }

    $('#blogFilterForm input[name="search"]').on('input', function () { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => reloadBlogTable(), 350); });
    $('#btnResetFilter').on('click', function () { $('#blogFilterForm')[0]?.reset(); reloadBlogTable(); });
    $(document).on('click', '#paginationContainer a.page-link', function (event) { event.preventDefault(); const url = $(this).attr('href'); if (url) reloadBlogTable(url); });
    $(document).on('change', '#blogSelectAll', function () { $('[data-blog-checkbox]').prop('checked', this.checked); });
    $(document).on('input', '#blog-description', updateDescriptionCount);

    $(document).on('click', '#btnCreateBlog', function () { resetBlogForm(); $('#blogFormModal').modal('show'); });

    $(document).on('click', '.btn-edit-blog', function (event) {
        event.preventDefault();
        const button = $(this);
        $.ajax({url: button.data('url'), method: 'GET', dataType: 'json', success: function (res) { if (!res.success) return; populateBlogForm(res.blog, button.data('update-url')); $('#blogFormModal').modal('show'); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Could not load blog for editing.', 'error'); }});
    });

    $(document).off('submit.blogAjax', '#blogAjaxForm').on('submit.blogAjax', '#blogAjaxForm', function (event) {
        event.preventDefault();
        clearBlogErrors();
        const form = $(this);
        const button = $('#btnSubmitBlogForm');
        const original = button.html();
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
        $.ajax({url: form.attr('action'), method: 'POST', data: new FormData(this), processData: false, contentType: false, dataType: 'json', success: function (res) { if (!res.success) return; $('#blogFormModal').modal('hide'); showAlert(res.message || 'Blog saved successfully.', 'success'); reloadBlogTable(); }, error: function (xhr) { if (xhr.status === 422) { showBlogErrors(xhr.responseJSON?.errors || {}); showAlert('Please fix the highlighted fields.', 'error'); return; } showAlert(xhr.responseJSON?.message || 'Could not save blog.', 'error'); }, complete: function () { button.prop('disabled', false).html(original); }});
    });

    $(document).on('click', '.btn-show-blog', function (event) {
        event.preventDefault();
        $.ajax({url: $(this).data('url'), method: 'GET', dataType: 'json', success: function (res) { if (!res.success) return; const blog = res.blog; $('#modal-blog-title').text(blog.title || '—'); $('#modal-blog-slug').text(blog.slug || '—'); $('#modal-blog-author').text(blog.author_label || blog.author || 'System'); $('#modal-blog-created').text(blog.created_at || '—'); $('#modal-blog-updated').text(blog.updated_at || '—'); $('#modal-blog-description').text(blog.description || 'No description provided.'); $('#modal-blog-content').text(blog.content || 'No content provided.'); $('#showBlogModal').modal('show'); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Could not load blog details.', 'error'); }});
    });

    function executeBlogAction(button) {
        $.ajax({url: button.data('url'), method: button.data('method') || 'POST', data: {_token: csrfToken}, dataType: 'json', success: function (res) { showAlert(res.message || 'Operation completed successfully.', 'success'); reloadBlogTable(); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Operation failed.', 'error'); }});
    }

    $(document).on('click', '.btn-blog-action', function (event) {
        event.preventDefault();
        const button = $(this);
        const execute = () => executeBlogAction(button);
        if (!window.Swal || typeof window.Swal.fire !== 'function') { if (window.confirm(button.data('confirm-text') || 'Continue?')) execute(); return; }
        window.Swal.fire({title: button.data('confirm-title') || 'Are you sure?', text: button.data('confirm-text') || 'Please confirm this action.', type: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, continue', cancelButtonText: 'Cancel'}).then(function (result) { if (result.value || result.isConfirmed) execute(); });
    });

    $(document).off('submit.blogBulk', '#blogBulkForm').on('submit.blogBulk', '#blogBulkForm', function (event) {
        event.preventDefault();
        const form = $(this);
        const action = form.find('[name="action"]').val();
        const ids = $('[data-blog-checkbox]:checked').map(function () { return this.value; }).get();
        if (!action || !ids.length) { showAlert('Please select blog items and an action.', 'error'); return; }
        const run = function () { $.ajax({url: form.attr('action'), method: 'POST', data: form.serialize() + '&' + $.param({blog_ids: ids}), dataType: 'json', success: function (res) { showAlert(res.message || 'Blogs processed.', 'success'); reloadBlogTable(); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Bulk operation failed.', 'error'); }}); };
        if (!window.Swal || typeof window.Swal.fire !== 'function') { if (window.confirm('Process selected blog posts?')) run(); return; }
        window.Swal.fire({title: 'Confirm bulk action', text: `${ids.length} blog(s) will be processed.`, type: action === 'force-delete' ? 'warning' : 'question', showCancelButton: true, confirmButtonText: 'Yes, continue'}).then(function (result) { if (result.value || result.isConfirmed) run(); });
    });

    if (!isTrashPage) {
        const query = new URLSearchParams(window.location.search);
        if (query.get('create') === '1') $('#btnCreateBlog').trigger('click');
        const editId = Number(query.get('edit') || 0);
        const showId = Number(query.get('show') || 0);
        if (editId > 0) $(`.btn-edit-blog[data-update-url$="/${editId}"]`).first().trigger('click');
        else if (showId > 0) $(`.btn-show-blog[data-url$="/${showId}"]`).first().trigger('click');
    }
});
</script>
