@extends('adminlte::page')
@section('title', __('Painel Principal'))
@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0" style="font-size:1.5rem;font-weight:900">
            <i class="fas fa-tachometer-alt mr-2" style="color:var(--ti-gold,#C9A84C)"></i>
            {{ __('Painel Principal') }}
        </h1>
        <small class="text-muted">{{ __('Bem-vindo') }}, <strong>{{ Auth::user()->name }}</strong> — {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('obras.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>{{ __('Nova Obra') }}</a>
        <a href="{{ route('gastos.create') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-dollar-sign mr-1"></i>{{ __('Lançar Custo') }}</a>
    </div>
</div>
@stop

@section('content')
<style>
:root { --ti-gold:#C9A84C; --ti-gold2:#A8873A; --ti-green:#1A9E6E; --ti-red:#C94040; }
.kpi-top { border-radius:0; border:none; background:transparent; }
.kpi-top .k-box { border-radius:10px; padding:14px 20px; min-width:160px; flex:1; }
.k-box .k-lbl { font-size:.65rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; opacity:.8; margin-bottom:2px; }
.k-box .k-val { font-size:1.35rem; font-weight:900; line-height:1.1; }
.k-box .k-sub { font-size:.7rem; margin-top:3px; opacity:.75; }

.obra-card { border-radius:12px; border:none; overflow:hidden; transition:.25s; box-shadow:0 2px 12px rgba(0,0,0,.09); }
.obra-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.14); }
.obra-card .oc-strip { height:4px; }
.obra-card .oc-body  { padding:14px 16px 12px; }
.obra-card .oc-nome  { font-weight:800; font-size:.95rem; line-height:1.2; }
.obra-card .oc-cod   { font-size:.68rem; color:#999; }

.badge-lucro   { background:#d4edda; color:#155724; font-size:.65rem; padding:3px 8px; border-radius:20px; font-weight:700; }
.badge-prej    { background:#f8d7da; color:#721c24; font-size:.65rem; padding:3px 8px; border-radius:20px; font-weight:700; }
.badge-neutro  { background:#e2e3e5; color:#383d41; font-size:.65rem; padding:3px 8px; border-radius:20px; font-weight:700; }
.badge-cron-ok   { background:#cce5ff; color:#004085; font-size:.65rem; padding:3px 8px; border-radius:20px; font-weight:700; }
.badge-cron-lat  { background:#d4edda; color:#155724; font-size:.65rem; padding:3px 8px; border-radius:20px; font-weight:700; }
.badge-cron-atrs { background:#f8d7da; color:#721c24; font-size:.65rem; padding:3px 8px; border-radius:20px; font-weight:700; }

.prog-sm { height:6px; background:#eee; border-radius:3px; overflow:hidden; margin:4px 0; }
.prog-sm-fill { height:100%; border-radius:3px; }

.kpi-row { display:flex; gap:10px; justify-content:space-between; }
.kpi-item { flex:1; text-align:center; }
.kpi-item .kv { font-size:1rem; font-weight:800; }
.kpi-item .kl { font-size:.62rem; color:#aaa; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }

.result-mes-val { font-size:1.15rem; font-weight:800; }
.result-acum-val { font-size:.88rem; font-weight:700; }
.sep-line { border-top:1px solid rgba(0,0,0,.06); margin:10px 0 8px; }

.gasto-up   { color:var(--ti-red); }
.gasto-down { color:var(--ti-green); }
.gasto-eq   { color:#888; }

.saldo-lucro { background:linear-gradient(90deg,#1A9E6E,#2DC48A); }
.saldo-prej  { background:linear-gradient(90deg,#C94040,#E26060); }
.saldo-neutro{ background:linear-gradient(90deg,#C9A84C,#E2C87A); }
</style>

{{-- ═══════════════════════════════════════════════════
     BARRA DE KPIs GLOBAIS (estilo topo draga)
     ═══════════════════════════════════════════════════ --}}
<div class="d-flex flex-wrap gap-2 mb-4" style="gap:10px">

    <div class="k-box" style="background:#f8d7da;flex:1;min-width:170px;border-radius:10px;padding:14px 20px">
        <div class="k-lbl" style="color:#721c24">{{ __('Total de Despesas') }}</div>
        <div class="k-val" style="color:#721c24">R$ {{ number_format($totalGastoMes,2,',','.') }}</div>
        <div class="k-sub" style="color:#721c24">
            {{ now()->format('m/Y') }}
            @if($varGastoMes != 0)
                <span class="{{ $varGastoMes > 0 ? 'gasto-up' : 'gasto-down' }}">
                    ({{ $varGastoMes > 0 ? '+' : '' }}{{ $varGastoMes }}% vs mês ant.)
                </span>
            @endif
        </div>
    </div>

    <div class="k-box" style="background:#fff3cd;flex:1;min-width:170px;border-radius:10px;padding:14px 20px">
        <div class="k-lbl" style="color:#856404">{{ __('Pendente a Pagar') }}</div>
        <div class="k-val" style="color:#856404">R$ {{ number_format($totalGastoPendente,2,',','.') }}</div>
        <div class="k-sub" style="color:#856404">{{ __('em aberto') }}</div>
    </div>

    <div class="k-box" style="background:#d1ecf1;flex:1;min-width:170px;border-radius:10px;padding:14px 20px">
        <div class="k-lbl" style="color:#0c5460">{{ __('Gasto Acumulado') }}</div>
        <div class="k-val" style="color:#0c5460">R$ {{ number_format($totalGastoAcum,2,',','.') }}</div>
        <div class="k-sub" style="color:#0c5460">{{ __('todas as obras') }}</div>
    </div>

    <div class="k-box" style="background:#d4edda;flex:1;min-width:130px;border-radius:10px;padding:14px 20px">
        <div class="k-lbl" style="color:#155724">{{ __('Obras Ativas') }}</div>
        <div class="k-val" style="color:#155724">{{ $obrasEmAndamento }}</div>
        <div class="k-sub" style="color:#155724">{{ $obrasPendentes }} {{ __('pendentes') }}</div>
    </div>

    <div class="k-box" style="background:{{ $totalFasesAtrasadas > 0 ? '#f8d7da' : '#e2e3e5' }};flex:1;min-width:130px;border-radius:10px;padding:14px 20px">
        <div class="k-lbl" style="color:{{ $totalFasesAtrasadas > 0 ? '#721c24' : '#383d41' }}">{{ __('Fases Atrasadas') }}</div>
        <div class="k-val" style="color:{{ $totalFasesAtrasadas > 0 ? 'var(--ti-red)' : '#383d41' }}">{{ $totalFasesAtrasadas }}</div>
        <div class="k-sub" style="color:{{ $totalFasesAtrasadas > 0 ? '#721c24' : '#383d41' }}">{{ $obrasConcluidas }} {{ __('obras conc.') }}</div>
    </div>

    @if($taxasPendentes > 0)
    <div class="k-box" style="background:#f3e8ff;flex:1;min-width:150px;border-radius:10px;padding:14px 20px">
        <div class="k-lbl" style="color:#6f42c1">{{ __('Taxa ADM Pendente') }}</div>
        <div class="k-val" style="color:#6f42c1">R$ {{ number_format($taxasPendentesValor,2,',','.') }}</div>
        <div class="k-sub" style="color:#6f42c1">{{ $taxasPendentes }} {{ __('lançamento(s)') }}</div>
    </div>
    @endif

</div>

{{-- ═══════════════════════════════════════════════════
     CARDS POR OBRA (estilo draga)
     ═══════════════════════════════════════════════════ --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 font-weight-bold" style="font-size:.9rem;text-transform:uppercase;letter-spacing:.05em;color:#6a6259">
        <i class="fas fa-hard-hat mr-2" style="color:var(--ti-gold)"></i>{{ __('Desempenho por Obra') }}
    </h5>
    <span class="badge badge-secondary">{{ $obrasCards->count() }} {{ __('ativas') }}</span>
</div>

@if($obrasCards->isEmpty())
<div class="card" style="border-radius:12px;border:2px dashed #ddd;box-shadow:none">
    <div class="card-body text-center py-5 text-muted">
        <i class="fas fa-hard-hat fa-3x mb-3" style="opacity:.25"></i>
        <p class="mb-3">{{ __('Nenhuma obra ativa no momento.') }}</p>
        <a href="{{ route('obras.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>{{ __('Cadastrar Obra') }}</a>
    </div>
</div>
@else
<div class="row">
@foreach($obrasCards as $card)
@php
    $obra = $card->obra;
    $varCls = $card->varMes > 0 ? 'gasto-up' : ($card->varMes < 0 ? 'gasto-down' : 'gasto-eq');
    $varIco = $card->varMes > 0 ? 'fa-arrow-up' : ($card->varMes < 0 ? 'fa-arrow-down' : 'fa-minus');
    $stripCls = match($obra->status) {
        'em_andamento' => $card->fasesAtrasadas > 0 ? 'saldo-prej' : 'saldo-lucro',
        'pausada'      => 'saldo-neutro',
        default        => 'saldo-neutro',
    };
    $cronLabel = match($card->cronStatus) {
        'atrasado'  => ['text'=>__('Atrasado'),  'cls'=>'badge-cron-atrs', 'ico'=>'fa-exclamation-triangle'],
        'adiantado' => ['text'=>__('Adiantado'), 'cls'=>'badge-cron-lat',  'ico'=>'fa-check-circle'],
        default     => ['text'=>__('No Prazo'),  'cls'=>'badge-cron-ok',   'ico'=>'fa-clock'],
    };
    $progColor = $card->fasesAtrasadas > 0 ? 'var(--ti-red)' : ($card->progresso >= 75 ? 'var(--ti-green)' : 'var(--ti-gold)');
@endphp
<div class="col-md-6 col-xl-4 mb-4">
    <div class="obra-card">
        <div class="oc-strip {{ $stripCls }}"></div>
        <div class="oc-body" style="background:#fff">

            {{-- Nome + badges --}}
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div style="flex:1;margin-right:8px">
                    <div class="oc-nome">{{ $obra->nome }}</div>
                    <div class="oc-cod">{{ $obra->codigo ?? '' }} @if($obra->cliente)· {{ $obra->cliente }}@endif</div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1" style="gap:4px">
                    <span class="{{ $card->cronStatus==='atrasado' ? 'badge-prej' : ($card->cronStatus==='adiantado' ? 'badge-lucro' : 'badge-neutro') }}">
                        <i class="fas {{ $cronLabel['ico'] }} mr-1"></i>{{ $cronLabel['text'] }}
                    </span>
                    @if($obra->status==='pausada')
                    <span class="badge-neutro"><i class="fas fa-pause mr-1"></i>{{ __('Pausada') }}</span>
                    @endif
                </div>
            </div>

            {{-- Resultado deste mês --}}
            <div style="background:#f9f7f3;border-radius:8px;padding:10px 12px;margin-bottom:10px">
                <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;color:#999;margin-bottom:4px">
                    <i class="fas fa-calendar mr-1"></i>{{ __('RESULTADO DO MÊS') }} – {{ now()->format('m/Y') }}
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="result-mes-val" style="color:var(--ti-red)">
                            - R$ {{ number_format($card->gastoMes, 2, ',', '.') }}
                        </div>
                        @if($card->varMes != 0)
                        <div style="font-size:.7rem" class="{{ $varCls }}">
                            <i class="fas {{ $varIco }} mr-1"></i>
                            {{ abs($card->varMes) }}% vs {{ now()->subMonth()->format('m/Y') }}
                        </div>
                        @else
                        <div style="font-size:.7rem;color:#aaa">{{ __('Sem mês anterior para comparar') }}</div>
                        @endif
                    </div>
                    <div class="text-right">
                        <div style="font-size:.62rem;color:#999">{{ __('Mês anterior') }}</div>
                        <div style="font-size:.85rem;font-weight:700;color:#aaa">R$ {{ number_format($card->gastoMesAnt,2,',','.') }}</div>
                    </div>
                </div>
            </div>

            {{-- Saldo acumulado --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;color:#999">{{ __('SALDO ACUMULADO') }}</div>
                    <div class="result-acum-val" style="color:var(--ti-red)">- R$ {{ number_format($card->gastoAcum,2,',','.') }}</div>
                </div>
                @if($obra->orcamento_total)
                <div class="text-right">
                    <div style="font-size:.62rem;color:#999">{{ __('Orçamento') }}</div>
                    <div style="font-size:.82rem;font-weight:600">R$ {{ number_format($obra->orcamento_total,2,',','.') }}</div>
                </div>
                @endif
            </div>

            <div class="sep-line"></div>

            {{-- KPIs rápidos --}}
            <div class="kpi-row mb-2">
                <div class="kpi-item">
                    <div class="kv" style="color:var(--ti-gold)">{{ $card->funcMes }}</div>
                    <div class="kl">{{ __('Func. mês') }}</div>
                    @if($card->funcMesAnt > 0)
                    <div style="font-size:.6rem;color:{{ $card->funcMes > $card->funcMesAnt ? 'var(--ti-green)' : 'var(--ti-red)' }}">
                        <i class="fas fa-{{ $card->funcMes >= $card->funcMesAnt ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($card->funcMes - $card->funcMesAnt) }} vs ant.
                    </div>
                    @endif
                </div>
                <div class="kpi-item">
                    <div class="kv" style="color:#6f42c1">{{ $card->terceiros }}</div>
                    <div class="kl">{{ __('Terceiros') }}</div>
                </div>
                <div class="kpi-item">
                    <div class="kv">{{ $card->fasesConcl }}/{{ $card->totalFases }}</div>
                    <div class="kl">{{ __('Fases') }}</div>
                </div>
                <div class="kpi-item">
                    <div class="kv" style="color:{{ $progColor }}">{{ $card->progresso }}%</div>
                    <div class="kl">{{ __('Progresso') }}</div>
                </div>
            </div>

            {{-- Barra de progresso --}}
            <div class="prog-sm">
                <div class="prog-sm-fill" style="width:{{ $card->progresso }}%;background:{{ $progColor }}"></div>
            </div>

            {{-- Fase atual --}}
            @if($card->faseAtual)
            <div style="font-size:.72rem;margin-top:6px">
                <i class="fas fa-dot-circle mr-1" style="color:var(--ti-green);font-size:.55rem"></i>
                <span class="text-muted">{{ __('Fase atual:') }}</span>
                <strong>{{ $card->faseAtual->nome_personalizado ?? $card->faseAtual->faseCatalogo->nome ?? '—' }}</strong>
            </div>
            @endif
            {{-- Fases em atraso detalhadas --}}
            @if($card->fasesAtrasadasColl->count() > 0)
            <div style="background:#fff5f5;border:1px solid #f5c6cb;border-radius:6px;padding:6px 8px;margin-top:6px">
                <div style="font-size:.62rem;font-weight:800;color:#721c24;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">
                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ __('Fases em Atraso') }}
                </div>
                @foreach($card->fasesAtrasadasColl->take(3) as $fAtras)
                <div style="font-size:.68rem;color:#721c24;display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
                    <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $fAtras->nome_personalizado ?? ($fAtras->faseCatalogo->nome ?? __('Fase')) }}
                    </span>
                    <span style="background:#f8d7da;color:#721c24;font-size:.6rem;padding:1px 6px;border-radius:10px;margin-left:6px;font-weight:700;white-space:nowrap">
                        {{ $fAtras->dias_atrasados }}d {{ __('atras.') }}
                    </span>
                </div>
                @endforeach
                @if($card->fasesAtrasadasColl->count() > 3)
                <div style="font-size:.62rem;color:#999;margin-top:2px">
                    +{{ $card->fasesAtrasadasColl->count() - 3 }} {{ __('mais') }}
                </div>
                @endif
            </div>
            @endif

            {{-- Prazo --}}
            @if($obra->data_fim_prevista)
            <div style="font-size:.68rem;color:#aaa;margin-top:4px">
                <i class="fas fa-calendar-alt mr-1"></i>{{ __('Prazo') }}: {{ $obra->data_fim_prevista->format('d/m/Y') }}
                @if($card->diasRestantes !== null)
                <span style="font-weight:700;color:{{ $card->diasRestantes < 0 ? 'var(--ti-red)' : ($card->diasRestantes < 30 ? '#c9a84c' : 'var(--ti-green)') }}">
                    ({{ $card->diasRestantes >= 0 ? $card->diasRestantes.' d' : abs($card->diasRestantes).' d atras.' }})
                </span>
                @endif
            </div>
            @endif

            <div class="sep-line"></div>

            {{-- Ações --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route('obras.dashboard', $obra) }}" class="btn btn-sm" style="background:var(--ti-gold);color:#fff;border-radius:20px;font-size:.75rem;padding:4px 14px;font-weight:700">
                    <i class="fas fa-chart-bar mr-1"></i>{{ __('Dashboard') }}
                </a>
                <div class="d-flex gap-2" style="gap:6px">
                    <a href="{{ route('obras.show', $obra) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:20px;font-size:.75rem;padding:4px 10px">{{ __('Detalhes') }}</a>
                    <a href="{{ route('cronograma.index') }}?obra_id={{ $obra->id }}" class="btn btn-sm btn-outline-secondary" style="border-radius:20px;font-size:.75rem;padding:4px 10px">{{ __('Cronograma') }}</a>
                </div>
            </div>

        </div>
    </div>
</div>
@endforeach
</div>
@endif

{{-- ═══ Gráfico global de desembolso ══════════════════ --}}
@if($gastosPorMes->count() > 0)
<div class="card mt-3" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
    <div class="card-header" style="background:#f7f5f0;border-radius:12px 12px 0 0">
        <h6 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2" style="color:var(--ti-gold)"></i>{{ __('Desembolso Mensal — Todas as Obras') }}</h6>
    </div>
    <div class="card-body">
        <canvas id="chartDesembolso" height="80"></canvas>
    </div>
</div>
@endif

@stop

@section('js')
@if($gastosPorMes->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
var meses  = @json($gastosPorMes->pluck('mes'));
var totais = @json($gastosPorMes->pluck('total')->map(fn($v)=>(float)$v));
var pagos  = @json($gastosPorMes->pluck('pago')->map(fn($v)=>(float)$v));

new Chart(document.getElementById('chartDesembolso'), {
    type: 'bar',
    data: {
        labels: meses,
        datasets: [
            { label: '{{ __("Total Lançado") }}', data: totais, backgroundColor: 'rgba(201,168,76,.55)', borderColor: '#A8873A', borderWidth: 2, borderRadius: 5 },
            { label: '{{ __("Pago") }}',           data: pagos,  backgroundColor: 'rgba(26,158,110,.5)',  borderColor: '#1A9E6E', borderWidth: 2, borderRadius: 5 },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { ticks: { callback: v => 'R$ ' + Number(v).toLocaleString('pt-BR',{minimumFractionDigits:0}) } }
        }
    }
});
</script>
@endif
@stop
