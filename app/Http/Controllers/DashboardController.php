<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Verifica se o usuário é administrador (tem todas as permissões)
     */
    private function isAdmin($user)
    {
        if (!$user || !$user->profile_id) {
            return false;
        }
        
        try {
            // Verifica pelo nome do perfil
            $profile = DB::table('profiles')->where('id', $user->profile_id)->first();
            if ($profile && strtolower($profile->name) === 'administrador') {
                return true;
            }
            
            // Verifica se o perfil tem todas as permissões (ou quase todas)
            $totalPermissions = DB::table('permissions')->count();
            $userPermissions = DB::table('profile_permissions')
                ->where('profile_id', $user->profile_id)
                ->count();
            
            // Se tem 90% ou mais das permissões, é admin
            if ($totalPermissions > 0 && ($userPermissions / $totalPermissions) >= 0.9) {
                return true;
            }
        } catch (\Exception $e) {
            // Se der erro, assume que não é admin
        }
        
        return false;
    }
    
    /**
     * Exibe a view do dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        
        // Inicializa variáveis para admin
        $stats = [
            'ocs_pendentes' => 0,
            'ocs_pendentes_valor' => 0,
            'cotacoes_abertas' => 0,
            'contas_vencidas' => 0,
            'contas_vencidas_valor' => 0,
            'os_abertas' => 0,
            'contas_hoje' => 0,
        ];
        $ocsPorStatus = ['pendente' => 0, 'aprovada' => 0, 'recebida' => 0];
        $cotacoesPorStatus = ['aberta' => 0, 'finalizada' => 0, 'parcial' => 0];
        $topFornecedores = collect([]);
        $topCentrosCusto = collect([]);
        $comprasPorMes = collect([]);
        $ocsPendentesLista = collect([]);
        $contasAVencer = collect([]);
        
        if ($isAdmin) {
            try {
                // === CARDS DE RESUMO ===
                
                // O.C.s Pendentes (aguardando aprovação)
                $stats['ocs_pendentes'] = DB::table('ordens_compra')->where('status', 'pendente')->count();
                $stats['ocs_pendentes_valor'] = DB::table('ordens_compra')->where('status', 'pendente')->sum('valor_total');
                
                // Cotações Abertas
                $stats['cotacoes_abertas'] = DB::table('cotacoes')->where('status', 'aberta')->count();
                
                // Contas Vencidas
                $stats['contas_vencidas'] = DB::table('contas_pagar')
                    ->whereNotIn('status', ['pago', 'cancelado'])
                    ->where('vencimento', '<', date('Y-m-d'))
                    ->count();
                $stats['contas_vencidas_valor'] = DB::table('contas_pagar')
                    ->whereNotIn('status', ['pago', 'cancelado'])
                    ->where('vencimento', '<', date('Y-m-d'))
                    ->sum('valor');
                
                // Contas que vencem hoje
                $stats['contas_hoje'] = DB::table('contas_pagar')
                    ->whereNotIn('status', ['pago', 'cancelado'])
                    ->where('vencimento', date('Y-m-d'))
                    ->count();
                
                // O.S. Abertas
                $stats['os_abertas'] = DB::table('ordens_servico')->where('status', 'aberta')->count();
                
                // === GRÁFICOS ===
                
                // O.C.s por Status
                $ocsPorStatus = [
                    'pendente' => DB::table('ordens_compra')->where('status', 'pendente')->count(),
                    'aprovada' => DB::table('ordens_compra')->where('status', 'aprovada')->count(),
                    'recebida' => DB::table('ordens_compra')->whereIn('status', ['recebida', 'recebida_parcial'])->count(),
                ];
                
                // Cotações por Status
                $cotacoesPorStatus = [
                    'aberta' => DB::table('cotacoes')->where('status', 'aberta')->count(),
                    'finalizada' => DB::table('cotacoes')->where('status', 'finalizada')->count(),
                    'parcial' => DB::table('cotacoes')->where('status', 'parcial')->count(),
                ];
                
                // Top 5 Fornecedores (por quantidade de OCs)
                $topFornecedores = DB::table('ordens_compra as oc')
                    ->join('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                    ->select('f.razao_social as nome', DB::raw('COUNT(*) as qtd'), DB::raw('SUM(oc.valor_total) as total'))
                    ->groupBy('f.id', 'f.razao_social')
                    ->orderByDesc('qtd')
                    ->limit(5)
                    ->get();
                
                // Top 5 Centros de Custo por Gasto
                $topCentrosCusto = DB::table('ordens_compra as oc')
                    ->join('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
                    ->join('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                    ->join('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                    ->select('cc.nome', DB::raw('SUM(oc.valor_total) as total'), DB::raw('COUNT(*) as qtd_ocs'))
                    ->whereIn('oc.status', ['aprovada', 'recebida', 'recebida_parcial'])
                    ->groupBy('cc.id', 'cc.nome')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get();
                
                // Compras por Mês (últimos 6 meses)
                $comprasPorMes = DB::table('ordens_compra')
                    ->select(
                        DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                        DB::raw('COUNT(*) as qtd'),
                        DB::raw('SUM(valor_total) as total')
                    )
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get();
                
                // === NOVOS DADOS PARA CARDS DO DASHBOARD ===
                
                // ORDENS DE SERVIÇO
                $stats['os_total'] = DB::table('ordens_servico')->count();
                $stats['os_abertas'] = DB::table('ordens_servico')->where('status', 'aberta')->count();
                $stats['os_fechadas'] = DB::table('ordens_servico')->where('status', 'fechada')->count();
                
                // COTAÇÕES
                $stats['cotacoes_total'] = DB::table('cotacoes')->count();
                $stats['cotacoes_abertas'] = DB::table('cotacoes')->where('status', 'aberta')->count();
                $stats['cotacoes_finalizadas'] = DB::table('cotacoes')->where('status', 'finalizada')->count();
                $stats['cotacoes_urgentes'] = DB::table('cotacoes')
                    ->whereNotIn('status', ['finalizada', 'cancelada'])
                    ->where('data_limite', '<=', now()->addDays(2)->format('Y-m-d'))
                    ->count();
                
                // ORDENS DE COMPRA
                $stats['ocs_total'] = DB::table('ordens_compra')->count();
                $stats['ocs_aprovadas'] = DB::table('ordens_compra')->where('status', 'aprovada')->count();
                $stats['ocs_recebidas'] = DB::table('ordens_compra')->whereIn('status', ['recebida', 'recebida_parcial'])->count();
                $stats['ocs_aguardando_recebimento'] = DB::table('ordens_compra')
                    ->where('status', 'aprovada')
                    ->where('status_pagamento', 'pago')
                    ->count();
                
                // PRESTADORES/TERCEIRIZADOS
                $stats['terceirizados_total'] = DB::table('ordens_servico_prestadores')->count();
                $stats['terceirizados_aguardando_autorizacao'] = DB::table('ordens_servico_prestadores')
                    ->where('status_pagamento', 'aguardando_autorizacao')->count();
                $stats['terceirizados_aguardando_pagamento'] = DB::table('ordens_servico_prestadores')
                    ->where('status_pagamento', 'aguardando_pagamento')->count();
                $stats['terceirizados_pagos'] = DB::table('ordens_servico_prestadores')
                    ->where('status_pagamento', 'pago')->count();
                $stats['terceirizados_valor'] = DB::table('ordens_servico_prestadores')->sum('valor');
                
                // ESTOQUE
                $stats['estoque_total'] = DB::table('estoque')->count();
                $stats['estoque_zerado'] = DB::table('estoque')->where('quantidade', '<=', 0)->count();
                $stats['estoque_abaixo_minimo'] = DB::table('estoque')
                    ->join('estoque_min_max', 'estoque.id', '=', 'estoque_min_max.produto_id')
                    ->whereRaw('estoque.quantidade <= estoque_min_max.minimo')
                    ->count();
                
                // RECEBIMENTOS
                $stats['recebimentos_total'] = DB::table('recebimentos')->count();
                $stats['recebimentos_hoje'] = DB::table('recebimentos')
                    ->whereDate('created_at', now()->format('Y-m-d'))->count();
                $stats['recebimentos_mes'] = DB::table('recebimentos')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();
                
            } catch (\Exception $e) {
                // Mantém valores padrão
            }
            
            // Contas a pagar próximas do vencimento (próximos 7 dias)
            try {
                $contasAVencer = DB::table('contas_pagar')
                    ->select('id', 'descricao', 'fornecedor', 'valor', 'vencimento', 'status')
                    ->whereNotIn('status', ['pago', 'cancelado'])
                    ->where('vencimento', '<=', date('Y-m-d', strtotime('+7 days')))
                    ->orderBy('vencimento')
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                $contasAVencer = collect([]);
            }
        }
        
        return view('admin.dashboard-livewire', compact(
            'isAdmin',
            'stats',
            'ocsPorStatus',
            'cotacoesPorStatus',
            'topFornecedores',
            'topCentrosCusto',
            'comprasPorMes',
            'ocsPendentesLista',
            'contasAVencer'
        ));
    }
}
