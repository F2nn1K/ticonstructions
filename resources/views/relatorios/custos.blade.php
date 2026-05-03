@extends('adminlte::page')
@section('title', __('app.menu.costs_report'))
@section('content_header')
<div><h1 class="mb-0"><i class="fas fa-file-invoice-dollar mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.costs_report') }}</h1>
<small class="text-muted">{{ __('Resumo de custos por obra e categoria') }}</small></div>
@stop
@section('content')
<style>.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}.kpi-mini .k-val{font-size:1.4rem;font-weight:800;line-height:1.2}</style>
<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-sm-6 col-md-4">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Todas as obras') }}</option>
                    @foreach($obras as $ob)<option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3 mt-2 mt-sm-0 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button>
                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="row mb-4">
    <div class="col-6 col-md-4 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-danger">{{ __('Total Gasto') }}</div><div class="k-val text-danger">R$ {{ number_format($totalGeral,2,',','.') }}</div></div></div></div>
    <div class="col-6 col-md-4 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-muted">{{ __('Lançamentos') }}</div><div class="k-val text-dark">{{ $totalLancamentos }}</div></div></div></div>
    <div class="col-6 col-md-4 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-muted">{{ __('Obras') }}</div><div class="k-val text-dark">{{ $porObra->count() }}</div></div></div></div>
</div>
<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header border-0 pt-3 pb-0"><h6 class="font-weight-bold mb-0">{{ __('Gastos por Obra') }}</h6></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr><th>{{ __('Obra') }}</th><th>{{ __('Lançamentos') }}</th><th>{{ __('Total') }}</th></tr></thead>
                    <tbody>
                    @forelse($porObra as $r)
                        <tr><td class="small font-weight-bold">{{ $r->obra_nome }}</td><td class="small text-center">{{ $r->qtd }}</td><td class="small font-weight-bold text-danger">R$ {{ number_format($r->total,2,',','.') }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">{{ __('Sem dados.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
    <div class="col-lg-5 mb-4">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header border-0 pt-3 pb-0"><h6 class="font-weight-bold mb-0">{{ __('Gastos por Categoria') }}</h6></div>
            <div class="card-body"><canvas id="chartCats" height="220"></canvas></div>
        </div>
    </div>
</div>
@stop
@section('js')
@if(count($labels))
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
new Chart(document.getElementById('chartCats'),{
    type:'doughnut',
    data:{ labels: @json($labels), datasets:[{ data: @json($valores), backgroundColor:['#A8873A','#E2C87A','#1A9E6E','#3B82F6','#EF4444','#8B5CF6','#F59E0B','#6EE7B7'] }] },
    options:{ plugins:{ legend:{ position:'bottom' } } }
});
</script>
@endif
@stop
