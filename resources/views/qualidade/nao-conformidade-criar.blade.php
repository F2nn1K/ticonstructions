@extends('adminlte::page')
@section('title', __('Registrar Não Conformidade'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0"><i class="fas fa-plus-circle mr-2" style="color:var(--ti-gold)"></i>{{ __('Registrar Não Conformidade') }}</h1>
        <a href="{{ route('qualidade.nao-conformidades') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>{{ __('app.menu.non_conformities') }}</a>
    </div>
@stop
@section('content')
@if($errors->any())<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
        <div class="card-header" style="background:linear-gradient(135deg,#A8873A,#E2C87A);border-radius:12px 12px 0 0">
            <h6 class="mb-0 font-weight-bold text-white"><i class="fas fa-times-circle mr-2"></i>{{ __('Dados da Não Conformidade') }}</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('qualidade.nao-conformidade-store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Obra') }} <span class="text-danger">*</span></label>
                            <select name="obra_id" id="obraSelect" class="form-control @error('obra_id') is-invalid @enderror" required>
                                <option value="">{{ __('Selecione a obra...') }}</option>
                                @foreach($obras as $ob)<option value="{{ $ob->id }}" {{ old('obra_id',$obraId)==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Fase') }}</label>
                            <select name="obra_fase_id" id="faseSelect" class="form-control">
                                <option value="">{{ __('Toda a obra') }}</option>
                                @foreach($fases as $f)<option value="{{ $f->id }}" {{ old('obra_fase_id')==$f->id?'selected':'' }}>{{ $f->nome_personalizado }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">{{ __('Título') }} <span class="text-danger">*</span></label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" class="form-control @error('titulo') is-invalid @enderror" required>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">{{ __('Descrição') }} <span class="text-danger">*</span></label>
                    <textarea name="descricao" rows="4" class="form-control @error('descricao') is-invalid @enderror" required>{{ old('descricao') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Gravidade') }} <span class="text-danger">*</span></label>
                            <select name="gravidade" class="form-control" required>
                                @php $g = old('gravidade','moderada'); @endphp
                                <option value="leve"     {{ $g=='leve'    ?'selected':'' }}>{{ __('Leve') }}</option>
                                <option value="moderada" {{ $g=='moderada'?'selected':'' }}>{{ __('Moderada') }}</option>
                                <option value="grave"    {{ $g=='grave'   ?'selected':'' }}>{{ __('Grave') }}</option>
                                <option value="critica"  {{ $g=='critica' ?'selected':'' }}>{{ __('Crítica') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                @php $st = old('status','aberta'); @endphp
                                <option value="aberta"      {{ $st=='aberta'      ?'selected':'' }}>{{ __('Aberta') }}</option>
                                <option value="em_correcao" {{ $st=='em_correcao' ?'selected':'' }}>{{ __('Em Correção') }}</option>
                                <option value="resolvida"   {{ $st=='resolvida'   ?'selected':'' }}>{{ __('Resolvida') }}</option>
                                <option value="aceita"      {{ $st=='aceita'      ?'selected':'' }}>{{ __('Aceita') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Prazo para Correção') }}</label>
                            <input type="date" name="prazo_correcao" value="{{ old('prazo_correcao') }}" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">{{ __('Ação Corretiva') }}</label>
                    <textarea name="acao_corretiva" rows="3" class="form-control" placeholder="{{ __('Descreva a ação corretiva adotada ou planejada...') }}">{{ old('acao_corretiva') }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-grow-1"><i class="fas fa-save mr-1"></i>{{ __('Registrar Não Conformidade') }}</button>
                    <a href="{{ route('qualidade.nao-conformidades') }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
                </div>
            </form>
        </div>
    </div>
</div></div>
@stop
@section('js')
<script>
document.getElementById('obraSelect').addEventListener('change', function() {
    const id = this.value, sel = document.getElementById('faseSelect');
    sel.innerHTML = '<option value="">{{ __("Toda a obra") }}</option>';
    if (!id) return;
    fetch('/qualidade/fases/' + id).then(r=>r.json()).then(fases => {
        fases.forEach(f => { const o=document.createElement('option'); o.value=f.id; o.textContent=f.nome_personalizado; sel.appendChild(o); });
    });
});
</script>
@stop
