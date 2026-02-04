@extends('adminlte::page')

@section('title', 'Baixa da O.S.')

@section('content_header')
    <h1><i class="fas fa-clipboard-check mr-2"></i>Baixa da O.S.</h1>
    <small class="text-muted">Libere os materiais solicitados nas Ordens de Serviço</small>
@stop

@section('content')
<style>
    .card-baixa {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: none;
    }
    
    .card-baixa .card-header {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        color: #fff;
        border-radius: 10px 10px 0 0;
        padding: 15px 20px;
    }
    
    .filtros-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .badge-pendente {
        background: #ffc107;
        color: #000;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .badge-liberado {
        background: #28a745;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .btn-liberar {
        background: #28a745;
        border: none;
        padding: 8px 20px;
        font-size: 13px;
    }
    
    .btn-liberar:hover {
        background: #1e7e34;
    }
    
    .item-material {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 8px;
        border-left: 4px solid #17a2b8;
    }
    
    .item-material.liberado {
        border-left-color: #28a745;
        background: #d4edda;
    }
    
    .os-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 15px;
        overflow: hidden;
    }
    
    .os-card-header {
        background: #e9ecef;
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .os-card-body {
        padding: 15px;
    }
    
    .stats-card {
        color: #fff;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .stats-card h2 {
        font-size: 36px;
        margin: 0;
    }
    
    .stats-card p {
        margin: 5px 0 0;
        opacity: 0.9;
    }
    
    /* Área de impressão - oculta na tela, visível apenas na impressão */
    #areaPrint {
        display: none;
    }
    
    /* Estilos para impressão */
    @media print {
        /* Esconder tudo da página */
        body > *:not(#areaPrint) {
            display: none !important;
        }
        
        /* Mostrar apenas a área de impressão */
        #areaPrint {
            display: block !important;
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: auto !important;
            background: #fff !important;
            z-index: 99999 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        #areaPrint * {
            visibility: visible !important;
            color: #000 !important;
        }
        
        .print-layout {
            display: block !important;
            width: 100% !important;
            padding: 20px !important;
            background: #fff !important;
        }
        
        .print-header, .print-info, .print-table, .print-assinaturas {
            display: block !important;
        }
        
        .print-table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        
        .print-table th, .print-table td {
            border: 1px solid #000 !important;
            padding: 8px !important;
        }
        
        .print-assinaturas {
            display: flex !important;
            justify-content: space-between !important;
            margin-top: 50px !important;
        }
    }
    
    .print-layout {
        font-family: Arial, sans-serif;
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        color: #000;
    }
    
    .print-header {
        text-align: center;
        border-bottom: 2px solid #333;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    
    .print-header h1 {
        font-size: 24px;
        margin: 0;
    }
    
    .print-header p {
        margin: 5px 0 0;
        color: #666;
    }
    
    .print-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 5px;
    }
    
    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }
    
    .print-table th, .print-table td {
        border: 1px solid #333;
        padding: 10px;
        text-align: left;
    }
    
    .print-table th {
        background: #333;
        color: #fff;
    }
    
    .print-assinaturas {
        display: flex;
        justify-content: space-between;
        margin-top: 60px;
    }
    
    .assinatura-box {
        width: 45%;
        text-align: center;
    }
    
    .assinatura-linha {
        border-top: 1px solid #333;
        margin-top: 50px;
        padding-top: 10px;
    }
    
    .funcionario-search-container {
        position: relative;
    }
    
    .funcionario-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .funcionario-result-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    
    .funcionario-result-item:hover {
        background: #f0f0f0;
    }
</style>

<div class="card card-baixa">
    <div class="card-header">
        <h3 class="mb-0"><i class="fas fa-clipboard-check mr-2"></i>Materiais Solicitados nas O.S.</h3>
    </div>
    <div class="card-body">
        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
                    <h2 id="totalPendentes">0</h2>
                    <p>Materiais Pendentes</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                    <h2 id="totalItens">0</h2>
                    <p>Itens para Liberar</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                    <h2 id="totalLiberados">0</h2>
                    <p>Liberados Hoje</p>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filtros-container">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Status</label>
                    <select id="filtroStatus" class="form-control">
                        <option value="pendente">Pendentes</option>
                        <option value="liberado">Liberados</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Data Início</label>
                    <input type="date" id="filtroDataInicio" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Data Fim</label>
                    <input type="date" id="filtroDataFim" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Nº O.S.</label>
                    <input type="text" id="filtroOS" class="form-control" placeholder="Buscar...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-right">
                    <button class="btn btn-primary" onclick="carregarOS()">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    <button class="btn btn-secondary ml-2" onclick="limparFiltros()">
                        <i class="fas fa-eraser mr-1"></i> Limpar
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Lista de O.S. com materiais -->
        <div id="listaOS">
            <div class="text-center text-muted py-5">
                <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                <p>Carregando...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Liberar Materiais -->
<div class="modal fade" id="modalLiberar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-hand-holding mr-2"></i>Liberar Materiais</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>O.S.:</strong> <span id="modalNumeroOS"></span> | 
                    <strong>Data:</strong> <span id="modalDataOS"></span> |
                    <strong>Funcionário:</strong> <span id="modalFuncionario"></span>
                </div>
                
                <div class="form-group">
                    <label class="font-weight-bold"><i class="fas fa-user mr-1"></i> Quem está retirando o material?</label>
                    <div class="funcionario-search-container">
                        <input type="text" id="nomeRetirada" class="form-control" 
                               placeholder="Digite o nome de quem está retirando..." autocomplete="off">
                        <div id="funcionarioResults" class="funcionario-results"></div>
                    </div>
                    <small class="text-muted">Este nome aparecerá no comprovante de retirada</small>
                </div>
                
                <hr>
                
                <h6 class="font-weight-bold mb-3">
                    <i class="fas fa-boxes mr-2"></i>Materiais a Liberar
                </h6>
                
                <div id="listaMateriais">
                    <!-- Materiais serão carregados aqui -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarLiberacao()">
                    <i class="fas fa-check mr-1"></i> Liberar e Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Área de Impressão -->
<div id="areaPrint">
    <div class="print-layout">
        <div class="print-header">
            <h1>COMPROVANTE DE RETIRADA DE MATERIAIS</h1>
            <p>Sistema de Gestão - ASC Sistemas</p>
        </div>
        
        <div class="print-info">
            <div><strong>O.S.:</strong> <span id="printNumeroOS">-</span></div>
            <div><strong>Data:</strong> <span id="printData">-</span></div>
            <div><strong>Hora:</strong> <span id="printHora">-</span></div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <p><strong>Centro de Custo:</strong> <span id="printCentroCusto">-</span></p>
            <p><strong>Solicitante:</strong> <span id="printSolicitante">-</span></p>
        </div>
        
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 10%">#</th>
                    <th style="width: 60%">Material</th>
                    <th style="width: 15%">Quantidade</th>
                    <th style="width: 15%">Unidade</th>
                </tr>
            </thead>
            <tbody id="printMateriais">
                <tr><td colspan="4" style="text-align: center;">Nenhum material</td></tr>
            </tbody>
        </table>
        
        <div class="print-assinaturas">
            <div class="assinatura-box">
                <div class="assinatura-linha">
                    <strong id="printRetiradoPor">_________________________</strong><br>
                    <small>Retirado por</small>
                </div>
            </div>
            <div class="assinatura-box">
                <div class="assinatura-linha">
                    <strong id="printLiberadoPor">_________________________</strong><br>
                    <small>Encarregado / Liberado por</small>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 40px; text-align: center; font-size: 12px; color: #666;">
            <p>Documento gerado em <span id="printDataGeracao">-</span></p>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
let osAtual = null;
let funcionariosCache = {};

$(document).ready(function() {
    // Data padrão - último mês
    const hoje = new Date();
    const mesPassado = new Date();
    mesPassado.setMonth(mesPassado.getMonth() - 1);
    
    $('#filtroDataInicio').val(mesPassado.toISOString().split('T')[0]);
    $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);
    
    carregarOS();
    
    // Busca de funcionários
    $('#nomeRetirada').on('input', function() {
        const nome = $(this).val().trim();
        
        if (nome.length < 2) {
            $('#funcionarioResults').hide();
            return;
        }
        
        $.get('/api/baixa-os/funcionarios', { nome: nome })
            .done(function(funcionarios) {
                if (funcionarios.length > 0) {
                    funcionariosCache = {};
                    let html = '';
                    funcionarios.forEach(function(f) {
                        funcionariosCache[f.id] = f;
                        html += `<div class="funcionario-result-item" data-id="${f.id}" data-nome="${f.name}">${f.name}</div>`;
                    });
                    $('#funcionarioResults').html(html).show();
                } else {
                    $('#funcionarioResults').hide();
                }
            });
    });
    
    // Selecionar funcionário
    $(document).on('click', '.funcionario-result-item', function() {
        const nome = $(this).data('nome');
        $('#nomeRetirada').val(nome);
        $('#funcionarioResults').hide();
    });
    
    // Esconder resultados ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#nomeRetirada, #funcionarioResults').length) {
            $('#funcionarioResults').hide();
        }
    });
});

function carregarOS() {
    const status = $('#filtroStatus').val();
    const dataInicio = $('#filtroDataInicio').val();
    const dataFim = $('#filtroDataFim').val();
    const numeroOS = $('#filtroOS').val();
    
    $('#listaOS').html(`
        <div class="text-center text-muted py-5">
            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
            <p>Carregando...</p>
        </div>
    `);
    
    $.get('/api/baixa-os/listar', {
        status: status,
        data_inicio: dataInicio,
        data_fim: dataFim,
        numero_os: numeroOS
    })
    .done(function(response) {
        renderizarOS(response.ordens || []);
        $('#totalPendentes').text(response.total_pendentes || 0);
        $('#totalItens').text(response.total_itens || 0);
        $('#totalLiberados').text(response.total_liberados_hoje || 0);
    })
    .fail(function(xhr) {
        console.error('Erro:', xhr.responseText);
        $('#listaOS').html(`
            <div class="text-center text-danger py-5">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <p>Erro ao carregar dados. Verifique se o SQL foi executado.</p>
            </div>
        `);
    });
}

function renderizarOS(ordens) {
    if (ordens.length === 0) {
        $('#listaOS').html(`
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>Nenhuma O.S. encontrada com materiais</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    
    ordens.forEach(function(os) {
        // Verificar se todos os materiais foram liberados
        const todosLiberados = os.materiais.every(m => m.liberado == 1);
        const statusBadge = todosLiberados 
            ? '<span class="badge badge-liberado"><i class="fas fa-check mr-1"></i>Liberado</span>'
            : '<span class="badge badge-pendente"><i class="fas fa-clock mr-1"></i>Pendente</span>';
        
        html += `
            <div class="os-card">
                <div class="os-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="mr-2">${os.numero_os}</strong>
                        <span class="text-muted mr-3">${formatarData(os.data_os)}</span>
                        ${statusBadge}
                    </div>
                    <div>
                        ${!todosLiberados ? `
                            <button class="btn btn-liberar btn-sm text-white" onclick="abrirLiberacao('${os.id}', '${os.numero_os}', '${os.data_os}', '${os.funcionario_nome || ''}', '${os.centro_custo_nome || ''}')">
                                <i class="fas fa-hand-holding mr-1"></i> Liberar
                            </button>
                        ` : `
                            <button class="btn btn-info btn-sm text-white" onclick="reimprimirComprovante('${os.id}')">
                                <i class="fas fa-print mr-1"></i> Reimprimir
                            </button>
                        `}
                    </div>
                </div>
                <div class="os-card-body">
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <small class="text-muted">Solicitante:</small>
                            <p class="mb-1"><strong>${os.funcionario_nome || '-'}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Centro de Custo:</small>
                            <p class="mb-1"><strong>${os.centro_custo_nome || '-'}</strong></p>
                        </div>
                    </div>
                    <hr class="my-2">
                    <small class="text-muted d-block mb-2">Materiais Solicitados (${os.materiais.length}):</small>
                    ${renderizarMateriais(os.materiais)}
                </div>
            </div>
        `;
    });
    
    $('#listaOS').html(html);
}

function renderizarMateriais(materiais) {
    if (materiais.length === 0) {
        return '<p class="text-muted">Nenhum material</p>';
    }
    
    let html = '<div class="row">';
    materiais.forEach(function(m) {
        const liberadoClass = m.liberado ? 'liberado' : '';
        const liberadoIcon = m.liberado ? '<i class="fas fa-check-circle text-success ml-2"></i>' : '';
        
        html += `
            <div class="col-md-6">
                <div class="item-material ${liberadoClass}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${m.produto_nome || 'Produto não encontrado'}</strong>
                            ${liberadoIcon}
                        </div>
                        <span class="badge badge-primary">${m.quantidade} un</span>
                    </div>
                    ${m.liberado && m.retirado_por ? `<small class="text-success">Retirado por: ${m.retirado_por}</small>` : ''}
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

function formatarData(data) {
    if (!data) return '-';
    const d = new Date(data + 'T00:00:00');
    return d.toLocaleDateString('pt-BR');
}

let osParaLiberar = null;

function abrirLiberacao(osId, numeroOS, dataOS, funcionarioNome, centroCusto) {
    osParaLiberar = {
        id: osId,
        numero_os: numeroOS,
        data_os: dataOS,
        funcionario_nome: funcionarioNome,
        centro_custo_nome: centroCusto
    };
    
    $('#modalNumeroOS').text(numeroOS);
    $('#modalDataOS').text(formatarData(dataOS));
    $('#modalFuncionario').text(funcionarioNome || '-');
    $('#nomeRetirada').val('');
    
    // Buscar materiais da O.S.
    $.get('/api/baixa-os/listar', { numero_os: numeroOS, status: 'todos' })
        .done(function(response) {
            if (response.ordens && response.ordens.length > 0) {
                const os = response.ordens[0];
                osParaLiberar.materiais = os.materiais;
                osParaLiberar.centro_custo_nome = os.centro_custo_nome;
                osParaLiberar.funcionario_nome = os.funcionario_nome;
                
                let html = '';
                os.materiais.forEach(function(m) {
                    if (!m.liberado) {
                        html += `
                            <div class="item-material">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>${m.produto_nome || 'Produto'}</strong>
                                    <span class="badge badge-primary">${m.quantidade} un</span>
                                </div>
                            </div>
                        `;
                    }
                });
                
                if (!html) {
                    html = '<p class="text-success">Todos os materiais já foram liberados!</p>';
                }
                
                $('#listaMateriais').html(html);
            }
        });
    
    $('#modalLiberar').modal('show');
}

function confirmarLiberacao() {
    if (!osParaLiberar) return;
    
    const nomeRetirada = $('#nomeRetirada').val().trim();
    
    if (!nomeRetirada) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Informe o nome de quem está retirando o material'
        });
        $('#nomeRetirada').focus();
        return;
    }
    
    Swal.fire({
        title: 'Confirmar Liberação',
        html: `Confirma a liberação dos materiais para <strong>${nomeRetirada}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, liberar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/api/baixa-os/${osParaLiberar.id}/liberar`,
                method: 'POST',
                data: {
                    retirado_por: nomeRetirada
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    $('#modalLiberar').modal('hide');
                    
                    // Preparar impressão
                    prepararImpressao(response);
                    
                    // Preparar e abrir janela de impressão
                    prepararImpressao(response);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Materiais Liberados!',
                        text: 'O comprovante foi aberto para impressão.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        carregarOS();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao liberar materiais'
                    });
                }
            })
            .fail(function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Não foi possível liberar os materiais'
                });
            });
        }
    });
}

function prepararImpressao(response) {
    const agora = new Date();
    
    // Materiais
    let materiaisHtml = '';
    if (response.itens && response.itens.length > 0) {
        response.itens.forEach(function(item, index) {
            materiaisHtml += `
                <tr>
                    <td style="text-align: center; border: 1px solid #000; padding: 8px;">${index + 1}</td>
                    <td style="border: 1px solid #000; padding: 8px;">${item.produto_nome || 'Material'}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 8px;">${item.quantidade}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 8px;">UN</td>
                </tr>
            `;
        });
    } else if (osParaLiberar.materiais) {
        osParaLiberar.materiais.forEach(function(item, index) {
            materiaisHtml += `
                <tr>
                    <td style="text-align: center; border: 1px solid #000; padding: 8px;">${index + 1}</td>
                    <td style="border: 1px solid #000; padding: 8px;">${item.produto_nome || 'Material'}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 8px;">${item.quantidade}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 8px;">UN</td>
                </tr>
            `;
        });
    }
    
    // Abrir janela de impressão
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Comprovante de Retirada - ${osParaLiberar.numero_os}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; margin: 0; }
                .print-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
                .print-header h1 { font-size: 22px; margin: 0; }
                .print-header p { margin: 5px 0 0; color: #666; }
                .print-info { display: flex; justify-content: space-between; margin-bottom: 20px; padding: 10px; background: #f5f5f5; border-radius: 5px; }
                .print-info div { font-size: 14px; }
                .dados-os { margin-bottom: 20px; }
                .dados-os p { margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                th { background: #333; color: #fff; padding: 10px; text-align: left; border: 1px solid #000; }
                td { border: 1px solid #000; padding: 8px; }
                .assinaturas { display: flex; justify-content: space-between; margin-top: 60px; }
                .assinatura-box { width: 45%; text-align: center; }
                .assinatura-linha { border-top: 1px solid #333; margin-top: 50px; padding-top: 10px; }
                .rodape { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
                @media print { body { padding: 10px; } }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>COMPROVANTE DE RETIRADA DE MATERIAIS</h1>
                <p>Sistema de Gestão - ASC Sistemas</p>
            </div>
            
            <div class="print-info">
                <div><strong>O.S.:</strong> ${osParaLiberar.numero_os}</div>
                <div><strong>Data:</strong> ${formatarData(osParaLiberar.data_os)}</div>
                <div><strong>Hora:</strong> ${agora.toLocaleTimeString('pt-BR')}</div>
            </div>
            
            <div class="dados-os">
                <p><strong>Centro de Custo:</strong> ${osParaLiberar.centro_custo_nome || '-'}</p>
                <p><strong>Solicitante:</strong> ${osParaLiberar.funcionario_nome || '-'}</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%">#</th>
                        <th style="width: 60%">Material</th>
                        <th style="width: 15%">Quantidade</th>
                        <th style="width: 15%">Unidade</th>
                    </tr>
                </thead>
                <tbody>
                    ${materiaisHtml}
                </tbody>
            </table>
            
            <div class="assinaturas">
                <div class="assinatura-box">
                    <div class="assinatura-linha">
                        <strong>${response.retirado_por || ''}</strong><br>
                        <small>Retirado por</small>
                    </div>
                </div>
                <div class="assinatura-box">
                    <div class="assinatura-linha">
                        <strong>${response.liberado_por || ''}</strong><br>
                        <small>Encarregado / Liberado por</small>
                    </div>
                </div>
            </div>
            
            <div class="rodape">
                <p>Documento gerado em ${agora.toLocaleString('pt-BR')}</p>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    
    // Aguardar carregamento e imprimir
    setTimeout(function() {
        printWindow.print();
    }, 500);
}

function esconderAreaImpressao() {
    // Não precisa mais - usando janela popup
}

function reimprimirComprovante(osId) {
    // Buscar dados da O.S.
    $.get('/api/baixa-os/listar', { status: 'todos' })
        .done(function(response) {
            const os = response.ordens.find(o => o.id == osId);
            if (os) {
                osParaLiberar = os;
                
                // Simular response para reutilizar função
                const fakeResponse = {
                    retirado_por: os.materiais[0]?.retirado_por || '-',
                    liberado_por: '-',
                    itens: os.materiais
                };
                
                prepararImpressao(fakeResponse);
            }
        });
}

function limparFiltros() {
    const hoje = new Date();
    const mesPassado = new Date();
    mesPassado.setMonth(mesPassado.getMonth() - 1);
    
    $('#filtroStatus').val('pendente');
    $('#filtroDataInicio').val(mesPassado.toISOString().split('T')[0]);
    $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);
    $('#filtroOS').val('');
    
    carregarOS();
}
</script>
@stop
