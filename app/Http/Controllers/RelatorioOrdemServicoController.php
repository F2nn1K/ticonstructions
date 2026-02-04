<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioOrdemServicoController extends Controller
{
    /**
     * Exibe a página do relatório de O.S.
     */
    public function index()
    {
        // Buscar centros de custo (obras) para o filtro
        $centrosCusto = DB::table('centros_custo')
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();
        
        // Buscar usuários para filtro de responsável
        $usuarios = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return view('relatorios.ordem-servico', compact('centrosCusto', 'usuarios'));
    }

    /**
     * Retorna os dados do relatório de O.S. com filtros
     * Inclui todo o fluxo: Solicitações -> Cotações -> OCs -> Pagamentos -> Recebimentos
     */
    public function data(Request $request)
    {
        // Aumentar tempo limite para relatórios grandes
        set_time_limit(120);
        
        try {
            $query = DB::table('ordens_servico as os')
                ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                ->leftJoin('users as u', 'os.user_id', '=', 'u.id')
                ->select(
                    'os.id',
                    'os.numero_os as numero',
                    'os.descricao',
                    'os.status',
                    'os.data_os as created_at',
                    'cc.nome as centro_custo',
                    'u.name as responsavel'
                );

            // Filtros
            // Múltiplos centros de custo
            if ($request->filled('centro_custo_ids')) {
                $ids = array_filter(explode(',', $request->centro_custo_ids));
                if (!empty($ids)) {
                    $query->whereIn('os.centro_custo_id', $ids);
                }
            }
            
            if ($request->filled('responsavel_id')) {
                $query->where('os.user_id', $request->responsavel_id);
            }
            
            if ($request->filled('status')) {
                $query->where('os.status', $request->status);
            }
            
            if ($request->filled('data_inicio')) {
                $query->whereDate('os.data_os', '>=', $request->data_inicio);
            }
            
            if ($request->filled('data_fim')) {
                $query->whereDate('os.data_os', '<=', $request->data_fim);
            }

            $ordens = $query->orderBy('os.data_os', 'desc')->get();

            // Calcular totais de cada O.S. com todo o fluxo
            $resultado = [];
            $totaisGerais = [
                'valor_solicitado' => 0,
                'valor_cotado' => 0,
                'valor_aprovado' => 0,
                'valor_pendente_aprovacao' => 0,
                'valor_pago' => 0,
                'valor_a_pagar' => 0,
                'valor_recebido' => 0,
                'valor_a_receber' => 0,
                'valor_fretes' => 0,
                'valor_terceirizados' => 0,
                'valor_terceirizados_pago' => 0,
                'valor_terceirizados_pendente' => 0,
                'valor_total' => 0
            ];
            
            foreach ($ordens as $os) {
                $dadosOS = $this->calcularFluxoOS($os->id);
                
                // Acumular totais gerais
                foreach ($totaisGerais as $key => $val) {
                    $totaisGerais[$key] += $dadosOS[$key] ?? 0;
                }
                
                $resultado[] = [
                    'id' => $os->id,
                    'numero' => $os->numero,
                    'descricao' => $os->descricao,
                    'status' => $os->status,
                    'created_at' => $os->created_at,
                    'centro_custo' => $os->centro_custo,
                    'responsavel' => $os->responsavel,
                    // Dados do fluxo
                    'qtd_cotacoes' => $dadosOS['qtd_cotacoes'],
                    'qtd_ocs' => $dadosOS['qtd_ocs'],
                    'qtd_ocs_aprovadas' => $dadosOS['qtd_ocs_aprovadas'],
                    'qtd_ocs_pendentes' => $dadosOS['qtd_ocs_pendentes'],
                    'valor_solicitado' => $dadosOS['valor_solicitado'],
                    'valor_cotado' => $dadosOS['valor_cotado'],
                    'valor_aprovado' => $dadosOS['valor_aprovado'],
                    'valor_pendente_aprovacao' => $dadosOS['valor_pendente_aprovacao'],
                    'valor_pago' => $dadosOS['valor_pago'],
                    'valor_a_pagar' => $dadosOS['valor_a_pagar'],
                    'valor_recebido' => $dadosOS['valor_recebido'],
                    'valor_a_receber' => $dadosOS['valor_a_receber'],
                    'valor_fretes' => $dadosOS['valor_fretes'],
                    // Terceirizados
                    'qtd_terceirizados' => $dadosOS['qtd_terceirizados'],
                    'valor_terceirizados' => $dadosOS['valor_terceirizados'],
                    'valor_terceirizados_pago' => $dadosOS['valor_terceirizados_pago'],
                    'valor_terceirizados_pendente' => $dadosOS['valor_terceirizados_pendente'],
                    'valor_total' => $dadosOS['valor_total']
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'resumo' => [
                    'total_os' => count($resultado),
                    'totais' => $totaisGerais
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro no relatório de O.S.: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcula todo o fluxo financeiro de uma O.S.
     */
    private function calcularFluxoOS($osId)
    {
        // 1. Buscar cotações dessa O.S.
        $cotacoes = DB::table('cotacoes')
            ->where('ordem_servico_id', $osId)
            ->where('status', '!=', 'cancelada')
            ->get();
        
        $cotacaoIds = $cotacoes->pluck('id')->toArray();
        $qtdCotacoes = count($cotacaoIds);
        
        // 2. Calcular valor cotado (soma dos itens cotados pelos fornecedores)
        $valorCotado = 0;
        if (!empty($cotacaoIds)) {
            $valorCotado = DB::table('cotacao_fornecedores')
                ->whereIn('cotacao_id', $cotacaoIds)
                ->where('selecionado', 1)
                ->sum('valor_total');
        }
        
        // 3. Buscar Ordens de Compra vinculadas às cotações
        $ordensCompra = [];
        if (!empty($cotacaoIds)) {
            $ordensCompra = DB::table('ordens_compra')
                ->whereIn('cotacao_id', $cotacaoIds)
                ->where('status', '!=', 'cancelada')
                ->get();
        }
        
        $ocIds = collect($ordensCompra)->pluck('id')->toArray();
        $qtdOCs = count($ocIds);
        
        // Separar OCs aprovadas e pendentes
        $ocsAprovadas = collect($ordensCompra)->filter(function($oc) {
            return in_array($oc->status, ['aprovada', 'enviada', 'recebida_parcial', 'recebida']);
        });
        $ocsPendentes = collect($ordensCompra)->filter(function($oc) {
            return $oc->status === 'pendente';
        });
        
        $valorAprovado = $ocsAprovadas->sum('valor_total');
        $valorPendenteAprovacao = $ocsPendentes->sum('valor_total');
        
        // 4. Buscar Pagamentos (Contas a Pagar) vinculados às OCs
        $valorPago = 0;
        $valorAPagar = 0;
        if (!empty($ocIds)) {
            // Pagamentos feitos
            $valorPago = DB::table('contas_pagar')
                ->whereIn('ordem_compra_id', $ocIds)
                ->where('status', 'pago')
                ->sum('valor_pago');
            
            // Valores pendentes de pagamento
            $valorAPagar = DB::table('contas_pagar')
                ->whereIn('ordem_compra_id', $ocIds)
                ->whereIn('status', ['pendente', 'vencido'])
                ->sum('valor');
        }
        
        // 5. Buscar Recebimentos no Almoxarifado
        $valorRecebido = 0;
        $valorAReceber = 0;
        if (!empty($ocIds)) {
            // OCs com recebimento
            $ocsRecebidas = DB::table('recebimentos')
                ->whereIn('ordem_compra_id', $ocIds)
                ->distinct()
                ->pluck('ordem_compra_id')
                ->toArray();
            
            // Valor das OCs recebidas
            $valorRecebido = collect($ordensCompra)
                ->filter(function($oc) use ($ocsRecebidas) {
                    return in_array($oc->id, $ocsRecebidas);
                })
                ->sum('valor_total');
            
            // Valor a receber (OCs aprovadas mas não recebidas)
            $valorAReceber = $ocsAprovadas
                ->filter(function($oc) use ($ocsRecebidas) {
                    return !in_array($oc->id, $ocsRecebidas);
                })
                ->sum('valor_total');
        }
        
        // 6. Buscar Fretes
        $valorFretes = DB::table('fretes')
            ->where('ordem_servico_id', $osId)
            ->sum('valor_aprovado');
        
        // 7. Buscar Terceirizados/Prestadores de Serviço
        $terceirizados = DB::table('ordens_servico_prestadores')
            ->where('ordem_servico_id', $osId)
            ->get();
        
        $qtdTerceirizados = $terceirizados->count();
        $valorTerceirizados = $terceirizados->sum('valor');
        $valorTerceirizadosPago = $terceirizados->where('status_pagamento', 'pago')->sum('valor');
        $valorTerceirizadosPendente = $terceirizados->whereIn('status_pagamento', ['aguardando_autorizacao', 'aguardando_pagamento', 'pendente'])->sum('valor');
        
        // 8. Valor total (OCs aprovadas + fretes + terceirizados pagos)
        $valorTotal = $valorAprovado + $valorFretes + $valorTerceirizadosPago;
        
        // 9. Valor solicitado (estimativa baseada nos itens das cotações)
        $valorSolicitado = $valorCotado; // Como não temos preço na solicitação, usamos o valor cotado como referência
        
        return [
            'qtd_cotacoes' => $qtdCotacoes,
            'qtd_ocs' => $qtdOCs,
            'qtd_ocs_aprovadas' => $ocsAprovadas->count(),
            'qtd_ocs_pendentes' => $ocsPendentes->count(),
            'valor_solicitado' => $valorSolicitado,
            'valor_cotado' => $valorCotado,
            'valor_aprovado' => $valorAprovado,
            'valor_pendente_aprovacao' => $valorPendenteAprovacao,
            'valor_pago' => $valorPago,
            'valor_a_pagar' => $valorAPagar,
            'valor_recebido' => $valorRecebido,
            'valor_a_receber' => $valorAReceber,
            'valor_fretes' => $valorFretes,
            'qtd_terceirizados' => $qtdTerceirizados,
            'valor_terceirizados' => $valorTerceirizados,
            'valor_terceirizados_pago' => $valorTerceirizadosPago,
            'valor_terceirizados_pendente' => $valorTerceirizadosPendente,
            'valor_total' => $valorTotal
        ];
    }

    /**
     * Retorna os detalhes completos de uma O.S. específica
     */
    public function detalhes($id)
    {
        try {
            // Dados da O.S.
            $os = DB::table('ordens_servico as os')
                ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                ->leftJoin('users as u', 'os.user_id', '=', 'u.id')
                ->where('os.id', $id)
                ->select('os.*', 'cc.nome as centro_custo', 'u.name as responsavel')
                ->first();
            
            if (!$os) {
                return response()->json(['success' => false, 'message' => 'O.S. não encontrada'], 404);
            }
            
            // Cotações
            $cotacoes = DB::table('cotacoes as c')
                ->leftJoin('users as u', 'c.solicitante_id', '=', 'u.id')
                ->where('c.ordem_servico_id', $id)
                ->select('c.*', 'u.name as solicitante')
                ->orderBy('c.created_at', 'desc')
                ->get();
            
            $cotacaoIds = $cotacoes->pluck('id')->toArray();
            
            // Ordens de Compra
            $ordensCompra = [];
            if (!empty($cotacaoIds)) {
                $ordensCompra = DB::table('ordens_compra as oc')
                    ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                    ->leftJoin('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
                    ->whereIn('oc.cotacao_id', $cotacaoIds)
                    ->select('oc.*', 'f.razao_social as fornecedor', 'c.numero as cotacao_numero')
                    ->orderBy('oc.created_at', 'desc')
                    ->get();
            }
            
            $ocIds = collect($ordensCompra)->pluck('id')->toArray();
            
            // Pagamentos
            $pagamentos = [];
            if (!empty($ocIds)) {
                $pagamentos = DB::table('contas_pagar as cp')
                    ->leftJoin('ordens_compra as oc', 'cp.ordem_compra_id', '=', 'oc.id')
                    ->whereIn('cp.ordem_compra_id', $ocIds)
                    ->select('cp.*', 'oc.numero as oc_numero')
                    ->orderBy('cp.data_vencimento', 'asc')
                    ->get();
            }
            
            // Recebimentos
            $recebimentos = [];
            if (!empty($ocIds)) {
                $recebimentos = DB::table('recebimentos as r')
                    ->leftJoin('ordens_compra as oc', 'r.ordem_compra_id', '=', 'oc.id')
                    ->leftJoin('users as u', 'r.responsavel_id', '=', 'u.id')
                    ->whereIn('r.ordem_compra_id', $ocIds)
                    ->select('r.*', 'oc.numero as oc_numero', 'u.name as responsavel')
                    ->orderBy('r.data_recebimento', 'desc')
                    ->get();
            }
            
            // Fretes
            $fretes = DB::table('fretes')
                ->where('ordem_servico_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Terceirizados/Prestadores de Serviço
            $terceirizados = DB::table('ordens_servico_prestadores')
                ->where('ordem_servico_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'os' => $os,
                'cotacoes' => $cotacoes,
                'ordens_compra' => $ordensCompra,
                'pagamentos' => $pagamentos,
                'recebimentos' => $recebimentos,
                'fretes' => $fretes,
                'terceirizados' => $terceirizados
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar detalhes: ' . $e->getMessage()
            ], 500);
        }
    }
}
