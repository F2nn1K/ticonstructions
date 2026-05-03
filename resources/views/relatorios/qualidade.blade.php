@extends('adminlte::page')
@section('title', __('app.menu.quality_report'))
@section('content_header')
<div><h1 class="mb-0"><i class="fas fa-medal mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.quality_report') }}</h1>
<small class="text-muted">{{ __('Inspeções e não conformidades por obra') }}</small></div>
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
    <div class="col-6 col-md-4 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-primary">{{ __('Inspeções') }}</div><div class="k-val text-primary">{{ $totalInspecoes }}</div></div></div></div>
    <div class="col-6 col-md-4 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label" style="color:#1A9E6E">{{ __('Concluídas') }}</div><div class="k-val" style="color:#1A9E6E">{{ $inspecoesConcluidas }}</div></div></div></div>
    <div class="col-6 col-md-4 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-danger">{{ __('NCs Abertas') }}</div><div class="k-val text-danger">{{ $ncsAbertas }}</div></div></div></div>
</div>
<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header border-0 pt-3 pb-0"><h6 class="font-weight-bold mb-0">{{ __('Não Conformidades') }}</h6></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr><th>{{ __('Título') }}</th><th>{{ __('Obra') }}</th><th>{{ __('Gravidade') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                    @php $gravColors=['leve'=>'success','moderada'=>'warning','grave'=>'danger','critica'=>'dark'];
                         $stColors=['aberta'=>'danger','em_correcao'=>'warning','resolvida'=>'success','aceita'=>'secondary']; @endphp
                    @forelse($ncs as $nc)
                        <tr>
                            <td class="small font-weight-bold">{{ \Illuminate\Support\Str::limit($nc->titulo,40) }}</td>
                            <td class="small text-muted">{{ $nc->obra_nome }}</td>
                            <td><span class="badge badge-{{ $gravColors[$nc->gravidade]??'secondary' }}" style="font-size:.65rem">{{ ucfirst($nc->gravidade) }}</span></td>
                            <td><span class="badge badge-{{ $stColors[$nc->status]??'secondary' }}" style="font-size:.65rem">{{ ucfirst(str_replace('_',' ',$nc->status)) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('Nenhuma não conformidade.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
    <div class="col-lg-5 mb-4">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header border-0 pt-3 pb-0"><h6 class="font-weight-bold mb-0">{{ __('NCs por Gravidade') }}</h6></div>
            <div class="card-body text-center">
                @if(count($ncLabels))
                    <canvas id="chartNCs" height="220"></canvas>
                @else
                    <div class="text-muted py-4"><i class="fas fa-chart-pie fa-2x mb-2 d-block" style="opacity:.3"></i>{{ __('Sem dados') }}</div>
                @endif
                <div class="mt-3 text-muted small">
                    {{ __('Total de NCs') }}: <strong>{{ $totalNCs }}</strong> &nbsp;|&nbsp;
                    {{ __('Críticas') }}: <strong class="text-danger">{{ $ncsCriticas }}</strong>
                </div>
            </div>
        </div>
        <div class="card mt-3" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header border-0 pt-3 pb-0"><h6 class="font-weight-bold mb-0">{{ __('Inspeções Recentes') }}</h6></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr><th>{{ __('Data') }}</th><th>{{ __('Título') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                    @php $stC=['pendente'=>'warning','em_andamento'=>'info','concluida'=>'success','reprovada'=>'danger']; @endphp
                    @forelse($inspecoes->take(6) as $ins)
                        <tr>
                            <td class="small text-nowrap">{{ \Carbon\Carbon::parse($ins->data_inspecao)->format('d/m/Y') }}</td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($ins->titulo,30) }}</td>
                            <td><span class="badge badge-{{ $stC[$ins->status]??'secondary' }}" style="font-size:.6rem">{{ ucfirst($ins->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">{{ __('Sem inspeções.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
</div>
@stop
@section('js')
@if(count($ncLabels))
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
new Chart(document.getElementById('chartNCs'),{
    type:'doughnut',
    data:{ labels:@json($ncLabels), datasets:[{ data:@json($ncData), backgroundColor:['#22c55e','#f59e0b','#ef4444','#1f2937'] }] },
    options:{ plugins:{ legend:{ position:'bottom' } } }
});
</script>
@endif
@stop
