@extends('adminlte::page')
@section('title', __('Comparação de Preços por Fornecedor'))
@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="fas fa-chart-bar mr-2" style="color:var(--ti-gold)"></i>{{ __('Comparação de Preços') }}</h1>
        <small class="text-muted">{{ __('Compare preços do mesmo produto entre diferentes fornecedores') }}</small>
    </div>
    <a href="{{ route('fornecedores.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>{{ __('Fornecedores') }}</a>
</div>
@stop
@section('content')
<style>
.comp-card { border-radius:10px;border:none;box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:1.2rem;overflow:hidden; }
.comp-header { padding:10px 16px;background:#f7f5f0;border-bottom:1px solid #eee;display:flex;align-items:center;gap:10px; }
.comp-prod { font-weight:700;font-size:.87rem; }
.comp-cat { font-size:.72rem;color:#999; }
.forn-row { display:flex;align-items:center;padding:9px 16px;border-bottom:1px solid #f5f5f5; }
.forn-row:last-child { border-bottom:none; }
.forn-row.mais-barato { background:#f0fbf4; }
.forn-row.mais-caro   { background:#fff8f8; }
.badge-barato { background:#d4edda;color:#155724;font-size:.7rem;padding:2px 8px;border-radius:20px;font-weight:700 }
.badge-caro   { background:#f8d7da;color:#721c24;font-size:.7rem;padding:2px 8px;border-radius:20px;font-weight:700 }
.preco-val { font-size:1.05rem;font-weight:800; }
.preco-min { color:#1A9E6E; }
.preco-max { color:#C94040; }
.pct-diff  { font-size:.72rem;padding:2px 7px;border-radius:10px;font-weight:700; }
.pct-diff.up   { background:#f8d7da;color:#721c24; }
.pct-diff.zero { background:#d4edda;color:#155724; }
</style>

{{-- Filtros --}}
<div class="card mb-4" style="border-radius:10px;border:none;box-shadow:0 1px 6px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end g-2">
            <div class="col-md-4 form-group mb-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Buscar produto / descrição') }}</label>
                <input type="text" name="descricao" value="{{ request('descricao') }}" class="form-control form-control-sm" placeholder="{{ __('Cimento, areia, vergalhão...') }}">
            </div>
            <div class="col-md-3 form-group mb-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Categoria') }}</label>
                <select name="categoria_id" class="form-control form-control-sm" id="filtroCategoria">
                    <option value="">{{ __('Todas') }}</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ request('categoria_id')==$cat->id?'selected':'' }}>{{ $cat->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group mb-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Subcategoria') }}</label>
                <select name="subcategoria_id" class="form-control form-control-sm" id="filtroSubcat">
                    <option value="">{{ __('Todas') }}</option>
                    @foreach($categorias as $cat)
                        @foreach($cat->subcategorias as $sub)
                        <option value="{{ $sub->id }}" data-cat="{{ $cat->id }}" {{ request('subcategoria_id')==$sub->id?'selected':'' }}>{{ $sub->nome }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group mb-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button>
                @if(request()->hasAny(['descricao','categoria_id','subcategoria_id']))
                <a href="{{ route('fornecedores.relatorio-comparacao') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($agrupado->isEmpty())
<div class="card comp-card"><div class="card-body text-center py-5 text-muted">
    <i class="fas fa-chart-bar fa-3x mb-3" style="opacity:.25"></i>
    <p class="mb-2">{{ __('Nenhuma comparação disponível.') }}</p>
    <p class="small">{{ __('Para aparecer aqui, os lançamentos de custo precisam ter um fornecedor cadastrado vinculado.') }}</p>
    <a href="{{ route('gastos.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>{{ __('Lançar Custo com Fornecedor') }}</a>
</div></div>
@else

@php $totalGrupos = $agrupado->count(); $grupoComDiferenca = 0; @endphp
@foreach($agrupado as $chave => $linhas)
    @php
        $amostra  = $linhas->first();
        $precoMin = $linhas->min('preco_min');
        $precoMax = $linhas->max('preco_max');
        $diff     = $precoMin > 0 ? round((($precoMax - $precoMin) / $precoMin) * 100) : 0;
        $multiplos = $linhas->count() > 1;
        if ($diff > 0) $grupoComDiferenca++;
    @endphp
    <div class="comp-card @if($diff >= 30) border-left border-danger @elseif($diff >= 10) border-left border-warning @endif">
        <div class="comp-header">
            <div style="flex:1">
                <div class="comp-prod">
                    <i class="fas fa-box mr-2 text-muted" style="font-size:.75rem"></i>
                    {{ $amostra->descricao }}
                    @if($amostra->unidade) <span class="text-muted font-weight-normal">({{ $amostra->unidade }})</span>@endif
                </div>
                <div class="comp-cat">
                    {{ $amostra->categoria ?? '—' }}
                    @if($amostra->subcategoria) › {{ $amostra->subcategoria }}@endif
                </div>
            </div>
            @if($multiplos && $diff > 0)
            <div class="text-right">
                <div style="font-size:.72rem;color:#999;margin-bottom:2px">{{ __('Diferença') }}</div>
                <span class="pct-diff {{ $diff >= 20 ? 'up' : 'zero' }}">
                    +{{ $diff }}%
                </span>
            </div>
            @endif
            @if($multiplos)
            <div class="text-right ml-3">
                <div style="font-size:.68rem;color:#999">{{ $linhas->count() }} {{ __('fornecedores') }}</div>
            </div>
            @endif
        </div>

        @foreach($linhas->sortBy('preco_min') as $i => $linha)
            @php
                $eMaisBarato = abs($linha->preco_min - $precoMin) < 0.01;
                $eMaisCaro   = $multiplos && abs($linha->preco_min - $precoMax) < 0.01 && !$eMaisBarato;
                $pctRelativo = $precoMin > 0 && !$eMaisBarato ? round((($linha->preco_min - $precoMin) / $precoMin) * 100) : 0;
            @endphp
            <div class="forn-row {{ $eMaisBarato && $multiplos ? 'mais-barato' : ($eMaisCaro ? 'mais-caro':'') }}">
                {{-- Posição --}}
                <div style="width:28px;text-align:center;font-size:.75rem;font-weight:800;color:{{ $eMaisBarato?'#1A9E6E':($eMaisCaro?'#C94040':'#999') }}">
                    @if($multiplos) #{{ $i+1 }} @endif
                </div>

                {{-- Fornecedor --}}
                <div style="flex:1;margin-left:8px">
                    <div style="font-size:.85rem;font-weight:600">{{ $linha->fornecedor }}</div>
                    <div style="font-size:.68rem;color:#aaa">
                        {{ $linha->qtd_compras }} {{ __('compra(s)') }} •
                        {{ __('última') }}: {{ \Carbon\Carbon::parse($linha->ultima_compra)->format('d/m/Y') }}
                    </div>
                </div>

                {{-- Preço médio --}}
                @if($linha->qtd_compras > 1)
                <div class="text-center mr-4" style="min-width:80px">
                    <div style="font-size:.68rem;color:#aaa">{{ __('Média') }}</div>
                    <div style="font-size:.8rem;font-weight:600">R$ {{ number_format($linha->preco_medio,2,',','.') }}</div>
                </div>
                @endif

                {{-- Preço mínimo registrado --}}
                <div class="text-right mr-4" style="min-width:80px">
                    <div style="font-size:.68rem;color:#aaa">{{ __('Mín. registrado') }}</div>
                    <div class="preco-val {{ $eMaisBarato&&$multiplos?'preco-min':($eMaisCaro?'preco-max':'') }}">
                        R$ {{ number_format($linha->preco_min,2,',','.') }}
                    </div>
                </div>

                {{-- Badge --}}
                <div style="min-width:80px;text-align:right">
                    @if($eMaisBarato && $multiplos)
                        <span class="badge-barato"><i class="fas fa-check mr-1"></i>{{ __('Mais barato') }}</span>
                    @elseif($eMaisCaro)
                        <span class="badge-caro"><i class="fas fa-exclamation mr-1"></i>{{ __('Mais caro') }}</span>
                    @elseif($pctRelativo > 0)
                        <span class="pct-diff up">+{{ $pctRelativo }}%</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endforeach

{{-- Resumo --}}
<div class="alert alert-info mt-3" style="border-radius:10px">
    <i class="fas fa-info-circle mr-2"></i>
    {{ __('Mostrando') }} <strong>{{ $totalGrupos }}</strong> {{ __('produtos') }}.
    <strong>{{ $grupoComDiferenca }}</strong> {{ __('têm diferença de preço entre fornecedores.') }}
</div>
@endif

@stop
@section('js')
<script>
// Filtrar subcategorias ao selecionar categoria
document.getElementById('filtroCategoria').addEventListener('change', function() {
    var catId = this.value;
    var opts  = document.querySelectorAll('#filtroSubcat option[data-cat]');
    opts.forEach(function(opt) {
        opt.style.display = !catId || opt.dataset.cat === catId ? '' : 'none';
    });
    document.getElementById('filtroSubcat').value = '';
});
</script>
@stop
