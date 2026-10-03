(() => {
    'use strict';

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ajax = () => window.ShopPilotAjax;

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const wrap = button.closest('.auth-input');
            const input = wrap?.querySelector('[data-password-input]');
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.innerHTML = show ? '<i class="far fa-eye-slash"></i>' : '<i class="far fa-eye"></i>';
        });
    });

    document.querySelectorAll('.auth-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!ajax()) {
                form.submit();
                return;
            }

            const button = form.querySelector('button[type="submit"]');
            ajax().clearFormErrors(form);
            ajax().setButtonLoading(button, true, 'Please wait…');

            try {
                const payload = await ajax().request(form.action, { method: 'POST', form });
                ajax().trackMeta(payload.meta_events || payload.meta_event);
                ajax().toast(payload.message || 'Request completed successfully.');

                if (payload.redirect_url) {
                    window.setTimeout(() => window.location.assign(payload.redirect_url), 80);
                    return;
                }

                if (form.action.includes('/forgot-password')) form.reset();
            } catch (error) {
                if (error.status === 422) {
                    ajax().showFormErrors(form, error.payload?.errors || {});
                }
                ajax().toast(ajax().firstError(error.payload, error.message), 'error');
            } finally {
                ajax().setButtonLoading(button, false);
            }
        });
    });

    const targets = [...document.querySelectorAll('[data-auth-reveal]')];
    targets.forEach((el) => el.style.setProperty('--auth-delay', `${Number(el.dataset.authDelay || 0)}ms`));
    if (reduced || !('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        io.unobserve(entry.target);
    }), { threshold: .12 });
    targets.forEach((el) => io.observe(el));
})();
