(() => {
    'use strict';

    const ajax = () => window.ShopPilotAjax;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const reveal = (root = document) => {
        const nodes = [...root.querySelectorAll('[data-track-reveal]')].filter((node) => !node.classList.contains('is-visible'));
        if (reduced || !('IntersectionObserver' in window)) {
            nodes.forEach((node) => node.classList.add('is-visible'));
            return;
        }
        const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        }), { threshold: .08 });
        nodes.forEach((node) => observer.observe(node));
        window.setTimeout(() => nodes.forEach((node) => node.classList.add('is-visible')), 1200);
    };

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-track-order-form]');
        if (!form || !ajax()) return;
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        ajax().clearFormErrors(form);
        ajax().setButtonLoading(button, true, 'Tracking…');

        try {
            const payload = await ajax().request(form.action, { method: 'POST', form });
            const region = document.querySelector('[data-track-result-region]');
            if (region && payload.html) {
                region.innerHTML = payload.html;
                reveal(region);
                region.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'start' });
            }
        } catch (error) {
            if (error.status === 422) ajax().showFormErrors(form, error.payload?.errors || {});
            ajax().toast(ajax().firstError(error.payload, error.message), 'error');
        } finally {
            ajax().setButtonLoading(button, false);
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-track-print]')) return;
        window.print();
    });

    reveal();
})();
