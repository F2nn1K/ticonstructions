@extends('adminlte::page')

@section('title', 'Contas a Pagar')

@push('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content_header')
<h1><i class="fas fa-file-invoice-dollar mr-2"></i>Contas a Pagar</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Cards de Resumo -->
    <div class="row mb-3">
        <div class="col" style="flex: 0 0 20%; max-width: 20%;">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="totalBruto">R$ 0,00</h3>
                    <p>Valor Bruto</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>
        <div class="col" style="flex: 0 0 20%; max-width: 20%;">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalGeral">R$ 0,00</h3>
                    <p>Valor Líquido</p>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
        </div>
        <div class="col" style="flex: 0 0 20%; max-width: 20%;">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="totalPago">R$ 0,00</h3>
                    <p>Pago</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col" style="flex: 0 0 20%; max-width: 20%;">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="totalPendente">R$ 0,00</h3>
                    <p>Pendente</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col" style="flex: 0 0 20%; max-width: 20%;">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="totalVencido">R$ 0,00</h3>
                    <p>Vencido</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-md-2">
            <label>Status</label>
            <select class="form-control" id="filtroStatus">
                <option value="">Todos</option>
                <option value="a_pagar" selected>A Pagar</option>
                <option value="pendente">Pendente</option>
                <option value="pago">Pago</option>
                <option value="vencido">Vencido</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>
        <div class="col-md-2">
            <label>Categoria</label>
            <select class="form-control" id="filtroCategoria">
                <option value="">Todas</option>
                @if(isset($categorias))
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-md-2">
            <label>Centro de Custo</label>
            <input type="text" class="form-control" id="filtroCentroCusto" placeholder="Digite para buscar..." autocomplete="off">
            <input type="hidden" id="filtroCentroCustoId">
        </div>
        <div class="col-md-2">
            <label>Data Início</label>
            <input type="date" class="form-control" id="filtroDataInicio">
        </div>
        <div class="col-md-2">
            <label>Data Fim</label>
            <input type="date" class="form-control" id="filtroDataFim">
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <div class="btn-group btn-block">
                <button type="button" class="btn btn-success" onclick="filtrarContas()">
                    <i class="fas fa-search mr-1"></i> Filtrar
                </button>
                <button type="button" class="btn btn-secondary" onclick="limparFiltros()">
                    <i class="fas fa-times mr-1"></i> Limpar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabela de Contas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gerenciamento de Contas a Pagar</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm" onclick="abrirModalNova()">
                    <i class="fas fa-plus mr-1"></i> Nova Conta
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped" id="tabelaContas">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nº Doc</th>
                        <th>Descrição</th>
                        <th>Fornecedor</th>
                        <th>C. Custo</th>
                        <th>Categoria</th>
                        <th class="text-right">V. Bruto</th>
                        <th class="text-right">V. Líquido</th>
                        <th class="text-center">Emissão</th>
                        <th class="text-center">Vencim.</th>
                        <th class="text-center">Status</th>
                        <th>Obs.</th>
                        <th class="text-center">Anx.</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="corpoTabela">
                    @forelse($contas as $conta)
                    @php
                        $dataVencimento = $conta->data_vencimento ?? $conta->vencimento ?? null;
                        $vencimento = $dataVencimento ? \Carbon\Carbon::parse($dataVencimento) : null;
                        $hoje = now()->startOfDay();
                        $diasRestantes = $vencimento ? $hoje->diffInDays($vencimento->copy()->startOfDay(), false) : 0;
                        
                        $status = $conta->status ?? 'pendente';
                        if ($status == 'pendente' && $diasRestantes < 0) {
                            $status = 'vencido';
                        }
                    @endphp
                    <tr data-id="{{ $conta->id }}" data-valor-liquido="{{ $conta->valor_liquido ?? $conta->valor ?? 0 }}" data-anexo="{{ $conta->anexo_path ?? '' }}">
                        <td>{{ $conta->id }}</td>
                        <td>{{ $conta->documento ?? $conta->oc_numero ?? '-' }}</td>
                        <td>{{ $conta->descricao }}</td>
                        <td>{{ $conta->fornecedor_nome ?? $conta->fornecedor ?? '-' }}</td>
                        <td>{{ $conta->centro_custo_nome ?? '-' }}</td>
                        <td>{{ $conta->categoria_nome ?? '-' }}</td>
                        <td class="text-right">R$ {{ number_format($conta->valor_bruto ?? $conta->valor ?? 0, 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($conta->valor_liquido ?? $conta->valor ?? 0, 2, ',', '.') }}</td>
                        <td class="text-center">{{ isset($conta->data_emissao) ? \Carbon\Carbon::parse($conta->data_emissao)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $vencimento ? $vencimento->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">
                            @switch($status)
                                @case('pendente')
                                    <span class="badge badge-warning">Pendente</span>
                                    @break
                                @case('pago')
                                    <span class="badge badge-success">Pago</span>
                                    @break
                                @case('vencido')
                                    <span class="badge badge-danger">Vencido</span>
                                    @break
                                @case('cancelado')
                                    <span class="badge badge-secondary">Cancelado</span>
                                    @break
                                @default
                                    <span class="badge badge-info">{{ $status }}</span>
                            @endswitch
                        </td>
                        <td style="max-width: 150px; font-size: 0.85em;">
                            @if(!empty($conta->observacoes))
                                <span title="{{ $conta->observacoes }}">{{ Str::limit($conta->observacoes, 50) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($conta->anexo_path) || !empty($conta->comprovante_path))
                            <button type="button" class="btn btn-sm btn-primary" onclick="verAnexo({{ $conta->id }})" title="Ver Anexo PDF">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-info" onclick="editarConta({{ $conta->id }})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($status === 'pendente' || $status === 'vencido')
                                <button type="button" class="btn btn-success" onclick="baixarConta({{ $conta->id }})" title="Baixar">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                @if($isAdmin)
                                <button type="button" class="btn btn-danger" onclick="excluirConta({{ $conta->id }})" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="semRegistros">
                        <td colspan="14" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle mr-1"></i> Nenhuma conta cadastrada
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nova/Editar Conta -->
<div class="modal fade" id="modalConta" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalContaLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Nova Conta a Pagar
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formConta">
                <input type="hidden" id="contaId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="descricao">Descrição <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="descricao" name="descricao" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="documento">Nº Documento</label>
                                <input type="text" class="form-control" id="documento" name="documento">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fornecedor">Fornecedor</label>
                                <input type="text" class="form-control" id="fornecedor" name="fornecedor">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="centro_custo_id">Centro de Custo</label>
                                <select class="form-control" id="centro_custo_id" name="centro_custo_id">
                                    <option value="">Selecione...</option>
                                    @if(isset($centrosCusto))
                                        @foreach($centrosCusto as $cc)
                                        <option value="{{ $cc->id }}">{{ $cc->nome }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="categoria_id">Categoria</label>
                                <select class="form-control" id="categoria_id" name="categoria_id">
                                    <option value="">Selecione...</option>
                                    @if(isset($categorias))
                                        @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="valor_bruto">Valor Bruto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" id="valor_bruto" name="valor_bruto" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="valor_liquido">Valor Líquido <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" id="valor_liquido" name="valor_liquido" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_emissao">Data de Emissão <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="data_emissao" name="data_emissao" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_vencimento">Data Vencimento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="data_vencimento" name="data_vencimento" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="pendente">Pendente</option>
                                    <option value="pago">Pago</option>
                                    <option value="vencido">Vencido</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="observacoes">Observações</label>
                                <textarea class="form-control" id="observacoes" name="observacoes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Repetição de conta -->
                    <div class="row" id="secaoRepeticao">
                        <div class="col-md-12">
                            <div class="card card-outline card-info">
                                <div class="card-header py-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="repetir_conta" name="repetir_conta">
                                        <label class="custom-control-label" for="repetir_conta">
                                            <strong><i class="fas fa-redo mr-1"></i> Repetir esta conta</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body py-2" id="opcoesRepeticao" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label for="tipo_repeticao">Repetir a cada</label>
                                                <select class="form-control form-control-sm" id="tipo_repeticao" name="tipo_repeticao">
                                                    <option value="mensal">Mês</option>
                                                    <option value="quinzenal">15 dias</option>
                                                    <option value="semanal">Semana</option>
                                                    <option value="bimestral">2 meses</option>
                                                    <option value="trimestral">3 meses</option>
                                                    <option value="semestral">6 meses</option>
                                                    <option value="anual">Ano</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label for="quantidade_repeticoes">Quantidade de vezes</label>
                                                <input type="number" class="form-control form-control-sm" id="quantidade_repeticoes" name="quantidade_repeticoes" min="1" max="36" value="12">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label>&nbsp;</label>
                                                <div class="text-muted small" id="previewRepeticao">
                                                    <i class="fas fa-info-circle"></i> Serão criadas 12 parcelas mensais
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

<!-- Modal Baixar Conta -->
<div class="modal fade" id="modalBaixar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle mr-2"></i>Baixar Conta a Pagar
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formBaixar" enctype="multipart/form-data">
                <input type="hidden" id="baixarContaId">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Conta:</strong> <span id="baixarDescricao"></span><br>
                        <strong>Valor:</strong> <span id="baixarValorOriginal"></span>
                    </div>
                    <div class="form-group">
                        <label for="dataPagamento">Data do Pagamento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="dataPagamento" name="data_pagamento" required>
                    </div>
                    <div class="form-group">
                        <label for="valorPago">Valor Pago <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">R$</span>
                            </div>
                            <input type="text" class="form-control money" id="valorPago" name="valor_pago" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="formaPagamento">Forma de Pagamento <span class="text-danger">*</span></label>
                        <select class="form-control" id="formaPagamento" name="forma_pagamento" required>
                            <option value="">Selecione...</option>
                            <option value="pix">PIX</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                            <option value="transferencia">Transferência Bancária (TED/DOC)</option>
                            <option value="dinheiro">Dinheiro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comprovante">Comprovante (opcional)</label>
                        <input type="file" class="form-control-file" id="comprovante" name="comprovante" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">PDF, JPG ou PNG (máx. 5MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pagamento Parcial -->
<div class="modal fade" id="modalParcial" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark">
                    <i class="fas fa-divide mr-2"></i>Pagamento Parcial
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formParcial" enctype="multipart/form-data">
                <input type="hidden" id="parcialContaId">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Conta:</strong> <span id="parcialDescricao"></span>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center py-2">
                                    <small class="text-muted d-block">Valor Total</small>
                                    <h4 class="mb-0 text-primary" id="parcialValorTotal">R$ 0,00</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center py-2">
                                    <small class="text-muted d-block">Já Pago</small>
                                    <h4 class="mb-0 text-success" id="parcialJaPago">R$ 0,00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card border-danger">
                                <div class="card-body text-center py-2">
                                    <small class="text-muted d-block">Falta Pagar</small>
                                    <h4 class="mb-0 text-danger" id="parcialFaltaPagar">R$ 0,00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="form-group">
                        <label for="parcialData">Data do Pagamento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="parcialData" name="data_pagamento" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="parcialValor">Valor a Pagar Agora <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">R$</span>
                            </div>
                            <input type="text" class="form-control money" id="parcialValor" name="valor_pago" required>
                        </div>
                        <small class="text-muted">Digite o valor que deseja pagar agora</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Restante Após Este Pagamento</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">R$</span>
                            </div>
                            <input type="text" class="form-control" id="parcialRestante" readonly style="background: #f8f9fa; font-weight: bold;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="parcialFormaPagamento">Forma de Pagamento <span class="text-danger">*</span></label>
                        <select class="form-control" id="parcialFormaPagamento" name="forma_pagamento" required>
                            <option value="">Selecione...</option>
                            <option value="pix">PIX</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                            <option value="transferencia">Transferência Bancária (TED/DOC)</option>
                            <option value="dinheiro">Dinheiro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="parcialComprovante">Comprovante (opcional)</label>
                        <input type="file" class="form-control-file" id="parcialComprovante" name="comprovante" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">PDF, JPG ou PNG (máx. 5MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check mr-1"></i> Confirmar Pagamento Parcial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .table thead th {
        background-color: #007bff;
        color: white;
        border: none;
        white-space: nowrap;
        font-size: 0.8rem;
        padding: 8px 6px;
    }
    .small-box h3 {
        font-size: 1.5rem;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.4rem;
    }
    
    /* Responsividade da tabela */
    #tabelaContas {
        font-size: 0.85rem;
        width: 100%;
        min-width: 1200px;
    }
    
    #tabelaContas td {
        padding: 8px 6px;
        vertical-align: middle;
    }
    
    /* Colunas com largura otimizada */
    #tabelaContas th:nth-child(1), #tabelaContas td:nth-child(1) { width: 45px; } /* ID */
    #tabelaContas th:nth-child(2), #tabelaContas td:nth-child(2) { width: 70px; } /* Nº Doc */
    #tabelaContas th:nth-child(3), #tabelaContas td:nth-child(3) { max-width: 180px; } /* Descrição */
    #tabelaContas th:nth-child(4), #tabelaContas td:nth-child(4) { max-width: 120px; } /* Fornecedor */
    #tabelaContas th:nth-child(5), #tabelaContas td:nth-child(5) { max-width: 120px; } /* Centro Custo */
    #tabelaContas th:nth-child(6), #tabelaContas td:nth-child(6) { max-width: 100px; } /* Categoria */
    #tabelaContas th:nth-child(7), #tabelaContas td:nth-child(7) { width: 85px; } /* Valor Bruto */
    #tabelaContas th:nth-child(8), #tabelaContas td:nth-child(8) { width: 85px; } /* Valor Líquido */
    #tabelaContas th:nth-child(9), #tabelaContas td:nth-child(9) { width: 80px; } /* Emissão */
    #tabelaContas th:nth-child(10), #tabelaContas td:nth-child(10) { width: 80px; } /* Vencimento */
    #tabelaContas th:nth-child(11), #tabelaContas td:nth-child(11) { width: 70px; } /* Status */
    #tabelaContas th:nth-child(12), #tabelaContas td:nth-child(12) { max-width: 120px; } /* Observações */
    #tabelaContas th:nth-child(13), #tabelaContas td:nth-child(13) { width: 50px; } /* Anexo */
    #tabelaContas th:nth-child(14), #tabelaContas td:nth-child(14) { width: 110px; } /* Ações */
    
    /* Truncar textos longos */
    #tabelaContas td:nth-child(3),
    #tabelaContas td:nth-child(4),
    #tabelaContas td:nth-child(5),
    #tabelaContas td:nth-child(6),
    #tabelaContas td:nth-child(12) {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    /* Garantir scroll horizontal suave */
    .card-body.table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Botões de ação menores */
    #tabelaContas .btn-group-sm .btn {
        padding: 0.2rem 0.35rem;
        font-size: 0.75rem;
    }
    
    /* Badge de status */
    #tabelaContas .badge {
        font-size: 0.7rem;
        padding: 0.3em 0.5em;
    }
    
    /* Cards de resumo responsivos */
    @media (max-width: 1400px) {
        .small-box h3 {
            font-size: 1.2rem;
        }
        .small-box p {
            font-size: 0.85rem;
        }
    }
    
    @media (max-width: 1200px) {
        .row.mb-3 > .col[style*="flex: 0 0 20%"] {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            margin-bottom: 10px;
        }
    }
    
    @media (max-width: 768px) {
        .row.mb-3 > .col[style*="flex: 0 0 20%"] {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }
    }
</style>
@stop

@section('js')
<script>
// Variável para controle de admin
const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

// Configurar CSRF token para todas as requisições AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(function() {
    // Máscara para valor monetário
    $('.money').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        value = (parseInt(value || 0) / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        $(this).val(value);
    });

    // Data padrão para hoje
    $('#dataPagamento').val(new Date().toISOString().split('T')[0]);
    
    // Definir filtro padrão para o mês atual
    const hoje = new Date();
    const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    const ultimoDiaMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
    
    $('#filtroDataInicio').val(primeiroDiaMes.toISOString().split('T')[0]);
    $('#filtroDataFim').val(ultimoDiaMes.toISOString().split('T')[0]);
    
    // Filtrar automaticamente ao carregar (mês atual)
    filtrarContas();
    
    // Submit do formulário de conta
    $('#formConta').on('submit', function(e) {
        e.preventDefault();
        salvarConta();
    });
    
    // Submit do formulário de baixa
    $('#formBaixar').on('submit', function(e) {
        e.preventDefault();
        confirmarBaixa();
    });
});

// Variável global para armazenar as contas
var contasCarregadas = [];

function calcularTotais() {
    let pago = 0, pendente = 0, vencido = 0, totalLiquido = 0, totalBruto = 0;
    
    contasCarregadas.forEach(function(conta) {
        const valorBruto = parseFloat(conta.valor_bruto || conta.valor || 0);
        const valorLiquido = parseFloat(conta.valor_liquido || conta.valor || 0);
        const valorPago = parseFloat(conta.valor_pago || 0);
        const status = conta.status || 'pendente';
        
        totalBruto += valorBruto;
        totalLiquido += valorLiquido;
        
        if (status === 'pago') {
            // Se está pago, soma o valor líquido total (ou valor_pago se existir)
            pago += valorPago > 0 ? valorPago : valorLiquido;
        } else if (status === 'vencido') {
            // Soma o que já foi pago parcialmente
            pago += valorPago;
            // Pendente do vencido = valor líquido - valor já pago
            vencido += (valorLiquido - valorPago);
        } else if (status === 'pendente') {
            // Soma o que já foi pago parcialmente
            pago += valorPago;
            // Pendente = valor líquido - valor já pago
            pendente += (valorLiquido - valorPago);
        }
    });
    
    $('#totalBruto').text(formatarMoeda(totalBruto));
    $('#totalPago').text(formatarMoeda(pago));
    $('#totalPendente').text(formatarMoeda(pendente));
    $('#totalVencido').text(formatarMoeda(vencido));
    $('#totalGeral').text(formatarMoeda(totalLiquido));
}

function formatarMoeda(valor) {
    return 'R$ ' + valor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

var contaAtualId = null;

function abrirModalNova() {
    contaAtualId = null;
    $('#contaId').val('');
    $('#formConta')[0].reset();
    $('#anexo').val('');
    $('#anexoAtual').hide();
    $('#repetir_conta').prop('checked', false);
    $('#opcoesRepeticao').hide();
    $('#secaoRepeticao').show();
    $('#modalContaLabel').html('<i class="fas fa-plus-circle mr-2"></i>Nova Conta a Pagar');
    $('#modalConta').modal('show');
}

// Controle da seção de repetição
$('#repetir_conta').on('change', function() {
    if ($(this).is(':checked')) {
        $('#opcoesRepeticao').slideDown();
        atualizarPreviewRepeticao();
    } else {
        $('#opcoesRepeticao').slideUp();
    }
});

$('#tipo_repeticao, #quantidade_repeticoes').on('change keyup', function() {
    atualizarPreviewRepeticao();
});

function atualizarPreviewRepeticao() {
    const tipo = $('#tipo_repeticao').val();
    const qtd = parseInt($('#quantidade_repeticoes').val()) || 1;
    
    const tiposTexto = {
        'semanal': 'semanais',
        'quinzenal': 'quinzenais',
        'mensal': 'mensais',
        'bimestral': 'bimestrais',
        'trimestral': 'trimestrais',
        'semestral': 'semestrais',
        'anual': 'anuais'
    };
    
    $('#previewRepeticao').html('<i class="fas fa-info-circle"></i> Serão criadas <strong>' + qtd + '</strong> parcelas ' + tiposTexto[tipo]);
}

function editarConta(id) {
    $.get('/financeiro/api/contas-pagar/' + id, function(response) {
        if (response.success) {
            const conta = response.conta;
            contaAtualId = conta.id;
            $('#contaId').val(conta.id);
            $('#descricao').val(conta.descricao);
            $('#documento').val(conta.documento || conta.oc_numero || '');
            $('#fornecedor').val(conta.fornecedor || conta.fornecedor_nome || '');
            $('#centro_custo_id').val(conta.centro_custo_id);
            $('#categoria_id').val(conta.categoria_id || '');
            $('#valor_bruto').val(formatarValorInput(conta.valor_bruto || conta.valor || 0));
            $('#valor_liquido').val(formatarValorInput(conta.valor_liquido || conta.valor || 0));
            $('#data_emissao').val(conta.data_emissao || '');
            $('#data_vencimento').val(conta.data_vencimento || conta.vencimento || '');
            $('#status').val(conta.status);
            $('#observacoes').val(conta.observacoes);
            $('#anexo').val('');
            
            // Esconder seção de repetição ao editar (só aparece para nova conta)
            $('#secaoRepeticao').hide();
            
            // Mostrar anexo atual se existir
            if (conta.anexo_path || conta.comprovante_path) {
                $('#anexoNome').text((conta.anexo_path || conta.comprovante_path).split('/').pop());
                $('#anexoAtual').show();
            } else {
                $('#anexoAtual').hide();
            }
            
            $('#modalContaLabel').html('<i class="fas fa-edit mr-2"></i>Editar Conta a Pagar');
            $('#modalConta').modal('show');
        } else {
            Swal.fire('Erro', response.message, 'error');
        }
    }).fail(function() {
        Swal.fire('Erro', 'Não foi possível carregar os dados da conta.', 'error');
    });
}

function formatarValorInput(valor) {
    return parseFloat(valor).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function salvarConta() {
    const id = $('#contaId').val();
    const url = id ? '/financeiro/api/contas-pagar/' + id : '/financeiro/api/contas-pagar';
    
    // Usar FormData para enviar arquivo
    const formData = new FormData($('#formConta')[0]);
    
    // Para PUT, adicionar _method
    if (id) {
        formData.append('_method', 'PUT');
    }
    
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Swal.fire('Sucesso', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'Erro ao salvar conta.';
            Swal.fire('Erro', msg, 'error');
        }
    });
}

function baixarConta(id) {
    $.get('/financeiro/api/contas-pagar/' + id, function(response) {
        if (response.success) {
            const conta = response.conta;
            const valorLiquido = parseFloat(conta.valor_liquido || conta.valor || 0);
            const valorPago = parseFloat(conta.valor_pago || 0);
            const faltaPagar = valorLiquido - valorPago;
            
            $('#baixarContaId').val(conta.id);
            $('#baixarDescricao').text(conta.descricao);
            
            // Mostrar valor restante se já houve pagamento parcial
            if (valorPago > 0) {
                $('#baixarValorOriginal').html(
                    `${formatarMoeda(valorLiquido)} <br><small class="text-success">Já pago: ${formatarMoeda(valorPago)}</small> <br><small class="text-danger">Falta: ${formatarMoeda(faltaPagar)}</small>`
                );
                $('#valorPago').val(formatarValorInput(faltaPagar));
            } else {
                $('#baixarValorOriginal').text(formatarMoeda(valorLiquido));
                $('#valorPago').val(formatarValorInput(valorLiquido));
            }
            
            $('#dataPagamento').val(new Date().toISOString().split('T')[0]);
            $('#formaPagamento').val(''); // Resetar forma de pagamento
            $('#comprovante').val(''); // Resetar comprovante
            $('#modalBaixar').modal('show');
        } else {
            Swal.fire('Erro', response.message, 'error');
        }
    });
}

function confirmarBaixa() {
    const id = $('#baixarContaId').val();
    
    // Validar forma de pagamento
    if (!$('#formaPagamento').val()) {
        Swal.fire('Atenção', 'Por favor, selecione a forma de pagamento.', 'warning');
        $('#formaPagamento').focus();
        return;
    }
    
    const formData = new FormData($('#formBaixar')[0]);
    
    $.ajax({
        url: '/financeiro/api/contas-pagar/' + id + '/baixar',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#modalBaixar').modal('hide');
                Swal.fire('Sucesso', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'Erro ao baixar conta.';
            Swal.fire('Erro', msg, 'error');
        }
    });
}

function excluirConta(id) {
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
                url: '/financeiro/api/contas-pagar/' + id,
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
                    const msg = xhr.responseJSON?.message || 'Erro ao excluir conta.';
                    Swal.fire('Erro', msg, 'error');
                }
            });
        }
    });
}

function verAnexo(id) {
    // Buscar lista de comprovantes
    $.get('/financeiro/api/contas-pagar/' + id + '/comprovantes', function(response) {
        if (response.success && response.comprovantes && response.comprovantes.length > 0) {
            if (response.comprovantes.length === 1) {
                // Apenas 1 comprovante - abrir direto
                window.open('/financeiro/api/contas-pagar/' + id + '/comprovante/0', '_blank');
            } else {
                // Múltiplos comprovantes - mostrar modal para escolher
                let html = '<div class="list-group">';
                response.comprovantes.forEach(function(comp, index) {
                    html += '<a href="/financeiro/api/contas-pagar/' + id + '/comprovante/' + comp.index + '" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">';
                    html += '<span><i class="fas fa-file-pdf text-danger mr-2"></i> Comprovante ' + (index + 1) + '</span>';
                    html += '<small class="text-muted">' + comp.nome + '</small>';
                    html += '</a>';
                });
                html += '</div>';
                
                Swal.fire({
                    title: 'Comprovantes de Pagamento',
                    html: html,
                    icon: 'info',
                    showCloseButton: true,
                    showConfirmButton: false,
                    width: '500px'
                });
            }
        } else {
            // Fallback para comportamento antigo
            window.open('/financeiro/api/contas-pagar/' + id + '/comprovante', '_blank');
        }
    }).fail(function() {
        // Fallback em caso de erro
        window.open('/financeiro/api/contas-pagar/' + id + '/comprovante', '_blank');
    });
}

function verAnexoAtual() {
    if (contaAtualId) {
        verAnexo(contaAtualId);
    }
}

// Variáveis globais para pagamento parcial
let parcialValorTotal = 0;
let parcialValorPago = 0;

function pagarParcial(id) {
    $.get('/financeiro/api/contas-pagar/' + id, function(response) {
        if (response.success) {
            const conta = response.conta;
            const valorTotal = parseFloat(conta.valor_liquido || conta.valor || 0);
            const valorPago = parseFloat(conta.valor_pago || 0);
            const faltaPagar = valorTotal - valorPago;
            
            parcialValorTotal = valorTotal;
            parcialValorPago = valorPago;
            
            $('#parcialContaId').val(conta.id);
            $('#parcialDescricao').text(conta.descricao);
            $('#parcialValorTotal').text(formatarMoeda(valorTotal));
            $('#parcialJaPago').text(formatarMoeda(valorPago));
            $('#parcialFaltaPagar').text(formatarMoeda(faltaPagar));
            $('#parcialValor').val('');
            $('#parcialRestante').val(formatarMoeda(faltaPagar).replace('R$ ', ''));
            $('#parcialData').val(new Date().toISOString().split('T')[0]);
            $('#parcialFormaPagamento').val(''); // Resetar forma de pagamento
            $('#parcialComprovante').val('');
            
            $('#modalParcial').modal('show');
        } else {
            Swal.fire('Erro', response.message, 'error');
        }
    });
}

// Atualizar restante quando digitar valor
$('#parcialValor').on('input keyup change', function() {
    let valorDigitado = $(this).val();
    // Remover pontos de milhar e trocar vírgula por ponto
    valorDigitado = valorDigitado.replace(/\./g, '').replace(',', '.');
    valorDigitado = parseFloat(valorDigitado) || 0;
    
    const faltaPagar = parcialValorTotal - parcialValorPago;
    const restante = faltaPagar - valorDigitado;
    
    // Formatar o valor restante
    const restanteFormatado = Math.max(0, restante).toFixed(2).replace('.', ',');
    
    if (restante <= 0) {
        $('#parcialRestante').val('0,00').css('color', '#28a745');
    } else {
        $('#parcialRestante').val(restanteFormatado).css('color', '#dc3545');
    }
});

// Submit do form de pagamento parcial
$('#formParcial').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#parcialContaId').val();
    const formData = new FormData(this);
    
    // Validar forma de pagamento
    if (!$('#parcialFormaPagamento').val()) {
        Swal.fire('Atenção', 'Por favor, selecione a forma de pagamento.', 'warning');
        $('#parcialFormaPagamento').focus();
        return;
    }
    
    // Validar valor
    let valorDigitado = $('#parcialValor').val();
    valorDigitado = valorDigitado.replace(/\./g, '').replace(',', '.');
    valorDigitado = parseFloat(valorDigitado) || 0;
    
    if (valorDigitado <= 0) {
        Swal.fire('Atenção', 'Digite um valor válido para o pagamento.', 'warning');
        return;
    }
    
    const faltaPagar = parcialValorTotal - parcialValorPago;
    if (valorDigitado > faltaPagar + 0.01) {
        Swal.fire('Atenção', 'O valor digitado é maior que o restante a pagar.', 'warning');
        return;
    }
    
    $.ajax({
        url: '/financeiro/api/contas-pagar/' + id + '/baixar',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#modalParcial').modal('hide');
                Swal.fire('Sucesso', 'Pagamento parcial registrado com sucesso!', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'Erro ao registrar pagamento.';
            Swal.fire('Erro', msg, 'error');
        }
    });
});

function filtrarContas() {
    const params = {
        status: $('#filtroStatus').val(),
        categoria_id: $('#filtroCategoria').val(),
        centro_custo_id: $('#filtroCentroCustoId').val(),
        data_inicio: $('#filtroDataInicio').val(),
        data_fim: $('#filtroDataFim').val()
    };
    
    $.get('/financeiro/api/contas-pagar/listar', params, function(response) {
        if (response.success) {
            renderizarTabela(response.contas);
            calcularTotais();
        }
    });
}

function limparFiltros() {
    $('#filtroStatus').val('a_pagar'); // Volta para "A Pagar" como padrão
    $('#filtroCategoria').val('');
    $('#filtroCentroCusto').val('');
    $('#filtroCentroCustoId').val('');
    $('#filtroDataInicio').val('');
    $('#filtroDataFim').val('');
    filtrarContas();
}

function renderizarTabela(contas) {
    // Salvar contas na variável global para cálculo de totais
    contasCarregadas = contas;
    
    const tbody = $('#corpoTabela');
    tbody.empty();
    
    if (contas.length === 0) {
        tbody.append('<tr id="semRegistros"><td colspan="14" class="text-center text-muted py-4"><i class="fas fa-info-circle mr-1"></i> Nenhuma conta encontrada</td></tr>');
        return;
    }
    
    contas.forEach(function(conta) {
        let status = conta.status || 'pendente';
        const valorTotal = parseFloat(conta.valor_liquido || conta.valor || 0);
        const valorPago = parseFloat(conta.valor_pago || 0);
        
        // Determinar se é pagamento parcial
        const isParcial = valorPago > 0 && valorPago < (valorTotal - 0.01) && status !== 'pago';
        
        let statusBadgeHtml;
        if (isParcial) {
            const falta = (valorTotal - valorPago).toFixed(2).replace('.', ',');
            statusBadgeHtml = `<span class="badge badge-info" title="Falta R$ ${falta}">Pago Parcial</span>`;
        } else {
            const statusBadge = {
                'pendente': '<span class="badge badge-warning">Pendente</span>',
                'pago': '<span class="badge badge-success">Pago</span>',
                'vencido': '<span class="badge badge-danger">Vencido</span>',
                'cancelado': '<span class="badge badge-secondary">Cancelado</span>'
            };
            statusBadgeHtml = statusBadge[status] || status;
        }
        
        let acoes = `<button type="button" class="btn btn-info" onclick="editarConta(${conta.id})" title="Editar"><i class="fas fa-edit"></i></button>`;
        
        // Botão de baixa total (se não está pago)
        if (status !== 'pago' && status !== 'cancelado') {
            acoes += `<button type="button" class="btn btn-success" onclick="baixarConta(${conta.id})" title="Pagar Total"><i class="fas fa-check"></i></button>`;
        }
        
        // Botão de pagamento parcial (se não está pago)
        if (status !== 'pago' && status !== 'cancelado') {
            acoes += `<button type="button" class="btn btn-warning text-white" onclick="pagarParcial(${conta.id})" title="Pagar Parcial"><i class="fas fa-divide"></i></button>`;
        }
        
        if (isAdmin) {
            acoes += `<button type="button" class="btn btn-danger" onclick="excluirConta(${conta.id})" title="Excluir"><i class="fas fa-trash"></i></button>`;
        }
        
        const vencimento = conta.data_vencimento ? new Date(conta.data_vencimento + 'T00:00:00').toLocaleDateString('pt-BR') : '-';
        const emissao = conta.data_emissao ? new Date(conta.data_emissao + 'T00:00:00').toLocaleDateString('pt-BR') : '-';
        const valorBruto = parseFloat(conta.valor_bruto || conta.valor || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        const valorLiquido = parseFloat(conta.valor_liquido || conta.valor || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        // Coluna de anexo
        let anexoHtml = '<span class="text-muted">-</span>';
        if (conta.anexo_path || conta.comprovante_path) {
            anexoHtml = `<button type="button" class="btn btn-sm btn-primary" onclick="verAnexo(${conta.id})" title="Ver Anexo PDF"><i class="fas fa-file-pdf"></i></button>`;
        }
        
        tbody.append(`
            <tr data-id="${conta.id}" data-valor-liquido="${conta.valor_liquido || conta.valor || 0}" data-anexo="${conta.anexo_path || ''}">
                <td>${conta.id}</td>
                <td>${conta.documento || conta.oc_numero || '-'}</td>
                <td>${conta.descricao}</td>
                <td>${conta.fornecedor_nome || conta.fornecedor || '-'}</td>
                <td>${conta.centro_custo_nome || '-'}</td>
                <td>${conta.categoria_nome || '-'}</td>
                <td class="text-right">R$ ${valorBruto}</td>
                <td class="text-right">R$ ${valorLiquido}</td>
                <td class="text-center">${emissao}</td>
                <td class="text-center">${vencimento}</td>
                <td class="text-center">${statusBadgeHtml}</td>
                <td style="max-width: 150px; font-size: 0.85em;" title="${conta.observacoes || ''}">${conta.observacoes ? (conta.observacoes.length > 50 ? conta.observacoes.substring(0, 50) + '...' : conta.observacoes) : '<span class="text-muted">-</span>'}</td>
                <td class="text-center">${anexoHtml}</td>
                <td class="text-center"><div class="btn-group btn-group-sm">${acoes}</div></td>
            </tr>
        `);
    });
}

// =============================================
// AUTOCOMPLETE CENTRO DE CUSTO
// =============================================
$(document).ready(function() {
    var centrosCustoCache = [];
    
    // Buscar centros de custo uma vez
    $.get('/api/suprimentos/centros-custo/listar', function(response) {
        if (response.success) {
            centrosCustoCache = response.centros_custo;
        }
    });
    
    // Autocomplete no input
    $('#filtroCentroCusto').on('input', function() {
        var termo = $(this).val().toLowerCase();
        var $input = $(this);
        
        // Remover dropdown anterior
        $('.centro-custo-dropdown').remove();
        
        if (termo.length < 2) {
            $('#filtroCentroCustoId').val('');
            return;
        }
        
        // Filtrar centros de custo
        var resultados = centrosCustoCache.filter(function(cc) {
            return cc.nome.toLowerCase().indexOf(termo) !== -1;
        });
        
        if (resultados.length > 0) {
            var dropdown = $('<div class="centro-custo-dropdown" style="position:absolute;background:#fff;border:1px solid #ddd;max-height:200px;overflow-y:auto;z-index:9999;width:' + $input.outerWidth() + 'px;box-shadow:0 2px 5px rgba(0,0,0,0.2);"></div>');
            
            resultados.slice(0, 10).forEach(function(cc) {
                var item = $('<div style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #eee;">' + cc.nome + '</div>');
                item.on('click', function() {
                    $('#filtroCentroCusto').val(cc.nome);
                    $('#filtroCentroCustoId').val(cc.id);
                    $('.centro-custo-dropdown').remove();
                });
                item.on('mouseenter', function() {
                    $(this).css('background', '#f0f0f0');
                });
                item.on('mouseleave', function() {
                    $(this).css('background', '#fff');
                });
                dropdown.append(item);
            });
            
            $input.after(dropdown);
        }
    });
    
    // Fechar dropdown ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#filtroCentroCusto, .centro-custo-dropdown').length) {
            $('.centro-custo-dropdown').remove();
        }
    });
    
    // Limpar ID se o campo de texto for alterado manualmente
    $('#filtroCentroCusto').on('blur', function() {
        setTimeout(function() {
            var nome = $('#filtroCentroCusto').val();
            var encontrado = centrosCustoCache.find(function(cc) {
                return cc.nome.toLowerCase() === nome.toLowerCase();
            });
            if (!encontrado) {
                $('#filtroCentroCustoId').val('');
            }
        }, 200);
    });
});
</script>
@stop
