<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioSolicitacaoController extends Controller
{
    /**
     * Exibe a página do relatório de solicitações
     */
    public function index()
    {
        // Buscar centros de custo para o filtro
        $centrosCusto = DB::table('centros_custo')
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();
        
        // Buscar usuários para filtro de solicitante
        $usuarios = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return view('relatorios.solicitacoes', compact('centrosCusto', 'usuarios'));
    }

    /**
     * Retorna os dados do relatório de solicitações com filtros
     */
    public function data(Request $request)
    {
        try {
            $resultado = [];
            $totais = [
                'total' => 0,
                'qtd_itens' => 0,
                'valor_total' => 0,
                'materiais' => 0,
                'terceirizados' => 0,
            ];
            
            $tipoFiltro = $request->input('tipo'); // 'material', 'terceirizado' ou vazio (todos)
            $statusFiltro = $request->input('status'); // Status específico a filtrar
            
            // Mapear status genéricos para status específicos por tipo
            $statusMateriais = ['aberta', 'finalizada', 'parcial', 'rejeitada', 'cancelada'];
            $statusTerceirizados = ['aguardando_autorizacao', 'aguardando_pagamento', 'pendente', 'pago'];
            
            // Se tem filtro de status, determinar se deve buscar materiais e/ou terceirizados
            $buscarMateriais = true;
            $buscarTerceirizados = true;
            
            if ($statusFiltro) {
                // Se o status é específico de materiais, não buscar terceirizados
                if (in_array($statusFiltro, $statusMateriais)) {
                    $buscarTerceirizados = false;
                }
                // Se o status é específico de terceirizados, não buscar materiais
                if (in_array($statusFiltro, $statusTerceirizados)) {
                    $buscarMateriais = false;
                }
            }
            
            // ========================================
            // 1. BUSCAR COTAÇÕES (MATERIAIS)
            // ========================================
            if ((!$tipoFiltro || $tipoFiltro === 'material') && $buscarMateriais) {
            $queryCotacoes = DB::table('cotacoes as c')
                ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                ->leftJoin('users as u', 'c.solicitante_id', '=', 'u.id')
                ->select(
                    'c.id',
                    'c.numero',
                    'c.descricao',
                    'c.status',
                    'c.created_at',
                    'os.numero_os',
                    'cc.nome as centro_custo',
                    'u.name as solicitante'
                );

            // Filtros para cotações
            if ($request->filled('centro_custo_ids')) {
                $ids = array_filter(explode(',', $request->centro_custo_ids));
                if (!empty($ids)) {
                    $queryCotacoes->whereIn('os.centro_custo_id', $ids);
                }
            }
            
            if ($request->filled('solicitante_id')) {
                $queryCotacoes->where('c.solicitante_id', $request->solicitante_id);
            }
            
            if ($request->filled('data_inicio')) {
                $queryCotacoes->whereDate('c.created_at', '>=', $request->data_inicio);
            }
            
            if ($request->filled('data_fim')) {
                $queryCotacoes->whereDate('c.created_at', '<=', $request->data_fim);
            }
            
            // Filtro de status para materiais
            if ($statusFiltro && in_array($statusFiltro, $statusMateriais)) {
                $queryCotacoes->where('c.status', $statusFiltro);
            }

            $cotacoes = $queryCotacoes->orderBy('c.created_at', 'desc')->get();

            foreach ($cotacoes as $cot) {
                $qtdItensCotacao = DB::table('cotacao_itens')
                    ->where('cotacao_id', $cot->id)
                    ->count();
                
                // Buscar TODOS os fornecedores cotados para esta cotação
                $fornecedoresCotados = DB::table('cotacao_fornecedores as cf')
                    ->leftJoin('fornecedores as f', 'cf.fornecedor_id', '=', 'f.id')
                    ->where('cf.cotacao_id', $cot->id)
                    ->select('cf.*', 'f.razao_social as fornecedor_nome')
                    ->get();
                
                if ($fornecedoresCotados->isEmpty()) {
                    // Se não tem fornecedor cotado, mostra a cotação com valor 0
                    $totais['qtd_itens'] += $qtdItensCotacao;
                    $totais['total']++;
                    $totais['materiais']++;
                    
                    $resultado[] = [
                        'id' => $cot->id,
                        'numero' => $cot->numero,
                        'tipo' => 'material',
                        'descricao' => $cot->descricao,
                        'status' => $cot->status,
                        'created_at' => $cot->created_at,
                        'centro_custo' => $cot->centro_custo,
                        'solicitante' => $cot->solicitante,
                        'ordem_servico' => $cot->numero_os,
                        'qtd_itens' => $qtdItensCotacao,
                        'valor_cotado' => 0,
                        'fornecedor' => '-',
                    ];
                } else {
                    // Criar uma linha para CADA fornecedor cotado
                    foreach ($fornecedoresCotados as $fc) {
                        $valorCotado = floatval($fc->valor_total ?? 0);
                        
                        // Contar itens deste fornecedor específico
                        $qtdItensFornecedor = DB::table('cotacao_fornecedor_itens')
                            ->where('cotacao_fornecedor_id', $fc->id)
                            ->count();
                        
                        // Se não tem itens específicos, usar os itens da cotação
                        if ($qtdItensFornecedor == 0) {
                            $qtdItensFornecedor = $qtdItensCotacao;
                        }
                        
                        $totais['qtd_itens'] += $qtdItensFornecedor;
                        $totais['valor_total'] += $valorCotado;
                        $totais['total']++;
                        $totais['materiais']++;
                        
                        $resultado[] = [
                            'id' => $cot->id,
                            'numero' => $cot->numero,
                            'tipo' => 'material',
                            'descricao' => $cot->descricao,
                            'status' => $cot->status,
                            'created_at' => $cot->created_at,
                            'centro_custo' => $cot->centro_custo,
                            'solicitante' => $cot->solicitante,
                            'ordem_servico' => $cot->numero_os,
                            'qtd_itens' => $qtdItensFornecedor,
                            'valor_cotado' => $valorCotado,
                            'fornecedor' => $fc->fornecedor_nome ?? 'Fornecedor #' . $fc->fornecedor_id,
                        ];
                    }
                }
            }
            } // Fim do if para materiais
            
            // ========================================
            // 2. BUSCAR TERCEIRIZADOS/PRESTADORES
            // ========================================
            if ((!$tipoFiltro || $tipoFiltro === 'terceirizado') && $buscarTerceirizados) {
            $queryTerceirizados = DB::table('ordens_servico_prestadores as p')
                ->join('ordens_servico as os', 'p.ordem_servico_id', '=', 'os.id')
                ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                ->leftJoin('users as u', 'os.user_id', '=', 'u.id')
                ->select(
                    'p.id',
                    'p.nome_prestador',
                    'p.descricao_servico',
                    'p.valor',
                    'p.status_pagamento',
                    'p.created_at',
                    'os.numero_os',
                    'cc.nome as centro_custo',
                    'u.name as solicitante'
                );

            // Filtros para terceirizados
            if ($request->filled('centro_custo_ids')) {
                $ids = array_filter(explode(',', $request->centro_custo_ids));
                if (!empty($ids)) {
                    $queryTerceirizados->whereIn('os.centro_custo_id', $ids);
                }
            }
            
            if ($request->filled('solicitante_id')) {
                $queryTerceirizados->where('os.user_id', $request->solicitante_id);
            }
            
            if ($request->filled('data_inicio')) {
                $queryTerceirizados->whereDate('p.created_at', '>=', $request->data_inicio);
            }
            
            if ($request->filled('data_fim')) {
                $queryTerceirizados->whereDate('p.created_at', '<=', $request->data_fim);
            }
            
            // Filtro de status para terceirizados
            if ($statusFiltro && in_array($statusFiltro, $statusTerceirizados)) {
                $queryTerceirizados->where('p.status_pagamento', $statusFiltro);
            }

            $terceirizados = $queryTerceirizados->orderBy('p.created_at', 'desc')->get();

            foreach ($terceirizados as $terc) {
                $valor = floatval($terc->valor ?? 0);
                
                $totais['valor_total'] += $valor;
                $totais['total']++;
                $totais['terceirizados']++;
                
                // Mapear status do terceirizado
                $statusMap = [
                    'aguardando_autorizacao' => 'aguard_autorizacao',
                    'aguardando_pagamento' => 'aguard_pagamento',
                    'pendente' => 'pendente',
                    'pago' => 'pago'
                ];
                
                $resultado[] = [
                    'id' => $terc->id,
                    'numero' => 'TERC-' . str_pad($terc->id, 4, '0', STR_PAD_LEFT),
                    'tipo' => 'terceirizado',
                    'descricao' => $terc->nome_prestador . ' - ' . ($terc->descricao_servico ?? ''),
                    'status' => $statusMap[$terc->status_pagamento] ?? $terc->status_pagamento,
                    'created_at' => $terc->created_at,
                    'centro_custo' => $terc->centro_custo,
                    'solicitante' => $terc->solicitante,
                    'ordem_servico' => $terc->numero_os,
                    'qtd_itens' => 1,
                    'valor_cotado' => $valor,
                ];
            }
            } // Fim do if para terceirizados
            
            // Ordenar resultado final por data (mais recente primeiro)
            usort($resultado, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'resumo' => $totais
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro no relatório de solicitações: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }
}
