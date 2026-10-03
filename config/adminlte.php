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
    'usermenu_profile_url'                    => true,

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
            'text'   => 'Dashboard',
            'route'  => 'admin.redirect',
            'icon'   => 'fas fa-fw fa-tachometer-alt',
            'can'    => 'dashboard.view',
            'active' => ['admin/dashboard*'],
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

        // CONTACT MANAGEMENT
        [
            'header' => 'CONTACT MANAGEMENT',
        ],
        [
            'text'   => 'Contacts',
            'route'  => 'admin.contacts.index',
            'icon'   => 'fas fa-fw fa-address-card',
            'active' => ['admin/contacts*'],
            'can'    => 'contacts.view',
        ],
        [
            'text'   => 'Contact Messages',
            'route'  => 'admin.contact-messages.index',
            'icon'   => 'fas fa-fw fa-inbox',
            'active' => ['admin/contact-messages*'],
            'can'    => 'contact-messages.view',
        ],

        // MARKETING MANAGEMENT
        [
            'header' => 'MARKETING MANAGEMENT',
            'can'    => 'newsletter-subscribers.view',
        ],
        [
            'text'   => 'Newsletter Subscribers',
            'route'  => 'admin.newsletter-subscribers.index',
            'icon'   => 'fas fa-fw fa-envelope-open-text',
            'active' => ['admin/newsletter-subscribers*'],
            'can'    => 'newsletter-subscribers.view',
        ],

        // E-COMMERCE MANAGEMENT
        [
            'header' => 'E-COMMERCE MANAGEMENT',
            'can'    => ['categories.view', 'products.view',
                'coupons.view', 'payment-methods.view'],
        ],
        [
            'text'    => 'Ecommerce',
            'icon'    => 'fas fa-fw fa-shopping-bag',
            'can'     => ['categories.view', 'products.view',
                'coupons.view', 'payment-methods.view',
                'orders.view', 'order-items.view', 'order-histories.view'],
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

                [
                    'text'   => 'Order Histories',
                    'icon'   => 'fas fa-fw fa-history',
                    'route'  => 'admin.order-histories.index',
                    'active' => ['admin/order-histories*'],
                    'can'    => 'orders.view',
                ],
            ],
        ],

        // PAYMENT MANAGEMENT
        [
            'header' => 'PAYMENT MANAGEMENT',
            'can'    => 'payment-methods.view',
        ],
        [
            'text'   => 'Payment Methods',
            'icon'   => 'fas fa-fw fa-credit-card',
            'route'  => 'admin.payment-methods.index',
            'active' => ['admin/payment-methods*'],
            'can'    => 'payment-methods.view',
        ],
        [
            'text'   => 'Payment Submissions',
            'icon'   => 'fas fa-fw fa-file-invoice-dollar',
            'route'  => 'admin.payments.index',
            'active' => ['admin/payments*'],
            'can'    => 'payments.view',
        ],

        // CUSTOMER MANAGEMENT
        [
            'header' => 'CUSTOMER MANAGEMENT',
            'can'    => 'customers.view',
        ],
        [
            'text'   => 'Customers',
            'route'  => 'admin.customers.index',
            'icon'   => 'fas fa-fw fa-users',
            'active' => ['admin/customers*'],
            'can'    => 'customers.view',
        ],

        // TRACKING & ANALYTICS
        [
            'header' => 'TRACKING & ANALYTICS',
            'can'    => 'meta-pixels.view',
        ],
        [
            'text'   => 'Meta Pixel',
            'route'  => 'admin.meta-pixels.index',
            'icon'   => 'fab fa-fw fa-facebook',
            'active' => ['admin/meta-pixels*'],
            'can'    => 'meta-pixels.view',
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
            'text'   => 'System Commands',
            'route'  => 'admin.command.index',
            'icon'   => 'fas fa-fw fa-terminal',
            'active' => ['admin/command*'],
            'can'    => 'settings.update',
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
