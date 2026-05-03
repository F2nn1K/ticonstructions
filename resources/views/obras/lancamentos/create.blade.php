@extends('adminlte::page')

@section('title', __('Novo Lançamento'))

@section('content_header')
    <div class="d-flex align-items-center">
        <a href="{{ route('obras.show', $obra) }}" class="btn btn-sm btn-outline-secondary mr-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1><i class="fas fa-dollar-sign mr-2"></i>{{ __('Novo Lançamento') }}</h1>
            <small class="text-muted">{{ $obra->nome }}</small>
        </div>
    </div>
@stop

@section('content')
<div class="row justify-content-center">
<div class="col-md-9">

{{-- Fase ativa destaque --}}
<div class="alert alert-info mb-3">
    <i class="fas fa-info-circle mr-2"></i>
    {{ __('Este lançamento será vinculado automaticamente à fase ativa:') }}
    <strong>{{ $faseAtiva->nome }}</strong>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0"><i class="fas fa-plus mr-2"></i>{{ __('Dados do Lançamento') }}</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('obras.lancamentos.store', $obra) }}" id="formLanc">
        @csrf

        <div class="row">
            {{-- Categoria --}}
            <div class="col-md-5 form-group">
                <label class="font-weight-bold">{{ __('Categoria') }} <span class="text-danger">*</span></label>
                <select name="categoria_id" id="selectCategoria" class="form-control @error('categoria_id') is-invalid @enderror" required>
                    <option value="">{{ __('Selecione...') }}</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nome }}
                        </option>
                    @endforeach
                </select>
                @error('categoria_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            {{-- Subcategoria --}}
            <div class="col-md-4 form-group">
                <label class="font-weight-bold">{{ __('Subcategoria') }}</label>
                <select name="subcategoria_id" id="selectSubcategoria" class="form-control">
                    <option value="">{{ __('Selecione a categoria primeiro') }}</option>
                </select>
            </div>

            {{-- Tipo --}}
            <div class="col-md-3 form-group">
                <label class="font-weight-bold">{{ __('Tipo') }} <span class="text-danger">*</span></label>
                <select name="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                    <option value="material"    {{ old('tipo')=='material'    ? 'selected':'' }}>{{ __('Material') }}</option>
                    <option value="servico"     {{ old('tipo')=='servico'     ? 'selected':'' }}>{{ __('Serviço') }}</option>
                    <option value="mao_de_obra" {{ old('tipo')=='mao_de_obra' ? 'selected':'' }}>{{ __('Mão de Obra') }}</option>
                    <option value="equipamento" {{ old('tipo')=='equipamento' ? 'selected':'' }}>{{ __('Equipamento') }}</option>
                    <option value="terceiro"    {{ old('tipo')=='terceiro'    ? 'selected':'' }}>{{ __('Terceiro') }}</option>
                </select>
                @error('tipo')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- Descrição --}}
        <div class="form-group">
            <label class="font-weight-bold">{{ __('Descrição') }} <span class="text-danger">*</span></label>
            <input type="text" name="descricao" class="form-control @error('descricao') is-invalid @enderror"
                   value="{{ old('descricao') }}" required
                   placeholder="{{ __('Ex.: Cimento CP II - 50kg, Pedreiro hora extra, Aluguel betoneira...') }}">
            @error('descricao')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="row">
            {{-- Quantidade --}}
            <div class="col-md-2 form-group">
                <label class="font-weight-bold">{{ __('Quantidade') }} <span class="text-danger">*</span></label>
                <input type="number" name="quantidade" id="qtd" class="form-control @error('quantidade') is-invalid @enderror"
                       step="0.001" min="0.001" value="{{ old('quantidade', 1) }}" required>
                @error('quantidade')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            {{-- Unidade --}}
            <div class="col-md-2 form-group">
                <label class="font-weight-bold">{{ __('Unidade') }}</label>
                <input type="text" name="unidade" id="unidade" class="form-control"
                       value="{{ old('unidade') }}" placeholder="un, kg, m²">
            </div>

            {{-- Custo unitário orçado --}}
            <div class="col-md-2 form-group">
                <label class="font-weight-bold">{{ __('Custo Unit. Orçado') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                    <input type="number" name="custo_unitario_orcado" id="custoUnitOrc"
                           class="form-control" step="0.01" min="0" value="{{ old('custo_unitario_orcado') }}">
                </div>
            </div>

            {{-- Custo unitário real --}}
            <div class="col-md-2 form-group">
                <label class="font-weight-bold">{{ __('Custo Unit. Real') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                    <input type="number" name="custo_unitario_real" id="custoUnitReal"
                           class="form-control @error('custo_unitario_real') is-invalid @enderror"
                           step="0.01" min="0" value="{{ old('custo_unitario_real') }}" required>
                    @error('custo_unitario_real')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Total calculado --}}
            <div class="col-md-2 form-group">
                <label class="font-weight-bold">{{ __('Total (R$)') }}</label>
                <div class="form-control bg-light text-success font-weight-bold" id="totalCalc">0,00</div>
            </div>
        </div>

        <div class="row">
            {{-- Fornecedor --}}
            <div class="col-md-4 form-group">
                <label class="font-weight-bold">{{ __('Fornecedor') }}</label>
                <input type="text" name="fornecedor" class="form-control"
                       value="{{ old('fornecedor') }}" placeholder="{{ __('Nome ou CNPJ') }}">
            </div>

            {{-- Nota Fiscal --}}
            <div class="col-md-3 form-group">
                <label class="font-weight-bold">{{ __('Nota Fiscal') }}</label>
                <input type="text" name="nota_fiscal" class="form-control"
                       value="{{ old('nota_fiscal') }}" placeholder="NF-e / número">
            </div>

            {{-- Data do Lançamento --}}
            <div class="col-md-5 form-group">
                <label class="font-weight-bold">{{ __('Data do Lançamento') }} <span class="text-danger">*</span></label>
                <input type="date" name="data_lancamento" class="form-control"
                       value="{{ old('data_lancamento', now()->toDateString()) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">{{ __('Observações') }}</label>
            <textarea name="observacoes" rows="2" class="form-control"
                      placeholder="{{ __('Detalhes adicionais, número do pedido, etc.') }}">{{ old('observacoes') }}</textarea>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <a href="{{ route('obras.show', $obra) }}" class="btn btn-outline-secondary mr-2">
                {{ __('Cancelar') }}
            </a>
            <button type="submit" class="btn btn-success px-4">
                <i class="fas fa-save mr-2"></i>{{ __('Salvar Lançamento') }}
            </button>
        </div>

        </form>
    </div>
</div>

</div>
</div>
@stop

@section('js')
<script>
document.getElementById('selectCategoria').addEventListener('change', function () {
    const catId = this.value;
    const sel = document.getElementById('selectSubcategoria');

    if (!catId) {
        sel.innerHTML = '<option value="">{{ __('Selecione a categoria primeiro') }}</option>';
        return;
    }

    fetch('/api/subcategorias?categoria_id=' + catId)
        .then(r => r.json())
        .then(function (data) {
            sel.innerHTML = '<option value="">— {{ __('Nenhuma') }} —</option>';
            data.forEach(function (s) {
                sel.innerHTML += `<option value="${s.id}">${s.nome}${s.unidade ? ' ('+s.unidade+')' : ''}</option>`;
            });
        });
});

document.getElementById('selectSubcategoria').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (opt && opt.text) {
        const match = opt.text.match(/\(([^)]+)\)$/);
        if (match) document.getElementById('unidade').value = match[1];
    }
});

function recalcular() {
    const qtd  = parseFloat(document.getElementById('qtd').value) || 0;
    const unit = parseFloat(document.getElementById('custoUnitReal').value) || 0;
    const total = qtd * unit;
    document.getElementById('totalCalc').textContent = total.toLocaleString('pt-BR', {minimumFractionDigits:2});
}
document.getElementById('qtd').addEventListener('input', recalcular);
document.getElementById('custoUnitReal').addEventListener('input', recalcular);
</script>
@stop
