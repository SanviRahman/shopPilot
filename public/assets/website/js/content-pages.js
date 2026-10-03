(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ajax = () => window.ShopPilotAjax;
    let revealObserver = null;
    let activeFaqCategory = 'all';
    let blogController = null;

    const reveal = (root = document) => {
        const targets = Array.from(root.querySelectorAll('[data-content-reveal]')).filter((element) => !element.classList.contains('is-visible'));
        targets.forEach((element, index) => element.style.setProperty('--content-delay', `${Math.min(index * 55, 275)}ms`));
        if (reducedMotion || !('IntersectionObserver' in window)) { targets.forEach((element) => element.classList.add('is-visible')); return; }
        revealObserver ??= new IntersectionObserver((entries) => entries.forEach((entry) => { if (!entry.isIntersecting) return; entry.target.classList.add('is-visible'); revealObserver.unobserve(entry.target); }), { threshold: 0.11, rootMargin: '0px 0px -42px 0px' });
        targets.forEach((element) => revealObserver.observe(element));
        window.setTimeout(() => targets.filter((element) => !element.classList.contains('is-visible')).forEach((element) => element.classList.add('is-visible')), 1300);
    };

    const setupCardTilt = (root = document) => {
        if (reducedMotion || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
        root.querySelectorAll('.metric-card,.mini-feature-card,.blog-card,.quick-link-card,.contact-info-grid article').forEach((card) => {
            if (card.dataset.tiltBound === '1') return;
            card.dataset.tiltBound = '1';
            card.addEventListener('mousemove', (event) => { const rect = card.getBoundingClientRect(); const x = (event.clientX - rect.left) / rect.width - 0.5; const y = (event.clientY - rect.top) / rect.height - 0.5; card.style.transform = `perspective(700px) rotateX(${y * -2.2}deg) rotateY(${x * 2.6}deg) translateY(-4px)`; });
            card.addEventListener('mouseleave', () => card.style.removeProperty('transform'));
        });
    };

    const updateFaq = () => {
        const faqItems = Array.from(document.querySelectorAll('[data-faq-item]'));
        if (!faqItems.length) return;
        const faqSearch = document.querySelector('[data-faq-search]');
        const query = String(faqSearch?.value || '').trim().toLowerCase();
        let visible = 0;
        faqItems.forEach((item) => { const show = (activeFaqCategory === 'all' || item.dataset.category === activeFaqCategory) && (query === '' || String(item.dataset.search || '').includes(query)); item.classList.toggle('d-none', !show); if (show) visible++; });
        const count = document.querySelector('[data-faq-visible-count]');
        const empty = document.querySelector('[data-faq-empty]');
        if (count) count.textContent = String(visible);
        empty?.classList.toggle('d-none', visible !== 0);
    };

    const updateContactCounter = () => {
        const message = document.querySelector('[data-contact-message]');
        const counter = document.querySelector('[data-contact-character-count]');
        if (message && counter) counter.textContent = String(message.value.length);
    };

    const loadBlog = async (url, pushState = true) => {
        if (!ajax() || !document.querySelector('[data-blog-ajax-region]')) { window.location.assign(url.toString()); return; }
        blogController?.abort();
        blogController = new AbortController();
        try {
            const result = await ajax().fetchFragment(url.toString(), '[data-blog-ajax-region]', { targetSelector: '[data-blog-ajax-region]', pushState, signal: blogController.signal });
            reveal(result.node);
            setupCardTilt(result.node);
            result.node.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
        } catch (error) {
            if (error.name === 'AbortError') return;
            ajax().toast(error.message || 'Unable to refresh blog articles.', 'error');
        }
    };

    document.addEventListener('click', (event) => {
        const faqToggle = event.target.closest('[data-faq-toggle]');
        if (faqToggle) { const answer = faqToggle.parentElement?.querySelector('.faq-answer'); const open = faqToggle.getAttribute('aria-expanded') === 'true'; faqToggle.setAttribute('aria-expanded', open ? 'false' : 'true'); answer?.classList.toggle('open', !open); return; }
        const faqCategory = event.target.closest('[data-faq-category]');
        if (faqCategory) { document.querySelectorAll('[data-faq-category]').forEach((button) => button.classList.remove('active')); faqCategory.classList.add('active'); activeFaqCategory = faqCategory.dataset.faqCategory || 'all'; updateFaq(); return; }
        if (event.target.closest('[data-faq-search-button]')) { updateFaq(); document.querySelector('[data-faq-search]')?.focus(); return; }
        if (event.target.closest('[data-faq-reset]')) { activeFaqCategory = 'all'; const search = document.querySelector('[data-faq-search]'); if (search) search.value = ''; document.querySelectorAll('[data-faq-category]').forEach((button) => button.classList.toggle('active', button.dataset.faqCategory === 'all')); updateFaq(); return; }
        const blogLink = event.target.closest('[data-blog-filter], [data-blog-pagination] a[href]');
        if (blogLink && document.querySelector('[data-blog-ajax-region]')) { event.preventDefault(); loadBlog(new URL(blogLink.href, window.location.origin)); }
    });

    document.addEventListener('input', (event) => { if (event.target.matches('[data-faq-search]')) updateFaq(); if (event.target.matches('[data-contact-message]')) updateContactCounter(); });
    document.addEventListener('keydown', (event) => { if (event.target.matches('[data-faq-search]') && event.key === 'Enter') { event.preventDefault(); updateFaq(); } });

    document.addEventListener('submit', async (event) => {
        const contactForm = event.target.closest('[data-contact-form]');
        if (contactForm) {
            if (!ajax()) return;
            event.preventDefault();
            const submit = contactForm.querySelector('[data-contact-submit]');
            ajax().clearFormErrors(contactForm);
            ajax().setButtonLoading(submit, true, 'Sending…');
            try { const payload = await ajax().request(contactForm.action, { method: 'POST', form: contactForm }); ajax().toast(payload.message || 'Your message has been sent.'); contactForm.reset(); updateContactCounter(); } catch (error) { if (error.status === 422) ajax().showFormErrors(contactForm, error.payload?.errors || {}); ajax().toast(ajax().firstError(error.payload, error.message), 'error'); } finally { ajax().setButtonLoading(submit, false); }
            return;
        }

        const blogForm = event.target.closest('[data-blog-search-form]');
        if (blogForm && document.querySelector('[data-blog-ajax-region]')) {
            event.preventDefault();
            const url = new URL(blogForm.action, window.location.origin);
            url.search = new URLSearchParams(new FormData(blogForm)).toString();
            loadBlog(url);
        }
    });

    window.addEventListener('popstate', () => { if (document.querySelector('[data-blog-ajax-region]')) loadBlog(new URL(window.location.href), false); });

    reveal();
    setupCardTilt();
    updateFaq();
    updateContactCounter();
})();
