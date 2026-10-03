(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealTargets = Array.from(document.querySelectorAll('[data-content-reveal]'));

    revealTargets.forEach((element, index) => {
        element.style.setProperty('--content-delay', `${Math.min(index * 55, 275)}ms`);
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
        }, { threshold: 0.11, rootMargin: '0px 0px -42px 0px' });
        revealTargets.forEach((element) => observer.observe(element));
    }

    window.setTimeout(() => {
        revealTargets.filter((element) => !element.classList.contains('is-visible')).forEach((element) => element.classList.add('is-visible'));
    }, 1300);

    const faqItems = Array.from(document.querySelectorAll('[data-faq-item]'));
    const faqSearch = document.querySelector('[data-faq-search]');
    const faqCount = document.querySelector('[data-faq-visible-count]');
    const faqEmpty = document.querySelector('[data-faq-empty]');
    let activeFaqCategory = 'all';

    const updateFaq = () => {
        if (!faqItems.length) return;
        const query = String(faqSearch?.value || '').trim().toLowerCase();
        let visible = 0;
        faqItems.forEach((item) => {
            const categoryMatch = activeFaqCategory === 'all' || item.dataset.category === activeFaqCategory;
            const searchMatch = query === '' || String(item.dataset.search || '').includes(query);
            const show = categoryMatch && searchMatch;
            item.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        if (faqCount) faqCount.textContent = String(visible);
        faqEmpty?.classList.toggle('d-none', visible !== 0);
    };

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-faq-toggle]');
        if (toggle) {
            const answer = toggle.parentElement?.querySelector('.faq-answer');
            const open = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
            answer?.classList.toggle('open', !open);
            return;
        }

        const categoryButton = event.target.closest('[data-faq-category]');
        if (categoryButton) {
            document.querySelectorAll('[data-faq-category]').forEach((button) => button.classList.remove('active'));
            categoryButton.classList.add('active');
            activeFaqCategory = categoryButton.dataset.faqCategory || 'all';
            updateFaq();
            return;
        }

        const searchButton = event.target.closest('[data-faq-search-button]');
        if (searchButton) {
            updateFaq();
            faqSearch?.focus();
            return;
        }

        const resetButton = event.target.closest('[data-faq-reset]');
        if (resetButton) {
            activeFaqCategory = 'all';
            if (faqSearch) faqSearch.value = '';
            document.querySelectorAll('[data-faq-category]').forEach((button) => button.classList.toggle('active', button.dataset.faqCategory === 'all'));
            updateFaq();
        }
    });

    faqSearch?.addEventListener('input', updateFaq);
    faqSearch?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        updateFaq();
    });
    updateFaq();

    const contactForm = document.querySelector('[data-contact-form]');
    const contactMessage = document.querySelector('[data-contact-message]');
    const contactCounter = document.querySelector('[data-contact-character-count]');
    const ajax = () => window.ShopPilotAjax;

    const updateCharacterCount = () => {
        if (contactMessage && contactCounter) contactCounter.textContent = String(contactMessage.value.length);
    };

    contactMessage?.addEventListener('input', updateCharacterCount);
    updateCharacterCount();

    contactForm?.addEventListener('submit', async (event) => {
        if (!ajax()) return;
        event.preventDefault();
        const submit = contactForm.querySelector('[data-contact-submit]');
        ajax().clearFormErrors(contactForm);
        ajax().setButtonLoading(submit, true, 'Sending…');

        try {
            const payload = await ajax().request(contactForm.action, { method: 'POST', form: contactForm });
            ajax().toast(payload.message || 'Your message has been sent.');
            contactForm.reset();
            updateCharacterCount();
        } catch (error) {
            if (error.status === 422) ajax().showFormErrors(contactForm, error.payload?.errors || {});
            ajax().toast(ajax().firstError(error.payload, error.message), 'error');
        } finally {
            ajax().setButtonLoading(submit, false);
        }
    });

    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (finePointer && !reducedMotion) {
        document.querySelectorAll('.metric-card,.mini-feature-card,.blog-card,.quick-link-card,.contact-info-grid article').forEach((card) => {
            card.addEventListener('mousemove', (event) => {
                const rect = card.getBoundingClientRect();
                const x = (event.clientX - rect.left) / rect.width - 0.5;
                const y = (event.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = `perspective(700px) rotateX(${y * -2.2}deg) rotateY(${x * 2.6}deg) translateY(-4px)`;
            });
            card.addEventListener('mouseleave', () => card.style.removeProperty('transform'));
        });
    }
})();
