@extends('adminlte::page')

@section('title', __('app.menu.reports'))

@section('content_header')
<h1>Relatórios</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="reports-card">
                <div class="reports-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="reports-content">
                    <h2 class="reports-title">Sistema de Relatórios</h2>
                    <p class="reports-message">
                        Esta seção está preparada para receber os relatórios do sistema.
                        <br>
                        Os relatórios específicos serão implementados conforme a necessidade.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
    .reports-icon {
        background: #17a2b8;
    }
</style>
@stop
@endsection 