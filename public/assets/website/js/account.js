(() => {
    'use strict';

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ajax = () => window.ShopPilotAjax;
    let searchTimer = null;
    let activeController = null;

    const setupReveal = (root = document) => {
        const targets = [...root.querySelectorAll('[data-account-reveal]')];
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
})();
