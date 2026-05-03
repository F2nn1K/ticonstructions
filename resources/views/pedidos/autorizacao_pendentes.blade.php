@extends('adminlte::page')

@section('title', 'Autorizações Pendentes')

@section('plugins.Sweetalert2', true)

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-clock text-warning mr-2"></i>Autorizações Pendentes</h1>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="card card-primary shadow-sm">
  <div class="card-body p-0">
    <div class="px-3 py-2 bg-primary text-white d-flex justify-content-between align-items-center">
      <strong>Solicitações Pendentes de Autorização</strong>
      <a href="{{ route('pedidos.autorizacao') }}" class="btn btn-outline-light btn-sm text-white"><i class="fas fa-arrow-left mr-1"></i>{{ __('Voltar') }}</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0 align-middle" id="tabela-pendentes">
        <thead>
          <tr>
            <th>Nº Pedido <button type="button" class="btn btn-link btn-sm p-0 js-sort align-baseline" data-col="num_pedido" title="Ordenar"><i class="fas fa-sort"></i></button></th>
            <th>Data Solicitação <button type="button" class="btn btn-link btn-sm p-0 js-sort align-baseline" data-col="data_solicitacao" title="Ordenar"><i class="fas fa-sort"></i></button></th>
            <th>Solicitante <button type="button" class="btn btn-link btn-sm p-0 js-sort align-baseline" data-col="solicitante" title="Ordenar"><i class="fas fa-sort"></i></button></th>
            <th>Produto <button type="button" class="btn btn-link btn-sm p-0 js-sort align-baseline" data-col="itens" title="Ordenar"><i class="fas fa-sort"></i></button></th>
            <th>Quantidade <button type="button" class="btn btn-link btn-sm p-0 js-sort align-baseline" data-col="quantidade_total" title="Ordenar"><i class="fas fa-sort"></i></button></th>
            <th>Prioridade <button type="button" class="btn btn-link btn-sm p-0 js-sort align-baseline" data-col="prioridade" title="Ordenar"><i class="fas fa-sort"></i></button></th>
            <th>Centro Custo <button type="button" class="btn btn-link btn-sm p-0 js-sort align-baseline" data-col="centro_custo_nome" title="Ordenar"><i class="fas fa-sort"></i></button></th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="8" class="text-center text-muted" id="empty-row">
              <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
              Nenhuma solicitação pendente de autorização
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
// CSRF para requisições PUT/POST em JSON
try { $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } }); } catch(e) {}
// Função para formatar data no padrão brasileiro (DD/MM/AAAA HH:MM)
function formatarDataBR(dataISO) {
    if (!dataISO) return '—';
    const data = new Date(dataISO);
    if (isNaN(data.getTime())) return '—';
    
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();
    const hora = String(data.getHours()).padStart(2, '0');
    const minuto = String(data.getMinutes()).padStart(2, '0');
    
    return `${dia}/${mes}/${ano} ${hora}:${minuto}`;
}

// Formata moeda pt-BR
function formatarMoedaBR(valor){
    const n = Number(valor||0);
    return n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Função para formatar tipo de interação
function formatTipo(tipo) {
    const tipos = {
        'aprovacao': '<span class="badge badge-success">APROVAÇÃO</span>',
        'rejeicao': '<span class="badge badge-danger">REJEIÇÃO</span>',
        'comentario': '<span class="badge badge-info">COMENTÁRIO</span>'
    };
    return tipos[tipo] || '<span class="badge badge-secondary">OUTRO</span>';
}

// Função para escapar HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Função para escapar HTML (alias para escapeHtml)
function esc(text) {
    return escapeHtml(text);
}

// Estado de ordenação em memória (sem DataTables)
window.__ord = { col: null, dir: 'asc' };

$(function(){ carregarPendentes(); setInterval(carregarPendentes, 30000); });
function ordenarGrupos(grupos){
  const st = window.__ord || { col:null, dir:'asc' };
  if (!st.col) return grupos;
  const dir = st.dir === 'desc' ? -1 : 1;
  const col = st.col;
  const clone = grupos.slice();
  clone.sort(function(a,b){
    let va = a[col]; let vb = b[col];
    // Normalizações
    if (col === 'data_solicitacao') {
      va = va ? new Date(va).getTime() : 0; vb = vb ? new Date(vb).getTime() : 0;
    } else if (col === 'quantidade_total' || col === 'itens') {
      va = Number(va||0); vb = Number(vb||0);
    } else if (typeof va === 'string') {
      va = va.toLowerCase(); vb = (vb||'').toLowerCase();
    }
    if (va < vb) return -1*dir; if (va > vb) return 1*dir; return 0;
  });
  return clone;
}

function carregarPendentes(){
  const params = new URLSearchParams(window.location.search);
  const data_ini = params.get('data_ini');
  const data_fim = params.get('data_fim');
  const qs = (data_ini && data_fim) ? (`?data_ini=${encodeURIComponent(data_ini)}&data_fim=${encodeURIComponent(data_fim)}`) : '';
  $.get('/api/pedidos-pendentes-agrupados'+qs, function(resp){
    if(!resp.success) return; const grupos = resp.data || [];
    $('#badge-pendentes').text(`${grupos.length} pendentes`);
    const tbody = $('#tabela-pendentes tbody'); tbody.empty();
    if(grupos.length===0){ tbody.append(`<tr><td colspan="8" class="text-center text-muted" id="empty-row">
      <i class=\"fas fa-check-circle fa-2x mb-2 text-success\"></i><br>
      Nenhuma solicitação pendente de autorização
    </td></tr>`); return; }
    const arr = ordenarGrupos(grupos);
    const esc = s=>String(s||'').replace(/[&<>\"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    const prioBadge = p=>{ const k=String(p||'').toLowerCase(); return k==='alta'?'danger':(k==='media'?'warning':'secondary'); };
    const prioRow = p=>{ const k=String(p||'').toLowerCase(); return k==='alta'?'tr-prio-alta':(k==='media'?'tr-prio-media':'tr-prio-baixa'); };
    arr.forEach(function(g){
      const rowCls = prioRow(g.prioridade);
      const badgeCls = prioBadge(g.prioridade);
      const tr = `
        <tr class="${rowCls}">
          <td><span class="badge badge-dark">${g.num_pedido||'—'}</span></td>
          <td>${formatarDataBR(g.data_solicitacao)}</td>
          <td>${esc(g.solicitante||'—')}</td>
          <td>
            <div class="d-flex align-items-center">
              <div class="rounded bg-light mr-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                <i class="fas fa-box text-primary"></i>
              </div>
              <div>
                <div class="font-weight-600">${g.itens} itens</div>
                <small class="text-muted">Qtd total: ${Number(g.quantidade_total||0).toLocaleString('pt-BR')}</small>
              </div>
            </div>
          </td>
          <td>${(g.quantidade_total!=null) ? Number(g.quantidade_total).toLocaleString('pt-BR') : '—'}</td>
           <td><span class="badge badge-${badgeCls}">${(g.prioridade||'').toUpperCase()}</span></td>
          <td>${esc(g.centro_custo_nome||'—')}</td>
          <td class="text-nowrap">
            <button class="btn btn-outline-primary btn-sm" onclick="abrirGrupo('${g.grupo_hash}')" title="Detalhar"><i class="fas fa-search"></i></button>
            <button class="btn btn-outline-danger btn-sm ml-1 js-btn-excluir" data-num="${g.num_pedido}" title="Excluir pedido"><i class="fas fa-trash"></i></button>
          </td>
        </tr>`; tbody.append(tr);
    });
  });
}

// Controles de ordenação (ícones nos cabeçalhos)
$(document).on('click', '.js-sort', function(){
  const col = $(this).data('col');
  if (!col) return;
  if (!window.__ord) window.__ord = { col:null, dir:'asc' };
  if (window.__ord.col === col) {
    window.__ord.dir = (window.__ord.dir === 'asc') ? 'desc' : 'asc';
  } else {
    window.__ord.col = col; window.__ord.dir = 'asc';
  }
  carregarPendentes();
});

// (removido) botão de adicionar item na coluna de ações

// Excluir grupo (somente Admin)
$(document).off('click.excluir').on('click.excluir', '.js-btn-excluir', function(){
  const num = $(this).data('num');
  // confirmar
  Swal.fire({
    title: 'Excluir pedido? ',
    html: `Nº <strong>${num}</strong><br>Esta ação removerá todas as linhas deste pedido.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar'
  }).then(function(res){
    if(!res.isConfirmed) return;
    Swal.fire({ title:'Excluindo...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
    $.ajax({
      url:`/api/pedidos-agrupado/${num}`, method:'DELETE',
      success:function(r){
        Swal.close();
        if(r && r.success){
          Swal.fire({ icon:'success', title:'Pedido excluído', timer:1400, showConfirmButton:false });
          carregarPendentes();
        } else {
          Swal.fire('Atenção', (r && r.message) ? r.message : 'Não foi possível excluir.', 'warning');
        }
      },
      error:function(xhr){
        Swal.close();
        const msg=(xhr.responseJSON&&xhr.responseJSON.message)?xhr.responseJSON.message:'Erro ao excluir.';
        Swal.fire('Erro', msg, 'error');
      }
    });
  });
});

function abrirGrupo(hash){
  $.get(`/api/pedidos-agrupado/${hash}`, function(resp){
    if(!resp.success) return;
    window.__hashGrupo = hash;
    const cab = resp.data.cabecalho; const itens = resp.data.itens; const interacoes = resp.data.interacoes||[];
    // Detectar admin (já usamos este endpoint abaixo para os botões)
    let isAdmin = false;
    try { if (window.__isAdmin === true) { isAdmin = true; } } catch(e) {}
    // Exibir o valor exatamente como está na coluna `valor` do banco (unitário)
    const linhas = itens.map(i => {
      const qtd = Number(i.quantidade||0);
      const unitario = Number(i.valor_unitario||0);
      const totalItem = Number(i.valor||0); // já gravado somando ou exibindo fiel
      const qInput = isAdmin ? `<input type="number" min="1" class="form-control form-control-sm text-center js-qtd" value="${qtd}" data-id="${i.id}">` : `${qtd}`;
      const nomeInput = isAdmin ? `<input type="text" class="form-control form-control-sm js-nome" value="${escapeHtml(i.produto_nome)}" data-id="${i.id}">` : `${i.produto_nome}`;
      return `
        <tr data-id="${i.id}">
          <td>
            <div class="d-flex align-items-center justify-content-between">
              <div class="flex-grow-1 pr-2">${nomeInput}</div>
              ${isAdmin ? `<button type="button" class="btn btn-outline-danger btn-xs ml-1 js-excluir-item" title="Excluir item" data-id="${i.id}"><i class="fas fa-trash"></i></button>` : ''}
            </div>
          </td>
          <td class="text-center">${qInput}</td>
          <td class="text-right js-unit">${unitario>0 ? 'R$ '+formatarMoedaBR(unitario) : '—'}</td>
          <td class="text-right js-total">${totalItem>0 ? 'R$ '+formatarMoedaBR(totalItem) : '—'}</td>
        </tr>`;
    }).join('');
    // Total geral: soma dos valores exibidos na coluna total
    const totalGeral = (itens||[]).reduce((sum, it) => sum + Number(it.valor||0), 0);
    const html = `
      <div class="modal fade" id="modalGrupo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
          <div class="modal-header"> 
            <h4 class="modal-title mb-0"><i class="fas fa-file-alt mr-2"></i>Detalhes da Solicitação</h4> 
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button> 
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6"><p><strong>Nº Pedido:</strong> ${cab.num_pedido||'—'}</p><p><strong>Data:</strong> ${cab.data_solicitacao}</p><p><strong>Solicitante:</strong> ${cab.solicitante}</p></div>
              <div class="col-md-6"><p><strong>Prioridade:</strong> ${cab.prioridade.toUpperCase()}</p><p><strong>Centro de Custo:</strong> <span id="cc-view" data-ccid="${cab.centro_custo_id||''}">${cab.centro_custo_nome}</span></p></div>
            </div>
            <div class="row">
              <div class="col-md-6"><p><strong>Rota:</strong> <span id="rota-view" data-rotaid="${cab.rota_id||''}">${cab.rota_nome||'—'}</span></p></div>
              <div class="col-md-6"><p><strong>Roteirização:</strong> <span id="rot-view" data-rotid="${cab.roteirizacao_id||''}">${cab.roteirizacao_nome||'—'}</span></p></div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <p class="mb-1"><strong>Observações</strong></p>
              <div id="header-edit-actions-view" class="d-none">
                <button class="btn btn-link btn-sm p-0" id="btn-edit-header"><i class="fas fa-edit mr-1"></i>{{ __('Editar') }}</button>
                <div class="d-inline-block d-none" id="btns-header-save">
                  <button class="btn btn-outline-secondary btn-sm mr-1" id="btn-cancel-header">{{ __('Cancelar') }}</button>
                  <button class="btn btn-primary btn-sm" id="btn-salvar-header">{{ __('Salvar') }}</button>
                </div>
              </div>
            </div>
            <div id="obs-view" class="bg-light p-2 rounded">${cab.observacao || '—'}</div>
            <div id="obs-edit" class="d-none"><textarea id="obs-admin" class="form-control" rows="2">${escapeHtml(cab.observacao||'')}</textarea></div>
            <hr/>
            <p class="mb-2 mt-2"><strong>Itens</strong></p>
            <div class="table-responsive">
              <table class="table table-sm table-striped mb-2" id="tabela-itens-modal">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th class="text-center" style="width:120px">Quantidade</th>
                    <th class="text-right" style="width:160px">Preço unitário</th>
                    <th class="text-right" style="width:160px">Valor</th>
                  </tr>
                </thead>
                <tbody>${linhas}</tbody>
                <tfoot>
                  <tr>
                    <th colspan="3" class="text-right">Total</th>
                    <th class="text-right text-success">R$ ${formatarMoedaBR(totalGeral)}</th>
                  </tr>
                </tfoot>
              </table>
                     <div class="d-flex justify-content-between align-items-center">
                       <div>
                         <button class="btn btn-outline-success btn-sm d-none" id="btn-add-item-modal"><i class="fas fa-plus mr-1"></i>Incluir item</button>
                       </div>
                       <div class="text-right" id="area-salvar-admin"></div>
                     </div>
            </div>
            <hr/>
            <div class="form-group mb-2"> 
              <label for="msg_interacao" class="mb-1">Interagir com o solicitante</label>
              <textarea id="msg_interacao" class="form-control" rows="2" placeholder="Mensagem ao solicitante"></textarea>
              <div class="d-flex justify-content-between align-items-center mt-2"> 
                <div></div>
                <div id="acoes-autorizacao" class="d-none"> 
                  <button class="btn btn-success btn-sm mr-1" onclick="aprovarGrupo('${hash}')"><i class="fas fa-check mr-1"></i>{{ __('Aprovar') }}</button> 
                  <button class="btn btn-danger btn-sm mr-1" onclick="rejeitarGrupo('${hash}')"><i class="fas fa-times mr-1"></i>{{ __('Rejeitar') }}</button> 
                </div>
                <div>
                  <button class="btn btn-outline-primary btn-sm" onclick="enviarMensagemGrupo('${hash}')"><i class="fas fa-paper-plane mr-1"></i>Enviar mensagem</button> 
                </div>
              </div> 
            </div>
            <p class="mb-1"><strong>Interações</strong></p>
            <ul class="list-group" id="lista-interacoes">${interacoes.map(int => `
              <li class='list-group-item'>
                <strong>${int.usuario}</strong>
                <span class='text-muted'>${formatarDataBR(int.created_at)}</span><br>
                ${formatTipo(int.tipo)}${int.mensagem ? ': '+escapeHtml(int.mensagem) : ''}
              </li>
            `).join('') || '<li class=\'list-group-item text-muted\'>Sem interações</li>'}</ul>
          </div>
          <div class="modal-footer"> 
            <button class="btn btn-secondary" data-dismiss="modal">{{ __('Fechar') }}</button> 
          </div>
        </div></div>
      </div>`;
    try { $('#modalGrupo').modal('hide'); } catch(e) {}
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('#modalGrupo').remove();
    $('body').append(html);
    $('#modalGrupo').modal('show');
    // Mostrar botões Aprovar/Rejeitar somente para administradores
    try {
      $.get('/api/usuario/permissoes', function(r){
        let isAdmin = false;
        try {
          if (r && r.success) {
            const perms = Array.isArray(r.permissoes) ? r.permissoes : [];
            isAdmin = perms.includes('administrador') || r.perfil === 'Admin' || r.is_admin === true;
          }
        } catch(e) {}
        // Fallback: se o badge no topo da página indica "Administrador", assume admin
        if (!isAdmin) {
          const badge = $('body').find(':contains("Administrador")').first();
          if (badge.length) isAdmin = true;
        }
        if (isAdmin) { $('#acoes-autorizacao').removeClass('d-none'); $('#btn-add-item-modal').removeClass('d-none'); }
        window.__isAdmin = !!isAdmin;
        if (window.__isAdmin) { habilitarEdicaoAdmin(); }
      });
    } catch(e) { window.__isAdmin = true; habilitarEdicaoAdmin(); }
  });
}

function habilitarEdicaoAdmin(){
  const $tbl = $('#tabela-itens-modal');
  if ($tbl.length===0) return;

  // Transformar células em inputs se ainda não estiverem
  if ($tbl.find('.js-nome').length===0) {
    $tbl.find('tbody tr').each(function(){
      const $tr = $(this);
      const id = Number($tr.data('id'))||0;
      const nome = $tr.find('td').eq(0).text().trim();
      const qtd = Number(($tr.find('td').eq(1).text()||'').replace(/\D/g,'')||0);
      // Campo com botão de excluir
      $tr.find('td').eq(0).html(`
        <div class="d-flex align-items-center justify-content-between">
          <div class="flex-grow-1 pr-2">
            <input type="text" class="form-control form-control-sm js-nome" data-id="${id}" value="${escapeHtml(nome)}">
          </div>
          <button type="button" class="btn btn-outline-danger btn-sm ml-1 js-excluir-item" title="Excluir item" data-id="${id}">
            <i class="fas fa-trash"></i>
          </button>
        </div>`);
      $tr.find('td').eq(1).html(`<input type="number" min="1" class="form-control form-control-sm text-center js-qtd" data-id="${id}" value="${qtd||1}">`);
    });
  }

  // Botão salvar
  if ($('#btn-salvar-itens').length===0) {
    $('#area-salvar-admin').html('<button class="btn btn-primary btn-sm" id="btn-salvar-itens"><i class="fas fa-save mr-1"></i>Salvar itens</button>');
    $('#header-edit-actions-view').removeClass('d-none');
  }

  function recalcTotalGeral(){
    let soma = 0;
    $tbl.find('tbody tr').each(function(){
      const txt = $(this).find('.js-total').text().replace(/[^0-9,.-]/g,'').replace(/\./g,'').replace(',', '.');
      const n = parseFloat(txt)||0; soma += n;
    });
    $tbl.find('tfoot th:last').text('R$ '+formatarMoedaBR(soma));
  }

  // Recalcular ao digitar
  $tbl.off('input.edicao').on('input.edicao', '.js-qtd, .js-nome', function(){
    const $tr = $(this).closest('tr');
    const qtd = Number($tr.find('.js-qtd').val()||0);
    const nome = ($tr.find('.js-nome').val()||'').trim();
    if (!nome || qtd<=0) return;
    $.get('/api/estoque/preco', { nome }, function(resp){
      const unit = Number(resp && resp.valor_unitario ? resp.valor_unitario : 0);
      const total = unit*qtd;
      $tr.find('.js-unit').text(unit>0 ? 'R$ '+formatarMoedaBR(unit) : '—');
      $tr.find('.js-total').text(total>0 ? 'R$ '+formatarMoedaBR(total) : '—');
      recalcTotalGeral();
    });
  });

  // Autocomplete de produtos (nome/código)
  $tbl.off('keyup.autocomplete').on('keyup.autocomplete', '.js-nome', function(e){
    const $inp = $(this);
    const termo = ($inp.val()||'').trim();
    if (termo.length < 3 && !/^\d+$/.test(termo)) { removerSugestoes($inp); return; }
    $.get('/api/estoque-pedido/produtos/buscar', { q: termo }, function(r){
      if (!(r && r.success)) return; mostrarSugestoes($inp, r.data||[]);
    });
  });

  function mostrarSugestoes($input, itens){
    removerSugestoes($input);
    if (!itens.length) return;
    const $list = $('<div class="autocomplete-list list-group position-absolute w-100" style="z-index:1051;"></div>');
    itens.forEach(p=>{
      const label = `${p.codigo ? ('['+p.codigo+'] ') : ''}${p.produto}`;
      const $item = $(`<button type="button" class="list-group-item list-group-item-action py-1">${label}<span class="float-right">${p.valor_unitario?('R$ '+formatarMoedaBR(p.valor_unitario)):'—'}</span></button>`);
      $item.on('click', function(){
        $input.val(p.produto).trigger('input');
        removerSugestoes($input);
      });
      $list.append($item);
    });
    $input.after($list);
  }
  function removerSugestoes($input){ $input.siblings('.autocomplete-list').remove(); }

  $(document).on('click', function(e){
    if (!$(e.target).closest('.autocomplete-list, .js-nome').length) {
      $('.autocomplete-list').remove();
    }
  });

  // Salvar alterações
  $('#btn-salvar-itens').off('click.edicao').on('click.edicao', function(){
    const itens = [];
    $tbl.find('tbody tr').each(function(){
      const id = Number($(this).data('id'));
      const nome = ($(this).find('.js-nome').val()||'').trim();
      const qtd = Number($(this).find('.js-qtd').val()||0);
      if (id && nome && qtd>0) itens.push({ id, produto_nome: nome, quantidade: qtd });
    });
    if (itens.length===0) { Swal.fire('Atenção','Nada para salvar.','info'); return; }
    Swal.fire({ title:'Salvando...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
    $.ajax({
      url:`/api/pedidos-agrupado/${window.__hashGrupo}/itens`,
      method:'PUT',
      contentType:'application/json',
      data: JSON.stringify({ itens }),
      success:function(r){
        Swal.close();
        if (r && r.success) {
          Swal.fire({ icon:'success', title:'Alterações salvas', timer:1500, showConfirmButton:false });
          abrirGrupo(window.__hashGrupo);
        } else {
          Swal.fire('Atenção', (r && r.message) ? r.message : 'Não foi possível salvar.', 'warning');
        }
      },
      error:function(xhr){
        Swal.close();
        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao salvar.';
        Swal.fire('Erro', msg, 'error');
      }
    });
  });

  // Botão incluir item no modal
  $('#btn-add-item-modal').off('click.addm').on('click.addm', function(){
    Swal.fire({
      title: 'Adicionar item',
      html: '<div style="text-align: left; padding: 10px;"><div class="form-group mb-3"><label class="form-label fw-bold mb-2">Produto:</label><input id="swm-nome" type="text" class="form-control" placeholder="Digite o nome do produto ou código..." autocomplete="off" autocapitalize="off" spellcheck="false" style="font-size: 14px; padding: 8px 12px;"></div><div class="form-group mb-2"><label class="form-label fw-bold mb-2">Quantidade:</label><input id="swm-qtd" type="number" min="1" class="form-control" placeholder="Quantidade" value="1" style="font-size: 14px; padding: 8px 12px;"></div></div>',
      focusConfirm: false,
      width: '500px',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-plus mr-2"></i>Adicionar',
      cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
      buttonsStyling: false,
      customClass: {
        confirmButton: 'btn btn-success btn-lg px-4 mr-2',
        cancelButton: 'btn btn-secondary btn-lg px-4'
      },
      willOpen: function(){
        // Suprimir warnings de aria-hidden ANTES do render do popup
        try {
          if (!window.__origConsole) {
            window.__origConsole = { warn: console.warn, error: console.error };
            const supress = msg => typeof msg === 'string' && msg.toLowerCase().includes('aria-hidden');
            console.warn = function(){ if (supress(arguments[0])) return; return window.__origConsole.warn.apply(console, arguments); };
            console.error = function(){ if (supress(arguments[0])) return; return window.__origConsole.error.apply(console, arguments); };
          }
        } catch(e) {}
        try { $(document).off('focusin.bs.modal'); } catch(e) {}
        try { $('#modalGrupo').attr('aria-hidden','false'); } catch(e) {}
      },
      didOpen: function(){
        // Evita warnings de aria-hidden ao usar SweetAlert sobre modal Bootstrap
        try { $('#modalGrupo').attr('aria-hidden', 'false'); } catch(e) {}
        try { $(document).off('focusin.bs.modal'); } catch(e) {}
        // Autocomplete a partir de estoque_pedido (por nome ou código)
        var timer=null;
        var $inp = $('#swm-nome');
        $inp.prop('disabled', false).prop('readonly', false);
        setTimeout(function(){ try { $inp.trigger('focus')[0].setSelectionRange($inp.val().length,$inp.val().length); } catch(e) {} }, 0);
        function remover(){ $inp.siblings('.autocomplete-list').remove(); }
        function sugerir(lista){
          remover();
          if (!lista || !lista.length) return;
          var $list = $('<div class="autocomplete-list list-group w-100" style="max-height:200px; overflow:auto; border:1px solid #ced4da; border-radius:.375rem; margin-top:8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></div>');
          lista.forEach(function(p){
            var label = (p.codigo ? ('['+p.codigo+'] ') : '') + p.produto;
            var vu = (p.valor_unitario!=null) ? ('R$ '+Number(p.valor_unitario).toLocaleString('pt-BR',{minimumFractionDigits:2})) : '—';
            var $it = $('<button type="button" class="list-group-item list-group-item-action py-2 px-3 d-flex justify-content-between align-items-center" style="border: none; border-bottom: 1px solid #f0f0f0; transition: background-color 0.15s ease;"></button>').html('<span style="font-size: 14px; font-weight: 500;">'+label+'</span><span class="badge badge-light" style="font-size: 12px;">'+vu+'</span>');
            $it.on('click', function(){ $inp.val(p.produto); remover(); $('#swm-qtd').focus(); });
            $list.append($it);
          });
          $inp.after($list);
        }
        $inp.on('input', function(){
          var termo = ($inp.val()||'').trim();
          if (timer) clearTimeout(timer);
          if (termo.length < 3 && !/^\d+$/.test(termo)) { remover(); return; }
          timer = setTimeout(function(){
            $.get('/api/estoque-pedido/produtos/buscar', { q: termo }, function(r){
              sugerir((r && r.success) ? (r.data||[]) : []);
            });
          }, 250);
        });
        setTimeout(function(){ try { $inp.trigger('focus'); } catch(e) {} }, 0);
        $(document).on('click.swm', function(e){ if (!$(e.target).closest('#swm-nome, .autocomplete-list').length) remover(); });
      },
      willClose: function(){
        // Restaurar console
        try {
          if (window.__origConsole) {
            console.warn = window.__origConsole.warn;
            console.error = window.__origConsole.error;
            delete window.__origConsole;
          }
        } catch(e) {}
      },
      preConfirm: () => {
        const produto_nome = ($('#swm-nome').val()||'').trim();
        const quantidade = Number($('#swm-qtd').val()||0);
        if (!produto_nome || quantidade<=0) { Swal.showValidationMessage('Informe produto e quantidade.'); return false; }
        return { produto_nome, quantidade };
      }
    }).then(function(res){
      if (!res.isConfirmed || !res.value) return;
      const payload = res.value;
      Swal.fire({ title:'Adicionando...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
      $.ajax({
        url:`/api/pedidos-agrupado/${window.__hashGrupo}/itens`, method:'POST', contentType:'application/json', data: JSON.stringify(payload),
        success:function(r){
          Swal.close();
          if (r && r.success) {
            Swal.fire({ icon:'success', title:'Item adicionado', timer:1400, showConfirmButton:false });
            abrirGrupo(window.__hashGrupo);
            carregarPendentes();
          } else {
            Swal.fire('Atenção', (r && r.message) ? r.message : 'Não foi possível adicionar o item.', 'warning');
          }
        },
        error:function(xhr){
          Swal.close();
          const msg=(xhr.responseJSON&&xhr.responseJSON.message)?xhr.responseJSON.message:'Erro ao adicionar item.';
          Swal.fire('Erro', msg, 'error');
        }
      });
    });
  });

  // Excluir item (somente Admin)
  $tbl.off('click.delItem').on('click.delItem', '.js-excluir-item', function(){
    const id = Number($(this).data('id'))||0;
    if (!id) return;
    Swal.fire({
      title:'Excluir item?',
      text:'Esta ação removerá apenas este item do pedido.',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Sim, excluir',
      cancelButtonText:'Cancelar'
    }).then(function(res){
      if(!res.isConfirmed) return;
      Swal.fire({ title:'Excluindo...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
      $.ajax({
        url:`/api/pedidos/${id}`,
        method:'DELETE',
        success:function(r){
          Swal.close();
          if(r && r.success){
            Swal.fire({ icon:'success', title:'Item excluído', timer:1400, showConfirmButton:false });
            // Recarregar detalhes do grupo após exclusão
            abrirGrupo(window.__hashGrupo);
            // Atualiza lista de pendentes (podem zerar itens do grupo)
            carregarPendentes();
          } else {
            Swal.fire('Atenção', (r && r.message) ? r.message : 'Não foi possível excluir o item.', 'warning');
          }
        },
        error:function(xhr){
          Swal.close();
          const msg=(xhr.responseJSON&&xhr.responseJSON.message)?xhr.responseJSON.message:'Erro ao excluir item.';
          Swal.fire('Erro', msg, 'error');
        }
      });
    });
  });

  // --------- Cabeçalho inline: mostrar/editar sem a faixa extra ---------
  let ccTimer=null, ccSelecionadoId = null;
  // Exibir controles de edição
  $('#btn-edit-header').off('click').on('click', function(){
    $('#obs-view').addClass('d-none');
    $('#obs-edit, #btns-header-save').removeClass('d-none');
    // preparar CC input
    const atual = $('#cc-view').text().trim();
    if ($('#cc-admin').length===0) {
      // inserir input ao lado do texto, sobrepondo visualmente
      const inp = $('<input id="cc-admin" class="form-control form-control-sm d-inline-block ml-2" style="max-width:280px" placeholder="Centro de Custo">').val(atual);
      $('#cc-view').after(inp);
    }
    // inserir selects rota/roteirização
    if ($('#rota-admin').length===0) {
      const selR = $('<select id="rota-admin" class="form-control form-control-sm d-inline-block ml-2" style="max-width:260px"><option value="">Selecione...</option></select>');
      $('#rota-view').after(selR);
    }
    if ($('#rot-admin').length===0) {
      const selRt = $('<select id="rot-admin" class="form-control form-control-sm d-inline-block ml-2" style="max-width:260px"><option value="">Selecione...</option></select>');
      $('#rot-view').after(selRt);
    }

    // carregar listas conforme valores atuais
    const ccIdAtual = $('#cc-view').data('ccid')||null;
    if (ccIdAtual) { carregarRotasCC(ccIdAtual, $('#rota-view').data('rotaid')||null, $('#rot-view').data('rotid')||null); }
  });

  $('#btn-cancel-header').off('click').on('click', function(){ abrirGrupo(window.__hashGrupo); });

  // Autocomplete Centro de Custo
  $(document).off('input.cc').on('input.cc', '#cc-admin', function(){
    const termo = $(this).val().trim(); ccSelecionadoId = null;
    if (ccTimer) clearTimeout(ccTimer);
    $('.cc-sug').remove();
    if (termo.length < 3) return;
    const $host = $(this);
    ccTimer = setTimeout(function(){
      $.get('/api/centro-custos/buscar', { termo }, function(r){
        const $list = $('<div class="cc-sug dropdown-menu show" style="max-height:180px;overflow:auto"></div>');
        (r && r.data ? r.data : []).forEach(cc=>{
          const $it = $(`<button type="button" class="dropdown-item">${cc.nome}</button>`);
          $it.on('click', function(){ $host.val(cc.nome); ccSelecionadoId = cc.id; $('.cc-sug').remove(); carregarRotasCC(cc.id); });
          $list.append($it);
        });
        $host.after($list);
      });
    }, 300);
  });

  function carregarRotasCC(ccId, rotaIdSel=null, rotSel=null){
    $.get('/api/rotas/por-centro-custo', { centro_custo_id: ccId }, function(r){
      const $r = $('#rota-admin'); $r.empty().append('<option value="">Selecione...</option>');
      (r && r.data ? r.data : []).forEach(ro => $r.append(`<option value="${ro.id}">${ro.nome_rota}</option>`));
      if (rotaIdSel) { $r.val(String(rotaIdSel)); carregarRoteirizacao(rotaIdSel, rotSel); }
    });
  }
  function carregarRoteirizacao(rotaId, rotSel=null){
    $.get('/api/roteirizacoes/por-rota', { rota_id: rotaId }, function(r){
      const $rt = $('#rot-admin'); $rt.empty().append('<option value="">Selecione...</option>');
      (r && r.data ? r.data : []).forEach(rt => $rt.append(`<option value="${rt.id}">${rt.nome}</option>`));
      if (rotSel) $rt.val(String(rotSel));
    });
  }
  $(document).off('change.rota').on('change.rota', '#rota-admin', function(){
    const rid = $(this).val(); if (!rid) return; carregarRoteirizacao(rid);
  });

  $(document).off('click.saveHeader').on('click.saveHeader', '#btn-salvar-header', function(){
    const payload = {
      centro_custo_id: ccSelecionadoId,
      rota_id: $('#rota-admin').val() || null,
      roteirizacao_id: $('#rot-admin').val() || null,
      observacao: ($('#obs-admin').val()||'').trim()
    };
    // Remover nulos/vazios, exceto 'observacao' (permitir limpar observação)
    Object.keys(payload).forEach(k => { if (k !== 'observacao' && (payload[k]===null || payload[k]==='')) delete payload[k]; });
    if (Object.keys(payload).length===0) { Swal.fire('Atenção','Nada para salvar.','info'); return; }
    Swal.fire({ title:'Salvando...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
    $.ajax({
      url:`/api/pedidos-agrupado/${window.__hashGrupo}/cabecalho`, method:'PUT', contentType:'application/json', data: JSON.stringify(payload),
      success:function(r){ Swal.close(); if(r&&r.success){ Swal.fire({icon:'success',title:'Cabeçalho atualizado',timer:1400,showConfirmButton:false}); abrirGrupo(window.__hashGrupo); } else { Swal.fire('Atenção', (r&&r.message)?r.message:'Falha ao salvar.', 'warning'); } },
      error:function(xhr){ Swal.close(); const msg=(xhr.responseJSON&&xhr.responseJSON.message)?xhr.responseJSON.message:'Erro ao salvar.'; Swal.fire('Erro', msg, 'error'); }
    });
  });
}

function enviarMensagemGrupo(hash){
  const mensagem = ($('#msg_interacao').val()||'').trim();
  if(!mensagem){ Swal.fire('Atenção','Digite uma mensagem.','warning'); return; }
  $.post(`/api/pedidos-agrupado/${hash}/mensagem`, {
    mensagem: mensagem,
    _token: $('meta[name="csrf-token"]').attr('content')
  }, function(r){
    if(r && r.success){
      // após enviar, recarrega detalhes para listar interação
      $.get(`/api/pedidos-agrupado/${hash}`, function(resp){
        if(resp.success){
          const interacoes = resp.data.interacoes||[];
          const lis = interacoes.map(int => `
            <li class='list-group-item'>
              <strong>${int.usuario}</strong>
              <span class='text-muted'>${formatarDataBR(int.created_at)}</span><br>
              ${formatTipo(int.tipo)}${int.mensagem ? ': '+escapeHtml(int.mensagem) : ''}
            </li>`).join('') || "<li class='list-group-item text-muted'>Sem interações</li>";
          $('#lista-interacoes').html(lis);
          $('#msg_interacao').val('');
          Swal.fire({ icon: 'success', title: 'Mensagem enviada', timer: 1500, showConfirmButton: false });
        }
      });
    } else {
      Swal.fire('Erro', (r && r.message) ? r.message : 'Não foi possível enviar.', 'error');
    }
  }).fail(function(xhr){
    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Falha ao enviar mensagem';
    Swal.fire('Erro', msg, 'error');
  });
}

function formatTipo(t){
  if(!t) return '';
  if(t === 'aprovacao') return '<span class="badge badge-success">APROVAÇÃO</span>';
  if(t === 'rejeicao') return '<span class="badge badge-danger">REJEIÇÃO</span>';
  if(t === 'comentario') return '<span class="badge badge-info">COMENTÁRIO</span>';
  return `<span class="badge badge-secondary">${(t||'').toUpperCase()}</span>`;
}

function escapeHtml(str){
  return (str||'').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
}

function aprovarGrupo(hash){
  const mensagem = $('#msg_interacao').val();
  Swal.fire({
    title: 'Confirmar aprovação',
    text: 'Deseja aprovar este pedido de compras?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, aprovar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if(!result.isConfirmed) return;
    Swal.fire({
      title: 'Processando...',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });
    $.ajax({
      url:`/api/pedidos-agrupado/${hash}/aprovar`, method:'PUT', data:{mensagem},
      success:function(r){
        Swal.close();
        if(r && r.success){
          $('#modalGrupo').modal('hide');
          Swal.fire('Aprovado!', 'Pedido aprovado com sucesso.', 'success');
          carregarPendentes();
        } else {
          Swal.fire('Atenção', (r && r.message) ? r.message : 'Não foi possível aprovar.', 'warning');
        }
      },
      error:function(xhr){
        Swal.close();
        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao aprovar pedido.';
        Swal.fire('Erro', msg, 'error');
      }
    });
  });
}

function rejeitarGrupo(hash){
  const mensagem = $('#msg_interacao').val();
  Swal.fire({
    title: 'Confirmar rejeição',
    text: 'Deseja rejeitar este pedido de compras?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, rejeitar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if(!result.isConfirmed) return;
    Swal.fire({
      title: 'Processando...',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });
    $.ajax({
      url:`/api/pedidos-agrupado/${hash}/rejeitar`, method:'PUT', data:{mensagem},
      success:function(r){
        Swal.close();
        if(r && r.success){
          $('#modalGrupo').modal('hide');
          Swal.fire('Rejeitado!', 'Pedido rejeitado com sucesso.', 'success');
          carregarPendentes();
        } else {
          Swal.fire('Atenção', (r && r.message) ? r.message : 'Não foi possível rejeitar.', 'warning');
        }
      },
      error:function(xhr){
        Swal.close();
        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao rejeitar pedido.';
        Swal.fire('Erro', msg, 'error');
      }
    });
  });
}
</script>
<style>
.tr-prio-alta { border-left: 4px solid #dc3545; }
.tr-prio-media { border-left: 4px solid #ffc107; }
.tr-prio-baixa { border-left: 4px solid #6c757d; }

/* Melhorias visuais do modal Adicionar item */
.swal2-popup { border-radius: 10px !important; }
.autocomplete-list .list-group-item:hover { background-color: #f8f9fa !important; }
.autocomplete-list .list-group-item:first-child { border-top-left-radius: .375rem; border-top-right-radius: .375rem; }
.autocomplete-list .list-group-item:last-child { border-bottom-left-radius: .375rem; border-bottom-right-radius: .375rem; border-bottom: none; }

/* Ajustes responsivos (somente mobile) */
@media (max-width: 576px) {
  /* Tabela da listagem */
  #tabela-pendentes th, #tabela-pendentes td { padding: .5rem; font-size: 12px; }
  #tabela-pendentes .btn { padding: .25rem .5rem; }
  /* Esconder colunas menos críticas no mobile: 3=Solicitante, 7=Centro Custo */
  #tabela-pendentes th:nth-child(3), #tabela-pendentes td:nth-child(3) { display: none; }
  #tabela-pendentes th:nth-child(7), #tabela-pendentes td:nth-child(7) { display: none; }

  /* Modal: tornar corpo rolável e ocultar Preço unitário para caber melhor */
  #modalGrupo .modal-dialog { margin: .5rem; }
  #modalGrupo .modal-body { max-height: calc(100vh - 160px); overflow-y: auto; }
  #modalGrupo table th, #modalGrupo table td { font-size: 12px; }
  /* Na tabela do modal: esconder a coluna 3 (Preço unitário) */
  #modalGrupo table thead th:nth-child(3),
  #modalGrupo table tbody td:nth-child(3),
  #modalGrupo table tfoot th:nth-child(3) { display: none; }
  /* Ajustar colspan do total quando a coluna 3 está oculta */
  #modalGrupo table tfoot th[colspan="3"] { display: none; }
  #modalGrupo table tfoot tr:last-child th:first-child { display: table-cell; text-align: right; }
}
</style>
@stop


