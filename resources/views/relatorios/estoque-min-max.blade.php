@extends('adminlte::page')

@section('title', 'Relatório: Máximo e Mínimo (Estoque)')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-chart-bar text-primary mr-3"></i>
            Relatório: Máximo e Mínimo (Estoque)
        </h1>
        <p class="text-muted mt-1 mb-0">Produtos abaixo do mínimo definido e acima do máximo (opcional)</p>
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
            <div class="form-inline">
                <div class="form-check mr-3">
                    <input class="form-check-input" type="checkbox" id="onlyBelow" checked>
                    <label class="form-check-label" for="onlyBelow">Mostrar apenas abaixo do mínimo</label>
                </div>
                <button id="btnBuscar" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i> Gerar
                </button>
            </div>
        </div>
    </div>

    <div class="card" id="resultadoCard" style="display:none;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i>Resultados</h5>
            <button class="btn btn-success btn-sm" id="btnImprimir"><i class="fas fa-print mr-1"></i> Imprimir</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tabelaResultado">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Produto</th>
                            <th>Descrição</th>
                            <th class="text-center" style="width:120px">Qtd</th>
                            <th class="text-center" style="width:120px">Mínimo</th>
                            <th class="text-center" style="width:120px">Máximo</th>
                            <th class="text-center" style="width:160px">Status</th>
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
.badge-status { padding: 6px 10px; border-radius: 10px; font-weight: 600; }
.badge-danger-soft { background: #fee2e2; color: #991b1b; }
.badge-warning-soft { background: #fef3c7; color: #92400e; }
.badge-success-soft { background: #dcfce7; color: #166534; }
</style>
@stop

@section('js')
<script>
$(function(){
    $('#btnBuscar').on('click', function(){
        const onlyBelow = $('#onlyBelow').is(':checked');
        $.get('/api/relatorios/estoque-min-max', { apenas_abaixo: onlyBelow })
          .done(function(resp){
            if (!resp || !resp.success){
                Swal.fire('Erro','Falha ao carregar dados.','error');
                return;
            }
            const dados = resp.data || [];
            const tbody = $('#tabelaResultado tbody');
            tbody.empty();
            let idx = 1;
            dados.forEach(d => {
                const status = d.abaixo_minimo ? '<span class="badge badge-status badge-danger-soft">Abaixo do mínimo</span>' : (d.acima_maximo ? '<span class="badge badge-status badge-warning-soft">Acima do máximo</span>' : '<span class="badge badge-status badge-success-soft">OK</span>');
                tbody.append(`
                    <tr>
                        <td class="text-center">${idx++}</td>
                        <td>${d.nome||'-'}</td>
                        <td>${d.descricao||'-'}</td>
                        <td class="text-center">${d.quantidade ?? 0}</td>
                        <td class="text-center">${d.minimo ?? 0}</td>
                        <td class="text-center">${d.maximo ?? ''}</td>
                        <td class="text-center">${status}</td>
                    </tr>
                `);
            });
            $('#resultadoCard').show();
          })
          .fail(function(){ Swal.fire('Erro','Não foi possível carregar o relatório.','error'); });
    });

    $('#btnImprimir').on('click', function(){
        imprimirRelatorio();
    });
});

function imprimirRelatorio() {
    const dados = [];
    $('#tabelaResultado tbody tr').each(function() {
        const cells = $(this).find('td');
        if (cells.length >= 7) {
            dados.push({
                id: cells.eq(0).text().trim(),
                nome: cells.eq(1).text().trim(),
                descricao: cells.eq(2).text().trim(),
                quantidade: cells.eq(3).text().trim(),
                minimo: cells.eq(4).text().trim(),
                maximo: cells.eq(5).text().trim(),
                status: cells.eq(6).find('.badge').text().trim() || cells.eq(6).text().trim()
            });
        }
    });

    if (dados.length === 0) {
        alert('Nenhum dado disponível para impressão. Gere um relatório primeiro.');
        return;
    }

    const onlyBelow = $('#onlyBelow').is(':checked');
    const filtroTexto = onlyBelow ? 'Apenas produtos abaixo do mínimo' : 'Todos os produtos';

    const linhas = dados.map(d => `
        <tr>
            <td class="text-center">${d.id}</td>
            <td>${d.nome}</td>
            <td>${d.descricao}</td>
            <td class="text-center qtd-value">${d.quantidade}</td>
            <td class="text-center min-value">${d.minimo}</td>
            <td class="text-center max-value">${d.maximo}</td>
            <td class="text-center">
                <span class="status-badge ${d.status.includes('Abaixo') ? 'status-danger' : (d.status.includes('Acima') ? 'status-warning' : 'status-success')}">
                    ${d.status}
                </span>
            </td>
        </tr>
    `).join('');

    const html = `<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Relatório de Estoque - Máximo e Mínimo</title>
        <style>
            @page { 
                size: A4 portrait; 
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
                font-size: 20px;
                font-weight: 700;
                color: #2c3e50;
                margin: 0 0 5px 0;
                letter-spacing: 0.5px;
            }
            .company-info .subtitle {
                font-size: 13px;
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
            .filter-text {
                font-size: 11px;
                color: #5a6c7d;
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
            .qtd-value, .min-value, .max-value {
                font-weight: 600;
                color: #2980b9;
            }
            .status-badge {
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 10px;
                font-weight: 600;
                text-transform: uppercase;
            }
            .status-danger {
                background: #fee2e2;
                color: #991b1b;
            }
            .status-warning {
                background: #fef3c7;
                color: #92400e;
            }
            .status-success {
                background: #dcfce7;
                color: #166534;
            }
            .summary-section {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 6px;
                margin-top: 20px;
                border-left: 4px solid #27ae60;
            }
            .summary-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
                text-align: center;
            }
            .summary-item {
                background: white;
                padding: 10px;
                border-radius: 6px;
                border: 1px solid #e0e6ed;
            }
            .summary-value {
                font-size: 16px;
                font-weight: 700;
                color: #2c3e50;
                margin-bottom: 5px;
            }
            .summary-label {
                font-size: 10px;
                color: #7f8c8d;
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
                    <h1>RELATÓRIO DE ESTOQUE</h1>
                    <p class="subtitle">Controle de Níveis Mínimos e Máximos</p>
                </div>
            </div>
            <div class="report-info">
                <div class="date">Emitido em: ${new Date().toLocaleString('pt-BR')}</div>
                <div>Total de produtos: ${dados.length}</div>
            </div>
        </div>
        
        <div class="filters-section">
            <div class="filters-title">FILTROS APLICADOS</div>
            <div class="filter-text">
                <strong>Exibição:</strong> ${filtroTexto}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Produto</th>
                    <th>Descrição</th>
                    <th class="text-center" style="width:120px">Qtd Atual</th>
                    <th class="text-center" style="width:120px">Mínimo</th>
                    <th class="text-center" style="width:120px">Máximo</th>
                    <th class="text-center" style="width:160px">Status</th>
                </tr>
            </thead>
            <tbody>
                ${linhas}
            </tbody>
        </table>
        
        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value">${dados.filter(d => d.status.includes('Abaixo')).length}</div>
                    <div class="summary-label">🔴 Abaixo do Mínimo</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">${dados.filter(d => d.status.includes('Acima')).length}</div>
                    <div class="summary-label">🟡 Acima do Máximo</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">${dados.filter(d => d.status.includes('OK')).length}</div>
                    <div class="summary-label">🟢 Dentro do Padrão</div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Sistema Integrado de Gestão Operacional (SIGO) - BRS Transportes</p>
        </div>
    </body>
    </html>`;

    // Imprimir usando iframe
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
    doc.write(html);
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
</script>
@stop


