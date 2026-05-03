@extends('adminlte::page')
@section('title', __('app.menu.physical_s_curve'))
@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div><h1 class="mb-0"><i class="fas fa-chart-area mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.physical_s_curve') }}</h1>
    <small class="text-muted">{{ __('Progresso físico por fase das obras') }}</small></div>
</div>
@stop
@section('content')
<style>.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}.kpi-mini .k-val{font-size:1.8rem;font-weight:800;line-height:1.2}</style>

<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-sm-6 col-md-4">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Selecionar Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Selecione a obra...') }}</option>
                    @foreach($obras as $ob)<option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3 mt-2 mt-sm-0 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Visualizar') }}</button>
                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

@if($obra)
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-muted">{{ __('Total de Fases') }}</div><div class="k-val text-dark">{{ $totalFases }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label" style="color:#1A9E6E">{{ __('Concluídas') }}</div><div class="k-val" style="color:#1A9E6E">{{ $concluidas }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-primary">{{ __('Progresso Médio') }}</div><div class="k-val text-primary">{{ $progressoMedio }}%</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-warning">{{ __('Em Andamento') }}</div><div class="k-val text-warning">{{ $totalFases - $concluidas }}</div></div></div></div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header border-0 pt-3 pb-0"><h6 class="font-weight-bold mb-0">{{ __('Progresso por Fase') }}</h6></div>
            <div class="card-body"><canvas id="chartFases" height="280"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5 mb-4">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header border-0 pt-3 pb-0"><h6 class="font-weight-bold mb-0">{{ __('Detalhe das Fases') }}</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light"><tr><th>{{ __('Fase') }}</th><th>{{ __('Previsto') }}</th><th>{{ __('Realizado') }}</th></tr></thead>
                        <tbody>
                        @forelse($fases as $fase)
                            <tr>
                                <td class="small">{{ $fase->nome_personalizado }}</td>
                                <td><div class="progress" style="height:12px"><div class="progress-bar bg-secondary" style="width:100%"></div></div><small class="text-muted">100%</small></td>
                                <td>
                                    <div class="progress" style="height:12px">
                                        <div class="progress-bar {{ ($fase->percentual_realizado??0)>=100?'bg-success':(($fase->percentual_realizado??0)>0?'bg-warning':'bg-light') }}"
                                             style="width:{{ min($fase->percentual_realizado??0,100) }}%"></div>
                                    </div>
                                    <small class="{{ ($fase->percentual_realizado??0)>=100?'text-success':'text-muted' }}">{{ round($fase->percentual_realizado??0,1) }}%</small>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">{{ __('Nenhuma fase cadastrada.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
    <div class="card-body text-center py-5 text-muted">
        <i class="fas fa-chart-area fa-3x mb-3" style="opacity:.3"></i>
        <p>{{ __('Selecione uma obra para visualizar o relatório.') }}</p>
    </div>
</div>
@endif
@stop
@section('js')
@if($obra && count($labels))
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
new Chart(document.getElementById('chartFases'), {
    type: 'bar',
    data: {
        labels: @json($labels),
        datasets: [
            { label: '{{ __("Planejado") }}', data: @json($planejado), backgroundColor: 'rgba(168,135,58,0.2)', borderColor: '#A8873A', borderWidth:2 },
            { label: '{{ __("Realizado") }}', data: @json($realizado), backgroundColor: 'rgba(26,158,110,0.6)', borderColor: '#1A9E6E', borderWidth:2 }
        ]
    },
    options: { responsive:true, scales: { y: { min:0, max:100, ticks:{ callback: v => v+'%' } } } }
});
</script>
@endif
@stop
