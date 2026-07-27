<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identidad del panel
    |--------------------------------------------------------------------------
    */

    'title' => 'Pinax',
    'title_prefix' => '',
    'title_postfix' => ' | Sistema Contable',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Fuentes externas
    |--------------------------------------------------------------------------
    | Las desactivamos para que la interfaz no dependa de Google Fonts.
    | El tema utilizará fuentes disponibles en el sistema operativo.
    */

    'google_fonts' => [
        'allowed' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logo principal
    |--------------------------------------------------------------------------
    */

    'logo' => '<b>PINAX</b>',
    'logo_img' => 'images/pinax-logo.png',
    'logo_img_class' => 'brand-image',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Logo de Pinax',

    /*
    |--------------------------------------------------------------------------
    | Logo de autenticación
    |--------------------------------------------------------------------------
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'images/pinax-logo.png',
            'alt' => 'Logo de Pinax',
            'class' => '',
            'width' => 90,
            'height' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader
    |--------------------------------------------------------------------------
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'images/pinax-logo.png',
            'alt' => 'Cargando Pinax',
            'effect' => 'animation__pulse',
            'width' => 90,
            'height' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menú de usuario
    |--------------------------------------------------------------------------
    */

    'usermenu_enabled' => false,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout general
    |--------------------------------------------------------------------------
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Clases de autenticación
    |--------------------------------------------------------------------------
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Clases generales del panel
    |--------------------------------------------------------------------------
    | Estas clases están definidas en public/css/pinax-theme.css.
    */

    'classes_body' => 'pinax-body',
    'classes_brand' => 'pinax-brand',
    'classes_brand_text' => 'pinax-brand-text',
    'classes_content_wrapper' => 'pinax-content-wrapper',
    'classes_content_header' => '',
    'classes_content' => 'pinax-content',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4 pinax-sidebar',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-dark pinax-topnav',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container-fluid',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Sidebar derecho
    |--------------------------------------------------------------------------
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | Direcciones URL
    |--------------------------------------------------------------------------
    */

    'use_route_url' => false,

    // Al hacer clic en la marca de Pinax se abrirá /dashboard.
    'dashboard_url' => 'dashboard',

    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Empaquetado de assets de Laravel
    |--------------------------------------------------------------------------
    | AdminLTE ya administra sus recursos. Nuestro CSS se cargará mediante
    | la configuración de plugins ubicada más adelante.
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menú principal
    |--------------------------------------------------------------------------
    | Solamente incluimos módulos con rutas existentes para evitar enlaces
    | que conduzcan a páginas todavía no implementadas.
    */

    'menu' => [

        /*
        |--------------------------------------------------------------------------
        | Elementos del navbar superior
        |--------------------------------------------------------------------------
        */

        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | Buscador del menú lateral
        |--------------------------------------------------------------------------
        */

        [
            'type' => 'sidebar-menu-search',
            'text' => 'Buscar módulo',
        ],

        /*
        |--------------------------------------------------------------------------
        | Navegación principal
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'NAVEGACIÓN',
        ],
        [
            'text' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'fas fa-fw fa-chart-pie',
        ],

        /*
        |--------------------------------------------------------------------------
        | Módulos de gestión
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'GESTIÓN',
        ],
        [
            'text' => 'Personas',
            'icon' => 'fas fa-fw fa-users',
            'submenu' => [
                [
                    'text' => 'Listado de personas',
                    'route' => 'personas.index',
                    'icon' => 'fas fa-fw fa-list',
                ],
                [
                    'text' => 'Registrar persona',
                    'route' => 'personas.create',
                    'icon' => 'fas fa-fw fa-user-plus',
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | CONTABILIDAD
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'CONTABILIDAD',
        ],
        [
            'text' => 'Catálogo de cuentas',
            'icon' => 'fas fa-fw fa-sitemap',
            'submenu' => [
                [
                    'text' => 'Listado de cuentas',
                    'route' => 'catalogo.index',
                    'icon' => 'fas fa-fw fa-list',
                ],
                [
                    'text' => 'Registrar cuenta',
                    'route' => 'catalogo.create',
                    'icon' => 'fas fa-fw fa-plus',
                ],
            ],
        ],
        [
            'text' => 'Asientos contables',
            'route' => 'asientos.index',
            'icon' => 'fas fa-fw fa-file-invoice-dollar',
        ],
        [
            'text' => 'CT & Mayorización',
            'route' => 'mayorizacion.index',
            'icon' => 'fas fa-fw fa-balance-scale',
        ],

        /*
        |--------------------------------------------------------------------------
        | REPORTES FINANCIEROS (ACTIVO)
        |--------------------------------------------------------------------------
        */

        [
            'text' => 'Reportes Financieros',
            'route' => 'reportes.index',
            'icon' => 'fas fa-fw fa-chart-bar',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Filtros del menú
    |--------------------------------------------------------------------------
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins
    |--------------------------------------------------------------------------
    */

    'plugins' => [

        /*
        |--------------------------------------------------------------------------
        | Tema visual local de Pinax
        |--------------------------------------------------------------------------
        | Este plugin carga public/css/pinax-theme.css en todas las páginas.
        | Es un archivo local y no depende de CDN, npm ni otra plantilla.
        */

        'PinaxTheme' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'css/pinax-theme.css',
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Plugins opcionales de AdminLTE
        |--------------------------------------------------------------------------
        | Permanecen desactivados. Si en el futuro necesitamos alguno,
        | primero evaluaremos su instalación local.
        */

        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],

        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],

        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],

        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],

        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modo IFrame
    |--------------------------------------------------------------------------
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */

    'livewire' => false,
];
