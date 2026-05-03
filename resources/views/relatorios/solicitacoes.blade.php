@extends('adminlte::page')

@section('title', 'Relatório de Solicitações')

@section('content_header')
<h1><i class="fas fa-clipboard-list text-primary mr-2"></i>Relatório de Solicitações</h1>
<small class="text-muted">Visualize todas as solicitações de compras do sistema</small>
@stop

@section('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .card {
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    
    .card-header {
        border-radius: 8px 8px 0 0;
    }
    
    /* Cards de Resumo */
    .resumo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
    }
    
    .resumo-card {
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        border-left: 4px solid #007bff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .resumo-card h6 {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .resumo-card .valor {
        font-size: 1.3rem;
        font-weight: bold;
    }
    
    .resumo-card.total { border-left-color: #007bff; }
    .resumo-card.abertas { border-left-color: #17a2b8; }
    .resumo-card.finalizadas { border-left-color: #28a745; }
    .resumo-card.parciais { border-left-color: #ffc107; }
    .resumo-card.canceladas { border-left-color: #dc3545; }
    .resumo-card.valor { border-left-color: #6f42c1; }
    
    /* Tabela */
    .table-solicitacoes {
        font-size: 0.85rem;
    }
    
    .table-solicitacoes th {
        white-space: nowrap;
        font-size: 0.75rem;
        text-transform: uppercase;
        background-color: #007bff !important;
        color: white;
    }
    
    .table-solicitacoes td {
        vertical-align: middle;
    }
    
    /* Badge de Status */
    .badge-status {
        font-size: 0.7rem;
        padding: 0.4em 0.6em;
    }
    
    /* Select2 customização */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
    
    /* Loading */
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Filtros */
    .filtros-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .filtros-card label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #495057;
    }
    
    /* Cores */
    .text-purple { color: #6f42c1 !important; }
    
    /* Impressão - Paisagem */
    @page {
        size: landscape;
        margin: 10mm;
    }
    
    @media print {
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-size: 11px !important;
        }
        
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        
        .card { 
            box-shadow: none !important; 
            border: none !important;
            margin: 0 !important;
        }
        
        .card-header {
            display: none !important;
        }
        
        .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        /* Cabeçalho de impressão */
        .print-header {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #3490dc;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .print-header img {
            height: 50px;
        }
        
        .print-header .print-title {
            text-align: center;
            flex: 1;
        }
        
        .print-header .print-title h2 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .print-header .print-title p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        
        .print-header .print-date {
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        
        /* Cards de resumo para impressão */
        #resumoCards {
            margin-bottom: 15px !important;
        }
        
        #resumoCards .row {
            display: flex !important;
            flex-wrap: nowrap !important;
        }
        
        #resumoCards .col-md-3,
        #resumoCards .col-md-2 {
            flex: 1 !important;
            max-width: none !important;
            padding: 0 5px !important;
        }
        
        .resumo-card {
            padding: 8px !important;
            font-size: 10px !important;
            border: 1px solid #ddd !important;
            background: #f8f9fa !important;
        }
        
        .resumo-card h6 {
            font-size: 9px !important;
            margin-bottom: 3px !important;
        }
        
        .resumo-card .valor {
            font-size: 14px !important;
        }
        
        /* Tabela para impressão */
        .table-solicitacoes {
            font-size: 10px !important;
            width: 100% !important;
        }
        
        .table-solicitacoes thead {
            background-color: #3490dc !important;
            -webkit-print-color-adjust: exact;
        }
        
        .table-solicitacoes thead th {
            color: white !important;
            font-size: 9px !important;
            padding: 6px 4px !important;
            white-space: nowrap;
        }
        
        .table-solicitacoes tbody td {
            padding: 5px 4px !important;
            font-size: 9px !important;
            border-bottom: 1px solid #ddd;
        }
        
        .table-solicitacoes tfoot td {
            font-size: 10px !important;
            padding: 6px 4px !important;
            background-color: #e9ecef !important;
        }
        
        .badge {
            font-size: 8px !important;
            padding: 2px 5px !important;
        }
        
        /* Rodapé de impressão */
        .print-footer {
            display: block !important;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        
        /* Totais - aparece apenas no final */
        #totaisRodape {
            page-break-inside: avoid;
            break-inside: avoid;
            margin-top: 10px;
            border: 2px solid #28a745;
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
        }
        
        #totaisRodape .row {
            display: flex !important;
            align-items: center;
        }
        
        #totaisRodape strong {
            font-size: 12px !important;
        }
    }
    
    /* Esconder elementos de impressão na tela */
    .print-only {
        display: none;
    }
</style>
@stop

@section('content')
<div class="container-fluid">
    
    <!-- Cabeçalho de Impressão -->
    <div class="print-header print-only">
        <img src="{{ asset('img/logo.png') }}" alt="Logo">
        <div class="print-title">
            <h2>Relatório de Solicitações</h2>
            <p>Solicitações de Materiais e Terceirizados</p>
        </div>
        <div class="print-date">
            <strong>Data:</strong> {{ date('d/m/Y H:i') }}<br>
            <span id="printFiltros"></span>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card no-print">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <!-- Tipo -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><i class="fas fa-tags mr-1"></i> Tipo</label>
                            <select class="form-control" id="filtroTipo">
                                <option value="">{{ __('Todos') }}</option>
                                <option value="material">Materiais</option>
                                <option value="terceirizado">Serviços</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><i class="fas fa-flag mr-1"></i> Status</label>
                            <select class="form-control" id="filtroStatus">
                                <option value="">{{ __('Todos') }}</option>
                                <option value="aberta">Aberta</option>
                                <option value="finalizada">Finalizada</option>
                                <option value="rejeitada">Rejeitada</option>
                                <option value="aguardando_autorizacao">Aguard. Autorização</option>
                                <option value="aguardando_pagamento">Aguard. Pagamento</option>
                                <option value="pago">Pago</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Solicitante -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><i class="fas fa-user mr-1"></i> Solicitante</label>
                            <select class="form-control" id="filtroSolicitante">
                                <option value="">{{ __('Todos') }}</option>
                                @foreach($usuarios as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Data Início -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><i class="fas fa-calendar mr-1"></i> Data Início</label>
                            <input type="date" class="form-control" id="filtroDataInicio">
                        </div>
                    </div>
                    
                    <!-- Data Fim -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><i class="fas fa-calendar mr-1"></i> Data Fim</label>
                            <input type="date" class="form-control" id="filtroDataFim">
                        </div>
                    </div>
                    
                    <!-- Botão Gerar -->
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search mr-1"></i> Gerar Relatório
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Segunda linha: Centro de Custo e botões -->
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="mb-0">
                                    <i class="fas fa-building text-primary mr-1"></i> Centro de Custo (Obra)
                                </label>
                                <div>
                                    <span class="badge badge-info mr-2" id="contadorCentros">Todos os centros</span>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" id="limparCentros">
                                        <i class="fas fa-times"></i> Limpar
                                    </a>
                                </div>
                            </div>
                            <select class="form-control" id="filtroCentroCusto" name="centro_custo_id[]" multiple="multiple">
                                @foreach($centrosCusto as $cc)
                                    <option value="{{ $cc->id }}">{{ $cc->nome }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Selecione até 7 centros de custo. Deixe vazio para incluir todos.</small>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100 mb-0">
                            <button type="button" class="btn btn-secondary btn-block" id="btnLimpar">
                                <i class="fas fa-eraser mr-1"></i> Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div id="resumoCards" style="display: none;">
        <div class="row mb-3">
            <div class="col-md-3 col-6 mb-2">
                <div class="resumo-card total">
                    <h6><i class="fas fa-clipboard-list"></i> Total Orçamentos</h6>
                    <div class="valor text-primary" id="totalSolicitacoes">0</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="resumo-card" style="border-left-color: #17a2b8;">
                    <h6><i class="fas fa-boxes"></i> Materiais</h6>
                    <div class="valor text-info" id="totalMateriais">0</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="resumo-card" style="border-left-color: #6f42c1;">
                    <h6><i class="fas fa-hard-hat"></i> Terceirizados</h6>
                    <div class="valor text-purple" id="totalTerceirizados">0</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="resumo-card" style="border-left-color: #fd7e14;">
                    <h6><i class="fas fa-cubes"></i> Itens</h6>
                    <div class="valor text-warning" id="totalItens">0</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="resumo-card valor">
                    <h6><i class="fas fa-dollar-sign"></i> Valor Total</h6>
                    <div class="valor text-success" id="valorTotal">R$ 0,00</div>
                </div>
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
            <h3 class="card-title"><i class="fas fa-list"></i> Solicitações de Compras</h3>
            <div class="card-tools no-print">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnImprimir">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped table-solicitacoes">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th class="text-center">Tipo</th>
                        <th>Data</th>
                        <th>Solicitante</th>
                        <th>Centro de Custo</th>
                        <th>Fornecedor</th>
                        <th>O.S.</th>
                        <th class="text-center">Itens</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Valor</th>
                    </tr>
                </thead>
                <tbody id="tabelaResultados">
                </tbody>
            </table>
        </div>
        
        <!-- Totais - Aparece apenas no final (última página na impressão) -->
        <div class="card-footer bg-light" id="totaisRodape">
            <div class="row">
                <div class="col-md-8 text-right">
                    <strong>TOTAIS:</strong>
                </div>
                <div class="col-md-2 text-center">
                    <strong>Itens: <span id="totalItensTabela">0</span></strong>
                </div>
                <div class="col-md-2 text-right">
                    <strong class="text-success">Valor: <span id="totalValorTabela">R$ 0,00</span></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado Inicial -->
    <div class="card" id="estadoInicial">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Relatório de Solicitações</h4>
            <p class="text-muted">Utilize os filtros acima e clique em "Gerar Relatório" para visualizar os dados.</p>
        </div>
    </div>
    
    <!-- Rodapé de Impressão -->
    <div class="print-footer print-only">
        Sistema de Gestão - Gerado em {{ date('d/m/Y H:i') }}
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
        maximumSelectionLength: 7,
        closeOnSelect: false
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
        $('#filtroCentroCusto').val(null).trigger('change');
        $('#cardResultados').hide();
        $('#resumoCards').hide();
        $('#estadoInicial').show();
    });
    
    // Imprimir
    $('#btnImprimir').click(function() {
        // Preparar informações dos filtros para impressão
        var filtrosTexto = [];
        
        var tipo = $('#filtroTipo option:selected').text();
        if ($('#filtroTipo').val()) {
            filtrosTexto.push('Tipo: ' + tipo);
        }
        
        var status = $('#filtroStatus option:selected').text();
        if ($('#filtroStatus').val()) {
            filtrosTexto.push('Status: ' + status);
        }
        
        var solicitante = $('#filtroSolicitante option:selected').text();
        if ($('#filtroSolicitante').val()) {
            filtrosTexto.push('Solicitante: ' + solicitante);
        }
        
        var dataInicio = $('#filtroDataInicio').val();
        var dataFim = $('#filtroDataFim').val();
        if (dataInicio || dataFim) {
            var periodo = 'Período: ';
            if (dataInicio) periodo += formatarDataBR(dataInicio);
            if (dataInicio && dataFim) periodo += ' a ';
            if (dataFim) periodo += formatarDataBR(dataFim);
            filtrosTexto.push(periodo);
        }
        
        var centros = $('#filtroCentroCusto').val();
        if (centros && centros.length > 0) {
            filtrosTexto.push(centros.length + ' centro(s) selecionado(s)');
        }
        
        $('#printFiltros').html(filtrosTexto.join('<br>') || 'Todos os registros');
        
        window.print();
    });
    
    function formatarDataBR(data) {
        if (!data) return '';
        var partes = data.split('-');
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }
    
    function gerarRelatorio() {
        var centrosCusto = $('#filtroCentroCusto').val();
        var params = {
            tipo: $('#filtroTipo').val(),
            status: $('#filtroStatus').val(),
            centro_custo_ids: centrosCusto && centrosCusto.length > 0 ? centrosCusto.join(',') : '',
            solicitante_id: $('#filtroSolicitante').val(),
            data_inicio: $('#filtroDataInicio').val(),
            data_fim: $('#filtroDataFim').val()
        };
        
        $('#loading').show();
        $('#cardResultados').hide();
        $('#resumoCards').hide();
        $('#estadoInicial').hide();
        
        $.get('/api/relatorios/solicitacoes', params)
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
        // Atualizar cards de resumo
        $('#totalSolicitacoes').text(resumo.total);
        $('#totalMateriais').text(resumo.materiais || 0);
        $('#totalTerceirizados').text(resumo.terceirizados || 0);
        $('#totalItens').text(resumo.qtd_itens);
        $('#valorTotal').text(formatarMoeda(resumo.valor_total));
        $('#resumoCards').show();
        
        // Renderizar tabela
        var tbody = $('#tabelaResultados');
        tbody.empty();
        
        var totais = {
            itens: 0,
            valor: 0
        };
        
        if (data.length === 0) {
            tbody.append('<tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-info-circle"></i> Nenhuma solicitação encontrada com os filtros selecionados.</td></tr>');
        } else {
            data.forEach(function(sol) {
                totais.itens += parseInt(sol.qtd_itens) || 0;
                totais.valor += parseFloat(sol.valor_cotado) || 0;
                
                var statusBadge = getStatusBadge(sol.status, sol.tipo);
                var tipoBadge = getTipoBadge(sol.tipo);
                var dataFormatada = sol.created_at ? new Date(sol.created_at).toLocaleDateString('pt-BR') : '-';
                var fornecedor = sol.fornecedor || (sol.tipo === 'terceirizado' ? sol.descricao.split(' - ')[0] : '-');
                
                tbody.append(`
                    <tr>
                        <td><strong class="text-primary">${sol.numero || '-'}</strong></td>
                        <td class="text-center">${tipoBadge}</td>
                        <td>${dataFormatada}</td>
                        <td>${sol.solicitante || '-'}</td>
                        <td title="${sol.centro_custo || ''}">${truncar(sol.centro_custo, 18)}</td>
                        <td title="${fornecedor}">${truncar(fornecedor, 20)}</td>
                        <td>${sol.ordem_servico || '-'}</td>
                        <td class="text-center"><span class="badge badge-secondary">${sol.qtd_itens}</span></td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-right">${formatarMoeda(sol.valor_cotado)}</td>
                    </tr>
                `);
            });
        }
        
        // Atualizar totais do rodapé
        $('#totalItensTabela').text(totais.itens);
        $('#totalValorTabela').text(formatarMoeda(totais.valor));
        
        $('#cardResultados').show();
    }
    
    function getTipoBadge(tipo) {
        if (tipo === 'material') {
            return '<span class="badge badge-info" title="Solicitação de Material"><i class="fas fa-boxes"></i></span>';
        } else if (tipo === 'terceirizado') {
            return '<span class="badge badge-purple" style="background-color: #6f42c1;" title="Terceirizado/Prestador"><i class="fas fa-hard-hat"></i></span>';
        }
        return '<span class="badge badge-secondary">-</span>';
    }
    
    function getStatusBadge(status, tipo) {
        // Status para materiais
        var badgesMaterial = {
            'aberta': '<span class="badge badge-info badge-status">Aberta</span>',
            'em_cotacao': '<span class="badge badge-primary badge-status">Em Cotação</span>',
            'finalizada': '<span class="badge badge-success badge-status">Finalizada</span>',
            'parcial': '<span class="badge badge-warning badge-status">Parcial</span>',
            'cancelada': '<span class="badge badge-danger badge-status">Cancelada</span>',
            'rejeitada': '<span class="badge badge-dark badge-status">Rejeitada</span>'
        };
        
        // Status para terceirizados - com labels mais claros
        var badgesTerceirizado = {
            'aguard_autorizacao': '<span class="badge badge-warning badge-status">Pend. Aprovação</span>',
            'aguardando_autorizacao': '<span class="badge badge-warning badge-status">Pend. Aprovação</span>',
            'aguard_pagamento': '<span class="badge badge-info badge-status">Pend. Pagamento</span>',
            'aguardando_pagamento': '<span class="badge badge-info badge-status">Pend. Pagamento</span>',
            'pendente': '<span class="badge badge-secondary badge-status">Pend. Pagamento</span>',
            'pago': '<span class="badge badge-success badge-status">Pago</span>'
        };
        
        if (tipo === 'terceirizado') {
            return badgesTerceirizado[status] || '<span class="badge badge-secondary badge-status">' + (status || '-') + '</span>';
        }
        return badgesMaterial[status] || '<span class="badge badge-secondary badge-status">' + (status || '-') + '</span>';
    }
    
    function formatarMoeda(valor) {
        var num = parseFloat(valor) || 0;
        return 'R$ ' + num.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    function truncar(texto, tamanho) {
        if (!texto) return '-';
        return texto.length > tamanho ? texto.substring(0, tamanho) + '...' : texto;
    }
});
</script>
@stop
