@extends('adminlte::page')
@section('title', isset($risco) ? __('Editar Risco') : __('app.menu.register_risk'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-{{ isset($risco) ? 'edit' : 'plus-circle' }} mr-2" style="color:var(--ti-gold)"></i>
                {{ isset($risco) ? __('Editar Risco') : __('app.menu.register_risk') }}
            </h1>
        </div>
        <a href="{{ route('riscos.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> {{ __('app.menu.risk_matrix') }}
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
    <div class="col-lg-8">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header" style="background:linear-gradient(135deg,#A8873A,#E2C87A);border-radius:12px 12px 0 0">
                <h6 class="mb-0 font-weight-bold text-white">
                    <i class="fas fa-shield-alt mr-2"></i>{{ __('Dados do Risco') }}
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($risco) ? route('riscos.update', $risco) : route('riscos.store') }}">
                    @csrf
                    @if(isset($risco)) @method('PUT') @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Título') }} <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" value="{{ old('titulo', $risco->titulo ?? '') }}"
                                       class="form-control @error('titulo') is-invalid @enderror"
                                       placeholder="{{ __('Descreva o risco brevemente') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Obra') }}</label>
                                <select name="obra_id" class="form-control">
                                    <option value="">{{ __('Geral (sem obra)') }}</option>
                                    @foreach($obras as $ob)
                                        <option value="{{ $ob->id }}" {{ old('obra_id', $risco->obra_id ?? '') == $ob->id ? 'selected' : '' }}>
                                            {{ $ob->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Descrição') }}</label>
                        <textarea name="descricao" rows="3" class="form-control"
                                  placeholder="{{ __('Detalhes sobre o risco...') }}">{{ old('descricao', $risco->descricao ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Categoria') }} <span class="text-danger">*</span></label>
                                <select name="categoria" class="form-control @error('categoria') is-invalid @enderror" required>
                                    @php $cat = old('categoria', $risco->categoria ?? ''); @endphp
                                    <option value="seguranca"  {{ $cat=='seguranca' ?'selected':'' }}>{{ __('Segurança') }}</option>
                                    <option value="financeiro" {{ $cat=='financeiro'?'selected':'' }}>{{ __('Financeiro') }}</option>
                                    <option value="ambiental"  {{ $cat=='ambiental' ?'selected':'' }}>{{ __('Ambiental') }}</option>
                                    <option value="cronograma" {{ $cat=='cronograma'?'selected':'' }}>{{ __('Cronograma') }}</option>
                                    <option value="qualidade"  {{ $cat=='qualidade' ?'selected':'' }}>{{ __('Qualidade') }}</option>
                                    <option value="outro"      {{ $cat=='outro'     ?'selected':'' }}>{{ __('Outro') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Probabilidade') }} (1-5) <span class="text-danger">*</span></label>
                                <select name="probabilidade" class="form-control" required>
                                    @php $prob = old('probabilidade', $risco->probabilidade ?? 1); @endphp
                                    @for($i=1;$i<=5;$i++)
                                        <option value="{{ $i }}" {{ $prob==$i?'selected':'' }}>
                                            {{ $i }} — {{ [1=>__('Muito Baixa'),2=>__('Baixa'),3=>__('Média'),4=>__('Alta'),5=>__('Muito Alta')][$i] }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Impacto') }} (1-5) <span class="text-danger">*</span></label>
                                <select name="impacto" class="form-control" required>
                                    @php $imp = old('impacto', $risco->impacto ?? 1); @endphp
                                    @for($i=1;$i<=5;$i++)
                                        <option value="{{ $i }}" {{ $imp==$i?'selected':'' }}>
                                            {{ $i }} — {{ [1=>__('Muito Baixo'),2=>__('Baixo'),3=>__('Médio'),4=>__('Alto'),5=>__('Muito Alto')][$i] }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Plano de Ação / Mitigação') }}</label>
                        <textarea name="plano_acao" rows="3" class="form-control"
                                  placeholder="{{ __('Descreva as ações para mitigar ou controlar este risco...') }}">{{ old('plano_acao', $risco->plano_acao ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Responsável') }}</label>
                                <input type="text" name="responsavel" value="{{ old('responsavel', $risco->responsavel ?? '') }}"
                                       class="form-control" placeholder="{{ __('Nome do responsável') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Prazo') }}</label>
                                <input type="date" name="prazo" value="{{ old('prazo', isset($risco->prazo) ? $risco->prazo->toDateString() : '') }}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Status') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    @php $st = old('status', $risco->status ?? 'identificado'); @endphp
                                    <option value="identificado" {{ $st=='identificado'?'selected':'' }}>{{ __('Identificado') }}</option>
                                    <option value="em_mitigacao" {{ $st=='em_mitigacao'?'selected':'' }}>{{ __('Em Mitigação') }}</option>
                                    <option value="mitigado"     {{ $st=='mitigado'    ?'selected':'' }}>{{ __('Mitigado') }}</option>
                                    <option value="aceito"       {{ $st=='aceito'      ?'selected':'' }}>{{ __('Aceito') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="fas fa-save mr-1"></i>
                            {{ isset($risco) ? __('Salvar Alterações') : __('Registrar Risco') }}
                        </button>
                        <a href="{{ route('riscos.index') }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
