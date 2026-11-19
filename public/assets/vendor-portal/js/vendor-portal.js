(function () {
    'use strict';

    const body = document.body;
    const toggle = document.querySelector('[data-vp-sidebar-toggle]');
    const close = document.querySelectorAll('[data-vp-sidebar-close]');
    const sidebarLinks = document.querySelectorAll('#vpSidebar a');

    const closeSidebar = () => body.classList.remove('is-sidebar-open');

    if (toggle) {
        toggle.addEventListener('click', () => body.classList.toggle('is-sidebar-open'));
    }

    close.forEach((element) => element.addEventListener('click', closeSidebar));
    sidebarLinks.forEach((link) => link.addEventListener('click', closeSidebar));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSidebar();
    });

    const productType = document.querySelector('[data-vp-product-type]');
    const simpleFields = document.querySelector('[data-vp-simple-fields]');
    const variationPanel = document.querySelector('[data-vp-variation-panel]');
    const productForm = document.querySelector('[data-vp-product-form]');
    const attributeChoices = document.querySelector('[data-vp-attribute-choices]');
    const variantRows = document.querySelector('[data-vp-variant-rows]');
    const variantSummary = document.querySelector('[data-vp-variant-summary]');
    const variantError = document.querySelector('[data-vp-variant-error]');
    const generateButton = document.querySelector('[data-vp-generate-variants]');

    const readJson = (selector) => {
        const element = document.querySelector(selector);
        if (!element) return [];
        try {
            return JSON.parse(element.textContent || '[]');
        } catch (_) {
            return [];
        }
    };

    const attributes = readJson('[data-vp-product-attributes]');
    const initialVariants = readJson('[data-vp-initial-variants]');

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);

    const syncProductType = () => {
        if (!productType) return;
        const isVariable = productType.value === 'variable';
        if (simpleFields) simpleFields.hidden = isVariable;
        if (variationPanel) variationPanel.hidden = !isVariable;
    };

    if (productType) {
        productType.addEventListener('change', syncProductType);
        syncProductType();
    }

    if (!productForm || !attributeChoices || !variantRows) return;

    const selectedGroups = () => Array.from(attributeChoices.querySelectorAll('[data-vp-attribute-card]'))
        .map((card) => {
            const toggle = card.querySelector('[data-vp-attribute-toggle]');
            if (!toggle?.checked) return null;

            const attribute = attributes.find((item) => Number(item.id) === Number(card.dataset.attributeId));
            const values = Array.from(card.querySelectorAll('[data-vp-attribute-value]:checked'))
                .map((input) => attribute?.values.find((value) => Number(value.id) === Number(input.value)))
                .filter(Boolean);

            return { id: Number(card.dataset.attributeId), name: attribute?.name || '', values };
        })
        .filter(Boolean);

    const variationLabel = (valueIds) => valueIds.map((id) => {
        for (const attribute of attributes) {
            const value = attribute.values.find((item) => Number(item.id) === Number(id));
            if (value) return `${attribute.name}: ${value.label}`;
        }
        return '';
    }).filter(Boolean).join(' / ');

    const currentVariants = () => {
        const current = new Map();
        variantRows.querySelectorAll('[data-vp-variant-row]').forEach((row) => {
            const ids = JSON.parse(row.dataset.variantValues || '[]').map(Number).sort((a, b) => a - b);
            current.set(ids.join('-'), {
                id: row.querySelector('[name$="[id]"]')?.value || null,
                sku: row.querySelector('[name$="[sku]"]')?.value || '',
                regular_price: row.querySelector('[name$="[regular_price]"]')?.value || '',
                sale_price: row.querySelector('[name$="[sale_price]"]')?.value || '',
                stock_quantity: row.querySelector('[name$="[stock_quantity]"]')?.value || 0,
                image_url: row.querySelector('[data-vp-variant-image]')?.getAttribute('src') || null,
                attribute_value_ids: ids
            });
        });
        return current;
    };

    const updateSummary = () => {
        const rows = Array.from(variantRows.querySelectorAll('[data-vp-variant-row]'));
        if (!rows.length) {
            variantSummary.hidden = true;
            variantSummary.textContent = '';
            return;
        }

        const stock = rows.reduce((total, row) => total + Math.max(0, Number(row.querySelector('[name$="[stock_quantity]"]')?.value || 0)), 0);
        variantSummary.textContent = `${rows.length} variation(s) · ${stock.toLocaleString()} unit(s) total stock`;
        variantSummary.hidden = false;
    };

    const renumberRows = () => {
        variantRows.querySelectorAll('[data-vp-variant-row]').forEach((row, index) => {
            row.querySelectorAll('input[name], select[name], textarea[name]').forEach((input) => {
                input.name = input.name.replace(/variants\[\d+\]/, `variants[${index}]`);
            });
        });
        updateSummary();
    };

    const renderVariants = (variants) => {
        if (!variants.length) {
            variantRows.innerHTML = '<tr><td colspan="7" class="vp-table-empty">Choose attribute values and generate variations.</td></tr>';
            updateSummary();
            return;
        }

        variantRows.innerHTML = variants.map((variant, index) => {
            const ids = variant.attribute_value_ids.map(Number);
            const hiddenValues = ids.map((id) => `<input type="hidden" name="variants[${index}][attribute_value_ids][]" value="${id}">`).join('');
            const idField = variant.id ? `<input type="hidden" name="variants[${index}][id]" value="${Number(variant.id)}">` : '';
            const image = variant.image_url
                ? `<img src="${escapeHtml(variant.image_url)}" data-vp-variant-image alt="Variation image">`
                : '<img src="" data-vp-variant-image alt="Variation image" hidden>';

            return `<tr data-vp-variant-row data-variant-values='${JSON.stringify(ids)}'>
                <td>${idField}${hiddenValues}<span class="vp-variant-option-label">${escapeHtml(variant.label || variationLabel(ids))}</span></td>
                <td><input type="text" name="variants[${index}][sku]" value="${escapeHtml(variant.sku || '')}" maxlength="100" required></td>
                <td><input type="number" name="variants[${index}][regular_price]" min="0" step="0.01" value="${escapeHtml(variant.regular_price ?? '')}" required></td>
                <td><input type="number" name="variants[${index}][sale_price]" min="0" step="0.01" value="${escapeHtml(variant.sale_price ?? '')}"></td>
                <td><input type="number" name="variants[${index}][stock_quantity]" min="0" step="1" value="${escapeHtml(variant.stock_quantity ?? 0)}" required></td>
                <td><div class="vp-variant-image-control">${image}<label><i class="fas fa-camera"></i><input type="file" name="variants[${index}][image]" accept="image/png,image/jpeg,image/webp" hidden></label></div></td>
                <td><button type="button" class="vp-icon-button vp-variant-remove" data-vp-remove-variant aria-label="Remove variation"><i class="fas fa-times"></i></button></td>
            </tr>`;
        }).join('');

        updateSummary();
    };

    const generateVariants = (savedVariants = null) => {
        const groups = selectedGroups();
        if (!groups.length || groups.some((group) => !group.values.length)) {
            variantError.textContent = 'Select at least one value for each chosen attribute.';
            variantError.hidden = false;
            return;
        }

        variantError.hidden = true;
        const previous = savedVariants
            ? new Map(savedVariants.map((item) => [item.attribute_value_ids.map(Number).sort((a, b) => a - b).join('-'), item]))
            : currentVariants();
        const combinations = groups.reduce((result, group) => result.flatMap((combination) => group.values.map((value) => [...combination, value])), [[]]);
        const baseSku = (productForm.querySelector('[name="sku"]')?.value || 'PRODUCT').trim().toUpperCase();

        const variants = combinations.map((combination) => {
            const ids = combination.map((value) => Number(value.id)).sort((a, b) => a - b);
            const saved = previous.get(ids.join('-')) || {};
            const suffix = combination.map((value) => String(value.slug || value.label).toUpperCase().replace(/[^A-Z0-9]+/g, '-').replace(/^-|-$/g, '')).join('-');

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

        renderVariants(variants);
    };

    attributeChoices.addEventListener('change', (event) => {
        if (event.target.matches('[data-vp-attribute-toggle]')) {
            const card = event.target.closest('[data-vp-attribute-card]');
            card.querySelectorAll('[data-vp-attribute-value]').forEach((input) => {
                input.disabled = !event.target.checked;
                if (!event.target.checked) input.checked = false;
            });
        }
    });

    generateButton?.addEventListener('click', () => generateVariants());

    variantRows.addEventListener('click', (event) => {
        const button = event.target.closest('[data-vp-remove-variant]');
        if (!button) return;
        button.closest('[data-vp-variant-row]').remove();
        if (!variantRows.querySelector('[data-vp-variant-row]')) {
            variantRows.innerHTML = '<tr><td colspan="7" class="vp-table-empty">Choose attribute values and generate variations.</td></tr>';
        }
        renumberRows();
    });

    variantRows.addEventListener('change', (event) => {
        if (!event.target.matches('input[type="file"]')) return;
        const file = event.target.files?.[0];
        if (!file) return;
        const image = event.target.closest('tr').querySelector('[data-vp-variant-image]');
        image.src = URL.createObjectURL(file);
        image.hidden = false;
    });

    variantRows.addEventListener('input', (event) => {
        if (event.target.matches('[name$="[stock_quantity]"]')) updateSummary();
    });

    productForm.addEventListener('submit', (event) => {
        if (productType?.value === 'variable' && !variantRows.querySelector('[data-vp-variant-row]')) {
            event.preventDefault();
            variantError.textContent = 'Generate at least one variation before saving this product.';
            variantError.hidden = false;
            variationPanel?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    const selectedIds = new Set(initialVariants.flatMap((variant) => (variant.attribute_value_ids || []).map(Number)));
    attributeChoices.querySelectorAll('[data-vp-attribute-card]').forEach((card) => {
        const inputs = Array.from(card.querySelectorAll('[data-vp-attribute-value]'));
        const hasSelected = inputs.some((input) => selectedIds.has(Number(input.value)) || input.checked);
        const toggleInput = card.querySelector('[data-vp-attribute-toggle]');
        if (hasSelected && toggleInput) toggleInput.checked = true;
        inputs.forEach((input) => {
            if (selectedIds.has(Number(input.value))) input.checked = true;
            input.disabled = !toggleInput?.checked;
        });
    });

    if (initialVariants.length) generateVariants(initialVariants);
})();
