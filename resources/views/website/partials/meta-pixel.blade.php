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
            window[layerName] = window[layerName] || [];
            window[layerName].push({
                event: 'shop_pilot_meta_lifecycle',
                tracking_active: @json($metaPixels->isNotEmpty()),
                active_config_count: @json($metaPixels->count()),
                active_pixel_ids: @json($allPixelIds),
                lifecycle_state: @json($metaPixels->isNotEmpty() ? 'active' : 'inactive')
            });
        })();
    </script>
@endif

@if($metaPixels->isNotEmpty())
    @foreach($metaPixels as $metaPixel)
        @if(filled($metaPixel->full_script))
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
