@extends('adminlte::page')

@section('title', 'Frota - Relatório de Consumo')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-chart-line text-primary mr-3"></i>
            Relatório de Consumo
        </h1>
        <p class="text-muted mt-1 mb-0">Análise de consumo da frota por período</p>
    </div>
    <div>
        <!-- Botão removido - usar apenas o da área de filtros -->
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
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>
                                    Gerar Relatório
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" onclick="limparFiltros()">
                                    <i class="fas fa-eraser mr-1"></i>
                                    Limpar Filtros
                                </button>
                                <button type="button" class="btn btn-info ml-2" onclick="imprimirRelatorio()" disabled id="btnImprimirFiltros">
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
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-gas-pump fa-2x mb-2"></i>
                    <h3 id="totalAbastecimentos">0</h3>
                    <p class="mb-0">Total Abastecimentos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-tint fa-2x mb-2"></i>
                    <h3 id="totalLitros">0L</h3>
                    <p class="mb-0">Total de Litros</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-road fa-2x mb-2"></i>
                    <h3 id="totalKm">0 km</h3>
                    <p class="mb-0">KM Percorridos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-2x mb-2"></i>
                    <h3 id="consumoMedio">0,0</h3>
                    <p class="mb-0">Km/L Médio</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico -->
    <div class="row mb-4" id="graficoSection" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area mr-2"></i>
                        Evolução do Consumo
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoConsumo" height="100"></canvas>
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
                        Relatório Detalhado de Consumo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabelaRelatorio">
                            <thead class="table-dark">
                                <tr>
                                    <th>Veículo</th>
                                    <th>Período</th>
                                    <th>Total Abastecimentos</th>
                                    <th>Total Litros</th>
                                    <th>KM Inicial</th>
                                    <th>KM Final</th>
                                    <th>KM Percorridos</th>
                                    <th>Consumo (Km/L)</th>
                                    <th>Custo Total</th>
                                    <th>Custo por KM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dados serão inseridos via JavaScript -->
                            </tbody>
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
                <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Relatório de Consumo da Frota</h4>
                <p class="text-muted">Configure os filtros acima e clique em "Gerar Relatório" para visualizar o consumo por período</p>
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
}

.table td {
    border-bottom: 1px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
<script>
let graficoConsumo = null;
let mapaVeiculos = {};

$(document).ready(function() {
    // Configurar CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Definir datas padrão (último mês)
    const hoje = new Date();
    const mesPassado = new Date();
    mesPassado.setMonth(hoje.getMonth() - 1);
    
    $('#data_inicio').val(mesPassado.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);

    // Carregar veículos no select (modelo - placa)
    $.get('/frota/api/veiculos').done(function(veiculos){
        const sel = $('#veiculo_id');
        sel.find('option').remove();
        sel.append('<option value="">Todos os veículos</option>');
        (veiculos||[]).forEach(v => {
            const label = `${v.modelo || ''} - ${v.placa || ''}`.trim();
            mapaVeiculos[String(v.id)] = label;
            sel.append(`<option value="${v.id}">${label}</option>`);
        });
    });

    // Submissão do formulário
    $('#formFiltros').submit(function(e) {
        e.preventDefault();
        gerarRelatorio();
    });
});

function gerarRelatorio() {
    const filtros = {
        data_inicio: $('#data_inicio').val(),
        data_fim: $('#data_fim').val(),
        veiculo_id: $('#veiculo_id').val()
    };

    // Validar datas
    if (!filtros.data_inicio || !filtros.data_fim) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Por favor, informe o período para gerar o relatório.'
        });
        return;
    }

    // Buscar dados reais (placeholder simples)
    $.get('/frota/api/abastecimentos', filtros).done(function(rows){
        const selecionado = $('#veiculo_id').val();
        const ini = String(filtros.data_inicio);
        const fim = String(filtros.data_fim);
        // Aplicar filtro de período (fallback caso a API ignore)
        let filtrados = (rows||[]).filter(r => String(r.data) >= ini && String(r.data) <= fim);
        // Filtro por veículo (opcional)
        if (selecionado) filtrados = filtrados.filter(r => String(r.vehicle_id) === String(selecionado));
        const periodoLabel = `${new Date(ini).toLocaleDateString('pt-BR')} - ${new Date(fim).toLocaleDateString('pt-BR')}`;
        const dados = agregarConsumo(filtrados, periodoLabel);
        exibirResultados(dados);
        atualizarResumo(dados);
        criarGrafico(dados);
        $('#estadoInicial').hide();
        $('#resumoSection, #graficoSection, #resultadosSection').show();
        $('#btnImprimirFiltros').prop('disabled', false);
    });
}

function agregarConsumo(rows, periodoLabel){
    const porVeiculo = {};
    rows.forEach(r => {
        const key = r.vehicle_id;
        if(!porVeiculo[key]) porVeiculo[key] = { veiculo: key, total_abastecimentos:0, total_litros:0, km_inicial:null, km_final:null, custo_total:0 };
        const item = porVeiculo[key];
        item.total_abastecimentos++;
        item.total_litros += Number(r.litros||0);
        item.custo_total += Number(r.valor||0);
        if (item.km_inicial === null || r.km < item.km_inicial) item.km_inicial = r.km;
        if (item.km_final === null || r.km > item.km_final) item.km_final = r.km;
    });
    return Object.values(porVeiculo).map(v => {
        const km_percorridos = (v.km_final ?? 0) - (v.km_inicial ?? 0);
        const consumo_kml = v.total_litros > 0 ? km_percorridos / v.total_litros : 0;
        const custo_por_km = km_percorridos > 0 ? v.custo_total / km_percorridos : 0;
        const veiculoLabel = mapaVeiculos[String(v.veiculo)] || String(v.veiculo);
        return { veiculoId: String(v.veiculo), veiculo: veiculoLabel, periodo: periodoLabel || '-', total_abastecimentos: v.total_abastecimentos, total_litros: v.total_litros, km_inicial: v.km_inicial||0, km_final: v.km_final||0, km_percorridos, consumo_kml, custo_total: v.custo_total, custo_por_km };
    });
}

function exibirResultados(dados) {
    const tbody = $('#tabelaRelatorio tbody');
    tbody.empty();

    dados.forEach(item => {
        tbody.append(`
            <tr>
                <td><strong>${item.veiculo}</strong></td>
                <td>${item.periodo}</td>
                <td>${item.total_abastecimentos}</td>
                <td>${item.total_litros}L</td>
                <td>${item.km_inicial.toLocaleString()} km</td>
                <td>${item.km_final.toLocaleString()} km</td>
                <td>${item.km_percorridos.toLocaleString()} km</td>
                <td><span class="badge badge-info">${item.consumo_kml.toFixed(2)} km/L</span></td>
                <td>R$ ${item.custo_total.toFixed(2)}</td>
                <td>R$ ${item.custo_por_km.toFixed(3)}</td>
            </tr>
        `);
    });
}

function atualizarResumo(dados) {
    const totais = dados.reduce((acc, item) => {
        acc.abastecimentos += item.total_abastecimentos;
        acc.litros += item.total_litros;
        acc.km += item.km_percorridos;
        acc.custo += item.custo_total;
        return acc;
    }, { abastecimentos: 0, litros: 0, km: 0, custo: 0 });

    const consumoMedio = totais.km / totais.litros;

    $('#totalAbastecimentos').text(totais.abastecimentos);
    const litrosFmt = Number(totais.litros||0).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
    $('#totalLitros').text(`${litrosFmt}L`);
    $('#totalKm').text(`${Number(totais.km||0).toLocaleString('pt-BR')} km`);
    $('#consumoMedio').text(`${Number(consumoMedio||0).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} km/L`);
}

function criarGrafico(dados) {
    const ctx = document.getElementById('graficoConsumo').getContext('2d');
    
    // Destruir gráfico anterior se existir
    if (graficoConsumo) {
        graficoConsumo.destroy();
    }

    const labels = dados.map(item => item.veiculo);
    const consumos = dados.map(item => item.consumo_kml);
    const custos = dados.map(item => item.custo_por_km);

    graficoConsumo = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Consumo (km/L)',
                data: consumos,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Custo por KM (R$)',
                data: custos,
                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Consumo vs Custo por Veículo'
                },
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Consumo (km/L)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Custo por KM (R$)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

function limparFiltros() {
    $('#formFiltros')[0].reset();
    
    // Redefinir datas padrão
    const hoje = new Date();
    const mesPassado = new Date();
    mesPassado.setMonth(hoje.getMonth() - 1);
    
    $('#data_inicio').val(mesPassado.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);
    
    $('#resumoSection, #graficoSection, #resultadosSection').hide();
    $('#estadoInicial').show();
    $('#btnImprimirFiltros').prop('disabled', true);
}

function imprimirRelatorio() {
    // Preparar conteúdo para impressão sem abrir nova aba (usa iframe oculto)
    const tabela = document.getElementById('tabelaRelatorio');
    const resumo = document.getElementById('resumoSection');
    
    if (!tabela) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Gere o relatório primeiro para poder imprimir.'
        });
        return;
    }
    
    // Obter filtros aplicados
    const dataInicio = $('#data_inicio').val();
    const dataFim = $('#data_fim').val();
    const veiculo = $('#veiculo_id option:selected').text();
    
    // HTML para impressão com layout horizontal
    const htmlImpressao = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Relatório de Consumo - Frota</title>
            <style>
                @page {
                    size: A4 landscape;
                    margin: 15mm;
                }
                
                body {
                    font-family: Arial, sans-serif;
                    font-size: 11px;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                
                .header {
                    text-align: center;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                
                .header h1 {
                    color: #007bff;
                    margin: 0;
                    font-size: 18px;
                }
                
                .header p {
                    margin: 5px 0 0 0;
                    color: #666;
                }
                
                .filtros {
                    background-color: #f8f9fa;
                    padding: 10px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                }
                
                .filtros h3 {
                    margin: 0 0 10px 0;
                    font-size: 14px;
                    color: #495057;
                }
                
                .filtros-grid {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 15px;
                }
                
                .filtro-item {
                    display: flex;
                    flex-direction: column;
                }
                
                .filtro-label {
                    font-weight: bold;
                    margin-bottom: 2px;
                    font-size: 10px;
                }
                
                .filtro-valor {
                    font-size: 11px;
                }
                
                .resumo {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 15px;
                    margin-bottom: 20px;
                }
                
                .resumo-card {
                    background-color: #f8f9fa;
                    padding: 12px;
                    border-radius: 5px;
                    text-align: center;
                    border-left: 4px solid #007bff;
                }
                
                .resumo-card h4 {
                    margin: 0;
                    font-size: 16px;
                    color: #007bff;
                }
                
                .resumo-card p {
                    margin: 5px 0 0 0;
                    font-size: 10px;
                    color: #666;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                
                th, td {
                    border: 1px solid #ddd;
                    padding: 6px;
                    text-align: left;
                    font-size: 10px;
                }
                
                th {
                    background-color: #007bff;
                    color: white;
                    font-weight: bold;
                    text-align: center;
                }
                
                tr:nth-child(even) {
                    background-color: #f8f9fa;
                }
                
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    padding-top: 10px;
                    border-top: 1px solid #ddd;
                    font-size: 9px;
                    color: #666;
                }
                
                @media print {
                    body { print-color-adjust: exact; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>📊 Relatório de Consumo da Frota</h1>
                <p>Análise detalhada de consumo por período - Gerado em ${new Date().toLocaleString('pt-BR')}</p>
            </div>
            
            <div class="filtros">
                <h3>Filtros Aplicados</h3>
                <div class="filtros-grid">
                    <div class="filtro-item">
                        <span class="filtro-label">Período:</span>
                        <span class="filtro-valor">${new Date(dataInicio).toLocaleDateString('pt-BR')} até ${new Date(dataFim).toLocaleDateString('pt-BR')}</span>
                    </div>
                    <div class="filtro-item">
                        <span class="filtro-label">Veículo:</span>
                        <span class="filtro-valor">${veiculo}</span>
                    </div>
                    <div class="filtro-item">
                        <span class="filtro-label">Total de Registros:</span>
                        <span class="filtro-valor">${tabela.rows.length - 1} veículos</span>
                    </div>
                </div>
            </div>
            
            ${resumo ? `
            <div class="resumo">
                <div class="resumo-card">
                    <h4>${document.getElementById('totalAbastecimentos')?.textContent || '0'}</h4>
                    <p>Total Abastecimentos</p>
                </div>
                <div class="resumo-card">
                    <h4>${document.getElementById('totalLitros')?.textContent || '0L'}</h4>
                    <p>Total de Litros</p>
                </div>
                <div class="resumo-card">
                    <h4>${document.getElementById('totalKm')?.textContent || '0 km'}</h4>
                    <p>KM Percorridos</p>
                </div>
                <div class="resumo-card">
                    <h4>${document.getElementById('consumoMedio')?.textContent || '0 km/L'}</h4>
                    <p>Km/L Médio</p>
                </div>
            </div>
            ` : ''}
            
            ${tabela.outerHTML}
            
            <div class="footer">
                <p>Relatório gerado pelo Sistema de Gestão Integrada | Data: ${new Date().toLocaleDateString('pt-BR')} às ${new Date().toLocaleTimeString('pt-BR')}</p>
            </div>
        </body>
        </html>
    `;
    // Criar iframe oculto, imprimir e remover após
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

    const cleanup = () => {
        try { document.body.removeChild(iframe); } catch (e) {}
    };

    // Disparar impressão quando conteúdo estiver pronto
    iframe.onload = function() {
        try { iframe.contentWindow.focus(); } catch (e) {}
        try { iframe.contentWindow.print(); } catch (e) {}
    };
    if (iframe.contentWindow) {
        iframe.contentWindow.onafterprint = cleanup;
    }
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@stop