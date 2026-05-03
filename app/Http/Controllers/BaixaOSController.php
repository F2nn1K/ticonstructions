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
                'e.unidade as produto_unidade',
                'cc.nome as centro_custo_nome'
            )
            ->join('ordens_servico as os', 'osi.ordem_servico_id', '=', 'os.id')
            ->leftJoin('estoque as e', 'osi.produto_id', '=', 'e.id')
            ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id');
        
        // Join com tabela correta de funcionários
        if (Schema::hasTable('funcionarios')) {
            $query->leftJoin('funcionarios as f', 'os.funcionario_id', '=', 'f.id')
                  ->addSelect('f.nome as funcionario_nome');
        } else {
            $query->leftJoin('users as u', 'os.funcionario_id', '=', 'u.id')
                  ->addSelect('u.name as funcionario_nome');
        }
        
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
        
        // Agrupar por O.S. e consolidar itens com mesmo produto_id
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
            
            // Consolidar itens com mesmo produto_id dentro da mesma O.S.
            $produtoExistente = false;
            foreach ($ordensAgrupadas[$osId]['materiais'] as &$mat) {
                if ($mat['produto_id'] == $item->produto_id) {
                    $mat['quantidade'] += $item->quantidade;
                    $mat['item_ids'][] = $item->item_id;
                    $produtoExistente = true;
                    break;
                }
            }
            unset($mat);
            
            if (!$produtoExistente) {
                $ordensAgrupadas[$osId]['materiais'][] = [
                    'item_id' => $item->item_id,
                    'item_ids' => [$item->item_id],
                    'produto_id' => $item->produto_id,
                    'produto_nome' => $item->produto_nome,
                    'produto_unidade' => $item->produto_unidade ?? 'UN',
                    'quantidade' => $item->quantidade,
                    'liberado' => $hasMateriaisLiberados ? ($item->liberado ?? 0) : 0,
                    'liberado_por' => $hasMateriaisLiberados ? ($item->liberado_por ?? null) : null,
                    'liberado_em' => $hasMateriaisLiberados ? ($item->liberado_em ?? null) : null,
                    'retirado_por' => $hasMateriaisLiberados ? ($item->retirado_por ?? null) : null
                ];
            }
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
            
            // Buscar itens ANTES de liberar (para o log)
            $itensAntes = DB::table('ordens_servico_itens as osi')
                ->select('osi.*', 'e.nome as produto_nome')
                ->leftJoin('estoque as e', 'osi.produto_id', '=', 'e.id')
                ->where('osi.ordem_servico_id', $id)
                ->get();
            
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
            
            // Buscar centro de custo e solicitante
            $centroCusto = null;
            $solicitante = null;
            if (Schema::hasColumn('ordens_servico', 'centro_custo_id') && $os->centro_custo_id) {
                $cc = DB::table('centros_custo')->where('id', $os->centro_custo_id)->first();
                $centroCusto = $cc ? $cc->nome : null;
            }
            if ($os->funcionario_id && Schema::hasTable('funcionarios')) {
                $func = DB::table('funcionarios')->where('id', $os->funcionario_id)->first();
                $solicitante = $func ? $func->nome : null;
            }
            
            // Registrar log na tabela
            $this->registrarLog($os, 'liberacao', $itensAntes, $retiradoPor, $centroCusto, $solicitante);
            
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
     * Excluir baixa de materiais da O.S.
     * - Se já liberado: volta para pendente (exclusão lógica da liberação)
     * - Se pendente: remove os itens e devolve o estoque
     */
    public function excluir(Request $request, $id)
    {
        try {
            $motivo = $request->get('motivo', '');
            
            $os = DB::table('ordens_servico')->where('id', $id)->first();
            
            if (!$os) {
                return response()->json([
                    'success' => false,
                    'message' => 'O.S. não encontrada'
                ], 404);
            }
            
            // Buscar itens antes de excluir (para o log)
            $itens = DB::table('ordens_servico_itens as osi')
                ->select('osi.*', 'e.nome as produto_nome')
                ->leftJoin('estoque as e', 'osi.produto_id', '=', 'e.id')
                ->where('osi.ordem_servico_id', $id)
                ->get();
            
            if ($itens->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum material encontrado para esta O.S.'
                ], 404);
            }
            
            // Buscar centro de custo e solicitante
            $centroCusto = null;
            $solicitante = null;
            if (Schema::hasColumn('ordens_servico', 'centro_custo_id') && $os->centro_custo_id) {
                $cc = DB::table('centros_custo')->where('id', $os->centro_custo_id)->first();
                $centroCusto = $cc ? $cc->nome : null;
            }
            if ($os->funcionario_id && Schema::hasTable('funcionarios')) {
                $func = DB::table('funcionarios')->where('id', $os->funcionario_id)->first();
                $solicitante = $func ? $func->nome : null;
            }
            
            // Verificar se os itens já foram liberados
            $hasLiberado = Schema::hasColumn('ordens_servico_itens', 'liberado');
            $todosLiberados = $hasLiberado && $itens->every(fn($i) => $i->liberado == 1);
            
            // Registrar log ANTES de excluir
            $this->registrarLog($os, 'exclusao', $itens, null, $centroCusto, $solicitante, $motivo);
            
            if ($todosLiberados && $hasLiberado) {
                // Já liberado: volta para pendente (exclusão lógica)
                DB::table('ordens_servico_itens')
                    ->where('ordem_servico_id', $id)
                    ->update([
                        'liberado' => 0,
                        'liberado_por' => null,
                        'liberado_em' => null,
                        'retirado_por' => null
                    ]);
                
                $mensagem = 'Liberação excluída! Os materiais voltaram para pendente.';
            } else {
                // Pendente: devolver estoque e remover itens
                foreach ($itens as $item) {
                    DB::table('estoque')
                        ->where('id', $item->produto_id)
                        ->increment('quantidade', $item->quantidade);
                    
                    \Log::info("Estoque devolvido - Produto: " . ($item->produto_nome ?? $item->produto_id) . 
                        " - Qtd: {$item->quantidade} - O.S.: {$os->numero_os} - Motivo: {$motivo}");
                }
                
                // Remover todos os itens da O.S.
                DB::table('ordens_servico_itens')
                    ->where('ordem_servico_id', $id)
                    ->delete();
                
                $mensagem = 'Baixa excluída! Os materiais foram devolvidos ao estoque.';
            }
            
            \Log::info("Baixa excluída - O.S. #{$os->numero_os} - Tipo: " . ($todosLiberados ? 'liberação' : 'pendente') . 
                " - Motivo: {$motivo} - Excluído por: " . Auth::user()->name);
            
            return response()->json([
                'success' => true,
                'message' => $mensagem
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Erro ao excluir baixa da O.S. #{$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Listar logs de liberação e exclusão
     */
    public function listarLogs(Request $request)
    {
        if (!Schema::hasTable('logs_baixa_os')) {
            return response()->json(['logs' => [], 'total' => 0]);
        }
        
        $query = DB::table('logs_baixa_os')
            ->orderBy('created_at', 'desc');
        
        if ($request->filled('numero_os')) {
            $query->where('numero_os', 'like', '%' . $request->numero_os . '%');
        }
        
        if ($request->filled('acao')) {
            $query->where('acao', $request->acao);
        }
        
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }
        
        $total = $query->count();
        $logs = $query->limit(200)->get();
        
        // Decodificar JSON dos materiais
        foreach ($logs as $log) {
            $log->materiais = json_decode($log->materiais_json, true) ?? [];
        }
        
        return response()->json([
            'logs' => $logs,
            'total' => $total
        ]);
    }
    
    /**
     * Registrar log de ação na Baixa da O.S.
     */
    private function registrarLog($os, $acao, $itens, $retiradoPor = null, $centroCusto = null, $solicitante = null, $motivo = null)
    {
        try {
            if (!Schema::hasTable('logs_baixa_os')) {
                return;
            }
            
            $materiaisArray = $itens->map(function ($item) {
                return [
                    'produto_nome' => $item->produto_nome ?? 'N/A',
                    'quantidade' => $item->quantidade,
                    'liberado' => $item->liberado ?? 0,
                    'retirado_por' => $item->retirado_por ?? null,
                    'liberado_em' => $item->liberado_em ?? null,
                ];
            })->toArray();
            
            DB::table('logs_baixa_os')->insert([
                'ordem_servico_id' => $os->id,
                'numero_os' => $os->numero_os,
                'acao' => $acao,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'retirado_por' => $retiradoPor,
                'materiais_json' => json_encode($materiaisArray, JSON_UNESCAPED_UNICODE),
                'centro_custo' => $centroCusto,
                'solicitante' => $solicitante,
                'motivo' => $motivo,
                'ip' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('Erro ao registrar log de baixa O.S.: ' . $e->getMessage());
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
        
        $query = DB::table('users')
            ->select('id', 'name')
            ->where('name', 'like', '%' . $nome . '%')
            ->orderBy('name')
            ->limit(10);
        
        if (Schema::hasColumn('users', 'active')) {
            $query->where('active', 1);
        }
        
        $funcionarios = $query->get();
        
        return response()->json($funcionarios);
    }
}
