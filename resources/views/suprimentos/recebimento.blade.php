@extends('adminlte::page')

@section('title', __('Recebimento'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0"><i class="fas fa-truck-loading text-success"></i> __('Recebimento de Material')</h1>
    <small class="text-muted">__('Controle de entrada de materiais')</small>
</div>
@stop

@section('css')
<style>
    /* Cards de Estatísticas */
    .stat-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }
    .stat-card .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .stat-card .stat-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
    }
    .stat-card .stat-label {
        font-size: 13px;
        color: #6c757d;
        margin-top: 4px;
    }
    
    /* Cards de OC */
    .oc-card {
        border-radius: 10px;
        border: 1px solid #e9ecef;
        background: #fff;
        transition: all 0.2s;
        margin-bottom: 12px;
    }
    .oc-card:hover {
        border-color: #28a745;
        box-shadow: 0 3px 12px rgba(40,167,69,0.15);
    }
    .oc-card.aguardando:hover {
        border-color: #ffc107;
        box-shadow: 0 3px 12px rgba(255,193,7,0.15);
    }
    .oc-card .oc-header {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .oc-card .oc-body {
        padding: 12px 15px;
    }
    .oc-card .oc-numero {
        font-weight: 700;
        font-size: 15px;
        color: #333;
    }
    .oc-card .oc-fornecedor {
        font-size: 13px;
        color: #666;
        margin-top: 2px;
    }
    .oc-card .oc-info {
        display: flex;
        gap: 20px;
        font-size: 12px;
        color: #888;
    }
    .oc-card .oc-info i {
        margin-right: 4px;
    }
    
    /* Seção com título */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    .section-header h5 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-header .badge {
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 20px;
    }
    
    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #adb5bd;
    }
    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    .empty-state p {
        margin: 0;
        font-size: 14px;
    }
    
    /* Tabela melhorada */
    .table-modern {
        border-radius: 8px;
        overflow: hidden;
    }
    .table-modern thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #495057;
        padding: 12px 15px;
    }
    .table-modern tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-color: #f0f0f0;
    }
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Botões de ação */
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    
    /* Modal melhorado */
    .modal-header.bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    /* Card principal */
    .main-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .main-card .card-header {
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 15px 20px;
    }
    
    /* Lista de OCs com scroll */
    .ocs-panel {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        background: #fff;
        overflow: hidden;
    }
    .ocs-panel .panel-header {
        padding: 15px 20px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
    }
    .ocs-panel .panel-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 16px;
    }
    .ocs-panel .panel-search {
        padding: 12px 15px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    .ocs-panel .panel-search input {
        border-radius: 20px;
        border: 1px solid #dee2e6;
        padding-left: 35px;
        font-size: 13px;
    }
    .ocs-panel .panel-search .search-icon {
        position: absolute;
        left: 27px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
    }
    .ocs-panel .panel-body {
        max-height: 500px;
        overflow-y: auto;
    }
    .ocs-panel .panel-body::-webkit-scrollbar {
        width: 6px;
    }
    .ocs-panel .panel-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .ocs-panel .panel-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    .ocs-panel .panel-body::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
    
    /* Tabela compacta de OCs */
    .ocs-table {
        margin: 0;
        font-size: 13px;
    }
    .ocs-table thead th {
        background: #fff;
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        padding: 10px 12px;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .ocs-table tbody td {
        padding: 10px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    .ocs-table tbody tr {
        cursor: pointer;
        transition: background 0.15s;
    }
    .ocs-table tbody tr:hover {
        background: #e8f5e9;
    }
    .ocs-table tbody tr.aguardando:hover {
        background: #fff8e1;
    }
    .ocs-table .oc-numero-cell {
        font-weight: 600;
        color: #333;
    }
    .ocs-table .oc-fornecedor-cell {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ocs-table .oc-cc-cell {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ocs-table .oc-valor-cell {
        font-weight: 500;
        color: #28a745;
    }
    
    /* Tabs para alternar */
    .ocs-tabs {
        display: flex;
        border-bottom: 1px solid #e9ecef;
    }
    .ocs-tabs .tab {
        flex: 1;
        text-align: center;
        padding: 12px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        color: #6c757d;
    }
    .ocs-tabs .tab:hover {
        background: #f8f9fa;
    }
    .ocs-tabs .tab.active {
        color: #28a745;
        border-bottom-color: #28a745;
        background: #fff;
    }
    .ocs-tabs .tab.tab-warning.active {
        color: #ffc107;
        border-bottom-color: #ffc107;
    }
    .ocs-tabs .tab .badge {
        font-size: 11px;
        padding: 3px 8px;
        margin-left: 5px;
    }
    
    /* Checkbox "Já cadastrei" */
    .check-ja-cadastrado {
        background: #e7f3ff;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px dashed #17a2b8;
    }
    .check-ja-cadastrado label {
        font-size: 11px;
        color: #17a2b8;
    }
    .check-ja-cadastrado input[type="checkbox"] {
        transform: scale(1.1);
    }
</style>
@stop

@section('content')
<div class="container-fluid">
    
    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="stat-icon bg-success text-white mr-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-value text-success">{{ count($ordensAbertas) }}</div>
                        <div class="stat-label">Liberadas p/ Receber</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="stat-icon bg-warning text-white mr-3">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="stat-value text-warning">{{ isset($ordensAguardandoPagamento) ? count($ordensAguardandoPagamento) : 0 }}</div>
                        <div class="stat-label">Aguardando Pagamento</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="stat-icon bg-info text-white mr-3">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <div class="stat-value text-info">{{ count($recebimentos) }}</div>
                        <div class="stat-label">Total Recebidos</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="stat-icon bg-primary text-white mr-3">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <div class="stat-value text-primary">{{ $recebimentos->where('data_recebimento', '>=', now()->startOfDay())->count() }}</div>
                        <div class="stat-label">Recebidos Hoje</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coluna da Esquerda: OCs para Receber -->
        <div class="col-lg-6 col-xl-5 mb-4">
            
            <div class="ocs-panel">
                <!-- Header -->
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-truck-loading mr-2"></i> Ordens de Compra</h5>
                    <span class="badge badge-light text-success">{{ count($ordensAbertas) + (isset($ordensAguardandoPagamento) ? count($ordensAguardandoPagamento) : 0) }} total</span>
                </div>
                
                <!-- Tabs -->
                <div class="ocs-tabs">
                    <div class="tab active" data-tab="liberadas">
                        <i class="fas fa-check-circle"></i> Liberadas
                        <span class="badge badge-success">{{ count($ordensAbertas) }}</span>
                    </div>
                    <div class="tab tab-warning" data-tab="aguardando">
                        <i class="fas fa-hourglass-half"></i> Aguardando
                        <span class="badge badge-warning">{{ isset($ordensAguardandoPagamento) ? count($ordensAguardandoPagamento) : 0 }}</span>
                    </div>
                </div>
                
                <!-- Busca -->
                <div class="panel-search position-relative">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control form-control-sm" id="buscaOC" placeholder="Buscar por número, fornecedor ou obra...">
                </div>
                
                <!-- Lista de OCs Liberadas -->
                <div class="panel-body" id="tabLiberadas">
                    @if(count($ordensAbertas) > 0)
                    <table class="table ocs-table">
                        <thead>
                            <tr>
                                <th>Nº OC</th>
                                <th>Fornecedor</th>
                                <th>Centro de Custo</th>
                                <th>Valor</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordensAbertas as $oc)
                            <tr class="oc-row" 
                                data-numero="{{ $oc->numero }}" 
                                data-fornecedor="{{ $oc->fornecedor ?? '' }}"
                                data-centro-custo="{{ $oc->centro_custo ?? '' }}">
                                <td class="oc-numero-cell">{{ $oc->numero }}</td>
                                <td class="oc-fornecedor-cell" title="{{ $oc->fornecedor ?? '-' }}">{{ Str::limit($oc->fornecedor ?? '-', 25) }}</td>
                                <td class="oc-cc-cell" title="{{ $oc->centro_custo ?? '-' }}">{{ Str::limit($oc->centro_custo ?? '-', 22) }}</td>
                                <td class="oc-valor-cell">R$ {{ number_format($oc->valor_total ?? 0, 2, ',', '.') }}</td>
                                <td class="text-right">
                                    <button class="btn btn-success btn-sm btn-registrar-recebimento" 
                                        data-id="{{ $oc->id }}" 
                                        data-numero="{{ $oc->numero }}" 
                                        data-fornecedor="{{ $oc->fornecedor ?? '-' }}"
                                        title="Registrar Recebimento">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="empty-state py-4">
                        <i class="fas fa-inbox" style="font-size: 32px;"></i>
                        <p>Nenhuma OC liberada</p>
                    </div>
                    @endif
                </div>
                
                <!-- Lista de OCs Aguardando Pagamento -->
                <div class="panel-body" id="tabAguardando" style="display: none;">
                    @if(isset($ordensAguardandoPagamento) && count($ordensAguardandoPagamento) > 0)
                    <table class="table ocs-table">
                        <thead>
                            <tr>
                                <th>Nº OC</th>
                                <th>Fornecedor</th>
                                <th>Centro de Custo</th>
                                <th>Valor</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordensAguardandoPagamento as $oc)
                            <tr class="oc-row aguardando" 
                                data-numero="{{ $oc->numero }}" 
                                data-fornecedor="{{ $oc->fornecedor ?? '' }}"
                                data-centro-custo="{{ $oc->centro_custo ?? '' }}">
                                <td class="oc-numero-cell">{{ $oc->numero }}</td>
                                <td class="oc-fornecedor-cell" title="{{ $oc->fornecedor ?? '-' }}">{{ Str::limit($oc->fornecedor ?? '-', 25) }}</td>
                                <td class="oc-cc-cell" title="{{ $oc->centro_custo ?? '-' }}">{{ Str::limit($oc->centro_custo ?? '-', 22) }}</td>
                                <td class="oc-valor-cell" style="color: #6c757d;">R$ {{ number_format($oc->valor_total ?? 0, 2, ',', '.') }}</td>
                                <td class="text-right">
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i></span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-3 bg-light border-top">
                        <small class="text-muted">
                            <i class="fas fa-info-circle text-warning"></i> 
                            Aguardando baixa do Financeiro.
                        </small>
                    </div>
                    @else
                    <div class="empty-state py-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 32px;"></i>
                        <p>Nenhuma OC aguardando pagamento</p>
                    </div>
                    @endif
                </div>
            </div>
            
        </div>
        
        <!-- Coluna da Direita: Histórico -->
        <div class="col-lg-6 col-xl-7">
            
            <!-- Recebimentos Recentes -->
            <div class="card main-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history text-info mr-2"></i> Histórico de Recebimentos</h5>
                    <span class="badge badge-info">{{ count($recebimentos) }} registros</span>
                </div>
                <div class="card-body p-0">
                    @if(count($recebimentos) > 0)
                    <div class="table-responsive">
                        <table class="table table-modern mb-0" id="tabelaRecebimentos">
                            <thead>
                                <tr>
                                    <th>Nº OC</th>
                                    <th>Fornecedor</th>
                                    <th>Centro de Custo</th>
                                    <th>Data Receb.</th>
                                    <th>Nota Fiscal</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recebimentos as $r)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $r->ordem_numero ?? '-' }}</strong>
                                    </td>
                                    <td>
                                        <span class="d-block">{{ Str::limit($r->fornecedor ?? '-', 30) }}</span>
                                        @if($r->observacoes)
                                        <small class="text-muted d-block"><i class="fas fa-comment"></i> {{ Str::limit($r->observacoes, 40) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="d-block" title="{{ $r->centro_custo ?? '-' }}">{{ Str::limit($r->centro_custo ?? '-', 25) }}</span>
                                    </td>
                                    <td>
                                        <span class="d-block">{{ \Carbon\Carbon::parse($r->data_recebimento)->format('d/m/Y') }}</span>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($r->data_recebimento)->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if($r->nf_numero)
                                        <span class="badge badge-light border">NF {{ $r->nf_numero }}</span>
                                        @endif
                                        @if(isset($r->arquivo_nf) && $r->arquivo_nf)
                                        <a href="{{ asset('storage/' . $r->arquivo_nf) }}" target="_blank" class="btn btn-xs btn-outline-info ml-1" title="Ver PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        @endif
                                        @if(!$r->nf_numero && !isset($r->arquivo_nf))
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-action btn-outline-info btn-ver-recebimento" 
                                            data-id="{{ $r->id }}"
                                            data-oc="{{ $r->ordem_numero ?? '-' }}"
                                            data-fornecedor="{{ $r->fornecedor ?? '-' }}"
                                            data-data="{{ \Carbon\Carbon::parse($r->data_recebimento)->format('d/m/Y') }}"
                                            data-nf="{{ $r->nf_numero ?? '-' }}"
                                            data-obs="{{ $r->observacoes ?? '' }}"
                                            data-arquivo="{{ $r->arquivo_nf ?? '' }}"
                                            title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-action btn-outline-secondary ml-1 btn-imprimir-recebimento" 
                                            data-id="{{ $r->id }}" 
                                            title="Imprimir">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        {{-- Botão de excluir desativado
                                        @if(auth()->user() && (auth()->user()->profile_id == 1 || strtolower(auth()->user()->perfil ?? '') === 'administrador'))
                                        <button class="btn btn-action btn-outline-danger ml-1 btn-excluir-recebimento" data-id="{{ $r->id }}" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                        --}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state py-5">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Nenhum recebimento registrado ainda</p>
                    </div>
                    @endif
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Modal Ver Detalhes do Recebimento -->
<div class="modal fade" id="modalVerRecebimento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-clipboard-check mr-2"></i> Detalhes do Recebimento</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block">Ordem de Compra</small>
                            <strong class="text-primary" id="verOcNumero" style="font-size: 18px;"></strong>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block">Data do Recebimento</small>
                            <strong id="verDataRecebimento"></strong>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small mb-1">Fornecedor</label>
                        <p class="mb-0 font-weight-bold" id="verFornecedor"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small mb-1">Nota Fiscal</label>
                        <p class="mb-0" id="verNotaFiscal"></p>
                    </div>
                </div>
                
                <div id="verArquivoNfContainer" class="mb-3" style="display: none;">
                    <label class="text-muted small mb-1">Arquivo da Nota Fiscal</label>
                    <div>
                        <a href="#" id="verArquivoNfLink" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-file-pdf mr-1"></i> Ver PDF da Nota Fiscal
                        </a>
                    </div>
                </div>
                
                <div id="verObservacoesContainer" class="mb-3" style="display: none;">
                    <label class="text-muted small mb-1">Observações</label>
                    <div class="bg-light rounded p-3">
                        <p class="mb-0" id="verObservacoes"></p>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="mb-3"><i class="fas fa-boxes text-info mr-2"></i> Itens Recebidos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Produto</th>
                                <th class="text-center" width="100">Quantidade</th>
                                <th class="text-center" width="120">Vinculado</th>
                            </tr>
                        </thead>
                        <tbody id="verItensRecebimento">
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Carregando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Fechar
                </button>
                <button type="button" class="btn btn-info btn-imprimir-modal" id="btnImprimirRecebimento">
                    <i class="fas fa-print mr-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Recebimento -->
<div class="modal fade" id="modalRecebimento" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title"><i class="fas fa-truck-loading mr-2"></i> Registrar Recebimento</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formRecebimento">
                    <input type="hidden" name="ordem_compra_id" id="ordem_compra_id">
                    
                    <!-- Info da OC -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3">
                                <small class="text-muted d-block">Ordem de Compra</small>
                                <strong class="text-primary" id="ocNumeroDisplay" style="font-size: 18px;"></strong>
                                <input type="hidden" id="ocNumero">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3">
                                <small class="text-muted d-block">Fornecedor</small>
                                <strong id="ocFornecedorDisplay"></strong>
                                <input type="hidden" id="ocFornecedor">
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <!-- Dados do Recebimento -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt text-muted mr-1"></i> Data de Recebimento *</label>
                                <input type="date" class="form-control" name="data_recebimento" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-file-invoice text-muted mr-1"></i> Nº da Nota Fiscal</label>
                                <input type="text" class="form-control" name="nf_numero" placeholder="Ex: 12345">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-tasks text-muted mr-1"></i> Tipo de Recebimento</label>
                                <select class="form-control" name="recebimento_parcial">
                                    <option value="0">Total</option>
                                    <option value="1">Parcial</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-paperclip text-muted mr-1"></i> Anexar Nota Fiscal</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="arquivo_nf" name="arquivo_nf" accept=".pdf,.PDF,.jpg,.jpeg,.png,.gif,.webp">
                                    <label class="custom-file-label" for="arquivo_nf" data-browse="Escolher">Selecione o arquivo...</label>
                                </div>
                                <small class="text-muted">PDF ou Imagem (JPG, PNG) - máx. 10MB</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">
                    
                    <h6 class="mb-3"><i class="fas fa-list-check text-success mr-2"></i> Conferência dos Itens</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-modern">
                            <thead>
                                <tr>
                                    <th>Item Solicitado</th>
                                    <th width="100" class="text-center" title="Quantidade e unidade conforme a OC">Qtd (OC)</th>
                                    <th width="72" class="text-center" title="Unidade no estoque após o recebimento">Und.</th>
                                    <th width="100" class="text-center">Recebido</th>
                                    <th>Vincular ao Estoque</th>
                                    <th width="80" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="itensConferencia">
                                <!-- Itens serão carregados dinamicamente -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-light border py-2 px-3 mt-3">
                        <i class="fas fa-lightbulb text-warning mr-1"></i>
                        <small><strong>Dica:</strong> Em <strong>Und.</strong> escolha a unidade (lista do sistema + unidades já usadas no estoque). Ajuste <strong>Recebido</strong> conforme a NF. Ao vincular um produto existente, a unidade escolhida substitui a do cadastro. Selecione um produto do estoque para dar entrada automática, ou crie um novo.</small>
                    </div>

                    <div class="form-group mt-3">
                        <label><i class="fas fa-comment text-muted mr-1"></i> Observações</label>
                        <textarea class="form-control" name="observacoes" rows="2" placeholder="Registre divergências ou observações importantes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmarRecebimento">
                    <i class="fas fa-check mr-1"></i> Confirmar Recebimento
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
var produtosEstoque = [];
var unidadesMedida = [];

function unidadesMedidaFallback() {
    return [
        { codigo: 'UN', label: 'UN — Unidade' },
        { codigo: 'PC', label: 'PC — Peça' },
        { codigo: 'PCT', label: 'PCT — Pacote' },
        { codigo: 'CX', label: 'CX — Caixa' },
        { codigo: 'KG', label: 'KG — Quilograma' },
        { codigo: 'LT', label: 'LT — Litro' },
        { codigo: 'MT', label: 'MT — Metro' },
        { codigo: 'M2', label: 'M² — Metro quadrado' },
        { codigo: 'M3', label: 'M³ — Metro cúbico' },
        { codigo: 'PAR', label: 'PAR — Par' },
        { codigo: 'JG', label: 'JG — Jogo' },
        { codigo: 'KIT', label: 'KIT — Kit' },
        { codigo: 'RL', label: 'RL — Rolo' },
        { codigo: 'SC', label: 'SC — Saco' },
        { codigo: 'FD', label: 'FD — Fardo' },
        { codigo: 'BD', label: 'BD — Balde' }
    ];
}

function htmlSelectUnidadeRecebimento(idx, unPadrao) {
    var uNorm = String(unPadrao != null && unPadrao !== '' ? unPadrao : 'UN').trim().toUpperCase();
    var lista = (unidadesMedida && unidadesMedida.length) ? unidadesMedida : unidadesMedidaFallback();
    var seen = {};
    var html = '<select class="form-control form-control-sm select-unidade-recebimento text-center" name="itens[' + idx + '][unidade]" style="max-width: 130px; margin: 0 auto;" title="Unidade no estoque">';
    lista.forEach(function(u) {
        var cod = String(u.codigo || '').trim().toUpperCase();
        if (!cod) {
            return;
        }
        seen[cod] = true;
        var lab = u.label || cod;
        var sel = (cod === uNorm) ? ' selected' : '';
        html += '<option value="' + escAttrRecebimento(cod) + '"' + sel + '>' + escTextRecebimento(lab) + '</option>';
    });
    if (!seen[uNorm]) {
        html += '<option value="' + escAttrRecebimento(uNorm) + '" selected>' + escTextRecebimento(uNorm) + '</option>';
    }
    html += '</select>';
    return html;
}

function escAttrRecebimento(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

function escTextRecebimento(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

$(document).ready(function() {
    // Produtos e unidades (lista padrão + distintas do cadastro de estoque)
    $.when(
        $.get('/api/estoque/produtos'),
        $.get('/api/estoque/unidades-medida')
    ).done(function(r1, r2) {
        var d1 = r1[0];
        var d2 = r2[0];
        produtosEstoque = (d1 && d1.produtos) ? d1.produtos : (d1 || []);
        unidadesMedida = (d2 && d2.unidades) ? d2.unidades : [];
    }).fail(function() {
        produtosEstoque = produtosEstoque || [];
        unidadesMedida = unidadesMedidaFallback();
    });
    
    // ========================================
    // TABS DE OCs (Liberadas / Aguardando)
    // ========================================
    $('.ocs-tabs .tab').click(function() {
        var tab = $(this).data('tab');
        
        // Atualizar tabs ativas
        $('.ocs-tabs .tab').removeClass('active');
        $(this).addClass('active');
        
        // Mostrar/esconder conteúdo
        if (tab === 'liberadas') {
            $('#tabLiberadas').show();
            $('#tabAguardando').hide();
        } else {
            $('#tabLiberadas').hide();
            $('#tabAguardando').show();
        }
    });
    
    // ========================================
    // BUSCA DE OCs
    // ========================================
    $('#buscaOC').on('input', function() {
        var termo = $(this).val().toLowerCase();
        
        // Buscar em ambas as tabs
        $('.oc-row').each(function() {
            var numero = $(this).data('numero').toString().toLowerCase();
            var fornecedor = $(this).data('fornecedor').toString().toLowerCase();
            var centroCusto = ($(this).data('centro-custo') || '').toString().toLowerCase();
            
            if (numero.includes(termo) || fornecedor.includes(termo) || centroCusto.includes(termo)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // ========================================
    // ABRIR MODAL DE RECEBIMENTO
    // ========================================
    $(document).on('click', '.btn-registrar-recebimento', function() {
        var id = $(this).data('id');
        var numero = $(this).data('numero');
        var fornecedor = $(this).data('fornecedor');
        
        $('#ordem_compra_id').val(id);
        $('#ocNumero').val(numero);
        $('#ocFornecedor').val(fornecedor);
        $('#ocNumeroDisplay').text(numero);
        $('#ocFornecedorDisplay').text(fornecedor);
        
        // Carregar itens da OC
        $.get('/api/suprimentos/ordens-compra/' + id, function(data) {
            var html = '';
            (data.itens || []).forEach(function(item, idx) {
                var unPadrao = (item.unidade != null && item.unidade !== '') ? String(item.unidade) : 'UN';
                var qtdOc = item.quantidade != null ? String(item.quantidade) : '0';
                html += '<tr data-item-idx="' + idx + '">';
                html += '<td>';
                html += '<strong>' + escTextRecebimento(item.produto) + '</strong>';
                html += '<input type="hidden" name="itens[' + idx + '][descricao]" value="' + escAttrRecebimento(item.produto) + '">';
                html += '<input type="hidden" name="itens[' + idx + '][cotacao_item_id]" value="' + (item.id || '') + '">';
                html += '</td>';
                html += '<td class="text-center">';
                html += '<small class="text-muted d-block">' + escTextRecebimento(qtdOc) + '</small>';
                html += '<span class="badge badge-light border">' + escTextRecebimento(unPadrao) + '</span>';
                html += '</td>';
                html += '<td class="text-center">';
                html += htmlSelectUnidadeRecebimento(idx, unPadrao);
                html += '</td>';
                html += '<td class="text-center">';
                html += '<input type="text" class="form-control form-control-sm text-center qtd-recebida" name="itens[' + idx + '][quantidade]" value="' + escAttrRecebimento(qtdOc) + '" style="width: 78px; margin: 0 auto;" inputmode="decimal" title="Quantidade recebida nesta unidade">';
                html += '</td>';
                html += '<td>';
                html += '<div class="d-flex align-items-center">';
                html += '<select class="form-control form-control-sm select-produto" name="itens[' + idx + '][produto_id]" style="flex: 1;">';
                html += '<option value="">-- Não vincular --</option>';
                html += '<option value="NOVO">+ Criar novo produto</option>';
                produtosEstoque.forEach(function(prod) {
                    var u = prod.unidade ? String(prod.unidade) : 'UN';
                    html += '<option value="' + prod.id + '" data-unidade="' + escAttrRecebimento(u) + '">' + escTextRecebimento(prod.nome) + ' (' + escTextRecebimento(prod.quantidade) + ' ' + escTextRecebimento(u) + ')</option>';
                });
                html += '</select>';
                html += '</div>';
                html += '<div class="mt-1 check-ja-cadastrado" style="display: none;">';
                html += '<label class="mb-0 small" style="cursor: pointer;">';
                html += '<input type="checkbox" class="mr-1 checkbox-ja-cadastrado" name="itens[' + idx + '][ja_cadastrado]" value="1">';
                html += '<span class="text-info">Já cadastrei este produto (não somar qtd)</span>';
                html += '</label>';
                html += '</div>';
                html += '</td>';
                html += '<td class="text-center"><span class="badge badge-secondary"><i class="fas fa-minus"></i></span></td>';
                html += '</tr>';
            });
            
            if (html === '') {
                html = '<tr><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-info-circle mr-1"></i> Nenhum item encontrado</td></tr>';
            }
            
            $('#itensConferencia').html(html);
        });
        
        $('#modalRecebimento').modal('show');
    });
    
    // Atualizar status quando seleciona produto
    $(document).on('change', '.select-produto', function() {
        var row = $(this).closest('tr');
        var val = $(this).val();
        var badge = row.find('td:last .badge');
        var checkDiv = row.find('.check-ja-cadastrado');
        var checkbox = row.find('.checkbox-ja-cadastrado');
        var opt = $(this).find('option:selected');
        var unEstoque = opt.data('unidade');
        
        if (val === 'NOVO') {
            badge.removeClass('badge-secondary badge-success badge-info').addClass('badge-warning').html('<i class="fas fa-plus"></i> Novo');
            checkDiv.hide();
            checkbox.prop('checked', false);
        } else if (val && val !== '') {
            if (unEstoque) {
                var u = String(unEstoque).trim().toUpperCase();
                var $su = row.find('.select-unidade-recebimento');
                if ($su.length && !$su.find('option[value="' + escAttrRecebimento(u) + '"]').length) {
                    $su.append('<option value="' + escAttrRecebimento(u) + '">' + escTextRecebimento(u) + '</option>');
                }
                if ($su.length) {
                    $su.val(u);
                }
            }
            // Produto existente selecionado - mostrar opção "já cadastrei"
            checkDiv.show();
            if (checkbox.is(':checked')) {
                badge.removeClass('badge-secondary badge-warning badge-success').addClass('badge-info').html('<i class="fas fa-check"></i> Vinc.');
            } else {
                badge.removeClass('badge-secondary badge-warning badge-info').addClass('badge-success').html('<i class="fas fa-plus-circle"></i> +Qtd');
            }
        } else {
            badge.removeClass('badge-success badge-warning badge-info').addClass('badge-secondary').html('<i class="fas fa-minus"></i>');
            checkDiv.hide();
            checkbox.prop('checked', false);
        }
    });
    
    // Atualizar badge quando marca/desmarca "já cadastrei"
    $(document).on('change', '.checkbox-ja-cadastrado', function() {
        var row = $(this).closest('tr');
        var badge = row.find('td:last .badge');
        
        if ($(this).is(':checked')) {
            badge.removeClass('badge-success').addClass('badge-info').html('<i class="fas fa-check"></i> Vinc.');
        } else {
            badge.removeClass('badge-info').addClass('badge-success').html('<i class="fas fa-plus-circle"></i> +Qtd');
        }
    });
    
    // Atualizar label do input file
    $('#arquivo_nf').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Selecione o arquivo...');
    });
    
    // ========================================
    // VER DETALHES DO RECEBIMENTO
    // ========================================
    var recebimentoAtualId = null;
    
    $(document).on('click', '.btn-ver-recebimento', function() {
        var id = $(this).data('id');
        var oc = $(this).data('oc');
        var fornecedor = $(this).data('fornecedor');
        var data = $(this).data('data');
        var nf = $(this).data('nf');
        var obs = $(this).data('obs');
        var arquivo = $(this).data('arquivo');
        
        recebimentoAtualId = id;
        
        // Preencher dados básicos
        $('#verOcNumero').text(oc);
        $('#verFornecedor').text(fornecedor);
        $('#verDataRecebimento').text(data);
        $('#verNotaFiscal').html(nf !== '-' && nf ? '<span class="badge badge-info">NF ' + nf + '</span>' : '<span class="text-muted">Não informada</span>');
        
        // Arquivo da NF
        if (arquivo) {
            $('#verArquivoNfContainer').show();
            $('#verArquivoNfLink').attr('href', '/storage/' + arquivo);
        } else {
            $('#verArquivoNfContainer').hide();
        }
        
        // Observações
        if (obs && obs.trim() !== '') {
            $('#verObservacoesContainer').show();
            $('#verObservacoes').text(obs);
        } else {
            $('#verObservacoesContainer').hide();
        }
        
        // Carregar itens do recebimento
        $('#verItensRecebimento').html('<tr><td colspan="3" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Carregando...</td></tr>');
        
        $.get('/api/suprimentos/recebimentos/' + id, function(data) {
            var html = '';
            if (data.itens && data.itens.length > 0) {
                data.itens.forEach(function(item) {
                    html += '<tr>';
                    html += '<td>' + (item.descricao || item.produto || 'Item') + '</td>';
                    html += '<td class="text-center"><span class="badge badge-secondary">' + (item.quantidade || 0) + ' ' + (item.unidade || 'UN') + '</span></td>';
                    html += '<td class="text-center">';
                    if (item.produto_nome || item.produto_id) {
                        html += '<span class="badge badge-success"><i class="fas fa-link mr-1"></i>' + (item.produto_nome || 'Vinculado') + '</span>';
                    } else {
                        html += '<span class="text-muted">-</span>';
                    }
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="3" class="text-center text-muted py-3">Nenhum item encontrado</td></tr>';
            }
            $('#verItensRecebimento').html(html);
        }).fail(function() {
            $('#verItensRecebimento').html('<tr><td colspan="3" class="text-center text-muted py-3">Erro ao carregar itens</td></tr>');
        });
        
        $('#modalVerRecebimento').modal('show');
    });
    
    // ========================================
    // IMPRIMIR RECEBIMENTO (sem abrir nova aba)
    // ========================================
    $(document).on('click', '.btn-imprimir-recebimento, #btnImprimirRecebimento', function() {
        var id = $(this).data('id') || recebimentoAtualId;
        if (id) {
            // Remover iframe anterior se existir
            $('#iframePrint').remove();
            
            // Criar iframe oculto
            var iframe = $('<iframe>', {
                id: 'iframePrint',
                src: '/suprimentos/recebimentos/' + id + '/imprimir',
                style: 'position: absolute; width: 0; height: 0; border: none; left: -9999px;'
            });
            
            $('body').append(iframe);
            
            // Aguardar carregar e imprimir
            iframe.on('load', function() {
                try {
                    this.contentWindow.print();
                } catch(e) {
                    // Fallback para nova aba se iframe falhar
                    window.open('/suprimentos/recebimentos/' + id + '/imprimir', '_blank');
                }
            });
        } else {
            toastr.warning('Selecione um recebimento para imprimir.');
        }
    });
    
    // Confirmar recebimento (com validação prévia para evitar duplicações)
    $('#btnConfirmarRecebimento').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Validando...');
        
        var formData = new FormData($('#formRecebimento')[0]);
        
        // Primeiro: validar para detectar possíveis duplicações
        $.ajax({
            url: '/api/suprimentos/recebimentos/validar',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(validacao) {
                if (validacao.success && validacao.tem_alertas) {
                    // Há alertas de possível duplicação - mostrar confirmação
                    var alertasHtml = '<div class="text-left">';
                    alertasHtml += '<p class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> <strong>Atenção!</strong> Foram detectados possíveis problemas:</p>';
                    alertasHtml += '<ul class="mb-3">';
                    validacao.alertas.forEach(function(alerta) {
                        var corBadge = alerta.tipo === 'duplicacao' ? 'danger' : 'warning';
                        alertasHtml += '<li class="mb-2">';
                        alertasHtml += '<span class="badge badge-' + corBadge + ' mr-1">' + (alerta.tipo === 'duplicacao' ? 'DUPLICAÇÃO?' : 'AVISO') + '</span>';
                        alertasHtml += '<strong>' + alerta.produto_nome + '</strong><br>';
                        alertasHtml += '<small class="text-muted">' + alerta.mensagem + '</small>';
                        alertasHtml += '</li>';
                    });
                    alertasHtml += '</ul>';
                    alertasHtml += '<p>Deseja continuar mesmo assim?</p>';
                    alertasHtml += '</div>';
                    
                    Swal.fire({
                        title: 'Possível Duplicação Detectada',
                        html: alertasHtml,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-check"></i> Sim, continuar mesmo assim',
                        cancelButtonText: 'Cancelar e revisar',
                        width: '600px'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Usuário confirmou - prosseguir com o recebimento
                            executarRecebimento(btn, formData);
                        } else {
                            btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirmar Recebimento');
                        }
                    });
                } else {
                    // Sem alertas - prosseguir diretamente
                    executarRecebimento(btn, formData);
                }
            },
            error: function(xhr) {
                // Erro na validação - prosseguir com o recebimento normal
                console.warn('Erro na validação prévia, prosseguindo...', xhr);
                executarRecebimento(btn, formData);
            }
        });
    });
    
    // Função auxiliar para executar o recebimento
    function executarRecebimento(btn, formData) {
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');
        
        $.ajax({
            url: '/api/suprimentos/recebimentos',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Recebimento Registrado!',
                        text: response.message,
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
                var msg = 'Erro ao registrar recebimento!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: msg,
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirmar Recebimento');
            }
        });
    }
    
    // Excluir recebimento (apenas admin)
    $('.btn-excluir-recebimento').click(function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Confirmar exclusão?',
            text: 'Esta ação não pode ser desfeita!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/recebimentos/' + id,
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Excluído!', response.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Erro!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Erro!', 'Erro ao excluir recebimento', 'error');
                    }
                });
            }
        });
    });
});
</script>
@stop
