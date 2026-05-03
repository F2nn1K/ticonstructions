@extends('adminlte::page')

@section('title', 'Novo Administrador')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-family:'Playfair Display',serif;">
        <i class="fas fa-user-plus mr-2" style="color:#C9A84C;"></i>
        Novo Administrador
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
                <form method="POST" action="{{ route('obras.administradores.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="font-weight-bold">Nome completo <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome') }}" placeholder="Nome do administrador" required>
                            @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">CPF</label>
                            <input type="text" name="cpf" class="form-control"
                                   value="{{ old('cpf') }}" placeholder="000.000.000-00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">E-mail</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email') }}" placeholder="email@empresa.com.br">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Telefone</label>
                            <input type="text" name="telefone" class="form-control"
                                   value="{{ old('telefone') }}" placeholder="(00) 00000-0000">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Cargo</label>
                            <input type="text" name="cargo" class="form-control"
                                   value="{{ old('cargo', 'Administrador') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">
                                Taxa de Administração (%)
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="percentual_taxa" step="0.01" min="0" max="100"
                                       class="form-control @error('percentual_taxa') is-invalid @enderror"
                                       value="{{ old('percentual_taxa', '10.00') }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text" style="background:rgba(201,168,76,.1);border-color:rgba(201,168,76,.3);color:#A8873A;font-weight:700;">%</span>
                                </div>
                            </div>
                            <small class="text-muted">Percentual cobrado sobre o custo de obra (padrão: 10%)</small>
                            @error('percentual_taxa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">Usuário do Sistema (opcional)</label>
                        <select name="user_id" class="form-control">
                            <option value="">— Vincular usuário —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Se vinculado, o administrador terá acesso à sua própria ficha de taxa.</small>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3"
                                  placeholder="Informações adicionais...">{{ old('observacoes') }}</textarea>
                    </div>

                    <!-- Info visual sobre cálculo -->
                    <div class="p-3 rounded mb-4" style="background:rgba(201,168,76,.06);border:1px solid rgba(201,168,76,.2);">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle mr-2" style="color:#C9A84C;font-size:1.1rem;"></i>
                            <div>
                                <strong style="color:#7D6A52;">Como funciona a taxa de administração?</strong>
                                <p class="mb-0 mt-1" style="font-size:.85rem;color:#6A6259;">
                                    A taxa é calculada sobre o <strong>custo de obra</strong>
                                    (materiais + mão de obra + serviços), <em>excluindo</em> os próprios
                                    lançamentos de taxa de administração, para evitar circularidade no cálculo.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('obras.administradores.index') }}" class="btn btn-secondary mr-2">{{ __('Cancelar') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Salvar Administrador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
