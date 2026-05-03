@extends('adminlte::page')
@section('title', __('app.menu.occurrences'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fas fa-exclamation-triangle mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.occurrences') }}</h1>
            <small class="text-muted">{{ __('Registros de imprevistos e impactos nas obras') }}</small>
        </div>
        <a href="{{ route('ocorrencias-obra.criar') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus mr-1"></i> {{ __('app.menu.register_occurrence') }}
        </a>
    </div>
@stop
@section('content')
<style>
.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}
.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}
.kpi-mini .k-val{font-size:1.8rem;font-weight:800;line-height:1.2}
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif

<div class="row mb-4">
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini"><div class="card-body py-3 text-center">
            <div class="k-label text-muted">{{ __('Total Geral') }}</div>
            <div class="k-val text-dark">{{ $totalGeral }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini"><div class="card-body py-3 text-center">
            <div class="k-label" style="color:var(--ti-gold)">{{ __('No Mês Atual') }}</div>
            <div class="k-val" style="color:var(--ti-gold)">{{ $totalMes }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini"><div class="card-body py-3 text-center">
            <div class="k-label text-danger">{{ __('Dias de Impacto (Total)') }}</div>
            <div class="k-val text-danger">{{ $totalImpacto }}</div>
        </div></div>
    </div>
</div>

<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('ocorrencias-obra.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Todas as obras') }}</option>
                    @foreach($obras as $ob)
                        <option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Tipo') }}</label>
                <select name="tipo" class="form-control form-control-sm">
                    <option value="">{{ __('Todos') }}</option>
                    @php $tipos = ['chuva'=>__('Chuva'),'falta_material'=>__('Falta de Material'),'falta_mao_de_obra'=>__('Falta de Mão de Obra'),'erro_projeto'=>__('Erro de Projeto'),'problema_equipamento'=>__('Problema Equipamento'),'acidente'=>__('Acidente'),'outro'=>__('Outro')]; @endphp
                    @foreach($tipos as $val => $label)
                        <option value="{{ $val }}" {{ $tipo==$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('De') }}</label>
                <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="form-control form-control-sm">
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Até') }}</label>
                <input type="date" name="data_fim" value="{{ $dataFim }}" class="form-control form-control-sm">
            </div>
            <div class="col-sm-8 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button>
                <a href="{{ route('ocorrencias-obra.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Data') }}</th>
                        <th>{{ __('Obra') }}</th>
                        <th>{{ __('Fase') }}</th>
                        <th>{{ __('Tipo') }}</th>
                        <th>{{ __('Título') }}</th>
                        <th>{{ __('Impacto (dias)') }}</th>
                        <th>{{ __('Registrado por') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $tipoLabels = ['chuva'=>__('Chuva'),'falta_material'=>__('Falta de Material'),'falta_mao_de_obra'=>__('Falta de M.O.'),'erro_projeto'=>__('Erro de Projeto'),'problema_equipamento'=>__('Prob. Equipamento'),'acidente'=>__('Acidente'),'outro'=>__('Outro')];
                    @endphp
                    @forelse($ocorrencias as $oc)
                        <tr>
                            <td class="small font-weight-bold text-nowrap">{{ \Carbon\Carbon::parse($oc->data_ocorrencia)->translatedFormat('d/m/Y') }}</td>
                            <td class="small"><span class="font-weight-bold">{{ $oc->obra_nome }}</span>@if($oc->obra_codigo)<br><small class="text-muted">{{ $oc->obra_codigo }}</small>@endif</td>
                            <td class="small text-muted">{{ $oc->fase_nome }}</td>
                            <td><span class="badge badge-secondary" style="font-size:.65rem">{{ $tipoLabels[$oc->tipo] ?? $oc->tipo }}</span></td>
                            <td>
                                <div class="font-weight-bold small">{{ $oc->titulo }}</div>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($oc->descricao, 60) }}</small>
                            </td>
                            <td class="text-center">
                                @if($oc->impacto_dias > 0)
                                    <span class="badge badge-danger">{{ $oc->impacto_dias }}d</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $oc->registrador_nome ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block" style="opacity:.3"></i>
                                {{ __('Nenhuma ocorrência registrada.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ocorrencias->hasPages())
        <div class="card-footer">{{ $ocorrencias->links() }}</div>
    @endif
</div>
@stop
