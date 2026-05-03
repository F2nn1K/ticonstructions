@extends('adminlte::page')
@section('title', $obra->nome . ' — Dashboard')
@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <a href="{{ route('dashboard') }}" class="text-muted" style="text-decoration:none;font-size:.8rem">
            <i class="fas fa-arrow-left mr-1"></i>{{ __('Painel Principal') }}
        </a>
        <h1 class="mb-0 mt-1" style="font-size:1.35rem;font-weight:900">
            <i class="fas fa-chart-bar mr-2" style="color:var(--ti-gold)"></i>{{ $obra->nome }}
        </h1>
        <small class="text-muted">{{ $obra->codigo ?? '' }} @if($obra->cliente)· {{ $obra->cliente }}@endif</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('obras.show', $obra) }}"         class="btn btn-outline-secondary btn-sm"><i class="fas fa-info-circle mr-1"></i>{{ __('Detalhes') }}</a>
        <a href="{{ route('cronograma.index') }}"          class="btn btn-outline-secondary btn-sm"><i class="fas fa-calendar mr-1"></i>{{ __('Cronograma') }}</a>
        <a href="{{ route('gastos.create') }}?obra_id={{ $obra->id }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>{{ __('Lançar Custo') }}</a>
    </div>
</div>
@stop

@section('content')
<style>
:root { --ti-gold:#C9A84C; --ti-green:#1A9E6E; --ti-red:#C94040; }
.dash-card { border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:1.25rem; }
.dash-card .dc-header { padding:12px 18px;background:#f7f5f0;border-radius:12px 12px 0 0;border-bottom:1px solid #eee; }
.dash-card .dc-header h6 { margin:0;font-weight:700;font-size:.83rem;color:#4a3f2f; }
.kpi-top-obra { border-radius:10px;padding:16px 20px;margin-bottom:12px; }
.kpi-top-obra .kv { font-size:1.5rem;font-weight:900;line-height:1.1; }
.kpi-top-obra .kl { font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;opacity:.8;margin-bottom:3px; }
.kpi-top-obra .ks { font-size:.72rem;margin-top:3px; }
.fase-row { display:flex;align-items:center;padding:8px 16px;border-bottom:1px solid #f5f5f5; }
.fase-row:last-child { border-bottom:none; }
.fase-prog { flex:1;margin:0 12px; }
.fase-prog-bar { height:6px;background:#eee;border-radius:3px;overflow:hidden; }
.fase-prog-fill { height:100%;border-radius:3px; }
.terc-row { padding:9px 16px;border-bottom:1px solid #f5f5f5;display:flex;align-items:center;justify-content:space-between; }
.terc-row:last-child { border-bottom:none; }
</style>

{{-- ═══ KPIs topo ══════════════════════════════════════════ --}}
<div class="row mb-3">
    <div class="col-6 col-md-3">
        <div class="kpi-top-obra" style="background:#f8d7da">
            <div class="kl" style="color:#721c24">{{ __('Gasto Acumulado') }}</div>
            <div class="kv" style="color:#721c24">R$ {{ number_format($gastoAcum,2,',','.') }}</div>
            <div class="ks" style="color:#721c24">{{ __('total histórico') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-top-obra" style="background:#fff3cd">
            <div class="kl" style="color:#856404">{{ __('Gasto Este Mês') }}</div>
            <div class="kv" style="color:#856404">R$ {{ number_format($gastoMes,2,',','.') }}</div>
            <div class="ks" style="color:#856404">
                {{ now()->format('m/Y') }}
                @php $varMes = $gastoMesAnt > 0 ? round((($gastoMes-$gastoMesAnt)/$gastoMesAnt)*100,1) : 0; @endphp
                @if($varMes != 0) <span style="font-weight:700">{{ $varMes > 0 ? '+' : '' }}{{ $varMes }}%</span> vs ant.@endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-top-obra" style="background:#d4edda">
            <div class="kl" style="color:#155724">{{ __('Funcionários Mês') }}</div>
            <div class="kv" style="color:#155724">{{ $funcMesAtual }}</div>
            <div class="ks" style="color:#155724">
                {{ __('mês ant.:') }} {{ $funcMesAnt }}
                @if($funcMesAtual > $funcMesAnt) <i class="fas fa-arrow-up"></i>@elseif($funcMesAtual < $funcMesAnt) <i class="fas fa-arrow-down"></i>@endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-top-obra" style="background:#e2e3e5">
            <div class="kl" style="color:#383d41">{{ __('Empresas Terceirizadas') }}</div>
            <div class="kv" style="color:#6f42c1">{{ $totalTerceiros }}</div>
            <div class="ks" style="color:#383d41">{{ __('envolvidas no projeto') }}</div>
        </div>
    </div>
</div>

<div class="row">

    {{-- ═══ Coluna esquerda ═══ --}}
    <div class="col-lg-8">

        {{-- Gráfico: Desembolso mensal --}}
        <div class="card dash-card">
            <div class="dc-header"><h6><i class="fas fa-chart-bar mr-2" style="color:var(--ti-gold)"></i>{{ __('Desembolso Mensal') }}</h6></div>
            <div class="card-body"><canvas id="chartMeses" height="90"></canvas></div>
        </div>

        {{-- Gráfico: Categorias mês atual vs anterior --}}
        <div class="card dash-card">
            <div class="dc-header">
                <h6><i class="fas fa-tags mr-2" style="color:var(--ti-gold)"></i>{{ __('Gastos por Categoria') }} — {{ __('Mês Atual vs Anterior') }}</h6>
            </div>
            <div class="card-body"><canvas id="chartCatCompar" height="110"></canvas></div>
        </div>

        {{-- Gráfico: Funcionários por mês --}}
        <div class="card dash-card">
            <div class="dc-header"><h6><i class="fas fa-users mr-2" style="color:var(--ti-gold)"></i>{{ __('Funcionários por Mês') }}</h6></div>
            <div class="card-body"><canvas id="chartFunc" height="80"></canvas></div>
        </div>

    </div>

    {{-- ═══ Coluna direita ═══ --}}
    <div class="col-lg-4">

        {{-- Donut: distribuição por categoria --}}
        <div class="card dash-card">
            <div class="dc-header"><h6><i class="fas fa-chart-pie mr-2" style="color:var(--ti-gold)"></i>{{ __('Distribuição por Categoria') }}</h6></div>
            <div class="card-body text-center">
                <canvas id="chartDonut" height="180"></canvas>
                <div class="mt-2" id="legendaDonut" style="font-size:.72rem;text-align:left"></div>
            </div>
        </div>

        {{-- Gastos por tipo --}}
        <div class="card dash-card">
            <div class="dc-header"><h6><i class="fas fa-layer-group mr-2" style="color:var(--ti-gold)"></i>{{ __('Gastos por Tipo') }}</h6></div>
            <div class="card-body p-0">
                @foreach($gastosPorTipo as $tp)
                @php
                    $tpLabels = ['material'=>__('Material'),'servico'=>__('Serviço'),'mao_de_obra'=>__('Mão de Obra'),'equipamento'=>__('Equipamento'),'terceiro'=>__('Terceiro')];
                    $tpColors = ['material'=>'#A8873A','servico'=>'#1A9E6E','mao_de_obra'=>'#4A90D9','equipamento'=>'#E67E22','terceiro'=>'#6f42c1'];
                    $tpTotal  = $gastosPorTipo->sum('total');
                    $pct = $tpTotal > 0 ? round(($tp->total/$tpTotal)*100,1) : 0;
                @endphp
                <div style="padding:9px 16px;border-bottom:1px solid #f5f5f5">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size:.8rem;font-weight:600">{{ $tpLabels[$tp->tipo] ?? $tp->tipo }}</span>
                        <span style="font-size:.8rem;font-weight:700;color:{{ $tpColors[$tp->tipo] ?? '#888' }}">R$ {{ number_format($tp->total,2,',','.') }}</span>
                    </div>
                    <div style="height:5px;background:#eee;border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $tpColors[$tp->tipo] ?? '#888' }};border-radius:3px"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Terceirizados --}}
        @if($terceirizados->count() > 0)
        <div class="card dash-card">
            <div class="dc-header"><h6><i class="fas fa-building mr-2" style="color:#6f42c1"></i>{{ __('Empresas Terceirizadas') }} ({{ $totalTerceiros }})</h6></div>
            <div class="card-body p-0">
                @foreach($terceirizados->take(8) as $t)
                <div class="terc-row">
                    <div>
                        <div style="font-size:.82rem;font-weight:600">{{ $t->nome }}</div>
                        <div style="font-size:.68rem;color:#aaa">{{ $t->qtd }} {{ __('lançamentos') }}</div>
                    </div>
                    <div style="font-size:.85rem;font-weight:700;color:#6f42c1">R$ {{ number_format($t->total,2,',','.') }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Fases --}}
        <div class="card dash-card">
            <div class="dc-header">
                <h6><i class="fas fa-tasks mr-2" style="color:var(--ti-gold)"></i>{{ __('Fases da Obra') }} ({{ $fasesConcl }}/{{ $totalFases }})</h6>
            </div>
            <div class="card-body p-0">
                @foreach($fases as $fase)
                @php
                    $fCor = match($fase->status) {
                        'concluida'    => '#1A9E6E',
                        'em_andamento' => $fase->atrasada ? '#C94040' : '#A8873A',
                        default        => '#ccc',
                    };
                    $fPct = $fase->percentual_realizado ?? 0;
                @endphp
                <div class="fase-row">
                    <div style="width:22px;font-size:.7rem;font-weight:800;color:#ccc">{{ $fase->ordem }}</div>
                    <div class="fase-prog">
                        <div style="font-size:.78rem;font-weight:600;margin-bottom:3px">
                            {{ $fase->nome_personalizado ?? $fase->faseCatalogo->nome ?? '—' }}
                            @if($fase->status==='concluida')<i class="fas fa-check-circle text-success ml-1" style="font-size:.65rem"></i>@endif
                            @if($fase->atrasada)<span style="color:#C94040;font-size:.6rem;font-weight:700">● {{ __('Atrasada') }}</span>@endif
                        </div>
                        <div class="fase-prog-bar">
                            <div class="fase-prog-fill" style="width:{{ $fPct }}%;background:{{ $fCor }}"></div>
                        </div>
                    </div>
                    <div style="font-size:.75rem;font-weight:700;color:{{ $fCor }};min-width:36px;text-align:right">{{ $fPct }}%</div>
                </div>
                @endforeach
                @if($fases->isEmpty())
                <div class="text-center py-3 text-muted small">{{ __('Nenhuma fase cadastrada.') }}</div>
                @endif
                {{-- Barra de progresso geral --}}
                <div style="padding:10px 16px;background:#f7f5f0;border-radius:0 0 12px 12px">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="font-weight-bold">{{ __('Progresso Geral') }}</span>
                        <strong style="color:var(--ti-gold)">{{ $progresso }}%</strong>
                    </div>
                    <div style="height:8px;background:#ddd;border-radius:4px;overflow:hidden">
                        <div style="height:100%;width:{{ $progresso }}%;background:linear-gradient(90deg,#A8873A,#E2C87A);border-radius:4px"></div>
                    </div>
                    @if($obra->data_fim_prevista)
                    <div class="text-muted mt-1" style="font-size:.68rem">
                        <i class="fas fa-calendar mr-1"></i>{{ __('Prazo') }}: {{ $obra->data_fim_prevista->format('d/m/Y') }}
                        @if($diasRestantes !== null)
                        <span style="font-weight:700;color:{{ $diasRestantes < 0 ? '#C94040' : ($diasRestantes < 30 ? '#C9A84C' : '#1A9E6E') }}">
                            ({{ $diasRestantes >= 0 ? $diasRestantes.' dias restantes' : abs($diasRestantes).' dias atrasado' }})
                        </span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /col-lg-4 --}}
</div>{{-- /row --}}
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
var GOLD   = 'rgba(168,135,58,.75)';
var GREEN  = 'rgba(26,158,110,.75)';
var RED    = 'rgba(201,64,64,.75)';
var BLUES  = ['#4A90D9','#A8873A','#1A9E6E','#C94040','#6f42c1','#E67E22','#17a2b8','#e83e8c'];

// ── Desembolso mensal
var mesesData = @json($gastosMeses->map(fn($r)=>(object)['mes'=>$r->mes,'total'=>(float)$r->total]));
new Chart(document.getElementById('chartMeses'), {
    type: 'bar',
    data: {
        labels: mesesData.map(r => r.mes),
        datasets: [{
            label: '{{ __("Gasto") }} (R$)',
            data: mesesData.map(r => r.total),
            backgroundColor: mesesData.map((r,i) => r.mes === '{{ $mesAtual }}' ? GOLD : 'rgba(168,135,58,.3)'),
            borderColor: GOLD, borderWidth: 2, borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { ticks: { callback: v => 'R$ ' + Number(v).toLocaleString('pt-BR',{minimumFractionDigits:0}) } } }
    }
});

// ── Categorias mês atual vs anterior
var catLabels = @json($categoriasLabels);
var catAtual  = @json($catMesAtual->keyBy('categoria')->map(fn($r)=>(float)$r->total));
var catAnt    = @json($catMesAnt->map(fn($r)=>(float)$r->total));
new Chart(document.getElementById('chartCatCompar'), {
    type: 'bar',
    data: {
        labels: catLabels,
        datasets: [
            { label: '{{ now()->format("m/Y") }}',                         data: catLabels.map(l => catAtual[l] || 0), backgroundColor: GOLD,  borderRadius: 4 },
            { label: '{{ now()->subMonth()->format("m/Y") }}', data: catLabels.map(l => catAnt[l]   || 0), backgroundColor: 'rgba(168,135,58,.3)', borderRadius: 4 },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { ticks: { callback: v => 'R$ ' + Number(v).toLocaleString('pt-BR',{minimumFractionDigits:0}) } } }
    }
});

// ── Funcionários por mês
var funcMeses = @json($funcPorMes->map(fn($r)=>(object)['mes'=>$r->mes,'total'=>(int)$r->total]));
new Chart(document.getElementById('chartFunc'), {
    type: 'bar',
    data: {
        labels: funcMeses.map(r => r.mes),
        datasets: [{
            label: '{{ __("Funcionários Únicos") }}',
            data: funcMeses.map(r => r.total),
            backgroundColor: funcMeses.map((r,i) => r.mes === '{{ $mesAtual }}' ? GREEN : 'rgba(26,158,110,.3)'),
            borderColor: GREEN, borderWidth: 2, borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { ticks: { stepSize: 1 } } }
    }
});

// ── Donut por categoria
var donutLabels = @json($gastosPorCategoria->pluck('categoria'));
var donutData   = @json($gastosPorCategoria->pluck('total')->map(fn($v)=>(float)$v));
new Chart(document.getElementById('chartDonut'), {
    type: 'doughnut',
    data: {
        labels: donutLabels,
        datasets: [{ data: donutData, backgroundColor: BLUES, hoverOffset: 8 }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ctx.label + ': R$ ' + Number(ctx.raw).toLocaleString('pt-BR',{minimumFractionDigits:2}) } }
        },
        cutout: '62%',
    }
});
// Legenda manual
var leg = document.getElementById('legendaDonut');
donutLabels.forEach(function(l, i) {
    var total = Number(donutData[i]).toLocaleString('pt-BR',{minimumFractionDigits:2});
    leg.innerHTML += '<div style="display:flex;align-items:center;margin-bottom:3px">'
        + '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:'+BLUES[i%BLUES.length]+';margin-right:6px;flex-shrink:0"></span>'
        + '<span style="flex:1">' + l + '</span>'
        + '<strong style="margin-left:8px">R$ ' + total + '</strong>'
        + '</div>';
});
</script>
@stop
