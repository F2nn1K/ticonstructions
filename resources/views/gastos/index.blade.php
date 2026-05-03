@extends('adminlte::page')

@section('title', __('Custos da Obra'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-file-invoice-dollar mr-2" style="color:var(--ti-gold)"></i>
                {{ __('Custos da Obra') }}
            </h1>
            <small class="text-muted">{{ __('Todos os lançamentos financeiros das obras') }}</small>
        </div>
        <a href="{{ route('gastos.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> {{ __('Lançar Custo') }}
        </a>
    </div>
@stop

@section('content')
<style>
.kpi-mini { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); }
.kpi-mini .k-label { font-size:.72rem; text-transform:uppercase; font-weight:700; letter-spacing:.05em; opacity:.7; }
.kpi-mini .k-val   { font-size:1.8rem; font-weight:800; line-height:1.2; }
.filter-bar { background:#fff; border-radius:12px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,.06); margin-bottom:20px; }
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- KPIs --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-muted">{{ __('Total Lançado') }}</div>
                <div class="k-val text-dark">R$ {{ number_format($totalReal, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:#1A9E6E">{{ __('Pago') }}</div>
                <div class="k-val" style="color:#1A9E6E">R$ {{ number_format($totalPago, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:var(--ti-gold,#C9A84C)">{{ __('Pendente') }}</div>
                <div class="k-val" style="color:var(--ti-gold,#C9A84C)">R$ {{ number_format($totalPendente, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-muted">{{ __('Registros') }}</div>
                <div class="k-val text-dark">{{ $lancamentos->total() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('gastos.index') }}" class="row g-2 align-items-end">
        <div class="col-sm-4 col-md-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
            <select name="obra_id" class="form-control form-control-sm">
                <option value="">{{ __('Todas as obras') }}</option>
                @foreach($obras as $ob)
                    <option value="{{ $ob->id }}" {{ request('obra_id')==$ob->id?'selected':'' }}>
                        {{ $ob->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4 col-md-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('Categoria') }}</label>
            <select name="categoria_id" class="form-control form-control-sm">
                <option value="">{{ __('Todas') }}</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ request('categoria_id')==$cat->id?'selected':'' }}>
                        {{ $cat->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4 col-md-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('Status') }}</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">{{ __('Todos') }}</option>
                <option value="pendente"  {{ request('status')=='pendente'  ?'selected':'' }}>{{ __('Pendente') }}</option>
                <option value="pago"      {{ request('status')=='pago'      ?'selected':'' }}>{{ __('Pago') }}</option>
                <option value="cancelado" {{ request('status')=='cancelado' ?'selected':'' }}>{{ __('Cancelado') }}</option>
            </select>
        </div>
        <div class="col-sm-4 col-md-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('De') }}</label>
            <input type="date" name="data_de" value="{{ request('data_de') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-4 col-md-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('Até') }}</label>
            <input type="date" name="data_ate" value="{{ request('data_ate') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-4 col-md-2">
            <div class="d-flex gap-1 mt-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="fas fa-search"></i>
                </button>
                @if(request()->hasAny(['obra_id','categoria_id','status','tipo','data_de','data_ate']))
                    <a href="{{ route('gastos.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
                <a href="{{ route('gastos.fluxo-caixa') }}" class="btn btn-sm btn-outline-info" title="{{ __('Fluxo de Caixa') }}">
                    <i class="fas fa-chart-line"></i>
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Tabela --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Data') }}</th>
                        <th>{{ __('Obra') }}</th>
                        <th>{{ __('Fase') }}</th>
                        <th>{{ __('Descrição') }}</th>
                        <th>{{ __('Categoria') }}</th>
                        <th>{{ __('Tipo') }}</th>
                        <th class="text-right">{{ __('Valor') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lancamentos as $lanc)
                        <tr>
                            <td class="text-muted small text-nowrap">
                                {{ $lanc->data_lancamento->format('d/m/Y') }}
                            </td>
                            <td class="small">
                                <a href="{{ route('obras.show', $lanc->obra) }}" class="text-dark font-weight-bold">
                                    {{ Str::limit($lanc->obra->nome, 20) }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $lanc->fase?->nome ?? '—' }}</td>
                            <td>
                                <div class="font-weight-bold">{{ Str::limit($lanc->descricao, 32) }}</div>
                                @if($lanc->fornecedor)
                                    <small class="text-muted">{{ Str::limit($lanc->fornecedor, 20) }}</small>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $lanc->categoria?->nome ?? '—' }}</td>
                            <td>
                                <span class="badge badge-secondary" style="font-size:.65rem">
                                    {{ $lanc->tipo_label }}
                                </span>
                            </td>
                            <td class="text-right font-weight-bold text-success text-nowrap">
                                R$ {{ number_format($lanc->custo_total_real, 2, ',', '.') }}
                                @if($lanc->lote_id)
                                    <br><span class="badge badge-light" style="font-size:.6rem;color:#aaa" title="Lançamento em lote">lote</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeCls = match($lanc->status_pagamento) {
                                        'pago'      => 'badge-success',
                                        'pendente'  => 'badge-warning',
                                        'cancelado' => 'badge-danger',
                                        default     => 'badge-secondary',
                                    };
                                    $statusLabel = match($lanc->status_pagamento) {
                                        'pago'      => __('Pago'),
                                        'pendente'  => __('Pendente'),
                                        'cancelado' => __('Cancelado'),
                                        default     => ucfirst($lanc->status_pagamento),
                                    };
                                @endphp
                                <span class="badge {{ $badgeCls }}" style="font-size:.65rem">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('obras.lancamentos.index', $lanc->obra) }}"
                                   class="btn btn-xs btn-outline-secondary" title="{{ __('Ver na obra') }}">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-search fa-2x mb-2 d-block" style="opacity:.3"></i>
                                {{ __('Nenhum lançamento encontrado.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($lancamentos->hasPages())
        <div class="card-footer">{{ $lancamentos->links() }}</div>
    @endif
</div>

@stop
