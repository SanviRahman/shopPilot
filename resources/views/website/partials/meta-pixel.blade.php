@php
    $metaPixelService = app(\App\Services\MetaPixelService::class);
    $metaPixels = $metaPixelService->activeForFrontend();
    $pageViewIds = $metaPixels->where('track_page_view', true)->pluck('pixel_ids')->flatten()->filter()->unique()->values();
    $ecommerceIds = $metaPixels->where('track_ecommerce', true)->pluck('pixel_ids')->flatten()->filter()->unique()->values();
    $allPixelIds = $pageViewIds->merge($ecommerceIds)->filter()->unique()->values();
    $serverMetaEvent = session()->pull('meta_event');
    $dataLayerEnabled = (bool) config('meta-pixel.data_layer.enabled', true);
    $dataLayerName = (string) config('meta-pixel.data_layer.name', 'dataLayer');
    $dataLayerEventMap = (array) config('meta-pixel.event_map', []);
    $dataLayerLifecycleEnabled = (bool) config('meta-pixel.data_layer.lifecycle.enabled', true);
    $dataLayerHistoryEnabled = (bool) config('meta-pixel.data_layer.lifecycle.history', true);
    $dataLayerScrollThresholds = collect(config('meta-pixel.data_layer.lifecycle.scroll_thresholds', [25, 50, 75, 90]))
        ->map(fn ($threshold) => (int) $threshold)
        ->filter(fn ($threshold) => $threshold > 0 && $threshold <= 100)
        ->unique()
        ->sort()
        ->values()
        ->all();

    $routeEvent = null;
    if (request()->routeIs('website.products.show') && isset($product)) {
        $routeEvent = [
            'name' => 'ViewContent',
            'payload' => [
                'content_ids' => [(string) $product->id],
                'content_name' => $product->name,
                'content_type' => 'product',
                'value' => (float) ($product->sale_price ?: $product->regular_price),
                'currency' => 'BDT',
            ],
        ];
    } elseif (request()->routeIs('website.shop') && request()->filled('q')) {
        $routeEvent = ['name' => 'Search', 'payload' => ['search_string' => request('q')]];
    } elseif (request()->routeIs('website.checkout.index')) {
        $routeEvent = ['name' => 'InitiateCheckout', 'payload' => []];
    }
@endphp

@if($dataLayerEnabled)
    <script>
        (function () {
            const layerName = @json($dataLayerName);
            const lifecycleEnabled = @json($dataLayerLifecycleEnabled);
            const historyEnabled = @json($dataLayerHistoryEnabled);
            const scrollThresholds = @json($dataLayerScrollThresholds);
            const layer = window[layerName] = window[layerName] || [];

            const state = window.__ShopPilotDataLayerLifecycle = window.__ShopPilotDataLayerLifecycle || {
                gtmJs: false,
                gtmDom: false,
                gtmLoad: false,
                historyWrapped: false,
                scrollBound: false,
                scrollTicking: false,
                scrollFired: {},
                lastUrl: window.location.href,
                metaLifecycle: false
            };

            const push = (eventName, details = {}) => {
                layer.push(Object.assign({ event: eventName }, details));
            };

            if (!state.metaLifecycle) {
                state.metaLifecycle = true;
                push('shop_pilot_meta_lifecycle', {
                    tracking_active: @json($metaPixels->isNotEmpty()),
                    active_config_count: @json($metaPixels->count()),
                    active_pixel_ids: @json($allPixelIds),
                    lifecycle_state: @json($metaPixels->isNotEmpty() ? 'active' : 'inactive')
                });
            }

            if (!lifecycleEnabled) return;

            if (!state.gtmJs) {
                state.gtmJs = true;
                push('gtm.js', {
                    'gtm.start': Date.now(),
                    'gtm.uniqueEventId': Date.now()
                });
            }

            const fireDom = () => {
                if (state.gtmDom) return;
                state.gtmDom = true;
                push('gtm.dom', {
                    'gtm.uniqueEventId': Date.now()
                });
            };

            const fireLoad = () => {
                if (state.gtmLoad) return;
                state.gtmLoad = true;
                push('gtm.load', {
                    'gtm.uniqueEventId': Date.now()
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fireDom, { once: true });
            } else {
                fireDom();
            }

            if (document.readyState === 'complete') {
                window.setTimeout(fireLoad, 0);
            } else {
                window.addEventListener('load', fireLoad, { once: true });
            }

            if (historyEnabled && !state.historyWrapped) {
                state.historyWrapped = true;

                const emitHistoryChange = (source, oldUrl, oldState) => {
                    const newUrl = window.location.href;
                    if (oldUrl === newUrl) return;

                    let oldFragment = '';
                    let newFragment = '';
                    try { oldFragment = new URL(oldUrl).hash.replace(/^#/, ''); } catch (_) {}
                    try { newFragment = new URL(newUrl).hash.replace(/^#/, ''); } catch (_) {}

                    push('gtm.historyChange-v2', {
                        'gtm.historyChangeSource': source,
                        'gtm.oldUrl': oldUrl,
                        'gtm.newUrl': newUrl,
                        'gtm.oldUrlFragment': oldFragment,
                        'gtm.newUrlFragment': newFragment,
                        'gtm.oldHistoryState': oldState ?? null,
                        'gtm.newHistoryState': window.history.state ?? null,
                        'gtm.uniqueEventId': Date.now()
                    });

                    state.lastUrl = newUrl;
                };

                ['pushState', 'replaceState'].forEach((method) => {
                    const original = window.history[method];
                    if (typeof original !== 'function') return;

                    window.history[method] = function (...args) {
                        const oldUrl = window.location.href;
                        const oldState = window.history.state;
                        const result = original.apply(this, args);
                        emitHistoryChange(method === 'pushState' ? 'pushState' : 'replaceState', oldUrl, oldState);
                        return result;
                    };
                });

                window.addEventListener('popstate', (event) => {
                    const oldUrl = state.lastUrl || document.referrer || window.location.href;
                    const oldState = null;
                    window.setTimeout(() => emitHistoryChange('popstate', oldUrl, oldState), 0);
                });
            }

            if (!state.scrollBound && Array.isArray(scrollThresholds) && scrollThresholds.length) {
                state.scrollBound = true;

                const onScroll = () => {
                    if (state.scrollTicking) return;
                    state.scrollTicking = true;

                    window.requestAnimationFrame(() => {
                        state.scrollTicking = false;

                        const doc = document.documentElement;
                        const body = document.body;
                        const fullHeight = Math.max(
                            doc.scrollHeight,
                            doc.offsetHeight,
                            body ? body.scrollHeight : 0,
                            body ? body.offsetHeight : 0
                        );
                        const viewportBottom = window.scrollY + window.innerHeight;
                        const percent = fullHeight > 0
                            ? Math.min(100, Math.floor((viewportBottom / fullHeight) * 100))
                            : 100;

                        scrollThresholds.forEach((threshold) => {
                            const key = String(threshold);
                            if (state.scrollFired[key] || percent < threshold) return;

                            state.scrollFired[key] = true;
                            push('gtm.scrollDepth', {
                                'gtm.scrollThreshold': threshold,
                                'gtm.scrollUnits': 'percent',
                                'gtm.scrollDirection': 'vertical',
                                'gtm.uniqueEventId': Date.now()
                            });
                        });
                    });
                };

                window.addEventListener('scroll', onScroll, { passive: true });
            }
        })();
    </script>
@endif

@if($metaPixels->isNotEmpty())
    @foreach($metaPixels as $metaPixel)
        @php
            $entryScripts = collect($metaPixel->pixel_entries ?? [])
                ->pluck('script')
                ->filter(fn ($script) => filled($script))
                ->values();
        @endphp

        @if($entryScripts->isNotEmpty())
            @foreach($entryScripts as $entryScript)
                {!! $entryScript !!}
            @endforeach
        @elseif(filled($metaPixel->full_script))
            {{-- Legacy fallback for records created before repeatable Pixel entries existed. --}}
            {!! $metaPixel->full_script !!}
        @endif
    @endforeach

    <script>
    (function () {
        if (typeof window.fbq !== 'function') {
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
            (window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
        }

        const pageViewIds = @json($pageViewIds);
        const ecommerceIds = @json($ecommerceIds);
        const allIds = [...new Set([...pageViewIds, ...ecommerceIds].map(String))];
        const dataLayerEnabled = @json($dataLayerEnabled);
        const dataLayerName = @json($dataLayerName);
        const dataLayerEventMap = @json($dataLayerEventMap);

        allIds.forEach(id => window.fbq('init', id));

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const endpoint = @json(route('website.meta-pixel.events'));

        function eventId() {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                return window.crypto.randomUUID();
            }

            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        function pushDataLayer(name, payload, id, pixelIds) {
            if (!dataLayerEnabled) return;

            window[dataLayerName] = window[dataLayerName] || [];

            const eventName = dataLayerEventMap[name]
                || ('meta_' + String(name).replace(/([a-z])([A-Z])/g, '$1_$2').replace(/[^A-Za-z0-9_]/g, '_').toLowerCase());

            window[dataLayerName].push({
                event: eventName,
                meta_event_name: name,
                meta_event_id: id,
                meta_pixel_ids: pixelIds.map(String),
                meta_payload: payload || {},
                ecommerce: payload || {}
            });
        }

        function persist(name, payload, id, status) {
            fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    event_name: name,
                    event_id: id,
                    page_url: location.href,
                    referrer: document.referrer || null,
                    payload: payload || {},
                    delivery_status: status || 'dispatched'
                })
            }).catch(() => {});
        }

        window.ShopPilotMeta = {
            track(name, payload = {}, options = {}) {
                const ids = (options.pageView ? pageViewIds : ecommerceIds).map(String);
                if (!ids.length) return;

                const id = eventId();

                ids.forEach(pixelId => {
                    window.fbq('trackSingle', pixelId, name, payload, {eventID: id});
                });

                pushDataLayer(name, payload, id, ids);
                persist(name, payload, id, 'dispatched');
            },

            custom(name, payload = {}) {
                const ids = ecommerceIds.map(String);
                if (!ids.length) return;

                const id = eventId();

                ids.forEach(pixelId => {
                    window.fbq('trackSingleCustom', pixelId, name, payload, {eventID: id});
                });

                pushDataLayer(name, payload, id, ids);
                persist(name, payload, id, 'dispatched');
            }
        };

        if (pageViewIds.length) {
            window.ShopPilotMeta.track('PageView', {}, {pageView: true});
        }

        const routeEvent = @json($routeEvent);
        if (routeEvent) {
            window.ShopPilotMeta.track(routeEvent.name, routeEvent.payload || {});
        }

        const serverEvent = @json($serverMetaEvent);
        if (serverEvent && serverEvent.name) {
            window.ShopPilotMeta.track(serverEvent.name, serverEvent.payload || {});
        }

        document.addEventListener('click', function (event) {
            const node = event.target.closest('[data-meta-event]');
            if (!node) return;

            const name = node.dataset.metaEvent;
            let payload = {};

            try {
                payload = JSON.parse(node.dataset.metaPayload || '{}');
            } catch (_) {}

            window.ShopPilotMeta.custom(name, payload);
        }, {passive: true});
    })();
    </script>
@endif
