<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Baixa;
use App\Models\Estoque;
use App\Models\CentroCusto;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;

class RelatorioEstoqueController extends Controller
{
    public function index()
    {
        return view('relatorios.estoque');
    }

    public function gerarRelatorio(Request $request)
    {
        // Novo comportamento: listar produtos da tabela de estoque (nome, descricao, quantidade)
        $request->validate([
            'produto_id' => 'nullable|exists:estoque,id',
        ]);

        $query = Estoque::query()->select('id', 'nome', 'descricao', 'quantidade');

        if ($request->filled('produto_id')) {
            $query->where('id', $request->produto_id);
        }

        $produtos = $query->orderBy('nome')->get()->map(function ($p) {
            return [
                'id' => (int) $p->id,
                'nome' => (string) ($p->nome ?? ''),
                'descricao' => (string) ($p->descricao ?? ''),
                'quantidade' => (int) ($p->quantidade ?? 0),
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'dados' => $produtos,
            'total_registros' => count($produtos)
        ]);
    }

    private function agruparBaixas($baixas)
    {
        $agrupados = [];

        foreach ($baixas as $baixa) {
            $chave = $baixa->funcionario_id . '_' . $baixa->centro_custo_id . '_' . $baixa->data_baixa->format('Y-m-d H:i');
            
            if (!isset($agrupados[$chave])) {
                $agrupados[$chave] = [
                    'data' => $baixa->data_baixa->format('d/m/Y'),
                    'hora' => $baixa->data_baixa->format('H:i'),
                    'funcionario' => [
                        'id' => $baixa->funcionario->id,
                        'nome' => $baixa->funcionario->nome,
                        'funcao' => $baixa->funcionario->funcao ?? 'Não informado'
                    ],
                    'centro_custo' => [
                        'id' => $baixa->centroCusto->id ?? null,
                        'nome' => $baixa->centroCusto->nome ?? 'Não informado'
                    ],
                    'observacoes' => $baixa->observacoes,
                    'usuario' => $baixa->usuario->name,
                    'produtos' => [],
                    'total_itens' => 0
                ];
            }

            $agrupados[$chave]['produtos'][] = [
                'id' => $baixa->produto->id,
                'nome' => $baixa->produto->nome,
                'descricao' => $baixa->produto->descricao,
                'quantidade' => $baixa->quantidade
            ];

            $agrupados[$chave]['total_itens'] += $baixa->quantidade;
        }

        return array_values($agrupados);
    }

    private function agruparPorCentroCustoProduto($baixas)
    {
        $agrupados = [];

        foreach ($baixas as $baixa) {
            $centroCustoId = $baixa->centro_custo_id ?? 0;
            $centroCustoNome = $baixa->centroCusto->nome ?? 'Centro de Custo não informado';
            $produtoId = $baixa->produto_id;
                        $produtoNome = $baixa->produto->nome ?? 'Produto não identificado';
            
            // Chave do centro de custo
            if (!isset($agrupados[$centroCustoId])) {
                $agrupados[$centroCustoId] = [
                    'centro_custo' => [
                        'id' => $centroCustoId,
                        'nome' => $centroCustoNome
                    ],
                    'produtos' => [],
                    'total_centro_custo' => 0,
                    'total_produtos_tipos' => 0
                ];
            }
            
            // Chave do produto dentro do centro de custo (forçar como integer para evitar duplicatas)
            $produtoKey = (int)$produtoId;
            
            if (!isset($agrupados[$centroCustoId]['produtos'][$produtoKey])) {
                $agrupados[$centroCustoId]['produtos'][$produtoKey] = [
                    'produto' => [
                        'id' => $produtoId,
                        'nome' => $produtoNome,
                        'descricao' => $baixa->produto->descricao ?? ''
                    ],
                    'quantidade_total' => 0,
                    'movimentacoes_count' => 0,
                    'periodo_primeira' => null,
                    'periodo_ultima' => null
                ];
            }
            
            // Acumular totais
            $agrupados[$centroCustoId]['produtos'][$produtoKey]['quantidade_total'] += $baixa->quantidade;
            $agrupados[$centroCustoId]['produtos'][$produtoKey]['movimentacoes_count']++;
            $agrupados[$centroCustoId]['total_centro_custo'] += $baixa->quantidade;
            
            // Controlar período das movimentações
            $dataAtual = $baixa->data_baixa->format('d/m/Y');
            if (!$agrupados[$centroCustoId]['produtos'][$produtoKey]['periodo_primeira']) {
                $agrupados[$centroCustoId]['produtos'][$produtoKey]['periodo_primeira'] = $dataAtual;
            }
            $agrupados[$centroCustoId]['produtos'][$produtoKey]['periodo_ultima'] = $dataAtual;
        }
        
        // Processar dados finais
        foreach ($agrupados as &$centro) {
            $centro['total_produtos_tipos'] = count($centro['produtos']);
            
            // Converter produtos de array associativo para array indexado e ordenar por quantidade
            $produtosArray = array_values($centro['produtos']);
            usort($produtosArray, function($a, $b) {
                return $b['quantidade_total'] - $a['quantidade_total'];
            });
            $centro['produtos'] = $produtosArray;
        }

        // Ordenar centros de custo por total de saídas (maior primeiro)
        $centrosArray = array_values($agrupados);
        usort($centrosArray, function($a, $b) {
            return $b['total_centro_custo'] - $a['total_centro_custo'];
        });

        return $centrosArray;
    }

    private function calcularResumo($baixas, $request)
    {
        $totalSaidas = $baixas->sum('quantidade');
        $totalMovimentacoes = $baixas->count();
        $produtosUnicos = $baixas->pluck('produto_id')->unique()->count();
        $centrosUnicos = $baixas->pluck('centro_custo_id')->unique()->filter()->count();
        $funcionariosUnicos = $baixas->pluck('funcionario_id')->unique()->count();

        // Calcular total de entradas no período (se houver tabela de entradas)
        $totalEntradas = 0; // Por enquanto 0, pois não temos tabela de entradas separada

        return [
            'total_saidas' => $totalSaidas,
            'total_entradas' => $totalEntradas,
            'total_movimentacoes' => $totalMovimentacoes,
            'produtos_movimentados' => $produtosUnicos,
            'centros_envolvidos' => $centrosUnicos,
            'funcionarios_atendidos' => $funcionariosUnicos,
            'periodo' => [
                'inicio' => $request->data_inicio,
                'fim' => $request->data_fim
            ]
        ];
    }

    public function exportarExcel(Request $request)
    {
        // Implementar exportação Excel futuramente
        return response()->json([
            'success' => false,
            'message' => 'Funcionalidade de exportação em desenvolvimento'
        ]);
    }
}