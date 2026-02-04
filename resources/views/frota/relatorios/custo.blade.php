@extends('adminlte::page')

@section('title', 'Frota - Relatório de Custo Total')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-dollar-sign text-primary mr-3"></i>
            Relatório de Custo Total
        </h1>
        <p class="text-muted mt-1 mb-0">Análise de custos da frota por período</p>
    </div>
    <div></div>
</div>
@stop

@section('content')
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
                    <form>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Data Início</label>
                                    <input type="date" class="form-control" id="data_inicio">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Data Fim</label>
                                    <input type="date" class="form-control" id="data_fim">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Veículo</label>
                                    <select class="form-control" id="veiculo_id">
                                        <option value="">Todos os veículos</option>
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
                                <button type="button" class="btn btn-secondary ml-2">
                                    <i class="fas fa-eraser mr-1"></i>
                                    Limpar Filtros
                                </button>
                                <button type="button" class="btn btn-outline-dark ml-2" onclick="imprimirRelatorio()">
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
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h3 id="custo_total">R$ 0,00</h3>
                    <p class="mb-0">Custo Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-gas-pump fa-2x mb-2"></i>
                    <h3 id="custo_combustivel">R$ 0,00</h3>
                    <p class="mb-0">Combustível</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-2x mb-2"></i>
                    <h3 id="custo_manutencao">R$ 0,00</h3>
                    <p class="mb-0">Manutenção</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-road fa-2x mb-2"></i>
                    <h3 id="custo_km">R$ 0,000</h3>
                    <p class="mb-0">Custo por KM</p>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Área de Impressão: Tabela + Detalhes -->
    <div id="areaImpressao">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table mr-2"></i>
                        Relatório Detalhado de Custos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabelaCustos">
                            <thead class="table-dark">
                                <tr>
                                    <th>Veículo</th>
                                    <th>Combustível</th>
                                    <th>Manutenção</th>
                                    <th>Total</th>
                                    <th>KM Percorridos</th>
                                    <th>Custo por KM</th>
                                    <th>% do Total</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyCustos"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhamento por Categoria -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-gas-pump mr-2"></i>
                        Combustível - Detalhes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Veículo</th>
                                    <th>Valor</th>
                                    <th>Litros</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyCombustivel"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-tools mr-2"></i>
                        Manutenção - Detalhes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Veículo</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyManutencao"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line mr-2"></i>
                        Ranking de Custos
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Posição</th>
                                    <th>Veículo</th>
                                    <th>Custo Total</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyRanking"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div><!-- /areaImpressao -->
</div>
@stop

@section('css')
<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.table th {
    background-color: #343a40;
    color: white;
    border: none;
}

.table td {
    border-bottom: 1px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table-sm th, .table-sm td {
    padding: 0.3rem;
}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    carregarVeiculos();
    gerarRelatorio();
    document.querySelector('form').addEventListener('submit', function(e){
        e.preventDefault();
        gerarRelatorio();
    });
    // reagir imediatamente às mudanças de filtros
    ['data_inicio','data_fim','veiculo_id'].forEach(id=>{
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', gerarRelatorio);
    });
});

function carregarVeiculos(){
    fetch('/frota/api/veiculos').then(r=>r.json()).then(veiculos=>{
        const sel = document.getElementById('veiculo_id');
        if(!sel) return;
        sel.querySelectorAll('option:not(:first-child)').forEach(o=>o.remove());
        window._veicMap = {};
        veiculos.forEach(v=>{
            const o = document.createElement('option');
            o.value = v.id; o.textContent = `${v.placa} - ${v.marca||''} ${v.modelo||''}`; sel.appendChild(o);
            window._veicMap[v.id] = v.placa || String(v.id);
        });
    });
}

function gerarRelatorio(){
    const params = new URLSearchParams({
        data_inicio: document.getElementById('data_inicio').value || '',
        data_fim: document.getElementById('data_fim').value || '',
        vehicle_id: document.getElementById('veiculo_id').value || ''
    });
    const tipoFiltro = '';
    Promise.all([
        fetch('/frota/api/abastecimentos?'+params.toString()).then(r=>r.json()),
        fetch('/frota/api/manutencoes?'+params.toString()).then(r=>r.json())
    ]).then(([absAll, mansAll])=>{
        // filtros em frontend para garantir consistência
        const veiculoId = (document.getElementById('veiculo_id').value||'').toString();
        const di = (document.getElementById('data_inicio').value||'').toString(); // YYYY-MM-DD
        const df = (document.getElementById('data_fim').value||'').toString();    // YYYY-MM-DD
        const inRange = (dataStr)=>{
            if (!dataStr) return true;
            const d = dataStr.toString().slice(0,10); // normaliza
            if (di && d < di) return false;
            if (df && d > df) return false;
            return true;
        };
        const abs = (absAll||[]).filter(r => (!veiculoId || String(r.vehicle_id)===veiculoId) && inRange(r.data));
        const mans = (mansAll||[]).filter(r => (!veiculoId || String(r.vehicle_id)===veiculoId) && inRange(r.data));
        const totalComb = abs.reduce((a,b)=>a+Number(b.valor||0),0);
        const totalLitros = abs.reduce((a,b)=>a+Number(b.litros||0),0);
        const totalMan = mans.reduce((a,b)=>a+Number(b.custo||0),0);
        const kmMin = Math.min(...abs.map(x=>x.km||0), 0);
        const kmMax = Math.max(...abs.map(x=>x.km||0), 0);
        const kmPerc = Math.max(kmMax-kmMin, 0);
        const total = totalComb + totalMan; 
        document.getElementById('custo_total').textContent = formatBRL2(total);
        document.getElementById('custo_combustivel').textContent = formatBRL2(totalComb);
        document.getElementById('custo_manutencao').textContent = formatBRL2(totalMan);
        document.getElementById('custo_km').textContent = formatBRL3(kmPerc>0? total/kmPerc:0);

        const tbody = document.getElementById('tbodyCustos');
        tbody.innerHTML = '';
        // tabela simplificada por veículo
        const porVeiculo = {};
        abs.forEach(r=>{
            porVeiculo[r.vehicle_id] = porVeiculo[r.vehicle_id] || {comb:0, litros:0, kmMin:null, kmMax:null};
            const it = porVeiculo[r.vehicle_id];
            it.comb += Number(r.valor||0);
            it.litros += Number(r.litros||0);
            if (it.kmMin===null || r.km<it.kmMin) it.kmMin = r.km;
            if (it.kmMax===null || r.km>it.kmMax) it.kmMax = r.km;
        });
        mans.forEach(r=>{
            porVeiculo[r.vehicle_id] = porVeiculo[r.vehicle_id] || {comb:0, litros:0, kmMin:null, kmMax:null};
            porVeiculo[r.vehicle_id].man = (porVeiculo[r.vehicle_id].man||0) + Number(r.custo||0);
        });
        const totalGeral = Object.values(porVeiculo).reduce((acc, it)=> acc + (it.comb||0) + (it.man||0), 0);
        Object.keys(porVeiculo).forEach(k=>{
            const it = porVeiculo[k];
            const km = Math.max((it.kmMax||0)-(it.kmMin||0),0);
            const totalV = (it.comb||0) + (it.man||0);
            const pct = totalGeral > 0 ? (totalV / totalGeral * 100) : 0;
            const tr = document.createElement('tr');
            tr.innerHTML = `<td><strong>${labelVeiculo(k)}</strong></td><td>${formatBRL2(it.comb||0)}</td><td>${formatBRL2(it.man||0)}</td><td><strong>${formatBRL2(totalV)}</strong></td><td>${km.toLocaleString('pt-BR')} km</td><td>${formatBRL3(km>0? totalV/km:0)}</td><td>${pct.toFixed(1)}%</td>`;
            tbody.appendChild(tr);
        });

        // detalhes
        const tComb = document.getElementById('tbodyCombustivel');
        tComb.innerHTML = '';
        abs.forEach(r=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${labelVeiculo(r.vehicle_id)}</td><td>${formatBRL2(Number(r.valor||0))}</td><td>${Number(r.litros||0).toLocaleString('pt-BR')}L</td>`;
            tComb.appendChild(tr);
        });

        const tMan = document.getElementById('tbodyManutencao');
        tMan.innerHTML = '';
        mans.forEach(r=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${labelVeiculo(r.vehicle_id)}</td><td>${r.tipo}</td><td>${formatBRL2(Number(r.custo||0))}</td>`;
            tMan.appendChild(tr);
        });

        const tRank = document.getElementById('tbodyRanking');
        tRank.innerHTML = '';
        const ranking = Object.entries(porVeiculo).map(([k,v])=>({placa:labelVeiculo(k),total:(v.comb||0)+(v.man||0)})).sort((a,b)=>b.total-a.total);
        ranking.forEach((r,idx)=>{
            const icone = idx===0?'🥇':idx===1?'🥈':idx===2?'🥉':idx+1;
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${icone}</td><td>${r.placa}</td><td>${formatBRL2(r.total)}</td>`;
            tRank.appendChild(tr);
        });
    });
}

function imprimirRelatorio(){
    // cria iframe para imprimir apenas a área do relatório
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    
    // Obter dados dos filtros para o cabeçalho
    const dataInicio = document.getElementById('data_inicio').value || 'Não informado';
    const dataFim = document.getElementById('data_fim').value || 'Não informado';
    const _sel = document.getElementById('veiculo_id');
    const veiculoSelecionado = _sel && _sel.selectedOptions && _sel.selectedOptions.length ? _sel.selectedOptions[0].text : 'Todos os veículos';
    const agora = new Date().toLocaleString('pt-BR');
    
    const html = `<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Relatório de Custo Total</title>
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
            .summary-cards {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 12px;
                margin: 20px 0;
            }
            .summary-card {
                background: #fff;
                border: 1px solid #e0e6ed;
                border-radius: 8px;
                padding: 15px;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .summary-card h6 {
                margin: 0 0 8px 0;
                font-size: 11px;
                color: #7f8c8d;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .summary-card .value {
                font-size: 16px;
                font-weight: 700;
                color: #2c3e50;
                margin: 0;
            }
            .card-combustivel .value { color: #2980b9; }
            .card-manutencao .value { color: #3498db; }
            .card-total .value { color: #1e3a8a; }
            .card-km .value { color: #2563eb; }
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
            .table-title {
                font-weight: 700;
                margin: 25px 0 12px 0;
                color: #2c3e50;
                font-size: 14px;
                border-left: 4px solid #3498db;
                padding-left: 12px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
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
                    <h1>RELATÓRIO DE CUSTO TOTAL</h1>
                    <p class="subtitle">Análise Financeira da Frota</p>
                </div>
            </div>
            <div class="report-info">
                <div class="date">Emitido em: ${agora}</div>
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
                    <span class="filter-value">${veiculoSelecionado}</span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Análise:</span>
                    <span class="filter-value">Custos Consolidados</span>
                </div>
            </div>
        </div>

        <div class="summary-cards">
            <div class="summary-card card-total">
                <h6>💰 Custo Total</h6>
                <div class="value">${document.getElementById('custo_total').textContent}</div>
            </div>
            <div class="summary-card card-combustivel">
                <h6>⛽ Combustível</h6>
                <div class="value">${document.getElementById('custo_combustivel').textContent}</div>
            </div>
            <div class="summary-card card-manutencao">
                <h6>🔧 Manutenção</h6>
                <div class="value">${document.getElementById('custo_manutencao').textContent}</div>
            </div>
            <div class="summary-card card-km">
                <h6>📊 Custo/KM</h6>
                <div class="value">${document.getElementById('custo_km').textContent}</div>
            </div>
        </div>
        
        ${document.getElementById('areaImpressao').innerHTML}
        
        <div class="footer">
            <p>Sistema Integrado de Gestão Operacional (SIGO) - BRS Transportes</p>
        </div>
    </body>
    </html>`;
    // segurança: tentar print após load, com foco e fallback
    let printed = false;
    iframe.onload = function(){
        setTimeout(() => {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                printed = true;
            } catch(e) {
                console.error('Erro ao imprimir:', e);
            } finally {
                setTimeout(()=>document.body.removeChild(iframe), 2000);
            }
        }, 300);
    };
    doc.open();
    doc.write(html);
    doc.close();
    // fallback: se onload não disparar por algum motivo, tenta imprimir após 1s
    setTimeout(()=>{
        if (!printed) {
            try {
                iframe.contentWindow && iframe.contentWindow.focus();
                iframe.contentWindow && iframe.contentWindow.print();
            } catch(e) {}
        }
    }, 1000);
}

function labelVeiculo(id){
    if (window._veicMap && window._veicMap[id]) return window._veicMap[id];
    return String(id||'');
}

// Helpers de formatação em padrão brasileiro
function formatBRL2(valor){
    try{
        return (valor||0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }catch(e){
        const n = Number(valor||0);
        return 'R$ '+n.toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
    }
}
function formatBRL3(valor){
    try{
        return (valor||0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 3, maximumFractionDigits: 3 });
    }catch(e){
        const n = Number(valor||0);
        return 'R$ '+n.toFixed(3).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
    }
}
</script>
@stop