@extends('adminlte::page')
@section('title', __('Lançar Custo'))
@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('gastos.index') }}" class="btn btn-sm btn-outline-secondary mr-3"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="mb-0"><i class="fas fa-plus-circle mr-2" style="color:var(--ti-gold)"></i>{{ __('Lançar Custo') }}</h1>
        <small class="text-muted">{{ __('Registrar novo lançamento financeiro') }}</small>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
<style>
.modo-btn { border:2px solid #ddd;border-radius:8px;padding:7px 14px;font-size:.8rem;font-weight:700;background:#fff;color:#777;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:6px; }
.modo-btn:hover { border-color:#A8873A;color:#A8873A; }
.modo-btn.ativo { border-color:#A8873A;background:#A8873A;color:#fff; }
.modo-btn.ativo-blue { border-color:#1A9E6E;background:#1A9E6E;color:#fff; }
.modo-btn.ativo-red { border-color:#C94040;background:#C94040;color:#fff; }
.bloco-card { border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07);margin-bottom:1rem; }
.bloco-header { padding:10px 16px;border-radius:10px 10px 0 0;border-bottom:1px solid #eee;background:#f7f5f0; }
.bloco-header h6 { margin:0;font-size:.82rem;font-weight:700;color:#6a6259; }

/* Select2 */
.select2-container--bootstrap .select2-selection { border-color: #ced4da; min-height: 38px; }
.select2-container--bootstrap .select2-selection--single .select2-selection__rendered { padding-top: 4px; }
.select2-container--bootstrap .select2-selection--single .select2-selection__arrow { height: 36px; }

/* Dropdown grande e confortável */
.s2-dropdown-lg { min-width: 320px !important; }
.s2-dropdown-lg .select2-results__option { padding: 8px 14px; font-size: .9rem; }
.s2-dropdown-lg .select2-search--dropdown .select2-search__field { padding: 6px 10px; font-size: .9rem; border-radius: 4px; }
.s2-dropdown-lg .select2-results { max-height: 280px; }
</style>
@stop

@section('content')
<div class="row justify-content-center">
<div class="col-md-11">

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif
@if($errors->any())
<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('gastos.store') }}" id="formLanc">
@csrf
<input type="hidden" name="modo_lancamento" id="hiddenModo" value="{{ old('modo_lancamento','por_unidade') }}">

{{-- ── 1. Obra ────────────────────────────────────────────────────── --}}
<div class="card bloco-card">
    <div class="bloco-header" style="background:var(--ti-gold-gradient,linear-gradient(135deg,#A8873A,#E2C87A))">
        <h6 style="color:#fff"><i class="fas fa-hard-hat mr-2"></i>{{ __('Obra') }}</h6>
    </div>
    <div class="card-body pb-2">
        <div class="form-group mb-0">
            <select name="obra_id" id="selectObra" class="form-control @error('obra_id') is-invalid @enderror" required>
                <option value="">{{ __('Selecione a obra...') }}</option>
                @foreach($obras as $ob)
                <option value="{{ $ob->id }}" {{ (old('obra_id', $obraSel?->id) == $ob->id) ? 'selected' : '' }}>{{ $ob->nome }}</option>
                @endforeach
            </select>
            @error('obra_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            <div id="faseInfo" class="mt-1 small text-info" style="display:none">
                <i class="fas fa-info-circle mr-1"></i>{{ __('Lançamento vinculado automaticamente à fase ativa da obra.') }}
            </div>
        </div>
    </div>
</div>

{{-- ── 2. Categoria, Tipo e Modo ──────────────────────────────────── --}}
<div class="card bloco-card">
    <div class="bloco-header"><h6><i class="fas fa-tags mr-2"></i>{{ __('Categoria e Tipo de Lançamento') }}</h6></div>
    <div class="card-body pb-2">
        <div class="row align-items-end">
            {{-- Categoria --}}
            <div class="col-md-3 form-group mb-2">
                <label class="font-weight-bold small">{{ __('Categoria') }} <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center" style="gap:4px">
                    <div style="flex:1;min-width:0">
                        <select name="categoria_id" id="selectCategoria" class="form-control @error('categoria_id') is-invalid @enderror" required>
                            <option value="">{{ __('Selecione...') }}</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" data-nome="{{ strtolower($cat->nome) }}" {{ old('categoria_id')==$cat->id?'selected':'' }}>{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" style="height:38px;width:34px;padding:0" data-toggle="modal" data-target="#modalNovaCategoria" title="{{ __('Nova Categoria') }}"><i class="fas fa-plus"></i></button>
                </div>
                @error('categoria_id')<span class="text-danger small">{{ $message }}</span>@enderror
            </div>

            {{-- Subcategoria --}}
            <div class="col-md-3 form-group mb-2">
                <label class="font-weight-bold small">{{ __('Subcategoria') }}</label>
                <div class="d-flex align-items-center" style="gap:4px">
                    <div style="flex:1;min-width:0">
                        <select name="subcategoria_id" id="selectSubcategoria" class="form-control">
                            <option value="">{{ __('Selecione a categoria primeiro') }}</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" style="height:38px;width:34px;padding:0" id="btnNovaSubcateg" disabled data-toggle="modal" data-target="#modalNovaSubcategoria" title="{{ __('Nova Subcategoria') }}"><i class="fas fa-plus"></i></button>
                </div>
            </div>

            {{-- Tipo --}}
            <div class="col-md-2 form-group mb-2">
                <label class="font-weight-bold small">{{ __('Tipo') }} <span class="text-danger">*</span></label>
                <select name="tipo" id="selectTipo" class="form-control @error('tipo') is-invalid @enderror" required>
                    <option value="material"    {{ old('tipo','material')=='material'    ?'selected':'' }}>{{ __('Material') }}</option>
                    <option value="servico"     {{ old('tipo')=='servico'     ?'selected':'' }}>{{ __('Serviço') }}</option>
                    <option value="mao_de_obra" {{ old('tipo')=='mao_de_obra' ?'selected':'' }}>{{ __('Mão de Obra') }}</option>
                    <option value="equipamento" {{ old('tipo')=='equipamento' ?'selected':'' }}>{{ __('Equipamento') }}</option>
                    <option value="terceiro"    {{ old('tipo')=='terceiro'    ?'selected':'' }}>{{ __('Terceiro') }}</option>
                </select>
            </div>

            {{-- Código produto --}}
            <div class="col-md-4 form-group mb-2">
                <label class="font-weight-bold small">{{ __('Cód. Produto') }}</label>
                <input type="text" name="produto_codigo" class="form-control" value="{{ old('produto_codigo') }}" placeholder="Ex: CIM-CP2-50KG" style="text-transform:uppercase">
            </div>
        </div>

        {{-- Descrição --}}
        <div class="form-group mb-2">
            <label class="font-weight-bold small">{{ __('Descrição / Especificação') }} <span class="text-danger">*</span></label>
            <input type="text" name="descricao" class="form-control @error('descricao') is-invalid @enderror"
                   value="{{ old('descricao') }}" required
                   placeholder="{{ __('Ex: Cimento CP II 50kg, Pedreiro hora extra, Empreitada alvenaria...') }}">
            @error('descricao')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        {{-- SELETOR DE MODO --}}
        <div id="blocoModo" class="mt-1 mb-1">
            <div style="font-size:.73rem;font-weight:700;text-transform:uppercase;color:#999;margin-bottom:6px">
                {{ __('Modo de lançamento') }}
            </div>
            <div id="modosMaoObra" style="display:none;gap:6px;flex-wrap:wrap" class="d-flex">
                <button type="button" class="modo-btn" data-modo="por_hora" onclick="setModo('por_hora')">
                    <i class="fas fa-clock"></i> {{ __('Por Hora') }}
                </button>
                <button type="button" class="modo-btn" data-modo="salario" onclick="setModo('salario')">
                    <i class="fas fa-calendar-alt"></i> {{ __('Salário Fixo') }}
                </button>
                <button type="button" class="modo-btn" data-modo="empreitada" onclick="setModo('empreitada')">
                    <i class="fas fa-hammer"></i> {{ __('Empreitada') }}
                </button>
            </div>
            <div id="modosGeral" style="gap:6px;flex-wrap:wrap" class="d-flex">
                <button type="button" class="modo-btn" data-modo="por_unidade" onclick="setModo('por_unidade')">
                    <i class="fas fa-ruler-combined"></i> {{ __('Por Unidade (qtd × preço)') }}
                </button>
                <button type="button" class="modo-btn" data-modo="valor_total" onclick="setModo('valor_total')">
                    <i class="fas fa-dollar-sign"></i> {{ __('Valor Total Direto') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── 3. Valores ───────────────────────────────────────────────────── --}}
<div class="card bloco-card">
    <div class="bloco-header"><h6 id="tituloBloco3"><i class="fas fa-calculator mr-2"></i>{{ __('Quantidade e Valores') }}</h6></div>
    <div class="card-body pb-2">

        <div id="blocoComMedida">
            <div class="row align-items-end">
                <div class="col-md-2 form-group">
                    <label class="font-weight-bold small" id="lblQtd">{{ __('Quantidade') }} <span class="text-danger">*</span></label>
                    <input type="number" name="quantidade" id="qtd" class="form-control @error('quantidade') is-invalid @enderror"
                           step="0.001" min="0.001" value="{{ old('quantidade', 1) }}">
                </div>
                <div class="col-md-2 form-group">
                    <label class="font-weight-bold small" id="lblUnidade">{{ __('Unidade') }}</label>
                    <input type="text" name="unidade" id="unidade" class="form-control" value="{{ old('unidade') }}" placeholder="un, kg, m², h">
                </div>
                <div class="col-md-2 form-group">
                    <label class="font-weight-bold small">{{ __('Unit. Orçado') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text" style="font-size:.75rem">R$</span></div>
                        <input type="number" name="custo_unitario_orcado" id="unitOrc" class="form-control" step="0.01" min="0" value="{{ old('custo_unitario_orcado') }}">
                    </div>
                </div>
                <div class="col-md-2 form-group">
                    <label class="font-weight-bold small" id="lblUnitReal">{{ __('Unit. Real') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text" style="font-size:.75rem">R$</span></div>
                        <input type="number" name="custo_unitario_real" id="custoUnitReal" class="form-control @error('custo_unitario_real') is-invalid @enderror" step="0.01" min="0" value="{{ old('custo_unitario_real') }}">
                        @error('custo_unitario_real')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-2 form-group">
                    <label class="font-weight-bold small">{{ __('Total') }}</label>
                    <div class="form-control bg-light text-success font-weight-bold text-right" id="totalCalc">R$ 0,00</div>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <div class="custom-control custom-switch ml-1 pb-1">
                        <input type="checkbox" class="custom-control-input" id="excluirTaxaAdm" name="excluir_base_taxa_admin" value="1" {{ old('excluir_base_taxa_admin') ? 'checked':'' }}>
                        <label class="custom-control-label" for="excluirTaxaAdm" style="font-size:.75rem;line-height:1.3;padding-top:1px">
                            {{ __('Excluir taxa ADM') }}
                            <i class="fas fa-info-circle text-muted" title="{{ __('Marque para custos de repasse que não entram na base da taxa de administração.') }}"></i>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div id="blocoValorDireto" style="display:none">
            <div class="row align-items-end">
                <div class="col-md-3 form-group">
                    <label class="font-weight-bold" id="lblValorOrc">{{ __('Valor Orçado') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                        <input type="number" name="valor_total_orcado" id="valorOrc" class="form-control" step="0.01" min="0" value="{{ old('valor_total_orcado') }}" placeholder="0,00">
                    </div>
                </div>
                <div class="col-md-3 form-group">
                    <label class="font-weight-bold" id="lblValorReal">{{ __('Valor Real') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                        <input type="number" name="valor_total_direto" id="valorDireto" class="form-control @error('valor_total_direto') is-invalid @enderror" step="0.01" min="0" value="{{ old('valor_total_direto') }}" placeholder="0,00">
                        @error('valor_total_direto')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-3 form-group d-flex align-items-end">
                    <div class="custom-control custom-switch ml-1 pb-1">
                        <input type="checkbox" class="custom-control-input" id="excluirTaxaAdm2" name="excluir_base_taxa_admin" value="1" {{ old('excluir_base_taxa_admin') ? 'checked':'' }}>
                        <label class="custom-control-label" for="excluirTaxaAdm2" style="font-size:.75rem;line-height:1.3;padding-top:1px">
                            {{ __('Excluir taxa ADM') }}
                        </label>
                    </div>
                </div>
                <div class="col-md-3 form-group d-flex align-items-end">
                    <div id="badgeModo" class="badge badge-secondary" style="font-size:.75rem;padding:6px 10px;border-radius:20px"></div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── 4. Fornecedor e Pagamento ──────────────────────────────────── --}}
<div class="card bloco-card">
    <div class="bloco-header"><h6><i class="fas fa-truck mr-2"></i>{{ __('Fornecedor e Pagamento') }}</h6></div>
    <div class="card-body pb-2">
        <div class="row">
            <div class="col-md-5 form-group">
                <label class="font-weight-bold small">{{ __('Fornecedor Cadastrado') }}</label>
                <div class="d-flex" style="gap:4px">
                    <select name="fornecedor_id" id="selectFornecedor" class="form-control" style="flex:1;min-width:0">
                        <option value="">{{ __('Selecione ou digite abaixo...') }}</option>
                        @foreach($fornecedores as $forn)
                        <option value="{{ $forn->id }}" {{ old('fornecedor_id')==$forn->id?'selected':'' }}>
                            {{ $forn->nome_fantasia ?: $forn->razao_social }}
                        </option>
                        @endforeach
                    </select>
                    <a href="{{ route('fornecedores.create') }}" target="_blank" class="btn btn-outline-primary btn-sm flex-shrink-0" style="height:38px;width:34px;padding:0;display:flex;align-items:center;justify-content:center" title="{{ __('Cadastrar Novo') }}"><i class="fas fa-plus"></i></a>
                </div>
                <small class="text-muted">{{ __('Para relatório de comparação de preços.') }}</small>
            </div>
            <div class="col-md-3 form-group">
                <label class="font-weight-bold small">{{ __('Fornecedor (livre)') }}</label>
                <input type="text" name="fornecedor" id="fornecedorTexto" class="form-control" value="{{ old('fornecedor') }}" placeholder="{{ __('Nome ou CNPJ') }}">
            </div>
            <div class="col-md-2 form-group">
                <label class="font-weight-bold small">{{ __('Nota Fiscal') }}</label>
                <input type="text" name="nota_fiscal" class="form-control" value="{{ old('nota_fiscal') }}" placeholder="NF-e / número">
            </div>
            <div class="col-md-2 form-group">
                <label class="font-weight-bold small">{{ __('Data do Lançamento') }} <span class="text-danger">*</span></label>
                <input type="date" name="data_lancamento" class="form-control @error('data_lancamento') is-invalid @enderror"
                       value="{{ old('data_lancamento', now()->toDateString()) }}" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 form-group mb-2">
                <label class="font-weight-bold small">{{ __('Previsão de Pagamento') }}</label>
                <input type="date" name="data_prevista_pagamento" class="form-control" value="{{ old('data_prevista_pagamento') }}">
            </div>
            <div class="col-md-9 form-group mb-2">
                <label class="font-weight-bold small">{{ __('Observações') }}</label>
                <input type="text" name="observacoes" class="form-control" value="{{ old('observacoes') }}"
                       placeholder="{{ __('Detalhes adicionais, número do pedido, etc.') }}">
            </div>
        </div>
    </div>
</div>

{{-- Botões --}}
<div class="d-flex justify-content-end mb-4">
    <a href="{{ route('gastos.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Cancelar') }}</a>
    <button type="submit" name="action" value="save" class="btn btn-success px-4">
        <i class="fas fa-save mr-2"></i>{{ __('Salvar Lançamento') }}
    </button>
    <button type="submit" name="action" value="save_new" class="btn btn-primary ml-2 px-4">
        <i class="fas fa-plus mr-2"></i>{{ __('Salvar e Lançar Outro') }}
    </button>
</div>

</form>
</div>
</div>

{{-- ── Modal Nova Categoria ─────────────────────────────────────────── --}}
<div class="modal fade" id="modalNovaCategoria" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:12px">
            <div class="modal-header border-0 pb-0"><h6 class="modal-title font-weight-bold"><i class="fas fa-tag mr-2" style="color:#A8873A"></i>{{ __('Nova Categoria') }}</h6><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <div class="form-group mb-2"><label class="small font-weight-bold">{{ __('Nome') }} *</label><input type="text" id="novaCategoriaNome" class="form-control form-control-sm" placeholder="Ex: Elétrica, Hidráulica..."></div>
                <div class="form-group mb-0"><label class="small font-weight-bold">{{ __('Tipo') }}</label><select id="novaCategoriaTipo" class="form-control form-control-sm"><option value="ambos">{{ __('Ambos') }}</option><option value="material">{{ __('Material') }}</option><option value="servico">{{ __('Serviço') }}</option></select></div>
            </div>
            <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">{{ __('Cancelar') }}</button><button type="button" class="btn btn-primary btn-sm" id="btnSalvarCategoria"><i class="fas fa-save mr-1"></i>{{ __('Salvar') }}</button></div>
        </div>
    </div>
</div>

{{-- ── Modal Nova Subcategoria ──────────────────────────────────────── --}}
<div class="modal fade" id="modalNovaSubcategoria" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:12px">
            <div class="modal-header border-0 pb-0"><h6 class="modal-title font-weight-bold"><i class="fas fa-tag mr-2" style="color:#A8873A"></i>{{ __('Nova Subcategoria') }}</h6><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <div id="msgSubCategPai" class="small text-muted mb-2"></div>
                <div class="form-group mb-2"><label class="small font-weight-bold">{{ __('Nome') }} *</label><input type="text" id="novaSubcategNome" class="form-control form-control-sm"></div>
                <div class="form-group mb-0"><label class="small font-weight-bold">{{ __('Unidade') }}</label><input type="text" id="novaSubcategUnidade" class="form-control form-control-sm" placeholder="kg, m², un, h..."></div>
            </div>
            <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">{{ __('Cancelar') }}</button><button type="button" class="btn btn-primary btn-sm" id="btnSalvarSubcategoria"><i class="fas fa-save mr-1"></i>{{ __('Salvar') }}</button></div>
        </div>
    </div>
</div>

@stop
@section('js')
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
<script>
// ── Dados de subcategorias por categoria
var subcatsData = @json($categorias->mapWithKeys(fn($c) => [$c->id => $c->subcategorias->map(fn($s) => ['id'=>$s->id,'nome'=>$s->nome,'unidade'=>$s->unidade])]));

// ── Labels para cada modo
var MODOS = {
    'por_unidade': { label: 'Por Unidade',   lblQtd: 'Quantidade', lblUnidade: 'Unidade',   lblUnitReal: 'Unit. Real', icone: 'fas fa-ruler-combined', cor: 'secondary' },
    'por_hora':    { label: 'Por Hora',       lblQtd: 'Horas',      lblUnidade: 'h',          lblUnitReal: 'R$/hora',    icone: 'fas fa-clock',          cor: 'info'      },
    'salario':     { label: 'Salário Fixo',   lblQtd: null,         lblUnidade: null,         lblUnitReal: null,         icone: 'fas fa-calendar-alt',   cor: 'success'   },
    'empreitada':  { label: 'Empreitada',     lblQtd: null,         lblUnidade: null,         lblUnitReal: null,         icone: 'fas fa-hammer',         cor: 'warning'   },
    'valor_total': { label: 'Valor Total',    lblQtd: null,         lblUnidade: null,         lblUnitReal: null,         icone: 'fas fa-dollar-sign',    cor: 'dark'      },
};

var modoAtual = document.getElementById('hiddenModo').value || 'por_unidade';

function setModo(modo) {
    modoAtual = modo;
    document.getElementById('hiddenModo').value = modo;

    var modoInfo = MODOS[modo] || MODOS['por_unidade'];
    var valorDireto = (modo === 'salario' || modo === 'empreitada' || modo === 'valor_total');

    document.getElementById('blocoComMedida').style.display  = valorDireto ? 'none' : 'block';
    document.getElementById('blocoValorDireto').style.display = valorDireto ? 'block' : 'none';

    document.getElementById('qtd').required = !valorDireto;
    document.getElementById('custoUnitReal').required = !valorDireto;
    var vd = document.getElementById('valorDireto');
    if (vd) vd.required = valorDireto;

    if (modo === 'por_hora') {
        document.getElementById('lblQtd').innerHTML = 'Horas <span class="text-danger">*</span>';
        document.getElementById('unidade').value = 'h';
        document.getElementById('unidade').readOnly = true;
        document.getElementById('lblUnitReal').innerHTML = 'R$/hora <span class="text-danger">*</span>';
    } else {
        document.getElementById('lblQtd').innerHTML = 'Quantidade <span class="text-danger">*</span>';
        document.getElementById('unidade').readOnly = false;
        if (!document.getElementById('unidade').value || document.getElementById('unidade').value === 'h') {
            document.getElementById('unidade').value = '';
        }
        document.getElementById('lblUnitReal').innerHTML = 'Unit. Real <span class="text-danger">*</span>';
    }

    var badge = document.getElementById('badgeModo');
    if (badge) badge.innerHTML = '<i class="' + modoInfo.icone + ' mr-1"></i>' + modoInfo.label;

    var t = document.getElementById('tituloBloco3');
    if (t) t.innerHTML = '<i class="fas fa-calculator mr-2"></i>' +
        (valorDireto ? 'Valor do Lançamento' : 'Quantidade e Valores');

    document.querySelectorAll('.modo-btn').forEach(function(b) {
        b.classList.remove('ativo','ativo-blue','ativo-red');
    });
    var btn = document.querySelector('.modo-btn[data-modo="' + modo + '"]');
    if (btn) {
        if (modo === 'por_hora') btn.classList.add('ativo-blue');
        else if (modo === 'empreitada') btn.classList.add('ativo-red');
        else btn.classList.add('ativo');
    }

    recalcular();
}

function atualizarModosBotoes() {
    var tipo = document.getElementById('selectTipo').value;
    var isMaoObra = (tipo === 'mao_de_obra');
    document.getElementById('modosMaoObra').style.display = isMaoObra ? 'flex' : 'none';
    document.getElementById('modosGeral').style.display   = isMaoObra ? 'none' : 'flex';

    if (isMaoObra && (modoAtual === 'por_unidade' || modoAtual === 'valor_total')) {
        setModo('por_hora');
    }
    if (!isMaoObra && (modoAtual === 'por_hora' || modoAtual === 'salario' || modoAtual === 'empreitada')) {
        setModo('por_unidade');
    }
}

document.getElementById('selectTipo').addEventListener('change', atualizarModosBotoes);

// ── Select2 em Categoria e Subcategoria
function initSelect2Subcategoria() {
    if ($('#selectSubcategoria').hasClass('select2-hidden-accessible')) {
        $('#selectSubcategoria').select2('destroy');
    }
    $('#selectSubcategoria').select2({
        theme: 'bootstrap',
        placeholder: '-- Nenhuma --',
        allowClear: true,
        language: {
            noResults: function() { return 'Nenhuma subcategoria encontrada'; },
            searching: function() { return 'Buscando...'; }
        },
        width: '100%',
        dropdownAutoWidth: true,
        dropdownCssClass: 's2-dropdown-lg'
    });

    $('#selectSubcategoria').on('select2:select select2:clear', function() {
        this.dispatchEvent(new Event('change'));
    });
}

$(function() {
    $('#selectCategoria').select2({
        theme: 'bootstrap',
        placeholder: '{{ __("Selecione...") }}',
        allowClear: true,
        language: {
            noResults: function() { return 'Nenhuma categoria encontrada'; },
            searching: function() { return 'Buscando...'; }
        },
        width: '100%',
        dropdownAutoWidth: true,
        dropdownCssClass: 's2-dropdown-lg'
    });

    $('#selectCategoria').on('select2:select select2:clear', function() {
        this.dispatchEvent(new Event('change'));
    });

    initSelect2Subcategoria();

    @if(old('categoria_id'))
    $('#selectCategoria').val('{{ old("categoria_id") }}').trigger('change');
    @endif
});

// ── Carregar subcategorias
document.getElementById('selectCategoria').addEventListener('change', function() {
    var catId = this.value;
    var btn   = document.getElementById('btnNovaSubcateg');
    btn.disabled = !catId;

    if ($('#selectSubcategoria').hasClass('select2-hidden-accessible')) {
        $('#selectSubcategoria').select2('destroy');
    }

    var sel = document.getElementById('selectSubcategoria');
    if (!catId) {
        sel.innerHTML = '<option value="">{{ __("Selecione a categoria primeiro") }}</option>';
    } else {
        var subs = subcatsData[catId] || [];
        sel.innerHTML = '<option value="">-- {{ __("Nenhuma") }} --</option>';
        subs.forEach(function(s) {
            sel.innerHTML += '<option value="' + s.id + '">' + s.nome + (s.unidade ? ' (' + s.unidade + ')' : '') + '</option>';
        });
    }

    initSelect2Subcategoria();
});

// ── Preencher unidade ao escolher subcategoria
document.getElementById('selectSubcategoria').addEventListener('change', function() {
    var txt = $('#selectSubcategoria').find(':selected').text() || '';
    var match = txt.match(/\(([^)]+)\)$/);
    if (match && modoAtual !== 'por_hora') document.getElementById('unidade').value = match[1];
});

// ── Calcular total
function recalcular() {
    if (modoAtual === 'salario' || modoAtual === 'empreitada' || modoAtual === 'valor_total') return;
    var qtd  = parseFloat(document.getElementById('qtd').value) || 0;
    var unit = parseFloat(document.getElementById('custoUnitReal').value) || 0;
    document.getElementById('totalCalc').textContent = 'R$ ' + (qtd * unit).toLocaleString('pt-BR', {minimumFractionDigits:2});
}
document.getElementById('qtd').addEventListener('input', recalcular);
document.getElementById('custoUnitReal').addEventListener('input', recalcular);

// ── Código produto uppercase
document.querySelector('[name="produto_codigo"]').addEventListener('input', function() { this.value = this.value.toUpperCase(); });

// ── Obra info
document.getElementById('selectObra').addEventListener('change', function() {
    document.getElementById('faseInfo').style.display = this.value ? 'block' : 'none';
});
(function() { if (document.getElementById('selectObra').value) document.getElementById('faseInfo').style.display = 'block'; })();

// ── Fornecedor cadastrado → preencher placeholder no campo livre
document.getElementById('selectFornecedor').addEventListener('change', function() {
    var nome = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
    document.getElementById('fornecedorTexto').placeholder = this.value ? nome : '{{ __("Nome ou CNPJ") }}';
});

// ── Criar categoria via AJAX
document.getElementById('btnSalvarCategoria').addEventListener('click', function() {
    var nome = document.getElementById('novaCategoriaNome').value.trim();
    var tipo = document.getElementById('novaCategoriaTipo').value;
    if (!nome) { alert('{{ __("Informe o nome") }}'); return; }
    fetch('{{ route("api.categorias.store") }}', {
        method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({nome:nome,tipo:tipo})
    }).then(r=>r.json()).then(function(data) {
        var opt = new Option(data.nome, data.id, true, true);
        $('#selectCategoria').append(opt).trigger('change');
        document.getElementById('selectCategoria').dispatchEvent(new Event('change'));
        $('#modalNovaCategoria').modal('hide');
        document.getElementById('novaCategoriaNome').value='';
    });
});

// ── Criar subcategoria via AJAX
document.getElementById('btnSalvarSubcategoria').addEventListener('click', function() {
    var catId=document.getElementById('selectCategoria').value;
    var nome=document.getElementById('novaSubcategNome').value.trim();
    var unid=document.getElementById('novaSubcategUnidade').value.trim();
    if (!nome) { alert('{{ __("Informe o nome") }}'); return; }
    fetch('{{ route("api.subcategorias.store") }}', {
        method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({categoria_id:catId,nome:nome,unidade:unid})
    }).then(r=>r.json()).then(function(data) {
        var label = data.nome+(data.unidade?' ('+data.unidade+')':'');
        var opt = new Option(label, data.id, true, true);
        $('#selectSubcategoria').append(opt).trigger('change');
        document.getElementById('selectSubcategoria').dispatchEvent(new Event('change'));
        if (data.unidade && modoAtual !== 'por_hora') document.getElementById('unidade').value = data.unidade;
        $('#modalNovaSubcategoria').modal('hide');
        document.getElementById('novaSubcategNome').value='';
        document.getElementById('novaSubcategUnidade').value='';
    });
});

document.getElementById('btnNovaSubcateg').addEventListener('click', function() {
    var texto = $('#selectCategoria').find(':selected').text() || '';
    document.getElementById('msgSubCategPai').textContent='{{ __("Categoria") }}: ' + texto;
});

// ── Inicializar com modo salvo
(function() {
    var modo = '{{ old("modo_lancamento","por_unidade") }}';
    var tipo = document.getElementById('selectTipo').value;
    var isMaoObra = (tipo === 'mao_de_obra');
    document.getElementById('modosMaoObra').style.display = isMaoObra ? 'flex' : 'none';
    document.getElementById('modosGeral').style.display   = isMaoObra ? 'none' : 'flex';
    setModo(modo);
})();
</script>
@stop
