@extends('adminlte::page')

@section('title', __('Bloquear Itens'))

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-ban text-danger mr-2"></i>{{ __('Bloquear Itens') }}</h1>
@stop

@section('content')
<!-- Card Seleção de Usuário -->
<div class="card card-primary shadow-sm">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-user mr-2"></i>{{ __('Selecionar Usuário') }}</h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label for="inp-user">Usuário</label>
          <div class="input-group">
            <input id="inp-user" type="text" class="form-control" placeholder="Digite o nome ou e-mail do usuário">
            <div class="input-group-append">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
          </div>
          <input id="inp-user-id" type="hidden">
          <div id="user-sug" class="list-group position-absolute w-100" style="z-index:1051;"></div>
        </div>
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <div class="alert alert-info w-100 mb-0">
          <i class="fas fa-info-circle mr-2"></i>
          <strong>Instruções:</strong> Digite o nome ou e-mail do usuário para visualizar e gerenciar os bloqueios de produtos.
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Card Gestão de Bloqueios -->
<div class="card card-warning shadow-sm" id="card-bloqueios" style="display:none;">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-shield-alt mr-2"></i>Bloqueios do Usuário: <span id="nome-usuario-selecionado"></span></h3>
  </div>
  <div class="card-body">
    <!-- Adicionar Produto -->
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="form-group">
          <label for="inp-prod">Adicionar Produto para Bloqueio</label>
          <div class="input-group">
            <input id="inp-prod" type="text" class="form-control" placeholder="Digite o nome, descrição ou código do produto">
            <input id="inp-prod-id" type="hidden">
            <div class="input-group-append">
              <button id="btn-add" class="btn btn-primary" disabled>
                <i class="fas fa-plus mr-1"></i>Bloquear Produto
              </button>
            </div>
          </div>
          <div id="prod-sug" class="list-group position-absolute w-100" style="z-index:1051;"></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="info-box bg-danger">
          <span class="info-box-icon"><i class="fas fa-ban"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Bloqueados</span>
            <span class="info-box-number" id="total-bloqueados">0</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de Produtos Bloqueados -->
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width: 100px;">Código</th>
            <th>Produto</th>
            <th>Unidade</th>
            <th style="width: 120px;" class="text-center">Ações</th>
          </tr>
        </thead>
        <tbody id="tbody-bloq">
          <tr><td colspan="4" class="text-center text-muted p-4">Nenhum produto bloqueado</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
function esc(s){ return String(s||'').replace(/[&<>\"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

// Autocomplete Usuário
let userTimer=null; const $user = $('#inp-user');
$user.on('input', function(){
  const q = $(this).val().trim(); $('#inp-user-id').val('');
  if (userTimer) clearTimeout(userTimer); $('#user-sug').empty().hide();
  $('#card-bloqueios').hide(); // esconder card de bloqueios
  if (q.length < 2) return;
  userTimer = setTimeout(function(){
    $.get('/api/bloq/usuarios', { q }, function(r){
      const $box = $('#user-sug'); $box.empty();
      (r && r.data ? r.data : []).forEach(u=>{
        const $it = $(`<button type="button" class="list-group-item list-group-item-action py-1">${esc(u.name)} <small class="text-muted">${esc(u.email||'')}</small></button>`);
        $it.on('click', function(){ 
          $('#inp-user-id').val(u.id); 
          $user.val(u.name); 
          $('#nome-usuario-selecionado').text(u.name);
          $box.empty().hide(); 
          $('#card-bloqueios').show(); 
          carregarBloqueios(u.id); 
          habilitarAdd(); 
        });
        $box.append($it);
      });
      if ($box.children().length) $box.show();
    });
  }, 300);
});

// Autocomplete Produto
let prodTimer=null; const $prod = $('#inp-prod');
$prod.on('input', function(){
  const q = $(this).val().trim(); $('#inp-prod-id').val('');
  if (prodTimer) clearTimeout(prodTimer); $('#prod-sug').empty().hide();
  if (q.length < 2 && !/^\d+$/.test(q)) { $('#btn-add').prop('disabled', true); return; }
  prodTimer = setTimeout(function(){
    $.get('/api/bloq/produtos', { q }, function(r){
      const $box = $('#prod-sug'); $box.empty();
      (r && r.data ? r.data : []).forEach(p=>{
        const label = `${p.codigo ? '['+p.codigo+'] ' : ''}${p.produto}`;
        const $it = $(`<button type="button" class="list-group-item list-group-item-action py-1">${esc(label)}</button>`);
        $it.on('click', function(){ $('#inp-prod-id').val(p.id); $prod.val(label); $box.empty().hide(); habilitarAdd(); });
        $box.append($it);
      });
      if ($box.children().length) $box.show();
    });
  }, 300);
});

function habilitarAdd(){
  const temUsuario = $('#inp-user-id').val();
  const temProdutoId = $('#inp-prod-id').val();
  const textoProduto = $('#inp-prod').val().trim();
  const ok = temUsuario && (temProdutoId || (textoProduto.length >= 2));
  $('#btn-add').prop('disabled', !ok);
}

// Listar bloqueios do usuário
function carregarBloqueios(userId){
  $('#tbody-bloq').html('<tr><td colspan="4" class="text-center text-muted">Carregando...</td></tr>');
  $.get(`/api/bloq/${userId}/listar`, function(r){
    const $tb = $('#tbody-bloq'); $tb.empty();
    if (!(r && r.success) || !(r.data && r.data.length)) {
      $tb.html('<tr><td colspan="4" class="text-center text-muted p-4">Nenhum produto bloqueado</td></tr>'); 
      $('#total-bloqueados').text('0');
      return;
    }
    $('#total-bloqueados').text(r.data.length);
    r.data.forEach(it=>{
      const $tr = $(`
        <tr>
          <td>${esc(it.codigo||'—')}</td>
          <td>${esc(it.produto)}</td>
          <td>${esc(it.descricao||'UN')}</td>
          <td class="text-center">
            <button class="btn btn-danger btn-sm js-desb" data-uid="${it.user_id}" data-pid="${it.produto_id}" title="Desbloquear produto">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
      `);
      $tb.append($tr);
    });
  });
}

// Adicionar bloqueio
$('#btn-add').on('click', function(){
  const userId = $('#inp-user-id').val();
  let prodId = $('#inp-prod-id').val();
  const texto = $('#inp-prod').val().trim();
  if (!userId) return;
  const $btn = $(this);
  $btn.prop('disabled', true);

  function efetivarBloqueio(pid){
    if (!pid) { alert('Selecione um produto válido.'); $btn.prop('disabled', false); return; }
    $.ajax({
      url:'/api/bloq', method:'POST', contentType:'application/json', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},
      data: JSON.stringify({ user_id: Number(userId), produto_id: Number(pid) }),
      success: function(){
        carregarBloqueios(userId);
        $('#inp-prod-id').val('');
        $('#inp-prod').val('');
        habilitarAdd();
      },
      error: function(){ alert('Falha ao bloquear produto.'); $btn.prop('disabled', false); }
    });
  }

  // Se já temos o ID do produto (selecionado na sugestão), bloqueia direto
  if (prodId) { efetivarBloqueio(Number(prodId)); return; }

  // Fallback: tentar resolver o produto digitado (por código [123] ou nome)
  if (!texto || texto.length < 2) { alert('Digite ao menos 2 caracteres do produto.'); $btn.prop('disabled', false); return; }
  let q = texto;
  const m = texto.match(/^\s*\[(\d+)\]/); // captura código no formato [123]
  if (m) { q = m[1]; }
  $.get('/api/bloq/produtos', { q }, function(r){
    const lista = (r && r.data) ? r.data : [];
    let escolhido = null;
    if (m) {
      escolhido = lista.find(p => String(p.codigo) === m[1]) || null;
    }
    if (!escolhido) {
      const alvo = texto.replace(/^\s*\[\d+\]\s*/, '').toLowerCase();
      escolhido = lista.find(p => String(p.produto||'').toLowerCase() === alvo) || lista[0] || null;
    }
    if (escolhido) {
      efetivarBloqueio(Number(escolhido.id));
    } else {
      alert('Produto não encontrado. Selecione na lista.');
      $btn.prop('disabled', false);
    }
  }).fail(function(){
    alert('Falha ao localizar produto.');
    $btn.prop('disabled', false);
  });
});

// Remover bloqueio
$(document).on('click', '.js-desb', function(){
  const uid = $(this).data('uid'); const pid = $(this).data('pid');
  $.ajax({ url:`/api/bloq/${uid}/${pid}`, method:'DELETE', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}, success: function(){ carregarBloqueios(uid); } });
});
</script>
@stop

