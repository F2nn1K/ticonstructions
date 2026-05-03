@extends('adminlte::page')

@section('title', 'Cotação')

@section('css')
<style>
    /* ============== RESPONSIVIDADE DO MODAL DE COTAÇÃO ============== */
    /* Modal responsivo - altura máxima e scroll */
    #modalVerCotacao .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }
    
    #modalVerCotacao .modal-body {
        overflow-y: auto;
        flex: 1 1 auto;
    }
    
    #modalVerCotacao .modal-footer {
        flex-shrink: 0;
        flex-wrap: wrap;
        gap: 5px;
    }
    
    /* Tablets e dispositivos menores */
    @media (max-width: 991px) {
        #modalVerCotacao .modal-dialog {
            max-width: 98% !important;
            margin: 1.75rem auto;
        }
        
        #modalVerCotacao .modal-content {
            max-height: 85vh;
        }
        
        #modalVerCotacao .modal-body {
            padding: 10px;
        }
        
        #modalVerCotacao .row > [class*="col-"] {
            margin-bottom: 10px;
        }
        
        #modalVerCotacao .table-responsive {
            font-size: 12px;
        }
        
        #modalVerCotacao .modal-footer .btn {
            font-size: 12px;
            padding: 6px 10px;
        }
        
        /* Cards de fornecedor responsivos */
        #modalVerCotacao .fornecedor-card .card-header .row {
            flex-direction: column;
        }
        
        #modalVerCotacao .fornecedor-card .card-header .row > div {
            width: 100%;
            margin-bottom: 8px;
        }
        
        #modalVerCotacao .fornecedor-card .card-body .row > div {
            width: 100%;
            margin-bottom: 8px;
        }
    }
    
    /* Celulares */
    @media (max-width: 576px) {
        #modalVerCotacao .modal-dialog {
            margin: 10px;
            max-width: calc(100% - 20px) !important;
        }
        
        #modalVerCotacao .modal-content {
            max-height: calc(100vh - 20px);
        }
        
        #modalVerCotacao .modal-header {
            padding: 10px 15px;
        }
        
        #modalVerCotacao .modal-header h5 {
            font-size: 14px;
        }
        
        #modalVerCotacao .modal-body {
            padding: 10px;
        }
        
        #modalVerCotacao .modal-body h4 {
            font-size: 16px;
        }
        
        #modalVerCotacao .modal-body h6 {
            font-size: 13px;
        }
        
        #modalVerCotacao .modal-body label {
            font-size: 11px;
        }
        
        #modalVerCotacao .modal-body p {
            font-size: 13px;
        }
        
        #modalVerCotacao .table {
            font-size: 11px;
        }
        
        #modalVerCotacao .table th,
        #modalVerCotacao .table td {
            padding: 5px 8px;
        }
        
        #modalVerCotacao .modal-footer {
            padding: 8px;
            justify-content: center;
        }
        
        #modalVerCotacao .modal-footer .btn {
            font-size: 11px;
            padding: 5px 8px;
            flex: 1 1 auto;
            min-width: 0;
        }
        
        #modalVerCotacao .modal-footer .btn i {
            display: none;
        }
        
        /* Input fields menores */
        #modalVerCotacao .form-control-sm {
            font-size: 12px;
            padding: 4px 8px;
        }
        
        /* Cards de fornecedor em mobile */
        #modalVerCotacao .fornecedor-card {
            margin-bottom: 10px;
        }
        
        #modalVerCotacao .fornecedor-card .card-header {
            padding: 8px;
        }
        
        #modalVerCotacao .fornecedor-card .card-body {
            padding: 8px;
        }
        
        /* Badges menores */
        #modalVerCotacao .badge {
            font-size: 10px;
            padding: 3px 6px;
        }
    }
    
    /* ============== FIM RESPONSIVIDADE ============== */

    /* Forçar tamanho adequado dos campos na tabela de itens */
    #tabelaItens td input[type="number"],
    #tabelaItens td select {
        width: 100% !important;
        min-width: 60px !important;
        padding: 4px 8px !important;
    }
    #tabelaItens th {
        white-space: nowrap;
    }
    /* Campos de fornecedores */
    #modalCotacao .card-success input[type="number"],
    #modalCotacao .card-success input[type="text"] {
        width: 100% !important;
        min-width: 70px !important;
    }
    /* Autocomplete de fornecedores */
    .fornecedor-autocomplete-wrapper {
        position: relative;
    }
    .fornecedor-autocomplete-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 9999;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        display: none;
    }
    
    /* Autocomplete de produtos do estoque */
    .produto-autocomplete-wrapper {
        position: relative;
        width: 100%;
    }
    .produto-autocomplete-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 9999;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        display: none;
        min-width: 300px;
    }
    .produto-autocomplete-list .item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        white-space: normal;
        word-wrap: break-word;
    }
    .produto-autocomplete-list .item:hover {
        background: #007bff;
        color: #fff;
    }
    .produto-autocomplete-list .item .produto-nome {
        font-weight: bold;
        font-size: 13px;
        display: block;
        margin-bottom: 3px;
    }
    .produto-autocomplete-list .item .produto-info {
        font-size: 11px;
        opacity: 0.8;
        display: block;
    }
    .produto-autocomplete-list .item .produto-estoque {
        font-size: 11px;
        font-weight: bold;
        display: block;
        margin-top: 2px;
    }
    .fornecedor-autocomplete-list .item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .fornecedor-autocomplete-list .item:hover,
    .fornecedor-autocomplete-list .item.active {
        background-color: #007bff;
        color: #fff;
    }
    .fornecedor-autocomplete-list .item:last-child {
        border-bottom: none;
    }
    .fornecedor-autocomplete-list .item-cadastrar {
        background-color: #e8f5e9;
        color: #2e7d32;
        font-weight: 500;
    }
    .fornecedor-autocomplete-list .item-cadastrar:hover {
        background-color: #28a745;
        color: #fff;
    }
    .fornecedor-autocomplete-list .item small {
        opacity: 0.7;
    }
    .fornecedor-selecionado {
        background-color: #e3f2fd !important;
        border-color: #2196f3 !important;
    }
    /* Botão de upload estilizado */
    .btn-upload {
        position: relative;
        overflow: hidden;
        display: inline-block;
        cursor: pointer;
    }
    .btn-upload input[type="file"] {
        position: absolute;
        top: 0;
        right: 0;
        min-width: 100%;
        min-height: 100%;
        font-size: 100px;
        text-align: right;
        filter: alpha(opacity=0);
        opacity: 0;
        outline: none;
        cursor: inherit;
        display: block;
    }
    .btn-upload .file-name {
        display: inline-block;
        max-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }
    .btn-upload.has-file {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
    }
    
    /* Autocomplete de Centro de Custo - Estilo compacto */
    #listaCentroCusto .cc-item {
        padding: 8px 10px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        font-size: 11px;
    }
    #listaCentroCusto .cc-item:last-child {
        border-bottom: none;
    }
    #listaCentroCusto .cc-item:hover {
        background: #007bff;
        color: #fff;
    }
    #listaCentroCusto .cc-item .cc-nome {
        font-weight: 500;
    }
</style>
@stop

@section('content_header')
<h1><i class="fas fa-file-invoice-dollar"></i> Cotação</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- CARDS DE ESTATÍSTICAS -->
    <div class="row mb-3">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['aguardando'] ?? 0 }}</h3>
                    <p>Aguardando Cotação</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('suprimentos.cotacao', ['status' => 'aberta']) }}" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['cotadas'] ?? 0 }}</h3>
                    <p>Cotadas (Prontas p/ OC)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('suprimentos.cotacao', ['status' => 'aberta']) }}" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['finalizadas'] ?? 0 }}</h3>
                    <p>Finalizadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-flag-checkered"></i>
                </div>
                <a href="{{ route('suprimentos.cotacao', ['status' => 'finalizada']) }}" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>Total de Cotações</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <a href="{{ route('suprimentos.cotacao') }}" class="small-box-footer">
                    Ver todas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- LISTA DE COTAÇÕES -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cotações Realizadas</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btnNovaCotacao">
                    <i class="fas fa-plus"></i> Nova Cotação
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros de Pesquisa - Layout Inline -->
            <form method="GET" action="{{ route('suprimentos.cotacao') }}" id="formFiltroCotacao">
                <div class="filtros-card mb-3">
                    <!-- Linha 1: Filtros principais -->
                    <div class="filtros-row">
                        <div class="filtro-col" style="min-width: 150px; flex: 1;">
                            <label>Nº Cotação</label>
                            <input type="text" class="form-control" name="busca_cotacao" 
                                   value="{{ request('busca_cotacao') }}" placeholder="COT-2026-...">
                        </div>
                        <div class="filtro-col" style="min-width: 150px; flex: 1;">
                            <label>{{ __('Status') }}</label>
                            <select class="form-control" name="status">
                                <option value="">{{ __('Todos') }}</option>
                                <option value="aberta" {{ request('status') == 'aberta' ? 'selected' : '' }}>Aguardando</option>
                                <option value="em_cotacao" {{ request('status') == 'em_cotacao' ? 'selected' : '' }}>Em Cotação</option>
                                <option value="parcial" {{ request('status') == 'parcial' ? 'selected' : '' }}>Parcial</option>
                                <option value="finalizada" {{ request('status') == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                                <option value="rejeitada" {{ request('status') == 'rejeitada' ? 'selected' : '' }}>Rejeitada</option>
                            </select>
                        </div>
                        <div class="filtro-col" style="min-width: 130px; flex: 1;">
                            <label>Urgência</label>
                            <select class="form-control" name="urgencia">
                                <option value="">{{ __('Todas') }}</option>
                                <option value="alta" {{ request('urgencia') == 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="media" {{ request('urgencia') == 'media' ? 'selected' : '' }}>Média</option>
                                <option value="normal" {{ request('urgencia') == 'normal' ? 'selected' : '' }}>Normal</option>
                            </select>
                        </div>
                        <div class="filtro-col" style="min-width: 180px; flex: 1.5;">
                            <label>Solicitante</label>
                            <select class="form-control" name="solicitante_id">
                                <option value="">{{ __('Todos') }}</option>
                                @foreach($solicitantes ?? [] as $sol)
                                    <option value="{{ $sol->id }}" {{ request('solicitante_id') == $sol->id ? 'selected' : '' }}>
                                        {{ Str::limit($sol->name, 25) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filtro-col" style="min-width: 300px; flex: 2;">
                            <label>Período</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="date" class="form-control" name="data_inicial" 
                                       value="{{ request('data_inicial') }}">
                                <span class="text-muted px-1">a</span>
                                <input type="date" class="form-control" name="data_final" 
                                       value="{{ request('data_final') }}">
                            </div>
                        </div>
                        <div class="filtro-col filtro-acoes" style="min-width: 140px;">
                            <label>&nbsp;</label>
                            <div class="d-flex gap-2">
                                <a href="{{ route('suprimentos.cotacao') }}" class="btn btn-outline-secondary" title="Limpar">
                                    <i class="fas fa-eraser"></i>
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Linha 2: Centro de Custo (separado para não quebrar layout) -->
                    <div class="filtros-row filtros-row-cc">
                        <div class="filtro-col-cc">
                            <label><i class="fas fa-building text-primary"></i> Centro de Custo 
                                <span class="badge badge-light ml-1">{{ request('centro_custo_ids') ? count(explode(',', request('centro_custo_ids'))) : 0 }}/7</span>
                            </label>
                            <div class="cc-input-wrapper">
                                <input type="text" class="form-control form-control-sm" id="filtro_centro_custo" 
                                       placeholder="Digite para buscar e adicionar obras..." autocomplete="off">
                                <input type="hidden" name="centro_custo_ids" id="filtro_centro_custo_ids" value="{{ request('centro_custo_ids') }}">
                                <div class="cc-dropdown" id="listaCentroCusto"></div>
                            </div>
                            <div id="centroCustoSelecionados" class="cc-tags"></div>
                        </div>
                    </div>
                </div>
            </form>
            
            <style>
                .filtros-card {
                    background: #fff;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                }
                .filtros-row {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 15px;
                    align-items: flex-end;
                }
                .filtros-row-cc {
                    margin-top: 15px;
                    padding-top: 15px;
                    border-top: 1px solid #f0f0f0;
                }
                .filtro-col {
                    display: flex;
                    flex-direction: column;
                }
                .filtro-col label {
                    font-size: 12px;
                    font-weight: 600;
                    color: #555;
                    margin-bottom: 6px;
                }
                .filtro-col .form-control {
                    font-size: 14px;
                    height: 38px;
                    border-radius: 6px;
                    border: 1px solid #ced4da;
                }
                .filtro-col .form-control:focus {
                    border-color: #007bff;
                    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
                }
                .filtro-acoes {
                    margin-left: auto;
                }
                .filtro-acoes .btn {
                    height: 38px;
                    padding: 0 16px;
                    font-size: 14px;
                }
                
                /* Centro de Custo - Layout horizontal */
                .filtro-col-cc {
                    width: 100%;
                }
                .filtro-col-cc label {
                    font-size: 12px;
                    font-weight: 600;
                    color: #555;
                    margin-bottom: 6px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .filtro-col-cc label .badge {
                    font-size: 11px;
                    background: #e9ecef;
                    color: #666;
                    padding: 3px 8px;
                }
                .cc-input-wrapper {
                    position: relative;
                    max-width: 500px;
                }
                .cc-input-wrapper .form-control {
                    font-size: 14px;
                    height: 38px;
                    border-radius: 6px;
                    border: 1px solid #ced4da;
                }
                .cc-input-wrapper .form-control:focus {
                    border-color: #007bff;
                    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
                }
                .cc-dropdown {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    width: 100%;
                    min-width: 300px;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 0 0 6px 6px;
                    max-height: 200px;
                    overflow-y: auto;
                    z-index: 1050;
                    display: none;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                }
                .cc-dropdown .item,
                .cc-dropdown .cc-item {
                    padding: 10px 12px;
                    cursor: pointer;
                    font-size: 12px;
                    border-bottom: 1px solid #f5f5f5;
                    transition: background 0.15s;
                }
                .cc-dropdown .item:hover,
                .cc-dropdown .cc-item:hover {
                    background: #007bff;
                    color: #fff;
                }
                .cc-dropdown .item:last-child,
                .cc-dropdown .cc-item:last-child {
                    border-bottom: none;
                }
                
                /* Tags dos centros de custo selecionados */
                .cc-tags {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                    margin-top: 8px;
                }
                .cc-tags:empty {
                    display: none;
                }
                .cc-tags .badge,
                .cc-tags .badge-cc-selecionado {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 6px 10px;
                    font-size: 12px;
                    font-weight: 500;
                    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                    color: #fff;
                    border-radius: 20px;
                    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
                }
                .cc-tags .badge .remove-cc-multi,
                .cc-tags .badge-cc-selecionado .remove-cc-multi {
                    cursor: pointer;
                    opacity: 0.8;
                    font-size: 14px;
                    margin-left: 2px;
                    transition: all 0.15s;
                }
                .cc-tags .badge .remove-cc-multi:hover,
                .cc-tags .badge-cc-selecionado .remove-cc-multi:hover {
                    opacity: 1;
                    transform: scale(1.2);
                }
                
                /* Responsivo */
                @media (max-width: 992px) {
                    .filtros-row {
                        gap: 10px;
                    }
                    .filtro-col {
                        flex: 1 1 45% !important;
                        min-width: 120px;
                    }
                    .filtro-acoes {
                        flex: 1 1 100% !important;
                        margin-left: 0;
                        margin-top: 8px;
                    }
                    .filtro-acoes > div {
                        justify-content: flex-end;
                        width: 100%;
                    }
                }
                
                /* Dropdown dentro da tabela - evitar quebra de layout */
                .table td .dropdown {
                    position: static;
                }
                .table td .dropdown-menu {
                    position: fixed !important;
                    z-index: 9999 !important;
                    max-height: 300px;
                    overflow-y: auto;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                    border: 1px solid #ddd;
                }
                .table td .dropdown-menu .dropdown-item-text {
                    white-space: normal;
                }
                .table td .dropdown-menu .dropdown-header {
                    background: #f8f9fa;
                    font-weight: 600;
                    padding: 8px 12px;
                }
            </style>
            
            <div class="table-responsive" style="overflow: visible;">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nº Cotação</th>
                            <th>Data</th>
                            <th>Centro de Custo</th>
                            <th>Município/UF</th>
                            <th>Fornecedor(es)</th>
                            <th>Valor Total</th>
                            <th>Orçamento</th>
                            <th>Status</th>
                            <th>Urgência</th>
                            <th>Autorizado</th>
                            <th>Pago</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cotacoes as $c)
                        <tr>
                            <td><strong>{{ $c->numero }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($c->data_solicitacao)->format('d/m/Y') }}</td>
                            <td>{{ $c->centro_custo_nome ?? '-' }}</td>
                            <td>{{ $c->municipio_uf ?? '-' }}</td>
                            <td>
                                @if(isset($c->multiplos_fornecedores) && $c->multiplos_fornecedores)
                                    <span class="badge badge-info">{{ $c->qtd_fornecedores }} fornecedores</span>
                                @else
                                    {{ $c->fornecedor_vencedor ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @if($c->valor_vencedor)
                                    <span class="text-success font-weight-bold">R$ {{ number_format($c->valor_vencedor, 2, ',', '.') }}</span>
                                    @if(isset($c->multiplos_fornecedores) && $c->multiplos_fornecedores)
                                        <br><small class="text-muted">(soma de todos)</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!empty($c->arquivos_orcamento) && count($c->arquivos_orcamento) > 0)
                                    @if(count($c->arquivos_orcamento) == 1)
                                        <a href="/storage/{{ $c->arquivos_orcamento[0] }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver Orçamento">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-danger dropdown-toggle" data-toggle="dropdown" title="{{ count($c->arquivos_orcamento) }} arquivos">
                                                <i class="fas fa-file-pdf"></i> {{ count($c->arquivos_orcamento) }}
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @foreach($c->arquivos_orcamento as $index => $arquivo)
                                                    <a class="dropdown-item" href="/storage/{{ $arquivo }}" target="_blank">
                                                        <i class="fas fa-file-pdf text-danger"></i> Orçamento {{ $index + 1 }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @switch($c->status)
                                    @case('aberta')
                                        <span class="badge badge-warning">Aguardando Cotação</span>
                                        @break
                                    @case('em_cotacao')
                                        <span class="badge badge-info">Em Cotação</span>
                                        @break
                                    @case('parcial')
                                        <span class="badge badge-primary">Cotação Parcial</span>
                                        @break
                                    @case('finalizada')
                                        <span class="badge badge-success">Finalizada</span>
                                        @break
                                    @case('rejeitada')
                                    @case('reprovada')
                                        <span class="badge badge-danger">Rejeitada</span>
                                        @break
                                    @case('cancelada')
                                        <span class="badge badge-dark">Cancelada</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $c->status }}</span>
                                @endswitch
                            </td>
                            <td class="text-center">
                                @switch($c->urgencia ?? 'normal')
                                    @case('alta')
                                        <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Alta</span>
                                        @break
                                    @case('media')
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> Média</span>
                                        @break
                                    @default
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Normal</span>
                                @endswitch
                            </td>
                            <td class="text-center">
                                @if($c->qtd_ocs > 0)
                                    @if($c->qtd_ocs == 1)
                                        {{-- Apenas 1 OC - mostrar status simples --}}
                                        @if($c->status_autorizacao == 'aprovado')
                                            <span class="badge badge-success" title="OC aprovada"><i class="fas fa-check"></i> Sim</span>
                                        @else
                                            <span class="badge badge-secondary" title="Aguardando aprovação"><i class="fas fa-hourglass-half"></i> Pendente</span>
                                        @endif
                                    @else
                                        {{-- Múltiplas OCs - mostrar detalhes com dropdown --}}
                                        <div class="dropdown d-inline-block">
                                            @if($c->status_autorizacao == 'aprovado')
                                                <span class="badge badge-success dropdown-toggle" data-toggle="dropdown" style="cursor: pointer;" title="Clique para ver detalhes">
                                                    <i class="fas fa-check"></i> {{ $c->qtd_ocs_aprovadas }}/{{ $c->qtd_ocs }}
                                                </span>
                                            @elseif($c->status_autorizacao == 'parcial')
                                                <span class="badge badge-warning dropdown-toggle" data-toggle="dropdown" style="cursor: pointer;" title="Clique para ver detalhes">
                                                    <i class="fas fa-clock"></i> {{ $c->qtd_ocs_aprovadas }}/{{ $c->qtd_ocs }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary dropdown-toggle" data-toggle="dropdown" style="cursor: pointer;" title="Clique para ver detalhes">
                                                    <i class="fas fa-hourglass-half"></i> 0/{{ $c->qtd_ocs }}
                                                </span>
                                            @endif
                                            <div class="dropdown-menu dropdown-menu-right" style="min-width: 280px; font-size: 12px;">
                                                <h6 class="dropdown-header"><i class="fas fa-file-invoice"></i> OCs desta Cotação</h6>
                                                @foreach($c->ocs_detalhes as $oc)
                                                    <div class="dropdown-item-text py-1 border-bottom">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="text-truncate" style="max-width: 180px;" title="{{ $oc->fornecedor }}">
                                                                <strong>{{ $oc->numero }}</strong><br>
                                                                <small class="text-muted">{{ Str::limit($oc->fornecedor, 25) }}</small>
                                                            </span>
                                                            @if(in_array($oc->status, ['aprovada', 'enviada', 'recebida', 'recebida_parcial']))
                                                                <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                                            @else
                                                                <span class="badge badge-secondary"><i class="fas fa-clock"></i></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($c->qtd_ocs > 0)
                                    @if($c->qtd_ocs == 1)
                                        {{-- Apenas 1 OC - mostrar status simples --}}
                                        @if($c->status_pagamento == 'pago')
                                            <span class="badge badge-success" title="OC paga"><i class="fas fa-check"></i> Sim</span>
                                        @else
                                            <span class="badge badge-secondary" title="Aguardando pagamento"><i class="fas fa-hourglass-half"></i> Pendente</span>
                                        @endif
                                    @else
                                        {{-- Múltiplas OCs - mostrar detalhes com dropdown --}}
                                        <div class="dropdown d-inline-block">
                                            @if($c->status_pagamento == 'pago')
                                                <span class="badge badge-success dropdown-toggle" data-toggle="dropdown" style="cursor: pointer;" title="Clique para ver detalhes">
                                                    <i class="fas fa-check"></i> {{ $c->qtd_ocs_pagas }}/{{ $c->qtd_ocs }}
                                                </span>
                                            @elseif($c->status_pagamento == 'parcial')
                                                <span class="badge badge-warning dropdown-toggle" data-toggle="dropdown" style="cursor: pointer;" title="Clique para ver detalhes">
                                                    <i class="fas fa-clock"></i> {{ $c->qtd_ocs_pagas }}/{{ $c->qtd_ocs }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary dropdown-toggle" data-toggle="dropdown" style="cursor: pointer;" title="Clique para ver detalhes">
                                                    <i class="fas fa-hourglass-half"></i> 0/{{ $c->qtd_ocs }}
                                                </span>
                                            @endif
                                            <div class="dropdown-menu dropdown-menu-right" style="min-width: 280px; font-size: 12px;">
                                                <h6 class="dropdown-header"><i class="fas fa-money-bill"></i> Pagamentos</h6>
                                                @foreach($c->ocs_detalhes as $oc)
                                                    <div class="dropdown-item-text py-1 border-bottom">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="text-truncate" style="max-width: 180px;" title="{{ $oc->fornecedor }}">
                                                                <strong>{{ $oc->numero }}</strong><br>
                                                                <small class="text-muted">{{ Str::limit($oc->fornecedor, 25) }} - R$ {{ number_format($oc->valor_total, 2, ',', '.') }}</small>
                                                            </span>
                                                            @if($oc->status_pagamento == 'pago')
                                                                <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                                            @else
                                                                <span class="badge badge-secondary"><i class="fas fa-clock"></i></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <button class="btn btn-sm btn-info btn-ver" data-id="{{ $c->id }}" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($canDelete ?? $isAdmin)
                                <button class="btn btn-sm btn-danger btn-excluir" data-id="{{ $c->id }}" data-numero="{{ $c->numero }}" title="Rejeitar Cotação">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> Nenhuma cotação registrada ainda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            @if($cotacoes instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Mostrando {{ $cotacoes->firstItem() ?? 0 }} a {{ $cotacoes->lastItem() ?? 0 }} de {{ $cotacoes->total() }} cotações
                </div>
                <div>
                    {{ $cotacoes->links('pagination::bootstrap-4') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Nova Cotação -->
<div class="modal fade" id="modalCotacao" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary py-2">
                <h5 class="modal-title text-white"><i class="fas fa-file-invoice-dollar"></i> Nova Cotação</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <form id="formCotacao">
                    <!-- DESCRIÇÃO -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-2">
                                <label class="mb-1"><i class="fas fa-tag text-primary"></i> Descrição *</label>
                                <input type="text" class="form-control form-control-sm" name="descricao" placeholder="Ex: Materiais de escritório..." required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="mb-1"><i class="fas fa-calendar text-primary"></i> Data Limite *</label>
                                <input type="date" class="form-control form-control-sm" name="data_limite" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- PRODUTOS -->
                    <div class="card card-outline card-info mb-2">
                        <div class="card-header py-1">
                            <h6 class="mb-0"><i class="fas fa-box"></i> Produtos 
                                <button type="button" class="btn btn-success btn-xs float-right" id="btnAddItem">
                                    <i class="fas fa-plus"></i> Adicionar
                                </button>
                            </h6>
                        </div>
                        <div class="card-body p-2" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0" id="tabelaItens">
                                <colgroup>
                                    <col>
                                    <col style="width: 80px;">
                                    <col style="width: 80px;">
                                    <col style="width: 40px;">
                                </colgroup>
                                <thead class="thead-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th class="text-center">Qtd</th>
                                        <th class="text-center">Unid</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="form-control form-control-sm input-produto" name="itens[0][produto]" placeholder="Nome do produto"></td>
                                        <td><input type="number" class="form-control form-control-sm text-center" name="itens[0][quantidade]" min="1" value="1" step="1"></td>
                                        <td>
                                            <select class="form-control form-control-sm item-unidade-cotacao" name="itens[0][unidade]">
                                                <option value="UN" selected>UN</option>
                                                <option value="PC">PC</option>
                                                <option value="PCT">PCT</option>
                                                <option value="CX">CX</option>
                                                <option value="KG">KG</option>
                                                <option value="MT">MT</option>
                                                <option value="M2">M2</option>
                                                <option value="M3">M3</option>
                                                <option value="LT">LT</option>
                                                <option value="FD">FD</option>
                                                <option value="RL">RL</option>
                                                <option value="SC">SC</option>
                                                <option value="BD">BD</option>
                                            </select>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- FORNECEDORES COM VALORES -->
                    <div class="card card-outline card-success mb-2">
                        <div class="card-header py-1 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-building"></i> Fornecedores e Valores</h6>
                            <button type="button" class="btn btn-success btn-sm" id="btnAdicionarFornecedor" title="Adicionar mais fornecedor">
                                <i class="fas fa-plus"></i> Adicionar Fornecedor
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <table class="table table-sm table-bordered mb-0" id="tabelaFornecedores">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fornecedor <small class="text-muted">(digite 3 letras)</small></th>
                                        <th style="width: 120px;">Valor (R$)</th>
                                        <th style="width: 110px;">Prazo (dias)</th>
                                        <th style="width: 180px;">Orçamento (PDF)</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyFornecedores">
                                    <tr data-index="0">
                                        <td>
                                            <div class="fornecedor-autocomplete-wrapper">
                                                <input type="text" class="form-control form-control-sm input-fornecedor" data-index="0" placeholder="Digite o nome do fornecedor..." autocomplete="off">
                                                <input type="hidden" name="fornecedores[0][id]" class="fornecedor-id">
                                                <div class="fornecedor-autocomplete-list"></div>
                                            </div>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm input-valor" name="fornecedores[0][valor]" placeholder="0,00"></td>
                                        <td><input type="text" class="form-control form-control-sm text-center" name="fornecedores[0][prazo]" placeholder="7" inputmode="numeric" pattern="[0-9]*"></td>
                                        <td><input type="file" class="form-control-file form-control-sm" name="fornecedores[0][orcamento]" accept=".pdf,.jpg,.jpeg,.png"></td>
                                        <td></td>
                                    </tr>
                                    <tr data-index="1">
                                        <td>
                                            <div class="fornecedor-autocomplete-wrapper">
                                                <input type="text" class="form-control form-control-sm input-fornecedor" data-index="1" placeholder="Digite o nome do fornecedor..." autocomplete="off">
                                                <input type="hidden" name="fornecedores[1][id]" class="fornecedor-id">
                                                <div class="fornecedor-autocomplete-list"></div>
                                            </div>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm input-valor" name="fornecedores[1][valor]" placeholder="0,00"></td>
                                        <td><input type="text" class="form-control form-control-sm text-center" name="fornecedores[1][prazo]" placeholder="7" inputmode="numeric" pattern="[0-9]*"></td>
                                        <td><input type="file" class="form-control-file form-control-sm" name="fornecedores[1][orcamento]" accept=".pdf,.jpg,.jpeg,.png"></td>
                                        <td></td>
                                    </tr>
                                    <tr data-index="2">
                                        <td>
                                            <div class="fornecedor-autocomplete-wrapper">
                                                <input type="text" class="form-control form-control-sm input-fornecedor" data-index="2" placeholder="Digite o nome do fornecedor..." autocomplete="off">
                                                <input type="hidden" name="fornecedores[2][id]" class="fornecedor-id">
                                                <div class="fornecedor-autocomplete-list"></div>
                                            </div>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm input-valor" name="fornecedores[2][valor]" placeholder="0,00"></td>
                                        <td><input type="text" class="form-control form-control-sm text-center" name="fornecedores[2][prazo]" placeholder="7" inputmode="numeric" pattern="[0-9]*"></td>
                                        <td><input type="file" class="form-control-file form-control-sm" name="fornecedores[2][orcamento]" accept=".pdf,.jpg,.jpeg,.png"></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Você pode adicionar até 10 fornecedores por cotação.</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSalvarCotacao">
                    <i class="fas fa-save"></i> Salvar Cotação
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalhes da Cotação -->
<div class="modal fade" id="modalVerCotacao" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="fas fa-eye"></i> Detalhes da Cotação</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small mb-0">Nº da Cotação</label>
                        <h4 id="ver-numero" class="mb-0 text-primary"></h4>
                    </div>
                    <div class="col-md-5">
                        <label class="text-muted small mb-0">Descrição</label>
                        <p id="ver-descricao" class="mb-0 font-weight-bold"></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small mb-0">Status</label>
                        <p id="ver-status" class="mb-0"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="text-muted small mb-0">Data da Solicitação</label>
                        <p id="ver-data" class="mb-0"></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small mb-0">Data Limite</label>
                        <p id="ver-data-limite" class="mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small mb-0"><i class="fas fa-user text-primary"></i> Solicitante</label>
                        <p id="ver-solicitante" class="mb-0 font-weight-bold"></p>
                    </div>
                </div>
                <div class="row mb-3" id="row-obra" style="display: none;">
                    <div class="col-12">
                        <label class="text-muted small mb-0"><i class="fas fa-building text-warning"></i> Obra (Centro de Custo)</label>
                        <p id="ver-obra" class="mb-0 font-weight-bold text-warning"></p>
                    </div>
                </div>
                <div class="row mb-3" id="row-descricao-servico" style="display: none;">
                    <div class="col-12">
                        <label class="text-muted small mb-0"><i class="fas fa-clipboard-list text-info"></i> Descrição do Serviço (O.S.)</label>
                        <div id="ver-descricao-servico" class="border rounded p-2 bg-light" style="white-space: pre-wrap; max-height: 150px; overflow-y: auto;"></div>
                    </div>
                </div>

                <hr>

                <h6><i class="fas fa-box text-primary"></i> Produtos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Produto</th>
                                <th width="100">Quantidade</th>
                                <th width="100">Unidade</th>
                            </tr>
                        </thead>
                        <tbody id="ver-itens">
                        </tbody>
                    </table>
                </div>

                <hr>

                <!-- Seção de fornecedores existentes (só aparece se tiver fornecedores) -->
                <div id="secao-fornecedores-existentes" style="display: none;">
                    <h6><i class="fas fa-building text-primary"></i> Fornecedores Cotados</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fornecedor</th>
                                    <th width="120">Valor</th>
                                    <th width="80">Prazo</th>
                                    <th width="100">Pagamento</th>
                                    <th id="th-itens-fornecedor" style="display:none;">Itens</th>
                                    <th width="150">Observação</th>
                                    <th width="100">Status</th>
                                    <th width="100">Orçamento</th>
                                </tr>
                            </thead>
                            <tbody id="ver-fornecedores">
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Seção para adicionar fornecedores -->
                <div id="secao-adicionar-fornecedores" style="display: none;">
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="fas fa-plus-circle text-success"></i> Adicionar Valores dos Fornecedores</h6>
                        <button type="button" class="btn btn-success btn-sm" id="btnAdicionarMaisFornecedor" title="Adicionar mais um fornecedor">
                            <i class="fas fa-plus"></i> Adicionar Fornecedor
                        </button>
                    </div>
                    <input type="hidden" id="cotacao-id-editar">
                    <form id="formAdicionarFornecedores" enctype="multipart/form-data">
                        <!-- Card Fornecedor 1 -->
                        <div class="card card-outline card-primary mb-2 fornecedor-card" id="fornecedor-card-1">
                            <div class="card-header py-2 px-3">
                                <div class="row align-items-center">
                                    <div class="col-lg-3 col-md-4 mb-1 mb-lg-0">
                                        <div class="fornecedor-autocomplete-wrapper">
                                            <input type="text" class="form-control form-control-sm input-fornecedor" data-index="add-0" placeholder="Digite o fornecedor..." autocomplete="off">
                                            <input type="hidden" id="forn1" name="forn1" class="fornecedor-id">
                                            <div class="fornecedor-autocomplete-list"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                                        <input type="text" class="form-control form-control-sm valor-fornecedor" id="valor1" name="valor1" placeholder="R$ 0,00">
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                                        <input type="text" class="form-control form-control-sm text-center" id="prazo1" name="prazo1" placeholder="Prazo (dias)" inputmode="numeric">
                                    </div>
                                    <div class="col-lg-2 col-md-2 mb-1 mb-lg-0">
                                        <select class="form-control form-control-sm" id="pagamento1" name="pagamento1">
                                            <option value="">Forma Pagamento</option>
                                            <option value="pix">PIX</option>
                                            <option value="boleto">Boleto</option>
                                            <option value="credito">Crédito</option>
                                            <option value="debito">Débito</option>
                                            <option value="dinheiro">Dinheiro</option>
                                            <option value="transferencia">Transferência</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                                        <label class="btn btn-sm btn-outline-secondary btn-upload mb-0 w-100" title="Anexar PDF do orçamento">
                                            <i class="fas fa-paperclip"></i> <span class="file-name">PDF</span>
                                            <input type="file" id="orcamento1" name="orcamento1" accept=".pdf,.jpg,.jpeg,.png">
                                        </label>
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0 parcelas-container" id="parcelas-container-1" style="display: none;">
                                        <input type="number" class="form-control form-control-sm text-center parcelas-input" id="parcelas1" name="parcelas1" placeholder="Vezes" min="1" max="24" title="Número de parcelas do boleto">
                                    </div>
                                    <div class="col-lg-3 col-md-12">
                                        <input type="text" class="form-control form-control-sm" id="obs1" name="obs1" placeholder="Observação: chave PIX, dados bancários, informações importantes...">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted"><i class="fas fa-check-square"></i> Marque os itens disponíveis neste fornecedor:</small>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-xs btn-outline-info mr-2 btn-editar-qtd" data-fornecedor="1" title="Editar quantidades dos itens">
                                            <i class="fas fa-edit"></i> Editar Qtd
                                        </button>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input marcar-todos" type="checkbox" id="marcarTodos1" data-fornecedor="1">
                                            <label class="form-check-label" for="marcarTodos1" style="font-size: 12px; cursor: pointer;">
                                                Marcar/Desmarcar Todos
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="itens-fornecedor" id="itens-fornecedor-1">
                                    <!-- Itens serão carregados via JS -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card Fornecedor 2 -->
                        <div class="card card-outline card-success mb-2 fornecedor-card" id="fornecedor-card-2">
                            <div class="card-header py-2 px-3">
                                <div class="row align-items-center">
                                    <div class="col-lg-3 col-md-4 mb-1 mb-lg-0">
                                        <div class="fornecedor-autocomplete-wrapper">
                                            <input type="text" class="form-control form-control-sm input-fornecedor" data-index="add-1" placeholder="Digite o fornecedor..." autocomplete="off">
                                            <input type="hidden" id="forn2" name="forn2" class="fornecedor-id">
                                            <div class="fornecedor-autocomplete-list"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                                        <input type="text" class="form-control form-control-sm valor-fornecedor" id="valor2" name="valor2" placeholder="R$ 0,00">
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                                        <input type="text" class="form-control form-control-sm text-center" id="prazo2" name="prazo2" placeholder="Prazo (dias)" inputmode="numeric">
                                    </div>
                                    <div class="col-lg-2 col-md-2 mb-1 mb-lg-0">
                                        <select class="form-control form-control-sm" id="pagamento2" name="pagamento2">
                                            <option value="">Forma Pagamento</option>
                                            <option value="pix">PIX</option>
                                            <option value="boleto">Boleto</option>
                                            <option value="credito">Crédito</option>
                                            <option value="debito">Débito</option>
                                            <option value="dinheiro">Dinheiro</option>
                                            <option value="transferencia">Transferência</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                                        <label class="btn btn-sm btn-outline-secondary btn-upload mb-0 w-100" title="Anexar PDF do orçamento">
                                            <i class="fas fa-paperclip"></i> <span class="file-name">PDF</span>
                                            <input type="file" id="orcamento2" name="orcamento2" accept=".pdf,.jpg,.jpeg,.png">
                                        </label>
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0 parcelas-container" id="parcelas-container-2" style="display: none;">
                                        <input type="number" class="form-control form-control-sm text-center parcelas-input" id="parcelas2" name="parcelas2" placeholder="Vezes" min="1" max="24" title="Número de parcelas do boleto">
                                    </div>
                                    <div class="col-lg-3 col-md-12">
                                        <input type="text" class="form-control form-control-sm" id="obs2" name="obs2" placeholder="Observação: chave PIX, dados bancários, informações importantes...">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted"><i class="fas fa-check-square"></i> Marque os itens disponíveis neste fornecedor:</small>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-xs btn-outline-info mr-2 btn-editar-qtd" data-fornecedor="2" title="Editar quantidades dos itens">
                                            <i class="fas fa-edit"></i> Editar Qtd
                                        </button>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input marcar-todos" type="checkbox" id="marcarTodos2" data-fornecedor="2">
                                            <label class="form-check-label" for="marcarTodos2" style="font-size: 12px; cursor: pointer;">
                                                Marcar/Desmarcar Todos
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="itens-fornecedor" id="itens-fornecedor-2">
                                    <!-- Itens serão carregados via JS -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card Fornecedor 3 -->
                        <div class="card card-outline card-warning mb-2 fornecedor-card" id="fornecedor-card-3">
                            <div class="card-header py-2 px-3">
                                <div class="row align-items-center">
                                    <div class="col-lg-3 col-md-4 mb-1 mb-lg-0">
                                        <div class="fornecedor-autocomplete-wrapper">
                                            <input type="text" class="form-control form-control-sm input-fornecedor" data-index="add-2" placeholder="Digite o fornecedor..." autocomplete="off">
                                            <input type="hidden" id="forn3" name="forn3" class="fornecedor-id">
                                            <div class="fornecedor-autocomplete-list"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                                        <input type="text" class="form-control form-control-sm valor-fornecedor" id="valor3" name="valor3" placeholder="R$ 0,00">
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                                        <input type="text" class="form-control form-control-sm text-center" id="prazo3" name="prazo3" placeholder="Prazo (dias)" inputmode="numeric">
                                    </div>
                                    <div class="col-lg-2 col-md-2 mb-1 mb-lg-0">
                                        <select class="form-control form-control-sm" id="pagamento3" name="pagamento3">
                                            <option value="">Forma Pagamento</option>
                                            <option value="pix">PIX</option>
                                            <option value="boleto">Boleto</option>
                                            <option value="credito">Crédito</option>
                                            <option value="debito">Débito</option>
                                            <option value="dinheiro">Dinheiro</option>
                                            <option value="transferencia">Transferência</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                                        <label class="btn btn-sm btn-outline-secondary btn-upload mb-0 w-100" title="Anexar PDF do orçamento">
                                            <i class="fas fa-paperclip"></i> <span class="file-name">PDF</span>
                                            <input type="file" id="orcamento3" name="orcamento3" accept=".pdf,.jpg,.jpeg,.png">
                                        </label>
                                    </div>
                                    <div class="col-lg-1 col-md-2 mb-1 mb-lg-0 parcelas-container" id="parcelas-container-3" style="display: none;">
                                        <input type="number" class="form-control form-control-sm text-center parcelas-input" id="parcelas3" name="parcelas3" placeholder="Vezes" min="1" max="24" title="Número de parcelas do boleto">
                                    </div>
                                    <div class="col-lg-3 col-md-12">
                                        <input type="text" class="form-control form-control-sm" id="obs3" name="obs3" placeholder="Observação: chave PIX, dados bancários, informações importantes...">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted"><i class="fas fa-check-square"></i> Marque os itens disponíveis neste fornecedor:</small>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-xs btn-outline-info mr-2 btn-editar-qtd" data-fornecedor="3" title="Editar quantidades dos itens">
                                            <i class="fas fa-edit"></i> Editar Qtd
                                        </button>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input marcar-todos" type="checkbox" id="marcarTodos3" data-fornecedor="3">
                                            <label class="form-check-label" for="marcarTodos3" style="font-size: 12px; cursor: pointer;">
                                                Marcar/Desmarcar Todos
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="itens-fornecedor" id="itens-fornecedor-3">
                                    <!-- Itens serão carregados via JS -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Container para fornecedores adicionais (dinâmicos) -->
                        <div id="fornecedores-adicionais-container"></div>
                        
                        <small class="text-muted d-block mt-2"><i class="fas fa-info-circle"></i> Você pode adicionar até 10 fornecedores por cotação. Clique no botão "Adicionar Fornecedor" acima para incluir mais.</small>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-success" id="btnCopiarWhatsApp" title="Copiar para enviar via WhatsApp">
                    <i class="fab fa-whatsapp"></i> Copiar para WhatsApp
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Fechar
                </button>
                <button type="button" class="btn btn-success" id="btnAdicionarFornecedores" style="display: none;">
                    <i class="fas fa-plus"></i> Adicionar Fornecedores
                </button>
                <button type="button" class="btn btn-primary" id="btnEnviarAutorizacaoExistentes" style="display: none;" title="Envia os fornecedores já cadastrados para gerar O.C.s">
                    <i class="fas fa-paper-plane"></i> Enviar para Autorização
                </button>
                <button type="button" class="btn btn-warning" id="btnEnviarParcial" style="display: none;" title="Envia só os fornecedores preenchidos e deixa o resto para depois">
                    <i class="fas fa-clock"></i> Enviar Parcial
                </button>
                <button type="button" class="btn btn-primary" id="btnEnviarSeparado" style="display: none;" title="Envia uma O.C. para cada fornecedor e gera para autorização">
                    <i class="fas fa-paper-plane"></i> Enviar para Autorização
                </button>
                {{-- BOTÃO DESATIVADO - Lógica de menor preço não é mais utilizada (02/02/2026)
                <button type="button" class="btn btn-primary" id="btnSalvarFornecedores" style="display: none;" title="Compara preços e seleciona o mais barato (mesmos produtos)">
                    <i class="fas fa-balance-scale"></i> Finalizar com Melhor Preço
                </button>
                --}}
            </div>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Novo Fornecedor -->
<div class="modal fade" id="modalNovoFornecedor" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success py-2">
                <h5 class="modal-title text-white"><i class="fas fa-building"></i> Novo Fornecedor</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formNovoFornecedor">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Razão Social *</label>
                                <input type="text" class="form-control" name="razao_social" id="novo_razao_social" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>CNPJ *</label>
                                <input type="text" class="form-control" name="cnpj" id="novo_cnpj" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nome Fantasia</label>
                                <input type="text" class="form-control" name="nome_fantasia" id="novo_nome_fantasia">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Telefone</label>
                                <input type="text" class="form-control" name="telefone" id="novo_telefone">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" id="novo_email">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Endereço</label>
                                <input type="text" class="form-control" name="endereco" id="novo_endereco">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cidade</label>
                                <input type="text" class="form-control" name="cidade" id="novo_cidade">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>UF</label>
                                <input type="text" class="form-control" name="uf" id="novo_uf" maxlength="2">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Observações') }}</label>
                        <textarea class="form-control" name="observacoes" id="novo_observacoes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnSalvarNovoFornecedor">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Quantidades -->
<div class="modal fade" id="modalEditarQtd" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit"></i> Editar Quantidades - Fornecedor <span id="qtdFornecedorNum"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Fracionamento de pedido:</strong> Ajuste a quantidade de cada item para este fornecedor. 
                    O restante pode ser comprado de outro fornecedor.
                </div>
                <input type="hidden" id="qtdFornecedorIndex">
                <table class="table table-sm table-bordered" id="tabelaEditarQtd">
                    <thead class="thead-light">
                        <tr>
                            <th>Produto</th>
                            <th width="120" class="text-center">Qtd Original</th>
                            <th width="120" class="text-center">Qtd p/ Fornecedor</th>
                            <th width="80" class="text-center">Unidade</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabelaEditarQtd">
                        <!-- Preenchido via JS -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn btn-info" id="btnSalvarQtd">
                    <i class="fas fa-save"></i> Salvar Quantidades
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    var itemIndex = 1;
    
    // Fix para dropdown dentro de tabela - posicionamento correto
    $('.table').on('show.bs.dropdown', '.dropdown', function() {
        var $dropdown = $(this);
        var $toggle = $dropdown.find('[data-toggle="dropdown"]');
        var $menu = $dropdown.find('.dropdown-menu');
        
        // Calcular posição
        var offset = $toggle.offset();
        var toggleHeight = $toggle.outerHeight();
        var menuWidth = 280;
        
        // Posicionar o menu
        $menu.css({
            'position': 'fixed',
            'top': offset.top + toggleHeight + 2,
            'left': offset.left - menuWidth + $toggle.outerWidth(),
            'right': 'auto'
        });
        
        // Verificar se está saindo da tela pela direita
        if (offset.left - menuWidth + $toggle.outerWidth() < 10) {
            $menu.css('left', offset.left);
        }
        
        // Verificar se está saindo da tela por baixo
        var windowHeight = $(window).height();
        var menuHeight = $menu.outerHeight() || 200;
        if (offset.top + toggleHeight + menuHeight > windowHeight) {
            $menu.css('top', offset.top - menuHeight - 2);
        }
    });
    
    // Verificar se deve abrir uma cotação específica (vindo da página de solicitações)
    var urlParams = new URLSearchParams(window.location.search);
    var abrirCotacaoId = urlParams.get('abrir_cotacao');
    if (abrirCotacaoId) {
        // Aguardar um momento para a página carregar e então abrir os detalhes
        setTimeout(function() {
            // Simular clique no botão "Ver" da cotação
            var btnVer = $('.btn-ver[data-id="' + abrirCotacaoId + '"]');
            if (btnVer.length > 0) {
                btnVer.click();
            } else {
                // Se não encontrou na lista, abrir diretamente via AJAX
                abrirDetalhesCotacao(abrirCotacaoId);
            }
            // Limpar o parâmetro da URL sem recarregar a página
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 500);
    }
    
    // Função para abrir detalhes da cotação diretamente
    function abrirDetalhesCotacao(id) {
        $.get('/api/suprimentos/cotacoes/' + id, function(data) {
            if (data && data.cotacao) {
                // Preencher modal de visualização
                $('#ver-numero').text(data.cotacao.numero);
                $('#ver-descricao').text(data.cotacao.descricao || '-');
                
                // Formatar data
                if (data.cotacao.data_solicitacao) {
                    var dataSol = new Date(data.cotacao.data_solicitacao);
                    $('#ver-data-sol').text(dataSol.toLocaleDateString('pt-BR'));
                }
                if (data.cotacao.data_limite) {
                    var dataLim = new Date(data.cotacao.data_limite);
                    $('#ver-data-limite').text(dataLim.toLocaleDateString('pt-BR'));
                }
                
                // Status
                var statusBadge = getStatusBadge(data.cotacao.status);
                $('#ver-status').html(statusBadge);
                
                // Solicitante
                $('#ver-solicitante').text(data.cotacao.solicitante_nome || '-');
                
                // Obra (Centro de Custo)
                if (data.cotacao.obra_nome) {
                    $('#ver-obra').text(data.cotacao.obra_nome);
                    $('#row-obra').show();
                } else {
                    $('#row-obra').hide();
                }
                
                // Descrição do Serviço (O.S.)
                if (data.cotacao.descricao_servico) {
                    $('#ver-descricao-servico').text(data.cotacao.descricao_servico);
                    $('#row-descricao-servico').show();
                } else {
                    $('#row-descricao-servico').hide();
                }
                
                // Itens
                var itensHtml = '';
                if (data.itens && data.itens.length > 0) {
                    data.itens.forEach(function(item) {
                        itensHtml += '<tr>';
                        itensHtml += '<td>' + item.produto + '</td>';
                        itensHtml += '<td class="text-center">' + item.quantidade + '</td>';
                        itensHtml += '<td class="text-center">' + item.unidade + '</td>';
                        itensHtml += '</tr>';
                    });
                } else {
                    itensHtml = '<tr><td colspan="3" class="text-center text-muted">Nenhum item</td></tr>';
                }
                $('#ver-itens-body').html(itensHtml);
                
                // Fornecedores
                var fornHtml = '';
                if (data.fornecedores && data.fornecedores.length > 0) {
                    data.fornecedores.forEach(function(f) {
                        fornHtml += '<tr>';
                        fornHtml += '<td>' + (f.razao_social || '-') + '</td>';
                        fornHtml += '<td class="text-right">' + (f.valor_total ? 'R$ ' + parseFloat(f.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2}) : '-') + '</td>';
                        fornHtml += '<td class="text-center">' + (f.prazo_entrega || '-') + '</td>';
                        fornHtml += '</tr>';
                    });
                } else {
                    fornHtml = '<tr><td colspan="3" class="text-center text-muted">Nenhum fornecedor cotado</td></tr>';
                }
                $('#ver-fornecedores-body').html(fornHtml);
                
                $('#modalVerCotacao').modal('show');
            }
        });
    }
    
    function getStatusBadge(status) {
        switch(status) {
            case 'aberta': return '<span class="badge badge-warning">Aguardando Cotação</span>';
            case 'em_cotacao': return '<span class="badge badge-info">Em Cotação</span>';
            case 'parcial': return '<span class="badge badge-primary">Cotação Parcial</span>';
            case 'finalizada': return '<span class="badge badge-success">Finalizada</span>';
            case 'rejeitada':
            case 'reprovada': return '<span class="badge badge-danger">Rejeitada</span>';
            case 'cancelada': return '<span class="badge badge-dark">Cancelada</span>';
            default: return '<span class="badge badge-secondary">' + status + '</span>';
        }
    }
    
    // Função para converter valor formatado BRL para número (para envio ao backend)
    function desformatarMoedaBRL(valor) {
        if (!valor) return '0.00';
        // Remove pontos de milhar e substitui vírgula por ponto
        return valor.replace(/\./g, '').replace(',', '.');
    }
    
    // Função para formatar número para exibição (apenas ao carregar valores do banco)
    function formatarParaExibicao(valor) {
        if (!valor) return '';
        var num = parseFloat(valor);
        if (isNaN(num)) return '';
        return num.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    // ========== FORMATAÇÃO DE MOEDA EM TEMPO REAL v2 ==========
    // Formata valor em Real brasileiro enquanto digita
    // Ex: 2563 -> 2.563 | 2563,26 -> 2.563,26
    
    function aplicarMascaraMoeda(input) {
        var valor = input.value;
        
        // Guarda posição do cursor
        var cursorPos = input.selectionStart;
        var tamanhoAntes = valor.length;
        
        // Verifica se tem vírgula
        var temVirgula = valor.indexOf(',') !== -1;
        
        // Separa parte inteira e decimal pela vírgula
        var partes = valor.split(',');
        
        // Limpa a parte inteira (remove tudo que não é número)
        var parteInteira = partes[0].replace(/\D/g, '');
        
        // Limpa a parte decimal (máximo 2 dígitos)
        var parteDecimal = partes[1] ? partes[1].replace(/\D/g, '').substring(0, 2) : '';
        
        // Se não tem nada, retorna vazio
        if (parteInteira === '' && !temVirgula) {
            input.value = '';
            return;
        }
        
        // Converte para número e formata com pontos de milhar
        var numero = parteInteira === '' ? 0 : parseInt(parteInteira);
        var formatado = numero.toLocaleString('pt-BR');
        
        // Adiciona a vírgula e centavos se o usuário digitou vírgula
        if (temVirgula) {
            formatado += ',' + parteDecimal;
        }
        
        // Atualiza o valor
        input.value = formatado;
        
        // Ajusta cursor
        var tamanhoDepois = formatado.length;
        var diff = tamanhoDepois - tamanhoAntes;
        var novaPosicao = cursorPos + diff;
        if (novaPosicao < 0) novaPosicao = 0;
        if (novaPosicao > tamanhoDepois) novaPosicao = tamanhoDepois;
        
        try {
            input.setSelectionRange(novaPosicao, novaPosicao);
        } catch(e) {}
    }
    
    // Evento de input para formatação em tempo real
    $(document).on('input', 'input[id^="valor"]', function() {
        aplicarMascaraMoeda(this);
    });
    
    // Ao sair do campo, completa com ,00 se necessário
    $(document).on('blur', 'input[id^="valor"]', function() {
        var valor = this.value;
        if (valor && valor.trim() !== '') {
            if (valor.indexOf(',') === -1) {
                this.value = valor + ',00';
            } else {
                var partes = valor.split(',');
                if (partes[1].length === 0) {
                    this.value = valor + '00';
                } else if (partes[1].length === 1) {
                    this.value = valor + '0';
                }
            }
        }
    });
    // ========== FIM FORMATAÇÃO DE MOEDA ==========
    
    // Função para formatar valores existentes nos campos (ao carregar do banco)
    function formatarValoresExistentes() {
        $('.input-valor, .valor-fornecedor, [id^="valor"]').each(function() {
            var valor = $(this).val();
            if (valor && valor.trim() !== '') {
                // Se o valor NÃO tem vírgula, assume que veio do banco
                if (valor.indexOf(',') === -1) {
                    var valorFormatado = formatarParaExibicao(valor);
                    if (valorFormatado) {
                        $(this).val(valorFormatado);
                    }
                }
            }
        });
    }
    
    // Formatar valores existentes ao carregar a página
    formatarValoresExistentes();
    
    // Formatar valores quando modais são abertos
    $('#modalCotacao, #modalVerCotacao').on('shown.bs.modal', function() {
        setTimeout(formatarValoresExistentes, 100);
    });
    
    // Marcar/Desmarcar todos os itens de um fornecedor
    $(document).on('change', '.marcar-todos', function() {
        var fornecedorNum = $(this).data('fornecedor');
        var isChecked = $(this).prop('checked');
        
        $('#itens-fornecedor-' + fornecedorNum + ' input[type="checkbox"]').prop('checked', isChecked);
    });
    
    // Atualizar estado do "marcar todos" quando um item individual for alterado
    $(document).on('change', '.itens-fornecedor input[type="checkbox"]', function() {
        var container = $(this).closest('.itens-fornecedor');
        var fornecedorNum = container.attr('id').replace('itens-fornecedor-', '');
        var totalCheckboxes = container.find('input[type="checkbox"]').length;
        var checkedCheckboxes = container.find('input[type="checkbox"]:checked').length;
        
        // Atualizar o checkbox "marcar todos"
        var marcarTodos = $('#marcarTodos' + fornecedorNum);
        if (checkedCheckboxes === 0) {
            marcarTodos.prop('checked', false);
            marcarTodos.prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            marcarTodos.prop('checked', true);
            marcarTodos.prop('indeterminate', false);
        } else {
            marcarTodos.prop('checked', false);
            marcarTodos.prop('indeterminate', true);
        }
    });
    
    // Atualizar botão de upload quando arquivo for selecionado
    $(document).on('change', '.btn-upload input[type="file"]', function() {
        var btn = $(this).closest('.btn-upload');
        var fileName = this.files[0] ? this.files[0].name : 'PDF';
        
        if (this.files[0]) {
            // Truncar nome se muito longo
            if (fileName.length > 10) {
                fileName = fileName.substring(0, 7) + '...';
            }
            btn.find('.file-name').text(fileName);
            btn.removeClass('btn-outline-secondary').addClass('btn-success has-file');
            btn.attr('title', this.files[0].name);
        } else {
            btn.find('.file-name').text('PDF');
            btn.removeClass('btn-success has-file').addClass('btn-outline-secondary');
            btn.attr('title', 'Anexar PDF do orçamento');
        }
    });
    
    // Abrir modal nova cotação
    $('#btnNovaCotacao').click(function() {
        $('#formCotacao')[0].reset();
        itemIndex = 1;
        $('#tabelaItens tbody').html(`
            <tr>
                <td><input type="text" class="form-control form-control-sm input-produto" name="itens[0][produto]" placeholder="Nome do produto"></td>
                <td><input type="number" class="form-control form-control-sm text-center" name="itens[0][quantidade]" min="1" value="1" step="1"></td>
                <td>
                    <select class="form-control form-control-sm item-unidade-cotacao" name="itens[0][unidade]">
                        <option value="UN" selected>UN</option>
                        <option value="PC">PC</option>
                        <option value="PCT">PCT</option>
                        <option value="CX">CX</option>
                        <option value="KG">KG</option>
                        <option value="MT">MT</option>
                        <option value="M2">M2</option>
                        <option value="M3">M3</option>
                        <option value="LT">LT</option>
                        <option value="FD">FD</option>
                        <option value="RL">RL</option>
                        <option value="SC">SC</option>
                        <option value="BD">BD</option>
                    </select>
                </td>
                <td></td>
            </tr>
        `);
        $('#modalCotacao').modal('show');
        setTimeout(function() {
            inicializarAutocompleteProdutos();
        }, 100);
    });
    
    // Adicionar item
    $('#btnAddItem').click(function() {
        var newRow = `<tr>
            <td><input type="text" class="form-control form-control-sm input-produto" name="itens[${itemIndex}][produto]" placeholder="Nome do produto"></td>
            <td><input type="number" class="form-control form-control-sm text-center" name="itens[${itemIndex}][quantidade]" min="1" value="1" step="1"></td>
            <td>
                <select class="form-control form-control-sm item-unidade-cotacao" name="itens[${itemIndex}][unidade]">
                    <option value="UN" selected>UN</option>
                    <option value="PC">PC</option>
                    <option value="PCT">PCT</option>
                    <option value="CX">CX</option>
                    <option value="KG">KG</option>
                    <option value="MT">MT</option>
                    <option value="M2">M2</option>
                    <option value="M3">M3</option>
                    <option value="LT">LT</option>
                    <option value="FD">FD</option>
                    <option value="RL">RL</option>
                    <option value="SC">SC</option>
                    <option value="BD">BD</option>
                </select>
            </td>
            <td><button type="button" class="btn btn-danger btn-xs btn-remove-item"><i class="fas fa-times"></i></button></td>
        </tr>`;
        $('#tabelaItens tbody').append(newRow);
        itemIndex++;
        inicializarAutocompleteProdutos();
    });
    
    // Remover item
    $(document).on('click', '.btn-remove-item', function() {
        $(this).closest('tr').remove();
    });
    
    // ==========================================
    // AUTOCOMPLETE DE FORNECEDORES
    // ==========================================
    var inputAtualFornecedor = null;
    var timeoutBusca = null;
    
    // Buscar fornecedores ao digitar
    $(document).on('input', '.input-fornecedor', function() {
        var input = $(this);
        var termo = input.val().trim();
        var wrapper = input.closest('.fornecedor-autocomplete-wrapper');
        var lista = wrapper.find('.fornecedor-autocomplete-list');
        
        // Limpar ID se o usuário está digitando
        wrapper.find('.fornecedor-id').val('');
        input.removeClass('fornecedor-selecionado');
        
        // Cancelar busca anterior
        if (timeoutBusca) clearTimeout(timeoutBusca);
        
        if (termo.length < 3) {
            lista.hide().empty();
            return;
        }
        
        // Mostrar "Buscando..." imediatamente
        lista.empty().append('<div class="item text-muted"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();
        
        // Debounce de 300ms
        timeoutBusca = setTimeout(function() {
            $.get('/api/suprimentos/fornecedores/buscar', { termo: termo })
                .done(function(fornecedores) {
                    lista.empty();
                    
                    if (fornecedores.length > 0) {
                        fornecedores.forEach(function(f) {
                            var texto = f.razao_social;
                            if (f.nome_fantasia) texto += ' <small>(' + f.nome_fantasia + ')</small>';
                            lista.append('<div class="item" data-id="' + f.id + '" data-nome="' + f.razao_social + '">' + texto + '</div>');
                        });
                    } else {
                        lista.append('<div class="item text-muted">Nenhum fornecedor encontrado</div>');
                    }
                    
                    // Sempre mostrar opção de cadastrar
                    lista.append('<div class="item item-cadastrar" data-action="cadastrar"><i class="fas fa-plus-circle"></i> Cadastrar novo fornecedor</div>');
                    
                    lista.show();
                })
                .fail(function() {
                    lista.empty().append('<div class="item item-cadastrar" data-action="cadastrar"><i class="fas fa-plus-circle"></i> Cadastrar novo fornecedor</div>').show();
                });
        }, 300);
    });
    
    // Selecionar fornecedor da lista
    $(document).on('click', '.fornecedor-autocomplete-list .item', function() {
        var item = $(this);
        var wrapper = item.closest('.fornecedor-autocomplete-wrapper');
        var input = wrapper.find('.input-fornecedor');
        var lista = wrapper.find('.fornecedor-autocomplete-list');
        
        if (item.data('action') === 'cadastrar') {
            // Abrir modal de cadastro
            inputAtualFornecedor = input;
            $('#formNovoFornecedor')[0].reset();
            // Preencher com o que foi digitado
            $('#novo_razao_social').val(input.val());
            $('#modalNovoFornecedor').modal('show');
            lista.hide();
            return;
        }
        
        var id = item.data('id');
        var nome = item.data('nome');
        
        if (id) {
            // Verificar duplicado
            var duplicado = false;
            $('.fornecedor-id').each(function() {
                if ($(this).val() == id && !$(this).is(wrapper.find('.fornecedor-id'))) {
                    duplicado = true;
                }
            });
            
            if (duplicado) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fornecedor Duplicado!',
                    text: 'Este fornecedor já foi selecionado.',
                    confirmButtonColor: '#ffc107'
                });
                lista.hide();
                return;
            }
            
            input.val(nome).addClass('fornecedor-selecionado');
            wrapper.find('.fornecedor-id').val(id);
        }
        
        lista.hide();
    });
    
    // Fechar lista ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.fornecedor-autocomplete-wrapper').length) {
            $('.fornecedor-autocomplete-list').hide();
        }
    });
    
    // Limpar ao abrir modal de cotação
    $('#modalCotacao').on('show.bs.modal', function() {
        $('.input-fornecedor').val('').removeClass('fornecedor-selecionado');
        $('.fornecedor-id').val('');
        $('.fornecedor-autocomplete-list').hide().empty();
    });
    
    // ==========================================
    // AUTOCOMPLETE DE PRODUTOS DO ESTOQUE
    // ==========================================
    function inicializarAutocompleteProdutos() {
        $('.input-produto').each(function() {
            var input = $(this);
            
            // Evitar duplicar o autocomplete
            if (input.data('autocomplete-init')) return;
            input.data('autocomplete-init', true);
            
            // Criar wrapper e lista se não existir
            if (!input.parent().hasClass('produto-autocomplete-wrapper')) {
                input.wrap('<div class="produto-autocomplete-wrapper"></div>');
                input.after('<div class="produto-autocomplete-list"></div>');
            }
            
            var wrapper = input.parent();
            var lista = wrapper.find('.produto-autocomplete-list');
            var timeoutBusca;
            
            input.on('input', function() {
                var termo = $(this).val().trim();
                clearTimeout(timeoutBusca);
                
                if (termo.length < 3) {
                    lista.hide();
                    return;
                }
                
                timeoutBusca = setTimeout(function() {
                    lista.empty().append('<div class="item text-center text-muted">Buscando...</div>').show();
                    
                    $.get('/api/suprimentos/estoque/buscar', { termo: termo })
                        .done(function(produtos) {
                            lista.empty();
                            if (produtos.length > 0) {
                                produtos.forEach(function(p) {
                                    var itemHtml = '<div class="item" data-nome="' + p.nome + '" data-unidade="' + (p.unidade || 'UN') + '">' +
                                        '<div class="produto-nome">' + p.nome + '</div>' +
                                        '<div class="produto-info">' + (p.descricao || '') + '</div>' +
                                        '<div class="produto-estoque">Estoque: ' + (p.quantidade || 0) + ' ' + (p.unidade || 'UN') + '</div>' +
                                        '</div>';
                                    lista.append(itemHtml);
                                });
                            } else {
                                lista.append('<div class="item text-muted">Nenhum produto encontrado no estoque</div>');
                            }
                            lista.show();
                        })
                        .fail(function() {
                            lista.empty().append('<div class="item text-danger">Erro ao buscar produtos.</div>').show();
                        });
                }, 300);
            });
            
            // Selecionar produto da lista
            lista.on('click', '.item[data-nome]', function() {
                var nome = $(this).data('nome');
                var unidade = $(this).data('unidade');
                
                input.val(nome);
                input.closest('tr').find('.item-unidade-cotacao').val(unidade);
                lista.hide();
            });
        });
    }
    
    // Fechar autocomplete de produtos ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.produto-autocomplete-wrapper').length) {
            $('.produto-autocomplete-list').hide();
        }
    });
    
    // ==========================================
    // CADASTRAR NOVO FORNECEDOR
    // ==========================================
    $('#btnSalvarNovoFornecedor').click(function() {
        var btn = $(this);
        var razaoSocial = $('#novo_razao_social').val().trim();
        var cnpj = $('#novo_cnpj').val().trim();
        
        if (!razaoSocial) {
            Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Informe a Razão Social!' });
            return;
        }
        if (!cnpj) {
            Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Informe o CNPJ!' });
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        
        $.ajax({
            url: '/api/suprimentos/fornecedores',
            method: 'POST',
            data: {
                razao_social: razaoSocial,
                cnpj: cnpj,
                nome_fantasia: $('#novo_nome_fantasia').val(),
                telefone: $('#novo_telefone').val(),
                email: $('#novo_email').val(),
                endereco: $('#novo_endereco').val(),
                cidade: $('#novo_cidade').val(),
                uf: $('#novo_uf').val(),
                observacoes: $('#novo_observacoes').val()
            },
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    // Preencher o campo que estava sendo editado
                    if (inputAtualFornecedor) {
                        inputAtualFornecedor.val(razaoSocial).addClass('fornecedor-selecionado');
                        inputAtualFornecedor.closest('.fornecedor-autocomplete-wrapper').find('.fornecedor-id').val(response.id);
                    }
                    
                    $('#modalNovoFornecedor').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Fornecedor Cadastrado!',
                        text: 'O fornecedor foi cadastrado e selecionado.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro', text: response.message });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao cadastrar fornecedor';
                Swal.fire({ icon: 'error', title: 'Erro', text: msg });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar');
            }
        });
    });
    
    // ==========================================
    // Validar fornecedores antes de salvar
    // ==========================================
    function validarFornecedoresSelecionados() {
        var fornecedoresSelecionados = [];
        $('.fornecedor-id').each(function() {
            var val = $(this).val();
            if (val && val !== '') {
                fornecedoresSelecionados.push(val);
            }
        });
        return fornecedoresSelecionados;
    }
    
    // Salvar cotação completa (com produtos e valores dos fornecedores)
    $('#btnSalvarCotacao').click(function() {
        var btn = $(this);
        
        // Validar descrição
        var descricao = $('input[name="descricao"]').val();
        if (!descricao || descricao.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Preencha a descrição da cotação!',
                confirmButtonColor: '#ffc107'
            });
            $('input[name="descricao"]').focus();
            return;
        }
        
        // Validar data limite
        var dataLimite = $('input[name="data_limite"]').val();
        if (!dataLimite || dataLimite.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Preencha a data limite!',
                confirmButtonColor: '#ffc107'
            });
            $('input[name="data_limite"]').focus();
            return;
        }
        
        // Validar se tem pelo menos 1 produto
        var temProduto = false;
        $('input[name^="itens"][name$="[produto]"]').each(function() {
            if ($(this).val().trim() !== '') {
                temProduto = true;
            }
        });
        
        if (!temProduto) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Adicione pelo menos um produto!',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        // Validar se selecionou pelo menos 1 fornecedor
        var fornecedoresSelecionados = validarFornecedoresSelecionados();
        
        if (fornecedoresSelecionados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Selecione pelo menos 1 fornecedor!',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        // Validar duplicados
        var uniqueFornecedores = [...new Set(fornecedoresSelecionados)];
        if (uniqueFornecedores.length !== fornecedoresSelecionados.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Fornecedor Duplicado!',
                text: 'Você não pode selecionar o mesmo fornecedor mais de uma vez.',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        
        // Usar FormData para enviar arquivos
        var formData = new FormData($('#formCotacao')[0]);
        
        // Desformatar valores antes de enviar
        $('.input-valor').each(function(index) {
            var valorFormatado = $(this).val();
            var valorDesformatado = desformatarMoedaBRL(valorFormatado);
            formData.set('fornecedores[' + index + '][valor]', valorDesformatado);
        });
        
        $.ajax({
            url: '/api/suprimentos/cotacoes',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cotação Salva!',
                        html: 'Cotação <strong>' + response.numero + '</strong> registrada com sucesso!',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: response.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao salvar cotação!';
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: msg,
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar Cotação');
            }
        });
    });
    
    // Ver detalhes da cotação
    $(document).on('click', '.btn-ver', function() {
        var id = $(this).data('id');
        $.get('/api/suprimentos/cotacoes/' + id, function(data) {
            // Armazenar dados para copiar WhatsApp
            dadosCotacaoAtual = data;
            
            $('#ver-numero').text(data.cotacao.numero);
            $('#ver-descricao').text(data.cotacao.descricao);
            
            var statusBadge = '';
            switch(data.cotacao.status) {
                case 'aberta': statusBadge = '<span class="badge badge-warning">Aguardando Cotação</span>'; break;
                case 'em_cotacao': statusBadge = '<span class="badge badge-info">Em Cotação</span>'; break;
                case 'parcial': statusBadge = '<span class="badge badge-primary">Cotação Parcial</span>'; break;
                case 'finalizada': statusBadge = '<span class="badge badge-success">Finalizada</span>'; break;
                case 'rejeitada': 
                case 'reprovada': statusBadge = '<span class="badge badge-danger">Rejeitada</span>'; break;
                case 'cancelada': statusBadge = '<span class="badge badge-dark">Cancelada</span>'; break;
                default: statusBadge = '<span class="badge badge-secondary">' + data.cotacao.status + '</span>';
            }
            $('#ver-status').html(statusBadge);
            
            if (data.cotacao.data_solicitacao) {
                var dataSol = new Date(data.cotacao.data_solicitacao);
                $('#ver-data').text(dataSol.toLocaleDateString('pt-BR'));
            }
            if (data.cotacao.data_limite) {
                var dataLim = new Date(data.cotacao.data_limite);
                $('#ver-data-limite').text(dataLim.toLocaleDateString('pt-BR'));
            }
            
            // Solicitante
            $('#ver-solicitante').text(data.cotacao.solicitante_nome || '-');
            
            // Obra (Centro de Custo)
            if (data.cotacao.obra_nome) {
                $('#ver-obra').text(data.cotacao.obra_nome);
                $('#row-obra').show();
            } else {
                $('#row-obra').hide();
            }
            
            // Descrição do Serviço (O.S.)
            if (data.cotacao.descricao_servico) {
                $('#ver-descricao-servico').text(data.cotacao.descricao_servico);
                $('#row-descricao-servico').show();
            } else {
                $('#row-descricao-servico').hide();
            }
            
            // Itens - Armazenar globalmente para uso nos checkboxes
            window.itensCotacaoAtual = data.itens || [];
            window.itensJaCotados = data.itens_ja_cotados || [];
            
            var itensHtml = '';
            if (data.itens && data.itens.length > 0) {
                data.itens.forEach(function(item) {
                    itensHtml += '<tr>';
                    itensHtml += '<td>' + item.produto + '</td>';
                    itensHtml += '<td class="text-center">' + item.quantidade + '</td>';
                    itensHtml += '<td class="text-center">' + (item.unidade || 'UN') + '</td>';
                    itensHtml += '</tr>';
                });
            } else {
                itensHtml = '<tr><td colspan="3" class="text-center text-muted">Nenhum item</td></tr>';
            }
            $('#ver-itens').html(itensHtml);
            
            // Fornecedores - Encontrar menor valor
            var menorValor = Infinity;
            if (data.fornecedores && data.fornecedores.length > 0) {
                data.fornecedores.forEach(function(f) {
                    if (f.valor_total && parseFloat(f.valor_total) < menorValor) {
                        menorValor = parseFloat(f.valor_total);
                    }
                });
            }
            
            // Verificar se há itens por fornecedor
            var hasItensPorFornecedor = data.has_itens_por_fornecedor && data.fornecedores && data.fornecedores.some(function(f) {
                return f.itens && f.itens.length > 0;
            });
            
            // Mostrar/ocultar coluna de itens
            if (hasItensPorFornecedor) {
                $('#th-itens-fornecedor').show();
            } else {
                $('#th-itens-fornecedor').hide();
            }
            
            var fornecedoresHtml = '';
            if (data.fornecedores && data.fornecedores.length > 0) {
                data.fornecedores.forEach(function(f) {
                    var isMenor = (f.valor_total && parseFloat(f.valor_total) === menorValor);
                    fornecedoresHtml += '<tr class="' + (isMenor ? 'table-success' : '') + '">';
                    fornecedoresHtml += '<td>' + (f.razao_social || 'Fornecedor') + '</td>';
                    if (f.valor_total) {
                        fornecedoresHtml += '<td class="text-right font-weight-bold">R$ ' + parseFloat(f.valor_total).toFixed(2).replace('.', ',') + '</td>';
                    } else {
                        fornecedoresHtml += '<td class="text-center text-muted">-</td>';
                    }
                    fornecedoresHtml += '<td class="text-center">' + (f.prazo_entrega ? f.prazo_entrega + ' dias' : '-') + '</td>';
                    
                    // Coluna de forma de pagamento
                    var pagamentoLabel = '-';
                    if (f.condicao_pagamento) {
                        var pagLabels = {
                            'pix': '<span class="badge badge-success">PIX</span>',
                            'boleto': '<span class="badge badge-warning">Boleto</span>',
                            'credito': '<span class="badge badge-info">Crédito</span>',
                            'debito': '<span class="badge badge-primary">Débito</span>',
                            'dinheiro': '<span class="badge badge-secondary">Dinheiro</span>',
                            'transferencia': '<span class="badge badge-dark">Transf.</span>'
                        };
                        pagamentoLabel = pagLabels[f.condicao_pagamento] || f.condicao_pagamento;
                    }
                    fornecedoresHtml += '<td class="text-center">' + pagamentoLabel + '</td>';
                    
                    // Coluna de itens (se houver)
                    if (hasItensPorFornecedor) {
                        if (f.itens && f.itens.length > 0) {
                            var itensTexto = f.itens.map(function(i) {
                                return '<span class="badge badge-light mr-1 mb-1" title="' + i.produto + '">' + i.quantidade + ' ' + (i.unidade || 'UN') + ' - ' + i.produto.substring(0, 20) + (i.produto.length > 20 ? '...' : '') + '</span>';
                            }).join('');
                            fornecedoresHtml += '<td class="small">' + itensTexto + '</td>';
                        } else {
                            fornecedoresHtml += '<td class="text-center"><span class="badge badge-info">Todos os itens</span></td>';
                        }
                    }
                    
                    // Coluna de observação
                    fornecedoresHtml += '<td class="small">' + (f.observacao || '-') + '</td>';
                    
                    if (isMenor) {
                        fornecedoresHtml += '<td><span class="badge badge-success"><i class="fas fa-trophy"></i> Cotado</span></td>';
                    } else if (f.valor_total) {
                        fornecedoresHtml += '<td><span class="badge badge-secondary">Cotado</span></td>';
                    } else {
                        fornecedoresHtml += '<td><span class="badge badge-light">Sem valor</span></td>';
                    }
                    // Coluna do orçamento
                    if (f.arquivo_orcamento) {
                        fornecedoresHtml += '<td class="text-center"><a href="/storage/' + f.arquivo_orcamento + '" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver Orçamento"><i class="fas fa-file-pdf"></i> Ver</a></td>';
                    } else {
                        fornecedoresHtml += '<td class="text-center text-muted">-</td>';
                    }
                    fornecedoresHtml += '</tr>';
                });
            } else {
                var colspan = hasItensPorFornecedor ? 8 : 7;
                fornecedoresHtml = '<tr><td colspan="' + colspan + '" class="text-center text-muted">Nenhum fornecedor</td></tr>';
            }
            $('#ver-fornecedores').html(fornecedoresHtml);
            
            // Armazenar o ID da cotação para edição
            $('#cotacao-id-editar').val(id);
            
            // Controlar exibição das seções
            // Esconder todos os botões primeiro
            $('#btnAdicionarFornecedores').hide();
            $('#btnEnviarAutorizacaoExistentes').hide();
            $('#btnSalvarFornecedores').hide();
            $('#btnEnviarSeparado').hide();
            $('#btnEnviarParcial').hide();
            $('#secao-adicionar-fornecedores').hide();
            
            if (data.fornecedores && data.fornecedores.length > 0) {
                // Tem fornecedores - mostrar tabela de fornecedores existentes
                $('#secao-fornecedores-existentes').show();
                
                // Verificar se tem fornecedores com valor (cotados)
                var fornecedoresComValor = data.fornecedores.filter(f => f.valor_total && parseFloat(f.valor_total) > 0);
                
                // Se for cotação parcial ou aberta E tiver fornecedores com valor, mostrar botão de enviar
                if ((data.cotacao.status === 'parcial' || data.cotacao.status === 'aberta' || data.cotacao.status === 'em_cotacao') && fornecedoresComValor.length > 0) {
                    $('#btnEnviarAutorizacaoExistentes').show(); // Botão para enviar fornecedores existentes
                    $('#btnAdicionarFornecedores').show(); // Também pode adicionar mais
                } else if (data.cotacao.status === 'parcial') {
                    $('#btnAdicionarFornecedores').show();
                }
            } else if (data.cotacao.status === 'aberta' || data.cotacao.status === 'parcial') {
                // Não tem fornecedores e está aberta ou parcial - mostrar botão para adicionar
                $('#secao-fornecedores-existentes').hide();
                $('#btnAdicionarFornecedores').show();
            } else {
                // Não tem fornecedores e não está aberta
                $('#secao-fornecedores-existentes').hide();
            }
            
            $('#modalVerCotacao').modal('show');
        });
    });
    
    // Variável global para armazenar os itens da cotação atual
    // Variável global para armazenar itens da cotação atual
    window.itensCotacaoAtual = window.itensCotacaoAtual || [];
    
    // Variável para armazenar itens já cotados
    window.itensJaCotados = window.itensJaCotados || [];
    
    // Função para renderizar checkboxes de itens em cada card de fornecedor
    function renderizarItensNosFornecedores() {
        var temItensPendentes = false;
        var temItensCotados = false;
        
        for (var f = 1; f <= 3; f++) {
            var container = $('#itens-fornecedor-' + f);
            var html = '<div class="row">';
            
            window.itensCotacaoAtual.forEach(function(item, idx) {
                var jaCotado = item.ja_cotado || (window.itensJaCotados && window.itensJaCotados.indexOf(item.id) !== -1);
                
                if (jaCotado) {
                    temItensCotados = true;
                    // Item já cotado - mostrar como desabilitado com badge diferente
                    html += '<div class="col-md-6 col-lg-4 mb-1">';
                    html += '<div class="custom-control custom-checkbox">';
                    html += '<input type="checkbox" class="custom-control-input" id="item-forn' + f + '-' + item.id + '-disabled" disabled checked>';
                    html += '<label class="custom-control-label small text-muted" for="item-forn' + f + '-' + item.id + '-disabled">';
                    html += '<span class="badge badge-success mr-1" title="Já cotado"><i class="fas fa-check"></i> ' + item.quantidade + ' ' + (item.unidade || 'UN') + '</span>';
                    html += '<s>' + item.produto.substring(0, 25) + '</s>';
                    html += ' <small class="text-success">(já cotado)</small>';
                    html += '</label>';
                    html += '</div>';
                    html += '</div>';
                } else {
                    temItensPendentes = true;
                    // Item pendente - pode ser selecionado
                    html += '<div class="col-md-6 col-lg-4 mb-1">';
                    html += '<div class="custom-control custom-checkbox">';
                    html += '<input type="checkbox" class="custom-control-input item-check-' + f + '" id="item-forn' + f + '-' + item.id + '" value="' + item.id + '" checked>';
                    html += '<label class="custom-control-label small" for="item-forn' + f + '-' + item.id + '">';
                    html += '<span class="badge badge-warning mr-1">' + item.quantidade + ' ' + (item.unidade || 'UN') + '</span>';
                    html += item.produto.substring(0, 30) + (item.produto.length > 30 ? '...' : '');
                    html += '</label>';
                    html += '</div>';
                    html += '</div>';
                }
            });
            html += '</div>';
            
            // Mostrar legenda se tiver itens já cotados
            if (temItensCotados) {
                html = '<div class="alert alert-info py-1 px-2 mb-2 small"><i class="fas fa-info-circle"></i> Itens em <span class="badge badge-success"><i class="fas fa-check"></i></span> verde já foram cotados. Selecione apenas os itens <span class="badge badge-warning">pendentes</span> que este fornecedor tem.</div>' + html;
            }
            
            container.html(html);
        }
    }
    
    // Botão para mostrar campos de adicionar fornecedores
    $('#btnAdicionarFornecedores').click(function() {
        $('#secao-adicionar-fornecedores').slideDown();
        $(this).hide();
        // Mostrar botões de envio (melhor preço desativado)
        // $('#btnSalvarFornecedores').show(); // DESATIVADO - lógica de menor preço
        $('#btnEnviarSeparado').show();
        $('#btnEnviarParcial').show();
        
        // Limpar campos (hidden IDs e inputs visíveis)
        $('#forn1, #forn2, #forn3').val('');
        $('#secao-adicionar-fornecedores .input-fornecedor').val('').removeClass('fornecedor-selecionado');
        $('#valor1, #valor2, #valor3').val('');
        $('#prazo1, #prazo2, #prazo3').val('');
        
        // Renderizar checkboxes de itens
        renderizarItensNosFornecedores();
    });
    
    // =========================================================================
    // BOTÃO ENVIAR PARA AUTORIZAÇÃO (fornecedores já existentes)
    // Envia os fornecedores já cadastrados na cotação para gerar O.C.s
    // =========================================================================
    $('#btnEnviarAutorizacaoExistentes').click(function() {
        var cotacaoId = $('#cotacao-id-editar').val();
        var btn = $(this);
        
        Swal.fire({
            title: 'Enviar para Autorização?',
            html: '<p>Serão geradas <strong>Ordens de Compra</strong> para os fornecedores já cadastrados nesta cotação.</p>' +
                  '<p class="text-info"><i class="fas fa-info-circle"></i> Uma O.C. será criada para cada fornecedor com seus respectivos itens.</p>' +
                  '<p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Se ainda faltarem itens para cotar, a cotação continuará como <strong>"Parcial"</strong>.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Sim, enviar para autorização',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
                
                // Enviar requisição para gerar OCs dos fornecedores existentes
                $.ajax({
                    url: '/api/suprimentos/cotacoes/' + cotacaoId + '/enviar-autorizacao',
                    method: 'POST',
                    data: {
                        enviar_separado: '1'
                    },
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            var html = response.message;
                            
                            // Verificar se foram geradas múltiplas OCs
                            if (response.ocs_geradas && response.ocs_geradas.length > 0) {
                                html = '<strong>' + response.ocs_geradas.length + ' Ordem(ns) de Compra gerada(s):</strong><br><br>';
                                response.ocs_geradas.forEach(function(oc) {
                                    html += '<div class="mb-2 p-2 border rounded text-left">';
                                    html += '<strong>' + oc.numero + '</strong><br>';
                                    html += 'Fornecedor: ' + oc.fornecedor + '<br>';
                                    html += 'Valor: R$ ' + parseFloat(oc.valor).toFixed(2).replace('.', ',') + '<br>';
                                    html += '<small class="text-muted">' + oc.itens + ' item(ns)</small>';
                                    html += '</div>';
                                });
                                
                                if (response.itens_faltando && response.itens_faltando > 0) {
                                    html += '<br><p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Ainda faltam <strong>' + response.itens_faltando + ' item(ns)</strong> para cotar. A cotação continua como "Parcial".</p>';
                                }
                                
                                html += '<br><small class="text-success"><i class="fas fa-check"></i> Aguardando aprovação em Ordem de Compra.</small>';
                            }
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                html: html,
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Erro ao enviar para autorização'
                            });
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao enviar para autorização!';
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: msg
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar para Autorização');
                    }
                });
            }
        });
    });
    
    /* =======================================================================
     * BOTÃO btnSalvarFornecedores DESATIVADO - Usava lógica de menor preço
     * Comentado em 02/02/2026 - Agora usar "Enviar para Autorização" ou "Enviar Parcial"
     * ======================================================================= */
    /*
    $('#btnSalvarFornecedores').click(function() {
        var cotacaoId = $('#cotacao-id-editar').val();
        
        // Contar quantos fornecedores existem (3 iniciais + adicionais)
        var totalFornecedores = 3 + $('#fornecedores-adicionais-container .fornecedor-adicional').length;
        
        // Verificar se tem pelo menos um fornecedor com valor
        var temFornecedor = false;
        for (var i = 1; i <= totalFornecedores; i++) {
            var fornId = $('#forn' + i).val();
            var valor = $('#valor' + i).val();
            if (fornId && valor) {
                temFornecedor = true;
                break;
            }
        }
        
        if (!temFornecedor) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Selecione pelo menos um fornecedor e informe o valor!'
            });
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        
        // Usar FormData para enviar arquivos
        var formData = new FormData();
        formData.append('cotacao_id', cotacaoId);
        
        var fornecedorIndex = 0;
        
        // Processar todos os fornecedores (1 até totalFornecedores)
        for (var i = 1; i <= totalFornecedores; i++) {
            var fornId = $('#forn' + i).val();
            var valor = $('#valor' + i).val();
            var prazo = $('#prazo' + i).val();
            var $orcamentoInput = $('#orcamento' + i);
            var arquivo = $orcamentoInput.length > 0 && $orcamentoInput[0].files.length > 0 ? $orcamentoInput[0].files[0] : null;
            
            if (fornId && valor) {
                formData.append('valores[' + fornecedorIndex + '][fornecedor_id]', fornId);
                formData.append('valores[' + fornecedorIndex + '][valor_total]', desformatarMoedaBRL(valor));
                formData.append('valores[' + fornecedorIndex + '][prazo_entrega]', prazo || 7);
                formData.append('valores[' + fornecedorIndex + '][condicao_pagamento]', $('#pagamento' + i).val() || '');
                formData.append('valores[' + fornecedorIndex + '][observacao]', $('#obs' + i).val() || '');
                
                // Número de parcelas (para boleto parcelado)
                var parcelas = $('#parcelas' + i).val();
                if (parcelas && parseInt(parcelas) > 1) {
                    formData.append('valores[' + fornecedorIndex + '][parcelas]', parcelas);
                }
                
                // Coletar itens selecionados para este fornecedor com quantidades personalizadas
                var itensSelecionados = [];
                var itensComQuantidade = [];
                var qtdPersonalizadas = window.getQuantidadesPersonalizadas ? window.getQuantidadesPersonalizadas() : {};
                
                // Usar a classe correta item-check-N
                $('.item-check-' + i + ':checked').each(function() {
                    var itemId = $(this).val();
                    itensSelecionados.push(itemId);
                    
                    // Verificar se tem quantidade personalizada
                    var qtdPersonalizada = null;
                    if (qtdPersonalizadas[i] && qtdPersonalizadas[i][itemId] !== undefined) {
                        qtdPersonalizada = qtdPersonalizadas[i][itemId];
                    }
                    
                    itensComQuantidade.push({
                        id: itemId,
                        quantidade_personalizada: qtdPersonalizada
                    });
                });
                console.log('Fornecedor ' + i + ' - Itens selecionados:', itensSelecionados);
                console.log('Fornecedor ' + i + ' - Itens com quantidade:', itensComQuantidade);
                formData.append('valores[' + fornecedorIndex + '][itens]', JSON.stringify(itensSelecionados));
                formData.append('valores[' + fornecedorIndex + '][itens_quantidade]', JSON.stringify(itensComQuantidade));
                
                if (arquivo) {
                    formData.append('orcamentos[' + fornecedorIndex + ']', arquivo);
                    formData.append('valores[' + fornecedorIndex + '][orcamento_index]', fornecedorIndex);
                }
                fornecedorIndex++;
            }
        }
        
        $.ajax({
            url: '/api/suprimentos/cotacoes/' + cotacaoId + '/adicionar-fornecedores',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(response) {
                if (response.success) {
                    var msg = response.message || 'Valores salvos com sucesso!';
                    var html = msg;
                    
                    // Verificar se foram geradas múltiplas OCs (cotação dividida)
                    if (response.multiplas_ocs && response.ocs_geradas) {
                        html = 'Cotação finalizada!<br><br>';
                        html += '<strong>' + response.ocs_geradas.length + ' Ordens de Compra geradas:</strong><br><br>';
                        response.ocs_geradas.forEach(function(oc) {
                            html += '<div class="mb-2 p-2 border rounded">';
                            html += '<strong>' + oc.numero + '</strong><br>';
                            html += 'Fornecedor: ' + oc.fornecedor + '<br>';
                            html += 'Valor: R$ ' + parseFloat(oc.valor).toFixed(2).replace('.', ',') + '<br>';
                            html += '<small class="text-muted">' + oc.itens + ' item(ns)</small>';
                            html += '</div>';
                        });
                        html += '<br><small>Aguardando aprovação em Ordem de Compra.</small>';
                    } else if (response.numero_oc) {
                        html = 'Cotação finalizada!<br><br>' +
                               'Ordem de Compra <strong>' + response.numero_oc + '</strong> gerada.<br>' +
                               'Fornecedor: <strong>' + response.fornecedor + '</strong><br>' +
                               'Valor: <strong>R$ ' + parseFloat(response.valor).toFixed(2).replace('.', ',') + '</strong><br><br>' +
                               '<small>Aguardando aprovação em Ordem de Compra.</small>';
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        html: html,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao salvar valores'
                    });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao salvar valores!';
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: msg
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-balance-scale"></i> Finalizar com Melhor Preço');
            }
        });
    });
    FIM DO CÓDIGO ANTIGO COMENTADO */
    
    // Enviar Todos Separado - Gera uma O.C. para cada fornecedor sem comparar preços
    $('#btnEnviarSeparado').click(function() {
        var cotacaoId = $('#cotacao-id-editar').val();
        
        // Contar quantos fornecedores existem (3 iniciais + adicionais)
        var totalFornecedores = 3 + $('#fornecedores-adicionais-container .fornecedor-adicional').length;
        
        // Coletar fornecedores com valor
        var fornecedoresValidos = [];
        for (var i = 1; i <= totalFornecedores; i++) {
            var fornId = $('#forn' + i).val();
            var valor = $('#valor' + i).val();
            if (fornId && valor) {
                fornecedoresValidos.push({
                    index: i,
                    fornecedor_id: fornId,
                    valor: valor
                });
            }
        }
        
        if (fornecedoresValidos.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Informe pelo menos um fornecedor com valor!'
            });
            return;
        }
        
        Swal.fire({
            title: 'Enviar para Autorização?',
            html: '<p>Serão geradas <strong>' + fornecedoresValidos.length + ' Ordem(ns) de Compra</strong> e enviadas para autorização.</p>' +
                  '<p class="text-info"><i class="fas fa-info-circle"></i> Uma O.C. será criada para cada fornecedor com os itens selecionados.</p>' +
                  '<p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Se ainda faltarem itens para cotar, a cotação continuará como <strong>"Parcial"</strong>.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Sim, enviar para autorização',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                var btn = $('#btnEnviarSeparado');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
                
                var formData = new FormData();
                formData.append('cotacao_id', cotacaoId);
                formData.append('enviar_separado', '1'); // Flag para indicar envio separado
                
                var fornecedorIndex = 0;
                
                // Processar todos os fornecedores
                for (var i = 1; i <= totalFornecedores; i++) {
                    var fornId = $('#forn' + i).val();
                    var valor = $('#valor' + i).val();
                    var prazo = $('#prazo' + i).val();
                    var $orcamentoInput = $('#orcamento' + i);
                    var arquivo = $orcamentoInput.length > 0 && $orcamentoInput[0].files.length > 0 ? $orcamentoInput[0].files[0] : null;
                    
                    if (fornId && valor) {
                        formData.append('valores[' + fornecedorIndex + '][fornecedor_id]', fornId);
                        formData.append('valores[' + fornecedorIndex + '][valor_total]', desformatarMoedaBRL(valor));
                        formData.append('valores[' + fornecedorIndex + '][prazo_entrega]', prazo || 7);
                        formData.append('valores[' + fornecedorIndex + '][condicao_pagamento]', $('#pagamento' + i).val() || '');
                        formData.append('valores[' + fornecedorIndex + '][observacao]', $('#obs' + i).val() || '');
                        
                        // Número de parcelas (para boleto parcelado) - Enviar Separado
                        var parcelasSep = $('#parcelas' + i).val();
                        if (parcelasSep && parseInt(parcelasSep) > 1) {
                            formData.append('valores[' + fornecedorIndex + '][parcelas]', parcelasSep);
                        }
                        
                        // Coletar itens selecionados para este fornecedor
                        var itensSelecionados = [];
                        $('.item-check-' + i + ':checked').each(function() {
                            itensSelecionados.push($(this).val());
                        });
                        formData.append('valores[' + fornecedorIndex + '][itens]', JSON.stringify(itensSelecionados));
                        
                        if (arquivo) {
                            formData.append('orcamentos[' + fornecedorIndex + ']', arquivo);
                        }
                        fornecedorIndex++;
                    }
                }
                
                $.ajax({
                    url: '/api/suprimentos/cotacoes/' + cotacaoId + '/adicionar-fornecedores',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Enviado!',
                                html: response.message || 'Ordens de compra geradas com sucesso!',
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Erro ao enviar cotação'
                            });
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao enviar!';
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: msg
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar para Autorização');
                    }
                });
            }
        });
    });
    
    // Enviar Parcial - Envia só os fornecedores preenchidos e mantém cotação como parcial
    $('#btnEnviarParcial').click(function() {
        var cotacaoId = $('#cotacao-id-editar').val();
        
        // Contar quantos fornecedores existem (3 iniciais + adicionais)
        var totalFornecedores = 3 + $('#fornecedores-adicionais-container .fornecedor-adicional').length;
        
        // Coletar fornecedores com valor
        var fornecedoresValidos = [];
        var fornecedoresVazios = 0;
        for (var i = 1; i <= totalFornecedores; i++) {
            var fornId = $('#forn' + i).val();
            var valor = $('#valor' + i).val();
            if (fornId && valor) {
                fornecedoresValidos.push({
                    index: i,
                    fornecedor_id: fornId,
                    valor: valor
                });
            } else if ($('#forn' + i).length > 0) {
                fornecedoresVazios++;
            }
        }
        
        if (fornecedoresValidos.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Informe pelo menos um fornecedor com valor para enviar!'
            });
            return;
        }
        
        Swal.fire({
            title: 'Enviar Parcialmente?',
            html: '<p>Serão geradas <strong>' + fornecedoresValidos.length + ' Ordem(ns) de Compra</strong> agora.</p>' +
                  '<p class="text-warning"><i class="fas fa-clock"></i> A cotação ficará como <strong>"Parcial"</strong> para você continuar cotando os demais itens depois.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sim, enviar parcial',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                var btn = $('#btnEnviarParcial');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
                
                var formData = new FormData();
                formData.append('cotacao_id', cotacaoId);
                formData.append('enviar_parcial', '1'); // Flag para indicar envio parcial
                
                var fornecedorIndex = 0;
                
                // Processar todos os fornecedores
                for (var i = 1; i <= totalFornecedores; i++) {
                    var fornId = $('#forn' + i).val();
                    var valor = $('#valor' + i).val();
                    var prazo = $('#prazo' + i).val();
                    var $orcamentoInput = $('#orcamento' + i);
                    var arquivo = $orcamentoInput.length > 0 && $orcamentoInput[0].files.length > 0 ? $orcamentoInput[0].files[0] : null;
                    
                    if (fornId && valor) {
                        formData.append('valores[' + fornecedorIndex + '][fornecedor_id]', fornId);
                        formData.append('valores[' + fornecedorIndex + '][valor_total]', desformatarMoedaBRL(valor));
                        formData.append('valores[' + fornecedorIndex + '][prazo_entrega]', prazo || 7);
                        formData.append('valores[' + fornecedorIndex + '][condicao_pagamento]', $('#pagamento' + i).val() || '');
                        formData.append('valores[' + fornecedorIndex + '][observacao]', $('#obs' + i).val() || '');
                        
                        // Número de parcelas (para boleto parcelado) - Enviar Parcial
                        var parcelasParcial = $('#parcelas' + i).val();
                        if (parcelasParcial && parseInt(parcelasParcial) > 1) {
                            formData.append('valores[' + fornecedorIndex + '][parcelas]', parcelasParcial);
                        }
                        
                        // Coletar itens selecionados para este fornecedor
                        var itensSelecionados = [];
                        $('.item-check-' + i + ':checked').each(function() {
                            itensSelecionados.push($(this).val());
                        });
                        formData.append('valores[' + fornecedorIndex + '][itens]', JSON.stringify(itensSelecionados));
                        
                        if (arquivo) {
                            formData.append('orcamentos[' + fornecedorIndex + ']', arquivo);
                        }
                        fornecedorIndex++;
                    }
                }
                
                $.ajax({
                    url: '/api/suprimentos/cotacoes/' + cotacaoId + '/adicionar-fornecedores',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Enviado Parcialmente!',
                                html: response.message || 'Ordens de compra geradas! A cotação continua aberta para os demais itens.',
                                timer: 4000,
                                showConfirmButton: true
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Erro ao enviar cotação'
                            });
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao enviar!';
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: msg
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-clock"></i> Enviar Parcial');
                    }
                });
            }
        });
    });
    
    // Rejeitar cotação (apenas admin) - mantém todo o histórico com status "rejeitada"
    $(document).on('click', '.btn-excluir', function() {
        var id = $(this).data('id');
        var numero = $(this).data('numero');
        
        Swal.fire({
            title: 'Rejeitar Cotação',
            html: '<p>Você está prestes a <strong>REJEITAR</strong> a cotação <strong>' + numero + '</strong>.</p>' +
                  '<p class="text-success"><i class="fas fa-check-circle mr-1"></i>O histórico completo será mantido para consulta.</p>' +
                  '<hr>' +
                  '<label for="motivo-cancelamento" class="text-left d-block"><strong>Motivo da rejeição: *</strong></label>' +
                  '<textarea id="motivo-cancelamento" class="form-control" rows="3" placeholder="Informe o motivo da rejeição (mínimo 10 caracteres)..." required></textarea>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-ban"></i> Confirmar Rejeição',
            cancelButtonText: 'Voltar',
            preConfirm: () => {
                const motivo = document.getElementById('motivo-cancelamento').value;
                if (!motivo || motivo.trim().length < 10) {
                    Swal.showValidationMessage('O motivo deve ter pelo menos 10 caracteres');
                    return false;
                }
                return motivo;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/cotacoes/' + id,
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    contentType: 'application/json',
                    data: JSON.stringify({ motivo: result.value }),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Rejeitada!',
                                text: response.message || 'Cotação rejeitada com sucesso!',
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Erro ao rejeitar cotação'
                            });
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao rejeitar cotação!';
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: msg
                        });
                    }
                });
            }
        });
    });
    
    // Copiar cotação para WhatsApp
    var dadosCotacaoAtual = null;
    
    $('#btnCopiarWhatsApp').click(function() {
        if (!dadosCotacaoAtual) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Dados da cotação não disponíveis.'
            });
            return;
        }
        
        var texto = '📋 *SOLICITAÇÃO DE COTAÇÃO*\n';
        
        // Nome da Obra (Centro de Custo)
        if (dadosCotacaoAtual.cotacao.obra_nome) {
            texto += '🏗️ *' + dadosCotacaoAtual.cotacao.obra_nome + '*\n';
        }
        
        texto += '━━━━━━━━━━━━━━━━━━━━━\n';
        texto += '📌 *Nº:* ' + dadosCotacaoAtual.cotacao.numero + '\n';
        texto += '📝 *Descrição:* ' + dadosCotacaoAtual.cotacao.descricao + '\n';
        
        if (dadosCotacaoAtual.cotacao.data_limite) {
            var dataLim = new Date(dadosCotacaoAtual.cotacao.data_limite);
            texto += '📅 *Data Limite:* ' + dataLim.toLocaleDateString('pt-BR') + '\n';
        }
        
        // Nome do Solicitante
        if (dadosCotacaoAtual.cotacao.solicitante_nome) {
            texto += '👤 *Solicitante:* ' + dadosCotacaoAtual.cotacao.solicitante_nome + '\n';
        }
        
        texto += '━━━━━━━━━━━━━━━━━━━━━\n\n';
        texto += '📦 *ITENS PARA COTAÇÃO:*\n\n';
        
        if (dadosCotacaoAtual.itens && dadosCotacaoAtual.itens.length > 0) {
            dadosCotacaoAtual.itens.forEach(function(item) {
                texto += '• ' + item.produto + '\n';
                texto += '   ↳ Qtd: *' + item.quantidade + ' ' + (item.unidade || 'UN') + '*\n';
            });
        }
        
        texto += '\n━━━━━━━━━━━━━━━━━━━━━\n';
        texto += '💰 Por favor, envie o orçamento com:\n';
        texto += '• Valor unitário e total\n';
        texto += '• Prazo de entrega\n';
        texto += '• Condições de pagamento\n';
        texto += '\n📄 *IMPORTANTE:* Favor enviar a cotação em PDF.\n';
        texto += '━━━━━━━━━━━━━━━━━━━━━\n';
        texto += '\n_Aguardamos seu retorno!_ 🙏';
        
        // Copiar para área de transferência
        navigator.clipboard.writeText(texto).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Copiado!',
                html: 'Texto copiado para a área de transferência.<br><small class="text-muted">Agora é só colar no WhatsApp!</small>',
                timer: 2500,
                showConfirmButton: false
            });
        }).catch(function(err) {
            // Fallback para navegadores mais antigos
            var textarea = document.createElement('textarea');
            textarea.value = texto;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                Swal.fire({
                    icon: 'success',
                    title: 'Copiado!',
                    html: 'Texto copiado para a área de transferência.<br><small class="text-muted">Agora é só colar no WhatsApp!</small>',
                    timer: 2500,
                    showConfirmButton: false
                });
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Não foi possível copiar. Tente novamente.'
                });
            }
            document.body.removeChild(textarea);
        });
    });

    // =============================================
    // ADICIONAR MAIS FORNECEDORES (até 10)
    // =============================================
    let contadorFornecedores = 3; // Já temos 3 iniciais (índices 0, 1, 2)
    const maxFornecedores = 10;

    $('#btnAdicionarFornecedor').on('click', function() {
        if (contadorFornecedores >= maxFornecedores) {
            toastr.warning('Máximo de ' + maxFornecedores + ' fornecedores por cotação.');
            return;
        }

        const idx = contadorFornecedores;
        const novaLinha = `
            <tr data-index="${idx}">
                <td>
                    <div class="fornecedor-autocomplete-wrapper">
                        <input type="text" class="form-control form-control-sm input-fornecedor" data-index="${idx}" placeholder="Digite o nome do fornecedor..." autocomplete="off">
                        <input type="hidden" name="fornecedores[${idx}][id]" class="fornecedor-id">
                        <div class="fornecedor-autocomplete-list"></div>
                    </div>
                </td>
                <td><input type="text" class="form-control form-control-sm input-valor" name="fornecedores[${idx}][valor]" placeholder="0,00"></td>
                <td><input type="text" class="form-control form-control-sm text-center" name="fornecedores[${idx}][prazo]" placeholder="7" inputmode="numeric" pattern="[0-9]*"></td>
                <td><input type="file" class="form-control-file form-control-sm" name="fornecedores[${idx}][orcamento]" accept=".pdf,.jpg,.jpeg,.png"></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-remover-fornecedor" title="Remover">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#tbodyFornecedores').append(novaLinha);
        contadorFornecedores++;

        // Atualizar contador visual
        if (contadorFornecedores >= maxFornecedores) {
            $('#btnAdicionarFornecedor').prop('disabled', true).addClass('disabled');
        }

        toastr.success('Fornecedor ' + contadorFornecedores + ' adicionado.');
    });

    // Remover linha de fornecedor
    $(document).on('click', '.btn-remover-fornecedor', function() {
        $(this).closest('tr').remove();
        // Não decrementamos o contador para evitar conflitos de índice
        $('#btnAdicionarFornecedor').prop('disabled', false).removeClass('disabled');
    });

    // Resetar fornecedores ao abrir modal de nova cotação
    $('#modalCotacao').on('show.bs.modal', function() {
        // Remover linhas extras (manter só as 3 primeiras)
        $('#tbodyFornecedores tr').each(function(index) {
            if (index >= 3) {
                $(this).remove();
            }
        });
        contadorFornecedores = 3;
        $('#btnAdicionarFornecedor').prop('disabled', false).removeClass('disabled');
    });

    // =============================================
    // ADICIONAR MAIS FORNECEDORES NO MODAL VER COTAÇÃO (até 10)
    // =============================================
    let contadorFornecedoresAdicional = 4; // Já temos 3 iniciais (cards 1, 2, 3)
    const maxFornecedoresAdicional = 10;
    const coresCards = ['info', 'secondary', 'dark', 'primary', 'success', 'warning', 'danger'];

    $('#btnAdicionarMaisFornecedor').on('click', function() {
        if (contadorFornecedoresAdicional > maxFornecedoresAdicional) {
            toastr.warning('Máximo de ' + maxFornecedoresAdicional + ' fornecedores por cotação.');
            return;
        }

        const num = contadorFornecedoresAdicional;
        const corCard = coresCards[(num - 1) % coresCards.length];
        
        // Usar itensCotacaoAtual para renderizar os itens do novo fornecedor
        let itensHtml = '';
        let temItensCotados = false;
        let temItensPendentes = false;
        
        if (window.itensCotacaoAtual && window.itensCotacaoAtual.length > 0) {
            itensHtml = '<div class="row">';
            window.itensCotacaoAtual.forEach(function(item) {
                var jaCotado = item.ja_cotado || (window.itensJaCotados && window.itensJaCotados.indexOf(item.id) !== -1);
                
                if (jaCotado) {
                    temItensCotados = true;
                    // Item já cotado - desabilitado
                    itensHtml += '<div class="col-md-6 col-lg-4 mb-1">';
                    itensHtml += '<div class="custom-control custom-checkbox">';
                    itensHtml += '<input type="checkbox" class="custom-control-input" id="item-forn' + num + '-' + item.id + '-disabled" disabled checked>';
                    itensHtml += '<label class="custom-control-label small text-muted" for="item-forn' + num + '-' + item.id + '-disabled">';
                    itensHtml += '<span class="badge badge-success mr-1"><i class="fas fa-check"></i> ' + item.quantidade + ' ' + (item.unidade || 'UN') + '</span>';
                    itensHtml += '<s>' + item.produto.substring(0, 25) + '</s>';
                    itensHtml += ' <small class="text-success">(já cotado)</small>';
                    itensHtml += '</label>';
                    itensHtml += '</div>';
                    itensHtml += '</div>';
                } else {
                    temItensPendentes = true;
                    // Item pendente
                    itensHtml += '<div class="col-md-6 col-lg-4 mb-1">';
                    itensHtml += '<div class="custom-control custom-checkbox">';
                    itensHtml += '<input type="checkbox" class="custom-control-input item-check-' + num + '" id="item-forn' + num + '-' + item.id + '" value="' + item.id + '" checked>';
                    itensHtml += '<label class="custom-control-label small" for="item-forn' + num + '-' + item.id + '">';
                    itensHtml += '<span class="badge badge-warning mr-1">' + item.quantidade + ' ' + (item.unidade || 'UN') + '</span>';
                    itensHtml += item.produto.substring(0, 30) + (item.produto.length > 30 ? '...' : '');
                    itensHtml += '</label>';
                    itensHtml += '</div>';
                    itensHtml += '</div>';
                }
            });
            itensHtml += '</div>';
            
            // Adicionar legenda se tiver itens já cotados
            if (temItensCotados) {
                itensHtml = '<div class="alert alert-info py-1 px-2 mb-2 small"><i class="fas fa-info-circle"></i> Itens em <span class="badge badge-success"><i class="fas fa-check"></i></span> verde já foram cotados. Selecione apenas os itens <span class="badge badge-warning">pendentes</span> que este fornecedor tem.</div>' + itensHtml;
            }
        } else {
            itensHtml = '<div class="col-12"><small class="text-muted">Nenhum item disponível</small></div>';
        }

        const novoCard = `
            <div class="card card-outline card-${corCard} mb-2 fornecedor-card fornecedor-adicional" id="fornecedor-card-${num}">
                <div class="card-header py-2 px-3">
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-4 mb-1 mb-lg-0">
                            <div class="fornecedor-autocomplete-wrapper">
                                <input type="text" class="form-control form-control-sm input-fornecedor" data-index="add-${num-1}" placeholder="Digite o fornecedor..." autocomplete="off">
                                <input type="hidden" id="forn${num}" name="forn${num}" class="fornecedor-id">
                                <div class="fornecedor-autocomplete-list"></div>
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                            <input type="text" class="form-control form-control-sm valor-fornecedor" id="valor${num}" name="valor${num}" placeholder="R$ 0,00">
                        </div>
                        <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                            <input type="text" class="form-control form-control-sm text-center" id="prazo${num}" name="prazo${num}" placeholder="Prazo (dias)" inputmode="numeric">
                        </div>
                        <div class="col-lg-2 col-md-2 mb-1 mb-lg-0">
                            <select class="form-control form-control-sm" id="pagamento${num}" name="pagamento${num}">
                                <option value="">Forma Pagamento</option>
                                <option value="pix">PIX</option>
                                <option value="boleto">Boleto</option>
                                <option value="credito">Crédito</option>
                                <option value="debito">Débito</option>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="transferencia">Transferência</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-2 mb-1 mb-lg-0">
                            <label class="btn btn-sm btn-outline-secondary btn-upload mb-0 w-100" title="Anexar PDF do orçamento">
                                <i class="fas fa-paperclip"></i> <span class="file-name">PDF</span>
                                <input type="file" id="orcamento${num}" name="orcamento${num}" accept=".pdf,.jpg,.jpeg,.png">
                            </label>
                        </div>
                        <div class="col-lg-1 col-md-2 mb-1 mb-lg-0 parcelas-container" id="parcelas-container-${num}" style="display: none;">
                            <input type="number" class="form-control form-control-sm text-center parcelas-input" id="parcelas${num}" name="parcelas${num}" placeholder="Vezes" min="1" max="24" title="Número de parcelas do boleto">
                        </div>
                        <div class="col-lg-2 col-md-10">
                            <input type="text" class="form-control form-control-sm" id="obs${num}" name="obs${num}" placeholder="Observação: chave PIX, dados bancários...">
                        </div>
                        <div class="col-lg-1 col-md-2 text-right">
                            <button type="button" class="btn btn-danger btn-sm btn-remover-fornecedor-card" data-card="${num}" title="Remover fornecedor">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted"><i class="fas fa-check-square"></i> Marque os itens disponíveis neste fornecedor:</small>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-xs btn-outline-info mr-2 btn-editar-qtd" data-fornecedor="${num}" title="Editar quantidades dos itens">
                                <i class="fas fa-edit"></i> Editar Qtd
                            </button>
                            <div class="form-check mb-0">
                                <input class="form-check-input marcar-todos" type="checkbox" id="marcarTodos${num}" data-fornecedor="${num}" checked>
                                <label class="form-check-label" for="marcarTodos${num}" style="font-size: 12px; cursor: pointer;">
                                    Marcar/Desmarcar Todos
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="itens-fornecedor" id="itens-fornecedor-${num}">
                        ${itensHtml}
                    </div>
                </div>
            </div>
        `;
        
        $('#fornecedores-adicionais-container').append(novoCard);
        contadorFornecedoresAdicional++;

        // Atualizar contador visual
        if (contadorFornecedoresAdicional > maxFornecedoresAdicional) {
            $('#btnAdicionarMaisFornecedor').prop('disabled', true).addClass('disabled');
        }

        toastr.success('Fornecedor ' + (contadorFornecedoresAdicional - 1) + ' adicionado.');
    });

    // Remover card de fornecedor adicional
    $(document).on('click', '.btn-remover-fornecedor-card', function() {
        const cardNum = $(this).data('card');
        $('#fornecedor-card-' + cardNum).fadeOut(300, function() {
            $(this).remove();
        });
        $('#btnAdicionarMaisFornecedor').prop('disabled', false).removeClass('disabled');
        toastr.info('Fornecedor removido.');
    });

    // Resetar fornecedores adicionais ao abrir modal Ver Cotação
    $('#modalVerCotacao').on('show.bs.modal', function() {
        $('#fornecedores-adicionais-container').empty();
        contadorFornecedoresAdicional = 4;
        $('#btnAdicionarMaisFornecedor').prop('disabled', false).removeClass('disabled');
    });
    
    // =============================================
    // EDITAR QUANTIDADES DOS ITENS POR FORNECEDOR
    // =============================================
    
    // Armazenar quantidades personalizadas por fornecedor
    var quantidadesPersonalizadas = {};
    
    // Abrir modal de edição de quantidades
    $(document).on('click', '.btn-editar-qtd', function() {
        var fornecedorNum = $(this).data('fornecedor');
        $('#qtdFornecedorNum').text(fornecedorNum);
        $('#qtdFornecedorIndex').val(fornecedorNum);
        
        // Inicializar objeto para este fornecedor se não existir
        if (!quantidadesPersonalizadas[fornecedorNum]) {
            quantidadesPersonalizadas[fornecedorNum] = {};
        }
        
        // Preencher tabela com os itens da cotação
        var html = '';
        var itensParaEditar = window.itensCotacaoAtual || [];
        if (itensParaEditar.length > 0) {
            itensParaEditar.forEach(function(item) {
                var qtdOriginal = parseFloat(item.quantidade) || 0;
                var qtdFornecedor = quantidadesPersonalizadas[fornecedorNum][item.id] !== undefined 
                    ? quantidadesPersonalizadas[fornecedorNum][item.id] 
                    : qtdOriginal;
                
                html += '<tr data-item-id="' + item.id + '">';
                html += '<td>' + item.produto + '</td>';
                html += '<td class="text-center"><span class="badge badge-warning">' + qtdOriginal + '</span></td>';
                html += '<td class="text-center">';
                html += '<input type="number" class="form-control form-control-sm text-center input-qtd-fornecedor" ';
                html += 'data-item-id="' + item.id + '" data-qtd-original="' + qtdOriginal + '" ';
                html += 'value="' + qtdFornecedor + '" min="0" max="' + qtdOriginal + '" step="0.01">';
                html += '</td>';
                html += '<td class="text-center">' + (item.unidade || 'UN') + '</td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="4" class="text-center text-muted">Nenhum item encontrado</td></tr>';
        }
        
        $('#corpoTabelaEditarQtd').html(html);
        $('#modalEditarQtd').modal('show');
    });
    
    // Validar quantidade digitada
    $(document).on('input', '.input-qtd-fornecedor', function() {
        var qtdOriginal = parseFloat($(this).data('qtd-original')) || 0;
        var qtdDigitada = parseFloat($(this).val()) || 0;
        
        if (qtdDigitada > qtdOriginal) {
            $(this).val(qtdOriginal);
            toastr.warning('A quantidade não pode ser maior que a original (' + qtdOriginal + ')');
        }
        if (qtdDigitada < 0) {
            $(this).val(0);
        }
    });
    
    // Salvar quantidades personalizadas
    $('#btnSalvarQtd').click(function() {
        var fornecedorNum = $('#qtdFornecedorIndex').val();
        
        // Salvar cada quantidade
        $('#corpoTabelaEditarQtd .input-qtd-fornecedor').each(function() {
            var itemId = $(this).data('item-id');
            var qtdFornecedor = parseFloat($(this).val()) || 0;
            var qtdOriginal = parseFloat($(this).data('qtd-original')) || 0;
            
            // Só armazenar se for diferente da original
            if (qtdFornecedor !== qtdOriginal) {
                quantidadesPersonalizadas[fornecedorNum][itemId] = qtdFornecedor;
            } else {
                delete quantidadesPersonalizadas[fornecedorNum][itemId];
            }
        });
        
        // Atualizar visual dos itens no card do fornecedor
        atualizarVisualizacaoQuantidades(fornecedorNum);
        
        $('#modalEditarQtd').modal('hide');
        toastr.success('Quantidades salvas para o Fornecedor ' + fornecedorNum);
    });
    
    // Atualizar visualização dos itens com quantidades personalizadas
    function atualizarVisualizacaoQuantidades(fornecedorNum) {
        var container = $('#itens-fornecedor-' + fornecedorNum);
        
        container.find('.badge-warning, .badge-success').each(function() {
            var checkbox = $(this).closest('.custom-control').find('input[type="checkbox"]');
            var itemId = checkbox.val();
            
            if (quantidadesPersonalizadas[fornecedorNum] && quantidadesPersonalizadas[fornecedorNum][itemId] !== undefined) {
                var qtdPersonalizada = quantidadesPersonalizadas[fornecedorNum][itemId];
                var textoOriginal = $(this).text();
                var unidade = textoOriginal.split(' ').pop() || 'UN';
                
                if (qtdPersonalizada === 0) {
                    // Se quantidade for 0, desmarcar o checkbox
                    checkbox.prop('checked', false);
                    $(this).removeClass('badge-warning badge-info').addClass('badge-secondary');
                } else {
                    // Quantidade personalizada diferente de 0 - mostrar com badge-info
                    $(this).removeClass('badge-warning badge-secondary').addClass('badge-info');
                }
                
                $(this).html(qtdPersonalizada + ' ' + unidade + ' <i class="fas fa-edit" title="Quantidade editada"></i>');
            }
        });
    }
    
    // Expor quantidades personalizadas para uso no envio
    window.getQuantidadesPersonalizadas = function() {
        return quantidadesPersonalizadas;
    };
    
    // ===== AUTOCOMPLETE CENTRO DE CUSTO - MÚLTIPLA SELEÇÃO (até 7) =====
    var inputCC = $('#filtro_centro_custo');
    var hiddenCC = $('#filtro_centro_custo_ids');
    var listaCC = $('#listaCentroCusto');
    var containerSelecionados = $('#centroCustoSelecionados');
    var timeoutBuscaCC;
    var centrosCustoSelecionados = []; // Array de objetos {id, nome}
    var MAX_SELECAO = 7;
    
    // Carregar centros de custo já selecionados (se houver)
    var idsIniciais = hiddenCC.val();
    if (idsIniciais) {
        // Se tiver IDs iniciais, podemos carregá-los via AJAX ou já ter os nomes
        // Por enquanto, apenas mantemos os IDs
    }
    
    // Função para atualizar o campo hidden com os IDs selecionados
    function atualizarHiddenCentrosCusto() {
        var ids = centrosCustoSelecionados.map(function(cc) { return cc.id; });
        hiddenCC.val(ids.join(','));
    }
    
    // Função para renderizar os badges dos centros de custo selecionados
    function renderizarCentrosCustoSelecionados() {
        containerSelecionados.empty();
        centrosCustoSelecionados.forEach(function(cc, index) {
            containerSelecionados.append(
                '<span class="badge-cc-selecionado" data-index="' + index + '" style="' +
                'display: inline-flex; align-items: center; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); ' +
                'color: white; border-radius: 20px; padding: 5px 10px; font-size: 12px; margin: 2px;">' +
                '<span class="text-truncate" style="max-width: 150px;" title="' + cc.nome + '">' + cc.nome + '</span>' +
                '<span class="remove-cc-multi ml-2" style="cursor: pointer; font-weight: bold; font-size: 14px;" title="Remover">&times;</span>' +
                '</span>'
            );
        });
        
        // Atualizar placeholder do input
        if (centrosCustoSelecionados.length >= MAX_SELECAO) {
            inputCC.prop('disabled', true).attr('placeholder', 'Máximo de ' + MAX_SELECAO + ' selecionados');
        } else {
            inputCC.prop('disabled', false).attr('placeholder', 'Digite para buscar... (' + centrosCustoSelecionados.length + '/' + MAX_SELECAO + ')');
        }
    }
    
    // Buscar centros de custo ao digitar (mínimo 1 caractere)
    inputCC.on('input', function() {
        var termo = $(this).val().trim();
        clearTimeout(timeoutBuscaCC);
        
        if (termo.length < 1) {
            listaCC.hide();
            return;
        }
        
        timeoutBuscaCC = setTimeout(function() {
            listaCC.empty().append('<div class="cc-item text-center text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Buscando...</div>').show();
            
            $.get('/api/centros-custo/buscar-inicio', { termo: termo })
                .done(function(centros) {
                    listaCC.empty();
                    if (centros.length > 0) {
                        // Filtrar os já selecionados
                        var idsSelecionados = centrosCustoSelecionados.map(function(cc) { return cc.id; });
                        var centrosFiltrados = centros.filter(function(cc) {
                            return idsSelecionados.indexOf(cc.id) === -1;
                        });
                        
                        if (centrosFiltrados.length > 0) {
                            centrosFiltrados.forEach(function(cc) {
                                listaCC.append(
                                    '<div class="cc-item" data-id="' + cc.id + '" data-nome="' + cc.nome + '">' +
                                    '<i class="fas fa-building text-muted mr-2"></i>' +
                                    '<span class="cc-nome">' + cc.nome + '</span>' +
                                    '</div>'
                                );
                            });
                        } else {
                            listaCC.append('<div class="cc-item text-muted"><i class="fas fa-check-circle mr-2"></i>Todos já selecionados</div>');
                        }
                    } else {
                        listaCC.append('<div class="cc-item text-muted"><i class="fas fa-exclamation-circle mr-2"></i>Nenhum centro de custo encontrado</div>');
                    }
                    listaCC.show();
                })
                .fail(function() {
                    listaCC.empty().append('<div class="cc-item text-danger"><i class="fas fa-times-circle mr-2"></i>Erro ao buscar.</div>').show();
                });
        }, 300);
    });
    
    // Selecionar centro de custo da lista
    listaCC.on('click', '.cc-item[data-id]', function() {
        if (centrosCustoSelecionados.length >= MAX_SELECAO) {
            Swal.fire('Atenção', 'Máximo de ' + MAX_SELECAO + ' centros de custo permitidos.', 'warning');
            return;
        }
        
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        
        // Adicionar ao array
        centrosCustoSelecionados.push({ id: id, nome: nome });
        
        // Atualizar interface
        atualizarHiddenCentrosCusto();
        renderizarCentrosCustoSelecionados();
        
        inputCC.val('');
        listaCC.hide();
    });
    
    // Remover centro de custo selecionado
    containerSelecionados.on('click', '.remove-cc-multi', function() {
        var index = $(this).closest('.badge-cc-selecionado').data('index');
        centrosCustoSelecionados.splice(index, 1);
        atualizarHiddenCentrosCusto();
        renderizarCentrosCustoSelecionados();
    });
    
    // Fechar lista ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.centro-custo-autocomplete-wrapper').length) {
            listaCC.hide();
        }
    });
    
    // Focus no input ao clicar no wrapper
    inputCC.on('focus', function() {
        if ($(this).val().length >= 3) {
            listaCC.show();
        }
    });
    
    // ===== LÓGICA PARA CAMPO DE PARCELAS (BOLETO - INOVA PISOS) =====
    // Lista de fornecedores que permitem parcelamento no boleto
    var fornecedoresParcelamento = ['INOVA PISOS', 'INOVA PISOS E REVESTIMENTOS'];
    
    // Função para verificar se deve mostrar campo de parcelas
    function verificarCampoParcelas(cardNum) {
        var fornecedorInput = $('#fornecedor-card-' + cardNum + ' .input-fornecedor');
        var pagamentoSelect = $('#pagamento' + cardNum);
        var parcelasContainer = $('#parcelas-container-' + cardNum);
        
        if (!fornecedorInput.length || !pagamentoSelect.length || !parcelasContainer.length) return;
        
        var fornecedorNome = fornecedorInput.val().toUpperCase();
        var formaPagamento = pagamentoSelect.val();
        
        // Verificar se é um fornecedor que permite parcelamento E se é boleto
        var permiteParcelamento = fornecedoresParcelamento.some(function(f) {
            return fornecedorNome.indexOf(f.toUpperCase()) !== -1;
        });
        
        if (permiteParcelamento && formaPagamento === 'boleto') {
            parcelasContainer.slideDown(200);
        } else {
            parcelasContainer.slideUp(200);
            $('#parcelas' + cardNum).val(''); // Limpar valor
        }
    }
    
    // Event listeners para mudanças no pagamento e no fornecedor
    $(document).on('change', '[id^="pagamento"]', function() {
        var id = $(this).attr('id');
        var num = id.replace('pagamento', '');
        verificarCampoParcelas(num);
    });
    
    // Quando seleciona um fornecedor (após o autocomplete preencher)
    $(document).on('blur change', '.input-fornecedor', function() {
        var cardNum = $(this).closest('.fornecedor-card').attr('id');
        if (cardNum) {
            var num = cardNum.replace('fornecedor-card-', '');
            setTimeout(function() {
                verificarCampoParcelas(num);
            }, 100);
        }
    });
    
    // Quando seleciona fornecedor da lista de autocomplete
    $(document).on('click', '.fornecedor-autocomplete-item', function() {
        var card = $(this).closest('.fornecedor-card');
        if (card.length) {
            var num = card.attr('id').replace('fornecedor-card-', '');
            setTimeout(function() {
                verificarCampoParcelas(num);
            }, 200);
        }
    });
});
</script>
@stop
