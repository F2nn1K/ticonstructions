@extends('adminlte::page')

@section('title', 'Frota - Manutenções')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-tools text-primary mr-3"></i>
            Manutenções
        </h1>
        <p class="text-muted mt-1 mb-0">Gerencie as manutenções da frota</p>
    </div>
    <div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalManutencao">
            <i class="fas fa-plus mr-1"></i>
            Nova Manutenção
        </button>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    @php($__isAdmin = optional(auth()->user()->profile)->name === 'Admin')
    <!-- Filtro por Mês -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="filtroMes" class="font-weight-bold mb-1">Filtrar por mês</label>
            <div class="d-flex">
                <input type="month" id="filtroMes" class="form-control mr-2">
                <button type="button" id="btnLimparMes" class="btn btn-outline-secondary">Limpar</button>
            </div>
        </div>
    </div>
    <!-- Cards de estatísticas -->
    @if($__isAdmin)
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-primary">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-2x mb-2"></i>
                    <h3 id="man_total_mes">0</h3>
                    <p class="mb-0">Total do Mês</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-success">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h3 id="man_custo_total">R$ 0,00</h3>
                    <p class="mb-0">Custo Total</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-info">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-2x mb-2"></i>
                    <h3 id="man_custo_medio">R$ 0,00</h3>
                    <p class="mb-0">Custo Médio</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-danger">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3 id="man_vencidas">0</h3>
                    <p class="mb-0">Vencidas</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Manutenções -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>
                Histórico de Manutenções
            </h5>
        </div>
        <div class="card-body">
            <!-- Desktop/Tablet -->
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-striped" id="tabelaManutencoes">
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
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <!-- Mobile: lista simplificada -->
            <div class="d-block d-md-none">
                <div id="listaManutencoesMobile" class="list-group"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Manutenção -->
<div class="modal fade" id="modalManutencao" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-tools mr-2"></i>
                    Nova Manutenção
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formManutencao">
                <div class="modal-body">
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
                                <label>Data <span class="text-danger">*</span></label>
                                <input id="data" type="date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select id="tipo" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="preventiva">Preventiva</option>
                                    <option value="corretiva">Corretiva</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Quilometragem <span class="text-danger">*</span></label>
                                <input id="km" type="text" inputmode="numeric" class="form-control" placeholder="Ex: 25.100" required>
                                <small id="kmAtualHintMan" class="text-muted d-block"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Custo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input id="custo" type="text" inputmode="decimal" class="form-control" placeholder="Ex: 350,00">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Descrição <span class="text-danger">*</span></label>
                                <textarea id="descricao" class="form-control" rows="3" placeholder="Descreva o serviço realizado" maxlength="200" required></textarea>
                                <small class="text-muted d-block text-right"><span id="descricaoCount">0</span>/200</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Oficina/Prestador</label>
                                <input id="oficina" type="text" class="form-control" placeholder="Ex: Oficina João - Centro">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Próxima Manutenção (KM)</label>
                                <input id="proxima_km" type="text" inputmode="numeric" class="form-control" placeholder="Ex: 35.000">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="manutencao_id">
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
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}

.btn-sm {
    margin: 0 2px;
}

/* Larguras específicas para colunas que estavam quebrando */
.table th:nth-child(1), .table td:nth-child(1) { width: 100px; } /* Data */
.table th:nth-child(2), .table td:nth-child(2) { width: 140px; } /* Veículo */
.table th:nth-child(3), .table td:nth-child(3) { width: 120px; } /* Motorista */
.table th:nth-child(4), .table td:nth-child(4) { width: 110px; } /* Tipo */
.table th:nth-child(5), .table td:nth-child(5) { min-width: 200px; } /* Descrição */
.table th:nth-child(6), .table td:nth-child(6) { width: 110px; text-align: right; white-space: nowrap; } /* KM */
.table th:nth-child(7), .table td:nth-child(7) { width: 120px; text-align: right; white-space: nowrap; } /* Custo */
.table th:nth-child(8), .table td:nth-child(8) { width: 130px; white-space: nowrap; } /* Próxima */
.table th:nth-child(9), .table td:nth-child(9) { width: 120px; text-align: center; white-space: nowrap; } /* Ações */
</style>
@stop

@section('js')
<script>
// Disponibiliza no escopo global para uso nas funções abaixo
var IS_ADMIN = @json(optional(auth()->user()->profile)->name === 'Admin');
var VEHICLE_MAP = {};

$(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    // Carregar veículos primeiro para preencher o mapa; depois listar
    carregarVeiculosNoSelect().always(function(){
        carregarLista();
    });

        $('#formManutencao').on('submit', function(e){
        e.preventDefault();
        salvarManutencao();
    });

    // máscara de moeda BR no input #custo
    $('#custo').on('input', function(){
        $(this).val(formatarMoedaDigitacao($(this).val()));
    });

    // máscaras de milhares para KM
    $('#km').on('input', function(){
        this.value = formatKmBrFromString(this.value);
    });
    $('#proxima_km').on('input', function(){
        this.value = formatKmBrFromString(this.value);
    });

    // contador de caracteres para descrição (máx 200)
    $('#descricao').on('input', function(){
        var len = $(this).val().length;
        if (len > 200) {
            $(this).val($(this).val().substring(0, 200));
            len = 200;
        }
        $('#descricaoCount').text(len);
    }).trigger('input');

    // Ao fechar modal, voltar para modo editável
    $('#modalManutencao').on('hidden.bs.modal', function(){
        setModalReadOnly(false);
        $('#formManutencao')[0].reset();
        $('#manutencao_id').val('');
    });

    // Filtro de mês (default: mês atual)
    const hoje = new Date();
    const pad = (n)=> String(n).padStart(2,'0');
    $('#filtroMes').val(`${hoje.getFullYear()}-${pad(hoje.getMonth()+1)}`);
    $('#filtroMes').on('change', function(){ carregarLista(); });
    $('#btnLimparMes').on('click', function(){ $('#filtroMes').val(''); carregarLista(); });
});

// Converte string BR (1.500,25) para número (1500.25)
function normalizarMoedaBR(valor){
    if (valor == null) return 0;
    const limpo = String(valor)
        .replace(/[^0-9]/g, '');
    const num = parseFloat(limpo);
    return isNaN(num) ? 0 : num;
}

// Formata número para padrão brasileiro (1.500,25) sem símbolo
function formatarNumeroBR(valor){
    const numero = typeof valor === 'number' ? valor : normalizarMoedaBR(valor);
    return Number(numero).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Formatação amigável durante digitação: trata a entrada como centavos
// Ex.: digitar "2500" => 25,00 -> exibe "2.500,00" com milhares
function formatarMoedaDigitacao(valor){
    // mantém apenas dígitos
    const digits = String(valor).replace(/\D/g, '');
    const inteiro = digits.length > 2 ? digits.slice(0, -2) : '0';
    const centavos = digits.padStart(3, '0').slice(-2);
    const inteiroFormatado = Number(inteiro).toLocaleString('pt-BR');
    return `${inteiroFormatado},${centavos}`;
}

// Utilitário: formata milhares pt-BR durante digitação (quilometragem)
function formatKmBrFromString(value){
    const digitsOnly = String(value || '').replace(/\D/g, '');
    if (!digitsOnly) return '';
    return Number(digitsOnly).toLocaleString('pt-BR');
}

function carregarVeiculosNoSelect(){
    return $.get('/frota/api/veiculos', { only_usable: 1 }).done(function(veiculos){
        const sel = $('#vehicle_id');
        sel.find('option:not(:first)').remove();
        VEHICLE_MAP = {};
        veiculos.forEach(v => {
            const label = `${v.placa} - ${v.marca||''} ${v.modelo||''}`;
            VEHICLE_MAP[Number(v.id)] = label;
            sel.append(`<option value="${v.id}" data-km="${v.km_atual||0}">${label}</option>`);
        });
        sel.on('change', function(){
            const km = $(this).find('option:selected').data('km');
            $('#kmAtualHintMan').text(km ? `KM atual: ${Number(km).toLocaleString()} km` : '');
        }).trigger('change');
    });
}

// Mapa completo para exibir corretamente na tabela (inclui inativos)
var VEHICLE_MAP_ALL = {};
function carregarVeiculosParaDisplay(){
    return $.get('/frota/api/veiculos', { ts: Date.now() }).done(function(veiculos){
        VEHICLE_MAP_ALL = {};
        veiculos.forEach(v => {
            const label = `${v.placa} - ${v.marca||''} ${v.modelo||''}`;
            VEHICLE_MAP_ALL[Number(v.id)] = label;
        });
        atualizarRotulosVeiculos();
    });
}

// Atualiza rótulos de veículo já renderizados quando o mapa chega
function atualizarRotulosVeiculos(){
    $('.celula-veiculo').each(function(){
        const id = Number($(this).data('vehicle-id'));
        const label = VEHICLE_MAP_ALL[id] || VEHICLE_MAP[id] || id;
        if ($(this).is('td')) {
            const strong = $(this).find('strong');
            if (strong.length) {
                strong.text(label);
            } else {
                $(this).text(label);
            }
        } else {
            $(this).text(label);
        }
    });
}

function carregarLista(){
    // Carrega o mapa completo em paralelo (não bloqueia a listagem)
    carregarVeiculosParaDisplay();
    // Para não-admin, não precisamos enviar user_id: o backend já filtra automaticamente.
    // Para admin, poderemos no futuro aplicar filtros; por ora, busca tudo.
    const params = {};
    // Aplicar filtro de mês se selecionado
    const mesSel = $('#filtroMes').val();
    if (mesSel) {
        const [ano, mes] = mesSel.split('-');
        params.data_ini = `${ano}-${mes}-01`;
        const ultimoDia = new Date(Number(ano), Number(mes), 0).getDate();
        params.data_fim = `${ano}-${mes}-${String(ultimoDia).padStart(2,'0')}`;
    }
    $.get('/frota/api/manutencoes', params)
    .done(function(resp){
        const rows = Array.isArray(resp) ? resp : (Array.isArray(resp?.data) ? resp.data : []);
        const tbody = $('#tabelaManutencoes tbody');
        const listaMobile = $('#listaManutencoesMobile');
        const isMobile = window.matchMedia('(max-width: 767.98px)').matches;

        tbody.find('tr').remove();
        listaMobile.empty();

        let total = 0; let qtd = 0; let venc = 0; let doMes = 0;
        const hoje = new Date();

		// A mensagem de vazio será exibida após aplicar o filtro e o agrupamento por dia

        // Aplicar filtro client-side se API não filtrar
        let dataset = rows.slice();
        if (mesSel) {
            const [ano, mes] = mesSel.split('-');
            const ultimoDia = new Date(Number(ano), Number(mes), 0).getDate();
            const ini = `${ano}-${mes}-01`;
            const fim = `${ano}-${mes}-${String(ultimoDia).padStart(2,'0')}`;
            dataset = dataset.filter(r => String(r.data) >= ini && String(r.data) <= fim);
        }

		// Agrupar por dia (desc), como na tela de Abastecimentos
		const sorted = dataset.sort((a,b) => new Date(b.data) - new Date(a.data));
		const dias = [];
		const incluidos = [];
		sorted.forEach(r => {
			const d = r.data;
			if (dias.indexOf(d) === -1) dias.push(d);
			incluidos.push(r);
		});

		if (!incluidos.length){
			if (isMobile) {
				listaMobile.append('<div class="list-group-item text-center text-muted">Nenhuma manutenção encontrada</div>');
			} else {
				tbody.append('<tr><td colspan="9" class="text-center text-muted">Nenhuma manutenção encontrada</td></tr>');
			}
		}

        dias.forEach(diaISO => {
			const header = new Date(diaISO + 'T00:00:00').toLocaleDateString('pt-BR');
			const doDia = incluidos.filter(x => x.data === diaISO);

			if (!isMobile) {
				tbody.append(`<tr class="table-active"><td colspan="9" class="font-weight-bold">${header}</td></tr>`);
			} else {
				listaMobile.append(`<div class="list-group-item bg-light font-weight-bold">${header}</div>`);
			}

			doDia.forEach(r => {
				const data = new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR');
				const tipoBadge = r.tipo === 'preventiva'
					? '<span class="badge badge-success">Preventiva</span>'
					: '<span class="badge badge-warning">Corretiva</span>';
				const proxPartes = [];
				if (r.proxima_data) proxPartes.push(new Date(r.proxima_data + 'T00:00:00').toLocaleDateString('pt-BR'));
				if (r.proxima_km) proxPartes.push(Number(r.proxima_km).toLocaleString() + ' km');
				const prox = proxPartes.join('<br>');

                total += Number(r.custo||0);
                qtd++;
				if (r.proxima_data && new Date(r.proxima_data) < hoje) venc++;
				if (mesSel) { doMes++; }
				else if (r.data && new Date(r.data).getMonth() === hoje.getMonth() && new Date(r.data).getFullYear() === hoje.getFullYear()) { doMes++; }

				const acoesDesktop = IS_ADMIN
					? `<button class=\"btn btn-sm btn-info\" onclick=\"editar(${r.id})\"><i class=\"fas fa-edit\"></i></button>
					   <button class=\"btn btn-sm btn-danger\" onclick=\"excluirReg(${r.id})\"><i class=\"fas fa-trash\"></i></button>`
					: `<button class=\"btn btn-sm btn-primary\" onclick=\"ver(${r.id})\"><i class=\"fas fa-eye\"></i></button>`;

				if (!isMobile) {
					const labelVeiculo = VEHICLE_MAP_ALL[Number(r.vehicle_id)] || VEHICLE_MAP[Number(r.vehicle_id)] || r.vehicle_id;
					tbody.append(`
						<tr>
							<td>${data}</td>
							<td class="celula-veiculo" data-vehicle-id="${r.vehicle_id}"><strong>${labelVeiculo}</strong></td>
							<td>${(r.user && r.user.name) ? r.user.name : '-'}</td>
							<td>${tipoBadge}</td>
							<td>${r.descricao||''}</td>
							<td>${Number(r.km).toLocaleString()} km</td>
							<td>R$ ${formatarNumeroBR(Number(r.custo||0))}</td>
							<td>${prox || '-'}</td>
							<td class="text-center">${acoesDesktop}</td>
						</tr>
					`);
				} else {
					const acaoMobile = IS_ADMIN
						? `<div class="btn-group btn-group-sm w-100">
								<button class="btn btn-info" onclick="editar(${r.id})"><i class="fas fa-edit"></i></button>
								<button class="btn btn-danger" onclick="excluirReg(${r.id})"><i class="fas fa-trash"></i></button>
						   </div>`
						: `<button class="btn btn-primary btn-block" onclick="ver(${r.id})"><i class="fas fa-eye mr-1"></i> Ver</button>`;

					listaMobile.append(`
						<div class="list-group-item">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<div class="font-weight-bold celula-veiculo" data-vehicle-id="${r.vehicle_id}">${VEHICLE_MAP_ALL[Number(r.vehicle_id)] || VEHICLE_MAP[Number(r.vehicle_id)] || r.vehicle_id}</div>
									<small class="text-muted">${data} • ${Number(r.km).toLocaleString()} km</small>
								</div>
								<div class="text-right">
									<div>${tipoBadge}</div>
									<small class="text-muted">${(r.user && r.user.name) ? r.user.name + ' • ' : ''}R$ ${formatarNumeroBR(Number(r.custo||0))}</small>
								</div>
							</div>
							${prox ? `<div class="mt-1"><small class="text-muted">Próx.: ${prox}</small></div>` : ''}
							<div class="mt-2">${acaoMobile}</div>
						</div>
					`);
				}
			});
		});

        // Após renderizar, garantir atualização dos rótulos quando mapas estiverem disponíveis
        atualizarRotulosVeiculos();

        $('#man_total_mes').text(doMes);
        $('#man_custo_total').text('R$ ' + formatarNumeroBR(total));
        const custoMedio = qtd > 0 ? (total / qtd) : 0;
        $('#man_custo_medio').text('R$ ' + formatarNumeroBR(custoMedio));
        $('#man_vencidas').text(venc);
    })
    .fail(function(xhr){
        const tbody = $('#tabelaManutencoes tbody');
        const listaMobile = $('#listaManutencoesMobile');
        tbody.find('tr').remove();
        listaMobile.empty();
        const msg = xhr.status + ' ' + (xhr.responseJSON?.message || 'Falha ao carregar manutenções');
        if (window.matchMedia('(max-width: 767.98px)').matches) {
            listaMobile.append(`<div class="list-group-item text-center text-danger">${msg}</div>`);
        } else {
            tbody.append(`<tr><td colspan="9" class="text-center text-danger">${msg}</td></tr>`);
        }
        Swal.fire('Erro', msg, 'error');
    });
}

function salvarManutencao(){
    const id = $('#manutencao_id').val();
    const dados = {
        vehicle_id: $('#vehicle_id').val(),
        data: $('#data').val(),
        tipo: $('#tipo').val(),
        descricao: $('#descricao').val(),
        km: $('#km').val().replace(/\D/g, ''),
        // enviar como número simples (ex.: 1500)
        custo: Number(String($('#custo').val()).replace(/\D/g, '')) / 100,
        oficina: $('#oficina').val(),
        proxima_km: $('#proxima_km').val().replace(/\D/g, '')
    };
    const url = id ? `/frota/api/manutencoes/${id}` : '/frota/api/manutencoes';
    const method = id ? 'PUT' : 'POST';
    $.ajax({ url, method, data: dados })
        .done(() => { 
            $('#modalManutencao').modal('hide'); 
            carregarLista();
            Swal.fire({ icon: 'success', title: 'Salvo!', timer: 1400, showConfirmButton: false });
        })
        .fail(xhr => { 
            const msg = xhr.responseJSON?.message || 'Erro ao salvar';
            Swal.fire('Erro', msg, 'error'); 
        });
}

function editar(id){
    $.get('/frota/api/manutencoes').done(function(rows){
        const r = rows.find(x => x.id == id);
        if(!r) return;
        $('#manutencao_id').val(r.id);
        $('#vehicle_id').val(r.vehicle_id);
        $('#data').val(r.data);
        $('#tipo').val(r.tipo);
        $('#descricao').val(r.descricao);
        $('#km').val(formatKmBrFromString(r.km));
        $('#custo').val(formatarMoedaDigitacao(String(Math.round(Number(r.custo||0)*100))));
        $('#oficina').val(r.oficina);
        $('#proxima_km').val(formatKmBrFromString(r.proxima_km));
        $('#modalManutencao').modal('show');
    });
}

function ver(id){
    $.get('/frota/api/manutencoes').done(function(rows){
        const r = rows.find(x => x.id == id);
        if(!r) return;
        $('#manutencao_id').val(r.id);
        $('#vehicle_id').val(r.vehicle_id).prop('disabled', true);
        $('#data').val(r.data).prop('disabled', true);
        $('#tipo').val(r.tipo).prop('disabled', true);
        $('#descricao').val(r.descricao).prop('readonly', true);
        $('#km').val(formatKmBrFromString(r.km)).prop('disabled', true);
        $('#custo').val(formatarMoedaDigitacao(String(Math.round(Number(r.custo||0)*100)))).prop('disabled', true);
        $('#oficina').val(r.oficina).prop('disabled', true);
        $('#proxima_km').val(formatKmBrFromString(r.proxima_km)).prop('disabled', true);
        setModalReadOnly(true);
        $('#modalManutencao').modal('show');
    });
}

function setModalReadOnly(readOnly){
    if (readOnly){
        $('#formManutencao button[type="submit"]').hide();
    } else {
        $('#vehicle_id, #data, #tipo, #km, #custo, #oficina, #proxima_km').prop('disabled', false);
        $('#descricao').prop('readonly', false);
        $('#formManutencao button[type="submit"]').show();
    }
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
        $.ajax({ url: `/frota/api/manutencoes/${id}`, method: 'DELETE' })
            .done(() => { carregarLista(); Swal.fire({ icon:'success', title:'Excluído!', timer:1200, showConfirmButton:false }); })
            .fail(() => Swal.fire('Erro', 'Erro ao excluir', 'error'));
    });
}
</script>
@stop


