<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function () {
    const fetchUrl = '{{ $fetchUrl }}';

    // Show Alert Box
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

    // Initialize Drag & Drop Sortable
    function initDragDropSortable() {
        if ($('#sortableCategoryBody').length) {
            $('#sortableCategoryBody').sortable({
                handle: '.drag-handle',
                items: '.sortable-row',
                placeholder: 'ui-state-highlight bg-light',
                update: function () {
                    const orderPayload = [];
                    $('#sortableCategoryBody tr.sortable-row').each(function (index) {
                        const id = $(this).data('id');
                        const newOrder = index + 1;
                        $(this).find('.order-index').text(newOrder);
                        orderPayload.push({ id: id, sort_order: newOrder });
                    });

                    // AJAX call to persist sort order
                    $.ajax({
                        url: '{{ route("admin.categories.reorder") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            orders: orderPayload
                        },
                        dataType: 'json',
                        success: function (res) {
                            if (res.success) {
                                showAlert('Order sequence updated successfully.', 'success');
                            }
                        },
                        error: function () {
                            showAlert('Failed to update sort order.', 'danger');
                        }
                    });
                }
            });
        }
    }

    initDragDropSortable();

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
                initDragDropSortable();
            },
            error: function () {
                showAlert('Failed to refresh categories table.', 'danger');
            },
            complete: function () {
                $('#tableOverlay').addClass('d-none');
            }
        });
    }

    // Debounced Live Search & Filter
    let searchTimeout = null;
    $('#category-search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { reloadTable(); }, 400);
    });

    $('#category-status-filter').on('change', function () {
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

    // Check All Checkbox
    $(document).on('change', '[data-select-all]', function () {
        const target = $(this).data('selectAll');
        $(`${target} [data-row-checkbox]`).prop('checked', $(this).is(':checked'));
    });

    // Clear Errors & Reset Previews
    function clearFormErrors() {
        $('.invalid-feedback').text('');
        $('.form-control, .custom-select').removeClass('is-invalid');
        $('#modalAlertContainer').html('');
        $('#imagePreviewContainer, #ogImagePreviewContainer, #twitterImagePreviewContainer').addClass('d-none');
        $('.custom-file-label').text('Choose file...');
        $('#categoryTab a:first').tab('show');
    }

    // Open Create Modal
    $('#btnCreateCategory').on('click', function (e) {
        e.preventDefault();
        clearFormErrors();
        $('#categoryAjaxForm')[0].reset();
        $('#categoryFormMethod').val('POST');
        $('#categoryAjaxForm').attr('action', '{{ route("admin.categories.store") }}');
        $('#categoryFormModalTitle span').text('Create Category');
        $('#categoryFormModal').modal('show');
    });

    // Open Edit Modal via AJAX
    $(document).on('click', '.btn-edit-category', function (e) {
        e.preventDefault();
        const catId = $(this).data('id');
        clearFormErrors();

        $.ajax({
            url: `/admin/categories/${catId}/edit`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const c = res.category;
                    $('#categoryAjaxForm')[0].reset();
                    $('#categoryFormMethod').val('PUT');
                    $('#categoryAjaxForm').attr('action', `/admin/categories/${catId}`);
                    $('#categoryFormModalTitle span').text('Update Category: ' + c.name);

                    // General
                    $('#cat-name').val(c.name);
                    $('#cat-slug').val(c.slug);
                    $('#cat-status').val(c.status);
                    $('#cat-sort-order').val(c.sort_order);
                    $('#cat-description').val(c.description);

                    if (c.image_url) {
                        $('#imagePreview').attr('src', c.image_url);
                        $('#imagePreviewContainer').removeClass('d-none');
                    }

                    // SEO
                    $('#seo-meta-title').val(c.meta_title);
                    $('#seo-meta-description').val(c.meta_description);
                    $('#seo-meta-keywords').val(c.meta_keywords);
                    $('#seo-canonical-url').val(c.canonical_url);

                    // OG
                    $('#seo-og-title').val(c.og_title);
                    $('#seo-og-description').val(c.og_description);
                    if (c.og_image_url) {
                        $('#ogImagePreview').attr('src', c.og_image_url);
                        $('#ogImagePreviewContainer').removeClass('d-none');
                    }

                    // Twitter
                    $('#seo-twitter-title').val(c.twitter_title);
                    $('#seo-twitter-description').val(c.twitter_description);
                    if (c.twitter_image_url) {
                        $('#twitterImagePreview').attr('src', c.twitter_image_url);
                        $('#twitterImagePreviewContainer').removeClass('d-none');
                    }

                    $('#categoryFormModal').modal('show');
                }
            },
            error: function () {
                showAlert('Failed to retrieve category details.', 'danger');
            }
        });
    });

    // File Input Custom Label Update
    $('.custom-file-input').on('change', function () {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Choose file...');
    });

    // Submit Create/Edit Form via AJAX with FormData
    $('#categoryAjaxForm').on('submit', function (e) {
        e.preventDefault();
        clearFormErrors();

        const form = $(this);
        const submitBtn = $('#btnSubmitCategoryForm');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        const formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#categoryFormModal').modal('hide');
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
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Category');
            }
        });
    });

    // View Category Details via AJAX
    $(document).on('click', '.btn-view-category', function (e) {
        e.preventDefault();
        const catId = $(this).data('id');

        $.ajax({
            url: `/admin/categories/${catId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const c = res.category;
                    $('#showCatName').text(c.name);
                    $('#showCatSlug').text('Slug: ' + c.slug);
                    $('#showCatSort').text(c.sort_order);
                    $('#showCatStatus').html(`<span class="badge badge-${c.status === 'Active' ? 'success' : 'secondary'}">${c.status}</span>`);

                    if (c.image) {
                        $('#showCatImg').attr('src', c.image);
                        $('#showCatImgWrapper').removeClass('d-none');
                    } else {
                        $('#showCatImgWrapper').addClass('d-none');
                    }

                    // SEO Details
                    $('#showSeoTitle').text(c.seo.meta_title);
                    $('#showSeoCanonical').text(c.seo.canonical_url);
                    $('#showSeoDesc').text(c.seo.meta_description);
                    $('#showSeoKeywords').text(c.seo.meta_keywords);

                    // OG
                    $('#showOgTitle').text(c.seo.og_title);
                    $('#showOgDesc').text(c.seo.og_description);
                    if (c.seo.og_image) {
                        $('#showOgImage').attr('src', c.seo.og_image).removeClass('d-none');
                    } else {
                        $('#showOgImage').addClass('d-none');
                    }

                    // Twitter
                    $('#showTwitterTitle').text(c.seo.twitter_title);
                    $('#showTwitterDesc').text(c.seo.twitter_description);
                    if (c.seo.twitter_image) {
                        $('#showTwitterImage').attr('src', c.seo.twitter_image).removeClass('d-none');
                    } else {
                        $('#showTwitterImage').addClass('d-none');
                    }

                    $('#categoryShowModal').modal('show');
                }
            },
            error: function () {
                showAlert('Could not load category details.', 'danger');
            }
        });
    });

    // Action Confirmation Modal (Delete, Restore, Force Delete)
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
            showAlert('Please select at least one category and choose a bulk action.', 'danger');
            return;
        }

        const labels = {
            'delete': 'move selected categories to trash',
            'restore': 'restore selected categories',
            'force-delete': 'permanently delete selected categories'
        };

        pendingAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                category_ids: selected
            }
        };

        $('#confirmModalTitle').text('Confirm Bulk Action');
        $('#confirmModalText').text(`Are you sure you want to ${labels[action] || 'process'}?`);
        $('#confirmModal').modal('show');
    });
});
</script>