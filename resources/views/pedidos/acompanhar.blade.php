@extends('adminlte::page')

@section('title', 'Acompanhar Pedido')

@section('plugins.Sweetalert2', true)

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-eye text-primary mr-2"></i>Acompanhar Pedido</h1>
@stop

@section('content')
<div class="card card-primary shadow-sm">
  <div class="card-body p-0">
    <div class="px-3 py-2 bg-primary text-white d-flex justify-content-between align-items-center">
      <strong>Meus Pedidos (pendentes, aprovados e rejeitados)</strong>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0 align-middle" id="tabela-acompanhar">
        <thead>
          <tr>
            <th>Nº Pedido</th>
            <th>Data</th>
            <th>Centro Custo</th>
            <th>Itens</th>
            <th>Qtd Total</th>
            <th>Prioridade</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="8" class="text-center text-muted" id="empty-row">
              <i class="fas fa-info-circle fa-2x mb-2 text-secondary"></i><br>
              Nenhum pedido encontrado
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal detalhes (somente leitura) -->
<div class="modal fade" id="modalAcompanhar" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><i class="fas fa-file-alt mr-2"></i>Detalhes do Pedido <span id="ac-numero"></span></h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <p class="mb-1"><strong>Data:</strong> <span id="ac-data"></span></p>
            <p class="mb-1"><strong>Centro de Custo:</strong> <span id="ac-cc"></span></p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Prioridade:</strong> <span id="ac-prioridade"></span></p>
            <p class="mb-1"><strong>Status:</strong> <span id="ac-status"></span></p>
          </div>
        </div>
        <hr/>
        <p class="mb-2"><strong>Itens</strong></p>
        <ul class="list-group mb-3" id="ac-itens"></ul>
        <p class="mb-2"><strong>Interações</strong></p>
        <ul class="list-group" id="ac-interacoes"></ul>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">{{ __('Fechar') }}</button>
      </div>
    </div>
  </div>
</div>
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
    const tbody = $('#tabela-acompanhar tbody'); tbody.empty();
    if(!resp.success || !resp.data || resp.data.length===0){
      tbody.append('<tr><td colspan="8" class="text-center text-muted">Nenhum pedido encontrado</td></tr>');
      return;
    }
    resp.data.forEach(function(p){
      const tr = `
        <tr>
          <td><span class="badge badge-dark">${p.num_pedido}</span></td>
          <td>${formatarDataBR(p.data_solicitacao)}</td>
          <td>${p.centro_custo_nome||'—'}</td>
          <td>${p.itens} itens</td>
          <td>${p.quantidade_total||0}</td>
          <td><span class="badge badge-${p.prioridade}">${(p.prioridade||'').toUpperCase()}</span></td>
          <td>${(p.status||'pendente').toUpperCase()}</td>
          <td class="text-nowrap">
            <button class="btn btn-outline-primary btn-sm" onclick="abrir('${p.grupo_hash}')" title="Detalhar"><i class="fas fa-search"></i></button>
          </td>
        </tr>`;
      tbody.append(tr);
    });
  });
}

function abrir(hash){
  $.get(`/api/pedidos/acompanhar/${hash}`, function(resp){
    if(!resp.success) return;
    const h = resp.data.cabecalho; const itens = resp.data.itens || []; const ints = resp.data.interacoes || [];
    $('#ac-numero').text(h.num_pedido);
    $('#ac-data').text(h.data_solicitacao);
    $('#ac-cc').text(h.centro_custo_nome||'—');
    $('#ac-prioridade').text((h.prioridade||'').toUpperCase());
    $('#ac-status').text((h.aprovacao||'pendente').toUpperCase());
    $('#ac-itens').html(itens.map(i=>`<li class="list-group-item d-flex justify-content-between"><span>${i.produto_nome}</span><span class="badge badge-secondary">${i.quantidade}</span></li>`).join(''));
    $('#ac-interacoes').html(ints.map(it=>`<li class="list-group-item"><strong>${it.usuario}</strong> — <span class="text-muted">${formatarDataBR(it.created_at)}</span><br>${formatTipo(it.tipo)}${it.mensagem ? ': '+escapeHtml(it.mensagem) : ''}</li>`).join('') || '<li class="list-group-item text-muted">Sem interações</li>');
    $('#modalAcompanhar').modal('show');
  });
}

function formatTipo(t){
  if(!t) return '';
  if(t === 'aprovacao') return '<span class="badge badge-success">APROVAÇÃO</span>';
  if(t === 'rejeicao') return '<span class="badge badge-danger">REJEIÇÃO</span>';
  if(t === 'comentario') return '<span class="badge badge-info">COMENTÁRIO</span>';
  return `<span class="badge badge-secondary">${(t||'').toUpperCase()}</span>`;
}
function escapeHtml(str){ return (str||'').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
</script>
@stop


