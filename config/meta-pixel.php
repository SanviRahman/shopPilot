<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Meta Pixel Tracking
    |--------------------------------------------------------------------------
    |
    | Global switch for ShopPilot's Meta Pixel integration. Individual pixel
    | configurations still have their own lifecycle status and schedule in the
    | database. Keeping this switch in config makes emergency disable/enable
    | possible without changing database rows.
    |
    */
    'enabled' => env('META_PIXEL_TRACKING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Frontend Cache
    |--------------------------------------------------------------------------
    |
    | Laravel 13 defaults to disallowing object unserialization from cache.
    | Therefore MetaPixelService caches plain arrays only, never Eloquent
    | models or Collection objects.
    |
    */
    'cache' => [
        'key' => env('META_PIXEL_CACHE_KEY', 'meta_pixels.live.v2'),
        'ttl_seconds' => (int) env('META_PIXEL_CACHE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Layer
    |--------------------------------------------------------------------------
    |
    | The default name is `dataLayer`, which is compatible with Google Tag
    | Manager/DataLayer debugging extensions. Meta events are mirrored into
    | this layer while still being dispatched through fbq().
    |
    */
    'data_layer' => [
        'enabled' => env('META_PIXEL_DATALAYER_ENABLED', true),
        'name' => env('META_PIXEL_DATALAYER_NAME', 'dataLayer'),
        'lifecycle' => [
            'enabled' => env('META_PIXEL_DATALAYER_LIFECYCLE_ENABLED', true),
            'history' => env('META_PIXEL_DATALAYER_HISTORY_ENABLED', true),
            'scroll_thresholds' => array_values(array_filter(array_map(
                static fn ($value) => (int) trim($value),
                explode(',', (string) env('META_PIXEL_DATALAYER_SCROLL_THRESHOLDS', '25,50,75,90'))
            ), static fn ($value) => $value > 0 && $value <= 100)),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lifecycle
    |--------------------------------------------------------------------------
    |
    | The database remains authoritative. A configuration is live only when
    | lifecycle_status=active and its optional starts_at/ends_at window allows
    | it. These values document the supported states for diagnostics/UI.
    |
    */
    'lifecycle' => [
        'states' => ['draft', 'testing', 'active', 'paused', 'archived'],
        'live_state' => 'active',
    ],

    /*
    |--------------------------------------------------------------------------
    | DataLayer Event Mapping
    |--------------------------------------------------------------------------
    */
    'event_map' => [
        'PageView' => 'page_view',
        'ViewContent' => 'view_item',
        'Search' => 'search',
        'AddToCart' => 'add_to_cart',
        'InitiateCheckout' => 'begin_checkout',
        'CompleteRegistration' => 'sign_up',
        'Purchase' => 'purchase',
    ],
];
