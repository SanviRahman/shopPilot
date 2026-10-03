(() => {
    'use strict';

    const onReady = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    };

    onReady(() => {
        const mainImage = document.querySelector('[data-product-main-image]');
        const thumbnails = [...document.querySelectorAll('[data-product-thumb]')];
        const lightbox = document.querySelector('[data-product-lightbox]');
        const lightboxImage = document.querySelector('[data-product-lightbox-image]');
        const zoomButton = document.querySelector('[data-product-zoom]');
        const lightboxClose = document.querySelector('[data-product-lightbox-close]');

        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', () => {
                const imageUrl = thumbnail.dataset.image;

                if (! imageUrl || ! mainImage) {
                    return;
                }

                thumbnails.forEach((item) => item.classList.remove('active'));
                thumbnail.classList.add('active');
                mainImage.src = imageUrl;

                if (lightboxImage) {
                    lightboxImage.src = imageUrl;
                }
            });
        });

        const openLightbox = () => {
            if (! lightbox || ! lightboxImage || ! mainImage) {
                return;
            }

            lightboxImage.src = mainImage.src;
            lightbox.classList.add('open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const closeLightbox = () => {
            if (! lightbox) {
                return;
            }

            lightbox.classList.remove('open');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        zoomButton?.addEventListener('click', openLightbox);
        lightboxClose?.addEventListener('click', closeLightbox);
        lightbox?.addEventListener('click', (event) => {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && lightbox?.classList.contains('open')) {
                closeLightbox();
            }
        });

        document.querySelectorAll('[data-product-quantity]').forEach((control) => {
            const input = control.querySelector('[data-qty-input]');
            const minus = control.querySelector('[data-qty-minus]');
            const plus = control.querySelector('[data-qty-plus]');

            if (! input) {
                return;
            }

            const min = Math.max(1, Number.parseInt(input.min || '1', 10));
            const maxFromControl = Number.parseInt(control.dataset.max || '', 10);
            const maxFromInput = Number.parseInt(input.max || '', 10);
            const max = Number.isFinite(maxFromControl)
                ? maxFromControl
                : (Number.isFinite(maxFromInput) ? maxFromInput : Number.MAX_SAFE_INTEGER);

            const normalize = (value) => Math.min(max, Math.max(min, Number.parseInt(value, 10) || min));
            const setQuantity = (value) => {
                input.value = String(normalize(value));
            };

            minus?.addEventListener('click', () => setQuantity(Number(input.value) - 1));
            plus?.addEventListener('click', () => setQuantity(Number(input.value) + 1));
            input.addEventListener('change', () => setQuantity(input.value));
        });

        const tabs = [...document.querySelectorAll('[data-product-tab]')];
        const panels = [...document.querySelectorAll('[data-product-panel]')];

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.productTab;

                tabs.forEach((item) => {
                    const active = item === tab;
                    item.classList.toggle('active', active);
                    item.setAttribute('aria-selected', active ? 'true' : 'false');
                });

                panels.forEach((panel) => {
                    const active = panel.dataset.productPanel === target;
                    panel.classList.toggle('active', active);
                    panel.hidden = ! active;
                });
            });
        });

        const relatedTrack = document.querySelector('[data-related-track]');
        const relatedPrev = document.querySelector('[data-related-prev]');
        const relatedNext = document.querySelector('[data-related-next]');

        const scrollRelated = (direction) => {
            if (! relatedTrack) {
                return;
            }

            const item = relatedTrack.querySelector('.related-product-item');
            const amount = item ? item.getBoundingClientRect().width + 12 : relatedTrack.clientWidth * 0.8;
            relatedTrack.scrollBy({ left: direction * amount, behavior: 'smooth' });
        };

        relatedPrev?.addEventListener('click', () => scrollRelated(-1));
        relatedNext?.addEventListener('click', () => scrollRelated(1));

        const revealItems = [...document.querySelectorAll('[data-product-reveal]')];

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, currentObserver) => {
                entries.forEach((entry) => {
                    if (! entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    currentObserver.unobserve(entry.target);
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });

            revealItems.forEach((item, index) => {
                item.style.setProperty('--product-reveal-delay', `${Math.min(index * 45, 220)}ms`);
                observer.observe(item);
            });
        } else {
            revealItems.forEach((item) => item.classList.add('is-visible'));
        }
    });
})();
