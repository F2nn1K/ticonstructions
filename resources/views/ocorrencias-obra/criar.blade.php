@extends('adminlte::page')
@section('title', __('app.menu.register_occurrence'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fas fa-plus-circle mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.register_occurrence') }}</h1>
            <small class="text-muted">{{ __('Registre imprevistos e impactos nas fases da obra') }}</small>
        </div>
        <a href="{{ route('ocorrencias-obra.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> {{ __('app.menu.occurrences') }}
        </a>
    </div>
@stop
@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header" style="background:linear-gradient(135deg,#A8873A,#E2C87A);border-radius:12px 12px 0 0">
                <h6 class="mb-0 font-weight-bold text-white"><i class="fas fa-exclamation-triangle mr-2"></i>{{ __('Dados da Ocorrência') }}</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('ocorrencias-obra.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Obra') }} <span class="text-danger">*</span></label>
                                <select name="obra_id" id="obraSelect" class="form-control @error('obra_id') is-invalid @enderror" required>
                                    <option value="">{{ __('Selecione a obra...') }}</option>
                                    @foreach($obras as $ob)
                                        <option value="{{ $ob->id }}" {{ old('obra_id',$obraId)==$ob->id?'selected':'' }}>
                                            {{ $ob->nome }}{{ $ob->codigo?' ('.$ob->codigo.')':'' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Fase') }} <span class="text-danger">*</span></label>
                                <select name="obra_fase_id" id="faseSelect" class="form-control @error('obra_fase_id') is-invalid @enderror" required>
                                    <option value="">{{ __('Selecione a fase...') }}</option>
                                    @foreach($fases as $f)
                                        <option value="{{ $f->id }}" {{ old('obra_fase_id')==$f->id?'selected':'' }}>{{ $f->nome_personalizado }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Tipo') }} <span class="text-danger">*</span></label>
                                <select name="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                                    @php $tipoSel = old('tipo',''); @endphp
                                    <option value="chuva"               {{ $tipoSel=='chuva'               ?'selected':'' }}>{{ __('Chuva') }}</option>
                                    <option value="falta_material"      {{ $tipoSel=='falta_material'      ?'selected':'' }}>{{ __('Falta de Material') }}</option>
                                    <option value="falta_mao_de_obra"   {{ $tipoSel=='falta_mao_de_obra'   ?'selected':'' }}>{{ __('Falta de Mão de Obra') }}</option>
                                    <option value="erro_projeto"        {{ $tipoSel=='erro_projeto'        ?'selected':'' }}>{{ __('Erro de Projeto') }}</option>
                                    <option value="problema_equipamento"{{ $tipoSel=='problema_equipamento'?'selected':'' }}>{{ __('Problema de Equipamento') }}</option>
                                    <option value="acidente"            {{ $tipoSel=='acidente'            ?'selected':'' }}>{{ __('Acidente') }}</option>
                                    <option value="outro"               {{ $tipoSel=='outro'               ?'selected':'' }}>{{ __('Outro') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Data da Ocorrência') }} <span class="text-danger">*</span></label>
                                <input type="date" name="data_ocorrencia" value="{{ old('data_ocorrencia', today()->toDateString()) }}"
                                       class="form-control @error('data_ocorrencia') is-invalid @enderror" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Impacto no Prazo (dias)') }}</label>
                                <input type="number" name="impacto_dias" value="{{ old('impacto_dias', 0) }}"
                                       class="form-control" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Título') }} <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" value="{{ old('titulo') }}"
                               class="form-control @error('titulo') is-invalid @enderror"
                               placeholder="{{ __('Resumo da ocorrência') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Descrição') }} <span class="text-danger">*</span></label>
                        <textarea name="descricao" rows="4" class="form-control @error('descricao') is-invalid @enderror"
                                  placeholder="{{ __('Descreva o imprevisto em detalhes...') }}" required>{{ old('descricao') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Ação Tomada') }}</label>
                        <textarea name="acao_tomada" rows="3" class="form-control"
                                  placeholder="{{ __('Descreva as ações corretivas adotadas (opcional)...') }}">{{ old('acao_tomada') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="fas fa-save mr-1"></i> {{ __('Registrar Ocorrência') }}
                        </button>
                        <a href="{{ route('ocorrencias-obra.index') }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
document.getElementById('obraSelect').addEventListener('change', function() {
    const obraId = this.value;
    const faseSelect = document.getElementById('faseSelect');
    faseSelect.innerHTML = '<option value="">{{ __("Selecione a fase...") }}</option>';
    if (!obraId) return;
    fetch('/ocorrencias/fases/' + obraId)
        .then(r => r.json())
        .then(fases => {
            fases.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.id;
                opt.textContent = f.nome_personalizado;
                faseSelect.appendChild(opt);
            });
        });
});
</script>
@stop
