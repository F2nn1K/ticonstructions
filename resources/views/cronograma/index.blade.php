@extends('adminlte::page')

@section('title', __('Cronograma de Obras'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fas fa-calendar-alt mr-2" style="color:var(--ti-gold)"></i>{{ __('Cronograma de Obras') }}</h1>
            <small class="text-muted">{{ __('Acompanhamento de fases e progresso de todas as obras') }}</small>
        </div>
        <a href="{{ route('obras.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> {{ __('Nova Obra') }}
        </a>
    </div>
@stop

@section('content')
<style>
.kpi-mini { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); transition:transform .2s; }
.kpi-mini:hover { transform:translateY(-2px); }
.kpi-mini .k-label { font-size:.72rem; text-transform:uppercase; font-weight:700; letter-spacing:.05em; opacity:.7; }
.kpi-mini .k-val   { font-size:1.9rem; font-weight:800; line-height:1.1; }

.obra-card { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); transition:all .25s; overflow:hidden; }
.obra-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.12); }
.obra-card .card-header-strip {
    height:4px;
    background: var(--ti-gold-gradient, linear-gradient(90deg,#A8873A,#E2C87A));
}
.obra-card.status-em_andamento .card-header-strip { background:linear-gradient(90deg,#1A9E6E,#2DC48A); }
.obra-card.status-concluida    .card-header-strip { background:linear-gradient(90deg,#1A9E6E,#2DC48A); }
.obra-card.status-atrasada     .card-header-strip { background:linear-gradient(90deg,#C94040,#E26060); }
.obra-card.status-pausada      .card-header-strip { background:linear-gradient(90deg,#C9A84C,#E2C87A); }
.obra-card.status-pendente     .card-header-strip { background:linear-gradient(90deg,#6A6259,#B0A898); }

.fase-pills { display:flex; flex-wrap:wrap; gap:3px; }
.fase-pill  { font-size:.62rem; padding:2px 7px; border-radius:20px; font-weight:600; }
.fase-pill.done   { background:#d4edda; color:#155724; }
.fase-pill.active { background:#cce5ff; color:#004085; }
.fase-pill.late   { background:#f8d7da; color:#721c24; }
.fase-pill.pend   { background:#f0f0f0; color:#666; }

.prog-bar-obra { height:8px; border-radius:4px; background:#eee; overflow:hidden; }
.prog-bar-fill { height:100%; border-radius:4px; transition:width .4s ease; }

.status-badge { font-size:.72rem; padding:4px 10px; border-radius:20px; font-weight:700; }
.badge-em_andamento { background:#d4edda; color:#155724; }
.badge-concluida    { background:#d4edda; color:#155724; }
.badge-pendente     { background:#f0f0f0; color:#555; }
.badge-pausada      { background:#fff3cd; color:#856404; }
.badge-cancelada    { background:#f8d7da; color:#721c24; }

.filter-bar { background:#fff; border-radius:12px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,.06); margin-bottom:20px; }
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- ── KPIs ───────────────────────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-muted">{{ __('Total de Obras') }}</div>
                <div class="k-val text-dark">{{ $totais['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:#1A9E6E">{{ __('Em Andamento') }}</div>
                <div class="k-val" style="color:#1A9E6E">{{ $totais['em_andamento'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-muted">{{ __('Concluídas') }}</div>
                <div class="k-val text-dark">{{ $totais['concluidas'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-muted">{{ __('Pendentes') }}</div>
                <div class="k-val" style="color:var(--ti-gold,#C9A84C)">{{ $totais['pendentes'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filtros ─────────────────────────────────────────────────── --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('cronograma.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
        <div class="mr-3 mb-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('Buscar') }}</label>
            <input type="text" name="busca" value="{{ request('busca') }}"
                   class="form-control form-control-sm"
                   placeholder="{{ __('Nome, cliente ou código...') }}" style="min-width:220px">
        </div>
        <div class="mr-3 mb-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('Status') }}</label>
            <select name="status" class="form-control form-control-sm" style="min-width:160px">
                <option value="">{{ __('Todos') }}</option>
                <option value="pendente"     {{ request('status')=='pendente'     ? 'selected':'' }}>{{ __('Pendente') }}</option>
                <option value="em_andamento" {{ request('status')=='em_andamento' ? 'selected':'' }}>{{ __('Em Andamento') }}</option>
                <option value="pausada"      {{ request('status')=='pausada'      ? 'selected':'' }}>{{ __('Pausada') }}</option>
                <option value="concluida"    {{ request('status')=='concluida'    ? 'selected':'' }}>{{ __('Concluída') }}</option>
                <option value="cancelada"    {{ request('status')=='cancelada'    ? 'selected':'' }}>{{ __('Cancelada') }}</option>
            </select>
        </div>
        <div class="mb-2 mr-2">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search mr-1"></i> {{ __('Filtrar') }}
            </button>
        </div>
        @if(request()->hasAny(['busca','status']))
            <div class="mb-2">
                <a href="{{ route('cronograma.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> {{ __('Limpar') }}
                </a>
            </div>
        @endif
    </form>
</div>

{{-- ── Cards das Obras ─────────────────────────────────────────── --}}
@if($obras->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-hard-hat fa-3x mb-3" style="opacity:.3"></i>
            <p class="mb-2">{{ __('Nenhuma obra encontrada.') }}</p>
            <a href="{{ route('obras.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> {{ __('Cadastrar Obra') }}
            </a>
        </div>
    </div>
@else
<div class="row">
    @foreach($obras as $obra)
        @php
            $pct         = $obra->percentual_geral ?? 0;
            $faseAtiva   = $obra->fases->firstWhere('status', 'em_andamento');
            $faseAtraso  = $obra->fases->where('status', 'em_andamento')->filter(fn($f) => $f->atrasada)->count();
            $fasesConcl  = $obra->fases->where('status', 'concluida')->count();
            $totalFases  = $obra->fases->count();

            $barColor = match($obra->status) {
                'concluida'    => '#1A9E6E',
                'em_andamento' => $faseAtraso ? '#C94040' : 'var(--ti-gold,#C9A84C)',
                'pausada'      => '#C9A84C',
                default        => '#B0A898',
            };
        @endphp
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card obra-card status-{{ $obra->status }}">
                <div class="card-header-strip"></div>
                <div class="card-body pt-3 pb-2">

                    {{-- Cabeçalho --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1 mr-2">
                            <a href="{{ route('obras.show', $obra) }}"
                               class="font-weight-bold text-dark"
                               style="font-size:1rem; text-decoration:none; display:block; line-height:1.3">
                                {{ $obra->nome }}
                            </a>
                            @if($obra->cliente)
                                <small class="text-muted">
                                    <i class="fas fa-user mr-1"></i>{{ $obra->cliente }}
                                </small>
                            @endif
                        </div>
                        <span class="status-badge badge-{{ $obra->status }}">
                            @php
                                $labels = [
                                    'pendente'     => __('Pendente'),
                                    'em_andamento' => __('Em Andamento'),
                                    'pausada'      => __('Pausada'),
                                    'concluida'    => __('Concluída'),
                                    'cancelada'    => __('Cancelada'),
                                ];
                            @endphp
                            {{ $labels[$obra->status] ?? $obra->status }}
                        </span>
                    </div>

                    {{-- Progresso --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">{{ __('Progresso Geral') }}</span>
                            <strong>{{ $pct }}%</strong>
                        </div>
                        <div class="prog-bar-obra">
                            <div class="prog-bar-fill" style="width:{{ $pct }}%; background:{{ $barColor }}"></div>
                        </div>
                    </div>

                    {{-- Fase ativa --}}
                    @if($faseAtiva)
                        @php
                            $totalT = \Illuminate\Support\Facades\DB::table('obra_fase_tarefas')->where('obra_fase_id',$faseAtiva->id)->count();
                            $concT  = \Illuminate\Support\Facades\DB::table('obra_fase_tarefas')->where('obra_fase_id',$faseAtiva->id)->where('concluida',1)->count();
                            $pctT   = $totalT > 0 ? round(($concT/$totalT)*100) : ($faseAtiva->percentual_realizado ?? 0);
                        @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">
                                    <i class="fas fa-dot-circle mr-1" style="color:#1A9E6E;font-size:.6rem"></i>
                                    {{ __('Fase atual:') }}
                                    <a href="{{ route('cronograma.fase-detalhe', [$obra, $faseAtiva]) }}"
                                       class="font-weight-bold text-dark" style="text-decoration:none">
                                        {{ Str::limit($faseAtiva->nome_personalizado ?? $faseAtiva->faseCatalogo->nome ?? '—', 22) }}
                                    </a>
                                    @if($faseAtiva->atrasada)
                                        <span class="badge badge-danger ml-1" style="font-size:.6rem">{{ __('Atrasada') }}</span>
                                    @endif
                                </span>
                                <strong style="font-size:.72rem">{{ $pctT }}%</strong>
                            </div>
                            @if($totalT > 0)
                            <div style="height:5px;background:#eee;border-radius:3px;overflow:hidden">
                                <div style="height:100%;width:{{ $pctT }}%;background:#1A9E6E;border-radius:3px;transition:.4s"></div>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.65rem">{{ $concT }}/{{ $totalT }} {{ __('tarefas concluídas') }}</div>
                            @endif
                        </div>
                    @endif

                    {{-- Pílulas das fases clicáveis --}}
                    @if($totalFases > 0)
                        <div class="fase-pills mb-3">
                            @foreach($obra->fases->take(8) as $fs)
                                @php
                                    $pc = match(true) {
                                        $fs->status === 'concluida' => 'done',
                                        $fs->status === 'em_andamento' && $fs->atrasada => 'late',
                                        $fs->status === 'em_andamento' => 'active',
                                        default => 'pend',
                                    };
                                @endphp
                                <a href="{{ route('cronograma.fase-detalhe', [$obra, $fs]) }}"
                                   class="fase-pill {{ $pc }}"
                                   title="{{ $fs->nome_personalizado ?? $fs->faseCatalogo->nome ?? '—' }}"
                                   style="text-decoration:none">
                                    {{ Str::limit($fs->nome_personalizado ?? $fs->faseCatalogo->nome ?? '—', 14) }}
                                </a>
                            @endforeach
                            @if($totalFases > 8)
                                <span class="fase-pill pend">+{{ $totalFases - 8 }} {{ __('mais') }}</span>
                            @endif
                        </div>
                    @else
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ __('Sem fases cadastradas.') }}
                            <a href="{{ route('obras.show', $obra) }}">{{ __('Configurar') }}</a>
                        </p>
                    @endif

                    {{-- Rodapé --}}
                    <div class="d-flex justify-content-between align-items-center small text-muted pt-2"
                         style="border-top:1px solid #f0f0f0">
                        <span>
                            <i class="fas fa-tasks mr-1"></i>
                            {{ $fasesConcl }}/{{ $totalFases }} {{ __('fases') }}
                        </span>
                        @if($obra->data_fim_prevista)
                            <span>
                                <i class="fas fa-calendar mr-1"></i>
                                {{ __('Prazo') }}: {{ $obra->data_fim_prevista->format('d/m/Y') }}
                            </span>
                        @endif
                        <a href="{{ route('obras.show', $obra) }}" class="btn btn-xs"
                           style="background:var(--ti-gold-gradient,linear-gradient(135deg,#A8873A,#E2C87A));color:#fff;border-radius:20px;padding:3px 10px">
                            {{ __('Abrir') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Paginação --}}
@if($obras->hasPages())
    <div class="d-flex justify-content-center mt-2">
        {{ $obras->links() }}
    </div>
@endif
@endif

@stop

@section('css')
<style>
.gap-2 { gap:.5rem; }
</style>
@stop
