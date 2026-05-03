@extends('adminlte::page')

@section('title', __('Gestor de Ocorrências - Frota'))
@section('plugins.Sweetalert2', true)

@section('content_header')
<h1>{{ __('Gestor de Ocorrências') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        @foreach($veiculosComOcorrencias as $item)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card vehicle-card {{ $item['tem_ocorrencias'] ? 'border-warning' : 'border-success' }}" 
                     onclick="abrirHistoricoVeiculo({{ $item['veiculo']->id }}, '{{ $item['veiculo']->placa }}', '{{ $item['veiculo']->marca }} {{ $item['veiculo']->modelo }}')"
                     style="cursor: pointer;">
                    <div class="card-body text-center">
                        <div class="vehicle-icon mb-3">
                            <i class="fas fa-car fa-3x {{ $item['tem_ocorrencias'] ? 'text-warning' : 'text-success' }}"></i>
                        </div>
                        <h5 class="card-title mb-1">{{ $item['veiculo']->placa }}</h5>
                        <p class="text-muted mb-3">{{ $item['veiculo']->marca }} {{ $item['veiculo']->modelo }}</p>
                        
                        <div class="status-info mb-3">
                            <span class="badge badge-{{ $item['veiculo']->status === 'ativo' ? 'success' : ($item['veiculo']->status === 'manutencao' ? 'danger' : 'warning') }}">
                                {{ ucfirst($item['veiculo']->status) }}
                            </span>
                        </div>
                        
                        @if($item['tem_ocorrencias'])
                            <div class="occurrence-summary">
                                <span class="badge badge-warning badge-lg">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $item['total_ocorrencias'] }} pendente(s)
                                </span>
                            </div>
                        @else
                            <div class="occurrence-summary">
                                <span class="badge badge-success badge-lg">
                                    <i class="fas fa-check mr-1"></i>
                                    Sem pendências
                                </span>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <small class="text-muted">Clique para ver histórico</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Histórico do Veículo -->
<div class="modal fade" id="modalHistoricoVeiculo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-car mr-2"></i>
                    Histórico de Ocorrências - <span id="modalVeiculoInfo"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Abas -->
                <ul class="nav nav-tabs" id="historicoTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="pendentes-tab" data-toggle="tab" href="#pendentes" role="tab">
                            <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                            Pendentes (<span id="countPendentes">0</span>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="resolvidas-tab" data-toggle="tab" href="#resolvidas" role="tab">
                            <i class="fas fa-check text-success mr-1"></i>
                            Resolvidas (<span id="countResolvidas">0</span>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="todas-tab" data-toggle="tab" href="#todas" role="tab">
                            <i class="fas fa-list mr-1"></i>
                            Todas (<span id="countTodas">0</span>)
                        </a>
                    </li>
                </ul>
                
                <!-- Conteúdo das abas -->
                <div class="tab-content mt-3" id="historicoTabContent">
                    <div class="tab-pane fade show active" id="pendentes" role="tabpanel">
                        <div id="listaPendentes"></div>
                    </div>
                    <div class="tab-pane fade" id="resolvidas" role="tabpanel">
                        <div id="listaResolvidas"></div>
                    </div>
                    <div class="tab-pane fade" id="todas" role="tabpanel">
                        <div id="listaTodas"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .vehicle-card {
        border-radius: 12px;
        height: 100%;
    }
    .border-warning {
        border-color: #ffc107 !important;
        border-width: 3px !important;
    }
    .border-success {
        border-color: #28a745 !important;
        border-width: 3px !important;
    }
    .vehicle-icon {
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
    .occurrence-item {
        border-left: 4px solid #ffc107;
        cursor: pointer;
    }
    .occurrence-resolved {
        border-left-color: #28a745;
        opacity: 0.8;
    }
    .tab-content {
        max-height: 500px;
        overflow-y: auto;
    }
    .occurrence-status {
        position: absolute;
        top: 8px;
        right: 8px;
        font-size: 0.75rem;
    }
    .occurrence-item {
        position: relative;
        margin-bottom: 0.75rem;
        padding: 1rem;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #e9ecef;
    }
    
    /* ===============================================
       DARK MODE - Modo Escuro
       =============================================== */
    
    /* Modal no dark mode */
    html[data-theme="dark"] #modalHistoricoVeiculo .modal-content {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    
    html[data-theme="dark"] #modalHistoricoVeiculo .modal-header {
        background-color: #1e293b !important;
        border-bottom-color: #334155 !important;
    }
    
    html[data-theme="dark"] #modalHistoricoVeiculo .modal-title {
        color: #f1f5f9 !important;
    }
    
    html[data-theme="dark"] #modalHistoricoVeiculo .close {
        color: #f1f5f9 !important;
    }
    
    html[data-theme="dark"] #modalHistoricoVeiculo .modal-body {
        background-color: #1e293b !important;
    }
    
    /* Tabs no dark mode */
    html[data-theme="dark"] .nav-tabs {
        border-bottom-color: #334155 !important;
    }
    
    html[data-theme="dark"] .nav-tabs .nav-link {
        color: #94a3b8 !important;
        border-color: transparent !important;
    }
    
    html[data-theme="dark"] .nav-tabs .nav-link:hover {
        color: #f1f5f9 !important;
        border-color: #475569 #475569 #334155 !important;
    }
    
    html[data-theme="dark"] .nav-tabs .nav-link.active {
        color: #f1f5f9 !important;
        background-color: #1e293b !important;
        border-color: #334155 #334155 #1e293b !important;
    }
    
    /* Itens de ocorrência no dark mode */
    html[data-theme="dark"] .occurrence-item {
        background: #0f172a !important;
        background-color: #0f172a !important;
        border-color: #334155 !important;
        border-left-color: #ffc107 !important;
    }
    
    html[data-theme="dark"] .occurrence-item.occurrence-resolved {
        border-left-color: #28a745 !important;
    }
    
    html[data-theme="dark"] .occurrence-item:hover {
        background: #1e293b !important;
        background-color: #1e293b !important;
    }
    
    html[data-theme="dark"] .occurrence-item strong {
        color: #f1f5f9 !important;
    }
    
    html[data-theme="dark"] .occurrence-item p {
        color: #cbd5e1 !important;
    }
    
    html[data-theme="dark"] .occurrence-item .text-muted {
        color: #94a3b8 !important;
    }
    
    html[data-theme="dark"] .occurrence-item .text-info {
        color: #38bdf8 !important;
    }
    
    /* Vehicle cards no dark mode */
    html[data-theme="dark"] .vehicle-card {
        background-color: #1e293b !important;
    }
    
    html[data-theme="dark"] .vehicle-card .card-body {
        background-color: #1e293b !important;
    }
    
    html[data-theme="dark"] .vehicle-card .card-title {
        color: #f1f5f9 !important;
    }
    
    html[data-theme="dark"] .vehicle-card .text-muted {
        color: #94a3b8 !important;
    }
    
    /* Tab content no dark mode */
    html[data-theme="dark"] .tab-content {
        background-color: #1e293b !important;
    }
    
    /* Área vazia no dark mode */
    html[data-theme="dark"] .tab-content .text-center.text-muted {
        color: #94a3b8 !important;
    }
    
    /* Descrições das ocorrências */
    html[data-theme="dark"] .occurrence-description p {
        color: #cbd5e1 !important;
    }
    
    html[data-theme="dark"] .occurrence-description small {
        color: #38bdf8 !important;
    }
    
    html[data-theme="dark"] .occurrence-header small {
        color: #94a3b8 !important;
    }
    
    /* Badges de status */
    html[data-theme="dark"] .badge-secondary {
        background-color: #475569 !important;
        color: #f1f5f9 !important;
    }
</style>
@stop

@section('js')
<script>
let veiculoAtual = null;

function abrirHistoricoVeiculo(veiculoId, placa, modelo) {
    veiculoAtual = veiculoId;
    $('#modalVeiculoInfo').text(`${placa} - ${modelo}`);
    carregarHistoricoCompleto(veiculoId);
    $('#modalHistoricoVeiculo').modal('show');
}

async function carregarHistoricoCompleto(veiculoId) {
    try {
        // Carregar todas as ocorrências do veículo
        const resp = await fetch(`/frota/ocorrencias/api/veiculo/${veiculoId}/historico`);
        if (!resp.ok) throw new Error('Falha ao carregar histórico');
        
        const dados = await resp.json();
        const { pendentes, resolvidas, todas } = dados;
        
        // Atualizar contadores
        $('#countPendentes').text(pendentes.length);
        $('#countResolvidas').text(resolvidas.length);
        $('#countTodas').text(todas.length);
        
        // Preencher listas
        preencherListaOcorrencias('#listaPendentes', pendentes, true);
        preencherListaOcorrencias('#listaResolvidas', resolvidas, false);
        preencherListaOcorrencias('#listaTodas', todas, null);
        
        // Aplicar estilos dark mode se necessário
        setTimeout(() => aplicarDarkModeOcorrencias(), 100);
        
    } catch (e) {
        console.error('Erro ao carregar histórico:', e);
        $('#listaPendentes, #listaResolvidas, #listaTodas').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Erro ao carregar histórico. Por favor, tente novamente.
            </div>
        `);
    }
}

function aplicarDarkModeOcorrencias() {
    if (document.documentElement.getAttribute('data-theme') === 'dark') {
        document.querySelectorAll('.occurrence-item').forEach(item => {
            item.style.backgroundColor = '#0f172a';
            item.style.borderColor = '#334155';
        });
        document.querySelectorAll('.occurrence-item strong').forEach(el => {
            el.style.color = '#f1f5f9';
        });
        document.querySelectorAll('.occurrence-item p').forEach(el => {
            el.style.color = '#cbd5e1';
        });
        document.querySelectorAll('.occurrence-item .text-muted').forEach(el => {
            el.style.color = '#94a3b8';
        });
    }
}

function preencherListaOcorrencias(container, ocorrencias, somenteAcoesPendentes) {
    const $container = $(container);
    
    if (!ocorrencias || ocorrencias.length === 0) {
        $container.html(`
            <div class="text-center py-4 text-muted">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>Nenhuma ocorrência encontrada</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    ocorrencias.forEach(ocorrencia => {
        const status = ocorrencia.status || 'novo';
        const isResolvido = status === 'resolvido';
        const isEmAndamento = status === 'em_andamento';
        
        const badgeStatus = isResolvido ? 'badge-success' : (isEmAndamento ? 'badge-warning' : 'badge-secondary');
        const statusText = isResolvido ? 'Resolvido' : (isEmAndamento ? 'Em andamento' : 'Novo');
        
        const podeMarcarEmAndamento = status === 'novo';
        const podeMarcarResolvido = status !== 'resolvido';
        
        let acoes = '';
        if (somenteAcoesPendentes === true) {
            // Aba pendentes - mostrar ações
            if (podeMarcarEmAndamento) {
                acoes += `<button class="btn btn-sm btn-warning mr-1" onclick="atualizarStatusOcorrencia(${ocorrencia.id}, 'em_andamento')">
                    <i class="fas fa-play mr-1"></i>Em andamento
                </button>`;
            }
            if (podeMarcarResolvido) {
                acoes += `<button class="btn btn-sm btn-success" onclick="atualizarStatusOcorrencia(${ocorrencia.id}, 'resolvido')">
                    <i class="fas fa-check mr-1"></i>Resolver
                </button>`;
            }
        } else if (somenteAcoesPendentes === null) {
            // Aba todas - mostrar ações se não resolvido
            if (podeMarcarEmAndamento) {
                acoes += `<button class="btn btn-sm btn-warning mr-1" onclick="atualizarStatusOcorrencia(${ocorrencia.id}, 'em_andamento')">
                    <i class="fas fa-play mr-1"></i>Em andamento
                </button>`;
            }
            if (podeMarcarResolvido) {
                acoes += `<button class="btn btn-sm btn-success mr-1" onclick="atualizarStatusOcorrencia(${ocorrencia.id}, 'resolvido')">
                    <i class="fas fa-check mr-1"></i>Resolver
                </button>`;
            }
        }
        
        html += `
            <div class="occurrence-item ${isResolvido ? 'occurrence-resolved' : ''}" onclick="verDetalhesOcorrencia(${ocorrencia.id})">
                <div class="occurrence-status">
                    <span class="badge ${badgeStatus}">${statusText}</span>
                </div>
                <div class="occurrence-header mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${ocorrencia.motorista_nome || 'Motorista não informado'}</strong>
                            <br><small class="text-muted">${formatarDataBr(ocorrencia.data)} ${ocorrencia.hora || ''}</small>
                        </div>
                        <small class="text-muted">${formatarDataBr(ocorrencia.created_at)}</small>
                    </div>
                </div>
                <div class="occurrence-description mb-2">
                    <p class="mb-1">${limitarTexto(ocorrencia.descricao || '', 120)}</p>
                    ${ocorrencia.sugestao ? `<small class="text-info"><strong>Sugestão:</strong> ${limitarTexto(ocorrencia.sugestao, 80)}</small>` : ''}
                </div>
                ${acoes ? `<div class="occurrence-actions mt-2" onclick="event.stopPropagation()">${acoes}</div>` : ''}
            </div>
        `;
    });
    
    $container.html(html);
}

async function atualizarStatusOcorrencia(id, status) {
    try {
        const form = new FormData();
        form.append('status', status);
        form.append('_token', '{{ csrf_token() }}');
        
        const resp = await fetch(`/frota/ocorrencias/api/${id}/status`, {
            method: 'POST',
            body: form,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        if (!resp.ok) {
            let msg = 'Falha ao atualizar status.';
            try { const j = await resp.json(); if (j.message) msg = j.message; } catch {}
            throw new Error(msg);
        }
        
        // Recarregar histórico
        await carregarHistoricoCompleto(veiculoAtual);
        
        // Mostrar sucesso
        const statusText = status === 'resolvido' ? 'resolvida' : 'marcada como em andamento';
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: `Ocorrência ${statusText}.`,
            timer: 2000,
            showConfirmButton: false
        });
        
    } catch (e) {
        Swal.fire('Erro', e.message, 'error');
    }
}

async function verDetalhesOcorrencia(id) {
    try {
        const resp = await fetch(`/frota/ocorrencias/api/${id}`);
        if (!resp.ok) throw new Error('Falha ao carregar dados');
        
        const o = await resp.json();
        const statusAtual = (o.status || 'novo').toLowerCase();
        
        const html = `
            <div style="text-align:left">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Motorista:</strong><br>${o.motorista || 'Não informado'}
                    </div>
                    <div class="col-md-6">
                        <strong>Data/Hora:</strong><br>${o.data || ''} ${o.hora || ''}
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Status:</strong><br>
                    <span class="badge badge-${statusAtual === 'resolvido' ? 'success' : (statusAtual === 'em_andamento' ? 'warning' : 'secondary')}">${
                        statusAtual === 'resolvido' ? 'Resolvido' : (statusAtual === 'em_andamento' ? 'Em andamento' : 'Novo')
                    }</span>
                </div>
                <div id="area-fotos-ocorrencia" class="mb-3" style="display:none;">
                    <strong>Imagens:</strong>
                    <div id="grid-fotos-ocorrencia" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:6px;"></div>
                </div>
                <div class="mb-3">
                    <strong>Descrição:</strong><br>${escapeHtml(o.descricao || '')}
                </div>
                ${o.sugestao ? `<div class="mb-3"><strong>Sugestão:</strong><br>${escapeHtml(o.sugestao)}</div>` : ''}
                <small class="text-muted">Registrado em: ${o.created_at || ''}</small>
            </div>
        `;
        
        Swal.fire({
            title: 'Detalhes da Ocorrência',
            html,
            width: 600,
            showConfirmButton: true,
            confirmButtonText: 'Fechar'
        });
        
        // Após abrir o modal, buscar fotos disponíveis e renderizar miniaturas
        try {
            const rFotos = await fetch(`/frota/ocorrencias/api/${id}/fotos`);
            if (rFotos.ok) {
                const j = await rFotos.json();
                const lista = (j && j.success) ? (j.data || []) : [];
                if (lista.length > 0) {
                    const area = document.getElementById('area-fotos-ocorrencia');
                    const grid = document.getElementById('grid-fotos-ocorrencia');
                    area.style.display = '';
                    grid.innerHTML = '';
                    lista.forEach(f => {
                        const a = document.createElement('a');
                        a.href = f.url;
                        a.target = '_blank';
                        a.rel = 'noopener noreferrer';
                        a.style.display = 'inline-block';
                        a.style.width = '84px';
                        a.style.height = '84px';
                        a.style.border = '1px solid #e0e0e0';
                        a.style.borderRadius = '6px';
                        a.style.overflow = 'hidden';
                        a.title = `Abrir imagem ${f.idx}`;

                        const img = document.createElement('img');
                        img.src = f.url;
                        img.alt = `Foto ${f.idx}`;
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        a.appendChild(img);
                        grid.appendChild(a);
                    });
                }
            }
        } catch {}

    } catch (e) {
        Swal.fire('Erro', 'Não foi possível carregar os detalhes.', 'error');
    }
}

function formatarDataBr(data) {
    if (!data) return '';
    try {
        return new Date(data).toLocaleDateString('pt-BR');
    } catch {
        return data;
    }
}

function limitarTexto(texto, limite) {
    if (!texto) return '';
    return texto.length > limite ? texto.substring(0, limite) + '...' : texto;
}

function escapeHtml(s){
    if (!s) return '';
    const div = document.createElement('div');
    div.innerText = s; 
    return div.innerHTML;
}

// Manter funções antigas para compatibilidade (caso ainda sejam chamadas)
function abrirOcorrencia(id) {
    verDetalhesOcorrencia(id);
}

function verTodasOcorrencias(veiculoId) {
    abrirHistoricoVeiculo(veiculoId, 'Veículo', '');
}
</script>
@stop


