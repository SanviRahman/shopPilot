(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ajax = () => window.ShopPilotAjax;
    const cartRegion = () => document.querySelector('[data-cart-ajax-content]');
    let refreshController = null;
    let pendingConfirmForm = null;
    let previouslyFocusedElement = null;

    const syncSelection = () => {
        const selectAll = cartRegion()?.querySelector('[data-cart-select-all]');
        const itemChecks = Array.from(cartRegion()?.querySelectorAll('[data-cart-item-check]') || []);
        const removeSelected = cartRegion()?.querySelector('[data-remove-selected]');
        if (!itemChecks.length) return;
        const selected = itemChecks.filter((input) => input.checked).length;
        if (selectAll) {
            selectAll.checked = selected === itemChecks.length;
            selectAll.indeterminate = selected > 0 && selected < itemChecks.length;
        }
        if (removeSelected) removeSelected.disabled = selected === 0;
    };

    const setupReveal = () => {
        const targets = Array.from(cartRegion()?.querySelectorAll('[data-cart-reveal]') || []);
        targets.forEach((element, index) => {
            if (!element.style.getPropertyValue('--cart-delay')) {
                element.style.setProperty('--cart-delay', `${Math.min(index * 55, 320)}ms`);
            }
        });

        if (reducedMotion || !('IntersectionObserver' in window)) {
            targets.forEach((element) => element.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries, instance) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                instance.unobserve(entry.target);
            });
        }, { threshold: .08, rootMargin: '0px 0px -36px 0px' });
        targets.forEach((element) => observer.observe(element));
    };

    const setupRegion = () => {
        syncSelection();
        setupReveal();
    };

    const reloadCart = async () => {
        if (!cartRegion() || !ajax()) return;
        refreshController?.abort();
        refreshController = new AbortController();

        try {
            const result = await ajax().fetchFragment(window.location.href, '[data-cart-ajax-content]', {
                pushState: false,
                signal: refreshController.signal,
            });
            ajax().updateCartHeader(result.payload?.cart);
            setupRegion();
        } catch (error) {
            if (error.name !== 'AbortError') ajax().toast(error.message || 'Unable to refresh the cart.', 'error');
        }
    };

    const submitCartForm = async (form, button = null) => {
        if (!form || !ajax()) return;
        const submitButton = button || form.querySelector('button[type="submit"]');
        ajax().setButtonLoading(submitButton, true, 'Updating…');

        try {
            const payload = await ajax().request(form.action, { method: 'POST', form });
            ajax().updateCartHeader(payload.cart);
            ajax().toast(payload.message || 'Cart updated.');
            await reloadCart();
        } catch (error) {
            ajax().toast(ajax().firstError(error.payload, error.message), 'error');
        } finally {
            ajax().setButtonLoading(submitButton, false);
        }
    };

    const confirmModal = () => document.querySelector('[data-cart-confirm-modal]');
    const closeConfirmModal = () => {
        const modal = confirmModal();
        if (!modal) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('cart-confirm-open');
        pendingConfirmForm = null;
        window.setTimeout(() => previouslyFocusedElement?.focus?.(), reducedMotion ? 0 : 180);
    };

    const openConfirmModal = (form) => {
        const modal = confirmModal();
        const dialog = modal?.querySelector('.cart-confirm-dialog');
        const submit = modal?.querySelector('[data-cart-confirm-submit]');
        if (!modal || !dialog || !submit) return false;

        pendingConfirmForm = form;
        previouslyFocusedElement = document.activeElement;
        const title = modal.querySelector('[data-cart-confirm-title]');
        const message = modal.querySelector('[data-cart-confirm-message]');
        const note = modal.querySelector('[data-cart-confirm-note] span');
        if (title) title.textContent = form.dataset.confirmTitle || 'Please confirm this action';
        if (message) message.textContent = form.dataset.confirmForm || 'This action will update your shopping cart.';
        if (note) note.textContent = form.dataset.confirmNote || 'Your account and checkout information stay safe.';
        submit.textContent = form.dataset.confirmAction || 'Yes, continue';
        submit.disabled = false;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('cart-confirm-open');
        window.requestAnimationFrame(() => dialog.focus());
        return true;
    };

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!cartRegion()?.contains(target)) return;

        if (target.matches('[data-cart-select-all]')) {
            cartRegion().querySelectorAll('[data-cart-item-check]').forEach((input) => { input.checked = target.checked; });
            syncSelection();
            return;
        }

        if (target.matches('[data-cart-item-check]')) {
            syncSelection();
            return;
        }

        if (target.matches('[data-cart-qty-input]')) {
            const form = target.closest('[data-cart-quantity-form]');
            const max = Math.max(1, Number(form?.dataset.max || 1));
            target.value = String(Math.max(1, Math.min(max, Number(target.value) || 1)));
            submitCartForm(form);
        }
    });

    document.addEventListener('click', (event) => {
        const target = event.target;

        const close = target.closest('[data-cart-confirm-close]');
        if (close) { event.preventDefault(); closeConfirmModal(); return; }

        const confirmSubmit = target.closest('[data-cart-confirm-submit]');
        if (confirmSubmit) {
            event.preventDefault();
            if (!pendingConfirmForm) return;
            const form = pendingConfirmForm;
            closeConfirmModal();
            submitCartForm(form, form.querySelector('button[type="submit"]'));
            return;
        }

        if (!cartRegion()?.contains(target)) return;

        const minus = target.closest('[data-cart-qty-minus]');
        const plus = target.closest('[data-cart-qty-plus]');
        if (minus || plus) {
            event.preventDefault();
            const form = target.closest('[data-cart-quantity-form]');
            const input = form?.querySelector('[data-cart-qty-input]');
            if (!input) return;
            const max = Math.max(1, Number(form.dataset.max || 1));
            const next = Math.max(1, Math.min(max, Number(input.value || 1) + (plus ? 1 : -1)));
            if (next === Number(input.value)) return;
            input.value = String(next);
            submitCartForm(form);
            return;
        }

        const couponToggle = target.closest('[data-coupon-toggle]');
        if (couponToggle) {
            event.preventDefault();
            const body = cartRegion().querySelector('[data-coupon-body]');
            const open = body?.classList.toggle('open');
            couponToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            return;
        }

        const summaryToggle = target.closest('[data-cart-summary-toggle]');
        if (summaryToggle && window.innerWidth <= 780) {
            event.preventDefault();
            const body = cartRegion().querySelector('[data-cart-summary-body]');
            const collapsed = body?.classList.toggle('collapsed');
            summaryToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!cartRegion()?.contains(form)) return;
        if (form.matches('.product-card-cart-form, .product-order-form')) return;

        if (form.matches('[data-confirm-form]')) {
            event.preventDefault();
            if (!openConfirmModal(form) && window.confirm(form.dataset.confirmForm || 'Are you sure?')) {
                submitCartForm(form);
            }
            return;
        }

        const isCartMutation = form.matches('[data-cart-quantity-form], .coupon-form')
            || form.action.includes('/cart/coupon');

        if (isCartMutation) {
            event.preventDefault();
            submitCartForm(form);
        }
    });

    document.addEventListener('shoppilot:cart-changed', () => {
        if (cartRegion()) reloadCart();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && confirmModal()?.classList.contains('open')) closeConfirmModal();
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 780) {
            const body = cartRegion()?.querySelector('[data-cart-summary-body]');
            body?.classList.remove('collapsed');
            cartRegion()?.querySelector('[data-cart-summary-toggle]')?.setAttribute('aria-expanded', 'true');
        }
    }, { passive: true });

    setupRegion();
})();
