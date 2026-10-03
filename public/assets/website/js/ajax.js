(() => {
    'use strict';

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const toast = (message, type = 'success') => {
        if (!message) return;
        document.dispatchEvent(new CustomEvent('shoppilot:toast', { detail: { message, type } }));
    };

    const firstError = (payload, fallback = 'Something went wrong. Please try again.') => {
        if (payload?.errors && typeof payload.errors === 'object') {
            const first = Object.values(payload.errors).flat().find(Boolean);
            if (first) return String(first);
        }
        return payload?.message || fallback;
    };

    const request = async (url, options = {}) => {
        const headers = new Headers(options.headers || {});
        headers.set('Accept', 'application/json');
        headers.set('X-Requested-With', 'XMLHttpRequest');

        const method = String(options.method || 'GET').toUpperCase();
        let body = options.body ?? null;

        if (options.form instanceof HTMLFormElement) {
            body = new FormData(options.form);
        } else if (options.data && !(options.data instanceof FormData)) {
            headers.set('Content-Type', 'application/json');
            body = JSON.stringify(options.data);
        } else if (options.data instanceof FormData) {
            body = options.data;
        }

        if (method !== 'GET' && method !== 'HEAD' && csrf()) {
            headers.set('X-CSRF-TOKEN', csrf());
        }

        const response = await fetch(url, {
            method,
            body: method === 'GET' || method === 'HEAD' ? null : body,
            headers,
            credentials: 'same-origin',
            redirect: 'follow',
            signal: options.signal,
        });

        const contentType = response.headers.get('content-type') || '';
        let payload = null;

        if (contentType.includes('application/json')) {
            payload = await response.json();
        } else {
            const text = await response.text();
            payload = {
                message: response.ok
                    ? (text || response.statusText)
                    : (response.status === 419 ? 'Your session expired. Refresh the page and try again.' : (response.statusText || `Request failed with status ${response.status}.`)),
            };
        }

        if (payload?.csrf_token) {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', payload.csrf_token);
        }

        if (!response.ok) {
            const error = new Error(firstError(payload));
            error.status = response.status;
            error.payload = payload;
            throw error;
        }

        return payload;
    };

    const fetchFragment = async (url, selector, options = {}) => {
        const current = document.querySelector(options.targetSelector || selector);
        if (!current) throw new Error(`AJAX target not found: ${options.targetSelector || selector}`);

        current.classList.add('ajax-is-loading');

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json, text/html;q=0.9',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: options.signal,
            });

            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}.`);
            }

            const contentType = response.headers.get('content-type') || '';
            let payload = null;
            let html = '';

            if (contentType.includes('application/json')) {
                payload = await response.json();
                html = String(payload?.html || '');
            } else {
                html = await response.text();
            }

            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const incoming = parsed.querySelector(selector);

            if (!incoming) {
                if (response.redirected && response.url) {
                    window.location.assign(response.url);
                    return { document: parsed, node: current, url: response.url, payload };
                }
                throw new Error(`AJAX response fragment not found: ${selector}`);
            }

            const imported = document.importNode(incoming, true);
            current.replaceWith(imported);

            const historyUrl = payload?.url || response.url || url;
            if (options.pushState !== false) {
                window.history.pushState({ shopPilotAjax: true }, '', historyUrl);
            }

            return { document: parsed, node: imported, url: historyUrl, payload };
        } finally {
            document.querySelector(options.targetSelector || selector)?.classList.remove('ajax-is-loading');
        }
    };

    const setButtonLoading = (button, loading, text = 'Please wait…') => {
        if (!button) return;
        if (loading) {
            button.dataset.ajaxOriginalHtml ??= button.innerHTML;
            button.disabled = true;
            button.classList.add('ajax-button-loading');
            button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
            return;
        }

        button.disabled = false;
        button.classList.remove('ajax-button-loading');
        if (button.dataset.ajaxOriginalHtml) {
            button.innerHTML = button.dataset.ajaxOriginalHtml;
        }
    };

    const clearFormErrors = (form) => {
        if (!form) return;
        form.querySelectorAll('.ajax-field-error').forEach((node) => node.remove());
        form.querySelectorAll('.has-error, .is-invalid').forEach((node) => {
            node.classList.remove('has-error', 'is-invalid');
        });
    };

    const showFormErrors = (form, errors = {}) => {
        if (!form) return;
        clearFormErrors(form);

        Object.entries(errors).forEach(([name, messages]) => {
            const field = form.querySelector(`[name="${CSS.escape(name)}"]`);
            if (!field) return;

            const shell = field.closest('.auth-input, .input-shell, .profile-phone-shell, .password-input-shell, .track-input-shell, .checkout-field, .profile-field, label') || field;
            shell.classList.add('has-error');
            field.classList.add('is-invalid');

            const error = document.createElement('small');
            error.className = 'field-error ajax-field-error';
            error.textContent = Array.isArray(messages) ? messages[0] : String(messages);

            const insertionTarget = field.closest('.auth-input, .input-shell, .profile-phone-shell, .password-input-shell, .track-input-shell') || field;
            insertionTarget.insertAdjacentElement('afterend', error);
        });
    };

    const updateCartHeader = (cart) => {
        if (!cart) return;
        const count = Number(cart.count || 0);
        const total = Number(cart.grand_total || 0);

        document.querySelectorAll('[data-header-cart-count]').forEach((node) => {
            node.textContent = String(count);
        });
        document.querySelectorAll('[data-header-cart-total]').forEach((node) => {
            node.textContent = `৳${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        });
        document.querySelectorAll('[data-mobile-cart-count]').forEach((node) => {
            node.textContent = String(count);
        });
    };

    const updateWishlistHeader = (wishlist) => {
        if (!wishlist) return;
        const count = Number(wishlist.count || 0);
        document.querySelectorAll('[data-header-wishlist-count]').forEach((node) => {
            node.textContent = String(count);
        });
    };

    const syncWishlistButtons = (productId, active) => {
        document.querySelectorAll(`[data-wishlist-toggle][data-product-id="${CSS.escape(String(productId))}"]`).forEach((button) => {
            button.dataset.wishlisted = active ? '1' : '0';
            button.classList.toggle('active', active);
            const icon = button.querySelector('.fa-heart');
            icon?.classList.toggle('fas', active);
            icon?.classList.toggle('far', !active);
            const label = button.querySelector('[data-wishlist-label]');
            if (label) label.textContent = active ? 'Saved to Wishlist' : 'Add to Wishlist';
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    };

    const toggleWishlist = async (button) => {
        const productId = Number(button.dataset.productId || 0);
        if (!productId) return;

        if (document.body?.dataset.authenticated !== '1') {
            window.location.assign(document.body?.dataset.loginUrl || '/login');
            return;
        }

        const active = button.dataset.wishlisted === '1';
        const url = active ? button.dataset.wishlistDestroyUrl : button.dataset.wishlistStoreUrl;
        if (!url) return;

        button.disabled = true;
        try {
            const payload = await request(url, {
                method: active ? 'DELETE' : 'POST',
                data: active ? undefined : { product_id: productId },
            });
            const nowActive = Boolean(payload?.wishlist?.active);
            syncWishlistButtons(productId, nowActive);
            updateWishlistHeader(payload?.wishlist);
            toast(payload?.message || (nowActive ? 'Saved to wishlist.' : 'Removed from wishlist.'));
            document.dispatchEvent(new CustomEvent('shoppilot:wishlist-changed', {
                detail: {
                    ...payload,
                    productId,
                    active: nowActive,
                    card_html: payload?.card_html || null,
                    in_stock: Boolean(payload?.in_stock),
                },
            }));
        } catch (error) {
            toast(firstError(error.payload, error.message), 'error');
        } finally {
            button.disabled = false;
        }
    };

    const trackMeta = (event) => {
        if (!event?.name || !window.ShopPilotMeta?.track) return;
        window.ShopPilotMeta.track(event.name, event.payload || {});
    };

    const submitProductCartForm = async (form, submitter = null) => {
        const button = submitter || form.querySelector('button[type="submit"]');
        setButtonLoading(button, true, 'Adding…');

        try {
            const formData = new FormData(form);

            // FormData(form) does not include the clicked submit button in all browsers.
            // Preserve redirect_to so Add to Cart stays on the page while Buy Now can navigate.
            if (submitter?.name) {
                formData.set(submitter.name, submitter.value || '');
            }

            const payload = await request(form.action, { method: 'POST', data: formData });
            updateCartHeader(payload.cart);
            trackMeta(payload.meta_event);
            document.dispatchEvent(new CustomEvent('shoppilot:cart-changed', { detail: payload }));

            if (payload?.redirect_url) {
                window.location.assign(payload.redirect_url);
                return;
            }

            toast(payload.message || 'Product added to your cart.');
        } catch (error) {
            toast(firstError(error.payload, error.message), 'error');
        } finally {
            setButtonLoading(button, false);
        }
    };

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.product-card-cart-form, .product-order-form');
        if (!form) return;
        event.preventDefault();
        submitProductCartForm(form, event.submitter || null);
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-wishlist-toggle]');
        if (!button) return;
        event.preventDefault();
        toggleWishlist(button);
    });

    window.ShopPilotAjax = {
        csrf,
        request,
        fetchFragment,
        toast,
        firstError,
        setButtonLoading,
        clearFormErrors,
        showFormErrors,
        updateCartHeader,
        updateWishlistHeader,
        syncWishlistButtons,
        trackMeta,
    };
})();
