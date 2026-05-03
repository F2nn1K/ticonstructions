@extends('adminlte::page')

@section('title', __('app.menu.record_measurement'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-ruler-combined mr-2" style="color:var(--ti-gold)"></i>
                {{ __('app.menu.record_measurement') }}
            </h1>
            <small class="text-muted">{{ __('Registre o avanço físico de uma obra ou fase') }}</small>
        </div>
        <a href="{{ route('producao.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> {{ __('Avanço Físico') }}
        </a>
    </div>
@stop

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card" style="border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header" style="background:linear-gradient(135deg,#A8873A,#E2C87A); border-radius:12px 12px 0 0">
                <h6 class="mb-0 font-weight-bold text-white">
                    <i class="fas fa-plus-circle mr-2"></i>{{ __('Nova Medição') }}
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('producao.store') }}" id="formMedicao">
                    @csrf

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Obra') }} <span class="text-danger">*</span></label>
                        <select name="obra_id" id="obraSelect"
                                class="form-control @error('obra_id') is-invalid @enderror" required>
                            <option value="">{{ __('Selecione a obra...') }}</option>
                            @foreach($obras as $ob)
                                <option value="{{ $ob->id }}"
                                    {{ old('obra_id', $obraId) == $ob->id ? 'selected' : '' }}>
                                    {{ $ob->nome }}{{ $ob->codigo ? ' ('.$ob->codigo.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="faseContainer">
                        <label class="font-weight-bold">{{ __('Fase') }}</label>
                        <select name="obra_fase_id" id="faseSelect" class="form-control">
                            <option value="">{{ __('Toda a obra (geral)') }}</option>
                            @foreach($fases as $fase)
                                <option value="{{ $fase->id }}"
                                    data-pct="{{ $fase->percentual_realizado }}"
                                    {{ old('obra_fase_id') == $fase->id ? 'selected' : '' }}>
                                    {{ $fase->nome_personalizado }}
                                    ({{ number_format($fase->percentual_realizado, 0) }}% {{ __('realizado') }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('Opcional — deixe em branco para medir a obra toda') }}</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Data da Medição') }} <span class="text-danger">*</span></label>
                        <input type="date" name="data_medicao" value="{{ old('data_medicao', today()->toDateString()) }}"
                               class="form-control @error('data_medicao') is-invalid @enderror" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('% Medido nesta entrada') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="percentual_medido" id="pctMedido"
                                           value="{{ old('percentual_medido', 0) }}"
                                           class="form-control @error('percentual_medido') is-invalid @enderror"
                                           min="0" max="100" step="0.01" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Valor da Medição (R$)') }}</label>
                                <input type="number" name="valor_medicao"
                                       value="{{ old('valor_medicao') }}"
                                       class="form-control"
                                       min="0" step="0.01"
                                       placeholder="{{ __('Opcional') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Descrição dos Serviços Executados') }} <span class="text-danger">*</span></label>
                        <textarea name="descricao" rows="4"
                                  class="form-control @error('descricao') is-invalid @enderror"
                                  placeholder="{{ __('Descreva os serviços executados nesta medição...') }}"
                                  required>{{ old('descricao') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Observações') }}</label>
                        <textarea name="observacoes" rows="2"
                                  class="form-control"
                                  placeholder="{{ __('Opcional...') }}">{{ old('observacoes') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="fas fa-save mr-1"></i> {{ __('Lançar Medição') }}
                        </button>
                        <a href="{{ route('producao.index') }}" class="btn btn-outline-secondary">
                            {{ __('Cancelar') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
document.getElementById('obraSelect').addEventListener('change', function () {
    const obraId = this.value;
    const faseSelect = document.getElementById('faseSelect');
    faseSelect.innerHTML = '<option value="">{{ __("Toda a obra (geral)") }}</option>';

    if (!obraId) return;

    fetch('/producao/fases/' + obraId)
        .then(r => r.json())
        .then(fases => {
            fases.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.id;
                opt.dataset.pct = f.percentual_realizado;
                opt.textContent = f.nome_personalizado +
                    ' (' + parseFloat(f.percentual_realizado).toFixed(0) + '% {{ __("realizado") }})';
                faseSelect.appendChild(opt);
            });
        });
});
</script>
@stop
