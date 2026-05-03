@extends('adminlte::page')

@section('title', 'Nota Fiscal de Entrada')

@section('content_header')
<h1><i class="fas fa-file-alt"></i> Nota Fiscal de Entrada</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Notas Fiscais de Entrada</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btnNovaNF">
                    <i class="fas fa-plus"></i> Lançar NF
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-2">
                    <input type="text" class="form-control" placeholder="Nº NF" id="filtroNF">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Fornecedor" id="filtroFornecedor">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="filtroDataIni">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="filtroDataFim">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-secondary" id="btnFiltrar">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <button class="btn btn-outline-secondary" id="btnLimpar">
                        <i class="fas fa-eraser"></i> Limpar
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tabelaNF">
                    <thead>
                        <tr>
                            <th>Nº NF</th>
                            <th>Série</th>
                            <th>Data Emissão</th>
                            <th>Data Entrada</th>
                            <th>Fornecedor</th>
                            <th>Valor Total</th>
                            <th>OC Vinculada</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notas as $nf)
                        <tr>
                            <td><strong>{{ $nf->numero }}</strong></td>
                            <td>{{ $nf->serie ?? '1' }}</td>
                            <td>{{ \Carbon\Carbon::parse($nf->data_emissao)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($nf->data_entrada)->format('d/m/Y') }}</td>
                            <td>{{ $nf->fornecedor ?? '-' }}</td>
                            <td class="text-right">R$ {{ number_format($nf->valor_total, 2, ',', '.') }}</td>
                            <td>{{ $nf->ordem_numero ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" title="Ver Detalhes"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-sm btn-secondary" title="Imprimir"><i class="fas fa-print"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> Nenhuma nota fiscal registrada ainda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lançar NF -->
<div class="modal fade" id="modalNF" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="fas fa-file-alt"></i> Lançar Nota Fiscal</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formNF">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Número da NF *</label>
                                <input type="text" class="form-control" name="numero_nf" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Série</label>
                                <input type="text" class="form-control" name="serie" value="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Data Emissão *</label>
                                <input type="date" class="form-control" name="data_emissao" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Data Entrada</label>
                                <input type="date" class="form-control" name="data_entrada" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Fornecedor *</label>
                                <select class="form-control" name="fornecedor_id" required>
                                    <option value="">Selecione o fornecedor...</option>
                                    @foreach($fornecedores as $f)
                                    <option value="{{ $f->id }}">{{ $f->razao_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Vincular à OC</label>
                                <select class="form-control" name="ordem_compra_id">
                                    <option value="">Sem vínculo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Valor Produtos *</label>
                                <input type="text" class="form-control" name="valor_produtos" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Valor Frete</label>
                                <input type="text" class="form-control" name="valor_frete" value="0,00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Valor Desconto</label>
                                <input type="text" class="form-control" name="valor_desconto" value="0,00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Valor Total NF</label>
                                <input type="text" class="form-control" name="valor_total" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Chave de Acesso (NFe)</label>
                                <input type="text" class="form-control" name="chave_acesso" maxlength="44">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>CFOP</label>
                                <input type="text" class="form-control" name="cfop">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Natureza da Operação</label>
                                <input type="text" class="form-control" name="natureza_operacao" value="Compra">
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
                <button type="button" class="btn btn-info" id="btnSalvarNF">
                    <i class="fas fa-save"></i> Salvar NF
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#btnNovaNF').click(function() {
        $('#formNF')[0].reset();
        $('input[name="data_entrada"]').val('{{ date("Y-m-d") }}');
        $('#modalNF').modal('show');
    });
    
    // Calcular valor total
    $('input[name="valor_produtos"], input[name="valor_frete"], input[name="valor_desconto"]').on('input', function() {
        var produtos = parseFloat($('input[name="valor_produtos"]').val().replace('.', '').replace(',', '.')) || 0;
        var frete = parseFloat($('input[name="valor_frete"]').val().replace('.', '').replace(',', '.')) || 0;
        var desconto = parseFloat($('input[name="valor_desconto"]').val().replace('.', '').replace(',', '.')) || 0;
        var total = produtos + frete - desconto;
        $('input[name="valor_total"]').val(total.toFixed(2).replace('.', ','));
    });
    
    // Salvar NF
    $('#btnSalvarNF').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        
        $.ajax({
            url: '/api/suprimentos/nf-entrada',
            method: 'POST',
            data: $('#formNF').serialize(),
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
                    text: 'Erro ao lançar NF!',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar NF');
            }
        });
    });
});
</script>
@stop

