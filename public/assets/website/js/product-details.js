(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const mainImage = document.querySelector('[data-product-main-image]');
    const lightboxImage = document.querySelector('[data-product-lightbox-image]');
    const thumbs = Array.from(document.querySelectorAll('[data-product-thumb]'));

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const image = thumb.dataset.image;
            if (!image || !mainImage) return;

            thumbs.forEach((item) => item.classList.remove('active'));
            thumb.classList.add('active');

            if (reducedMotion) {
                mainImage.src = image;
            } else {
                mainImage.animate([
                    { opacity: .35, transform: 'scale(.97)' },
                    { opacity: 1, transform: 'scale(1)' },
                ], { duration: 280, easing: 'ease-out' });
                mainImage.src = image;
            }

            if (lightboxImage) lightboxImage.src = image;
        });
    });

    const zoomButton = document.querySelector('[data-product-zoom]');
    const lightbox = document.querySelector('[data-product-lightbox]');
    const lightboxClose = document.querySelector('[data-product-lightbox-close]');

    const setLightbox = (open) => {
        if (!lightbox) return;
        lightbox.classList.toggle('open', open);
        lightbox.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';
        if (open && lightboxImage && mainImage) lightboxImage.src = mainImage.src;
    };

    zoomButton?.addEventListener('click', () => setLightbox(true));
    lightboxClose?.addEventListener('click', () => setLightbox(false));
    lightbox?.addEventListener('click', (event) => {
        if (event.target === lightbox) setLightbox(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setLightbox(false);
    });

    document.querySelectorAll('[data-product-quantity]').forEach((control) => {
        const input = control.querySelector('[data-qty-input]');
        const minus = control.querySelector('[data-qty-minus]');
        const plus = control.querySelector('[data-qty-plus]');
        const max = Math.max(1, Number(control.dataset.max || 1));

        const clamp = (value) => Math.max(1, Math.min(max, Number(value) || 1));
        const setValue = (value) => {
            if (!input) return;
            input.value = String(clamp(value));
        };

        minus?.addEventListener('click', () => setValue(Number(input?.value || 1) - 1));
        plus?.addEventListener('click', () => setValue(Number(input?.value || 1) + 1));
        input?.addEventListener('change', () => setValue(input.value));
    });

    const tabButtons = Array.from(document.querySelectorAll('[data-product-tab]'));
    const tabPanels = Array.from(document.querySelectorAll('[data-product-panel]'));

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.dataset.productTab;
            tabButtons.forEach((item) => {
                const active = item === button;
                item.classList.toggle('active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            tabPanels.forEach((panel) => {
                const active = panel.dataset.productPanel === target;
                panel.classList.toggle('active', active);
                panel.hidden = !active;
            });
        });
    });

    const relatedTrack = document.querySelector('[data-related-track]');
    const relatedPrev = document.querySelector('[data-related-prev]');
    const relatedNext = document.querySelector('[data-related-next]');

    const relatedScrollAmount = () => {
        if (!relatedTrack) return 320;
        const firstItem = relatedTrack.querySelector('.related-product-item');
        return firstItem ? firstItem.getBoundingClientRect().width + 12 : 320;
    };

    relatedPrev?.addEventListener('click', () => relatedTrack?.scrollBy({
        left: -relatedScrollAmount() * 2,
        behavior: reducedMotion ? 'auto' : 'smooth',
    }));
    relatedNext?.addEventListener('click', () => relatedTrack?.scrollBy({
        left: relatedScrollAmount() * 2,
        behavior: reducedMotion ? 'auto' : 'smooth',
    }));

    const revealTargets = Array.from(document.querySelectorAll('[data-product-reveal]'));
    revealTargets.forEach((element, index) => {
        element.style.setProperty('--product-reveal-delay', `${Math.min(index * 65, 300)}ms`);
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
            threshold: .1,
            rootMargin: '0px 0px -45px 0px',
        });

        revealTargets.forEach((element) => observer.observe(element));
    }
})();
