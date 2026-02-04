<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FreteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:frete');
    }

    /**
     * Página principal do controle de frete
     */
    public function index()
    {
        return view('frete.index');
    }

    /**
     * Listar fretes cadastrados
     */
    public function listar(Request $request)
    {
        try {
            $query = DB::table('fretes')
                ->leftJoin('ordens_servico', 'fretes.ordem_servico_id', '=', 'ordens_servico.id')
                ->leftJoin('users as solicitante', 'fretes.usuario_solicitante', '=', 'solicitante.id')
                ->select(
                    'fretes.*',
                    'ordens_servico.numero_os',
                    'ordens_servico.descricao as os_descricao',
                    'solicitante.name as solicitante_nome'
                )
                ->orderBy('fretes.created_at', 'desc');

            // Filtros
            if ($request->filled('data_inicio')) {
                $query->whereDate('fretes.data_solicitacao', '>=', $request->data_inicio);
            }
            if ($request->filled('data_fim')) {
                $query->whereDate('fretes.data_solicitacao', '<=', $request->data_fim);
            }
            if ($request->filled('numero_os')) {
                $query->where('ordens_servico.numero_os', 'like', '%' . $request->numero_os . '%');
            }
            if ($request->filled('status')) {
                $query->where('fretes.status', $request->status);
            }

            $fretes = $query->paginate(20);

            return response()->json([
                'success' => true,
                'fretes' => $fretes
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar fretes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar fretes'
            ], 500);
        }
    }

    /**
     * Gerar próximo número de frete
     */
    private function gerarNumeroFrete()
    {
        $hoje = date('Ymd');
        $prefixo = "FR-{$hoje}-";
        
        $ultimo = DB::table('fretes')
            ->where('numero_frete', 'like', $prefixo . '%')
            ->orderBy('numero_frete', 'desc')
            ->value('numero_frete');
        
        if ($ultimo) {
            $seq = (int) substr($ultimo, -4) + 1;
        } else {
            $seq = 1;
        }
        
        return $prefixo . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Salvar novo frete
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ordem_servico_id' => 'required|exists:ordens_servico,id',
                'descricao' => 'required|string|max:500',
                'origem' => 'nullable|string|max:255',
                'destino' => 'nullable|string|max:255',
                'observacoes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $numeroFrete = $this->gerarNumeroFrete();

            $id = DB::table('fretes')->insertGetId([
                'ordem_servico_id' => $request->ordem_servico_id,
                'numero_frete' => $numeroFrete,
                'descricao' => $request->descricao,
                'origem' => $request->origem,
                'destino' => $request->destino,
                'status' => 'aguardando_cotacao',
                'data_solicitacao' => now(),
                'usuario_solicitante' => Auth::id(),
                'observacoes' => $request->observacoes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Frete solicitado com sucesso',
                'id' => $id,
                'numero_frete' => $numeroFrete
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar frete: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar frete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar frete específico
     */
    public function show($id)
    {
        try {
            $frete = DB::table('fretes')
                ->leftJoin('ordens_servico', 'fretes.ordem_servico_id', '=', 'ordens_servico.id')
                ->leftJoin('users as solicitante', 'fretes.usuario_solicitante', '=', 'solicitante.id')
                ->leftJoin('users as cotador', 'fretes.usuario_cotacao', '=', 'cotador.id')
                ->leftJoin('users as aprovador', 'fretes.usuario_aprovacao', '=', 'aprovador.id')
                ->select(
                    'fretes.*', 
                    'ordens_servico.numero_os',
                    'ordens_servico.descricao as os_descricao',
                    'solicitante.name as solicitante_nome',
                    'cotador.name as cotador_nome',
                    'aprovador.name as aprovador_nome'
                )
                ->where('fretes.id', $id)
                ->first();

            if (!$frete) {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete não encontrado'
                ], 404);
            }

            // Buscar cotações de transportadoras
            $cotacoes = DB::table('fretes_cotacoes')
                ->where('frete_id', $id)
                ->orderBy('valor', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'frete' => $frete,
                'cotacoes' => $cotacoes
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar frete: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar frete'
            ], 500);
        }
    }

    /**
     * Atualizar frete
     */
    public function update(Request $request, $id)
    {
        try {
            $frete = DB::table('fretes')->where('id', $id)->first();
            
            if (!$frete) {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete não encontrado'
                ], 404);
            }

            // Só pode editar se ainda não foi pago
            if (in_array($frete->status, ['pago', 'liberado', 'entregue'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete já foi pago, não pode ser alterado'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'descricao' => 'required|string|max:500',
                'origem' => 'nullable|string|max:255',
                'destino' => 'nullable|string|max:255',
                'observacoes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            DB::table('fretes')
                ->where('id', $id)
                ->update([
                    'descricao' => $request->descricao,
                    'origem' => $request->origem,
                    'destino' => $request->destino,
                    'observacoes' => $request->observacoes,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Frete atualizado com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar frete: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar frete'
            ], 500);
        }
    }

    /**
     * Cancelar frete
     */
    public function cancelar(Request $request, $id)
    {
        try {
            $frete = DB::table('fretes')->where('id', $id)->first();
            
            if (!$frete) {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete não encontrado'
                ], 404);
            }

            // Só pode cancelar antes do pagamento
            if (in_array($frete->status, ['pago', 'liberado', 'entregue'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete já foi pago, não pode ser cancelado'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'motivo' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Informe o motivo do cancelamento'
                ], 422);
            }

            DB::table('fretes')
                ->where('id', $id)
                ->update([
                    'status' => 'cancelado',
                    'motivo_cancelamento' => $request->motivo,
                    'data_cancelamento' => now(),
                    'usuario_cancelamento' => Auth::id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Frete cancelado com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar frete: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar frete'
            ], 500);
        }
    }

    /**
     * Adicionar cotação de transportadora
     */
    public function adicionarCotacao(Request $request, $freteId)
    {
        try {
            $frete = DB::table('fretes')->where('id', $freteId)->first();
            
            if (!$frete) {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete não encontrado'
                ], 404);
            }

            if (!in_array($frete->status, ['aguardando_cotacao', 'em_cotacao'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete não está em fase de cotação'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'transportadora' => 'required|string|max:255',
                'valor' => 'required|numeric|min:0',
                'prazo_entrega' => 'nullable|string|max:100',
                'observacoes' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Inserir cotação
            DB::table('fretes_cotacoes')->insert([
                'frete_id' => $freteId,
                'transportadora' => $request->transportadora,
                'valor' => $request->valor,
                'prazo_entrega' => $request->prazo_entrega,
                'observacoes' => $request->observacoes,
                'selecionada' => false,
                'created_at' => now(),
            ]);

            // Atualizar status do frete para "em_cotacao" se ainda estava aguardando
            if ($frete->status === 'aguardando_cotacao') {
                DB::table('fretes')
                    ->where('id', $freteId)
                    ->update([
                        'status' => 'em_cotacao',
                        'usuario_cotacao' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cotação adicionada com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao adicionar cotação: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar cotação'
            ], 500);
        }
    }

    /**
     * Selecionar cotação vencedora
     */
    public function selecionarCotacao(Request $request, $freteId, $cotacaoId)
    {
        try {
            $frete = DB::table('fretes')->where('id', $freteId)->first();
            
            if (!$frete || !in_array($frete->status, ['em_cotacao'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete não está em fase de cotação'
                ], 403);
            }

            $cotacao = DB::table('fretes_cotacoes')
                ->where('id', $cotacaoId)
                ->where('frete_id', $freteId)
                ->first();

            if (!$cotacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotação não encontrada'
                ], 404);
            }

            // Desmarcar outras cotações
            DB::table('fretes_cotacoes')
                ->where('frete_id', $freteId)
                ->update(['selecionada' => false]);

            // Marcar esta como selecionada
            DB::table('fretes_cotacoes')
                ->where('id', $cotacaoId)
                ->update(['selecionada' => true]);

            // Atualizar frete
            DB::table('fretes')
                ->where('id', $freteId)
                ->update([
                    'status' => 'cotado',
                    'transportadora_selecionada' => $cotacao->transportadora,
                    'valor_cotado' => $cotacao->valor,
                    'data_cotacao' => now(),
                    'usuario_cotacao' => Auth::id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Cotação selecionada com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao selecionar cotação: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao selecionar cotação'
            ], 500);
        }
    }

    /**
     * Aprovar frete (gera conta a pagar)
     */
    public function aprovar(Request $request, $id)
    {
        try {
            // Verificar permissão de ordem de compra
            if (!Auth::user()->temPermissao('ordem_compra')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para aprovar fretes'
                ], 403);
            }

            $frete = DB::table('fretes')->where('id', $id)->first();
            
            if (!$frete || $frete->status !== 'cotado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete não está pronto para aprovação'
                ], 403);
            }

            DB::beginTransaction();

            // Criar conta a pagar
            $contaPagarId = DB::table('contas_pagar')->insertGetId([
                'descricao' => "FRETE: {$frete->numero_frete} - {$frete->descricao}",
                'valor' => $frete->valor_cotado,
                'data_vencimento' => now()->addDays(7),
                'status' => 'pendente',
                'categoria_id' => $this->getCategoriaFrete(),
                'fornecedor' => $frete->transportadora_selecionada,
                'observacoes' => "Frete #{$frete->numero_frete} vinculado à O.S.",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Atualizar frete
            DB::table('fretes')
                ->where('id', $id)
                ->update([
                    'status' => 'aguardando_pagamento',
                    'valor_aprovado' => $frete->valor_cotado,
                    'conta_pagar_id' => $contaPagarId,
                    'data_aprovacao' => now(),
                    'usuario_aprovacao' => Auth::id(),
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Frete aprovado! Conta a pagar gerada.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao aprovar frete: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aprovar frete'
            ], 500);
        }
    }

    /**
     * Buscar ou criar categoria "FRETE" em contas a pagar
     */
    private function getCategoriaFrete()
    {
        $categoria = DB::table('categorias_contas')
            ->where('nome', 'FRETE')
            ->first();

        if ($categoria) {
            return $categoria->id;
        }

        // Criar categoria se não existir
        return DB::table('categorias_contas')->insertGetId([
            'nome' => 'FRETE',
            'tipo' => 'despesa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Confirmar entrega do frete
     */
    public function confirmarEntrega(Request $request, $id)
    {
        try {
            $frete = DB::table('fretes')->where('id', $id)->first();
            
            if (!$frete || $frete->status !== 'liberado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Frete não está liberado para entrega'
                ], 403);
            }

            DB::table('fretes')
                ->where('id', $id)
                ->update([
                    'status' => 'entregue',
                    'data_entrega' => now(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Entrega confirmada com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao confirmar entrega: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar entrega'
            ], 500);
        }
    }

    /**
     * Buscar O.S. para autocomplete
     */
    public function buscarOS(Request $request)
    {
        try {
            $termo = $request->get('q', '');
            
            $ordens = DB::table('ordens_servico')
                ->where(function($query) use ($termo) {
                    $query->where('numero_os', 'like', "%{$termo}%")
                          ->orWhere('descricao', 'like', "%{$termo}%");
                })
                ->where('status', 'aberta')
                ->orderBy('data_os', 'desc')
                ->limit(20)
                ->get(['id', 'numero_os', 'data_os', 'descricao']);

            return response()->json([
                'success' => true,
                'ordens' => $ordens
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar O.S.: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar O.S.'
            ], 500);
        }
    }

    /**
     * Listar fretes de uma O.S. específica (para Gestão de O.S.)
     */
    public function listarPorOS($ordemServicoId)
    {
        try {
            $fretes = DB::table('fretes')
                ->where('ordem_servico_id', $ordemServicoId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'fretes' => $fretes
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar fretes da O.S.: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar fretes'
            ], 500);
        }
    }

    /**
     * Estatísticas para dashboard
     */
    public function estatisticas()
    {
        try {
            $stats = [
                'aguardando_cotacao' => DB::table('fretes')->where('status', 'aguardando_cotacao')->count(),
                'em_cotacao' => DB::table('fretes')->where('status', 'em_cotacao')->count(),
                'cotado' => DB::table('fretes')->where('status', 'cotado')->count(),
                'aguardando_pagamento' => DB::table('fretes')->where('status', 'aguardando_pagamento')->count(),
                'pago' => DB::table('fretes')->where('status', 'pago')->count(),
                'liberado' => DB::table('fretes')->where('status', 'liberado')->count(),
                'entregue' => DB::table('fretes')->where('status', 'entregue')->count(),
                'cancelado' => DB::table('fretes')->where('status', 'cancelado')->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas'
            ], 500);
        }
    }
}
