@extends('adminlte::page')
@section('title', __('app.menu.risk_matrix'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fas fa-shield-alt mr-2" style="color:var(--ti-gold)"></i>{{ __('app.menu.risk_matrix') }}</h1>
            <small class="text-muted">{{ __('Identificação e controle de riscos das obras') }}</small>
        </div>
        <a href="{{ route('riscos.criar') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus mr-1"></i> {{ __('app.menu.register_risk') }}
        </a>
    </div>
@stop
@section('content')
<style>
.kpi-mini{border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)}
.kpi-mini .k-label{font-size:.72rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;opacity:.7}
.kpi-mini .k-val{font-size:1.8rem;font-weight:800;line-height:1.2}
.matrix-cell{width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;font-weight:700;font-size:.85rem}
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
@endif

{{-- KPIs --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini"><div class="card-body py-3 text-center">
            <div class="k-label text-danger">{{ __('Críticos') }}</div>
            <div class="k-val text-danger">{{ $totalCriticos }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini"><div class="card-body py-3 text-center">
            <div class="k-label text-warning">{{ __('Altos') }}</div>
            <div class="k-val text-warning">{{ $totalAltos }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini"><div class="card-body py-3 text-center">
            <div class="k-label" style="color:var(--ti-gold)">{{ __('Em Aberto') }}</div>
            <div class="k-val" style="color:var(--ti-gold)">{{ $totalAbertos }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card kpi-mini"><div class="card-body py-3 text-center">
            <div class="k-label" style="color:#1A9E6E">{{ __('Mitigados') }}</div>
            <div class="k-val" style="color:#1A9E6E">{{ $totalMitigados }}</div>
        </div></div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-3" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.06)">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('riscos.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Obra') }}</label>
                <select name="obra_id" class="form-control form-control-sm">
                    <option value="">{{ __('Todas as obras') }}</option>
                    @foreach($obras as $ob)
                        <option value="{{ $ob->id }}" {{ $obraId==$ob->id?'selected':'' }}>{{ $ob->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Status') }}</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">{{ __('Todos') }}</option>
                    <option value="identificado"  {{ $status=='identificado' ?'selected':'' }}>{{ __('Identificado') }}</option>
                    <option value="em_mitigacao"  {{ $status=='em_mitigacao'?'selected':'' }}>{{ __('Em Mitigação') }}</option>
                    <option value="mitigado"      {{ $status=='mitigado'    ?'selected':'' }}>{{ __('Mitigado') }}</option>
                    <option value="aceito"        {{ $status=='aceito'      ?'selected':'' }}>{{ __('Aceito') }}</option>
                </select>
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="small font-weight-bold text-muted mb-1">{{ __('Categoria') }}</label>
                <select name="categoria" class="form-control form-control-sm">
                    <option value="">{{ __('Todas') }}</option>
                    <option value="seguranca"    {{ $categoria=='seguranca'    ?'selected':'' }}>{{ __('Segurança') }}</option>
                    <option value="financeiro"   {{ $categoria=='financeiro'   ?'selected':'' }}>{{ __('Financeiro') }}</option>
                    <option value="ambiental"    {{ $categoria=='ambiental'    ?'selected':'' }}>{{ __('Ambiental') }}</option>
                    <option value="cronograma"   {{ $categoria=='cronograma'   ?'selected':'' }}>{{ __('Cronograma') }}</option>
                    <option value="qualidade"    {{ $categoria=='qualidade'    ?'selected':'' }}>{{ __('Qualidade') }}</option>
                    <option value="outro"        {{ $categoria=='outro'        ?'selected':'' }}>{{ __('Outro') }}</option>
                </select>
            </div>
            <div class="col-sm-12 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search mr-1"></i>{{ __('Filtrar') }}</button>
                <a href="{{ route('riscos.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Tabela --}}
<div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.07)">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Nível') }}</th>
                        <th>{{ __('Título') }}</th>
                        <th>{{ __('Categoria') }}</th>
                        <th>{{ __('Obra') }}</th>
                        <th>{{ __('Probabilidade') }}</th>
                        <th>{{ __('Impacto') }}</th>
                        <th>{{ __('Responsável') }}</th>
                        <th>{{ __('Prazo') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riscos as $risco)
                        @php
                            $nivel = $risco->probabilidade * $risco->impacto;
                            $cor = $nivel >= 15 ? '#dc3545' : ($nivel >= 8 ? '#ffc107' : ($nivel >= 4 ? '#17a2b8' : '#28a745'));
                            $statusLabels = ['identificado'=>__('Identificado'),'em_mitigacao'=>__('Em Mitigação'),'mitigado'=>__('Mitigado'),'aceito'=>__('Aceito')];
                            $catLabels = ['seguranca'=>__('Segurança'),'financeiro'=>__('Financeiro'),'ambiental'=>__('Ambiental'),'cronograma'=>__('Cronograma'),'qualidade'=>__('Qualidade'),'outro'=>__('Outro')];
                        @endphp
                        <tr>
                            <td>
                                <span class="badge" style="background:{{ $cor }};color:#fff;font-size:.8rem;padding:5px 8px">
                                    {{ $nivel }}
                                </span>
                            </td>
                            <td>
                                <div class="font-weight-bold small">{{ $risco->titulo }}</div>
                                @if($risco->descricao)
                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($risco->descricao, 60) }}</small>
                                @endif
                            </td>
                            <td class="small">{{ $catLabels[$risco->categoria] ?? $risco->categoria }}</td>
                            <td class="small text-muted">{{ $risco->obra?->nome ?? __('Geral') }}</td>
                            <td class="text-center"><span class="badge badge-secondary">{{ $risco->probabilidade }}/5</span></td>
                            <td class="text-center"><span class="badge badge-secondary">{{ $risco->impacto }}/5</span></td>
                            <td class="small">{{ $risco->responsavel ?? '—' }}</td>
                            <td class="small text-nowrap">{{ $risco->prazo ? $risco->prazo->translatedFormat('d/m/Y') : '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $risco->status==='mitigado'?'success':($risco->status==='em_mitigacao'?'warning':'secondary') }}" style="font-size:.65rem">
                                    {{ $statusLabels[$risco->status] ?? $risco->status }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('riscos.edit', $risco) }}" class="btn btn-xs btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('riscos.destroy', $risco) }}" class="d-inline"
                                      onsubmit="return confirm('{{ __('Excluir este risco?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-shield-alt fa-2x mb-2 d-block" style="opacity:.3"></i>
                                {{ __('Nenhum risco registrado.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
