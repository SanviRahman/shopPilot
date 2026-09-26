<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    'title'                                   => 'ShopPilot',
    'title_prefix'                            => '',
    'title_postfix'                           => '',

    'use_ico_only'                            => false,
    'use_full_favicon'                        => false,

    'google_fonts'                            => [
        'allowed' => true,
    ],

    'logo'                                    => '<b>Shop</b>Pilot',
    'logo_img'                                => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class'                          => 'brand-image img-circle elevation-3',
    'logo_img_xl'                             => null,
    'logo_img_xl_class'                       => 'brand-image-xs',
    'logo_img_alt'                            => 'Admin Logo',

    'auth_logo'                               => [
        'enabled' => false,
        'img'     => [
            'path'   => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt'    => 'Auth Logo',
            'class'  => '',
            'width'  => 50,
            'height' => 50,
        ],
    ],

    'preloader'                               => [
        'enabled' => true,
        'mode'    => 'fullscreen',
        'img'     => [
            'path'   => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt'    => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width'  => 60,
            'height' => 60,
        ],
    ],

    'usermenu_enabled'                        => true,
    'usermenu_header'                         => false,
    'usermenu_header_class'                   => 'bg-primary',
    'usermenu_image'                          => true,
    'usermenu_desc'                           => false,
    'usermenu_profile_url'                    => false,

    'layout_topnav'                           => null,
    'layout_boxed'                            => null,
    'layout_fixed_sidebar'                    => null,
    'layout_fixed_navbar'                     => null,
    'layout_fixed_footer'                     => null,
    'layout_dark_mode'                        => null,

    'classes_auth_card'                       => 'card-outline card-primary',
    'classes_auth_header'                     => '',
    'classes_auth_body'                       => '',
    'classes_auth_footer'                     => '',
    'classes_auth_icon'                       => '',
    'classes_auth_btn'                        => 'btn-flat btn-primary',

    'classes_body'                            => '',
    'classes_brand'                           => '',
    'classes_brand_text'                      => '',
    'classes_content_wrapper'                 => '',
    'classes_content_header'                  => '',
    'classes_content'                         => '',
    'classes_sidebar'                         => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav'                     => '',
    'classes_topnav'                          => 'navbar-white navbar-light',
    'classes_topnav_nav'                      => 'navbar-expand',
    'classes_topnav_container'                => 'container',

    'sidebar_mini'                            => 'lg',
    'sidebar_collapse'                        => false,
    'sidebar_collapse_auto_size'              => false,
    'sidebar_collapse_remember'               => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme'                 => 'os-theme-light',
    'sidebar_scrollbar_auto_hide'             => 'l',
    'sidebar_nav_accordion'                   => true,
    'sidebar_nav_animation_speed'             => 300,

    'right_sidebar'                           => false,
    'right_sidebar_icon'                      => 'fas fa-cogs',
    'right_sidebar_theme'                     => 'dark',
    'right_sidebar_slide'                     => true,
    'right_sidebar_push'                      => true,
    'right_sidebar_scrollbar_theme'           => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide'       => 'l',

    'use_route_url'                           => true,
    'dashboard_url'                           => 'admin.redirect',
    'logout_url'                              => 'admin.logout',
    'login_url'                               => 'admin.login',
    'register_url'                            => false,
    'password_reset_url'                      => false,
    'password_email_url'                      => false,
    'profile_url'                             => false,
    'disable_darkmode_routes'                 => false,

    'laravel_asset_bundling'                  => false,
    'laravel_css_path'                        => 'css/app.css',
    'laravel_js_path'                         => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items (Role & Permission Gated)
    |--------------------------------------------------------------------------
    */

    'menu'                                    => [
        [
            'type'         => 'fullscreen-widget',
            'topnav_right' => true,
        ],
        [
            'text'  => 'Dashboard',
            'route' => 'admin.redirect',
            'icon'  => 'fas fa-fw fa-tachometer-alt',
            'can'   => 'dashboard.view',
        ],

        // MEDIA MANAGEMENT
        [
            'header' => 'MEDIA MANAGEMENT',
            'can'    => 'media.view',
        ],
        [
            'text'   => 'Media',
            'route'  => 'admin.media.index',
            'icon'   => 'fas fa-fw fa-photo-video',
            'active' => ['admin/media*'],
            'can'    => 'media.view',
        ],

        // BLOG MANAGEMENT
        [
            'header' => 'BLOG MANAGEMENT',
            'can'    => 'blogs.view',
        ],
        [
            'text'   => 'Blogs',
            'route'  => 'admin.blogs.index',
            'icon'   => 'fas fa-fw fa-newspaper',
            'active' => ['admin/blogs*'],
            'can'    => 'blogs.view',
        ],

        // E-COMMERCE MANAGEMENT
        [
            'header' => 'E-COMMERCE MANAGEMENT',
            'can'    => ['categories.view', 'products.view', 'coupons.view', 'payment-methods.view'],
        ],
        [
            'text'    => 'Ecommerce',
            'icon'    => 'fas fa-fw fa-shopping-bag',
            'can'     => ['categories.view', 'products.view', 
            'coupons.view', 'payment-methods.view', 
            'orders.view', 'order-items.view'],
            'submenu' => [
                [
                    'text'   => 'Categories',
                    'icon'   => 'fas fa-fw fa-tags',
                    'route'  => 'admin.categories.index',
                    'active' => ['admin/categories*'],
                    'can'    => 'categories.view',
                ],
                [
                    'text'   => 'Products',
                    'icon'   => 'fas fa-fw fa-box',
                    'route'  => 'admin.products.index',
                    'active' => ['admin/products*'],
                    'can'    => 'products.view',
                ],
                [
                    'text'   => 'Coupons',
                    'icon'   => 'fas fa-fw fa-percent',
                    'route'  => 'admin.coupons.index',
                    'active' => ['admin/coupons*'],
                    'can'    => 'coupons.view',
                ],
                [
                    'text'   => 'Payment Methods',
                    'icon'   => 'fas fa-fw fa-credit-card',
                    'route'  => 'admin.payment-methods.index',
                    'active' => ['admin/payment-methods*'],
                    'can'    => 'payment-methods.view',
                ],
                                [
                    'text'   => 'Orders',
                    'icon'   => 'fas fa-fw fa-shopping-cart',
                    'route'  => 'admin.orders.index',
                    'active' => ['admin/orders*'],
                    'can'    => 'orders.view',
                ],

                                [
                    'text'   => 'Order Items',
                    'icon'   => 'fas fa-fw fa-list-ol',
                    'route'  => 'admin.order-items.index',
                    'active' => ['admin/order-items*'],
                    'can'    => 'orders.view',
                ],
            ],
        ],

        // ACCOUNT
        [
            'header' => 'ACCOUNT',
        ],
        [
            'text'   => 'Profile',
            'route'  => 'admin.profile',
            'icon'   => 'fas fa-fw fa-user-circle',
            'active' => ['admin/profile*'],
        ],
        [
            'text'   => 'Change Password',
            'route'  => 'admin.password',
            'icon'   => 'fas fa-fw fa-key',
            'active' => ['admin/password*'],
        ],

        // ACCESS CONTROL
        [
            'header' => 'ACCESS CONTROL',
            'can'    => ['staff.view', 'roles.view', 'permissions.view'],
        ],
        [
            'text'   => 'Admins',
            'route'  => 'admin.admins.index',
            'icon'   => 'fas fa-fw fa-user-shield',
            'active' => ['admin/admins*'],
            'can'    => 'staff.view',
        ],
        [
            'text'   => 'Roles',
            'route'  => 'admin.roles.index',
            'icon'   => 'fas fa-fw fa-user-tag',
            'active' => ['admin/roles*'],
            'can'    => 'roles.view',
        ],
        [
            'text'   => 'Permissions',
            'route'  => 'admin.permissions.index',
            'icon'   => 'fas fa-fw fa-key',
            'active' => ['admin/permissions*'],
            'can'    => 'permissions.view',
        ],

        // SYSTEM COMMANDS (Restricted to Admins)
        [
            'header' => 'SYSTEM COMMANDS',
            'can'    => 'settings.update',
        ],
        [
            'text'    => 'Commands',
            'icon'    => 'fas fa-fw fa-terminal',
            'can'     => 'settings.update',
            'submenu' => [
                [
                    'text'  => 'Clear Cache',
                    'route' => 'admin.command.clear-cache',
                    'icon'  => 'fas fa-fw fa-broom',
                    'can'   => 'settings.update',
                ],
                [
                    'text'  => 'Clear Config',
                    'route' => 'admin.command.clear-config',
                    'icon'  => 'fas fa-fw fa-cog',
                    'can'   => 'settings.update',
                ],
                [
                    'text'  => 'Clear Route',
                    'route' => 'admin.command.clear-route',
                    'icon'  => 'fas fa-fw fa-route',
                    'can'   => 'settings.update',
                ],
                [
                    'text'  => 'Clear View',
                    'route' => 'admin.command.clear-view',
                    'icon'  => 'fas fa-fw fa-eye-slash',
                    'can'   => 'settings.update',
                ],
                [
                    'text'  => 'Optimize Clear',
                    'route' => 'admin.command.optimize-clear',
                    'icon'  => 'fas fa-fw fa-bolt',
                    'can'   => 'settings.update',
                ],
                [
                    'text'  => 'Migrate',
                    'route' => 'admin.command.migrate',
                    'icon'  => 'fas fa-fw fa-database',
                    'can'   => 'settings.update',
                ],
                [
                    'text'  => 'Seed Database',
                    'route' => 'admin.command.seed',
                    'icon'  => 'fas fa-fw fa-seedling',
                    'can'   => 'settings.update',
                ],
                [
                    'text'  => 'Migrate Fresh',
                    'route' => 'admin.command.migrate-fresh',
                    'icon'  => 'fas fa-fw fa-sync-alt',
                    'can'   => 'settings.update',
                ],
                [
                    'text'  => 'Migrate Fresh & Seed',
                    'route' => 'admin.command.migrate-fresh-seed',
                    'icon'  => 'fas fa-fw fa-database',
                    'can'   => 'settings.update',
                ],
            ],
        ],
    ],

    'filters'                                 => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    'plugins'                                 => [
        'Datatables'  => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2'     => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs'     => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace'        => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    'iframe'                                  => [
        'default_tab' => [
            'url'   => null,
            'title' => null,
        ],
        'buttons'     => [
            'close'           => true,
            'close_all'       => true,
            'close_all_other' => true,
            'scroll_left'     => true,
            'scroll_right'    => true,
            'fullscreen'      => true,
        ],
        'options'     => [
            'loading_screen'    => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items'  => true,
        ],
    ],

    'livewire'                                => false,
];
