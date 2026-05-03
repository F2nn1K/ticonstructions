@extends('adminlte::page')

@section('title', __('app.financial.accounts_receivable'))

@push('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content_header')
<h1><i class="fas fa-hand-holding-usd mr-2"></i>{{ __('app.financial.accounts_receivable') }}</h1>
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
                    <h3 id="totalRecebido">R$ 0,00</h3>
                    <p>Recebido</p>
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
            <label>{{ __('Status') }}</label>
            <select class="form-control" id="filtroStatus">
                <option value="">{{ __('Todos') }}</option>
                <option value="pendente">Pendente</option>
                <option value="recebido">Recebido</option>
                <option value="vencido">Vencido</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>
        <div class="col-md-2">
            <label>{{ __('Categoria') }}</label>
            <select class="form-control" id="filtroCategoria">
                <option value="">{{ __('Todas') }}</option>
                @if(isset($categorias))
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-md-2">
            <label>Cliente</label>
            <input type="text" class="form-control" id="filtroCliente" placeholder="Nome do cliente">
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
            <h3 class="card-title">Gerenciamento de Contas a Receber</h3>
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
                        <th style="width: 50px">ID</th>
                        <th style="width: 100px">Nº Doc</th>
                        <th>Descrição</th>
                        <th>Cliente</th>
                        <th>Categoria</th>
                        <th>Centro de Custo</th>
                        <th style="width: 100px" class="text-right">Valor Bruto</th>
                        <th style="width: 100px" class="text-right">Valor Líquido</th>
                        <th style="width: 85px" class="text-center">Emissão</th>
                        <th style="width: 85px" class="text-center">Vencimento</th>
                        <th style="width: 70px" class="text-center">Status</th>
                        <th style="width: 60px" class="text-center">Anexo</th>
                        <th style="width: 130px" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="corpoTabela">
                    @forelse($contas as $conta)
                    <tr data-id="{{ $conta->id }}" data-valor-liquido="{{ $conta->valor_liquido ?? $conta->valor ?? 0 }}" data-anexo="{{ $conta->anexo_path ?? '' }}">
                        <td>{{ $conta->id }}</td>
                        <td>{{ $conta->documento ?? '-' }}</td>
                        <td>{{ $conta->descricao }}</td>
                        <td>{{ $conta->cliente ?? '-' }}</td>
                        <td>
                            @if(!empty($conta->categoria_nome))
                            <span class="badge" style="background: {{ $conta->categoria_cor ?? '#28a745' }}; color: #fff;">{{ $conta->categoria_nome }}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $conta->centro_custo_nome ?? '-' }}</td>
                        <td class="text-right">R$ {{ number_format($conta->valor_bruto ?? $conta->valor ?? 0, 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($conta->valor_liquido ?? $conta->valor ?? 0, 2, ',', '.') }}</td>
                        <td class="text-center">{{ $conta->data_emissao ? \Carbon\Carbon::parse($conta->data_emissao)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($conta->vencimento)->format('d/m/Y') }}</td>
                        <td class="text-center">
                            @switch($conta->status)
                                @case('pendente')
                                    <span class="badge badge-warning">Pendente</span>
                                    @break
                                @case('recebido')
                                    <span class="badge badge-success">Recebido</span>
                                    @break
                                @case('vencido')
                                    <span class="badge badge-danger">Vencido</span>
                                    @break
                                @case('cancelado')
                                    <span class="badge badge-secondary">Cancelado</span>
                                    @break
                                @default
                                    <span class="badge badge-info">{{ $conta->status }}</span>
                            @endswitch
                        </td>
                        <td class="text-center">
                            @if(!empty($conta->anexo_path))
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
                                @if($conta->status === 'pendente' || $conta->status === 'vencido')
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
                        <td colspan="13" class="text-center text-muted py-4">
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
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalContaLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Nova Conta a Receber
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cliente">Cliente</label>
                                <input type="text" class="form-control" id="cliente" name="cliente">
                            </div>
                        </div>
                        <div class="col-md-6">
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
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="categoria_id">Categoria</label>
                                <select class="form-control" id="categoria_id" name="categoria_id">
                                    <option value="">Selecione...</option>
                                    @if(isset($categorias))
                                        @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" data-cor="{{ $cat->cor ?? '#28a745' }}">{{ $cat->nome }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vencimento">Data Vencimento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="vencimento" name="vencimento" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="pendente">Pendente</option>
                                    <option value="recebido">Recebido</option>
                                    <option value="vencido">Vencido</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="observacoes">Observações</label>
                                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="anexo">Anexo (PDF)</label>
                                <input type="file" class="form-control-file" id="anexo" name="anexo" accept=".pdf">
                                <small class="text-muted">Apenas arquivos PDF (máx. 10MB)</small>
                                <div id="anexoAtual" class="mt-2" style="display: none;">
                                    <span class="badge badge-info">
                                        <i class="fas fa-file-pdf mr-1"></i>
                                        <span id="anexoNome">documento.pdf</span>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-primary ml-2" onclick="verAnexoAtual()">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </div>
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

<!-- Modal Baixar Conta -->
<div class="modal fade" id="modalBaixar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle mr-2"></i>Baixar Conta a Receber
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
                        <label for="dataRecebimento">Data do Recebimento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="dataRecebimento" name="data_recebimento" required>
                    </div>
                    <div class="form-group">
                        <label for="valorRecebido">Valor Recebido <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">R$</span>
                            </div>
                            <input type="text" class="form-control money" id="valorRecebido" name="valor_recebido" required>
                        </div>
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
                        <i class="fas fa-check mr-1"></i> Confirmar Recebimento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recebimento Parcial -->
<div class="modal fade" id="modalParcial" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark">
                    <i class="fas fa-divide mr-2"></i>Recebimento Parcial
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
                                    <small class="text-muted d-block">Já Recebido</small>
                                    <h4 class="mb-0 text-success" id="parcialJaRecebido">R$ 0,00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card border-danger">
                                <div class="card-body text-center py-2">
                                    <small class="text-muted d-block">Falta Receber</small>
                                    <h4 class="mb-0 text-danger" id="parcialFaltaReceber">R$ 0,00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="form-group">
                        <label for="parcialData">Data do Recebimento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="parcialData" name="data_recebimento" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="parcialValor">Valor a Receber Agora <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">R$</span>
                            </div>
                            <input type="text" class="form-control money" id="parcialValor" name="valor_recebido" required>
                        </div>
                        <small class="text-muted">Digite o valor que deseja receber agora</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Restante Após Este Recebimento</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">R$</span>
                            </div>
                            <input type="text" class="form-control" id="parcialRestante" readonly style="background: #f8f9fa; font-weight: bold;">
                        </div>
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
                        <i class="fas fa-check mr-1"></i> Confirmar Recebimento Parcial
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
        background-color: #28a745;
        color: white;
        border: none;
    }
    .small-box h3 {
        font-size: 1.5rem;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
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
    $('#dataRecebimento').val(new Date().toISOString().split('T')[0]);
    
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
    let recebido = 0, pendente = 0, vencido = 0, totalLiquido = 0, totalBruto = 0;
    
    contasCarregadas.forEach(function(conta) {
        const valorBruto = parseFloat(conta.valor_bruto || conta.valor || 0);
        const valorLiquido = parseFloat(conta.valor_liquido || conta.valor || 0);
        const valorRecebido = parseFloat(conta.valor_recebido || 0);
        const status = conta.status || 'pendente';
        
        totalBruto += valorBruto;
        totalLiquido += valorLiquido;
        
        if (status === 'recebido') {
            // Se está recebido, soma o valor líquido total (ou valor_recebido se existir)
            recebido += valorRecebido > 0 ? valorRecebido : valorLiquido;
        } else if (status === 'vencido') {
            // Soma o que já foi recebido parcialmente
            recebido += valorRecebido;
            // Pendente do vencido = valor líquido - valor já recebido
            vencido += (valorLiquido - valorRecebido);
        } else if (status === 'pendente') {
            // Soma o que já foi recebido parcialmente
            recebido += valorRecebido;
            // Pendente = valor líquido - valor já recebido
            pendente += (valorLiquido - valorRecebido);
        }
    });
    
    $('#totalBruto').text(formatarMoeda(totalBruto));
    $('#totalRecebido').text(formatarMoeda(recebido));
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
    $('#modalContaLabel').html('<i class="fas fa-plus-circle mr-2"></i>Nova Conta a Receber');
    $('#modalConta').modal('show');
}

function editarConta(id) {
    $.get('/financeiro/api/contas-receber/' + id, function(response) {
        if (response.success) {
            const conta = response.conta;
            contaAtualId = conta.id;
            $('#contaId').val(conta.id);
            $('#descricao').val(conta.descricao);
            $('#documento').val(conta.documento);
            $('#cliente').val(conta.cliente);
            $('#centro_custo_id').val(conta.centro_custo_id);
            $('#categoria_id').val(conta.categoria_id);
            $('#valor_bruto').val(formatarValorInput(conta.valor_bruto || conta.valor || 0));
            $('#valor_liquido').val(formatarValorInput(conta.valor_liquido || conta.valor || 0));
            $('#data_emissao').val(conta.data_emissao || '');
            $('#vencimento').val(conta.vencimento);
            $('#status').val(conta.status);
            $('#observacoes').val(conta.observacoes);
            $('#anexo').val('');
            
            // Mostrar anexo atual se existir
            if (conta.anexo_path) {
                $('#anexoNome').text(conta.anexo_path.split('/').pop());
                $('#anexoAtual').show();
            } else {
                $('#anexoAtual').hide();
            }
            
            $('#modalContaLabel').html('<i class="fas fa-edit mr-2"></i>Editar Conta a Receber');
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
    const url = id ? '/financeiro/api/contas-receber/' + id : '/financeiro/api/contas-receber';
    
    // Usar FormData para enviar arquivo
    const formData = new FormData($('#formConta')[0]);
    
    // Para PUT, adicionar _method
    if (id) {
        formData.append('_method', 'PUT');
    }
    
    $.ajax({
        url: url,
        method: 'POST', // Sempre POST para FormData, usar _method para PUT
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
    $.get('/financeiro/api/contas-receber/' + id, function(response) {
        if (response.success) {
            const conta = response.conta;
            const valorLiquido = parseFloat(conta.valor_liquido || conta.valor || 0);
            const valorRecebido = parseFloat(conta.valor_recebido || 0);
            const faltaReceber = valorLiquido - valorRecebido;
            
            $('#baixarContaId').val(conta.id);
            $('#baixarDescricao').text(conta.descricao);
            
            // Mostrar valor restante se já houve recebimento parcial
            if (valorRecebido > 0) {
                $('#baixarValorOriginal').html(
                    `${formatarMoeda(valorLiquido)} <br><small class="text-success">Já recebido: ${formatarMoeda(valorRecebido)}</small> <br><small class="text-danger">Falta: ${formatarMoeda(faltaReceber)}</small>`
                );
                $('#valorRecebido').val(formatarValorInput(faltaReceber));
            } else {
                $('#baixarValorOriginal').text(formatarMoeda(valorLiquido));
                $('#valorRecebido').val(formatarValorInput(valorLiquido));
            }
            
            $('#dataRecebimento').val(new Date().toISOString().split('T')[0]);
            $('#modalBaixar').modal('show');
        } else {
            Swal.fire('Erro', response.message, 'error');
        }
    });
}

function confirmarBaixa() {
    const id = $('#baixarContaId').val();
    const formData = new FormData($('#formBaixar')[0]);
    
    $.ajax({
        url: '/financeiro/api/contas-receber/' + id + '/baixar',
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
                url: '/financeiro/api/contas-receber/' + id,
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

function verComprovante(id) {
    window.open('/financeiro/api/contas-receber/' + id + '/comprovante', '_blank');
}

// Variáveis globais para recebimento parcial
let parcialValorTotal = 0;
let parcialValorRecebido = 0;

function receberParcial(id) {
    $.get('/financeiro/api/contas-receber/' + id, function(response) {
        if (response.success) {
            const conta = response.conta;
            const valorTotal = parseFloat(conta.valor_liquido || conta.valor || 0);
            const valorRecebido = parseFloat(conta.valor_recebido || 0);
            const faltaReceber = valorTotal - valorRecebido;
            
            parcialValorTotal = valorTotal;
            parcialValorRecebido = valorRecebido;
            
            $('#parcialContaId').val(conta.id);
            $('#parcialDescricao').text(conta.descricao);
            $('#parcialValorTotal').text(formatarMoeda(valorTotal));
            $('#parcialJaRecebido').text(formatarMoeda(valorRecebido));
            $('#parcialFaltaReceber').text(formatarMoeda(faltaReceber));
            $('#parcialValor').val('');
            $('#parcialRestante').val(formatarMoeda(faltaReceber).replace('R$ ', ''));
            $('#parcialData').val(new Date().toISOString().split('T')[0]);
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
    
    const faltaReceber = parcialValorTotal - parcialValorRecebido;
    const restante = faltaReceber - valorDigitado;
    
    // Formatar o valor restante
    const restanteFormatado = Math.max(0, restante).toFixed(2).replace('.', ',');
    
    if (restante <= 0) {
        $('#parcialRestante').val('0,00').css('color', '#28a745');
    } else {
        $('#parcialRestante').val(restanteFormatado).css('color', '#dc3545');
    }
});

// Submit do form de recebimento parcial
$('#formParcial').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#parcialContaId').val();
    const formData = new FormData(this);
    
    // Validar valor
    let valorDigitado = $('#parcialValor').val();
    valorDigitado = valorDigitado.replace(/\./g, '').replace(',', '.');
    valorDigitado = parseFloat(valorDigitado) || 0;
    
    if (valorDigitado <= 0) {
        Swal.fire('Atenção', 'Digite um valor válido para o recebimento.', 'warning');
        return;
    }
    
    const faltaReceber = parcialValorTotal - parcialValorRecebido;
    if (valorDigitado > faltaReceber + 0.01) {
        Swal.fire('Atenção', 'O valor digitado é maior que o restante a receber.', 'warning');
        return;
    }
    
    $.ajax({
        url: '/financeiro/api/contas-receber/' + id + '/baixar',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#modalParcial').modal('hide');
                Swal.fire('Sucesso', 'Recebimento parcial registrado com sucesso!', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'Erro ao registrar recebimento.';
            Swal.fire('Erro', msg, 'error');
        }
    });
});

function verAnexo(id) {
    window.open('/financeiro/api/contas-receber/' + id + '/anexo', '_blank');
}

function verAnexoAtual() {
    if (contaAtualId) {
        window.open('/financeiro/api/contas-receber/' + contaAtualId + '/anexo', '_blank');
    }
}

function filtrarContas() {
    const params = {
        status: $('#filtroStatus').val(),
        categoria_id: $('#filtroCategoria').val(),
        cliente: $('#filtroCliente').val(),
        data_inicio: $('#filtroDataInicio').val(),
        data_fim: $('#filtroDataFim').val()
    };
    
    $.get('/financeiro/api/contas-receber', params, function(response) {
        if (response.success) {
            renderizarTabela(response.contas);
            calcularTotais();
        }
    });
}

function limparFiltros() {
    $('#filtroStatus').val('');
    $('#filtroCategoria').val('');
    $('#filtroCliente').val('');
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
        tbody.append('<tr id="semRegistros"><td colspan="13" class="text-center text-muted py-4"><i class="fas fa-info-circle mr-1"></i> Nenhuma conta encontrada</td></tr>');
        return;
    }
    
    contas.forEach(function(conta) {
        let status = conta.status || 'pendente';
        const valorTotal = parseFloat(conta.valor_liquido || conta.valor || 0);
        const valorRecebido = parseFloat(conta.valor_recebido || 0);
        
        // Determinar se é recebimento parcial
        const isParcial = valorRecebido > 0 && valorRecebido < (valorTotal - 0.01) && status !== 'recebido';
        
        let statusBadgeHtml;
        if (isParcial) {
            const falta = (valorTotal - valorRecebido).toFixed(2).replace('.', ',');
            statusBadgeHtml = `<span class="badge badge-info" title="Falta R$ ${falta}">Recebido Parcial</span>`;
        } else {
            const statusBadge = {
                'pendente': '<span class="badge badge-warning">Pendente</span>',
                'recebido': '<span class="badge badge-success">Recebido</span>',
                'vencido': '<span class="badge badge-danger">Vencido</span>',
                'cancelado': '<span class="badge badge-secondary">Cancelado</span>'
            };
            statusBadgeHtml = statusBadge[status] || status;
        }
        
        let acoes = `<button type="button" class="btn btn-info" onclick="editarConta(${conta.id})" title="Editar"><i class="fas fa-edit"></i></button>`;
        
        // Botão de baixa total (se não está recebido)
        if (status !== 'recebido' && status !== 'cancelado') {
            acoes += `<button type="button" class="btn btn-success" onclick="baixarConta(${conta.id})" title="Receber Total"><i class="fas fa-check"></i></button>`;
        }
        
        // Botão de recebimento parcial (se não está recebido)
        if (status !== 'recebido' && status !== 'cancelado') {
            acoes += `<button type="button" class="btn btn-warning text-white" onclick="receberParcial(${conta.id})" title="Receber Parcial"><i class="fas fa-divide"></i></button>`;
        }
        
        if (isAdmin) {
            acoes += `<button type="button" class="btn btn-danger" onclick="excluirConta(${conta.id})" title="Excluir"><i class="fas fa-trash"></i></button>`;
        }
        
        const vencimento = new Date(conta.vencimento + 'T00:00:00');
        const vencimentoFormatado = vencimento.toLocaleDateString('pt-BR');
        
        const emissao = conta.data_emissao ? new Date(conta.data_emissao + 'T00:00:00').toLocaleDateString('pt-BR') : '-';
        const valorBruto = parseFloat(conta.valor_bruto || conta.valor || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        const valorLiquido = parseFloat(conta.valor_liquido || conta.valor || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        // Coluna de anexo
        let anexoHtml = '<span class="text-muted">-</span>';
        if (conta.anexo_path) {
            anexoHtml = `<button type="button" class="btn btn-sm btn-primary" onclick="verAnexo(${conta.id})" title="Ver Anexo PDF"><i class="fas fa-file-pdf"></i></button>`;
        }
        
        // Categoria
        let categoriaHtml = '-';
        if (conta.categoria_nome) {
            const corCat = conta.categoria_cor || '#28a745';
            categoriaHtml = `<span class="badge" style="background: ${corCat}; color: #fff;">${conta.categoria_nome}</span>`;
        }
        
        tbody.append(`
            <tr data-id="${conta.id}" data-valor-liquido="${conta.valor_liquido || conta.valor || 0}" data-anexo="${conta.anexo_path || ''}">
                <td>${conta.id}</td>
                <td>${conta.documento || '-'}</td>
                <td>${conta.descricao}</td>
                <td>${conta.cliente || '-'}</td>
                <td>${categoriaHtml}</td>
                <td>${conta.centro_custo_nome || '-'}</td>
                <td class="text-right">R$ ${valorBruto}</td>
                <td class="text-right">R$ ${valorLiquido}</td>
                <td class="text-center">${emissao}</td>
                <td class="text-center">${vencimentoFormatado}</td>
                <td class="text-center">${statusBadgeHtml}</td>
                <td class="text-center">${anexoHtml}</td>
                <td class="text-center"><div class="btn-group btn-group-sm">${acoes}</div></td>
            </tr>
        `);
    });
}
</script>
@stop
