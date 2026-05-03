@extends('adminlte::page')
@section('title', __('app.menu.inspections'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fas fa-search mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.inspections') }}</h1>
            <small class="text-muted">{{ __('Registro e acompanhamento de inspeções de qualidade') }}</small>
        </div>
        <a href="{{ route('qualidade.inspecao-criar') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i>{{ __('Nova Inspeção') }}</a>
    </div>
@stop
@section('content')
<style>.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}.kpi-mini .k-val{font-size:1.8rem;font-weight:800;line-height:1.2}</style>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

<div class="row mb-4">
    @php $statusLabels = ['pendente'=>__('Pendente'),'em_andamento'=>__('Em Andamento'),'concluida'=>__('Concluída'),'reprovada'=>__('Reprovada')];
         $statusColors = ['pendente'=>'warning','em_andamento'=>'info','concluida'=>'success','reprovada'=>'danger']; @endphp
    @foreach($statusLabels as $st => $label)
        <div class="col-6 col-md-3 mb-3">
            <div class="card kpi-mini"><div class="card-body py-3 text-center">
                <div class="k-label text-{{ $statusColors[$st] }}">{{ $label }}</div>
                <div class="k-val text-{{ $statusColors[$st] }}">{{ $totais[$st] ?? 0 }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('qualidade.inspecoes') }}" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Todas as obras') }}</option>
                    @foreach($obras as $ob)<option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Status') }}</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">{{ __('Todos') }}</option>
                    @foreach($statusLabels as $v=>$l)<option value="{{ $v }}" {{ $status==$v?'selected':'' }}>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button>
                <a href="{{ route('qualidade.inspecoes') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
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
                        <th>{{ __('Título') }}</th>
                        <th>{{ __('Obra') }}</th>
                        <th>{{ __('Fase') }}</th>
                        <th>{{ __('Checklist') }}</th>
                        <th>{{ __('Responsável') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inspecoes as $ins)
                        <tr>
                            <td class="small font-weight-bold text-nowrap">{{ \Carbon\Carbon::parse($ins->data_inspecao)->translatedFormat('d/m/Y') }}</td>
                            <td><div class="small font-weight-bold">{{ $ins->titulo }}</div></td>
                            <td class="small text-muted">{{ $ins->obra_nome }}</td>
                            <td class="small text-muted">{{ $ins->fase_nome ?? '—' }}</td>
                            <td class="small text-muted">{{ $ins->checklist_titulo ?? '—' }}</td>
                            <td class="small">{{ $ins->responsavel ?? '—' }}</td>
                            <td><span class="badge badge-{{ $statusColors[$ins->status] ?? 'secondary' }}" style="font-size:.65rem">{{ $statusLabels[$ins->status] ?? $ins->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-search fa-2x mb-2 d-block" style="opacity:.3"></i>{{ __('Nenhuma inspeção registrada.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($inspecoes->hasPages())<div class="card-footer">{{ $inspecoes->links() }}</div>@endif
</div>
@stop
