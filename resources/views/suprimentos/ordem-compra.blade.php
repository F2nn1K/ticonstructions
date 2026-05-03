@extends('adminlte::page')

@section('title', 'Ordem de Compra')

@section('content_header')
<h1><i class="fas fa-file-contract"></i> Ordem de Compra</h1>
@stop

@section('content')
<div class="container-fluid">
    
    <!-- CARDS DE RESUMO -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="valorPendente">R$ 0,00</h3>
                    <p>Pendente de Aprovação</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="valorAprovado">R$ 0,00</h3>
                    <p>Aprovado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="valorTotal">R$ 0,00</h3>
                    <p>Total Geral</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3 id="qtdOCs">0</h3>
                    <p>Total de O.C.s</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-contract"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ORDENS DE COMPRA -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ordens de Compra</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-secondary btn-sm mr-2" id="btnImprimirOC" title="Imprimir listagem">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                {{-- <button type="button" class="btn btn-primary btn-sm" id="btnNovaOC">
                    <i class="fas fa-plus"></i> Nova OC Manual
                </button> --}}
            </div>
        </div>
        <div class="card-body">
            <!-- FILTROS -->
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="mb-1 small">Centro de Custo (Obra)</label>
                    <div class="oc-filtro-autocomplete-wrapper">
                        <input type="hidden" id="filtroCentroCusto" value="">
                        <input type="text" class="form-control form-control-sm" id="filtroCentroCustoBusca" placeholder="Digite 3 letras…" autocomplete="off">
                        <div class="oc-filtro-autocomplete-list" id="listaFiltroCentroCusto"></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="mb-1 small">Status</label>
                    <select class="form-control form-control-sm" id="filtroStatus">
                        <option value="">{{ __('Todos') }}</option>
                        <option value="pendente" selected>Pendente</option>
                        <option value="aprovada">Aprovada</option>
                        <option value="enviada">Enviada</option>
                        <option value="recebida_parcial">Rec. Parcial</option>
                        <option value="recebida">Recebida</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="mb-1 small">Fornecedor</label>
                    <div class="oc-filtro-autocomplete-wrapper">
                        <input type="hidden" id="filtroFornecedor" value="">
                        <input type="text" class="form-control form-control-sm" id="filtroFornecedorBusca" placeholder="Digite 3 letras…" autocomplete="off">
                        <div class="oc-filtro-autocomplete-list" id="listaFiltroFornecedor"></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="mb-1 small">Valor Máximo (R$)</label>
                    <input type="text" class="form-control form-control-sm" id="filtroValorMaximo" placeholder="Ex: 500,00">
                </div>
                <div class="col-md-2">
                    <label class="mb-1 small">Data Inicial</label>
                    <input type="date" class="form-control form-control-sm" id="filtroDataInicio">
                </div>
                <div class="col-md-1">
                    <label class="mb-1 small">Data Final</label>
                    <input type="date" class="form-control form-control-sm" id="filtroDataFim">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-info btn-sm mr-1" id="btnFiltrar" title="Filtrar">
                        <i class="fas fa-search"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="btnLimparFiltros" title="Limpar">
                        <i class="fas fa-eraser"></i>
                    </button>
                </div>
            </div>
            
            <!-- Loading -->
            <div id="loadingOC" class="text-center py-4" style="display: none;">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2 text-muted">Carregando...</p>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tabelaOC">
                    <thead>
                        <tr>
                            <th>Nº OC</th>
                            <th>Cotação Origem</th>
                            <th>Criado por</th>
                            <th>Centro de Custo</th>
                            <th>Produtos</th>
                            <th>Município/UF</th>
                            <th>Data</th>
                            <th>Fornecedor</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyOC">
                        <!-- Carregado via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Transformação -->
<div class="modal fade" id="modalConfirmarOC" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="fas fa-exchange-alt"></i> Transformar Cotação em OC</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-file-invoice-dollar fa-3x text-warning mb-2"></i>
                    <i class="fas fa-arrow-right fa-2x text-muted mx-3"></i>
                    <i class="fas fa-file-contract fa-3x text-success"></i>
                </div>
                
                <input type="hidden" id="cotacao_id">
                
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Cotação:</th>
                        <td id="info-cotacao-numero" class="font-weight-bold"></td>
                    </tr>
                    <tr>
                        <th>Descrição:</th>
                        <td id="info-cotacao-descricao"></td>
                    </tr>
                    <tr>
                        <th>Fornecedor:</th>
                        <td id="info-cotacao-fornecedor" class="text-success"></td>
                    </tr>
                    <tr>
                        <th>Valor:</th>
                        <td id="info-cotacao-valor" class="text-success font-weight-bold"></td>
                    </tr>
                </table>
                
                <div class="form-group">
                    <label>Prazo de Entrega *</label>
                    <input type="date" class="form-control" id="prazo_entrega_oc" required>
                </div>
                
                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea class="form-control" id="obs_oc" rows="2" placeholder="Observações opcionais..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn btn-success" id="btnConfirmarTransformar">
                    <i class="fas fa-check"></i> Gerar Ordem de Compra
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova OC Manual -->
<div class="modal fade" id="modalOC" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary py-2">
                <h5 class="modal-title text-white"><i class="fas fa-file-contract"></i> Nova Ordem de Compra Manual</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <form id="formOC">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="mb-1">Fornecedor *</label>
                                <select class="form-control form-control-sm" name="fornecedor_id" required>
                                    <option value="">Selecione...</option>
                                    @foreach($fornecedores as $f)
                                    <option value="{{ $f->id }}">{{ $f->razao_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="mb-1">Data da OC</label>
                                <input type="date" class="form-control form-control-sm" name="data_oc" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="mb-1">Prazo de Entrega *</label>
                                <input type="date" class="form-control form-control-sm" name="prazo_entrega" required>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-info mb-2">
                        <div class="card-header py-1">
                            <h6 class="mb-0"><i class="fas fa-list"></i> Itens
                                <button type="button" class="btn btn-success btn-xs float-right" id="btnAddItemOC">
                                    <i class="fas fa-plus"></i> Adicionar
                                </button>
                            </h6>
                        </div>
                        <div class="card-body p-2" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0" id="tabelaItensOC">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th width="70">Qtd</th>
                                        <th width="60">Un</th>
                                        <th width="100">Valor Unit.</th>
                                        <th width="100">Total</th>
                                        <th width="40"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="form-control form-control-sm" name="itens[0][produto]"></td>
                                        <td><input type="number" class="form-control form-control-sm item-qtd" name="itens[0][quantidade]" min="1" value="1"></td>
                                        <td><input type="text" class="form-control form-control-sm" name="itens[0][unidade]" value="UN"></td>
                                        <td><input type="text" class="form-control form-control-sm item-valor" name="itens[0][valor_unit]" placeholder="0,00"></td>
                                        <td><input type="text" class="form-control form-control-sm item-total" name="itens[0][valor_total]" readonly></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <td colspan="4" class="text-right"><strong>TOTAL:</strong></td>
                                        <td><strong id="totalOC">R$ 0,00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="mb-1">Observações</label>
                        <textarea class="form-control form-control-sm" name="observacoes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSalvarOC">
                    <i class="fas fa-save"></i> Salvar OC
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalhes da Cotação -->
<div class="modal fade" id="modalVerCotacao" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info py-2">
                <h5 class="modal-title text-white"><i class="fas fa-eye"></i> Detalhes da Cotação</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small mb-0">Nº da Cotação</label>
                        <h5 id="ver-cot-numero" class="mb-0 text-primary"></h5>
                    </div>
                    <div class="col-md-5">
                        <label class="text-muted small mb-0">Descrição</label>
                        <p id="ver-cot-descricao" class="mb-0 font-weight-bold"></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small mb-0">Status</label>
                        <p id="ver-cot-status" class="mb-0"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small mb-0">Data da Solicitação</label>
                        <p id="ver-cot-data" class="mb-0"></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small mb-0">Data Limite</label>
                        <p id="ver-cot-data-limite" class="mb-0"></p>
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
                                <th width="80">Unidade</th>
                            </tr>
                        </thead>
                        <tbody id="ver-cot-itens">
                        </tbody>
                    </table>
                </div>

                <hr>

                <h6><i class="fas fa-building text-success"></i> Fornecedores Cotados</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Fornecedor</th>
                                <th width="120">Valor</th>
                                <th width="100">Prazo</th>
                                <th width="80">Status</th>
                            </tr>
                        </thead>
                        <tbody id="ver-cot-fornecedores">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ÁREA DE IMPRESSÃO (oculta na tela) -->
<div class="print-area" id="printArea">
    <div class="print-header">
        <div class="print-logo-section">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="print-logo">
        </div>
        <div class="print-title-section">
            <h1>ORDENS DE COMPRA</h1>
            <p class="print-subtitle" id="printSubtitle"></p>
            <p class="print-date">Emitido em: <span id="printDate"></span></p>
        </div>
    </div>
    
    <div class="print-summary">
        <div class="summary-item pendente">
            <span class="label">Pendente:</span>
            <span class="value" id="printPendente">R$ 0,00</span>
        </div>
        <div class="summary-item aprovado">
            <span class="label">Aprovado:</span>
            <span class="value" id="printAprovado">R$ 0,00</span>
        </div>
        <div class="summary-item total">
            <span class="label">Total Geral:</span>
            <span class="value" id="printTotal">R$ 0,00</span>
        </div>
        <div class="summary-item qtd">
            <span class="label">Qtd O.C.s:</span>
            <span class="value" id="printQtd">0</span>
        </div>
    </div>
    
    <table class="print-table">
        <thead>
            <tr>
                <th>Nº OC</th>
                <th>Cotação</th>
                <th>Criado por</th>
                <th>Centro de Custo</th>
                <th>Produtos</th>
                <th>Município/UF</th>
                <th>Data</th>
                <th>Fornecedor</th>
                <th class="text-right">Valor Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="printTbody">
        </tbody>
    </table>
    
    <div class="print-footer">
        <p>ASC Sistemas - Gestão Empresarial</p>
    </div>
</div>
@stop

@section('css')
<style>
/* Autocomplete filtros OC (centro de custo / fornecedor) */
.oc-filtro-autocomplete-wrapper { position: relative; }
.oc-filtro-autocomplete-list {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 .2rem .2rem;
    max-height: 220px;
    overflow-y: auto;
    z-index: 1050;
    box-shadow: 0 4px 6px rgba(0,0,0,.12);
}
.oc-filtro-autocomplete-list .oc-filtro-item {
    padding: 6px 10px;
    cursor: pointer;
    font-size: .8125rem;
    border-bottom: 1px solid #f0f0f0;
}
.oc-filtro-autocomplete-list .oc-filtro-item:hover {
    background: #007bff;
    color: #fff;
}
.oc-filtro-autocomplete-list .oc-filtro-item small {
    display: block;
    opacity: .85;
    font-size: .7rem;
}
.oc-filtro-autocomplete-list .oc-filtro-item:hover small { color: #fff; }

/* Área de impressão - oculta na tela */
.print-area { display: none; }

/* ==================== ESTILOS DE IMPRESSÃO ==================== */
@media print {
    @page {
        size: A4 landscape;
        margin: 8mm;
    }

    /* Ocultar tudo exceto área de impressão */
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .no-print { display: none !important; }
    
    .main-sidebar, .main-header, .main-footer, .content-header, .preloader,
    .card, .small-box {
        display: none !important;
    }

    .print-area {
        display: block !important;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        padding: 10px;
        background: white;
    }

    /* Header de impressão */
    .print-header {
        display: flex;
        align-items: center;
        border-bottom: 3px solid #007bff;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }

    .print-logo-section {
        flex: 0 0 120px;
    }

    .print-logo {
        max-width: 100px;
        max-height: 60px;
    }

    .print-title-section {
        flex: 1;
        text-align: center;
    }

    .print-title-section h1 {
        margin: 0;
        font-size: 22px;
        color: #333;
        font-weight: bold;
    }

    .print-subtitle {
        margin: 5px 0 0 0;
        font-size: 12px;
        color: #666;
    }

    .print-date {
        margin: 3px 0 0 0;
        font-size: 10px;
        color: #888;
    }

    /* Resumo */
    .print-summary {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .summary-item {
        text-align: center;
        padding: 5px 15px;
    }

    .summary-item .label {
        display: block;
        font-size: 10px;
        color: #666;
        font-weight: bold;
    }

    .summary-item .value {
        display: block;
        font-size: 14px;
        font-weight: bold;
    }

    .summary-item.pendente .value { color: #f39c12; }
    .summary-item.aprovado .value { color: #28a745; }
    .summary-item.total .value { color: #17a2b8; }
    .summary-item.qtd .value { color: #6c757d; }

    /* Tabela de impressão */
    .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }

    .print-table th {
        background: #007bff;
        color: white;
        padding: 8px 5px;
        text-align: left;
        font-weight: bold;
        border: 1px solid #0056b3;
    }

    .print-table td {
        padding: 6px 5px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }

    .print-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .print-table .text-right {
        text-align: right;
    }

    .print-table .status-pendente { color: #f39c12; font-weight: bold; }
    .print-table .status-aprovada { color: #28a745; font-weight: bold; }
    .print-table .status-recebida { color: #17a2b8; font-weight: bold; }
    .print-table .status-cancelada { color: #dc3545; font-weight: bold; }

    /* Footer de impressão */
    .print-footer {
        margin-top: 20px;
        text-align: center;
        font-size: 9px;
        color: #999;
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    var itemIndex = 1;
    var isAdmin = {{ $isAdmin ? 'true' : 'false' }};

    // Autocomplete filtros: Centro de Custo e Fornecedor (mín. 3 letras)
    (function initOcFiltrosAutocomplete() {
        var debounceMs = 300;
        var ccTimer = null;
        var fornTimer = null;

        function hideList($list) {
            $list.hide().empty();
        }

        $('#filtroCentroCustoBusca').on('input', function() {
            var $input = $(this);
            var $list = $('#listaFiltroCentroCusto');
            var q = $input.val().trim();
            clearTimeout(ccTimer);
            $('#filtroCentroCusto').val('');
            if (q.length < 3) {
                hideList($list);
                return;
            }
            ccTimer = setTimeout(function() {
                $.get('/api/centros-custo/buscar-inicio', { termo: q })
                    .done(function(rows) {
                        $list.empty();
                        if (!rows || !rows.length) {
                            $list.append($('<div class="oc-filtro-item text-muted" style="cursor:default"></div>').text('Nenhum centro encontrado'));
                            $list.show();
                            return;
                        }
                        rows.forEach(function(r) {
                            var $it = $('<div class="oc-filtro-item"></div>').text(r.nome || '');
                            $it.on('mousedown', function(e) { e.preventDefault(); });
                            $it.on('click', function() {
                                $('#filtroCentroCusto').val(r.id);
                                $input.val(r.nome || '');
                                hideList($list);
                            });
                            $list.append($it);
                        });
                        $list.show();
                    });
            }, debounceMs);
        });

        $('#filtroFornecedorBusca').on('input', function() {
            var $input = $(this);
            var $list = $('#listaFiltroFornecedor');
            var q = $input.val().trim();
            clearTimeout(fornTimer);
            $('#filtroFornecedor').val('');
            if (q.length < 3) {
                hideList($list);
                return;
            }
            fornTimer = setTimeout(function() {
                $.get('/api/suprimentos/fornecedores/buscar', { termo: q })
                    .done(function(rows) {
                        $list.empty();
                        if (!rows || !rows.length) {
                            $list.append($('<div class="oc-filtro-item text-muted" style="cursor:default"></div>').text('Nenhum fornecedor encontrado'));
                            $list.show();
                            return;
                        }
                        rows.forEach(function(r) {
                            var label = r.razao_social || '';
                            var sub = (r.nome_fantasia && r.nome_fantasia !== label) ? r.nome_fantasia : '';
                            var $it = $('<div class="oc-filtro-item"></div>');
                            $it.append($('<span></span>').text(label));
                            if (sub) {
                                $it.append($('<small></small>').text(sub));
                            }
                            $it.on('mousedown', function(e) { e.preventDefault(); });
                            $it.on('click', function() {
                                $('#filtroFornecedor').val(r.id);
                                $input.val(label);
                                hideList($list);
                            });
                            $list.append($it);
                        });
                        $list.show();
                    });
            }, debounceMs);
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.oc-filtro-autocomplete-wrapper').length) {
                hideList($('#listaFiltroCentroCusto'));
                hideList($('#listaFiltroFornecedor'));
            }
        });
    })();
    
    // =============================================
    // CARREGAR ORDENS DE COMPRA COM FILTROS
    // =============================================
    function carregarOrdensCompra() {
        var dataInicio = $('#filtroDataInicio').val();
        var dataFim = $('#filtroDataFim').val();
        var status = $('#filtroStatus').val();
        var fornecedor = $('#filtroFornecedor').val();
        var centroCusto = $('#filtroCentroCusto').val();
        var valorMaximo = $('#filtroValorMaximo').val();
        
        // Converter valor máximo para número
        var valorMaximoNum = 0;
        if (valorMaximo) {
            valorMaximoNum = parseFloat(valorMaximo.replace(/\./g, '').replace(',', '.')) || 0;
        }
        
        $('#loadingOC').show();
        $('#tbodyOC').html('');
        
        $.ajax({
            url: '/api/suprimentos/ordens-compra/listar',
            method: 'GET',
            data: {
                data_inicio: dataInicio,
                data_fim: dataFim,
                status: status,
                fornecedor_id: fornecedor,
                centro_custo_id: centroCusto,
                valor_maximo: valorMaximoNum > 0 ? valorMaximoNum : ''
            },
            success: function(response) {
                $('#loadingOC').hide();
                
                // Calcular totais para os cards
                var totalPendente = 0;
                var totalAprovado = 0;
                var totalGeral = 0;
                var qtdOCs = 0;
                
                if (response.success && response.ordens && response.ordens.length > 0) {
                    var html = '';
                    response.ordens.forEach(function(oc) {
                        var valor = parseFloat(oc.valor_total) || 0;
                        totalGeral += valor;
                        qtdOCs++;
                        
                        if (oc.status == 'pendente') {
                            totalPendente += valor;
                        } else if (oc.status == 'aprovada' || oc.status == 'enviada' || oc.status == 'recebida_parcial' || oc.status == 'recebida') {
                            totalAprovado += valor;
                        }
                        
                        html += '<tr>';
                        html += '<td><strong>' + oc.numero + '</strong></td>';
                        html += '<td>';
                        if (oc.cotacao_numero) {
                            html += '<span class="badge badge-info">' + oc.cotacao_numero + '</span>';
                        } else if (oc.tipo_origem == 'terceiro') {
                            html += '<span class="badge badge-warning">TER</span>';
                        } else {
                            html += '<span class="text-muted">Manual</span>';
                        }
                        html += '</td>';
                        html += '<td><small class="text-muted">' + (oc.criador_nome || '—') + '</small></td>';
                        html += '<td>' + (oc.centro_custo || '-') + '</td>';
                        html += '<td title="' + (oc.produtos_resumo || '-') + '">';
                        if (oc.produtos_resumo) {
                            html += '<small>' + oc.produtos_resumo + '</small>';
                            if (oc.qtd_itens > 0) {
                                html += ' <span class="badge badge-light">' + oc.qtd_itens + '</span>';
                            }
                        } else {
                            html += '-';
                        }
                        html += '</td>';
                        html += '<td>' + (oc.municipio_uf || '-') + '</td>';
                        html += '<td>' + formatarData(oc.data_emissao) + '</td>';
                        html += '<td>' + (oc.fornecedor || '-') + '</td>';
                        html += '<td class="text-right text-success font-weight-bold">R$ ' + formatarMoeda(oc.valor_total) + '</td>';
                        html += '<td>' + getBadgeStatus(oc.status) + '</td>';
                        html += '<td class="text-nowrap">';
                        html += '<button class="btn btn-sm btn-info btn-ver-oc" data-id="' + oc.id + '" title="Ver Detalhes"><i class="fas fa-eye"></i></button> ';
                        
                        if (oc.status == 'pendente') {
                            html += '<button class="btn btn-sm btn-success btn-aprovar-oc" data-id="' + oc.id + '" title="Aprovar"><i class="fas fa-check"></i></button> ';
                            html += '<button class="btn btn-sm btn-danger btn-recusar-oc" data-id="' + oc.id + '" title="Recusar"><i class="fas fa-times"></i></button> ';
                        }
                        
                        if (oc.status == 'aprovada' && oc.status_pagamento == 'pago') {
                            html += '<a href="/suprimentos/recebimento" class="btn btn-sm btn-success" title="Registrar Recebimento"><i class="fas fa-clipboard-check"></i></a> ';
                        }
                        
                        if (isAdmin) {
                            html += '<button class="btn btn-sm btn-secondary btn-excluir-oc" data-id="' + oc.id + '" data-numero="' + oc.numero + '" title="Excluir"><i class="fas fa-trash"></i></button>';
                        }
                        
                        html += '</td>';
                        html += '</tr>';
                    });
                    $('#tbodyOC').html(html);
                } else {
                    $('#tbodyOC').html('<tr><td colspan="11" class="text-center text-muted"><i class="fas fa-info-circle"></i> Nenhuma ordem de compra encontrada.</td></tr>');
                }
                
                // Atualizar cards de resumo
                $('#valorPendente').text('R$ ' + formatarMoeda(totalPendente));
                $('#valorAprovado').text('R$ ' + formatarMoeda(totalAprovado));
                $('#valorTotal').text('R$ ' + formatarMoeda(totalGeral));
                $('#qtdOCs').text(qtdOCs);
            },
            error: function() {
                $('#loadingOC').hide();
                $('#tbodyOC').html('<tr><td colspan="11" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Erro ao carregar dados.</td></tr>');
                
                // Zerar cards em caso de erro
                $('#valorPendente').text('R$ 0,00');
                $('#valorAprovado').text('R$ 0,00');
                $('#valorTotal').text('R$ 0,00');
                $('#qtdOCs').text('0');
            }
        });
    }
    
    function formatarData(data) {
        if (!data) return '-';
        var d = new Date(data);
        return d.toLocaleDateString('pt-BR');
    }
    
    function formatarMoeda(valor) {
        return parseFloat(valor || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    
    function getBadgeStatus(status) {
        switch(status) {
            case 'pendente': return '<span class="badge badge-secondary">Pendente</span>';
            case 'aprovada': return '<span class="badge badge-primary">Aprovada</span>';
            case 'enviada': return '<span class="badge badge-info">Enviada</span>';
            case 'recebida_parcial': return '<span class="badge badge-warning">Rec. Parcial</span>';
            case 'recebida': return '<span class="badge badge-success">Recebida</span>';
            case 'cancelada': return '<span class="badge badge-danger">Cancelada</span>';
            default: return '<span class="badge badge-secondary">' + status + '</span>';
        }
    }
    
    // Filtrar ao clicar
    $('#btnFiltrar').click(function() {
        carregarOrdensCompra();
    });
    
    // Limpar filtros
    $('#btnLimparFiltros').click(function() {
        $('#filtroDataInicio').val('');
        $('#filtroDataFim').val('');
        $('#filtroStatus').val('pendente');
        $('#filtroFornecedor').val('');
        $('#filtroFornecedorBusca').val('');
        $('#filtroCentroCusto').val('');
        $('#filtroCentroCustoBusca').val('');
        $('#listaFiltroCentroCusto').hide().empty();
        $('#listaFiltroFornecedor').hide().empty();
        $('#filtroValorMaximo').val('');
        carregarOrdensCompra();
    });
    
    // Máscara para campo de valor máximo
    $('#filtroValorMaximo').on('input', function() {
        var valor = $(this).val().replace(/\D/g, '');
        if (valor) {
            valor = (parseInt(valor) / 100).toFixed(2);
            valor = valor.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        $(this).val(valor);
    });
    
    // =============================================
    // IMPRIMIR LISTAGEM
    // =============================================
    $('#btnImprimirOC').click(function() {
        // Verificar se há dados para imprimir
        var rows = $('#tbodyOC tr');
        if (rows.length === 0 || (rows.length === 1 && rows.first().find('td').length === 1)) {
            Swal.fire('Atenção', 'Não há dados para imprimir.', 'warning');
            return;
        }
        
        // Montar subtítulo com filtros aplicados
        var filtros = [];
        if ($('#filtroStatus').val()) {
            var statusText = $('#filtroStatus option:selected').text();
            filtros.push('Status: ' + statusText);
        }
        if ($('#filtroCentroCusto').val()) {
            var ccText = ($('#filtroCentroCustoBusca').val() || '').trim();
            filtros.push('Centro de Custo: ' + ccText.substring(0, 40) + (ccText.length > 40 ? '...' : ''));
        }
        if ($('#filtroFornecedor').val()) {
            var fornText = ($('#filtroFornecedorBusca').val() || '').trim();
            filtros.push('Fornecedor: ' + fornText.substring(0, 35) + (fornText.length > 35 ? '...' : ''));
        }
        if ($('#filtroValorMaximo').val()) {
            filtros.push('Valor até: R$ ' + $('#filtroValorMaximo').val());
        }
        if ($('#filtroDataInicio').val() || $('#filtroDataFim').val()) {
            var periodo = 'Período: ';
            if ($('#filtroDataInicio').val()) {
                periodo += formatarDataPrint($('#filtroDataInicio').val());
            }
            periodo += ' a ';
            if ($('#filtroDataFim').val()) {
                periodo += formatarDataPrint($('#filtroDataFim').val());
            }
            filtros.push(periodo);
        }
        
        $('#printSubtitle').text(filtros.length > 0 ? filtros.join(' | ') : 'Todas as Ordens de Compra');
        $('#printDate').text(new Date().toLocaleString('pt-BR'));
        
        // Copiar valores dos cards
        $('#printPendente').text($('#valorPendente').text());
        $('#printAprovado').text($('#valorAprovado').text());
        $('#printTotal').text($('#valorTotal').text());
        $('#printQtd').text($('#qtdOCs').text());
        
        // Copiar dados da tabela
        var printHtml = '';
        rows.each(function() {
            var cells = $(this).find('td');
            if (cells.length > 1) { // Ignorar linha de "nenhum registro"
                var status = $(cells[9]).text().trim();
                var statusClass = '';
                if (status.toLowerCase().indexOf('pendente') !== -1) statusClass = 'status-pendente';
                else if (status.toLowerCase().indexOf('aprovada') !== -1) statusClass = 'status-aprovada';
                else if (status.toLowerCase().indexOf('recebida') !== -1) statusClass = 'status-recebida';
                else if (status.toLowerCase().indexOf('cancelada') !== -1) statusClass = 'status-cancelada';
                
                printHtml += '<tr>';
                printHtml += '<td>' + $(cells[0]).text() + '</td>'; // Nº OC
                printHtml += '<td>' + $(cells[1]).text() + '</td>'; // Cotação
                printHtml += '<td>' + $(cells[2]).text() + '</td>'; // Criado por
                printHtml += '<td>' + $(cells[3]).text() + '</td>'; // Centro de Custo
                printHtml += '<td>' + $(cells[4]).text() + '</td>'; // Produtos
                printHtml += '<td>' + $(cells[5]).text() + '</td>'; // Município/UF
                printHtml += '<td>' + $(cells[6]).text() + '</td>'; // Data
                printHtml += '<td>' + $(cells[7]).text() + '</td>'; // Fornecedor
                printHtml += '<td class="text-right">' + $(cells[8]).text() + '</td>'; // Valor
                printHtml += '<td class="' + statusClass + '">' + status + '</td>'; // Status
                printHtml += '</tr>';
            }
        });
        $('#printTbody').html(printHtml);
        
        // Imprimir
        window.print();
    });
    
    function formatarDataPrint(data) {
        if (!data) return '';
        var parts = data.split('-');
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }
    
    // Carregar ao iniciar (com status pendente por padrão)
    carregarOrdensCompra();
    
    // =============================================
    // VER DETALHES DA COTAÇÃO
    // =============================================
    $(document).on('click', '.btn-ver-cotacao', function() {
        var id = $(this).data('id');
        
        $.get('/api/suprimentos/cotacoes/' + id, function(data) {
            $('#ver-cot-numero').text(data.cotacao.numero);
            $('#ver-cot-descricao').text(data.cotacao.descricao);
            
            var statusBadge = '';
            switch(data.cotacao.status) {
                case 'aberta': statusBadge = '<span class="badge badge-warning">Aberta</span>'; break;
                case 'finalizada': statusBadge = '<span class="badge badge-success">Finalizada</span>'; break;
                case 'cancelada': statusBadge = '<span class="badge badge-danger">Cancelada</span>'; break;
                default: statusBadge = '<span class="badge badge-secondary">' + data.cotacao.status + '</span>';
            }
            $('#ver-cot-status').html(statusBadge);
            
            if (data.cotacao.data_solicitacao) {
                var dataSol = new Date(data.cotacao.data_solicitacao);
                $('#ver-cot-data').text(dataSol.toLocaleDateString('pt-BR'));
            }
            if (data.cotacao.data_limite) {
                var dataLim = new Date(data.cotacao.data_limite);
                $('#ver-cot-data-limite').text(dataLim.toLocaleDateString('pt-BR'));
            }
            
            // Itens
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
            $('#ver-cot-itens').html(itensHtml);
            
            // Fornecedores
            var fornecedoresHtml = '';
            if (data.fornecedores && data.fornecedores.length > 0) {
                data.fornecedores.forEach(function(f) {
                    var isVencedor = data.cotacao.fornecedor_vencedor_id == f.fornecedor_id;
                    fornecedoresHtml += '<tr class="' + (isVencedor ? 'table-success' : '') + '">';
                    fornecedoresHtml += '<td>' + (f.razao_social || 'Fornecedor #' + f.fornecedor_id);
                    if (isVencedor) {
                        fornecedoresHtml += ' <i class="fas fa-trophy text-warning"></i>';
                    }
                    fornecedoresHtml += '</td>';
                    fornecedoresHtml += '<td class="text-right">R$ ' + parseFloat(f.valor_total || 0).toFixed(2).replace('.', ',') + '</td>';
                    fornecedoresHtml += '<td class="text-center">' + (f.prazo_entrega || '-') + ' dias</td>';
                    fornecedoresHtml += '<td class="text-center">';
                    if (isVencedor) {
                        fornecedoresHtml += '<span class="badge badge-success">Vencedor</span>';
                    } else {
                        fornecedoresHtml += '<span class="badge badge-secondary">Cotado</span>';
                    }
                    fornecedoresHtml += '</td>';
                    fornecedoresHtml += '</tr>';
                });
            } else {
                fornecedoresHtml = '<tr><td colspan="4" class="text-center text-muted">Nenhum fornecedor</td></tr>';
            }
            $('#ver-cot-fornecedores').html(fornecedoresHtml);
            
            $('#modalVerCotacao').modal('show');
        }).fail(function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Não foi possível carregar os detalhes da cotação.',
                confirmButtonColor: '#dc3545'
            });
        });
    });
    
    // =============================================
    // TRANSFORMAR COTAÇÃO EM OC
    // =============================================
    $('.btn-transformar-oc').click(function() {
        var id = $(this).data('id');
        var numero = $(this).data('numero');
        var descricao = $(this).data('descricao');
        var fornecedor = $(this).data('fornecedor');
        var fornecedorId = $(this).data('fornecedor-id');
        var valor = $(this).data('valor');
        var prazo = $(this).data('prazo');
        
        $('#cotacao_id').val(id);
        $('#info-cotacao-numero').text(numero);
        $('#info-cotacao-descricao').text(descricao);
        $('#info-cotacao-fornecedor').text(fornecedor || '-');
        $('#info-cotacao-valor').text(valor ? 'R$ ' + parseFloat(valor).toFixed(2).replace('.', ',') : '-');
        
        // Definir prazo padrão (hoje + prazo em dias)
        if (prazo) {
            var hoje = new Date();
            hoje.setDate(hoje.getDate() + parseInt(prazo));
            $('#prazo_entrega_oc').val(hoje.toISOString().split('T')[0]);
        } else {
            var semana = new Date();
            semana.setDate(semana.getDate() + 7);
            $('#prazo_entrega_oc').val(semana.toISOString().split('T')[0]);
        }
        
        $('#modalConfirmarOC').modal('show');
    });
    
    // Confirmar transformação
    $('#btnConfirmarTransformar').click(function() {
        var btn = $(this);
        var cotacaoId = $('#cotacao_id').val();
        var prazo = $('#prazo_entrega_oc').val();
        var obs = $('#obs_oc').val();
        
        if (!prazo) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Informe o prazo de entrega!',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Gerando...');
        
        $.ajax({
            url: '/api/suprimentos/cotacoes/' + cotacaoId + '/gerar-oc',
            method: 'POST',
            data: {
                prazo_entrega: prazo,
                observacoes: obs
            },
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'OC Gerada!',
                        html: 'Ordem de Compra <strong>' + response.numero + '</strong> criada com sucesso!',
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
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao gerar OC!';
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: msg,
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-check"></i> Gerar Ordem de Compra');
            }
        });
    });
    
    // =============================================
    // OC MANUAL
    // =============================================
    $('#btnNovaOC').click(function() {
        $('#formOC')[0].reset();
        $('#modalOC').modal('show');
    });
    
    // Adicionar item
    $('#btnAddItemOC').click(function() {
        var newRow = `<tr>
            <td><input type="text" class="form-control form-control-sm" name="itens[${itemIndex}][produto]"></td>
            <td><input type="number" class="form-control form-control-sm item-qtd" name="itens[${itemIndex}][quantidade]" min="1" value="1"></td>
            <td><input type="text" class="form-control form-control-sm" name="itens[${itemIndex}][unidade]" value="UN"></td>
            <td><input type="text" class="form-control form-control-sm item-valor" name="itens[${itemIndex}][valor_unit]" placeholder="0,00"></td>
            <td><input type="text" class="form-control form-control-sm item-total" name="itens[${itemIndex}][valor_total]" readonly></td>
            <td><button type="button" class="btn btn-danger btn-xs btn-remove-item"><i class="fas fa-times"></i></button></td>
        </tr>`;
        $('#tabelaItensOC tbody').append(newRow);
        itemIndex++;
    });
    
    // Remover item
    $(document).on('click', '.btn-remove-item', function() {
        $(this).closest('tr').remove();
        calcularTotal();
    });
    
    // Calcular valor total do item
    $(document).on('input', '.item-qtd, .item-valor', function() {
        var row = $(this).closest('tr');
        var qtd = parseFloat(row.find('.item-qtd').val()) || 0;
        var valor = parseFloat(row.find('.item-valor').val().replace(',', '.')) || 0;
        var total = qtd * valor;
        row.find('.item-total').val(total.toFixed(2).replace('.', ','));
        calcularTotal();
    });
    
    function calcularTotal() {
        var total = 0;
        $('.item-total').each(function() {
            var val = parseFloat($(this).val().replace(',', '.')) || 0;
            total += val;
        });
        $('#totalOC').text('R$ ' + total.toFixed(2).replace('.', ','));
    }
    
    // Salvar OC Manual
    $('#btnSalvarOC').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        
        $.ajax({
            url: '/api/suprimentos/ordens-compra',
            method: 'POST',
            data: $('#formOC').serialize(),
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        html: response.message + '<br><strong>Número: ' + response.numero + '</strong>',
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
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao salvar OC!',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar OC');
            }
        });
    });
    
    // Ver detalhes da OC
    $(document).on('click', '.btn-ver-oc', function() {
        var id = $(this).data('id');
        $.get('/api/suprimentos/ordens-compra/' + id, function(data) {
            var itensHtml = '<table class="table table-sm table-bordered mt-3"><thead class="thead-light"><tr><th style="background:#0d6efd;color:#fff;">PRODUTO</th><th style="background:#0d6efd;color:#fff;">QTD</th><th style="background:#0d6efd;color:#fff;">VALOR</th></tr></thead><tbody>';
            if (data.itens && data.itens.length > 0) {
                data.itens.forEach(function(item) {
                    itensHtml += '<tr><td>' + item.produto + '</td><td>' + item.quantidade + ' ' + (item.unidade || 'UN') + '</td><td>R$ ' + parseFloat(item.valor_total || 0).toFixed(2).replace('.', ',') + '</td></tr>';
                });
            } else {
                itensHtml += '<tr><td colspan="3" class="text-center text-muted">Sem itens detalhados</td></tr>';
            }
            itensHtml += '</tbody></table>';
            
            // Link do orçamento PDF
            var orcamentoHtml = '';
            if (data.arquivo_orcamento) {
                orcamentoHtml = '<p><strong>Orçamento:</strong> <a href="/storage/' + data.arquivo_orcamento + '" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf"></i> Ver PDF</a></p>';
            }
            
            // Forma de pagamento
            var pagamentoHtml = '';
            if (data.condicao_pagamento) {
                var pagLabels = {
                    'pix': '<span class="badge badge-success">PIX</span>',
                    'boleto': '<span class="badge badge-warning">Boleto</span>',
                    'credito': '<span class="badge badge-info">Crédito</span>',
                    'debito': '<span class="badge badge-primary">Débito</span>',
                    'dinheiro': '<span class="badge badge-secondary">Dinheiro</span>',
                    'transferencia': '<span class="badge badge-dark">Transferência</span>'
                };
                var pagLabel = pagLabels[data.condicao_pagamento] || data.condicao_pagamento;
                pagamentoHtml = '<p><strong>Forma de Pagamento:</strong> ' + pagLabel + '</p>';
            }
            
            // Observação (PIX, dados bancários, etc)
            var observacaoHtml = '';
            if (data.observacao) {
                observacaoHtml = '<p><strong>Observação:</strong> <span class="text-info">' + data.observacao + '</span></p>';
            }
            
            Swal.fire({
                title: '<i class="fas fa-file-invoice text-primary"></i> ' + data.ordem.numero,
                html: '<div class="text-left">' +
                    '<p><strong>Fornecedor:</strong> ' + (data.ordem.fornecedor || '-') + '</p>' +
                    '<p><strong>Data:</strong> ' + (data.ordem.data_emissao || '-') + '</p>' +
                    '<p><strong>Valor Total:</strong> R$ ' + parseFloat(data.ordem.valor_total || 0).toFixed(2).replace('.', ',') + '</p>' +
                    pagamentoHtml +
                    observacaoHtml +
                    (data.ordem.cotacao_id ? '<p><strong>Origem:</strong> Cotação #' + data.ordem.cotacao_id + '</p>' : '') +
                    orcamentoHtml +
                    '<hr><h6>Itens:</h6>' + itensHtml +
                    '</div>',
                width: 600,
                confirmButtonColor: '#007bff'
            });
        });
    });
    
    // =============================================
    // APROVAR ORDEM DE COMPRA
    // =============================================
    $(document).on('click', '.btn-aprovar-oc', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Aprovar Ordem de Compra',
            text: 'Deseja aprovar esta O.C.? Uma conta a pagar será criada no Financeiro.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, Aprovar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/ordens-compra/' + id + '/aprovar',
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Aprovado!',
                                text: response.message,
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao aprovar O.C.!';
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
    
    // =============================================
    // RECUSAR ORDEM DE COMPRA
    // =============================================
    $(document).on('click', '.btn-recusar-oc', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Recusar Ordem de Compra',
            text: 'Deseja recusar/cancelar esta O.C.? Esta ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, Recusar',
            cancelButtonText: 'Voltar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/ordens-compra/' + id + '/recusar',
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Recusado!',
                                text: response.message,
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao recusar O.C.!';
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
    
    // =============================================
    // EXCLUIR ORDEM DE COMPRA (APENAS ADMIN)
    // =============================================
    $(document).on('click', '.btn-excluir-oc', function() {
        var id = $(this).data('id');
        var numero = $(this).data('numero');
        
        Swal.fire({
            title: 'Excluir Ordem de Compra',
            html: '<p>Tem certeza que deseja <strong>EXCLUIR PERMANENTEMENTE</strong> a O.C. <strong>' + numero + '</strong>?</p>' +
                  '<p class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Esta ação não pode ser desfeita!</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/ordens-compra/' + id,
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: 'Ordem de Compra excluída com sucesso!',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Erro ao excluir O.C.'
                            });
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao excluir O.C.!';
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
});
</script>
@stop
