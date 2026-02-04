@extends('adminlte::page')

@section('title', 'Relatório de O.S.')

@section('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 customização */
    .select2-container {
        width: 100% !important;
    }
    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 14px;
    }
    .select2-results__option {
        padding: 10px 12px;
        font-size: 14px;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
    }
    /* Select2 múltipla seleção - estilo melhorado */
    .select2-container--default .select2-selection--multiple {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        min-height: 48px;
        padding: 6px 10px;
        background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .select2-container--default .select2-selection--multiple:hover {
        border-color: #007bff;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        color: white;
        border-radius: 20px;
        padding: 5px 12px;
        margin: 4px 6px 4px 0;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255,255,255,0.8);
        margin-right: 6px;
        font-size: 16px;
        font-weight: bold;
        order: -1;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
    }
    .select2-container--default .select2-selection--multiple .select2-search--inline {
        width: 100%;
        float: none;
    }
    .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
        margin-top: 6px;
        font-size: 14px;
        width: 100% !important;
        min-width: 300px;
        height: 32px;
    }
    .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
        color: #999;
    }
    /* Corrigir altura mínima quando vazio */
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        width: 100%;
        padding: 0;
    }
    /* Campo de busca maior quando não tem seleção */
    .select2-container--default .select2-selection--multiple .select2-selection__rendered li.select2-search--inline:first-child {
        width: 100%;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered li.select2-search--inline:first-child .select2-search__field {
        width: 100% !important;
    }
    
    .card-resumo {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .card-resumo:hover {
        transform: translateY(-2px);
    }
    .card-resumo.total { border-left-color: #007bff; }
    .card-resumo.aprovado { border-left-color: #28a745; }
    .card-resumo.pendente { border-left-color: #ffc107; }
    .card-resumo.pago { border-left-color: #17a2b8; }
    .card-resumo.recebido { border-left-color: #6f42c1; }
    .card-resumo.fretes { border-left-color: #fd7e14; }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,123,255,0.1);
    }
    
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .badge-status {
        font-size: 0.85em;
        padding: 5px 10px;
    }
    
    .btn-detalhes {
        padding: 2px 8px;
        font-size: 0.8em;
    }
    
    .valor-positivo { color: #28a745; }
    .valor-pendente { color: #ffc107; }
    .valor-negativo { color: #dc3545; }
    
    /* Cards de resumo menores */
    .resumo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
    }
    
    .resumo-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid;
    }
    
    .resumo-card h6 {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .resumo-card .valor {
        font-size: 1.1rem;
        font-weight: bold;
    }
    
    .resumo-card.total { border-left-color: #007bff; }
    .resumo-card.aprovado { border-left-color: #28a745; }
    .resumo-card.pendente { border-left-color: #ffc107; }
    .resumo-card.pago { border-left-color: #17a2b8; }
    .resumo-card.recebido { border-left-color: #6f42c1; }
    .resumo-card.fretes { border-left-color: #fd7e14; }
    .resumo-card.a-pagar { border-left-color: #dc3545; }
    .resumo-card.a-receber { border-left-color: #20c997; }
    .resumo-card.terceirizados { border-left-color: #6c757d; }
    
    /* Tabela responsiva */
    .table-os {
        font-size: 0.85rem;
    }
    
    .table-os th {
        white-space: nowrap;
        font-size: 0.75rem;
        text-transform: uppercase;
    }
    
    .table-os td {
        vertical-align: middle;
    }
    
    /* Modal de detalhes */
    .detalhe-section {
        margin-bottom: 20px;
    }
    
    .detalhe-section h6 {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .detalhe-section h6 .badge {
        font-size: 0.7rem;
    }
    
    .timeline-item {
        padding: 10px 15px;
        border-left: 3px solid #dee2e6;
        margin-left: 10px;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 0 5px 5px 0;
    }
    
    .timeline-item.cotacao { border-left-color: #007bff; }
    .timeline-item.oc-aprovada { border-left-color: #28a745; }
    .timeline-item.oc-pendente { border-left-color: #ffc107; }
    .timeline-item.pago { border-left-color: #17a2b8; }
    .timeline-item.recebido { border-left-color: #6f42c1; }
    
    .fluxo-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.7rem;
        background: #e9ecef;
    }
    
    .fluxo-badge.success { background: #d4edda; color: #155724; }
    .fluxo-badge.warning { background: #fff3cd; color: #856404; }
    .fluxo-badge.info { background: #d1ecf1; color: #0c5460; }
    
    /* Tooltip melhorado */
    .info-tooltip {
        cursor: help;
        color: #6c757d;
    }
    
    /* ==================== ESTILOS DE IMPRESSÃO ==================== */
    @media print {
        @page {
            size: landscape;
            margin: 10mm;
        }
        
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            background: white !important;
        }
        
        .no-print, 
        .main-header, 
        .main-sidebar, 
        .main-footer,
        .content-header,
        .card-outline,
        #estadoInicial,
        #loading,
        .btn-ver-detalhes {
            display: none !important;
        }
        
        .content-wrapper {
            margin-left: 0 !important;
            background: white !important;
        }
        
        .container-fluid {
            padding: 0 !important;
        }
        
        /* Área de impressão visível */
        .print-area {
            display: block !important;
            padding: 0;
        }
        
        /* Esconder conteúdo normal */
        #resumoCards,
        #cardResultados {
            display: none !important;
        }
    }
    
    /* Área de impressão - oculta na tela */
    .print-area {
        display: none;
    }
    
    /* Layout de impressão */
    .print-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px solid #007bff;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    
    .print-logo {
        height: 60px;
    }
    
    .print-titulo {
        text-align: right;
    }
    
    .print-titulo h1 {
        font-size: 24px;
        color: #333;
        margin: 0;
    }
    
    .print-titulo p {
        font-size: 12px;
        color: #666;
        margin: 5px 0 0 0;
    }
    
    .print-filtros {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        font-size: 11px;
    }
    
    .print-filtros span {
        margin-right: 20px;
    }
    
    .print-resumo {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 8px;
        margin-bottom: 20px;
    }
    
    .print-resumo-item {
        text-align: center;
        padding: 12px 8px;
        border-radius: 8px;
        border: 2px solid;
        background: white;
    }
    
    .print-resumo-item.azul { 
        border-color: #2196f3; 
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    }
    .print-resumo-item.verde { 
        border-color: #4caf50; 
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    }
    .print-resumo-item.amarelo { 
        border-color: #ff9800; 
        background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
    }
    .print-resumo-item.ciano { 
        border-color: #00bcd4; 
        background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
    }
    .print-resumo-item.vermelho { 
        border-color: #f44336; 
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    }
    .print-resumo-item.roxo { 
        border-color: #9c27b0; 
        background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
    }
    .print-resumo-item.teal { 
        border-color: #009688; 
        background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%);
    }
    .print-resumo-item.laranja { 
        border-color: #ff5722; 
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    }
    .print-resumo-item.cinza { 
        border-color: #6c757d; 
        background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
    }
    
    .print-resumo-valor {
        font-size: 14px;
        font-weight: 700;
        display: block;
        color: #333;
        margin-bottom: 2px;
    }
    
    .print-resumo-item.azul .print-resumo-valor { color: #1565c0; }
    .print-resumo-item.verde .print-resumo-valor { color: #2e7d32; }
    .print-resumo-item.amarelo .print-resumo-valor { color: #e65100; }
    .print-resumo-item.ciano .print-resumo-valor { color: #00838f; }
    .print-resumo-item.vermelho .print-resumo-valor { color: #c62828; }
    .print-resumo-item.roxo .print-resumo-valor { color: #7b1fa2; }
    .print-resumo-item.teal .print-resumo-valor { color: #00695c; }
    .print-resumo-item.laranja .print-resumo-valor { color: #d84315; }
    .print-resumo-item.cinza .print-resumo-valor { color: #495057; }
    
    .print-resumo-label {
        font-size: 8px;
        color: #555;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    
    .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    
    .print-table th {
        background: #007bff !important;
        color: white !important;
        padding: 8px 5px;
        text-align: left;
        font-weight: 600;
        font-size: 9px;
        text-transform: uppercase;
    }
    
    .print-table td {
        padding: 6px 5px;
        border-bottom: 1px solid #ddd;
    }
    
    .print-table tr:nth-child(even) {
        background: #f9f9f9;
    }
    
    .print-table .text-right {
        text-align: right;
    }
    
    .print-table .text-center {
        text-align: center;
    }
    
    .print-total-row td {
        background: #e9ecef !important;
        font-weight: bold;
        border-top: 2px solid #007bff;
    }
    
    .print-footer {
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid #ddd;
        font-size: 9px;
        color: #666;
        display: flex;
        justify-content: space-between;
    }
    
    .print-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: 500;
    }
    
    .print-badge.aberta { background: #ffc107; color: #000; }
    .print-badge.fechada { background: #28a745; color: #fff; }
    .print-badge.em_andamento { background: #17a2b8; color: #fff; }
    .print-badge.finalizada { background: #28a745; color: #fff; }
    .print-badge.cancelada { background: #dc3545; color: #fff; }
</style>
@stop

@section('content_header')
<h1><i class="fas fa-clipboard-list text-primary"></i> Relatório de O.S. - Fluxo Completo</h1>
<small class="text-muted">Solicitações → Cotações → Ordens de Compra → Pagamentos → Recebimentos</small>
@stop

@section('content')
<div class="container-fluid">
    <!-- Filtros -->
    <div class="card card-outline card-primary no-print">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="formFiltros">
                <!-- Linha 1: Período, Responsável, Status e Botões -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="font-weight-bold"><i class="fas fa-calendar-alt text-muted mr-1"></i> Período</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="filtroDataInicio" name="data_inicio">
                            <div class="input-group-append input-group-prepend">
                                <span class="input-group-text bg-light"><i class="fas fa-arrow-right text-muted"></i></span>
                            </div>
                            <input type="date" class="form-control" id="filtroDataFim" name="data_fim">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold"><i class="fas fa-user text-muted mr-1"></i> Responsável</label>
                        <select class="form-control" id="filtroResponsavel" name="responsavel_id">
                            <option value="">Todos</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold"><i class="fas fa-tasks text-muted mr-1"></i> Status</label>
                        <select class="form-control" id="filtroStatus" name="status">
                            <option value="">Todos</option>
                            <option value="aberta">Aberta</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="finalizada">Finalizada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-5 d-flex align-items-end justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg mr-2">
                            <i class="fas fa-search mr-1"></i> Gerar Relatório
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg" id="btnLimpar">
                            <i class="fas fa-eraser mr-1"></i> Limpar
                        </button>
                    </div>
                </div>
                
                <!-- Linha 2: Centros de Custo (múltipla seleção) -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="centro-custo-wrapper p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="font-weight-bold mb-0">
                                    <i class="fas fa-building text-primary mr-1"></i> Centros de Custo (Obras)
                                </label>
                                <div>
                                    <span class="badge badge-info mr-2" id="contadorCentros">Todos os centros</span>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" id="limparCentros">
                                        <i class="fas fa-times"></i> Limpar
                                    </a>
                                </div>
                            </div>
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-info-circle"></i> Digite para buscar e selecione até 7 centros de custo. Deixe vazio para incluir todos.
                            </small>
                            <select class="form-control" id="filtroCentroCusto" name="centro_custo_id[]" multiple="multiple">
                                @foreach($centrosCusto as $cc)
                                    <option value="{{ $cc->id }}">{{ $cc->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div id="resumoCards" style="display: none;">
        <div class="resumo-grid mb-3">
            <div class="resumo-card total">
                <h6><i class="fas fa-clipboard-list"></i> Total O.S.</h6>
                <div class="valor text-primary" id="totalOS">0</div>
            </div>
            <div class="resumo-card aprovado">
                <h6><i class="fas fa-check-circle"></i> Aprovado</h6>
                <div class="valor text-success" id="valorAprovado">R$ 0,00</div>
            </div>
            <div class="resumo-card pendente">
                <h6><i class="fas fa-clock"></i> Aguard. Aprovação</h6>
                <div class="valor text-warning" id="valorPendente">R$ 0,00</div>
            </div>
            <div class="resumo-card pago">
                <h6><i class="fas fa-money-bill-wave"></i> Pago</h6>
                <div class="valor text-info" id="valorPago">R$ 0,00</div>
            </div>
            <div class="resumo-card a-pagar">
                <h6><i class="fas fa-exclamation-triangle"></i> A Pagar</h6>
                <div class="valor text-danger" id="valorAPagar">R$ 0,00</div>
            </div>
            <div class="resumo-card recebido">
                <h6><i class="fas fa-box-open"></i> Recebido</h6>
                <div class="valor text-purple" id="valorRecebido">R$ 0,00</div>
            </div>
            <div class="resumo-card a-receber">
                <h6><i class="fas fa-truck"></i> A Receber</h6>
                <div class="valor text-teal" id="valorAReceber">R$ 0,00</div>
            </div>
            <div class="resumo-card fretes">
                <h6><i class="fas fa-truck-loading"></i> Fretes</h6>
                <div class="valor text-orange" id="valorFretes">R$ 0,00</div>
            </div>
            <div class="resumo-card terceirizados">
                <h6><i class="fas fa-hard-hat"></i> Terceirizados</h6>
                <div class="valor text-secondary" id="valorTerceirizados">R$ 0,00</div>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div class="text-center py-5" id="loading" style="display: none;">
        <div class="loading-spinner"></div>
        <p class="mt-2 text-muted">Carregando dados...</p>
    </div>

    <!-- Resultados -->
    <div class="card" id="cardResultados" style="display: none;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Ordens de Serviço</h3>
            <div class="card-tools no-print">
                {{-- DESATIVADO: Exportação Excel
                <button type="button" class="btn btn-sm btn-outline-success" id="btnExportarExcel">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                --}}
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnImprimir">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped table-os">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Nº O.S.</th>
                        <th>Data</th>
                        <th>Obra</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th class="text-center" title="Cotações / OCs">
                            <i class="fas fa-file-invoice info-tooltip" title="Cotações"></i> /
                            <i class="fas fa-shopping-cart info-tooltip" title="Ordens de Compra"></i>
                        </th>
                        <th class="text-right">Aprovado</th>
                        <th class="text-right">Pendente</th>
                        <th class="text-right">Pago</th>
                        <th class="text-right">Recebido</th>
                        <th class="text-right">Fretes</th>
                        <th class="text-right">Terceiriz.</th>
                        <th class="text-right">Total</th>
                        <th class="text-center no-print">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaResultados">
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="6" class="text-right">TOTAIS:</td>
                        <td class="text-right text-success" id="totalAprovadoTabela">R$ 0,00</td>
                        <td class="text-right text-warning" id="totalPendenteTabela">R$ 0,00</td>
                        <td class="text-right text-info" id="totalPagoTabela">R$ 0,00</td>
                        <td class="text-right text-purple" id="totalRecebidoTabela">R$ 0,00</td>
                        <td class="text-right text-orange" id="totalFretesTabela">R$ 0,00</td>
                        <td class="text-right text-secondary" id="totalTerceirizadosTabela">R$ 0,00</td>
                        <td class="text-right" id="totalGeralTabela">R$ 0,00</td>
                        <td class="no-print"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Estado Inicial -->
    <div class="card" id="estadoInicial">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Relatório de O.S.</h4>
            <p class="text-muted">Utilize os filtros acima e clique em "Gerar Relatório" para visualizar os dados.</p>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-list"></i> Detalhes da O.S. <span id="osNumero"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetalhesBody">
                <div class="text-center py-4">
                    <div class="loading-spinner"></div>
                    <p class="mt-2 text-muted">Carregando detalhes...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Área de Impressão -->
<div class="print-area" id="printArea">
    <div class="print-header">
        <img src="/img/logo.png" alt="Logo" class="print-logo">
        <div class="print-titulo">
            <h1>Relatório de Ordens de Serviço</h1>
            <p>Fluxo Completo: Cotações → OCs → Pagamentos → Recebimentos</p>
        </div>
    </div>
    
    <div class="print-filtros" id="printFiltros">
        <strong>Filtros aplicados:</strong>
        <span id="printFiltroData"></span>
        <span id="printFiltroCentros"></span>
        <span id="printFiltroStatus"></span>
    </div>
    
    <div class="print-resumo">
        <div class="print-resumo-item azul">
            <span class="print-resumo-valor" id="printTotalOS">0</span>
            <span class="print-resumo-label">Total O.S.</span>
        </div>
        <div class="print-resumo-item verde">
            <span class="print-resumo-valor" id="printAprovado">R$ 0,00</span>
            <span class="print-resumo-label">Aprovado</span>
        </div>
        <div class="print-resumo-item amarelo">
            <span class="print-resumo-valor" id="printPendente">R$ 0,00</span>
            <span class="print-resumo-label">Aguard. Aprov.</span>
        </div>
        <div class="print-resumo-item ciano">
            <span class="print-resumo-valor" id="printPago">R$ 0,00</span>
            <span class="print-resumo-label">Pago</span>
        </div>
        <div class="print-resumo-item vermelho">
            <span class="print-resumo-valor" id="printAPagar">R$ 0,00</span>
            <span class="print-resumo-label">A Pagar</span>
        </div>
        <div class="print-resumo-item roxo">
            <span class="print-resumo-valor" id="printRecebido">R$ 0,00</span>
            <span class="print-resumo-label">Recebido</span>
        </div>
        <div class="print-resumo-item teal">
            <span class="print-resumo-valor" id="printAReceber">R$ 0,00</span>
            <span class="print-resumo-label">A Receber</span>
        </div>
        <div class="print-resumo-item laranja">
            <span class="print-resumo-valor" id="printFretes">R$ 0,00</span>
            <span class="print-resumo-label">Fretes</span>
        </div>
        <div class="print-resumo-item cinza">
            <span class="print-resumo-valor" id="printTerceirizados">R$ 0,00</span>
            <span class="print-resumo-label">Terceirizados</span>
        </div>
    </div>
    
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 100px;">Nº O.S.</th>
                <th style="width: 70px;">Data</th>
                <th style="width: 130px;">Obra</th>
                <th>Descrição</th>
                <th style="width: 55px;">Status</th>
                <th style="width: 70px;" class="text-right">Aprovado</th>
                <th style="width: 70px;" class="text-right">Pendente</th>
                <th style="width: 70px;" class="text-right">Pago</th>
                <th style="width: 70px;" class="text-right">Recebido</th>
                <th style="width: 70px;" class="text-right">Fretes</th>
                <th style="width: 70px;" class="text-right">Terceiriz.</th>
                <th style="width: 75px;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody id="printTbody">
        </tbody>
        <tfoot>
            <tr class="print-total-row">
                <td colspan="5" style="text-align: right;">TOTAIS:</td>
                <td class="text-right" id="printTotalAprovado">R$ 0,00</td>
                <td class="text-right" id="printTotalPendente">R$ 0,00</td>
                <td class="text-right" id="printTotalPago">R$ 0,00</td>
                <td class="text-right" id="printTotalRecebido">R$ 0,00</td>
                <td class="text-right" id="printTotalFretes">R$ 0,00</td>
                <td class="text-right" id="printTotalTerceirizados">R$ 0,00</td>
                <td class="text-right" id="printTotalGeral">R$ 0,00</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="print-footer">
        <span>Gerado em: <span id="printDataHora"></span></span>
        <span>Sistema SIGO - Gestão Integrada</span>
        <span>Página 1 de 1</span>
    </div>
</div>
@stop

@section('js')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>
<script>
$(document).ready(function() {
    
    // Inicializar Select2 no Centro de Custo com múltipla seleção
    $('#filtroCentroCusto').select2({
        placeholder: 'Digite para buscar centros de custo...',
        allowClear: true,
        language: 'pt-BR',
        minimumInputLength: 0,
        maximumSelectionLength: 7, // Máximo de 7 seleções
        closeOnSelect: false, // Não fecha ao selecionar
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            var term = params.term.toLowerCase();
            var text = data.text.toLowerCase();
            if (text.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });
    
    // Atualizar contador de centros selecionados
    $('#filtroCentroCusto').on('change', function() {
        var count = $(this).val() ? $(this).val().length : 0;
        if (count === 0) {
            $('#contadorCentros').text('Todos os centros').removeClass('badge-primary').addClass('badge-info');
        } else {
            $('#contadorCentros').text(count + ' selecionado' + (count > 1 ? 's' : '')).removeClass('badge-info').addClass('badge-primary');
        }
    });
    
    // Limpar seleção de centros
    $('#limparCentros').on('click', function(e) {
        e.preventDefault();
        $('#filtroCentroCusto').val(null).trigger('change');
    });
    
    // Gerar relatório
    $('#formFiltros').on('submit', function(e) {
        e.preventDefault();
        gerarRelatorio();
    });
    
    // Limpar filtros
    $('#btnLimpar').click(function() {
        $('#formFiltros')[0].reset();
        // Limpar Select2
        $('#filtroCentroCusto').val(null).trigger('change');
        $('#cardResultados').hide();
        $('#resumoCards').hide();
        $('#estadoInicial').show();
    });
    
    // Imprimir
    $('#btnImprimir').click(function() {
        prepararImpressao();
        setTimeout(function() {
            window.print();
        }, 300);
    });
    
    /* DESATIVADO: Exportação Excel
    $('#btnExportarExcel').click(function() {
        exportarExcel();
    });
    */
    
    // Ver detalhes
    $(document).on('click', '.btn-ver-detalhes', function() {
        var id = $(this).data('id');
        var numero = $(this).data('numero');
        verDetalhes(id, numero);
    });
    
    function gerarRelatorio() {
        var centrosCusto = $('#filtroCentroCusto').val();
        var params = {
            centro_custo_ids: centrosCusto && centrosCusto.length > 0 ? centrosCusto.join(',') : '',
            responsavel_id: $('#filtroResponsavel').val(),
            status: $('#filtroStatus').val(),
            data_inicio: $('#filtroDataInicio').val(),
            data_fim: $('#filtroDataFim').val()
        };
        
        $('#loading').show();
        $('#cardResultados').hide();
        $('#resumoCards').hide();
        $('#estadoInicial').hide();
        
        $.get('/api/relatorios/ordem-servico', params)
            .done(function(response) {
                $('#loading').hide();
                if (response.success) {
                    renderizarResultados(response.data, response.resumo);
                } else {
                    Swal.fire('Erro', response.message || 'Erro ao gerar relatório', 'error');
                    $('#estadoInicial').show();
                }
            })
            .fail(function(xhr) {
                $('#loading').hide();
                $('#estadoInicial').show();
                Swal.fire('Erro', 'Erro ao conectar com o servidor', 'error');
            });
    }
    
    function renderizarResultados(data, resumo) {
        // Armazenar dados para impressão
        armazenarDadosRelatorio(data, resumo);
        
        // Atualizar cards de resumo
        $('#totalOS').text(resumo.total_os);
        $('#valorAprovado').text(formatarMoeda(resumo.totais.valor_aprovado));
        $('#valorPendente').text(formatarMoeda(resumo.totais.valor_pendente_aprovacao));
        $('#valorPago').text(formatarMoeda(resumo.totais.valor_pago));
        $('#valorAPagar').text(formatarMoeda(resumo.totais.valor_a_pagar));
        $('#valorRecebido').text(formatarMoeda(resumo.totais.valor_recebido));
        $('#valorAReceber').text(formatarMoeda(resumo.totais.valor_a_receber));
        $('#valorFretes').text(formatarMoeda(resumo.totais.valor_fretes));
        $('#valorTerceirizados').text(formatarMoeda(resumo.totais.valor_terceirizados));
        $('#resumoCards').show();
        
        // Renderizar tabela
        var tbody = $('#tabelaResultados');
        tbody.empty();
        
        var totais = {
            aprovado: 0,
            pendente: 0,
            pago: 0,
            recebido: 0,
            fretes: 0,
            terceirizados: 0,
            total: 0
        };
        
        if (data.length === 0) {
            tbody.append('<tr><td colspan="14" class="text-center text-muted py-4"><i class="fas fa-info-circle"></i> Nenhuma O.S. encontrada com os filtros selecionados.</td></tr>');
        } else {
            data.forEach(function(os) {
                totais.aprovado += parseFloat(os.valor_aprovado) || 0;
                totais.pendente += parseFloat(os.valor_pendente_aprovacao) || 0;
                totais.pago += parseFloat(os.valor_pago) || 0;
                totais.recebido += parseFloat(os.valor_recebido) || 0;
                totais.fretes += parseFloat(os.valor_fretes) || 0;
                totais.terceirizados += parseFloat(os.valor_terceirizados) || 0;
                totais.total += parseFloat(os.valor_total) || 0;
                
                var statusBadge = getStatusBadge(os.status);
                var dataFormatada = os.created_at ? new Date(os.created_at).toLocaleDateString('pt-BR') : '-';
                
                // Badges de fluxo
                var fluxoBadges = '';
                if (os.qtd_cotacoes > 0) {
                    fluxoBadges += `<span class="fluxo-badge info">${os.qtd_cotacoes} cot</span> `;
                }
                if (os.qtd_ocs_aprovadas > 0) {
                    fluxoBadges += `<span class="fluxo-badge success">${os.qtd_ocs_aprovadas} OC</span> `;
                }
                if (os.qtd_ocs_pendentes > 0) {
                    fluxoBadges += `<span class="fluxo-badge warning">${os.qtd_ocs_pendentes} pend</span>`;
                }
                
                tbody.append(`
                    <tr>
                        <td><strong>${os.numero || '-'}</strong></td>
                        <td>${dataFormatada}</td>
                        <td title="${os.centro_custo || ''}">${truncar(os.centro_custo, 20)}</td>
                        <td title="${os.descricao || ''}">${truncar(os.descricao, 25)}</td>
                        <td>${statusBadge}</td>
                        <td class="text-center">${fluxoBadges || '-'}</td>
                        <td class="text-right text-success">${formatarMoeda(os.valor_aprovado)}</td>
                        <td class="text-right text-warning">${formatarMoeda(os.valor_pendente_aprovacao)}</td>
                        <td class="text-right text-info">${formatarMoeda(os.valor_pago)}</td>
                        <td class="text-right text-purple">${formatarMoeda(os.valor_recebido)}</td>
                        <td class="text-right text-orange">${formatarMoeda(os.valor_fretes)}</td>
                        <td class="text-right text-secondary">${formatarMoeda(os.valor_terceirizados)}</td>
                        <td class="text-right font-weight-bold">${formatarMoeda(os.valor_total)}</td>
                        <td class="text-center no-print">
                            <button class="btn btn-sm btn-info btn-ver-detalhes" data-id="${os.id}" data-numero="${os.numero}" title="Ver Detalhes">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }
        
        // Atualizar totais do rodapé
        $('#totalAprovadoTabela').text(formatarMoeda(totais.aprovado));
        $('#totalPendenteTabela').text(formatarMoeda(totais.pendente));
        $('#totalPagoTabela').text(formatarMoeda(totais.pago));
        $('#totalRecebidoTabela').text(formatarMoeda(totais.recebido));
        $('#totalFretesTabela').text(formatarMoeda(totais.fretes));
        $('#totalTerceirizadosTabela').text(formatarMoeda(totais.terceirizados));
        $('#totalGeralTabela').text(formatarMoeda(totais.total));
        
        $('#cardResultados').show();
    }
    
    function verDetalhes(id, numero) {
        $('#osNumero').text(numero);
        $('#modalDetalhesBody').html(`
            <div class="text-center py-4">
                <div class="loading-spinner"></div>
                <p class="mt-2 text-muted">Carregando detalhes...</p>
            </div>
        `);
        $('#modalDetalhes').modal('show');
        
        $.get('/api/relatorios/ordem-servico/' + id + '/detalhes')
            .done(function(response) {
                if (response.success) {
                    renderizarDetalhes(response);
                } else {
                    $('#modalDetalhesBody').html('<div class="alert alert-danger">Erro ao carregar detalhes</div>');
                }
            })
            .fail(function() {
                $('#modalDetalhesBody').html('<div class="alert alert-danger">Erro ao conectar com o servidor</div>');
            });
    }
    
    function renderizarDetalhes(data) {
        var html = '';
        
        // Info da O.S.
        html += `
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Centro de Custo:</strong> ${data.os.centro_custo || '-'}
                </div>
                <div class="col-md-4">
                    <strong>Responsável:</strong> ${data.os.responsavel || '-'}
                </div>
                <div class="col-md-4">
                    <strong>Status:</strong> ${getStatusBadge(data.os.status)}
                </div>
            </div>
            <hr>
        `;
        
        // Cotações
        html += `<div class="detalhe-section">
            <h6><i class="fas fa-file-invoice text-primary"></i> Cotações 
                <span class="badge badge-primary">${data.cotacoes.length}</span>
            </h6>`;
        
        if (data.cotacoes.length === 0) {
            html += '<p class="text-muted ml-3">Nenhuma cotação vinculada.</p>';
        } else {
            data.cotacoes.forEach(function(cot) {
                html += `
                    <div class="timeline-item cotacao">
                        <strong>${cot.numero}</strong> - ${cot.descricao || '-'}
                        <br><small class="text-muted">
                            Solicitante: ${cot.solicitante || '-'} | 
                            Status: ${getStatusBadge(cot.status)}
                        </small>
                    </div>
                `;
            });
        }
        html += '</div>';
        
        // Ordens de Compra
        html += `<div class="detalhe-section">
            <h6><i class="fas fa-shopping-cart text-success"></i> Ordens de Compra 
                <span class="badge badge-success">${data.ordens_compra.length}</span>
            </h6>`;
        
        if (data.ordens_compra.length === 0) {
            html += '<p class="text-muted ml-3">Nenhuma ordem de compra vinculada.</p>';
        } else {
            html += `<div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Nº OC</th>
                            <th>Cotação</th>
                            <th>Fornecedor</th>
                            <th>Status</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            data.ordens_compra.forEach(function(oc) {
                var statusClass = oc.status === 'aprovada' || oc.status === 'recebida' ? 'oc-aprovada' : 
                                  oc.status === 'pendente' ? 'oc-pendente' : '';
                html += `
                    <tr class="${statusClass}">
                        <td><strong>${oc.numero}</strong></td>
                        <td>${oc.cotacao_numero || '-'}</td>
                        <td>${oc.fornecedor || '-'}</td>
                        <td>${getStatusBadge(oc.status)}</td>
                        <td class="text-right">${formatarMoeda(oc.valor_total)}</td>
                    </tr>
                `;
            });
            
            html += `</tbody></table></div>`;
        }
        html += '</div>';
        
        // Pagamentos
        html += `<div class="detalhe-section">
            <h6><i class="fas fa-money-bill-wave text-info"></i> Pagamentos (Contas a Pagar) 
                <span class="badge badge-info">${data.pagamentos.length}</span>
            </h6>`;
        
        if (data.pagamentos.length === 0) {
            html += '<p class="text-muted ml-3">Nenhum pagamento registrado.</p>';
        } else {
            html += `<div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>OC</th>
                            <th>Descrição</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th class="text-right">Valor</th>
                            <th class="text-right">Pago</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            data.pagamentos.forEach(function(pag) {
                var venc = pag.data_vencimento ? new Date(pag.data_vencimento).toLocaleDateString('pt-BR') : '-';
                html += `
                    <tr>
                        <td>${pag.oc_numero || '-'}</td>
                        <td>${pag.descricao || '-'}</td>
                        <td>${venc}</td>
                        <td>${getStatusPagamento(pag.status)}</td>
                        <td class="text-right">${formatarMoeda(pag.valor)}</td>
                        <td class="text-right">${formatarMoeda(pag.valor_pago)}</td>
                    </tr>
                `;
            });
            
            html += `</tbody></table></div>`;
        }
        html += '</div>';
        
        // Recebimentos
        html += `<div class="detalhe-section">
            <h6><i class="fas fa-box-open text-purple"></i> Recebimentos no Almoxarifado 
                <span class="badge badge-purple" style="background:#6f42c1;color:white;">${data.recebimentos.length}</span>
            </h6>`;
        
        if (data.recebimentos.length === 0) {
            html += '<p class="text-muted ml-3">Nenhum recebimento registrado.</p>';
        } else {
            html += `<div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>OC</th>
                            <th>Data Recebimento</th>
                            <th>Responsável</th>
                            <th>NF</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            data.recebimentos.forEach(function(rec) {
                var dataRec = rec.data_recebimento ? new Date(rec.data_recebimento).toLocaleDateString('pt-BR') : '-';
                html += `
                    <tr>
                        <td>${rec.oc_numero || '-'}</td>
                        <td>${dataRec}</td>
                        <td>${rec.responsavel || '-'}</td>
                        <td>${rec.nf_numero || '-'}</td>
                        <td>${rec.observacoes || '-'}</td>
                    </tr>
                `;
            });
            
            html += `</tbody></table></div>`;
        }
        html += '</div>';
        
        // Fretes
        if (data.fretes && data.fretes.length > 0) {
            html += `<div class="detalhe-section">
                <h6><i class="fas fa-truck-loading text-orange"></i> Fretes 
                    <span class="badge badge-warning">${data.fretes.length}</span>
                </h6>`;
            
            html += `<div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            var totalFretes = 0;
            data.fretes.forEach(function(frete) {
                var dataFrete = frete.created_at ? new Date(frete.created_at).toLocaleDateString('pt-BR') : '-';
                totalFretes += parseFloat(frete.valor_aprovado) || 0;
                html += `
                    <tr>
                        <td>${dataFrete}</td>
                        <td>${frete.descricao || '-'}</td>
                        <td class="text-right">${formatarMoeda(frete.valor_aprovado)}</td>
                    </tr>
                `;
            });
            
            html += `<tr class="bg-light font-weight-bold">
                        <td colspan="2" class="text-right">Total Fretes:</td>
                        <td class="text-right">${formatarMoeda(totalFretes)}</td>
                     </tr>`;
            
            html += `</tbody></table></div></div>`;
        }
        
        // Terceirizados/Prestadores de Serviço
        if (data.terceirizados && data.terceirizados.length > 0) {
            html += `<div class="detalhe-section">
                <h6><i class="fas fa-hard-hat text-secondary"></i> Terceirizados / Prestadores de Serviço 
                    <span class="badge badge-secondary">${data.terceirizados.length}</span>
                </h6>`;
            
            html += `<div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Prestador</th>
                            <th>Descrição do Serviço</th>
                            <th>Data Serviço</th>
                            <th>Status</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            var totalTerceirizados = 0;
            data.terceirizados.forEach(function(terc) {
                var dataServico = terc.data_servico ? new Date(terc.data_servico).toLocaleDateString('pt-BR') : '-';
                totalTerceirizados += parseFloat(terc.valor) || 0;
                var statusTerceirizado = getStatusTerceirizado(terc.status_pagamento);
                html += `
                    <tr>
                        <td><strong>${terc.nome_prestador || '-'}</strong></td>
                        <td>${terc.descricao_servico || '-'}</td>
                        <td>${dataServico}</td>
                        <td>${statusTerceirizado}</td>
                        <td class="text-right">${formatarMoeda(terc.valor)}</td>
                    </tr>
                `;
            });
            
            html += `<tr class="bg-light font-weight-bold">
                        <td colspan="4" class="text-right">Total Terceirizados:</td>
                        <td class="text-right">${formatarMoeda(totalTerceirizados)}</td>
                     </tr>`;
            
            html += `</tbody></table></div></div>`;
        }
        
        $('#modalDetalhesBody').html(html);
    }
    
    function getStatusTerceirizado(status) {
        var badges = {
            'aguardando_autorizacao': '<span class="badge badge-info">Aguard. Autorização</span>',
            'aguardando_pagamento': '<span class="badge badge-warning">Aguard. Pagamento</span>',
            'pendente': '<span class="badge badge-warning">Pendente</span>',
            'pago': '<span class="badge badge-success">Pago</span>'
        };
        return badges[status] || '<span class="badge badge-secondary">' + (status || '-') + '</span>';
    }
    
    function getStatusBadge(status) {
        var badges = {
            'aberta': '<span class="badge badge-warning badge-status">Aberta</span>',
            'em_andamento': '<span class="badge badge-info badge-status">Em Andamento</span>',
            'em_cotacao': '<span class="badge badge-info badge-status">Em Cotação</span>',
            'finalizada': '<span class="badge badge-success badge-status">Finalizada</span>',
            'cancelada': '<span class="badge badge-danger badge-status">Cancelada</span>',
            'pendente': '<span class="badge badge-warning badge-status">Pendente</span>',
            'aprovada': '<span class="badge badge-success badge-status">Aprovada</span>',
            'enviada': '<span class="badge badge-primary badge-status">Enviada</span>',
            'recebida': '<span class="badge badge-success badge-status">Recebida</span>',
            'recebida_parcial': '<span class="badge badge-info badge-status">Recebida Parcial</span>',
            'parcial': '<span class="badge badge-info badge-status">Parcial</span>'
        };
        return badges[status] || '<span class="badge badge-secondary badge-status">' + (status || '-') + '</span>';
    }
    
    function getStatusPagamento(status) {
        var badges = {
            'pendente': '<span class="badge badge-warning">Pendente</span>',
            'pago': '<span class="badge badge-success">Pago</span>',
            'vencido': '<span class="badge badge-danger">Vencido</span>',
            'cancelado': '<span class="badge badge-secondary">Cancelado</span>'
        };
        return badges[status] || '<span class="badge badge-secondary">' + (status || '-') + '</span>';
    }
    
    function formatarMoeda(valor) {
        var num = parseFloat(valor) || 0;
        return 'R$ ' + num.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    function truncar(texto, tamanho) {
        if (!texto) return '-';
        return texto.length > tamanho ? texto.substring(0, tamanho) + '...' : texto;
    }
    
    function exportarExcel() {
        // Exportação simples via tabela HTML
        var table = document.getElementById('cardResultados').querySelector('table');
        if (!table) return;
        
        var html = table.outerHTML;
        var blob = new Blob(['\ufeff', html], {type: 'application/vnd.ms-excel'});
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'relatorio_os_' + new Date().toISOString().split('T')[0] + '.xls';
        a.click();
    }
    
    // Variável global para armazenar dados do relatório
    var dadosRelatorio = null;
    var resumoRelatorio = null;
    
    // Atualizar dados após gerar relatório
    function armazenarDadosRelatorio(data, resumo) {
        dadosRelatorio = data;
        resumoRelatorio = resumo;
    }
    
    function prepararImpressao() {
        if (!dadosRelatorio || !resumoRelatorio) {
            Swal.fire('Atenção', 'Gere o relatório antes de imprimir.', 'warning');
            return;
        }
        
        // Data e hora atual
        var agora = new Date();
        $('#printDataHora').text(agora.toLocaleString('pt-BR'));
        
        // Filtros aplicados
        var dataInicio = $('#filtroDataInicio').val();
        var dataFim = $('#filtroDataFim').val();
        if (dataInicio || dataFim) {
            var periodo = '';
            if (dataInicio) periodo += new Date(dataInicio + 'T00:00:00').toLocaleDateString('pt-BR');
            if (dataInicio && dataFim) periodo += ' a ';
            if (dataFim) periodo += new Date(dataFim + 'T00:00:00').toLocaleDateString('pt-BR');
            $('#printFiltroData').text('Período: ' + periodo);
        } else {
            $('#printFiltroData').text('Período: Todos');
        }
        
        // Centros de custo selecionados
        var centrosSelecionados = $('#filtroCentroCusto').val();
        if (centrosSelecionados && centrosSelecionados.length > 0) {
            var nomes = [];
            centrosSelecionados.forEach(function(id) {
                var option = $('#filtroCentroCusto option[value="' + id + '"]');
                if (option.length) nomes.push(option.text());
            });
            $('#printFiltroCentros').text('Obras: ' + nomes.join(', '));
        } else {
            $('#printFiltroCentros').text('Obras: Todas');
        }
        
        // Status
        var status = $('#filtroStatus').val();
        $('#printFiltroStatus').text('Status: ' + (status ? status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ') : 'Todos'));
        
        // Cards de resumo
        $('#printTotalOS').text(resumoRelatorio.total_os);
        $('#printAprovado').text(formatarMoeda(resumoRelatorio.totais.valor_aprovado));
        $('#printPendente').text(formatarMoeda(resumoRelatorio.totais.valor_pendente_aprovacao));
        $('#printPago').text(formatarMoeda(resumoRelatorio.totais.valor_pago));
        $('#printAPagar').text(formatarMoeda(resumoRelatorio.totais.valor_a_pagar));
        $('#printRecebido').text(formatarMoeda(resumoRelatorio.totais.valor_recebido));
        $('#printAReceber').text(formatarMoeda(resumoRelatorio.totais.valor_a_receber));
        $('#printFretes').text(formatarMoeda(resumoRelatorio.totais.valor_fretes));
        $('#printTerceirizados').text(formatarMoeda(resumoRelatorio.totais.valor_terceirizados));
        
        // Tabela
        var tbody = $('#printTbody');
        tbody.empty();
        
        var totais = {
            aprovado: 0,
            pendente: 0,
            pago: 0,
            recebido: 0,
            fretes: 0,
            terceirizados: 0,
            total: 0
        };
        
        dadosRelatorio.forEach(function(os) {
            totais.aprovado += parseFloat(os.valor_aprovado) || 0;
            totais.pendente += parseFloat(os.valor_pendente_aprovacao) || 0;
            totais.pago += parseFloat(os.valor_pago) || 0;
            totais.recebido += parseFloat(os.valor_recebido) || 0;
            totais.fretes += parseFloat(os.valor_fretes) || 0;
            totais.terceirizados += parseFloat(os.valor_terceirizados) || 0;
            totais.total += parseFloat(os.valor_total) || 0;
            
            var dataFormatada = os.created_at ? new Date(os.created_at).toLocaleDateString('pt-BR') : '-';
            var statusClass = os.status || '';
            
            tbody.append(`
                <tr>
                    <td><strong>${os.numero || '-'}</strong></td>
                    <td>${dataFormatada}</td>
                    <td>${truncar(os.centro_custo, 20)}</td>
                    <td>${truncar(os.descricao, 30)}</td>
                    <td><span class="print-badge ${statusClass}">${(os.status || '-').replace('_', ' ')}</span></td>
                    <td class="text-right">${formatarMoeda(os.valor_aprovado)}</td>
                    <td class="text-right">${formatarMoeda(os.valor_pendente_aprovacao)}</td>
                    <td class="text-right">${formatarMoeda(os.valor_pago)}</td>
                    <td class="text-right">${formatarMoeda(os.valor_recebido)}</td>
                    <td class="text-right">${formatarMoeda(os.valor_fretes)}</td>
                    <td class="text-right">${formatarMoeda(os.valor_terceirizados)}</td>
                    <td class="text-right"><strong>${formatarMoeda(os.valor_total)}</strong></td>
                </tr>
            `);
        });
        
        // Totais do rodapé
        $('#printTotalAprovado').text(formatarMoeda(totais.aprovado));
        $('#printTotalPendente').text(formatarMoeda(totais.pendente));
        $('#printTotalPago').text(formatarMoeda(totais.pago));
        $('#printTotalRecebido').text(formatarMoeda(totais.recebido));
        $('#printTotalFretes').text(formatarMoeda(totais.fretes));
        $('#printTotalTerceirizados').text(formatarMoeda(totais.terceirizados));
        $('#printTotalGeral').text(formatarMoeda(totais.total));
    }
});
</script>
@stop
