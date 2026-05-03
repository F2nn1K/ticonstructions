@extends('adminlte::page')

@section('title', __('app.menu.approve_measurements'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-check-double mr-2" style="color:var(--ti-gold)"></i>
                {{ __('app.menu.approve_measurements') }}
            </h1>
            <small class="text-muted">{{ __('Aprovar ou rejeitar medições de produção') }}</small>
        </div>
        <div>
            <a href="{{ route('producao.medicao') }}" class="btn btn-success btn-sm mr-2">
                <i class="fas fa-plus mr-1"></i> {{ __('app.menu.record_measurement') }}
            </a>
            <a href="{{ route('producao.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-chart-line mr-1"></i> {{ __('Avanço Físico') }}
            </a>
        </div>
    </div>
@stop

@section('content')
<style>
.kpi-mini { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); }
.kpi-mini .k-label { font-size:.72rem; text-transform:uppercase; font-weight:700; letter-spacing:.05em; opacity:.7; }
.kpi-mini .k-val   { font-size:1.8rem; font-weight:800; line-height:1.2; }
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- KPIs --}}
<div class="row mb-4">
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-warning">{{ __('Pendentes (Total)') }}</div>
                <div class="k-val text-warning">{{ $totalPendentes }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:#1A9E6E">{{ __('Aprovadas no Período') }}</div>
                <div class="k-val" style="color:#1A9E6E">{{ $totalAprovadas }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-danger">{{ __('Rejeitadas no Período') }}</div>
                <div class="k-val text-danger">{{ $totalRejeitadas }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-3" style="border-radius:12px; border:none; box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('producao.aprovacao') }}" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('De') }}</label>
                <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="form-control form-control-sm">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Até') }}</label>
                <input type="date" name="data_fim" value="{{ $dataFim }}" class="form-control form-control-sm">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Status') }}</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="todos"    {{ $status=='todos'    ?'selected':'' }}>{{ __('Todos') }}</option>
                    <option value="pendente" {{ $status=='pendente' ?'selected':'' }}>{{ __('Pendente') }}</option>
                    <option value="aprovado" {{ $status=='aprovado' ?'selected':'' }}>{{ __('Aprovado') }}</option>
                    <option value="rejeitado"{{ $status=='rejeitado'?'selected':'' }}>{{ __('Rejeitado') }}</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Todas as obras') }}</option>
                    @foreach($obras as $ob)
                        <option value="{{ $ob->id }}" {{ request('obra_id')==$ob->id ?'selected':'' }}>
                            {{ $ob->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-12 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="fas fa-search mr-1"></i> {{ __('Filtrar') }}
                </button>
                <a href="{{ route('producao.aprovacao') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabela --}}
<form method="POST" action="{{ route('producao.processar-lote') }}" id="formLote">
    @csrf
    <div class="card" style="border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07)">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 font-weight-bold">
                <i class="fas fa-list mr-2" style="color:var(--ti-gold)"></i>
                {{ __('Medições') }}
                <span class="badge badge-secondary ml-1">{{ $medicoes->count() }}</span>
            </h6>
            @if($medicoes->where('status','pendente')->count() > 0)
            <div class="d-flex gap-2">
                <button type="submit" name="acao" value="aprovado"
                        class="btn btn-success btn-sm"
                        onclick="return confirmarLote('{{ __('Aprovar todos os selecionados?') }}')">
                    <i class="fas fa-check mr-1"></i> {{ __('Aprovar Selecionados') }}
                </button>
                <button type="submit" name="acao" value="rejeitado"
                        class="btn btn-danger btn-sm"
                        onclick="return confirmarLote('{{ __('Rejeitar todos os selecionados?') }}')">
                    <i class="fas fa-times mr-1"></i> {{ __('Rejeitar Selecionados') }}
                </button>
            </div>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:30px">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>{{ __('Data') }}</th>
                            <th>{{ __('Obra') }}</th>
                            <th>{{ __('Fase') }}</th>
                            <th>{{ __('% Medido') }}</th>
                            <th>{{ __('% Acumulado') }}</th>
                            <th>{{ __('Valor') }}</th>
                            <th>{{ __('Descrição') }}</th>
                            <th>{{ __('Registrado por') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicoes as $med)
                            @php
                                $statusLabels = [
                                    'pendente'  => __('Pendente'),
                                    'aprovado'  => __('Aprovado'),
                                    'rejeitado' => __('Rejeitado'),
                                ];
                            @endphp
                            <tr>
                                <td>
                                    @if($med->status === 'pendente')
                                        <input type="checkbox" name="ids[]" value="{{ $med->id }}" class="check-item">
                                    @endif
                                </td>
                                <td class="small font-weight-bold text-nowrap">
                                    {{ $med->data_medicao->translatedFormat('d/m/Y') }}
                                </td>
                                <td>
                                    <div class="small font-weight-bold">{{ $med->obra->nome ?? '—' }}</div>
                                    @if($med->obra?->codigo)
                                        <small class="text-muted">{{ $med->obra->codigo }}</small>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $med->fase?->nome_personalizado ?? __('Geral') }}</td>
                                <td class="small font-weight-bold" style="color:var(--ti-gold)">
                                    {{ number_format($med->percentual_medido, 1) }}%
                                </td>
                                <td class="small">{{ number_format($med->percentual_acumulado, 1) }}%</td>
                                <td class="small text-nowrap">
                                    {{ $med->valor_medicao ? 'R$ '.number_format($med->valor_medicao, 2, ',', '.') : '—' }}
                                </td>
                                <td class="small" style="max-width:180px">
                                    <span class="text-truncate d-block" title="{{ $med->descricao }}">
                                        {{ \Illuminate\Support\Str::limit($med->descricao, 50) }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $med->registrador->name ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $med->status_badge }}" style="font-size:.65rem">
                                        {{ $statusLabels[$med->status] ?? $med->status }}
                                    </span>
                                    @if($med->aprovado_em)
                                        <br><small class="text-muted" style="font-size:.6rem">
                                            {{ $med->aprovado_em->format('d/m H:i') }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if($med->status === 'pendente')
                                        <form method="POST" action="{{ route('producao.processar', $med) }}"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" name="acao" value="aprovado"
                                                    class="btn btn-xs btn-success" title="{{ __('Aprovar') }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="submit" name="acao" value="rejeitado"
                                                    class="btn btn-xs btn-danger" title="{{ __('Rejeitar') }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-5">
                                    <i class="fas fa-check-double fa-2x mb-2 d-block" style="opacity:.3"></i>
                                    {{ __('Nenhuma medição encontrada para os filtros selecionados.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

@stop

@section('js')
<script>
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.check-item').forEach(cb => cb.checked = this.checked);
});

function confirmarLote(msg) {
    const sel = document.querySelectorAll('.check-item:checked');
    if (sel.length === 0) {
        alert('{{ __("Selecione ao menos uma medição.") }}');
        return false;
    }
    return confirm(msg + ' (' + sel.length + ' {{ __("registro(s)") }})');
}
</script>
@stop
