@extends('adminlte::page')

@section('title', __('app.menu.cash_flow'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-chart-line mr-2" style="color:var(--ti-gold)"></i>
                {{ __('Fluxo de Caixa') }}
            </h1>
            <small class="text-muted">{{ __('Visão financeira consolidada de todas as obras') }}</small>
        </div>
        <div>
            <a href="{{ route('gastos.create') }}" class="btn btn-success btn-sm mr-2">
                <i class="fas fa-plus mr-1"></i> {{ __('Lançar Custo') }}
            </a>
            <a href="{{ route('gastos.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list mr-1"></i> {{ __('Ver Lançamentos') }}
            </a>
        </div>
    </div>
@stop

@section('content')
<style>
.kpi-mini { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); }
.kpi-mini .k-label { font-size:.72rem; text-transform:uppercase; font-weight:700; letter-spacing:.05em; opacity:.7; }
.kpi-mini .k-val   { font-size:1.8rem; font-weight:800; line-height:1.2; }
.chart-card { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); }
.rank-bar { height:10px; border-radius:5px; background:var(--ti-gold-gradient,linear-gradient(90deg,#A8873A,#E2C87A)); }
</style>

{{-- KPIs --}}
<div class="row mb-4">
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-muted">{{ __('Total Geral') }}</div>
                <div class="k-val text-dark">R$ {{ number_format($totalGeral, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:#1A9E6E">{{ __('Pago') }}</div>
                <div class="k-val" style="color:#1A9E6E">R$ {{ number_format($totalPago, 0, ',', '.') }}</div>
                @if($totalGeral > 0)
                    <small class="text-muted">{{ number_format($totalPago/$totalGeral*100, 1) }}% {{ __('do total') }}</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:var(--ti-gold,#C9A84C)">{{ __('A Pagar') }}</div>
                <div class="k-val" style="color:var(--ti-gold,#C9A84C)">R$ {{ number_format($totalPend, 0, ',', '.') }}</div>
                @if($totalGeral > 0)
                    <small class="text-muted">{{ number_format($totalPend/$totalGeral*100, 1) }}% {{ __('do total') }}</small>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Gráfico mensal --}}
    <div class="col-md-8 mb-4">
        <div class="card chart-card">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-chart-bar mr-2" style="color:var(--ti-gold)"></i>
                    {{ __('Desembolso Mensal — Últimos 12 Meses') }}
                </h6>
            </div>
            <div class="card-body">
                <canvas id="chartMensal" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Ranking por obra --}}
    <div class="col-md-4 mb-4">
        <div class="card chart-card">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-hard-hat mr-2" style="color:var(--ti-gold)"></i>
                    {{ __('Top Obras (por custo)') }}
                </h6>
            </div>
            <div class="card-body">
                @php $maxObra = $porObra->max('total') ?: 1; @endphp
                @forelse($porObra as $item)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="font-weight-bold text-truncate" style="max-width:160px"
                                  title="{{ $item->obra->nome ?? 'N/A' }}">
                                {{ $item->obra->nome ?? 'N/A' }}
                            </span>
                            <span class="text-muted">R$ {{ number_format($item->total, 0, ',', '.') }}</span>
                        </div>
                        <div style="height:8px; border-radius:4px; background:#f0f0f0; overflow:hidden">
                            <div class="rank-bar" style="width:{{ ($item->total/$maxObra)*100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">{{ __('Nenhum dado.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Ranking por categoria --}}
    <div class="col-md-6 mb-4">
        <div class="card chart-card">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-tags mr-2" style="color:var(--ti-gold)"></i>
                    {{ __('Top Categorias de Custo') }}
                </h6>
            </div>
            <div class="card-body">
                @php $maxCat = $porCategoria->max('total') ?: 1; @endphp
                @forelse($porCategoria as $item)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="font-weight-bold">{{ $item->categoria->nome ?? 'N/A' }}</span>
                            <span class="text-muted">R$ {{ number_format($item->total, 0, ',', '.') }}</span>
                        </div>
                        <div style="height:8px; border-radius:4px; background:#f0f0f0; overflow:hidden">
                            <div class="rank-bar" style="width:{{ ($item->total/$maxCat)*100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">{{ __('Nenhum dado.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tabela mensal --}}
    <div class="col-md-6 mb-4">
        <div class="card chart-card">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-table mr-2" style="color:var(--ti-gold)"></i>
                    {{ __('Resumo por Mês') }}
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Mês') }}</th>
                                <th class="text-right">{{ __('Total') }}</th>
                                <th class="text-right">{{ __('Pago') }}</th>
                                <th class="text-right">{{ __('Pendente') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($meses as $m)
                                <tr>
                                    <td class="small font-weight-bold">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $m->mes)->translatedFormat('M/Y') }}
                                    </td>
                                    <td class="text-right small text-dark">
                                        R$ {{ number_format($m->total, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right small text-success">
                                        R$ {{ number_format($m->pago, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right small" style="color:var(--ti-gold,#C9A84C)">
                                        R$ {{ number_format($m->pendente, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        {{ __('Nenhum dado disponível.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
(function () {
    var meses = @json($meses);
    var labelPago     = '{{ __("Pago") }}';
    var labelPendente = '{{ __("Pendente") }}';

    var labels   = meses.map(function(m) {
        var parts = m.mes.split('-');
        var d = new Date(parts[0], parts[1] - 1);
        return d.toLocaleDateString('{{ app()->getLocale() == "en" ? "en-US" : "pt-BR" }}', { month: 'short', year: '2-digit' });
    });
    var totais   = meses.map(function(m) { return parseFloat(m.total)   || 0; });
    var pagos    = meses.map(function(m) { return parseFloat(m.pago)    || 0; });
    var pendentes= meses.map(function(m) { return parseFloat(m.pendente)|| 0; });

    var ctx = document.getElementById('chartMensal').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: labelPago,
                    data: pagos,
                    backgroundColor: 'rgba(26,158,110,0.75)',
                    borderRadius: 4
                },
                {
                    label: labelPendente,
                    data: pendentes,
                    backgroundColor: 'rgba(201,168,76,0.75)',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': R$ ' +
                                ctx.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    ticks: {
                        callback: function(v) {
                            return 'R$ ' + (v/1000).toFixed(0) + 'k';
                        }
                    }
                }
            }
        }
    });
})();
</script>
@stop
