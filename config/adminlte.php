<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'TI Constructions',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => false, // Desabilitado para melhor performance mobile
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '',
    'logo_img' => 'img/logo.png',
    'logo_img_class' => 'elevation-0',
    'logo_img_xl' => null,
    'logo_img_xl_class' => '',
    'logo_img_alt' => 'TI Constructions',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'img/logo.png',
            'alt' => 'TI Constructions',
            'class' => '',
            'width' => 250,
            'height' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'img/brs-logo.png',
            'alt' => 'TI Constructions',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
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
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
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
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */
    
    'menu' => [

        // ── Dashboard ────────────────────────────────────────────────────────
        [
            'text' => 'app.menu.dashboard',
            'url'  => 'home',
            'icon' => 'fas fa-tachometer-alt',
            'can'  => 'dashboard-ver',
        ],

        // ── Cronograma de Obra ───────────────────────────────────────────────
        [
            'text'    => 'app.menu.schedule',
            'icon'    => 'fas fa-calendar-alt',
            'can_any' => ['cronograma-ver', 'cronograma-criar'],
            'submenu' => [
                [
                    'text' => 'app.menu.construction_schedule',
                    'url'  => 'cronograma',
                    'icon' => 'fas fa-tasks',
                    'can'  => 'cronograma-ver',
                ],
                [
                    'text' => 'app.menu.new_activity',
                    'url'  => 'cronograma/criar',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'cronograma-criar',
                ],
                [
                    'text' => 'app.menu.schedule_occurrences',
                    'url'  => 'cronograma/ocorrencias',
                    'icon' => 'fas fa-exclamation-circle',
                    'can'  => 'ocorrencias-cronograma-ver',
                ],
            ],
        ],

        // ── Diário de Obra ───────────────────────────────────────────────────
        [
            'text'    => 'app.menu.work_diary',
            'icon'    => 'fas fa-book-open',
            'can_any' => ['diario-ver', 'diario-criar'],
            'submenu' => [
                [
                    'text' => 'app.menu.all_records',
                    'url'  => 'diario',
                    'icon' => 'fas fa-list',
                    'can'  => 'diario-ver',
                ],
                [
                    'text' => 'app.menu.new_record',
                    'url'  => 'diario/novo',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'diario-criar',
                ],
            ],
        ],

        // ── Controle de Gastos ───────────────────────────────────────────────
        [
            'text'    => 'app.menu.cost_control',
            'icon'    => 'fas fa-dollar-sign',
            'can_any' => ['gastos-ver', 'fluxo-caixa-ver'],
            'submenu' => [
                [
                    'text' => 'app.menu.project_costs',
                    'url'  => 'gastos',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can'  => 'gastos-ver',
                ],
                [
                    'text' => 'app.menu.record_cost',
                    'url'  => 'gastos/criar',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'gastos-criar',
                ],
                [
                    'text' => 'app.menu.cash_flow',
                    'url'  => 'gastos/fluxo-caixa',
                    'icon' => 'fas fa-chart-line',
                    'can'  => 'fluxo-caixa-ver',
                ],
            ],
        ],

        // ── Gestão de Compras / Fornecedores ────────────────────────────────
        [
            'text'    => 'Gestão de Compras',
            'icon'    => 'fas fa-truck',
            'submenu' => [
                [
                    'text' => 'Fornecedores',
                    'url'  => 'fornecedores',
                    'icon' => 'fas fa-building',
                ],
                [
                    'text' => 'Novo Fornecedor',
                    'url'  => 'fornecedores/novo',
                    'icon' => 'fas fa-plus',
                ],
                [
                    'text' => 'Comparar Preços',
                    'url'  => 'fornecedores/relatorio-comparacao',
                    'icon' => 'fas fa-chart-bar',
                ],
                ['header' => 'Categorias'],
                [
                    'text' => 'Categorias de Custo',
                    'url'  => 'categorias-custo',
                    'icon' => 'fas fa-tags',
                ],
            ],
        ],

        // ── Funcionários (RH de Obra) ────────────────────────────────────────
        [
            'text'    => 'app.menu.employees',
            'icon'    => 'fas fa-hard-hat',
            'can_any' => ['funcionarios-ver', 'apontamento-ver'],
            'submenu' => [
                [
                    'text' => 'app.menu.employee_registration',
                    'url'  => 'funcionarios',
                    'icon' => 'fas fa-users',
                    'can'  => 'funcionarios-ver',
                ],
                [
                    'text' => 'app.menu.daily_timesheet',
                    'url'  => 'funcionarios/apontamento',
                    'icon' => 'fas fa-clock',
                    'can'  => 'apontamento-ver',
                ],
                [
                    'text' => 'app.menu.approve_timesheets',
                    'url'  => 'funcionarios/apontamento/aprovar',
                    'icon' => 'fas fa-check-circle',
                    'can'  => 'apontamento-aprovar',
                ],
            ],
        ],

        // ── Suprimentos ─────────────────────────────────────────────────────
        // DESATIVADO — módulo não será utilizado
        // [
        //     'text'    => 'app.menu.supplies',
        //     'icon'    => 'fas fa-truck-loading',
        //     'can_any' => ['materiais-ver', 'solicitacao-compra-ver', 'cotacao-ver', 'ordem-compra-ver', 'estoque-ver', 'fornecedores-ver'],
        //     'submenu' => [
        //         ['text' => 'app.menu.materials',       'url' => 'suprimentos/materiais',      'icon' => 'fas fa-box',                'can' => 'materiais-ver'],
        //         ['text' => 'app.menu.suppliers',       'url' => 'suprimentos/fornecedores',   'icon' => 'fas fa-building',           'can' => 'fornecedores-ver'],
        //         ['text' => 'app.menu.purchase_request','url' => 'suprimentos/solicitacoes',   'icon' => 'fas fa-hand-paper',         'can' => 'solicitacao-compra-ver'],
        //         ['text' => 'app.menu.quotations',      'url' => 'suprimentos/cotacoes',       'icon' => 'fas fa-file-invoice-dollar','can' => 'cotacao-ver'],
        //         ['text' => 'app.menu.purchase_orders', 'url' => 'suprimentos/ordens-compra',  'icon' => 'fas fa-file-contract',      'can' => 'ordem-compra-ver'],
        //         ['text' => 'app.menu.inventory',       'url' => 'suprimentos/estoque',        'icon' => 'fas fa-boxes',              'can' => 'estoque-ver'],
        //     ],
        // ],

        // ── Produção e Avanço Físico ─────────────────────────────────────────
        [
            'text'    => 'app.menu.production',
            'icon'    => 'fas fa-hard-hat',
            'can_any' => ['producao-ver', 'producao-lancar'],
            'submenu' => [
                [
                    'text' => 'app.menu.physical_progress',
                    'url'  => 'producao',
                    'icon' => 'fas fa-chart-bar',
                    'can'  => 'producao-ver',
                ],
                [
                    'text' => 'app.menu.record_measurement',
                    'url'  => 'producao/medicao',
                    'icon' => 'fas fa-ruler-combined',
                    'can'  => 'producao-lancar',
                ],
                [
                    'text' => 'app.menu.approve_measurements',
                    'url'  => 'producao/aprovacao',
                    'icon' => 'fas fa-check-double',
                    'can'  => 'producao-aprovar',
                ],
            ],
        ],

        // ── Riscos e Ocorrências ─────────────────────────────────────────────
        [
            'text'    => 'app.menu.risks_occurrences',
            'icon'    => 'fas fa-exclamation-triangle',
            'can_any' => ['riscos-ver', 'ocorrencias-ver'],
            'submenu' => [
                [
                    'text' => 'app.menu.risk_matrix',
                    'url'  => 'riscos',
                    'icon' => 'fas fa-shield-alt',
                    'can'  => 'riscos-ver',
                ],
                [
                    'text' => 'app.menu.register_risk',
                    'url'  => 'riscos/criar',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'riscos-criar',
                ],
                [
                    'text' => 'app.menu.occurrences',
                    'url'  => 'ocorrencias',
                    'icon' => 'fas fa-clipboard-list',
                    'can'  => 'ocorrencias-ver',
                ],
                [
                    'text' => 'app.menu.register_occurrence',
                    'url'  => 'ocorrencias/criar',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'ocorrencias-criar',
                ],
            ],
        ],

        // ── Qualidade ────────────────────────────────────────────────────────
        [
            'text'    => 'app.menu.quality',
            'icon'    => 'fas fa-medal',
            'can_any' => ['qualidade-ver', 'qualidade-checklist'],
            'submenu' => [
                [
                    'text' => 'app.menu.checklists',
                    'url'  => 'qualidade/checklists',
                    'icon' => 'fas fa-list-ol',
                    'can'  => 'qualidade-checklist',
                ],
                [
                    'text' => 'app.menu.inspections',
                    'url'  => 'qualidade/inspecoes',
                    'icon' => 'fas fa-search',
                    'can'  => 'qualidade-inspecao',
                ],
                [
                    'text' => 'app.menu.non_conformities',
                    'url'  => 'qualidade/nao-conformidades',
                    'icon' => 'fas fa-times-circle',
                    'can'  => 'qualidade-nao-conformidade',
                ],
            ],
        ],

        // ── Relatórios ───────────────────────────────────────────────────────
        [
            'text'    => 'app.menu.reports',
            'icon'    => 'fas fa-chart-pie',
            'can_any' => [
                'relatorio-curva-s-fisica',
                'relatorio-curva-s-financeira',
                'relatorio-cronograma',
                'relatorio-custos',
                'relatorio-mao-de-obra',
                'relatorio-suprimentos',
                'relatorio-producao',
                'relatorio-riscos',
                'relatorio-qualidade',
            ],
            'submenu' => [
                [
                    'text' => 'app.menu.physical_s_curve',
                    'url'  => 'relatorios/curva-s-fisica',
                    'icon' => 'fas fa-chart-area',
                    'can'  => 'relatorio-curva-s-fisica',
                ],
                [
                    'text' => 'app.menu.financial_s_curve',
                    'url'  => 'relatorios/curva-s-financeira',
                    'icon' => 'fas fa-chart-line',
                    'can'  => 'relatorio-curva-s-financeira',
                ],
                [
                    'text' => 'app.menu.schedule_report',
                    'url'  => 'relatorios/cronograma',
                    'icon' => 'fas fa-calendar-check',
                    'can'  => 'relatorio-cronograma',
                ],
                [
                    'text' => 'app.menu.costs_report',
                    'url'  => 'relatorios/custos',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can'  => 'relatorio-custos',
                ],
                [
                    'text' => 'app.menu.labor_report',
                    'url'  => 'relatorios/mao-de-obra',
                    'icon' => 'fas fa-users',
                    'can'  => 'relatorio-mao-de-obra',
                ],
                [
                    'text' => 'app.menu.supplies_report',
                    'url'  => 'relatorios/suprimentos',
                    'icon' => 'fas fa-boxes',
                    'can'  => 'relatorio-suprimentos',
                ],
                [
                    'text' => 'app.menu.production_report',
                    'url'  => 'relatorios/producao',
                    'icon' => 'fas fa-industry',
                    'can'  => 'relatorio-producao',
                ],
                [
                    'text' => 'app.menu.risks_report',
                    'url'  => 'relatorios/riscos',
                    'icon' => 'fas fa-exclamation-triangle',
                    'can'  => 'relatorio-riscos',
                ],
                [
                    'text' => 'app.menu.quality_report',
                    'url'  => 'relatorios/qualidade',
                    'icon' => 'fas fa-medal',
                    'can'  => 'relatorio-qualidade',
                ],
            ],
        ],

        // ── Gerenciamento ────────────────────────────────────────────────────
        [
            'text'    => 'app.menu.management',
            'icon'    => 'fas fa-users-cog',
            'can_any' => ['gerenciar-usuarios', 'gerenciar-permissoes'],
            'submenu' => [
                [
                    'text' => 'app.menu.users',
                    'url'  => 'usuarios',
                    'icon' => 'fas fa-user-cog',
                    'can'  => 'gerenciar-usuarios',
                ],
                [
                    'text' => 'app.menu.permissions',
                    'url'  => 'permissoes',
                    'icon' => 'fas fa-key',
                    'can'  => 'gerenciar-permissoes',
                ],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
        App\Filters\MenuPermissionFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'DarkMode' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '/css/dark-mode.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '/js/dark-mode.js',
                ],
            ],
        ],
        'ThemeSigo' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '/css/theme-sigo.css?v=3.6',
                ],
            ],
        ],
        'SidebarUX' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '/css/sidebar-ux.css',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '/css/fix-menu.css',
                ],
            ],
        ],
        'Datatables' => [
            'active' => true,
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
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
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
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
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
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    */
    
    'auth_type' => 'login',
    'auth_field' => [
        'username' => 'login',
        'password' => 'password'
    ],
    'auth_labels' => [
        'username' => 'Nome de Usuário',
        'password' => 'Senha'
    ],
    'auth_icons' => [
        'username' => 'fas fa-user',
        'password' => 'fas fa-lock'
    ],
    'auth_btn_label' => 'Entrar',
    'auth_remember_label' => 'Lembrar-me',
];
