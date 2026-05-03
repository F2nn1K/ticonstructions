@extends('adminlte::page')

@section('title', 'Administradores')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0" style="font-family:'Playfair Display',serif;">
            <i class="fas fa-user-tie mr-2" style="color:#C9A84C;"></i>
            Administradores
        </h1>
        <p class="text-muted mb-0 mt-1" style="font-size:.9rem;">
            Fichas dos administradores e gestão da taxa de administração
        </p>
    </div>
    <a href="{{ route('obras.administradores.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Novo Administrador
    </a>
</div>
@stop

@section('content')
<style>
:root {
    --gold:  #C9A84C; --gold-d: #A8873A; --gold-l: #E2C87A;
    --ebony: #0F0D0A; --cream:  #FAF6EF; --taupe:  #7D6A52;
}

/* ── Stat cards ─────────────────────────────────────────────────── */
.admin-stat {
    background: #fff;
    border-radius: 16px;
    padding: 24px 28px;
    border: 1px solid rgba(201,168,76,.15);
    box-shadow: 0 4px 18px rgba(15,13,10,.07);
    position: relative;
    overflow: hidden;
}
.admin-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--gold-d), var(--gold-l), var(--gold));
}
.admin-stat .stat-icon {
    width: 52px; height: 52px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: var(--ebony);
    background: linear-gradient(135deg, var(--gold-d) 0%, var(--gold-l) 100%);
    margin-bottom: 14px;
    box-shadow: 0 4px 14px rgba(201,168,76,.35);
}
.admin-stat .stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 1.85rem; font-weight: 700; color: var(--ebony);
    line-height: 1; margin-bottom: 4px;
}
.admin-stat .stat-label { font-size: .82rem; color: var(--taupe); font-weight: 500; }

/* ── Tabela ──────────────────────────────────────────────────────── */
.admin-table thead th {
    background: linear-gradient(135deg, var(--taupe) 0%, var(--gold) 100%) !important;
    color: #fff !important;
    font-size: .75rem;
    letter-spacing: .5px;
    text-transform: uppercase;
    padding: 14px 18px;
    border: none;
}
.admin-table tbody td { padding: 14px 18px; vertical-align: middle; }
.admin-table tbody tr:hover { background: rgba(201,168,76,.04) !important; }

.badge-ativo   { background: rgba(16,158,110,.12); color:#0D7A52; padding:4px 12px; border-radius:20px; font-weight:600; font-size:.75rem; }
.badge-inativo { background: rgba(201,64,64,.10);  color:#A83030; padding:4px 12px; border-radius:20px; font-weight:600; font-size:.75rem; }

.pct-badge {
    background: var(--cream);
    border: 1.5px solid rgba(201,168,76,.4);
    color: var(--gold-d);
    padding: 3px 10px; border-radius: 20px;
    font-weight: 700; font-size: .82rem;
}
</style>

@if(session('success'))
<div class="alert" style="background:rgba(16,158,110,.08);border-left:4px solid #1A9E6E;border-radius:10px;color:#0D7A52;padding:14px 18px;margin-bottom:20px;">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

<!-- Stat cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="admin-stat">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ $administradores->total() }}</div>
            <div class="stat-label">Total de Administradores</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="admin-stat">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
            <div class="stat-label">Total Pago em Taxas</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="admin-stat">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value">R$ {{ number_format($totalPendente, 2, ',', '.') }}</div>
            <div class="stat-label">Total Pendente de Pagamento</div>
        </div>
    </div>
</div>

<!-- Tabela -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-user-tie mr-2" style="color:#C9A84C;"></i>
            Cadastro de Administradores
        </h5>
    </div>
    <div class="card-body p-0">
        @if($administradores->count())
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Cargo</th>
                        <th>E-mail / Telefone</th>
                        <th>Taxa (%)</th>
                        <th>Taxas Geradas</th>
                        <th>Status</th>
                        <th width="140">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($administradores as $admin)
                    <tr>
                        <td>
                            <strong style="color:#0F0D0A;">{{ $admin->nome }}</strong>
                            @if($admin->user)
                                <br><small class="text-muted">{{ $admin->user->name }}</small>
                            @endif
                        </td>
                        <td>{{ $admin->cpf ?? '—' }}</td>
                        <td>{{ $admin->cargo ?? '—' }}</td>
                        <td>
                            @if($admin->email)<div>{{ $admin->email }}</div>@endif
                            @if($admin->telefone)<div class="text-muted" style="font-size:.85rem;">{{ $admin->telefone }}</div>@endif
                        </td>
                        <td><span class="pct-badge">{{ number_format($admin->percentual_taxa,2,',','.') }}%</span></td>
                        <td class="text-center">
                            <span class="badge badge-secondary" style="font-size:.8rem;">{{ $admin->taxas_count }}</span>
                        </td>
                        <td>
                            @if($admin->ativo)
                                <span class="badge-ativo"><i class="fas fa-circle mr-1" style="font-size:.5rem;"></i>{{ __('Ativo') }}</span>
                            @else
                                <span class="badge-inativo"><i class="fas fa-circle mr-1" style="font-size:.5rem;"></i>{{ __('Inativo') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('obras.administradores.show', $admin) }}" class="btn btn-sm btn-info" title="Ver ficha">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('obras.administradores.edit', $admin) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form method="POST" action="{{ route('obras.administradores.destroy', $admin) }}"
                                      onsubmit="return confirm('Remover este administrador?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Remover"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">
            {{ $administradores->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-user-tie fa-3x mb-3" style="color:rgba(201,168,76,.3);"></i>
            <p class="text-muted">Nenhum administrador cadastrado ainda.</p>
            <a href="{{ route('obras.administradores.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Cadastrar Administrador
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Atalho: Pagar Taxa de Administração -->
<div class="text-center mt-3">
    <a href="{{ route('obras.taxa-administracao.index') }}" class="btn btn-secondary">
        <i class="fas fa-dollar-sign mr-2" style="color:#C9A84C;"></i>
        Gerenciar Taxas de Administração
    </a>
</div>
@stop
