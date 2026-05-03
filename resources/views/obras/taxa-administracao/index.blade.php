@extends('adminlte::page')

@section('title', 'Taxa de Administração')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0" style="font-family:'Playfair Display',serif;">
            <i class="fas fa-percentage mr-2" style="color:#C9A84C;"></i>
            Taxa de Administração
        </h1>
        <p class="text-muted mb-0 mt-1" style="font-size:.9rem;">
            10% sobre o custo de obra — separado do custo total
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('obras.administradores.index') }}" class="btn btn-secondary mr-2">
            <i class="fas fa-users mr-1"></i> Administradores
        </a>
        <a href="{{ route('obras.taxa-administracao.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Gerar Taxa
        </a>
    </div>
</div>
@stop

@section('content')
<style>
:root { --gold:#C9A84C; --gold-d:#A8873A; --gold-l:#E2C87A; --ebony:#0F0D0A; --cream:#FAF6EF; --taupe:#7D6A52; }

/* Stat cards */
.taxa-stat {
    background: #fff;
    border-radius: 16px; padding: 22px 26px;
    border: 1px solid rgba(201,168,76,.15);
    box-shadow: 0 4px 18px rgba(15,13,10,.07);
    position: relative; overflow: hidden;
}
.taxa-stat::before {
    content: ''; position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--gold-d), var(--gold-l), var(--gold));
}
.taxa-stat .stat-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: var(--ebony);
    background: linear-gradient(135deg, var(--gold-d), var(--gold-l));
    margin-bottom: 12px;
}
.taxa-stat .stat-val { font-family:'Playfair Display',serif; font-size:1.7rem; font-weight:700; color:var(--ebony); }
.taxa-stat .stat-lbl { font-size:.8rem; color:var(--taupe); font-weight:500; }

/* Filtros */
.filter-bar {
    background: #fff; border-radius: 12px; padding: 16px 20px;
    border: 1px solid rgba(201,168,76,.12); margin-bottom: 20px;
}

/* Tabela */
.taxa-table thead th {
    background: linear-gradient(135deg, var(--taupe) 0%, var(--gold) 100%) !important;
    color: #fff !important; font-size: .73rem; letter-spacing: .5px;
    text-transform: uppercase; padding: 13px 16px; border: none;
}
.taxa-table tbody td { padding: 13px 16px; vertical-align: middle; }
.taxa-table tbody tr:hover { background: rgba(201,168,76,.04) !important; }

/* Status badges */
.st-pendente { background:rgba(201,168,76,.12); color:#A8873A; padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:600; }
.st-pago     { background:rgba(16,158,110,.10); color:#0D7A52; padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:600; }
.st-cancelado{ background:rgba(201,64,64,.10);  color:#A83030; padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:600; }

/* Modal de pagamento */
.modal-header-gold {
    background: linear-gradient(135deg, var(--gold-d) 0%, var(--gold-l) 100%);
    color: var(--ebony);
}

/* Regra de cálculo */
.calc-info {
    background: rgba(201,168,76,.06);
    border: 1px solid rgba(201,168,76,.20);
    border-radius: 12px; padding: 16px 20px;
    margin-bottom: 22px;
}
</style>

@if(session('success'))
<div class="alert" style="background:rgba(16,158,110,.08);border-left:4px solid #1A9E6E;border-radius:10px;color:#0D7A52;padding:14px 18px;margin-bottom:20px;">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

<!-- Regra de negócio (visual) -->
<div class="calc-info">
    <div class="d-flex align-items-start">
        <i class="fas fa-info-circle mr-3 mt-1" style="color:#C9A84C;font-size:1.2rem;"></i>
        <div>
            <strong style="color:#7D6A52;">Fórmula do Cálculo</strong>
            <div class="mt-1" style="font-size:.87rem;color:#6A6259;">
                <code style="background:rgba(201,168,76,.12);padding:2px 8px;border-radius:6px;color:#A8873A;">
                    Taxa = Custo de Obra × {{ $taxas->count() ? number_format($taxas->first()?->percentual ?? 10, 0) : '10' }}%
                </code>
                &nbsp; | &nbsp;
                <strong>Custo de Obra</strong> = soma dos lançamentos
                <em>excluindo</em> os próprios pagamentos de taxa de administração.
                <br>
                <small class="mt-1 d-block">Isso evita que a taxa entre no cálculo de si mesma (circularidade).</small>
            </div>
        </div>
    </div>
</div>

<!-- Stat cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="taxa-stat">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-val">R$ {{ number_format($totalPendente, 2, ',', '.') }}</div>
            <div class="stat-lbl">Total Pendente</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="taxa-stat">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-val">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
            <div class="stat-lbl">Total Pago</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="taxa-stat">
            <div class="stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="stat-val">{{ $taxas->total() }}</div>
            <div class="stat-lbl">Total de Registros</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="taxa-stat">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-val">{{ $administradores->count() }}</div>
            <div class="stat-lbl">Administradores Ativos</div>
        </div>
    </div>
</div>

<!-- Filtros -->
<form method="GET" class="filter-bar">
    <div class="row align-items-end">
        <div class="col-md-3 mb-2 mb-md-0">
            <label class="d-block" style="font-size:.75rem;font-weight:600;color:#7D6A52;text-transform:uppercase;letter-spacing:.5px;">Administrador</label>
            <select name="administrador_id" class="form-control form-control-sm">
                <option value="">{{ __('Todos') }}</option>
                @foreach($administradores as $adm)
                <option value="{{ $adm->id }}" {{ request('administrador_id') == $adm->id ? 'selected':'' }}>{{ $adm->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
            <label class="d-block" style="font-size:.75rem;font-weight:600;color:#7D6A52;text-transform:uppercase;letter-spacing:.5px;">Obra</label>
            <select name="obra_id" class="form-control form-control-sm">
                <option value="">{{ __('Todas') }}</option>
                @foreach($obras as $obra)
                <option value="{{ $obra->id }}" {{ request('obra_id') == $obra->id ? 'selected':'' }}>{{ $obra->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
            <label class="d-block" style="font-size:.75rem;font-weight:600;color:#7D6A52;text-transform:uppercase;letter-spacing:.5px;">Status</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">{{ __('Todos') }}</option>
                <option value="pendente"  {{ request('status')=='pendente'  ? 'selected':'' }}>Pendente</option>
                <option value="pago"      {{ request('status')=='pago'      ? 'selected':'' }}>Pago</option>
                <option value="cancelado" {{ request('status')=='cancelado' ? 'selected':'' }}>Cancelado</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>
        </div>
    </div>
</form>

<!-- Tabela -->
<div class="card">
    <div class="card-body p-0">
        @if($taxas->count())
        <div class="table-responsive">
            <table class="table taxa-table mb-0">
                <thead>
                    <tr>
                        <th>Administrador</th>
                        <th>Obra</th>
                        <th>Referência</th>
                        <th>Custo Base Obra</th>
                        <th>%</th>
                        <th>Valor da Taxa</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th width="140">Ações</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($taxas as $taxa)
                <tr>
                    <td><strong>{{ $taxa->administrador->nome }}</strong></td>
                    <td>{{ $taxa->obra->nome }}</td>
                    <td>{{ $taxa->data_referencia->format('d/m/Y') }}</td>
                    <td>R$ {{ number_format($taxa->custo_base_obra, 2, ',', '.') }}</td>
                    <td style="font-weight:700;color:#A8873A;">{{ number_format($taxa->percentual,2,',','.')  }}%</td>
                    <td style="font-weight:700;font-size:1rem;">R$ {{ number_format($taxa->valor_taxa, 2, ',', '.') }}</td>
                    <td>
                        @if($taxa->data_vencimento)
                            <span class="{{ $taxa->status === 'pendente' && $taxa->data_vencimento->isPast() ? 'text-danger font-weight-bold' : '' }}">
                                {{ $taxa->data_vencimento->format('d/m/Y') }}
                            </span>
                        @else — @endif
                    </td>
                    <td>
                        @if($taxa->status === 'pendente')
                            <span class="st-pendente"><i class="fas fa-clock mr-1"></i>Pendente</span>
                        @elseif($taxa->status === 'pago')
                            <span class="st-pago"><i class="fas fa-check mr-1"></i>Pago</span>
                        @else
                            <span class="st-cancelado"><i class="fas fa-ban mr-1"></i>Cancelado</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($taxa->status === 'pendente')
                            <button class="btn btn-sm btn-success btn-pagar"
                                    data-id="{{ $taxa->id }}"
                                    data-valor="{{ number_format($taxa->valor_taxa, 2, ',', '.') }}"
                                    data-admin="{{ $taxa->administrador->nome }}"
                                    data-toggle="modal" data-target="#modalPagar"
                                    title="Marcar como pago">
                                <i class="fas fa-dollar-sign"></i>
                            </button>
                            <form method="POST" action="{{ route('obras.taxa-administracao.cancelar', $taxa) }}"
                                  onsubmit="return confirm('Cancelar esta taxa?')">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-warning" title="Cancelar"><i class="fas fa-ban"></i></button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('obras.taxa-administracao.destroy', $taxa) }}"
                                  onsubmit="return confirm('Excluir permanentemente?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $taxas->appends(request()->query())->links() }}</div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-percentage fa-3x mb-3" style="color:rgba(201,168,76,.3);"></i>
            <p class="text-muted mb-3">Nenhuma taxa de administração registrada.</p>
            <a href="{{ route('obras.taxa-administracao.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Gerar Taxa
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Modal: marcar como pago -->
<div class="modal fade" id="modalPagar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;box-shadow:0 20px 60px rgba(15,13,10,.2);">
            <div class="modal-header modal-header-gold">
                <h5 class="modal-title" style="font-family:'Playfair Display',serif;">
                    <i class="fas fa-dollar-sign mr-2"></i>
                    Registrar Pagamento
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span style="color:#0F0D0A;">&times;</span>
                </button>
            </div>
            <form id="formPagar" method="POST" action="">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3 p-3 rounded" style="background:rgba(201,168,76,.06);border:1px solid rgba(201,168,76,.2);">
                        <strong id="infoAdmin" style="color:#7D6A52;"></strong>
                        <br><span style="font-size:.9rem;color:#6A6259;">Valor: <strong id="infoValor" style="color:#0F0D0A;font-family:'Playfair Display',serif;"></strong></span>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Data de Pagamento <span class="text-danger">*</span></label>
                            <input type="date" name="data_pagamento" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Valor Pago (R$) <span class="text-danger">*</span></label>
                            <input type="number" name="valor_pago" id="valorPagoInput" step="0.01" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="font-weight-bold">Forma de Pagamento</label>
                        <select name="forma_pagamento" class="form-control">
                            <option value="">— Selecionar —</option>
                            <option>Transferência Bancária</option>
                            <option>PIX</option>
                            <option>Cheque</option>
                            <option>Dinheiro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="font-weight-bold">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
document.querySelectorAll('.btn-pagar').forEach(btn => {
    btn.addEventListener('click', function () {
        const id    = this.dataset.id;
        const valor = this.dataset.valor.replace('.', '').replace(',', '.');
        document.getElementById('formPagar').action =
            `/obras/taxa-administracao/${id}/pagar`;
        document.getElementById('infoAdmin').textContent = this.dataset.admin;
        document.getElementById('infoValor').textContent = 'R$ ' + this.dataset.valor;
        document.getElementById('valorPagoInput').value = valor;
    });
});
</script>
@endpush
