@extends('adminlte::page')

@section('title', 'Autorizações Rejeitadas')

@section('plugins.Sweetalert2', true)

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-times text-danger mr-2"></i>Autorizações Rejeitadas</h1>
@stop

@section('content')
<div class="card card-danger shadow-sm">
  <div class="card-body p-0">
    <div class="px-3 py-2 bg-danger text-white d-flex justify-content-between align-items-center">
      <strong>Solicitações Rejeitadas</strong>
      <div class="d-flex align-items-center">
        <a href="{{ route('pedidos.autorizacao') }}" class="btn btn-outline-light btn-sm mr-2 text-white"><i class="fas fa-arrow-left mr-1"></i>{{ __('Voltar') }}</a>
        <span class="badge badge-light" id="badge-rejeitadas">0 rejeitadas</span>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0 align-middle" id="tabela-rejeitadas">
        <thead>
          <tr>
            <th>Nº Pedido</th>
            <th>Data Solicitação</th>
            <th>Solicitante</th>
            <th>Rota</th>
            <th>Roteirização</th>
            <th>Itens</th>
            <th>Qtd Total</th>
            <th>Prioridade</th>
            <th>Centro Custo</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="6" class="text-center text-muted" id="empty-row">
              <i class="fas fa-info-circle fa-2x mb-2 text-secondary"></i><br>
              Nenhum registro encontrado
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

// Escape seguro de HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}
// Alias curto
function esc(t){ return escapeHtml(t); }

$(function(){ carregarRejeitados(); });
function carregarRejeitados(){
  const params = new URLSearchParams(window.location.search);
  const data_ini = params.get('data_ini');
  const data_fim = params.get('data_fim');
  const qs = (data_ini && data_fim) ? (`?data_ini=${encodeURIComponent(data_ini)}&data_fim=${encodeURIComponent(data_fim)}`) : '';
  $.get('/api/pedidos-rejeitados-agrupados'+qs, function(resp){
    if(!resp.success) return; const grupos = resp.data || [];
    $('#badge-rejeitadas').text(`${grupos.length} rejeitadas`);
    const tbody = $('#tabela-rejeitadas tbody'); tbody.empty();
    if(grupos.length===0){ tbody.append(`<tr><td colspan=\"9\" class=\"text-center text-muted\" id=\"empty-row\">Nenhum registro encontrado</td></tr>`); return; }
    grupos.forEach(function(g){
      const tr = `
        <tr>
          <td><span class="badge badge-dark">${g.num_pedido||'—'}</span></td>
          <td>${formatarDataBR(g.data_solicitacao)}</td>
          <td>${g.solicitante||'—'}</td>
          <td>${g.rota_nome||'—'}</td>
          <td>${g.roteirizacao_nome||'—'}</td>
          <td>${g.itens} itens</td>
          <td>${g.quantidade_total||0}</td>
          <td><span class="badge badge-${g.prioridade}">${(g.prioridade||'').toUpperCase()}</span></td>
          <td>${g.centro_custo_nome||'—'}</td>
          <td class="text-nowrap">
            <button class="btn btn-outline-primary btn-sm" onclick="verRejeitado('${g.grupo_hash}')" title="Visualizar"><i class="fas fa-eye"></i></button>
            <button class="btn btn-outline-secondary btn-sm ml-1" onclick="imprimirRejeitado('${g.grupo_hash}')" title="Imprimir">
              <i class="fas fa-print"></i>
            </button>
          </td>
        </tr>`; tbody.append(tr);
    });
  });
}

// Abrir modal somente leitura do pedido rejeitado
function verRejeitado(hash){
  $.get(`/api/relatorio-pc/detalhes/${hash}`, function(resp){
    if(!(resp && resp.success)) return;
    const cab = resp.data.cabecalho; const itens = resp.data.itens||[]; const interacoes = resp.data.interacoes||[];
    const linhas = itens.map(i => `
      <tr>
        <td>${esc(i.produto_nome)}</td>
        <td class="text-center">${Number(i.quantidade||0)}</td>
      </tr>
    `).join('');
    const html = `
      <div class="modal fade" id="modalRejeitado" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header bg-light">
              <h5 class="modal-title"><i class="fas fa-file-alt mr-2"></i>Pedido Rejeitado - ${esc(cab.num_pedido||'')}</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6"><p><strong>Data:</strong> ${esc(cab.data_solicitacao||'')}</p><p><strong>Solicitante:</strong> ${esc(cab.solicitante||'')}</p></div>
                <div class="col-md-6"><p><strong>Centro de Custo:</strong> ${esc(cab.centro_custo_nome||'—')}</p><p><strong>Prioridade:</strong> ${(cab.prioridade||'').toUpperCase()}</p></div>
              </div>
              <hr/>
              <p class="mb-2"><strong>Itens</strong></p>
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead><tr><th>Item</th><th style="width:120px" class="text-center">Quantidade</th></tr></thead>
                  <tbody>${linhas||'<tr><td colspan="2" class="text-center text-muted">Sem itens</td></tr>'}</tbody>
                </table>
              </div>
              <hr/>
              <p class="mb-1"><strong>Interações</strong></p>
              <ul class="list-group">${(interacoes||[]).map(int => `
                <li class='list-group-item'>
                  <strong>${esc(int.usuario||'')}</strong>
                  <span class='text-muted'>${formatarDataBR(int.created_at)}</span><br>
                  ${formatTipo(int.tipo)}${int.mensagem ? ': '+esc(int.mensagem) : ''}
                </li>
              `).join('') || '<li class="list-group-item text-muted">Sem interações</li>'}</ul>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" onclick="imprimirRejeitado('${hash}')"><i class="fas fa-print mr-1"></i>{{ __('Imprimir') }}</button>
              <button class="btn btn-secondary" data-dismiss="modal">{{ __('Fechar') }}</button>
            </div>
          </div>
        </div>
      </div>`;
    try { $('#modalRejeitado').modal('hide'); } catch(e) {}
    $('.modal-backdrop').remove(); $('body').removeClass('modal-open'); $('#modalRejeitado').remove();
    $('body').append(html); $('#modalRejeitado').modal('show');
  });
}

// Imprimir na mesma aba, abrindo o diálogo de impressoras sem nova guia
function imprimirRejeitado(hash){
  const url = `/relatorio-pc/imprimir/${hash}`;
  try {
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.src = url;
    iframe.onload = function(){
      try {
        setTimeout(function(){
          iframe.contentWindow.focus();
          iframe.contentWindow.print();
          setTimeout(function(){ document.body.removeChild(iframe); }, 1500);
        }, 300);
      } catch(e) {
        document.body.removeChild(iframe);
        window.location.href = url; // fallback
      }
    };
    document.body.appendChild(iframe);
  } catch(e) {
    // Fallback final
    window.location.href = url;
  }
}
</script>
@stop


