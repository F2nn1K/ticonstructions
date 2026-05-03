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

@section('plugins.Sweetalert2', true)
@section('css')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
<style>
.modo-btn { border:2px solid #ddd;border-radius:8px;padding:7px 14px;font-size:.8rem;font-weight:700;background:#fff;color:#777;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:6px; }
.modo-btn:hover { border-color:#A8873A;color:#A8873A; }
.modo-btn.ativo { border-color:#A8873A;background:#A8873A;color:#fff; }
.modo-btn.ativo-blue { border-color:#1A9E6E;background:#1A9E6E;color:#fff; }
.modo-btn.ativo-red { border-color:#C94040;background:#C94040;color:#fff; }
.bloco-card { border-radius:10px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07);margin-bottom:1rem;overflow:visible !important; }
.bloco-card .card-body { overflow:visible !important; }
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

/* Autocomplete catálogo de insumo (por obra) */
#wrapCatalogoDesc { position: relative; }
#dropdownCatalogoProduto {
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    z-index: 1055;
    margin-top: 2px;
    max-height: 260px;
    overflow-y: auto;
    border-radius: 8px;
    border: 1px solid rgba(168,135,58,.35);
    background: #fff;
    box-shadow: 0 8px 28px rgba(0,0,0,.14);
}
#dropdownCatalogoProduto button.list-group-item {
    border-radius: 0;
    cursor: pointer;
    font-size: .88rem;
    border-left: none;
    border-right: none;
    text-align: left;
}
#dropdownCatalogoProduto button.list-group-item:first-child { border-top: none; }
#dropdownCatalogoProduto button.list-group-item-action:hover {
    background: rgba(168,135,58,.12);
}
#dropdownCatalogoProduto .cat-nuevo-insumo .cat-plus {
    color: #198754;
    font-weight: 700;
}

/* Fluxo lançamento → valores */
.secao-valores-highlight {
    transition: box-shadow .35s ease, border-color .35s ease;
}
.secao-valores-highlight.ativo {
    box-shadow: 0 8px 28px rgba(168,135,58,.28) !important;
    border: 2px solid #A8873A !important;
}
#resumoAntesValores { border-radius: 10px; }
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

{{-- ── 2. Fornecedor e Pagamento (logo após Obra) ───────────────────── --}}
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
                    <button type="button" class="btn btn-outline-primary btn-sm flex-shrink-0" style="height:38px;width:34px;padding:0;display:flex;align-items:center;justify-content:center" data-toggle="modal" data-target="#modalNovoFornecedor" title="{{ __('Cadastrar Novo') }}"><i class="fas fa-plus"></i></button>
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
            <div class="col-md-12 form-group mb-2">
                <label class="font-weight-bold small">{{ __('Observações') }}</label>
                <input type="text" name="observacoes" class="form-control" value="{{ old('observacoes') }}"
                       placeholder="{{ __('Detalhes adicionais, número do pedido, etc.') }}">
            </div>
        </div>
    </div>
</div>

{{-- ── 3. Categoria, Tipo e Modo ──────────────────────────────────── --}}
<div class="card bloco-card" id="secaoProdutoTipo">
    <div class="bloco-header"><h6><i class="fas fa-tags mr-2"></i>{{ __('Categoria e Tipo de Lançamento') }}</h6></div>
    <div class="card-body pb-2">
        {{-- Descrição (primeiro) — mesmo texto no histórico preenche categoria/unidade --}}
        <div class="form-group mb-2">
            <label class="font-weight-bold small">{{ __('Descrição / Especificação') }} <span class="text-danger">*</span></label>
            <div id="wrapCatalogoDesc">
                <input type="text" name="descricao" id="campoDescricao" class="form-control @error('descricao') is-invalid @enderror"
                       value="{{ old('descricao') }}" required autocomplete="off"
                       placeholder="{{ __('Ex: Cimento CP II 50kg, Pedreiro hora extra, Empreitada alvenaria...') }}">
                <div id="dropdownCatalogoProduto" class="list-group" role="listbox" style="display:none"></div>
            </div>
            <small class="text-muted" style="font-size:.72rem">{{ __('Descrições já usadas nesta obra aparecem ao digitar. Use Cadastrar (novo insumo) com obra, categoria e tipo preenchidos para gravar categoria, subcategoria, tipo e unidade; depois escolha o item na lista (pode alterar antes de salvar o lançamento).') }}</small>
            @error('descricao')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

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

            {{-- Quantidade e Unidade (junto ao produto) --}}
            <div class="col-md-2 form-group mb-2">
                <label class="font-weight-bold small" id="lblQtd">{{ __('Quantidade') }} <span class="text-danger">*</span></label>
                <input type="number" name="quantidade" id="qtd" class="form-control @error('quantidade') is-invalid @enderror"
                       step="0.001" min="0.001" value="{{ old('quantidade', 1) }}">
            </div>
            <div class="col-md-2 form-group mb-2">
                <label class="font-weight-bold small" id="lblUnidade">{{ __('Unidade') }}</label>
                <input type="text" name="unidade" id="unidade" class="form-control" value="{{ old('unidade') }}" placeholder="un, kg, m², h">
            </div>
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

        {{-- Confirmar dados do item e enviar usuário ao bloco de valores --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 pt-3 border-top">
            <p class="small text-muted mb-2 mb-md-0 mr-md-3 flex-grow-1" id="hintLancarValores">{{ __('Confira descrição, categoria e modo. Clique em Lançar para preencher o valor logo abaixo.') }}</p>
            <button type="button" class="btn font-weight-bold px-4 align-self-md-end shadow-sm text-white flex-shrink-0" id="btnLancarParaValores"
                    style="background:linear-gradient(135deg,#1A9E6E,#2abf8f);border:none;border-radius:8px">
                <i class="fas fa-level-down-alt mr-2"></i>{{ __('Lançar') }}
            </button>
        </div>
    </div>
</div>

{{-- ── 4. Valores ───────────────────────────────────────────────────── --}}
<div class="card bloco-card secao-valores-highlight" id="secaoValores">
    <div class="bloco-header"><h6 id="tituloBloco3"><i class="fas fa-calculator mr-2"></i>{{ __('Valores') }}</h6></div>
    <div class="card-body pb-2">
        <p id="avisoValoresVazio" class="small text-muted mb-3 border rounded p-3 bg-white" style="border-color:#eee!important;">
            <i class="fas fa-info-circle mr-1" style="color:#A8873A"></i>
            {{ __('Use o botão Lançar no bloco acima para consolidar os dados do item aqui e informar o valor.') }}
        </p>

        <div id="resumoAntesValores" class="mb-3 p-3 bg-white border" style="display:none;border-radius:10px;background:linear-gradient(135deg,#fdfbf5,#fff)!important;border-color:rgba(168,135,58,.42)!important;">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start">
                <div>
                    <small class="text-muted font-weight-bold text-uppercase" style="font-size:.65rem">{{ __('Item definido para este lançamento') }}</small>
                    <div id="resumoDescLinha" class="font-weight-bold text-dark mb-1" style="font-size:1rem"></div>
                    <div id="resumoMetaLinha" class="small text-muted" style="line-height:1.45"></div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0" id="btnAlterarItemValores">
                    <i class="fas fa-edit mr-1"></i>{{ __('Alterar dados do item') }}
                </button>
            </div>
        </div>

        <div id="blocoComMedida">
            <div class="row align-items-end">
                <div class="col-md-4 form-group">
                    <label class="font-weight-bold small" id="lblUnitReal">{{ __('Unit. Real') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text" style="font-size:.75rem">R$</span></div>
                        <input type="number" name="custo_unitario_real" id="custoUnitReal" class="form-control @error('custo_unitario_real') is-invalid @enderror" step="0.01" min="0" value="{{ old('custo_unitario_real') }}">
                        @error('custo_unitario_real')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-3 form-group">
                    <label class="font-weight-bold small">{{ __('Total') }}</label>
                    <div class="form-control bg-light text-success font-weight-bold text-right" id="totalCalc">R$ 0,00</div>
                </div>
                <div class="col-md-5 form-group d-flex align-items-end">
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

{{-- ── Modal Novo Fornecedor ───────────────────────────────────────────── --}}
<div class="modal fade" id="modalNovoFornecedor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:12px">
            <div class="modal-header text-white py-3" style="background:linear-gradient(135deg,#A8873A,#E2C87A);border-radius:12px 12px 0 0">
                <h6 class="modal-title font-weight-bold mb-0"><i class="fas fa-truck mr-2"></i>{{ __('Dados do Fornecedor') }}</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modalFornecedorErros" class="alert alert-danger small py-2" style="display:none"></div>
                <div class="row">
                    <div class="col-md-7 form-group mb-2">
                        <label class="small font-weight-bold mb-1">{{ __('Razão Social') }} <span class="text-danger">*</span></label>
                        <input type="text" id="modalFornecedorRazaoSocial" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-5 form-group mb-2">
                        <label class="small font-weight-bold mb-1">{{ __('Nome Fantasia') }}</label>
                        <input type="text" id="modalFornecedorNomeFantasia" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group mb-2">
                        <label class="small font-weight-bold mb-1">{{ __('CNPJ') }}</label>
                        <input type="text" id="modalFornecedorCnpj" class="form-control form-control-sm" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="small font-weight-bold mb-1">{{ __('Telefone') }}</label>
                        <input type="text" id="modalFornecedorTelefone" class="form-control form-control-sm" placeholder="(00) 00000-0000">
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="small font-weight-bold mb-1">{{ __('E-mail') }}</label>
                        <input type="email" id="modalFornecedorEmail" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-2">
                        <label class="small font-weight-bold mb-1">{{ __('Endereço') }}</label>
                        <input type="text" id="modalFornecedorEndereco" class="form-control form-control-sm" placeholder="{{ __('Rua, número, complemento...') }}">
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="small font-weight-bold mb-1">{{ __('Cidade') }}</label>
                        <input type="text" id="modalFornecedorCidade" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="small font-weight-bold mb-1">{{ __('UF') }}</label>
                        <input type="text" id="modalFornecedorUf" class="form-control form-control-sm" maxlength="2" style="text-transform:uppercase" placeholder="SP">
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold mb-1">{{ __('Observações') }}</label>
                    <textarea id="modalFornecedorObservacoes" rows="2" class="form-control form-control-sm" placeholder="{{ __('Condições de pagamento, prazos, especialidades...') }}"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSalvarModalFornecedor"><i class="fas fa-save mr-1"></i>{{ __('Salvar') }}</button>
            </div>
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

var URL_CATALOGO_BUSCAR = @json(route('gastos.catalogo.buscar'));
var URL_CATALOGO_UPSERT = @json(route('gastos.catalogo.upsert'));

function normCatalogoTxt(str) {
    return String(str || '').trim().replace(/\s+/g, ' ').toLowerCase();
}

function isModoValorDireto() {
    return modoAtual === 'salario' || modoAtual === 'empreitada' || modoAtual === 'valor_total';
}

function escHtml(s) {
    return String(s || '').replace(/[&<>"']/g, function (c) {
        return ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', '\'':'&#39;' })[c];
    });
}

// ── Labels para cada modo
var MODOS = {
    'por_unidade': { label: 'Por Unidade',   lblQtd: 'Quantidade', lblUnidade: 'Unidade',   lblUnitReal: 'Unit. Real', icone: 'fas fa-ruler-combined', cor: 'secondary' },
    'por_hora':    { label: 'Por Hora',       lblQtd: 'Horas',      lblUnidade: 'h',          lblUnitReal: 'R$/hora',    icone: 'fas fa-clock',          cor: 'info'      },
    'salario':     { label: 'Salário Fixo',   lblQtd: null,         lblUnidade: null,         lblUnitReal: null,         icone: 'fas fa-calendar-alt',   cor: 'success'   },
    'empreitada':  { label: 'Empreitada',     lblQtd: null,         lblUnidade: null,         lblUnitReal: null,         icone: 'fas fa-hammer',         cor: 'warning'   },
    'valor_total': { label: 'Valor Total',    lblQtd: null,         lblUnidade: null,         lblUnitReal: null,         icone: 'fas fa-dollar-sign',    cor: 'dark'      },
};

var modoAtual = document.getElementById('hiddenModo').value || 'por_unidade';

// ── Fluxo: botão Lançar leva dados do item à secção Valores
function textoOpcaoSelect(idSel) {
    var sel = document.getElementById(idSel);
    if (!sel || sel.selectedIndex < 0) return '';
    var t = sel.options[sel.selectedIndex].text || '';
    return String(t).replace(/\s+/g, ' ').trim();
}

function subCatUsavel() {
    var sel = document.getElementById('selectSubcategoria');
    if (!sel || !String(sel.value || '').trim()) return '';
    return textoOpcaoSelect('selectSubcategoria');
}

function refreshResumoValoresPainel() {
    document.getElementById('resumoDescLinha').textContent =
        (document.getElementById('campoDescricao').value || '').trim();

    var catTxt = textoOpcaoSelect('selectCategoria');
    var sub = subCatUsavel();
    var tipoLbl = textoOpcaoSelect('selectTipo');
    var modoLbl = (MODOS[modoAtual] || MODOS['por_unidade']).label;

    var bullets = [];
    bullets.push({!! json_encode(__('Categoria')) !!} + ': ' + catTxt);
    if (sub) bullets.push({!! json_encode(__('Subcategoria')) !!} + ': ' + sub);
    bullets.push({!! json_encode(__('Tipo')) !!} + ': ' + tipoLbl);
    bullets.push({!! json_encode(__('Modo')) !!} + ': ' + modoLbl);

    var qFmt = '';
    if (!isModoValorDireto()) {
        var qRaw = parseFloat(document.getElementById('qtd').value);
        var uni = '';
        if (modoAtual === 'por_hora') uni = 'h';
        else uni = (document.getElementById('unidade').value || '').trim();

        qFmt = '';
        if (!isNaN(qRaw) && qRaw >= 0.001)
            qFmt = qRaw.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 3 }) +
                (uni ? ' \u00D7 ' + uni : '');
        bullets.push({!! json_encode(__('Quantidade')) !!} + ': ' + (qFmt || '—'));
    } else {
        bullets.push({!! json_encode(__('Quantidade')) !!} + ': ' + {!! json_encode(__('1 (valor direto)')) !!});
    }

    document.getElementById('resumoMetaLinha').textContent = bullets.join(' · ');
}

function atualizarResumoSeVisivel() {
    var r = document.getElementById('resumoAntesValores');
    if (!r || r.style.display === 'none' || window.getComputedStyle(r).display === 'none') return;
    refreshResumoValoresPainel();
}

function expandirPainelResumoValor() {
    refreshResumoValoresPainel();
    document.getElementById('avisoValoresVazio').style.display = 'none';
    document.getElementById('resumoAntesValores').style.display = 'block';
    document.getElementById('secaoValores').classList.add('ativo');

    var hk = document.getElementById('hintLancarValores');
    if (hk) {
        hk.classList.remove('text-muted');
        hk.classList.add('text-success', 'small', 'font-weight-bold', 'mb-2', 'mb-md-0');
        hk.innerHTML = '<i class="fas fa-check-circle mr-1"></i>{{ __("Dados lançados. Informe abaixo o valor ou preço.") }}';
    }
}

function tentarExpandirPainelValorComValidacao(opts) {
    opts = opts || {};
    var errs = [];

    if (!document.getElementById('selectObra').value) errs.push({!! json_encode(__('Selecione a obra.')) !!});
    if (!(document.getElementById('campoDescricao').value || '').trim()) errs.push({!! json_encode(__('Informe a descricao.')) !!});
    if (!document.getElementById('selectCategoria').value) errs.push({!! json_encode(__('Selecione a categoria.')) !!});
    if (!document.getElementById('selectTipo').value) errs.push({!! json_encode(__('Selecione o tipo.')) !!});

    if (!isModoValorDireto()) {
        var q = parseFloat(document.getElementById('qtd').value);
        if (!q || q < 0.001) errs.push({!! json_encode(__('Informe uma quantidade valida (minimo 0,001).')) !!});
        if (modoAtual === 'por_unidade' && !(document.getElementById('unidade').value || '').trim())
            errs.push({!! json_encode(__('Informe a unidade.')) !!});
    }

    if (errs.length) {
        if (!opts.silentValidation) {
            Swal.fire({
                icon: 'warning',
                title: {!! json_encode(__('Revise antes de lancar')) !!},
                html: errs.join('<br>'),
                confirmButtonColor: '#A8873A'
            });
        }
        return false;
    }

    expandirPainelResumoValor();

    var scrollOk = opts.scroll !== false;
    var focusOk = opts.focus !== false;
    var flashBtn = opts.flashBtn !== false;

    if (scrollOk && document.getElementById('secaoValores'))
        setTimeout(function () {
            document.getElementById('secaoValores').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 120);

    if (focusOk) {
        setTimeout(function () {
            var el = document.getElementById(isModoValorDireto() ? 'valorDireto' : 'custoUnitReal');
            if (el) el.focus();
        }, 480);
    }

    if (flashBtn) {
        var b = document.getElementById('btnLancarParaValores');
        if (b) {
            b.style.transform = 'scale(0.96)';
            setTimeout(function () { b.style.transition = '.2s'; b.style.transform = 'scale(1)'; }, 140);
        }
    }

    return true;
}

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
        (valorDireto ? {!! json_encode(__('Valor do lancamento')) !!} : {!! json_encode(__('Valores')) !!});

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
    atualizarResumoSeVisivel();
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

    persistCatalogoDebounced();
    atualizarResumoSeVisivel();
});

// ── Preencher unidade ao escolher subcategoria
document.getElementById('selectSubcategoria').addEventListener('change', function() {
    var txt = $('#selectSubcategoria').find(':selected').text() || '';
    var match = txt.match(/\(([^)]+)\)$/);
    if (match && modoAtual !== 'por_hora') document.getElementById('unidade').value = match[1];

    persistCatalogoDebounced();
    atualizarResumoSeVisivel();
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

// ── Catálogo de insumos por obra (autocomplete + persistência ao alterar campos)
var catalogoBuscaTimer = null;
var catalogoPersistTimer = null;
var catalogoListaAtual = [];

function fecharDropdownCatalogo() {
    var dd = document.getElementById('dropdownCatalogoProduto');
    if (dd) { dd.innerHTML = ''; dd.style.display = 'none'; }
}

function obraSelecionadaId() {
    return document.getElementById('selectObra').value || '';
}

function podePersistCatalogo() {
    if (!obraSelecionadaId()) return false;
    var desc = normCatalogoTxt(document.getElementById('campoDescricao').value);
    if (desc.length < 2) return false;
    if (!document.getElementById('selectCategoria').value) return false;
    if (!document.getElementById('selectTipo').value) return false;
    return true;
}

function payloadUpsertCatalogo() {
    var obraId = obraSelecionadaId();
    var desc = document.getElementById('campoDescricao').value.trim();
    var modoVD = isModoValorDireto();
    var qtyEl = document.getElementById('qtd');
    var qParsed = modoVD ? null : (parseFloat(qtyEl.value) || null);
    var unidEl = document.getElementById('unidade');
    var unidade = null;
    if (modoAtual === 'por_hora') {
        unidade = 'h';
    } else if (modoVD) {
        if (modoAtual === 'salario') unidade = 'mês';
        else unidade = 'vb';
    } else if (unidEl && !unidEl.readOnly) {
        unidade = unidEl.value.trim() || null;
    }

    var subVal = $('#selectSubcategoria').val();
    return {
        obra_id: obraId ? parseInt(obraId, 10) : null,
        descricao: desc,
        categoria_id: parseInt(document.getElementById('selectCategoria').value, 10),
        subcategoria_id: subVal ? parseInt(subVal, 10) : null,
        tipo: document.getElementById('selectTipo').value,
        unidade: unidade,
        quantidade_padrao: qParsed
    };
}

function persistCatalogoDebounced() {
    clearTimeout(catalogoPersistTimer);
    catalogoPersistTimer = setTimeout(function () {
        if (!podePersistCatalogo()) return;
        fetch(URL_CATALOGO_UPSERT, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payloadUpsertCatalogo())
        }).catch(function () {});
    }, 550);
}

function aplicarLinhaSalvaLista(it) {
    var data = Object.assign({ source: 'catalogo' }, it);
    aplicarHintLancamento(data);
    fecharDropdownCatalogo();
}

function renderOpcoesAutocomplete(items, textoDigitado) {
    var box = document.getElementById('dropdownCatalogoProduto');
    if (textoDigitado.length < 2) {
        box.style.display = 'none';
        return;
    }
    var obra = obraSelecionadaId();
    catalogoListaAtual = items || [];

    var nDig = normCatalogoTxt(textoDigitado);
    var html = '';

    catalogoListaAtual.forEach(function (it, ix) {
        var label = escHtml(it.descricao);
        html += '<button type="button" class="list-group-item list-group-item-action catalogo-opt catalogo-opt-linha py-2" data-cat-i="' + ix + '">';
        html += '<span class="d-block">' + label + '</span>';
        html += '<span class="d-block small text-muted">' +
            '{{ __("Cat.") }} #' + String(it.categoria_id) +
            ' · ' + escHtml(it.tipo || '') +
            ' · ' + (it.unidade ? escHtml(it.unidade) : '-') + '</span>';
        html += '</button>';
    });

    var temExato = catalogoListaAtual.some(function (it) {
        return normCatalogoTxt(it.descricao) === nDig;
    });

    var cadastrarLabel  = {!! json_encode(__('Cadastrar')) !!};
    var novoInsumoLabel = {!! json_encode(__('(novo insumo)')) !!};

    if (!temExato && textoDigitado.trim().length >= 2) {
        var semObra = !obra;
        html += '<button type="button" class="list-group-item list-group-item-action catalogo-opt cat-nuevo-insumo catalogo-opt-novo py-2"' +
            (semObra ? ' style="opacity:.55"' : '') + '>';
        html += '<span class="mr-2 cat-plus"><i class="fas fa-plus-circle"></i></span>';
        html += cadastrarLabel + ' \u201c' + escHtml(textoDigitado.trim()) + '\u201d ' + novoInsumoLabel;
        if (semObra) {
            html += ' <small class="text-muted ml-1">({{ __("selecione a obra primeiro") }})</small>';
        }
        html += '</button>';
    }

    if (!html.length) {
        box.style.display = 'none';
        return;
    }
    box.innerHTML = html;

    Array.prototype.slice.call(box.querySelectorAll('.catalogo-opt-linha')).forEach(function (btn) {
        var ix = parseInt(btn.getAttribute('data-cat-i'), 10);
        if (isNaN(ix) || !catalogoListaAtual[ix]) return;
        btn.onclick = function () {
            aplicarLinhaSalvaLista(catalogoListaAtual[ix]);
        };
    });

    Array.prototype.slice.call(box.querySelectorAll('.catalogo-opt-novo')).forEach(function (btn) {
        btn.onclick = function () {
            if (!obraSelecionadaId()) {
                Swal.fire({ icon: 'warning', title: {!! json_encode(__('Atenção')) !!}, text: {!! json_encode(__('Selecione a obra antes de cadastrar o insumo.')) !!}, confirmButtonColor: '#A8873A' });
                return;
            }
            if (!podePersistCatalogo()) {
                Swal.fire({ icon: 'info', title: {!! json_encode(__('Atenção')) !!}, text: {!! json_encode(__('Selecione categoria e tipo antes de registrar o novo insumo na memória.')) !!}, confirmButtonColor: '#A8873A' });
                return;
            }
            if (!isModoValorDireto() && modoAtual === 'por_unidade') {
                var uCad = (document.getElementById('unidade').value || '').trim();
                if (!uCad && !document.getElementById('unidade').readOnly) {
                    Swal.fire({
                        icon: 'info',
                        title: {!! json_encode(__('Unidade')) !!},
                        text: {!! json_encode(__('Informe a unidade (ex.: un, kg, m2) para gravar este insumo completo na memoria da obra.')) !!},
                        confirmButtonColor: '#A8873A'
                    });
                    document.getElementById('unidade').focus();
                    return;
                }
            }
            var payloadCad = payloadUpsertCatalogo();
            fetch(URL_CATALOGO_UPSERT, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payloadCad)
            })
                .then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (body) {
                        if (!r.ok) {
                            var linhas = [];
                            if (body && body.errors) {
                                Object.keys(body.errors || {}).forEach(function (k) {
                                    (body.errors[k] || []).forEach(function (m) { linhas.push(m); });
                                });
                            }
                            if (body && body.message && !linhas.length) linhas.push(body.message);
                            if (!linhas.length) linhas.push({!! json_encode(__('Não foi possível salvar. Verifique os dados.')) !!});
                            Swal.fire({ icon: 'error', title: {!! json_encode(__('Não gravado')) !!}, html: linhas.join('<br>'), confirmButtonColor: '#A8873A' });
                            throw new Error('upsert_erro');
                        }
                        return body;
                    });
                })
                .then(function (gravado) {
                    aplicarLinhaSalvaLista(gravado);
                    Swal.fire({
                        icon: 'success',
                        title: {!! json_encode(__('Insumo salvo na obra')) !!},
                        text: {!! json_encode(__('Ao digitar o nome, escolha o item na lista - categoria, subcategoria, tipo e unidade sao preenchidos automaticamente. Voce pode alterar antes de lancar.')) !!},
                        confirmButtonColor: '#A8873A'
                    }).then(function () {
                        var cx = document.getElementById('campoDescricao');
                        var tq = cx ? cx.value.trim() : '';
                        if (tq.length >= 2 && obraSelecionadaId()) buscarCatalogoDebounced(tq);
                        if (cx) cx.focus();
                    });
                })
                .catch(function (err) {
                    if (err && err.message === 'upsert_erro') return;
                    Swal.fire({ icon: 'error', title: {!! json_encode(__('Erro')) !!}, text: {!! json_encode(__('Não foi possível salvar o insumo.')) !!}, confirmButtonColor: '#A8873A' });
                });
        };
    });

    box.style.display = 'block';

    box.onmousedown = function (e) {
        if (e.target.closest('.catalogo-opt')) {
            e.preventDefault();
        }
    };
}

function buscarCatalogoDebounced(txt) {
    clearTimeout(catalogoBuscaTimer);
    catalogoBuscaTimer = setTimeout(function () {
        var obra = obraSelecionadaId();
        if (txt.length < 2) {
            fecharDropdownCatalogo();
            return;
        }
        if (!obra) {
            // Sem obra: mostra só o botão de cadastrar (desabilitado por obra)
            renderOpcoesAutocomplete([], txt);
            return;
        }
        fetch(URL_CATALOGO_BUSCAR + '?obra_id=' + encodeURIComponent(obra) + '&q=' + encodeURIComponent(txt), {
            headers: { Accept: 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (lista) {
                renderOpcoesAutocomplete(Array.isArray(lista) ? lista : [], txt);
            })
            .catch(function () { renderOpcoesAutocomplete([], txt); });
    }, 220);
}

// ── Dica ao sair da descrição ou ao repetir lançamento: catálogo da obra primeiro, depois histórico
function aplicarHintLancamento(data) {
    if (!data || !data.categoria_id) return;

    var fromCatalogo = (data.source === 'catalogo');

    // Histórico de lançamento só sugere quando a categoria está vazia; catálogo salvo sempre aplica os dados armazenados
    if (!fromCatalogo && document.getElementById('selectCategoria').value) return;

    $('#selectCategoria').val(String(data.categoria_id)).trigger('change');

    var subId = data.subcategoria_id != null && data.subcategoria_id !== '' ? String(data.subcategoria_id) : null;

    var tries = 0;
    var iv = setInterval(function () {
        tries++;
        var prontoParaUnidade = false;
        if (subId) {
            if ($('#selectSubcategoria option[value="' + subId + '"]').length) {
                $('#selectSubcategoria').val(subId).trigger('change');
                prontoParaUnidade = true;
            }
        } else {
            prontoParaUnidade = tries >= 3;
        }
        if (prontoParaUnidade || tries >= 40) {
            clearInterval(iv);
            if (data.unidade && modoAtual !== 'por_hora') {
                var u = document.getElementById('unidade');
                if (u && !u.readOnly) u.value = (data.unidade || '').trim();
            }
            if (
                fromCatalogo && data.quantidade_padrao != null && String(data.quantidade_padrao) !== ''
                && data.quantidade_padrao !== undefined && !isModoValorDireto()
            ) {
                var qEl = document.getElementById('qtd');
                var cur = parseFloat(qEl.value);
                if (!cur || cur === 1 || isNaN(cur)) {
                    qEl.value = parseFloat(data.quantidade_padrao);
                    recalcular();
                }
            }
        }
    }, 55);

    if (data.tipo) {
        document.getElementById('selectTipo').value = data.tipo;
        atualizarModosBotoes();
    }

    persistCatalogoDebounced();
}

var campoDescEl = document.getElementById('campoDescricao');
if (campoDescEl) {
    campoDescEl.addEventListener('input', function () {
        buscarCatalogoDebounced(this.value.trim());
        atualizarResumoSeVisivel();
    });
    campoDescEl.addEventListener('focus', function () {
        var t = this.value.trim();
        if (t.length >= 2) buscarCatalogoDebounced(t);
    });
}

document.addEventListener('click', function (e) {
    var wrap = document.getElementById('wrapCatalogoDesc');
    if (wrap && !wrap.contains(e.target)) fecharDropdownCatalogo();
});

campoDescEl && campoDescEl.addEventListener('blur', function () {
    var d = this.value.trim();
    if (d.length < 2) return;
    var ob = obraSelecionadaId();
    var baseHint = @json(route('gastos.hint-descricao'));
    var url = baseHint + '?descricao=' + encodeURIComponent(d);
    if (ob) url += '&obra_id=' + encodeURIComponent(ob);

    fetch(url, { headers: { Accept: 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(aplicarHintLancamento)
        .catch(function () {});
});

// ── Persistir alterações nos selects / qtd / unidade na memória do catálogo
document.getElementById('selectTipo').addEventListener('change', function () {
    persistCatalogoDebounced();
    atualizarResumoSeVisivel();
});

document.getElementById('qtd').addEventListener('input', function () {
    persistCatalogoDebounced();
    atualizarResumoSeVisivel();
});
document.getElementById('unidade').addEventListener('input', function () {
    persistCatalogoDebounced();
    atualizarResumoSeVisivel();
});

// ── Lançar item para bloco Valores (+ voltar aos dados acima)
var btnLz = document.getElementById('btnLancarParaValores');
if (btnLz) {
    btnLz.addEventListener('click', function () {
        tentarExpandirPainelValorComValidacao({});
    });
}
var btnEd = document.getElementById('btnAlterarItemValores');
if (btnEd) {
    btnEd.addEventListener('click', function () {
        document.getElementById('secaoProdutoTipo').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}

// ── Obra info
document.getElementById('selectObra').addEventListener('change', function() {
    document.getElementById('faseInfo').style.display = this.value ? 'block' : 'none';
    fecharDropdownCatalogo();
    persistCatalogoDebounced();
    atualizarResumoSeVisivel();
});
(function() { if (document.getElementById('selectObra').value) document.getElementById('faseInfo').style.display = 'block'; })();

// ── Fornecedor cadastrado → preencher placeholder no campo livre
document.getElementById('selectFornecedor').addEventListener('change', function() {
    var nome = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
    document.getElementById('fornecedorTexto').placeholder = this.value ? nome : '{{ __("Nome ou CNPJ") }}';
});

function limparModalFornecedor() {
    ['modalFornecedorRazaoSocial','modalFornecedorNomeFantasia','modalFornecedorCnpj','modalFornecedorTelefone','modalFornecedorEmail','modalFornecedorEndereco','modalFornecedorCidade','modalFornecedorUf','modalFornecedorObservacoes'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    var err = document.getElementById('modalFornecedorErros');
    if (err) { err.style.display = 'none'; err.innerHTML = ''; }
}

$('#modalNovoFornecedor').on('hidden.bs.modal', limparModalFornecedor).on('shown.bs.modal', function() {
    document.getElementById('modalFornecedorRazaoSocial').focus();
});

document.getElementById('btnSalvarModalFornecedor').addEventListener('click', function() {
    var errBox = document.getElementById('modalFornecedorErros');
    errBox.style.display = 'none';
    errBox.innerHTML = '';
    var razao = document.getElementById('modalFornecedorRazaoSocial').value.trim();
    if (!razao) {
        errBox.innerHTML = '<ul class="mb-0"><li>{{ __("Informe a razão social") }}</li></ul>';
        errBox.style.display = 'block';
        return;
    }
    var payload = {
        razao_social: razao,
        nome_fantasia: document.getElementById('modalFornecedorNomeFantasia').value.trim() || null,
        cnpj: document.getElementById('modalFornecedorCnpj').value.trim() || null,
        telefone: document.getElementById('modalFornecedorTelefone').value.trim() || null,
        email: document.getElementById('modalFornecedorEmail').value.trim() || null,
        endereco: document.getElementById('modalFornecedorEndereco').value.trim() || null,
        cidade: document.getElementById('modalFornecedorCidade').value.trim() || null,
        uf: document.getElementById('modalFornecedorUf').value.trim() || null,
        observacoes: document.getElementById('modalFornecedorObservacoes').value.trim() || null
    };
    fetch('{{ route("fornecedores.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    }).then(function(r) {
        return r.json().then(function(body) {
            if (!r.ok) throw body;
            return body;
        });
    }).then(function(data) {
        var sel = document.getElementById('selectFornecedor');
        var nome = data.nome || '';
        var opt = new Option(nome, data.id, true, true);
        sel.add(opt);
        sel.value = data.id;
        document.getElementById('fornecedorTexto').placeholder = nome || '{{ __("Nome ou CNPJ") }}';
        $('#modalNovoFornecedor').modal('hide');
    }).catch(function(body) {
        var msgs = [];
        if (body && body.errors) {
            Object.keys(body.errors).forEach(function(k) {
                (body.errors[k] || []).forEach(function(m) { msgs.push(m); });
            });
        }
        if (msgs.length === 0 && body && body.message) msgs.push(body.message);
        if (msgs.length === 0) msgs.push('{{ __("Não foi possível salvar. Verifique os dados.") }}');
        errBox.innerHTML = '<ul class="mb-0">' + msgs.map(function(m) { return '<li>' + m + '</li>'; }).join('') + '</ul>';
        errBox.style.display = 'block';
    });
});

// ── Criar categoria via AJAX
document.getElementById('btnSalvarCategoria').addEventListener('click', function() {
    var nome = document.getElementById('novaCategoriaNome').value.trim();
    var tipo = document.getElementById('novaCategoriaTipo').value;
    if (!nome) { Swal.fire({ icon: 'warning', title: {!! json_encode(__('Atenção')) !!}, text: '{{ __("Informe o nome") }}', confirmButtonColor: '#A8873A' }); return; }
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
    if (!nome) { Swal.fire({ icon: 'warning', title: {!! json_encode(__('Atenção')) !!}, text: '{{ __("Informe o nome") }}', confirmButtonColor: '#A8873A' }); return; }
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

    @if(old('descricao'))
    setTimeout(function () {
        expandirPainelResumoValor();
    }, 280);
    @endif
})();
</script>
@stop
