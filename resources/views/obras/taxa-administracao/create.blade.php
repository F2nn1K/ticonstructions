@extends('adminlte::page')

@section('title', 'Gerar Taxa de Administração')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-family:'Playfair Display',serif;">
        <i class="fas fa-calculator mr-2" style="color:#C9A84C;"></i>
        Gerar Taxa de Administração
    </h1>
    <a href="{{ route('obras.taxa-administracao.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Voltar
    </a>
</div>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Preview dinâmico do cálculo -->
        <div id="previewCalculo" class="card mb-3" style="display:none!important;border-top:3px solid #C9A84C;">
            <div class="card-body">
                <h6 style="font-family:'Playfair Display',serif;color:#7D6A52;">
                    <i class="fas fa-chart-bar mr-2" style="color:#C9A84C;"></i>
                    Pré-visualização do Cálculo
                </h6>
                <div class="row text-center mt-3">
                    <div class="col-4">
                        <div style="font-size:.75rem;color:#7D6A52;text-transform:uppercase;letter-spacing:.5px;">Custo Base da Obra</div>
                        <div id="prvBase" style="font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:#0F0D0A;">—</div>
                    </div>
                    <div class="col-2 d-flex align-items-center justify-content-center">
                        <span style="font-size:1.5rem;color:#C9A84C;">×</span>
                    </div>
                    <div class="col-2">
                        <div style="font-size:.75rem;color:#7D6A52;text-transform:uppercase;letter-spacing:.5px;">Taxa</div>
                        <div id="prvPct" style="font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:#C9A84C;">—</div>
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">
                        <span style="font-size:1.5rem;color:#C9A84C;">=</span>
                    </div>
                    <div class="col-3">
                        <div style="font-size:.75rem;color:#7D6A52;text-transform:uppercase;letter-spacing:.5px;">Valor da Taxa</div>
                        <div id="prvTaxa" style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:#A8873A;">—</div>
                    </div>
                </div>
                <p class="text-muted text-center mt-2 mb-0" style="font-size:.8rem;">
                    Custo base exclui lançamentos marcados como "Taxa de Administração" para evitar circularidade.
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice-dollar mr-2" style="color:#C9A84C;"></i>
                    Nova Taxa de Administração
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('obras.taxa-administracao.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Obra <span class="text-danger">*</span></label>
                            <select name="obra_id" id="selectObra" class="form-control" required>
                                <option value="">— Selecionar obra —</option>
                                @foreach($obras as $obra)
                                <option value="{{ $obra->id }}" {{ old('obra_id') == $obra->id ? 'selected':'' }}>
                                    {{ $obra->nome }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Administrador <span class="text-danger">*</span></label>
                            <select name="administrador_id" id="selectAdmin" class="form-control" required>
                                <option value="">— Selecionar administrador —</option>
                                @foreach($administradores as $adm)
                                <option value="{{ $adm->id }}"
                                        data-pct="{{ $adm->percentual_taxa }}"
                                        {{ old('administrador_id') == $adm->id ? 'selected':'' }}>
                                    {{ $adm->nome }} ({{ number_format($adm->percentual_taxa,2,',','.') }}%)
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">
                                Percentual (%)
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="percentual" id="inputPct" step="0.01" min="0" max="100"
                                       class="form-control"
                                       value="{{ old('percentual', '10.00') }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text" style="background:rgba(201,168,76,.1);color:#A8873A;font-weight:700;">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Data de Referência <span class="text-danger">*</span></label>
                            <input type="date" name="data_referencia" class="form-control"
                                   value="{{ old('data_referencia', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Data de Vencimento</label>
                            <input type="date" name="data_vencimento" class="form-control"
                                   value="{{ old('data_vencimento') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">Descrição</label>
                        <input type="text" name="descricao" class="form-control"
                               value="{{ old('descricao') }}"
                               placeholder="Ex: Parcela 1 — Abril 2026">
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="2"
                                  placeholder="Notas adicionais...">{{ old('observacoes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('obras.taxa-administracao.index') }}" class="btn btn-secondary mr-2">{{ __('Cancelar') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Gerar Taxa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
const selectObra  = document.getElementById('selectObra');
const selectAdmin = document.getElementById('selectAdmin');
const inputPct    = document.getElementById('inputPct');
const preview     = document.getElementById('previewCalculo');

function fmt(v) {
    return 'R$ ' + parseFloat(v).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function calcPreview() {
    const obraId  = selectObra.value;
    const adminId = selectAdmin.value;
    const pct     = parseFloat(inputPct.value) || 0;

    if (!obraId || !adminId) { preview.style.display = 'none'; return; }

    fetch(`{{ url('/obras/taxa-administracao/calcular-preview') }}?obra_id=${obraId}&administrador_id=${adminId}&percentual=${pct}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('prvBase').textContent  = fmt(data.custo_base);
            document.getElementById('prvPct').textContent   = data.percentual + '%';
            document.getElementById('prvTaxa').textContent  = fmt(data.valor_taxa);
            preview.style.removeProperty('display');
            preview.style.display = 'block';
        });
}

// Quando selecionar administrador, preenche o percentual padrão
selectAdmin.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.pct) { inputPct.value = opt.dataset.pct; }
    calcPreview();
});

selectObra.addEventListener('change', calcPreview);
inputPct.addEventListener('input', calcPreview);
</script>
@endpush
