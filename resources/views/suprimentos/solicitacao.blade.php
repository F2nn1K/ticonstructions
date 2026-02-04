@extends('adminlte::page')

@section('title', 'Solicitação de Compras')

@section('css')
<style>
    .urgencia-alta { background-color: #f8d7da !important; }
    .urgencia-media { background-color: #fff3cd !important; }
    .urgencia-normal { background-color: #d4edda !important; }
    .badge-urgencia-alta { background-color: #dc3545; }
    .badge-urgencia-media { background-color: #ffc107; color: #333; }
    .badge-urgencia-normal { background-color: #28a745; }
    
    /* Autocomplete de produtos do estoque */
    .produto-autocomplete-wrapper {
        position: relative;
        width: 100%;
    }
    .produto-autocomplete-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 9999;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        display: none;
        min-width: 300px;
    }
    .produto-autocomplete-list .item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        white-space: normal;
        word-wrap: break-word;
    }
    .produto-autocomplete-list .item:hover {
        background: #f8f9fa;
    }
    .produto-autocomplete-list .item .produto-nome {
        font-weight: bold;
        color: #333;
        font-size: 13px;
        display: block;
        margin-bottom: 3px;
    }
    .produto-autocomplete-list .item .produto-info {
        font-size: 11px;
        color: #666;
        margin-top: 2px;
        display: block;
    }
    .produto-autocomplete-list .item .produto-estoque {
        font-size: 11px;
        color: #28a745;
        font-weight: bold;
        display: block;
        margin-top: 2px;
    }
    
    /* Ajustes na tabela de itens */
    #tabelaItens td, #tabelaItensOS td {
        vertical-align: middle;
        padding: 8px 5px;
    }
    
    #tabelaItens .item-descricao,
    #tabelaItensOS .item-descricao-os {
        width: 100%;
        min-width: 200px;
    }
    
    /* Espaçamento do botão Adicionar Item */
    #btnAddItem, #btnAddItemOS {
        margin-top: 15px;
    }
    
    /* Autocomplete de Centro de Custo */
    .centro-custo-autocomplete-wrapper {
        position: relative;
    }
    .centro-custo-autocomplete-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 9999;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        display: none;
    }
    .centro-custo-autocomplete-list .cc-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
    }
    .centro-custo-autocomplete-list .cc-item:hover {
        background: #007bff;
        color: #fff;
    }
    .centro-custo-autocomplete-list .cc-item .cc-nome {
        font-weight: 500;
    }
    .centro-custo-autocomplete-list .cc-item:hover .cc-nome {
        color: #fff;
    }
    .centro-custo-tag {
        display: inline-flex;
        align-items: center;
        background: #007bff;
        color: #fff;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        margin-top: 5px;
    }
    .centro-custo-tag .remove-cc {
        margin-left: 8px;
        cursor: pointer;
        font-weight: bold;
    }
    .centro-custo-tag .remove-cc:hover {
        color: #ffcccc;
    }
    
    /* Melhorar visualização do modal */
    .modal-xl {
        max-width: 95%;
    }
    
    @media (min-width: 1200px) {
        .modal-xl {
            max-width: 1400px;
        }
    }
</style>
@stop

@section('content_header')
<h1><i class="fas fa-hand-paper"></i> Solicitação de Compras</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- CARDS DE ESTATÍSTICAS -->
    <div class="row mb-3">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pendentes'] ?? 0 }}</h3>
                    <p>Aguardando Cotação</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="javascript:void(0)" class="small-box-footer" onclick="filtrarStatus('pendente')">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['em_cotacao'] ?? 0 }}</h3>
                    <p>Em Cotação</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <a href="javascript:void(0)" class="small-box-footer" onclick="filtrarStatus('em_cotacao')">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['urgentes'] ?? 0 }}</h3>
                    <p>Urgentes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="javascript:void(0)" class="small-box-footer" onclick="filtrarUrgencia('alta')">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['finalizadas'] ?? 0 }}</h3>
                    <p>Finalizadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="javascript:void(0)" class="small-box-footer" onclick="filtrarStatus('finalizada')">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- LISTA DE SOLICITAÇÕES -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Solicitações Realizadas</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm" id="btnSolicitacaoOS">
                    <i class="fas fa-clipboard-list"></i> Solicitação via O.S.
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnNovaSolicitacao">
                    <i class="fas fa-plus"></i> Nova Solicitação
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" action="{{ route('suprimentos.solicitacao') }}" class="mb-3" id="formFiltros">
                <div class="row">
                    <div class="col-md-3">
                        <label class="small text-muted mb-1">Status</label>
                        <select class="form-control form-control-sm" name="filtro_status" id="filtro_status">
                            <option value="aguardando" {{ request('filtro_status', 'aguardando') == 'aguardando' ? 'selected' : '' }}>Aguardando Cotação</option>
                            <option value="em_cotacao" {{ request('filtro_status') == 'em_cotacao' ? 'selected' : '' }}>Em Cotação</option>
                            <option value="finalizada" {{ request('filtro_status') == 'finalizada' ? 'selected' : '' }}>Finalizadas</option>
                            <option value="rejeitada" {{ request('filtro_status') == 'rejeitada' ? 'selected' : '' }}>Rejeitadas</option>
                            <option value="todas" {{ request('filtro_status') == 'todas' ? 'selected' : '' }}>Todas</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted mb-1">Centro de Custo (Obra)</label>
                        <div class="centro-custo-autocomplete-wrapper">
                            <input type="text" class="form-control form-control-sm" id="filtro_centro_custo" 
                                   placeholder="Digite 3 letras para buscar..." autocomplete="off">
                            <input type="hidden" name="centro_custo_id" id="filtro_centro_custo_id" value="{{ request('centro_custo_id') }}">
                            <div class="centro-custo-autocomplete-list" id="listaCentroCusto"></div>
                        </div>
                        <div id="centroCustoSelecionado">
                            @if(request('centro_custo_id') && $centroCustoNome)
                            <span class="centro-custo-tag" data-id="{{ request('centro_custo_id') }}">
                                <span class="cc-nome-tag">{{ $centroCustoNome }}</span>
                                <span class="remove-cc" title="Remover filtro">&times;</span>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary mr-1">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('suprimentos.solicitacao') }}?filtro_status=aguardando" class="btn btn-sm btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="tabelaSolicitacoes">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Nº Solicitação</th>
                            <th>Data</th>
                            <th>Solicitante</th>
                            <th>Obra</th>
                            <th>Descrição</th>
                            <th>Urgência</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitacoes as $sol)
                        <tr>
                            <td><strong>{{ $sol->numero }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($sol->created_at)->format('d/m/Y') }}</td>
                            <td>{{ $sol->solicitante ?? '-' }}</td>
                            <td>{{ $sol->obra ?? '-' }}</td>
                            <td>{{ Str::limit($sol->descricao, 40) }}</td>
                            <td>
                                @if($sol->urgencia == 'alta')
                                    <span class="badge badge-urgencia-alta">Alta</span>
                                @elseif($sol->urgencia == 'media')
                                    <span class="badge badge-urgencia-media">Média</span>
                                @else
                                    <span class="badge badge-urgencia-normal">Normal</span>
                                @endif
                            </td>
                            <td>
                                @if($sol->status == 'pendente')
                                    <span class="badge badge-warning">Aguardando</span>
                                @elseif($sol->status == 'em_cotacao')
                                    <span class="badge badge-info">Em Cotação</span>
                                @elseif($sol->status == 'finalizada')
                                    <span class="badge badge-success">Finalizada</span>
                                @elseif($sol->status == 'rejeitada' || $sol->status == 'reprovada')
                                    <span class="badge badge-danger">Rejeitada</span>
                                @elseif($sol->status == 'cancelada')
                                    <span class="badge badge-dark">Cancelada</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $sol->status)) }}</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-info btn-ver" 
                                        data-id="{{ $sol->id }}" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </button>
                                {{-- DESATIVADO: Edição de solicitação desabilitada para evitar problemas na cotação
                                @if($sol->pode_editar ?? false)
                                <button type="button" class="btn btn-sm btn-warning btn-editar" 
                                        data-id="{{ $sol->id }}" title="Editar/Incluir Itens">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                @endif
                                --}}
                                @if($sol->status == 'em_cotacao' || $sol->status == 'aberta')
                                <button type="button" class="btn btn-sm btn-primary btn-ir-cotacao" 
                                        data-id="{{ $sol->id }}" title="Ver Cotação">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </button>
                                @endif
                                @if($canDelete ?? $isAdmin)
                                <button type="button" class="btn btn-sm btn-danger btn-excluir" 
                                        data-id="{{ $sol->id }}" data-numero="{{ $sol->numero }}" title="Rejeitar">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Nenhuma solicitação cadastrada</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova/Editar Solicitação -->
<div class="modal fade" id="modalSolicitacao" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="fas fa-hand-paper mr-2"></i><span id="modalTitulo">Nova Solicitação</span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formSolicitacao">
                <div class="modal-body">
                    <input type="hidden" id="solicitacao-id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Urgência *</label>
                                <select class="form-control" id="urgencia" name="urgencia" required>
                                    <option value="normal">Normal</option>
                                    <option value="media">Média</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Descrição / Título *</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" 
                               placeholder="Ex: Material de escritório, Peças para manutenção..." required>
                    </div>
                    
                    <div class="form-group">
                        <label>Justificativa</label>
                        <textarea class="form-control" id="justificativa" name="justificativa" rows="2"
                                  placeholder="Explique a necessidade desta compra..."></textarea>
                    </div>
                    
                    <hr>
                    
                    <!-- Itens da Solicitação -->
                    <h5><i class="fas fa-list mr-1"></i> Itens da Solicitação</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tabelaItens" style="table-layout: auto; width: 100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th style="min-width: 300px; width: 40%;">Descrição do Item *</th>
                                    <th style="min-width: 80px; width: 12%;">Quantidade *</th>
                                    <th style="min-width: 100px; width: 12%;">Unidade</th>
                                    <th style="min-width: 150px; width: 28%;">Observação</th>
                                    <th style="min-width: 50px; width: 8%;">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="itensBody">
                                <tr>
                                    <td><input type="text" class="form-control form-control-sm item-descricao" placeholder="Descrição do item" required></td>
                                    <td><input type="number" class="form-control form-control-sm item-quantidade" min="1" value="1" required></td>
                                    <td>
                                        <select class="form-control form-control-sm item-unidade">
                                            <option value="UN">UN</option>
                                            <option value="PC">PC</option>
                                            <option value="PCT">PCT</option>
                                            <option value="CX">CX</option>
                                            <option value="KG">KG</option>
                                            <option value="MT">MT</option>
                                            <option value="M2">M2</option>
                                            <option value="M3">M3</option>
                                            <option value="LT">LT</option>
                                            <option value="FD">FD</option>
                                            <option value="RL">RL</option>
                                            <option value="SC">SC</option>
                                            <option value="BD">BD</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm item-observacao" placeholder="Obs..."></td>
                                    <td><button type="button" class="btn btn-danger btn-sm btn-remover-item"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="btnAddItem">
                        <i class="fas fa-plus"></i> Adicionar Item
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvar">
                        <i class="fas fa-save"></i> Salvar Solicitação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white py-2">
                <h5 class="modal-title"><i class="fas fa-file-alt mr-2"></i>Detalhes da Solicitação</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Nº Solicitação:</strong><br>
                        <span id="det-numero" class="h5 text-primary"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Data:</strong><br>
                        <span id="det-data"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong><br>
                        <span id="det-status"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Solicitante:</strong><br>
                        <span id="det-solicitante"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Descrição:</strong><br>
                    <span id="det-descricao"></span>
                </div>
                <div class="mb-3">
                    <strong>Justificativa:</strong><br>
                    <span id="det-justificativa"></span>
                </div>
                
                <hr>
                <h5><i class="fas fa-list mr-1"></i> Itens Solicitados</h5>
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Descrição</th>
                            <th class="text-center">Qtd</th>
                            <th class="text-center">Unidade</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody id="det-itens">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Solicitação via O.S. -->
<div class="modal fade" id="modalSolicitacaoOS" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title"><i class="fas fa-clipboard-list mr-2"></i>Solicitação Vinculada a O.S.</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formSolicitacaoOS">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Esta solicitação será vinculada a uma Ordem de Serviço existente. O fluxo completo (cotação → ordem de compra → pagamento → recebimento) será atrelado à O.S. selecionada.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ordem de Serviço *</label>
                                <select class="form-control" id="os_id" name="os_id" required>
                                    <option value="">Selecione uma O.S...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Urgência *</label>
                                <select class="form-control" id="urgencia_os" name="urgencia" required>
                                    <option value="normal">Normal</option>
                                    <option value="media">Média</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div id="infoOS" style="display: none;">
                        <div class="card bg-light mb-3">
                            <div class="card-body py-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">Nº O.S.:</small><br>
                                        <strong id="os-numero"></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Data:</small><br>
                                        <span id="os-data"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Centro de Custo:</small><br>
                                        <span id="os-centro-custo"></span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <small class="text-muted">Descrição:</small><br>
                                        <span id="os-descricao"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Descrição da Solicitação *</label>
                        <input type="text" class="form-control" id="descricao_os" name="descricao" 
                               placeholder="Ex: Material para manutenção da O.S..." required>
                    </div>
                    
                    <div class="form-group">
                        <label>Justificativa</label>
                        <textarea class="form-control" id="justificativa_os" name="justificativa" rows="2"
                                  placeholder="Explique a necessidade desta compra..."></textarea>
                    </div>
                    
                    <hr>
                    
                    <!-- Itens da Solicitação -->
                    <h5><i class="fas fa-list mr-1"></i> Itens da Solicitação</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tabelaItensOS" style="table-layout: auto; width: 100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th style="min-width: 300px; width: 40%;">Descrição do Item *</th>
                                    <th style="min-width: 80px; width: 12%;">Quantidade *</th>
                                    <th style="min-width: 100px; width: 12%;">Unidade</th>
                                    <th style="min-width: 150px; width: 28%;">Observação</th>
                                    <th style="min-width: 50px; width: 8%;">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="itensBodyOS">
                                <tr>
                                    <td><input type="text" class="form-control form-control-sm item-descricao-os" placeholder="Descrição do item" required></td>
                                    <td><input type="number" class="form-control form-control-sm item-quantidade-os" min="1" value="1" required></td>
                                    <td>
                                        <select class="form-control form-control-sm item-unidade-os">
                                            <option value="UN">UN</option>
                                            <option value="PC">PC</option>
                                            <option value="PCT">PCT</option>
                                            <option value="CX">CX</option>
                                            <option value="KG">KG</option>
                                            <option value="MT">MT</option>
                                            <option value="M2">M2</option>
                                            <option value="M3">M3</option>
                                            <option value="LT">LT</option>
                                            <option value="FD">FD</option>
                                            <option value="RL">RL</option>
                                            <option value="SC">SC</option>
                                            <option value="BD">BD</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm item-observacao-os" placeholder="Obs..."></td>
                                    <td><button type="button" class="btn btn-danger btn-sm btn-remover-item-os"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="btnAddItemOS">
                        <i class="fas fa-plus"></i> Adicionar Item
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnSalvarOS">
                        <i class="fas fa-save"></i> Criar Solicitação Vinculada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
$(function() {
    // ===== SOLICITAÇÃO VIA O.S. =====
    
    // Abrir modal solicitação via O.S.
    $('#btnSolicitacaoOS').on('click', function() {
        $('#formSolicitacaoOS')[0].reset();
        $('#infoOS').hide();
        $('#itensBodyOS').html(`
            <tr>
                <td><input type="text" class="form-control form-control-sm item-descricao-os" placeholder="Descrição do item" required></td>
                <td><input type="number" class="form-control form-control-sm item-quantidade-os" min="1" value="1" required></td>
                <td>
                    <select class="form-control form-control-sm item-unidade-os">
                        <option value="UN">UN</option>
                        <option value="PC">PC</option>
                        <option value="PCT">PCT</option>
                        <option value="CX">CX</option>
                        <option value="KG">KG</option>
                        <option value="MT">MT</option>
                        <option value="M2">M2</option>
                        <option value="M3">M3</option>
                        <option value="LT">LT</option>
                        <option value="FD">FD</option>
                        <option value="RL">RL</option>
                        <option value="SC">SC</option>
                        <option value="BD">BD</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm item-observacao-os" placeholder="Obs..."></td>
                <td><button type="button" class="btn btn-danger btn-sm btn-remover-item-os"><i class="fas fa-trash"></i></button></td>
            </tr>
        `);
        
        // Inicializar autocomplete para O.S.
        setTimeout(function() {
            inicializarAutocompleteProdutosOS();
        }, 100);
        
        // Carregar O.S. abertas (todas=1 para mostrar todas as O.S. de todos os usuários)
        $.get('/area-tecnica/api/gestao-os/listar?status=aberta&todas=1', function(response) {
            var select = $('#os_id');
            select.html('<option value="">Selecione uma O.S...</option>');
            if (response.ordens && response.ordens.length > 0) {
                response.ordens.forEach(function(os) {
                    // Montar texto com número, centro de custo e criado por
                    var texto = os.numero_os;
                    if (os.centro_custo_nome) {
                        texto += ' | ' + os.centro_custo_nome;
                    }
                    if (os.criado_por) {
                        texto += ' | ' + os.criado_por;
                    }
                    select.append('<option value="' + os.id + '" data-numero="' + os.numero_os + '" data-data="' + os.data_os + '" data-descricao="' + (os.descricao_servico || '') + '" data-cc="' + (os.centro_custo_nome || '-') + '" data-cc-id="' + (os.centro_custo_id || '') + '" data-criador="' + (os.criado_por || '-') + '">' + texto + '</option>');
                });
            }
        });
        
        $('#modalSolicitacaoOS').modal('show');
    });
    
    // Ao selecionar uma O.S., mostrar informações
    $('#os_id').on('change', function() {
        var selected = $(this).find(':selected');
        if (selected.val()) {
            $('#os-numero').text(selected.data('numero'));
            $('#os-data').text(selected.data('data') ? new Date(selected.data('data') + 'T00:00:00').toLocaleDateString('pt-BR') : '-');
            $('#os-centro-custo').text(selected.data('cc'));
            $('#os-descricao').text(selected.data('descricao') || '-');
            $('#infoOS').show();
            
            // Preencher descrição automaticamente
            if (!$('#descricao_os').val()) {
                $('#descricao_os').val('Material para O.S. ' + selected.data('numero'));
            }
        } else {
            $('#infoOS').hide();
        }
    });
    
    // Função para inicializar autocomplete de produtos do estoque (modal O.S.)
    function inicializarAutocompleteProdutosOS() {
        $('.item-descricao-os').each(function() {
            var input = $(this);
            
            if (input.data('autocomplete-init')) return;
            input.data('autocomplete-init', true);
            
            if (!input.parent().hasClass('produto-autocomplete-wrapper')) {
                input.wrap('<div class="produto-autocomplete-wrapper"></div>');
                input.after('<div class="produto-autocomplete-list"></div>');
            }
            
            var wrapper = input.parent();
            var lista = wrapper.find('.produto-autocomplete-list');
            var timeoutBusca;
            
            input.on('input', function() {
                var termo = $(this).val().trim();
                clearTimeout(timeoutBusca);
                
                if (termo.length < 3) {
                    lista.hide();
                    return;
                }
                
                timeoutBusca = setTimeout(function() {
                    lista.empty().append('<div class="item text-center text-muted">Buscando...</div>').show();
                    
                    $.get('/api/suprimentos/estoque/buscar', { termo: termo })
                        .done(function(produtos) {
                            lista.empty();
                            if (produtos.length > 0) {
                                produtos.forEach(function(p) {
                                    var itemHtml = '<div class="item" data-nome="' + p.nome + '" data-unidade="' + (p.unidade || 'UN') + '">' +
                                        '<div class="produto-nome">' + p.nome + '</div>' +
                                        '<div class="produto-info">' + (p.descricao || '') + '</div>' +
                                        '<div class="produto-estoque">Estoque: ' + (p.quantidade || 0) + ' ' + (p.unidade || 'UN') + '</div>' +
                                        '</div>';
                                    lista.append(itemHtml);
                                });
                            } else {
                                lista.append('<div class="item text-muted">Nenhum produto encontrado no estoque</div>');
                            }
                            lista.show();
                        })
                        .fail(function() {
                            lista.empty().append('<div class="item text-danger">Erro ao buscar produtos.</div>').show();
                        });
                }, 300);
            });
            
            lista.on('click', '.item[data-nome]', function() {
                var nome = $(this).data('nome');
                var unidade = $(this).data('unidade');
                
                input.val(nome);
                input.closest('tr').find('.item-unidade-os').val(unidade);
                lista.hide();
            });
        });
    }
    
    // Adicionar item O.S.
    $('#btnAddItemOS').on('click', function() {
        $('#itensBodyOS').append(`
            <tr>
                <td><input type="text" class="form-control form-control-sm item-descricao-os" placeholder="Descrição do item" required></td>
                <td><input type="number" class="form-control form-control-sm item-quantidade-os" min="1" value="1" required></td>
                <td>
                    <select class="form-control form-control-sm item-unidade-os">
                        <option value="UN">UN</option>
                        <option value="PC">PC</option>
                        <option value="PCT">PCT</option>
                        <option value="CX">CX</option>
                        <option value="KG">KG</option>
                        <option value="MT">MT</option>
                        <option value="M2">M2</option>
                        <option value="M3">M3</option>
                        <option value="LT">LT</option>
                        <option value="FD">FD</option>
                        <option value="RL">RL</option>
                        <option value="SC">SC</option>
                        <option value="BD">BD</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm item-observacao-os" placeholder="Obs..."></td>
                <td><button type="button" class="btn btn-danger btn-sm btn-remover-item-os"><i class="fas fa-trash"></i></button></td>
            </tr>
        `);
        inicializarAutocompleteProdutosOS();
    });
    
    // Remover item O.S.
    $(document).on('click', '.btn-remover-item-os', function() {
        if ($('#itensBodyOS tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'A solicitação deve ter pelo menos 1 item!'
            });
        }
    });
    
    // Salvar solicitação vinculada a O.S.
    var salvandoSolicitacaoOS = false; // Flag para evitar duplo clique
    
    $('#formSolicitacaoOS').on('submit', function(e) {
        e.preventDefault();
        
        // Evitar duplo clique
        if (salvandoSolicitacaoOS) {
            console.log('Solicitação via O.S. já está sendo salva, aguarde...');
            return;
        }
        
        var osId = $('#os_id').val();
        if (!osId) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Selecione uma Ordem de Serviço!'
            });
            return;
        }
        
        // Coletar itens
        var itens = [];
        $('#itensBodyOS tr').each(function() {
            var descricao = $(this).find('.item-descricao-os').val();
            if (descricao) {
                itens.push({
                    descricao: descricao,
                    quantidade: $(this).find('.item-quantidade-os').val() || 1,
                    unidade: $(this).find('.item-unidade-os').val() || 'UN',
                    observacao: $(this).find('.item-observacao-os').val() || ''
                });
            }
        });
        
        if (itens.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Adicione pelo menos 1 item à solicitação!'
            });
            return;
        }
        
        var selected = $('#os_id').find(':selected');
        var centroCustoId = selected.data('cc-id');
        
        // Ativar flag e desabilitar botão
        salvandoSolicitacaoOS = true;
        var btnSalvarOS = $('#btnSalvarOS');
        var btnTextoOriginal = btnSalvarOS.html();
        btnSalvarOS.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        
        $.ajax({
            url: '/api/suprimentos/solicitacoes/vincular-os',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ordem_servico_id: osId,
                numero_os: selected.data('numero'),
                centro_custo_id: centroCustoId,
                urgencia: $('#urgencia_os').val(),
                descricao: $('#descricao_os').val(),
                justificativa: $('#justificativa_os').val(),
                itens: itens
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        html: response.message + (response.cotacao_numero ? '<br><small class="text-muted">Cotação ' + response.cotacao_numero + ' gerada.</small>' : ''),
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    // Reativar botão em caso de erro
                    salvandoSolicitacaoOS = false;
                    btnSalvarOS.prop('disabled', false).html(btnTextoOriginal);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                // Reativar botão em caso de erro
                salvandoSolicitacaoOS = false;
                btnSalvarOS.prop('disabled', false).html(btnTextoOriginal);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: xhr.responseJSON?.message || 'Erro ao salvar solicitação'
                });
            }
        });
    });
    
    // ===== SOLICITAÇÃO NORMAL =====
    
    // Função para inicializar autocomplete de produtos do estoque
    function inicializarAutocompleteProdutos() {
        $('.item-descricao').each(function() {
            var input = $(this);
            
            // Evitar duplicar o autocomplete
            if (input.data('autocomplete-init')) return;
            input.data('autocomplete-init', true);
            
            // Criar wrapper e lista se não existir
            if (!input.parent().hasClass('produto-autocomplete-wrapper')) {
                input.wrap('<div class="produto-autocomplete-wrapper"></div>');
                input.after('<div class="produto-autocomplete-list"></div>');
            }
            
            var wrapper = input.parent();
            var lista = wrapper.find('.produto-autocomplete-list');
            var timeoutBusca;
            
            input.on('input', function() {
                var termo = $(this).val().trim();
                clearTimeout(timeoutBusca);
                
                if (termo.length < 3) {
                    lista.hide();
                    return;
                }
                
                timeoutBusca = setTimeout(function() {
                    lista.empty().append('<div class="item text-center text-muted">Buscando...</div>').show();
                    
                    $.get('/api/suprimentos/estoque/buscar', { termo: termo })
                        .done(function(produtos) {
                            lista.empty();
                            if (produtos.length > 0) {
                                produtos.forEach(function(p) {
                                    var itemHtml = '<div class="item" data-nome="' + p.nome + '" data-unidade="' + (p.unidade || 'UN') + '">' +
                                        '<div class="produto-nome">' + p.nome + '</div>' +
                                        '<div class="produto-info">' + (p.descricao || '') + '</div>' +
                                        '<div class="produto-estoque">Estoque: ' + (p.quantidade || 0) + ' ' + (p.unidade || 'UN') + '</div>' +
                                        '</div>';
                                    lista.append(itemHtml);
                                });
                            } else {
                                lista.append('<div class="item text-muted">Nenhum produto encontrado no estoque</div>');
                            }
                            lista.show();
                        })
                        .fail(function() {
                            lista.empty().append('<div class="item text-danger">Erro ao buscar produtos.</div>').show();
                        });
                }, 300);
            });
            
            // Selecionar produto da lista
            lista.on('click', '.item[data-nome]', function() {
                var nome = $(this).data('nome');
                var unidade = $(this).data('unidade');
                
                input.val(nome);
                input.closest('tr').find('.item-unidade').val(unidade);
                lista.hide();
            });
            
            // Fechar ao clicar fora
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.produto-autocomplete-wrapper').length) {
                    $('.produto-autocomplete-list').hide();
                }
            });
        });
    }
    
    // Abrir modal nova solicitação
    $('#btnNovaSolicitacao').on('click', function() {
        $('#modalTitulo').text('Nova Solicitação');
        $('#formSolicitacao')[0].reset();
        $('#solicitacao-id').val('');
        $('#itensBody').html(`
            <tr>
                <td><input type="text" class="form-control form-control-sm item-descricao" placeholder="Descrição do item" required></td>
                <td><input type="number" class="form-control form-control-sm item-quantidade" min="1" value="1" required></td>
                <td>
                    <select class="form-control form-control-sm item-unidade">
                        <option value="UN">UN</option>
                        <option value="PC">PC</option>
                        <option value="PCT">PCT</option>
                        <option value="CX">CX</option>
                        <option value="KG">KG</option>
                        <option value="MT">MT</option>
                        <option value="M2">M2</option>
                        <option value="M3">M3</option>
                        <option value="LT">LT</option>
                        <option value="FD">FD</option>
                        <option value="RL">RL</option>
                        <option value="SC">SC</option>
                        <option value="BD">BD</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm item-observacao" placeholder="Obs..."></td>
                <td><button type="button" class="btn btn-danger btn-sm btn-remover-item"><i class="fas fa-trash"></i></button></td>
            </tr>
        `);
        $('#modalSolicitacao').modal('show');
        // Inicializar autocomplete
        setTimeout(function() {
            inicializarAutocompleteProdutos();
        }, 100);
    });
    
    // Adicionar item
    $('#btnAddItem').on('click', function() {
        $('#itensBody').append(`
            <tr>
                <td><input type="text" class="form-control form-control-sm item-descricao" placeholder="Descrição do item" required></td>
                <td><input type="number" class="form-control form-control-sm item-quantidade" min="1" value="1" required></td>
                <td>
                    <select class="form-control form-control-sm item-unidade">
                        <option value="UN">UN</option>
                        <option value="PC">PC</option>
                        <option value="PCT">PCT</option>
                        <option value="CX">CX</option>
                        <option value="KG">KG</option>
                        <option value="MT">MT</option>
                        <option value="M2">M2</option>
                        <option value="M3">M3</option>
                        <option value="LT">LT</option>
                        <option value="FD">FD</option>
                        <option value="RL">RL</option>
                        <option value="SC">SC</option>
                        <option value="BD">BD</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm item-observacao" placeholder="Obs..."></td>
                <td><button type="button" class="btn btn-danger btn-sm btn-remover-item"><i class="fas fa-trash"></i></button></td>
            </tr>
        `);
        // Inicializar autocomplete no novo campo
        inicializarAutocompleteProdutos();
    });
    
    // Remover item
    $(document).on('click', '.btn-remover-item', function() {
        if ($('#itensBody tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'A solicitação deve ter pelo menos 1 item!'
            });
        }
    });
    
    // Salvar solicitação
    var salvandoSolicitacao = false; // Flag para evitar duplo clique
    
    $('#formSolicitacao').on('submit', function(e) {
        e.preventDefault();
        
        // Evitar duplo clique
        if (salvandoSolicitacao) {
            console.log('Solicitação já está sendo salva, aguarde...');
            return;
        }
        
        // Coletar itens
        var itens = [];
        $('#itensBody tr').each(function() {
            var descricao = $(this).find('.item-descricao').val();
            if (descricao) {
                itens.push({
                    descricao: descricao,
                    quantidade: $(this).find('.item-quantidade').val() || 1,
                    unidade: $(this).find('.item-unidade').val() || 'UN',
                    observacao: $(this).find('.item-observacao').val() || ''
                });
            }
        });
        
        if (itens.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Adicione pelo menos 1 item à solicitação!'
            });
            return;
        }
        
        var id = $('#solicitacao-id').val();
        var url = id ? '/api/suprimentos/solicitacoes/' + id : '/api/suprimentos/solicitacoes';
        var method = id ? 'PUT' : 'POST';
        
        // Ativar flag e desabilitar botão
        salvandoSolicitacao = true;
        var btnSalvar = $('#btnSalvar');
        var btnTextoOriginal = btnSalvar.html();
        btnSalvar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        
        $.ajax({
            url: url,
            method: method,
            data: {
                _token: '{{ csrf_token() }}',
                urgencia: $('#urgencia').val(),
                descricao: $('#descricao').val(),
                justificativa: $('#justificativa').val(),
                itens: itens
            },
            success: function(response) {
                if (response.success) {
                    var msg = response.message;
                    if (response.cotacao_numero) {
                        msg = 'Solicitação criada! Cotação ' + response.cotacao_numero + ' gerada automaticamente.';
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: msg,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    // Reativar botão em caso de erro
                    salvandoSolicitacao = false;
                    btnSalvar.prop('disabled', false).html(btnTextoOriginal);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                // Reativar botão em caso de erro
                salvandoSolicitacao = false;
                btnSalvar.prop('disabled', false).html(btnTextoOriginal);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: xhr.responseJSON?.message || 'Erro ao salvar solicitação'
                });
            }
        });
    });
    
    // Ver detalhes
    $(document).on('click', '.btn-ver', function() {
        var id = $(this).data('id');
        
        $.get('/api/suprimentos/solicitacoes/' + id, function(response) {
            if (response.success) {
                var sol = response.solicitacao;
                var itens = response.itens;
                
                $('#det-numero').text(sol.numero);
                $('#det-data').text(new Date(sol.created_at).toLocaleDateString('pt-BR'));
                $('#det-solicitante').text(sol.solicitante || '-');
                $('#det-descricao').text(sol.descricao);
                $('#det-justificativa').text(sol.justificativa || '-');
                
                // Status badge
                var statusBadge = '';
                if (sol.status === 'pendente') statusBadge = '<span class="badge badge-warning">Pendente</span>';
                else if (sol.status === 'aprovada') statusBadge = '<span class="badge badge-success">Aprovada</span>';
                else if (sol.status === 'reprovada') statusBadge = '<span class="badge badge-danger">Reprovada</span>';
                else if (sol.status === 'em_cotacao') statusBadge = '<span class="badge badge-info">Em Cotação</span>';
                else statusBadge = '<span class="badge badge-secondary">' + sol.status + '</span>';
                $('#det-status').html(statusBadge);
                
                // Itens
                var itensHtml = '';
                itens.forEach(function(item) {
                    itensHtml += '<tr>';
                    itensHtml += '<td>' + item.descricao + '</td>';
                    itensHtml += '<td class="text-center">' + item.quantidade + '</td>';
                    itensHtml += '<td class="text-center">' + item.unidade + '</td>';
                    itensHtml += '<td>' + (item.observacao || '-') + '</td>';
                    itensHtml += '</tr>';
                });
                $('#det-itens').html(itensHtml);
                
                $('#modalDetalhes').modal('show');
            }
        });
    });
    
    // Ir para cotação específica
    $(document).on('click', '.btn-ir-cotacao', function() {
        var id = $(this).data('id');
        
        // Redirecionar para a página de cotação e abrir os detalhes automaticamente
        // O ID da solicitação é o mesmo ID da cotação (já que são a mesma coisa)
        window.location.href = '/suprimentos/cotacao?abrir_cotacao=' + id;
    });
    
    /* DESATIVADO: Edição de solicitação desabilitada para evitar problemas na cotação
    // Editar
    $(document).on('click', '.btn-editar', function() {
        var id = $(this).data('id');
        
        $.get('/api/suprimentos/solicitacoes/' + id, function(response) {
            if (response.success) {
                var sol = response.solicitacao;
                var itens = response.itens;
                
                $('#modalTitulo').text('Editar Solicitação');
                $('#solicitacao-id').val(sol.id);
                $('#urgencia').val(sol.urgencia);
                $('#descricao').val(sol.descricao);
                $('#justificativa').val(sol.justificativa);
                
                // Preencher itens
                var itensHtml = '';
                itens.forEach(function(item) {
                    itensHtml += `
                        <tr>
                            <td><input type="text" class="form-control form-control-sm item-descricao" value="${item.descricao}" required></td>
                            <td><input type="number" class="form-control form-control-sm item-quantidade" min="1" value="${item.quantidade}" required></td>
                            <td>
                                <select class="form-control form-control-sm item-unidade">
                                    <option value="UN" ${item.unidade === 'UN' ? 'selected' : ''}>UN</option>
                                    <option value="PC" ${item.unidade === 'PC' ? 'selected' : ''}>PC</option>
                                    <option value="PCT" ${item.unidade === 'PCT' ? 'selected' : ''}>PCT</option>
                                    <option value="CX" ${item.unidade === 'CX' ? 'selected' : ''}>CX</option>
                                    <option value="KG" ${item.unidade === 'KG' ? 'selected' : ''}>KG</option>
                                    <option value="MT" ${item.unidade === 'MT' ? 'selected' : ''}>MT</option>
                                    <option value="M2" ${item.unidade === 'M2' ? 'selected' : ''}>M2</option>
                                    <option value="M3" ${item.unidade === 'M3' ? 'selected' : ''}>M3</option>
                                    <option value="LT" ${item.unidade === 'LT' ? 'selected' : ''}>LT</option>
                                    <option value="FD" ${item.unidade === 'FD' ? 'selected' : ''}>FD</option>
                                    <option value="RL" ${item.unidade === 'RL' ? 'selected' : ''}>RL</option>
                                    <option value="SC" ${item.unidade === 'SC' ? 'selected' : ''}>SC</option>
                                    <option value="BD" ${item.unidade === 'BD' ? 'selected' : ''}>BD</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control form-control-sm item-observacao" value="${item.observacao || ''}"></td>
                            <td><button type="button" class="btn btn-danger btn-sm btn-remover-item"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    `;
                });
                $('#itensBody').html(itensHtml);
                
                $('#modalSolicitacao').modal('show');
            }
        });
    });
    */
    
    // Cancelar solicitação
    $(document).on('click', '.btn-cancelar', function() {
        var id = $(this).data('id');
        var numero = $(this).data('numero');
        
        Swal.fire({
            title: 'Cancelar Solicitação?',
            text: 'Tem certeza que deseja cancelar a solicitação ' + numero + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, cancelar!',
            cancelButtonText: 'Não'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/solicitacoes/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelada!',
                                text: 'Solicitação cancelada com sucesso.'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: xhr.responseJSON?.message || 'Erro ao cancelar'
                        });
                    }
                });
            }
        });
    });
    
    // Rejeitar solicitação (apenas admin) - mantém histórico
    $(document).on('click', '.btn-excluir', function() {
        var id = $(this).data('id');
        var numero = $(this).data('numero');
        
        Swal.fire({
            title: 'Rejeitar Solicitação',
            html: '<p>Você está prestes a <strong>REJEITAR</strong> a solicitação <strong>' + numero + '</strong>.</p>' +
                  '<p class="text-success"><i class="fas fa-check-circle mr-1"></i>O histórico completo será mantido para consulta.</p>' +
                  '<hr>' +
                  '<label for="motivo-rejeicao" class="text-left d-block"><strong>Motivo da rejeição: *</strong></label>' +
                  '<textarea id="motivo-rejeicao" class="form-control" rows="3" placeholder="Informe o motivo da rejeição (mínimo 10 caracteres)..." required></textarea>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-ban"></i> Confirmar Rejeição',
            cancelButtonText: 'Voltar',
            preConfirm: () => {
                const motivo = document.getElementById('motivo-rejeicao').value;
                if (!motivo || motivo.trim().length < 10) {
                    Swal.showValidationMessage('O motivo deve ter pelo menos 10 caracteres');
                    return false;
                }
                return motivo;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/suprimentos/solicitacoes/' + id,
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    contentType: 'application/json',
                    data: JSON.stringify({ motivo: result.value }),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Rejeitada!',
                                text: response.message || 'Solicitação rejeitada com sucesso!'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: response.message || 'Erro ao rejeitar'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: xhr.responseJSON?.message || 'Erro ao rejeitar'
                        });
                    }
                });
            }
        });
    });
});

// Funções de filtro para os cards - redireciona para buscar do servidor
function filtrarStatus(status) {
    // Mapear status para o filtro correto
    var filtroStatus = 'todas';
    if (status === 'pendente') {
        filtroStatus = 'aguardando';
    } else if (status === 'em_cotacao') {
        filtroStatus = 'em_cotacao';
    } else if (status === 'finalizada') {
        filtroStatus = 'finalizada';
    }
    
    // Atualizar o select e submeter o formulário
    $('#filtro_status').val(filtroStatus);
    $('#formFiltros').submit();
}

function filtrarUrgencia(urgencia) {
    // Para urgência, mostrar todas e filtrar por urgência
    // Como não tem filtro de urgência no backend, vamos usar "todas" e filtrar no JS após carregar
    $('#filtro_status').val('todas');
    
    // Criar um campo hidden temporário para indicar filtro de urgência
    var inputUrgencia = $('<input>').attr({
        type: 'hidden',
        name: 'filtro_urgencia',
        value: urgencia
    });
    $('#formFiltros').append(inputUrgencia);
    $('#formFiltros').submit();
}

function limparFiltros() {
    $('#filtro_status').val('todas');
    $('#filtro_centro_custo').val('');
    $('#filtro_centro_custo_id').val('');
    $('#centroCustoSelecionado').empty();
    $('#formFiltros').submit();
}

// ===== AUTOCOMPLETE CENTRO DE CUSTO =====
$(function() {
    var inputCC = $('#filtro_centro_custo');
    var hiddenCC = $('#filtro_centro_custo_id');
    var listaCC = $('#listaCentroCusto');
    var containerSelecionado = $('#centroCustoSelecionado');
    var timeoutBuscaCC;
    
    // Buscar centros de custo ao digitar (mínimo 3 caracteres)
    inputCC.on('input', function() {
        var termo = $(this).val().trim();
        clearTimeout(timeoutBuscaCC);
        
        if (termo.length < 3) {
            listaCC.hide();
            return;
        }
        
        timeoutBuscaCC = setTimeout(function() {
            listaCC.empty().append('<div class="cc-item text-center text-muted">Buscando...</div>').show();
            
            $.get('/api/centros-custo/buscar-inicio', { termo: termo })
                .done(function(centros) {
                    listaCC.empty();
                    if (centros.length > 0) {
                        centros.forEach(function(cc) {
                            listaCC.append(
                                '<div class="cc-item" data-id="' + cc.id + '" data-nome="' + cc.nome + '">' +
                                '<span class="cc-nome">' + cc.nome + '</span>' +
                                '</div>'
                            );
                        });
                    } else {
                        listaCC.append('<div class="cc-item text-muted">Nenhum centro de custo encontrado</div>');
                    }
                    listaCC.show();
                })
                .fail(function() {
                    listaCC.empty().append('<div class="cc-item text-danger">Erro ao buscar.</div>').show();
                });
        }, 300);
    });
    
    // Selecionar centro de custo da lista
    listaCC.on('click', '.cc-item[data-id]', function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        
        hiddenCC.val(id);
        inputCC.val('');
        listaCC.hide();
        
        // Mostrar tag do centro de custo selecionado
        containerSelecionado.html(
            '<span class="centro-custo-tag" data-id="' + id + '">' +
            '<span class="cc-nome-tag">' + nome + '</span>' +
            '<span class="remove-cc" title="Remover filtro">&times;</span>' +
            '</span>'
        );
        
        // Adicionar campo hidden para o nome (para exibir após reload)
        if ($('#filtro_centro_custo_nome').length === 0) {
            $('<input type="hidden" name="centro_custo_nome" id="filtro_centro_custo_nome">').appendTo('#formFiltros');
        }
        $('#filtro_centro_custo_nome').val(nome);
    });
    
    // Remover centro de custo selecionado
    containerSelecionado.on('click', '.remove-cc', function() {
        hiddenCC.val('');
        containerSelecionado.empty();
        $('#filtro_centro_custo_nome').remove();
    });
    
    // Fechar lista ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.centro-custo-autocomplete-wrapper').length) {
            listaCC.hide();
        }
    });
    
    // Focus no input ao clicar no wrapper
    inputCC.on('focus', function() {
        if ($(this).val().length >= 3) {
            listaCC.show();
        }
    });
});
</script>
@stop
