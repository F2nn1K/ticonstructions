@extends('adminlte::page')

@section('title', 'Relatório de Manutenções (Frota)')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-tools text-primary mr-2"></i>Relatório de Manutenções</h1>
    <span class="text-muted small">Mês corrente por padrão</span>
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
                                    <label for="data_ini" class="font-weight-bold">Data Inicial</label>
                                    <input id="data_ini" type="date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="data_fim" class="font-weight-bold">Data Final</label>
                                    <input id="data_fim" type="date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="vehicle_id" class="font-weight-bold">Veículo</label>
                                    <select id="vehicle_id" class="form-control">
                                        <option value="">Todos os veículos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="user_id" class="font-weight-bold">Motorista</label>
                                    <select id="user_id" class="form-control">
                                        <option value="">Todos os motoristas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" id="btnFiltrar" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>
                                    Gerar Relatório
                                </button>
                                <button type="button" id="btnLimpar" class="btn btn-secondary ml-2">
                                    <i class="fas fa-eraser mr-1"></i>
                                    Limpar Filtros
                                </button>
                                <button type="button" id="btnImprimir" class="btn btn-info ml-2" disabled>
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table mr-2"></i>
                        Relatório Detalhado de Manutenções
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabelaRel">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Veículo</th>
                                    <th>Motorista</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>KM</th>
                                    <th>Custo</th>
                                    <th>Próxima</th>
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
                <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Relatório de Manutenções da Frota</h4>
                <p class="text-muted">Configure os filtros acima e clique em "Gerar Relatório" para visualizar as manutenções por período</p>
            </div>
        </div>
    </div>
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
    white-space: nowrap;
}

.table td {
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Larguras específicas para colunas que estavam quebrando */
.table th:nth-child(1), .table td:nth-child(1) { width: 100px; } /* Data */
.table th:nth-child(2), .table td:nth-child(2) { width: 120px; } /* Veículo */
.table th:nth-child(3), .table td:nth-child(3) { width: 120px; } /* Motorista */
.table th:nth-child(4), .table td:nth-child(4) { width: 100px; } /* Tipo */
.table th:nth-child(5), .table td:nth-child(5) { min-width: 200px; } /* Descrição */
.table th:nth-child(6), .table td:nth-child(6) { width: 110px; text-align: right; white-space: nowrap; } /* KM */
.table th:nth-child(7), .table td:nth-child(7) { width: 120px; text-align: right; white-space: nowrap; } /* Custo */
.table th:nth-child(8), .table td:nth-child(8) { width: 130px; white-space: nowrap; } /* Próxima */

.loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@stop

@section('js')
<script>
const IS_ADMIN = @json(optional(auth()->user()->profile)->name === 'Admin');
let LAST_ROWS = [];

function pad(n){ return String(n).padStart(2, '0'); }
function setMesAtual(){
  const hoje = new Date();
  const ini = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
  const fim = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
  document.getElementById('data_ini').value = `${ini.getFullYear()}-${pad(ini.getMonth()+1)}-${pad(ini.getDate())}`;
  document.getElementById('data_fim').value = `${fim.getFullYear()}-${pad(fim.getMonth()+1)}-${pad(fim.getDate())}`;
}

async function carregarOpcoes(){
  const [veics, users] = await Promise.all([
    fetch('/frota/api/veiculos').then(r=>r.json()),
    fetch('/api/usuarios').then(r=>r.json())
  ]);
  const selV = document.getElementById('vehicle_id');
  veics.forEach(v=> selV.insertAdjacentHTML('beforeend', `<option value="${v.id}">${v.placa}</option>`));
  const selU = document.getElementById('user_id');
  users.forEach(u=> selU.insertAdjacentHTML('beforeend', `<option value="${u.id}">${u.name}</option>`));
  if (!IS_ADMIN) { selU.value = '{{ auth()->id() }}'; selU.disabled = true; }
}

function formatarBR(n, frac=0){ return Number(n||0).toLocaleString('pt-BR', { minimumFractionDigits: frac, maximumFractionDigits: frac }); }

async function listar(){
  const params = new URLSearchParams({
    data_ini: document.getElementById('data_ini').value,
    data_fim: document.getElementById('data_fim').value,
    vehicle_id: document.getElementById('vehicle_id').value,
    user_id: document.getElementById('user_id').value
  });
  
  // Mostrar loading
  const tbody = document.querySelector('#tabelaRel tbody');
  tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="loading-spinner mr-2"></div>Carregando manutenções...</td></tr>';
  
  const resp = await fetch('/frota/api/relatorios/manutencoes?' + params.toString());
  const rows = await resp.json();
  LAST_ROWS = Array.isArray(rows) ? rows : [];
  tbody.innerHTML = '';
  
  if (!LAST_ROWS.length){
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Nenhum registro encontrado para o período selecionado</td></tr>';
    $('#btnImprimir').prop('disabled', true);
    return;
  }
  
  // Habilitar botão de impressão e mostrar seção de resultados
  $('#btnImprimir').prop('disabled', false);
  $('#estadoInicial').hide();
  $('#resultadosSection').show();
  LAST_ROWS.forEach(r => {
    const data = r.data ? new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR') : '-';
    const proxs = [];
    if (r.proxima_data) proxs.push(new Date(r.proxima_data + 'T00:00:00').toLocaleDateString('pt-BR'));
    if (r.proxima_km) proxs.push(formatarBR(r.proxima_km) + ' km');
    tbody.insertAdjacentHTML('beforeend', `
      <tr>
        <td>${data}</td>
        <td>${r.veiculo||'-'}</td>
        <td>${r.motorista||'-'}</td>
        <td>${r.tipo||'-'}</td>
        <td>${r.descricao||''}</td>
        <td>${formatarBR(r.km)} km</td>
        <td>R$ ${formatarBR(r.custo, 2)}</td>
        <td>${proxs.join('<br>') || '-'}</td>
      </tr>
    `);
  });
}

function getSelectedText(selId){
  const el = document.getElementById(selId);
  if (!el) return '';
  const opt = el.options[el.selectedIndex];
  return opt ? opt.text : '';
}

function imprimir(){
  if (!LAST_ROWS.length){
    if (window.Swal) { Swal.fire('Atenção', 'Nenhum registro para imprimir.', 'info'); }
    return;
  }
  const dataIni = document.getElementById('data_ini').value;
  const dataFim = document.getElementById('data_fim').value;
  const veiculoTxt = getSelectedText('vehicle_id') || 'Todos';
  const userTxt = getSelectedText('user_id') || 'Todos';

  const linhas = LAST_ROWS.map(r => {
    const data = r.data ? new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR') : '-';
    const proxs = [];
    if (r.proxima_data) proxs.push(new Date(r.proxima_data + 'T00:00:00').toLocaleDateString('pt-BR'));
    if (r.proxima_km) proxs.push(formatarBR(r.proxima_km) + ' km');
    return `
      <tr>
        <td>${data}</td>
        <td>${r.veiculo||'-'}</td>
        <td>${r.motorista||'-'}</td>
        <td>${r.tipo||'-'}</td>
        <td>${r.descricao||''}</td>
        <td>${formatarBR(r.km)} km</td>
        <td>R$ ${formatarBR(r.custo, 2)}</td>
        <td>${proxs.join('<br>') || '-'}</td>
      </tr>`;
  }).join('');

  const html = `
  <html>
  <head>
    <meta charset="utf-8" />
    <title>Relatório de Manutenções</title>
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
        background: #ecf0f1;
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
      table { 
        width: 100%; 
        border-collapse: collapse; 
        font-size: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 6px;
        overflow: hidden;
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
        white-space: nowrap;
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
        vertical-align: middle;
        border-left: none;
        border-right: none;
        white-space: nowrap;
      }
      /* Larguras específicas para colunas na impressão */
      th:nth-child(1), td:nth-child(1) { width: 80px; } /* Data */
      th:nth-child(2), td:nth-child(2) { width: 120px; } /* Veículo */
      th:nth-child(3), td:nth-child(3) { width: 100px; } /* Motorista */
      th:nth-child(4), td:nth-child(4) { width: 80px; } /* Tipo */
      th:nth-child(5), td:nth-child(5) { width: 200px; white-space: normal; } /* Descrição */
      th:nth-child(6), td:nth-child(6) { width: 90px; text-align: right; } /* KM */
      th:nth-child(7), td:nth-child(7) { width: 100px; text-align: right; } /* Custo */
      th:nth-child(8), td:nth-child(8) { width: 110px; } /* Próxima */
      .currency {
        text-align: right;
        font-weight: 600;
        color: #27ae60;
      }
      .km-value {
        text-align: right;
        font-weight: 500;
      }
      .footer {
        margin-top: 25px;
        padding-top: 15px;
        border-top: 2px solid #bdc3c7;
        text-align: center;
        font-size: 10px;
        color: #7f8c8d;
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
          <h1>RELATÓRIO DE MANUTENÇÕES</h1>
          <p class="subtitle">Controle e Acompanhamento da Frota</p>
        </div>
      </div>
      <div class="report-info">
        <div class="date">Emitido em: ${new Date().toLocaleString('pt-BR')}</div>
        <div>Total de registros: ${LAST_ROWS.length}</div>
      </div>
    </div>
    
    <div class="filters-section">
      <div class="filters-title">FILTROS APLICADOS</div>
      <div class="filters-grid">
        <div class="filter-item">
          <span class="filter-label">Período:</span>
          <span class="filter-value">${new Date(dataIni+'T00:00:00').toLocaleDateString('pt-BR')} a ${new Date(dataFim+'T00:00:00').toLocaleDateString('pt-BR')}</span>
        </div>
        <div class="filter-item">
          <span class="filter-label">Veículo:</span>
          <span class="filter-value">${veiculoTxt}</span>
        </div>
        <div class="filter-item">
          <span class="filter-label">Motorista:</span>
          <span class="filter-value">${userTxt}</span>
        </div>
        <div class="filter-item">
          <span class="filter-label">Status:</span>
          <span class="filter-value">Todas as manutenções</span>
        </div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Data</th>
          <th>Veículo</th>
          <th>Motorista</th>
          <th>Tipo</th>
          <th>Descrição</th>
          <th>KM</th>
          <th>Custo</th>
          <th>Próxima</th>
        </tr>
      </thead>
      <tbody>${linhas}</tbody>
    </table>
    
    <div class="total-summary">
      <div class="total-row">
        <span>CUSTO TOTAL DO PERÍODO:</span>
        <span>R$ ${formatarBR(LAST_ROWS.reduce((sum, r) => sum + (Number(r.custo) || 0), 0), 2)}</span>
      </div>
    </div>
    
    <div class="footer">
      <p>Sistema Integrado de Gestão Operacional (SIGO) - BRS Transportes</p>
    </div>
  </body>
  </html>`;

  const frame = document.createElement('iframe');
  frame.style.position = 'fixed';
  frame.style.right = '0';
  frame.style.bottom = '0';
  frame.style.width = '0';
  frame.style.height = '0';
  frame.style.border = '0';
  document.body.appendChild(frame);
  const doc = frame.contentWindow.document;
  doc.open();
  doc.write(html);
  doc.close();
  setTimeout(() => { frame.contentWindow.focus(); frame.contentWindow.print(); setTimeout(()=>document.body.removeChild(frame), 500); }, 250);
}

document.addEventListener('DOMContentLoaded', function(){
  setMesAtual();
  carregarOpcoes();

  // Estado inicial: mostrar apenas a seção de instruções

  document.getElementById('btnFiltrar').addEventListener('click', listar);
  document.getElementById('btnLimpar').addEventListener('click', function(){
    document.getElementById('vehicle_id').value = '';
    if (IS_ADMIN) document.getElementById('user_id').value = '';
    setMesAtual();
    $('#resultadosSection').hide();
    $('#estadoInicial').show();
    $('#btnImprimir').prop('disabled', true);
  });
  document.getElementById('btnImprimir').addEventListener('click', imprimir);
});
</script>
@stop


