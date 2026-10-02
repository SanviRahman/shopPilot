(() => {
    'use strict';

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ajax = () => window.ShopPilotAjax;
    let searchTimer = null;
    let activeController = null;

    const setupReveal = (root = document) => {
        const targets = [...root.querySelectorAll('[data-account-reveal]')].filter((el) => !el.classList.contains('is-visible'));
        targets.forEach((el, index) => el.style.setProperty('--account-delay', `${Math.min(index * 45, 260)}ms`));
        if (reduced || !('IntersectionObserver' in window)) {
            targets.forEach((el) => el.classList.add('is-visible'));
            return;
        }
        const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        }), { threshold: .08 });
        targets.forEach((el) => observer.observe(el));
        window.setTimeout(() => targets.forEach((el) => el.classList.add('is-visible')), 1200);
    };

    const currentRegionSelector = () => {
        if (document.querySelector('[data-account-orders-region]')) return '[data-account-orders-region]';
        if (document.querySelector('[data-account-payments-region]')) return '[data-account-payments-region]';
        return null;
    };

    const loadRegion = async (url, pushState = true) => {
        const selector = currentRegionSelector();
        if (!selector || !ajax()) {
            window.location.assign(url.toString());
            return;
        }

        activeController?.abort();
        activeController = new AbortController();

        try {
            const result = await ajax().fetchFragment(url.toString(), selector, {
                pushState,
                signal: activeController.signal,
            });
            setupReveal(result.node);
        } catch (error) {
            if (error.name === 'AbortError') return;
            ajax().toast(error.message || 'Unable to refresh account data.', 'error');
        }
    };

    const submitOrderSearch = (form) => {
        const url = new URL(form.action, window.location.origin);
        url.search = new URLSearchParams(new FormData(form)).toString();
        loadRegion(url);
    };

    const setWishlistStat = (selector, value) => {
        const node = document.querySelector(selector);
        if (node) node.textContent = String(Math.max(0, Number(value) || 0));
    };

    const wishlistEmptyMarkup = () => `
        <div class="wishlist-empty" data-wishlist-empty>
            <span><i class="far fa-heart"></i></span>
            <h2>Your wishlist is empty</h2>
            <p>Save products you love and they'll appear here for easy access later.</p>
            <a href="/shop" class="primary-account-button"><i class="fas fa-shopping-bag"></i> Explore Products</a>
        </div>`;

    const updateWishlistControls = (count, inStock = null) => {
        document.querySelectorAll('[data-wishlist-clear] button').forEach((button) => {
            button.disabled = count <= 0;
        });
        document.querySelectorAll('[data-wishlist-add-all] button').forEach((button) => {
            button.disabled = count <= 0 || (inStock !== null && inStock <= 0);
        });
    };

    const removeWishlistCard = (productId) => {
        const card = document.querySelector(`[data-wishlist-card="${CSS.escape(String(productId))}"]`);
        if (!card) return;

        const totalNode = document.querySelector('[data-wishlist-page-count]');
        const inStockNode = document.querySelector('[data-wishlist-in-stock-count]');
        const outStockNode = document.querySelector('[data-wishlist-out-stock-count]');
        const total = Math.max(0, Number(totalNode?.textContent || 0) - 1);
        const inStock = Math.max(0, Number(inStockNode?.textContent || 0) - (card.dataset.stock === '1' ? 1 : 0));
        const outStock = Math.max(0, Number(outStockNode?.textContent || 0) - (card.dataset.stock === '1' ? 0 : 1));

        card.style.opacity = '0';
        card.style.transform = 'scale(.96)';
        window.setTimeout(() => card.remove(), reduced ? 0 : 180);

        setWishlistStat('[data-wishlist-page-count]', total);
        setWishlistStat('[data-wishlist-in-stock-count]', inStock);
        setWishlistStat('[data-wishlist-out-stock-count]', outStock);
        updateWishlistControls(total, inStock);

        if (total === 0) {
            window.setTimeout(() => {
                const section = document.querySelector('.wishlist-products-section');
                if (section) section.innerHTML = wishlistEmptyMarkup();
            }, reduced ? 0 : 200);
        }
    };

    const addWishlistCard = (detail) => {
        if (!detail?.productId || !detail?.card_html) return;
        if (document.querySelector(`[data-wishlist-card="${CSS.escape(String(detail.productId))}"]`)) return;

        const section = document.querySelector('.wishlist-products-section');
        if (!section) return;

        let grid = section.querySelector('[data-wishlist-grid]');
        if (!grid) {
            section.innerHTML = '<div class="wishlist-grid" data-wishlist-grid></div>';
            grid = section.querySelector('[data-wishlist-grid]');
        }

        const template = document.createElement('template');
        template.innerHTML = String(detail.card_html).trim();
        const card = template.content.firstElementChild;
        if (card) {
            grid.prepend(card);
            window.requestAnimationFrame(() => card.classList.add('is-visible'));
        }

        const total = Number(document.querySelector('[data-wishlist-page-count]')?.textContent || 0) + 1;
        const inStock = Number(document.querySelector('[data-wishlist-in-stock-count]')?.textContent || 0) + (detail.in_stock ? 1 : 0);
        const outStock = Number(document.querySelector('[data-wishlist-out-stock-count]')?.textContent || 0) + (detail.in_stock ? 0 : 1);
        setWishlistStat('[data-wishlist-page-count]', total);
        setWishlistStat('[data-wishlist-in-stock-count]', inStock);
        setWishlistStat('[data-wishlist-out-stock-count]', outStock);
        document.querySelectorAll('[data-wishlist-clear] button').forEach((button) => { button.disabled = false; });
        if (inStock > 0) document.querySelectorAll('[data-wishlist-add-all] button').forEach((button) => { button.disabled = false; });
    };

    const clearWishlistUi = () => {
        const section = document.querySelector('.wishlist-products-section');
        if (section) section.innerHTML = wishlistEmptyMarkup();
        setWishlistStat('[data-wishlist-page-count]', 0);
        setWishlistStat('[data-wishlist-in-stock-count]', 0);
        setWishlistStat('[data-wishlist-out-stock-count]', 0);
        updateWishlistControls(0, 0);
    };

    const setupAvatarPreview = () => {
        const input = document.querySelector('[data-avatar-input]');
        const preview = document.querySelector('[data-avatar-preview]');
        if (!input || !preview || input.dataset.bound === '1') return;
        input.dataset.bound = '1';
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file || !file.type.startsWith('image/')) return;
            const url = URL.createObjectURL(file);
            preview.innerHTML = `<img src="${url}" alt="Profile preview">`;
        });
    };

    const scorePassword = (value) => {
        if (!value) return 0;
        let score = value.length >= 8 ? 1 : 0;
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
        if (/\d/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;
        return Math.min(score, 4);
    };

    const setupPasswordUi = () => {
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            if (button.dataset.bound === '1') return;
            button.dataset.bound = '1';
            button.addEventListener('click', () => {
                const input = button.closest('.password-input-shell')?.querySelector('input');
                if (!input) return;
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                button.innerHTML = showing ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
                button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            });
        });

        const password = document.querySelector('[data-new-password]');
        const strength = document.querySelector('[data-password-strength]');
        if (password && strength && password.dataset.strengthBound !== '1') {
            password.dataset.strengthBound = '1';
            password.addEventListener('input', () => {
                strength.dataset.score = String(scorePassword(password.value));
            });
        }
    };

    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (form.matches('.order-search') && document.querySelector('[data-account-orders-region]')) {
            event.preventDefault();
            submitOrderSearch(form);
            return;
        }

        if (form.matches('.payment-submit-form') && document.querySelector('[data-account-payments-region]')) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            ajax().clearFormErrors(form);
            ajax().setButtonLoading(button, true, 'Submitting…');

            try {
                const payload = await ajax().request(form.action, { method: 'POST', form });
                ajax().toast(payload.message || 'Payment submitted successfully.');
                await loadRegion(new URL(payload.redirect_url || window.location.href, window.location.origin), true);
            } catch (error) {
                if (error.status === 422) ajax().showFormErrors(form, error.payload?.errors || {});
                ajax().toast(ajax().firstError(error.payload, error.message), 'error');
            } finally {
                ajax().setButtonLoading(button, false);
            }
            return;
        }

        if (form.matches('[data-profile-form]')) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            ajax().clearFormErrors(form);
            ajax().setButtonLoading(button, true, 'Saving…');
            try {
                const payload = await ajax().request(form.action, { method: 'POST', form });
                ajax().toast(payload.message || 'Profile updated successfully.');
                document.querySelectorAll('[data-profile-name]').forEach((node) => { node.textContent = payload.profile?.name || node.textContent; });
                document.querySelectorAll('[data-profile-email]').forEach((node) => { node.textContent = payload.profile?.email || node.textContent; });
                document.querySelectorAll('[data-profile-header-name]').forEach((node) => { node.textContent = payload.profile?.name || node.textContent; });
                const avatar = payload.profile?.avatar_url;
                if (avatar) {
                    document.querySelectorAll('.account-avatar-media').forEach((node) => { node.innerHTML = `<img src="${avatar}" alt="Profile photo">`; });
                    const preview = document.querySelector('[data-avatar-preview]');
                    if (preview) preview.innerHTML = `<img src="${avatar}" alt="Profile photo">`;
                } else if (payload.profile?.avatar_initial) {
                    document.querySelectorAll('.account-avatar-media').forEach((node) => { node.textContent = payload.profile.avatar_initial; });
                    const preview = document.querySelector('[data-avatar-preview]');
                    if (preview) preview.innerHTML = `<span>${payload.profile.avatar_initial}</span>`;
                }
                const avatarInput = form.querySelector('[data-avatar-input]');
                if (avatarInput) avatarInput.value = '';
            } catch (error) {
                if (error.status === 422) ajax().showFormErrors(form, error.payload?.errors || {});
                ajax().toast(ajax().firstError(error.payload, error.message), 'error');
            } finally {
                ajax().setButtonLoading(button, false);
            }
            return;
        }

        if (form.matches('[data-password-form]')) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            ajax().clearFormErrors(form);
            ajax().setButtonLoading(button, true, 'Updating…');
            try {
                const payload = await ajax().request(form.action, { method: 'POST', form });
                ajax().toast(payload.message || 'Password updated successfully.');
                form.reset();
                const strength = form.querySelector('[data-password-strength]');
                if (strength) strength.dataset.score = '0';
            } catch (error) {
                if (error.status === 422) ajax().showFormErrors(form, error.payload?.errors || {});
                ajax().toast(ajax().firstError(error.payload, error.message), 'error');
            } finally {
                ajax().setButtonLoading(button, false);
            }
            return;
        }

        if (form.matches('[data-wishlist-add-all]')) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            ajax().setButtonLoading(button, true, 'Adding…');
            try {
                const payload = await ajax().request(form.action, { method: 'POST', form });
                ajax().updateCartHeader(payload.cart);
                ajax().toast(payload.message || 'Wishlist items added to cart.');
            } catch (error) {
                ajax().toast(ajax().firstError(error.payload, error.message), 'error');
            } finally {
                ajax().setButtonLoading(button, false);
            }
            return;
        }

        if (form.matches('[data-wishlist-clear]')) {
            event.preventDefault();
            if (!window.confirm('Clear all products from your wishlist?')) return;
            const button = form.querySelector('button[type="submit"]');
            ajax().setButtonLoading(button, true, 'Clearing…');
            try {
                const payload = await ajax().request(form.action, { method: 'POST', form });
                ajax().updateWishlistHeader(payload.wishlist);
                clearWishlistUi();
                ajax().toast(payload.message || 'Wishlist cleared.');
            } catch (error) {
                ajax().toast(ajax().firstError(error.payload, error.message), 'error');
            } finally {
                ajax().setButtonLoading(button, false);
            }
            return;
        }

        if (form.matches('[data-ajax-logout]')) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            ajax().setButtonLoading(button, true, 'Logging out…');
            try {
                const payload = await ajax().request(form.action, { method: 'POST', form });
                window.location.assign(payload.redirect_url || '/');
            } catch (error) {
                ajax().toast(ajax().firstError(error.payload, error.message), 'error');
                ajax().setButtonLoading(button, false);
            }
        }
    });

    document.addEventListener('shoppilot:wishlist-changed', (event) => {
        if (!document.querySelector('[data-wishlist-page]')) return;
        if (event.detail?.active === false) {
            removeWishlistCard(event.detail.productId);
            return;
        }
        if (event.detail?.active === true) addWishlistCard(event.detail);
    });

    document.addEventListener('input', (event) => {
        const input = event.target.closest('.order-search input[type="search"]');
        if (!input || !document.querySelector('[data-account-orders-region]')) return;
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => submitOrderSearch(input.form), 380);
    });

    document.addEventListener('click', (event) => {
        const selector = currentRegionSelector();
        if (!selector) return;
        const link = event.target.closest(`${selector} a[href]`);
        if (!link) return;

        const url = new URL(link.href, window.location.origin);
        const current = new URL(window.location.href);
        if (url.origin !== current.origin || url.pathname !== current.pathname) return;
        event.preventDefault();
        loadRegion(url);
    });

    window.addEventListener('popstate', () => {
        if (currentRegionSelector()) loadRegion(new URL(window.location.href), false);
    });

    setupReveal();
    setupAvatarPreview();
    setupPasswordUi();
})();
