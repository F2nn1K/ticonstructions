@extends('adminlte::page')
@section('title', $fornecedor->id ? __('Editar Fornecedor') : __('Novo Fornecedor'))
@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('fornecedores.index') }}" class="btn btn-sm btn-outline-secondary mr-3"><i class="fas fa-arrow-left"></i></a>
    <h1 class="mb-0">
        <i class="fas fa-truck mr-2" style="color:var(--ti-gold)"></i>
        {{ $fornecedor->id ? __('Editar Fornecedor') : __('Novo Fornecedor') }}
    </h1>
</div>
@stop
@section('content')
<div class="row justify-content-center">
<div class="col-md-9">
@if($errors->any())
<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.08)">
    <div class="card-header text-white" style="background:var(--ti-gold-gradient,linear-gradient(135deg,#A8873A,#E2C87A));border-radius:12px 12px 0 0">
        <h6 class="mb-0"><i class="fas fa-truck mr-2"></i>{{ __('Dados do Fornecedor') }}</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ $fornecedor->id ? route('fornecedores.update',$fornecedor) : route('fornecedores.store') }}">
            @csrf
            @if($fornecedor->id) @method('PUT') @endif

            <div class="row">
                <div class="col-md-7 form-group">
                    <label class="font-weight-bold">{{ __('Razão Social') }} <span class="text-danger">*</span></label>
                    <input type="text" name="razao_social" class="form-control @error('razao_social') is-invalid @enderror"
                           value="{{ old('razao_social', $fornecedor->razao_social) }}" required>
                    @error('razao_social')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-5 form-group">
                    <label class="font-weight-bold">{{ __('Nome Fantasia') }}</label>
                    <input type="text" name="nome_fantasia" class="form-control" value="{{ old('nome_fantasia', $fornecedor->nome_fantasia) }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label class="font-weight-bold">{{ __('CNPJ') }}</label>
                    <input type="text" name="cnpj" class="form-control" value="{{ old('cnpj', $fornecedor->cnpj) }}" placeholder="00.000.000/0000-00">
                </div>
                <div class="col-md-4 form-group">
                    <label class="font-weight-bold">{{ __('Telefone') }}</label>
                    <input type="text" name="telefone" class="form-control" value="{{ old('telefone', $fornecedor->telefone) }}" placeholder="(00) 00000-0000">
                </div>
                <div class="col-md-4 form-group">
                    <label class="font-weight-bold">{{ __('E-mail') }}</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $fornecedor->email) }}">
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold">{{ __('Endereço') }}</label>
                    <input type="text" name="endereco" class="form-control" value="{{ old('endereco', $fornecedor->endereco) }}" placeholder="{{ __('Rua, número, complemento...') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label class="font-weight-bold">{{ __('Cidade') }}</label>
                    <input type="text" name="cidade" class="form-control" value="{{ old('cidade', $fornecedor->cidade) }}">
                </div>
                <div class="col-md-2 form-group">
                    <label class="font-weight-bold">{{ __('UF') }}</label>
                    <input type="text" name="uf" class="form-control" value="{{ old('uf', $fornecedor->uf) }}" maxlength="2" style="text-transform:uppercase" placeholder="SP">
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">{{ __('Observações') }}</label>
                <textarea name="observacoes" rows="2" class="form-control" placeholder="{{ __('Condições de pagamento, prazos, especialidades...') }}">{{ old('observacoes', $fornecedor->observacoes) }}</textarea>
            </div>

            @if($fornecedor->id)
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="ativo" name="ativo" value="1" {{ old('ativo', $fornecedor->ativo) ? 'checked':'' }}>
                    <label class="custom-control-label font-weight-bold" for="ativo">{{ __('Fornecedor ativo') }}</label>
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-end pt-2">
                <a href="{{ route('fornecedores.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-2"></i>{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@stop
