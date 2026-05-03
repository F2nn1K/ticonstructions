@extends('adminlte::page')

@section('title', 'Relatório Contas a Pagar')

@push('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* Estilos normais */
    .table-responsive { max-height: 70vh; overflow-y: auto; }
    .table thead th { position: sticky; top: 0; background: #007bff; color: white; z-index: 10; }
    
    /* Cabeçalho de impressão (oculto na tela) */
    .print-header { display: none; }
    
    /* Estilos de impressão */
    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm 15mm;
        }
        
        /* Ocultar elementos desnecessários */
        .no-print,
        .main-sidebar,
        .main-header,
        .main-footer,
        .content-header,
        .card-tools,
        .card-header:not(.print-show) { 
            display: none !important; 
        }
        
        /* Reset geral */
        body {
            background: white !important;
            font-size: 10pt;
            color: #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .content-wrapper {
            margin-left: 0 !important;
            background: white !important;
        }
        
        .container-fluid {
            padding: 0 !important;
        }
        
        /* Mostrar cabeçalho de impressão */
        .print-header {
            display: block !important;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }
        
        .print-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .print-logo {
            height: 50px;
            width: auto;
        }
        
        .print-title {
            flex: 1;
            text-align: center;
        }
        
        .print-title h2 {
            margin: 0;
            font-size: 18pt;
            font-weight: bold;
            color: #007bff;
        }
        
        .print-title p {
            margin: 3px 0 0 0;
            font-size: 10pt;
            color: #666;
        }
        
        .print-date {
            text-align: right;
            font-size: 9pt;
            color: #666;
        }
        
        /* Card */
        .card {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
        }
        
        .card-body {
            padding: 0 !important;
        }
        
        /* Tabela */
        .table-responsive {
            max-height: none !important;
            overflow: visible !important;
        }
        
        .table {
            width: 100% !important;
            font-size: 8pt;
            border-collapse: collapse;
        }
        
        .table thead th {
            background-color: #007bff !important;
            color: white !important;
            padding: 4px 3px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #0056b3;
            position: static;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 4px 3px;
            border: 1px solid #ddd;
            vertical-align: middle;
            white-space: nowrap;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa !important;
        }
        
        /* Badges para impressão */
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #28a745 !important;
            color: white !important;
        }
        
        .badge-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }
        
        .badge-danger {
            background-color: #dc3545 !important;
            color: white !important;
        }
        
        .badge-info {
            background-color: #17a2b8 !important;
            color: white !important;
        }
        
        /* Resumo */
        .print-summary {
            display: block !important;
            margin-top: 20px;
            padding: 15px;
            border-top: 2px solid #007bff;
            background: #f8f9fa !important;
            page-break-inside: avoid;
        }
        
        .print-summary-content {
            display: flex;
            justify-content: flex-end;
            gap: 40px;
        }
        
        .print-summary-item {
            text-align: right;
        }
        
        .print-summary-item .label {
            font-size: 9pt;
            color: #333;
            font-weight: bold;
        }
        
        .print-summary-item .value {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
        }
        
        /* Rodapé de impressão */
        .print-footer {
            display: block !important;
            margin-top: 20px;
            padding-top: 10px;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
        }
        
        .print-footer-content {
            display: flex;
            justify-content: space-between;
        }
    }
    
    .print-summary, .print-footer { display: none; }
    
    /* Select2 customização */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
    
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
        font-size: 12px;
    }
    
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
    }
    
    .centro-custo-container {
        position: relative;
    }
    
    .centro-custo-container .badge-contador {
        position: absolute;
        top: -8px;
        right: 0;
        font-size: 11px;
    }
</style>
@endpush

@section('content_header')
<h1 class="no-print"><i class="fas fa-chart-pie mr-2"></i>Relatório Contas a Pagar</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Cabeçalho de Impressão -->
    <div class="print-header">
        <div class="print-header-content">
            <div>
                <img src="/img/logo.png" alt="Logo" class="print-logo">
            </div>
            <div class="print-title">
                <h2>Relatório de Contas a Pagar</h2>
                <p id="printPeriodo">Período: -</p>
            </div>
            <div class="print-date">
                <strong>Emitido em:</strong><br>
                <span id="printDataEmissao"></span>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card no-print">
        <div class="card-header bg-light">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <label>{{ __('Status') }}</label>
                    <select class="form-control" id="filtroStatus">
                        <option value="">{{ __('Todos') }}</option>
                        <option value="pago">Pagos</option>
                        <option value="pendente">A Pagar</option>
                        <option value="vencido">Vencido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="centro-custo-container">
                        <label>Centro de Custo <span class="badge badge-info badge-contador" id="contadorCentros">{{ __('Todos') }}</span></label>
                        <select class="form-control" id="filtroCentroCusto" multiple="multiple">
                        </select>
                        <small class="text-muted">Selecione até 7 centros</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <label>{{ __('Categoria') }}</label>
                    <select class="form-control" id="filtroCategoria">
                        <option value="">{{ __('Todas') }}</option>
                        @foreach(\DB::table('categorias_contas')->where('ativo', 1)->orderBy('nome')->get() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Data Início</label>
                    <input type="date" class="form-control" id="filtroDataInicio">
                </div>
                <div class="col-md-2">
                    <label>Data Fim</label>
                    <input type="date" class="form-control" id="filtroDataFim">
                </div>
                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-success btn-block" onclick="filtrarContas()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card">
        <div class="card-header no-print">
            <h3 class="card-title">Contas a Pagar</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-info btn-sm" onclick="exportarExcel()">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="imprimirRelatorio()">
                    <i class="fas fa-print mr-1"></i> Imprimir
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0" id="tabelaContas">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 100px;">Nº Doc</th>
                            <th>Descrição</th>
                            <th>Fornecedor</th>
                            <th>Centro de Custo</th>
                            <th style="width: 100px;">Valor Líquido</th>
                            <th style="width: 100px;">Valor Pago</th>
                            <th style="width: 90px;">Vencimento</th>
                            <th style="width: 80px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Carregando...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Resumo para impressão -->
    <div class="print-summary">
        <div class="print-summary-content">
            <div class="print-summary-item">
                <div class="label">Total de Registros</div>
                <div class="value" id="printTotalRegistros">0</div>
            </div>
            <div class="print-summary-item">
                <div class="label">Total Valor Líquido</div>
                <div class="value" id="printTotalLiquido">R$ 0,00</div>
            </div>
            <div class="print-summary-item">
                <div class="label">Total Valor Pago</div>
                <div class="value" id="printTotalPago">R$ 0,00</div>
            </div>
        </div>
    </div>
    
    <!-- Rodapé de impressão -->
    <div class="print-footer">
        <div class="print-footer-content">
            <span>SIGO - Sistema Integrado de Gestão Operacional</span>
            <span>Página 1</span>
        </div>
    </div>
</div>
@stop

@section('js')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>
<script>
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

var contasCarregadas = [];

$(document).ready(function() {
    // Datas padrão: mês atual
    var hoje = new Date();
    var primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    var ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
    
    $('#filtroDataInicio').val(primeiroDia.toISOString().split('T')[0]);
    $('#filtroDataFim').val(ultimoDia.toISOString().split('T')[0]);
    
    carregarCentrosCusto();
    filtrarContas();
});

function carregarCentrosCusto() {
    $.get('/api/centros-custo', function(data) {
        var select = $('#filtroCentroCusto');
        select.empty();
        data.forEach(function(cc) {
            select.append('<option value="' + cc.id + '">' + cc.nome + '</option>');
        });
        
        // Inicializar Select2 após carregar os dados
        select.select2({
            placeholder: 'Digite para buscar...',
            allowClear: true,
            language: 'pt-BR',
            minimumInputLength: 0,
            maximumSelectionLength: 7,
            closeOnSelect: false,
            width: '100%'
        });
        
        // Atualizar contador quando mudar
        select.on('change', function() {
            var count = $(this).val() ? $(this).val().length : 0;
            if (count === 0) {
                $('#contadorCentros').text('Todos').removeClass('badge-primary').addClass('badge-info');
            } else {
                $('#contadorCentros').text(count + ' selecionado' + (count > 1 ? 's' : '')).removeClass('badge-info').addClass('badge-primary');
            }
        });
    });
}

function filtrarContas() {
    var centrosCusto = $('#filtroCentroCusto').val();
    var params = {
        status: $('#filtroStatus').val(),
        centro_custo_ids: centrosCusto && centrosCusto.length > 0 ? centrosCusto.join(',') : '',
        categoria_id: $('#filtroCategoria').val(),
        data_inicio: $('#filtroDataInicio').val(),
        data_fim: $('#filtroDataFim').val()
    };
    
    $('#corpoTabela').html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i> Carregando...</td></tr>');
    
    $.get('/financeiro/api/contas-pagar/listar', params, function(response) {
        contasCarregadas = response.contas || [];
        renderizarTabela();
        atualizarDadosImpressao();
    }).fail(function() {
        $('#corpoTabela').html('<tr><td colspan="9" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle mr-2"></i> Erro ao carregar dados</td></tr>');
    });
}

function renderizarTabela() {
    var html = '';
    
    if (contasCarregadas.length === 0) {
        html = '<tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-info-circle mr-2"></i> Nenhuma conta encontrada</td></tr>';
    } else {
        contasCarregadas.forEach(function(conta) {
            var valorLiquido = parseFloat(conta.valor_liquido || conta.valor || 0);
            var valorPago = parseFloat(conta.valor_pago || 0);
            var statusBadge = getStatusBadge(conta.status, valorPago, valorLiquido);
            
            html += '<tr>';
            html += '<td class="text-center">' + conta.id + '</td>';
            html += '<td>' + (conta.documento || conta.numero_documento || '-') + '</td>';
            html += '<td>' + (conta.descricao || '-') + '</td>';
            html += '<td>' + (conta.fornecedor_nome || conta.fornecedor || '-') + '</td>';
            html += '<td>' + (conta.centro_custo_nome || '-') + '</td>';
            html += '<td class="text-right">' + formatarMoeda(valorLiquido) + '</td>';
            html += '<td class="text-right">' + formatarMoeda(valorPago) + '</td>';
            html += '<td class="text-center">' + formatarData(conta.data_vencimento || conta.vencimento) + '</td>';
            html += '<td class="text-center">' + statusBadge + '</td>';
            html += '</tr>';
        });
    }
    
    $('#corpoTabela').html(html);
}

function atualizarDadosImpressao() {
    // Data de emissão
    var agora = new Date();
    $('#printDataEmissao').text(agora.toLocaleDateString('pt-BR') + ' às ' + agora.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'}));
    
    // Período
    var dataInicio = $('#filtroDataInicio').val();
    var dataFim = $('#filtroDataFim').val();
    if (dataInicio && dataFim) {
        var di = new Date(dataInicio + 'T00:00:00');
        var df = new Date(dataFim + 'T00:00:00');
        $('#printPeriodo').text('Período: ' + di.toLocaleDateString('pt-BR') + ' a ' + df.toLocaleDateString('pt-BR'));
    }
    
    // Totais
    var totalLiquido = 0, totalPago = 0;
    contasCarregadas.forEach(function(conta) {
        totalLiquido += parseFloat(conta.valor_liquido || conta.valor || 0);
        totalPago += parseFloat(conta.valor_pago || 0);
    });
    
    $('#printTotalRegistros').text(contasCarregadas.length);
    $('#printTotalLiquido').text(formatarMoeda(totalLiquido));
    $('#printTotalPago').text(formatarMoeda(totalPago));
}

function getStatusBadge(status, valorPago, valorLiquido) {
    if (valorPago > 0 && valorPago < valorLiquido) {
        return '<span class="badge badge-info">Parcial</span>';
    }
    
    switch(status) {
        case 'pago': return '<span class="badge badge-success">Pago</span>';
        case 'vencido': return '<span class="badge badge-danger">Vencido</span>';
        case 'cancelado': return '<span class="badge badge-secondary">Cancelado</span>';
        default: return '<span class="badge badge-warning">Pendente</span>';
    }
}

function formatarMoeda(valor) {
    return 'R$ ' + parseFloat(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatarData(data) {
    if (!data) return '-';
    var d = new Date(data + 'T00:00:00');
    return d.toLocaleDateString('pt-BR');
}

function limparFiltros() {
    $('#filtroStatus').val('');
    $('#filtroCentroCusto').val(null).trigger('change');
    $('#filtroCategoria').val('');
    
    var hoje = new Date();
    var primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    var ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
    $('#filtroDataInicio').val(primeiroDia.toISOString().split('T')[0]);
    $('#filtroDataFim').val(ultimoDia.toISOString().split('T')[0]);
    
    filtrarContas();
}

function imprimirRelatorio() {
    atualizarDadosImpressao();
    window.print();
}

function exportarExcel() {
    var centrosCusto = $('#filtroCentroCusto').val();
    var params = new URLSearchParams({
        status: $('#filtroStatus').val(),
        centro_custo_ids: centrosCusto && centrosCusto.length > 0 ? centrosCusto.join(',') : '',
        categoria_id: $('#filtroCategoria').val(),
        data_inicio: $('#filtroDataInicio').val(),
        data_fim: $('#filtroDataFim').val(),
        export: 'excel'
    });
    
    window.location.href = '/relatorios/contas-pagar/export?' + params.toString();
}
</script>
@stop
