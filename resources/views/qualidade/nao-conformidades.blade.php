@extends('adminlte::page')
@section('title', __('app.menu.non_conformities'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fas fa-times-circle mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.non_conformities') }}</h1>
            <small class="text-muted">{{ __('Registro e tratamento de não conformidades') }}</small>
        </div>
        <a href="{{ route('qualidade.nao-conformidade-criar') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i>{{ __('Registrar Não Conformidade') }}</a>
    </div>
@stop
@section('content')
<style>.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}.kpi-mini .k-val{font-size:1.8rem;font-weight:800;line-height:1.2}</style>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-muted">{{ __('Total') }}</div><div class="k-val text-dark">{{ $totalGeral }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-danger">{{ __('Abertas') }}</div><div class="k-val text-danger">{{ $totalAberta }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-danger">{{ __('Críticas') }}</div><div class="k-val text-danger">{{ $totalCritica }}</div></div></div></div>
    <div class="col-6 col-md-3 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label" style="color:#1A9E6E">{{ __('Resolvidas') }}</div><div class="k-val" style="color:#1A9E6E">{{ $totalResolvida }}</div></div></div></div>
</div>

<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('qualidade.nao-conformidades') }}" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3"><label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm"><option value="">{{ __('Todas as obras') }}</option>@foreach($obras as $ob)<option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach</select></div>
            <div class="col-sm-3 col-md-2"><label class="small font-weight-bold text-muted mb-1">{{ __('Status') }}</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">{{ __('Todos') }}</option>
                    <option value="aberta"      {{ $status=='aberta'      ?'selected':'' }}>{{ __('Aberta') }}</option>
                    <option value="em_correcao" {{ $status=='em_correcao' ?'selected':'' }}>{{ __('Em Correção') }}</option>
                    <option value="resolvida"   {{ $status=='resolvida'   ?'selected':'' }}>{{ __('Resolvida') }}</option>
                    <option value="aceita"      {{ $status=='aceita'      ?'selected':'' }}>{{ __('Aceita') }}</option>
                </select></div>
            <div class="col-sm-3 col-md-2"><label class="small font-weight-bold text-muted mb-1">{{ __('Gravidade') }}</label>
                <select name="gravidade" class="form-control form-control-sm">
                    <option value="">{{ __('Todas') }}</option>
                    <option value="leve"     {{ $gravidade=='leve'    ?'selected':'' }}>{{ __('Leve') }}</option>
                    <option value="moderada" {{ $gravidade=='moderada'?'selected':'' }}>{{ __('Moderada') }}</option>
                    <option value="grave"    {{ $gravidade=='grave'   ?'selected':'' }}>{{ __('Grave') }}</option>
                    <option value="critica"  {{ $gravidade=='critica' ?'selected':'' }}>{{ __('Crítica') }}</option>
                </select></div>
            <div class="col-sm-4 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button>
                <a href="{{ route('qualidade.nao-conformidades') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
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
                        <th>{{ __('Título') }}</th>
                        <th>{{ __('Obra') }}</th>
                        <th>{{ __('Fase') }}</th>
                        <th>{{ __('Gravidade') }}</th>
                        <th>{{ __('Prazo') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $gravLabels = ['leve'=>__('Leve'),'moderada'=>__('Moderada'),'grave'=>__('Grave'),'critica'=>__('Crítica')];
                        $gravColors = ['leve'=>'success','moderada'=>'warning','grave'=>'danger','critica'=>'dark'];
                        $stLabels = ['aberta'=>__('Aberta'),'em_correcao'=>__('Em Correção'),'resolvida'=>__('Resolvida'),'aceita'=>__('Aceita')];
                        $stColors = ['aberta'=>'danger','em_correcao'=>'warning','resolvida'=>'success','aceita'=>'secondary'];
                    @endphp
                    @forelse($ncs as $nc)
                        <tr>
                            <td><div class="small font-weight-bold">{{ $nc->titulo }}</div><small class="text-muted">{{ \Illuminate\Support\Str::limit($nc->descricao,60) }}</small></td>
                            <td class="small text-muted">{{ $nc->obra_nome }}</td>
                            <td class="small text-muted">{{ $nc->fase_nome ?? '—' }}</td>
                            <td><span class="badge badge-{{ $gravColors[$nc->gravidade] ?? 'secondary' }}" style="font-size:.65rem">{{ $gravLabels[$nc->gravidade] ?? $nc->gravidade }}</span></td>
                            <td class="small text-nowrap">{{ $nc->prazo_correcao ? \Carbon\Carbon::parse($nc->prazo_correcao)->translatedFormat('d/m/Y') : '—' }}</td>
                            <td><span class="badge badge-{{ $stColors[$nc->status] ?? 'secondary' }}" style="font-size:.65rem">{{ $stLabels[$nc->status] ?? $nc->status }}</span></td>
                            <td>
                                @if(!in_array($nc->status, ['resolvida','aceita']))
                                    <form method="POST" action="{{ route('qualidade.nao-conformidade-update', $nc->id) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-control-sm d-inline" style="width:auto" onchange="this.form.submit()">
                                            @foreach($stLabels as $v=>$l)<option value="{{ $v }}" {{ $nc->status==$v?'selected':'' }}>{{ $l }}</option>@endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-times-circle fa-2x mb-2 d-block" style="opacity:.3"></i>{{ __('Nenhuma não conformidade registrada.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ncs->hasPages())<div class="card-footer">{{ $ncs->links() }}</div>@endif
</div>
@stop
