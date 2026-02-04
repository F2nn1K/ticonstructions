@extends('adminlte::page')

@section('title', 'Produtos')

@section('content_header')
<h1>Produtos</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center">
        <div class="input-group" style="max-width: 420px;">
            <input id="busca" type="text" class="form-control" placeholder="Buscar por código, produto ou unidade">
            <div class="input-group-append">
                <button id="btnBuscar" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </div>
        <button id="btnNovo" class="btn btn-success ml-auto"><i class="fas fa-plus"></i> Cadastrar Produto</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 100px;">Código</th>
                        <th>Produto</th>
                        <th>Unidade</th>
                        <th style="width: 160px;" class="text-right">Valor</th>
                        <th style="width: 120px;">Ativo</th>
                        <th style="width: 120px;">Ações</th>
                    </tr>
                </thead>
                <tbody id="tbodyProdutos">
                    <tr><td colspan="6" class="text-center p-4">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Novo Produto -->
<div class="modal fade" id="modalNovo" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar Produto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label>Código</label>
            <input id="novoCodigo" type="number" class="form-control" placeholder="Opcional">
        </div>
        <div class="form-group">
            <label>Produto</label>
            <input id="novoProduto" type="text" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Unidade</label>
            <input id="novoDescricao" type="text" class="form-control" placeholder="UN" value="UN">
        </div>
        <div class="form-group">
            <label>Valor do produto</label>
            <input id="novoValor" type="number" class="form-control" step="0.01" min="0" placeholder="0,00">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" id="btnSalvarNovo" class="btn btn-primary">Salvar</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal Editar Produto -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Produto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editId">
        <div class="form-group">
            <label>Código</label>
            <input id="editCodigo" type="number" class="form-control" placeholder="Opcional">
        </div>
        <div class="form-group">
            <label>Produto</label>
            <input id="editProduto" type="text" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Unidade</label>
            <input id="editDescricao" type="text" class="form-control" placeholder="UN" value="UN">
        </div>
        <div class="form-group">
            <label>Valor do produto</label>
            <input id="editValor" type="number" class="form-control" step="0.01" min="0" placeholder="0,00">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" id="btnSalvarEdicao" class="btn btn-primary">Salvar</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<script>
function formatarBRL(v){
    const num = Number(v||0);
    return 'R$ ' + num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function carregar(q = '') {
    const url = '/api/estoque-pedido' + (q ? ('?q=' + encodeURIComponent(q)) : '');
    fetch(url)
        .then(r => r.json())
        .then(resp => {
            const tbody = document.getElementById('tbodyProdutos');
            tbody.innerHTML = '';
            if (!resp.success || !resp.data || resp.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center p-4">Nenhum produto encontrado</td></tr>';
                return;
            }
            resp.data.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${p.codigo ?? ''}</td>
                    <td>${p.produto}</td>
                    <td>${p.descricao ?? 'UN'}</td>
                    <td class="text-right">${formatarBRL(p.valor_unitario)}</td>
                    <td>
                        <span class="badge ${p.ativo ? 'badge-success' : 'badge-secondary'}">${p.ativo ? 'Ativo' : 'Inativo'}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Ações">
                            <button class="btn btn-primary btn-editar"
                                    data-id="${p.id}"
                                    data-codigo="${p.codigo ?? ''}"
                                    data-produto="${p.produto}"
                                    data-descricao="${p.descricao ?? ''}"
                                    data-valor="${p.valor_unitario ?? 0}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn ${p.ativo ? 'btn-warning' : 'btn-success'}" onclick="toggleAtivo(${p.id})">
                                ${p.ativo ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>'}
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(() => {
            document.getElementById('tbodyProdutos').innerHTML = '<tr><td colspan="6" class="text-center p-4 text-danger">Erro ao carregar</td></tr>';
            Swal.fire('Erro', 'Não foi possível carregar os produtos.', 'error');
        });
}

function toggleAtivo(id) {
    Swal.fire({
        title: 'Confirmar alteração?',
        text: 'Deseja alternar o status deste produto?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`/api/estoque-pedido/${id}/toggle`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: '{}'
        })
        .then(r => r.json())
        .then(resp => {
            if (resp && resp.success) {
                Swal.fire({ icon: 'success', title: 'Atualizado!', timer: 1200, showConfirmButton: false });
                carregar(document.getElementById('busca').value.trim());
            } else {
                Swal.fire('Atenção', (resp && resp.message) ? resp.message : 'Não foi possível alterar o status.', 'warning');
            }
        })
        .catch(() => Swal.fire('Erro', 'Erro ao alterar status.', 'error'));
    });
}

document.getElementById('btnBuscar').addEventListener('click', () => {
    carregar(document.getElementById('busca').value.trim());
});

document.getElementById('busca').addEventListener('keyup', (e) => {
    if (e.key === 'Enter') document.getElementById('btnBuscar').click();
});

document.getElementById('btnNovo').addEventListener('click', () => {
    document.getElementById('novoCodigo').value = '';
    document.getElementById('novoProduto').value = '';
    document.getElementById('novoDescricao').value = '';
    document.getElementById('novoValor').value = '';
    $('#modalNovo').modal('show');
});

document.getElementById('btnSalvarNovo').addEventListener('click', () => {
    const payload = {
        codigo: document.getElementById('novoCodigo').value || null,
        produto: document.getElementById('novoProduto').value.trim(),
        descricao: document.getElementById('novoDescricao').value.trim() || 'UN',
        valor_unitario: parseFloat((document.getElementById('novoValor').value || '0').replace(',','.')),
    };
    if (!payload.produto) {
        Swal.fire('Atenção', 'Informe o nome do produto.', 'warning');
        return;
    }
    if (isNaN(payload.valor_unitario) || payload.valor_unitario < 0) {
        Swal.fire('Atenção', 'Informe um valor válido (maior ou igual a 0).', 'warning');
        return;
    }
    fetch('/api/estoque-pedido', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(resp => {
        if (!resp || !resp.success) {
            Swal.fire('Atenção', (resp && resp.message) ? resp.message : 'Não foi possível salvar.', 'warning');
            return;
        }
        $('#modalNovo').modal('hide');
        carregar();
        Swal.fire({ icon: 'success', title: 'Salvo!', timer: 1200, showConfirmButton: false });
    })
    .catch(() => Swal.fire('Erro', 'Erro ao cadastrar produto.', 'error'));
});

document.addEventListener('DOMContentLoaded', () => carregar());

// Abrir modal de edição pré-preenchido
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-editar');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    document.getElementById('editId').value = id;
    document.getElementById('editCodigo').value = btn.getAttribute('data-codigo') || '';
    document.getElementById('editProduto').value = btn.getAttribute('data-produto') || '';
    document.getElementById('editDescricao').value = btn.getAttribute('data-descricao') || 'UN';
    document.getElementById('editValor').value = btn.getAttribute('data-valor') || '';
    $('#modalEditar').modal('show');
});

// Salvar edição
document.getElementById('btnSalvarEdicao').addEventListener('click', () => {
    const payload = {
        codigo: document.getElementById('editCodigo').value || null,
        produto: document.getElementById('editProduto').value.trim(),
        descricao: document.getElementById('editDescricao').value.trim() || 'UN',
        valor_unitario: parseFloat((document.getElementById('editValor').value || '0').replace(',','.')),
    };
    if (!payload.produto) {
        Swal.fire('Atenção', 'Informe o nome do produto.', 'warning');
        return;
    }
    if (isNaN(payload.valor_unitario) || payload.valor_unitario < 0) {
        Swal.fire('Atenção', 'Informe um valor válido (maior ou igual a 0).', 'warning');
        return;
    }
    const id = document.getElementById('editId').value;
    fetch(`/api/estoque-pedido/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(resp => {
        if (!resp || !resp.success) {
            Swal.fire('Atenção', (resp && resp.message) ? resp.message : 'Não foi possível salvar.', 'warning');
            return;
        }
        $('#modalEditar').modal('hide');
        carregar(document.getElementById('busca').value.trim());
        Swal.fire({ icon: 'success', title: 'Atualizado!', timer: 1200, showConfirmButton: false });
    })
    .catch(() => Swal.fire('Erro', 'Erro ao atualizar produto.', 'error'));
});
</script>
@stop


