@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Language Switcher --}}
        @php $currentLocale = app()->getLocale(); @endphp
        <li class="nav-item d-flex align-items-center" style="margin-right: 6px;">
            @if($currentLocale === 'en')
                <a href="{{ route('lang.switch', 'pt_BR') }}"
                   title="Mudar para Português"
                   style="display:inline-flex; align-items:center; gap:5px; font-size:0.76rem; font-weight:700;
                          padding:4px 12px; border-radius:20px; border:1px solid rgba(0,0,0,.18);
                          background:rgba(255,255,255,.15); color:inherit; text-decoration:none; white-space:nowrap;">
                    <svg width="16" height="12" viewBox="0 0 20 14" style="border-radius:2px;flex-shrink:0">
                        <rect width="20" height="14" fill="#009c3b"/>
                        <polygon points="10,1 19,7 10,13 1,7" fill="#fedf00"/>
                        <circle cx="10" cy="7" r="3.5" fill="#002776"/>
                    </svg>
                    PT
                </a>
            @else
                <a href="{{ route('lang.switch', 'en') }}"
                   title="Switch to English"
                   style="display:inline-flex; align-items:center; gap:5px; font-size:0.76rem; font-weight:700;
                          padding:4px 12px; border-radius:20px; border:1px solid rgba(0,0,0,.18);
                          background:rgba(255,255,255,.15); color:inherit; text-decoration:none; white-space:nowrap;">
                    <svg width="16" height="12" viewBox="0 0 60 40" style="border-radius:2px;flex-shrink:0">
                        <rect width="60" height="40" fill="#B22234"/>
                        <rect y="6" width="60" height="5" fill="#fff"/>
                        <rect y="16" width="60" height="5" fill="#fff"/>
                        <rect y="26" width="60" height="5" fill="#fff"/>
                        <rect y="36" width="60" height="4" fill="#fff"/>
                        <rect width="25" height="22" fill="#3C3B6E"/>
                    </svg>
                    EN
                </a>
            @endif
        </li>

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
