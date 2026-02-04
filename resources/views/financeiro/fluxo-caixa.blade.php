@extends('adminlte::page')

@section('title', 'Fluxo de Caixa')

@section('content_header')
<h1><i class="fas fa-chart-line mr-2"></i>Fluxo de Caixa</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="data_inicio">Data Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="data_fim">Data Fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="conta_bancaria">Conta Bancária</label>
                                <select class="form-control" id="conta_bancaria" name="conta_bancaria">
                                    <option value="">Todas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-warning btn-block" id="btnFiltrar">
                                    <i class="fas fa-search mr-1"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo -->
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>R$ 0,00</h3>
                    <p>Entradas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>R$ 0,00</h3>
                    <p>Saídas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>R$ 0,00</h3>
                    <p>Saldo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Movimentações -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Movimentações</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#modalNovaEntrada">
                            <i class="fas fa-plus mr-1"></i> Nova Entrada
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalNovaSaida">
                            <i class="fas fa-minus mr-1"></i> Nova Saída
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="tabelaFluxo">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Saldo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-info-circle mr-1"></i> Nenhuma movimentação encontrada
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Entrada -->
<div class="modal fade" id="modalNovaEntrada" tabindex="-1" role="dialog" aria-labelledby="modalNovaEntradaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalNovaEntradaLabel">
                    <i class="fas fa-arrow-up mr-2"></i>Nova Entrada
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formNovaEntrada">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="descricao_entrada">Descrição <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="descricao_entrada" name="descricao" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_entrada">Data <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="data_entrada" name="data" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="valor_entrada">Valor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" id="valor_entrada" name="valor" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="categoria_entrada">Categoria</label>
                                <select class="form-control" id="categoria_entrada" name="categoria">
                                    <option value="">Selecione...</option>
                                    <option value="vendas">Vendas</option>
                                    <option value="servicos">Serviços</option>
                                    <option value="recebimentos">Recebimentos</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="conta_entrada">Conta Bancária</label>
                                <select class="form-control" id="conta_entrada" name="conta_bancaria">
                                    <option value="">Selecione...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="obs_entrada">Observações</label>
                                <textarea class="form-control" id="obs_entrada" name="observacoes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nova Saída -->
<div class="modal fade" id="modalNovaSaida" tabindex="-1" role="dialog" aria-labelledby="modalNovaSaidaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalNovaSaidaLabel">
                    <i class="fas fa-arrow-down mr-2"></i>Nova Saída
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formNovaSaida">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="descricao_saida">Descrição <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="descricao_saida" name="descricao" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_saida">Data <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="data_saida" name="data" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="valor_saida">Valor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" id="valor_saida" name="valor" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="categoria_saida">Categoria</label>
                                <select class="form-control" id="categoria_saida" name="categoria">
                                    <option value="">Selecione...</option>
                                    <option value="fornecedores">Fornecedores</option>
                                    <option value="salarios">Salários</option>
                                    <option value="impostos">Impostos</option>
                                    <option value="despesas">Despesas</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="conta_saida">Conta Bancária</label>
                                <select class="form-control" id="conta_saida" name="conta_bancaria">
                                    <option value="">Selecione...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="obs_saida">Observações</label>
                                <textarea class="form-control" id="obs_saida" name="observacoes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
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
        background-color: #ffc107;
        color: #212529;
    }
    .small-box .icon {
        font-size: 70px;
    }
</style>
@stop

@section('js')
<script>
$(function() {
    // Definir data atual nos filtros
    var hoje = new Date().toISOString().split('T')[0];
    var primeiroDia = new Date();
    primeiroDia.setDate(1);
    $('#data_inicio').val(primeiroDia.toISOString().split('T')[0]);
    $('#data_fim').val(hoje);

    // Máscara para valor monetário
    $('.money').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        value = (parseInt(value) / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        $(this).val(value);
    });

    // Submit dos formulários
    $('#formNovaEntrada, #formNovaSaida').on('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            icon: 'info',
            title: 'Em desenvolvimento',
            text: 'A funcionalidade de cadastro será implementada em breve.',
            confirmButtonColor: '#ffc107'
        });
        
        $('.modal').modal('hide');
    });

    // Botão filtrar
    $('#btnFiltrar').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Em desenvolvimento',
            text: 'A funcionalidade de filtro será implementada em breve.',
            confirmButtonColor: '#ffc107'
        });
    });
});
</script>
@stop
