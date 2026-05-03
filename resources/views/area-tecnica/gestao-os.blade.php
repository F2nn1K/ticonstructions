@extends('adminlte::page')

@section('title', 'Gestão de O.S.')

@section('content_header')
    <h1><i class="fas fa-tasks mr-2"></i>Gestão de O.S.</h1>
@stop

@section('content')
<style>
    .card-gestao {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: none;
    }
    
    .card-gestao .card-header {
        background: linear-gradient(135deg, #2c7873 0%, #1a5653 100%);
        color: #fff;
        border-radius: 10px 10px 0 0;
        padding: 15px 20px;
    }
    
    .card-gestao .card-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }
    
    .filtros-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .table-os thead th {
        background: #e9ecef;
        color: #333;
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
    }
    
    .table-os tbody td {
        vertical-align: middle;
        font-size: 14px;
    }
    
    .badge-aberta {
        background: #28a745;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .badge-fechada {
        background: #6c757d;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .btn-fechar-os {
        background: #dc3545;
        border: none;
        padding: 5px 15px;
        font-size: 12px;
    }
    
    .btn-fechar-os:hover {
        background: #c82333;
    }
    
    .btn-reabrir-os {
        background: #28a745;
        border: none;
        padding: 5px 15px;
        font-size: 12px;
    }
    
    .btn-reabrir-os:hover {
        background: #218838;
    }
    
    .btn-visualizar-os {
        background: #17a2b8;
        border: none;
        padding: 5px 15px;
        font-size: 12px;
    }
    
    .btn-visualizar-os:hover {
        background: #138496;
    }
    
    .btn-excluir-os {
        background: #6c757d;
        border: none;
        padding: 5px 15px;
        font-size: 12px;
    }
    
    .btn-excluir-os:hover {
        background: #5a6268;
    }
    
    .btn-adicionar-os {
        background: #28a745;
        border: none;
        padding: 5px 15px;
        font-size: 12px;
    }
    
    .btn-adicionar-os:hover {
        background: #218838;
    }
    
    .info-admin {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 15px;
        font-size: 13px;
    }
    
    .info-usuario {
        background: #d1ecf1;
        border: 1px solid #17a2b8;
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 15px;
        font-size: 13px;
    }
    
    .sem-status-alert {
        background: #f8d7da;
        border: 1px solid #dc3545;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .contador-os {
        display: inline-block;
        background: #fff;
        padding: 8px 15px;
        border-radius: 20px;
        margin-right: 15px;
        font-size: 14px;
    }
    
    .contador-os strong {
        color: #2c7873;
    }
</style>

<div class="card card-gestao">
    <div class="card-header">
        <h3><i class="fas fa-tasks mr-2"></i>Gestão de Ordens de Serviço</h3>
    </div>
    <div class="card-body">
        <!-- Info de visualização -->
        <div id="infoVisualizacao"></div>
        
        <!-- Alerta de coluna status -->
        <div id="alertaSemStatus" class="sem-status-alert" style="display: none;">
            <strong><i class="fas fa-exclamation-triangle mr-2"></i>Atenção:</strong> 
            A coluna <code>status</code> não existe na tabela. Execute o SQL abaixo para habilitar o fechamento de O.S.:
            <pre class="mt-2 mb-0" style="background: #fff; padding: 10px; border-radius: 5px; font-size: 12px;">ALTER TABLE ordens_servico 
ADD COLUMN status ENUM('aberta', 'fechada') DEFAULT 'aberta' AFTER observacoes,
ADD COLUMN data_fechamento DATETIME NULL AFTER status;</pre>
        </div>
        
        <!-- Filtros -->
        <div class="filtros-container">
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="filtroStatus" class="form-control">
                        <option value="aberta">Abertas</option>
                        <option value="fechada">Fechadas</option>
                        <option value="todas">{{ __('Todas') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data Início</label>
                    <input type="date" id="filtroDataInicio" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data Fim</label>
                    <input type="date" id="filtroDataFim" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nº O.S.</label>
                    <input type="text" id="filtroNumeroOS" class="form-control" placeholder="Buscar...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary btn-block" onclick="filtrarOS()">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary btn-block" onclick="limparFiltros()">
                        <i class="fas fa-eraser mr-1"></i> Limpar
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Contadores -->
        <div class="mb-3">
            <span class="contador-os">
                <i class="fas fa-clipboard-list text-success mr-1"></i>
                Abertas: <strong id="contadorAbertas">0</strong>
            </span>
            <span class="contador-os">
                <i class="fas fa-clipboard-check text-secondary mr-1"></i>
                Fechadas: <strong id="contadorFechadas">0</strong>
            </span>
            <span class="contador-os">
                <i class="fas fa-list text-primary mr-1"></i>
                Total: <strong id="contadorTotal">0</strong>
            </span>
        </div>
        
        <!-- Tabela -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-os">
                <thead>
                    <tr>
                        <th>Nº O.S.</th>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Centro de Custo</th>
                        <th>Criado por</th>
                        <th>Status</th>
                        <th>Status Produto</th>
                        <th>Almoxarifado</th>
                        <th>Terceirizados</th>
                        <th>Frete</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaOS">
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            Carregando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Visualizar O.S. -->
<div class="modal fade" id="modalVisualizarOS" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #2c7873 0%, #1a5653 100%); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-eye mr-2"></i>Detalhes da O.S.</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="modalVisualizarContent">
                <!-- Conteúdo carregado via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Fechar') }}</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
let isAdmin = false;
let hasStatus = false;

$(document).ready(function() {
    carregarOS();
});

function carregarOS() {
    const params = {
        status: $('#filtroStatus').val(),
        data_inicio: $('#filtroDataInicio').val(),
        data_fim: $('#filtroDataFim').val(),
        numero_os: $('#filtroNumeroOS').val()
    };
    
    $.get('/area-tecnica/api/gestao-os/listar', params)
        .done(function(response) {
            if (response.success) {
                isAdmin = response.is_admin;
                hasStatus = response.has_status;
                
                // Limpar info de visualização (não mostrar aviso)
                $('#infoVisualizacao').html('');
                
                // Mostrar alerta se não tem status
                if (!hasStatus) {
                    $('#alertaSemStatus').show();
                    $('#filtroStatus').prop('disabled', true);
                } else {
                    $('#alertaSemStatus').hide();
                    $('#filtroStatus').prop('disabled', false);
                }
                
                renderizarTabela(response.ordens);
                atualizarContadores(response.ordens);
            }
        })
        .fail(function() {
            $('#tabelaOS').html(`
                <tr>
                    <td colspan="11" class="text-center text-danger py-4">
                        Erro ao carregar ordens de serviço
                    </td>
                </tr>
            `);
        });
}

function renderizarTabela(ordens) {
    if (!ordens || ordens.length === 0) {
        $('#tabelaOS').html(`
            <tr>
                <td colspan="11" class="text-center text-muted py-4">
                    Nenhuma O.S. encontrada
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    ordens.forEach(function(os) {
        const status = os.status || 'aberta';
        const statusBadge = status === 'fechada' 
            ? '<span class="badge-fechada">Fechada</span>'
            : '<span class="badge-aberta">Aberta</span>';
        
        const dataFormatada = os.data_os ? formatarData(os.data_os) : '-';
        const descricaoResumo = os.descricao ? (os.descricao.length > 50 ? os.descricao.substring(0, 50) + '...' : os.descricao) : '-';
        
        // Status do produto/material
        let statusProdutoBadge = '-';
        if (os.status_produto) {
            statusProdutoBadge = `
                <span class="badge" style="background: ${os.status_produto.cor}; color: #fff; padding: 5px 10px; border-radius: 15px; font-size: 11px; white-space: nowrap;">
                    <i class="fas ${os.status_produto.icone} mr-1"></i>${os.status_produto.texto}
                </span>
            `;
        }
        
        // Status dos prestadores/terceirizados
        let statusPrestadoresBadge = '-';
        if (os.status_prestadores) {
            let tooltipText = '';
            if (os.status_prestadores.valor_total > 0) {
                tooltipText = `Total: R$ ${os.status_prestadores.valor_total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                if (os.status_prestadores.valor_pendente > 0) {
                    tooltipText += ` | Pendente: R$ ${os.status_prestadores.valor_pendente.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                }
            }
            statusPrestadoresBadge = `
                <span class="badge" style="background: ${os.status_prestadores.cor}; color: #fff; padding: 5px 10px; border-radius: 15px; font-size: 11px; white-space: nowrap; cursor: help;" title="${tooltipText}">
                    <i class="fas ${os.status_prestadores.icone} mr-1"></i>${os.status_prestadores.texto}
                </span>
            `;
        }
        
        // Status do almoxarifado
        let statusAlmoxarifadoBadge = '-';
        if (os.status_almoxarifado) {
            let tooltipAlmox = '';
            if (os.status_almoxarifado.total > 0) {
                tooltipAlmox = `${os.status_almoxarifado.liberados || 0}/${os.status_almoxarifado.total} itens liberados`;
            }
            statusAlmoxarifadoBadge = `
                <span class="badge" style="background: ${os.status_almoxarifado.cor}; color: #fff; padding: 5px 10px; border-radius: 15px; font-size: 11px; white-space: nowrap; cursor: help;" title="${tooltipAlmox}">
                    <i class="fas ${os.status_almoxarifado.icone} mr-1"></i>${os.status_almoxarifado.texto}
                </span>
            `;
        }
        
        // Status do frete
        let statusFreteBadge = '<span class="badge" style="background: #6c757d; color: #fff; padding: 5px 10px; border-radius: 15px; font-size: 11px; white-space: nowrap;"><i class="fas fa-truck mr-1"></i>Sem Frete</span>';
        if (os.status_frete) {
            const freteStatusConfig = {
                'aguardando_cotacao': { cor: '#ffc107', texto: 'Aguard. Cotação', icone: 'fa-clock' },
                'em_cotacao': { cor: '#17a2b8', texto: 'Em Cotação', icone: 'fa-search-dollar' },
                'cotado': { cor: '#6f42c1', texto: 'Cotado', icone: 'fa-check-circle' },
                'aguardando_pagamento': { cor: '#fd7e14', texto: 'Aguard. Pgto', icone: 'fa-hourglass-half' },
                'pago': { cor: '#28a745', texto: 'Pago', icone: 'fa-money-bill-wave' },
                'liberado': { cor: '#20c997', texto: 'Liberado', icone: 'fa-truck-loading' },
                'entregue': { cor: '#6c757d', texto: 'Entregue', icone: 'fa-check-double' },
                'cancelado': { cor: '#dc3545', texto: 'Cancelado', icone: 'fa-times-circle' }
            };
            const cfg = freteStatusConfig[os.status_frete.status] || { cor: '#6c757d', texto: os.status_frete.status, icone: 'fa-truck' };
            let tooltipFrete = os.status_frete.total > 1 ? `${os.status_frete.total} fretes` : '';
            if (os.status_frete.valor > 0) {
                tooltipFrete += (tooltipFrete ? ' | ' : '') + `R$ ${os.status_frete.valor.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
            }
            statusFreteBadge = `
                <span class="badge" style="background: ${cfg.cor}; color: #fff; padding: 5px 10px; border-radius: 15px; font-size: 11px; white-space: nowrap; cursor: pointer;" 
                      title="${tooltipFrete}" onclick="verFretesOS(${os.id})">
                    <i class="fas ${cfg.icone} mr-1"></i>${cfg.texto}
                </span>
            `;
        }
        
        let acoes = `
            <button class="btn btn-visualizar-os text-white mr-1" onclick="visualizarOS(${os.id})" title="Visualizar">
                <i class="fas fa-eye"></i>
            </button>
        `;
        
        if (hasStatus) {
            if (status === 'aberta') {
                // Botão para adicionar mais itens (só O.S. aberta)
                acoes += `
                    <button class="btn btn-adicionar-os text-white mr-1" onclick="adicionarItensOS(${os.id})" title="Adicionar itens na O.S.">
                        <i class="fas fa-plus"></i>
                    </button>
                `;
                acoes += `
                    <button class="btn btn-fechar-os text-white" onclick="fecharOS(${os.id})" title="Fechar O.S.">
                        <i class="fas fa-lock"></i>
                    </button>
                `;
            } else if (isAdmin) {
                acoes += `
                    <button class="btn btn-reabrir-os text-white" onclick="reabrirOS(${os.id})" title="Reabrir O.S.">
                        <i class="fas fa-lock-open"></i>
                    </button>
                `;
            }
        }
        
        // Botão excluir apenas para administradores
        if (isAdmin) {
            acoes += `
                <button class="btn btn-excluir-os text-white ml-1" onclick="excluirOS(${os.id}, '${os.numero_os}')" title="Excluir O.S.">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        }
        
        html += `<tr>
            <td><strong>${os.numero_os || '-'}</strong></td>
            <td>${dataFormatada}</td>
            <td>${descricaoResumo}</td>
            <td>${os.centro_custo_nome || '-'}</td>
            <td>${os.criado_por || '-'}</td>
            <td>${statusBadge}</td>
            <td>${statusProdutoBadge}</td>
            <td>${statusAlmoxarifadoBadge}</td>
            <td>${statusPrestadoresBadge}</td>
            <td>${statusFreteBadge}</td>
            <td>${acoes}</td>
        </tr>`;
    });
    
    $('#tabelaOS').html(html);
}

function atualizarContadores(ordens) {
    let abertas = 0;
    let fechadas = 0;
    
    ordens.forEach(function(os) {
        if (os.status === 'fechada') {
            fechadas++;
        } else {
            abertas++;
        }
    });
    
    $('#contadorAbertas').text(abertas);
    $('#contadorFechadas').text(fechadas);
    $('#contadorTotal').text(ordens.length);
}

function filtrarOS() {
    carregarOS();
}

function limparFiltros() {
    $('#filtroStatus').val('aberta');
    $('#filtroDataInicio').val('');
    $('#filtroDataFim').val('');
    $('#filtroNumeroOS').val('');
    carregarOS();
}

function visualizarOS(id) {
    $.get(`/area-tecnica/api/ordens-servico/${id}`)
        .done(function(os) {
            const status = os.status || 'aberta';
            const statusBadge = status === 'fechada' 
                ? '<span class="badge-fechada">Fechada</span>'
                : '<span class="badge-aberta">Aberta</span>';
            
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nº O.S.:</strong> ${os.numero_os || '-'}</p>
                        <p><strong>Data:</strong> ${formatarData(os.data_os)}</p>
                        <p><strong>Criado por:</strong> ${os.criado_por_nome || '-'}</p>
                        <p><strong>Centro de Custo:</strong> ${os.centro_custo_nome || '-'}</p>
                        <p><strong>Status:</strong> ${statusBadge}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Endereço:</strong> ${os.endereco || '-'}</p>
                        <p><strong>Cidade/UF:</strong> ${os.cidade || '-'} / ${os.estado || '-'}</p>
                        <p><strong>CEP:</strong> ${os.cep || '-'}</p>
                        <p><strong>Telefone:</strong> ${os.telefone || '-'}</p>
                        <p><strong>CPF/CNPJ:</strong> ${os.cpf_cnpj || '-'}</p>
                    </div>
                </div>
                <hr>
                <p><strong>Tipo de Atendimento:</strong> ${os.tipo_atendimento || '-'}</p>
                <p><strong>Descrição do Serviço:</strong></p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    ${os.descricao || '-'}
                </div>
                <p><strong>Observações:</strong></p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    ${os.observacoes || 'Nenhuma observação'}
                </div>
            `;
            
            // Prestadores de Serviço
            if (os.prestadores && os.prestadores.length > 0) {
                let totalPrestadores = 0;
                let totalPagos = 0;
                let totalPendentes = 0;
                
                html += `
                    <hr>
                    <p><strong>Prestadores de Serviço (Terceirizados):</strong></p>
                    <table class="table table-sm table-bordered">
                        <thead style="background: #28a745; color: #fff;">
                            <tr>
                                <th>Prestador</th>
                                <th>Descrição</th>
                                <th style="width: 120px;">Valor</th>
                                <th style="width: 120px;">Status Pgto.</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                os.prestadores.forEach(function(p) {
                    const valor = parseFloat(p.valor) || 0;
                    totalPrestadores += valor;
                    
                    let statusPgto = '';
                    if (p.status_pagamento === 'pago') {
                        statusPgto = '<span class="badge badge-success"><i class="fas fa-check"></i> Pago</span>';
                        totalPagos += valor;
                    } else if (p.status_pagamento === 'aguardando_pagamento') {
                        statusPgto = '<span class="badge badge-warning"><i class="fas fa-clock"></i> Aguard. Pagamento</span>';
                        totalPendentes += valor;
                    } else if (p.status_pagamento === 'aguardando_autorizacao') {
                        statusPgto = '<span class="badge badge-info"><i class="fas fa-hourglass-half"></i> Aguard. Autorização</span>';
                        totalPendentes += valor;
                    } else {
                        statusPgto = '<span class="badge badge-secondary"><i class="fas fa-question"></i> Pendente</span>';
                        totalPendentes += valor;
                    }
                    
                    html += `<tr>
                        <td>${p.nome_prestador}</td>
                        <td>${p.descricao_servico || '-'}</td>
                        <td class="text-right">R$ ${valor.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td class="text-center">${statusPgto}</td>
                    </tr>`;
                });
                html += `
                        </tbody>
                        <tfoot style="background: #f8f9fa;">
                            <tr>
                                <td colspan="2" class="text-right"><strong>Total:</strong></td>
                                <td class="text-right"><strong>R$ ${totalPrestadores.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                `;
                
                if (totalPendentes > 0) {
                    html += `<div class="alert alert-warning" style="font-size: 12px;">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Valor pendente:</strong> R$ ${totalPendentes.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        <br><small>Fluxo: Aprovar OC em Suprimentos → Pagar no Financeiro → Serviço liberado</small>
                    </div>`;
                } else if (totalPrestadores > 0) {
                    html += `<div class="alert alert-success" style="font-size: 12px;">
                        <i class="fas fa-check-circle mr-1"></i>
                        <strong>Todos os prestadores foram pagos!</strong>
                    </div>`;
                }
            }
            
            // Materiais
            if (os.materiais && os.materiais.length > 0) {
                html += `
                    <hr>
                    <p><strong>Materiais Utilizados:</strong></p>
                    <table class="table table-sm table-bordered">
                        <thead style="background: #3490dc; color: #fff;">
                            <tr>
                                <th>Material</th>
                                <th style="width: 100px;">Quantidade</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                os.materiais.forEach(function(m) {
                    html += `<tr><td>${m.produto_nome}</td><td>${m.quantidade}</td></tr>`;
                });
                html += `</tbody></table>`;
            }
            
            // Solicitações de Materiais
            if (os.solicitacoes && os.solicitacoes.length > 0) {
                html += `
                    <hr>
                    <p><strong>Solicitação de Materiais:</strong></p>
                    <table class="table table-sm table-bordered">
                        <thead style="background: #f0ad4e; color: #fff;">
                            <tr>
                                <th>Descrição</th>
                                <th style="width: 100px;">Quantidade</th>
                                <th style="width: 80px;">Unidade</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                os.solicitacoes.forEach(function(s) {
                    html += `<tr><td>${s.descricao}</td><td>${s.quantidade}</td><td>${s.unidade || 'UN'}</td></tr>`;
                });
                html += `</tbody></table>`;
                html += `<div class="alert alert-info mt-2" style="font-size: 12px;">
                    <i class="fas fa-info-circle mr-1"></i> 
                    Estes materiais foram enviados para <strong>Cotação</strong>.
                </div>`;
            }
            
            $('#modalVisualizarContent').html(html);
            $('#modalVisualizarOS').modal('show');
        });
}

// Redirecionar para editar/adicionar itens na O.S.
function adicionarItensOS(id) {
    window.location.href = `/area-tecnica/ordem-servico?editar=${id}`;
}

function fecharOS(id) {
    Swal.fire({
        title: 'Fechar O.S.',
        text: 'Tem certeza que deseja FECHAR esta O.S.? Ela ficará no histórico.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, fechar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/area-tecnica/api/ordens-servico/${id}/fechar`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: 'O.S. fechada com sucesso!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    carregarOS();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao fechar O.S.'
                    });
                }
            })
            .fail(function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao fechar O.S.';
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: msg
                });
            });
        }
    });
}

function reabrirOS(id) {
    Swal.fire({
        title: 'Reabrir O.S.',
        text: 'Tem certeza que deseja REABRIR esta O.S.?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, reabrir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/area-tecnica/api/ordens-servico/${id}/reabrir`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: 'O.S. reaberta com sucesso!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    carregarOS();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao reabrir O.S.'
                    });
                }
            })
            .fail(function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao reabrir O.S.';
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: msg
                });
            });
        }
    });
}

function excluirOS(id, numeroOS) {
    Swal.fire({
        title: 'Excluir O.S.',
        html: `<p>Tem certeza que deseja <strong>EXCLUIR PERMANENTEMENTE</strong> a O.S. <strong>${numeroOS}</strong>?</p>
               <p class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Esta ação não pode ser desfeita!</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/area-tecnica/api/ordens-servico/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: 'O.S. excluída com sucesso!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    carregarOS();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao excluir O.S.'
                    });
                }
            })
            .fail(function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao excluir O.S.';
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: msg
                });
            });
        }
    });
}

function formatarData(data) {
    if (!data) return '-';
    const partes = data.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return data;
}

// Ver fretes da O.S.
function verFretesOS(osId) {
    window.open('/frete?os=' + osId, '_blank');
}
</script>
@stop
