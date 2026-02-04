@extends('adminlte::page')

@section('title', 'Relatório Centro Custo')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="font-weight-bold">
            <i class="fas fa-building text-primary mr-3"></i>
            Relatório Centro Custo
        </h1>
        <p class="text-muted mt-1 mb-0">Relatórios completos por centro de custo</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">
    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-modern">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter mr-2"></i>
                        Filtros de Pesquisa
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formFiltros">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="data_inicio" class="font-weight-bold">Data Início</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="data_fim" class="font-weight-bold">Data Fim</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="centro_custo_id" class="font-weight-bold">Centro de Custo</label>
                                    <select class="form-control" id="centro_custo_id" name="centro_custo_id">
                                        <option value="">Todos os centros</option>
                                    </select>
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>
                                    Gerar Relatório
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" id="btnLimparFiltros">
                                    <i class="fas fa-eraser mr-1"></i>
                                    Limpar Filtros
                                </button>
                                <button type="button" class="btn btn-info ml-2" id="btnImprimir" disabled>
                                    <i class="fas fa-print mr-1"></i>
                                    Imprimir
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo -->
    <div class="row mb-4" id="resumoSection" style="display: none;">
        <div class="col-md-3">
            <div class="card card-modern bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-building fa-2x mb-2"></i>
                    <h3 id="totalCentros">0</h3>
                    <p class="mb-0">Centros de Custo</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-modern bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3 id="totalFuncionarios">0</h3>
                    <p class="mb-0">Funcionários</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-modern bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h3 id="totalMovimentacoes">0</h3>
                    <p class="mb-0">Movimentações</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-modern bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-sort-numeric-up fa-2x mb-2"></i>
                    <h3 id="totalItens">0</h3>
                    <p class="mb-0">Total de Itens</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Resultados -->
    <div class="row" id="resultadosSection" style="display: none;">
        <div class="col-12">
            <div class="card card-modern">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table mr-2"></i>
                        Relatório por Centro de Custo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern" id="tabelaRelatorio">
                            <thead class="table-dark">
                                <tr>
                                    <th width="15%">
                                        <i class="fas fa-building mr-1"></i>
                                        Centro de Custo
                                    </th>
                                    <th width="20%">
                                        <i class="fas fa-user mr-1"></i>
                                        Funcionário
                                    </th>
                                    <th width="15%">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Data/Hora
                                    </th>
                                    <th width="30%">
                                        <i class="fas fa-boxes mr-1"></i>
                                        Produtos Retirados
                                    </th>
                                    <th width="10%">
                                        <i class="fas fa-sort-numeric-down mr-1"></i>
                                        Total Itens
                                    </th>
                                    <th width="10%">
                                        <i class="fas fa-comment mr-1"></i>
                                        Observações
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabelaBody">
                                <!-- Dados serão inseridos via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Mostrando <span id="registroInicio">0</span> a <span id="registroFim">0</span> 
                                de <span id="totalRegistros">0</span> registros
                            </small>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="paginacao">
                                <!-- Paginação será inserida via JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado Inicial -->
    <div class="row" id="estadoInicial">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-building fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Relatório Centro de Custo</h4>
                <p class="text-muted">Configure os filtros acima e clique em "Gerar Relatório" para visualizar os dados por centro de custo</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-modern th {
        background: #343a40;
        color: white;
        font-weight: 600;
        font-size: 13px;
        padding: 12px 8px;
        border: none;
    }
    
    .table-modern td {
        padding: 10px 8px;
        border-bottom: 1px solid #dee2e6;
        font-size: 13px;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge-tipo {
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .badge-secondary {
        background-color: #6c757d;
        color: white;
        font-size: 11px;
        padding: 3px 6px;
        margin-bottom: 2px;
        display: inline-block;
    }
    
    .badge-info {
        background-color: #17a2b8;
        color: white;
        font-size: 12px;
        padding: 4px 8px;
    }
    
    .badge-primary {
        background-color: #007bff;
        color: white;
        font-size: 12px;
        padding: 4px 8px;
        font-weight: bold;
    }

    .badge-center {
        background-color: #28a745;
        color: white;
        font-size: 12px;
        padding: 6px 10px;
        font-weight: bold;
        border-radius: 20px;
    }

    /* Descrição abaixo do nome do produto (tabela on-screen) */
    .produto-desc {
        display: block;
        color: #6c757d;
        font-size: 12px;
        margin-top: 2px;
    }
</style>
@stop

@section('js')
<script>
// Função global para escapar HTML (usada em funções fora do ready)
function escapeHtml(str){ return String(str||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }

$(document).ready(function() {
    // Carregar dados iniciais
    carregarCentrosCusto();
    
    // Configurar data padrão (último mês)
    const hoje = new Date();
    const mesPassado = new Date(hoje.getFullYear(), hoje.getMonth() - 1, hoje.getDate());
    
    $('#data_inicio').val(mesPassado.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);
    
    // Submissão do formulário
    $('#formFiltros').submit(function(e) {
        e.preventDefault();
        gerarRelatorio();
    });
    
    // Limpar filtros
    $('#btnLimparFiltros').click(function() {
        $('#formFiltros')[0].reset();
        $('#data_inicio').val(mesPassado.toISOString().split('T')[0]);
        $('#data_fim').val(hoje.toISOString().split('T')[0]);
        $('#resultadosSection').hide();
        $('#resumoSection').hide();
        $('#estadoInicial').show();
        $('#btnImprimir').prop('disabled', true);
    });
    
    // Botão Imprimir
    $('#btnImprimir').click(function() {
        imprimirRelatorio();
    });
});

function carregarCentrosCusto() {
    $.get('/api/centros-custo')
        .done(function(resp) {
            const centros = (resp && resp.data) ? resp.data : (resp || []);
            let options = '<option value="">Todos os centros</option>';
            centros.forEach(function(centro) {
                options += `<option value="${centro.id}">${escapeHtml(centro.nome)}</option>`;
            });
            $('#centro_custo_id').html(options);
        })
        .fail(function(){
            // fallback para endpoint antigo
            $.get('/api/centro-custos').done(function(centros){
                let options = '<option value="">Todos os centros</option>';
                (centros||[]).forEach(function(centro){
                    options += `<option value="${centro.id}">${escapeHtml(centro.nome)}</option>`;
                });
                $('#centro_custo_id').html(options);
            });
        });
}

// Removido filtro de funcionário

function gerarRelatorio() {
    const formData = new FormData($('#formFiltros')[0]);
    
    // Mostrar loading
    $('#tabelaBody').html(`
        <tr>
            <td colspan="6" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                <br>
                <span class="text-muted">Carregando relatório...</span>
            </td>
        </tr>
    `);
    
    $('#estadoInicial').hide();
    $('#resultadosSection').show();
    $('#resumoSection').show();
    
    $.ajax({
        url: '/api/relatorio-centro-custo',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                preencherTabelaComArmazenamento(response.dados);
                atualizarResumo(response.resumo);
                $('#btnImprimir').prop('disabled', false);
                
                // Atualizar contadores
                $('#totalRegistros').text(response.total_registros);
                $('#registroInicio').text(response.total_registros > 0 ? 1 : 0);
                $('#registroFim').text(response.total_registros);
            } else {
                mostrarErro('Erro ao gerar relatório: ' + response.message);
            }
        },
        error: function(xhr) {
            // Erro na requisição
            let message = 'Erro interno do servidor';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            mostrarErro('Erro ao gerar relatório: ' + message);
        }
    });
}

function atualizarResumo(resumo) {
    $('#totalCentros').text(resumo.total_centros || 0);
    $('#totalFuncionarios').text(resumo.total_funcionarios || 0);
    $('#totalMovimentacoes').text(resumo.total_movimentacoes || 0);
    $('#totalItens').text(resumo.total_itens || 0);
}

function preencherTabela(dados) {
    if (dados.length === 0) {
        $('#tabelaBody').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fas fa-inbox text-muted fa-2x mb-2"></i>
                    <br>
                    <span class="text-muted">Nenhuma movimentação encontrada no período</span>
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    dados.forEach(function(item) {
        let produtosHtml = '';
        item.produtos.forEach(function(produto, index) {
            if (index > 0) produtosHtml += '<br>';
            produtosHtml += `
                <div>
                    <span class="badge badge-secondary mr-1">${escapeHtml(produto.nome)} (${produto.quantidade})</span>
                    ${produto.descricao ? `<small class="produto-desc">${escapeHtml(produto.descricao)}</small>` : ''}
                </div>`;
        });
        
        html += `
            <tr>
                <td>
                    <span class="badge badge-center">${escapeHtml(item.centro_custo.nome)}</span>
                </td>
                <td>
                    <div class="font-weight-bold">${escapeHtml(item.funcionario.nome)}</div>
                    <small class="text-muted">${escapeHtml(item.funcionario.funcao || 'Não informada')}</small>
                </td>
                <td>
                    <div class="font-weight-bold">${item.data}</div>
                    <small class="text-muted">${item.hora}</small>
                </td>
                <td>
                    ${produtosHtml}
                </td>
                <td class="text-center">
                    <span class="badge badge-primary">${item.total_itens}</span>
                </td>
                <td>
                    <small class="text-muted">${item.observacoes || 'Sem observações'}</small>
                </td>
            </tr>
        `;
    });
    
    $('#tabelaBody').html(html);
}

function mostrarErro(mensagem) {
    $('#tabelaBody').html(`
        <tr>
            <td colspan="6" class="text-center py-4">
                <i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>
                <br>
                <span class="text-danger">${mensagem}</span>
            </td>
        </tr>
    `);
    $('#resumoSection').hide();
}

let dadosRelatorio = []; // Variável global para armazenar dados

function preencherTabelaComArmazenamento(dados) {
    dadosRelatorio = dados; // Armazenar dados para impressão e exportação
    preencherTabela(dados);
}

function imprimirRelatorio() {
    if (dadosRelatorio.length === 0) {
        alert('Nenhum dado disponível para impressão. Gere um relatório primeiro.');
        return;
    }
    
    // Obter dados dos filtros
    const dataInicio = $('#data_inicio').val();
    const dataFim = $('#data_fim').val();
    const centroSelecionado = $('#centro_custo_id option:selected').text();
    
    // Criar HTML da impressão
    let htmlImpressao = `
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Relatório de Centro de Custo</title>
        <style>
            @page { 
                size: A4 landscape; 
                margin: 15mm 20mm 15mm 20mm; 
            }
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                font-size: 11px; 
                color: #2c3e50; 
                line-height: 1.4;
                margin: 0;
                padding: 0;
            }
            .header-container {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 3px solid #3498db;
            }
            .logo-section {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .logo {
                width: 60px;
                height: 60px;
                object-fit: contain;
            }
            .company-info h1 {
                font-size: 22px;
                font-weight: 700;
                color: #2c3e50;
                margin: 0 0 5px 0;
                letter-spacing: 0.5px;
            }
            .company-info .subtitle {
                font-size: 14px;
                color: #7f8c8d;
                margin: 0;
                font-weight: 500;
            }
            .report-info {
                text-align: right;
                font-size: 11px;
                color: #5a6c7d;
            }
            .report-info .date {
                font-weight: 600;
                color: #34495e;
                margin-bottom: 8px;
            }
            .filters-section {
                background: #f0f8ff;
                padding: 12px 15px;
                border-radius: 6px;
                margin-bottom: 20px;
                border-left: 4px solid #3498db;
            }
            .filters-title {
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 8px;
                font-size: 12px;
            }
            .filters-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
            }
            .filter-item {
                font-size: 11px;
            }
            .filter-label {
                font-weight: 600;
                color: #34495e;
            }
            .filter-value {
                color: #5a6c7d;
                margin-left: 5px;
            }
            

            
            .table-container {
                margin-bottom: 20px;
            }
            
            table { 
                width: 100%; 
                border-collapse: collapse; 
                font-size: 10px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border-radius: 6px;
                overflow: hidden;
                margin: 15px 0;
            }
            thead {
                background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
                color: white;
            }
            th { 
                padding: 12px 8px;
                font-weight: 600;
                text-align: left;
                font-size: 11px;
                letter-spacing: 0.3px;
                border: none;
            }
            tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            tbody tr:hover {
                background-color: #e3f2fd;
            }
            td { 
                padding: 10px 8px;
                border-bottom: 1px solid #e0e6ed;
                vertical-align: top;
                border-left: none;
                border-right: none;
            }
            
            .funcionario {
                font-weight: bold;
                color: #000;
            }
            
            .funcao {
                font-size: 9px;
                color: #666;
                font-style: italic;
            }
            
            .centro-custo {
                background: #28a745;
                color: white;
                padding: 3px 6px;
                border-radius: 10px;
                font-weight: bold;
                font-size: 9px;
            }

            .centro-custo-mini {
                background: #6c757d;
                color: white;
                padding: 2px 4px;
                border-radius: 5px;
                font-weight: bold;
                font-size: 8px;
            }

            .centro-header td {
                background: #e3f2fd !important;
                color: #1976d2 !important;
                font-weight: bold !important;
                text-align: center !important;
                font-size: 11px !important;
                border: 2px solid #1976d2 !important;
            }

            .centro-total td {
                background: #fff3cd !important;
                border-top: 2px solid #ffc107 !important;
                font-weight: bold !important;
            }

            .total-centro-badge {
                background: #ffc107;
                color: #212529;
                padding: 3px 8px;
                border-radius: 5px;
                font-weight: bold;
                font-size: 10px;
            }

            .centro-separador td {
                border: none !important;
                background: transparent !important;
                height: 15px !important;
            }
            
            .produto-item {
                margin-bottom: 2px;
                padding: 1px 3px;
                background: #f5f5f5;
                border-radius: 2px;
                font-size: 9px;
                border-left: 2px solid #28a745;
            }
            
            .total-badge {
                background: #007bff;
                color: white;
                padding: 2px 6px;
                border-radius: 3px;
                font-weight: bold;
                font-size: 9px;
            }
            
            .data-hora {
                font-weight: bold;
                color: #000;
            }
            
            .hora {
                font-size: 8px;
                color: #666;
            }
            
            .produto-desc-print {
                color: #666;
                font-size: 8px;
                margin-top: 2px;
            }
            .footer {
                margin-top: 25px;
                padding-top: 15px;
                border-top: 2px solid #bdc3c7;
                text-align: center;
                font-size: 10px;
                color: #7f8c8d;
            }
            @media print { 
                .no-print { display: none !important; }
                body { -webkit-print-color-adjust: exact; }
            }
        </style>
    </head>
    <body>
        <div class="header-container">
            <div class="logo-section">
                <img src="/img/brs-logo.png" alt="BRS Logo" class="logo" />
                <div class="company-info">
                    <h1>RELATÓRIO CENTRO DE CUSTO</h1>
                    <p class="subtitle">Movimentações por Centro de Custo</p>
                </div>
            </div>
            <div class="report-info">
                <div class="date">Emitido em: ${new Date().toLocaleString('pt-BR')}</div>
                <div>Total de registros: ${dadosRelatorio.length}</div>
            </div>
        </div>
        
        <div class="filters-section">
            <div class="filters-title">FILTROS APLICADOS</div>
            <div class="filters-grid">
                <div class="filter-item">
                    <span class="filter-label">Período:</span>
                    <span class="filter-value">${dataInicio} até ${dataFim}</span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Centro de Custo:</span>
                    <span class="filter-value">${centroSelecionado}</span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Tipo:</span>
                    <span class="filter-value">Movimentações de Estoque</span>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="20%">Centro de Custo</th>
                        <th width="20%">Funcionário</th>
                        <th width="15%">Data/Hora</th>
                        <th width="30%">Produtos</th>
                        <th width="10%">Total</th>
                        <th width="5%">Obs</th>
                    </tr>
                </thead>
                <tbody>`;
    
    // Agrupar dados por centro de custo para impressão
    const dadosAgrupados = {};
    dadosRelatorio.forEach(function(item) {
        const centroCustoId = item.centro_custo.id || 'sem_centro';
        const centroCustoNome = item.centro_custo.nome || 'Sem Centro de Custo';
        
        if (!dadosAgrupados[centroCustoId]) {
            dadosAgrupados[centroCustoId] = {
                nome: centroCustoNome,
                itens: [],
                totalItens: 0
            };
        }
        
        dadosAgrupados[centroCustoId].itens.push(item);
        dadosAgrupados[centroCustoId].totalItens += item.total_itens;
    });

    // Preencher dados da tabela agrupados por centro de custo
    Object.keys(dadosAgrupados).forEach(function(centroCustoId, index) {
        const grupo = dadosAgrupados[centroCustoId];
        
        // Cabeçalho do centro de custo
        if (index > 0) {
            htmlImpressao += `
                    <tr class="centro-separador">
                        <td colspan="6" style="height: 10px; border: none; background: transparent;"></td>
                    </tr>`;
        }
        
        htmlImpressao += `
                    <tr class="centro-header">
                        <td colspan="6" style="background: #e3f2fd; font-weight: bold; text-align: center; padding: 8px; border: 2px solid #1976d2;">
                            CENTRO DE CUSTO: ${grupo.nome.toUpperCase()}
                        </td>
                    </tr>`;
        
        // Itens do centro de custo
        grupo.itens.forEach(function(item) {
            let produtosHtml = '';
            item.produtos.forEach(function(produto) {
                const desc = produto.descricao ? `<div class=\"produto-desc-print\">${produto.descricao}</div>` : '';
                produtosHtml += `<div class="produto-item">${produto.nome} (${produto.quantidade})${desc}</div>`;
            });
            
            htmlImpressao += `
                        <tr>
                            <td style="background: #f8f9fa;">
                                <span class="centro-custo-mini">${item.centro_custo.nome}</span>
                            </td>
                            <td>
                                <div class="funcionario">${item.funcionario.nome}</div>
                                <div class="funcao">${item.funcionario.funcao || 'Não informada'}</div>
                            </td>
                            <td>
                                <div class="data-hora">${item.data}</div>
                                <div class="hora">${item.hora}</div>
                            </td>
                            <td>
                                ${produtosHtml}
                            </td>
                            <td style="text-align: center;">
                                <span class="total-badge">${item.total_itens}</span>
                            </td>
                            <td>
                                <small>${(item.observacoes || '').substring(0, 20)}${(item.observacoes || '').length > 20 ? '...' : ''}</small>
                            </td>
                        </tr>`;
        });
        
        // Total do centro de custo
        htmlImpressao += `
                    <tr class="centro-total">
                        <td colspan="4" style="background: #fff3cd; font-weight: bold; text-align: right; padding: 8px; border-top: 2px solid #ffc107;">
                            TOTAL ${grupo.nome.toUpperCase()}:
                        </td>
                        <td style="background: #fff3cd; text-align: center; font-weight: bold; border-top: 2px solid #ffc107;">
                            <span class="total-centro-badge">${grupo.totalItens}</span>
                        </td>
                        <td style="background: #fff3cd; border-top: 2px solid #ffc107;"></td>
                    </tr>`;
    });
    
    htmlImpressao += `
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <p>Sistema Integrado de Gestão Operacional (SIGO) - BRS Transportes</p>
        </div>
    </body>
    </html>`;
    
    // Imprimir usando iframe oculto
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);

    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(htmlImpressao);
    doc.close();

    iframe.onload = function(){
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } finally {
            setTimeout(function(){ 
                try { document.body.removeChild(iframe); } catch(e) {}
            }, 1000);
        }
    };
}



function formatarData(data) {
    if (!data) return 'Não especificado';
    return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
}
</script>
@stop