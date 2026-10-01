(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const toast = document.getElementById('spToast');
    let toastTimer = null;

    function showToast(message) {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => toast.classList.remove('show'), 2600);
    }

    function completePageLoad() {
        document.body.classList.add('page-loaded');
        const loader = document.getElementById('siteLoader');
        if (!loader) return;
        window.setTimeout(() => loader.classList.add('is-hidden'), reducedMotion ? 0 : 260);
    }

    if (document.readyState === 'complete') {
        completePageLoad();
    } else {
        window.addEventListener('load', completePageLoad, { once: true });
        window.setTimeout(completePageLoad, 1200);
    }

    document.addEventListener('click', (event) => {
        const comingSoon = event.target.closest('[data-coming-soon]');
        if (comingSoon) {
            event.preventDefault();
            showToast(`${comingSoon.dataset.comingSoon} will be connected in the next frontend step.`);
        }
    });

    const mobileToggle = document.querySelector('[data-mobile-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', () => {
            const open = mobileMenu.classList.toggle('open');
            mobileToggle.setAttribute('aria-expanded', String(open));
            mobileToggle.innerHTML = open ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });

        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('open');
                mobileToggle.setAttribute('aria-expanded', 'false');
                mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
            });
        });
    }

    const categoryToggle = document.querySelector('[data-category-toggle]');
    const categoryMenu = document.querySelector('[data-category-menu]');

    if (categoryToggle && categoryMenu) {
        categoryToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const open = categoryMenu.classList.toggle('open');
            categoryToggle.setAttribute('aria-expanded', String(open));
        });

        document.addEventListener('click', (event) => {
            if (!categoryMenu.contains(event.target) && !categoryToggle.contains(event.target)) {
                categoryMenu.classList.remove('open');
                categoryToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const slider = document.querySelector('[data-hero-slider]');
    if (slider) {
        const slides = Array.from(slider.querySelectorAll('[data-hero-slide]'));
        const dots = Array.from(slider.querySelectorAll('[data-hero-dot]'));
        const prev = slider.querySelector('[data-hero-prev]');
        const next = slider.querySelector('[data-hero-next]');
        const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        const autoplayDelay = Number(slider.dataset.autoplayMs || 4200);
        let current = 0;
        let autoplayTimer = null;
        let touchStartX = null;

        const clearAutoplay = () => {
            if (autoplayTimer !== null) {
                window.clearTimeout(autoplayTimer);
                autoplayTimer = null;
            }
        };

        const goTo = (index) => {
            if (!slides.length) return;

            current = (index + slides.length) % slides.length;

            slides.forEach((slide, i) => {
                const active = i === current;
                slide.classList.toggle('active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');

                if (!active) {
                    slide.style.removeProperty('--hero-x');
                    slide.style.removeProperty('--hero-y');
                    slide.style.removeProperty('--hero-img-x');
                    slide.style.removeProperty('--hero-img-y');
                }
            });

            dots.forEach((dot, i) => {
                const active = i === current;
                dot.classList.toggle('active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
            });
        };

        const scheduleAutoplay = () => {
            clearAutoplay();

            if (slides.length <= 1 || reducedMotion || document.hidden) {
                return;
            }

            autoplayTimer = window.setTimeout(() => {
                goTo(current + 1);
                scheduleAutoplay();
            }, autoplayDelay);
        };

        const goToAndRestart = (index) => {
            goTo(index);
            scheduleAutoplay();
        };

        prev?.addEventListener('click', () => goToAndRestart(current - 1));
        next?.addEventListener('click', () => goToAndRestart(current + 1));

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToAndRestart(index));
        });

        // Keep autoplay running even when the pointer is over the hero. This avoids
        // desktop/mobile-emulation hover states making the carousel appear frozen.
        if (finePointer && !reducedMotion) {
            slider.addEventListener('mousemove', (event) => {
                const rect = slider.getBoundingClientRect();
                const x = (event.clientX - rect.left) / rect.width - 0.5;
                const y = (event.clientY - rect.top) / rect.height - 0.5;
                const active = slider.querySelector('.hero-slide.active');
                if (!active) return;
                active.style.setProperty('--hero-x', `${x * 14}px`);
                active.style.setProperty('--hero-y', `${y * 10}px`);
                active.style.setProperty('--hero-img-x', `${x * -9}px`);
                active.style.setProperty('--hero-img-y', `${y * -6}px`);
            });
        }

        // Lightweight swipe support for phones/tablets.
        slider.addEventListener('touchstart', (event) => {
            touchStartX = event.changedTouches[0]?.clientX ?? null;
        }, { passive: true });

        slider.addEventListener('touchend', (event) => {
            if (touchStartX === null) return;
            const endX = event.changedTouches[0]?.clientX ?? touchStartX;
            const distance = endX - touchStartX;
            touchStartX = null;

            if (Math.abs(distance) < 45) return;
            goToAndRestart(distance < 0 ? current + 1 : current - 1);
        }, { passive: true });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearAutoplay();
            } else {
                scheduleAutoplay();
            }
        });

        goTo(0);
        scheduleAutoplay();
    }

    const newsletter = document.querySelector('[data-newsletter-form]');
    if (newsletter) {
        newsletter.addEventListener('submit', (event) => {
            event.preventDefault();
            const input = newsletter.querySelector('input[type="email"]');
            if (!input?.value) return;
            showToast('Newsletter UI is ready. Subscription storage will be connected later.');
            newsletter.reset();
        });
    }

    const header = document.getElementById('siteHeader');
    if (header) {
        const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 80);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    const revealGroups = [
        ['.benefit-item', 70],
        ['.section-heading', 0],
        ['.category-card', 55],
        ['.product-card', 45],
        ['.promo-banner', 0],
        ['.quality-strip > div', 70],
        ['.newsletter-card', 0],
        ['.footer-grid > div', 55],
    ];

    const revealTargets = [];
    revealGroups.forEach(([selector, step]) => {
        document.querySelectorAll(selector).forEach((element, index) => {
            element.classList.add('reveal-target');
            element.style.setProperty('--reveal-delay', `${Math.min(index * step, 320)}ms`);
            revealTargets.push(element);
        });
    });

    const promo = document.querySelector('.promo-banner');
    promo?.classList.add('reveal-left');

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
            threshold: 0.12,
            rootMargin: '0px 0px -45px 0px',
        });

        revealTargets.forEach((element) => observer.observe(element));
    }
})();
