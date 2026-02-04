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

    'title' => 'SIGO',
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

    'logo' => '<b>SIGO</b>',
    'logo_img' => 'img/logo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => '',
    'logo_img_alt' => 'SIGO',

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
            'alt' => 'SIGO',
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
            'alt' => 'SIGO',
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
        [
            'text' => 'Dashboard',
            'url'  => 'dashboard',
            'icon' => 'fas fa-tachometer-alt',
        ],
        [
            'text'        => 'Estoque',
            'icon'        => 'fas fa-boxes',
            'submenu'     => [
                [
                    'text' => 'Controle de Estoque',
                    'url'  => 'brs/controle-estoque',
                    'icon' => 'fas fa-boxes',
                    'can'  => 'controle-estoque',
                ],
                [
                    'text' => 'Estoque Mínimo e Máximo',
                    'url'  => 'brs/estoque-min-max',
                    'icon' => 'fas fa-layer-group',
                    'can'  => 'est_mm',
                ],
                [
                    'text' => 'Baixa da O.S.',
                    'url'  => 'brs/baixa-os',
                    'icon' => 'fas fa-clipboard-check',
                    'can'  => 'baixa_os',
                ],
            ],
        ],
        [
            'text'        => 'Documentos DP',
            'icon'        => 'fas fa-folder-open',
            'submenu'     => [
                [
                    'text' => 'Inclusão Documentos',
                    'url'  => 'documentos-dp/inclusao',
                    'icon' => 'fas fa-file-upload',
                    'can'  => 'doc_dp',
                ],
                [
                    'text' => 'Funcionários',
                    'url'  => 'documentos-dp/funcionarios',
                    'icon' => 'fas fa-users-cog',
                    'can'  => 'vis_func',
                ],
            ],
        ],
        [
            'text' => 'Controle de Frete',
            'url'  => 'frete',
            'icon' => 'fas fa-truck',
            'can'  => 'frete',
        ],
        [
            'text'        => 'Área Técnica',
            'icon'        => 'fas fa-tools',
            'submenu'     => [
                [
                    'text' => 'Ordem de Serviço',
                    'url'  => 'area-tecnica/ordem-servico',
                    'icon' => 'fas fa-clipboard-list',
                    'can'  => 'ordem_servico',
                ],
                [
                    'text' => 'Gestão de O.S.',
                    'url'  => 'area-tecnica/gestao-os',
                    'icon' => 'fas fa-tasks',
                    'can'  => 'gestao_os',
                ],
                [
                    'text' => 'Centros de Custo',
                    'url'  => 'suprimentos/centros-custo',
                    'icon' => 'fas fa-sitemap',
                    'can'  => 'cc_financeiro',
                ],
            ],
        ],
        [
            'text'        => 'Suprimentos',
            'icon'        => 'fas fa-truck-loading',
            'submenu'     => [
                [
                    'text' => 'Fornecedores',
                    'url'  => 'suprimentos/fornecedores',
                    'icon' => 'fas fa-building',
                    'can'  => 'fornecedores',
                ],
                [
                    'text' => 'Solicitação',
                    'url'  => 'suprimentos/solicitacao',
                    'icon' => 'fas fa-hand-paper',
                    'can'  => 'Solicitacao',
                ],
                [
                    'text' => 'Cotação',
                    'url'  => 'suprimentos/cotacao',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can'  => 'cotacao',
                ],
                [
                    'text' => 'Ordem de Compra',
                    'url'  => 'suprimentos/ordem-compra',
                    'icon' => 'fas fa-file-contract',
                    'can'  => 'ordem_compra',
                ],
                [
                    'text' => 'Recebimento',
                    'url'  => 'suprimentos/recebimento',
                    'icon' => 'fas fa-clipboard-check',
                    'can'  => 'recebimento',
                ],
                [
                    'text' => 'Nota Fiscal Entrada',
                    'url'  => 'suprimentos/nf-entrada',
                    'icon' => 'fas fa-file-alt',
                    'can'  => 'nf_entrada',
                ],
                // Vale de Retirada - DESATIVADO
                // [
                //     'text' => 'Vale de Retirada',
                //     'url'  => 'suprimentos/vale-retirada',
                //     'icon' => 'fas fa-hand-holding-box',
                //     'can'  => 'vale_retirada',
                // ],
            ],
        ],
        [
            'text'        => 'Financeiro',
            'icon'        => 'fas fa-dollar-sign',
            'can'         => 'Financeiro',
            'submenu'     => [
                [
                    'text' => 'Contas a Pagar',
                    'url'  => 'financeiro/contas-pagar',
                    'icon' => 'fas fa-file-invoice',
                    'can'  => 'contas_pagar',
                ],
                [
                    'text' => 'Contas a Receber',
                    'url'  => 'financeiro/contas-receber',
                    'icon' => 'fas fa-hand-holding-usd',
                    'can'  => 'contas_receber',
                ],
                [
                    'text' => 'Fluxo de Caixa',
                    'url'  => 'financeiro/fluxo-caixa',
                    'icon' => 'fas fa-chart-line',
                    'can'  => 'fluxo_caixa',
                ],
                [
                    'text' => 'Bancos',
                    'url'  => 'financeiro/bancos',
                    'icon' => 'fas fa-university',
                    'can'  => 'bancos',
                ],
                [
                    'text' => 'Categorias',
                    'url'  => 'financeiro/categorias',
                    'icon' => 'fas fa-tags',
                    'can'  => 'cat',
                ],
            ],
        ],
        [
            'text'        => 'Frota',
            'icon'        => 'fas fa-car-side',
            'submenu'     => [
                [
                    'text' => 'Veículos',
                    'url'  => 'frota/veiculos',
                    'icon' => 'fas fa-car',
                    'can'  => 'veiculos',
                ],
                [
                    'text' => 'Abastecimentos',
                    'url'  => 'frota/abastecimentos',
                    'icon' => 'fas fa-gas-pump',
                    'can'  => 'abastecimento',
                ],
                [
                    'text' => 'Manutenções',
                    'url'  => 'frota/manutencoes',
                    'icon' => 'fas fa-tools',
                    'can'  => 'manutencao',
                ],
                [
                    'text' => 'Viagens',
                    'url'  => 'frota/viagens',
                    'icon' => 'fas fa-route',
                    'can'  => 'viagens',
                ],
                [
                    'text' => 'Ocorrências',
                    'url'  => 'frota/ocorrencias',
                    'icon' => 'fas fa-exclamation-triangle',
                    'can'  => 'ocorrencia',
                ],
                [
                    'text' => 'Gestor de Ocorrências',
                    'url'  => 'frota/ocorrencias/gestor',
                    'icon' => 'fas fa-tasks',
                    'can'  => 'Gestão de Ocorrencia',
                ],
                [
                    // Relatórios foram movidos para o menu principal "Relatórios"
                ],
            ],
        ],
        [
            'text'        => 'Relatório Estoque',
            'icon'        => 'fas fa-boxes',
            'submenu'     => [
                [
                    'text' => 'Relatório Estoque',
                    'url'  => 'relatorios/estoque',
                    'icon' => 'fas fa-boxes',
                    'can'  => 'relatorio-estoque',
                ],
                [
                    'text' => 'Rel. Máximo e Mínimo',
                    'url'  => 'relatorios/estoque-min-max',
                    'icon' => 'fas fa-level-down-alt',
                    'can'  => 'rel_maxmin',
                ],
                [
                    'text' => 'Relatório C.C.(estoque)',
                    'url'  => 'relatorios/centro-custo',
                    'icon' => 'fas fa-building',
                    'can'  => 'relatorio-centro-custo',
                ],
                [
                    'text' => 'Relatório por Funcionário(estoque)',
                    'url'  => 'relatorios/funcionario',
                    'icon' => 'fas fa-user-tie',
                    'can'  => 'relatorio-funcionario',
                ],
                [
                    'text' => 'Relatório por Produto (Estoque)',
                    'url'  => 'relatorios/produto-estoque',
                    'icon' => 'fas fa-box-open',
                    'can'  => 'rel_por_prod',
                ],
            ],
        ],
        [
            'text'        => 'Relatório Frota',
            'icon'        => 'fas fa-car-side',
            'submenu'     => [
                [
                    'text' => 'Relatório de Abastecimento (Frota)',
                    'url'  => 'frota/relatorios/abastecimento',
                    'icon' => 'fas fa-gas-pump',
                    'can'  => 'rel_abast',
                ],
                [
                    'text' => 'Relatório Consumo (Frota)',
                    'url'  => 'frota/relatorios/consumo',
                    'icon' => 'fas fa-chart-line',
                    'can'  => 'rel_consm',
                ],
                [
                    'text' => 'Relatório Custo Total (Frota)',
                    'url'  => 'frota/relatorios/custo',
                    'icon' => 'fas fa-dollar-sign',
                    'can'  => 'rel_cust',
                ],
                [
                    'text' => 'Relatório Manutenções (Frota)',
                    'url'  => 'frota/relatorios/manutencoes',
                    'icon' => 'fas fa-tools',
                    'can'  => 'Rel_manu',
                ],
                [
                    'text' => 'Relatório de Ocorrências',
                    'url'  => 'frota/relatorios/ocorrencias',
                    'icon' => 'fas fa-exclamation-triangle',
                    'can'  => 'rel_ocorr',
                ],
                [
                    'text' => 'Relatório KM Percorrido (Frota)',
                    'url'  => 'frota/relatorios/km-percorrido',
                    'icon' => 'fas fa-road',
                    'can'  => 'rel_km',
                ],
                [
                    'text' => 'Conferência de NF (Frota)',
                    'url'  => 'frota/relatorios/conferencia-nf',
                    'icon' => 'fas fa-file-search',
                    'can'  => 'Rel_conf_nf',
                ],
            ],
        ],
        [
            'text'        => 'Relatório Financeiro',
            'icon'        => 'fas fa-chart-pie',
            'submenu'     => [
                [
                    'text' => 'Contas a Pagar',
                    'url'  => 'relatorios/contas-pagar',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can'  => 'Rel_cp',
                ],
                [
                    'text' => 'Contas a Receber',
                    'url'  => 'relatorios/contas-receber',
                    'icon' => 'fas fa-hand-holding-usd',
                    'can'  => 'rel_crec',
                ],
                [
                    'text' => 'Relatório de O.S.',
                    'url'  => 'relatorios/ordem-servico',
                    'icon' => 'fas fa-clipboard-list',
                    'can'  => 'rel_os',
                ],
            ],
        ],
        [
            'text' => 'Relatório de Suprimentos',
            'icon' => 'fas fa-boxes',
            'url'  => '#',
            'can_any' => ['Rel_cot', 'rel_sol'],
            'submenu' => [
                [
                    'text' => 'Solicitações',
                    'url'  => 'relatorios/solicitacoes',
                    'icon' => 'fas fa-clipboard-list',
                    'can'  => 'rel_sol',
                ],
                [
                    'text' => 'Cotações',
                    'url'  => 'relatorios/cotacoes',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can'  => 'Rel_cot',
                ],
            ],
        ],
        [
            'text' => 'Gerenciamento',
            'icon' => 'fas fa-users-cog',
            'url'  => '#',
            // Só mostra se o usuário tiver pelo menos uma dessas permissões
            'can_any' => ['gerenciar-usuarios', 'gerenciar-permissoes'],
            'submenu' => [
                [
                    'text' => 'Gerenciar Usuários',
                    'url'  => 'usuarios',
                    'icon' => 'fas fa-user-cog',
                    'can'  => 'gerenciar-usuarios',
                ],
                [
                    'text' => 'Gerenciar Permissões',
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
                    'location' => '/css/theme-sigo.css?v=3.4',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '/js/theme-toggle.js?v=1.1',
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
