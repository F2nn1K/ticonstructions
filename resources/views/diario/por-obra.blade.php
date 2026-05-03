@extends('adminlte::page')

@section('title', __('Diário') . ' — ' . $obra->nome)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('obras.show', $obra) }}" class="btn btn-sm btn-outline-secondary mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="mb-0">
                    <i class="fas fa-book-open mr-2" style="color:var(--ti-gold)"></i>
                    {{ __('Diário de Obra') }}
                </h1>
                <small class="text-muted">{{ $obra->nome }}</small>
            </div>
        </div>
        <a href="{{ route('diario.create', ['obra_id' => $obra->id]) }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> {{ __('Novo Registro') }}
        </a>
    </div>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@forelse($registros as $reg)
    @php $temOcorr = $reg->temOcorrencias(); @endphp
    <div class="card mb-3" style="border-left:4px solid {{ $temOcorr ? '#C94040' : 'var(--ti-gold,#C9A84C)' }}; border-radius:0 10px 10px 0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="font-weight-bold" style="font-size:1rem">
                        {{ $reg->titulo_formatado }}
                        @if($temOcorr)
                            <span class="badge badge-danger ml-2" style="font-size:.65rem">
                                <i class="fas fa-exclamation-triangle mr-1"></i>{{ __('Ocorrência') }}
                            </span>
                        @endif
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-calendar mr-1"></i>{{ $reg->data_registro->format('d/m/Y') }}
                        @if($reg->fase) · {{ $reg->fase->nome }} @endif
                        @if($reg->condicoes_climaticas) · {{ $reg->clima_icone }} {{ $reg->clima_label }} @endif
                        @if($reg->total_trabalhadores)
                            · <i class="fas fa-users mr-1"></i>{{ $reg->total_trabalhadores }}
                        @endif
                    </small>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('diario.show', $reg) }}" class="btn btn-xs btn-outline-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('diario.edit', $reg) }}" class="btn btn-xs btn-outline-secondary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route('diario.destroy', $reg) }}"
                          class="d-inline" onsubmit="return confirm('{{ __('Excluir?') }}')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>

            <div class="mt-2 small" style="color:#444; white-space:pre-line; line-height:1.6">
                {{ Str::limit($reg->atividades_executadas, 200) }}
            </div>

            @if($temOcorr)
                <div class="mt-2 small" style="background:#fff5f5; border-radius:6px; padding:8px 12px; color:#721c24">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ Str::limit($reg->ocorrencias, 150) }}
                </div>
            @endif

            @if($reg->totalFotos() > 0)
                <div class="mt-1">
                    <span class="badge badge-light" style="font-size:.7rem">
                        <i class="fas fa-camera mr-1"></i>{{ $reg->totalFotos() }} {{ __('foto(s)') }}
                    </span>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-book-open fa-3x mb-3" style="opacity:.25"></i>
            <p class="mb-2">{{ __('Nenhum registro para esta obra ainda.') }}</p>
            <a href="{{ route('diario.create', ['obra_id' => $obra->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> {{ __('Criar Primeiro Registro') }}
            </a>
        </div>
    </div>
@endforelse

@if($registros->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $registros->links() }}
    </div>
@endif

@stop
