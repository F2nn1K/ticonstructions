@extends('adminlte::page')

@section('title','Ordem de Serviço')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-bolt text-warning mr-2"></i>
            Ações Rápidas - O.S.
        </h1>
        <p class="text-muted mt-1 mb-0">Escolha entre criar nova ou visualizar as O.S.</p>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid os-page">
    <div class="modern-card">
        <div class="card-body-modern">
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-3">
                    <a href="{{ route('documentos-dp.ordem-servico.nova') }}" class="btn btn-block btn-primary" style="padding:18px; border-radius:12px;">
                        <i class="fas fa-plus mr-2"></i> Nova O.S.
                        <div class="small text-white-50">Criar nova ordem de serviço</div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <a href="{{ route('documentos-dp.ordem-servico.lista') }}" class="btn btn-block btn-outline-secondary" style="padding:18px; border-radius:12px;">
                        <i class="fas fa-search mr-2"></i> Visualizar O.S.
                        <div class="small text-muted">Ver ordens do dia</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
.os-page * { transition: none !important; }
.os-page a:hover { text-decoration: none !important; color: inherit !important; }
.os-page .btn:hover, .os-page .btn:focus, .os-page .btn:active { box-shadow: none !important; filter: none !important; transform: none !important; }
.os-page .modern-card:hover, .os-page .card:hover { box-shadow: none !important; transform: none !important; }
</style>
@stop


