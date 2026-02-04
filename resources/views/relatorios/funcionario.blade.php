@extends('adminlte::page')

@section('title', 'Relatório por Funcionário')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="font-weight-bold">
            <i class="fas fa-user-tie text-primary mr-3"></i>
            Relatório por Funcionário
        </h1>
        <p class="text-muted mt-1 mb-0">Relatório de entregas de material organizadas por funcionário</p>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="data_inicio" class="font-weight-bold">Data Início</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="data_fim" class="font-weight-bold">Data Fim</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="funcionario_busca" class="font-weight-bold">Funcionário</label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control" id="funcionario_busca" 
                                               placeholder="Digite o nome do funcionário..." autocomplete="off">
                                        <input type="hidden" id="funcionario_id" name="funcionario_id" value="">
                                        <div id="funcionarios_lista" class="autocomplete-dropdown" style="display: none;">
                                            <!-- Lista de funcionários será preenchida dinamicamente -->
                                        </div>
                                    </div>
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
                        <i class="fas fa-table mr-2"></i>
                        Relatório por Funcionário
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern" id="tabelaRelatorio">
                            <thead class="table-dark">
                                <tr>
                                    <th width="20%">
                                        <i class="fas fa-user mr-1"></i>
                                        Funcionário
                                    </th>
                                    <th width="15%">
                                        <i class="fas fa-building mr-1"></i>
                                        Centro de Custo
                                    </th>
                                    <th width="15%">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Data/Hora da Entrega
                                    </th>
                                    <th width="30%">
                                        <i class="fas fa-boxes mr-1"></i>
                                        Material Entregue
                                    </th>
                                    <th width="10%">
                                        <i class="fas fa-sort-numeric-down mr-1"></i>
                                        Quantidade
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
                <i class="fas fa-user-tie fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Relatório por Funcionário</h4>
                <p class="text-muted">Configure os filtros acima e clique em "Gerar Relatório" para visualizar as entregas de material por funcionário</p>
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
    
    .badge-funcionario {
        background-color: #28a745;
        color: white;
        font-size: 12px;
        padding: 6px 10px;
        font-weight: bold;
        border-radius: 20px;
    }
    
    .badge-centro {
        background-color: #17a2b8;
        color: white;
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .badge-produto {
        background-color: #6c757d;
        color: white;
        font-size: 11px;
        padding: 3px 6px;
        margin-bottom: 2px;
        display: inline-block;
    }
    .produto-desc {
        display: block;
        color: #6c757d;
        font-size: 12px;
        margin-top: 2px;
    }
    
    .badge-quantidade {
        background-color: #007bff;
        color: white;
        font-size: 12px;
        padding: 4px 8px;
        font-weight: bold;
    }

    /* Estilos para o autocomplete */
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .autocomplete-item {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f8f9fa;
        transition: background-color 0.2s;
    }

    .autocomplete-item:hover,
    .autocomplete-item.active {
        background-color: #e9ecef;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .funcionario-nome {
        font-weight: bold;
        color: #495057;
    }

    .funcionario-funcao {
        font-size: 12px;
        color: #6c757d;
        margin-top: 2px;
    }

    .autocomplete-clear {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
        font-size: 14px;
        padding: 2px;
        border-radius: 2px;
    }

    .autocomplete-clear:hover {
        color: #dc3545;
        background-color: #f8f9fa;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Carregar dados iniciais
    carregarFuncionarios();
    
    // Configurar autocomplete de funcionários
    configurarAutocompleteFuncionarios();
    
    // Configurar data padrão (último mês)
    const hoje = new Date();
    const mesPassado = new Date(hoje.getFullYear(), hoje.getMonth() - 1, hoje.getDate());
    
    $('#data_inicio').val(mesPassado.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);

    // Controle de toque para sincronizar datas quando o usuário selecionar apenas uma
    let tocouInicio = false;
    let tocouFim = false;

    $('#data_inicio').on('change', function() {
        tocouInicio = true;
        // Se o usuário ainda não tocou na data fim, igualar para filtrar um único dia
        if (!tocouFim) {
            $('#data_fim').val($(this).val());
        }
    });

    $('#data_fim').on('change', function() {
        tocouFim = true;
        // Se o usuário ainda não tocou na data início, igualar para filtrar um único dia
        if (!tocouInicio) {
            $('#data_inicio').val($(this).val());
        }
    });
    
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
        tocouInicio = false;
        tocouFim = false;
        $('#funcionario_busca').val('');
        $('#funcionario_id').val('');
        $('#funcionarios_lista').hide();
        $('#limpar_funcionario').hide();
        $('#resultadosSection').hide();
        $('#estadoInicial').show();
        $('#btnImprimir').prop('disabled', true);
    });
    
    // Botão Imprimir
    $('#btnImprimir').click(function() {
        imprimirRelatorio();
    });
});

let funcionariosData = []; // Armazenar dados dos funcionários

function carregarFuncionarios() {
    $.get('/api/funcionarios')
        .done(function(funcionarios) {
            funcionariosData = funcionarios;
        })
        .fail(function() {
            // Silenciar logs no navegador
        });
}

function configurarAutocompleteFuncionarios() {
    const $buscaInput = $('#funcionario_busca');
    const $listaDiv = $('#funcionarios_lista');
    const $hiddenInput = $('#funcionario_id');
    let currentIndex = -1;
    
    // Evento de digitação
    $buscaInput.on('input', function() {
        const termo = $(this).val().toLowerCase();
        currentIndex = -1;
        
        if (termo.length >= 2) {
            const funcionariosFiltrados = funcionariosData.filter(func => 
                func.nome.toLowerCase().includes(termo)
            );
            
            if (funcionariosFiltrados.length > 0) {
                mostrarListaFuncionarios(funcionariosFiltrados);
            } else {
                $listaDiv.hide();
            }
        } else {
            $listaDiv.hide();
        }
        
        // Limpar seleção se não há texto
        if (termo === '') {
            $hiddenInput.val('');
        }
    });
    
    // Navegação com teclado
    $buscaInput.on('keydown', function(e) {
        const $items = $listaDiv.find('.autocomplete-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentIndex = currentIndex < $items.length - 1 ? currentIndex + 1 : 0;
            destacarItem($items, currentIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentIndex = currentIndex > 0 ? currentIndex - 1 : $items.length - 1;
            destacarItem($items, currentIndex);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentIndex >= 0 && currentIndex < $items.length) {
                $items.eq(currentIndex).click();
            }
        } else if (e.key === 'Escape') {
            $listaDiv.hide();
        }
    });
    
    // Fechar lista quando clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#funcionario_busca, #funcionarios_lista').length) {
            $listaDiv.hide();
        }
    });
    
    // Adicionar botão limpar
    $buscaInput.parent().append('<span class="autocomplete-clear" id="limpar_funcionario" style="display: none;">✕</span>');
    
    // Mostrar/ocultar botão limpar
    $buscaInput.on('input', function() {
        const $clearBtn = $('#limpar_funcionario');
        if ($(this).val()) {
            $clearBtn.show();
        } else {
            $clearBtn.hide();
        }
    });
    
    // Função do botão limpar
    $('#limpar_funcionario').on('click', function() {
        $buscaInput.val('');
        $hiddenInput.val('');
        $listaDiv.hide();
        $(this).hide();
        $buscaInput.focus();
    });
}

function mostrarListaFuncionarios(funcionarios) {
    const $listaDiv = $('#funcionarios_lista');
    let html = '';
    
    // Limitar a 8 resultados para não sobrecarregar
    funcionarios.slice(0, 8).forEach(function(funcionario) {
        html += `
            <div class="autocomplete-item" data-id="${funcionario.id}" data-nome="${funcionario.nome}">
                <div class="funcionario-nome">${funcionario.nome}</div>
                <div class="funcionario-funcao">${funcionario.funcao || 'Função não informada'}</div>
            </div>
        `;
    });
    
    if (funcionarios.length > 8) {
        html += `<div class="autocomplete-item" style="background: #f8f9fa; text-align: center; font-style: italic; color: #6c757d;">
                    +${funcionarios.length - 8} funcionários... Digite mais caracteres para filtrar
                 </div>`;
    }
    
    $listaDiv.html(html).show();
    
    // Evento de clique nos itens
    $listaDiv.find('.autocomplete-item[data-id]').on('click', function() {
        const funcionarioId = $(this).data('id');
        const funcionarioNome = $(this).data('nome');
        
        $('#funcionario_busca').val(funcionarioNome);
        $('#funcionario_id').val(funcionarioId);
        $('#limpar_funcionario').show();
        $listaDiv.hide();
    });
}

function destacarItem($items, index) {
    $items.removeClass('active');
    if (index >= 0 && index < $items.length) {
        $items.eq(index).addClass('active');
    }
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
        url: '/api/relatorio-funcionario',
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
            let message = 'Erro interno do servidor';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            mostrarErro('Erro ao gerar relatório: ' + message);
        }
    });
}



function preencherTabela(dados) {
    if (dados.length === 0) {
        $('#tabelaBody').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fas fa-inbox text-muted fa-2x mb-2"></i>
                    <br>
                    <span class="text-muted">Nenhuma entrega encontrada no período</span>
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
                    <span class="badge badge-produto mr-1">${produto.nome}</span>
                    ${produto.descricao ? `<small class="produto-desc">${produto.descricao}</small>` : ''}
                </div>`;
        });
        
        html += `
            <tr>
                <td>
                    <span class="badge badge-funcionario">${item.funcionario.nome}</span>
                    <br>
                    <small class="text-muted">${item.funcionario.funcao || 'Função não informada'}</small>
                </td>
                <td>
                    <span class="badge badge-centro">${item.centro_custo.nome}</span>
                </td>
                <td>
                    <div class="font-weight-bold">${item.data}</div>
                    <small class="text-muted">${item.hora}</small>
                </td>
                <td>
                    ${produtosHtml}
                </td>
                <td class="text-center">
                    <span class="badge badge-quantidade">${item.total_itens}</span>
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
}

let dadosRelatorio = []; // Variável global para armazenar dados

function preencherTabelaComArmazenamento(dados) {
    dadosRelatorio = dados; // Armazenar dados para impressão
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
    const funcionarioSelecionado = $('#funcionario_id option:selected').text();
    const centroSelecionado = $('#centro_custo_id option:selected').text();
    
    // Criar HTML da impressão
    let htmlImpressao = `
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Relatório por Funcionário</title>
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
            
            .funcionario-nome {
                font-weight: 600;
                color: #2c3e50;
            }
            .funcionario-funcao {
                font-size: 9px;
                color: #7f8c8d;
                font-style: italic;
            }
            .funcionario-badge {
                background: #3498db;
                color: white;
                padding: 4px 8px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 10px;
            }
            .centro-custo {
                background: #2980b9;
                color: white;
                padding: 3px 6px;
                border-radius: 4px;
                font-weight: 600;
                font-size: 10px;
            }
            .produto-item {
                margin-bottom: 3px;
                padding: 2px 6px;
                background: #f8f9fa;
                border-radius: 4px;
                font-size: 9px;
                border-left: 3px solid #3498db;
                color: #2c3e50;
            }
            .quantidade-badge {
                background: #27ae60;
                color: white;
                padding: 3px 8px;
                border-radius: 4px;
                font-weight: 600;
                font-size: 10px;
            }
            .data-hora {
                font-weight: 600;
                color: #2c3e50;
            }
            .hora {
                font-size: 9px;
                color: #7f8c8d;
            }
            .funcionario-header td {
                background: #e3f2fd !important;
                color: #1976d2 !important;
                font-weight: bold !important;
                text-align: center !important;
                font-size: 11px !important;
                border: 2px solid #3498db !important;
            }
            .funcionario-total td {
                background: #f0f8ff !important;
                border-top: 2px solid #3498db !important;
                font-weight: bold !important;
            }
            .total-funcionario-badge {
                background: #3498db;
                color: white;
                padding: 4px 10px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 10px;
            }
            .funcionario-separador td {
                border: none !important;
                background: transparent !important;
                height: 15px !important;
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
                    <h1>RELATÓRIO POR FUNCIONÁRIO</h1>
                    <p class="subtitle">Entregas de Material por Funcionário</p>
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
                    <span class="filter-label">Funcionário:</span>
                    <span class="filter-value">${$('#funcionario_busca').val() || 'Todos'}</span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Tipo:</span>
                    <span class="filter-value">Entregas de Material</span>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="25%">Funcionário</th>
                        <th width="15%">Centro de Custo</th>
                        <th width="15%">Data/Hora</th>
                        <th width="30%">Material</th>
                        <th width="10%">Qtd</th>
                        <th width="5%">Obs</th>
                    </tr>
                </thead>
                <tbody>`;
    
    // Agrupar dados por funcionário para impressão
    const dadosAgrupados = {};
    dadosRelatorio.forEach(function(item) {
        const funcionarioId = item.funcionario.id;
        const funcionarioNome = item.funcionario.nome;
        
        if (!dadosAgrupados[funcionarioId]) {
            dadosAgrupados[funcionarioId] = {
                nome: funcionarioNome,
                funcao: item.funcionario.funcao || 'Não informada',
                itens: [],
                totalItens: 0
            };
        }
        
        dadosAgrupados[funcionarioId].itens.push(item);
        dadosAgrupados[funcionarioId].totalItens += item.total_itens;
    });

    // Preencher dados da tabela agrupados por funcionário
    Object.keys(dadosAgrupados).forEach(function(funcionarioId, index) {
        const grupo = dadosAgrupados[funcionarioId];
        
        // Separador entre funcionários
        if (index > 0) {
            htmlImpressao += `
                    <tr class="funcionario-separador">
                        <td colspan="6" style="height: 10px; border: none; background: transparent;"></td>
                    </tr>`;
        }
        
        // Cabeçalho do funcionário
        htmlImpressao += `
                    <tr class="funcionario-header">
                        <td colspan="6" style="background: #e8f5e8; font-weight: bold; text-align: center; padding: 8px; border: 2px solid #28a745;">
                            FUNCIONÁRIO: ${grupo.nome.toUpperCase()} - ${grupo.funcao.toUpperCase()}
                        </td>
                    </tr>`;
        
        // Itens do funcionário
        grupo.itens.forEach(function(item) {
            let produtosHtml = '';
            item.produtos.forEach(function(produto) {
                const desc = produto.descricao ? `<div class=\"produto-desc-print\">${produto.descricao}</div>` : '';
                produtosHtml += `<div class="produto-item">${produto.nome} (${produto.quantidade})${desc}</div>`;
            });
            
            htmlImpressao += `
                        <tr>
                            <td>
                                <span class="funcionario-badge">${item.funcionario.nome}</span>
                            </td>
                            <td>
                                <span class="centro-custo">${item.centro_custo.nome}</span>
                            </td>
                            <td>
                                <div class="data-hora">${item.data}</div>
                                <div class="hora">${item.hora}</div>
                            </td>
                            <td>
                                ${produtosHtml}
                            </td>
                            <td style="text-align: center;">
                                <span class="quantidade-badge">${item.total_itens}</span>
                            </td>
                            <td>
                                <small>${(item.observacoes || '').substring(0, 15)}${(item.observacoes || '').length > 15 ? '...' : ''}</small>
                            </td>
                        </tr>`;
        });
        
        // Total do funcionário
        htmlImpressao += `
                    <tr class="funcionario-total">
                        <td colspan="4" style="background: #fff3cd; font-weight: bold; text-align: right; padding: 8px; border-top: 2px solid #ffc107;">
                            TOTAL ${grupo.nome.toUpperCase()}:
                        </td>
                        <td style="background: #fff3cd; text-align: center; font-weight: bold; border-top: 2px solid #ffc107;">
                            <span class="total-funcionario-badge">${grupo.totalItens}</span>
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