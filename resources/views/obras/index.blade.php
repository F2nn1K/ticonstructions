@extends('adminlte::page')

@section('title', __('app.menu.construction_schedule'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-hard-hat mr-2"></i>{{ __('app.menu.construction_schedule') }}</h1>
        <a href="{{ route('obras.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> {{ __('app.works.new') }}
        </a>
    </div>
@stop

@section('content')
<style>
.card-obra {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
    border: none;
    transition: transform .15s;
}
.card-obra:hover { transform: translateY(-2px); }
.card-obra .card-header {
    background: linear-gradient(135deg, #3c8dbc 0%, #2a6a8a 100%);
    color: #fff;
    border-radius: 10px 10px 0 0;
    padding: 14px 18px;
}
.badge-status { font-size: .78rem; padding: 4px 10px; border-radius: 20px; }
.progress-fase { height: 8px; border-radius: 4px; }
.fase-pill {
    display: inline-block;
    font-size: .72rem;
    padding: 3px 10px;
    border-radius: 20px;
    background: #e3f2fd;
    color: #1565c0;
    font-weight: 600;
    border: 1px solid #bbdefb;
}
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

<div class="row">
@forelse($obras as $obra)
    @php
        $badgeColor = match($obra->status) {
            'planejamento' => 'secondary',
            'em_andamento' => 'primary',
            'concluida'    => 'success',
            'suspensa'     => 'warning',
            'cancelada'    => 'danger',
            default        => 'secondary'
        };
        $badgeLabel = match($obra->status) {
            'planejamento' => __('app.works.status_planning'),
            'em_andamento' => __('app.works.status_in_progress'),
            'concluida'    => __('app.works.status_completed'),
            'suspensa'     => __('app.works.status_suspended'),
            'cancelada'    => __('app.works.status_cancelled'),
            default        => $obra->status
        };
        $faseAtiva = $obra->faseAtiva;
        $percentual = $obra->percentual_geral;
    @endphp
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card card-obra">
            <div class="card-header d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-0 font-weight-bold">{{ $obra->nome }}</h6>
                    @if($obra->codigo)
                        <small class="opacity-75">{{ $obra->codigo }}</small>
                    @endif
                </div>
                <span class="badge badge-{{ $badgeColor }} badge-status">{{ $badgeLabel }}</span>
            </div>
            <div class="card-body pb-2">
                @if($obra->cliente)
                    <div class="text-muted small mb-1">
                        <i class="fas fa-user mr-1"></i> {{ $obra->cliente }}
                    </div>
                @endif
                @if($obra->cidade)
                    <div class="text-muted small mb-2">
                        <i class="fas fa-map-marker-alt mr-1"></i> {{ $obra->cidade }}{{ $obra->estado ? ', '.$obra->estado : '' }}
                    </div>
                @endif

                <div class="mb-2">
                    @if($faseAtiva)
                        <span class="fase-pill">
                            <i class="fas fa-circle fa-xs mr-1"></i>{{ $faseAtiva->nome }}
                        </span>
                    @else
                        <span class="text-muted small">{{ __('app.works.no_active_phase') }}</span>
                    @endif
                </div>

                <div class="mb-1">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">{{ __('app.works.overall_progress') }}</span>
                        <span class="font-weight-bold">{{ $percentual }}%</span>
                    </div>
                    <div class="progress progress-fase">
                        <div class="progress-bar {{ $percentual >= 100 ? 'bg-success' : 'bg-primary' }}"
                             style="width: {{ $percentual }}%"></div>
                    </div>
                </div>

                @if($obra->data_fim_prevista)
                    <div class="text-muted small mt-2">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ __('app.works.est_end') }}: {{ $obra->data_fim_prevista->format('d/m/Y') }}
                    </div>
                @endif
            </div>
            <div class="card-footer bg-transparent d-flex justify-content-between pt-2">
                <a href="{{ route('obras.show', $obra) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> {{ __('app.works.details') }}
                </a>
                <a href="{{ route('obras.lancamentos.create', $obra) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-plus"></i> {{ __('app.works.entry') }}
                </a>
                <a href="{{ route('obras.edit', $obra) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-edit"></i>
                </a>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-hard-hat fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('app.works.none_registered') }}</h5>
                <p class="text-muted">{{ __('Clique em') }} <strong>{{ __('app.works.new') }}</strong> {{ __('para começar o cronograma.') }}</p>
                <a href="{{ route('obras.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> {{ __('app.works.create_first') }}
                </a>
            </div>
        </div>
    </div>
@endforelse
</div>

{{ $obras->links() }}
@stop
