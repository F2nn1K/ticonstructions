@extends('adminlte::page')

@section('title', 'Relatório por Produto (Estoque)')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-box-open text-primary mr-3"></i>
            Relatório por Produto (Estoque)
        </h1>
        <p class="text-muted mt-1 mb-0">Consulta por produto e centro de custo</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter mr-2"></i>Filtros</h5>
        </div>
        <div class="card-body">
            <form id="form-produto">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Produto</label>
                            <div>
                                <input type="text" id="produto_busca" class="form-control mb-2" placeholder="Digite ao menos 3 letras do produto">
                                <div id="produtosSelecionados" class="mb-2"></div>
                                <small class="text-muted">Você pode selecionar até 5 produtos.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Centro de Custo</label>
                            <select id="centro_custo_id" class="form-control">
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="font-weight-bold">Data Início</label>
                            <input type="date" id="data_inicio" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="font-weight-bold">Data Fim</label>
                            <input type="date" id="data_fim" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="d-flex">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i> Gerar Relatório
                    </button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="limparFiltros()">
                        <i class="fas fa-eraser mr-1"></i> Limpar
                    </button>
                    <button type="button" class="btn btn-success ml-2" id="btnImprimirRelatorioProduto" onclick="imprimirRelatorioProduto()">
                        <i class="fas fa-print mr-1"></i> Imprimir
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card" id="resultadoCard" style="display:none;">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i>Resultados</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tabelaResultado">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Descrição</th>
                            <th>Centro de Custo</th>
                            <th>Quantidade</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
</style>
@stop

@section('js')
<script>
$(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }});
    // Datas padrão: mês atual
    const hoje = new Date();
    const ini = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    $('#data_inicio').val(ini.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);

    $('#form-produto').on('submit', function(e){
        e.preventDefault();
        const produtoIds = Array.from(document.querySelectorAll('#produtosSelecionados input[name="produto_ids[]"]')).map(i => i.value);
        const params = {
            produto_ids: produtoIds,
            centro_custo_id: $('#centro_custo_id').val(),
            data_inicio: $('#data_inicio').val(),
            data_fim: $('#data_fim').val()
        };
        $.get('/api/relatorios/produto-estoque', params).done(function(resp){
            const dados = (resp && resp.success) ? (resp.data||[]) : [];
            const tbody = $('#tabelaResultado tbody');
            tbody.empty();
            
            let totalQuantidade = 0;
            
            dados.forEach(l => {
                const quantidade = Number(l.quantidade||0);
                totalQuantidade += quantidade;
                
                tbody.append(`
                    <tr>
                        <td>${l.produto || '-'}</td>
                        <td>${l.descricao || '-'}</td>
                        <td>${l.centro_custo || '-'}</td>
                        <td>${quantidade.toLocaleString()}</td>
                    </tr>
                `);
            });
            
            // Adicionar linha de total
            tbody.append(`
                <tr class="table-info font-weight-bold">
                    <td colspan="3"><strong>Total Geral</strong></td>
                    <td><strong>${totalQuantidade.toLocaleString()}</strong></td>
                </tr>
            `);
            
            $('#resultadoCard').show();
        }).fail(function(){
            Swal.fire('Erro','Falha ao carregar dados do relatório.','error');
        });
    });
});

function limparFiltros(){
    $('#form-produto')[0].reset();
    const hoje = new Date();
    const ini = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    $('#data_inicio').val(ini.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);
    $('#resultadoCard').hide();
    $('#tabelaResultado tbody').empty();
    $('#centro_custo_id').val('');
    $('#produtosSelecionados').empty();
}

// Autocomplete de produto (até 5 selecionados)
$(function(){
    // Popular centros de custo
    $.get('/api/relatorios/produto-estoque/centros').done(function(resp){
        if (resp && resp.success){
            const sel = $('#centro_custo_id');
            (resp.data||[]).forEach(c => sel.append(`<option value="${c.id}">${c.nome}</option>`));
        }
    });

    let timer = null;
    $('#produto_busca').on('input', function(){
        const q = this.value.trim();
        if (q.length < 3){ $('#autocompleteLista').remove(); return; }
        clearTimeout(timer);
        timer = setTimeout(() => buscarProdutos(q), 250);
    });
});

function buscarProdutos(q){
    $.get('/api/relatorios/produto-estoque/produtos', { q }).done(function(resp){
        $('#autocompleteLista').remove();
        const dados = (resp && resp.success) ? (resp.data||[]) : [];
        if (!dados.length) return;
        const lista = $('<div id="autocompleteLista" class="list-group position-absolute w-100" style="z-index:1000; max-height:240px; overflow:auto;"></div>');
        dados.forEach(p => {
            const nome = p.nome || '';
            const descricao = p.descricao || '';
            const label = nome;
            
            const item = $(`
                <a href="#" class="list-group-item list-group-item-action">
                    <div><strong>${nome}</strong></div>
                    ${descricao ? `<small class="text-muted">${descricao}</small>` : ''}
                </a>
            `);
            item.on('click', function(e){ 
                e.preventDefault(); 
                adicionarProdutoSelecionado(p.id, label); 
                $('#autocompleteLista').remove(); 
                $('#produto_busca').val(''); 
            });
            lista.append(item);
        });
        $('#produto_busca').after(lista);
    });
}

function adicionarProdutoSelecionado(id, label){
    const container = $('#produtosSelecionados');
    const existentes = container.find('input[name="produto_ids[]"]').map((i,el)=>el.value).get();
    if (existentes.includes(String(id))) return; // evitar duplicados
    if (existentes.length >= 5){
        Swal.fire('Limite atingido','Você pode selecionar até 5 produtos.','info');
        return;
    }
    const chip = $(`
        <span class="badge badge-primary mr-1 mb-1" style="font-size:90%;">
            ${label}
            <input type="hidden" name="produto_ids[]" value="${id}">
            <a href="#" class="ml-1 text-white" onclick="$(this).parent().remove(); return false;">
                <i class="fas fa-times"></i>
            </a>
        </span>
    `);
    container.append(chip);
}

function imprimirRelatorioProduto() {
    const resultados = $('#tabelaResultado tbody tr');
    if (resultados.length === 0) {
        Swal.fire('Aviso', 'Nenhum resultado para imprimir. Gere o relatório primeiro.', 'warning');
        return;
    }

    // Coletando os filtros aplicados
    const produtosSelecionados = Array.from(document.querySelectorAll('#produtosSelecionados .badge')).map(badge => {
        return badge.textContent.replace('×', '').trim();
    });
    
    const centroCustoNome = $('#centro_custo_id option:selected').text();
    const dataInicio = $('#data_inicio').val();
    const dataFim = $('#data_fim').val();

    // Gerando HTML de impressão
    let htmlImpressao = `
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Relatório por Produto (Estoque)</title>
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
                    grid-template-columns: repeat(2, 1fr);
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
                        <h1>RELATÓRIO POR PRODUTO</h1>
                        <p class="subtitle">Consulta de Estoque por Produto</p>
                    </div>
                </div>
                <div class="report-info">
                    <div class="date">Emitido em: ${new Date().toLocaleString('pt-BR')}</div>
                    <div>Sistema SIGO - BRS Transportes</div>
                </div>
            </div>
            
            <div class="filters-section">
                <div class="filters-title">FILTROS APLICADOS</div>
                <div class="filters-grid">
                    <div class="filter-item">
                        <span class="filter-label">Produtos:</span>
                        <span class="filter-value">${produtosSelecionados.length > 0 ? produtosSelecionados.join(', ') : 'Todos'}</span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">Centro de Custo:</span>
                        <span class="filter-value">${(centroCustoNome && centroCustoNome !== 'Selecione...') ? centroCustoNome : 'Todos'}</span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">Período:</span>
                        <span class="filter-value">${dataInicio ? new Date(dataInicio + 'T00:00:00').toLocaleDateString('pt-BR') : 'Não informado'} até ${dataFim ? new Date(dataFim + 'T00:00:00').toLocaleDateString('pt-BR') : 'Não informado'}</span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">Tipo:</span>
                        <span class="filter-value">Consulta por Produto</span>
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Descrição</th>
                        <th>Centro de Custo</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>`;

    // Adicionando as linhas da tabela
    let totalQuantidade = 0;
    resultados.each(function() {
        const colunas = $(this).find('td');
        
        // Pular a linha de total que já existe na tabela
        if (colunas.eq(0).find('strong').length > 0) {
            return; // Não incluir a linha "Total Geral" na impressão
        }
        
        const produto = colunas.eq(0).text().trim();
        const descricao = colunas.eq(1).text().trim();
        const centro = colunas.eq(2).text().trim();
        const quantidade = colunas.eq(3).text().trim();
        
        // Somando quantidade (removendo formatação)
        const qtdNum = parseInt(quantidade.replace(/\./g, '')) || 0;
        totalQuantidade += qtdNum;

        htmlImpressao += `
                    <tr>
                        <td>${produto}</td>
                        <td>${descricao}</td>
                        <td>${centro}</td>
                        <td>${quantidade}</td>
                    </tr>`;
    });

    htmlImpressao += `
                </tbody>
            </table>
            
            <div class="total-summary">
                <div class="total-row">
                    <span>TOTAL GERAL DE ITENS:</span>
                    <span>${totalQuantidade.toLocaleString()} unidades</span>
                </div>
            </div>
            
            <div class="footer">
                <p>Sistema Integrado de Gestão Operacional (SIGO) - BRS Transportes</p>
            </div>
        </body>
        </html>`;

    // Criando iframe invisível para impressão
    const iframe = document.createElement('iframe');
    iframe.style.position = 'absolute';
    iframe.style.top = '-10000px';
    iframe.style.left = '-10000px';
    iframe.style.width = '1px';
    iframe.style.height = '1px';
    document.body.appendChild(iframe);

    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(htmlImpressao);
    doc.close();

    // Aguardar carregamento e imprimir
    iframe.onload = function() {
        setTimeout(function() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            
            // Remover iframe após impressão
            setTimeout(function() {
                document.body.removeChild(iframe);
            }, 1000);
        }, 500);
    };
}
</script>
@stop


