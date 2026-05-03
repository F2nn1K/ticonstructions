@extends('adminlte::page')
@section('title', __('Novo Checklist'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0"><i class="fas fa-plus-circle mr-2" style="color:var(--ti-gold)"></i>{{ __('Novo Checklist') }}</h1>
        <a href="{{ route('qualidade.checklists') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>{{ __('app.menu.checklists') }}</a>
    </div>
@stop
@section('content')
@if($errors->any())<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
        <div class="card-header" style="background:linear-gradient(135deg,#A8873A,#E2C87A);border-radius:12px 12px 0 0">
            <h6 class="mb-0 font-weight-bold text-white"><i class="fas fa-list-check mr-2"></i>{{ __('Dados do Checklist') }}</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('qualidade.checklist-store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Título') }} <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" value="{{ old('titulo') }}" class="form-control @error('titulo') is-invalid @enderror" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Categoria') }} <span class="text-danger">*</span></label>
                            <select name="categoria" class="form-control @error('categoria') is-invalid @enderror" required>
                                @php $c = old('categoria','outro'); @endphp
                                <option value="estrutura"            {{ $c=='estrutura'           ?'selected':'' }}>{{ __('Estrutura') }}</option>
                                <option value="acabamento"           {{ $c=='acabamento'          ?'selected':'' }}>{{ __('Acabamento') }}</option>
                                <option value="instalacao_eletrica"  {{ $c=='instalacao_eletrica' ?'selected':'' }}>{{ __('Inst. Elétrica') }}</option>
                                <option value="instalacao_hidraulica"{{ $c=='instalacao_hidraulica'?'selected':'' }}>{{ __('Inst. Hidráulica') }}</option>
                                <option value="seguranca"            {{ $c=='seguranca'           ?'selected':'' }}>{{ __('Segurança') }}</option>
                                <option value="outro"                {{ $c=='outro'               ?'selected':'' }}>{{ __('Outro') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Obra') }}</label>
                            <select name="obra_id" class="form-control">
                                <option value="">{{ __('Geral (qualquer obra)') }}</option>
                                @foreach($obras as $ob)<option value="{{ $ob->id }}" {{ old('obra_id')==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Descrição') }}</label>
                            <input type="text" name="descricao" value="{{ old('descricao') }}" class="form-control" placeholder="{{ __('Opcional') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">{{ __('Itens do Checklist') }}</label>
                    <small class="text-muted d-block mb-2">{{ __('Adicione os itens que serão verificados durante a inspeção') }}</small>
                    <div id="itensContainer">
                        @if(old('itens'))
                            @foreach(old('itens') as $i => $item)
                                <div class="input-group mb-2 item-row">
                                    <div class="input-group-prepend"><span class="input-group-text text-muted" style="font-size:.8rem">{{ $i+1 }}</span></div>
                                    <input type="text" name="itens[]" value="{{ $item }}" class="form-control" placeholder="{{ __('Descreva o item...') }}">
                                    <div class="input-group-append"><button type="button" class="btn btn-outline-danger btn-remover"><i class="fas fa-times"></i></button></div>
                                </div>
                            @endforeach
                        @else
                            @for($i=0; $i<3; $i++)
                                <div class="input-group mb-2 item-row">
                                    <div class="input-group-prepend"><span class="input-group-text text-muted" style="font-size:.8rem">{{ $i+1 }}</span></div>
                                    <input type="text" name="itens[]" class="form-control" placeholder="{{ __('Descreva o item...') }}">
                                    <div class="input-group-append"><button type="button" class="btn btn-outline-danger btn-remover"><i class="fas fa-times"></i></button></div>
                                </div>
                            @endfor
                        @endif
                    </div>
                    <button type="button" id="btnAddItem" class="btn btn-sm btn-outline-primary mt-1">
                        <i class="fas fa-plus mr-1"></i>{{ __('Adicionar Item') }}
                    </button>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-success flex-grow-1"><i class="fas fa-save mr-1"></i>{{ __('Salvar Checklist') }}</button>
                    <a href="{{ route('qualidade.checklists') }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
                </div>
            </form>
        </div>
    </div>
</div></div>
@stop
@section('js')
<script>
let counter = document.querySelectorAll('.item-row').length;
document.getElementById('btnAddItem').addEventListener('click', function() {
    counter++;
    const div = document.createElement('div');
    div.className = 'input-group mb-2 item-row';
    div.innerHTML = `<div class="input-group-prepend"><span class="input-group-text text-muted" style="font-size:.8rem">${counter}</span></div>
        <input type="text" name="itens[]" class="form-control" placeholder="{{ __('Descreva o item...') }}">
        <div class="input-group-append"><button type="button" class="btn btn-outline-danger btn-remover"><i class="fas fa-times"></i></button></div>`;
    document.getElementById('itensContainer').appendChild(div);
    div.querySelector('.btn-remover').addEventListener('click', function(){ div.remove(); renum(); });
    div.querySelector('input').focus();
});
document.querySelectorAll('.btn-remover').forEach(b => b.addEventListener('click', function(){ this.closest('.item-row').remove(); renum(); }));
function renum() {
    document.querySelectorAll('.item-row').forEach((r,i) => { r.querySelector('.input-group-text').textContent = i+1; });
    counter = document.querySelectorAll('.item-row').length;
}
</script>
@stop
