(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const mobileFilterQuery = window.matchMedia('(max-width: 780px)');
    let filterTimer = null;
    let activeController = null;

    const ajax = () => window.ShopPilotAjax;
    const region = () => document.querySelector('[data-shop-ajax-region]');
    const filterForm = () => region()?.querySelector('[data-shop-filter-form]');

    const setFilterOpen = (open) => {
        const filterPanel = region()?.querySelector('[data-shop-filter-panel]');
        const filterOverlay = region()?.querySelector('[data-shop-filter-overlay]');
        const openFilter = region()?.querySelector('[data-shop-filter-open]');
        if (!filterPanel || !filterOverlay) return;

        const isMobile = mobileFilterQuery.matches;
        const shouldOpen = Boolean(open) && isMobile;
        filterPanel.classList.toggle('open', shouldOpen);
        filterOverlay.classList.toggle('open', shouldOpen);
        document.body.classList.toggle('shop-filter-open', shouldOpen);
        filterPanel.setAttribute('aria-hidden', isMobile && !shouldOpen ? 'true' : 'false');
        openFilter?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    };

    const setupReveal = () => {
        // Hero + breadcrumb live outside the AJAX region, while filters/products
        // live inside it. Observe the whole document so both static and freshly
        // replaced AJAX content can become visible.
        const targets = Array.from(document.querySelectorAll('[data-shop-reveal]:not(.is-visible)'));
        if (reducedMotion || !('IntersectionObserver' in window)) {
            targets.forEach((element) => element.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries, instance) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                instance.unobserve(entry.target);
            });
        }, { threshold: 0.11, rootMargin: '0px 0px -38px 0px' });

        targets.forEach((element, index) => {
            element.style.transitionDelay = `${Math.min(index * 45, 180)}ms`;
            observer.observe(element);
        });
    };

    const paintPriceRange = (priceRange, source = null) => {
        if (!priceRange) return;
        const minRange = priceRange.querySelector('[data-price-min-range]');
        const maxRange = priceRange.querySelector('[data-price-max-range]');
        const minInput = filterForm()?.querySelector('[data-price-min]');
        const maxInput = filterForm()?.querySelector('[data-price-max]');
        const fill = priceRange.querySelector('[data-range-fill]');
        if (!minRange || !maxRange || !fill) return;

        const minimum = Number(priceRange.dataset.min || 0);
        const maximum = Number(priceRange.dataset.max || 1000);
        const minGap = 100;
        const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
        let low = clamp(Number(minRange.value || minimum), minimum, maximum);
        let high = clamp(Number(maxRange.value || maximum), minimum, maximum);

        if (high - low < minGap) {
            if (source === minRange) low = high - minGap;
            else high = low + minGap;
        }

        low = clamp(low, minimum, maximum);
        high = clamp(high, minimum, maximum);
        minRange.value = String(low);
        maxRange.value = String(high);
        if (minInput) minInput.value = String(low);
        if (maxInput) maxInput.value = String(high);

        const span = Math.max(1, maximum - minimum);
        fill.style.left = `${((low - minimum) / span) * 100}%`;
        fill.style.right = `${100 - ((high - minimum) / span) * 100}%`;
    };

    const setupRegion = () => {
        setFilterOpen(false);
        region()?.querySelectorAll('[data-price-range]').forEach((node) => paintPriceRange(node));
        setupReveal();
    };

    const formUrl = () => {
        const form = filterForm();
        if (!form) return new URL(window.location.href);
        const url = new URL(form.action, window.location.origin);
        const params = new URLSearchParams(new FormData(form));
        params.delete('page');
        url.search = params.toString();
        return url;
    };

    const updateFilterHidden = (key, value) => {
        const field = filterForm()?.querySelector(`[data-filter-${key}]`);
        if (field) field.value = value;
    };

    const syncOutsideState = (parsed, payload = null) => {
        const currentCount = document.querySelector('[data-shop-total-count]');
        if (currentCount && payload && Number.isFinite(Number(payload.total_count))) {
            currentCount.innerHTML = `<i class="fas fa-box-open"></i> ${Number(payload.total_count).toLocaleString('en-US')} products found`;
        } else {
            const nextCount = parsed.querySelector('[data-shop-total-count]');
            if (nextCount && currentCount) currentCount.innerHTML = nextCount.innerHTML;
        }

        const currentSearch = document.querySelector('[data-shop-search-form] input[name="q"]');
        if (currentSearch && payload && typeof payload.search_query === 'string') {
            currentSearch.value = payload.search_query;
        } else {
            const nextSearch = parsed.querySelector('[data-shop-search-form] input[name="q"]');
            if (currentSearch) currentSearch.value = nextSearch?.value || '';
        }

        if (payload && typeof payload.breadcrumb_html === 'string' && payload.breadcrumb_html.trim() !== '') {
            const currentBreadcrumb = document.querySelector('[data-shop-breadcrumb]');
            const breadcrumbDocument = new DOMParser().parseFromString(payload.breadcrumb_html, 'text/html');
            const incomingBreadcrumb = breadcrumbDocument.querySelector('[data-shop-breadcrumb]');

            if (currentBreadcrumb && incomingBreadcrumb) {
                currentBreadcrumb.replaceWith(document.importNode(incomingBreadcrumb, true));
            }
        }

        if (payload && typeof payload.shop_context === 'string') {
            document.querySelectorAll('[data-shop-nav-context]').forEach((link) => {
                link.classList.toggle('active', link.dataset.shopNavContext === payload.shop_context);
            });
        }
    };

    const load = async (url, pushState = true) => {
        if (!region() || !ajax()) {
            window.location.assign(url.toString());
            return;
        }

        activeController?.abort();
        activeController = new AbortController();

        try {
            const result = await ajax().fetchFragment(url.toString(), '[data-shop-ajax-region]', {
                pushState,
                signal: activeController.signal,
            });
            syncOutsideState(result.document, result.payload);
            setupRegion();

            const current = new URL(result.url, window.location.origin);
            const q = current.searchParams.get('q')?.trim();
            if (q) window.ShopPilotMeta?.track?.('Search', { search_string: q });
        } catch (error) {
            if (error.name === 'AbortError') return;
            ajax().toast(error.message || 'Unable to refresh products.', 'error');
        }
    };

    const scheduleFilter = (delay = 320) => {
        window.clearTimeout(filterTimer);
        filterTimer = window.setTimeout(() => load(formUrl()), delay);
    };

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-shop-filter-form]');
        if (!form) return;
        event.preventDefault();
        load(formUrl());
    });

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!region()?.contains(target)) return;

        if (target.matches('[data-shop-sort-select]')) {
            updateFilterHidden('sort', target.value);
            load(formUrl());
            return;
        }

        if (target.matches('[data-shop-per-page]')) {
            updateFilterHidden('per-page', target.value);
            load(formUrl());
            return;
        }

        if (target.matches('[data-price-min], [data-price-max]')) {
            const range = region()?.querySelector('[data-price-range]');
            const minRange = range?.querySelector('[data-price-min-range]');
            const maxRange = range?.querySelector('[data-price-max-range]');
            if (target.matches('[data-price-min]') && minRange) minRange.value = target.value;
            if (target.matches('[data-price-max]') && maxRange) maxRange.value = target.value;
            paintPriceRange(range);
            scheduleFilter(420);
            return;
        }

        if (target.matches('[data-shop-filter-form] input[type="checkbox"]')) {
            scheduleFilter(220);
        }
    });

    document.addEventListener('input', (event) => {
        const target = event.target;
        if (!region()?.contains(target) || !target.matches('[data-price-min-range], [data-price-max-range]')) return;
        paintPriceRange(target.closest('[data-price-range]'), target);
    });

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!region()?.contains(target) || !target.matches('[data-price-min-range], [data-price-max-range]')) return;
        paintPriceRange(target.closest('[data-price-range]'), target);
        scheduleFilter(250);
    });

    document.addEventListener('click', (event) => {
        const open = event.target.closest('[data-shop-filter-open]');
        if (open) { event.preventDefault(); setFilterOpen(true); return; }

        const close = event.target.closest('[data-shop-filter-close], [data-shop-filter-overlay]');
        if (close) { event.preventDefault(); setFilterOpen(false); return; }

        const previous = event.target.closest('[data-category-strip-prev]');
        const next = event.target.closest('[data-category-strip-next]');
        if (previous || next) {
            event.preventDefault();
            const strip = region()?.querySelector('[data-category-strip]');
            if (!strip) return;
            const amount = Math.max(210, strip.clientWidth * 0.58) * (previous ? -1 : 1);
            strip.scrollBy({ left: amount, behavior: reducedMotion ? 'auto' : 'smooth' });
            return;
        }

        const link = event.target.closest('[data-shop-ajax-region] a[href]')
            || event.target.closest('header a[href], footer a[href]');
        if (!link) return;
        const rawHref = link.getAttribute('href') || '';
        if (link.hasAttribute('data-coming-soon') || rawHref.startsWith('#')) return;
        const url = new URL(link.href, window.location.origin);
        const currentUrl = new URL(window.location.href);
        if (url.origin !== window.location.origin || url.pathname !== currentUrl.pathname) return;
        event.preventDefault();
        load(url);
    });

    document.querySelector('[data-shop-search-form]')?.addEventListener('submit', (event) => {
        if (!region()) return;
        event.preventDefault();
        const form = event.currentTarget;
        const url = new URL(form.action, window.location.origin);
        const params = new URLSearchParams(new FormData(form));
        url.search = params.toString();
        load(url);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setFilterOpen(false);
    });

    mobileFilterQuery.addEventListener?.('change', () => setFilterOpen(false));

    window.addEventListener('popstate', () => {
        if (region()) load(new URL(window.location.href), false);
    });

    setupRegion();

    // Do not allow entrance-animation state to leave content invisible if an
    // observer callback is delayed by browser extensions/background throttling.
    window.setTimeout(() => {
        document.querySelectorAll('[data-shop-reveal]:not(.is-visible)').forEach((element) => {
            element.classList.add('is-visible');
        });
    }, 1200);
})();
