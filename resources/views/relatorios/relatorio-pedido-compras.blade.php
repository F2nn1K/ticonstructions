@extends('adminlte::page')

@section('title', 'Relatório de Pedido de Compras')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <div>
    <h1 class="m-0 text-dark font-weight-bold">
      <i class="fas fa-file-invoice-dollar text-info mr-2"></i>
      Relatório de Pedido de Compras
    </h1>
    <small class="text-muted">Visualize pedidos aprovados. Clique para ver detalhes e imprimir.</small>
  </div>
</div>
@stop

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-header bg-info text-white">
      <div class="d-flex align-items-center">
        <i class="fas fa-filter mr-2"></i> Filtros
      </div>
    </div>
    <div class="card-body">
      <div class="form-row">
        <div class="col-md-4 mb-2">
          <label class="font-weight-bold">Data início</label>
          <input type="date" id="data_ini" class="form-control" />
        </div>
        <div class="col-md-4 mb-2">
          <label class="font-weight-bold">Data fim</label>
          <input type="date" id="data_fim" class="form-control" />
        </div>
        <div class="col-md-4 mb-2">
          <label class="font-weight-bold">Nº Pedido</label>
          <input type="text" id="num_pedido" class="form-control" placeholder="Ex.: PED-2025..." />
        </div>
      </div>
      <div class="form-row">
        <div class="col-md-12 d-flex justify-content-end">
          <button id="btnFiltrar" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-header">
      <i class="fas fa-list-alt mr-2"></i> Resultados
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover table-striped" id="tabelaPedidos">
          <thead class="thead-dark">
            <tr>
              <th>Data Aprovação</th>
              <th>Nº Pedido</th>
              <th>Solicitante</th>
              <th>Rota</th>
              <th>Itens</th>
              <th>Qtd Total</th>
              <th>Visualizado</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalhes do Pedido</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="cabecalhoPedido" class="mb-3"></div>
        <div class="table-responsive">
          <table class="table table-bordered" id="tabelaItens">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>Produto</th>
                <th>Quantidade</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div>
          <h6 class="font-weight-bold">Interações</h6>
          <ul id="listaInteracoes" class="list-group"></ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Fechar') }}</button>
        <button type="button" class="btn btn-primary" id="btnImprimirModal">
          <i class="fas fa-print"></i> Imprimir
        </button>
      </div>
    </div>
  </div>
 </div>
@stop

@section('css')
<style>
  @media print {
    .no-print { display: none !important; }
  }
  .badge-status { font-size: 12px; }
  .pointer { cursor: pointer; }
</style>
@stop

@section('js')
<script>
let dadosAtual = [];
let hashAtual = null; // Para armazenar o hash do pedido atual no modal
const esc = s => String(s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

function carregar() {
  const di = document.getElementById('data_ini').value;
  const df = document.getElementById('data_fim').value;
  const np = document.getElementById('num_pedido').value;
  const qs = new URLSearchParams({ data_ini: di || '', data_fim: df || '', num_pedido: np || '' }).toString();
  const base = '/api/relatorio-pc/aprovados'; // Sempre aprovados
  const url = qs ? `${base}?${qs}` : base;
  fetch(url)
    .then(async r => {
      if (!r.ok) {
        const txt = await r.text();
        throw new Error('Falha na API: ' + r.status + ' ' + txt.substring(0,200));
      }
      return r.json();
    })
    .then(j => {
      if (!j.success) { alert('Erro ao carregar'); return; }
      dadosAtual = j.data || [];
      preencherTabela(dadosAtual);
    })
    .catch(() => alert('Erro ao carregar'));
}

function preencherTabela(dados) {
  const tbody = document.querySelector('#tabelaPedidos tbody');
  tbody.innerHTML = '';
  dados.forEach((g, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${g.data_aprovacao || ''}</td>
      <td><span class="badge badge-dark">${esc(g.num_pedido || '-')}</span></td>
      <td>${esc(g.solicitante || '-')}</td>
      <td>${esc(g.rota_nome || '-')}</td>
      <td>${g.itens || 0}</td>
      <td>${g.quantidade_total || 0}</td>
      <td>${g.visualizado_em ? esc(g.visualizado_em) : '—'}</td>
      <td>
        <button class="btn btn-sm btn-outline-primary" onclick="abrirDetalhes('${g.grupo_hash}')">
          <i class="fas fa-eye"></i> Ver
        </button>
      </td>`;
    tbody.appendChild(tr);
  });
}

function abrirDetalhes(hash) {
  hashAtual = hash; // Armazena o hash para usar na impressão
  // Marca visualizado (global)
  fetch(`/api/relatorio-pc/visualizado/${hash}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(()=>carregar())
    .catch(()=>{});
  const qs = new URLSearchParams({ status: 'aprovados' }).toString();
  fetch(`/api/relatorio-pc/detalhes/${hash}?${qs}`)
    .then(async r => {
      if (!r.ok) {
        const txt = await r.text();
        throw new Error('Falha na API: ' + r.status + ' ' + txt.substring(0,200));
      }
      return r.json();
    })
    .then(j => {
      if (!j.success) { alert('Não encontrado'); return; }
      const d = j.data;
      document.getElementById('cabecalhoPedido').innerHTML = `
        <div class="row">
          <div class="col-md-6"><strong>Nº Pedido:</strong> ${esc(d.cabecalho.num_pedido || '-')}</div>
          <div class="col-md-6"><strong>Data Aprovação:</strong> ${esc(d.cabecalho.data_aprovacao || '-')}
          <div class="col-md-6"><strong>Centro de Custo:</strong> ${esc(d.cabecalho.centro_custo_nome || '-')}</div>
          <div class="col-md-6"><strong>Prioridade:</strong> ${esc(d.cabecalho.prioridade || '-')}
        </div>`;

      const tbody = document.querySelector('#tabelaItens tbody');
      tbody.innerHTML = '';
      (d.itens || []).forEach((it, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${i+1}</td><td>${esc(it.produto_nome)}</td><td>${it.quantidade}</td>`;
        tbody.appendChild(tr);
      });

      const ul = document.getElementById('listaInteracoes');
      ul.innerHTML = '';
      (d.interacoes || []).forEach(it => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.textContent = `[${it.created_at}] ${it.usuario}: ${it.tipo} - ${it.mensagem ?? ''}`;
        ul.appendChild(li);
      });

      $('#modalDetalhes').modal('show');
    });
}

function imprimirLista() {
  const conteudo = document.querySelector('.container-fluid').cloneNode(true);
  // Remove botões e modal
  conteudo.querySelectorAll('button, .modal').forEach(e => e.remove());
  const win = window.open('', '_blank');
  win.document.write('<html><head><title>Relatório de Pedido de Compras</title>');
  win.document.write('<link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.min.css">');
  win.document.write('</head><body>');
  win.document.body.appendChild(conteudo);
  win.document.write('</body></html>');
  win.document.close();
  win.focus();
  win.print();
  win.close();
}

document.getElementById('btnFiltrar').addEventListener('click', carregar);
document.getElementById('btnImprimirModal').addEventListener('click', function(){
  if (!hashAtual) {
    alert('Erro: Não foi possível identificar o pedido para impressão');
    return;
  }
  
  // Cria um iframe invisível para carregar o conteúdo de impressão
  const iframe = document.createElement('iframe');
  iframe.style.position = 'absolute';
  iframe.style.top = '-9999px';
  iframe.style.left = '-9999px';
  iframe.style.width = '1px';
  iframe.style.height = '1px';
  iframe.style.border = 'none';
  
  // Adiciona o iframe ao DOM
  document.body.appendChild(iframe);
  
  // Carrega o conteúdo e imprime quando estiver pronto
  iframe.onload = function() {
    // Aguarda um pouco para garantir que o conteúdo foi totalmente carregado
    setTimeout(function() {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      } catch (e) {
        // Erro ao imprimir
        alert('Erro ao abrir impressão. Tente novamente.');
      }
      
      // Remove o iframe após a impressão
      setTimeout(function() {
        document.body.removeChild(iframe);
      }, 1000);
    }, 500);
  };
  
  // Define a URL para carregar
  const qs = new URLSearchParams({ status: 'aprovados' }).toString();
  iframe.src = `/relatorio-pc/imprimir/${hashAtual}?${qs}`;
});

// Auto-carregar ao abrir
// Preenche datas padrão: hoje em ambos
const hoje = new Date().toISOString().split('T')[0];
document.getElementById('data_ini').value = hoje;
document.getElementById('data_fim').value = hoje;
carregar();
</script>
@stop


