@extends('adminlte::page')

@section('title', 'Controle de Frete')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-truck mr-2"></i> Controle de Frete</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNovoFrete">
            <i class="fas fa-plus mr-2"></i> Solicitar Frete
        </button>
    </div>
@stop

@section('content')
<!-- Cards de Estatísticas -->
<div class="row" id="statsCards">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="stat_aguardando">0</h3>
                <p>Aguardando Cotação</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="stat_cotacao">0</h3>
                <p>Em Cotação</p>
            </div>
            <div class="icon"><i class="fas fa-search-dollar"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3 id="stat_cotado">0</h3>
                <p>Cotado (Aguard. Aprovação)</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="stat_liberado">0</h3>
                <p>Liberados</p>
            </div>
            <div class="icon"><i class="fas fa-truck-loading"></i></div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i> Filtros</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="formFiltros" class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label><i class="fas fa-calendar mr-1"></i> Data Início</label>
                    <input type="date" class="form-control form-control-sm" id="filtro_data_inicio" name="data_inicio">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label><i class="fas fa-calendar mr-1"></i> Data Fim</label>
                    <input type="date" class="form-control form-control-sm" id="filtro_data_fim" name="data_fim">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label><i class="fas fa-clipboard-list mr-1"></i> Nº O.S.</label>
                    <input type="text" class="form-control form-control-sm" id="filtro_numero_os" name="numero_os" placeholder="Número">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fas fa-tag mr-1"></i> Status</label>
                    <select class="form-control form-control-sm" id="filtro_status" name="status">
                        <option value="">{{ __('Todos') }}</option>
                        <option value="aguardando_cotacao">Aguardando Cotação</option>
                        <option value="em_cotacao">Em Cotação</option>
                        <option value="cotado">Cotado</option>
                        <option value="aguardando_pagamento">Aguardando Pagamento</option>
                        <option value="pago">Pago</option>
                        <option value="liberado">Liberado</option>
                        <option value="entregue">Entregue</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-group w-100">
                    <button type="submit" class="btn btn-info btn-sm btn-block">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Fretes -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-2"></i> Fretes</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="130">Nº Frete</th>
                        <th width="110">Nº O.S.</th>
                        <th>Descrição</th>
                        <th width="130">Transportadora</th>
                        <th width="100" class="text-right">Valor</th>
                        <th width="140" class="text-center">Status</th>
                        <th width="100" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="listaFretes">
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Carregando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer" id="paginacao"></div>
</div>

<!-- Modal Novo Frete -->
<div class="modal fade" id="modalNovoFrete" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-truck mr-2"></i> Solicitar Frete</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formNovoFrete">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-clipboard-list mr-1"></i> Ordem de Serviço <span class="text-danger">*</span></label>
                        <select class="form-control" id="ordem_servico_id" name="ordem_servico_id" required>
                            <option value="">Buscar O.S...</option>
                        </select>
                        <small class="text-muted">Digite o número ou descrição da O.S.</small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-align-left mr-1"></i> Descrição do Frete <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                            placeholder="Ex: Transporte de materiais elétricos para obra..." required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt mr-1"></i> Origem</label>
                                <input type="text" class="form-control" id="origem" name="origem" placeholder="De onde sai">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-map-marker mr-1"></i> Destino</label>
                                <input type="text" class="form-control" id="destino" name="destino" placeholder="Para onde vai">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-sticky-note mr-1"></i> Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="2" placeholder="Informações adicionais..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-1"></i> Solicitar Frete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalhes do Frete -->
<div class="modal fade" id="modalDetalhesFrete" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle mr-2"></i> Detalhes do Frete</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="conteudoDetalhesFrete">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Cotação -->
<div class="modal fade" id="modalCotacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Adicionar Cotação</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCotacao">
                @csrf
                <input type="hidden" id="cotacao_frete_id" name="frete_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-building mr-1"></i> Transportadora <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cotacao_transportadora" name="transportadora" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign mr-1"></i> Valor (R$) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control money" id="cotacao_valor" name="valor" placeholder="0,00" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-clock mr-1"></i> Prazo de Entrega</label>
                        <input type="text" class="form-control" id="cotacao_prazo" name="prazo_entrega" placeholder="Ex: 3 dias úteis">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-comment mr-1"></i> Observações</label>
                        <textarea class="form-control" id="cotacao_obs" name="observacoes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Adicionar Cotação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cancelar -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i> Cancelar Frete</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCancelar">
                @csrf
                <input type="hidden" id="cancelar_frete_id">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Esta ação não pode ser desfeita!
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-comment mr-1"></i> Motivo do Cancelamento <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancelar_motivo" name="motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Voltar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i> Confirmar Cancelamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .badge-status {
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 20px;
    }
    .badge-aguardando_cotacao { background: #ffc107; color: #000; }
    .badge-em_cotacao { background: #17a2b8; color: #fff; }
    .badge-cotado { background: #6f42c1; color: #fff; }
    .badge-aguardando_pagamento { background: #fd7e14; color: #fff; }
    .badge-pago { background: #28a745; color: #fff; }
    .badge-liberado { background: #20c997; color: #fff; }
    .badge-entregue { background: #6c757d; color: #fff; }
    .badge-cancelado { background: #dc3545; color: #fff; }
    
    .timeline-frete {
        position: relative;
        padding-left: 30px;
    }
    .timeline-frete::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 15px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 3px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #dee2e6;
        border: 2px solid #fff;
    }
    .timeline-item.completed::before {
        background: #28a745;
    }
    .timeline-item.active::before {
        background: #007bff;
        box-shadow: 0 0 0 4px rgba(0,123,255,0.2);
    }
    .cotacao-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    .cotacao-card:hover {
        border-color: #007bff;
    }
    .cotacao-card.selecionada {
        border-color: #28a745;
        background: #f8fff9;
    }
    .small-box .icon {
        font-size: 50px;
    }
    .bg-purple {
        background: #6f42c1 !important;
        color: #fff;
    }
    
    /* Select2 maior e mais legível */
    .select2-container--default .select2-selection--single {
        height: 45px !important;
        padding: 8px 12px !important;
        font-size: 16px !important;
        border: 2px solid #ced4da !important;
        border-radius: 6px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        padding-left: 0 !important;
        font-size: 16px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d !important;
    }
    .select2-dropdown {
        border: 2px solid #007bff !important;
        border-radius: 6px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
    .select2-results__option {
        padding: 12px 15px !important;
        font-size: 15px !important;
        border-bottom: 1px solid #eee;
    }
    .select2-results__option--highlighted {
        background-color: #007bff !important;
    }
    .select2-search--dropdown .select2-search__field {
        padding: 10px 12px !important;
        font-size: 16px !important;
        border: 2px solid #ced4da !important;
        border-radius: 4px !important;
    }
    .select2-container {
        width: 100% !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const statusLabels = {
    'aguardando_cotacao': 'Aguardando Cotação',
    'em_cotacao': 'Em Cotação',
    'cotado': 'Cotado',
    'aguardando_pagamento': 'Aguard. Pagamento',
    'pago': 'Pago',
    'liberado': 'Liberado',
    'entregue': 'Entregue',
    'cancelado': 'Cancelado'
};

$(document).ready(function() {
    carregarEstatisticas();
    carregarFretes();

    // Select2 para O.S.
    $('#ordem_servico_id').select2({
        dropdownParent: $('#modalNovoFrete'),
        placeholder: 'Digite para buscar a O.S...',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: function() {
                return 'Digite o número ou descrição da O.S.';
            },
            noResults: function() {
                return 'Nenhuma O.S. encontrada';
            },
            searching: function() {
                return 'Buscando...';
            }
        },
        ajax: {
            url: '/frete/buscar-os',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return {
                    results: data.ordens.map(function(os) {
                        const dataFmt = os.data_os ? new Date(os.data_os).toLocaleDateString('pt-BR') : '';
                        return {
                            id: os.id,
                            text: os.numero_os + ' | ' + dataFmt + ' | ' + (os.descricao || '').substring(0, 80)
                        };
                    })
                };
            }
        },
        templateResult: function(os) {
            if (os.loading) return os.text;
            return $('<div style="padding: 5px 0;"><strong>' + os.text + '</strong></div>');
        }
    });

    // Máscara de dinheiro
    $(document).on('input', '.money', function() {
        let v = $(this).val().replace(/\D/g, '');
        v = (parseInt(v) / 100).toFixed(2);
        v = v.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        $(this).val(v);
    });

    // Filtros
    $('#formFiltros').on('submit', function(e) {
        e.preventDefault();
        carregarFretes();
    });

    // Novo frete
    $('#formNovoFrete').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/frete/store',
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if (res.success) {
                    $('#modalNovoFrete').modal('hide');
                    $('#formNovoFrete')[0].reset();
                    $('#ordem_servico_id').val(null).trigger('change');
                    carregarFretes();
                    carregarEstatisticas();
                    toastr.success('Frete solicitado! Nº: ' + res.numero_frete);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Erro ao salvar');
            }
        });
    });

    // Adicionar cotação
    $('#formCotacao').on('submit', function(e) {
        e.preventDefault();
        const freteId = $('#cotacao_frete_id').val();
        let valor = $('#cotacao_valor').val().replace(/\./g, '').replace(',', '.');
        
        $.ajax({
            url: '/frete/' + freteId + '/cotacao',
            method: 'POST',
            data: {
                transportadora: $('#cotacao_transportadora').val(),
                valor: valor,
                prazo_entrega: $('#cotacao_prazo').val(),
                observacoes: $('#cotacao_obs').val()
            },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if (res.success) {
                    $('#modalCotacao').modal('hide');
                    $('#formCotacao')[0].reset();
                    verDetalhes(freteId);
                    carregarFretes();
                    carregarEstatisticas();
                    toastr.success('Cotação adicionada!');
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Erro');
            }
        });
    });

    // Cancelar frete
    $('#formCancelar').on('submit', function(e) {
        e.preventDefault();
        const freteId = $('#cancelar_frete_id').val();
        
        $.ajax({
            url: '/frete/' + freteId + '/cancelar',
            method: 'POST',
            data: { motivo: $('#cancelar_motivo').val() },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if (res.success) {
                    $('#modalCancelar').modal('hide');
                    $('#modalDetalhesFrete').modal('hide');
                    carregarFretes();
                    carregarEstatisticas();
                    toastr.success('Frete cancelado!');
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Erro');
            }
        });
    });
});

function carregarEstatisticas() {
    $.get('/frete/estatisticas', function(res) {
        if (res.success) {
            $('#stat_aguardando').text(res.stats.aguardando_cotacao);
            $('#stat_cotacao').text(res.stats.em_cotacao + res.stats.cotado);
            $('#stat_cotado').text(res.stats.aguardando_pagamento);
            $('#stat_liberado').text(res.stats.liberado);
        }
    });
}

function carregarFretes(page = 1) {
    const params = new URLSearchParams($('#formFiltros').serialize());
    params.append('page', page);

    $.get('/frete/listar?' + params.toString(), function(res) {
        if (res.success) {
            renderizarFretes(res.fretes);
        }
    });
}

function renderizarFretes(fretes) {
    const tbody = $('#listaFretes');
    tbody.empty();

    if (!fretes.data || fretes.data.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i> Nenhum frete encontrado</td></tr>');
        return;
    }

    fretes.data.forEach(function(f) {
        const valor = f.valor_aprovado > 0 ? f.valor_aprovado : (f.valor_cotado > 0 ? f.valor_cotado : 0);
        const valorFmt = valor > 0 ? parseFloat(valor).toLocaleString('pt-BR', {style:'currency', currency:'BRL'}) : '-';
        
        tbody.append(`
            <tr>
                <td><strong>${f.numero_frete || '-'}</strong></td>
                <td><span class="badge badge-secondary">${f.numero_os || '-'}</span></td>
                <td>${(f.descricao || '').substring(0, 50)}${f.descricao?.length > 50 ? '...' : ''}</td>
                <td>${f.transportadora_selecionada || '<span class="text-muted">-</span>'}</td>
                <td class="text-right"><strong class="text-success">${valorFmt}</strong></td>
                <td class="text-center"><span class="badge badge-status badge-${f.status}">${statusLabels[f.status] || f.status}</span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info" onclick="verDetalhes(${f.id})" title="Ver Detalhes">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `);
    });

    // Paginação
    if (fretes.last_page > 1) {
        let html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0">';
        for (let i = 1; i <= fretes.last_page; i++) {
            html += `<li class="page-item ${i === fretes.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="carregarFretes(${i}); return false;">${i}</a></li>`;
        }
        html += '</ul></nav>';
        $('#paginacao').html(html);
    } else {
        $('#paginacao').empty();
    }
}

function verDetalhes(id) {
    $('#conteudoDetalhesFrete').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#modalDetalhesFrete').modal('show');

    $.get('/frete/' + id, function(res) {
        if (res.success) {
            renderizarDetalhes(res.frete, res.cotacoes);
        } else {
            $('#conteudoDetalhesFrete').html('<div class="alert alert-danger">Erro ao carregar</div>');
        }
    });
}

function renderizarDetalhes(f, cotacoes) {
    const valor = f.valor_aprovado > 0 ? f.valor_aprovado : (f.valor_cotado > 0 ? f.valor_cotado : 0);
    const valorFmt = valor > 0 ? parseFloat(valor).toLocaleString('pt-BR', {style:'currency', currency:'BRL'}) : 'Não cotado';
    
    let cotacoesHtml = '';
    if (cotacoes && cotacoes.length > 0) {
        cotacoes.forEach(c => {
            const valorCot = parseFloat(c.valor).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
            cotacoesHtml += `
                <div class="cotacao-card ${c.selecionada ? 'selecionada' : ''}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${c.transportadora}</strong>
                            ${c.prazo_entrega ? '<span class="text-muted ml-2">(' + c.prazo_entrega + ')</span>' : ''}
                            ${c.selecionada ? '<span class="badge badge-success ml-2">Selecionada</span>' : ''}
                        </div>
                        <div class="text-right">
                            <strong class="text-primary" style="font-size:18px">${valorCot}</strong>
                            ${!c.selecionada && f.status === 'em_cotacao' ? 
                                '<button class="btn btn-sm btn-outline-success ml-2" onclick="selecionarCotacao('+f.id+','+c.id+')"><i class="fas fa-check"></i></button>' : ''}
                        </div>
                    </div>
                    ${c.observacoes ? '<small class="text-muted mt-1 d-block">' + c.observacoes + '</small>' : ''}
                </div>
            `;
        });
    } else {
        cotacoesHtml = '<div class="text-muted text-center py-3">Nenhuma cotação ainda</div>';
    }

    let acoesHtml = '';
    if (['aguardando_cotacao', 'em_cotacao'].includes(f.status)) {
        acoesHtml += `<button class="btn btn-success mr-2" onclick="abrirModalCotacao(${f.id})"><i class="fas fa-plus mr-1"></i> Adicionar Cotação</button>`;
    }
    if (f.status === 'cotado') {
        acoesHtml += `<button class="btn btn-primary mr-2" onclick="aprovarFrete(${f.id})"><i class="fas fa-check mr-1"></i> Aprovar Frete</button>`;
    }
    if (f.status === 'liberado') {
        acoesHtml += `<button class="btn btn-success mr-2" onclick="confirmarEntrega(${f.id})"><i class="fas fa-truck mr-1"></i> Confirmar Entrega</button>`;
    }
    if (!['pago', 'liberado', 'entregue', 'cancelado'].includes(f.status)) {
        acoesHtml += `<button class="btn btn-danger" onclick="abrirModalCancelar(${f.id})"><i class="fas fa-times mr-1"></i>{{ __('Cancelar') }}</button>`;
    }

    $('#conteudoDetalhesFrete').html(`
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-info-circle mr-2 text-primary"></i> Informações</h5>
                <table class="table table-sm">
                    <tr><th width="40%">Número:</th><td><strong>${f.numero_frete}</strong></td></tr>
                    <tr><th>O.S.:</th><td><span class="badge badge-secondary">${f.numero_os || '-'}</span></td></tr>
                    <tr><th>Status:</th><td><span class="badge badge-status badge-${f.status}">${statusLabels[f.status]}</span></td></tr>
                    <tr><th>Valor:</th><td><strong class="text-success">${valorFmt}</strong></td></tr>
                    <tr><th>Transportadora:</th><td>${f.transportadora_selecionada || '-'}</td></tr>
                    <tr><th>Origem:</th><td>${f.origem || '-'}</td></tr>
                    <tr><th>Destino:</th><td>${f.destino || '-'}</td></tr>
                    <tr><th>Solicitante:</th><td>${f.solicitante_nome || '-'}</td></tr>
                </table>
                <h6 class="mt-3"><i class="fas fa-align-left mr-1"></i> Descrição</h6>
                <p class="bg-light p-2 rounded">${f.descricao || '-'}</p>
                ${f.observacoes ? '<h6><i class="fas fa-sticky-note mr-1"></i> Observações</h6><p class="bg-light p-2 rounded">' + f.observacoes + '</p>' : ''}
            </div>
            <div class="col-md-6">
                <h5><i class="fas fa-search-dollar mr-2 text-success"></i> Cotações de Transportadoras</h5>
                ${cotacoesHtml}
            </div>
        </div>
        <hr>
        <div class="text-right">
            ${acoesHtml}
            <button class="btn btn-secondary" data-dismiss="modal">{{ __('Fechar') }}</button>
        </div>
    `);
}

function abrirModalCotacao(freteId) {
    $('#cotacao_frete_id').val(freteId);
    $('#formCotacao')[0].reset();
    $('#modalCotacao').modal('show');
}

function selecionarCotacao(freteId, cotacaoId) {
    if (!confirm('Selecionar esta cotação como vencedora?')) return;
    
    $.post('/frete/' + freteId + '/cotacao/' + cotacaoId + '/selecionar', {}, function(res) {
        if (res.success) {
            verDetalhes(freteId);
            carregarFretes();
            carregarEstatisticas();
            toastr.success('Cotação selecionada!');
        } else {
            toastr.error(res.message);
        }
    }).fail(function(xhr) {
        toastr.error(xhr.responseJSON?.message || 'Erro');
    });
}

function aprovarFrete(id) {
    if (!confirm('Aprovar este frete? Será gerada uma conta a pagar.')) return;
    
    $.post('/frete/' + id + '/aprovar', {}, function(res) {
        if (res.success) {
            verDetalhes(id);
            carregarFretes();
            carregarEstatisticas();
            toastr.success(res.message);
        } else {
            toastr.error(res.message);
        }
    }).fail(function(xhr) {
        toastr.error(xhr.responseJSON?.message || 'Erro');
    });
}

function confirmarEntrega(id) {
    if (!confirm('Confirmar que os materiais foram entregues?')) return;
    
    $.post('/frete/' + id + '/entrega', {}, function(res) {
        if (res.success) {
            verDetalhes(id);
            carregarFretes();
            carregarEstatisticas();
            toastr.success(res.message);
        } else {
            toastr.error(res.message);
        }
    }).fail(function(xhr) {
        toastr.error(xhr.responseJSON?.message || 'Erro');
    });
}

function abrirModalCancelar(freteId) {
    $('#cancelar_frete_id').val(freteId);
    $('#cancelar_motivo').val('');
    $('#modalCancelar').modal('show');
}

// CSRF token para requests POST
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});
</script>
@stop
