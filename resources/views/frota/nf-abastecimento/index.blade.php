@extends('adminlte::page')

@section('title', 'NF Abastecimento')

@section('content_header')
<h1>NF Abastecimento</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="form-inline mb-3">
                <label class="mr-2">Data inicial:</label>
                <input type="date" id="dataInicio" class="form-control mr-2">
                <label class="mr-2">Data final:</label>
                <input type="date" id="dataFim" class="form-control mr-2">
                <label class="mr-2">Veículo:</label>
                <select id="vehicleFiltro" class="form-control mr-2" style="min-width:220px">
                    <option value="">Todos</option>
                </select>
                <button class="btn btn-primary mr-2" onclick="carregarAbastecimentos()">Buscar</button>
                <button class="btn btn-outline-secondary" onclick="limparFiltros()">Limpar</button>
                <div class="ml-auto d-flex">
                    <button id="btnAbrirNF" class="btn btn-success mr-2" disabled onclick="abrirModalNF()">
                        <i class="fas fa-file-invoice"></i> NF
                    </button>
                    <button id="btnAvulso" class="btn btn-warning" onclick="abrirModalAvulso()">
                        <i class="fas fa-plus"></i> Avulso
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped" id="tabelaAbastNF">
                    <thead>
                        <tr>
                            <th style="width:40px"><input type="checkbox" id="checkAll"></th>
                            <th>Data</th>
                            <th>Veículo</th>
                            <th>KM</th>
                            <th>Litros</th>
                            <th>Valor</th>
                            <th>Preço/L</th>
                            <th>Posto</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal NF -->
<div class="modal fade" id="modalNF" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document" style="max-width: 98vw;">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-file-invoice mr-2"></i>
          Gerar NF / Cupom
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" class="text-white">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="font-weight-bold">Número da NF</label>
                <input type="text" id="numero_nf" class="form-control" maxlength="50">
            </div>
            <div class="form-group col-md-3">
                <label class="font-weight-bold">Data de Pagamento</label>
                <input type="date" id="data_pagamento" class="form-control">
            </div>
            <div class="form-group col-md-6">
                <label class="font-weight-bold">Banco de Pagamento</label>
                <input type="text" id="banco_pagamento" class="form-control" maxlength="100" placeholder="Ex: Bradesco, Itaú, Caixa...">
            </div>
        </div>
        <div class="table-responsive" style="max-height:350px; overflow-y:auto; overflow-x:hidden;">
            <table class="table table-hover" id="tabelaSelecionados" style="font-size: 0.9rem; table-layout: fixed; width: 100%;">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:8%; padding: 12px 8px;">Data</th>
                        <th style="width:10%; padding: 12px 8px;">Veículo</th>
                        <th style="width:8%; padding: 12px 8px;">KM</th>
                        <th style="width:6%; padding: 12px 8px;">Litros</th>
                        <th style="width:9%; padding: 12px 8px;">Valor</th>
                        <th style="width:8%; padding: 12px 8px;">Preço/L</th>
                        <th style="width:12%; padding: 12px 8px;">Posto</th>
                        <th style="width:14%; padding: 12px 8px;">Usuário</th>
                        <th style="width:20%; padding: 12px 8px;">Observações</th>
                        <th style="width:17%; padding: 12px 8px;">Cupom</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.85rem;"></tbody>
            </table>
        </div>
        <div class="mt-3 p-2 bg-light rounded">
          <div class="text-right small text-muted font-weight-bold" id="resumoNF"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="finalizarNF()">
          <i class="fas fa-check mr-1"></i>
          Finalizar
        </button>
      </div>
    </div>
  </div>
  </div>

<!-- Modal Abastecimento Avulso -->
<div class="modal fade" id="modalAvulso" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Abastecimento Avulso</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Tipo de combustível <span class="text-danger">*</span></label>
          <select id="avl_tipo" class="form-control">
            <option value="">Selecione...</option>
            <option value="gasolina">Gasolina</option>
            <option value="etanol">Etanol</option>
            <option value="diesel">Diesel</option>
            <option value="gnv">GNV</option>
          </select>
        </div>
        <div class="form-group">
          <label>Placa</label>
          <input type="text" id="avl_placa" class="form-control" maxlength="20" placeholder="Opcional">
        </div>
        <div class="form-group">
          <label>Motorista</label>
          <input type="text" id="avl_motorista" class="form-control" maxlength="150" placeholder="Opcional">
        </div>
        <div class="form-group">
          <label>Litros <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="0.01" id="avl_litros" class="form-control" placeholder="0,00">
        </div>
        <div class="form-group">
          <label>Valor <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="0.01" id="avl_valor" class="form-control" placeholder="0,00">
        </div>
        <div class="form-group">
          <label>Posto</label>
          <input type="text" id="avl_posto" class="form-control" maxlength="150" value="Auto Posto Estrela D'alva">
        </div>
        <div class="form-group">
          <label>Observações</label>
          <textarea id="avl_observacoes" class="form-control" rows="3" maxlength="1000" placeholder="Opcional"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" onclick="salvarAvulso()">Salvar</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
let listaAtual = [];
let selecionados = new Set();

$(function(){
    const hoje = new Date();
    const d = hoje.toISOString().slice(0,10);
    $('#dataInicio').val(d);
    $('#dataFim').val(d);
    carregarSelectVeiculos();
    carregarAbastecimentos();

    $('#checkAll').on('change', function(){
        const check = this.checked;
        selecionados.clear();
        if (check) { listaAtual.forEach(r => selecionados.add(r.selKey)); }
        renderTabela();
        atualizarBotaoNF();
    });
});

function carregarAbastecimentos(){
    const params = {};
    const di = $('#dataInicio').val();
    const df = $('#dataFim').val();
    const vehicleId = $('#vehicleFiltro').val();
    if (di) params.data_inicio = di;
    if (df) params.data_fim = df;
    if (vehicleId) params.vehicle_id = vehicleId;
    params.exclude_consolidated = 1;
    // Buscar abastecimentos da Frota e também da Roçagem
    const reqFrota = $.get('/frota/api/abastecimentos', params).catch(()=>[]);
    const reqRoc = $.get('/rocagem/api/abastecimentos', {
        data_inicio: params.data_inicio,
        data_fim: params.data_fim
    }).catch(()=>[]);
    const reqRocConsol = $.get('/frota/api/nf/rocagem/consolidados', {
        data_inicio: params.data_inicio,
        data_fim: params.data_fim
    }).catch(()=>({ ids: [] }));
    Promise.all([reqFrota, reqRoc, reqRocConsol]).then(([rowsFrota, rowsRoc, respConsol]) => {
        const frota = Array.isArray(rowsFrota) ? rowsFrota : [];
        const roc = Array.isArray(rowsRoc) ? rowsRoc : [];
        const idsRocConsol = (respConsol && Array.isArray(respConsol.ids)) ? respConsol.ids.map(Number) : [];
        const keyRocConsol = (respConsol && Array.isArray(respConsol.keys)) ? new Set(respConsol.keys) : new Set();
        // Normalizar roçagem para exibição na mesma tabela
        const rocNorm = roc
          // 1) remover roçagem consolidada por id (quando houver referência direta)
          .filter(r => idsRocConsol.indexOf(Number(r.id)) === -1)
          // 2) fallback: remover por chave (data|litros|valor|posto) para lotes antigos sem a coluna de referência
          .filter(r => {
            const data = (r.data || '').toString();
            const litros = Number(r.litros||0).toFixed(3);
            const valor = Number(r.valor||0).toFixed(2);
            const posto = String(r.posto||'').trim().toLowerCase();
            const key = `${data}|${litros}|${valor}|${posto}`;
            return !keyRocConsol.has(key);
          })
          .map(r => ({
            id: r.id,
            origem: 'r',
            selKey: `r-${r.id}`,
            data: r.data,
            km: 0,
            litros: r.litros,
            valor: r.valor,
            preco_litro: r.preco_litro ? Number(r.preco_litro) : (Number(r.valor)/Number(r.litros)),
            posto: r.posto,
            observacoes: (r.local_rocagem ? `Local: ${r.local_rocagem}` : '') + (r.user_name ? (r.local_rocagem? ' • ' : '') + `Usuário: ${r.user_name}` : ''),
            veiculo_label: r.local_rocagem || 'Roçagem'
        }));
        const rows = frota.map(r => ({
            ...r,
            origem: 'f',
            selKey: `f-${r.id}`,
            preco_litro: r.preco_litro ? Number(r.preco_litro) : (Number(r.valor)/Number(r.litros)),
            veiculo_label: r.veiculo && r.veiculo.placa ? r.veiculo.placa : r.vehicle_id
        })).concat(rocNorm);
        // Ordenação: quando houver busca por período (data_inicio/data_fim), ordenar por data ASC
        // Caso contrário (visão do dia), manter DESC (mais recente primeiro)
        const hasRange = !!(params.data_inicio || params.data_fim);
        rows.sort((a,b) => {
            const da = Date.parse(a.data);
            const db = Date.parse(b.data);
            if (da !== db) return hasRange ? (da - db) : (db - da);
            // em empate, ordena por origem (f antes de r) e id
            if (a.origem !== b.origem) return a.origem < b.origem ? -1 : 1;
            const ia = Number(a.id||0), ib = Number(b.id||0);
            return hasRange ? (ia - ib) : (ib - ia);
        });
        listaAtual = rows;
        selecionados.clear();
        $('#checkAll').prop('checked', false);
        renderTabela();
        atualizarBotaoNF();
    });
}

function limparFiltros(){
    $('#dataInicio').val('');
    $('#dataFim').val('');
    $('#vehicleFiltro').val('');
    carregarAbastecimentos();
}

function carregarSelectVeiculos(){
    $.get('/frota/api/veiculos', { ts: Date.now() }).done(function(veiculos){
        const sel = $('#vehicleFiltro');
        sel.find('option:not(:first)').remove();
        veiculos.forEach(v => {
            const label = `${v.placa}${v.marca||v.modelo? ' - ' : ''}${v.marca||''} ${v.modelo||''}`.trim();
            sel.append(`<option value="${v.id}">${escapeHtml(label)}</option>`);
        });
    });
}

function renderTabela(){
    const tbody = $('#tabelaAbastNF tbody');
    tbody.empty();
    if (!listaAtual.length){
        tbody.append('<tr><td colspan="8" class="text-center text-muted">Nenhum abastecimento encontrado</td></tr>');
        return;
    }
    listaAtual.forEach(r => {
        const marcado = selecionados.has(r.selKey) ? 'checked' : '';
        const data = new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR');
        tbody.append(`
            <tr>
                <td><input type="checkbox" ${marcado} onchange="toggleSel('${r.selKey}', this.checked)"></td>
                <td>${data}</td>
                <td>${escapeHtml(r.veiculo_label)}</td>
                <td>${Number(r.km).toLocaleString()} km</td>
                <td>${Number(r.litros)} L</td>
                <td>R$ ${Number(r.valor).toFixed(2)}</td>
                <td>R$ ${Number(r.preco_litro).toFixed(3)}</td>
                <td>${r.posto||''}</td>
                <td>${escapeHtml(r.observacoes||'')}</td>
            </tr>
        `);
    });
}

function toggleSel(key, on){
    if (on) selecionados.add(key); else selecionados.delete(key);
    atualizarBotaoNF();
}

function atualizarBotaoNF(){
    $('#btnAbrirNF').prop('disabled', selecionados.size === 0);
}

function abrirModalNF(){
    const ids = Array.from(selecionados);
    const pick = listaAtual.filter(r => ids.includes(r.selKey));
    const tbody = $('#tabelaSelecionados tbody');
    tbody.empty();
    let total = 0, litros = 0;
    pick.forEach(r => {
        const data = new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR');
        total += Number(r.valor||0); litros += Number(r.litros||0);
        tbody.append(`
            <tr style="border-bottom: 1px solid #dee2e6;">
                <td style="padding: 10px 8px; vertical-align: middle;">${data}</td>
                <td style="padding: 10px 8px; vertical-align: middle;"><strong>${escapeHtml(r.veiculo_label)}</strong></td>
                <td style="padding: 10px 8px; vertical-align: middle;">${Number(r.km).toLocaleString()} km</td>
                <td style="padding: 10px 8px; vertical-align: middle; text-align: center;">${Number(r.litros).toLocaleString('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2})}L</td>
                <td style="padding: 10px 8px; vertical-align: middle; color: #28a745; font-weight: 600;">R$ ${Number(r.valor).toFixed(2)}</td>
                <td style="padding: 10px 8px; vertical-align: middle;">R$ ${Number(r.preco_litro).toFixed(3)}</td>
                <td style="padding: 10px 8px; vertical-align: middle;">${r.posto||''}</td>
                <td style="padding: 10px 8px; vertical-align: middle; color: #007bff; font-weight: 500;">${(r.usuario && r.usuario.name) ? r.usuario.name : (r.user_name||r.user_id||'')}</td>
                <td style="padding: 10px 8px; vertical-align: middle;">${escapeHtml(r.observacoes||'')}</td>
                <td style="padding: 10px 8px; vertical-align: middle;"><input type="text" class="form-control form-control-sm" id="cupom-${r.selKey}" maxlength="50" placeholder="Nº cupom" style="border: 2px solid #e9ecef; border-radius: 6px; width: 100%;"></td>
            </tr>
        `);
    });
    const litrosFmt = Number(litros).toLocaleString('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2});
    const totalFmt = Number(total).toLocaleString('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2});
    $('#resumoNF').text(`Selecionados: ${pick.length} • Litros: ${litrosFmt} • Total: R$ ${totalFmt}`);
    $('#numero_nf').val('');
    $('#data_pagamento').val('');
    $('#banco_pagamento').val('');
    $('#modalNF').modal('show');
}

function finalizarNF(){
    const ids = Array.from(selecionados);
    if (!ids.length) return;
    const idsFrota = [];
    const idsRoc = [];
    ids.forEach(k => {
        if (String(k).startsWith('f-')) idsFrota.push(Number(String(k).slice(2)));
        else if (String(k).startsWith('r-')) idsRoc.push(Number(String(k).slice(2)));
    });
    const payload = {
        ids_frota: idsFrota,
        ids_roc: idsRoc,
        numero_nf: $('#numero_nf').val().trim(),
        data_pagamento: $('#data_pagamento').val() || null,
        banco_pagamento: $('#banco_pagamento').val().trim() || null,
        cupons: {}
    };
    let faltando = [];
    ids.forEach(key => {
        const v = ($(`#cupom-${key}`).val() || '').trim();
        if (!v) faltando.push(key);
        payload.cupons[key] = v;
    });
    if (faltando.length){
        Swal.fire('Atenção', 'Informe o número do cupom para todos os abastecimentos selecionados.', 'warning');
        return;
    }
    $.ajax({
        url: '/frota/nf-abastecimento/finalizar',
        method: 'POST',
        data: payload,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    })
      .done(() => { $('#modalNF').modal('hide'); Swal.fire({ icon:'success', title:'Finalizado!', timer:1200, showConfirmButton:false }); carregarAbastecimentos(); })
      .fail(xhr => { const msg = xhr.responseJSON?.message || 'Erro ao finalizar'; Swal.fire('Erro', msg, 'error'); });
}

function abrirModalAvulso(){
    $('#avl_tipo').val('');
    $('#avl_placa').val('');
    $('#avl_motorista').val('');
    $('#avl_litros').val('');
    $('#avl_valor').val('');
    $('#avl_posto').val("Auto Posto Estrela D'alva");
    $('#avl_observacoes').val('');
    $('#modalAvulso').modal('show');
}

function salvarAvulso(){
    const payload = {
        tipo_combustivel: $('#avl_tipo').val(),
        placa: $('#avl_placa').val().trim(),
        motorista: $('#avl_motorista').val().trim(),
        litros: Number($('#avl_litros').val()),
        valor: Number($('#avl_valor').val()),
        posto: $('#avl_posto').val().trim(),
        observacoes: $('#avl_observacoes').val().trim(),
    };
    if (!payload.tipo_combustivel || !payload.litros || payload.litros <= 0 || !payload.valor || payload.valor <= 0){
        Swal.fire('Atenção','Preencha os campos obrigatórios.','warning');
        return;
    }
    $.ajax({
        url: '/frota/nf-abastecimento/avulso',
        method: 'POST',
        data: payload,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    })
    .done(() => { $('#modalAvulso').modal('hide'); Swal.fire({ icon:'success', title:'Registrado!', timer:1200, showConfirmButton:false }); carregarAbastecimentos(); })
    .fail(xhr => { const msg = xhr.responseJSON?.message || 'Erro ao salvar'; Swal.fire('Erro', msg, 'error'); });
}

// Utilitário simples para evitar XSS na renderização de textos
function escapeHtml(s){
    const div = document.createElement('div');
    div.innerText = (s == null) ? '' : String(s);
    return div.innerHTML;
}
</script>
@stop


