@extends('adminlte::page')

@section('title', __('Duplicar Pedido de Compras'))

@section('plugins.Sweetalert2', true)
 

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-copy text-info mr-2"></i>{{ __('Duplicar Pedido de Compras') }}</h1>
@stop

@section('content')
<style>
    /* Dark mode para esta página - Alta especificidade */
    html[data-theme="dark"] .d-none.d-md-block.table-responsive,
    html[data-theme="dark"] div.table-responsive,
    html[data-theme="dark"] .table-responsive {
        background-color: #1e293b !important;
    }
    html[data-theme="dark"] #tabelaPedidos,
    html[data-theme="dark"] table#tabelaPedidos,
    html[data-theme="dark"] .table#tabelaPedidos {
        background-color: #1e293b !important;
    }
    html[data-theme="dark"] #tabelaPedidos tbody,
    html[data-theme="dark"] table#tabelaPedidos tbody,
    html[data-theme="dark"] #tabelaPedidos > tbody {
        background-color: #1e293b !important;
    }
    html[data-theme="dark"] #tabelaPedidos tbody tr,
    html[data-theme="dark"] #tabelaPedidos tbody tr td,
    html[data-theme="dark"] table#tabelaPedidos tbody tr td {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
    }
    html[data-theme="dark"] .card-body.p-0,
    html[data-theme="dark"] .p-0.card-body,
    html[data-theme="dark"] div.card-body.p-0 {
        background-color: #1e293b !important;
    }
    html[data-theme="dark"] .bg-light,
    html[data-theme="dark"] div.bg-light,
    html[data-theme="dark"] .p-3.border-bottom.bg-light {
        background-color: #1e293b !important;
    }
    /* Força a área vazia */
    html[data-theme="dark"] #empty-row,
    html[data-theme="dark"] td#empty-row,
    html[data-theme="dark"] td[id="empty-row"] {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
    }
</style>
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-list mr-2"></i>{{ __('Meus Pedidos Anteriores') }}</h3>
    <span class="text-muted small">{{ __('Selecione um pedido para duplicar') }}</span>
  </div>
  <div class="card-body p-0">
    <div class="alert alert-info mx-3 mt-3 mb-0">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Como funciona:</strong> Selecione um pedido da lista abaixo para criar uma cópia idêntica. 
        O novo pedido terá todos os mesmos produtos, quantidades e informações do pedido original, 
        mas será criado com status pendente para nova aprovação.
    </div>
    
    <!-- Filtros -->
    <div class="p-3 border-bottom bg-light">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label for="filtroDataInicio" class="form-label mb-1"><small><strong>Data Inicial</strong></small></label>
                <input type="date" class="form-control form-control-sm" id="filtroDataInicio">
            </div>
            <div class="col-md-3">
                <label for="filtroDataFim" class="form-label mb-1"><small><strong>Data Final</strong></small></label>
                <input type="date" class="form-control form-control-sm" id="filtroDataFim">
            </div>
            <div class="col-md-4">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.aplicarFiltros()">
                        <i class="fas fa-search mr-1"></i>Filtrar
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.limparFiltros()">
                        <i class="fas fa-eraser mr-1"></i>Limpar
                    </button>
                </div>
            </div>
            <div class="col-md-2 text-right">
                <small class="text-muted">
                    <span id="totalPedidos">0</span> pedido(s) encontrado(s)
                </small>
            </div>
        </div>
    </div>
    
    <!-- Desktop/Tablet -->
    <div class="d-none d-md-block table-responsive">
        <table id="tabelaPedidos" class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Nº Pedido</th>
                    <th>Data</th>
                    <th>Centro de Custo</th>
                    <th>Prioridade</th>
                    <th>Total de Itens</th>
                    <th>Produtos</th>
                    <th class="text-nowrap">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="text-center text-muted" id="empty-row">
                        <i class="fas fa-copy fa-2x mb-2 text-info"></i><br>
                        Carregando seus pedidos anteriores...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mobile: lista de cartões com botão Duplicar visível -->
    <div class="d-block d-md-none p-2">
        <div id="listaPedidosMobile" class="list-group"></div>
    </div>
  </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalConfirmarDuplicacao" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-copy text-info mr-2"></i>
                    Confirmar Duplicação de Pedido
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Atenção:</strong> Você está prestes a duplicar o pedido selecionado. 
                    Uma cópia exata será criada com todos os produtos e informações originais.
                </div>
                
                <p class="mb-2"><strong>Detalhes do Pedido Original:</strong></p>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Número:</strong> <span id="modal_num_pedido">-</span></p>
                        <p class="mb-1"><strong>Data:</strong> <span id="modal_data">-</span></p>
                        <p class="mb-1"><strong>Centro de Custo:</strong> <span id="modal_centro_custo">-</span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Prioridade:</strong> <span id="modal_prioridade">-</span></p>
                        <p class="mb-1"><strong>Total de Itens:</strong> <span id="modal_total_itens">-</span></p>
                    </div>
                </div>
                <hr/>
                <p class="mb-2"><strong>Produtos:</strong></p>
                <div id="modal_produtos" class="bg-light p-3 rounded" style="max-height: 150px; overflow-y: auto;">-</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-info" id="btnConfirmarDuplicacao">
                    <i class="fas fa-copy mr-1"></i>
                    Confirmar Duplicação
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .produtos-resumo {
        font-size: 0.9em;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@stop

@section('js')
<script>
// Função para formatar data no padrão brasileiro (DD/MM/AAAA HH:MM)
function formatarDataBR(dataISO) {
    if (!dataISO) return '—';
    const data = new Date(dataISO);
    if (isNaN(data.getTime())) return '—';
    
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();
    const hora = String(data.getHours()).padStart(2, '0');
    const minuto = String(data.getMinutes()).padStart(2, '0');
    
    return `${dia}/${mes}/${ano} ${hora}:${minuto}`;
}

// Função para formatar prioridade com badge
function formatarPrioridade(prioridade) {
    const badges = {
        'baixa': '<span class="badge badge-success">Baixa</span>',
        'media': '<span class="badge badge-warning">Média</span>',
        'alta': '<span class="badge badge-danger">Alta</span>'
    };
    return badges[prioridade] || `<span class="badge badge-secondary">${prioridade || 'N/A'}</span>`;
}

// Variáveis globais
let tabelaPedidos;
let pedidoSelecionado = null;

// Função para aplicar filtros
window.aplicarFiltros = function() {
    const dataInicio = $('#filtroDataInicio').val();
    const dataFim = $('#filtroDataFim').val();
    
    // Validar se data fim não é anterior à data início
    if (dataInicio && dataFim && dataFim < dataInicio) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'A data final não pode ser anterior à data inicial.'
        });
        return;
    }
    
    const filtros = {};
    if (dataInicio) filtros.dataInicio = dataInicio;
    if (dataFim) filtros.dataFim = dataFim;
    
    carregarPedidos(filtros);
}

// Função para limpar filtros
window.limparFiltros = function() {
    $('#filtroDataInicio').val('');
    $('#filtroDataFim').val('');
    carregarPedidos();
}

// (Removido) Função de atalhos rápidos – não utilizada

$(document).ready(function() {
    // Configurar filtros padrão (últimos 30 dias)
    const hoje = new Date();
    const trintaDiasAtras = new Date();
    trintaDiasAtras.setDate(hoje.getDate() - 30);
    
    $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);
    $('#filtroDataInicio').val(trintaDiasAtras.toISOString().split('T')[0]);
    
    // Limpar tbody
    $('#tabelaPedidos tbody').empty();
    
    // Carregar dados dos pedidos com filtro padrão
    setTimeout(function() {
        window.aplicarFiltros();
    }, 100);
    
    // Confirmar duplicação
    $('#btnConfirmarDuplicacao').on('click', function() {
        if (pedidoSelecionado) {
            duplicarPedido(pedidoSelecionado);
        }
    });
    
    // Permitir filtrar ao pressionar Enter nos campos de data
    $('#filtroDataInicio, #filtroDataFim').on('keypress', function(e) {
        if (e.which === 13) { // Enter
            window.aplicarFiltros();
        }
    });
    
    window.carregarPedidos = function(filtros = {}) {
        const params = new URLSearchParams();
        
        if (filtros.dataInicio) {
            params.append('data_inicio', filtros.dataInicio);
        }
        if (filtros.dataFim) {
            params.append('data_fim', filtros.dataFim);
        }
        
        const url = '/api/pedidos/meus-pedidos' + (params.toString() ? '?' + params.toString() : '');
        
        // (logs removidos para produção)
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                //
                
                if (response.success) {
                    const dados = response.data || [];
                    window.__ULTIMOS_PEDIDOS__ = dados;
                    if (dados.length > 0) {
                        // Render simples no tbody
                        const rows = dados.map(function(item){
                            return '\
                                <tr>\
                                    <td>' + (item.num_pedido || '') + '</td>\
                                    <td>' + (item.data_solicitacao ? formatarDataBR(item.data_solicitacao) : '—') + '</td>\
                                    <td>' + (item.centro_custo_nome || '—') + '</td>\
                                    <td>' + (item.prioridade ? $(formatarPrioridade(item.prioridade)).text() : '—') + '</td>\
                                    <td>' + (item.total_itens || 0) + '</td>\
                                    <td><div class="produtos-resumo" title="' + (item.produtos_resumo || '') + '">' + (item.produtos_resumo || '—') + '</div></td>\
                                    <td>\
                                        <button type="button" class="btn btn-info btn-sm" onclick="window.abrirModalDuplicacao(\'' + item.num_pedido + '\')">\
                                            <i class="fas fa-copy"></i>\
                                        </button>\
                                    </td>\
                                </tr>';
                        }).join('');
                        $('#tabelaPedidos tbody').html(rows);
                        $('#totalPedidos').text(dados.length);

                        // Render mobile list (cards) com botão Duplicar visível
                        const cards = dados.map(function(item){
                            const prioridadeHTML = formatarPrioridade(item.prioridade);
                            return `
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="font-weight-bold">${item.num_pedido || ''}</div>
                                            <small class="text-muted">${item.data_solicitacao ? formatarDataBR(item.data_solicitacao) : '—'}</small>
                                        </div>
                                        <button type="button" class="btn btn-info btn-sm" onclick="window.abrirModalDuplicacao('${item.num_pedido}')">
                                            <i class="fas fa-copy mr-1"></i> Duplicar
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <div class="d-flex justify-content-between">
                                            <div><small>Centro de Custo</small><br><strong>${item.centro_custo_nome || '—'}</strong></div>
                                            <div class="text-right"><small>Prioridade</small><br>${prioridadeHTML}</div>
                                        </div>
                                        <div class="mt-2"><small>Produtos</small><br><div class="produtos-resumo" title="${item.produtos_resumo || ''}">${item.produtos_resumo || '—'}</div></div>
                                    </div>
                                </div>`;
                        }).join('');
                        $('#listaPedidosMobile').html(cards);
                    } else {
                        //
                        
                        $('#tabelaPedidos tbody').html('\
                            <tr>\
                                <td colspan="7" class="text-center text-muted">\
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>\
                                    Nenhum pedido encontrado para o período selecionado\
                                </td>\
                            </tr>\
                        ');
                        $('#totalPedidos').text('0');
                        $('#listaPedidosMobile').html('<div class="list-group-item text-center text-muted"><i class="fas fa-inbox fa-2x mb-2"></i><br>Nenhum pedido encontrado para o período selecionado</div>');
                    }
                } else {
                    //
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao carregar pedidos',
                        footer: response.debug ? '<small>Debug: ' + JSON.stringify(response.debug) + '</small>' : ''
                    });
                    $('#totalPedidos').text('0');
                }
            },
            error: function(xhr) {
                //
                
                let debugInfo = '';
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    debugInfo = errorResponse.debug ? JSON.stringify(errorResponse.debug) : '';
                } catch (e) {
                    debugInfo = 'Erro ao parsear resposta';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao carregar pedidos. Tente novamente.',
                    footer: debugInfo ? '<small>Detalhes: ' + debugInfo + '</small>' : ''
                });
                $('#totalPedidos').text('0');
            }
        });
    }


// Função para abrir modal de duplicação (modo sem DataTables)
window.abrirModalDuplicacao = function(numPedido) {
    // Procurar o item em um cache simples do último carregamento
    const linhas = window.__ULTIMOS_PEDIDOS__ || [];
    const pedido = linhas.find(p => String(p.num_pedido) === String(numPedido));
    
    if (!pedido) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Pedido não encontrado'
        });
        return;
    }
    
    // Preencher modal
    $('#modal_num_pedido').text(pedido.num_pedido);
    $('#modal_data').text(formatarDataBR(pedido.data_solicitacao));
    $('#modal_centro_custo').text(pedido.centro_custo_nome || '—');
    $('#modal_prioridade').html(formatarPrioridade(pedido.prioridade));
    $('#modal_total_itens').text(pedido.total_itens);
    // Buscar itens reais do pedido para garantir a lista completa
    $('#modal_produtos').html('<span class="text-muted">Carregando itens...</span>');
    $.get('/api/pedidos/itens/' + encodeURIComponent(pedido.num_pedido), function(r){
        if(r && r.success && r.data && Array.isArray(r.data.itens)){
            if(r.data.itens.length === 0){
                $('#modal_produtos').text('Nenhum produto encontrado');
            } else {
                const lista = r.data.itens.map(function(i){
                    const qtd = parseInt(i.quantidade || 0, 10);
                    return $('<div/>').text((i.produto_nome||'') + ' (' + (isNaN(qtd)?0:qtd) + ')')[0].outerHTML;
                }).join('');
                $('#modal_produtos').html(lista);
            }
        } else {
            // fallback para resumo pré-carregado
            $('#modal_produtos').text(pedido.produtos_resumo || 'Nenhum produto encontrado');
        }
    }).fail(function(){
        $('#modal_produtos').text(pedido.produtos_resumo || 'Nenhum produto encontrado');
    });
    
    pedidoSelecionado = numPedido;
    $('#modalConfirmarDuplicacao').modal('show');
}
    

});

// Função para duplicar pedido
function duplicarPedido(numPedido) {
    // Mostrar loading no botão
    const btn = $('#btnConfirmarDuplicacao');
    const textoOriginal = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Duplicando...').prop('disabled', true);
    
    $.ajax({
        url: `/api/pedidos/duplicar/${numPedido}`,
        method: 'POST',
        success: function(response) {
            btn.html(textoOriginal).prop('disabled', false);
            $('#modalConfirmarDuplicacao').modal('hide');
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: response.message,
                    footer: `<small>Novo pedido: ${response.novo_pedido}<br>Total de itens: ${response.total_itens}</small>`
                }).then(() => {
                    // Recarregar a tabela para mostrar o novo pedido
                    window.aplicarFiltros();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: response.message || 'Erro ao duplicar pedido'
                });
            }
        },
        error: function(xhr) {
            btn.html(textoOriginal).prop('disabled', false);
            console.error('Erro:', xhr);
            
            let mensagem = 'Erro ao duplicar pedido. Tente novamente.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensagem = xhr.responseJSON.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: mensagem
            });
        }
    });
}

// Função para aplicar estilos do modo escuro na tabela
function aplicarDarkModeTabela() {
    if (document.documentElement.getAttribute('data-theme') === 'dark') {
        const elementos = [
            document.querySelector('.d-none.d-md-block.table-responsive'),
            document.querySelector('#tabelaPedidos'),
            document.querySelector('#tabelaPedidos tbody'),
            document.querySelector('.card-body.p-0'),
            document.querySelector('.bg-light')
        ];
        
        elementos.forEach(el => {
            if (el) el.style.backgroundColor = '#1e293b';
        });
        
        document.querySelectorAll('#tabelaPedidos tbody tr').forEach(tr => {
            tr.style.backgroundColor = '#1e293b';
        });
        
        document.querySelectorAll('#tabelaPedidos tbody tr td').forEach(td => {
            td.style.backgroundColor = '#1e293b';
            td.style.color = '#94a3b8';
        });
    }
}

// Aplica o dark mode quando a página carrega e após carregamentos AJAX
$(document).ready(function() {
    aplicarDarkModeTabela();
});

// Observer para aplicar quando conteúdo muda (após AJAX)
const observer = new MutationObserver(function(mutations) {
    aplicarDarkModeTabela();
});
observer.observe(document.body, { childList: true, subtree: true });
</script>
@stop
