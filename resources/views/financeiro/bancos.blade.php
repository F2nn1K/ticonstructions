@extends('adminlte::page')

@section('title', 'Bancos')

@section('content_header')
<h1><i class="fas fa-university mr-2"></i>Bancos</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gerenciamento de Contas Bancárias</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#modalNovoBanco">
                            <i class="fas fa-plus mr-1"></i> Nova Conta Bancária
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="tabelaBancos">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Banco</th>
                                <th>Agência</th>
                                <th>Conta</th>
                                <th>Tipo</th>
                                <th>Saldo</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="fas fa-info-circle mr-1"></i> Nenhuma conta bancária cadastrada
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Conta Bancária -->
<div class="modal fade" id="modalNovoBanco" tabindex="-1" role="dialog" aria-labelledby="modalNovoBancoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalNovoBancoLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Nova Conta Bancária
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formNovoBanco">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banco">Banco <span class="text-danger">*</span></label>
                                <select class="form-control" id="banco" name="banco" required>
                                    <option value="">Selecione...</option>
                                    <option value="001">001 - Banco do Brasil</option>
                                    <option value="033">033 - Santander</option>
                                    <option value="104">104 - Caixa Econômica Federal</option>
                                    <option value="237">237 - Bradesco</option>
                                    <option value="341">341 - Itaú</option>
                                    <option value="356">356 - Banco Real</option>
                                    <option value="422">422 - Safra</option>
                                    <option value="745">745 - Citibank</option>
                                    <option value="756">756 - Sicoob</option>
                                    <option value="748">748 - Sicredi</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome_conta">Nome da Conta <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome_conta" name="nome_conta" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="agencia">Agência <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="agencia" name="agencia" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="conta">Conta <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="conta" name="conta" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="digito">Dígito</label>
                                <input type="text" class="form-control" id="digito" name="digito" maxlength="2">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo_conta">Tipo de Conta</label>
                                <select class="form-control" id="tipo_conta" name="tipo_conta">
                                    <option value="corrente">Conta Corrente</option>
                                    <option value="poupanca">Poupança</option>
                                    <option value="investimento">Investimento</option>
                                    <option value="salario">Conta Salário</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="saldo_inicial">Saldo Inicial</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" id="saldo_inicial" name="saldo_inicial" value="0,00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                        <div class="col-12">
                            <div class="form-group">
                                <label for="observacoes">Observações</label>
                                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-dark">
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
        background-color: #6c757d;
        color: white;
    }
</style>
@stop

@section('js')
<script>
$(function() {
    // Máscara para valor monetário
    $('.money').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        value = (parseInt(value) / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        $(this).val(value);
    });

    // Submit do formulário
    $('#formNovoBanco').on('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            icon: 'info',
            title: 'Em desenvolvimento',
            text: 'A funcionalidade de cadastro será implementada em breve.',
            confirmButtonColor: '#6c757d'
        });
        
        $('#modalNovoBanco').modal('hide');
    });
});
</script>
@stop
