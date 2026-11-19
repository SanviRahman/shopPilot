(() => {
    'use strict';

    const onReady = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    };

    onReady(() => {
        const mainImage = document.querySelector('[data-product-main-image]');
        const thumbnails = [...document.querySelectorAll('[data-product-thumb]')];
        const lightbox = document.querySelector('[data-product-lightbox]');
        const lightboxImage = document.querySelector('[data-product-lightbox-image]');
        const zoomButton = document.querySelector('[data-product-zoom]');
        const lightboxClose = document.querySelector('[data-product-lightbox-close]');

        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', () => {
                const imageUrl = thumbnail.dataset.image;

                if (! imageUrl || ! mainImage) {
                    return;
                }

                thumbnails.forEach((item) => item.classList.remove('active'));
                thumbnail.classList.add('active');
                mainImage.src = imageUrl;

                if (lightboxImage) {
                    lightboxImage.src = imageUrl;
                }
            });
        });

        const openLightbox = () => {
            if (! lightbox || ! lightboxImage || ! mainImage) {
                return;
            }

            lightboxImage.src = mainImage.src;
            lightbox.classList.add('open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const closeLightbox = () => {
            if (! lightbox) {
                return;
            }

            lightbox.classList.remove('open');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        zoomButton?.addEventListener('click', openLightbox);
        lightboxClose?.addEventListener('click', closeLightbox);
        lightbox?.addEventListener('click', (event) => {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && lightbox?.classList.contains('open')) {
                closeLightbox();
            }
        });

        document.querySelectorAll('[data-product-quantity]').forEach((control) => {
            const input = control.querySelector('[data-qty-input]');
            const minus = control.querySelector('[data-qty-minus]');
            const plus = control.querySelector('[data-qty-plus]');

            if (! input) {
                return;
            }

            const min = Math.max(1, Number.parseInt(input.min || '1', 10));
            const getMax = () => {
                const maxFromControl = Number.parseInt(control.dataset.max || '', 10);
                const maxFromInput = Number.parseInt(input.max || '', 10);
                return Number.isFinite(maxFromControl)
                    ? Math.max(1, maxFromControl)
                    : (Number.isFinite(maxFromInput) ? Math.max(1, maxFromInput) : Number.MAX_SAFE_INTEGER);
            };

            const normalize = (value) => Math.min(getMax(), Math.max(min, Number.parseInt(value, 10) || min));
            const setQuantity = (value) => {
                input.value = String(normalize(value));
            };

            minus?.addEventListener('click', () => setQuantity(Number(input.value) - 1));
            plus?.addEventListener('click', () => setQuantity(Number(input.value) + 1));
            input.addEventListener('change', () => setQuantity(input.value));
        });

        const variantData = document.querySelector('#product-variant-data');
        const variantGroups = [...document.querySelectorAll('[data-variant-group]')];

        if (variantData && variantGroups.length) {
            const variants = JSON.parse(variantData.textContent || '[]');
            const variantForm = document.querySelector('.product-order-form');
            const variantInput = variantForm?.querySelector('[data-selected-variant-id]');
            const message = document.querySelector('[data-variant-message]');
            const sku = document.querySelector('[data-product-sku]');
            const stockText = document.querySelector('[data-product-stock]');
            const availability = document.querySelector('[data-product-availability]');
            const orderStockLabel = document.querySelector('[data-order-stock-label]');
            const quantityControl = document.querySelector('[data-product-quantity]');
            const quantityInput = quantityControl?.querySelector('[data-qty-input]');
            const addButton = document.querySelector('[data-product-add-to-cart]');
            const buyNowButton = document.querySelector('[data-product-buy-now]');
            const currentPrice = document.querySelector('[data-product-current-price]');
            const regularPrice = document.querySelector('[data-product-regular-price]');
            const saving = document.querySelector('[data-product-saving]');
            const mainImage = document.querySelector('[data-product-main-image]');
            const lightboxImage = document.querySelector('[data-product-lightbox-image]');
            const defaultImage = mainImage?.dataset.baseImage || mainImage?.src || '';
            const discountBadge = document.querySelector('[data-product-detail-discount]');
            const selections = new Map();

            variantGroups.forEach((group) => {
                const selected = group.querySelector('[data-variant-option].active');
                const select = group.querySelector('[data-variant-select]');
                if (selected) selections.set(Number(group.dataset.attributeId), Number(selected.dataset.valueId));
                else if (select?.value) selections.set(Number(group.dataset.attributeId), Number(select.value));
            });

            const updateAvailability = (stock) => {
                const inStock = stock > 0;
                if (availability) {
                    availability.classList.toggle('in-stock', inStock);
                    availability.classList.toggle('out-of-stock', !inStock);
                    availability.innerHTML = `<i class="fas ${inStock ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${inStock ? 'In Stock' : 'Out of Stock'}`;
                }
                if (orderStockLabel) {
                    orderStockLabel.textContent = inStock ? (stock <= 5 ? `Only ${stock} left` : 'Ready to order') : 'Currently unavailable';
                    orderStockLabel.classList.toggle('danger', !inStock);
                }
                if (addButton) {
                    addButton.disabled = !inStock;
                    const label = addButton.querySelector('span');
                    if (label) label.textContent = inStock ? 'Add to Cart' : 'Out of Stock';
                }
                if (buyNowButton) buyNowButton.disabled = !inStock;
                if (stockText) stockText.textContent = Number(stock).toLocaleString();
                if (quantityControl && quantityInput) {
                    quantityControl.dataset.max = String(Math.max(1, stock));
                    quantityInput.max = String(Math.max(1, stock));
                    quantityInput.value = String(Math.min(Math.max(1, Number(quantityInput.value || 1)), Math.max(1, stock)));
                }
            };

            const updatePrice = (variant) => {
                const regular = Number(variant.regular_price || 0);
                const sale = variant.sale_price !== null ? Number(variant.sale_price) : null;
                const hasSale = sale !== null && sale > 0 && sale < regular;
                const price = hasSale ? sale : regular;
                if (currentPrice) currentPrice.textContent = `৳${Math.round(price).toLocaleString()}`;
                if (regularPrice) {
                    regularPrice.textContent = `৳${Math.round(regular).toLocaleString()}`;
                    regularPrice.classList.toggle('d-none', !hasSale);
                }
                if (saving) {
                    const amount = Math.round(regular - price);
                    const percent = regular > 0 ? Math.round(((regular - price) / regular) * 100) : 0;
                    saving.textContent = `Save ৳${amount.toLocaleString()} (${percent}%)`;
                    saving.classList.toggle('d-none', !hasSale);
                }
                if (discountBadge) {
                    const percent = regular > 0 ? Math.round(((regular - price) / regular) * 100) : 0;
                    discountBadge.textContent = `-${percent}%`;
                    discountBadge.classList.toggle('d-none', !hasSale);
                }
            };

            const findVariant = () => variants.find((variant) =>
                variant.attributes.length === selections.size && variant.attributes.every((attribute) =>
                    selections.get(Number(attribute.attribute_id)) === Number(attribute.value_id)
                )
            ) || null;

            const updateVariant = () => {
                const variant = findVariant();
                if (!variant) {
                    if (variantInput) variantInput.value = '';
                    if (message) {
                        message.textContent = 'This combination is not available. Choose another option.';
                        message.classList.add('is-error');
                    }
                    updateAvailability(0);
                    return;
                }

                if (variantInput) variantInput.value = variant.id;
                if (sku) sku.textContent = variant.sku;
                if (message) {
                    message.textContent = variant.stock_quantity > 0 ? 'Your selected combination is ready.' : 'This variation is currently out of stock.';
                    message.classList.toggle('is-error', variant.stock_quantity <= 0);
                }
                updatePrice(variant);
                updateAvailability(Number(variant.stock_quantity || 0));

                const imageUrl = variant.image_url || defaultImage;
                if (mainImage && imageUrl) mainImage.src = imageUrl;
                if (lightboxImage && imageUrl) lightboxImage.src = imageUrl;
                document.querySelectorAll('[data-product-thumb]').forEach((thumb) => {
                    thumb.classList.toggle('active', thumb.dataset.image === imageUrl);
                });
            };

            variantGroups.forEach((group) => {
                group.addEventListener('change', (event) => {
                    const select = event.target.closest('[data-variant-select]');
                    if (!select) return;
                    const attributeId = Number(group.dataset.attributeId);
                    const valueId = Number(select.value);
                    if (valueId) selections.set(attributeId, valueId);
                    else selections.delete(attributeId);
                    const label = group.querySelector('[data-variant-selected-label]');
                    if (label) label.textContent = select.selectedOptions[0]?.dataset.valueLabel || 'Choose an option';
                    updateVariant();
                });

                group.addEventListener('click', (event) => {
                    const option = event.target.closest('[data-variant-option]');
                    if (!option) return;
                    const attributeId = Number(group.dataset.attributeId);
                    const valueId = Number(option.dataset.valueId);
                    selections.set(attributeId, valueId);
                    group.querySelectorAll('[data-variant-option]').forEach((button) => {
                        const active = button === option;
                        button.classList.toggle('active', active);
                        button.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });
                    const label = group.querySelector('[data-variant-selected-label]');
                    if (label) label.textContent = option.dataset.valueLabel || option.textContent.trim();
                    updateVariant();
                });
            });

            variantForm?.addEventListener('submit', (event) => {
                if (!variantInput?.value) {
                    event.preventDefault();
                    message?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    message?.classList.add('is-error');
                }
            });

            updateVariant();
        }

        const tabs = [...document.querySelectorAll('[data-product-tab]')];
        const panels = [...document.querySelectorAll('[data-product-panel]')];

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.productTab;

                tabs.forEach((item) => {
                    const active = item === tab;
                    item.classList.toggle('active', active);
                    item.setAttribute('aria-selected', active ? 'true' : 'false');
                });

                panels.forEach((panel) => {
                    const active = panel.dataset.productPanel === target;
                    panel.classList.toggle('active', active);
                    panel.hidden = ! active;
                });
            });
        });

        const relatedTrack = document.querySelector('[data-related-track]');
        const relatedPrev = document.querySelector('[data-related-prev]');
        const relatedNext = document.querySelector('[data-related-next]');

        const scrollRelated = (direction) => {
            if (! relatedTrack) {
                return;
            }

            const item = relatedTrack.querySelector('.related-product-item');
            const amount = item ? item.getBoundingClientRect().width + 12 : relatedTrack.clientWidth * 0.8;
            relatedTrack.scrollBy({ left: direction * amount, behavior: 'smooth' });
        };

        relatedPrev?.addEventListener('click', () => scrollRelated(-1));
        relatedNext?.addEventListener('click', () => scrollRelated(1));

        const revealItems = [...document.querySelectorAll('[data-product-reveal]')];

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, currentObserver) => {
                entries.forEach((entry) => {
                    if (! entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    currentObserver.unobserve(entry.target);
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });

            revealItems.forEach((item, index) => {
                item.style.setProperty('--product-reveal-delay', `${Math.min(index * 45, 220)}ms`);
                observer.observe(item);
            });
        } else {
            revealItems.forEach((item) => item.classList.add('is-visible'));
        }
    });
})();
