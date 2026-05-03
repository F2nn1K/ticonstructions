<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Baixa;
use App\Models\Estoque;
use App\Models\CentroCusto;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;

class RelatorioCentroCustoController extends Controller
{
    public function index()
    {
        return view('relatorios.centro-custo');
    }

    public function gerarRelatorio(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'centro_custo_id' => 'nullable|exists:centros_custo,id',
            'funcionario_id' => 'nullable|exists:funcionarios,id'
        ]);

        $query = Baixa::with(['funcionario', 'produto', 'centroCusto', 'usuario'])
            ->whereBetween('data_baixa', [
                $request->data_inicio . ' 00:00:00',
                $request->data_fim . ' 23:59:59'
            ]);

        // Filtro por centro de custo
        if ($request->centro_custo_id) {
            $query->where('centro_custo_id', $request->centro_custo_id);
        }

        // Filtro por funcionário
        if ($request->funcionario_id) {
            $query->where('funcionario_id', $request->funcionario_id);
        }

        $baixas = $query->orderBy('centro_custo_id', 'asc')
                       ->orderBy('funcionario_id', 'asc')
                       ->orderBy('data_baixa', 'desc')
                       ->get();

        // Agrupar por funcionário e centro de custo
        $dadosAgrupados = $this->agruparBaixasPorCentroCusto($baixas);

        // Calcular resumo
        $resumo = $this->calcularResumo($baixas, $request);

        return response()->json([
            'success' => true,
            'dados' => $dadosAgrupados,
            'resumo' => $resumo,
            'total_registros' => count($dadosAgrupados)
        ]);
    }

    private function agruparBaixasPorCentroCusto($baixas)
    {
        $agrupados = [];

        foreach ($baixas as $baixa) {
            // Criar chave única para cada combinação centro-funcionário-data
            $chave = $baixa->centro_custo_id . '_' . $baixa->funcionario_id . '_' . $baixa->data_baixa->format('Y-m-d H:i');
            
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

    private function calcularResumo($baixas, $request)
    {
        $totalItens = $baixas->sum('quantidade');
        $totalMovimentacoes = $baixas->count();
        $centrosUnicos = $baixas->pluck('centro_custo_id')->unique()->filter()->count();
        $funcionariosUnicos = $baixas->pluck('funcionario_id')->unique()->count();

        // Calcular detalhamento por centro de custo
        $porCentro = [];
        foreach ($baixas->groupBy('centro_custo_id') as $ccId => $baixasCC) {
            $centroCusto = $baixasCC->first()->centroCusto;
            $porCentro[] = [
                'centro_id' => $ccId,
                'centro_nome' => $centroCusto ? $centroCusto->nome : 'Não informado',
                'total_itens' => $baixasCC->sum('quantidade'),
                'total_movimentacoes' => $baixasCC->count(),
                'funcionarios_envolvidos' => $baixasCC->pluck('funcionario_id')->unique()->count()
            ];
        }

        return [
            'total_centros' => $centrosUnicos,
            'total_funcionarios' => $funcionariosUnicos,
            'total_movimentacoes' => $totalMovimentacoes,
            'total_itens' => $totalItens,
            'detalhamento_por_centro' => $porCentro,
            'periodo' => [
                'inicio' => $request->data_inicio,
                'fim' => $request->data_fim
            ]
        ];
    }

    public function exportarExcel(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'centro_custo_id' => 'nullable|exists:centros_custo,id',
            'funcionario_id' => 'nullable|exists:funcionarios,id'
        ]);

        $query = Baixa::with(['funcionario', 'produto', 'centroCusto', 'usuario'])
            ->whereBetween('data_baixa', [
                $request->data_inicio . ' 00:00:00',
                $request->data_fim . ' 23:59:59'
            ]);

        // Aplicar os mesmos filtros
        if ($request->centro_custo_id) {
            $query->where('centro_custo_id', $request->centro_custo_id);
        }

        if ($request->funcionario_id) {
            $query->where('funcionario_id', $request->funcionario_id);
        }

        $baixas = $query->orderBy('centro_custo_id', 'asc')
                       ->orderBy('funcionario_id', 'asc')
                       ->orderBy('data_baixa', 'desc')
                       ->get();

        // Para simular Excel, retornamos CSV por enquanto
        $csv = "Centro de Custo,Funcionário,Data,Hora,Produto,Quantidade,Observações\n";
        
        foreach ($baixas as $baixa) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s",%d,"%s"' . "\n",
                $baixa->centroCusto->nome ?? 'Não informado',
                $baixa->funcionario->nome,
                $baixa->data_baixa->format('d/m/Y'),
                $baixa->data_baixa->format('H:i'),
                $baixa->produto->nome,
                $baixa->quantidade,
                $baixa->observacoes ?? ''
            );
        }

        $filename = 'relatorio-centro-custo-' . date('Y-m-d') . '.csv';
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}