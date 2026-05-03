@extends('adminlte::page')

@section('title', 'Relatório de Pedido C.C.')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <div>
    <h1 class="m-0 text-dark font-weight-bold">
      <i class="fas fa-stream text-primary mr-2"></i>
      Relatório de Pedidos de Compras por Centro de Custo
    </h1>
    <small class="text-muted">Visualize pedidos por centro de custo, rota e período. Filtre por status de aprovação.</small>
  </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">
  <!-- Filtros -->
  <div class="card">
    <div class="card-header bg-primary text-white">
      <div class="d-flex align-items-center">
        <i class="fas fa-filter mr-2"></i> Filtros de Pesquisa
      </div>
    </div>
    <div class="card-body">
      <form id="formFiltros">
        <div class="form-row">
          <div class="col-md-3 mb-3">
            <label for="centro_custo_id" class="font-weight-bold">Centro de Custo</label>
            <select class="form-control" id="centro_custo_id" name="centro_custo_id">
              <option value="">Todos os Centros de Custo</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label for="rota_id" class="font-weight-bold">Rota</label>
            <select class="form-control" id="rota_id" name="rota_id">
              <option value="">Todas as Rotas</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label for="data_inicio" class="font-weight-bold">Data Início</label>
            <input type="date" class="form-control" id="data_inicio" name="data_inicio">
          </div>
          <div class="col-md-3 mb-3">
            <label for="data_fim" class="font-weight-bold">Data Fim</label>
            <input type="date" class="form-control" id="data_fim" name="data_fim">
          </div>
        </div>
        <div class="form-row">
          <div class="col-md-4 mb-3">
            <label for="status" class="font-weight-bold">Status de Aprovação</label>
            <select class="form-control" id="status" name="status">
              <option value="">{{ __('Todos') }}</option>
              <option value="aprovado">Aprovados</option>
              <option value="rejeitado">Recusados</option>
              <option value="pendente">Pendentes</option>
            </select>
          </div>
          <div class="col-md-8 mb-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary mr-2">
              <i class="fas fa-search mr-1"></i> Gerar Relatório
            </button>
            <button type="button" class="btn btn-secondary mr-2" id="btnLimpar">
              <i class="fas fa-eraser mr-1"></i> Limpar Filtros
            </button>
            <button type="button" class="btn btn-info" id="btnImprimir" disabled>
              <i class="fas fa-print mr-1"></i> Imprimir
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Cabeçalho de Impressão (só aparece ao imprimir) -->
  <div id="printHeader" class="d-none" aria-hidden="true" style="display: none !important;">
    <table style="width: 100%; margin-bottom: 15px;">
      <tr>
        <td style="width: 70px; vertical-align: top;">
          <img src="/img/brs-logo.png" alt="Logo BRS" style="max-width: 60px; max-height: 60px; width: auto; height: auto; object-fit: contain;">
        </td>
        <td style="vertical-align: top; padding-left: 15px;">
          <h1 style="margin: 0; font-size: 18px; font-weight: bold; color: #1a1a1a; line-height: 1.3;">SIGO</h1>
          <p style="margin: 0; font-size: 10px; color: #666; line-height: 1.3;">Sistema Integrado de Gestão Operacional</p>
          <h2 style="margin: 8px 0 0 0; font-size: 13px; font-weight: bold; color: #333; text-transform: uppercase; letter-spacing: 0.5px;">
            Relatório de Pedidos de Compras por Centro de Custo
          </h2>
        </td>
        <td style="text-align: right; vertical-align: top; width: 160px;">
          <p style="margin: 0; font-size: 9px; color: #666;">Emitido em:</p>
          <p style="margin: 0; font-size: 11px; font-weight: bold; color: #333;" id="dataEmissao"></p>
          <div style="margin-top: 8px; padding: 4px 8px; background: #f0f0f0; border-radius: 3px;" id="resumoInline">
            <p style="margin: 0; font-size: 8px; color: #666;">Total:</p>
            <p style="margin: 0; font-size: 12px; font-weight: bold; color: #333;" id="totalPrint">0 pedidos</p>
          </div>
        </td>
      </tr>
    </table>
    
    <div style="border-top: 2px solid #007bff; border-bottom: 1px solid #ddd; padding: 4px 0; margin-bottom: 10px;">
      <p style="font-size: 9px; color: #666; margin: 0; text-align: center;" id="filtrosAplicados"></p>
    </div>
  </div>

  <!-- Resumo -->
  <div class="card mt-3" id="resumoCard" style="display: none;">
    <div class="card-header">
      <i class="fas fa-chart-bar mr-2"></i> Resumo do Período
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total de Pedidos</span>
              <span class="info-box-number" id="totalPedidos">0</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Aprovados</span>
              <span class="info-box-number" id="totalAprovados">0</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Rejeitados</span>
              <span class="info-box-number" id="totalRejeitados">0</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Pendentes</span>
              <span class="info-box-number" id="totalPendentes">0</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabela de Resultados -->
  <div class="card mt-3" id="resultadosCard" style="display: none;">
    <div class="card-header">
      <i class="fas fa-table mr-2"></i> Resultados
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-striped mb-0" id="tabelaResultados">
          <thead class="thead-dark">
            <tr>
              <th width="110px">Data Solicitação</th>
              <th width="140px">Nº Pedido</th>
              <th>Solicitante</th>
              <th>Centro de Custo</th>
              <th>Rota</th>
              <th width="60px" class="text-center">Itens</th>
              <th width="120px" class="text-right">Valor</th>
              <th width="80px" class="text-right">Qtd Total</th>
              <th width="90px" class="text-center">Prioridade</th>
              <th width="90px" class="text-center">Status</th>
              <th width="110px">Data Aprovação</th>
            </tr>
          </thead>
          <tbody id="tabelaBody">
          </tbody>
          <tfoot id="tabelaFoot" style="display: none;">
            <tr class="table-active">
              <th colspan="6" class="text-right font-weight-bold">Total dos Resultados:</th>
              <th class="text-right font-weight-bold text-success" id="valorTotalTabela">R$ 0,00</th>
              <th colspan="4"></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <!-- Mensagem quando não há resultados -->
  <div class="card mt-3" id="semResultados" style="display: none;">
    <div class="card-body text-center">
      <i class="fas fa-search fa-3x text-muted mb-3"></i>
      <h5 class="text-muted">Nenhum resultado encontrado</h5>
      <p class="text-muted">Ajuste os filtros e tente novamente.</p>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
// CSRF para requisições AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Mapas para resolver nomes por ID na tabela/impresso
var CENTRO_MAP = {};
var ROTA_MAP = {};

// Função para formatar data brasileiro
function formatarDataBR(dataISO) {
    if (!dataISO) return '—';
    const data = new Date(dataISO);
    if (isNaN(data.getTime())) return '—';
    
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();
    
    return `${dia}/${mes}/${ano}`;
}

// Função para formatar quantidade com separador de milhares
function formatarQuantidade(quantidade) {
    return Number(quantidade || 0).toLocaleString('pt-BR');
}

// Função para escapar HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Resolve nomes de Centro de Custo e Rota vindos em diferentes formatos/atributos
function getCentroCustoNome(item){
    if (!item) return '—';
    const direct = item.centro_custo_nome || item.nome_centro_custo || item.cc_nome || item.centroCustoNome;
    if (direct) return direct;
    if (item.centro_custo && typeof item.centro_custo === 'object'){
        return item.centro_custo.nome || item.centro_custo.descricao || '—';
    }
    if (typeof item.centro_custo === 'string') return item.centro_custo;
    const id = item.centro_custo_id || item.cc_id || item.centro_custo;
    if (id && CENTRO_MAP[Number(id)]) return CENTRO_MAP[Number(id)];
    return '—';
}

function getRotaNome(item){
    if (!item) return '—';
    const direct = item.rota_nome || item.nome_rota || item.rotaNome;
    if (direct) return direct;
    if (item.rota && typeof item.rota === 'object'){
        return item.rota.nome || item.rota.nome_rota || (item.rota.numero_rota ? `Rota ${item.rota.numero_rota}` : '—');
    }
    if (typeof item.rota === 'string') return item.rota;
    const id = item.rota_id || item.rotaId || item.rota;
    if (id && ROTA_MAP[Number(id)]) return ROTA_MAP[Number(id)];
    if (item.numero_rota) return `Rota ${item.numero_rota}`;
    return '—';
}

$(document).ready(function() {
    // Carregar centros de custo
    carregarCentrosCusto();
    // Carregar rotas (todas) para montar o mapa inicial
    carregarRotas('');

    // Evento do formulário
    $('#formFiltros').on('submit', function(e) {
        e.preventDefault();
        gerarRelatorio();
    });

    // Limpar filtros
    $('#btnLimpar').on('click', function() {
        $('#formFiltros')[0].reset();
        $('#centro_custo_id, #rota_id, #status').val('');
        $('#resumoCard, #resultadosCard, #semResultados').hide();
        $('#tabelaFoot').hide();
        $('#btnImprimir').prop('disabled', true);
    });

    // Imprimir (ativar cabeçalho somente durante a impressão)
    $('#btnImprimir').on('click', function() {
        prepararImpressao();
        $('#printHeader').attr('aria-hidden', 'false').show();
        setTimeout(function(){ window.print(); }, 0);
    });

    // Garantir que o cabeçalho de impressão volte a esconder após imprimir
    if (typeof window.matchMedia === 'function') {
        try {
            const mql = window.matchMedia('print');
            if (mql && mql.addEventListener) {
                mql.addEventListener('change', function(e){ if (!e.matches) { $('#printHeader').attr('aria-hidden','true').hide(); } });
            } else if (mql && mql.addListener) {
                mql.addListener(function(e){ if (!e.matches) { $('#printHeader').attr('aria-hidden','true').hide(); } });
            }
        } catch(_){}
    }
    window.addEventListener('afterprint', function(){ $('#printHeader').attr('aria-hidden','true').hide(); });

    // Carregar rotas quando selecionar centro de custo
    $('#centro_custo_id').on('change', function() {
        const centroCustoId = $(this).val();
        carregarRotas(centroCustoId);
    });
});

function carregarCentrosCusto() {
    // Busca todos os centros de custo
    $.get('/api/centro-custos', function(response) {
        if (response.success && response.data) {
            const select = $('#centro_custo_id');
            select.html('<option value="">Todos os Centros de Custo</option>');
            
            response.data.forEach(function(cc) {
                CENTRO_MAP[Number(cc.id)] = cc.nome;
                select.append(`<option value="${cc.id}">${escapeHtml(cc.nome)}</option>`);
            });
        }
    }).fail(function() {
        console.error('Erro ao carregar centros de custo');
    });
}

function carregarRotas(centroCustoId = '') {
    const url = centroCustoId ? 
        `/api/rotas/por-centro-custo?centro_custo_id=${centroCustoId}` : 
        '/api/rotas/buscar';

    $.get(url, function(response) {
        if (response.success && response.data) {
            const select = $('#rota_id');
            select.html('<option value="">Todas as Rotas</option>');
            
            response.data.forEach(function(rota) {
                const nome = rota.nome_rota || rota.nome || `Rota ${rota.numero_rota || rota.id}`;
                ROTA_MAP[Number(rota.id)] = nome;
                select.append(`<option value="${rota.id}">${escapeHtml(nome)}</option>`);
            });
        }
    }).fail(function() {
        console.error('Erro ao carregar rotas');
    });
}

function gerarRelatorio() {
    const dados = {
        centro_custo_id: $('#centro_custo_id').val(),
        rota_id: $('#rota_id').val(),
        data_inicio: $('#data_inicio').val(),
        data_fim: $('#data_fim').val(),
        status: $('#status').val()
    };

    // Mostrar loading
    $('#resultadosCard, #resumoCard, #semResultados').hide();
    
    $.post('/api/relatorios/pedido-cc', dados, function(response) {
        if (response.success) {
            preencherResumo(response.resumo);
            preencherTabela(response.dados);
            
            if (response.dados && response.dados.length > 0) {
                $('#resumoCard, #resultadosCard').show();
                $('#btnImprimir').prop('disabled', false);
            } else {
                $('#semResultados').show();
                $('#btnImprimir').prop('disabled', true);
            }
        } else {
            alert('Erro ao gerar relatório: ' + (response.message || 'Erro desconhecido'));
        }
    }).fail(function(xhr) {
        const msg = xhr.responseJSON?.message || 'Erro ao conectar com o servidor';
        alert('Erro: ' + msg);
    });
}

function preencherResumo(resumo) {
    $('#totalPedidos').text(resumo.total || 0);
    $('#totalAprovados').text(resumo.aprovados || 0);
    $('#totalRejeitados').text(resumo.rejeitados || 0);
    $('#totalPendentes').text(resumo.pendentes || 0);
}

function preencherTabela(dados) {
    const tbody = $('#tabelaBody');
    const tfoot = $('#tabelaFoot');
    tbody.empty();
    let somaValores = 0;

    if (dados && dados.length > 0) {
        dados.forEach(function(item) {
            const statusBadge = getStatusBadge(item.aprovacao);
            const prioridadeBadge = getPrioridadeBadge(item.prioridade);
            
            const valorFormatado = Number(item.valor_total||0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            somaValores += Number(item.valor_total||0);
            
            // Garantir nomes de Centro de Custo e Rota mesmo que backend envie apenas IDs
            const nomeCC = getCentroCustoNome(item);
            const nomeRota = getRotaNome(item);

            const row = `
                <tr>
                    <td class="text-nowrap">${formatarDataBR(item.data_solicitacao)}</td>
                    <td><span class="badge badge-dark">${escapeHtml(item.num_pedido || '—')}</span></td>
                    <td>${escapeHtml(item.solicitante || '—')}</td>
                    <td>${escapeHtml(nomeCC)}</td>
                    <td>${escapeHtml(nomeRota)}</td>
                    <td class="text-center">${Number(item.itens||0).toLocaleString('pt-BR')}</td>
                    <td class="text-right text-nowrap"><span class="text-success font-weight-bold">R$ ${valorFormatado}</span></td>
                    <td class="text-right">${formatarQuantidade(item.quantidade_total)}</td>
                    <td class="text-center">${prioridadeBadge}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-nowrap">${formatarDataBR(item.data_aprovacao)}</td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Mostrar rodapé com total (baseado no filtro aplicado)
        const valorTotalFmt = somaValores.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        // Rodapé para tela e para impressão
        tfoot.html(`
            <tr class="screen-only">
                <th colspan="6" class="text-right font-weight-bold">Total dos Resultados:</th>
                <th class="text-right font-weight-bold text-success" id="valorTotalTabela">R$ ${valorTotalFmt}</th>
                <th colspan="4"></th>
            </tr>
            <tr class="print-only">
                <th colspan="4" class="text-right font-weight-bold">Total dos Resultados:</th>
                <th class="text-right font-weight-bold" id="valorTotalTabelaPrint">R$ ${valorTotalFmt}</th>
            </tr>
        `);
        tfoot.show();
    } else {
        // Esconder rodapé se não há dados
        tfoot.hide();
    }
}

function getStatusBadge(status) {
    switch (status) {
        case 'aprovado':
            return '<span class="badge badge-success">Aprovado</span>';
        case 'rejeitado':
            return '<span class="badge badge-danger">Rejeitado</span>';
        case 'pendente':
            return '<span class="badge badge-warning">Pendente</span>';
        default:
            return '<span class="badge badge-secondary">—</span>';
    }
}

function getPrioridadeBadge(prioridade) {
    switch (prioridade) {
        case 'alta':
            return '<span class="badge badge-danger">Alta</span>';
        case 'media':
            return '<span class="badge badge-warning">Média</span>';
        case 'baixa':
            return '<span class="badge badge-secondary">Baixa</span>';
        default:
            return '<span class="badge badge-secondary">—</span>';
    }
}

function prepararImpressao() {
    // Mostrar cabeçalho de impressão
    $('#printHeader').removeClass('d-none').show();
    
    // Preencher data de emissão
    const agora = new Date();
    const dataEmissao = agora.toLocaleDateString('pt-BR') + ' às ' + agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    $('#dataEmissao').text(dataEmissao);
    
    // Preencher total de pedidos no cabeçalho
    const total = $('#totalPedidos').text() || '0';
    const aprovados = $('#totalAprovados').text() || '0';
    const rejeitados = $('#totalRejeitados').text() || '0';
    const pendentes = $('#totalPendentes').text() || '0';
    $('#totalPrint').text(`${total} pedidos (${aprovados} aprovados, ${rejeitados} rejeitados, ${pendentes} pendentes)`);
    
    // Preencher filtros aplicados
    const filtros = [];
    
    const cc = $('#centro_custo_id option:selected').text();
    if (cc && cc !== 'Todos os Centros de Custo') {
        filtros.push('Centro de Custo: ' + cc);
    }
    
    const rota = $('#rota_id option:selected').text();
    if (rota && rota !== 'Todas as Rotas') {
        filtros.push('Rota: ' + rota);
    }
    
    const dataIni = $('#data_inicio').val();
    const dataFim = $('#data_fim').val();
    if (dataIni && dataFim) {
        filtros.push('Período: ' + formatarDataBR(dataIni) + ' a ' + formatarDataBR(dataFim));
    } else if (dataIni) {
        filtros.push('A partir de: ' + formatarDataBR(dataIni));
    } else if (dataFim) {
        filtros.push('Até: ' + formatarDataBR(dataFim));
    }
    
    const status = $('#status option:selected').text();
    if (status && status !== 'Todos') {
        filtros.push('Status: ' + status);
    }
    
    const textoFiltros = filtros.length > 0 ? filtros.join(' • ') : 'Todos os pedidos';
    $('#filtrosAplicados').text(textoFiltros);
}
</script>

<style>
/* Fix: Garantir fundo branco nos inputs e selects (modo claro) */
.form-control,
select.form-control,
input.form-control,
input[type="date"].form-control,
input[type="text"].form-control,
#centro_custo_id,
#rota_id,
#status,
#data_inicio,
#data_fim {
    background-color: #ffffff !important;
    background: #ffffff !important;
    color: #333 !important;
}

/* ===============================================
   DARK MODE - Estilos para modo escuro
   =============================================== */

/* Card de filtros no dark mode */
html[data-theme="dark"] .card {
    background-color: #1e293b !important;
    border-color: #334155 !important;
}

html[data-theme="dark"] .card-header.bg-primary {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
    border-bottom-color: #475569 !important;
}

html[data-theme="dark"] .card-body {
    background-color: #1e293b !important;
}

/* Labels no dark mode */
html[data-theme="dark"] label,
html[data-theme="dark"] .font-weight-bold {
    color: #f1f5f9 !important;
}

/* Inputs e Selects no dark mode */
html[data-theme="dark"] .form-control,
html[data-theme="dark"] select.form-control,
html[data-theme="dark"] input.form-control,
html[data-theme="dark"] input[type="date"].form-control,
html[data-theme="dark"] input[type="text"].form-control,
html[data-theme="dark"] #centro_custo_id,
html[data-theme="dark"] #rota_id,
html[data-theme="dark"] #status,
html[data-theme="dark"] #data_inicio,
html[data-theme="dark"] #data_fim {
    background-color: #334155 !important;
    background: #334155 !important;
    color: #f1f5f9 !important;
    border-color: #475569 !important;
}

/* Placeholder no dark mode */
html[data-theme="dark"] .form-control::placeholder {
    color: #94a3b8 !important;
}

/* Tabela no dark mode */
html[data-theme="dark"] #tabelaResultados {
    background-color: #1e293b !important;
    color: #f1f5f9 !important;
}

html[data-theme="dark"] #tabelaResultados th {
    background-color: #0f172a !important;
    color: #f1f5f9 !important;
    border-color: #475569 !important;
}

html[data-theme="dark"] #tabelaResultados td {
    background-color: #1e293b !important;
    color: #cbd5e1 !important;
    border-color: #334155 !important;
}

html[data-theme="dark"] #tabelaResultados tbody tr:hover td {
    background-color: #334155 !important;
}

/* Botões no dark mode */
html[data-theme="dark"] .btn-secondary {
    background-color: #475569 !important;
    border-color: #64748b !important;
    color: #f1f5f9 !important;
}

html[data-theme="dark"] .btn-info {
    background-color: #0891b2 !important;
    border-color: #06b6d4 !important;
}

/* Layout da tabela */
#tabelaResultados {
    font-size: 14px;
}

#tabelaResultados th {
    white-space: nowrap;
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
    border-bottom: 2px solid #dee2e6;
}

#tabelaResultados td {
    vertical-align: middle;
    padding: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}

/* Colunas específicas */
#tabelaResultados .text-nowrap {
    white-space: nowrap;
}

/* Badges */
#tabelaResultados .badge {
    font-size: 0.85em;
    padding: 0.35em 0.6em;
}

/* Controle de visibilidade padrão (tela) para linhas específicas */
.print-only { display: none !important; }
.screen-only { display: table-row !important; }

/* Rodapé da tabela */
#tabelaResultados tfoot th {
    background-color: #f8f9fa;
    border-top: 2px solid #dee2e6;
    font-size: 1em;
    padding: 0.75rem 0.5rem;
}

/* Responsividade */
@media (max-width: 1200px) {
    #tabelaResultados {
        font-size: 13px;
    }
    
    #tabelaResultados th,
    #tabelaResultados td {
        padding: 0.4rem 0.3rem;
    }
}

@media (max-width: 992px) {
    #tabelaResultados th:nth-child(4),
    #tabelaResultados td:nth-child(4) {
        display: none; /* Esconde Centro de Custo em tablets */
    }
}

@media (max-width: 768px) {
    #tabelaResultados th:nth-child(5),
    #tabelaResultados td:nth-child(5),
    #tabelaResultados th:nth-child(11),
    #tabelaResultados td:nth-child(11) {
        display: none; /* Esconde Rota e Data Aprovação em mobile */
    }
}

@media print {
    /* Ocultar elementos desnecessários */
    .no-print,
    .content-header,
    .main-sidebar,
    .main-header,
    .main-footer,
    .btn,
    button,
    .info-box-icon,
    #formFiltros,
    .card-header.bg-primary,
    #resumoCard {
        display: none !important;
    }
    
    /* Mostrar cabeçalho de impressão */
    #printHeader[aria-hidden='false'] {
        display: block !important;
    }
    
    /* Reset do corpo da página */
    body {
        margin: 0;
        padding: 15px;
        background: white !important;
    }
    
    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Ocultar cards de resumo */
    #resumoCard {
        display: none !important;
    }
    
    /* Card de resultados - SEM margens */
    #resultadosCard {
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        page-break-before: avoid !important;
    }
    
    #resultadosCard .card-header {
        display: none !important;
    }
    
    #resultadosCard .card-body {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    #tabelaResultados {
        font-size: 9px;
        width: 100%;
        border-collapse: collapse;
        margin-top: 0 !important;
    }
    
    #tabelaResultados thead {
        background: #343a40 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        display: table-header-group !important;
    }
    
    #tabelaResultados thead tr {
        display: table-row !important;
    }
    
    #tabelaResultados th {
        padding: 8px 6px !important;
        border: 1px solid #333 !important;
        font-weight: bold;
        color: #fff !important;
        background: #343a40 !important;
        text-align: left;
        font-size: 9px !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        display: table-cell !important;
    }

    /* Garantir exibição das colunas 4 e 5 (Centro de Custo e Rota) na impressão */
    #tabelaResultados th:nth-child(4),
    #tabelaResultados td:nth-child(4),
    #tabelaResultados th:nth-child(5),
    #tabelaResultados td:nth-child(5) {
        display: table-cell !important;
    }

    /* Esconder o rodapé de tela durante impressão e exibir a versão de impressão */
    .screen-only { display: none !important; }
    .print-only { display: table-row !important; }
    
    /* Ocultar colunas na impressão
       6: Itens | 7: Valor | 8: Qtd Total | 9: Prioridade | 10: Status | 11: Data Aprovação
    */
    #tabelaResultados th:nth-child(6),
    #tabelaResultados td:nth-child(6),
    #tabelaResultados th:nth-child(7),
    #tabelaResultados td:nth-child(7),
    #tabelaResultados th:nth-child(8),
    #tabelaResultados td:nth-child(8),
    #tabelaResultados th:nth-child(9),
    #tabelaResultados td:nth-child(9),
    #tabelaResultados th:nth-child(10),
    #tabelaResultados td:nth-child(10),
    #tabelaResultados th:nth-child(11),
    #tabelaResultados td:nth-child(11) {
        display: none !important;
    }

    /* Larguras otimizadas para as 5 colunas visíveis (1-5) */
    #tabelaResultados { table-layout: fixed; }
    #tabelaResultados th:nth-child(1),
    #tabelaResultados td:nth-child(1) { width: 12%; }
    #tabelaResultados th:nth-child(2),
    #tabelaResultados td:nth-child(2) { width: 18%; }
    #tabelaResultados th:nth-child(3),
    #tabelaResultados td:nth-child(3) { width: 40%; }
    #tabelaResultados th:nth-child(4),
    #tabelaResultados td:nth-child(4) { width: 18%; }
    #tabelaResultados th:nth-child(5),
    #tabelaResultados td:nth-child(5) { width: 12%; }
    
    #tabelaResultados td {
        padding: 5px 5px !important;
        border: 1px solid #ccc !important;
        color: #333;
        font-size: 9px;
        line-height: 1.3;
    }
    
    #tabelaResultados tbody tr:nth-child(even) {
        background-color: #f5f5f5 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    #tabelaResultados tbody tr:nth-child(odd) {
        background-color: #fff !important;
    }
    
    /* Rodapé da tabela - somente ao FINAL (não repetir por página) */
    #tabelaResultados tfoot { display: table-row-group !important; break-inside: avoid; }
    
    #tabelaResultados tfoot th {
        background-color: #fff !important;
        color: #333 !important;
        border: none !important;
        padding: 6px 4px !important;
        font-size: 11px !important;
        font-weight: bold;
        text-align: right;
    }
    
    #tabelaResultados tfoot th:first-child { text-align: right; }

    /* alinhamento do rodapé de impressão: duas células apenas (label + valor) */
    .print-only { display: table-row !important; }
    .screen-only { display: none !important; }

    /* No rodapé, reexibir a célula do valor total (7ª coluna) ao lado do rótulo */
    #tabelaResultados tfoot th:nth-child(7) { display: table-cell !important; }
    
    /* Valores em destaque */
    #tabelaResultados .text-success {
        color: #28a745 !important;
        font-weight: bold;
    }
    
    /* Badges em impressão - COMPACTO */
    .badge {
        border: 1px solid #333;
        padding: 2px 6px !important;
        font-size: 8px !important;
        border-radius: 3px;
        font-weight: bold;
    }
    
    .badge-success {
        background-color: #d4edda !important;
        color: #155724 !important;
        border-color: #155724 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .badge-danger {
        background-color: #f8d7da !important;
        color: #721c24 !important;
        border-color: #721c24 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .badge-warning {
        background-color: #fff3cd !important;
        color: #856404 !important;
        border-color: #856404 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .badge-dark {
        background-color: #d6d8db !important;
        color: #1b1e21 !important;
        border-color: #1b1e21 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .badge-secondary {
        background-color: #e2e3e5 !important;
        color: #383d41 !important;
        border-color: #383d41 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Rodapé de página */
    @page {
        margin: 1.5cm;
        @bottom-right {
            content: 'Página ' counter(page) ' de ' counter(pages);
            font-size: 10px;
            color: #666;
        }
        @bottom-left {
            content: 'SIGO - Sistema Integrado de Gestão Operacional';
            font-size: 10px;
            color: #666;
        }
    }
    
    /* FORÇAR tabela começar na mesma página */
    * {
        page-break-before: avoid !important;
    }
    
    #printHeader {
        page-break-after: avoid !important;
    }
    
    #resultadosCard {
        page-break-before: avoid !important;
    }
    
    /* Permitir quebra dentro da tabela se necessário */
    tr {
        page-break-inside: avoid;
    }
}
</style>
@stop


