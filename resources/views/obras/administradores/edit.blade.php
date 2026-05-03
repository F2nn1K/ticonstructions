@extends('adminlte::page')

@section('title', 'Editar Administrador')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-family:'Playfair Display',serif;">
        <i class="fas fa-user-edit mr-2" style="color:#C9A84C;"></i>
        Editar — {{ $administrador->nome }}
    </h1>
    <a href="{{ route('obras.administradores.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Voltar
    </a>
</div>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-id-card mr-2" style="color:#C9A84C;"></i>
                    Ficha do Administrador
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('obras.administradores.update', $administrador) }}">
                    @csrf @method('PUT')

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="font-weight-bold">Nome completo <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome', $administrador->nome) }}" required>
                            @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">CPF</label>
                            <input type="text" name="cpf" class="form-control"
                                   value="{{ old('cpf', $administrador->cpf) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">E-mail</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $administrador->email) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Telefone</label>
                            <input type="text" name="telefone" class="form-control"
                                   value="{{ old('telefone', $administrador->telefone) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Cargo</label>
                            <input type="text" name="cargo" class="form-control"
                                   value="{{ old('cargo', $administrador->cargo) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Taxa de Administração (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="percentual_taxa" step="0.01" min="0" max="100"
                                       class="form-control"
                                       value="{{ old('percentual_taxa', $administrador->percentual_taxa) }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text" style="background:rgba(201,168,76,.1);color:#A8873A;font-weight:700;">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Status</label>
                            <select name="ativo" class="form-control">
                                <option value="1" {{ old('ativo', $administrador->ativo) ? 'selected' : '' }}>{{ __('Ativo') }}</option>
                                <option value="0" {{ !old('ativo', $administrador->ativo) ? 'selected' : '' }}>{{ __('Inativo') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">Usuário do Sistema</label>
                        <select name="user_id" class="form-control">
                            <option value="">— Sem vínculo —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $administrador->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', $administrador->observacoes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('obras.administradores.show', $administrador) }}" class="btn btn-info">
                            <i class="fas fa-eye mr-1"></i> Ver Ficha
                        </a>
                        <div>
                            <a href="{{ route('obras.administradores.index') }}" class="btn btn-secondary mr-2">{{ __('Cancelar') }}</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Atualizar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
