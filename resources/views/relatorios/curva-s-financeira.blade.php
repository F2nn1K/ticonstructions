@extends('adminlte::page')
@section('title', __('app.menu.financial_s_curve'))
@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div><h1 class="mb-0"><i class="fas fa-chart-line mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.financial_s_curve') }}</h1>
    <small class="text-muted">{{ __('Evolução financeira acumulada da obra') }}</small></div>
</div>
@stop
@section('content')
<style>.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}.kpi-mini .k-val{font-size:1.4rem;font-weight:800;line-height:1.2}</style>
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
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-muted">{{ __('Orçamento') }}</div><div class="k-val text-dark">R$ {{ number_format($orcamento,2,',','.') }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-danger">{{ __('Total Gasto') }}</div><div class="k-val text-danger">R$ {{ number_format($totalGasto,2,',','.') }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label" style="color:#1A9E6E">{{ __('Saldo') }}</div><div class="k-val" style="color:{{ $saldo>=0?'#1A9E6E':'#dc3545' }}">R$ {{ number_format($saldo,2,',','.') }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-warning">{{ __('% Executado') }}</div><div class="k-val text-warning">{{ $percGasto }}%</div></div></div></div>
</div>
<div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
    <div class="card-header border-0 pt-3 pb-0"><h6 class="font-weight-bold mb-0">{{ __('Curva S Financeira — Gasto Acumulado') }}</h6></div>
    <div class="card-body"><canvas id="chartFinanceiro" height="130"></canvas></div>
</div>
@else
<div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
    <div class="card-body text-center py-5 text-muted"><i class="fas fa-chart-line fa-3x mb-3" style="opacity:.3"></i><p>{{ __('Selecione uma obra para visualizar o relatório.') }}</p></div>
</div>
@endif
@stop
@section('js')
@if($obra && count($labels))
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
new Chart(document.getElementById('chartFinanceiro'), {
    type:'line',
    data:{
        labels: @json($labels),
        datasets:[
            { label:'{{ __("Mensal") }}', data: @json($valores), backgroundColor:'rgba(168,135,58,0.15)', borderColor:'#A8873A', borderWidth:2, fill:true, tension:.4 },
            { label:'{{ __("Acumulado") }}', data: @json($acumulado), backgroundColor:'transparent', borderColor:'#E2C87A', borderWidth:2, borderDash:[6,3], tension:.4 }
        ]
    },
    options:{ responsive:true, plugins:{legend:{position:'top'}}, scales:{ y:{ ticks:{ callback: v => 'R$ '+v.toLocaleString('pt-BR') } } } }
});
</script>
@endif
@stop
