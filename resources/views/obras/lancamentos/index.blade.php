@extends('adminlte::page')

@section('title', __('Lançamentos') . ' – ' . $obra->nome)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('obras.show', $obra) }}" class="btn btn-sm btn-outline-secondary mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1><i class="fas fa-dollar-sign mr-2"></i>{{ __('Lançamentos') }}</h1>
                <small class="text-muted">{{ $obra->nome }}</small>
            </div>
        </div>
        <a href="{{ route('obras.lancamentos.create', $obra) }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> {{ __('Novo Lançamento') }}
        </a>
    </div>
@stop

@section('content')

{{-- KPIs --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h4>R$ {{ number_format($totalReal, 2, ',', '.') }}</h4>
                <p>{{ __('Total Realizado') }}</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
        </div>
    </div>
    @if($totalOrcado)
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h4>R$ {{ number_format($totalOrcado, 2, ',', '.') }}</h4>
                <p>{{ __('Total Orçado') }}</p>
            </div>
            <div class="icon"><i class="fas fa-clipboard-list"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        @php $diff = $totalReal - $totalOrcado; @endphp
        <div class="small-box {{ $diff > 0 ? 'bg-danger' : 'bg-success' }}">
            <div class="inner">
                <h4>{{ $diff > 0 ? '+' : '' }}R$ {{ number_format(abs($diff), 2, ',', '.') }}</h4>
                <p>{{ $diff > 0 ? __('Estouro de Orçamento') : __('Dentro do Orçamento') }}</p>
            </div>
            <div class="icon"><i class="fas fa-{{ $diff > 0 ? 'exclamation-triangle' : 'check' }}"></i></div>
        </div>
    </div>
    @endif
</div>

{{-- Tabela --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Data') }}</th>
                        <th>{{ __('Descrição') }}</th>
                        <th>{{ __('Fase') }}</th>
                        <th>{{ __('Categoria') }}</th>
                        <th>{{ __('Tipo') }}</th>
                        <th>{{ __('Qtd') }}</th>
                        <th>{{ __('Custo Unit.') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lancamentos as $lanc)
                        <tr>
                            <td class="text-muted small">{{ $lanc->data_lancamento->format('d/m/Y') }}</td>
                            <td>
                                <div class="font-weight-bold">{{ Str::limit($lanc->descricao, 35) }}</div>
                                @if($lanc->fornecedor)
                                    <small class="text-muted">{{ $lanc->fornecedor }}</small>
                                @endif
                            </td>
                            <td class="small">{{ $lanc->fase?->nome ?? '—' }}</td>
                            <td class="small">{{ $lanc->categoria?->nome ?? '—' }}</td>
                            <td><span class="badge badge-secondary">{{ $lanc->tipo_label }}</span></td>
                            <td class="small">{{ number_format($lanc->quantidade, 2, ',', '.') }} {{ $lanc->unidade }}</td>
                            <td class="small text-right">R$ {{ number_format($lanc->custo_unitario_real, 2, ',', '.') }}</td>
                            <td class="font-weight-bold text-success text-right">
                                R$ {{ number_format($lanc->custo_total_real, 2, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $lanc->status_pagamento_badge }}">
                                    {{ ucfirst($lanc->status_pagamento) }}
                                </span>
                            </td>
                            <td>
                                <form method="POST"
                                      action="{{ route('obras.lancamentos.destroy', [$obra, $lanc]) }}"
                                      onsubmit="return confirm('{{ __('Confirmar exclusão deste lançamento?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                {{ __('Nenhum lançamento registrado ainda.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($lancamentos->hasPages())
        <div class="card-footer">
            {{ $lancamentos->links() }}
        </div>
    @endif
</div>
@stop
