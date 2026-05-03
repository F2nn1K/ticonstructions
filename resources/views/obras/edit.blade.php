@extends('adminlte::page')

@section('title', __('Editar Obra'))

@section('content_header')
    <div class="d-flex align-items-center">
        <a href="{{ route('obras.show', $obra) }}" class="btn btn-sm btn-outline-secondary mr-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1><i class="fas fa-edit mr-2"></i>{{ __('Editar') }}: {{ $obra->nome }}</h1>
    </div>
@stop

@section('content')
<form method="POST" action="{{ route('obras.update', $obra) }}">
@csrf
@method('PUT')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>{{ __('Dados da Obra') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 form-group">
                        <label class="font-weight-bold">{{ __('Nome da Obra') }} *</label>
                        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                               value="{{ old('nome', $obra->nome) }}" required>
                        @error('nome')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Código') }}</label>
                        <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $obra->codigo) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">{{ __('Cliente') }}</label>
                        <input type="text" name="cliente" class="form-control" value="{{ old('cliente', $obra->cliente) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">{{ __('Responsável Técnico') }}</label>
                        <input type="text" name="responsavel_tecnico" class="form-control"
                               value="{{ old('responsavel_tecnico', $obra->responsavel_tecnico) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5 form-group">
                        <label class="font-weight-bold">{{ __('Cidade') }}</label>
                        <input type="text" name="cidade" class="form-control" value="{{ old('cidade', $obra->cidade) }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="font-weight-bold">{{ __('UF') }}</label>
                        <input type="text" name="estado" class="form-control" maxlength="2"
                               value="{{ old('estado', $obra->estado) }}">
                    </div>
                    <div class="col-md-5 form-group">
                        <label class="font-weight-bold">{{ __('Endereço') }}</label>
                        <input type="text" name="endereco" class="form-control"
                               value="{{ old('endereco', $obra->endereco) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Valor Contrato (R$)') }}</label>
                        <input type="number" name="valor_contrato" class="form-control" step="0.01"
                               value="{{ old('valor_contrato', $obra->valor_contrato) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Área Total (m²)') }}</label>
                        <input type="number" name="area_total" class="form-control" step="0.01"
                               value="{{ old('area_total', $obra->area_total) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Status') }} *</label>
                        <select name="status" class="form-control" required>
                            @foreach([
                                'planejamento'  => __('Planejamento'),
                                'em_andamento'  => __('Em Andamento'),
                                'concluida'     => __('Concluída'),
                                'suspensa'      => __('Suspensa'),
                                'cancelada'     => __('Cancelada'),
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $obra->status) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Início Previsto') }}</label>
                        <input type="date" name="data_inicio_prevista" class="form-control"
                               value="{{ old('data_inicio_prevista', $obra->data_inicio_prevista?->toDateString()) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Término Previsto') }}</label>
                        <input type="date" name="data_fim_prevista" class="form-control"
                               value="{{ old('data_fim_prevista', $obra->data_fim_prevista?->toDateString()) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">{{ __('Descrição') }}</label>
                    <textarea name="descricao" rows="3" class="form-control">{{ old('descricao', $obra->descricao) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-success btn-block btn-lg">
                    <i class="fas fa-save mr-2"></i>{{ __('Salvar Alterações') }}
                </button>
                <a href="{{ route('obras.show', $obra) }}" class="btn btn-outline-secondary btn-block mt-2">
                    {{ __('Cancelar') }}
                </a>
                <hr>
                <form method="POST" action="{{ route('obras.destroy', $obra) }}"
                      onsubmit="return confirm('{{ __('Tem certeza que deseja remover esta obra? Esta ação não pode ser desfeita.') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-block btn-sm">
                        <i class="fas fa-trash mr-2"></i>{{ __('Excluir Obra') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</form>
@stop
