@extends('adminlte::page')
@section('title', __('Histórico do Fornecedor'))
@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('fornecedores.index') }}" class="btn btn-sm btn-outline-secondary mr-3"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="mb-0"><i class="fas fa-truck mr-2" style="color:var(--ti-gold)"></i>{{ $fornecedor->nome_exibicao }}</h1>
        @if($fornecedor->razao_social !== $fornecedor->nome_exibicao)<small class="text-muted">{{ $fornecedor->razao_social }}</small>@endif
    </div>
</div>
@stop
@section('content')
<div class="row mb-4">
    <div class="col-md-3"><div class="card text-center" style="border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07)"><div class="card-body py-3">
        <div class="small text-muted font-weight-bold text-uppercase">{{ __('Total Compras') }}</div>
        <div style="font-size:1.7rem;font-weight:800;color:#1A9E6E">R$ {{ number_format($totalCompras,2,',','.') }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card text-center" style="border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07)"><div class="card-body py-3">
        <div class="small text-muted font-weight-bold text-uppercase">{{ __('Lançamentos') }}</div>
        <div style="font-size:1.7rem;font-weight:800">{{ $lancamentos->total() }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card text-center" style="border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07)"><div class="card-body py-3">
        <div class="small text-muted font-weight-bold text-uppercase">{{ __('Categorias') }}</div>
        <div style="font-size:1.7rem;font-weight:800">{{ $porCategoria->count() }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card text-center" style="border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07)"><div class="card-body py-3">
        <div class="small text-muted font-weight-bold text-uppercase">{{ __('Situação') }}</div>
        <div class="mt-1">@if($fornecedor->ativo)<span class="badge badge-success badge-lg" style="font-size:.95rem">{{ __('Ativo') }}</span>@else<span class="badge badge-secondary" style="font-size:.95rem">{{ __('Inativo') }}</span>@endif</div>
    </div></div></div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card" style="border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07)">
            <div class="card-header" style="background:#f7f5f0"><h6 class="mb-0 font-weight-bold">{{ __('Histórico de Compras') }}</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.82rem">
                        <thead style="background:#fafaf7"><tr>
                            <th class="pl-3">{{ __('Data') }}</th>
                            <th>{{ __('Obra') }}</th>
                            <th>{{ __('Produto / Descrição') }}</th>
                            <th>{{ __('Categ.') }}</th>
                            <th class="text-right">{{ __('Qtd') }}</th>
                            <th class="text-right">{{ __('Unit. R$') }}</th>
                            <th class="text-right">{{ __('Total R$') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse($lancamentos as $l)
                            <tr>
                                <td class="pl-3 text-muted">{{ $l->data_lancamento->format('d/m/Y') }}</td>
                                <td>{{ $l->obra->nome ?? '—' }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $l->descricao }}</div>
                                    @if($l->produto_codigo)<small class="text-muted">{{ $l->produto_codigo }}</small>@endif
                                </td>
                                <td class="text-muted">{{ $l->categoria->nome ?? '—' }}</td>
                                <td class="text-right">{{ number_format($l->quantidade,2,',','.') }} {{ $l->unidade }}</td>
                                <td class="text-right">R$ {{ number_format($l->custo_unitario_real,2,',','.') }}</td>
                                <td class="text-right font-weight-bold" style="color:#1A9E6E">R$ {{ number_format($l->custo_total_real,2,',','.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('Nenhum lançamento.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($lancamentos->hasPages())<div class="mt-3">{{ $lancamentos->links() }}</div>@endif
    </div>

    <div class="col-md-4">
        <div class="card" style="border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07)">
            <div class="card-header" style="background:#f7f5f0"><h6 class="mb-0 font-weight-bold">{{ __('Gastos por Categoria') }}</h6></div>
            <div class="card-body p-0">
                @foreach($porCategoria->sortByDesc('total') as $pc)
                <div class="d-flex justify-content-between align-items-center px-3 py-2" style="border-bottom:1px solid #f5f5f5">
                    <div>
                        <div style="font-size:.8rem;font-weight:600">{{ $pc->categoria->nome ?? '—' }}</div>
                        <small class="text-muted">{{ $pc->qtd }} {{ __('lançamentos') }}</small>
                    </div>
                    <div class="text-right">
                        <div style="font-size:.85rem;font-weight:700;color:#1A9E6E">R$ {{ number_format($pc->total,2,',','.') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Info do fornecedor --}}
        <div class="card mt-3" style="border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07)">
            <div class="card-header" style="background:#f7f5f0"><h6 class="mb-0 font-weight-bold">{{ __('Dados do Fornecedor') }}</h6></div>
            <div class="card-body" style="font-size:.82rem">
                @if($fornecedor->cnpj)<div class="mb-1"><i class="fas fa-id-card mr-2 text-muted"></i>{{ $fornecedor->cnpj }}</div>@endif
                @if($fornecedor->email)<div class="mb-1"><i class="fas fa-envelope mr-2 text-muted"></i>{{ $fornecedor->email }}</div>@endif
                @if($fornecedor->telefone)<div class="mb-1"><i class="fas fa-phone mr-2 text-muted"></i>{{ $fornecedor->telefone }}</div>@endif
                @if($fornecedor->cidade)<div class="mb-1"><i class="fas fa-map-marker-alt mr-2 text-muted"></i>{{ $fornecedor->cidade }}/{{ $fornecedor->uf }}</div>@endif
                @if($fornecedor->observacoes)<div class="mt-2 text-muted"><i class="fas fa-comment mr-2"></i>{{ $fornecedor->observacoes }}</div>@endif
                <div class="mt-3"><a href="{{ route('fornecedores.edit',$fornecedor) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-pencil-alt mr-1"></i>{{ __('Editar') }}</a></div>
            </div>
        </div>
    </div>
</div>
@stop
