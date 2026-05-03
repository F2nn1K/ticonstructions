@extends('adminlte::page')
@section('title', __('app.menu.schedule_report'))
@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div><h1 class="mb-0"><i class="fas fa-calendar-check mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.schedule_report') }}</h1>
    <small class="text-muted">{{ __('Status do cronograma por fase') }}</small></div>
</div>
@stop
@section('content')
<style>.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}.kpi-mini .k-val{font-size:1.8rem;font-weight:800;line-height:1.2}</style>
<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-sm-6 col-md-4">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Selecionar Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Selecione a obra...') }}</option>
                    @foreach($obras as $ob)<option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3 mt-2 mt-sm-0 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Visualizar') }}</button>
                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>
@if($obra)
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label" style="color:#1A9E6E">{{ __('Concluídas') }}</div><div class="k-val" style="color:#1A9E6E">{{ $concluidas }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-primary">{{ __('Em Andamento') }}</div><div class="k-val text-primary">{{ $emAndamento }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-secondary">{{ __('Não Iniciadas') }}</div><div class="k-val text-secondary">{{ $naoIniciadas }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-danger">{{ __('Atrasadas') }}</div><div class="k-val text-danger">{{ $atrasadas }}</div></div></div></div>
</div>
<div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr><th>#</th><th>{{ __('Fase') }}</th><th>{{ __('Início Previsto') }}</th><th>{{ __('Fim Previsto') }}</th><th>{{ __('Progresso') }}</th><th>{{ __('Status') }}</th></tr>
                </thead>
                <tbody>
                @forelse($fases as $fase)
                    @php
                        $perc = $fase->percentual_realizado ?? 0;
                        $atrasada = $fase->data_fim_previsto && now()->gt($fase->data_fim_previsto) && $perc < 100;
                        if($perc >= 100) { $stLabel = __('Concluída'); $stColor = 'success'; }
                        elseif($atrasada) { $stLabel = __('Atrasada'); $stColor = 'danger'; }
                        elseif($perc > 0) { $stLabel = __('Em Andamento'); $stColor = 'info'; }
                        else { $stLabel = __('Não Iniciada'); $stColor = 'secondary'; }
                    @endphp
                    <tr>
                        <td class="text-muted small">{{ $fase->ordem }}</td>
                        <td class="font-weight-bold small">{{ $fase->nome_personalizado }}</td>
                        <td class="small text-nowrap">{{ $fase->data_inicio_previsto ? \Carbon\Carbon::parse($fase->data_inicio_previsto)->format('d/m/Y') : '—' }}</td>
                        <td class="small text-nowrap {{ $atrasada ? 'text-danger font-weight-bold' : '' }}">{{ $fase->data_fim_previsto ? \Carbon\Carbon::parse($fase->data_fim_previsto)->format('d/m/Y') : '—' }}</td>
                        <td style="min-width:120px">
                            <div class="progress" style="height:14px">
                                <div class="progress-bar bg-{{ $stColor }}" style="width:{{ min($perc,100) }}%"></div>
                            </div>
                            <small class="text-muted">{{ round($perc,1) }}%</small>
                        </td>
                        <td><span class="badge badge-{{ $stColor }}" style="font-size:.65rem">{{ $stLabel }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-calendar fa-2x mb-2 d-block" style="opacity:.3"></i>{{ __('Nenhuma fase cadastrada.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
    <div class="card-body text-center py-5 text-muted"><i class="fas fa-calendar-check fa-3x mb-3" style="opacity:.3"></i><p>{{ __('Selecione uma obra para visualizar o relatório.') }}</p></div>
</div>
@endif
@stop
