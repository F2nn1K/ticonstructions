@extends('adminlte::page')

@section('title', 'Autorizações Aprovadas')

@section('plugins.Sweetalert2', true)

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-check text-success mr-2"></i>Autorizações Aprovadas</h1>
@stop

@section('content')
<div class="card card-success shadow-sm">
  <div class="card-body p-0">
    <div class="px-3 py-2 bg-success text-white d-flex justify-content-between align-items-center">
      <strong>Solicitações Aprovadas</strong>
      <a href="{{ route('pedidos.autorizacao') }}" class="btn btn-outline-light btn-sm text-white"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0 align-middle" id="tabela-aprovadas">
        <thead>
          <tr>
            <th>Nº Pedido</th>
            <th>Data Solicitação</th>
            <th>Solicitante</th>
            <th>Aprovado por</th>
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

$(function(){ carregarAprovados(); });
function carregarAprovados(){
  const params = new URLSearchParams(window.location.search);
  const data_ini = params.get('data_ini');
  const data_fim = params.get('data_fim');
  const qs = (data_ini && data_fim) ? (`?data_ini=${encodeURIComponent(data_ini)}&data_fim=${encodeURIComponent(data_fim)}`) : '';
  $.get('/api/pedidos-aprovados-agrupados'+qs, function(resp){
    if(!resp.success) return; const grupos = resp.data || [];
    $('#badge-aprovadas').text(`${grupos.length} aprovadas`);
    const tbody = $('#tabela-aprovadas tbody'); tbody.empty();
      if(grupos.length===0){ tbody.append(`<tr><td colspan="10" class="text-center text-muted" id="empty-row">Nenhum registro encontrado</td></tr>`); return; }
    const esc = s=>String(s||'').replace(/[&<>\"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    grupos.forEach(function(g){
      const tr = `
        <tr>
            <td><span class="badge badge-dark">${g.num_pedido||'—'}</span></td>
          <td>${formatarDataBR(g.data_solicitacao)}</td>
          <td>${esc(g.solicitante||'—')}</td>
          <td>${esc(g.aprovador_nome||'—')}</td>
          <td>${esc(g.rota_nome||'—')}</td>
          <td>${esc(g.roteirizacao_nome||'—')}</td>
          <td>${g.itens} itens</td>
          <td>${g.quantidade_total||0}</td>
          <td><span class="badge badge-${g.prioridade}">${(g.prioridade||'').toUpperCase()}</span></td>
          <td>${esc(g.centro_custo_nome||'—')}</td>
          <td class="text-nowrap">
            <button class="btn btn-outline-secondary btn-sm" onclick="imprimirAprovado('${g.grupo_hash}')" title="Imprimir"><i class="fas fa-print"></i></button>
          </td>
        </tr>`; tbody.append(tr);
    });
  });
}

// Imprimir na mesma aba (sem abrir nova tab)
function imprimirAprovado(hash){
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
        window.location.href = url;
      }
    };
    document.body.appendChild(iframe);
  } catch(e) {
    window.location.href = url;
  }
}
</script>
@stop


