@extends('adminlte::page')

@section('title', '#' . $diario->numero . ' — ' . $diario->titulo_formatado)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <a href="{{ route('diario.index') }}" class="btn btn-sm btn-outline-secondary mr-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="mb-0">
                <i class="fas fa-book-open mr-2" style="color:var(--ti-gold)"></i>
                @if($diario->numero) <span style="color:#999">#{{ $diario->numero }}</span> @endif
                {{ $diario->titulo_formatado }}
            </h1>
            <small class="text-muted">{{ $diario->obra->nome }}
                @if($diario->fase) · {{ $diario->fase->nome }} @endif
            </small>
        </div>
    </div>
    <div>
        <span class="badge badge-{{ $diario->status_badge }} mr-2" style="font-size:.8rem">
            {{ $diario->status_label }}
        </span>
        <a href="{{ route('diario.edit', $diario) }}" class="btn btn-sm btn-outline-secondary mr-1">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
        <a href="{{ route('diario.create', ['obra_id' => $diario->obra_id]) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i> Novo Registro
        </a>
    </div>
</div>
@stop

@section('content')
@include('diario._styles')

<div class="card card-rdo mb-3">
<div class="card-body">

    {{-- ── Cabeçalho do RDO ── --}}
    <div class="rdo-header">
        <div class="row">
            <div class="col-md-6">
                <div class="rdo-num">
                    #{{ $diario->numero ?? '—' }} — {{ $diario->data_registro->translatedFormat('d M Y (l)') }}
                </div>
                @if($diario->responsavel)
                    <div class="rdo-meta-item text-muted">
                        Criado por: <strong>{{ $diario->responsavel->name }}</strong>
                    </div>
                @endif
            </div>
            <div class="col-md-6 text-md-right">
                @if($diario->obra->cliente)
                    <div class="rdo-meta-item"><strong>Cliente:</strong> {{ $diario->obra->cliente }}</div>
                @endif
                <div class="rdo-meta-item"><strong>Obra:</strong> {{ $diario->obra->nome }}</div>
                @php
                    $dataFim  = $diario->obra->data_fim_prevista;
                    $dataIni  = $diario->obra->data_inicio_prevista ?? $diario->obra->data_inicio_real;
                @endphp
                @if($dataFim)
                    @php
                        $hoje      = now()->startOfDay();
                        $faltam    = $hoje->diffInDays($dataFim, false);
                        $decorridos= $dataIni ? $dataIni->diffInDays($hoje) : null;
                        $total     = $dataIni ? $dataIni->diffInDays($dataFim) : null;
                    @endphp
                    <div class="rdo-meta-item">
                        Início: {{ $dataIni?->format('d/m/Y') ?? '—' }} &nbsp;–&nbsp; Término: {{ $dataFim->format('d/m/Y') }}
                    </div>
                    <div class="rdo-meta-item">
                        Prazo: <strong class="{{ $faltam < 30 ? 'text-danger' : 'text-primary' }}">
                            {{ $faltam >= 0 ? "faltam {$faltam} dia(s) corridos" : abs($faltam)." dia(s) em atraso" }}
                        </strong>
                    </div>
                    @if($decorridos !== null && $total)
                        <div class="rdo-meta-item text-muted" style="font-size:.78rem">
                            {{ $decorridos }} dia(s) decorridos de {{ $total }} dia(s)
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- ── Tempo ── --}}
    @if($diario->tempo_manha || $diario->tempo_tarde || $diario->tempo_noite)
    <div class="mb-3">
        <div class="rdo-section-title">Tempo</div>
        <div class="d-flex flex-wrap" style="gap:16px">
            @foreach([
                'Manhã'  => $diario->tempo_manha,
                'Tarde'  => $diario->tempo_tarde,
                'Noite'  => $diario->tempo_noite,
            ] as $turnoNome => $turno)
            @if($turno)
            <div style="text-align:center; min-width:90px">
                <div style="font-size:.7rem; font-weight:700; text-transform:uppercase; color:#999">{{ $turnoNome }}</div>
                <div style="font-size:1.8rem; line-height:1.2">
                    {{ $diario->climaIconePorValor($turno['clima'] ?? 'sol') }}
                </div>
                <div style="font-size:.78rem; color:{{ ($turno['status']??'') === 'impraticavel' ? '#e53935':'#555' }}">
                    {{ ($turno['status']??'praticavel') === 'praticavel' ? 'Praticável' : 'Impraticável' }}
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Mão de obra ── --}}
    @if($diario->maoDeObra->count())
    <div class="mb-3">
        <div class="rdo-section-title">Mão de Obra</div>
        <table class="rdo-show-table">
            <thead>
                <tr>
                    <th style="width:60px">Qtde</th>
                    <th>Função / Cargo</th>
                    <th>Profissional / Fornecedor</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($diario->maoDeObra as $mo)
                <tr>
                    <td class="text-center font-weight-bold">{{ $mo->quantidade }}</td>
                    <td>{{ $mo->funcao }}</td>
                    <td>{{ $mo->profissional_fornecedor ?? '—' }}</td>
                    <td>{{ $mo->observacao ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── Equipamentos ── --}}
    @if($diario->equipamentos->count())
    <div class="mb-3">
        <div class="rdo-section-title">Equipamentos</div>
        <table class="rdo-show-table">
            <thead>
                <tr>
                    <th style="width:60px">Qtde</th>
                    <th>Descrição do Equipamento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($diario->equipamentos as $eq)
                <tr>
                    <td class="text-center font-weight-bold">{{ $eq->quantidade }}</td>
                    <td>{{ $eq->descricao }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── Atividades ── --}}
    @if($diario->atividades->count())
    <div class="mb-3">
        <div class="rdo-section-title">Atividades</div>
        <table class="rdo-show-table">
            <thead>
                <tr>
                    <th>Atividade / Descrição</th>
                    <th style="width:110px">Qtde Orçada</th>
                    <th style="width:110px">Qtde Realizada</th>
                    <th style="width:80px">Evolução</th>
                    <th style="width:140px">Status da Atividade</th>
                    <th>Comentário</th>
                </tr>
            </thead>
            <tbody>
                @foreach($diario->atividades as $atv)
                <tr>
                    <td>
                        {{ $atv->descricao }}
                        @if($atv->obra_fase_tarefa_id)
                            <br><small class="text-muted">
                                <i class="fas fa-link mr-1" style="color:var(--rdo-gold)"></i>
                                Cronograma: {{ $atv->tarefaCronograma?->nome ?? 'Tarefa vinculada' }}
                            </small>
                        @endif
                    </td>
                    <td>{{ $atv->qtde_orcada ?? '—' }}</td>
                    <td>{{ $atv->qtde_realizada ?? '—' }}</td>
                    <td class="text-center">
                        @if($atv->evolucao_percentual !== null)
                            <strong>{{ rtrim(rtrim(number_format($atv->evolucao_percentual, 1), '0'), '.') }}%</strong>
                        @else —
                        @endif
                    </td>
                    <td>
                        <span class="status-dot" style="background:{{ $atv->status_cor }}"></span>
                        {{ $atv->status_label }}
                    </td>
                    <td>{{ $atv->comentario ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── Comentários ── --}}
    @if($diario->comentarios)
    <div class="mb-3">
        <div class="rdo-section-title">Comentários</div>
        <p style="white-space:pre-line; color:#333; line-height:1.7">{{ $diario->comentarios }}</p>
    </div>
    @endif

    {{-- ── Ocorrências ── --}}
    @if($diario->temOcorrencias())
    <div class="mb-3" style="background:#fff5f5; border-left:4px solid #C94040; border-radius:0 8px 8px 0; padding:14px 18px">
        <div class="rdo-section-title" style="color:#C94040; border-color:#C94040">Ocorrências / Problemas</div>
        <p style="white-space:pre-line; color:#333">{{ $diario->ocorrencias }}</p>
        @if($diario->solucoes_adotadas)
            <div class="rdo-section-title" style="color:#1A9E6E; border-color:#1A9E6E">Soluções Adotadas</div>
            <p style="white-space:pre-line; color:#333">{{ $diario->solucoes_adotadas }}</p>
        @endif
    </div>
    @endif

    {{-- ── Fotos ── --}}
    @if($diario->totalFotos() > 0)
    <div class="mb-3">
        <div class="rdo-section-title">Fotos</div>
        @foreach($diario->fotos_agrupadas as $pasta => $caminhos)
            <div class="pasta-titulo"><i class="fas fa-folder text-warning mr-1"></i> {{ $pasta }}</div>
            <div class="foto-grid-show mb-3">
                @foreach($caminhos as $caminho)
                    <a href="{{ Storage::url($caminho) }}" target="_blank">
                        <img src="{{ Storage::url($caminho) }}" alt="Foto">
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>
    @endif

</div>
</div>

{{-- Ações --}}
<div class="d-flex justify-content-end mb-4" style="gap:8px">
    <form method="POST" action="{{ route('diario.destroy', $diario) }}"
          onsubmit="return confirm('Excluir este registro permanentemente?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-trash mr-1"></i> Excluir
        </button>
    </form>
    <a href="{{ route('diario.edit', $diario) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-edit mr-1"></i> Editar
    </a>
    <a href="{{ route('diario.create', ['obra_id' => $diario->obra_id]) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Novo Registro (mesma obra)
    </a>
</div>
@stop
