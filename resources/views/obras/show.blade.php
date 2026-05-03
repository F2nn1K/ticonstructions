@extends('adminlte::page')

@section('title', $obra->nome)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('obras.index') }}" class="btn btn-sm btn-outline-secondary mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="mb-0"><i class="fas fa-hard-hat mr-2"></i>{{ $obra->nome }}</h1>
                @if($obra->cliente)
                    <small class="text-muted"><i class="fas fa-user mr-1"></i>{{ $obra->cliente }}</small>
                @endif
            </div>
        </div>
        <div>
            <a href="{{ route('obras.lancamentos.create', $obra) }}" class="btn btn-success mr-2">
                <i class="fas fa-plus mr-1"></i> {{ __('Lançamento') }}
            </a>
            <a href="{{ route('obras.diario', $obra) }}" class="btn btn-info mr-2">
                <i class="fas fa-book-open mr-1"></i> {{ __('Diário') }}
            </a>
            <a href="{{ route('obras.edit', $obra) }}" class="btn btn-secondary mr-2">
                <i class="fas fa-edit mr-1"></i> {{ __('Editar') }}
            </a>
            <span class="badge badge-{{ $obra->status_badge }} badge-lg p-2" style="font-size:.9rem">
                {{ $obra->status_label }}
            </span>
        </div>
    </div>
@stop

@section('content')
<style>
/* ── KPI Cards ── */
.kpi-card { border-radius:10px; border:none; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.kpi-card .kpi-label { font-size:.78rem; color:#666; text-transform:uppercase; font-weight:600; }
.kpi-card .kpi-value { font-size:1.6rem; font-weight:700; }

/* ── Timeline de fases ── */
.timeline-fase { position:relative; padding-left:50px; margin-bottom:0; }
.timeline-fase::before {
    content:''; position:absolute; left:22px; top:0; bottom:0;
    width:2px; background:#dee2e6;
}
.fase-node {
    position:relative; margin-bottom:18px; background:#fff;
    border:1px solid #dee2e6; border-radius:10px; overflow:hidden;
}
.fase-node.ativa { border-color:#3c8dbc; box-shadow:0 0 0 3px rgba(60,141,188,.2); }
.fase-node.concluida { border-color:#28a745; }
.fase-node.atrasada { border-color:#dc3545; box-shadow:0 0 0 3px rgba(220,53,69,.15); }
.fase-circle {
    position:absolute; left:-36px; top:16px;
    width:30px; height:30px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:.8rem; font-weight:700; z-index:1;
    border:2px solid #fff; box-shadow:0 0 0 2px #dee2e6;
}
.fase-circle.pendente   { background:#6c757d; color:#fff; }
.fase-circle.em_andamento { background:#3c8dbc; color:#fff; box-shadow:0 0 0 2px #3c8dbc; }
.fase-circle.concluida  { background:#28a745; color:#fff; box-shadow:0 0 0 2px #28a745; }
.fase-circle.atrasada   { background:#dc3545; color:#fff; box-shadow:0 0 0 2px #dc3545; }
.fase-circle.suspensa   { background:#ffc107; color:#fff; }

.fase-header { padding:12px 15px; display:flex; justify-content:space-between; align-items:center; }
.fase-body   { padding:0 15px 12px; }
.progress-fase { height:6px; border-radius:3px; }

/* ── Stepper de progresso no topo ── */
.stepper { display:flex; overflow-x:auto; padding:8px 0; }
.step-item { flex:1; text-align:center; position:relative; min-width:80px; }
.step-item::before {
    content:''; position:absolute; top:14px; right:-50%; left:50%;
    height:2px; background:#dee2e6; z-index:0;
}
.step-item:last-child::before { display:none; }
.step-bubble {
    width:28px; height:28px; border-radius:50%; margin:0 auto 4px;
    display:flex; align-items:center; justify-content:center;
    font-size:.7rem; font-weight:700; position:relative; z-index:1;
    border:2px solid #dee2e6; background:#fff;
}
.step-bubble.done  { background:#28a745; border-color:#28a745; color:#fff; }
.step-bubble.active{ background:#3c8dbc; border-color:#3c8dbc; color:#fff; }
.step-bubble.late  { background:#dc3545; border-color:#dc3545; color:#fff; }
.step-label { font-size:.65rem; color:#666; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:75px; margin:0 auto; }
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- ── KPI ROW ───────────────────────────────────────────────── --}}
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="card kpi-card">
            <div class="card-body text-center py-3">
                <div class="kpi-label">{{ __('Progresso Geral') }}</div>
                <div class="kpi-value text-primary">{{ $obra->percentual_geral }}%</div>
                <div class="progress progress-fase mt-2">
                    <div class="progress-bar bg-primary" style="width:{{ $obra->percentual_geral }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kpi-card">
            <div class="card-body text-center py-3">
                <div class="kpi-label">{{ __('Custo Realizado') }}</div>
                <div class="kpi-value text-success">R$ {{ number_format($custoTotalReal, 2, ',', '.') }}</div>
                @if($custoTotalOrcado)
                    <small class="text-muted">{{ __('Orçado') }}: R$ {{ number_format($custoTotalOrcado, 2, ',', '.') }}</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kpi-card">
            <div class="card-body text-center py-3">
                <div class="kpi-label">{{ __('Fases Concluídas') }}</div>
                <div class="kpi-value text-info">
                    {{ $obra->fases->where('status','concluida')->count() }}/{{ $obra->fases->count() }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kpi-card">
            <div class="card-body text-center py-3">
                <div class="kpi-label">{{ __('Fase Atual') }}</div>
                <div style="font-size:1rem; font-weight:700; color:#3c8dbc;">
                    @if($faseAtiva)
                        {{ $faseAtiva->nome }}
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── STEPPER ──────────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="stepper">
            @foreach($obra->fases as $fs)
                @php
                    $bubbleClass = match(true) {
                        $fs->status === 'concluida' => 'done',
                        $fs->status === 'em_andamento' && $fs->atrasada => 'late',
                        $fs->status === 'em_andamento' => 'active',
                        default => ''
                    };
                    $icon = match($fs->status) {
                        'concluida' => '✓',
                        default => $loop->iteration
                    };
                @endphp
                <div class="step-item">
                    <div class="step-bubble {{ $bubbleClass }}">{{ $icon }}</div>
                    <div class="step-label">{{ $fs->nome }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row">
    {{-- ── TIMELINE ─────────────────────────────────────────── --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-tasks mr-2"></i>{{ __('Cronograma de Fases') }}</h6>
            </div>
            <div class="card-body">
                <div class="timeline-fase">
                    @foreach($obra->fases as $fase)
                        @php
                            $nodeClass = match(true) {
                                $fase->status === 'concluida' => 'concluida',
                                $fase->status === 'em_andamento' && $fase->atrasada => 'atrasada',
                                $fase->status === 'em_andamento' => 'ativa',
                                default => ''
                            };
                            $circleClass = $fase->status === 'em_andamento' && $fase->atrasada ? 'atrasada' : $fase->status;
                        @endphp
                        <div class="fase-node {{ $nodeClass }}">
                            <div class="fase-circle {{ $circleClass }}">
                                @if($fase->status === 'concluida')
                                    <i class="fas fa-check"></i>
                                @elseif($fase->atrasada)
                                    <i class="fas fa-exclamation"></i>
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </div>

                            <div class="fase-header">
                                <div>
                                    <strong>{{ $fase->nome }}</strong>
                                    @if($fase->atrasada)
                                        <span class="badge badge-danger ml-2">
                                            {{ __('Atrasada') }} {{ $fase->dias_atrasados }}d
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-{{ $fase->status_badge }} mr-2">
                                        {{ $fase->status_label }}
                                    </span>
                                    @if($fase->status === 'em_andamento')
                                        <form method="POST"
                                              action="{{ route('obras.fases.avancar', [$obra, $fase]) }}"
                                              onsubmit="return confirm('{{ __('Confirmar conclusão desta fase e avançar para a próxima?') }}')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-forward mr-1"></i> {{ __('Avançar Fase') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div class="fase-body">
                                {{-- Datas --}}
                                <div class="row small text-muted mb-2">
                                    @if($fase->data_inicio_baseline)
                                        <div class="col">
                                            <i class="fas fa-calendar mr-1"></i>
                                            {{ __('Baseline') }}: {{ $fase->data_inicio_baseline->format('d/m/Y') }}
                                            → {{ $fase->data_fim_baseline?->format('d/m/Y') }}
                                        </div>
                                    @endif
                                    @if($fase->data_inicio_real)
                                        <div class="col">
                                            <i class="fas fa-calendar-check mr-1"></i>
                                            {{ __('Real') }}: {{ $fase->data_inicio_real->format('d/m/Y') }}
                                            @if($fase->data_fim_real)→ {{ $fase->data_fim_real->format('d/m/Y') }}@endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Progresso + custo --}}
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="flex-grow-1 mr-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">{{ __('Progresso') }}</span>
                                            <span class="font-weight-bold" id="pct-{{ $fase->id }}">
                                                {{ $fase->percentual_realizado }}%
                                            </span>
                                        </div>
                                        <div class="progress progress-fase">
                                            <div class="progress-bar {{ $fase->status === 'concluida' ? 'bg-success' : 'bg-primary' }}"
                                                 id="bar-{{ $fase->id }}"
                                                 style="width:{{ $fase->percentual_realizado }}%">
                                            </div>
                                        </div>
                                    </div>
                                    @if($fase->status === 'em_andamento')
                                        <input type="range" min="0" max="100" step="5"
                                               value="{{ $fase->percentual_realizado }}"
                                               class="slider-progresso"
                                               data-fase="{{ $fase->id }}"
                                               data-obra="{{ $obra->id }}"
                                               style="width:80px">
                                    @endif
                                    <div class="text-right small ml-3">
                                        <div class="text-muted">{{ __('Custo Real') }}</div>
                                        <strong class="text-success">
                                            R$ {{ number_format($fase->custo_total_real, 2, ',', '.') }}
                                        </strong>
                                    </div>
                                </div>

                                {{-- Ocorrências --}}
                                @if($fase->ocorrencias->count() > 0)
                                    <div class="mt-2">
                                        <span class="badge badge-warning">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ $fase->ocorrencias->count() }} {{ __('ocorrência(s)') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── PAINEL LATERAL ───────────────────────────────────── --}}
    <div class="col-md-4">

        {{-- Dados da obra --}}
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>{{ __('Dados da Obra') }}</h6>
            </div>
            <div class="card-body small">
                @if($obra->codigo)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ __('Código') }}</span>
                        <strong>{{ $obra->codigo }}</strong>
                    </div>
                @endif
                @if($obra->valor_contrato)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ __('Contrato') }}</span>
                        <strong>R$ {{ number_format($obra->valor_contrato, 2, ',', '.') }}</strong>
                    </div>
                @endif
                @if($obra->area_total)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ __('Área') }}</span>
                        <strong>{{ number_format($obra->area_total, 2, ',', '.') }} m²</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">{{ __('Início Previsto') }}</span>
                    <strong>{{ $obra->data_inicio_prevista?->format('d/m/Y') ?? '—' }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">{{ __('Término Previsto') }}</span>
                    <strong>{{ $obra->data_fim_prevista?->format('d/m/Y') ?? '—' }}</strong>
                </div>
            </div>
        </div>

        {{-- Últimos lançamentos --}}
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-dollar-sign mr-2"></i>{{ __('Últimos Lançamentos') }}</h6>
                <a href="{{ route('obras.lancamentos.index', $obra) }}" class="btn btn-xs btn-outline-primary">
                    {{ __('Ver todos') }}
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($obra->lancamentos()->with('categoria')->latest('data_lancamento')->take(5)->get() as $lanc)
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <div>
                            <div class="small font-weight-bold">{{ Str::limit($lanc->descricao, 30) }}</div>
                            <div class="text-muted" style="font-size:.7rem">
                                {{ $lanc->categoria->nome ?? '—' }} · {{ $lanc->data_lancamento->format('d/m/Y') }}
                            </div>
                        </div>
                        <span class="text-success font-weight-bold small">
                            R$ {{ number_format($lanc->custo_total_real, 2, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="text-muted text-center py-3 small mb-0">{{ __('Nenhum lançamento ainda.') }}</p>
                @endforelse
            </div>
            @if($faseAtiva)
                <div class="card-footer text-center">
                    <a href="{{ route('obras.lancamentos.create', $obra) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus mr-1"></i> {{ __('Novo Lançamento') }}
                    </a>
                </div>
            @endif
        </div>

        {{-- Últimos registros do diário --}}
        @php
            $ultimosDiarios = \App\Models\DiarioObra::where('obra_id', $obra->id)
                ->orderBy('data_registro','desc')->take(3)->get();
        @endphp
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-book-open mr-2"></i>{{ __('Diário de Obra') }}</h6>
                <div>
                    <a href="{{ route('diario.create', ['obra_id'=>$obra->id]) }}"
                       class="btn btn-xs btn-success mr-1">
                        <i class="fas fa-plus"></i>
                    </a>
                    <a href="{{ route('obras.diario', $obra) }}"
                       class="btn btn-xs btn-outline-primary">{{ __('Ver todos') }}</a>
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($ultimosDiarios as $reg)
                    <a href="{{ route('diario.show', $reg) }}"
                       class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom text-dark"
                       style="text-decoration:none">
                        <div>
                            <div class="small font-weight-bold">
                                @if($reg->temOcorrencias())
                                    <i class="fas fa-exclamation-triangle text-danger mr-1" style="font-size:.7rem"></i>
                                @endif
                                {{ Str::limit($reg->titulo_formatado, 28) }}
                            </div>
                            <div class="text-muted" style="font-size:.7rem">
                                {{ $reg->data_registro->format('d/m/Y') }}
                                @if($reg->condicoes_climaticas) · {{ $reg->clima_icone }} @endif
                            </div>
                        </div>
                        @if($reg->totalFotos() > 0)
                            <span class="badge badge-light" style="font-size:.65rem">
                                <i class="fas fa-camera"></i> {{ $reg->totalFotos() }}
                            </span>
                        @endif
                    </a>
                @empty
                    <p class="text-muted text-center py-3 small mb-0">
                        {{ __('Nenhum registro ainda.') }}
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Registrar Ocorrência --}}
        @if($faseAtiva)
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle mr-2 text-warning"></i>{{ __('Registrar Ocorrência') }}</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('obras.fases.ocorrencia', [$obra, $faseAtiva]) }}">
                    @csrf
                    <div class="form-group mb-2">
                        <select name="tipo" class="form-control form-control-sm" required>
                            <option value="">{{ __('Tipo de Ocorrência') }}</option>
                            <option value="chuva">🌧 {{ __('Chuva / Intempérie') }}</option>
                            <option value="falta_material">📦 {{ __('Falta de Material') }}</option>
                            <option value="falta_mao_de_obra">👷 {{ __('Falta de Mão de Obra') }}</option>
                            <option value="erro_projeto">📐 {{ __('Erro de Projeto') }}</option>
                            <option value="problema_equipamento">⚙️ {{ __('Problema de Equipamento') }}</option>
                            <option value="acidente">🚨 {{ __('Acidente') }}</option>
                            <option value="outro">📝 {{ __('Outro') }}</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <input type="text" name="titulo" class="form-control form-control-sm"
                               placeholder="{{ __('Título da ocorrência') }}" required>
                    </div>
                    <div class="form-group mb-2">
                        <textarea name="descricao" rows="2" class="form-control form-control-sm"
                                  placeholder="{{ __('Descrição...') }}" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-7 form-group mb-2">
                            <input type="date" name="data_ocorrencia" class="form-control form-control-sm"
                                   value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-5 form-group mb-2">
                            <div class="input-group input-group-sm">
                                <input type="number" name="impacto_dias" class="form-control" value="0" min="0">
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ __('dias') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-warning btn-block">
                        <i class="fas fa-save mr-1"></i> {{ __('Registrar') }}
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>
@stop

@section('js')
<script>
// Slider de progresso
document.querySelectorAll('.slider-progresso').forEach(function (slider) {
    slider.addEventListener('input', function () {
        const val = this.value;
        const faseId = this.dataset.fase;
        document.getElementById('pct-' + faseId).textContent = val + '%';
        document.getElementById('bar-' + faseId).style.width = val + '%';
    });

    slider.addEventListener('change', function () {
        const val = this.value;
        const faseId = this.dataset.fase;
        const obraId = this.dataset.obra;

        fetch(`/obras/${obraId}/fases/${faseId}/progresso`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ percentual_realizado: val })
        });
    });
});
</script>
@stop
