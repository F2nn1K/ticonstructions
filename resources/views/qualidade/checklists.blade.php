@extends('adminlte::page')
@section('title', __('app.menu.checklists'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fas fa-list-check mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.checklists') }}</h1>
            <small class="text-muted">{{ __('Modelos de verificação de qualidade') }}</small>
        </div>
        <a href="{{ route('qualidade.checklist-criar') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus mr-1"></i> {{ __('Novo Checklist') }}
        </a>
    </div>
@stop
@section('content')
<style>.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}.kpi-mini .k-val{font-size:1.8rem;font-weight:800;line-height:1.2}</style>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

<div class="row mb-4">
    <div class="col-6 col-md-4 mb-3">
        <div class="card kpi-mini"><div class="card-body py-3 text-center">
            <div class="k-label text-muted">{{ __('Total de Checklists') }}</div>
            <div class="k-val text-dark">{{ $total }}</div>
        </div></div>
    </div>
</div>

<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('qualidade.checklists') }}" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-4">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Todas as obras') }}</option>
                    @foreach($obras as $ob)<option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Categoria') }}</label>
                <select name="categoria" class="form-control form-control-sm">
                    <option value="">{{ __('Todas') }}</option>
                    @php $cats = ['estrutura'=>__('Estrutura'),'acabamento'=>__('Acabamento'),'instalacao_eletrica'=>__('Inst. Elétrica'),'instalacao_hidraulica'=>__('Inst. Hidráulica'),'seguranca'=>__('Segurança'),'outro'=>__('Outro')]; @endphp
                    @foreach($cats as $v=>$l)<option value="{{ $v }}" {{ $categoria==$v?'selected':'' }}>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button>
                <a href="{{ route('qualidade.checklists') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
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
                        <th>{{ __('Categoria') }}</th>
                        <th>{{ __('Obra') }}</th>
                        <th>{{ __('Itens') }}</th>
                        <th>{{ __('Criado por') }}</th>
                        <th>{{ __('Data') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($checklists as $cl)
                        @php $itens = json_decode($cl->itens ?? '[]', true); @endphp
                        <tr>
                            <td><div class="font-weight-bold small">{{ $cl->titulo }}</div>@if($cl->descricao)<small class="text-muted">{{ \Illuminate\Support\Str::limit($cl->descricao,60) }}</small>@endif</td>
                            <td><span class="badge badge-secondary" style="font-size:.65rem">{{ $cats[$cl->categoria] ?? $cl->categoria }}</span></td>
                            <td class="small text-muted">{{ $cl->obra_nome ?? __('Geral') }}</td>
                            <td class="text-center"><span class="badge badge-info">{{ count($itens) }}</span></td>
                            <td class="small text-muted">{{ $cl->autor ?? '—' }}</td>
                            <td class="small text-muted text-nowrap">{{ \Carbon\Carbon::parse($cl->created_at)->translatedFormat('d/m/Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('qualidade.checklist-destroy', $cl->id) }}" class="d-inline" onsubmit="return confirm('{{ __('Excluir este checklist?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-list-check fa-2x mb-2 d-block" style="opacity:.3"></i>{{ __('Nenhum checklist cadastrado.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($checklists->hasPages())<div class="card-footer">{{ $checklists->links() }}</div>@endif
</div>
@stop
