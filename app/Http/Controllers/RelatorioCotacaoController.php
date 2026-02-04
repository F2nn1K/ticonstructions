<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioCotacaoController extends Controller
{
    /**
     * Exibe a página do relatório de cotações
     */
    public function index()
    {
        // Buscar centros de custo ativos para o filtro
        $centrosCusto = DB::table('centros_custo')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get(['id', 'nome']);
        
        return view('relatorios.cotacoes', compact('centrosCusto'));
    }

    /**
     * Retorna os dados do relatório de cotações com filtros
     */
    public function data(Request $request)
    {
        $dataInicio = $request->query('data_inicio');
        $dataFim = $request->query('data_fim');
        $status = $request->query('status'); // 'aberta', 'finalizada', 'todas'
        $centroCustoIds = $request->query('centro_custo_ids'); // Agora pode ser múltiplos IDs separados por vírgula

        // Query base para cotações
        $query = DB::table('cotacoes as c')
            ->leftJoin('users as u', 'c.solicitante_id', '=', 'u.id')
            ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
            ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
            ->select([
                'c.id',
                'c.numero',
                'c.descricao',
                'c.data_solicitacao',
                'c.data_limite',
                'c.status',
                'c.created_at',
                'u.name as solicitante',
                'os.cidade',
                'os.estado',
                'cc.nome as centro_custo',
                'cc.id as centro_custo_id',
            ])
            ->orderBy('c.created_at', 'desc');

        // Filtro por data
        if ($dataInicio) {
            $query->whereDate('c.data_solicitacao', '>=', $dataInicio);
        }
        if ($dataFim) {
            $query->whereDate('c.data_solicitacao', '<=', $dataFim);
        }

        // Filtro por centro de custo (obra) - agora suporta múltiplos
        if ($centroCustoIds && $centroCustoIds !== 'todos') {
            $ids = array_filter(explode(',', $centroCustoIds));
            if (count($ids) > 0) {
                $query->whereIn('cc.id', $ids);
            }
        }

        // Filtro por status
        if ($status && $status !== 'todas') {
            if ($status === 'aguardando_aprovacao') {
                // Para "Aguardando Aprovação", buscar diretamente as OCs pendentes
                // e retornar cada OC como uma linha separada
                return $this->getOCsPendentes($request, $dataInicio, $dataFim, $centroCustoIds);
            } else {
                $query->where('c.status', $status);
            }
        }

        $cotacoes = $query->get();

        // Para cada cotação, buscar os itens e fornecedores
        $resultado = [];
        $totalGeral = 0;
        $totalNaoCotadas = 0;
        $totalCotadas = 0;

        foreach ($cotacoes as $cotacao) {
            // Buscar itens da cotação
            $itens = DB::table('cotacao_itens')
                ->where('cotacao_id', $cotacao->id)
                ->get(['produto', 'quantidade', 'unidade']);

            // Verificar se há OCs geradas para esta cotação
            $ocsGeradas = DB::table('ordens_compra as oc')
                ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                ->where('oc.cotacao_id', $cotacao->id)
                ->select('oc.*', 'f.razao_social as fornecedor')
                ->get();

            // Montar cidade/UF
            $cidadeUf = null;
            if ($cotacao->cidade || $cotacao->estado) {
                $cidadeUf = trim(($cotacao->cidade ?? '') . '/' . ($cotacao->estado ?? ''), '/');
            }

            if ($ocsGeradas->count() > 0) {
                // Se há OCs geradas, mostrar uma linha para cada OC
                foreach ($ocsGeradas as $oc) {
                    $totalCotadas++;
                    $totalGeral += $oc->valor_total ?? 0;

                    // Determinar status para exibição
                    $statusExibicao = $cotacao->status;
                    $isOcPendente = false;
                    if ($oc->status === 'pendente') {
                        $isOcPendente = true;
                    }

                    $resultado[] = [
                        'id' => $cotacao->id,
                        'numero' => $cotacao->numero,
                        'oc_numero' => $oc->numero,
                        'oc_id' => $oc->id,
                        'descricao' => $cotacao->descricao,
                        'data_solicitacao' => $cotacao->data_solicitacao,
                        'data_limite' => $cotacao->data_limite,
                        'status' => $statusExibicao,
                        'oc_status' => $oc->status,
                        'solicitante' => $cotacao->solicitante,
                        'cidade_uf' => $cidadeUf,
                        'centro_custo' => $cotacao->centro_custo,
                        'itens' => $itens,
                        'qtd_itens' => $itens->count(),
                        'fornecedores' => [],
                        'qtd_fornecedores' => 1,
                        'tem_cotacao' => true,
                        'menor_valor' => $oc->valor_total,
                        'fornecedor_vencedor' => $oc->fornecedor,
                        'is_oc_pendente' => $isOcPendente,
                    ];
                }
            } else {
                // Se não há OCs, mostrar a cotação normal (buscar fornecedores cotados)
                $fornecedores = DB::table('cotacao_fornecedores as cf')
                    ->leftJoin('fornecedores as f', 'cf.fornecedor_id', '=', 'f.id')
                    ->where('cf.cotacao_id', $cotacao->id)
                    ->get([
                        'f.razao_social',
                        'cf.valor_total',
                        'cf.prazo_entrega',
                        'cf.selecionado'
                    ]);

                // Calcular menor valor entre os fornecedores
                $menorValor = null;
                $fornecedorVencedor = null;
                foreach ($fornecedores as $forn) {
                    if ($forn->valor_total && ($menorValor === null || $forn->valor_total < $menorValor)) {
                        $menorValor = $forn->valor_total;
                        $fornecedorVencedor = $forn->razao_social;
                    }
                    // Se há um selecionado, usar ele
                    if ($forn->selecionado) {
                        $menorValor = $forn->valor_total;
                        $fornecedorVencedor = $forn->razao_social;
                        break;
                    }
                }

                $temCotacao = $fornecedores->count() > 0 && $menorValor !== null;

                if ($temCotacao) {
                    $totalCotadas++;
                    $totalGeral += $menorValor;
                } else {
                    $totalNaoCotadas++;
                }

                $resultado[] = [
                    'id' => $cotacao->id,
                    'numero' => $cotacao->numero,
                    'descricao' => $cotacao->descricao,
                    'data_solicitacao' => $cotacao->data_solicitacao,
                    'data_limite' => $cotacao->data_limite,
                    'status' => $cotacao->status,
                    'solicitante' => $cotacao->solicitante,
                    'cidade_uf' => $cidadeUf,
                    'centro_custo' => $cotacao->centro_custo,
                    'itens' => $itens,
                    'qtd_itens' => $itens->count(),
                    'fornecedores' => $fornecedores,
                    'qtd_fornecedores' => $fornecedores->count(),
                    'tem_cotacao' => $temCotacao,
                    'menor_valor' => $menorValor,
                    'fornecedor_vencedor' => $fornecedorVencedor,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $resultado,
            'resumo' => [
                'total' => count($resultado),
                'cotadas' => $totalCotadas,
                'nao_cotadas' => $totalNaoCotadas,
                'valor_total' => $totalGeral,
            ]
        ]);
    }

    /**
     * Busca OCs pendentes de aprovação (para mostrar cada OC separadamente)
     */
    private function getOCsPendentes(Request $request, $dataInicio, $dataFim, $centroCustoIds)
    {
        $query = DB::table('ordens_compra as oc')
            ->leftJoin('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
            ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
            ->leftJoin('users as u', 'c.solicitante_id', '=', 'u.id')
            ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
            ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
            ->select([
                'oc.id as oc_id',
                'oc.numero as oc_numero',
                'oc.valor_total',
                'oc.status as oc_status',
                'oc.data_emissao',
                'c.id',
                'c.numero',
                'c.descricao',
                'c.data_solicitacao',
                'c.data_limite',
                'c.status',
                'u.name as solicitante',
                'os.cidade',
                'os.estado',
                'cc.nome as centro_custo',
                'cc.id as centro_custo_id',
                'f.razao_social as fornecedor',
            ])
            ->where('oc.status', 'pendente')
            ->orderBy('oc.created_at', 'desc');

        // Filtro por data
        if ($dataInicio) {
            $query->whereDate('c.data_solicitacao', '>=', $dataInicio);
        }
        if ($dataFim) {
            $query->whereDate('c.data_solicitacao', '<=', $dataFim);
        }

        // Filtro por centro de custo (obra) - suporta múltiplos
        if ($centroCustoIds && $centroCustoIds !== 'todos') {
            $ids = array_filter(explode(',', $centroCustoIds));
            if (count($ids) > 0) {
                $query->whereIn('cc.id', $ids);
            }
        }

        $ocs = $query->get();

        $resultado = [];
        $totalGeral = 0;

        foreach ($ocs as $oc) {
            // Buscar itens da cotação
            $itens = DB::table('cotacao_itens')
                ->where('cotacao_id', $oc->id)
                ->get(['produto', 'quantidade', 'unidade']);

            // Montar cidade/UF
            $cidadeUf = null;
            if ($oc->cidade || $oc->estado) {
                $cidadeUf = trim(($oc->cidade ?? '') . '/' . ($oc->estado ?? ''), '/');
            }

            $totalGeral += $oc->valor_total ?? 0;

            $resultado[] = [
                'id' => $oc->id,
                'numero' => $oc->numero,
                'oc_numero' => $oc->oc_numero,
                'descricao' => $oc->descricao,
                'data_solicitacao' => $oc->data_solicitacao,
                'data_limite' => $oc->data_limite,
                'status' => $oc->status,
                'oc_status' => $oc->oc_status,
                'solicitante' => $oc->solicitante,
                'cidade_uf' => $cidadeUf,
                'centro_custo' => $oc->centro_custo,
                'itens' => $itens,
                'qtd_itens' => $itens->count(),
                'fornecedores' => [],
                'qtd_fornecedores' => 1,
                'tem_cotacao' => true,
                'menor_valor' => $oc->valor_total,
                'fornecedor_vencedor' => $oc->fornecedor,
                'is_oc_pendente' => true, // Flag para identificar que é uma OC pendente
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $resultado,
            'resumo' => [
                'total' => count($resultado),
                'cotadas' => count($resultado),
                'nao_cotadas' => 0,
                'valor_total' => $totalGeral,
            ]
        ]);
    }
}
