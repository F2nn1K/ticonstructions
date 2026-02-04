<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BaixaOSController extends Controller
{
    /**
     * Listar materiais das O.S. para liberação
     */
    public function listar(Request $request)
    {
        $status = $request->get('status', 'pendente');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $numeroOS = $request->get('numero_os');
        
        // Verificar se a coluna materiais_liberados existe
        $hasMateriaisLiberados = Schema::hasColumn('ordens_servico_itens', 'liberado');
        
        // Query base - Itens de O.S. com materiais
        $query = DB::table('ordens_servico_itens as osi')
            ->select(
                'osi.id as item_id',
                'osi.ordem_servico_id',
                'osi.produto_id',
                'osi.quantidade',
                'os.numero_os',
                'os.data_os',
                'os.status as os_status',
                'e.nome as produto_nome',
                'u.name as funcionario_nome',
                'cc.nome as centro_custo_nome'
            )
            ->join('ordens_servico as os', 'osi.ordem_servico_id', '=', 'os.id')
            ->leftJoin('estoque as e', 'osi.produto_id', '=', 'e.id')
            ->leftJoin('users as u', 'os.funcionario_id', '=', 'u.id')
            ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id');
        
        // Adicionar coluna liberado se existir
        if ($hasMateriaisLiberados) {
            $query->addSelect('osi.liberado', 'osi.liberado_por', 'osi.liberado_em', 'osi.retirado_por');
            
            // Filtro por status
            if ($status === 'pendente') {
                $query->where(function ($q) {
                    $q->whereNull('osi.liberado')
                      ->orWhere('osi.liberado', 0);
                });
            } elseif ($status === 'liberado') {
                $query->where('osi.liberado', 1);
            }
        }
        
        // Filtro por data
        if ($dataInicio) {
            $query->where('os.data_os', '>=', $dataInicio);
        }
        if ($dataFim) {
            $query->where('os.data_os', '<=', $dataFim);
        }
        
        // Filtro por número da O.S.
        if ($numeroOS) {
            $query->where('os.numero_os', 'like', '%' . $numeroOS . '%');
        }
        
        $itens = $query->orderBy('os.data_os', 'desc')
                       ->orderBy('os.id', 'desc')
                       ->limit(100)
                       ->get();
        
        // Agrupar por O.S.
        $ordensAgrupadas = [];
        foreach ($itens as $item) {
            $osId = $item->ordem_servico_id;
            if (!isset($ordensAgrupadas[$osId])) {
                $ordensAgrupadas[$osId] = [
                    'id' => $osId,
                    'numero_os' => $item->numero_os,
                    'data_os' => $item->data_os,
                    'funcionario_nome' => $item->funcionario_nome,
                    'centro_custo_nome' => $item->centro_custo_nome,
                    'materiais' => []
                ];
            }
            
            $ordensAgrupadas[$osId]['materiais'][] = [
                'item_id' => $item->item_id,
                'produto_id' => $item->produto_id,
                'produto_nome' => $item->produto_nome,
                'quantidade' => $item->quantidade,
                'liberado' => $hasMateriaisLiberados ? ($item->liberado ?? 0) : 0,
                'liberado_por' => $hasMateriaisLiberados ? ($item->liberado_por ?? null) : null,
                'liberado_em' => $hasMateriaisLiberados ? ($item->liberado_em ?? null) : null,
                'retirado_por' => $hasMateriaisLiberados ? ($item->retirado_por ?? null) : null
            ];
        }
        
        // Estatísticas
        $totalPendentes = 0;
        $totalItens = 0;
        $totalLiberadosHoje = 0;
        
        if ($hasMateriaisLiberados) {
            $totalPendentes = DB::table('ordens_servico_itens')
                ->where(function ($q) {
                    $q->whereNull('liberado')
                      ->orWhere('liberado', 0);
                })
                ->count();
            
            $totalItens = DB::table('ordens_servico_itens')
                ->where(function ($q) {
                    $q->whereNull('liberado')
                      ->orWhere('liberado', 0);
                })
                ->sum('quantidade');
            
            $totalLiberadosHoje = DB::table('ordens_servico_itens')
                ->where('liberado', 1)
                ->whereDate('liberado_em', today())
                ->count();
        } else {
            $totalPendentes = DB::table('ordens_servico_itens')->count();
            $totalItens = DB::table('ordens_servico_itens')->sum('quantidade');
        }
        
        return response()->json([
            'ordens' => array_values($ordensAgrupadas),
            'total_pendentes' => $totalPendentes,
            'total_itens' => $totalItens,
            'total_liberados_hoje' => $totalLiberadosHoje
        ]);
    }
    
    /**
     * Liberar materiais da O.S.
     */
    public function liberar(Request $request, $id)
    {
        try {
            $retiradoPor = $request->get('retirado_por', '');
            
            // Verificar se a O.S. existe
            $os = DB::table('ordens_servico')->where('id', $id)->first();
            
            if (!$os) {
                return response()->json([
                    'success' => false,
                    'message' => 'O.S. não encontrada'
                ], 404);
            }
            
            // Verificar se a coluna liberado existe
            if (!Schema::hasColumn('ordens_servico_itens', 'liberado')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Execute o SQL para adicionar as colunas necessárias'
                ], 400);
            }
            
            // Atualizar todos os itens da O.S. como liberados
            DB::table('ordens_servico_itens')
                ->where('ordem_servico_id', $id)
                ->update([
                    'liberado' => 1,
                    'liberado_por' => Auth::id(),
                    'liberado_em' => now(),
                    'retirado_por' => $retiradoPor
                ]);
            
            // Buscar dados para retorno
            $itens = DB::table('ordens_servico_itens as osi')
                ->select('osi.*', 'e.nome as produto_nome')
                ->leftJoin('estoque as e', 'osi.produto_id', '=', 'e.id')
                ->where('osi.ordem_servico_id', $id)
                ->get();
            
            // Registrar log
            \Log::info("Materiais liberados - O.S. #{$os->numero_os} - Retirado por: {$retiradoPor} - Liberado por: " . Auth::user()->name);
            
            return response()->json([
                'success' => true,
                'message' => 'Materiais liberados com sucesso!',
                'os' => $os,
                'itens' => $itens,
                'liberado_por' => Auth::user()->name,
                'retirado_por' => $retiradoPor,
                'data_liberacao' => now()->format('d/m/Y H:i')
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Erro ao liberar materiais da O.S. #{$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar liberação: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Buscar funcionários para o autocomplete
     */
    public function buscarFuncionarios(Request $request)
    {
        $nome = $request->get('nome', '');
        
        if (strlen($nome) < 2) {
            return response()->json([]);
        }
        
        $funcionarios = DB::table('users')
            ->select('id', 'name')
            ->where('name', 'like', '%' . $nome . '%')
            ->where('ativo', 1)
            ->orderBy('name')
            ->limit(10)
            ->get();
        
        return response()->json($funcionarios);
    }
}
