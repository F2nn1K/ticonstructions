@extends('adminlte::page')

@section('title', __('app.diary.new_record'))

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('diario.index') }}" class="btn btn-sm btn-outline-secondary mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="mb-0"><i class="fas fa-book-open mr-2" style="color:var(--ti-gold)"></i> {{ __('app.diary.new_record') }}</h1>
        <small class="text-muted">{{ __('app.diary.subtitle_new') }}</small>
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

<form method="POST" action="{{ route('diario.store') }}" enctype="multipart/form-data" id="formDiario">
@csrf

{{-- ── Cabeçalho ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-md-4 form-group mb-2">
                <label class="lbl-sec">{{ __('app.diary.work') }} <span class="text-danger">*</span></label>
                <select name="obra_id" id="selectObra" class="form-control @error('obra_id') is-invalid @enderror" required>
                    <option value="">{{ __('app.diary.select') }}</option>
                    @foreach($obras as $ob)
                        <option value="{{ $ob->id }}" {{ old('obra_id', $obraSel?->id) == $ob->id ? 'selected':'' }}>
                            {{ $ob->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group mb-2">
                <label class="lbl-sec">{{ __('app.diary.phase') }}</label>
                <select name="obra_fase_id" id="selectFase" class="form-control">
                    <option value="">{{ __('app.diary.select_work_first') }}</option>
                    @if($obraSel)
                        @foreach($obraSel->fases as $fase)
                            <option value="{{ $fase->id }}" {{ $fase->status === 'em_andamento' ? 'selected':'' }}>
                                {{ $fase->nome }}{{ $fase->status === 'em_andamento' ? ' ('.__('app.common.active').')':'' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label class="lbl-sec">{{ __('app.diary.date') }} <span class="text-danger">*</span></label>
                <input type="date" name="data_registro" class="form-control"
                       value="{{ old('data_registro', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label class="lbl-sec">{{ __('app.diary.type') }}</label>
                <select name="tipo" class="form-control">
                    <option value="diario"  {{ old('tipo','diario')=='diario'  ?'selected':'' }}>{{ __('app.diary.type_daily') }}</option>
                    <option value="semanal" {{ old('tipo')=='semanal'          ?'selected':'' }}>{{ __('app.diary.type_weekly') }}</option>
                </select>
            </div>
            <div class="col-md-1 form-group mb-2">
                <label class="lbl-sec">{{ __('app.diary.status') }}</label>
                <select name="status" class="form-control">
                    <option value="rascunho"   {{ old('status','rascunho')=='rascunho'  ?'selected':'' }}>{{ __('app.diary.status_draft') }}</option>
                    <option value="finalizado" {{ old('status')=='finalizado'           ?'selected':'' }}>{{ __('app.diary.status_final') }}</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group mb-2">
                <label class="lbl-sec">{{ __('app.diary.title_field') }}</label>
                <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}"
                       placeholder="{{ __('app.diary.title_placeholder') }}">
            </div>
            <div class="col-md-3 form-group mb-2">
                <label class="lbl-sec">{{ __('app.diary.responsible') }}</label>
                <select name="responsavel_id" class="form-control">
                    <option value="">{{ __('app.diary.select') }}</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ old('responsavel_id', auth()->id()) == $u->id ? 'selected':'' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group mb-2">
                <label class="lbl-sec">{{ __('app.diary.location') }}</label>
                <input type="text" name="local_area" class="form-control" value="{{ old('local_area') }}"
                       placeholder="{{ __('app.diary.location_placeholder') }}">
            </div>
        </div>
    </div>
</div>

{{-- ── Tempo ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="rdo-section-title">{{ __('app.diary.section_weather') }}</div>
        <div class="row">
            @foreach([
                ['key'=>'manha',  'label'=> __('app.diary.morning')],
                ['key'=>'tarde',  'label'=> __('app.diary.afternoon')],
                ['key'=>'noite',  'label'=> __('app.diary.night')],
            ] as $turno)
            <div class="col-md-4">
                <div class="turno-box">
                    <div class="turno-label">{{ $turno['label'] }}</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <select name="tempo_{{ $turno['key'] }}_status" class="form-control form-control-sm" style="width:auto">
                            <option value="praticavel">{{ __('app.diary.practicable') }}</option>
                            <option value="impraticavel">{{ __('app.diary.impracticable') }}</option>
                        </select>
                        <div class="clima-turno-btns">
                            @foreach([
                                'sol'=>['icon'=>'☀️','label'=>__('app.diary.weather_sun')],
                                'nublado'=>['icon'=>'☁️','label'=>__('app.diary.weather_cloudy')],
                                'chuva_leve'=>['icon'=>'🌦️','label'=>__('app.diary.weather_light_rain')],
                                'chuva_forte'=>['icon'=>'🌧️','label'=>__('app.diary.weather_heavy_rain')],
                                'vento'=>['icon'=>'💨','label'=>__('app.diary.weather_wind')],
                            ] as $val=>$w)
                                <button type="button" class="btn-clima {{ $val==='sol'?'ativo':'' }}"
                                        data-turno="{{ $turno['key'] }}" data-val="{{ $val }}"
                                        title="{{ $w['label'] }}">{{ $w['icon'] }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="tempo_{{ $turno['key'] }}_clima" class="clima-val-{{ $turno['key'] }}" value="sol">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Mão de Obra ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="rdo-section-title mb-0">{{ __('app.diary.section_labor') }}</div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow('mao_de_obra')">
                <i class="fas fa-plus mr-1"></i> {{ __('app.diary.btn_add') }}
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered rdo-table" id="tbl-mao_de_obra">
                <thead><tr>
                    <th style="width:60px">{{ __('app.diary.labor_qty') }}</th>
                    <th>{{ __('app.diary.labor_role') }}</th>
                    <th>{{ __('app.diary.labor_supplier') }}</th>
                    <th>{{ __('app.diary.labor_note') }}</th>
                    <th style="width:40px"></th>
                </tr></thead>
                <tbody id="body-mao_de_obra">
                    <tr class="tr-mao_de_obra">
                        <td><input type="number" name="mao_de_obra[0][quantidade]" class="form-control form-control-sm" value="1" min="1" style="width:60px"></td>
                        <td><input type="text" name="mao_de_obra[0][funcao]" class="form-control form-control-sm" placeholder="{{ __('app.diary.labor_placeholder') }}"></td>
                        <td><input type="text" name="mao_de_obra[0][profissional_fornecedor]" class="form-control form-control-sm"></td>
                        <td><input type="text" name="mao_de_obra[0][observacao]" class="form-control form-control-sm"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger px-1" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Equipamentos ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="rdo-section-title mb-0">{{ __('app.diary.section_equipment') }}</div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow('equipamentos')">
                <i class="fas fa-plus mr-1"></i> {{ __('app.diary.btn_add') }}
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered rdo-table">
                <thead><tr>
                    <th style="width:60px">{{ __('app.diary.equip_qty') }}</th>
                    <th>{{ __('app.diary.equip_desc') }}</th>
                    <th style="width:40px"></th>
                </tr></thead>
                <tbody id="body-equipamentos">
                    <tr class="tr-equipamentos">
                        <td><input type="number" name="equipamentos[0][quantidade]" class="form-control form-control-sm" value="1" min="1" style="width:60px"></td>
                        <td><input type="text" name="equipamentos[0][descricao]" class="form-control form-control-sm" placeholder="{{ __('app.diary.equip_placeholder') }}"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger px-1" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Atividades ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="rdo-section-title mb-0">{{ __('app.diary.section_activities') }}</div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow('atividades')">
                <i class="fas fa-plus mr-1"></i> {{ __('app.diary.btn_add') }}
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered rdo-table">
                <thead><tr>
                    <th>{{ __('app.diary.act_desc') }}</th>
                    <th style="width:110px">{{ __('app.diary.act_qty_budgeted') }}</th>
                    <th style="width:110px">{{ __('app.diary.act_qty_done') }}</th>
                    <th style="width:80px">{{ __('app.diary.act_progress') }}</th>
                    <th style="width:140px">{{ __('app.diary.act_status') }}</th>
                    <th style="width:160px">
                        {{ __('app.diary.act_link_task') }}
                        <i class="fas fa-info-circle text-muted ml-1" title="{{ __('app.diary.act_link_tooltip') }}" data-toggle="tooltip"></i>
                    </th>
                    <th>{{ __('app.diary.act_comment') }}</th>
                    <th style="width:40px"></th>
                </tr></thead>
                <tbody id="body-atividades">
                    <tr class="tr-atividades">
                        <td><textarea name="atividades[0][descricao]" class="form-control form-control-sm" rows="2" placeholder="{{ __('app.diary.act_placeholder') }}"></textarea></td>
                        <td><input type="text" name="atividades[0][qtde_orcada]"    class="form-control form-control-sm" placeholder="591,03 M2"></td>
                        <td><input type="text" name="atividades[0][qtde_realizada]" class="form-control form-control-sm"></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" name="atividades[0][evolucao_percentual]" class="form-control" step="1" min="0" max="100" placeholder="0">
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </td>
                        <td>
                            <select name="atividades[0][status_atividade]" class="form-control form-control-sm">
                                <option value="em_andamento">{{ __('app.diary.act_status_in_progress') }}</option>
                                <option value="paralisada">{{ __('app.diary.act_status_paused') }}</option>
                                <option value="finalizada">{{ __('app.diary.act_status_done') }}</option>
                                <option value="nao_iniciada">{{ __('app.diary.act_status_not_started') }}</option>
                            </select>
                        </td>
                        <td>
                            <select name="atividades[0][obra_fase_tarefa_id]" class="form-control form-control-sm sel-tarefa">
                                <option value="">{{ __('app.diary.act_select_phase') }}</option>
                            </select>
                        </td>
                        <td><input type="text" name="atividades[0][comentario]" class="form-control form-control-sm"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger px-1" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Comentários ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="rdo-section-title">{{ __('app.diary.section_comments') }}</div>
        <textarea name="comentarios" rows="4" class="form-control"
                  placeholder="{{ __('app.diary.comments_placeholder') }}">{{ old('comentarios') }}</textarea>
    </div>
</div>

{{-- ── Fotos ── --}}
<div class="card card-rdo mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="rdo-section-title mb-0">{{ __('app.diary.section_photos') }}</div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adicionarPasta()">
                <i class="fas fa-folder-plus mr-1"></i> {{ __('app.diary.new_folder') }}
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
                    <label class="custom-file-label">{{ __('app.diary.select_photos') }}</label>
                </div>
                <div class="foto-preview mt-2" id="preview-pasta-0"></div>
                <div class="pastas-inputs" id="inputs-pasta-0"></div>
            </div>
        </div>
        <small class="text-muted">{{ __('app.diary.photos_max') }}</small>
    </div>
</div>

{{-- Ocorrências --}}
<div class="card card-rdo mb-3">
    <div class="card-header bg-white py-2" style="cursor:pointer" data-toggle="collapse" data-target="#colOcorr">
        <span class="rdo-section-title mb-0" style="font-size:.8rem">
            <i class="fas fa-exclamation-triangle text-warning mr-1"></i> {{ __('app.diary.section_occurrences') }}
        </span>
        <span class="float-right text-muted small">{{ __('app.diary.expand') }}</span>
    </div>
    <div class="collapse" id="colOcorr">
        <div class="card-body pt-2">
            <textarea name="ocorrencias" rows="3" class="form-control mb-2"
                      placeholder="{{ __('app.diary.occurrences_placeholder') }}">{{ old('ocorrencias') }}</textarea>
            <label class="font-weight-bold small">{{ __('app.diary.section_solutions') }}</label>
            <textarea name="solucoes_adotadas" rows="2" class="form-control"
                      placeholder="{{ __('app.diary.solutions_placeholder') }}">{{ old('solucoes_adotadas') }}</textarea>
        </div>
    </div>
</div>

{{-- Botões --}}
<div class="d-flex justify-content-end mb-5">
    <a href="{{ route('diario.index') }}" class="btn btn-outline-secondary mr-2">{{ __('app.common.cancel') }}</a>
    <button type="submit" name="status_submit" value="rascunho" class="btn btn-outline-secondary mr-2">
        <i class="fas fa-save mr-1"></i> {{ __('app.diary.btn_save_draft') }}
    </button>
    <button type="submit" name="status_submit" value="finalizado" class="btn btn-primary">
        <i class="fas fa-check mr-1"></i> {{ __('app.diary.btn_finalize') }}
    </button>
</div>

<input type="hidden" name="redirect_obra" value="{{ request('obra_id') ? '1' : '' }}">
</form>
@stop

@section('js')
<script>
// Strings JS traduzidas via PHP
var jsStr = {
    noTasks:      "{{ __('app.diary.act_no_tasks') }}",
    noLink:       "{{ __('app.diary.act_no_task') }}",
    selectPhase:  "{{ __('app.diary.act_select_phase') }}",
    photosLabel:  "{{ __('app.diary.photos_selected') }}",
    selectPhotos: "{{ __('app.diary.select_photos') }}",
};

// ── Clima por turno ───────────────────────────────────────────────────────────
document.querySelectorAll('.btn-clima').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var turno = this.dataset.turno;
        document.querySelectorAll('.btn-clima[data-turno="'+turno+'"]').forEach(b => b.classList.remove('ativo'));
        this.classList.add('ativo');
        document.querySelector('.clima-val-'+turno).value = this.dataset.val;
    });
});

// ── Fases + Tarefas via AJAX ──────────────────────────────────────────────────
var tarefasDaFase = [];

document.getElementById('selectObra').addEventListener('change', function() {
    var obraId = this.value;
    var sel    = document.getElementById('selectFase');
    tarefasDaFase = [];
    atualizarDropdownsTarefas();
    if (!obraId) { sel.innerHTML = '<option value="">{{ __('app.diary.select_work_first') }}</option>'; return; }
    fetch('/api/diario/fases-obra/' + obraId)
        .then(r => r.json())
        .then(function(fases) {
            sel.innerHTML = '<option value="">-- {{ __('app.common.none') }} --</option>';
            fases.forEach(function(f) {
                var ativa = f.status === 'em_andamento' ? ' ({{ __('app.common.active') }})' : '';
                var s     = f.status === 'em_andamento' ? ' selected' : '';
                sel.innerHTML += '<option value="'+f.id+'"'+s+'>'+f.nome+ativa+'</option>';
            });
            var faseAtiva = fases.find(f => f.status === 'em_andamento');
            if (faseAtiva) carregarTarefasDaFase(faseAtiva.id);
        });
});

document.getElementById('selectFase').addEventListener('change', function() {
    if (this.value) carregarTarefasDaFase(this.value);
    else { tarefasDaFase = []; atualizarDropdownsTarefas(); }
});

function carregarTarefasDaFase(faseId) {
    fetch('/api/diario/tarefas-fase/' + faseId)
        .then(r => r.json())
        .then(function(tarefas) { tarefasDaFase = tarefas; atualizarDropdownsTarefas(); });
}

function buildTarefasHtml(selectedId) {
    if (!tarefasDaFase.length) return '<option value="">' + jsStr.noTasks + '</option>';
    var html = '<option value="">' + jsStr.noLink + '</option>';
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
        var cur = sel.value;
        sel.innerHTML = buildTarefasHtml(cur);
    });
}

// ── Linhas dinâmicas ──────────────────────────────────────────────────────────
var rowCounts = { mao_de_obra: 1, equipamentos: 1, atividades: 1 };

function addRow(tipo) {
    var idx   = rowCounts[tipo]++;
    var tbody = document.getElementById('body-' + tipo);
    var tpl   = document.querySelector('.tr-' + tipo);
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
        if (sel) sel.innerHTML = buildTarefasHtml(null);
    }
}

function removeRow(btn) {
    var tr = btn.closest('tr');
    var tbody = tr.closest('tbody');
    if (tbody.rows.length > 1) tr.remove();
    else {
        tr.querySelectorAll('input, textarea').forEach(el => el.value = el.type === 'number' ? 1 : '');
        tr.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    }
}

// ── Fotos em pastas ───────────────────────────────────────────────────────────
var pastaCount = 1, globalFotoIdx = 0;

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
            <label class="custom-file-label">${jsStr.selectPhotos}</label>
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
        bloco.querySelector('.custom-file-label').textContent = jsStr.selectPhotos + ' - ' + (input.value || 'Pasta');
    }, { once: true });
}

function bindFotoInput(inputEl) {
    inputEl.addEventListener('change', function() {
        var pastaIdx  = this.dataset.pastaIdx;
        var bloco     = this.closest('.pasta-bloco');
        var pastaNome = bloco.querySelector('.pasta-nome').value || 'Pasta 1';
        var preview   = document.getElementById('preview-pasta-' + pastaIdx);
        var hiddens   = document.getElementById('inputs-pasta-' + pastaIdx);
        bloco.querySelector('.custom-file-label').textContent = this.files.length + ' ' + jsStr.photosLabel;
        Array.from(this.files).forEach(function(file) {
            var reader = new FileReader();
            var fIdx = globalFotoIdx++;
            reader.onload = function(e) {
                var wrap = document.createElement('div');
                wrap.className = 'foto-item-preview';
                wrap.innerHTML = '<img src="'+e.target.result+'" alt=""><button type="button" class="btn-rm-foto" onclick="rmFoto(this)"><i class="fas fa-times"></i></button>';
                preview.appendChild(wrap);
            };
            reader.readAsDataURL(file);
            var h = document.createElement('input');
            h.type = 'hidden'; h.name = 'foto_pasta[' + fIdx + ']'; h.value = pastaNome;
            hiddens.appendChild(h);
        });
    });
}

function rmFoto(btn) { btn.closest('.foto-item-preview').remove(); }

document.querySelectorAll('.input-fotos').forEach(bindFotoInput);

// ── Status via botão ──────────────────────────────────────────────────────────
document.querySelectorAll('[name="status_submit"]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelector('[name="status"]').value = this.value;
    });
});

// Tooltip Bootstrap
$('[data-toggle="tooltip"]').tooltip();
</script>
@stop
