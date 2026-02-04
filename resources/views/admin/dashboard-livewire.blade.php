@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Mensagem de Boas-vindas -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-primary mb-0" style="border-left: 4px solid #007bff;">
                <h4 class="mb-1"><i class="fas fa-user-circle mr-2"></i>Bem-vindo, <strong>{{ Auth::user()->name }}</strong>!</h4>
                @if(isset($isAdmin) && $isAdmin)
                <p class="mb-0">Aqui está uma visão geral do sistema.</p>
                @else
                <p class="mb-0">Utilize o menu lateral para acessar as funcionalidades do sistema.</p>
                @endif
            </div>
        </div>
    </div>

    @if(isset($isAdmin) && $isAdmin)
    
    <!-- Cards de Resumo -->
    <div class="row">
        <!-- O.C.s Pendentes -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['ocs_pendentes'] ?? 0 }}</h3>
                    <p>O.C.s Aguardando Aprovação</p>
                    <span class="text-dark" style="font-size: 14px;">
                        <strong>R$ {{ number_format($stats['ocs_pendentes_valor'] ?? 0, 2, ',', '.') }}</strong>
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('suprimentos.ordem-compra') }}" class="small-box-footer">
                    Ver O.C.s <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <!-- Cotações Abertas -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['cotacoes_abertas'] ?? 0 }}</h3>
                    <p>Cotações Abertas</p>
                    <span style="font-size: 14px;">Aguardando cotação</span>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <a href="{{ route('suprimentos.cotacao') }}" class="small-box-footer">
                    Ver Cotações <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <!-- Contas Vencidas -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['contas_vencidas'] ?? 0 }}</h3>
                    <p>Contas Vencidas</p>
                    <span style="font-size: 14px;">
                        <strong>R$ {{ number_format($stats['contas_vencidas_valor'] ?? 0, 2, ',', '.') }}</strong>
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('financeiro.contas-pagar') }}" class="small-box-footer">
                    Ver Contas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <!-- O.S. Abertas -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['os_abertas'] ?? 0 }}</h3>
                    <p>Ordens de Serviço Abertas</p>
                    @if(($stats['contas_hoje'] ?? 0) > 0)
                    <span style="font-size: 14px; color: #fff;">
                        <i class="fas fa-bell"></i> {{ $stats['contas_hoje'] }} conta(s) vence(m) hoje
                    </span>
                    @else
                    <span style="font-size: 14px;">Em execução</span>
                    @endif
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <a href="{{ route('area-tecnica.ordem-servico') }}" class="small-box-footer">
                    Ver O.S. <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Aviso de Contas a Pagar Próximas do Vencimento -->
    @if(isset($contasAVencer) && $contasAVencer->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <strong>Atenção!</strong> Contas a Pagar Próximas do Vencimento (Próximos 7 dias)
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('financeiro.contas-pagar') }}" class="btn btn-sm btn-dark">
                            <i class="fas fa-arrow-right mr-1"></i> Ver Todas
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Descrição</th>
                                <th>Fornecedor</th>
                                <th class="text-right">Valor</th>
                                <th class="text-center">Vencimento</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contasAVencer as $conta)
                            @php
                                $vencimento = \Carbon\Carbon::parse($conta->vencimento);
                                $diasRestantes = now()->startOfDay()->diffInDays($vencimento->startOfDay(), false);
                                $badgeClass = 'badge-warning';
                                $badgeText = 'Vence em ' . $diasRestantes . ' dias';
                                
                                if ($diasRestantes == 0) {
                                    $badgeClass = 'badge-danger';
                                    $badgeText = 'Vence HOJE';
                                } elseif ($diasRestantes == 1) {
                                    $badgeClass = 'badge-danger';
                                    $badgeText = 'Vence AMANHÃ';
                                } elseif ($diasRestantes < 0) {
                                    $badgeClass = 'badge-dark';
                                    $badgeText = 'VENCIDO';
                                } elseif ($diasRestantes <= 3) {
                                    $badgeClass = 'badge-danger';
                                }
                            @endphp
                            <tr>
                                <td>{{ $conta->descricao }}</td>
                                <td>{{ $conta->fornecedor ?? '-' }}</td>
                                <td class="text-right font-weight-bold text-danger">
                                    R$ {{ number_format($conta->valor, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    {{ $vencimento->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="2" class="text-right font-weight-bold">Total a Pagar:</td>
                                <td class="text-right font-weight-bold text-danger">
                                    R$ {{ number_format($contasAVencer->sum('valor'), 2, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Primeira linha: Gráficos de Status -->
    <div class="row">
        <!-- Gráfico de Donut: O.C.s por Status -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart mr-2 text-primary"></i>Ordens de Compra por Status</h3>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartOCsStatus" style="max-height: 220px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Donut: Cotações por Status -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2 text-info"></i>Cotações por Status</h3>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartCotacoesStatus" style="max-height: 220px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Barras: Top Fornecedores -->
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-truck mr-2 text-success"></i>Top 5 Fornecedores</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartTopFornecedores" style="max-height: 220px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda linha: Gráfico de Compras e Top Centros de Custo -->
    <div class="row">
        <!-- Gráfico de Linha: Compras por Mês -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-2 text-primary"></i>Compras por Mês (Últimos 6 meses)</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartComprasMes" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabela: Top Centros de Custo por Gasto -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header bg-gradient-primary text-white">
                    <h3 class="card-title"><i class="fas fa-building mr-2"></i>Top 5 Centros de Custo (por Gasto)</h3>
                </div>
                <div class="card-body p-0">
                    @if(isset($topCentrosCusto) && $topCentrosCusto->count() > 0)
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Centro de Custo</th>
                                <th class="text-center">Qtd O.C.s</th>
                                <th class="text-right">Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCentrosCusto as $index => $cc)
                            <tr>
                                <td>
                                    @if($index == 0)
                                    <span class="badge badge-warning"><i class="fas fa-trophy"></i></span>
                                    @elseif($index == 1)
                                    <span class="badge badge-secondary"><i class="fas fa-medal"></i></span>
                                    @elseif($index == 2)
                                    <span class="badge badge-dark"><i class="fas fa-award"></i></span>
                                    @else
                                    <span class="badge badge-light">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>{{ Str::limit($cc->nome, 40) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $cc->qtd_ocs }}</span>
                                </td>
                                <td class="text-right font-weight-bold text-success">
                                    R$ {{ number_format($cc->total, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum dado disponível ainda.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Terceira linha: Cards de Resumo por Módulo -->
    <div class="row">
        <!-- ORDENS DE SERVIÇO -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Ordens de Serviço</h3>
                    <div class="card-tools">
                        <a href="{{ route('area-tecnica.ordem-servico') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total</span>
                        <span class="badge badge-primary badge-lg" style="font-size: 1rem;">{{ $stats['os_total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-folder-open text-warning mr-1"></i> Abertas</span>
                        <span class="badge badge-warning">{{ $stats['os_abertas'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-check-circle text-success mr-1"></i> Fechadas</span>
                        <span class="badge badge-success">{{ $stats['os_fechadas'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- COTAÇÕES -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Cotações</h3>
                    <div class="card-tools">
                        <a href="{{ route('suprimentos.cotacao') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total</span>
                        <span class="badge badge-primary badge-lg" style="font-size: 1rem;">{{ $stats['cotacoes_total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-folder-open text-info mr-1"></i> Abertas</span>
                        <span class="badge badge-info">{{ $stats['cotacoes_abertas'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-check-circle text-success mr-1"></i> Finalizadas</span>
                        <span class="badge badge-success">{{ $stats['cotacoes_finalizadas'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-exclamation-triangle text-danger mr-1"></i> Urgentes</span>
                        <span class="badge badge-danger">{{ $stats['cotacoes_urgentes'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ORDENS DE COMPRA -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title"><i class="fas fa-shopping-cart mr-2"></i>Ordens de Compra</h3>
                    <div class="card-tools">
                        <a href="{{ route('suprimentos.ordem-compra') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total</span>
                        <span class="badge badge-primary badge-lg" style="font-size: 1rem;">{{ $stats['ocs_total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-clock text-warning mr-1"></i> Aguard. Aprovação</span>
                        <span class="badge badge-warning">{{ $stats['ocs_pendentes'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-check text-success mr-1"></i> Aprovadas</span>
                        <span class="badge badge-success">{{ $stats['ocs_aprovadas'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-box text-info mr-1"></i> Recebidas</span>
                        <span class="badge badge-info">{{ $stats['ocs_recebidas'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-truck text-secondary mr-1"></i> Aguard. Recebimento</span>
                        <span class="badge badge-secondary">{{ $stats['ocs_aguardando_recebimento'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quarta linha: Mais Cards -->
    <div class="row">
        <!-- PRESTADORES/TERCEIRIZADOS -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title"><i class="fas fa-hard-hat mr-2"></i>Terceirizados</h3>
                    <div class="card-tools">
                        <a href="{{ route('area-tecnica.gestao-os') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total</span>
                        <span class="badge badge-primary badge-lg" style="font-size: 1rem;">{{ $stats['terceirizados_total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-hourglass-half text-info mr-1"></i> Aguard. Autorização</span>
                        <span class="badge badge-info">{{ $stats['terceirizados_aguardando_autorizacao'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-clock text-warning mr-1"></i> Aguard. Pagamento</span>
                        <span class="badge badge-warning">{{ $stats['terceirizados_aguardando_pagamento'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-check-circle text-success mr-1"></i> Pagos</span>
                        <span class="badge badge-success">{{ $stats['terceirizados_pagos'] ?? 0 }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><strong>Valor Total</strong></span>
                        <span class="text-primary font-weight-bold">R$ {{ number_format($stats['terceirizados_valor'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ESTOQUE -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-purple text-white" style="background-color: #6f42c1 !important;">
                    <h3 class="card-title"><i class="fas fa-boxes mr-2"></i>Estoque</h3>
                    <div class="card-tools">
                        <a href="{{ route('brs.controle-estoque') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total de Produtos</span>
                        <span class="badge badge-primary badge-lg" style="font-size: 1rem;">{{ $stats['estoque_total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-exclamation-circle text-danger mr-1"></i> Estoque Zerado</span>
                        <span class="badge badge-danger">{{ $stats['estoque_zerado'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-arrow-down text-warning mr-1"></i> Abaixo do Mínimo</span>
                        <span class="badge badge-warning">{{ $stats['estoque_abaixo_minimo'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECEBIMENTOS -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-teal text-white" style="background-color: #20c997 !important;">
                    <h3 class="card-title"><i class="fas fa-truck-loading mr-2"></i>Recebimentos</h3>
                    <div class="card-tools">
                        <a href="{{ route('suprimentos.recebimento') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total</span>
                        <span class="badge badge-primary badge-lg" style="font-size: 1rem;">{{ $stats['recebimentos_total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-calendar-day text-info mr-1"></i> Hoje</span>
                        <span class="badge badge-info">{{ $stats['recebimentos_hoje'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar-alt text-success mr-1"></i> Este Mês</span>
                        <span class="badge badge-success">{{ $stats['recebimentos_mes'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @endif
</div>
@stop

@section('css')
<style>
    .card {
        border-radius: 8px;
        border: none;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #eee;
        font-weight: 600;
    }
    
    .card-header.bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    }
    
    .card-header.bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
        color: #333;
    }
    
    .alert-primary {
        background-color: #e8f4fd;
        border-color: #007bff;
        color: #004085;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .h-100 {
        height: 100%;
    }
    
    .small-box {
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    
    .small-box .inner h3 {
        font-size: 2.2rem;
        font-weight: 700;
    }
    
    .small-box .inner p {
        font-size: 1rem;
    }
    
    .small-box .icon {
        font-size: 70px;
        opacity: 0.3;
    }
    
    .small-box-footer {
        background: rgba(0,0,0,0.1);
    }
    
    .bg-gradient-primary .card-title,
    .bg-gradient-warning .card-title {
        color: inherit;
    }
</style>
@stop

@section('js')
@if(isset($isAdmin) && $isAdmin)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Gráfico de Donut: O.C.s por Status
    const ctxOCs = document.getElementById('chartOCsStatus').getContext('2d');
    new Chart(ctxOCs, {
        type: 'doughnut',
        data: {
            labels: ['Pendentes', 'Aprovadas', 'Recebidas'],
            datasets: [{
                data: [{{ $ocsPorStatus['pendente'] ?? 0 }}, {{ $ocsPorStatus['aprovada'] ?? 0 }}, {{ $ocsPorStatus['recebida'] ?? 0 }}],
                backgroundColor: ['#ffc107', '#28a745', '#17a2b8'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Gráfico de Donut: Cotações por Status
    const ctxCotacoes = document.getElementById('chartCotacoesStatus').getContext('2d');
    new Chart(ctxCotacoes, {
        type: 'doughnut',
        data: {
            labels: ['Abertas', 'Finalizadas', 'Parciais'],
            datasets: [{
                data: [{{ $cotacoesPorStatus['aberta'] ?? 0 }}, {{ $cotacoesPorStatus['finalizada'] ?? 0 }}, {{ $cotacoesPorStatus['parcial'] ?? 0 }}],
                backgroundColor: ['#17a2b8', '#28a745', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Gráfico de Barras Horizontal: Top Fornecedores
    const ctxFornecedores = document.getElementById('chartTopFornecedores').getContext('2d');
    new Chart(ctxFornecedores, {
        type: 'bar',
        data: {
            labels: [
                @foreach($topFornecedores as $f)
                '{{ Str::limit($f->nome, 12) }}',
                @endforeach
            ],
            datasets: [{
                label: 'Qtd O.C.s',
                data: [
                    @foreach($topFornecedores as $f)
                    {{ $f->qtd }},
                    @endforeach
                ],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#17a2b8',
                    '#6c757d'
                ],
                borderWidth: 0,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Gráfico de Linha: Compras por Mês
    const ctxCompras = document.getElementById('chartComprasMes').getContext('2d');
    new Chart(ctxCompras, {
        type: 'line',
        data: {
            labels: [
                @foreach($comprasPorMes as $mes)
                '{{ \Carbon\Carbon::createFromFormat("Y-m", $mes->mes)->format("M/y") }}',
                @endforeach
            ],
            datasets: [{
                label: 'Valor (R$)',
                data: [
                    @foreach($comprasPorMes as $mes)
                    {{ $mes->total }},
                    @endforeach
                ],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@stop
