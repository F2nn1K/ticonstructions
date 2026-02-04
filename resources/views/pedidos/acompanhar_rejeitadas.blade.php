@extends('adminlte::page')
@section('title', 'Meus Rejeitados')
@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-times text-danger mr-2"></i>Meus Rejeitados</h1>
@stop
@section('content')
<div class="card card-danger shadow-sm">
  <div class="card-body p-0">
    <div class="px-3 py-2 bg-danger text-white d-flex justify-content-between align-items-center">
      <strong>Solicitações Rejeitadas</strong>
      <a href="{{ route('pedidos.acompanhar') }}" class="btn btn-outline-light btn-sm text-white"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
    </div>
    <!-- Desktop/Tablet -->
    <div class="d-none d-md-block table-responsive">
      <table class="table table-striped table-hover mb-0" id="tabela">
        <thead><tr><th>Nº Pedido</th><th>Data</th><th>Centro Custo</th><th>Rota</th><th>Roteirização</th><th>Itens</th><th>Qtd Total</th><th>Prioridade</th><th>Ações</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
    <!-- Mobile: lista de cartões -->
    <div class="d-block d-md-none p-2">
      <div id="listaRejeitadasMobile" class="list-group"></div>
    </div>
  </div>
</div>

@include('pedidos.partials_modal_acompanhar')
@stop
@section('js')
<script>
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

$(function(){ carregar(); });
function carregar(){
  $.get('/api/pedidos/acompanhar/lista', function(resp){
    const tbody = $('#tabela tbody'); tbody.empty();
    const lista = $('#listaRejeitadasMobile'); lista.empty();
    const dados = (resp.data||[]).filter(d => (d.status||'pendente')==='rejeitado');
    if(dados.length===0){
      tbody.append('<tr><td colspan="9" class="text-center text-muted">Nenhum registro</td></tr>');
      lista.html('<div class="list-group-item text-center text-muted">Nenhum registro</div>');
      return;
    }
    dados.forEach(p => {
      tbody.append(row(p));
      const card = `
        <div class="list-group-item">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="font-weight-bold">${p.num_pedido}</div>
              <small class="text-muted">${formatarDataBR(p.data_solicitacao)}</small>
            </div>
            <button class="btn btn-primary btn-sm" onclick="abrir('${p.grupo_hash}')"><i class="fas fa-search mr-1"></i> Ver</button>
          </div>
          <div class="mt-2">
            <div class="d-flex justify-content-between">
              <div><small>Centro Custo</small><br><strong>${p.centro_custo_nome||'—'}</strong></div>
              <div class="text-right"><small>Itens</small><br><strong>${p.itens} / ${p.quantidade_total||0}</strong></div>
            </div>
            <div class="d-flex justify-content-between mt-1">
              <div><small>Rota</small><br>${p.rota_nome||'—'}</div>
              <div class="text-right"><small>Prioridade</small><br><span class="badge badge-${p.prioridade}">${(p.prioridade||'').toUpperCase()}</span></div>
            </div>
          </div>
        </div>`;
      lista.append(card);
    });
  });
}
function row(p){
  return `<tr>
    <td><span class="badge badge-dark">${p.num_pedido}</span></td>
    <td>${formatarDataBR(p.data_solicitacao)}</td>
    <td>${p.centro_custo_nome||'—'}</td>
    <td>${p.rota_nome||'—'}</td>
    <td>${p.roteirizacao_nome||'—'}</td>
    <td>${p.itens} itens</td>
    <td>${p.quantidade_total||0}</td>
    <td><span class="badge badge-${p.prioridade}">${(p.prioridade||'').toUpperCase()}</span></td>
    <td><button class="btn btn-outline-primary btn-sm" onclick="abrir('${p.grupo_hash}')"><i class="fas fa-search"></i></button></td>
  </tr>`;
}
function abrir(hash){
  $.get(`/api/pedidos/acompanhar/${hash}`, function(resp){
    if(!resp.success) return; const h=resp.data.cabecalho, itens=resp.data.itens||[], ints=resp.data.interacoes||[];
    const esc = s=>String(s||'').replace(/[&<>\"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    $('#num').text(h.num_pedido); $('#cc').text(esc(h.centro_custo_nome||'—')); $('#pri').text((h.prioridade||'').toUpperCase());
    $('#itens').html(itens.map(i=>`<li class=\"list-group-item d-flex justify-content-between\"><span>${esc(i.produto_nome)}</span><span class=\"badge badge-secondary\">${i.quantidade}</span></li>`).join(''));
    $('#ints').html(ints.map(it=>`<li class=\"list-group-item\"><strong>${esc(it.usuario)}</strong> — <span class=\"text-muted\">${formatarDataBR(it.created_at)}</span><br>${(it.tipo||'').toUpperCase()}${it.mensagem?': '+esc(it.mensagem):''}</li>`).join('')||'<li class=\"list-group-item text-muted\">Sem interações</li>');
    $('#modal').modal('show');
  });
}
</script>
@stop

