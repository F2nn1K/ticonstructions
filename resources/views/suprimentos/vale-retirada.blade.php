@extends('adminlte::page')

@section('title', 'Vale de Retirada')

@section('content_header')
<h1><i class="fas fa-hand-holding-box"></i> Vale de Retirada</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Vales de Retirada de Material</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btnNovoVale">
                    <i class="fas fa-plus"></i> Novo Vale
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-2">
                    <input type="text" class="form-control" placeholder="Nº Vale" id="filtroVale">
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="filtroCentroCusto">
                        <option value="">Todos os Centros de Custo</option>
                    </select>
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
                <table class="table table-bordered table-striped" id="tabelaVales">
                    <thead>
                        <tr>
                            <th>Nº Vale</th>
                            <th>Data</th>
                            <th>Destino</th>
                            <th>Responsável</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vales as $v)
                        <tr>
                            <td><strong>{{ $v->numero }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($v->data_retirada)->format('d/m/Y') }}</td>
                            <td>{{ $v->destino ?? '-' }}</td>
                            <td>{{ $v->responsavel_retirada ?? '-' }}</td>
                            <td>
                                @switch($v->status)
                                    @case('pendente')
                                        <span class="badge badge-warning">Pendente</span>
                                        @break
                                    @case('aprovado')
                                        <span class="badge badge-primary">Aprovado</span>
                                        @break
                                    @case('entregue')
                                        <span class="badge badge-success">Entregue</span>
                                        @break
                                    @case('cancelado')
                                        <span class="badge badge-danger">Cancelado</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $v->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" title="Ver Detalhes"><i class="fas fa-eye"></i></button>
                                @if($v->status == 'pendente')
                                <button class="btn btn-sm btn-success btn-aprovar-vale" data-id="{{ $v->id }}" title="Aprovar"><i class="fas fa-check"></i></button>
                                @endif
                                @if($v->status == 'aprovado')
                                <button class="btn btn-sm btn-primary btn-entregar-vale" data-id="{{ $v->id }}" title="Marcar como Entregue"><i class="fas fa-truck"></i></button>
                                @endif
                                <button class="btn btn-sm btn-secondary" title="Imprimir"><i class="fas fa-print"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> Nenhum vale de retirada registrado ainda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Vale -->
<div class="modal fade" id="modalVale" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-hand-holding-box"></i> Novo Vale de Retirada</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formVale">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Data da Retirada *</label>
                                <input type="date" class="form-control" name="data_retirada" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Responsável pela Retirada *</label>
                                <input type="text" class="form-control" name="responsavel_retirada" required placeholder="Nome do responsável">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Destino *</label>
                                <input type="text" class="form-control" name="destino" required placeholder="Ex: Setor de Manutenção">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('Observações') }}</label>
                                <textarea class="form-control" name="observacoes" rows="2" placeholder="Observações adicionais..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn btn-primary" id="btnSalvarVale">
                    <i class="fas fa-save"></i> Salvar Vale
                </button>
                <button type="button" class="btn btn-success" id="btnSalvarImprimir">
                    <i class="fas fa-print"></i> Salvar e Imprimir
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#btnNovoVale').click(function() {
        $('#formVale')[0].reset();
        $('input[name="data_retirada"]').val('{{ date("Y-m-d") }}');
        $('#modalVale').modal('show');
    });
    
    // Salvar Vale
    $('#btnSalvarVale, #btnSalvarImprimir').click(function() {
        var btn = $(this);
        var imprimir = $(this).attr('id') === 'btnSalvarImprimir';
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        
        $.ajax({
            url: '/api/suprimentos/vales',
            method: 'POST',
            data: $('#formVale').serialize(),
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        html: response.message + '<br><strong>Número: ' + response.numero + '</strong>',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        if (imprimir) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Em breve!',
                                text: 'Funcionalidade de impressão será implementada em breve!',
                                confirmButtonColor: '#17a2b8'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            location.reload();
                        }
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
                    text: 'Erro ao criar vale!',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html(imprimir ? '<i class="fas fa-print"></i> Salvar e Imprimir' : '<i class="fas fa-save"></i> Salvar Vale');
            }
        });
    });
    
    // Aprovar Vale
    $('.btn-aprovar-vale').click(function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Aprovar Vale?',
            text: 'Deseja aprovar este vale de retirada?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sim, aprovar!',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/vales/' + id + '/status',
                    method: 'PUT',
                    data: {status: 'aprovado'},
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Aprovado!',
                                text: 'Vale aprovado com sucesso!',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Marcar como Entregue
    $('.btn-entregar-vale').click(function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Confirmar Entrega?',
            text: 'Confirmar que o material foi entregue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sim, confirmar!',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/vales/' + id + '/status',
                    method: 'PUT',
                    data: {status: 'entregue'},
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Entregue!',
                                text: 'Vale marcado como entregue!',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            }
        });
    });
});
</script>
@stop

