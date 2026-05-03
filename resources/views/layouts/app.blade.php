@extends('adminlte::page')

@section('title', config('app.name'))

@section('plugins.Sweetalert2', true)

@section('content_top_nav_right')
    @php $currentLocale = app()->getLocale(); @endphp
    <li class="nav-item d-flex align-items-center mx-1">
        @if($currentLocale === 'en')
            <a href="{{ route('lang.switch', 'pt_BR') }}"
               class="btn btn-sm btn-outline-light d-flex align-items-center gap-1"
               title="Mudar para Português"
               style="font-size:0.78rem; padding:3px 10px; border-radius:20px;">
                <span style="font-size:1rem;">🇧🇷</span>
                <span class="d-none d-sm-inline ml-1">PT</span>
            </a>
        @else
            <a href="{{ route('lang.switch', 'en') }}"
               class="btn btn-sm btn-outline-light d-flex align-items-center gap-1"
               title="Switch to English"
               style="font-size:0.78rem; padding:3px 10px; border-radius:20px;">
                <span style="font-size:1rem;">🇺🇸</span>
                <span class="d-none d-sm-inline ml-1">EN</span>
            </a>
        @endif
    </li>
@stop

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Meta tags de performance mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#007bff">
    
    <!-- Preconnect para recursos externos -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    
    <!-- Preload recursos críticos -->
    <link rel="preload" href="/vendor/adminlte/dist/css/adminlte.min.css" as="style">
    <link rel="preload" href="/css/responsive-overrides.css" as="style">
    <link rel="preload" href="/img/brs-logo.png" as="image" imagesrcset="/img/brs-logo.png" imagesizes="60px">
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <script>if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register('/sw.js'));}</script>
@stop

@section('content_header')
    <h1>@yield('page_title', 'Dashboard')</h1>
@stop

@section('content')
    @php
        $__licenseStatus = app(\App\Services\LicenseService::class)->status();
        $__isAdmin = auth()->check() && optional(auth()->user()->profile)->name === 'Admin';
        $__showLicenseBanner = $__isAdmin && in_array($__licenseStatus['reason'] ?? '', ['grace', 'expired']);
    @endphp

    @if($__showLicenseBanner)
        <div class="alert alert-warning" role="alert">
            {{ __('licença do Sistema SIGO expirada entre em contato com suporte') }}
        </div>
    @endif

    @yield('content')
@stop

@section('css')
    <link rel="stylesheet" href="{{ file_exists(public_path('css/responsive-overrides.css')) ? asset('css/responsive-overrides.css') : '/css/responsive-overrides.css' }}">
    <link rel="stylesheet" href="{{ asset('css/theme-sigo.css') }}">
    <link rel="stylesheet" href="/css/admin_custom.css?v={{ time() }}">
    
    @yield('css')
    @stack('styles')
@stop

@section('js')
    <script>
        // Configurar o token CSRF para todas as requisições AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    <!-- Fix: Logo sidebar minimizada -->
    <script src="{{ asset('js/sidebar-logo-fix.js') }}"></script>
    
    <!-- Lazy Load de Imagens -->
    <script src="{{ asset('js/lazy-load.js') }}" defer></script>
    
    @stack('scripts')
    @yield('js')
@stop
