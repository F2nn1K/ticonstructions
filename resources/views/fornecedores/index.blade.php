@extends('adminlte::page')
@section('title', __('Fornecedores'))
@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="fas fa-truck mr-2" style="color:var(--ti-gold)"></i>{{ __('Fornecedores') }}</h1>
        <small class="text-muted">{{ __('Gestão de fornecedores cadastrados') }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('fornecedores.relatorio-comparacao') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-chart-bar mr-1"></i>{{ __('Comparar Preços') }}
        </a>
        <a href="{{ route('fornecedores.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i>{{ __('Novo Fornecedor') }}
        </a>
    </div>
</div>
@stop
@section('content')
<style>
.forn-card { border-radius:10px;border:none;box-shadow:0 1px 6px rgba(0,0,0,.08);transition:.2s; }
.forn-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.13); }
</style>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif

{{-- Filtros --}}
<div class="card mb-3" style="border-radius:10px;border:none;box-shadow:0 1px 6px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
            <div class="mr-3">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Buscar') }}</label>
                <input type="text" name="busca" value="{{ request('busca') }}" class="form-control form-control-sm" placeholder="{{ __('Razão social, fantasia, CNPJ...') }}" style="min-width:220px">
            </div>
            <div class="mr-3">
                <label class="small font-weight-bold text-muted mb-1">{{ __('UF') }}</label>
                <select name="uf" class="form-control form-control-sm">
                    <option value="">{{ __('Todos') }}</option>
                    @foreach($ufs as $uf)<option value="{{ $uf }}" {{ request('uf')==$uf?'selected':'' }}>{{ $uf }}</option>@endforeach
                </select>
            </div>
            <div class="mb-0"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button></div>
            @if(request()->hasAny(['busca','uf']))<div class="mb-0"><a href="{{ route('fornecedores.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i>{{ __('Limpar') }}</a></div>@endif
        </form>
    </div>
</div>

@if($fornecedores->isEmpty())
<div class="card forn-card"><div class="card-body text-center py-5 text-muted">
    <i class="fas fa-truck fa-3x mb-3" style="opacity:.25"></i>
    <p class="mb-3">{{ __('Nenhum fornecedor cadastrado.') }}</p>
    <a href="{{ route('fornecedores.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>{{ __('Cadastrar Primeiro Fornecedor') }}</a>
</div></div>
@else
<div class="card forn-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.85rem">
                <thead style="background:#f7f5f0">
                    <tr>
                        <th class="pl-4">{{ __('Fornecedor') }}</th>
                        <th>{{ __('CNPJ') }}</th>
                        <th>{{ __('Contato') }}</th>
                        <th>{{ __('Cidade/UF') }}</th>
                        <th class="text-right">{{ __('Total Compras') }}</th>
                        <th class="text-center">{{ __('Lançamentos') }}</th>
                        <th class="text-center">{{ __('Situação') }}</th>
                        <th class="text-center">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fornecedores as $f)
                    <tr>
                        <td class="pl-4">
                            <div class="font-weight-bold">{{ $f->razao_social }}</div>
                            @if($f->nome_fantasia && $f->nome_fantasia !== $f->razao_social)
                            <small class="text-muted">{{ $f->nome_fantasia }}</small>
                            @endif
                        </td>
                        <td class="text-muted">{{ $f->cnpj ?: '—' }}</td>
                        <td>
                            @if($f->email)<div style="font-size:.75rem"><i class="fas fa-envelope mr-1 text-muted"></i>{{ $f->email }}</div>@endif
                            @if($f->telefone)<div style="font-size:.75rem"><i class="fas fa-phone mr-1 text-muted"></i>{{ $f->telefone }}</div>@endif
                        </td>
                        <td class="text-muted">{{ $f->cidade }}{{ $f->cidade && $f->uf ? '/' : '' }}{{ $f->uf }}</td>
                        <td class="text-right font-weight-bold" style="color:#1A9E6E">
                            R$ {{ number_format($f->lancamentos_sum_custo_total_real ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light">{{ $f->lancamentos_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($f->ativo)
                            <span class="badge badge-success">{{ __('Ativo') }}</span>
                            @else
                            <span class="badge badge-secondary">{{ __('Inativo') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('fornecedores.show', $f) }}" class="btn btn-xs btn-outline-info mr-1" title="{{ __('Histórico') }}"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('fornecedores.edit', $f) }}" class="btn btn-xs btn-outline-secondary mr-1" title="{{ __('Editar') }}"><i class="fas fa-pencil-alt"></i></a>
                            <form method="POST" action="{{ route('fornecedores.destroy', $f) }}" class="d-inline" onsubmit="return confirm('{{ __('Remover este fornecedor?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="{{ __('Remover') }}"><i class="fas fa-times"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($fornecedores->hasPages())
<div class="d-flex justify-content-center mt-3">{{ $fornecedores->links() }}</div>
@endif
@endif
@stop
