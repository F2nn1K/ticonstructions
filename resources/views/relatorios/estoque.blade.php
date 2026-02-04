@extends('adminlte::page')

@section('title', 'Relatório de Estoque')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="font-weight-bold">
            <i class="fas fa-boxes text-primary mr-3"></i>
            Relatório de Estoque
        </h1>
        <p class="text-muted mt-1 mb-0">Relatórios completos do controle de estoque</p>
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
                                    <label for="produto_id" class="font-weight-bold">Produto</label>
                                    <select class="form-control" id="produto_id" name="produto_id">
                                        <option value="">Todos os produtos</option>
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



    <!-- Tabela de Resultados -->
    <div class="row" id="resultadosSection" style="display: none;">
        <div class="col-12">
            <div class="card card-modern">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-warehouse mr-2"></i>
                        Estoque Atual (Produtos)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern" id="tabelaRelatorio">
                            <thead class="table-dark">
                                <tr>
                                    <th width="80px">ID</th>
                                    <th width="280px">Nome</th>
                                    <th>Descrição</th>
                                    <th width="140px" class="text-right">Quantidade</th>
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
                <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Relatório de Estoque</h4>
                <p class="text-muted">Configure os filtros acima e clique em "Gerar Relatório" para visualizar os dados</p>
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
</style>
@stop

@section('js')
<script>
// Sanitização básica para evitar XSS em HTML injetado via template strings (função global)
function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, function(c){
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]);
    });
}

let dadosRelatorio = [];

$(document).ready(function() {
    // Carregar dados iniciais
    carregarProdutos();
    
    // Configurar data padrão (último mês)
    const hoje = new Date();
    const mesPassado = new Date(hoje.getFullYear(), hoje.getMonth() - 1, hoje.getDate());
    
    $('#data_inicio').val(mesPassado.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);

    // Carregar relatório automaticamente ao abrir (estoque geral)
    gerarRelatorio();
    
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
        $('#estadoInicial').show();
        $('#btnImprimir').prop('disabled', true);
    });
    
    // Botão Imprimir
    $('#btnImprimir').click(function() {
        imprimirRelatorio();
    });
});

function carregarProdutos() {
    $.get('/api/produtos')
        .done(function(produtos) {

            let options = '<option value="">Todos os produtos</option>';
            produtos.forEach(function(produto) {
                options += `<option value="${produto.id}">${escapeHtml(produto.nome)}</option>`;
            });
            $('#produto_id').html(options);
        })
        .fail(function(xhr, status, error) {
            // Silenciar logs no navegador
            $('#produto_id').html('<option value="">Erro ao carregar produtos</option>');
        });
}



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
    
    $.ajax({
        url: '/api/relatorio-estoque',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                dadosRelatorio = Array.isArray(response.dados) ? response.dados : [];
                preencherTabelaEstoque(dadosRelatorio);
                $('#btnImprimir').prop('disabled', false);
                $('#totalRegistros').text(response.total_registros);
                $('#registroInicio').text(response.total_registros > 0 ? 1 : 0);
                $('#registroFim').text(response.total_registros);
            } else {
                mostrarErro('Erro ao gerar relatório: ' + response.message);
            }
        },
        error: function(xhr) {
            // Silenciar logs no navegador
            let message = 'Erro interno do servidor';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            mostrarErro('Erro ao gerar relatório: ' + message);
        }
    });
}

function preencherTabelaEstoque(dados) {
    if (!dados || dados.length === 0) {
        $('#tabelaBody').html(`
            <tr>
                <td colspan="4" class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <br>
                    <span class="text-muted">Nenhum produto encontrado</span>
                </td>
            </tr>
        `);
        return;
    }
    let html = '';
    dados.forEach(function(p, index){
        const rowClass = index % 2 === 0 ? '' : 'table-light';
        const desc = p.descricao && p.descricao.trim() !== '' ? p.descricao : 'Sem descrição';
        html += `
            <tr class="${rowClass}">
                <td>${p.id}</td>
                <td><strong>${escapeHtml(p.nome)}</strong></td>
                <td><small class="text-muted">${escapeHtml(desc)}</small></td>
                <td class="text-right"><span class="badge badge-primary">${Number(p.quantidade).toLocaleString('pt-BR')}</span></td>
            </tr>
        `;
    });
    $('#tabelaBody').html(html);
}

function mostrarErro(mensagem) {
    $('#tabelaBody').html(`
        <tr>
            <td colspan="4" class="text-center py-4">
                <i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>
                <br>
                    <span class="text-danger">${escapeHtml(mensagem)}</span>
            </td>
        </tr>
    `);
}
function imprimirRelatorio() {
    if (!dadosRelatorio || dadosRelatorio.length === 0) {
        alert('Nenhum dado disponível para impressão. Gere um relatório primeiro.');
        return;
    }

    // Obter dados dos filtros para o cabeçalho
    const dataInicio = $('#data_inicio').val() || 'Não informado';
    const dataFim = $('#data_fim').val() || 'Não informado';
    const produtoSel = $('#produto_id option:selected').text() || 'Todos os produtos';
    
    // Criar HTML da impressão (estoque geral)
    let htmlImpressao = `
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Relatório de Estoque Atual</title>
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
            .qtd { 
                text-align: right; 
                white-space: nowrap;
                font-weight: 600;
                color: #2980b9;
            }
            .total-summary {
                background: #f8f9fa;
                padding: 12px;
                border-radius: 6px;
                margin-top: 15px;
                border-left: 4px solid #27ae60;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                font-weight: 600;
                color: #2c3e50;
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
                    <h1>RELATÓRIO DE ESTOQUE</h1>
                    <p class="subtitle">Controle de Inventário e Produtos</p>
                </div>
            </div>
            <div class="report-info">
                <div class="date">Emitido em: ${new Date().toLocaleString('pt-BR')}</div>
                <div>Total de produtos: ${dadosRelatorio.length}</div>
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
                    <span class="filter-label">Produto:</span>
                    <span class="filter-value">${produtoSel}</span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Tipo:</span>
                    <span class="filter-value">Estoque Atual</span>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 260px;">Nome</th>
                    <th>Descrição</th>
                    <th style="width: 120px;" class="qtd">Quantidade</th>
                </tr>
            </thead>
            <tbody>`;

    let total = 0;
    dadosRelatorio.forEach(function(p){
        const desc = (p.descricao || '').trim() || 'Sem descrição';
        total += Number(p.quantidade || 0);
        htmlImpressao += `
            <tr>
                <td>${p.id}</td>
                <td>${escapeHtml(p.nome)}</td>
                <td>${escapeHtml(desc)}</td>
                <td class="qtd">${Number(p.quantidade||0).toLocaleString('pt-BR')}</td>
            </tr>`;
    });

    htmlImpressao += `
            </tbody>
        </table>
        
        <div class="total-summary">
            <div class="total-row">
                <span>TOTAL DE ITENS EM ESTOQUE:</span>
                <span>${total.toLocaleString('pt-BR')} unidades</span>
            </div>
        </div>
        
        <div class="footer">
            <p>Sistema Integrado de Gestão Operacional (SIGO) - BRS Transportes</p>
        </div>
    </body>
    </html>`;

    // Imprimir sem abrir nova aba: usar iframe oculto
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
            setTimeout(function(){ document.body.removeChild(iframe); }, 400);
        }
    };
}

function formatarData(data) {
    if (!data) return 'Não especificado';
    return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
}

function contarTotalItens() {
    return dadosRelatorio.reduce((total, item) => total + item.total_itens, 0);
}

function contarFuncionarios() {
    const funcionarios = new Set();
    dadosRelatorio.forEach(item => funcionarios.add(item.funcionario.id));
    return funcionarios.size;
}

function contarCentros() {
    const centros = new Set();
    dadosRelatorio.forEach(item => centros.add(item.centro_custo.id));
    return centros.size;
}
</script>
@stop