<script>
$(function () {
    const fetchUrl = '{{ $fetchUrl }}';

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
                showAlert('Failed to refresh products table.', 'error');
            },
            complete: function () {
                $('#tableOverlay').addClass('d-none');
            }
        });
    }

    // Debounced Live Search & Filter
    let searchTimeout = null;
    $('#product-search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { reloadTable(); }, 400);
    });

    $('#product-status-filter').on('change', function () {
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
        const target = $(this).data('selectAll');$(`${target} [data-row-checkbox]`).prop('checked', $(this).is(':checked'));
    });

    // Clear Errors & Reset Previews
    function clearFormErrors(resetMedia = false) {
        $('.invalid-feedback').text('');$('.form-control, .custom-select').removeClass('is-invalid');
        if (resetMedia) window.mediaPicker?.reset('#productAjaxForm');
        $('#productTab a:first').tab('show');
    }

    // Open Create Modal
    $('#btnCreateProduct').on('click', function (e) {
        e.preventDefault();
        clearFormErrors(true);
        $('#productAjaxForm')[0].reset();
        $('#productFormMethod').val('POST');
        $('#productAjaxForm').attr('action', '{{ route("admin.products.store") }}');
        $('#productFormModalTitle span').text('Create Product');
        $('#productFormModal').modal('show');
    });

    // Open Edit Modal via AJAX
    $(document).on('click', '.btn-edit-product', function (e) {
        e.preventDefault();
        const prodId = $(this).data('id');
        clearFormErrors(true);

        $.ajax({
            url: `/admin/products/${prodId}/edit`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const p = res.product;
                    $('#productAjaxForm')[0].reset();
                    $('#productFormMethod').val('PUT');
                    $('#productAjaxForm').attr('action', `/admin/products/${prodId}`);
                    $('#productFormModalTitle span').text('Update Product: ' + p.name);

                    // General
                    $('#prod-name').val(p.name);
                    $('#prod-slug').val(p.slug);
                    $('#prod-sku').val(p.sku);
                    $('#prod-category').val(p.category_id);
                    $('#prod-regular-price').val(p.regular_price);
                    $('#prod-sale-price').val(p.sale_price);
                    $('#prod-stock').val(p.stock_quantity);
                    $('#prod-status').val(p.status);
                    $('#prod-featured').prop('checked', p.featured == 1);
                    $('#prod-short-desc').val(p.short_description);
                    $('#prod-description').val(p.description);

                    window.mediaPicker?.setPreview('prod-image', p.image_url || null, p.image_media_name || '');

                    // SEO
                    $('#seo-meta-title').val(p.meta_title);
                    $('#seo-meta-description').val(p.meta_description);
                    $('#seo-meta-keywords').val(p.meta_keywords);
                    $('#seo-canonical-url').val(p.canonical_url);

                    // OG
                    $('#seo-og-title').val(p.og_title);
                    $('#seo-og-description').val(p.og_description);
                    window.mediaPicker?.setPreview('seo-og-image', p.og_image_url || null, p.og_image_media_name || '');

                    // Twitter
                    $('#seo-twitter-title').val(p.twitter_title);
                    $('#seo-twitter-description').val(p.twitter_description);
                    window.mediaPicker?.setPreview('seo-twitter-image', p.twitter_image_url || null, p.twitter_image_media_name || '');

                    $('#productFormModal').modal('show');
                }
            },
            error: function () {
                showAlert('Failed to retrieve product details.', 'error');
            }
        });
    });

    // Submit Create/Edit Form via AJAX with FormData
    $('#productAjaxForm').on('submit', function (e) {
        e.preventDefault();
        clearFormErrors(false);

        const form = $(this);
        const submitBtn = $('#btnSubmitProductForm');
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
                    $('#productFormModal').modal('hide');
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
                    showAlert(xhr.responseJSON?.message || 'Something went wrong.', 'error');
                }
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Product');
            }
        });
    });

    // View Product Details via AJAX
    $(document).on('click', '.btn-view-product', function (e) {
        e.preventDefault();
        const prodId = $(this).data('id');

        $.ajax({
            url: `/admin/products/${prodId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const p = res.product;
                    $('#showProdName').text(p.name);
                    $('#showProdSku').text('SKU: ' + p.sku + ' | Category: ' + (p.category || 'N/A'));
                    $('#showProdPrice').text('$' + p.regular_price);$('#showProdStatus').html(`<span class="badge badge-${p.status === 'Active' ? 'success' : 'secondary'}">${p.status}</span>`);

                    if (p.image) {
                        $('#showProdImg').attr('src', p.image);
                        $('#showProdImgWrapper').removeClass('d-none');
                    } else {
                        $('#showProdImgWrapper').addClass('d-none');
                    }

                    // SEO Details
                    $('#showSeoTitle').text(p.seo.meta_title || 'N/A');
                    $('#showSeoCanonical').text(p.seo.canonical_url || 'N/A');
                    $('#showSeoDesc').text(p.seo.meta_description || 'N/A');
                    $('#showSeoKeywords').text(p.seo.meta_keywords || 'N/A');

                    // OG
                    $('#showOgTitle').text(p.seo.og_title || 'N/A');
                    $('#showOgDesc').text(p.seo.og_description || 'N/A');
                    if (p.seo.og_image) {
                        $('#showOgImage').attr('src', p.seo.og_image).removeClass('d-none');
                    } else {
                        $('#showOgImage').addClass('d-none');
                    }

                    // Twitter
                    $('#showTwitterTitle').text(p.seo.twitter_title || 'N/A');
                    $('#showTwitterDesc').text(p.seo.twitter_description || 'N/A');
                    if (p.seo.twitter_image) {
                        $('#showTwitterImage').attr('src', p.seo.twitter_image).removeClass('d-none');
                    } else {
                        $('#showTwitterImage').addClass('d-none');
                    }

                    $('#productShowModal').modal('show');
                }
            },
            error: function () {
                showAlert('Could not load product details.', 'error');
            }
        });
    });

    // Delete, restore and permanent-delete confirmations via SweetAlert2.
    function executeAction(action) {
        $.ajax({
            url: action.url,
            method: action.method,
            data: action.data,
            dataType: 'json',
            success: function (res) {
                showAlert(res.message, 'success');
                reloadTable();
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Operation failed.', 'error');
            }
        });
    }

    $(document).on('click', '.btn-action', function (e) {
        e.preventDefault();
        const btn = $(this);
        const action = {
            url: btn.data('url'),
            method: btn.data('method'),
            data: { _token: '{{ csrf_token() }}' }
        };

        const confirm = function () {
            executeAction(action);
        };

        if (!window.Swal || typeof window.Swal.fire !== 'function') {
            confirm();
            return;
        }

        window.Swal.fire({
            title: btn.data('confirm-title') || 'Are you sure?',
            text: btn.data('confirm-text') || 'This action cannot be undone.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.value) confirm();
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
            showAlert('Please select at least one product and choose a bulk action.', 'error');
            return;
        }

        const labels = {
            'delete': 'move selected products to trash',
            'restore': 'restore selected products',
            'force-delete': 'permanently delete selected products'
        };

        const bulkAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                product_ids: selected
            }
        };

        const executeBulk = function () {
            executeAction(bulkAction);
        };

        if (!window.Swal || typeof window.Swal.fire !== 'function') {
            executeBulk();
            return;
        }

        window.Swal.fire({
            title: 'Confirm Bulk Action',
            text: `Are you sure you want to ${labels[action] || 'process'}?`,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.value) executeBulk();
        });
    });
});
</script>