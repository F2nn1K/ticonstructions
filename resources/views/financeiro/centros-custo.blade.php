@extends('adminlte::page')

@section('title', 'Centros de Custo Financeiro')

@push('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content_header')
<h1><i class="fas fa-sitemap mr-2"></i>Centros de Custo Financeiro</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gerenciamento de Centros de Custo</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalNovo()">
                            <i class="fas fa-plus mr-1"></i> Novo Centro de Custo
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped table-hover" id="tabelaCentros">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 120px;">Código</th>
                                <th>Nome</th>
                                <th style="width: 130px;">Tipo</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="corpoTabela">
                            @forelse($centros as $centro)
                            <tr data-id="{{ $centro->id }}">
                                <td>{{ $centro->id }}</td>
                                <td>{{ $centro->codigo ?? '-' }}</td>
                                <td>{{ $centro->nome }}</td>
                                <td>{{ ucfirst($centro->tipo ?? '-') }}</td>
                                <td>
                                    @if($centro->ativo)
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info" onclick="editarCentro({{ $centro->id }})" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="excluirCentro({{ $centro->id }})" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr id="semRegistros">
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle mr-1"></i> Nenhum centro de custo cadastrado
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo/Editar Centro de Custo -->
<div class="modal fade" id="modalCentro" tabindex="-1" role="dialog" aria-labelledby="modalCentroLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCentroLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Novo Centro de Custo
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCentro">
                <input type="hidden" id="centroId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="codigo">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Ex: CC001">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nome">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo">Tipo</label>
                                <select class="form-control" id="tipo" name="tipo">
                                    <option value="">Selecione...</option>
                                    <option value="receita">Receita</option>
                                    <option value="despesa">Despesa</option>
                                    <option value="investimento">Investimento</option>
                                    <option value="operacional">Operacional</option>
                                    <option value="administrativo">Administrativo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="responsavel">Responsável</label>
                                <input type="text" class="form-control" id="responsavel" name="responsavel">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="descricao">Descrição</label>
                                <input type="text" class="form-control" id="descricao" name="descricao">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="endereco">Endereço</label>
                                <input type="text" class="form-control" id="endereco" name="endereco" placeholder="Rua, número, bairro, cidade - UF">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card-header {
        background-color: #f8f9fa;
    }
    .table thead th {
        background-color: #007bff;
        color: white;
        border: none;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
    }
</style>
@stop

@section('js')
<script>
// Configurar CSRF token para todas as requisições AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var centroAtualId = null;

function abrirModalNovo() {
    centroAtualId = null;
    $('#centroId').val('');
    $('#formCentro')[0].reset();
    $('#status').val('ativo');
    $('#modalCentroLabel').html('<i class="fas fa-plus-circle mr-2"></i>Novo Centro de Custo');
    $('#modalCentro').modal('show');
}

function editarCentro(id) {
    $.get('/api/suprimentos/centros-custo/' + id, function(response) {
        if (response.success) {
            const centro = response.centro;
            centroAtualId = centro.id;
            $('#centroId').val(centro.id);
            $('#codigo').val(centro.codigo || '');
            $('#nome').val(centro.nome);
            $('#tipo').val(centro.tipo || '');
            $('#responsavel').val(centro.responsavel || '');
            $('#descricao').val(centro.descricao || '');
            $('#endereco').val(centro.endereco || '');
            $('#status').val(centro.ativo == 1 ? 'ativo' : 'inativo');
            $('#modalCentroLabel').html('<i class="fas fa-edit mr-2"></i>Editar Centro de Custo');
            $('#modalCentro').modal('show');
        } else {
            Swal.fire('Erro', response.message, 'error');
        }
    });
}

function excluirCentro(id) {
    Swal.fire({
        title: 'Confirmar exclusão?',
        text: 'Esta ação não pode ser desfeita!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/api/suprimentos/centros-custo/' + id,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Excluído!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Erro', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Erro ao excluir centro de custo.';
                    Swal.fire('Erro', msg, 'error');
                }
            });
        }
    });
}

$('#formCentro').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#centroId').val();
    const isEdit = id && id !== '';
    
    const data = {
        codigo: $('#codigo').val(),
        nome: $('#nome').val(),
        tipo: $('#tipo').val(),
        responsavel: $('#responsavel').val(),
        descricao: $('#descricao').val(),
        endereco: $('#endereco').val(),
        status: $('#status').val()
    };
    
    $.ajax({
        url: isEdit ? '/api/suprimentos/centros-custo/' + id : '/api/suprimentos/centros-custo',
        method: isEdit ? 'PUT' : 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                $('#modalCentro').modal('hide');
                Swal.fire('Sucesso', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'Erro ao salvar centro de custo.';
            Swal.fire('Erro', msg, 'error');
        }
    });
});
</script>
@stop
