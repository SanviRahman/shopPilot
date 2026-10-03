(() => {
    'use strict';

    const root = document.querySelector('[data-checkout-root]');

    const setupReveal = () => {
        const targets = document.querySelectorAll('[data-checkout-reveal]');
        if (!targets.length) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
            targets.forEach((el) => el.classList.add('is-visible'));
            return;
        }
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -35px' });
        targets.forEach((el) => observer.observe(el));
    };

    setupReveal();
    if (!root) return;

    const form = root.querySelector('[data-checkout-form]');
    const panels = [...root.querySelectorAll('[data-checkout-step]')];
    const progress = [...root.querySelectorAll('[data-progress-step]')];
    const totals = root.querySelector('.checkout-totals');
    const subtotalEl = root.querySelector('[data-total-subtotal]');
    const discountEl = root.querySelector('[data-total-discount]');
    const shippingEl = root.querySelector('[data-total-shipping]');
    const grandEl = root.querySelector('[data-total-grand]');
    const savingsLine = root.querySelector('[data-savings-line]');
    const savingsEl = root.querySelector('[data-savings]');
    let subtotal = Number(totals?.dataset.subtotal || 0);
    let discount = Number(totals?.dataset.discount || 0);
    let currentStep = Math.max(1, Math.min(3, Number(root.dataset.initialStep || 1)));

    const money = (value) => `৳${Math.round(Number(value || 0)).toLocaleString('en-US')}`;

    const selectedDelivery = () => root.querySelector('[data-delivery-radio]:checked');
    const selectedPayment = () => root.querySelector('[data-method-radio]:checked');

    const renderTotals = () => {
        const shipping = Number(selectedDelivery()?.dataset.fee || 0);
        const grand = Math.max(0, subtotal - discount + shipping);
        if (subtotalEl) subtotalEl.textContent = money(subtotal);
        if (discountEl) discountEl.textContent = `-${money(discount)}`;
        if (shippingEl) shippingEl.textContent = shipping > 0 ? money(shipping) : 'Free';
        if (grandEl) grandEl.textContent = money(grand);
        if (savingsEl) savingsEl.textContent = Math.round(discount).toLocaleString('en-US');
        savingsLine?.classList.toggle('hidden', discount <= 0);
    };

    const setStep = (step, scroll = true) => {
        currentStep = Math.max(1, Math.min(3, Number(step)));
        panels.forEach((panel) => {
            const active = Number(panel.dataset.checkoutStep) === currentStep;
            panel.classList.toggle('active', active);
            panel.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
        progress.forEach((item) => {
            const n = Number(item.dataset.progressStep);
            item.classList.toggle('active', n === currentStep);
            item.classList.toggle('done', n < currentStep);
        });
        if (currentStep === 3) renderReview();
        if (scroll) root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const fieldsForStep = (step) => {
        const panel = root.querySelector(`[data-checkout-step="${step}"]`);
        return panel ? [...panel.querySelectorAll('input[required], select[required], textarea[required]')] : [];
    };

    const validateStep = (step) => {
        for (const field of fieldsForStep(step)) {
            if (!field.checkValidity()) {
                field.reportValidity();
                field.focus({ preventScroll: false });
                return false;
            }
        }
        return true;
    };

    const value = (name) => form?.elements?.[name]?.value?.trim?.() || '';

    const renderReview = () => {
        const payment = selectedPayment();
        const delivery = selectedDelivery();
        const paymentCard = payment?.closest('[data-payment-option]');
        const deliveryCard = delivery?.closest('[data-delivery-option]');
        const assign = (selector, text) => { const el = root.querySelector(selector); if (el) el.textContent = text || '—'; };

        assign('[data-review-name]', value('buyer_name'));
        assign('[data-review-contact]', `${value('buyer_phone')} · ${value('buyer_email')}`);
        assign('[data-review-area]', [value('upazila'), value('district'), value('division')].filter(Boolean).join(', '));
        assign('[data-review-address]', [value('shipping_address'), value('postal_code') ? `Postal ${value('postal_code')}` : ''].filter(Boolean).join(' · '));
        assign('[data-review-delivery]', deliveryCard?.querySelector('.delivery-copy strong')?.textContent || '—');
        assign('[data-review-delivery-fee]', Number(delivery?.dataset.fee || 0) > 0 ? money(delivery.dataset.fee) : 'Free delivery');
        assign('[data-review-payment]', paymentCard?.querySelector('strong')?.textContent || '—');
        assign('[data-review-transaction]', value('transaction_id') ? `Transaction ID: ${value('transaction_id')}` : '—');
    };

    root.querySelectorAll('[data-go-step]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = Number(button.dataset.goStep);
            if (target > currentStep && !validateStep(currentStep)) return;
            setStep(target);
        });
    });

    root.querySelectorAll('[data-delivery-radio]').forEach((radio) => {
        radio.addEventListener('change', () => {
            root.querySelectorAll('[data-delivery-option]').forEach((card) => card.classList.toggle('selected', card.contains(radio) && radio.checked));
            root.querySelectorAll('[data-delivery-option]').forEach((card) => {
                const input = card.querySelector('[data-delivery-radio]');
                card.classList.toggle('selected', Boolean(input?.checked));
            });
            renderTotals();
        });
    });

    root.querySelectorAll('[data-method-radio]').forEach((radio) => {
        radio.addEventListener('change', () => {
            root.querySelectorAll('[data-payment-option]').forEach((card) => {
                const input = card.querySelector('[data-method-radio]');
                card.classList.toggle('selected', Boolean(input?.checked));
            });
            root.querySelectorAll('[data-payment-instruction]').forEach((instruction) => {
                instruction.classList.toggle('active', instruction.dataset.paymentInstruction === radio.value && radio.checked);
            });
        });
    });

    const summaryToggle = root.querySelector('[data-summary-toggle]');
    summaryToggle?.addEventListener('click', () => {
        const card = summaryToggle.closest('.checkout-summary-card');
        const collapsed = card.classList.toggle('collapsed');
        summaryToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        summaryToggle.querySelector('.fa-chevron-up, .fa-chevron-down')?.classList.toggle('fa-chevron-down', collapsed);
        summaryToggle.querySelector('.fa-chevron-up, .fa-chevron-down')?.classList.toggle('fa-chevron-up', !collapsed);
    });

    const couponToggle = root.querySelector('[data-checkout-coupon-toggle]');
    const couponBody = root.querySelector('[data-checkout-coupon-body]');
    couponToggle?.addEventListener('click', () => {
        const open = couponBody?.classList.toggle('open');
        couponToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    const couponFeedback = root.querySelector('[data-coupon-feedback]');
    const couponForm = root.querySelector('[data-coupon-form]');
    const couponApplied = root.querySelector('[data-coupon-applied]');
    const couponInput = root.querySelector('[data-coupon-input]');

    const setCouponLoading = (button, loading) => {
        if (!button) return;
        button.disabled = loading;
        button.dataset.originalText ||= button.textContent;
        button.textContent = loading ? 'Please wait…' : button.dataset.originalText;
    };

    const applySummary = (summary) => {
        subtotal = Number(summary.subtotal || 0);
        discount = Number(summary.discount || 0);
        totals.dataset.subtotal = subtotal;
        totals.dataset.discount = discount;
        root.querySelector('[data-coupon-code]') && (root.querySelector('[data-coupon-code]').textContent = summary.coupon_code || '');
        root.querySelector('[data-coupon-saving]') && (root.querySelector('[data-coupon-saving]').textContent = Math.round(discount).toLocaleString('en-US'));
        couponApplied?.classList.toggle('hidden', !summary.coupon_code);
        couponForm?.classList.toggle('hidden', Boolean(summary.coupon_code));
        if (window.ShopPilotCheckout?.checkoutMode !== 'buy_now') {
            window.ShopPilotAjax?.updateCartHeader?.({ count: summary.count, grand_total: summary.grand_total });
        }
        renderTotals();
    };

    root.querySelector('[data-coupon-apply]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const code = couponInput?.value?.trim();
        if (!code) { couponInput?.focus(); return; }
        setCouponLoading(button, true);
        couponFeedback?.classList.remove('error');
        try {
            const response = await fetch(button.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': window.ShopPilotCheckout.csrf },
                body: JSON.stringify({
                    coupon_code: code,
                    checkout_mode: window.ShopPilotCheckout?.checkoutMode || 'cart',
                }),
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload?.message || Object.values(payload?.errors || {})?.flat?.()?.[0] || 'Unable to apply coupon.');
            applySummary(payload.summary);
            if (couponFeedback) couponFeedback.textContent = payload.message;
        } catch (error) {
            if (couponFeedback) { couponFeedback.textContent = error.message; couponFeedback.classList.add('error'); }
        } finally { setCouponLoading(button, false); }
    });

    root.querySelector('[data-coupon-remove]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        setCouponLoading(button, true);
        try {
            const response = await fetch(window.ShopPilotCheckout.removeCouponUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.ShopPilotCheckout.csrf,
                },
                body: JSON.stringify({
                    checkout_mode: window.ShopPilotCheckout?.checkoutMode || 'cart',
                }),
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload?.message || 'Unable to remove coupon.');
            applySummary(payload.summary);
            if (couponInput) couponInput.value = '';
            if (couponFeedback) couponFeedback.textContent = payload.message;
        } catch (error) {
            if (couponFeedback) { couponFeedback.textContent = error.message; couponFeedback.classList.add('error'); }
        } finally { setCouponLoading(button, false); }
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const shippingValid = validateStep(1);
        const paymentValid = shippingValid ? validateStep(2) : false;
        if (!shippingValid || !paymentValid) {
            setStep(!shippingValid ? 1 : 2);
            return;
        }

        const ajax = window.ShopPilotAjax;
        if (!ajax) {
            form.submit();
            return;
        }

        const submit = form.querySelector('[data-place-order]');
        ajax.clearFormErrors(form);
        ajax.setButtonLoading(submit, true, 'Placing Order…');

        try {
            const payload = await ajax.request(form.action, { method: 'POST', form });
            ajax.updateCartHeader(payload.cart);
            ajax.trackMeta(payload.meta_event);
            ajax.toast(payload.message || 'Order placed successfully.');

            if (payload.redirect_url) {
                window.setTimeout(() => window.location.assign(payload.redirect_url), 100);
            }
        } catch (error) {
            if (error.status === 422) {
                const errors = error.payload?.errors || {};
                ajax.showFormErrors(form, errors);
                const paymentFields = ['payment_method_id', 'transaction_id'];
                const firstKey = Object.keys(errors)[0] || '';
                setStep(paymentFields.includes(firstKey) ? 2 : 1);
            }
            ajax.toast(ajax.firstError(error.payload, error.message), 'error');
            ajax.setButtonLoading(submit, false);
        }
    });

    renderTotals();
    setStep(currentStep, false);
})();
