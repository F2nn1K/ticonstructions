@extends('adminlte::auth.auth-page')

@section('auth_header', __('adminlte::adminlte.login_message'))

@section('auth_body')
    <form action="{{ route('login') }}" method="post">
        @csrf
        <div class="input-group mb-3">
            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                   value="{{ old('username') }}" placeholder="{{ __('Nome de Usuário') }}" autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user"></span>
                </div>
            </div>
            @error('username')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="{{ __('Senha') }}">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="row">
            <div class="col-7">
                <div class="icheck-primary">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">{{ __('Lembrar-me') }}</label>
                </div>
            </div>
            <div class="col-5">
                <button type="submit" class="btn btn-primary btn-block">{{ __('Entrar') }}</button>
            </div>
        </div>
    </form>

    <div class="text-center mt-3">
        @php $currentLocale = app()->getLocale(); @endphp
        @if($currentLocale === 'en')
            <a href="{{ route('lang.switch', 'pt_BR') }}" class="btn btn-sm btn-outline-secondary">
                <svg width="16" height="11" viewBox="0 0 20 14" style="border-radius:2px; margin-right:4px; vertical-align:middle">
                    <rect width="20" height="14" fill="#009c3b"/>
                    <polygon points="10,1 19,7 10,13 1,7" fill="#fedf00"/>
                    <circle cx="10" cy="7" r="3.5" fill="#002776"/>
                </svg>
                Português
            </a>
        @else
            <a href="{{ route('lang.switch', 'en') }}" class="btn btn-sm btn-outline-secondary">
                <svg width="16" height="11" viewBox="0 0 60 40" style="border-radius:2px; margin-right:4px; vertical-align:middle">
                    <rect width="60" height="40" fill="#B22234"/>
                    <rect y="6" width="60" height="5" fill="#fff"/>
                    <rect y="16" width="60" height="5" fill="#fff"/>
                    <rect y="26" width="60" height="5" fill="#fff"/>
                    <rect y="36" width="60" height="4" fill="#fff"/>
                    <rect width="25" height="22" fill="#3C3B6E"/>
                </svg>
                English
            </a>
        @endif
    </div>
@stop
