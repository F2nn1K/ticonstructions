@extends('adminlte::page')
@section('title', __('Categorias e Subcategorias'))
@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="mb-0"><i class="fas fa-tags mr-2" style="color:var(--ti-gold)"></i>{{ __('Categorias e Subcategorias') }}</h1>
</div>
@stop
@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif

<div class="row">
    {{-- Formulário nova categoria --}}
    <div class="col-md-4">
        <div class="card mb-4" style="border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07)">
            <div class="card-header" style="background:#f7f5f0"><h6 class="mb-0 font-weight-bold"><i class="fas fa-plus mr-2" style="color:#A8873A"></i>{{ __('Nova Categoria') }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('categorias.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold small">{{ __('Nome') }} *</label>
                        <input type="text" name="nome" class="form-control form-control-sm" required placeholder="Ex: Estrutura, Elétrica...">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">{{ __('Tipo') }}</label>
                        <select name="tipo" class="form-control form-control-sm">
                            <option value="ambos">{{ __('Ambos') }}</option>
                            <option value="material">{{ __('Material') }}</option>
                            <option value="servico">{{ __('Serviço') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">{{ __('Ícone FontAwesome') }}</label>
                        <input type="text" name="icone" class="form-control form-control-sm" placeholder="fas fa-tools">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-plus mr-1"></i>{{ __('Adicionar Categoria') }}</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Lista de categorias e subcategorias --}}
    <div class="col-md-8">
        @foreach($categorias as $cat)
        <div class="card mb-3" style="border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07)">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:#f7f5f0;border-radius:10px 10px 0 0">
                <div>
                    @if($cat->icone)<i class="{{ $cat->icone }} mr-2" style="color:#A8873A"></i>@endif
                    <strong>{{ $cat->nome }}</strong>
                    <span class="badge badge-secondary ml-2" style="font-size:.65rem">{{ __($cat->tipo) }}</span>
                </div>
                <form method="POST" action="{{ route('categorias.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('{{ __('Remover categoria e todas as subcategorias?') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-outline-danger" title="{{ __('Remover') }}"><i class="fas fa-times"></i></button>
                </form>
            </div>
            <div class="card-body p-0">
                {{-- Subcategorias existentes --}}
                @foreach($cat->subcategorias as $sub)
                <div class="d-flex align-items-center px-3 py-2" style="border-bottom:1px solid #f5f5f5">
                    <i class="fas fa-circle mr-2" style="color:#ddd;font-size:.5rem"></i>
                    <span style="flex:1;font-size:.84rem">{{ $sub->nome }}</span>
                    @if($sub->unidade)<span class="text-muted small mr-3">({{ $sub->unidade }})</span>@endif
                    <form method="POST" action="{{ route('categorias.subcategoria.destroy', $sub) }}" class="d-inline" onsubmit="return confirm('{{ __('Remover?') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs text-muted" style="background:none;border:none;padding:0 4px"><i class="fas fa-times" style="font-size:.65rem"></i></button>
                    </form>
                </div>
                @endforeach

                {{-- Adicionar subcategoria --}}
                <form method="POST" action="{{ route('categorias.subcategoria.store') }}" class="d-flex align-items-center px-3 py-2" style="gap:6px;background:#fafaf7">
                    @csrf
                    <input type="hidden" name="categoria_id" value="{{ $cat->id }}">
                    <input type="text" name="nome" class="form-control form-control-sm" placeholder="{{ __('Nova subcategoria...') }}" required style="flex:1">
                    <input type="text" name="unidade" class="form-control form-control-sm" placeholder="{{ __('Unidade') }}" style="width:80px">
                    <button type="submit" class="btn btn-outline-primary btn-sm" title="{{ __('Adicionar') }}"><i class="fas fa-plus"></i></button>
                </form>
            </div>
        </div>
        @endforeach
        @if($categorias->isEmpty())
        <div class="text-center text-muted py-5"><i class="fas fa-tags fa-3x mb-3" style="opacity:.25"></i><p>{{ __('Nenhuma categoria cadastrada.') }}</p></div>
        @endif
    </div>
</div>
@stop
