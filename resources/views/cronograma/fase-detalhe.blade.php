@extends('adminlte::page')
@section('title', $fase->nome_personalizado ?? $fase->faseCatalogo->nome ?? __('Fase'))
@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <a href="{{ route('cronograma.index') }}" class="text-muted" style="text-decoration:none;font-size:.8rem">
            <i class="fas fa-arrow-left mr-1"></i>{{ __('Cronograma') }}
        </a>
        <span class="text-muted mx-1">/</span>
        <a href="{{ route('obras.show', $obra) }}" class="text-muted" style="text-decoration:none;font-size:.8rem">{{ $obra->nome }}</a>
        <h1 class="mb-0 mt-1" style="font-size:1.4rem">
            <i class="fas fa-tasks mr-2" style="color:var(--ti-gold)"></i>
            {{ $fase->nome_personalizado ?? ($fase->faseCatalogo->nome ?? '—') }}
        </h1>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modalAddTarefa">
            <i class="fas fa-plus mr-1"></i>{{ __('Nova Tarefa') }}
        </button>
    </div>
</div>
@stop
@section('content')
<style>
.subfase-card { border-radius:10px; border:none; box-shadow:0 1px 8px rgba(0,0,0,.06); margin-bottom:1rem; }
.subfase-header { padding:10px 16px; border-radius:10px 10px 0 0; background:#f7f5f0; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between; }
.subfase-title { font-weight:700; font-size:.85rem; color:#4a3f2f; text-transform:uppercase; letter-spacing:.04em; }
.tarefa-row { display:flex; align-items:center; padding:9px 16px; border-bottom:1px solid #f5f5f5; transition:.15s; cursor:pointer; }
.tarefa-row:last-child { border-bottom:none; }
.tarefa-row:hover { background:#fafaf7; }
.tarefa-row.done { opacity:.65; }
.tarefa-check { width:22px; height:22px; border-radius:50%; border:2px solid #ccc; display:flex; align-items:center; justify-content:center; flex-shrink:0; cursor:pointer; transition:.2s; }
.tarefa-check.checked { background:#1A9E6E; border-color:#1A9E6E; }
.tarefa-check.checked i { color:#fff; font-size:.65rem; }
.tarefa-nome { flex:1; margin-left:10px; font-size:.83rem; }
.tarefa-row.done .tarefa-nome { text-decoration:line-through; color:#999; }
.tarefa-meta { font-size:.67rem; color:#aaa; margin-left:8px; white-space:nowrap; }
.prog-ring-wrap { display:flex; flex-direction:column; align-items:center; }
.nav-fases { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:1.5rem; }
.nav-fase-btn { font-size:.72rem; padding:5px 12px; border-radius:20px; border:1.5px solid #ddd; background:#fff; color:#555; text-decoration:none; transition:.15s; }
.nav-fase-btn.active { background:#A8873A; border-color:#A8873A; color:#fff; font-weight:700; }
.nav-fase-btn.done  { background:#d4edda; border-color:#b8dac0; color:#155724; }
.nav-fase-btn.late  { background:#f8d7da; border-color:#f1b0b7; color:#721c24; }
</style>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif

{{-- Navegação entre fases --}}
<div class="nav-fases">
    @foreach($outrasFases as $f)
        @php
            $isAtual = $f->id === $fase->id;
            $cls = $isAtual ? 'active' : ($f->status==='concluida' ? 'done' : ($f->status==='atrasada'?'late':''));
        @endphp
        <a href="{{ route('cronograma.fase-detalhe', [$obra, $f]) }}" class="nav-fase-btn {{ $cls }}">
            {{ $f->ordem }}. {{ $f->nome_personalizado ?? $f->faseCatalogo->nome ?? '—' }}
            @if($f->status==='concluida') <i class="fas fa-check ml-1"></i> @endif
        </a>
    @endforeach
</div>

<div class="row">
    {{-- Coluna principal: checklist --}}
    <div class="col-lg-8">

        @if($grupos->isEmpty())
        <div class="card subfase-card">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-list-check fa-2x mb-3" style="opacity:.3"></i>
                <p class="mb-3">{{ __('Nenhuma tarefa nesta fase ainda.') }}</p>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalAddTarefa">
                    <i class="fas fa-plus mr-1"></i>{{ __('Adicionar Tarefa') }}
                </button>
            </div>
        </div>
        @else
        @foreach($grupos as $grupoNome => $tarefas)
        @php
            $pg = $progressoGrupos[$grupoNome];
            $cor = $pg['perc'] >= 100 ? '#1A9E6E' : ($pg['perc'] > 0 ? '#A8873A' : '#ccc');
        @endphp
        <div class="card subfase-card">
            <div class="subfase-header">
                <span class="subfase-title">
                    <i class="fas fa-layer-group mr-2" style="color:#A8873A"></i>
                    {{ $grupoNome }}
                </span>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:80px;height:8px;background:#e0e0e0;border-radius:4px;overflow:hidden">
                        <div style="height:100%;width:{{ $pg['perc'] }}%;background:{{ $cor }};border-radius:4px;transition:.3s"></div>
                    </div>
                    <span style="font-size:.72rem;font-weight:700;color:{{ $cor }}">{{ $pg['concluidas'] }}/{{ $pg['total'] }}</span>
                    @if($pg['perc']>=100)<i class="fas fa-check-circle text-success ml-1"></i>@endif
                </div>
            </div>
            <div>
                @foreach($tarefas as $tarefa)
                <div class="tarefa-row {{ $tarefa->concluida ? 'done' : '' }}" id="row-{{ $tarefa->id }}">
                    <button class="tarefa-check {{ $tarefa->concluida ? 'checked' : '' }} btn-marcar"
                            data-id="{{ $tarefa->id }}"
                            title="{{ $tarefa->concluida ? __('Desmarcar') : __('Marcar como concluída') }}">
                        @if($tarefa->concluida)<i class="fas fa-check"></i>@endif
                    </button>
                    <span class="tarefa-nome">{{ $tarefa->nome }}</span>
                    @if($tarefa->concluida && $tarefa->data_conclusao)
                    <span class="tarefa-meta">
                        <i class="fas fa-calendar-check mr-1"></i>
                        {{ \Carbon\Carbon::parse($tarefa->data_conclusao)->format('d/m') }}
                    </span>
                    @endif
                    <form method="POST" action="{{ route('cronograma.tarefa-excluir', $tarefa->id) }}" class="d-inline ml-2"
                          onsubmit="return confirm('{{ __('Remover esta tarefa?') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs text-muted" style="background:none;border:none;padding:0 4px" title="{{ __('Remover') }}">
                            <i class="fas fa-times" style="font-size:.65rem"></i>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @endif

        {{-- Adicionar nova fase à obra --}}
        <div class="card" style="border-radius:10px;border:1.5px dashed #ddd;box-shadow:none;margin-top:1.5rem">
            <div class="card-body py-3">
                <div class="sec-title mb-3" style="font-size:.8rem"><i class="fas fa-plus-circle mr-1"></i>{{ __('Adicionar Nova Fase à Obra') }}</div>
                <form method="POST" action="{{ route('cronograma.adicionar-fase', $obra) }}" class="row align-items-end g-2">
                    @csrf
                    <div class="col-md-4">
                        <label class="small font-weight-bold text-muted mb-1">{{ __('Fase do Catálogo') }}</label>
                        <select name="fase_catalogo_id" class="form-control form-control-sm" required>
                            <option value="">{{ __('Selecione...') }}</option>
                            @foreach($fasesCatalogo as $fc)
                                <option value="{{ $fc->id }}">{{ $fc->nome }}</option>
                            @endforeach
                            <option value="">── {{ __('Personalizada') }} ──</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-muted mb-1">{{ __('Nome (opcional)') }}</label>
                        <input type="text" name="nome_personalizado" class="form-control form-control-sm" placeholder="{{ __('Ex: Estrutura Bloco B') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold text-muted mb-1">{{ __('Início') }}</label>
                        <input type="date" name="data_inicio_baseline" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold text-muted mb-1">{{ __('Fim') }}</label>
                        <input type="date" name="data_fim_baseline" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-plus"></i></button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Coluna lateral: resumo --}}
    <div class="col-lg-4">
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.08);position:sticky;top:80px">
            <div class="card-body text-center py-4">
                {{-- Anel de progresso --}}
                <div class="prog-ring-wrap mb-3">
                    <svg width="110" height="110" viewBox="0 0 110 110">
                        <circle cx="55" cy="55" r="46" fill="none" stroke="#eee" stroke-width="10"/>
                        <circle cx="55" cy="55" r="46" fill="none"
                                stroke="{{ $progressoFase>=100?'#1A9E6E':'#A8873A' }}" stroke-width="10"
                                stroke-dasharray="{{ round(2*3.14159*46) }}"
                                stroke-dashoffset="{{ round(2*3.14159*46*(1-$progressoFase/100)) }}"
                                stroke-linecap="round"
                                transform="rotate(-90 55 55)"
                                style="transition:stroke-dashoffset .5s ease"/>
                        <text x="55" y="52" text-anchor="middle" font-size="20" font-weight="800" fill="{{ $progressoFase>=100?'#1A9E6E':'#A8873A' }}">{{ $progressoFase }}%</text>
                        <text x="55" y="68" text-anchor="middle" font-size="9" fill="#999">{{ __('concluído') }}</text>
                    </svg>
                </div>

                <div class="font-weight-bold" style="font-size:1rem">{{ $fase->nome_personalizado ?? $fase->faseCatalogo->nome ?? '—' }}</div>
                <div class="text-muted small mb-3">{{ $obra->nome }}</div>

                <div class="row text-center">
                    <div class="col-4">
                        <div style="font-size:1.3rem;font-weight:800;color:#1A9E6E">{{ $tarefasConcluidas }}</div>
                        <div class="text-muted" style="font-size:.68rem">{{ __('Feitas') }}</div>
                    </div>
                    <div class="col-4" style="border-left:1px solid #eee;border-right:1px solid #eee">
                        <div style="font-size:1.3rem;font-weight:800;color:#A8873A">{{ $totalTarefas - $tarefasConcluidas }}</div>
                        <div class="text-muted" style="font-size:.68rem">{{ __('Pendentes') }}</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:1.3rem;font-weight:800">{{ $totalTarefas }}</div>
                        <div class="text-muted" style="font-size:.68rem">{{ __('Total') }}</div>
                    </div>
                </div>

                <hr>

                {{-- Status e datas --}}
                <div class="text-left" style="font-size:.78rem">
                    @php
                        $stColors = ['pendente'=>'secondary','em_andamento'=>'primary','concluida'=>'success','atrasada'=>'danger','suspensa'=>'warning'];
                        $stLabels = ['pendente'=>__('Pendente'),'em_andamento'=>__('Em Andamento'),'concluida'=>__('Concluída'),'atrasada'=>__('Atrasada'),'suspensa'=>__('Suspensa')];
                    @endphp
                    <div class="mb-2">
                        <span class="badge badge-{{ $stColors[$fase->status]??'secondary' }}">{{ $stLabels[$fase->status]??$fase->status }}</span>
                    </div>
                    @if($fase->data_inicio_baseline)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ __('Início previsto') }}</span>
                        <strong>{{ \Carbon\Carbon::parse($fase->data_inicio_baseline)->format('d/m/Y') }}</strong>
                    </div>
                    @endif
                    @if($fase->data_fim_baseline)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ __('Fim previsto') }}</span>
                        <strong>{{ \Carbon\Carbon::parse($fase->data_fim_baseline)->format('d/m/Y') }}</strong>
                    </div>
                    @endif
                    @if($fase->data_inicio_real)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ __('Início real') }}</span>
                        <strong class="text-success">{{ \Carbon\Carbon::parse($fase->data_inicio_real)->format('d/m/Y') }}</strong>
                    </div>
                    @endif
                    @if($fase->data_fim_real)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">{{ __('Conclusão') }}</span>
                        <strong class="text-success">{{ \Carbon\Carbon::parse($fase->data_fim_real)->format('d/m/Y') }}</strong>
                    </div>
                    @endif
                </div>

                {{-- Progresso por sub-fase --}}
                @if($progressoGrupos->count() > 0)
                <hr>
                <div class="text-left">
                    <div class="font-weight-bold text-muted mb-2" style="font-size:.72rem;text-transform:uppercase">{{ __('Sub-fases') }}</div>
                    @foreach($progressoGrupos as $gn => $pg)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1" style="font-size:.72rem">
                            <span class="text-truncate" style="max-width:140px" title="{{ $gn }}">{{ $gn }}</span>
                            <span style="font-weight:700;color:{{ $pg['perc']>=100?'#1A9E6E':'#A8873A' }}">{{ $pg['perc'] }}%</span>
                        </div>
                        <div style="height:5px;background:#eee;border-radius:3px;overflow:hidden">
                            <div style="height:100%;width:{{ $pg['perc'] }}%;background:{{ $pg['perc']>=100?'#1A9E6E':'#A8873A' }};border-radius:3px"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal: adicionar tarefa --}}
<div class="modal fade" id="modalAddTarefa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2" style="color:#A8873A"></i>{{ __('Nova Tarefa') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ route('cronograma.tarefa-adicionar', $fase) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Sub-fase (grupo)') }}</label>
                        <input type="text" name="grupo" class="form-control" list="grupos-existentes"
                               placeholder="{{ __('Ex: Estudos Técnicos, Projetos...') }}">
                        <datalist id="grupos-existentes">
                            @foreach($grupos->keys() as $g)<option value="{{ $g }}">@endforeach
                        </datalist>
                        <small class="text-muted">{{ __('Digite ou selecione um grupo existente') }}</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">{{ __('Descrição da Tarefa') }} <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control" required placeholder="{{ __('Ex: Levantamento topográfico...') }}">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>{{ __('Adicionar') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
// Marcar/desmarcar tarefa via AJAX
document.querySelectorAll('.btn-marcar').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var id  = this.dataset.id;
        var row = document.getElementById('row-' + id);
        var chk = this;

        fetch('{{ url("cronograma/tarefa") }}/' + id + '/marcar', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.concluida) {
                chk.classList.add('checked');
                chk.innerHTML = '<i class="fas fa-check"></i>';
                row.classList.add('done');
            } else {
                chk.classList.remove('checked');
                chk.innerHTML = '';
                row.classList.remove('done');
            }
            // Atualizar progresso da página sem reload
            updateProgresso(data.percentual);
        });
    });
});

function updateProgresso(perc) {
    // Recarregar a página para refletir os grupos corretamente
    setTimeout(() => location.reload(), 600);
}
</script>
@stop
