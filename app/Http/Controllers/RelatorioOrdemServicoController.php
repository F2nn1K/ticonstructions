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
     *
     * O filtro de período é aplicado sobre as datas de ATIVIDADE do fluxo
     * (cotações, OCs, pagamentos, recebimentos criados no período), não apenas
     * sobre a data de abertura da O.S.
     */
    public function data(Request $request)
    {
        // Aumentar tempo limite para relatórios grandes
        set_time_limit(120);
        
        try {
            $dataInicio = $request->input('data_inicio') ?: null;
            $dataFim    = $request->input('data_fim')    ?: null;

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
                )
                ->distinct();

            // Filtros não relacionados a datas
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

            // Filtro de período: inclui a O.S. se ela OU qualquer item do seu fluxo
            // (cotação, OC, pagamento, recebimento) foi criado dentro do intervalo.
            if ($dataInicio || $dataFim) {
                $query->where(function ($q) use ($dataInicio, $dataFim) {
                    // A própria O.S. foi aberta no período
                    $q->where(function ($s) use ($dataInicio, $dataFim) {
                        if ($dataInicio) $s->whereDate('os.data_os', '>=', $dataInicio);
                        if ($dataFim)    $s->whereDate('os.data_os', '<=', $dataFim);
                    });

                    // Ou tem cotação criada no período
                    $q->orWhereExists(function ($s) use ($dataInicio, $dataFim) {
                        $s->select(DB::raw(1))
                          ->from('cotacoes as cot')
                          ->whereColumn('cot.ordem_servico_id', 'os.id')
                          ->where('cot.status', '!=', 'cancelada');
                        if ($dataInicio) $s->whereDate('cot.created_at', '>=', $dataInicio);
                        if ($dataFim)    $s->whereDate('cot.created_at', '<=', $dataFim);
                    });

                    // Ou tem OC criada no período (via cotação da OS)
                    $q->orWhereExists(function ($s) use ($dataInicio, $dataFim) {
                        $s->select(DB::raw(1))
                          ->from('ordens_compra as oc2')
                          ->join('cotacoes as cot2', 'oc2.cotacao_id', '=', 'cot2.id')
                          ->whereColumn('cot2.ordem_servico_id', 'os.id')
                          ->where('oc2.status', '!=', 'cancelada');
                        if ($dataInicio) $s->whereDate('oc2.created_at', '>=', $dataInicio);
                        if ($dataFim)    $s->whereDate('oc2.created_at', '<=', $dataFim);
                    });

                    // Ou tem pagamento realizado no período
                    $q->orWhereExists(function ($s) use ($dataInicio, $dataFim) {
                        $s->select(DB::raw(1))
                          ->from('contas_pagar as cp2')
                          ->join('ordens_compra as oc3', 'cp2.ordem_compra_id', '=', 'oc3.id')
                          ->join('cotacoes as cot3', 'oc3.cotacao_id', '=', 'cot3.id')
                          ->whereColumn('cot3.ordem_servico_id', 'os.id');
                        if ($dataInicio) $s->whereDate('cp2.updated_at', '>=', $dataInicio);
                        if ($dataFim)    $s->whereDate('cp2.updated_at', '<=', $dataFim);
                    });

                    // Ou tem recebimento realizado no período
                    $q->orWhereExists(function ($s) use ($dataInicio, $dataFim) {
                        $s->select(DB::raw(1))
                          ->from('recebimentos as rec2')
                          ->join('ordens_compra as oc4', 'rec2.ordem_compra_id', '=', 'oc4.id')
                          ->join('cotacoes as cot4', 'oc4.cotacao_id', '=', 'cot4.id')
                          ->whereColumn('cot4.ordem_servico_id', 'os.id');
                        if ($dataInicio) $s->whereDate('rec2.data_recebimento', '>=', $dataInicio);
                        if ($dataFim)    $s->whereDate('rec2.data_recebimento', '<=', $dataFim);
                    });

                    // Ou tem prestador/terceirizado criado no período
                    $q->orWhereExists(function ($s) use ($dataInicio, $dataFim) {
                        $s->select(DB::raw(1))
                          ->from('ordens_servico_prestadores as osp2')
                          ->whereColumn('osp2.ordem_servico_id', 'os.id');
                        if ($dataInicio) $s->whereDate('osp2.created_at', '>=', $dataInicio);
                        if ($dataFim)    $s->whereDate('osp2.created_at', '<=', $dataFim);
                    });
                });
            }

            $ordens = $query->orderBy('os.data_os', 'desc')->get();

            // Calcular totais de cada O.S. com todo o fluxo (restrito ao período)
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
                $dadosOS = $this->calcularFluxoOS($os->id, $dataInicio, $dataFim);
                
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
     * Quando $dataInicio/$dataFim são informados, restringe cotações, OCs,
     * pagamentos, recebimentos, fretes e terceirizados ao período.
     */
    private function calcularFluxoOS($osId, $dataInicio = null, $dataFim = null)
    {
        // 1. Buscar cotações dessa O.S. (restritas ao período se informado)
        $qCot = DB::table('cotacoes')
            ->where('ordem_servico_id', $osId)
            ->where('status', '!=', 'cancelada');
        if ($dataInicio) $qCot->whereDate('created_at', '>=', $dataInicio);
        if ($dataFim)    $qCot->whereDate('created_at', '<=', $dataFim);
        $cotacoes = $qCot->get();

        // Também precisamos dos IDs de TODAS as cotações da OS para vincular OCs do período
        $todosIdsCotacoes = DB::table('cotacoes')
            ->where('ordem_servico_id', $osId)
            ->where('status', '!=', 'cancelada')
            ->pluck('id')
            ->toArray();

        $cotacaoIds = $cotacoes->pluck('id')->toArray();
        $qtdCotacoes = count($cotacaoIds);
        
        // 2. Calcular valor cotado (soma dos fornecedores selecionados nas cotações do período)
        $valorCotado = 0;
        if (!empty($cotacaoIds)) {
            $valorCotado = DB::table('cotacao_fornecedores')
                ->whereIn('cotacao_id', $cotacaoIds)
                ->where('selecionado', 1)
                ->sum('valor_total');
        }
        
        // 3. Buscar Ordens de Compra vinculadas às cotações (restritas ao período)
        $ordensCompra = collect([]);
        if (!empty($todosIdsCotacoes)) {
            $qOC = DB::table('ordens_compra')
                ->whereIn('cotacao_id', $todosIdsCotacoes)
                ->where('status', '!=', 'cancelada');
            if ($dataInicio) $qOC->whereDate('created_at', '>=', $dataInicio);
            if ($dataFim)    $qOC->whereDate('created_at', '<=', $dataFim);
            $ordensCompra = $qOC->get();
        }
        
        $ocIds = $ordensCompra->pluck('id')->toArray();
        $qtdOCs = count($ocIds);
        
        // Separar OCs aprovadas e pendentes
        $ocsAprovadas = $ordensCompra->filter(function ($oc) {
            return in_array($oc->status, ['aprovada', 'enviada', 'recebida_parcial', 'recebida']);
        });
        $ocsPendentes = $ordensCompra->filter(function ($oc) {
            return $oc->status === 'pendente';
        });
        
        $valorAprovado = $ocsAprovadas->sum('valor_total');
        $valorPendenteAprovacao = $ocsPendentes->sum('valor_total');
        
        // 4. Buscar Pagamentos (Contas a Pagar) vinculados às OCs (restritos ao período)
        $valorPago = 0;
        $valorAPagar = 0;
        if (!empty($ocIds)) {
            $qPago = DB::table('contas_pagar')
                ->whereIn('ordem_compra_id', $ocIds)
                ->where('status', 'pago');
            if ($dataInicio) $qPago->whereDate('updated_at', '>=', $dataInicio);
            if ($dataFim)    $qPago->whereDate('updated_at', '<=', $dataFim);
            $valorPago = $qPago->sum('valor_pago');
            
            $qAPagar = DB::table('contas_pagar')
                ->whereIn('ordem_compra_id', $ocIds)
                ->whereIn('status', ['pendente', 'vencido']);
            $valorAPagar = $qAPagar->sum('valor');
        }
        
        // 5. Buscar Recebimentos no Almoxarifado (restritos ao período)
        $valorRecebido = 0;
        $valorAReceber = 0;
        if (!empty($ocIds)) {
            $qRec = DB::table('recebimentos')
                ->whereIn('ordem_compra_id', $ocIds);
            if ($dataInicio) $qRec->whereDate('data_recebimento', '>=', $dataInicio);
            if ($dataFim)    $qRec->whereDate('data_recebimento', '<=', $dataFim);
            $ocsRecebidas = $qRec->distinct()->pluck('ordem_compra_id')->toArray();
            
            $valorRecebido = $ordensCompra
                ->filter(function ($oc) use ($ocsRecebidas) {
                    return in_array($oc->id, $ocsRecebidas);
                })
                ->sum('valor_total');
            
            $valorAReceber = $ocsAprovadas
                ->filter(function ($oc) use ($ocsRecebidas) {
                    return !in_array($oc->id, $ocsRecebidas);
                })
                ->sum('valor_total');
        }
        
        // 6. Buscar Fretes (restritos ao período)
        $qFretes = DB::table('fretes')->where('ordem_servico_id', $osId);
        if ($dataInicio) $qFretes->whereDate('created_at', '>=', $dataInicio);
        if ($dataFim)    $qFretes->whereDate('created_at', '<=', $dataFim);
        $valorFretes = $qFretes->sum('valor_aprovado');
        
        // 7. Buscar Terceirizados/Prestadores de Serviço (restritos ao período)
        $qTerc = DB::table('ordens_servico_prestadores')->where('ordem_servico_id', $osId);
        if ($dataInicio) $qTerc->whereDate('created_at', '>=', $dataInicio);
        if ($dataFim)    $qTerc->whereDate('created_at', '<=', $dataFim);
        $terceirizados = $qTerc->get();
        
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
