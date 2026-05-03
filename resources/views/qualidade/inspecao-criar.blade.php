@extends('adminlte::page')
@section('title', __('Nova Inspeção'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0"><i class="fas fa-plus-circle mr-2" style="color:var(--ti-gold)"></i>{{ __('Nova Inspeção') }}</h1>
        <a href="{{ route('qualidade.inspecoes') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>{{ __('app.menu.inspections') }}</a>
    </div>
@stop
@section('content')
@if($errors->any())<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
        <div class="card-header" style="background:linear-gradient(135deg,#A8873A,#E2C87A);border-radius:12px 12px 0 0">
            <h6 class="mb-0 font-weight-bold text-white"><i class="fas fa-search mr-2"></i>{{ __('Dados da Inspeção') }}</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('qualidade.inspecao-store') }}">
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
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Título') }} <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" value="{{ old('titulo') }}" class="form-control @error('titulo') is-invalid @enderror" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Data') }} <span class="text-danger">*</span></label>
                            <input type="date" name="data_inspecao" value="{{ old('data_inspecao', today()->toDateString()) }}" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Responsável') }}</label>
                            <input type="text" name="responsavel" value="{{ old('responsavel') }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Checklist') }}</label>
                            <select name="checklist_id" class="form-control">
                                <option value="">{{ __('Sem checklist') }}</option>
                                @foreach($checklists as $cl)<option value="{{ $cl->id }}" {{ old('checklist_id')==$cl->id?'selected':'' }}>{{ $cl->titulo }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                @php $st = old('status','pendente'); @endphp
                                <option value="pendente"    {{ $st=='pendente'    ?'selected':'' }}>{{ __('Pendente') }}</option>
                                <option value="em_andamento"{{ $st=='em_andamento'?'selected':'' }}>{{ __('Em Andamento') }}</option>
                                <option value="concluida"   {{ $st=='concluida'   ?'selected':'' }}>{{ __('Concluída') }}</option>
                                <option value="reprovada"   {{ $st=='reprovada'   ?'selected':'' }}>{{ __('Reprovada') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">{{ __('Observações') }}</label>
                    <textarea name="observacoes" rows="3" class="form-control" placeholder="{{ __('Opcional...') }}">{{ old('observacoes') }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-grow-1"><i class="fas fa-save mr-1"></i>{{ __('Salvar Inspeção') }}</button>
                    <a href="{{ route('qualidade.inspecoes') }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
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
