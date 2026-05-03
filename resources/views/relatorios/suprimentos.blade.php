@extends('adminlte::page')
@section('title', __('app.menu.supplies_report'))
@section('content_header')
<div><h1 class="mb-0"><i class="fas fa-boxes mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.supplies_report') }}</h1>
<small class="text-muted">{{ __('Gastos com materiais, equipamentos e suprimentos') }}</small></div>
@stop
@section('content')
<style>.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}.kpi-mini .k-val{font-size:1.4rem;font-weight:800;line-height:1.2}</style>
<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-sm-6 col-md-4"><label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Todas as obras') }}</option>
                    @foreach($obras as $ob)<option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach
                </select></div>
            <div class="col-sm-4 col-md-3 mt-2 mt-sm-0 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button>
                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="row mb-4">
    <div class="col-6 col-md-4 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-danger">{{ __('Total') }}</div><div class="k-val text-danger">R$ {{ number_format($totalValor,2,',','.') }}</div></div></div></div>
    <div class="col-6 col-md-4 mb-3"><div class="card kpi-mini"><div class="card-body py-3 text-center"><div class="k-label text-muted">{{ __('Lançamentos') }}</div><div class="k-val text-dark">{{ $totalItens }}</div></div></div></div>
</div>
<div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light"><tr><th>{{ __('Data') }}</th><th>{{ __('Descrição') }}</th><th>{{ __('Categoria') }}</th><th>{{ __('Obra') }}</th><th>{{ __('Valor') }}</th></tr></thead>
            <tbody>
            @forelse($itens as $item)
                <tr>
                    <td class="small text-nowrap">{{ \Carbon\Carbon::parse($item->data_gasto)->format('d/m/Y') }}</td>
                    <td class="small">{{ $item->descricao }}</td>
                    <td><span class="badge badge-secondary" style="font-size:.65rem">{{ $item->categoria }}</span></td>
                    <td class="small text-muted">{{ $item->obra_nome }}</td>
                    <td class="small font-weight-bold text-danger">R$ {{ number_format($item->valor,2,',','.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5"><i class="fas fa-boxes fa-2x mb-2 d-block" style="opacity:.3"></i>{{ __('Nenhum lançamento de suprimentos encontrado.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
</div>
@stop
