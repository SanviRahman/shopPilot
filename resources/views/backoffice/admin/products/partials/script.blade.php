<script>
$(function () {
    const fetchUrl = '{{ $fetchUrl }}';
    let productAttributes = @json($productAttributes->toArray());

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));

    let selectedAttributeValueIds = [];
    let managerValueIndex = 1;

    function renderVariantAttributeChoices(selectedIds = []) {
        const selected = new Set(selectedIds.map(Number));
        const available = new Set(productAttributes.flatMap((attribute) => attribute.values.map((value) => Number(value.id))));
        const removedSelection = [...selected].some((id) => !available.has(id));
        const cards = productAttributes.map((attribute) => {
            const values = attribute.values || [];
            const checked = values.some((value) => selected.has(Number(value.id)));

            return `<div class="attribute-choice-card" data-attribute-card data-attribute-id="${Number(attribute.id)}">
                <div class="attribute-choice-title">
                    <label class="mb-0"><input type="checkbox" data-attribute-toggle ${checked ? 'checked' : ''}> ${escapeHtml(attribute.name)}</label>
                    <small class="text-muted">${values.length} values</small>
                </div>
                <div class="attribute-choice-values">
                    ${values.map((value) => `<label class="attribute-choice-value">
                        <input type="checkbox" data-attribute-value value="${Number(value.id)}" ${selected.has(Number(value.id)) ? 'checked' : ''} ${checked ? '' : 'disabled'}>
                        ${attribute.display_type === 'color' ? `<i class="swatch" style="--swatch-color: ${escapeHtml(value.color_code || '#dbe4ef')}"></i>` : ''}
                        ${escapeHtml(value.label)}
                    </label>`).join('')}
                </div>
            </div>`;
        }).join('');

        $('#variantAttributeChoices').html(cards || '<div class="variant-empty-state w-100">No active attributes found. Create Color or Size first from Manage attributes.</div>');

        if (removedSelection) {
            $('#variantRows').html('<tr><td colspan="7"><div class="variant-empty-state">Some selected options are no longer available. Generate variations again.</div></td></tr>');
            $('#variantSummary').prop('hidden', true).empty();
            $('#err-variants').text('Selected attribute options changed. Generate variations again.');
            selectedAttributeValueIds = [];
        }
    }

    function managerValueRow(value = {}, index = 0) {
        const id = value.id ? `<input type="hidden" name="values[${index}][id]" value="${Number(value.id)}">` : '';
        const active = value.is_active !== false && Number(value.is_active) !== 0;
        const color = escapeHtml(value.color_code || '#2563eb');

        return `<div class="attribute-value-row" data-attribute-value-row>
            ${id}
            <input type="text" name="values[${index}][label]" class="form-control" value="${escapeHtml(value.label || '')}" placeholder="e.g. Red" required>
            <input type="color" name="values[${index}][color_code]" class="form-control attribute-color-input" value="${color}" aria-label="Color value">
            <input type="hidden" name="values[${index}][is_active]" value="0">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="values[${index}][is_active]" value="1" class="custom-control-input" id="manager-value-active-${index}" ${active ? 'checked' : ''}>
                <label class="custom-control-label" for="manager-value-active-${index}">Active</label>
            </div>
            <button type="button" class="btn btn-light text-danger" data-remove-manager-value aria-label="Remove value"><i class="fas fa-times"></i></button>
        </div>`;
    }

    function syncManagerColorFields() {
        $('#attributeManagerValueList .attribute-color-input').toggle($('#attributeManagerDisplayType').val() === 'color');
    }

    function resetAttributeManagerForm() {
        const form = $('#attributeManagerForm');
        if (!form.length) return;

        form[0].reset();
        form.attr('action', form.data('store-url') || form.attr('action'));
        $('#attributeManagerMethod').val('POST');
        $('#attributeManagerFormTitle').text('Create an attribute');
        $('#attributeManagerValueList').html(managerValueRow({ label: '', color_code: '#2563eb', is_active: true }, 0));
        $('#attributeManagerActive').prop('checked', true);
        $('#saveAttributeManagerForm').html('<i class="fas fa-save mr-1"></i> Create attribute').prop('disabled', form.attr('data-create-allowed') !== '1');
        $('#cancelAttributeEdit').addClass('d-none');
        managerValueIndex = 1;
        syncManagerColorFields();
    }

    function refreshAttributeManager() {
        $('#productAttributeModalBody').html('<div class="attribute-modal-loading"><i class="fas fa-circle-notch fa-spin"></i><span>Loading product attributes...</span></div>');

        $.ajax({
            url: '{{ route("admin.product-attributes.modal") }}',
            method: 'GET',
            dataType: 'json'
        }).done(function (response) {
            $('#productAttributeModalBody').html(response.html);
            productAttributes = response.productAttributes || [];
            renderVariantAttributeChoices(selectedAttributeValueIds);
            $('#variantRows [data-variant-row]').each(function () {
                const ids = JSON.parse($(this).attr('data-variant-values') || '[]').map(Number);
                const label = variationLabel(ids);
                if (label) $(this).find('.variant-option-label').text(label);
            });
            syncManagerColorFields();
        }).fail(function (xhr) {
            showAlert(xhr.responseJSON?.message || 'Could not load product attributes.', 'error');
            $('#productAttributeModal').modal('hide');
        });
    }

    function clearVariantBuilder() {
        $('#variantAttributeChoices [data-attribute-toggle]').prop('checked', false);
        $('#variantAttributeChoices [data-attribute-value]').prop('checked', false).prop('disabled', true);
        $('#variantRows').html('<tr><td colspan="7"><div class="variant-empty-state"><i class="fas fa-layer-group"></i>Select attributes, then generate your product variations.</div></td></tr>');
        $('#variantSummary').prop('hidden', true).empty();
        $('#err-variants').text('');
    }

    function setProductType(type) {
        const variable = type === 'variable';
        $('#simpleProductFields').toggleClass('d-none', variable).find(':input').prop('disabled', variable);
        $('#variableProductNotice, #variantTabNavItem').toggleClass('d-none', !variable);
    }

    function selectedAttributeGroups() {
        const groups = [];

        $('#variantAttributeChoices [data-attribute-card]').each(function () {
            const card = $(this);
            if (!card.find('[data-attribute-toggle]').is(':checked')) return;

            const values = card.find('[data-attribute-value]:checked').map(function () {
                const valueId = Number(this.value);
                const attribute = productAttributes.find((item) => Number(item.id) === Number(card.data('attributeId')));
                return attribute?.values.find((value) => Number(value.id) === valueId) || null;
            }).get().filter(Boolean);

            groups.push({
                id: Number(card.data('attributeId')),
                name: productAttributes.find((item) => Number(item.id) === Number(card.data('attributeId')))?.name || '',
                values
            });
        });

        return groups;
    }

    function currentVariantRows() {
        const current = new Map();
        $('#variantRows [data-variant-row]').each(function () {
            const row = $(this);
            const ids = JSON.parse(row.attr('data-variant-values') || '[]').map(Number).sort((a, b) => a - b);
            const key = ids.join('-');
            current.set(key, {
                id: row.find('[name$="[id]"]').val() || null,
                sku: row.find('[name$="[sku]"]').val(),
                regular_price: row.find('[name$="[regular_price]"]').val(),
                sale_price: row.find('[name$="[sale_price]"]').val(),
                stock_quantity: row.find('[name$="[stock_quantity]"]').val(),
                image_url: row.find('[data-variant-image]').attr('src') || null,
                attribute_value_ids: ids
            });
        });
        return current;
    }

    function renderVariantRows(variants) {
        if (!variants.length) {
            $('#variantRows').html('<tr><td colspan="7"><div class="variant-empty-state"><i class="fas fa-layer-group"></i>No variations generated yet.</div></td></tr>');
            $('#variantSummary').prop('hidden', true).empty();
            return;
        }

        const rows = variants.map((variant, index) => {
            const ids = [...variant.attribute_value_ids].map(Number);
            const hiddenValues = ids.map((id) => `<input type="hidden" name="variants[${index}][attribute_value_ids][]" value="${id}">`).join('');
            const idField = variant.id ? `<input type="hidden" name="variants[${index}][id]" value="${Number(variant.id)}">` : '';
            const image = variant.image_url ? `<img src="${escapeHtml(variant.image_url)}" data-variant-image alt="Variation image">` : '<img src="" data-variant-image class="d-none" alt="Variation image">';
            const label = escapeHtml(variant.label || 'Variation');
            const sku = escapeHtml(variant.sku || '');
            const regular = escapeHtml(variant.regular_price ?? '');
            const sale = escapeHtml(variant.sale_price ?? '');
            const stock = escapeHtml(variant.stock_quantity ?? 0);

            return `<tr data-variant-row data-variant-values='${JSON.stringify(ids)}'>
                <td>${idField}${hiddenValues}<span class="variant-option-label">${label}</span></td>
                <td><input type="text" name="variants[${index}][sku]" class="form-control" value="${sku}" required></td>
                <td><input type="number" name="variants[${index}][regular_price]" class="form-control" min="0" step="0.01" value="${regular}" required></td>
                <td><input type="number" name="variants[${index}][sale_price]" class="form-control" min="0" step="0.01" value="${sale}" placeholder="—"></td>
                <td><input type="number" name="variants[${index}][stock_quantity]" class="form-control" min="0" step="1" value="${stock}" required></td>
                <td><div class="variant-image-control">${image}<input type="file" name="variants[${index}][image]" accept="image/png,image/jpeg,image/webp"></div></td>
                <td><button type="button" class="variant-row-remove" data-remove-variant aria-label="Remove variation"><i class="fas fa-times"></i></button></td>
            </tr>`;
        }).join('');

        $('#variantRows').html(rows);
        const totalStock = variants.reduce((total, variant) => total + Math.max(0, Number(variant.stock_quantity || 0)), 0);
        $('#variantSummary').html(`<span>${variants.length} variation(s)</span><span>${totalStock} unit(s) total stock</span>`).prop('hidden', false);
    }

    function variationLabel(valueIds) {
        return valueIds.map((id) => {
            for (const attribute of productAttributes) {
                const value = attribute.values.find((item) => Number(item.id) === Number(id));
                if (value) return `${attribute.name}: ${value.label}`;
            }
            return '';
        }).filter(Boolean).join(' / ');
    }

    function generateVariations(existingVariants = null) {
        const groups = selectedAttributeGroups();
        if (!groups.length || groups.some((group) => !group.values.length)) {
            showAlert('Select at least one value for every chosen attribute.', 'error');
            return;
        }

        const previous = existingVariants ? new Map(existingVariants.map((item) => [item.attribute_value_ids.map(Number).sort((a, b) => a - b).join('-'), item])) : currentVariantRows();
        const combinations = groups.reduce((result, group) => result.flatMap((combination) => group.values.map((value) => [...combination, value])), [[]]);
        const baseSku = ($('#prod-sku').val() || 'PRODUCT').trim().toUpperCase();
        const variants = combinations.map((combination) => {
            const ids = combination.map((value) => Number(value.id)).sort((a, b) => a - b);
            const key = ids.join('-');
            const saved = previous.get(key) || {};
            const suffix = combination.map((value) => value.slug.toUpperCase().replace(/[^A-Z0-9]+/g, '-')).join('-');
            return {
                ...saved,
                attribute_value_ids: ids,
                label: combination.map((value) => `${groups.find((group) => group.values.some((item) => Number(item.id) === Number(value.id)))?.name}: ${value.label}`).join(' / '),
                sku: saved.sku || `${baseSku}-${suffix}`,
                regular_price: saved.regular_price ?? '',
                sale_price: saved.sale_price ?? '',
                stock_quantity: saved.stock_quantity ?? 0
            };
        });

        renderVariantRows(variants);
    }

    function loadProductVariants(product) {
        clearVariantBuilder();
        const variants = product.variants || [];
        const selectedAttributeIds = new Set();
        variants.forEach((variant) => {
            variant.attribute_value_ids.forEach((valueId) => {
                productAttributes.forEach((attribute) => {
                    if (attribute.values.some((value) => Number(value.id) === Number(valueId))) selectedAttributeIds.add(Number(attribute.id));
                });
            });
        });

        $('#variantAttributeChoices [data-attribute-card]').each(function () {
            const card = $(this);
            const attributeId = Number(card.data('attributeId'));
            const selected = selectedAttributeIds.has(attributeId);
            card.find('[data-attribute-toggle]').prop('checked', selected);
            card.find('[data-attribute-value]').prop('disabled', !selected).each(function () {
                $(this).prop('checked', variants.some((variant) => variant.attribute_value_ids.map(Number).includes(Number(this.value))));
            });
        });

        const prepared = variants.map((variant) => ({
            ...variant,
            label: variationLabel(variant.attribute_value_ids),
        }));
        renderVariantRows(prepared);
    }

    $('#prod-type').on('change', function () {
        setProductType(this.value);
    });

    $('#variantAttributeChoices').on('change', '[data-attribute-toggle]', function () {
        const card = $(this).closest('[data-attribute-card]');
        const enabled = $(this).is(':checked');
        card.find('[data-attribute-value]').prop('disabled', !enabled);
        if (!enabled) card.find('[data-attribute-value]').prop('checked', false);
    });

    $('#generateVariants').on('click', function () {
        generateVariations();
    });

    $('#openVariationsTab').on('click', function () {
        $('#variants-tab').tab('show');
    });

    $('#variantRows').on('click', '[data-remove-variant]', function () {
        $(this).closest('tr').remove();
        if (!$('#variantRows [data-variant-row]').length) {
            $('#variantRows').html('<tr><td colspan="7"><div class="variant-empty-state"><i class="fas fa-layer-group"></i>No variations generated yet.</div></td></tr>');
        }
        const count = $('#variantRows [data-variant-row]').length;
        const stock = $('#variantRows [name$="[stock_quantity]"]').toArray().reduce((total, input) => total + Math.max(0, Number(input.value || 0)), 0);
        $('#variantSummary').html(`<span>${count} variation(s)</span><span>${stock} unit(s) total stock</span>`).prop('hidden', count === 0);
        $('#variantRows [data-variant-row]').each(function (index) {
            $(this).find(':input[name]').each(function () {
                this.name = this.name.replace(/variants\[\d+\]/, `variants[${index}]`);
            });
        });
    });

    $('#variantRows').on('change', 'input[type="file"]', function () {
        const file = this.files?.[0];
        if (!file) return;
        const image = $(this).closest('tr').find('[data-variant-image]');
        image.attr('src', URL.createObjectURL(file)).removeClass('d-none');
    });

    $('#variantRows').on('input', '[name$="[stock_quantity]"]', function () {
        const count = $('#variantRows [data-variant-row]').length;
        const stock = $('#variantRows [name$="[stock_quantity]"]').toArray().reduce((total, input) => total + Math.max(0, Number(input.value || 0)), 0);
        $('#variantSummary').html(`<span>${count} variation(s)</span><span>${stock} unit(s) total stock</span>`);
    });

    setProductType('simple');

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
        clearVariantBuilder();
        setProductType('simple');
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
                    clearVariantBuilder();
                    $('#productFormMethod').val('PUT');
                    $('#productAjaxForm').attr('action', `/admin/products/${prodId}`);
                    $('#productFormModalTitle span').text('Update Product: ' + p.name);

                    // General
                    $('#prod-name').val(p.name);
                    $('#prod-slug').val(p.slug);
                    $('#prod-sku').val(p.sku);
                    $('#prod-type').val(p.product_type || 'simple');
                    setProductType(p.product_type || 'simple');
                    $('#prod-category').val(p.category_id);
                    $('#prod-regular-price').val(p.regular_price);
                    $('#prod-sale-price').val(p.sale_price);
                    $('#prod-stock').val(p.stock_quantity);
                    $('#prod-status').val(p.status);
                    $('#prod-featured').prop('checked', p.featured == 1);
                    $('#prod-short-desc').val(p.short_description);
                    $('#prod-description').val(p.description);
                    loadProductVariants(p);

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

        if ($('#prod-type').val() === 'variable' && !$('#variantRows [data-variant-row]').length) {
            showAlert('Generate at least one product variation before saving.', 'error');
            $('#variants-tab').tab('show');
            submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Product');
            return;
        }

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
                    let hasVariantError = false;
                    $.each(errors, function (key, messages) {
                        const cleanKey = key.replace(/\./g, '_');
                        $(`#err-${cleanKey}`).text(messages[0]);
                        const fieldName = key.split('.').reduce((name, part, index) => index === 0 ? part : `${name}[${part}]`, '');
                        $(`[name="${fieldName}"]`).addClass('is-invalid');
                        if (key.startsWith('variants')) {
                            hasVariantError = true;
                            $('#err-variants').text(messages[0]);
                        }
                    });
                    if (hasVariantError) $('#variants-tab').tab('show');
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
                    $('#showProdPrice').text('৳' + p.regular_price);
                    $('#showProdStatus').html(`<span class="badge badge-${p.status === 'Active' ? 'success' : 'secondary'}">${p.status}</span>`);
                    if (p.variants?.length) {
                        $('#showProdVariantsRows').html(p.variants.map((variant) => `<tr><td>${escapeHtml(variant.options)}</td><td><code>${escapeHtml(variant.sku)}</code></td><td>৳${escapeHtml(variant.price)}</td><td>${Number(variant.stock).toLocaleString()}</td></tr>`).join(''));
                        $('#showProdVariants').removeClass('d-none');
                    } else {
                        $('#showProdVariants').addClass('d-none');
                    }

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

    $(document).on('click', '#manageProductAttributes', function () {
        selectedAttributeValueIds = $('#variantAttributeChoices [data-attribute-value]:checked').map(function () {
            return Number(this.value);
        }).get();

        $('#productAttributeModal').modal({ backdrop: 'static', keyboard: false, show: true });
        refreshAttributeManager();
    });

    $('#productAttributeModal').on('show.bs.modal', function () {
        $(this).css('z-index', 1060);
    }).on('shown.bs.modal', function () {
        $('.modal-backdrop').not('.attribute-manager-backdrop').last().addClass('attribute-manager-backdrop');
        $('body').addClass('modal-open');
    }).on('hidden.bs.modal', function () {
        $('body').toggleClass('modal-open', $('#productFormModal').hasClass('show'));
        if ($('#productFormModal').hasClass('show')) $('#productFormModal').trigger('focus');
        $(this).css('z-index', '');
    });

    $(document).on('click', '#attributeManagerNew', resetAttributeManagerForm);
    $(document).on('click', '#cancelAttributeEdit', resetAttributeManagerForm);

    $(document).on('click', '#addManagerAttributeValue', function () {
        $('#attributeManagerValueList').append(managerValueRow({ label: '', color_code: '#2563eb', is_active: true }, managerValueIndex++));
        syncManagerColorFields();
    });

    $(document).on('click', '[data-remove-manager-value]', function () {
        const row = $(this).closest('[data-attribute-value-row]');
        if (row.find('input[name$="[id]"]').length) {
            row.find('input[type="checkbox"]').prop('checked', false);
            row.addClass('opacity-50');
            $(this).prop('disabled', true).attr('title', 'Existing value will be deactivated');
            return;
        }
        if ($('#attributeManagerValueList [data-attribute-value-row]').length > 1) row.remove();
    });

    $(document).on('change', '#attributeManagerDisplayType', syncManagerColorFields);

    $(document).on('click', '[data-edit-attribute]', function () {
        const button = $(this);
        const attribute = JSON.parse(decodeURIComponent(button.attr('data-edit-attribute')));
        const form = $('#attributeManagerForm');

        form.attr('action', form.data('update-url').replace('__ATTRIBUTE_ID__', attribute.id));
        $('#attributeManagerMethod').val('PUT');
        $('#attributeManagerName').val(attribute.name);
        $('#attributeManagerSlug').val(attribute.slug);
        $('#attributeManagerDisplayType').val(attribute.display_type);
        $('#attributeManagerActive').prop('checked', Boolean(attribute.is_active));
        $('#attributeManagerFormTitle').text('Edit ' + attribute.name);
        $('#attributeManagerValueList').html(attribute.values.map((value, index) => managerValueRow(value, index)).join(''));
        $('#saveAttributeManagerForm').html('<i class="fas fa-save mr-1"></i> Save changes').prop('disabled', form.attr('data-update-allowed') !== '1');
        $('#cancelAttributeEdit').removeClass('d-none');
        managerValueIndex = attribute.values.length;
        syncManagerColorFields();
    });

    $(document).on('submit', '#attributeManagerForm', function (e) {
        e.preventDefault();

        const form = $(this);
        const button = $('#saveAttributeManagerForm');
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (response) {
            showAlert(response.message, 'success');
            refreshAttributeManager();
        }).fail(function (xhr) {
            const errors = xhr.responseJSON?.errors;
            showAlert(errors ? Object.values(errors).flat() : (xhr.responseJSON?.message || 'Could not save the attribute.'), 'error');
        }).always(function () {
            button.prop('disabled', false);
        });
    });

    $(document).on('click', '[data-delete-attribute]', function () {
        const button = $(this);
        const remove = function () {
            $.ajax({
                url: button.data('url'),
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                dataType: 'json'
            }).done(function (response) {
                showAlert(response.message, 'success');
                refreshAttributeManager();
            }).fail(function (xhr) {
                showAlert(xhr.responseJSON?.message || 'This attribute could not be deleted.', 'error');
            });
        };

        if (!window.Swal || typeof window.Swal.fire !== 'function') {
            if (window.confirm(`Delete ${button.data('name')}?`)) remove();
            return;
        }

        window.Swal.fire({
            title: 'Delete attribute?',
            text: `${button.data('name')} will be removed if it is not used by existing variations.`,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.value) remove();
        });
    });
});
</script>