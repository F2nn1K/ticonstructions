@extends('adminlte::page')

@section('title', __('app.diary.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-book-open mr-2" style="color:var(--ti-gold)"></i>
                {{ __('app.diary.title') }}
            </h1>
            <small class="text-muted">{{ __('app.diary.subtitle_index') }}</small>
        </div>
        <a href="{{ route('diario.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> {{ __('app.diary.btn_new_record') }}
        </a>
    </div>
@stop

@section('content')
<style>
.kpi-mini { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); }
.kpi-mini .k-label { font-size:.72rem; text-transform:uppercase; font-weight:700; letter-spacing:.05em; opacity:.7; }
.kpi-mini .k-val   { font-size:1.9rem; font-weight:800; line-height:1.1; }
.diario-card { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); transition:all .2s; overflow:hidden; }
.diario-card:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.11); }
.diario-card .strip { height:4px; background: var(--ti-gold-gradient, linear-gradient(90deg,#A8873A,#E2C87A)); }
.diario-card.tem-ocorrencia .strip { background: linear-gradient(90deg,#C94040,#E26060); }
.diario-card.semanal .strip { background: linear-gradient(90deg,#2C7BE5,#6EC6FF); }
.clima-badge { font-size:1.1rem; background:rgba(0,0,0,.04); padding:3px 8px; border-radius:20px; }
.filter-bar { background:#fff; border-radius:12px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,.06); margin-bottom:20px; }
.tag-ocorrencia { background:#f8d7da; color:#721c24; font-size:.68rem; padding:2px 8px; border-radius:20px; font-weight:700; }
.tag-foto { background:#d4edda; color:#155724; font-size:.68rem; padding:2px 8px; border-radius:20px; font-weight:700; }
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
                <div class="k-label text-muted">{{ __('app.diary.kpi_total') }}</div>
                <div class="k-val text-dark">{{ $totais['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:var(--ti-gold,#C9A84C)">{{ __('app.diary.kpi_today') }}</div>
                <div class="k-val" style="color:var(--ti-gold,#C9A84C)">{{ $totais['hoje'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:#1A9E6E">{{ __('app.diary.kpi_week') }}</div>
                <div class="k-val" style="color:#1A9E6E">{{ $totais['semana'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:#C94040">{{ __('app.diary.kpi_occurrences') }}</div>
                <div class="k-val" style="color:#C94040">{{ $totais['ocorrencias'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('diario.index') }}" class="row align-items-end">
        <div class="col-sm-4 col-md-3 mb-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('app.diary.work') }}</label>
            <select name="obra_id" class="form-control form-control-sm">
                <option value="">{{ __('app.diary.filter_all_works') }}</option>
                @foreach($obras as $ob)
                    <option value="{{ $ob->id }}" {{ request('obra_id')==$ob->id?'selected':'' }}>
                        {{ $ob->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4 col-md-2 mb-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('app.diary.type') }}</label>
            <select name="tipo" class="form-control form-control-sm">
                <option value="">{{ __('app.diary.filter_all') }}</option>
                <option value="diario"  {{ request('tipo')=='diario'  ?'selected':'' }}>{{ __('app.diary.type_daily') }}</option>
                <option value="semanal" {{ request('tipo')=='semanal' ?'selected':'' }}>{{ __('app.diary.type_weekly') }}</option>
            </select>
        </div>
        <div class="col-sm-4 col-md-2 mb-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('app.common.date') }} ({{ __('app.diary.start') }})</label>
            <input type="date" name="data_de" value="{{ request('data_de') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-4 col-md-2 mb-2">
            <label class="small font-weight-bold text-muted mb-1">{{ __('app.diary.end') }}</label>
            <input type="date" name="data_ate" value="{{ request('data_ate') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-4 col-md-2 mb-2">
            <div class="form-check mt-4">
                <input type="checkbox" name="ocorrencias" value="1" id="chkOcorr" class="form-check-input"
                       {{ request('ocorrencias')=='1'?'checked':'' }}>
                <label class="form-check-label small font-weight-bold" for="chkOcorr">
                    {{ __('app.diary.filter_occurrences_only') }}
                </label>
            </div>
        </div>
        <div class="col-sm-4 col-md-1 mb-2">
            <button type="submit" class="btn btn-primary btn-sm btn-block">
                <i class="fas fa-search"></i>
            </button>
            @if(request()->hasAny(['obra_id','tipo','data_de','data_ate','ocorrencias']))
                <a href="{{ route('diario.index') }}" class="btn btn-sm btn-outline-secondary btn-block mt-1">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Cards --}}
@if($registros->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-book-open fa-3x mb-3" style="opacity:.25"></i>
            <p class="mb-2">{{ __('app.diary.no_records') }}</p>
            <a href="{{ route('diario.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> {{ __('app.diary.create_first') }}
            </a>
        </div>
    </div>
@else
    <div class="row">
        @foreach($registros as $reg)
            @php $temOcorr = $reg->temOcorrencias(); @endphp
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card diario-card {{ $temOcorr ? 'tem-ocorrencia' : '' }} {{ $reg->tipo === 'semanal' ? 'semanal' : '' }}">
                    <div class="strip"></div>
                    <div class="card-body pt-3 pb-2">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="font-weight-bold" style="font-size:.95rem">
                                    @if($reg->numero)<span class="text-muted" style="font-size:.8rem">#{{ $reg->numero }} — </span>@endif
                                    {{ $reg->titulo_formatado }}
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $reg->data_registro->format('d/m/Y') }}
                                    @if($reg->data_registro->isToday())
                                        <span class="badge badge-warning ml-1" style="font-size:.6rem">{{ __('app.diary.kpi_today') }}</span>
                                    @endif
                                </small>
                            </div>
                            <span class="badge badge-{{ $reg->status_badge }}" style="font-size:.65rem">
                                {{ $reg->status_label }}
                            </span>
                        </div>

                        <div class="mb-2 small">
                            <i class="fas fa-hard-hat mr-1 text-muted"></i>
                            <a href="{{ route('obras.show', $reg->obra) }}" class="text-dark font-weight-bold">
                                {{ $reg->obra->nome }}
                            </a>
                            @if($reg->fase)
                                <span class="text-muted"> · {{ $reg->fase->nome }}</span>
                            @endif
                        </div>

                        @if($reg->local_area)
                            <div class="small text-muted mb-2">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $reg->local_area }}
                            </div>
                        @endif

                        @php
                            $resumo = $reg->atividades_executadas
                                ?: $reg->atividades->pluck('descricao')->take(2)->implode(' · ');
                        @endphp
                        @if($resumo)
                        <div class="small mb-3" style="color:#444; line-height:1.5">
                            {{ Str::limit($resumo, 120) }}
                        </div>
                        @endif

                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @if($temOcorr)
                                <span class="tag-ocorrencia">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ __('app.diary.section_occurrences') }}
                                </span>
                            @endif
                            @if($reg->totalFotos() > 0)
                                <span class="tag-foto">
                                    <i class="fas fa-camera mr-1"></i>{{ $reg->totalFotos() }} {{ __('app.diary.section_photos') }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 small text-muted"
                             style="border-top:1px solid #f0f0f0">
                            <span>
                                @if($reg->responsavel)
                                    <i class="fas fa-user-tie mr-1"></i>{{ Str::limit($reg->responsavel->name, 18) }}
                                @endif
                            </span>
                            <div>
                                <a href="{{ route('diario.show', $reg) }}" class="btn btn-xs btn-outline-primary mr-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('diario.edit', $reg) }}" class="btn btn-xs btn-outline-secondary mr-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('diario.destroy', $reg) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('{{ __('app.diary.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($registros->hasPages())
        <div class="d-flex justify-content-center mt-2">
            {{ $registros->links() }}
        </div>
    @endif
@endif

@stop
@section('css')
<style>.gap-1{gap:.25rem}</style>
@stop
