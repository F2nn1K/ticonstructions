@extends('adminlte::page')

@section('title', __('app.menu.physical_progress'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-chart-line mr-2" style="color:var(--ti-gold)"></i>
                {{ __('app.menu.physical_progress') }}
            </h1>
            <small class="text-muted">{{ __('Progresso físico consolidado de todas as obras') }}</small>
        </div>
        <div>
            <a href="{{ route('producao.medicao') }}" class="btn btn-success btn-sm mr-2">
                <i class="fas fa-plus mr-1"></i> {{ __('app.menu.record_measurement') }}
            </a>
            <a href="{{ route('producao.aprovacao') }}" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-check-circle mr-1"></i> {{ __('app.menu.approve_measurements') }}
                @if($totalPendentes > 0)
                    <span class="badge badge-warning ml-1">{{ $totalPendentes }}</span>
                @endif
            </a>
        </div>
    </div>
@stop

@section('content')
<style>
.kpi-mini { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); }
.kpi-mini .k-label { font-size:.72rem; text-transform:uppercase; font-weight:700; letter-spacing:.05em; opacity:.7; }
.kpi-mini .k-val   { font-size:1.8rem; font-weight:800; line-height:1.2; }
.obra-card { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); overflow:hidden; }
.progress-bar-gold { background:linear-gradient(90deg,#A8873A,#E2C87A); }
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- KPIs --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-muted">{{ __('Total de Obras') }}</div>
                <div class="k-val text-dark">{{ $totalObras }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:var(--ti-gold,#C9A84C)">{{ __('Avanço Médio') }}</div>
                <div class="k-val" style="color:var(--ti-gold,#C9A84C)">{{ number_format($mediaAvanco ?? 0, 1) }}%</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-warning">{{ __('Medições Pendentes') }}</div>
                <div class="k-val text-warning">{{ $totalPendentes }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:#1A9E6E">{{ __('Aprovadas no Mês') }}</div>
                <div class="k-val" style="color:#1A9E6E">{{ $totalAprovadas }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filtro --}}
<div class="mb-3">
    <form method="GET" action="{{ route('producao.index') }}" class="d-flex gap-2 align-items-center">
        <select name="obra_id" class="form-control form-control-sm" style="max-width:300px" onchange="this.form.submit()">
            <option value="">{{ __('Todas as obras') }}</option>
            @foreach($todasObras as $ob)
                <option value="{{ $ob->id }}" {{ $obraId == $ob->id ? 'selected' : '' }}>
                    {{ $ob->nome }}{{ $ob->codigo ? ' ('.$ob->codigo.')' : '' }}
                </option>
            @endforeach
        </select>
        @if($obraId)
            <a href="{{ route('producao.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times"></i>
            </a>
        @endif
    </form>
</div>

{{-- Cards de obras --}}
@forelse($obras as $obra)
    <div class="card obra-card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="mb-0 font-weight-bold">
                        <a href="{{ route('obras.show', $obra) }}" class="text-dark">
                            {{ $obra->nome }}
                        </a>
                        @if($obra->codigo)
                            <small class="text-muted ml-1">({{ $obra->codigo }})</small>
                        @endif
                    </h6>
                    @if($obra->ultima_medicao)
                        <small class="text-muted">
                            {{ __('Última medição:') }}
                            {{ $obra->ultima_medicao->data_medicao->translatedFormat('d/m/Y') }}
                            — {{ number_format($obra->ultima_medicao->percentual_acumulado, 1) }}% {{ __('acumulado') }}
                        </small>
                    @else
                        <small class="text-muted">{{ __('Sem medições aprovadas') }}</small>
                    @endif
                </div>
                <div class="text-right">
                    <span class="font-weight-bold" style="font-size:1.2rem; color:var(--ti-gold)">
                        {{ number_format($obra->avanco_geral, 1) }}%
                    </span>
                    @if($obra->medicoes_pendentes > 0)
                        <br>
                        <span class="badge badge-warning" style="font-size:.65rem">
                            {{ $obra->medicoes_pendentes }} {{ __('pendente(s)') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Barra de progresso geral --}}
            <div class="progress mb-3" style="height:10px; border-radius:5px">
                <div class="progress-bar progress-bar-gold"
                     style="width:{{ $obra->avanco_geral }}%; border-radius:5px"></div>
            </div>

            {{-- Fases --}}
            @if($obra->fases->isNotEmpty())
                <div class="row">
                    @foreach($obra->fases as $fase)
                        @php
                            $pct = (float) ($fase->percentual_realizado ?? 0);
                            $badgeClass = match($fase->status) {
                                'concluida'  => 'badge-success',
                                'em_andamento' => 'badge-primary',
                                'pausada'    => 'badge-warning',
                                default      => 'badge-secondary',
                            };
                        @endphp
                        <div class="col-md-4 col-sm-6 mb-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="font-weight-bold text-truncate" style="max-width:120px"
                                      title="{{ $fase->nome_personalizado }}">
                                    {{ $fase->nome_personalizado }}
                                </span>
                                <span class="text-muted">{{ number_format($pct, 0) }}%</span>
                            </div>
                            <div class="progress" style="height:6px; border-radius:3px">
                                <div class="progress-bar progress-bar-gold"
                                     style="width:{{ $pct }}%; border-radius:3px"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <small class="text-muted">{{ __('Sem fases cadastradas.') }}</small>
            @endif
        </div>
        <div class="card-footer bg-white py-2 d-flex gap-2">
            <a href="{{ route('producao.medicao', ['obra_id' => $obra->id]) }}"
               class="btn btn-xs btn-outline-success">
                <i class="fas fa-plus mr-1"></i>{{ __('Lançar Medição') }}
            </a>
            <a href="{{ route('obras.show', $obra) }}"
               class="btn btn-xs btn-outline-secondary">
                <i class="fas fa-external-link-alt mr-1"></i>{{ __('Abrir Obra') }}
            </a>
        </div>
    </div>
@empty
    <div class="card obra-card">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-hard-hat fa-2x mb-2 d-block" style="opacity:.3"></i>
            {{ __('Nenhuma obra encontrada.') }}
        </div>
    </div>
@endforelse

@stop
