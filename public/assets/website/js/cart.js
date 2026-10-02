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

    document.querySelectorAll('[data-confirm-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirmForm || 'Are you sure?';
            if (!window.confirm(message)) event.preventDefault();
        });
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
        let isDown = false;
        let startX = 0;
        let startScroll = 0;

        relatedTrack.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'mouse' && event.button !== 0) return;
            isDown = true;
            startX = event.clientX;
            startScroll = relatedTrack.scrollLeft;
            relatedTrack.setPointerCapture?.(event.pointerId);
        });

        relatedTrack.addEventListener('pointermove', (event) => {
            if (!isDown) return;
            relatedTrack.scrollLeft = startScroll - (event.clientX - startX);
        });

        const stopDrag = () => { isDown = false; };
        relatedTrack.addEventListener('pointerup', stopDrag);
        relatedTrack.addEventListener('pointercancel', stopDrag);
    }
})();
