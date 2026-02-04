@extends('adminlte::page')

@section('title', 'Autorizações de Compras')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-check-circle text-success mr-3"></i>
            Autorização de Pedido de Compras
        </h1>
        <small class="text-muted">Autorize ou recuse solicitações de compras</small>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <a href="{{ route('pedidos.autorizacao.pendentes') }}" class="text-decoration-none">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pendentes</span>
                        <span class="info-box-number" id="count-pendentes">0</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('pedidos.autorizacao.aprovadas') }}" class="text-decoration-none">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Aprovadas</span>
                        <span class="info-box-number" id="count-aprovadas">0</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('pedidos.autorizacao.rejeitadas') }}" class="text-decoration-none">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Rejeitadas</span>
                        <span class="info-box-number" id="count-rejeitadas">0</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Modal de Autorização -->
<div class="modal fade" id="modalAutorizacao" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-gavel mr-2"></i>
                    Autorizar Solicitação
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Solicitante:</strong> <span id="modal_solicitante">-</span></p>
                        <p><strong>Data Solicitação:</strong> <span id="modal_data">-</span></p>
                        <p><strong>Prioridade:</strong> <span id="modal_prioridade">-</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Categoria:</strong> <span id="modal_categoria">-</span></p>
                        <p><strong>Data Necessidade:</strong> <span id="modal_necessidade">-</span></p>
                        <p><strong>Centro de Custo:</strong> <span id="modal_centro_custo">-</span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <p><strong>Descrição:</strong></p>
                        <p id="modal_descricao" class="bg-light p-3 rounded">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="observacoes_autorizacao">Observações da Autorização</label>
                            <textarea class="form-control" id="observacoes_autorizacao" rows="3" placeholder="Adicione observações sobre a decisão..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success">
                    <i class="fas fa-check mr-2"></i>
                    Aprovar
                </button>
                <button type="button" class="btn btn-danger">
                    <i class="fas fa-times mr-2"></i>
                    Rejeitar
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .info-box {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: .25rem;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .badge-priority-urgente {
        background-color: #dc3545 !important;
    }
    
    .badge-priority-alta {
        background-color: #fd7e14 !important;
    }
    
    .badge-priority-media {
        background-color: #ffc107 !important;
    }
    
    .badge-priority-baixa {
        background-color: #28a745 !important;
    }
    
    .form-group label {
        font-weight: 600;
        color: #495057;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        atualizarContagens();
        setInterval(atualizarContagens, 30000);
    });

    function atualizarContagens() {
        $.get('/api/pedidos-pendentes-agrupados', function(r){ if(r.success) $('#count-pendentes').text(r.data.length); });
        $.get('/api/pedidos-aprovados-agrupados', function(r){ if(r.success) $('#count-aprovadas').text(r.data.length); });
        $.get('/api/pedidos-rejeitados-agrupados', function(r){ if(r.success) $('#count-rejeitadas').text(r.data.length); });
    }
</script>
@stop
