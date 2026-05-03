@extends('adminlte::page')

@section('title', 'Editar Diário #' . $diario->numero)

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('diario.show', $diario) }}" class="btn btn-sm btn-outline-secondary mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="mb-0">
            <i class="fas fa-edit mr-2" style="color:var(--ti-gold)"></i>
            Editar Diário @if($diario->numero) <span style="color:#999">#{{ $diario->numero }}</span> @endif
        </h1>
        <small class="text-muted">{{ $diario->data_registro->format('d/m/Y') }} — {{ $diario->obra->nome }}</small>
    </div>
</div>
@stop

@section('content')
@include('diario._styles')

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('diario.update', $diario) }}" enctype="multipart/form-data" id="formDiario">
@csrf @method('PUT')

{{-- ── Cabeçalho ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-md-4 form-group mb-2">
                <label class="lbl-sec">Obra</label>
                <input type="text" class="form-control" value="{{ $diario->obra->nome }}" readonly style="background:#f9f9f9">
                <input type="hidden" name="obra_id" value="{{ $diario->obra_id }}">
            </div>
            <div class="col-md-3 form-group mb-2">
                <label class="lbl-sec">Fase da Obra</label>
                <select name="obra_fase_id" id="selectFase" class="form-control">
                    <option value="">-- Nenhuma --</option>
                    @foreach($obraSel->fases as $fase)
                        <option value="{{ $fase->id }}" {{ $diario->obra_fase_id == $fase->id ? 'selected':'' }}>
                            {{ $fase->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label class="lbl-sec">Data <span class="text-danger">*</span></label>
                <input type="date" name="data_registro" class="form-control"
                       value="{{ old('data_registro', $diario->data_registro->toDateString()) }}" required>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label class="lbl-sec">Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="diario"  {{ $diario->tipo === 'diario'  ?'selected':'' }}>Diário</option>
                    <option value="semanal" {{ $diario->tipo === 'semanal' ?'selected':'' }}>Semanal</option>
                </select>
            </div>
            <div class="col-md-1 form-group mb-2">
                <label class="lbl-sec">Status</label>
                <select name="status" class="form-control">
                    <option value="rascunho"   {{ ($diario->status??'rascunho') === 'rascunho'  ?'selected':'' }}>Rascunho</option>
                    <option value="finalizado" {{ ($diario->status??'') === 'finalizado'        ?'selected':'' }}>Finalizado</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-2">
                <label class="lbl-sec">Título / Resumo do dia</label>
                <input type="text" name="titulo" class="form-control"
                       value="{{ old('titulo', $diario->titulo) }}" placeholder="Ex.: Concretagem da laje do 2º pavimento">
            </div>
            <div class="col-md-3 form-group mb-2">
                <label class="lbl-sec">Responsável</label>
                <select name="responsavel_id" class="form-control">
                    <option value="">Selecione...</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ $diario->responsavel_id == $u->id ? 'selected':'' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group mb-2">
                <label class="lbl-sec">Local / Área</label>
                <input type="text" name="local_area" class="form-control"
                       value="{{ old('local_area', $diario->local_area) }}" placeholder="Ex.: Pav. 3 - Bloco A">
            </div>
        </div>
    </div>
</div>

{{-- ── Tempo por turno ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="rdo-section-title">Tempo</div>
        <div class="row">
            @foreach([
                ['key'=>'manha', 'label'=>'Manhã',  'val'=>$diario->tempo_manha],
                ['key'=>'tarde', 'label'=>'Tarde',  'val'=>$diario->tempo_tarde],
                ['key'=>'noite', 'label'=>'Noite',  'val'=>$diario->tempo_noite],
            ] as $turno)
            @php
                $tStatus = $turno['val']['status'] ?? 'praticavel';
                $tClima  = $turno['val']['clima']  ?? 'sol';
            @endphp
            <div class="col-md-4">
                <div class="turno-box">
                    <div class="turno-label">{{ $turno['label'] }}</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <select name="tempo_{{ $turno['key'] }}_status" class="form-control form-control-sm" style="width:auto">
                            <option value="praticavel"   {{ $tStatus === 'praticavel'   ?'selected':'' }}>Praticável</option>
                            <option value="impraticavel" {{ $tStatus === 'impraticavel' ?'selected':'' }}>Impraticável</option>
                        </select>
                        <div class="clima-turno-btns">
                            @foreach(['sol'=>'☀️','nublado'=>'☁️','chuva_leve'=>'🌦️','chuva_forte'=>'🌧️','vento'=>'💨'] as $val=>$icone)
                                <button type="button"
                                        class="btn-clima {{ $tClima === $val ? 'ativo':'' }}"
                                        data-turno="{{ $turno['key'] }}" data-val="{{ $val }}"
                                        title="{{ ucfirst(str_replace('_',' ',$val)) }}">
                                    {{ $icone }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="tempo_{{ $turno['key'] }}_clima"
                               class="clima-val-{{ $turno['key'] }}" value="{{ $tClima }}">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Mão de obra ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="rdo-section-title mb-0">Mão de Obra</div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow('mao_de_obra')">
                <i class="fas fa-plus mr-1"></i> Adicionar
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered rdo-table">
                <thead><tr>
                    <th style="width:60px">Qtde</th><th>Função / Cargo</th>
                    <th>Profissional / Fornecedor</th><th>Observação</th><th style="width:40px"></th>
                </tr></thead>
                <tbody id="body-mao_de_obra">
                    @forelse($diario->maoDeObra as $i => $mo)
                    <tr class="tr-mao_de_obra">
                        <td><input type="number" name="mao_de_obra[{{ $i }}][quantidade]" class="form-control form-control-sm" value="{{ $mo->quantidade }}" min="1" style="width:60px"></td>
                        <td><input type="text"   name="mao_de_obra[{{ $i }}][funcao]" class="form-control form-control-sm" value="{{ $mo->funcao }}"></td>
                        <td><input type="text"   name="mao_de_obra[{{ $i }}][profissional_fornecedor]" class="form-control form-control-sm" value="{{ $mo->profissional_fornecedor }}"></td>
                        <td><input type="text"   name="mao_de_obra[{{ $i }}][observacao]" class="form-control form-control-sm" value="{{ $mo->observacao }}"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger px-1" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                    @empty
                    <tr class="tr-mao_de_obra">
                        <td><input type="number" name="mao_de_obra[0][quantidade]" class="form-control form-control-sm" value="1" min="1" style="width:60px"></td>
                        <td><input type="text"   name="mao_de_obra[0][funcao]" class="form-control form-control-sm" placeholder="Ex.: Pedreiro"></td>
                        <td><input type="text"   name="mao_de_obra[0][profissional_fornecedor]" class="form-control form-control-sm"></td>
                        <td><input type="text"   name="mao_de_obra[0][observacao]" class="form-control form-control-sm"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger px-1" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Equipamentos ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="rdo-section-title mb-0">Equipamentos</div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow('equipamentos')">
                <i class="fas fa-plus mr-1"></i> Adicionar
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered rdo-table">
                <thead><tr>
                    <th style="width:60px">Qtde</th><th>Descrição do Equipamento</th><th style="width:40px"></th>
                </tr></thead>
                <tbody id="body-equipamentos">
                    @forelse($diario->equipamentos as $i => $eq)
                    <tr class="tr-equipamentos">
                        <td><input type="number" name="equipamentos[{{ $i }}][quantidade]" class="form-control form-control-sm" value="{{ $eq->quantidade }}" min="1" style="width:60px"></td>
                        <td><input type="text"   name="equipamentos[{{ $i }}][descricao]" class="form-control form-control-sm" value="{{ $eq->descricao }}"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger px-1" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                    @empty
                    <tr class="tr-equipamentos">
                        <td><input type="number" name="equipamentos[0][quantidade]" class="form-control form-control-sm" value="1" min="1" style="width:60px"></td>
                        <td><input type="text"   name="equipamentos[0][descricao]" class="form-control form-control-sm" placeholder="Ex.: Furadeira Bosch GBM"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger px-1" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Atividades ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="rdo-section-title mb-0">Atividades</div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow('atividades')">
                <i class="fas fa-plus mr-1"></i> Adicionar
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered rdo-table">
                <thead><tr>
                    <th>Atividade / Descrição</th>
                    <th style="width:110px">Qtde Orçada</th>
                    <th style="width:110px">Qtde Realizada</th>
                    <th style="width:80px">Evolução</th>
                    <th style="width:140px">Status</th>
                    <th style="width:160px">
                        Vincular Tarefa
                        <i class="fas fa-info-circle text-muted ml-1"
                           title="Quando finalizada, marca automaticamente a tarefa no Cronograma"
                           data-toggle="tooltip"></i>
                    </th>
                    <th>Comentário</th>
                    <th style="width:40px"></th>
                </tr></thead>
                <tbody id="body-atividades">
                    @forelse($diario->atividades as $i => $atv)
                    <tr class="tr-atividades">
                        <td><textarea name="atividades[{{ $i }}][descricao]" class="form-control form-control-sm" rows="2">{{ $atv->descricao }}</textarea></td>
                        <td><input type="text" name="atividades[{{ $i }}][qtde_orcada]"    class="form-control form-control-sm" value="{{ $atv->qtde_orcada }}"></td>
                        <td><input type="text" name="atividades[{{ $i }}][qtde_realizada]" class="form-control form-control-sm" value="{{ $atv->qtde_realizada }}"></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" name="atividades[{{ $i }}][evolucao_percentual]" class="form-control" step="1" min="0" max="100" value="{{ $atv->evolucao_percentual }}">
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </td>
                        <td>
                            <select name="atividades[{{ $i }}][status_atividade]" class="form-control form-control-sm">
                                <option value="em_andamento" {{ $atv->status_atividade === 'em_andamento' ?'selected':'' }}>Em Andamento</option>
                                <option value="paralisada"   {{ $atv->status_atividade === 'paralisada'   ?'selected':'' }}>Paralisada</option>
                                <option value="finalizada"   {{ $atv->status_atividade === 'finalizada'   ?'selected':'' }}>Finalizada</option>
                                <option value="nao_iniciada" {{ $atv->status_atividade === 'nao_iniciada' ?'selected':'' }}>Não Iniciada</option>
                            </select>
                        </td>
                        <td>
                            <select name="atividades[{{ $i }}][obra_fase_tarefa_id]"
                                    class="form-control form-control-sm sel-tarefa"
                                    data-selected="{{ $atv->obra_fase_tarefa_id }}">
                                <option value="">Carregando...</option>
                            </select>
                        </td>
                        <td><input type="text" name="atividades[{{ $i }}][comentario]" class="form-control form-control-sm" value="{{ $atv->comentario }}"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger px-1" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                    @empty
                    <tr class="tr-atividades">
                        <td><textarea name="atividades[0][descricao]" class="form-control form-control-sm" rows="2" placeholder="Descrição da atividade..."></textarea></td>
                        <td><input type="text" name="atividades[0][qtde_orcada]"    class="form-control form-control-sm" placeholder="591,03 M2"></td>
                        <td><input type="text" name="atividades[0][qtde_realizada]" class="form-control form-control-sm"></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" name="atividades[0][evolucao_percentual]" class="form-control" step="1" min="0" max="100">
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </td>
                        <td>
                            <select name="atividades[0][status_atividade]" class="form-control form-control-sm">
                                <option value="em_andamento">Em Andamento</option>
                                <option value="paralisada">Paralisada</option>
                                <option value="finalizada">Finalizada</option>
                                <option value="nao_iniciada">Não Iniciada</option>
                            </select>
                        </td>
                        <td>
                            <select name="atividades[0][obra_fase_tarefa_id]" class="form-control form-control-sm sel-tarefa" data-selected="">
                                <option value="">-- Não vincular --</option>
                            </select>
                        </td>
                        <td><input type="text" name="atividades[0][comentario]" class="form-control form-control-sm"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger px-1" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Comentários ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="rdo-section-title">Comentários</div>
        <textarea name="comentarios" rows="4" class="form-control"
                  placeholder="Situação geral, próximos passos...">{{ old('comentarios', $diario->comentarios) }}</textarea>
    </div>
</div>

{{-- ── Fotos existentes ── --}}
@if($diario->totalFotos() > 0)
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="rdo-section-title">Fotos Existentes</div>
        @foreach($diario->fotos_agrupadas as $pasta => $caminhos)
            <div class="pasta-titulo"><i class="fas fa-folder text-warning mr-1"></i> {{ $pasta }}</div>
            <div class="d-flex flex-wrap mb-3" style="gap:8px">
                @foreach($caminhos as $caminho)
                <div style="position:relative">
                    <img src="{{ Storage::url($caminho) }}" alt=""
                         style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:2px solid #ddd">
                    <label style="position:absolute;top:-6px;right:-6px;cursor:pointer"
                           title="Marcar para remover">
                        <input type="checkbox" name="fotos_remover[]" value="{{ $caminho }}"
                               class="fotos-remover-check" style="display:none">
                        <span class="btn-rm-foto"><i class="fas fa-times"></i></span>
                    </label>
                </div>
                @endforeach
            </div>
        @endforeach
        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Clique no X para marcar fotos a remover ao salvar.</small>
    </div>
</div>
@endif

{{-- ── Novas fotos ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="rdo-section-title mb-0">Adicionar Novas Fotos</div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adicionarPasta()">
                <i class="fas fa-folder-plus mr-1"></i> Nova Pasta
            </button>
        </div>
        <div id="pastas-container">
            <div class="pasta-bloco mb-3" data-pasta="Pasta 1">
                <div class="pasta-header d-flex align-items-center mb-2">
                    <i class="fas fa-folder text-warning mr-2"></i>
                    <input type="text" class="form-control form-control-sm pasta-nome" style="max-width:200px" value="Pasta 1" readonly>
                    <button type="button" class="btn btn-sm btn-link text-primary ml-2" onclick="editarPasta(this)">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                </div>
                <div class="custom-file">
                    <input type="file" class="custom-file-input input-fotos" multiple accept="image/*" data-pasta-idx="0">
                    <label class="custom-file-label">Selecionar novas fotos...</label>
                </div>
                <div class="foto-preview mt-2" id="preview-pasta-0"></div>
                <div class="pastas-inputs" id="inputs-pasta-0"></div>
            </div>
        </div>
        <small class="text-muted">Máx. 10MB por foto.</small>
    </div>
</div>

{{-- Ocorrências --}}
<div class="card card-rdo mb-3">
    <div class="card-header bg-white py-2" style="cursor:pointer" data-toggle="collapse" data-target="#colOcorr">
        <span class="rdo-section-title mb-0" style="font-size:.8rem">
            <i class="fas fa-exclamation-triangle text-warning mr-1"></i> Ocorrências / Problemas
        </span>
        <span class="float-right text-muted small">clique para {{ $diario->temOcorrencias() ? 'recolher':'expandir' }}</span>
    </div>
    <div class="collapse {{ $diario->temOcorrencias() ? 'show':'' }}" id="colOcorr">
        <div class="card-body pt-2">
            <textarea name="ocorrencias" rows="3" class="form-control mb-2"
                      placeholder="Registre imprevistos...">{{ old('ocorrencias', $diario->ocorrencias) }}</textarea>
            <label class="font-weight-bold small">Soluções Adotadas</label>
            <textarea name="solucoes_adotadas" rows="2" class="form-control"
                      placeholder="Descreva as ações tomadas...">{{ old('solucoes_adotadas', $diario->solucoes_adotadas) }}</textarea>
        </div>
    </div>
</div>

{{-- Botões --}}
<div class="d-flex justify-content-end mb-5" style="gap:8px">
    <a href="{{ route('diario.show', $diario) }}" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Salvar Alterações
    </button>
</div>

</form>
@stop

@section('js')
<script>
// ── Clima ─────────────────────────────────────────────────────────────────────
document.querySelectorAll('.btn-clima').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var turno = this.dataset.turno;
        document.querySelectorAll('.btn-clima[data-turno="'+turno+'"]').forEach(b => b.classList.remove('ativo'));
        this.classList.add('ativo');
        document.querySelector('.clima-val-'+turno).value = this.dataset.val;
    });
});

// ── Tarefas da fase ───────────────────────────────────────────────────────────
var tarefasDaFase = [];

@if($diario->obra_fase_id)
// Carregar tarefas da fase atual ao abrir a página
fetch('/api/diario/tarefas-fase/{{ $diario->obra_fase_id }}')
    .then(r => r.json())
    .then(function(tarefas) {
        tarefasDaFase = tarefas;
        document.querySelectorAll('.sel-tarefa').forEach(function(sel) {
            var selected = sel.dataset.selected || '';
            sel.innerHTML = buildTarefasHtml(selected);
        });
    });
@endif

document.getElementById('selectFase').addEventListener('change', function() {
    if (!this.value) { tarefasDaFase = []; atualizarDropdownsTarefas(); return; }
    fetch('/api/diario/tarefas-fase/' + this.value)
        .then(r => r.json())
        .then(function(tarefas) {
            tarefasDaFase = tarefas;
            atualizarDropdownsTarefas();
        });
});

function buildTarefasHtml(selectedId) {
    if (!tarefasDaFase.length) return '<option value="">-- sem tarefas --</option>';
    var html = '<option value="">-- Não vincular --</option>';
    var grupoAtual = null;
    tarefasDaFase.forEach(function(t) {
        if (t.grupo && t.grupo !== grupoAtual) {
            if (grupoAtual !== null) html += '</optgroup>';
            html += '<optgroup label="' + t.grupo + '">';
            grupoAtual = t.grupo;
        }
        var done = t.concluida ? ' ✓' : '';
        var sel  = (selectedId && parseInt(selectedId) === t.id) ? ' selected' : '';
        html += '<option value="'+t.id+'"'+sel+'>' + t.nome + done + '</option>';
    });
    if (grupoAtual !== null) html += '</optgroup>';
    return html;
}

function atualizarDropdownsTarefas() {
    document.querySelectorAll('.sel-tarefa').forEach(function(sel) {
        var cur = sel.dataset.selected || sel.value;
        sel.innerHTML = buildTarefasHtml(cur);
    });
}

// ── Linhas dinâmicas ──────────────────────────────────────────────────────────
var rowCounts = {
    mao_de_obra: {{ $diario->maoDeObra->count() ?: 1 }},
    equipamentos: {{ $diario->equipamentos->count() ?: 1 }},
    atividades: {{ $diario->atividades->count() ?: 1 }},
};

function addRow(tipo) {
    var idx   = rowCounts[tipo]++;
    var tbody = document.getElementById('body-' + tipo);
    var tpl   = tbody.querySelector('.tr-' + tipo);
    var novo  = tpl.cloneNode(true);
    novo.querySelectorAll('[name]').forEach(function(el) {
        el.name = el.name.replace(/\[\d+\]/, '[' + idx + ']');
        if (el.tagName === 'INPUT')    el.value = el.type === 'number' ? 1 : '';
        if (el.tagName === 'SELECT')   el.selectedIndex = 0;
        if (el.tagName === 'TEXTAREA') el.value = '';
    });
    tbody.appendChild(novo);
    if (tipo === 'atividades') {
        var sel = novo.querySelector('.sel-tarefa');
        if (sel) { sel.dataset.selected = ''; sel.innerHTML = buildTarefasHtml(null); }
    }
}

function removeRow(btn) {
    var tr = btn.closest('tr');
    var tbody = tr.closest('tbody');
    if (tbody.rows.length > 1) tr.remove();
}

// ── Fotos remover ─────────────────────────────────────────────────────────────
document.querySelectorAll('.fotos-remover-check').forEach(function(chk) {
    chk.addEventListener('change', function() {
        var img   = this.closest('div').querySelector('img');
        var label = this.closest('label').querySelector('.btn-rm-foto');
        if (this.checked) {
            img.style.opacity = '.3';
            label.style.background = '#555';
        } else {
            img.style.opacity = '1';
            label.style.background = '#e53935';
        }
    });
});

// ── Fotos novas em pastas ─────────────────────────────────────────────────────
var pastaCount  = 1;
var globalFotoIdx = 0;

function adicionarPasta() {
    pastaCount++;
    var nome = 'Pasta ' + pastaCount;
    var idx  = pastaCount - 1;
    var cont = document.getElementById('pastas-container');
    var div  = document.createElement('div');
    div.className = 'pasta-bloco mb-3';
    div.dataset.pasta = nome;
    div.innerHTML = `
        <div class="pasta-header d-flex align-items-center mb-2">
            <i class="fas fa-folder text-warning mr-2"></i>
            <input type="text" class="form-control form-control-sm pasta-nome" style="max-width:200px" value="${nome}" readonly>
            <button type="button" class="btn btn-sm btn-link text-primary ml-2" onclick="editarPasta(this)"><i class="fas fa-pencil-alt"></i></button>
            <button type="button" class="btn btn-sm btn-link text-danger ml-1" onclick="removerPasta(this)"><i class="fas fa-trash"></i></button>
        </div>
        <div class="custom-file">
            <input type="file" class="custom-file-input input-fotos" multiple accept="image/*" data-pasta-idx="${idx}">
            <label class="custom-file-label">Selecionar fotos para ${nome}...</label>
        </div>
        <div class="foto-preview mt-2" id="preview-pasta-${idx}"></div>
        <div class="pastas-inputs" id="inputs-pasta-${idx}"></div>
    `;
    cont.appendChild(div);
    bindFotoInput(div.querySelector('.input-fotos'));
}

function removerPasta(btn) { btn.closest('.pasta-bloco').remove(); }

function editarPasta(btn) {
    var input = btn.closest('.pasta-header').querySelector('.pasta-nome');
    input.removeAttribute('readonly');
    input.focus();
    input.addEventListener('blur', function() {
        input.setAttribute('readonly', true);
        var bloco = input.closest('.pasta-bloco');
        bloco.dataset.pasta = input.value || 'Pasta';
        bloco.querySelector('.custom-file-label').textContent = 'Selecionar fotos para ' + (input.value || 'Pasta') + '...';
    }, { once: true });
}

function bindFotoInput(inputEl) {
    inputEl.addEventListener('change', function() {
        var pastaIdx  = this.dataset.pastaIdx;
        var bloco     = this.closest('.pasta-bloco');
        var pastaNome = bloco.querySelector('.pasta-nome').value || 'Pasta 1';
        var preview   = document.getElementById('preview-pasta-' + pastaIdx);
        var hiddens   = document.getElementById('inputs-pasta-' + pastaIdx);
        bloco.querySelector('.custom-file-label').textContent = this.files.length + ' foto(s) selecionada(s)';
        Array.from(this.files).forEach(function(file) {
            var reader = new FileReader();
            var fIdx   = globalFotoIdx++;
            reader.onload = function(e) {
                var wrap = document.createElement('div');
                wrap.className = 'foto-item-preview';
                wrap.innerHTML = '<img src="'+e.target.result+'" alt="">';
                preview.appendChild(wrap);
            };
            reader.readAsDataURL(file);
            var h = document.createElement('input');
            h.type = 'hidden'; h.name = 'foto_pasta[' + fIdx + ']'; h.value = pastaNome;
            hiddens.appendChild(h);
        });
    });
}

document.querySelectorAll('.input-fotos').forEach(bindFotoInput);
</script>
@stop
