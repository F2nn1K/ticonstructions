@extends('adminlte::page')

@section('title', 'Fornecedores')

@section('content_header')
<h1><i class="fas fa-building"></i> Fornecedores</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cadastro de Fornecedores</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btnNovoFornecedor">
                    <i class="fas fa-plus"></i> Novo Fornecedor
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tabelaFornecedores">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Razão Social</th>
                            <th>CNPJ</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Cidade/UF</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fornecedores as $f)
                        <tr>
                            <td>{{ $f->id }}</td>
                            <td>
                                <strong>{{ $f->razao_social }}</strong>
                                @if($f->nome_fantasia)
                                <br><small class="text-muted">{{ $f->nome_fantasia }}</small>
                                @endif
                            </td>
                            <td>{{ $f->cnpj }}</td>
                            <td>{{ $f->telefone ?? '-' }}</td>
                            <td>{{ $f->email ?? '-' }}</td>
                            <td>{{ $f->cidade ?? '' }}{{ $f->uf ? '/'.$f->uf : '' }}</td>
                            <td>
                                @if($f->ativo)
                                    <span class="badge badge-success">{{ __('Ativo') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ __('Inativo') }}</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info btn-editar" data-id="{{ $f->id }}" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger btn-excluir" data-id="{{ $f->id }}" data-nome="{{ $f->razao_social }}" title="Excluir"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> Nenhum fornecedor cadastrado ainda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Fornecedor -->
<div class="modal fade" id="modalFornecedor" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-building"></i> Novo Fornecedor</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formFornecedor">
                    <input type="hidden" name="id" id="fornecedor_id">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Razão Social *</label>
                                <input type="text" class="form-control" name="razao_social" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>CNPJ *</label>
                                <input type="text" class="form-control" name="cnpj" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nome Fantasia</label>
                                <input type="text" class="form-control" name="nome_fantasia">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Telefone</label>
                                <input type="text" class="form-control" name="telefone">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Endereço</label>
                                <input type="text" class="form-control" name="endereco">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cidade</label>
                                <input type="text" class="form-control" name="cidade">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>UF</label>
                                <input type="text" class="form-control" name="uf" maxlength="2">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Observações') }}</label>
                        <textarea class="form-control" name="observacoes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn btn-primary" id="btnSalvarFornecedor">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Novo Fornecedor
    $('#btnNovoFornecedor').click(function() {
        $('#formFornecedor')[0].reset();
        $('#fornecedor_id').val('');
        $('.modal-title').html('<i class="fas fa-building"></i> Novo Fornecedor');
        $('#modalFornecedor').modal('show');
    });
    
    // Salvar Fornecedor
    $('#btnSalvarFornecedor').click(function() {
        var btn = $(this);
        var id = $('#fornecedor_id').val();
        var url = id ? '/api/suprimentos/fornecedores/' + id : '/api/suprimentos/fornecedores';
        var method = id ? 'PUT' : 'POST';
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        
        $.ajax({
            url: url,
            method: method,
            data: $('#formFornecedor').serialize(),
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: response.message,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: response.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao salvar fornecedor!',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar');
            }
        });
    });
    
    // Editar Fornecedor
    $('.btn-editar').click(function() {
        var id = $(this).data('id');
        
        $.get('/api/suprimentos/fornecedores/' + id, function(data) {
            $('#fornecedor_id').val(data.id);
            $('input[name="razao_social"]').val(data.razao_social);
            $('input[name="nome_fantasia"]').val(data.nome_fantasia);
            $('input[name="cnpj"]').val(data.cnpj);
            $('input[name="telefone"]').val(data.telefone);
            $('input[name="email"]').val(data.email);
            $('input[name="endereco"]').val(data.endereco);
            $('input[name="cidade"]').val(data.cidade);
            $('input[name="uf"]').val(data.uf);
            $('textarea[name="observacoes"]').val(data.observacoes);
            
            $('.modal-title').html('<i class="fas fa-building"></i> Editar Fornecedor');
            $('#modalFornecedor').modal('show');
        });
    });
    
    // Excluir Fornecedor
    $('.btn-excluir').click(function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        
        Swal.fire({
            title: 'Excluir Fornecedor?',
            html: 'Deseja realmente excluir o fornecedor<br><strong>"' + nome + '"</strong>?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Sim, excluir!',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/fornecedores/' + id,
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Excluído!',
                                text: response.message,
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: response.message,
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: 'Erro ao excluir fornecedor!',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });
});
</script>
@stop

