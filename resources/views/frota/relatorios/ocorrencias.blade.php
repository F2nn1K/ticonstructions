@extends('adminlte::page')

@section('title', 'Relatório de Ocorrências')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
            Relatório de Ocorrências
        </h1>
        <small class="text-muted">Visualize e analise as ocorrências registradas na frota</small>
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
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-filter mr-2"></i>
                        Filtros de Pesquisa
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formFiltro">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold">Data Início</label>
                                <input type="date" id="data_inicio" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold">Data Fim</label>
                                <input type="date" id="data_fim" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold">Veículo</label>
                                <select id="veiculo_id" class="form-control">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold">Status</label>
                                <select id="status" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="pendentes">Pendentes (Sem solução)</option>
                                    <option value="em_andamento">Em Andamento</option>
                                    <option value="resolvido">Resolvido</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-warning text-white mr-2">
                                    <i class="fas fa-search mr-1"></i>Gerar Relatório
                                </button>
                                <button type="button" id="btnLimpar" class="btn btn-secondary">
                                    <i class="fas fa-eraser mr-1"></i>Limpar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4" id="cardsEstatisticas" style="display:none;">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-warning">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3 id="totalOcorrencias">0</h3>
                    <p class="mb-0">Total de Ocorrências</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-danger">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                    <h3 id="totalNovas">0</h3>
                    <p class="mb-0">Novas</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-info">
                <div class="card-body text-center">
                    <i class="fas fa-cog fa-2x mb-2"></i>
                    <h3 id="totalAndamento">0</h3>
                    <p class="mb-0">Em Andamento</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3 id="totalResolvidas">0</h3>
                    <p class="mb-0">Resolvidas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card" id="cardResultados" style="display:none;">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>
                Ocorrências Registradas
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0" id="tabelaOcorrencias">
                    <thead class="thead-dark">
                        <tr>
                            <th width="80">ID</th>
                            <th width="140">Data/Hora</th>
                            <th>Veículo</th>
                            <th>Motorista</th>
                            <th>Descrição</th>
                            <th width="120" class="text-center">Status</th>
                            <th width="100" class="text-center">Fotos</th>
                            <th width="140" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Estado Inicial -->
    <div class="row" id="estadoInicial">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Relatório de Ocorrências</h4>
                <p class="text-muted">Configure os filtros acima e clique em "Gerar Relatório" para visualizar as ocorrências da frota</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
    .table td {
        vertical-align: middle;
    }
</style>
@stop

@section('js')
<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$(function(){
    carregarVeiculos();
    
    $('#formFiltro').on('submit', function(e){
        e.preventDefault();
        gerarRelatorio();
    });

    $('#btnLimpar').on('click', function(){
        $('#formFiltro')[0].reset();
        $('#cardResultados, #cardsEstatisticas, #estadoInicial').hide();
        $('#estadoInicial').show();
    });
});

function carregarVeiculos(){
    $.get('/frota/api/veiculos').done(function(veiculos){
        const sel = $('#veiculo_id');
        veiculos.forEach(v => {
            const label = `${v.placa} - ${v.marca||''} ${v.modelo||''}`;
            sel.append(`<option value="${v.id}">${label}</option>`);
        });
    });
}

function gerarRelatorio(){
    const dados = {
        data_inicio: $('#data_inicio').val(),
        data_fim: $('#data_fim').val(),
        veiculo_id: $('#veiculo_id').val(),
        status: $('#status').val()
    };

    $.get('/frota/api/relatorios/ocorrencias', dados).done(function(resp){
        if(!resp.success) {
            alert(resp.message || 'Erro ao gerar relatório');
            return;
        }

        const data = resp.data || [];
        const tbody = $('#tabelaOcorrencias tbody');
        tbody.empty();

        // Atualizar cards
        let novas = 0, andamento = 0, resolvidas = 0;
        data.forEach(o => {
            const st = (o.status || 'novo').toLowerCase();
            if (st === 'novo') novas++;
            else if (st === 'em_andamento') andamento++;
            else if (st === 'resolvido') resolvidas++;
        });

        $('#totalOcorrencias').text(data.length);
        $('#totalNovas').text(novas);
        $('#totalAndamento').text(andamento);
        $('#totalResolvidas').text(resolvidas);

        // Renderizar tabela
        if(data.length === 0){
            tbody.append('<tr><td colspan="8" class="text-center text-muted">Nenhuma ocorrência encontrada</td></tr>');
        } else {
            data.forEach(o => {
                const dataHora = formatarDataHora(o.created_at);
                const veiculo = o.veiculo ? `${o.veiculo.placa} - ${o.veiculo.marca||''} ${o.veiculo.modelo||''}` : '-';
                const motorista = o.motorista_nome || '-';
                const descricao = escapeHtml(o.descricao || '');
                
                let statusBadge = '<span class="badge badge-secondary">-</span>';
                const st = (o.status || 'novo').toLowerCase();
                if (st === 'novo') statusBadge = '<span class="badge badge-danger">Novo</span>';
                else if (st === 'em_andamento') statusBadge = '<span class="badge badge-info">Em Andamento</span>';
                else if (st === 'resolvido') statusBadge = '<span class="badge badge-success">Resolvido</span>';

                // Contar fotos disponíveis
                let qtdFotos = 0;
                for(let i = 1; i <= 10; i++){
                    if(o[`foto${i}`]) qtdFotos++;
                }
                const fotosTexto = qtdFotos > 0 ? `<i class=\"fas fa-camera\"></i> ${qtdFotos}` : '-';

                const acoes = `
                    <div class=\"btn-group btn-group-sm\" role=\"group\">
                        <button class=\"btn btn-primary\" title=\"Ver\" onclick=\"abrirOcorrenciaRel(${o.id})\"><i class=\"fas fa-eye\"></i></button>
                        <button class=\"btn btn-secondary\" title=\"Imprimir\" onclick=\"imprimirOcorrencia(${o.id})\"><i class=\"fas fa-print\"></i></button>
                    </div>`;

                const tr = `
                    <tr>
                        <td>${o.id}</td>
                        <td>${dataHora}</td>
                        <td>${veiculo}</td>
                        <td>${motorista}</td>
                        <td>${descricao}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">${fotosTexto}</td>
                        <td class="text-center">${acoes}</td>
                    </tr>`;
                tbody.append(tr);
            });
        }

        $('#estadoInicial').hide();
        $('#cardsEstatisticas, #cardResultados').show();
    }).fail(function(xhr){
        alert('Erro ao carregar dados: ' + (xhr.responseJSON?.message || 'servidor'));
    });
}

function formatarDataHora(iso){
    if(!iso) return '—';
    const d = new Date(iso);
    if(isNaN(d)) return '—';
    return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});
}

function escapeHtml(t){
    const div = document.createElement('div');
    div.textContent = t || '';
    return div.innerHTML;
}

// Ações
function abrirOcorrenciaRel(id){
    // Reaproveita endpoints do módulo Ocorrências (gestor) para exibir detalhes
    fetch(`/frota/ocorrencias/api/${id}`)
        .then(r => r.json())
        .then(o => {
            const statusAtual = (o.status || 'novo').toLowerCase();
            const html = `
                <div style="text-align:left">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Motorista:</strong><br>${o.motorista || '-'}</div>
                        <div class="col-md-6"><strong>Data/Hora:</strong><br>${o.data || ''} ${o.hora || ''}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge badge-${statusAtual==='resolvido'?'success':(statusAtual==='em_andamento'?'info':'danger')}">`
                        + (statusAtual==='resolvido'?'Resolvido':(statusAtual==='em_andamento'?'Em Andamento':'Novo')) + `</span>
                    </div>
                    <div id="area-fotos-rel" class="mb-3" style="display:none;">
                        <strong>Imagens:</strong>
                        <div id="grid-fotos-rel" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:6px;"></div>
                    </div>
                    <div class="mb-3"><strong>Descrição:</strong><br>${escapeHtml(o.descricao||'')}</div>
                    ${o.sugestao ? `<div class="mb-3"><strong>Sugestão:</strong><br>${escapeHtml(o.sugestao)}</div>` : ''}
                    <small class="text-muted">Registrado em: ${o.created_at || ''}</small>
                </div>`;

            Swal.fire({ title: 'Ocorrência', html, width: 640, showConfirmButton: true, confirmButtonText: 'Fechar' });

            // carrega thumbs
            fetch(`/frota/ocorrencias/api/${id}/fotos`).then(r=>r.json()).then(j=>{
                const lista = (j && j.success) ? (j.data || []) : [];
                if (lista.length){
                    const area = document.getElementById('area-fotos-rel');
                    const grid = document.getElementById('grid-fotos-rel');
                    area.style.display = '';
                    grid.innerHTML = '';
                    lista.forEach(f => {
                        const a = document.createElement('a'); a.href = f.url; a.target = '_blank'; a.rel='noopener';
                        a.style.display='inline-block'; a.style.width='84px'; a.style.height='84px'; a.style.border='1px solid #e0e0e0'; a.style.borderRadius='6px'; a.style.overflow='hidden';
                        const img = document.createElement('img'); img.src=f.url; img.alt=`Foto ${f.idx}`; img.style.width='100%'; img.style.height='100%'; img.style.objectFit='cover';
                        a.appendChild(img); grid.appendChild(a);
                    });
                }
            }).catch(()=>{});
        })
        .catch(()=> Swal.fire('Erro', 'Não foi possível abrir a ocorrência.', 'error'));
}

function imprimirOcorrencia(id){
    // Abre a página de impressão em iframe oculto e dispara impressão automaticamente
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = `/frota/ocorrencias/${id}/print?autoprint=1`;
    document.body.appendChild(iframe);
    
    // Aguarda carregar e remove iframe após impressão
    iframe.onload = function() {
        setTimeout(() => {
            try {
                iframe.contentWindow.print();
                // Remove iframe após fechar diálogo de impressão
                setTimeout(() => document.body.removeChild(iframe), 1000);
            } catch(e) {
                document.body.removeChild(iframe);
            }
        }, 500);
    };
}
</script>
@stop

