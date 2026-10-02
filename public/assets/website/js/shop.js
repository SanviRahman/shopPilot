(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const filterPanel = document.querySelector('[data-shop-filter-panel]');
    const filterOverlay = document.querySelector('[data-shop-filter-overlay]');
    const openFilter = document.querySelector('[data-shop-filter-open]');
    const closeFilter = document.querySelector('[data-shop-filter-close]');
    const mobileFilterQuery = window.matchMedia('(max-width: 780px)');

    const setFilterOpen = (open) => {
        if (!filterPanel || !filterOverlay) return;

        const isMobile = mobileFilterQuery.matches;
        const shouldOpen = Boolean(open) && isMobile;

        filterPanel.classList.toggle('open', shouldOpen);
        filterOverlay.classList.toggle('open', shouldOpen);
        document.body.classList.toggle('shop-filter-open', shouldOpen);

        filterPanel.setAttribute('aria-hidden', isMobile && !shouldOpen ? 'true' : 'false');
        openFilter?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    };

    // Always start with the mobile drawer closed. This also protects against
    // browser back/forward cache restoring stale open-state classes.
    setFilterOpen(false);

    openFilter?.addEventListener('click', () => setFilterOpen(true));
    closeFilter?.addEventListener('click', () => setFilterOpen(false));
    filterOverlay?.addEventListener('click', () => setFilterOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setFilterOpen(false);
    });

    mobileFilterQuery.addEventListener?.('change', () => setFilterOpen(false));

    const shopUrl = new URL(window.location.href);
    const navigateWith = (key, value) => {
        const next = new URL(shopUrl.toString());
        next.searchParams.set(key, value);
        next.searchParams.delete('page');
        window.location.assign(next.toString());
    };

    document.querySelectorAll('[data-shop-sort-select]').forEach((select) => {
        select.addEventListener('change', () => navigateWith('sort', select.value));
    });

    document.querySelector('[data-shop-per-page]')?.addEventListener('change', (event) => {
        navigateWith('per_page', event.currentTarget.value);
    });

    const strip = document.querySelector('[data-category-strip]');
    if (strip) {
        const scrollAmount = () => Math.max(210, strip.clientWidth * 0.58);
        document.querySelector('[data-category-strip-prev]')?.addEventListener('click', () => {
            strip.scrollBy({ left: -scrollAmount(), behavior: reducedMotion ? 'auto' : 'smooth' });
        });
        document.querySelector('[data-category-strip-next]')?.addEventListener('click', () => {
            strip.scrollBy({ left: scrollAmount(), behavior: reducedMotion ? 'auto' : 'smooth' });
        });
    }

    const priceRange = document.querySelector('[data-price-range]');
    if (priceRange) {
        const minRange = priceRange.querySelector('[data-price-min-range]');
        const maxRange = priceRange.querySelector('[data-price-max-range]');
        const minInput = document.querySelector('[data-price-min]');
        const maxInput = document.querySelector('[data-price-max]');
        const fill = priceRange.querySelector('[data-range-fill]');
        const minimum = Number(priceRange.dataset.min || 0);
        const maximum = Number(priceRange.dataset.max || 1000);
        const minGap = 100;

        const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

        const paint = () => {
            let low = clamp(Number(minRange.value || minimum), minimum, maximum);
            let high = clamp(Number(maxRange.value || maximum), minimum, maximum);

            if (high - low < minGap) {
                if (document.activeElement === minRange) low = high - minGap;
                else high = low + minGap;
            }

            low = clamp(low, minimum, maximum);
            high = clamp(high, minimum, maximum);
            minRange.value = String(low);
            maxRange.value = String(high);
            if (minInput) minInput.value = String(low);
            if (maxInput) maxInput.value = String(high);

            const span = Math.max(1, maximum - minimum);
            const left = ((low - minimum) / span) * 100;
            const right = 100 - ((high - minimum) / span) * 100;
            fill.style.left = `${left}%`;
            fill.style.right = `${right}%`;
        };

        minRange?.addEventListener('input', paint);
        maxRange?.addEventListener('input', paint);

        minInput?.addEventListener('change', () => {
            minRange.value = String(clamp(Number(minInput.value || minimum), minimum, maximum));
            paint();
        });
        maxInput?.addEventListener('change', () => {
            maxRange.value = String(clamp(Number(maxInput.value || maximum), minimum, maximum));
            paint();
        });

        paint();
    }

    const revealTargets = Array.from(document.querySelectorAll('[data-shop-reveal]'));
    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealTargets.forEach((element) => element.classList.add('is-visible'));
    } else {
        const observer = new IntersectionObserver((entries, instance) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                instance.unobserve(entry.target);
            });
        }, { threshold: 0.11, rootMargin: '0px 0px -38px 0px' });

        revealTargets.forEach((element, index) => {
            element.style.transitionDelay = `${Math.min(index * 45, 180)}ms`;
            observer.observe(element);
        });
    }
})();
