@extends('adminlte::page')

@section('title', 'Frota - Abastecimentos')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-gas-pump text-primary mr-3"></i>
            Abastecimentos
        </h1>
        <p class="text-muted mt-1 mb-0">Registre os abastecimentos da frota</p>
    </div>
    <div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAbastecimento">
            <i class="fas fa-plus mr-1"></i>
            Novo Abastecimento
        </button>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@php($__isAdmin = isset($isAdmin) ? $isAdmin : (optional(auth()->user()->profile)->name === 'Admin'))
<div class="container-fluid">
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
                    <i class="fas fa-gas-pump fa-2x mb-2"></i>
                    <h3 id="totalAbastecimentos">2</h3>
                    <p class="mb-0">Total do Mês</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-success">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h3 id="custoTotal">R$ 525,75</h3>
                    <p class="mb-0">Custo Total</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-info">
                <div class="card-body text-center">
                    <i class="fas fa-tint fa-2x mb-2"></i>
                    <h3 id="totalLitros">95.5L</h3>
                    <p class="mb-0">Total de Litros</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-warning">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-2x mb-2"></i>
                    <h3 id="precoMedio">R$ 5,50</h3>
                    <p class="mb-0">Preço Médio/L</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Abastecimentos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>
                Histórico de Abastecimentos
            </h5>
        </div>
        <div class="card-body">
            <!-- Desktop/Tablet -->
            <div id="abastDesktop" class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-striped" id="tabelaAbastecimentos">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Veículo</th>
                                <th>KM</th>
                                <th>Litros</th>
                                <th>Valor Total</th>
                                <th>Preço/L</th>
                                <th>Posto</th>
                                <th>Usuário</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <!-- Mobile: lista simplificada -->
            <div id="abastMobile" class="d-block d-md-none">
                <div id="listaAbastecimentosMobile" class="list-group"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Abastecimento -->
<div class="modal fade" id="modalAbastecimento" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-gas-pump mr-2"></i>
                    Novo Abastecimento
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formAbastecimento">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_id">Veículo <span class="text-danger">*</span></label>
                                <select id="vehicle_id" class="form-control" required>
                                    <option value="">Selecione o veículo...</option>
                                </select>
                                <small id="veicInactiveHint" class="text-danger d-none">Veículo inativo — edição bloqueada para troca de veículo.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data">Data <span class="text-danger">*</span></label>
                                <input id="data" type="date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="km">Quilometragem <span class="text-danger">*</span></label>
                                <input id="km" type="text" inputmode="numeric" class="form-control" placeholder="Ex: 25.100" required>
                                <small class="text-muted d-block">Pode ser igual ou maior que o KM atual</small>
                                <small id="kmAtualHintAbs" class="text-muted d-block"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="litros">Litros <span class="text-danger">*</span></label>
                                <input id="litros" type="text" inputmode="decimal" class="form-control" placeholder="Ex: 45.50" required>
                                <small class="text-muted">Use ponto como separador decimal.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valor">Valor Total <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input id="valor" type="text" inputmode="numeric" class="form-control text-right" placeholder="Ex: 250,75" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="posto">Posto</label>
                                <input id="posto" type="text" class="form-control" placeholder="Ex: Posto Shell - Centro" value="Auto Posto Estrela D'alva">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="abastecimento_id">
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

/* Mobile cards style for list items */
@media (max-width: 767.98px) {
  #abastMobile .list-group-item { border-radius: 10px; margin-bottom: .75rem; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
  #abastMobile .list-group-item .meta { font-size: .85rem; color: #6c757d; }
  #abastMobile .list-group-item .price { font-weight: 600; }
}
</style>
@stop

@section('js')
<script>
// Expor flag de permissão para o JS
const IS_ADMIN = @json($__isAdmin ?? false);
var VEHICLE_MAP = {};

$(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    // Carregar veículos primeiro para popular o mapa; depois listar
    carregarVeiculosNoSelect().always(function(){
        carregarLista();
    });

    // Resetar estado do select ao fechar o modal
    $('#modalAbastecimento').on('hidden.bs.modal', function(){
        const sel = $('#vehicle_id');
        sel.prop('disabled', false);
        $('#veicInactiveHint').addClass('d-none').text('Veículo inativo — edição bloqueada para troca de veículo.');
        sel.find('.opt-inativo, .opt-temp').remove();
        $('#abastecimento_id').val('');
    });

    $('#formAbastecimento').on('submit', function(e){
        e.preventDefault();
        salvarAbastecimento();
    });
    // Máscara de moeda BRL para o campo valor
    function maskMoedaBR(val){
        let digits = String(val||'').replace(/\D/g,'');
        if (!digits) return '0,00';
        const num = parseInt(digits, 10);
        const cents = (num/100).toFixed(2);
        // toLocaleString pt-BR separa milhar com ponto e decimal com vírgula
        return Number(cents).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    $('#valor').on('input', function(){
        this.value = maskMoedaBR(this.value);
        // cursor no fim
        const el = this; setTimeout(()=>{ try{ el.selectionStart = el.selectionEnd = el.value.length; }catch(e){} }, 0);
    }).on('focus click', function(){
        if (!this.value) this.value = '0,00';
        const el = this; setTimeout(()=>{ try{ el.selectionStart = el.selectionEnd = el.value.length; }catch(e){} }, 0);
    });
    // Litros: sem máscara/trava. Apenas permitir números e um único ponto.
    $('#litros').on('input', function(){
        let v = String(this.value || '');
        v = v.replace(/[^0-9.]/g, '');
        const parts = v.split('.');
        if (parts.length > 2) {
            v = parts[0] + '.' + parts.slice(1).join('');
        }
        this.value = v;
    });

    // máscara de milhares para quilometragem
    $('#km').on('input', function(){
        this.value = formatKmBrFromString(this.value);
    });

    // Filtro de mês
    const hoje = new Date();
    const pad = (n)=> String(n).padStart(2,'0');
    $('#filtroMes').val(`${hoje.getFullYear()}-${pad(hoje.getMonth()+1)}`);
    $('#filtroMes').on('change', function(){ carregarLista(); });
    $('#btnLimparMes').on('click', function(){ $('#filtroMes').val(''); carregarLista(); });
});

function carregarVeiculosNoSelect(){
    return $.get('/frota/api/veiculos', { exclude_maintenance: 1, only_usable: 1 }).done(function(veiculos){
        const sel = $('#vehicle_id');
        sel.find('option:not(:first)').remove();
        VEHICLE_MAP = {};
        veiculos.forEach(v => {
            if (String(v.status).toLowerCase() === 'manutencao') return;
            const label = `${v.placa} - ${v.marca||''} ${v.modelo||''}`;
            VEHICLE_MAP[Number(v.id)] = label;
            sel.append(`<option value="${v.id}" data-km="${v.km_atual||0}">${label}</option>`)
        });
        sel.on('change', function(){
            const km = $(this).find('option:selected').data('km');
            $('#kmAtualHintAbs').text(km ? `KM atual: ${Number(km).toLocaleString()} km` : '');
        }).trigger('change');
    });
}

function carregarLista(){
    const ONLY_MINE = @json(!($__isAdmin ?? false));
    const params = ONLY_MINE ? { user_id: {{ auth()->id() }} } : {};
    // Aplicar filtro de mês, se selecionado
    const mesSel = $('#filtroMes').val();
    if (mesSel) { // formato YYYY-MM
        const [ano, mes] = mesSel.split('-');
        params.data_ini = `${ano}-${mes}-01`;
        // calcular último dia do mês
        const ultimoDia = new Date(Number(ano), Number(mes), 0).getDate();
        params.data_fim = `${ano}-${mes}-${(''+ultimoDia).padStart(2,'0')}`;
    }
    $.get('/frota/api/abastecimentos', params).done(function(rows){
        const tbody = $('#tabelaAbastecimentos tbody');
        const listaMobile = $('#listaAbastecimentosMobile');
        const isMobile = window.matchMedia('(max-width: 767.98px)').matches;

        tbody.empty();
        listaMobile.empty();

        // Aplicar filtro por mês (client-side) caso a API não filtre
        const mesAtivo = $('#filtroMes').val();
        let dataset = (rows || []).slice();
        if (mesAtivo) {
            const [ano, mes] = mesAtivo.split('-');
            const ultimoDia = new Date(Number(ano), Number(mes), 0).getDate();
            const ini = `${ano}-${mes}-01`;
            const fim = `${ano}-${mes}-${String(ultimoDia).padStart(2,'0')}`;
            dataset = dataset.filter(r => String(r.data) >= ini && String(r.data) <= fim);
        }

        // Ordenar por data desc
        const sorted = dataset.sort((a,b) => new Date(b.data) - new Date(a.data));
        const dias = [];
        const incluidos = [];
        const limitar3dias = !mesAtivo; // mantém últimos 3 dias apenas quando não há filtro de mês
        for (const r of sorted){
            const d = r.data;
            if (dias.indexOf(d) === -1){
                if (limitar3dias && dias.length >= 3) continue;
                dias.push(d);
            }
            incluidos.push(r);
        }

        let total = 0, litros = 0;
        if (!incluidos.length){
            if (isMobile) {
                listaMobile.append('<div class="list-group-item text-center text-muted">Nenhum abastecimento encontrado</div>');
            } else {
                tbody.append('<tr><td colspan="9" class="text-center text-muted">Nenhum abastecimento encontrado</td></tr>');
            }
        }

        // Renderizar agrupado por dia
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
                const preco = r.preco_litro ? Number(r.preco_litro).toFixed(3) : (Number(r.valor)/Number(r.litros)).toFixed(3);
                total += Number(r.valor||0);
                litros += Number(r.litros||0);

                if (!isMobile) {
                    tbody.append(`
                        <tr>
                            <td>${data}</td>
                            <td><strong>${(function(){ const rel = r.veiculo || {}; const lblRel = rel.placa ? `${rel.placa}${(rel.marca||rel.modelo)? ' - ' : ''}${rel.marca||''} ${rel.modelo||''}`.trim() : ''; return lblRel || (VEHICLE_MAP[Number(r.vehicle_id)] || r.vehicle_id); })()}</strong></td>
                            <td>${Number(r.km).toLocaleString()} km</td>
                            <td>${Number(r.litros)}L</td>
                            <td>R$ ${Number(r.valor).toFixed(2)}</td>
                            <td>R$ ${preco}</td>
                            <td>${r.posto||''}</td>
                            <td>${(r.usuario && r.usuario.name) ? r.usuario.name : (r.user_name||r.user_id||'')}</td>
                            <td class="text-center">${IS_ADMIN ? `<button class="btn btn-sm btn-info" onclick="editar(${r.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="excluirReg(${r.id})"><i class="fas fa-trash"></i></button>` : ''}
                            </td>
                        </tr>
                    `);
                } else {
                    listaMobile.append(`
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="font-weight-bold">${(function(){ const rel = r.veiculo || {}; const lblRel = rel.placa ? `${rel.placa}${(rel.marca||rel.modelo)? ' - ' : ''}${rel.marca||''} ${rel.modelo||''}`.trim() : ''; return lblRel || (VEHICLE_MAP[Number(r.vehicle_id)] || r.vehicle_id); })()}</div>
                                    <div class="meta">${data} • ${Number(r.km).toLocaleString()} km</div>
                                </div>
                                <div><span class="badge badge-info">${Number(r.litros)}L</span></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <div class="meta">Preço/L</div><div class="price">R$ ${preco}</div>
                                <div class="meta">Total</div><div class="price">R$ ${Number(r.valor).toFixed(2)}</div>
                            </div>
                            ${ IS_ADMIN ? `<div class="mt-2"><div class="btn-group btn-group-sm w-100"><button class="btn btn-info" onclick="editar(${r.id})"><i class="fas fa-edit"></i></button><button class="btn btn-danger" onclick="excluirReg(${r.id})"><i class="fas fa-trash"></i></button></div></div>` : ''}
                        </div>
                    `);
                }
            });
        });

        // cards
        $('#totalAbastecimentos').text(incluidos.length);
        // Formatar valores nos cards com pt-BR
        const custoTotalFmt = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const litrosFmt = Number(litros).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('#custoTotal').text('R$ ' + custoTotalFmt);
        $('#totalLitros').text(litrosFmt + 'L');
        const precoMedio = incluidos.length && litros > 0 ? (total / litros) : 0;
        $('#precoMedio').text('R$ ' + precoMedio.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    });
}

function salvarAbastecimento(){
    const id = $('#abastecimento_id').val();
    const dados = {
        vehicle_id: $('#vehicle_id').val(),
        data: $('#data').val(),
        km: $('#km').val().replace(/\D/g, ''),
        litros: $('#litros').val(),
        valor: (function(){
            const v = $('#valor').val();
            // Converter "1.234,56" -> 1234.56
            const norm = String(v||'').replace(/\./g,'').replace(/,/g,'.');
            return norm;
        })(),
        posto: $('#posto').val()
    };
    const url = id ? `/frota/api/abastecimentos/${id}` : '/frota/api/abastecimentos';
    const method = id ? 'PUT' : 'POST';
    $.ajax({ url, method, data: dados })
        .done(() => { 
            $('#modalAbastecimento').modal('hide'); 
            carregarLista(); 
            Swal.fire({ icon: 'success', title: 'Salvo!', timer: 1400, showConfirmButton: false });
        })
        .fail(xhr => { 
            const msg = xhr.responseJSON?.message || 'Erro ao salvar';
            Swal.fire('Erro', msg, 'error'); 
        });
}

function editar(id){
    // Para simplificar: buscar a lista e preencher (poderia ter endpoint /{id})
    $.get('/frota/api/abastecimentos').done(function(rows){
        const r = rows.find(x => x.id == id);
        if(!r) return;
        $('#abastecimento_id').val(r.id);
        const sel = $('#vehicle_id');
        // limpar qualquer opção temporária anterior
        sel.find('.opt-inativo, .opt-temp').remove();
        // determinar label preferindo relacionamento (placa - marca modelo)
        const rel = r.veiculo || {};
        const lblRel = rel.placa ? `${rel.placa}${(rel.marca||rel.modelo)? ' - ' : ''}${rel.marca||''} ${rel.modelo||''}`.trim() : '';
        const label = lblRel || (VEHICLE_MAP[Number(r.vehicle_id)] || r.vehicle_id);
        // garantir que exista a option para o veículo vinculado
        if (!sel.find(`option[value='${r.vehicle_id}']`).length){
            sel.append(`<option class='opt-temp' value='${r.vehicle_id}'>${label}</option>`);
        }
        // selecionar e controlar a edição do veículo conforme permissão
        sel.val(r.vehicle_id);
        if (IS_ADMIN) {
            sel.prop('disabled', false);
            $('#veicInactiveHint').addClass('d-none').text('');
        } else {
            sel.prop('disabled', true);
            $('#veicInactiveHint').removeClass('d-none').text('Veículo fixo deste abastecimento — não é possível alterar.');
        }
        
        $('#data').val(r.data);
        $('#km').val(formatKmBrFromString(r.km));
        $('#litros').val(r.litros);
        $('#valor').val(r.valor);
        $('#posto').val(r.posto);
        $('#modalAbastecimento').modal('show');
    });
}

// Utilitário: formata milhares pt-BR durante digitação
function formatKmBrFromString(value){
    const digitsOnly = String(value || '').replace(/\D/g, '');
    if (!digitsOnly) return '';
    return Number(digitsOnly).toLocaleString('pt-BR');
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
        $.ajax({ url: `/frota/api/abastecimentos/${id}`, method: 'DELETE' })
            .done(() => { carregarLista(); Swal.fire({ icon:'success', title:'Excluído!', timer:1200, showConfirmButton:false }); })
            .fail(() => Swal.fire('Erro', 'Erro ao excluir', 'error'));
    });
}
</script>
@stop


