@extends('adminlte::page')

@section('title', 'Frota - Relatório de Abastecimento')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-gas-pump text-primary mr-3"></i>
            Relatório de Abastecimento
        </h1>
        <p class="text-muted mt-1 mb-0">Relatório que mostra abastecimento por carro ou funcionários</p>
    </div>
    <div></div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Cabeçalho para impressão (oculto na tela) -->
<div class="print-header" style="display: none;">
    <div class="header-container">
        <div class="logo-section">
            <img src="/img/brs-logo.png" alt="Logo" class="logo">
            <div class="company-info">
                <h1>SIGO</h1>
                <div class="subtitle">Relatório de Abastecimento da Frota</div>
            </div>
        </div>
        <div class="report-info">
            <div class="date">Impresso em: <span id="print-date"></span></div>
            <div class="print-filters" id="print-filters"></div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter mr-2"></i>
                        Filtros de Pesquisa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold">Data inicial</label>
                            <input type="date" id="data_ini" class="form-control" />
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold">Data final</label>
                            <input type="date" id="data_fim" class="form-control" />
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold">Veículo</label>
                            <select id="veiculo_id" class="form-control">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold">Funcionário</label>
                            <select id="user_id" class="form-control">
                                <option value="">Todos</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-2 d-flex align-items-end">
                            <button id="btnBuscar" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-3 mb-2 d-flex align-items-end">
                            <button id="btnImprimir" class="btn btn-secondary w-100">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-table mr-2"></i> Resultado
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover" id="tabela_relatorio">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Data</th>
                                <th>Veículo</th>
                                <th>Funcionário</th>
                                <th>KM</th>
                                <th>Litros</th>
                                <th>Preço/Litro</th>
                                <th>Valor</th>
                                <th>Posto</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Totais:</th>
                                <th id="ft_litros">0,00</th>
                                <th></th>
                                <th id="ft_valor">R$ 0,00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
@media print {
    /* Configurar página para impressão em paisagem */
    @page {
        size: landscape;
        margin: 0.5in;
    }
    
    html, body {
        width: 297mm;
        height: 210mm;
        margin: 0 !important;
        padding: 15px !important;
        font-size: 13px !important;
        color: #000 !important;
        background: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Ocultar elementos desnecessários na impressão */
    .main-header,
    .main-sidebar,
    .content-header,
    .row.mb-4,
    .no-print,
    .btn,
    .pagination,
    .sidebar,
    .navbar,
    .main-footer,
    .breadcrumb {
        display: none !important;
    }
    
    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
        min-height: auto !important;
    }
    
    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    
    /* Cabeçalho do relatório para impressão */
    .print-header {
        display: block !important;
        margin-bottom: 20px !important;
        padding-bottom: 15px !important;
    }
    
    .header-container {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 20px !important;
        padding-bottom: 15px !important;
        border-bottom: 3px solid #3498db !important;
    }
    
    .logo-section {
        display: flex !important;
        align-items: center !important;
        gap: 15px !important;
    }
    
    .logo {
        width: 60px !important;
        height: 60px !important;
        object-fit: contain !important;
    }
    
    .company-info h1 {
        font-size: 22px !important;
        font-weight: 700 !important;
        color: #2c3e50 !important;
        margin: 0 0 5px 0 !important;
        letter-spacing: 0.5px !important;
    }
    
    .company-info .subtitle {
        font-size: 14px !important;
        color: #7f8c8d !important;
        margin: 0 !important;
        font-weight: 500 !important;
    }
    
    .report-info {
        text-align: right !important;
        font-size: 11px !important;
        color: #5a6c7d !important;
    }
    
    .report-info .date {
        font-weight: 600 !important;
        color: #34495e !important;
        margin-bottom: 8px !important;
    }
    
    /* Forçar visibilidade da tabela */
    .row:last-child,
    .card:last-child,
    .card-body,
    table,
    tbody,
    tr,
    td,
    th {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    table {
        display: table !important;
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 13px !important;
        margin: 0 !important;
        background: white !important;
    }
    
    tbody {
        display: table-row-group !important;
    }
    
    tr {
        display: table-row !important;
        page-break-inside: avoid !important;
    }
    
    th, td {
        display: table-cell !important;
        border: 1px solid #000 !important;
        padding: 8px !important;
        text-align: left !important;
        background: white !important;
        color: #000 !important;
        font-size: 13px !important;
    }
    
    thead {
        display: table-header-group !important;
    }
    
    tfoot {
        display: table-footer-group !important;
    }
    
    th {
        background-color: #f0f0f0 !important;
        font-weight: bold !important;
        font-size: 14px !important;
    }
    
    tfoot th {
        background-color: #e0e0e0 !important;
        font-weight: bold !important;
        font-size: 14px !important;
    }
    
    /* Ocultar cabeçalho do card */
    .card-header {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: none !important;
        background: white !important;
    }
    
    .card-body {
        padding: 0 !important;
        background: white !important;
    }
    
    /* Ocultar rodapé da impressão */
    .print-footer {
        display: none !important;
    }
}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnBuscar = document.getElementById('btnBuscar');
    const tabela = document.querySelector('#tabela_relatorio tbody');
    const ftLitros = document.getElementById('ft_litros');
    const ftValor = document.getElementById('ft_valor');

    function normalizar(valor) { return valor ?? ''; }

    async function carregarOpcoes() {
        try {
            const resp = await fetch('/frota/api/relatorios/abastecimento/opcoes', { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            if (!resp.ok) throw new Error('Falha ao carregar opções');
            const json = await resp.json();
            preencherSelect('veiculo_id', json.veiculos || [], 'placa');
            preencherSelect('user_id', json.usuarios || [], 'name');
        } catch (e) {
            console.error(e);
        }
    }

    function preencherSelect(id, itens, labelKey) {
        const sel = document.getElementById(id);
        const opts = ['<option value="">Todos</option>'].concat(
            itens.map(x => `<option value="${x.id}">${normalizar(x[labelKey])}</option>`)
        );
        sel.innerHTML = opts.join('');
    }

    async function buscar() {
        tabela.innerHTML = '<tr><td colspan="9" class="text-center">Carregando...</td></tr>';
        const params = new URLSearchParams({
            data_ini: document.getElementById('data_ini').value,
            data_fim: document.getElementById('data_fim').value,
            veiculo_id: document.getElementById('veiculo_id').value,
            user_id: document.getElementById('user_id').value
        });

        try {
            const resp = await fetch('/frota/api/relatorios/abastecimento?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!resp.ok) throw new Error('Falha ao carregar');
            const dados = await resp.json();
            render(dados.data || [], dados.totais || {});
        } catch (e) {
            tabela.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Erro ao buscar dados</td></tr>';
            ftLitros.textContent = '0,00';
            ftValor.textContent = (0).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
        }
    }

    function render(linhas, totais) {
        if (!linhas.length) {
            tabela.innerHTML = '<tr><td colspan="9" class="text-center">Nenhum registro encontrado</td></tr>';
        } else {
            tabela.innerHTML = linhas.map((l, i) => `
                <tr>
                    <td>${i+1}</td>
                    <td>${l.data || ''}</td>
                    <td>${normalizar(l.placa)}</td>
                    <td>${normalizar(l.funcionario)}</td>
                    <td>${l.km ?? ''}</td>
                    <td>${Number(l.litros || 0).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td>${Number(l.preco_litro || 0).toLocaleString('pt-BR', {style:'currency', currency:'BRL'})}</td>
                    <td>${Number(l.valor || 0).toLocaleString('pt-BR', {style:'currency', currency:'BRL'})}</td>
                    <td>${normalizar(l.posto)}</td>
                </tr>
            `).join('');
        }
        ftLitros.textContent = Number(totais?.litros || 0).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
        ftValor.textContent = Number(totais?.valor || 0).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
    }

    btnBuscar.addEventListener('click', buscar);
    const btnImprimir = document.getElementById('btnImprimir');
    btnImprimir.addEventListener('click', function(){
        // Verificar se há dados na tabela
        const linhasTabela = tabela.children.length;
        if (linhasTabela === 0 || tabela.innerHTML.includes('Nenhum registro encontrado') || tabela.innerHTML.includes('Carregando...')) {
            alert('Não há dados para imprimir. Execute uma busca primeiro.');
            return;
        }
        
        prepararImpressao();
        window.print();
    });
    
    function prepararImpressao() {
        // Atualizar data de impressão
        const agora = new Date();
        document.getElementById('print-date').textContent = agora.toLocaleString('pt-BR');
        
        // Mostrar filtros aplicados
        const filtros = [];
        const dataIni = document.getElementById('data_ini').value;
        const dataFim = document.getElementById('data_fim').value;
        const veiculoSel = document.getElementById('veiculo_id');
        const userSel = document.getElementById('user_id');
        
        if (dataIni) filtros.push(`Data inicial: ${new Date(dataIni).toLocaleDateString('pt-BR')}`);
        if (dataFim) filtros.push(`Data final: ${new Date(dataFim).toLocaleDateString('pt-BR')}`);
        if (veiculoSel.value) filtros.push(`Veículo: ${veiculoSel.options[veiculoSel.selectedIndex].text}`);
        if (userSel.value) filtros.push(`Funcionário: ${userSel.options[userSel.selectedIndex].text}`);
        
        document.getElementById('print-filters').innerHTML = filtros.length ? 
            '<div style="margin-top: 10px; font-size: 11px;"><strong>Filtros aplicados:</strong> ' + filtros.join(' | ') + '</div>' : '';
    }
    
    carregarOpcoes();
});
</script>
@stop


