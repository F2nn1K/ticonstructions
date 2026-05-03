@extends('adminlte::page')

@section('title', __('app.menu.daily_timesheet'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-clock mr-2" style="color:var(--ti-gold)"></i>
                {{ __('app.menu.daily_timesheet') }}
            </h1>
            <small class="text-muted">{{ __('Registro diário de presença e horas trabalhadas') }}</small>
        </div>
        <a href="{{ route('funcionarios.apontamento.aprovar') }}" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-check-circle mr-1"></i> {{ __('app.menu.approve_timesheets') }}
        </a>
    </div>
@stop

@section('content')
<style>
.kpi-mini { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); }
.kpi-mini .k-label { font-size:.72rem; text-transform:uppercase; font-weight:700; letter-spacing:.05em; opacity:.7; }
.kpi-mini .k-val   { font-size:1.8rem; font-weight:800; line-height:1.2; }
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- KPIs --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-muted">{{ __('Total do Dia') }}</div>
                <div class="k-val text-dark">{{ $totalDia }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:#1A9E6E">{{ __('Aprovados') }}</div>
                <div class="k-val" style="color:#1A9E6E">{{ $totalAprovados }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label" style="color:var(--ti-gold,#C9A84C)">{{ __('Pendentes') }}</div>
                <div class="k-val" style="color:var(--ti-gold,#C9A84C)">{{ $totalPendentes }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini">
            <div class="card-body py-3 text-center">
                <div class="k-label text-muted">{{ __('Média de Horas') }}</div>
                <div class="k-val text-dark">{{ $mediaHoras ? number_format($mediaHoras, 1) . 'h' : '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Formulário de registro --}}
    <div class="col-lg-4 mb-4">
        <div class="card" style="border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header" style="background:linear-gradient(135deg,#A8873A,#E2C87A); border-radius:12px 12px 0 0">
                <h6 class="mb-0 font-weight-bold text-white">
                    <i class="fas fa-plus-circle mr-2"></i>{{ __('Registrar Apontamento') }}
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('funcionarios.apontamento.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold">{{ __('Funcionário') }} <span class="text-danger">*</span></label>
                        <select name="funcionario_id" class="form-control form-control-sm @error('funcionario_id') is-invalid @enderror" required>
                            <option value="">{{ __('Selecione...') }}</option>
                            @foreach($funcionarios as $f)
                                <option value="{{ $f->id }}" {{ old('funcionario_id') == $f->id ? 'selected' : '' }}>
                                    {{ $f->nome }} — {{ $f->funcao ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">{{ __('Data') }} <span class="text-danger">*</span></label>
                        <input type="date" name="data" value="{{ old('data', $data) }}"
                               class="form-control form-control-sm @error('data') is-invalid @enderror" required>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">{{ __('Obra') }}</label>
                        <select name="obra_id" class="form-control form-control-sm">
                            <option value="">{{ __('Nenhuma') }}</option>
                            @foreach($obras as $ob)
                                <option value="{{ $ob->id }}" {{ old('obra_id') == $ob->id ? 'selected' : '' }}>
                                    {{ $ob->nome }}{{ $ob->codigo ? ' ('.$ob->codigo.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">{{ __('Hora de Entrada') }}</label>
                                <input type="time" name="hora_entrada" value="{{ old('hora_entrada') }}"
                                       class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">{{ __('Hora de Saída') }}</label>
                                <input type="time" name="hora_saida" value="{{ old('hora_saida') }}"
                                       class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">{{ __('Saída Almoço') }}</label>
                                <input type="time" name="hora_almoco_saida" value="{{ old('hora_almoco_saida') }}"
                                       class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">{{ __('Retorno Almoço') }}</label>
                                <input type="time" name="hora_almoco_retorno" value="{{ old('hora_almoco_retorno') }}"
                                       class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">{{ __('Observações') }}</label>
                        <textarea name="observacoes" rows="2"
                                  class="form-control form-control-sm"
                                  placeholder="{{ __('Opcional...') }}">{{ old('observacoes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-sm btn-block">
                        <i class="fas fa-save mr-1"></i> {{ __('Salvar Apontamento') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Listagem do dia --}}
    <div class="col-lg-8 mb-4">
        <div class="card" style="border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07)">
            <div class="card-header bg-white d-flex justify-content-between align-items-center" style="border-radius:12px 12px 0 0">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-list mr-2" style="color:var(--ti-gold)"></i>
                    {{ __('Apontamentos de') }}
                    {{ \Carbon\Carbon::parse($data)->translatedFormat('d/m/Y') }}
                </h6>
                <form method="GET" action="{{ route('funcionarios.apontamento.index') }}" class="d-flex gap-1 align-items-center">
                    <input type="date" name="data" value="{{ $data }}" class="form-control form-control-sm" style="width:150px"
                           onchange="this.form.submit()">
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Funcionário') }}</th>
                                <th>{{ __('Obra') }}</th>
                                <th>{{ __('Entrada') }}</th>
                                <th>{{ __('Saída') }}</th>
                                <th>{{ __('Horas') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($apontamentos as $ap)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold small">{{ $ap->funcionario->nome ?? '—' }}</div>
                                        <small class="text-muted">{{ $ap->funcionario->funcao ?? '' }}</small>
                                    </td>
                                    <td class="small text-muted">{{ $ap->obra->nome ?? '—' }}</td>
                                    <td class="small">{{ $ap->hora_entrada ? substr($ap->hora_entrada, 0, 5) : '—' }}</td>
                                    <td class="small">{{ $ap->hora_saida ? substr($ap->hora_saida, 0, 5) : '—' }}</td>
                                    <td class="small font-weight-bold">
                                        {{ $ap->horas_trabalhadas ? number_format($ap->horas_trabalhadas, 1) . 'h' : '—' }}
                                    </td>
                                    <td>
                                        @php
                                            $statusLabels = [
                                                'pendente'  => __('Pendente'),
                                                'aprovado'  => __('Aprovado'),
                                                'rejeitado' => __('Rejeitado'),
                                            ];
                                        @endphp
                                        <span class="badge {{ $ap->status_badge }}" style="font-size:.65rem">
                                            {{ $statusLabels[$ap->status] ?? $ap->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('funcionarios.apontamento.destroy', $ap) }}"
                                              onsubmit="return confirm('{{ __('Excluir este apontamento?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-clock fa-2x mb-2 d-block" style="opacity:.3"></i>
                                        {{ __('Nenhum apontamento registrado para esta data.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
