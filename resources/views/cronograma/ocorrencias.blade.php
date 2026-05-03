@extends('adminlte::page')

@section('title', __('Ocorrências no Cronograma'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-exclamation-circle mr-2 text-warning"></i>
                {{ __('Ocorrências no Cronograma') }}
            </h1>
            <small class="text-muted">{{ __('Registros de imprevistos e impactos nas fases') }}</small>
        </div>
        <a href="{{ route('cronograma.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> {{ __('Voltar') }}
        </a>
    </div>
@stop

@section('content')

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Data') }}</th>
                        <th>{{ __('Obra') }}</th>
                        <th>{{ __('Fase') }}</th>
                        <th>{{ __('Tipo') }}</th>
                        <th>{{ __('Título') }}</th>
                        <th>{{ __('Impacto') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ocorrencias as $oc)
                        <tr>
                            <td class="text-muted small">{{ $oc->data_ocorrencia?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('obras.show', $oc->obraFase->obra) }}">
                                    {{ $oc->obraFase->obra->nome }}
                                </a>
                            </td>
                            <td class="small">{{ $oc->obraFase->nome }}</td>
                            <td>
                                <span class="badge badge-warning">{{ $oc->tipo }}</span>
                            </td>
                            <td>{{ $oc->titulo }}</td>
                            <td class="small text-muted">
                                @if($oc->impacto_dias)
                                    <i class="fas fa-clock mr-1 text-danger"></i>{{ $oc->impacto_dias }}d
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ __('Nenhuma ocorrência registrada.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ocorrencias->hasPages())
        <div class="card-footer">{{ $ocorrencias->links() }}</div>
    @endif
</div>

@stop
