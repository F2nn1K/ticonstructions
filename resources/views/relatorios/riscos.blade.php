@extends('adminlte::page')
@section('title', __('app.menu.risks_report'))
@section('content_header')
<div><h1 class="mb-0"><i class="fas fa-exclamation-triangle mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.risks_report') }}</h1>
<small class="text-muted">{{ __('Visão consolidada da matriz de riscos') }}</small></div>
@stop
@section('content')
<style>.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}.kpi-mini .k-val{font-size:1.8rem;font-weight:800;line-height:1.2}</style>
<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-sm-6 col-md-4"><label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Todas as obras') }}</option>
                    @foreach($obras as $ob)<option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach
                </select></div>
            <div class="col-sm-4 col-md-3 mt-2 mt-sm-0 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button>
                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-muted">{{ __('Total') }}</div><div class="k-val text-dark">{{ $totalRiscos }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-danger">{{ __('Críticos') }}</div><div class="k-val text-danger">{{ $criticos }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-warning">{{ __('Altos') }}</div><div class="k-val text-warning">{{ $altos }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label" style="color:#EF4444">{{ __('Abertos') }}</div><div class="k-val" style="color:#EF4444">{{ $abertos }}</div></div></div></div>
</div>
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light"><tr><th>{{ __('Título') }}</th><th>{{ __('Obra') }}</th><th>{{ __('Categoria') }}</th><th>{{ __('Prob.') }}</th><th>{{ __('Imp.') }}</th><th>{{ __('Nível') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                    @forelse($riscos as $r)
                        @php
                            $nivel = $r->probabilidade * $r->impacto;
                            if($nivel >= 12) { $nc='danger'; $nl=__('Crítico'); }
                            elseif($nivel >= 6) { $nc='warning'; $nl=__('Alto'); }
                            elseif($nivel >= 3) { $nc='info'; $nl=__('Médio'); }
                            else { $nc='success'; $nl=__('Baixo'); }
                            $sc = $r->status==='aberto'?'danger':($r->status==='mitigado'?'warning':'success');
                        @endphp
                        <tr>
                            <td class="small font-weight-bold">{{ \Illuminate\Support\Str::limit($r->titulo,40) }}</td>
                            <td class="small text-muted">{{ $r->obra_nome }}</td>
                            <td class="small">{{ $r->categoria }}</td>
                            <td class="text-center small">{{ $r->probabilidade }}</td>
                            <td class="text-center small">{{ $r->impacto }}</td>
                            <td><span class="badge badge-{{ $nc }}" style="font-size:.65rem">{{ $nl }} ({{ $nivel }})</span></td>
                            <td><span class="badge badge-{{ $sc }}" style="font-size:.65rem">{{ ucfirst($r->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block" style="opacity:.3"></i>{{ __('Nenhum risco registrado.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header border-0 pt-3 pb-0"><h6 class="font-weight-bold mb-0">{{ __('Por Categoria') }}</h6></div>
            <div class="card-body"><canvas id="chartRiscos" height="250"></canvas></div>
        </div>
    </div>
</div>
@stop
@section('js')
@if(count($catLabels))
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
new Chart(document.getElementById('chartRiscos'),{
    type:'bar',
    data:{ labels:@json($catLabels), datasets:[{ label:'{{ __("Quantidade") }}', data:@json($catData), backgroundColor:'rgba(168,135,58,0.7)', borderColor:'#A8873A', borderWidth:2 }] },
    options:{ indexAxis:'y', plugins:{legend:{display:false}}, scales:{x:{ticks:{stepSize:1}}} }
});
</script>
@endif
@stop
