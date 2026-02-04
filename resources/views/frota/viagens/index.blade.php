@extends('adminlte::page')

@section('title', 'Frota - Viagens')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-route text-primary mr-3"></i>
            Viagens
        </h1>
        <p class="text-muted mt-1 mb-0">Controle de uso dos veículos</p>
    </div>
    <div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalViagem" id="btnNovaViagem">
            <i class="fas fa-plus mr-1"></i>
            Nova Viagem
        </button>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@php($__isAdmin = isset($__isAdmin) ? $__isAdmin : ((optional(auth()->user()->profile)->name === 'Admin') || (auth()->user() && method_exists(auth()->user(),'temPermissao') && auth()->user()->temPermissao('Gestão de Frotas'))))
@php($__authId = auth()->id())
@php($__authName = auth()->user()->name)
<div class="container-fluid">
    <!-- Filtro por Mês -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="filtroMesViagem" class="font-weight-bold mb-1">Filtrar por mês</label>
            <div class="d-flex">
                <input type="month" id="filtroMesViagem" class="form-control mr-2">
                <button type="button" id="btnLimparMesViagem" class="btn btn-outline-secondary">Limpar</button>
            </div>
        </div>
    </div>
    @if($__isAdmin)
    <!-- Cards de estatísticas (somente Admin) -->
    <div class="row mb-4" id="cardsAdmin">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-primary">
                <div class="card-body text-center">
                    <i class="fas fa-route fa-2x mb-2"></i>
                    <h3 id="via_total_mes">0</h3>
                    <p class="mb-0">Viagens do Mês</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-success">
                <div class="card-body text-center">
                    <i class="fas fa-road fa-2x mb-2"></i>
                    <h3 id="via_total_km">0 km</h3>
                    <p class="mb-0">Total Percorrido</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-warning">
                <div class="card-body text-center">
                    <i class="fas fa-car fa-2x mb-2"></i>
                    <h3 id="via_andamento">0</h3>
                    <p class="mb-0">Em Andamento</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-info">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3 id="via_horas">0h</h3>
                    <p class="mb-0">Horas Totais</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Viagens -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>
                Histórico de Viagens
            </h5>
        </div>
        <div class="card-body">
            <!-- Desktop/Tablet: tabela completa -->
            <div class="d-none d-md-block">
                <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling: touch;">
                    <table class="table table-striped mb-0" id="tabelaViagens" style="min-width: 720px;">
                        <thead>
                            <tr>
                                <th>Saída</th>
                                <th>Retorno</th>
                                <th>Veículo</th>
                                <th>Motorista</th>
                                <th>Tempo</th>
                                <th>KM Percorrido</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile: lista simplificada com botão de ação em destaque -->
            <div class="d-block d-md-none">
                <div id="listaViagensMobile" class="list-group"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Viagem -->
<div class="modal fade" id="modalViagem" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-route mr-2"></i>
                    Nova Viagem
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formViagem">
                <div class="modal-body">
                    <!-- Informações Básicas -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Veículo <span class="text-danger">*</span></label>
                                <select id="vehicle_id" class="form-control" required>
                                    <option value="">Selecione o veículo...</option>
                                    <option value="1">ABC-1234 - Volkswagen Gol</option>
                                    <option value="2">DEF-5678 - Honda Civic</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Motorista</label>
                                <select id="user_id" class="form-control" disabled>
                                    <option value="{{ $__authId }}" selected>{{ $__authName }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Saída -->
                    <div class="card mt-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-play mr-2"></i>
                                Saída
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Data Saída <span class="text-danger">*</span></label>
                                        <input id="data_saida" type="date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Hora Saída <span class="text-danger">*</span></label>
                                        <input id="hora_saida" type="time" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>KM Saída <span class="text-danger">*</span></label>
                                        <input id="km_saida" type="text" inputmode="numeric" class="form-control" placeholder="Ex: 25.000" required>
                                        <small id="kmAtualHintVia" class="text-muted d-block"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Retorno -->
                    <div class="card mt-3 d-none" id="retornoCard">
                        <div class="card-header bg-warning text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-stop mr-2"></i>
                                Retorno
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Data Retorno</label>
                                        <input id="data_retorno" type="date" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Hora Retorno</label>
                                        <input id="hora_retorno" type="time" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>KM Retorno</label>
                                        <input id="km_retorno" type="text" inputmode="numeric" class="form-control" placeholder="Ex: 25.150">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Observações</label>
                                <textarea id="observacoes" class="form-control" rows="3" placeholder="Observações sobre a viagem..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="viagem_id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Salvar
                    </button>
                </div>
            </form>
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
    background-color: #f8f9fa;
    border-top: none;
}

.btn-sm {
    margin: 0 2px;
}
</style>
@stop

@section('js')
<script>
const IS_ADMIN = @json($__isAdmin);
const AUTH_USER_ID = @json($__authId);
const AUTH_USER_NAME = @json($__authName);
let vehicleMap = {};

$(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    // Carregar veículos e, após pronto, listar viagens para garantir mapa preenchido
    carregarVeiculos().always(function(){
        carregarLista();
    });
    carregarProximasManutencoes();
    if (IS_ADMIN) carregarMotoristas();
    carregarUsuariosMapa(); // Carregar mapa de usuários

    // Filtro por mês: padrão mês atual; ao alterar, recarrega
    (function(){
        const d = new Date(); const p=n=>String(n).padStart(2,'0');
        $('#filtroMesViagem').val(`${d.getFullYear()}-${p(d.getMonth()+1)}`);
    })();
    $('#filtroMesViagem').on('change', function(){ carregarLista(); });
    $('#btnLimparMesViagem').on('click', function(){ $('#filtroMesViagem').val(''); carregarLista(); });

    // Novo registro: mostrar só Saída e preencher motorista com usuário logado
    $('#btnNovaViagem').on('click', function(){
        $('#viagem_id').val('');
        $('#retornoCard').addClass('d-none');
        $('#user_id').empty().append(`<option value="${AUTH_USER_ID}" selected>${AUTH_USER_NAME}</option>`).prop('disabled', true);
        $('#formViagem')[0].reset();
        // Recarregar veículos para atualizar KM atual e disponibilidade
        carregarVeiculos();
    });

    // Submeter
    $('#formViagem').on('submit', function(e){
        e.preventDefault();
        salvarViagem();
    });
    // máscaras de milhares para KM de saída/retorno
    $('#km_saida').on('input', function(){ this.value = formatKmBrFromString(this.value); });
    $('#km_retorno').on('input', function(){ this.value = formatKmBrFromString(this.value); });
});

// Mapa: vehicle_id -> proxima_km (maior registrada)
let mapaProximaKm = {};
let userMap = {};

function carregarUsuariosMapa(){
    return $.get('/api/usuarios').done(function(users){
        userMap = {};
        (users||[]).forEach(u => { userMap[Number(u.id)] = u.name; });
    });
}

function carregarProximasManutencoes(){
    $.get('/frota/api/manutencoes').done(function(rows){
        mapaProximaKm = {};
        rows.forEach(r => {
            if (r.vehicle_id && r.proxima_km) {
                const atual = mapaProximaKm[r.vehicle_id] || 0;
                if (Number(r.proxima_km) > atual) mapaProximaKm[r.vehicle_id] = Number(r.proxima_km);
            }
        });
        atualizarHintsViagem();
    });
}

function carregarVeiculos(){
    // Buscar TODOS os veículos para montar o mapa (inclusive manutenção/inativos),
    // mas popular o select apenas com os utilizáveis
    return $.get('/frota/api/veiculos', { ts: Date.now() }).done(function(veiculos){
        const sel = $('#vehicle_id');
        sel.find('option:not(:first)').remove();
        // Buscar viagens em andamento para desabilitar veículos
        $.get('/frota/api/viagens', { only_open: 1, ts: Date.now() }).done(function(abertas){
            const emUso = new Set((abertas||[]).map(v => Number(v.vehicle_id)));
            // Sempre preencher o mapa com TODOS os veículos para exibição na lista
            vehicleMap = {};
            veiculos.forEach(v => {
                const label = `${v.placa} - ${v.marca||''} ${v.modelo||''}`.trim();
                vehicleMap[Number(v.id)] = label;
            });
            // Preencher o select apenas com os disponíveis (fora de uso e sem manutenção)
            veiculos.forEach(v => {
                if (emUso.has(Number(v.id))) {
                    // Não listar veículos em uso (indisponíveis para todos)
                    return;
                }
                const statusLower = String(v.status||'').toLowerCase();
                if (statusLower === 'manutencao' || statusLower === 'inativo') {
                    // Não listar veículos em manutenção
                    return;
                }
                const label = vehicleMap[Number(v.id)];
                sel.append(`<option value="${v.id}" data-km="${v.km_atual||0}">${label}</option>`);
            });
            sel.off('change').on('change', function(){
                atualizarHintsViagem();
            }).trigger('change');
            $('#km_saida').off('input').on('input', function(){
                this.value = formatKmBrFromString(this.value);
                verificarAvisoManutencao();
            });
        });
    });
}

function atualizarHintsViagem(){
    const sel = $('#vehicle_id');
    const selectedId = Number(sel.val()||0);
    if (!selectedId){
        $('#kmAtualHintVia').text('');
        return;
    }
    // Buscar km_atual mais recente do servidor para o veículo selecionado
    $.get('/frota/api/veiculos', { ts: Date.now(), only_usable: 1 }).done(function(veiculos){
        const v = (veiculos||[]).find(x => Number(x.id) === selectedId);
        if (v){
            sel.find('option:selected').data('km', v.km_atual||0);
        }
        const kmAtual = Number(sel.find('option:selected').data('km')||0);
        const proxKm = mapaProximaKm[selectedId];
        const partes = [];
        if (kmAtual) partes.push(`KM atual: ${kmAtual.toLocaleString()} km`);
        if (proxKm) partes.push(`Próxima manutenção: ${proxKm.toLocaleString()} km`);
        $('#kmAtualHintVia').text(partes.join(' · '));

        // Para novas viagens, preencher automaticamente o KM Saída com o KM atual do veículo
        const isNovaViagem = !$('#viagem_id').val();
        const kmSaidaCampo = $('#km_saida');
        if (isNovaViagem && !String(kmSaidaCampo.val()).trim()) {
            kmSaidaCampo.val(formatKmBrFromString(kmAtual));
        }
        verificarAvisoManutencao();
    });
}

function verificarAvisoManutencao(){
    const vid = Number($('#vehicle_id').val()||0);
    if (!vid) return;
    const kmAtual = Number($('#vehicle_id').find('option:selected').data('km')||0);
    const kmSaida = Number(String($('#km_saida').val()||'').replace(/\D/g, ''));
    const proxKm = mapaProximaKm[vid];
    if (!proxKm) return;

    if (kmAtual >= proxKm) {
        Swal.fire({ icon: 'warning', title: 'Manutenção vencida', text: `Veículo já ultrapassou a próxima manutenção (${proxKm.toLocaleString()} km).`, confirmButtonText: 'OK'});
        return;
    }
    if (kmSaida && kmSaida >= proxKm) {
        Swal.fire({ icon: 'info', title: 'Atenção', text: `KM de saída informado alcança a próxima manutenção (${proxKm.toLocaleString()} km).`, confirmButtonText: 'OK'});
    }
}

function carregarMotoristas(){
    $.get('/api/usuarios').done(function(users){
        const sel = $('#user_id');
        sel.find('option').remove();
        users.forEach(u => sel.append(`<option value="${u.id}">${u.name}</option>`));
        sel.val(AUTH_USER_ID).prop('disabled', true);
    });
}

function carregarLista(){
    const paramsBase = IS_ADMIN ? {} : { user_id: AUTH_USER_ID };
    // Filtro por mês (independente do perfil), quando selecionado
    let params = Object.assign({}, paramsBase);
    const mesSel = $('#filtroMesViagem').val();
    if (mesSel) {
        const [ano, mes] = mesSel.split('-');
        const ultimoDia = new Date(Number(ano), Number(mes), 0).getDate();
        const pad = n => String(n).padStart(2,'0');
        params.data_inicio = `${ano}-${mes}-01`;
        params.data_fim = `${ano}-${mes}-${pad(ultimoDia)}`;
    } else if (!IS_ADMIN) {
        // Sem mês selecionado: para usuários comuns, manter mês atual como padrão
        const hoje = new Date();
        const pad = n => String(n).padStart(2,'0');
        params.data_inicio = `${hoje.getFullYear()}-${pad(hoje.getMonth()+1)}-01`;
        params.data_fim = `${hoje.getFullYear()}-${pad(hoje.getMonth()+1)}-${pad(new Date(hoje.getFullYear(), hoje.getMonth()+1, 0).getDate())}`;
    }

    const listar = function(){
        // Trazer fechadas + em andamento (evitar esconder viagens abertas)
        $.when(
            $.get('/frota/api/viagens', params),
            $.get('/frota/api/viagens', Object.assign({}, params, { only_open: 1 }))
        ).done(function(resFechadas, resAbertas){
            let rows = [];
            try { rows = rows.concat(resFechadas && resFechadas[0] ? resFechadas[0] : []); } catch(e) {}
            try { rows = rows.concat(resAbertas && resAbertas[0] ? resAbertas[0] : []); } catch(e) {}
            const byId = new Map();
            rows.forEach(r => { byId.set(r.id, r); });
            rows = Array.from(byId.values());
            // Filtro de mês client-side (fallback caso a API ignore datas)
            if (mesSel) {
                const [ano, mes] = mesSel.split('-');
                const pad = n=>String(n).padStart(2,'0');
                const ini = `${ano}-${mes}-01`;
                const fim = `${ano}-${mes}-${pad(new Date(Number(ano), Number(mes), 0).getDate())}`;
                rows = rows.filter(r => String(r.data_saida) >= ini && String(r.data_saida) <= fim);
            }
            // Ordenação: agrupar por data_saida (asc) e, dentro de cada dia, ordenar por hora desc (mais recentes em cima)
            try {
                // Manter o dia atual no topo, demais dias em ordem decrescente (mais recentes acima)
                const hojeStr = (function(){
                    const d=new Date(); const p=n=>String(n).padStart(2,'0');
                    return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}`;
                })();
                rows.sort(function(a,b){
                    if (a.data_saida === b.data_saida) {
                        // mesmo dia: ordenar hora desc (mais recentes primeiro)
                        const ha = (a.hora_saida||'00:00');
                        const hb = (b.hora_saida||'00:00');
                        if (ha > hb) return -1; if (ha < hb) return 1; return 0;
                    }
                    // prioriza o dia atual
                    if (a.data_saida === hojeStr) return -1;
                    if (b.data_saida === hojeStr) return 1;
                    // demais dias: mais recentes primeiro (desc)
                    if (a.data_saida > b.data_saida) return -1;
                    if (a.data_saida < b.data_saida) return 1;
                    return 0;
                });
                // Após ordenar, limitar a no máximo 3 dias distintos (inclui hoje) quando NÃO há filtro de mês
                const dias = new Set();
                const filtrado = [];
                for (const r of rows){
                    dias.add(r.data_saida);
                    filtrado.push(r);
                    if (!mesSel && dias.size >= 3){
                        // ainda pode entrar registros do mesmo dia atual
                        // mas se o próximo for outro dia, paramos
                        // (otimização: verificaremos depois no append pelo separador)
                    }
                }
                // Para garantir só 3 dias: filtrar por dias capturados
                if (!mesSel) {
                    const diasArray = Array.from(dias).slice(0,3);
                    rows = rows.filter(r => diasArray.includes(r.data_saida));
                }
            } catch(e) {}
            const tbody = $('#tabelaViagens tbody');
            tbody.find('tr').remove();
            let totalKm = 0, andamento = 0, horas = 0, doMes = 0;
            const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
            const listaMobile = $('#listaViagensMobile');
            if (isMobile) listaMobile.empty();

            let diaAtual = null; let printedDays = 0;
            rows.forEach(r => {
                doMes++;
                const saida = `${new Date(r.data_saida+'T00:00:00').toLocaleDateString('pt-BR')} ${r.hora_saida || ''}`;
                const retorno = r.data_retorno ? `${new Date(r.data_retorno+'T00:00:00').toLocaleDateString('pt-BR')} ${r.hora_retorno || ''}` : '-';
                totalKm += Number(r.km_percorrido||0);
                if (!r.km_retorno) andamento++;
                if (r.data_saida && r.hora_saida && r.data_retorno && r.hora_retorno) {
                    const ini = new Date(`${r.data_saida}T${r.hora_saida}`);
                    const fimd = new Date(`${r.data_retorno}T${r.hora_retorno}`);
                    if (fimd > ini) horas += (fimd - ini)/(1000*60*60);
                }
                const startISO = `${r.data_saida}T${r.hora_saida||'00:00'}`;
                const endISO = (r.data_retorno && r.hora_retorno) ? `${r.data_retorno}T${r.hora_retorno}` : '';
                const tempoCell = `<span class="tempo-span" data-start="${startISO}" data-end="${endISO}"></span>`;
                const acoes = (!r.km_retorno)
                    ? `<button class="btn btn-sm btn-primary" title="Registrar retorno" onclick="registrarRetorno(${r.id})"><i class="fas fa-play"></i></button>`
                    : (IS_ADMIN
                        ? `<button class="btn btn-sm btn-info" onclick="editar(${r.id})" title="Editar"><i class="fas fa-edit"></i></button>
                           <button class="btn btn-sm btn-danger" onclick="excluirReg(${r.id})" title="Excluir"><i class="fas fa-trash"></i></button>`
                        : `<button class="btn btn-sm btn-secondary" onclick="ver(${r.id})" title="Ver"><i class="fas fa-eye"></i></button>`
                      );
                // Separador por dia (desktop)
                if (!isMobile) {
                    if (diaAtual !== r.data_saida) {
                        diaAtual = r.data_saida; printedDays++;
                        const labelDia = new Date(r.data_saida+'T00:00:00').toLocaleDateString('pt-BR');
                        tbody.append(`<tr class="table-active"><td colspan="7"><strong>${labelDia}</strong></td></tr>`);
                        if (printedDays > 3) return; // segurança extra (caso algo fuja do filtro)
                    }
                    tbody.append(`
                        <tr>
                            <td>${saida}</td>
                            <td>${retorno}</td>
                            <td><strong>${vehicleMap[Number(r.vehicle_id)] || r.vehicle_id}</strong></td>
                            <td>${userMap[Number(r.user_id)] || r.user_id}</td>
                            <td>${tempoCell}</td>
                            <td>${r.km_percorrido ? r.km_percorrido + ' km' : '-'}</td>
                            <td class="text-center">${acoes}</td>
                        </tr>
                    `);
                } else {
                const placa = vehicleMap[Number(r.vehicle_id)] || `Veículo #${r.vehicle_id}`;
                    const acaoMobile = (!r.km_retorno)
                        ? `<button class="btn btn-primary btn-block" onclick="registrarRetorno(${r.id})"><i class=\"fas fa-play mr-1\"></i> Registrar retorno</button>`
                        : (IS_ADMIN
                            ? `<div class=\"btn-group btn-group-sm w-100\">
                                    <button class=\"btn btn-info\" onclick=\"editar(${r.id})\"><i class=\"fas fa-edit\"></i></button>
                                    <button class=\"btn btn-danger\" onclick=\"excluirReg(${r.id})\"><i class=\"fas fa-trash\"></i></button>
                               </div>`
                            : `<button class=\"btn btn-secondary btn-block\" onclick=\"ver(${r.id})\"><i class=\"fas fa-eye mr-1\"></i> Ver</button>`);

                    listaMobile.append(`
                        <div class=\"list-group-item\">
                            <div class=\"d-flex justify-content-between align-items-center\">
                                <div>
                                    <div class=\"font-weight-bold\">${placa}</div>
                                    <small class=\"text-muted\">Saída: ${saida}</small>
                                </div>
                                <div class=\"text-right\">
                                    <div>${tempoCell}</div>
                                </div>
                            </div>
                            <div class=\"mt-2\">${acaoMobile}</div>
                        </div>
                    `);
                }
            });
            if (IS_ADMIN) {
                $('#via_total_mes').text(doMes);
                const totalKmFmt = Number(totalKm||0).toLocaleString('pt-BR');
                $('#via_total_km').text(totalKmFmt + ' km');
                $('#via_andamento').text(andamento);
                const horasFmt = Number(horas||0).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
                $('#via_horas').text(horasFmt + 'h');
            }
            atualizarTempos();
            if (window.__tempoInterval) clearInterval(window.__tempoInterval);
            window.__tempoInterval = setInterval(atualizarTempos, 1000);
        });
    };

    if (!Object.keys(userMap).length){
        carregarUsuariosMapa().always(listar);
    } else {
        listar();
    }
}

function atualizarTempos(){
    $('.tempo-span').each(function(){
        const start = new Date($(this).data('start'));
        const endAttr = $(this).data('end');
        const end = endAttr ? new Date(endAttr) : new Date();
        const ms = Math.max(0, end - start);
        $(this).text(formatDuration(ms));
    });
}

function formatDuration(ms){
    const totalSec = Math.floor(ms / 1000);
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    const mm = String(m).padStart(2, '0');
    const ss = String(s).padStart(2, '0');
    return `${h}h ${mm}m ${ss}s`;
}

function setSaidaEditable(canEdit){
    $('#vehicle_id, #data_saida, #hora_saida, #km_saida').prop('disabled', !canEdit);
}

function registrarRetorno(id){
    $.get('/frota/api/viagens').done(function(rows){
        const r = rows.find(x => x.id == id);
        if(!r) return;
        $('#viagem_id').val(r.id);
        // Garantir que o veículo da saída esteja selecionado mesmo que esteja "em uso"
        $.get('/frota/api/veiculos').done(function(veiculos){
            const v = (veiculos||[]).find(x => Number(x.id) === Number(r.vehicle_id));
            const label = v ? `${v.placa} - ${v.marca||''} ${v.modelo||''}` : `Veículo #${r.vehicle_id}`;
            const kmAtual = v ? (v.km_atual||0) : 0;
            const sel = $('#vehicle_id');
            sel.find('option').remove();
            sel.append(`<option value="${r.vehicle_id}" data-km="${kmAtual}" selected>${label}</option>`);
            sel.val(r.vehicle_id).prop('disabled', true);
            atualizarHintsViagem();
        });
        const motoristaNome = userMap[Number(r.user_id)] || r.user_id;
        $('#user_id').empty().append(`<option value="${r.user_id}" selected>${motoristaNome}</option>`).prop('disabled', true);
        $('#data_saida').val(r.data_saida);
        $('#hora_saida').val(r.hora_saida);
        $('#km_saida').val(formatKmBrFromString(r.km_saida));
        $('#retornoCard').removeClass('d-none');
        setSaidaEditable(IS_ADMIN); // bloqueia saída para não admin
        $('#modalViagem').modal('show');
    });
}

function ver(id){
    $.get('/frota/api/viagens').done(function(rows){
        const r = rows.find(x => x.id == id);
        if(!r) return;
        $('#viagem_id').val(r.id);
        $('#vehicle_id').val(r.vehicle_id).prop('disabled', true);
        const motoristaNomeVer = userMap[Number(r.user_id)] || r.user_id;
        $('#user_id').empty().append(`<option value="${r.user_id}" selected>${motoristaNomeVer}</option>`).prop('disabled', true);
        $('#data_saida').val(r.data_saida).prop('disabled', true);
        $('#hora_saida').val(r.hora_saida).prop('disabled', true);
        $('#km_saida').val(formatKmBrFromString(r.km_saida)).prop('disabled', true);
        $('#data_retorno').val(r.data_retorno).prop('disabled', true);
        $('#hora_retorno').val(r.hora_retorno).prop('disabled', true);
        $('#km_retorno').val(formatKmBrFromString(r.km_retorno)).prop('disabled', true);
        $('#observacoes').val(r.observacoes).prop('readonly', true);
        $('#retornoCard').removeClass('d-none');
        $('#modalViagem').modal('show');
    });
}

function salvarViagem(){
    const id = $('#viagem_id').val();
    const dados = {
        vehicle_id: $('#vehicle_id').val(),
        user_id: AUTH_USER_ID,
        data_saida: $('#data_saida').val(),
        hora_saida: $('#hora_saida').val(),
        km_saida: $('#km_saida').val().replace(/\D/g, ''),
        data_retorno: $('#data_retorno').val(),
        hora_retorno: $('#hora_retorno').val(),
        km_retorno: $('#km_retorno').val().replace(/\D/g, ''),
        observacoes: $('#observacoes').val(),
    };
    const url = id ? `/frota/api/viagens/${id}` : '/frota/api/viagens';
    const method = id ? 'PUT' : 'POST';
    $.ajax({ url, method, data: dados })
        .done(() => { 
            $('#modalViagem').modal('hide'); 
            carregarLista();
            carregarVeiculos();
            Swal.fire({ icon: 'success', title: 'Salvo!', timer: 1400, showConfirmButton: false });
        })
        .fail(xhr => { 
            const msg = xhr.responseJSON?.message || 'Erro ao salvar';
            Swal.fire('Erro', msg, 'error'); 
        });
}

// Utilitário: formata milhares pt-BR durante digitação (quilometragem)
function formatKmBrFromString(value){
    const digitsOnly = String(value || '').replace(/\D/g, '');
    if (!digitsOnly) return '';
    return Number(digitsOnly).toLocaleString('pt-BR');
}

function editar(id){
    $.get('/frota/api/viagens').done(function(rows){
        const r = rows.find(x => x.id == id);
        if(!r) return;
        $('#viagem_id').val(r.id);
        $('#vehicle_id').val(r.vehicle_id);
        const motoristaNomeEd = userMap[Number(r.user_id)] || r.user_id;
        $('#user_id').empty().append(`<option value="${r.user_id}" selected>${motoristaNomeEd}</option>`).prop('disabled', true);
        $('#data_saida').val(r.data_saida);
        $('#hora_saida').val(r.hora_saida);
        $('#km_saida').val(r.km_saida);
        $('#data_retorno').val(r.data_retorno);
        $('#hora_retorno').val(r.hora_retorno);
        $('#km_retorno').val(r.km_retorno);
        $('#observacoes').val(r.observacoes);
        $('#retornoCard').removeClass('d-none');
        $('#modalViagem').modal('show');
    });
}

function excluirReg(id){
    Swal.fire({
        title: 'Confirmar exclusão?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Excluir',
        cancelButtonText: 'Cancelar'
    }).then(res => {
        if(!res.isConfirmed) return;
        $.ajax({ url: `/frota/api/viagens/${id}`, method: 'DELETE' })
            .done(() => { carregarLista(); Swal.fire({ icon:'success', title:'Excluído!', timer:1200, showConfirmButton:false }); })
            .fail(() => Swal.fire('Erro', 'Erro ao excluir', 'error'));
    });
}
</script>
@stop
