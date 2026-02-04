@extends('adminlte::page')

@section('title', 'Frota - Relatório de KM Percorrido')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-road text-primary mr-3"></i>
            Relatório de KM Percorrido
        </h1>
        <p class="text-muted mt-1 mb-0">Total de quilômetros percorridos por veículo/usuário no período</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Filtros -->
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
                                    <label for="veiculo_id" class="font-weight-bold">Veículo</label>
                                    <select class="form-control" id="veiculo_id" name="veiculo_id">
                                        <option value="">Todos os veículos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="user_id" class="font-weight-bold">Usuário (motorista)</label>
                                    <select class="form-control" id="user_id" name="user_id">
                                        <option value="">Todos os usuários</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold d-block">Agrupar por</label>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="grp_veiculo" name="agrupar" class="custom-control-input" value="veiculo" checked>
                                        <label class="custom-control-label" for="grp_veiculo">Veículo</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="grp_usuario" name="agrupar" class="custom-control-input" value="usuario">
                                        <label class="custom-control-label" for="grp_usuario">Usuário</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search mr-1"></i>
                                    Gerar Relatório
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="limparFiltros()">
                                    <i class="fas fa-eraser mr-1"></i>
                                    Limpar Filtros
                                </button>
                                <button type="button" id="btnImprimirKm" class="btn btn-info ml-2" onclick="imprimirRelatorioKm()" disabled>
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
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-road fa-2x mb-2"></i>
                    <h3 id="totalKm">0 km</h3>
                    <p class="mb-0">KM Percorridos (período)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-car fa-2x mb-2"></i>
                    <h3 id="totalGrupos">0</h3>
                    <p class="mb-0">Itens (veículos/usuários)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-tachometer-alt fa-2x mb-2"></i>
                    <h3 id="kmMedio">0 km</h3>
                    <p class="mb-0">KM Médio por item</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="row" id="resultadosSection" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table mr-2"></i>
                        Relatório Detalhado de KM Percorrido
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabelaRelatorio">
                            <thead class="table-dark">
                                <tr>
                                    <th id="thLabel">Veículo/Usuário</th>
                                    <th>KM Inicial</th>
                                    <th>KM Final</th>
                                    <th>KM Percorrido</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado Inicial -->
    <div class="row" id="estadoInicial">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-road fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Relatório de KM Percorrido</h4>
                <p class="text-muted">Escolha um período e, se quiser, um veículo/usuário específico para calcular os quilômetros percorridos.</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.table th { background-color: #343a40; color: #fff; border: none; }
.table td { border-bottom: 1px solid #dee2e6; }
</style>
@stop

@section('js')
<script>
let mapaVeiculos = {};
let mapaUsuarios = {};

$(document).ready(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }});

    // Datas padrão (mês atual)
    const hoje = new Date();
    const inicio = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    $('#data_inicio').val(inicio.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);

    // Carregar veículos
    $.get('/frota/api/veiculos').done(function(veiculos){
        const sel = $('#veiculo_id');
        sel.find('option').remove();
        sel.append('<option value="">Todos os veículos</option>');
        (veiculos||[]).forEach(v => {
            const label = `${v.placa || ''} - ${v.marca||''} ${v.modelo||''}`.trim();
            mapaVeiculos[String(v.id)] = label;
            sel.append(`<option value="${v.id}">${label}</option>`);
        });
    });
    // Carregar usuários
    $.get('/api/usuarios').done(function(users){
        const sel = $('#user_id');
        sel.find('option').remove();
        sel.append('<option value="">Todos os usuários</option>');
        (users||[]).forEach(u => {
            mapaUsuarios[String(u.id)] = u.name;
            sel.append(`<option value="${u.id}">${u.name}</option>`);
        });
    });

    $('#formFiltros').on('submit', function(e){ e.preventDefault(); gerarRelatorio(); });
});

function gerarRelatorio(){
    const filtros = {
        data_inicio: $('#data_inicio').val(),
        data_fim: $('#data_fim').val(),
        vehicle_id: $('#veiculo_id').val(),
        user_id: $('#user_id').val()
    };
    if (!filtros.data_inicio || !filtros.data_fim){
        Swal.fire('Atenção','Informe o período.','warning');
        return;
    }
    const agrupar = $('input[name="agrupar"]:checked').val();
    // Atualizar cabeçalho
    $('#thLabel').text(agrupar === 'usuario' ? 'Usuário' : 'Veículo');

    const params = Object.assign({ agrupar }, filtros);
    $.get('/frota/api/relatorios/km-percorrido', params).done(function(resp){
        const dados = (resp && resp.success) ? (resp.data||[]) : [];
        exibirResultadosServidor(dados);
        atualizarResumoServidor(dados);
        $('#btnImprimirKm').prop('disabled', false);
        $('#estadoInicial').hide();
        $('#resumoSection, #resultadosSection').show();
    });
}

function agregarKmPorVeiculo(rows){
    const por = {};
    (rows||[]).forEach(r => {
        if (!r.km_saida || !r.km_retorno) return; // precisa ter retorno para computar
        const key = String(r.vehicle_id);
        const kmIni = Number(r.km_saida||0); const kmFim = Number(r.km_retorno||0);
        if (!por[key]) por[key] = { label: mapaVeiculos[key] || key, kmMin: null, kmMax: null };
        if (por[key].kmMin === null || kmIni < por[key].kmMin) por[key].kmMin = kmIni;
        if (por[key].kmMax === null || kmFim > por[key].kmMax) por[key].kmMax = kmFim;
    });
    return Object.values(por).map(v => ({
        label: v.label,
        kmInicial: v.kmMin||0,
        kmFinal: v.kmMax||0,
        kmPercorrido: Math.max(0, (v.kmMax||0) - (v.kmMin||0))
    })).sort((a,b) => b.kmPercorrido - a.kmPercorrido);
}

function agregarKmPorUsuario(rows){
    const por = {};
    (rows||[]).forEach(r => {
        if (!r.km_saida || !r.km_retorno) return;
        const key = String(r.user_id);
        const kmIni = Number(r.km_saida||0); const kmFim = Number(r.km_retorno||0);
        if (!por[key]) por[key] = { label: mapaUsuarios[key] || key, kmMin: null, kmMax: null };
        if (por[key].kmMin === null || kmIni < por[key].kmMin) por[key].kmMin = kmIni;
        if (por[key].kmMax === null || kmFim > por[key].kmMax) por[key].kmMax = kmFim;
    });
    return Object.values(por).map(v => ({
        label: v.label,
        kmInicial: v.kmMin||0,
        kmFinal: v.kmMax||0,
        kmPercorrido: Math.max(0, (v.kmMax||0) - (v.kmMin||0))
    })).sort((a,b) => b.kmPercorrido - a.kmPercorrido);
}

// Exibição usando dados vindos do servidor (controller)
function exibirResultadosServidor(dados){
    const tbody = $('#tabelaRelatorio tbody');
    tbody.empty();
    dados.forEach(item => {
        tbody.append(`
            <tr>
                <td><strong>${item.label}</strong></td>
                <td>${Number(item.kmInicial||0).toLocaleString()} km</td>
                <td>${Number(item.kmFinal||0).toLocaleString()} km</td>
                <td><span class="badge badge-info">${Number(item.kmPercorrido||0).toLocaleString()} km</span></td>
            </tr>
        `);
    });
}

function atualizarResumoServidor(dados){
    const totalKm = (dados||[]).reduce((acc, i) => acc + Number(i.kmPercorrido||0), 0);
    const qtd = (dados||[]).length;
    const medio = qtd ? (totalKm / qtd) : 0;
    $('#totalKm').text(`${totalKm.toLocaleString()} km`);
    $('#totalGrupos').text(qtd);
    $('#kmMedio').text(`${medio.toLocaleString(undefined,{maximumFractionDigits:0})} km`);
}

function limparFiltros(){
    $('#formFiltros')[0].reset();
    const hoje = new Date();
    const inicio = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    $('#data_inicio').val(inicio.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);
    $('#resumoSection, #resultadosSection').hide();
    $('#estadoInicial').show();
    $('#btnImprimirKm').prop('disabled', true);
}

// Impressão em pop-up nativo do navegador sem abrir nova aba
function imprimirRelatorioKm(){
    const tabela = document.getElementById('tabelaRelatorio');
    if (!tabela || !tabela.tBodies || !tabela.tBodies[0] || tabela.tBodies[0].rows.length === 0){
        Swal.fire('Atenção','Gere o relatório antes de imprimir.','warning');
        return;
    }

    const dataInicio = $('#data_inicio').val();
    const dataFim = $('#data_fim').val();
    const agrupar = $('input[name="agrupar"]:checked').val();
    const tituloAgrup = agrupar === 'usuario' ? 'Usuário' : 'Veículo';
    const veiculoTxt = $('#veiculo_id option:selected').text();
    const usuarioTxt = $('#user_id option:selected').text();

    const resumo = {
        totalKm: $('#totalKm').text() || '0 km',
        totalGrupos: $('#totalGrupos').text() || '0',
        kmMedio: $('#kmMedio').text() || '0 km'
    };

    const html = `<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8" />
        <title>Relatório de KM Percorrido</title>
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
                grid-template-columns: repeat(4, 1fr);
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
            .resumo { 
                display: grid; 
                grid-template-columns: repeat(3, 1fr); 
                gap: 15px; 
                margin: 20px 0; 
            }
            .card { 
                background: #fff; 
                border: 1px solid #e0e6ed;
                border-radius: 8px;
                padding: 15px; 
                text-align: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                border-left: 4px solid #3498db;
            }
            .card h3 { 
                margin: 0; 
                color: #2c3e50; 
                font-size: 18px;
                font-weight: 700;
            }
            .card p { 
                margin: 8px 0 0 0; 
                font-size: 11px; 
                color: #7f8c8d;
                text-transform: uppercase;
                letter-spacing: 0.5px;
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
                    <h1>RELATÓRIO DE KM PERCORRIDO</h1>
                    <p class="subtitle">Controle de Quilometragem da Frota</p>
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
                    <span class="filter-label">Período:</span>
                    <span class="filter-value">${dataInicio} até ${dataFim}</span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Veículo:</span>
                    <span class="filter-value">${veiculoTxt}</span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Usuário:</span>
                    <span class="filter-value">${usuarioTxt}</span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Agrupamento:</span>
                    <span class="filter-value">${tituloAgrup}</span>
                </div>
            </div>
        </div>

        <div class="resumo">
            <div class="card">
                <h3>${resumo.totalKm}</h3>
                <p>🛣️ Total KM Percorridos</p>
            </div>
            <div class="card">
                <h3>${resumo.totalGrupos}</h3>
                <p>📊 Total de Itens</p>
            </div>
            <div class="card">
                <h3>${resumo.kmMedio}</h3>
                <p>📈 KM Médio por Item</p>
            </div>
        </div>
        
        ${document.getElementById('tabelaRelatorio').outerHTML}
        
        <div class="footer">
            <p>Sistema Integrado de Gestão Operacional (SIGO) - BRS Transportes</p>
        </div>
    </body>
    </html>`;

    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open(); doc.write(html); doc.close();
    iframe.onload = function(){
        try { iframe.contentWindow.focus(); } catch(e) {}
        try { iframe.contentWindow.print(); } catch(e) {}
    };
    if (iframe.contentWindow) {
        iframe.contentWindow.onafterprint = function(){ try { document.body.removeChild(iframe); } catch(e) {} };
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@stop

