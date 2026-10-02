(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const selectAll = document.querySelector('[data-cart-select-all]');
    const itemChecks = Array.from(document.querySelectorAll('[data-cart-item-check]'));
    const removeSelected = document.querySelector('[data-remove-selected]');

    const syncSelection = () => {
        if (!itemChecks.length) return;
        const selected = itemChecks.filter((input) => input.checked).length;
        if (selectAll) {
            selectAll.checked = selected === itemChecks.length;
            selectAll.indeterminate = selected > 0 && selected < itemChecks.length;
        }
        if (removeSelected) removeSelected.disabled = selected === 0;
    };

    selectAll?.addEventListener('change', () => {
        itemChecks.forEach((input) => {
            input.checked = selectAll.checked;
        });
        syncSelection();
    });

    itemChecks.forEach((input) => input.addEventListener('change', syncSelection));
    syncSelection();

    document.querySelectorAll('[data-cart-quantity-form]').forEach((form) => {
        const input = form.querySelector('[data-cart-qty-input]');
        const minus = form.querySelector('[data-cart-qty-minus]');
        const plus = form.querySelector('[data-cart-qty-plus]');
        const max = Math.max(1, Number(form.dataset.max || 1));
        let lastSubmittedValue = Number(input?.value || 1);

        const clamp = (value) => Math.max(1, Math.min(max, Number(value) || 1));
        const submitValue = (value) => {
            if (!input) return;
            const next = clamp(value);
            input.value = String(next);
            if (next === lastSubmittedValue) return;
            lastSubmittedValue = next;
            form.requestSubmit();
        };

        minus?.addEventListener('click', () => submitValue(Number(input?.value || 1) - 1));
        plus?.addEventListener('click', () => submitValue(Number(input?.value || 1) + 1));
        input?.addEventListener('change', () => submitValue(input.value));
    });

    const confirmModal = document.querySelector('[data-cart-confirm-modal]');
    const confirmDialog = confirmModal?.querySelector('.cart-confirm-dialog');
    const confirmTitle = confirmModal?.querySelector('[data-cart-confirm-title]');
    const confirmMessage = confirmModal?.querySelector('[data-cart-confirm-message]');
    const confirmNote = confirmModal?.querySelector('[data-cart-confirm-note] span');
    const confirmSubmit = confirmModal?.querySelector('[data-cart-confirm-submit]');
    const confirmCloseButtons = Array.from(confirmModal?.querySelectorAll('[data-cart-confirm-close]') || []);
    let pendingConfirmForm = null;
    let previouslyFocusedElement = null;

    const closeConfirmModal = () => {
        if (!confirmModal) return;

        confirmModal.classList.remove('open');
        confirmModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('cart-confirm-open');
        pendingConfirmForm = null;

        window.setTimeout(() => previouslyFocusedElement?.focus?.(), reducedMotion ? 0 : 180);
    };

    const openConfirmModal = (form) => {
        if (!confirmModal || !confirmDialog || !confirmSubmit) return false;

        pendingConfirmForm = form;
        previouslyFocusedElement = document.activeElement;

        if (confirmTitle) {
            confirmTitle.textContent = form.dataset.confirmTitle || 'Please confirm this action';
        }

        if (confirmMessage) {
            confirmMessage.textContent = form.dataset.confirmForm || 'This action will update your shopping cart.';
        }

        if (confirmNote) {
            confirmNote.textContent = form.dataset.confirmNote || 'Your account and checkout information stay safe.';
        }

        confirmSubmit.textContent = form.dataset.confirmAction || 'Yes, continue';
        confirmModal.classList.add('open');
        confirmModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('cart-confirm-open');

        window.requestAnimationFrame(() => confirmDialog.focus());
        return true;
    };

    document.querySelectorAll('[data-confirm-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmApproved === 'true') {
                delete form.dataset.confirmApproved;
                return;
            }

            event.preventDefault();

            if (!openConfirmModal(form)) {
                // Safe fallback for extremely old browsers or markup failures.
                if (window.confirm(form.dataset.confirmForm || 'Are you sure?')) {
                    form.dataset.confirmApproved = 'true';
                    form.requestSubmit();
                }
            }
        });
    });

    confirmCloseButtons.forEach((button) => button.addEventListener('click', closeConfirmModal));

    confirmSubmit?.addEventListener('click', () => {
        if (!pendingConfirmForm) return;

        const form = pendingConfirmForm;
        form.dataset.confirmApproved = 'true';
        confirmSubmit.disabled = true;
        confirmSubmit.classList.add('loading');

        window.setTimeout(() => {
            form.requestSubmit();
        }, reducedMotion ? 0 : 130);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || !confirmModal?.classList.contains('open')) return;
        event.preventDefault();
        closeConfirmModal();
    });

    const couponToggle = document.querySelector('[data-coupon-toggle]');
    const couponBody = document.querySelector('[data-coupon-body]');
    couponToggle?.addEventListener('click', () => {
        if (!couponBody) return;
        const open = couponBody.classList.toggle('open');
        couponToggle.setAttribute('aria-expanded', String(open));
    });

    const summaryToggle = document.querySelector('[data-cart-summary-toggle]');
    const summaryBody = document.querySelector('[data-cart-summary-body]');
    summaryToggle?.addEventListener('click', () => {
        if (!summaryBody || window.innerWidth > 780) return;
        const collapsed = summaryBody.classList.toggle('collapsed');
        summaryToggle.setAttribute('aria-expanded', String(!collapsed));
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 780 && summaryBody) {
            summaryBody.classList.remove('collapsed');
            summaryToggle?.setAttribute('aria-expanded', 'true');
        }
    }, { passive: true });

    const revealTargets = Array.from(document.querySelectorAll('[data-cart-reveal]'));
    revealTargets.forEach((element, index) => {
        if (!element.style.getPropertyValue('--cart-delay')) {
            element.style.setProperty('--cart-delay', `${Math.min(index * 55, 320)}ms`);
        }
    });

    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealTargets.forEach((element) => element.classList.add('is-visible'));
    } else {
        const observer = new IntersectionObserver((entries, instance) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                instance.unobserve(entry.target);
            });
        }, {
            threshold: .08,
            rootMargin: '0px 0px -36px 0px',
        });

        revealTargets.forEach((element) => observer.observe(element));
    }

    const relatedTrack = document.querySelector('[data-cart-related-track]');
    if (relatedTrack && !reducedMotion) {
        const interactiveSelector = 'a, button, input, select, textarea, label, form';
        const dragThreshold = 7;
        let pointerDown = false;
        let dragging = false;
        let pointerId = null;
        let startX = 0;
        let startScroll = 0;

        relatedTrack.addEventListener('pointerdown', (event) => {
            // Touch/pen already get smooth native horizontal scrolling. More importantly,
            // never capture a pointer that started on a real control/link inside a card.
            if (event.pointerType !== 'mouse' || event.button !== 0) return;
            if (event.target.closest(interactiveSelector)) return;

            pointerDown = true;
            dragging = false;
            pointerId = event.pointerId;
            startX = event.clientX;
            startScroll = relatedTrack.scrollLeft;
        });

        relatedTrack.addEventListener('pointermove', (event) => {
            if (!pointerDown || event.pointerId !== pointerId) return;

            const deltaX = event.clientX - startX;

            if (!dragging) {
                if (Math.abs(deltaX) < dragThreshold) return;

                dragging = true;
                relatedTrack.classList.add('is-dragging');
                relatedTrack.setPointerCapture?.(event.pointerId);
            }

            event.preventDefault();
            relatedTrack.scrollLeft = startScroll - deltaX;
        });

        const stopDrag = (event) => {
            if (pointerId !== null && event?.pointerId !== undefined && event.pointerId !== pointerId) return;

            if (dragging && pointerId !== null && relatedTrack.hasPointerCapture?.(pointerId)) {
                relatedTrack.releasePointerCapture?.(pointerId);
            }

            pointerDown = false;
            dragging = false;
            pointerId = null;
            relatedTrack.classList.remove('is-dragging');
        };

        relatedTrack.addEventListener('pointerup', stopDrag);
        relatedTrack.addEventListener('pointercancel', stopDrag);
        relatedTrack.addEventListener('lostpointercapture', stopDrag);
    }
})();
