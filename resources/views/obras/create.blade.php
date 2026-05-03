@extends('adminlte::page')

@section('title', __('Nova Obra'))

@section('content_header')
    <div class="d-flex align-items-center">
        <a href="{{ route('obras.index') }}" class="btn btn-sm btn-outline-secondary mr-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1><i class="fas fa-hard-hat mr-2"></i>{{ __('Nova Obra') }}</h1>
    </div>
@stop

@section('content')
<style>
.fase-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 10px;
    position: relative;
}
.fase-item .handle { cursor: grab; color: #999; }
.btn-remove-fase { position: absolute; top: 10px; right: 10px; }
.fase-num {
    width: 28px; height: 28px;
    background: #3c8dbc; color: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: bold; font-size: .8rem;
    flex-shrink: 0;
}
</style>

<form method="POST" action="{{ route('obras.store') }}" id="formObra">
@csrf

<div class="row">
    {{-- Dados gerais --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>{{ __('Dados da Obra') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 form-group">
                        <label class="font-weight-bold">{{ __('Nome da Obra') }} <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                               value="{{ old('nome') }}" required placeholder="{{ __('Ex.: Residencial São Paulo – Bloco A') }}">
                        @error('nome')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Código') }}</label>
                        <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}"
                               placeholder="OBR-2026-001">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">{{ __('Cliente') }}</label>
                        <input type="text" name="cliente" class="form-control" value="{{ old('cliente') }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">{{ __('Responsável Técnico') }}</label>
                        <input type="text" name="responsavel_tecnico" class="form-control" value="{{ old('responsavel_tecnico') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5 form-group">
                        <label class="font-weight-bold">{{ __('Cidade') }}</label>
                        <input type="text" name="cidade" class="form-control" value="{{ old('cidade') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="font-weight-bold">{{ __('UF') }}</label>
                        <input type="text" name="estado" class="form-control" maxlength="2" value="{{ old('estado') }}">
                    </div>
                    <div class="col-md-5 form-group">
                        <label class="font-weight-bold">{{ __('Endereço') }}</label>
                        <input type="text" name="endereco" class="form-control" value="{{ old('endereco') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Valor do Contrato (R$)') }}</label>
                        <input type="text" name="valor_contrato" class="form-control money"
                               value="{{ old('valor_contrato') }}" placeholder="0,00">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Área Total (m²)') }}</label>
                        <input type="number" name="area_total" class="form-control"
                               step="0.01" value="{{ old('area_total') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Início Previsto') }} <span class="text-danger">*</span></label>
                        <input type="date" name="data_inicio_prevista" class="form-control"
                               value="{{ old('data_inicio_prevista', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">{{ __('Término Previsto') }}</label>
                        <input type="date" name="data_fim_prevista" class="form-control"
                               value="{{ old('data_fim_prevista') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">{{ __('Descrição / Observações') }}</label>
                    <textarea name="descricao" rows="3" class="form-control"
                              placeholder="{{ __('Detalhes da obra, escopo, observações gerais...') }}">{{ old('descricao') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Painel lateral --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-success btn-block btn-lg">
                    <i class="fas fa-save mr-2"></i>{{ __('Criar Obra') }}
                </button>
                <a href="{{ route('obras.index') }}" class="btn btn-outline-secondary btn-block mt-2">
                    {{ __('Cancelar') }}
                </a>
            </div>
        </div>

        <div class="callout callout-info">
            <h6><i class="fas fa-lightbulb mr-2"></i>{{ __('Como funciona') }}</h6>
            <p class="mb-1 small">{{ __('Defina as fases ao lado e o sistema avança automaticamente a fase ativa quando você marcar como concluída.') }}</p>
            <p class="mb-0 small">{{ __('Todo lançamento financeiro fica vinculado à fase ativa no momento do registro.') }}</p>
        </div>
    </div>
</div>

{{-- Fases --}}
<div class="card mt-2">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-tasks mr-2"></i>{{ __('Fases da Obra (Cronograma)') }}</h6>
        <button type="button" class="btn btn-sm btn-light" id="btnAddFase">
            <i class="fas fa-plus mr-1"></i> {{ __('Adicionar Fase') }}
        </button>
    </div>
    <div class="card-body">
        @error('fases')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <div id="fasesContainer">
            @php
                $fasesDefault = $fases->take(10);
            @endphp
            @foreach($fasesDefault as $i => $fase)
            <div class="fase-item" data-index="{{ $i }}">
                <input type="hidden" name="fases[{{ $i }}][fase_catalogo_id]" value="{{ $fase->id }}" class="field-catalogo-id">
                <div class="d-flex align-items-center mb-2">
                    <div class="fase-num mr-2">{{ $i + 1 }}</div>
                    <div class="flex-grow-1">
                        <input type="text" class="form-control form-control-sm field-nome" placeholder="{{ __('Nome personalizado (opcional)') }}"
                               name="fases[{{ $i }}][nome_personalizado]"
                               value="{{ $fase->nome }}">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ml-2 btn-remove-fase-inline"
                            title="{{ __('Remover fase') }}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group mb-0">
                        <label class="small font-weight-bold">{{ __('Fase Base') }}</label>
                        <select name="fases[{{ $i }}][fase_catalogo_id]" class="form-control form-control-sm field-catalogo-select">
                            @foreach($fases as $opt)
                                <option value="{{ $opt->id }}" {{ $opt->id == $fase->id ? 'selected' : '' }}>
                                    {{ $opt->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        <label class="small font-weight-bold">{{ __('Data Início') }} <span class="text-danger">*</span></label>
                        <input type="date" name="fases[{{ $i }}][data_inicio_baseline]"
                               class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        <label class="small font-weight-bold">{{ __('Data Fim') }} <span class="text-danger">*</span></label>
                        <input type="date" name="fases[{{ $i }}][data_fim_baseline]"
                               class="form-control form-control-sm" required>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Template oculto --}}
        <template id="tplFase">
            <div class="fase-item" data-index="__IDX__">
                <div class="d-flex align-items-center mb-2">
                    <div class="fase-num mr-2">__NUM__</div>
                    <div class="flex-grow-1">
                        <input type="text" class="form-control form-control-sm field-nome"
                               placeholder="{{ __('Nome personalizado') }}"
                               name="fases[__IDX__][nome_personalizado]">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ml-2 btn-remove-fase-inline"
                            title="{{ __('Remover fase') }}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group mb-0">
                        <label class="small font-weight-bold">{{ __('Fase Base') }}</label>
                        <select name="fases[__IDX__][fase_catalogo_id]" class="form-control form-control-sm">
                            @foreach($fases as $opt)
                                <option value="{{ $opt->id }}">{{ $opt->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        <label class="small font-weight-bold">{{ __('Data Início') }} *</label>
                        <input type="date" name="fases[__IDX__][data_inicio_baseline]"
                               class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        <label class="small font-weight-bold">{{ __('Data Fim') }} *</label>
                        <input type="date" name="fases[__IDX__][data_fim_baseline]"
                               class="form-control form-control-sm" required>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

</form>
@stop

@section('js')
<script>
(function() {
    let idx = {{ $fasesDefault->count() }};

    document.getElementById('btnAddFase').addEventListener('click', function () {
        const tpl = document.getElementById('tplFase').innerHTML
            .replace(/__IDX__/g, idx)
            .replace(/__NUM__/g, idx + 1);

        document.getElementById('fasesContainer').insertAdjacentHTML('beforeend', tpl);
        idx++;
        renumerar();
    });

    document.getElementById('fasesContainer').addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-fase-inline')) {
            e.target.closest('.fase-item').remove();
            renumerar();
        }
    });

    function renumerar() {
        document.querySelectorAll('#fasesContainer .fase-item').forEach(function (el, i) {
            el.querySelector('.fase-num').textContent = i + 1;
            el.setAttribute('data-index', i);
            el.querySelectorAll('[name]').forEach(function (input) {
                input.name = input.name.replace(/fases\[\d+\]/, 'fases[' + i + ']');
            });
        });
        idx = document.querySelectorAll('#fasesContainer .fase-item').length;
    }
})();
</script>
@stop
