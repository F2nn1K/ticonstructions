<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PedidoComprasController extends Controller
{
    /**
     * Exibe a página de solicitação de pedidos de compras
     *
     * @return \Illuminate\View\View
     */
    public function solicitacao()
    {
        return view('pedidos.solicitacao');
    }

    /**
     * Exibe a página de autorização de pedidos de compras
     *
     * @return \Illuminate\View\View
     */
    public function autorizacao()
    {
        return view('pedidos.autorizacao_home');
    }

    /**
     * View: autorizações pendentes
     */
    public function autorizacoesPendentesView()
    {
        return view('pedidos.autorizacao_pendentes');
    }

    /**
     * View: autorizações aprovadas
     */
    public function autorizacoesAprovadasView()
    {
        return view('pedidos.autorizacao_aprovadas');
    }

    /**
     * View: autorizações rejeitadas
     */
    public function autorizacoesRejeitadasView()
    {
        return view('pedidos.autorizacao_rejeitadas');
    }

    /** API: retorna perfil e permissões do usuário autenticado (para gating no front) */
    public function usuarioPermissoes()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }
        // Tenta coletar informações de perfil/permissões já usadas no sistema
        $perfil = method_exists($user, 'profile') && $user->profile ? ($user->profile->name ?? null) : null;
        $permissoes = [];
        try {
            if (method_exists($user, 'getPermissoes')) {
                $permissoes = (array) $user->getPermissoes();
            } elseif (property_exists($user, 'profile_permissions') && is_array($user->profile_permissions)) {
                $permissoes = $user->profile_permissions;
            }
        } catch (\Throwable $e) { /* noop */ }

        return response()->json([
            'success' => true,
            'perfil' => $perfil,
            'is_admin' => ($perfil === 'Admin'),
            'permissoes' => array_values($permissoes),
        ]);
    }

    /** View: histórico e interações do próprio usuário */
    public function minhasInteracoesView()
    {
        return view('pedidos.minhas_interacoes');
    }

    /** View: gestão de produtos (estoque_pedido) */
    public function produtosView()
    {
        return view('pedidos.produtos');
    }

    /** View: Bloquear Itens (somente para perfis com permissão 'bloq_ite') */
    public function bloquearItensView()
    {
        return view('pedidos.bloquear_itens');
    }

    /** Busca usuários para bloqueio (autocomplete) */
    public function bloqBuscarUsuarios(Request $request)
    {
        $q = trim((string)$request->get('q'));
        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $ql = mb_strtolower($q, 'UTF-8');
        $usuarios = \DB::table('users as u')
            ->where(function($w) use ($ql){
                $w->whereRaw('LOWER(u.name) LIKE ?', ['%'.$ql.'%'])
                  ->orWhereRaw('LOWER(COALESCE(u.email, "")) LIKE ?', ['%'.$ql.'%']);
            })
            ->orderBy('u.name')
            ->limit(20)
            ->get(['u.id','u.name','u.email']);
        return response()->json(['success' => true, 'data' => $usuarios]);
    }

    /** Busca produtos ativos para bloqueio (autocomplete) */
    public function bloqBuscarProdutos(Request $request)
    {
        $q = trim((string)$request->get('q'));
        if ($q === '' || (mb_strlen($q) < 2 && !ctype_digit($q))) {
            return response()->json(['success' => true, 'data' => []]);
        }
        $ql = mb_strtolower($q, 'UTF-8');
        $query = \DB::table('estoque_pedido as ep')
            ->where('ep.ativo', 1)
            ->limit(20)
            ->orderBy('ep.produto');
        $query->where(function($w) use ($ql, $q){
            $w->whereRaw('LOWER(ep.produto) LIKE ?', ['%'.$ql.'%'])
              ->orWhereRaw('LOWER(COALESCE(ep.descricao, "")) LIKE ?', ['%'.$ql.'%']);
            if ($q !== '' && ctype_digit($q)) {
                $w->orWhere('ep.codigo', 'like', '%'.$q.'%');
            }
        });
        $rows = $query->get(['ep.id','ep.codigo','ep.produto','ep.descricao']);
        return response()->json(['success' => true, 'data' => $rows]);
    }

    /** Lista bloqueios por usuário */
    public function bloqListarPorUsuario($userId)
    {
        $user = \DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuário não encontrado'], 404);
        }
        $rows = \DB::table('restricoes_produto_usuario as r')
            ->join('estoque_pedido as ep', 'ep.id', '=', 'r.produto_id')
            ->where('r.user_id', $userId)
            ->where('r.bloqueado', 1)
            ->orderBy('ep.produto')
            ->get([
                'r.user_id','r.produto_id','ep.codigo','ep.produto','ep.descricao'
            ]);
        return response()->json(['success' => true, 'data' => $rows]);
    }

    /** Adiciona/ativa bloqueio */
    public function bloqAdicionar(Request $request)
    {
        $dados = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'produto_id' => 'required|integer|exists:estoque_pedido,id',
        ]);

        \DB::table('restricoes_produto_usuario')->updateOrInsert(
            ['user_id' => $dados['user_id'], 'produto_id' => $dados['produto_id']],
            ['bloqueado' => 1, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    /** Remove bloqueio */
    public function bloqRemover($userId, $produtoId)
    {
        \DB::table('restricoes_produto_usuario')
            ->where('user_id', $userId)
            ->where('produto_id', $produtoId)
            ->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Armazena uma nova solicitação de pedido de compras
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $centroCustoId = $request->centro_custo_id;
            $rotaId = $request->rota_id;
            $roteirizacaoId = $request->roteirizacao_id;
            $prioridade = $request->prioridade;
            $observacao = $request->observacao;
            $produtos = $request->produtos;
            $usuarioId = auth()->id();
            
            // Gera um número de pedido único para este envio (mesmo número para todos os itens do envio)
            $numPedido = 'PED-' . now()->format('Ymd-His') . '-' . str_pad((string) $usuarioId, 3, '0', STR_PAD_LEFT);

            // Validações básicas
            if (!$centroCustoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Centro de custo é obrigatório'
                ], 400);
            }

            if (!$produtos || count($produtos) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adicione pelo menos um produto'
                ], 400);
            }

            // Prioridade permitida (sem "urgente")
            if (!in_array($prioridade, ['baixa', 'media', 'alta'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prioridade inválida'
                ], 422);
            }

            // Verificar bloqueios por usuário antes de salvar
            $bloqueados = [];
            foreach ($produtos as $produto) {
                $produtoNomeCheck = strip_tags(trim($produto['nome'] ?? ''));
                if ($produtoNomeCheck === '') { continue; }
                $ep = \DB::table('estoque_pedido')
                    ->select('id')
                    ->whereRaw('TRIM(UPPER(produto)) = ?', [mb_strtoupper(trim($produtoNomeCheck))])
                    ->first();
                if ($ep) {
                    $isBlocked = \DB::table('restricoes_produto_usuario')
                        ->where('user_id', $usuarioId)
                        ->where('produto_id', $ep->id)
                        ->where('bloqueado', 1)
                        ->exists();
                    if ($isBlocked) { $bloqueados[] = $produtoNomeCheck; }
                }
            }

            if (!empty($bloqueados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alguns produtos estão bloqueados para o seu usuário.',
                    'bloqueados' => $bloqueados,
                ], 422);
            }

            // Salvar cada produto como uma linha na tabela solicitacao (mesmo num_pedido)
            foreach ($produtos as $produto) {
                // Sanitização básica para prevenir SQL injection
                $produtoNome = strip_tags(trim($produto['nome'] ?? ''));
                $quantidade = (int) ($produto['quantidade'] ?? 0);
                
                // Pula produtos inválidos
                if (empty($produtoNome) || $quantidade <= 0) {
                    continue;
                }
                // (defesa adicional) impedir salvar se bloqueado
                $epRow = \DB::table('estoque_pedido')->select('id')->whereRaw('TRIM(UPPER(produto)) = ?', [mb_strtoupper(trim($produtoNome))])->first();
                if ($epRow) {
                    $blocked = \DB::table('restricoes_produto_usuario')
                        ->where('user_id', $usuarioId)
                        ->where('produto_id', $epRow->id)
                        ->where('bloqueado', 1)
                        ->exists();
                    if ($blocked) { continue; }
                }
                // Buscar valor unitário do produto (estoque_pedido)
                $valorUnitario = \DB::table('estoque_pedido')
                    ->whereRaw('TRIM(UPPER(produto)) = ?', [mb_strtoupper(trim($produtoNome))])
                    ->value('valor_unitario');
                $valorUnitarioNum = is_null($valorUnitario) ? 0 : (float)$valorUnitario;
                $valorTotal = $quantidade * $valorUnitarioNum;

                \DB::table('solicitacao')->insert([
                    'num_pedido' => $numPedido,
                    'usuario_id' => $usuarioId,
                    'centro_custo_id' => $centroCustoId,
                    'rota_id' => $rotaId,
                    'roteirizacao_id' => $roteirizacaoId,
                    'produto_nome' => $produtoNome,
                    'quantidade' => $quantidade,
                    // valor total do item (qtd * valor_unitario)
                    'valor' => $valorTotal,
                    'prioridade' => $prioridade,
                    'observacao' => strip_tags(trim($observacao ?? '')),
                    'data_solicitacao' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitação enviada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar solicitação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprova um pedido de compras
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function aprovar(Request $request, $id)
    {
        try {
            // Atualizar status, data e aprovador na solicitação individual
            \DB::table('solicitacao')
                ->where('id', $id)
                ->update([
                    'aprovacao' => 'aprovado',
                    'data_aprovacao' => now(),
                    'id_aprovador' => auth()->id(),
                ]);

            \DB::table('interacao')->insert([
                'solicitacao_id' => $id,
                'usuario_id' => auth()->id(),
                'tipo' => 'aprovacao',
                'mensagem' => $request->input('observacoes') ?? null,
                'dados_extras' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido aprovado com sucesso!'
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aprovar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeita um pedido de compras
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejeitar(Request $request, $id)
    {
        try {
            // Atualizar status, data e aprovador na solicitação individual
            \DB::table('solicitacao')
                ->where('id', $id)
                ->update([
                    'aprovacao' => 'rejeitado',
                    'data_aprovacao' => now(),
                    'id_aprovador' => auth()->id(),
                ]);

            \DB::table('interacao')->insert([
                'solicitacao_id' => $id,
                'usuario_id' => auth()->id(),
                'tipo' => 'rejeicao',
                'mensagem' => $request->input('observacoes') ?? null,
                'dados_extras' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido rejeitado com sucesso!'
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao rejeitar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /** Exclui um item específico (somente Admin) */
    public function excluirItem(\Illuminate\Http\Request $request, int $id)
    {
        // Verifica Admin (perfil)
        $user = auth()->user();
        if (!$user || !($user->profile && in_array($user->profile->name, ['Admin','Administrador'], true))) {
            return response()->json(['success' => false, 'message' => 'Apenas administradores podem excluir itens.'], 403);
        }

        try {
            $item = \DB::table('solicitacao')->where('id', $id)->first();
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item não encontrado'], 404);
            }
            // Permite excluir apenas itens ainda pendentes (ou null)
            if (!is_null($item->aprovacao) && $item->aprovacao !== 'pendente') {
                return response()->json(['success' => false, 'message' => 'Itens já decididos não podem ser excluídos.'], 422);
            }

            \DB::beginTransaction();

            // Log opcional, se a tabela existir
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('logs_solicitacao')) {
                    \DB::table('logs_solicitacao')->insert([
                        'num_pedido' => $item->num_pedido ?? null,
                        'solicitacao_id' => (int)$item->id,
                        'usuario_id' => (int)auth()->id(),
                        'acao' => 'delete_item',
                        'detalhes' => json_encode(['antes' => $item], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                        'ip' => $request->ip(),
                        'user_agent' => substr((string)$request->userAgent(), 0, 500),
                        'created_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) { /* ignore log errors */ }

            \DB::table('solicitacao')->where('id', $id)->delete();

            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Item excluído com sucesso']);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Falha ao excluir: '.$e->getMessage()], 500);
        }
    }

    /**
     * Lista as solicitações do usuário logado
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function minhasSolicitacoes()
    {
        $usuarioId = auth()->id();

        // Última interação (aprovacao/rejeicao) por solicitacao
        $ultimasInteracoes = \DB::table('interacao')
            ->select('solicitacao_id', \DB::raw('MAX(created_at) as max_created'))
            ->whereIn('tipo', ['aprovacao', 'rejeicao'])
            ->groupBy('solicitacao_id');

        $statusPorInteracao = \DB::table('interacao as i')
            ->joinSub($ultimasInteracoes, 'ui', function ($join) {
                $join->on('i.solicitacao_id', '=', 'ui.solicitacao_id')
                     ->on('i.created_at', '=', 'ui.max_created');
            })
            ->select('i.solicitacao_id', 'i.tipo');

        $registros = \DB::table('solicitacao as s')
            ->leftJoinSub($statusPorInteracao, 'st', function ($join) {
                $join->on('s.id', '=', 'st.solicitacao_id');
            })
            ->where('s.usuario_id', $usuarioId)
            ->orderByDesc('s.data_solicitacao')
            ->get([
                's.*',
                \DB::raw("COALESCE(st.tipo, 'pendente') as status")
            ]);

        return response()->json([
            'success' => true,
            'data' => $registros
        ]);
    }

    /** Dados para a página de interações: pedidos apenas do usuário logado, agrupados por num_pedido */
    public function minhasInteracoesData()
    {
        $usuarioId = auth()->id();

        $pedidos = \DB::table('solicitacao as s')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->leftJoin('rotas as r', 'r.id', '=', 's.rota_id')
            ->leftJoin('roteirizacao as rt', 'rt.id', '=', 's.roteirizacao_id')
            ->where('s.usuario_id', $usuarioId)
            ->where('s.aprovacao', '=', 'pendente')
            ->orderByDesc('s.data_solicitacao')
            ->get([
                's.id', 's.num_pedido', 's.produto_nome', 's.quantidade', 's.prioridade',
                's.aprovacao', 's.observacao', 's.data_solicitacao', 's.data_aprovacao',
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw("COALESCE(r.nome_rota,'—') as rota_nome"),
                \DB::raw("COALESCE(rt.nome,'—') as roteirizacao_nome")
            ]);

        // Agrupar por num_pedido
        $agrupado = $pedidos->groupBy('num_pedido')->map(function($items){
            return [
                'num_pedido' => $items->first()->num_pedido,
                'data_solicitacao' => $items->first()->data_solicitacao,
                'centro_custo_nome' => $items->first()->centro_custo_nome,
                'rota_nome' => $items->first()->rota_nome,
                'roteirizacao_nome' => $items->first()->roteirizacao_nome,
                'prioridade' => $items->first()->prioridade,
                'aprovacao' => 'pendente',
                'itens' => $items->map(function($i){ return [
                    'id' => $i->id,
                    'produto_nome' => $i->produto_nome,
                    'quantidade' => $i->quantidade,
                ]; })->values(),
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $agrupado]);
    }

    /** Interações (aprovação/rejeição/mensagens) por item de solicitação */
    public function interacoesPorPedido($id)
    {
        $usuarioId = auth()->id();
        // Garantir que o pedido pertence ao usuário
        $pertence = \DB::table('solicitacao')->where('id', $id)->where('usuario_id', $usuarioId)->exists();
        if (!$pertence) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        $interacoes = \DB::table('interacao as i')
            ->leftJoin('users as u', 'u.id', '=', 'i.usuario_id')
            ->where('i.solicitacao_id', $id)
            ->orderByDesc('i.created_at')
            ->get([
                'i.id', 'i.tipo', 'i.mensagem', 'i.created_at', \DB::raw("COALESCE(u.name,'—') as usuario")
            ]);

        return response()->json(['success' => true, 'data' => $interacoes]);
    }

    /** Registrar uma mensagem do solicitante no pedido (apenas pendente) */
    public function enviarInteracaoSolicitante(Request $request, $id)
    {
        $request->validate([
            'mensagem' => 'required|string|min:2|max:2000',
        ]);

        $usuarioId = auth()->id();
        $pedido = \DB::table('solicitacao')->where('id', $id)->first();
        if (!$pedido || (int)$pedido->usuario_id !== (int)$usuarioId) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }
        if ($pedido->aprovacao !== 'pendente') {
            return response()->json(['success' => false, 'message' => 'Pedido não está mais pendente'], 400);
        }

        \DB::table('interacao')->insert([
            'solicitacao_id' => $id,
            'usuario_id' => $usuarioId,
            'tipo' => 'comentario',
            'mensagem' => $request->input('mensagem'),
            'dados_extras' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Mensagem enviada']);
    }

    /**
     * Lista pedidos pendentes de autorização
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pedidosPendentes()
    {
        $pendentes = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.aprovacao', '=', 'pendente')
            ->orderByDesc('s.data_solicitacao')
            ->get([
                's.*',
                \DB::raw("COALESCE(u.name, '—') as solicitante"),
                \DB::raw("COALESCE(cc.nome, '—') as centro_custo_nome"),
            ]);

        return response()->json([
            'success' => true,
            'data' => $pendentes
        ]);
    }

    /**
     * Lista pendentes agrupados por envio (mesmos dados e mesma data de envio)
     */
    public function pedidosPendentesAgrupados(Request $request)
    {
        // Agrupar estritamente por num_pedido e agregar os demais campos
        $grupos = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where(function($q){ $q->where('s.aprovacao', '=', 'pendente')->orWhereNull('s.aprovacao'); })
            // Filtro opcional por período (mês atual no dashboard)
            ->when($request->filled('data_ini'), function($q) use ($request){
                $q->whereDate('s.data_solicitacao', '>=', $request->input('data_ini'));
            })
            ->when($request->filled('data_fim'), function($q) use ($request){
                $q->whereDate('s.data_solicitacao', '<=', $request->input('data_fim'));
            })
            ->groupBy('s.num_pedido')
            ->orderByDesc(\DB::raw('MIN(s.data_solicitacao)'))
            ->get([
                \DB::raw('s.num_pedido as grupo_hash'),
                \DB::raw('s.num_pedido as num_pedido'),
                \DB::raw("DATE_FORMAT(MIN(s.data_solicitacao),'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                \DB::raw('MIN(s.usuario_id) as usuario_id'),
                \DB::raw('MIN(s.centro_custo_id) as centro_custo_id'),
                \DB::raw('MIN(s.prioridade) as prioridade'),
                \DB::raw("MIN(COALESCE(s.observacao,'')) as observacao"),
                \DB::raw("MIN(COALESCE(u.name,'—')) as solicitante"),
                \DB::raw("MIN(COALESCE(cc.nome,'—')) as centro_custo_nome"),
                \DB::raw('COUNT(*) as itens'),
                \DB::raw('SUM(s.quantidade) as quantidade_total'),
            ]);

        return response()->json(['success' => true, 'data' => $grupos]);
    }

    /**
     * Detalhes de um grupo pendente (cabeçalho + itens)
     * O $hash agora é na verdade o num_pedido
     */
    public function detalhesPedidoAgrupado(string $numPedido)
    {
        // Sanitização do número do pedido
        $numPedido = trim($numPedido);
        if (empty($numPedido)) {
            return response()->json(['success' => false, 'message' => 'Número de pedido inválido'], 400);
        }

        $cabecalho = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->leftJoin('rotas as r', 'r.id', '=', 's.rota_id')
            ->leftJoin('roteirizacao as rt', 'rt.id', '=', 's.roteirizacao_id')
            ->where('s.num_pedido', '=', $numPedido)
            ->where(function($q){ $q->where('s.aprovacao', '=', 'pendente')->orWhereNull('s.aprovacao'); })
            ->orderByDesc('s.data_solicitacao')
            ->first([
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.rota_id',
                's.roteirizacao_id',
                's.num_pedido',
                's.prioridade',
                's.observacao',
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw("COALESCE(r.nome_rota,'—') as rota_nome"),
                \DB::raw("COALESCE(rt.nome,'—') as roteirizacao_nome"),
            ]);

        if (!$cabecalho) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        $itens = \DB::table('solicitacao as s')
            ->where('s.num_pedido', '=', $numPedido)
            ->where(function($q){ $q->where('s.aprovacao', '=', 'pendente')->orWhereNull('s.aprovacao'); })
            ->orderBy('s.id')
            ->get([
                's.id',
                's.produto_nome',
                's.quantidade',
                // valor total salvo e valor unitário derivado
                's.valor',
                \DB::raw('(CASE WHEN s.quantidade > 0 THEN s.valor / s.quantidade ELSE 0 END) as valor_unitario'),
            ]);

        // Interações de todos os itens do grupo
        $idsGrupo = $itens->pluck('id')->all();
        $interacoes = [];
        if (!empty($idsGrupo)) {
            $interacoes = \DB::table('interacao as i')
                ->leftJoin('users as u', 'u.id', '=', 'i.usuario_id')
                ->whereIn('i.solicitacao_id', $idsGrupo)
                ->orderByDesc('i.created_at')
                ->get([
                    'i.id', 'i.solicitacao_id', 'i.tipo', 'i.mensagem', 'i.created_at',
                    \DB::raw("COALESCE(u.name,'—') as usuario")
                ]);
        }

        return response()->json(['success' => true, 'data' => [
            'cabecalho' => $cabecalho,
            'itens' => $itens,
            'interacoes' => $interacoes,
        ]]);
    }

    /** Atualiza itens de um grupo (somente Admin): quantidade e produto_nome; recalc valor */
    public function atualizarItensGrupo(Request $request, string $numPedido)
    {
        try {
            // Verifica Admin (perfil)
            $user = auth()->user();
            if (!$user || !($user->profile && in_array($user->profile->name, ['Admin','Administrador'], true))) {
                return response()->json(['success' => false, 'message' => 'Apenas administradores podem alterar itens.'], 403);
            }

            $dados = $request->validate([
                'itens' => 'required|array|min:1',
                'itens.*.id' => 'required|integer|exists:solicitacao,id',
                'itens.*.produto_nome' => 'required|string',
                'itens.*.quantidade' => 'required|integer|min:1',
            ]);

            $ids = collect($dados['itens'])->pluck('id')->all();
            // Garante que todos pertencem ao mesmo num_pedido
            $num = \DB::table('solicitacao')->whereIn('id', $ids)->pluck('num_pedido')->unique();
            if ($num->count() !== 1 || $num->first() !== $numPedido) {
                return response()->json(['success' => false, 'message' => 'Itens inválidos para este pedido.'], 422);
            }

            \DB::beginTransaction();
            foreach ($dados['itens'] as $it) {
                $produtoNome = trim($it['produto_nome']);
                $qtd = (int) $it['quantidade'];
                // estado anterior para log
                $antes = \DB::table('solicitacao')->where('id', $it['id'])->first();
                // buscar valor_unitario do estoque
                $valorUnit = (float) (\DB::table('estoque_pedido')
                    ->whereRaw('TRIM(UPPER(produto)) = ?', [mb_strtoupper($produtoNome)])
                    ->value('valor_unitario') ?? 0);
                $valorTotal = $qtd * $valorUnit;

                $fields = [
                    'produto_nome' => $produtoNome,
                    'quantidade' => $qtd,
                    'valor' => $valorTotal,
                ];
                // Se a coluna updated_at existir neste ambiente, atualiza; caso contrário, ignora
                try {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('solicitacao', 'updated_at')) {
                        $fields['updated_at'] = now();
                    }
                } catch (\Throwable $e) { /* ignore schema errors */ }

                \DB::table('solicitacao')->where('id', $it['id'])->update($fields);

                // Registrar log, se a tabela existir
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('logs_solicitacao')) {
                        $depois = [
                            'produto_nome' => $produtoNome,
                            'quantidade' => $qtd,
                            'valor' => $valorTotal,
                        ];
                        $mudancas = [];
                        if ($antes) {
                            if ((string)($antes->produto_nome ?? '') !== (string)$produtoNome) {
                                $mudancas['produto_nome'] = [
                                    'antes' => $antes->produto_nome ?? null,
                                    'depois' => $produtoNome,
                                ];
                            }
                            if ((int)($antes->quantidade ?? 0) !== (int)$qtd) {
                                $mudancas['quantidade'] = [
                                    'antes' => (int)($antes->quantidade ?? 0),
                                    'depois' => (int)$qtd,
                                ];
                            }
                            // valor pode ser null no banco
                            $valorAntes = (float)($antes->valor ?? 0);
                            if ((float)$valorAntes !== (float)$valorTotal) {
                                $mudancas['valor'] = [
                                    'antes' => (float)$valorAntes,
                                    'depois' => (float)$valorTotal,
                                ];
                            }
                        } else {
                            $mudancas['novo'] = $depois;
                        }

                        \DB::table('logs_solicitacao')->insert([
                            'num_pedido' => $numPedido,
                            'solicitacao_id' => (int)$it['id'],
                            'usuario_id' => (int)auth()->id(),
                            'acao' => 'update_item',
                            'detalhes' => json_encode([
                                'antes' => $antes ? [
                                    'produto_nome' => $antes->produto_nome ?? null,
                                    'quantidade' => isset($antes->quantidade)?(int)$antes->quantidade:null,
                                    'valor' => isset($antes->valor)?(float)$antes->valor:null,
                                ] : null,
                                'depois' => $depois,
                                'mudancas' => $mudancas,
                            ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                            'ip' => request()->ip(),
                            'user_agent' => substr((string)request()->userAgent(), 0, 500),
                            'created_at' => now(),
                        ]);
                    }
                } catch (\Throwable $e) { /* se log falhar, não interromper a atualização */ }
            }
            \DB::commit();

            return response()->json(['success' => true, 'message' => 'Itens atualizados com sucesso.']);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Falha ao salvar: '.$e->getMessage()], 500);
        }
    }

    /** Adiciona um novo item ao grupo (somente Admin) */
    public function adicionarItemGrupo(Request $request, string $numPedido)
    {
        // Verifica Admin (perfil)
        $user = auth()->user();
        if (!$user || !($user->profile && in_array($user->profile->name, ['Admin','Administrador'], true))) {
            return response()->json(['success' => false, 'message' => 'Apenas administradores podem adicionar itens.'], 403);
        }

        $dados = $request->validate([
            'produto_nome' => 'required|string|min:2|max:255',
            'quantidade' => 'required|integer|min:1',
        ]);

        $numPedido = trim($numPedido);
        if ($numPedido === '') {
            return response()->json(['success' => false, 'message' => 'Número de pedido inválido.'], 422);
        }

        // Buscar cabeçalho base do grupo
        $base = \DB::table('solicitacao as s')
            ->where('s.num_pedido', '=', $numPedido)
            ->where(function($q){ $q->where('s.aprovacao', '=', 'pendente')->orWhereNull('s.aprovacao'); })
            ->first(['s.usuario_id','s.centro_custo_id','s.rota_id','s.roteirizacao_id','s.prioridade','s.observacao']);
        if (!$base) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado ou não está pendente.'], 404);
        }

        $produtoNome = trim($dados['produto_nome']);
        $qtd = (int)$dados['quantidade'];

        // Buscar preço unitário
        $valorUnit = (float) (\DB::table('estoque_pedido')
            ->whereRaw('TRIM(UPPER(produto)) = ?', [mb_strtoupper($produtoNome)])
            ->value('valor_unitario') ?? 0);
        $valorTotal = $qtd * $valorUnit;

        try {
            \DB::beginTransaction();
            $id = \DB::table('solicitacao')->insertGetId([
                'num_pedido' => $numPedido,
                'usuario_id' => (int)$base->usuario_id,
                'centro_custo_id' => $base->centro_custo_id,
                'rota_id' => $base->rota_id,
                'roteirizacao_id' => $base->roteirizacao_id,
                'produto_nome' => $produtoNome,
                'quantidade' => $qtd,
                'valor' => $valorTotal,
                'prioridade' => $base->prioridade,
                'observacao' => $base->observacao,
                'data_solicitacao' => now(),
                'aprovacao' => 'pendente',
            ]);

            // Log opcional
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('logs_solicitacao')) {
                    \DB::table('logs_solicitacao')->insert([
                        'num_pedido' => $numPedido,
                        'solicitacao_id' => (int)$id,
                        'usuario_id' => (int)auth()->id(),
                        'acao' => 'create_item',
                        'detalhes' => json_encode([
                            'novo' => [
                                'produto_nome' => $produtoNome,
                                'quantidade' => $qtd,
                                'valor' => $valorTotal,
                            ]
                        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                        'ip' => $request->ip(),
                        'user_agent' => substr((string)$request->userAgent(), 0, 500),
                        'created_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) { /* ignore log errors */ }

            \DB::commit();
            return response()->json(['success' => true, 'data' => [
                'id' => (int)$id,
                'valor_unitario' => $valorUnit,
                'valor' => $valorTotal,
            ]]);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Falha ao adicionar item: '.$e->getMessage()], 500);
        }
    }

    /** Retorna preço unitário de um produto por nome */
    public function precoProduto(Request $request)
    {
        $nome = trim((string)$request->get('nome'));
        if ($nome === '') {
            return response()->json(['success' => true, 'valor_unitario' => null]);
        }
        $vu = \DB::table('estoque_pedido')
            ->whereRaw('TRIM(UPPER(produto)) = ?', [mb_strtoupper($nome)])
            ->value('valor_unitario');
        return response()->json(['success' => true, 'valor_unitario' => $vu]);
    }

    /** Autocomplete: busca produtos por nome (>=3 chars) ou código */
    public function buscarProdutosEstoque(Request $request)
    {
        $q = trim((string) $request->get('q'));
        if (mb_strlen($q) < 3 && !ctype_digit($q)) {
            return response()->json(['success' => true, 'data' => []]);
        }
        $ql = mb_strtolower($q, 'UTF-8');
        $query = \DB::table('estoque_pedido as ep')
            ->select('ep.id','ep.codigo','ep.produto','ep.descricao','ep.valor_unitario')
            ->where('ep.ativo', 1)
            ->limit(20)
            ->orderBy('ep.produto');
        $query->where(function($w) use ($ql, $q){
            $w->whereRaw('LOWER(ep.produto) LIKE ?', ['%'.$ql.'%'])
              ->orWhereRaw('LOWER(COALESCE(ep.descricao, "")) LIKE ?', ['%'.$ql.'%']);
            if ($q !== '' && ctype_digit($q)) {
                $w->orWhere('ep.codigo', 'like', '%'.$q.'%');
            }
        });
        $res = $query->get();
        return response()->json(['success' => true, 'data' => $res]);
    }

    /** Aprova todas as solicitações do grupo */
    public function aprovarGrupo(Request $request, string $numPedido)
    {
        // Sanitização do número do pedido
        $numPedido = trim($numPedido);
        if (empty($numPedido)) {
            return response()->json(['success' => false, 'message' => 'Número de pedido inválido'], 400);
        }

        $ids = \DB::table('solicitacao as s')
            ->where('s.num_pedido', '=', $numPedido)
            ->where(function($q){ $q->where('s.aprovacao', '=', 'pendente')->orWhereNull('s.aprovacao'); })
            ->pluck('s.id');

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        $mensagem = $request->input('mensagem');
        $agora = now();

        $inserts = $ids->map(function ($id) use ($mensagem, $agora) {
            return [
                'solicitacao_id' => $id,
                'usuario_id' => auth()->id(),
                'tipo' => 'aprovacao',
                'mensagem' => $mensagem,
                'dados_extras' => null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        })->all();

        // Atualiza status, data e aprovador do grupo
        \DB::table('solicitacao')->whereIn('id', $ids)->update([
            'aprovacao' => 'aprovado',
            'data_aprovacao' => $agora,
            'id_aprovador' => auth()->id(),
        ]);

        // Registro de interação (opcional)
        if (!empty($mensagem)) {
            \DB::table('interacao')->insert($inserts);
        }

        return response()->json(['success' => true, 'message' => 'Pedido aprovado com sucesso']);
    }

    /** Rejeita todas as solicitações do grupo */
    public function rejeitarGrupo(Request $request, string $numPedido)
    {
        // Sanitização do número do pedido
        $numPedido = trim($numPedido);
        if (empty($numPedido)) {
            return response()->json(['success' => false, 'message' => 'Número de pedido inválido'], 400);
        }

        $ids = \DB::table('solicitacao as s')
            ->where('s.num_pedido', '=', $numPedido)
            ->where(function($q){ $q->where('s.aprovacao', '=', 'pendente')->orWhereNull('s.aprovacao'); })
            ->pluck('s.id');

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        $mensagem = $request->input('mensagem');
        $agora = now();

        $inserts = $ids->map(function ($id) use ($mensagem, $agora) {
            return [
                'solicitacao_id' => $id,
                'usuario_id' => auth()->id(),
                'tipo' => 'rejeicao',
                'mensagem' => $mensagem,
                'dados_extras' => null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        })->all();

        // Atualiza status, data e aprovador do grupo
        \DB::table('solicitacao')->whereIn('id', $ids)->update([
            'aprovacao' => 'rejeitado',
            'data_aprovacao' => $agora,
            'id_aprovador' => auth()->id(),
        ]);

        // Registro de interação (opcional)
        if (!empty($mensagem)) {
            \DB::table('interacao')->insert($inserts);
        }

        return response()->json(['success' => true, 'message' => 'Pedido rejeitado com sucesso']);
    }

    /** Enviar mensagem do autorizador para o solicitante em um grupo */
    public function mensagemGrupo(Request $request, string $numPedido)
    {
        // Sanitização do número do pedido
        $numPedido = trim($numPedido);
        if (empty($numPedido)) {
            return response()->json(['success' => false, 'message' => 'Número de pedido inválido'], 400);
        }
        
        $request->validate(['mensagem' => 'required|string|min:2|max:2000']);

        $ids = \DB::table('solicitacao as s')
            ->where('s.num_pedido', '=', $numPedido)
            ->where(function($q){ $q->where('s.aprovacao', '=', 'pendente')->orWhereNull('s.aprovacao'); })
            ->pluck('s.id');

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        // Inserir apenas UMA interação para o grupo, vinculada ao primeiro item,
        // evitando duplicação visual nas telas que agregam por grupo
        $agora = now();
        $mensagem = $request->input('mensagem');
        $primeiroId = $ids->first();

        \DB::table('interacao')->insert([
            'solicitacao_id' => $primeiroId,
            'usuario_id' => auth()->id(),
            'tipo' => 'comentario',
            'mensagem' => $mensagem,
            'dados_extras' => null,
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);

        return response()->json(['success' => true, 'message' => 'Mensagem enviada']);
    }

    /** Exclui todas as solicitações de um grupo (num_pedido) – somente Admin, com log */
    public function excluirGrupo(Request $request, string $numPedido)
    {
        // Verifica Admin (perfil)
        $user = auth()->user();
        if (!$user || !($user->profile && in_array($user->profile->name, ['Admin','Administrador'], true))) {
            return response()->json(['success' => false, 'message' => 'Apenas administradores podem excluir pedidos.'], 403);
        }

        $numPedido = trim($numPedido);
        if ($numPedido === '') {
            return response()->json(['success' => false, 'message' => 'Número de pedido inválido.'], 422);
        }

        try {
            \DB::beginTransaction();

            // Captura itens para log antes de excluir
            $itens = \DB::table('solicitacao')->where('num_pedido', $numPedido)->get();
            if ($itens->isEmpty()) {
                \DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Pedido não encontrado.'], 404);
            }

            // Log por item (se a tabela existir)
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('logs_solicitacao')) {
                    foreach ($itens as $it) {
                        \DB::table('logs_solicitacao')->insert([
                            'num_pedido' => $numPedido,
                            'solicitacao_id' => (int)$it->id,
                            'usuario_id' => (int)auth()->id(),
                            'acao' => 'delete_group',
                            'detalhes' => json_encode(['antes' => $it], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                            'ip' => $request->ip(),
                            'user_agent' => substr((string)$request->userAgent(), 0, 500),
                            'created_at' => now(),
                        ]);
                    }
                }
            } catch (\Throwable $e) { /* ignore log errors */ }

            // Excluir todos os itens do grupo
            \DB::table('solicitacao')->where('num_pedido', $numPedido)->delete();

            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Pedido excluído com sucesso.']);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Falha ao excluir: '.$e->getMessage()], 500);
        }
    }

    /**
     * Lista pedidos aprovados (última interação = aprovacao)
     */
    public function pedidosAprovados(Request $request)
    {
        $aprovadosQuery = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.aprovacao', '=', 'aprovado')
            ->orderByDesc('s.data_solicitacao');

        // filtros de data (data_solicitacao)
        if ($request->filled('data_ini')) {
            $aprovadosQuery->whereDate('s.data_solicitacao', '>=', $request->input('data_ini'));
        }
        if ($request->filled('data_fim')) {
            $aprovadosQuery->whereDate('s.data_solicitacao', '<=', $request->input('data_fim'));
        }

        $aprovados = $aprovadosQuery->get([
                's.*',
                \DB::raw("COALESCE(u.name, '—') as solicitante"),
                \DB::raw("COALESCE(cc.nome, '—') as centro_custo_nome"),
                's.data_aprovacao as data_decisao',
            ]);

        return response()->json(['success' => true, 'data' => $aprovados]);
    }

    /**
     * Lista pedidos rejeitados (última interação = rejeicao)
     */
    public function pedidosRejeitados(Request $request)
    {
        $rejeitadosQuery = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.aprovacao', '=', 'rejeitado')
            ->orderByDesc('s.data_solicitacao');

        if ($request->filled('data_ini')) {
            $rejeitadosQuery->whereDate('s.data_solicitacao', '>=', $request->input('data_ini'));
        }
        if ($request->filled('data_fim')) {
            $rejeitadosQuery->whereDate('s.data_solicitacao', '<=', $request->input('data_fim'));
        }

        $rejeitados = $rejeitadosQuery->get([
                's.*',
                \DB::raw("COALESCE(u.name, '—') as solicitante"),
                \DB::raw("COALESCE(cc.nome, '—') as centro_custo_nome"),
                's.data_aprovacao as data_decisao',
            ]);

        return response()->json(['success' => true, 'data' => $rejeitados]);
    }

    /**
     * Aprovados agrupados por envio
     */
    public function pedidosAprovadosAgrupados(Request $request)
    {
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        // Verifica presença da tabela de visualizações para evitar erro em produção
        $temViews = false;
        try { $temViews = \Illuminate\Support\Facades\Schema::hasTable('solicitacao_views'); } catch (\Throwable $e) { $temViews = false; }

        $query = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->leftJoin('users as uap', 'uap.id', '=', 's.id_aprovador')
            ->leftJoin('rotas as r', 'r.id', '=', 's.rota_id')
            ->leftJoin('roteirizacao as rt', 'rt.id', '=', 's.roteirizacao_id')
            ->where('s.aprovacao', '=', 'aprovado')
            ->groupByRaw("$hashExpr, s.usuario_id, s.centro_custo_id, s.prioridade, COALESCE(s.observacao,''), DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s'), u.name, cc.nome")
            ->orderByDesc('data_aprovacao');

        if ($temViews) {
            $query->leftJoin('solicitacao_views as sv', 'sv.solicitacao_id', '=', 's.id');
        }

        if ($request->filled('data_ini')) {
            $query->whereDate('s.data_aprovacao', '>=', $request->input('data_ini'));
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('s.data_aprovacao', '<=', $request->input('data_fim'));
        }

        // Filtro por número do pedido (parcial)
        if ($request->filled('num_pedido')) {
            $np = trim($request->input('num_pedido'));
            $query->where('s.num_pedido', 'like', "%{$np}%");
        }

        $grupos = $query->get([
                \DB::raw("$hashExpr as grupo_hash"),
                \DB::raw("DATE_FORMAT(MAX(s.data_aprovacao),'%Y-%m-%d %H:%i:%s') as data_aprovacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.prioridade',
                \DB::raw("COALESCE(s.observacao,'') as observacao"),
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw("COALESCE(MIN(r.nome_rota),'—') as rota_nome"),
                \DB::raw("COALESCE(MIN(rt.nome),'—') as roteirizacao_nome"),
                \DB::raw('MIN(s.num_pedido) as num_pedido'),
                \DB::raw('COUNT(*) as itens'),
                \DB::raw('SUM(s.quantidade) as quantidade_total'),
                \DB::raw("COALESCE(MIN(uap.name),'—') as aprovador_nome"),
                \DB::raw(($temViews ? "MIN(sv.visto_em)" : "NULL")." as visualizado_em"),
            ]);

        return response()->json(['success' => true, 'data' => $grupos]);
    }

    /**
     * Rejeitados agrupados por envio
     */
    public function pedidosRejeitadosAgrupados(Request $request)
    {
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $query = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->leftJoin('rotas as r', 'r.id', '=', 's.rota_id')
            ->leftJoin('roteirizacao as rt', 'rt.id', '=', 's.roteirizacao_id')
            ->where('s.aprovacao', '=', 'rejeitado')
            ->groupByRaw("$hashExpr, s.usuario_id, s.centro_custo_id, s.prioridade, COALESCE(s.observacao,''), DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s'), u.name, cc.nome")
            ->orderByDesc('data_aprovacao')
            ;

        if ($request->filled('data_ini')) {
            $query->whereDate('s.data_aprovacao', '>=', $request->input('data_ini'));
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('s.data_aprovacao', '<=', $request->input('data_fim'));
        }

        // Filtro por número do pedido (parcial)
        if ($request->filled('num_pedido')) {
            $np = trim($request->input('num_pedido'));
            $query->where('s.num_pedido', 'like', "%{$np}%");
        }

        $grupos = $query->get([
                \DB::raw("$hashExpr as grupo_hash"),
                \DB::raw("DATE_FORMAT(MAX(s.data_aprovacao),'%Y-%m-%d %H:%i:%s') as data_aprovacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.prioridade',
                \DB::raw("COALESCE(s.observacao,'') as observacao"),
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw("COALESCE(MIN(r.nome_rota),'—') as rota_nome"),
                \DB::raw("COALESCE(MIN(rt.nome),'—') as roteirizacao_nome"),
                \DB::raw('MIN(s.num_pedido) as num_pedido'),
                \DB::raw('COUNT(*) as itens'),
                \DB::raw('SUM(s.quantidade) as quantidade_total'),
            ]);

        return response()->json(['success' => true, 'data' => $grupos]);
    }

    /**
     * Detalhes (cabeçalho, itens e interações) para o Relatório de Pedido de Compras
     * baseado no mesmo hash usado em aprovados/rejeitados.
     */
    public function detalhesRelatorioPorHash(string $hash)
    {
        // Validar formato do hash SHA1
        if (!preg_match('/^[a-f0-9]{40}$/', $hash)) {
            return response()->json(['success' => false, 'message' => 'Parâmetro inválido'], 400);
        }

        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        // Filtro opcional por status (aprovados/rejeitados)
        $statusParam = strtolower((string) request()->query('status', ''));
        $statusFilter = null;
        if (in_array($statusParam, ['aprovados', 'aprovado'], true)) {
            $statusFilter = 'aprovado';
        } elseif (in_array($statusParam, ['rejeitados', 'rejeitado'], true)) {
            $statusFilter = 'rejeitado';
        }

        // Cabeçalho (sem filtrar por usuário; relatório geral)
        $cabecalhoQuery = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->leftJoin('rotas as r', 'r.id', '=', 's.rota_id')
            ->leftJoin('roteirizacao as rt', 'rt.id', '=', 's.roteirizacao_id')
            ->whereRaw("$hashExpr = ?", [$hash]);
        if ($statusFilter !== null) {
            $cabecalhoQuery->where('s.aprovacao', '=', $statusFilter);
        }
        $cabecalho = $cabecalhoQuery
            ->orderByDesc('s.data_solicitacao')
            ->first([
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.rota_id',
                's.roteirizacao_id',
                's.num_pedido',
                's.prioridade',
                's.observacao',
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw("COALESCE(r.nome_rota,'—') as rota_nome"),
                \DB::raw("COALESCE(rt.nome,'—') as roteirizacao_nome"),
            ]);

        // Buscar a data de aprovação (máxima) separadamente para evitar ONLY_FULL_GROUP_BY
        $aprovQuery = \DB::table('solicitacao as s')
            ->whereRaw("$hashExpr = ?", [$hash]);
        if ($statusFilter !== null) {
            $aprovQuery->where('s.aprovacao', '=', $statusFilter);
        }
        $aprov = $aprovQuery
            ->select(\DB::raw("DATE_FORMAT(MAX(s.data_aprovacao),'%Y-%m-%d %H:%i:%s') as data_aprovacao"))
            ->first();
        if ($cabecalho) {
            $cabecalho->data_aprovacao = $aprov->data_aprovacao ?? null;
        }

        if (!$cabecalho) {
            return response()->json(['success' => false, 'message' => 'Não encontrado'], 404);
        }

        // Itens do grupo
        $itensQuery = \DB::table('solicitacao as s')
            ->whereRaw("$hashExpr = ?", [$hash]);
        if ($statusFilter !== null) {
            $itensQuery->where('s.aprovacao', '=', $statusFilter);
        }
        $itens = $itensQuery
            ->orderBy('s.id')
            ->get([
                's.id',
                's.produto_nome',
                's.quantidade',
            ]);

        // Interações de todos os itens do grupo
        $idsGrupo = $itens->pluck('id')->all();
        $interacoes = [];
        if (!empty($idsGrupo)) {
            $interacoes = \DB::table('interacao as i')
                ->leftJoin('users as u', 'u.id', '=', 'i.usuario_id')
                ->whereIn('i.solicitacao_id', $idsGrupo)
                ->orderByDesc('i.created_at')
                ->get([
                    'i.id', 'i.solicitacao_id', 'i.tipo', 'i.mensagem', 'i.created_at',
                    \DB::raw("COALESCE(u.name,'—') as usuario")
                ]);
        }

        return response()->json(['success' => true, 'data' => compact('cabecalho','itens','interacoes')]);
    }

    /** Marca como visualizado (global) todos os itens do grupo identificado pelo hash */
    public function marcarVisualizadoPorHash(string $hash)
    {
        // Validar formato do hash SHA1
        if (!preg_match('/^[a-f0-9]{40}$/', $hash)) {
            return response()->json(['success' => false, 'message' => 'Parâmetro inválido'], 400);
        }

        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        // Pegar os IDs do grupo
        $ids = \DB::table('solicitacao as s')
            ->whereRaw("$hashExpr = ?", [$hash])
            ->pluck('s.id')
            ->all();

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        // Registrar visualização global (uma linha por item, sem vínculo com usuário)
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('solicitacao_views')) {
                // Se a tabela ainda não existir, não falhar: apenas retornar sucesso
                return response()->json(['success' => true]);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => true]);
        }

        $agora = now();
        $uid = (int) (auth()->id() ?? 0);
        $rows = array_map(function($id) use ($agora, $uid){
            return [ 'solicitacao_id' => (int)$id, 'user_id' => $uid, 'visto_em' => $agora ];
        }, $ids);

        // upsert por solicitacao_id
        try {
            // Upsert por (solicitacao_id, user_id) para registrar quem visualizou
            \DB::table('solicitacao_views')->upsert($rows, ['solicitacao_id','user_id'], ['visto_em']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Falha ao registrar visualização: '.$e->getMessage()], 500);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Busca produtos por nome (autocomplete)
     * Lista de produtos sugeridos para autocomplete
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarProdutos(Request $request)
    {
        $termo = $request->get('termo');
        
        // Sanitização para prevenir SQL injection
        $termo = preg_replace('/[^a-zA-Z0-9\sÀ-ÿ\-]/', '', (string) $termo);
        // Força busca case-insensitive de forma confiável
        $termoLower = mb_strtolower($termo ?? '', 'UTF-8');
        
        if (strlen($termo) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Digite ao menos 3 caracteres'
            ]);
        }

        try {
            // Busca por nome/descrição e também por código do produto
            $query = \DB::table('estoque_pedido as ep')
                ->where('ep.ativo', 1)
                ->where(function ($q) use ($termoLower) {
                    $q->whereRaw('LOWER(ep.produto) LIKE ?', ['%' . $termoLower . '%'])
                      ->orWhereRaw('LOWER(COALESCE(ep.descricao, "")) LIKE ?', ['%' . $termoLower . '%'])
                      // Permitir buscar pelo código (inteiro) convertendo para texto
                      ->orWhereRaw('CAST(ep.codigo AS CHAR) LIKE ?', ['%' . $termoLower . '%']);
                })
                ->orderBy('ep.produto')
                ->limit(15)
                ->select([
                    'ep.id',
                    'ep.produto as nome',
                    'ep.descricao',
                ]);

            // Filtrar bloqueados para o usuário logado
            $uid = auth()->id();
            if ($uid) {
                $query->whereNotExists(function($sub) use ($uid){
                    $sub->select(\DB::raw(1))
                        ->from('restricoes_produto_usuario as r')
                        ->whereColumn('r.produto_id', 'ep.id')
                        ->where('r.user_id', $uid)
                        ->where('r.bloqueado', 1);
                });
            }

            $produtos = $query->get();

            return response()->json([
                'success' => true,
                'data' => $produtos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
            ]);
        }
    }

    /** API: listar produtos de estoque_pedido com busca opcional */
    public function produtosListar(Request $request)
    {
        $termo = trim((string) $request->get('q'));

        $query = \DB::table('estoque_pedido as ep')
            ->select('ep.id','ep.codigo','ep.produto','ep.descricao','ep.valor_unitario','ep.ativo','ep.id_usuario','ep.alterado')
            ->orderBy('ep.produto');

        if ($termo !== '') {
            $termoLower = mb_strtolower($termo, 'UTF-8');
            $query->where(function($q) use ($termoLower) {
                $q->whereRaw('LOWER(ep.produto) LIKE ?', ['%' . $termoLower . '%'])
                  ->orWhereRaw('LOWER(COALESCE(ep.descricao, "")) LIKE ?', ['%' . $termoLower . '%'])
                  ->orWhere('ep.codigo', 'like', "%$termoLower%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /** API: criar novo produto em estoque_pedido */
    public function produtosCriar(Request $request)
    {
        $request->validate([
            'codigo' => 'nullable|integer',
            'produto' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'valor_unitario' => 'required|numeric|min:0',
        ]);

        $id = \DB::table('estoque_pedido')->insertGetId([
            'codigo' => $request->codigo,
            'produto' => $request->produto,
            'descricao' => $request->descricao,
            'valor_unitario' => $request->valor_unitario,
            'ativo' => 1,
            'id_usuario' => auth()->id(),
            'alterado' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $novo = \DB::table('estoque_pedido')->where('id', $id)->first();
        return response()->json(['success' => true, 'data' => $novo]);
    }

    /** API: alternar ativo/inativo */
    public function produtosToggleAtivo($id)
    {
        $produto = \DB::table('estoque_pedido')->where('id', $id)->first();
        if (!$produto) {
            return response()->json(['success' => false, 'message' => 'Produto não encontrado'], 404);
        }
        $novoValor = (int)(!$produto->ativo);
        \DB::table('estoque_pedido')->where('id', $id)->update([
            'ativo' => $novoValor,
            'id_usuario' => auth()->id(),
            'alterado' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true, 'data' => ['id' => (int)$id, 'ativo' => $novoValor]]);
    }

    /** API: atualizar produto de estoque_pedido */
    public function produtosAtualizar(Request $request, $id)
    {
        $produto = \DB::table('estoque_pedido')->where('id', $id)->first();
        if (!$produto) {
            return response()->json(['success' => false, 'message' => 'Produto não encontrado'], 404);
        }

        $dados = $request->validate([
            'codigo' => 'nullable|integer',
            'produto' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'valor_unitario' => 'required|numeric|min:0',
        ]);

        \DB::table('estoque_pedido')->where('id', $id)->update([
            'codigo' => $dados['codigo'] ?? null,
            'produto' => $dados['produto'],
            'descricao' => $dados['descricao'] ?? null,
            'valor_unitario' => $dados['valor_unitario'],
            'id_usuario' => auth()->id(),
            'alterado' => now(),
            'updated_at' => now(),
        ]);

        $novo = \DB::table('estoque_pedido')->where('id', $id)->first();
        return response()->json(['success' => true, 'data' => $novo]);
    }

    /**
     * Lista centros de custo (todos)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarCentrosCusto()
    {
        try {
            $centrosCusto = \DB::table('centro_custo')
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome']);

            return response()->json([
                'success' => true,
                'data' => $centrosCusto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar centros de custo'
            ]);
        }
    }

    /**
     * Busca centros de custo por nome (autocomplete)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarCentrosCustoAutocomplete(Request $request)
    {
        $termo = $request->get('termo');
        
        if (strlen($termo) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Digite ao menos 3 caracteres'
            ]);
        }

        try {
            $centrosCusto = \DB::table('centro_custo')
                ->where('ativo', true)
                ->where('nome', 'LIKE', "%{$termo}%")
                ->orderBy('nome')
                ->limit(10)
                ->get(['id', 'nome']);

            return response()->json([
                'success' => true,
                'data' => $centrosCusto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar centros de custo'
            ]);
        }
    }

    /** View: Acompanhar Pedido (somente leitura) */
    public function acompanharView()
    {
        return view('pedidos.acompanhar_home');
    }

    /** Lista agrupada (pendente/aprovado/rejeitado) só do usuário logado */
    public function acompanharLista()
    {
        $usuarioId = auth()->id();

        // Carrega os registros do usuário e realiza o agrupamento em PHP para evitar
        // problemas com modos SQL (ONLY_FULL_GROUP_BY) e diferenças de collation.
        $registros = \DB::table('solicitacao as s')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->leftJoin('rotas as r', 'r.id', '=', 's.rota_id')
            ->leftJoin('roteirizacao as rt', 'rt.id', '=', 's.roteirizacao_id')
            ->where('s.usuario_id', $usuarioId)
            ->orderByDesc('s.data_solicitacao')
            ->get([
                's.id', 's.usuario_id', 's.centro_custo_id', 's.num_pedido', 's.prioridade',
                's.observacao', 's.data_solicitacao', 's.quantidade', 's.aprovacao',
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw("COALESCE(r.nome_rota,'—') as rota_nome"),
                \DB::raw("COALESCE(rt.nome,'—') as roteirizacao_nome"),
            ]);

        $grupos = $registros->groupBy(function ($r) {
            $data = \Carbon\Carbon::parse($r->data_solicitacao)->format('Y-m-d H:i:s');
            return sha1($r->usuario_id . '|' . $r->centro_custo_id . '|' . $r->prioridade . '|' . ($r->observacao ?? '') . '|' . $data);
        })->map(function ($items) {
            $first = $items->first();

            // Determina o status do grupo: se algum item estiver rejeitado, prioriza rejeitado;
            // senão, se algum estiver aprovado, é aprovado; caso contrário, pendente.
            $status = 'pendente';
            if ($items->contains(function ($i) { return $i->aprovacao === 'rejeitado'; })) {
                $status = 'rejeitado';
            } elseif ($items->contains(function ($i) { return $i->aprovacao === 'aprovado'; })) {
                $status = 'aprovado';
            }

            $dataFormatada = \Carbon\Carbon::parse($first->data_solicitacao)->format('Y-m-d H:i:s');
            $grupoHash = sha1($first->usuario_id . '|' . $first->centro_custo_id . '|' . $first->prioridade . '|' . ($first->observacao ?? '') . '|' . $dataFormatada);

            return (object) [
                'grupo_hash' => $grupoHash,
                'num_pedido' => $first->num_pedido,
                'data_solicitacao' => $dataFormatada,
                'centro_custo_nome' => $first->centro_custo_nome,
                'rota_nome' => $first->rota_nome,
                'roteirizacao_nome' => $first->roteirizacao_nome,
                'itens' => $items->count(),
                'quantidade_total' => $items->sum('quantidade'),
                'prioridade' => $first->prioridade,
                'status' => $status,
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $grupos]);
    }

    /** Detalhes só leitura do grupo do usuário */
    public function acompanharDetalhes(string $hash)
    {
        $usuarioId = auth()->id();
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $cabecalho = \DB::table('solicitacao as s')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.usuario_id', $usuarioId)
            ->whereRaw("$hashExpr = ?", [$hash])
            ->orderByDesc('s.data_solicitacao')
            ->first([
                's.num_pedido',
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                's.prioridade', 's.aprovacao'
            ]);

        if (!$cabecalho) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        $itens = \DB::table('solicitacao as s')
            ->where('s.usuario_id', $usuarioId)
            ->whereRaw("$hashExpr = ?", [$hash])
            ->orderBy('s.id')
            ->get(['s.id','s.produto_nome','s.quantidade']);

        $interacoes = \DB::table('interacao as i')
            ->leftJoin('users as u', 'u.id', '=', 'i.usuario_id')
            ->whereIn('i.solicitacao_id', $itens->pluck('id')->all())
            ->orderByDesc('i.created_at')
            ->get(['i.id','i.tipo','i.mensagem','i.created_at', \DB::raw("COALESCE(u.name,'—') as usuario")]);

        return response()->json(['success' => true, 'data' => compact('cabecalho','itens','interacoes')]);
    }

    /**
     * Gera layout de impressão para um pedido específico
     */
    public function imprimirPedido(string $hash)
    {
        // Validação do hash para prevenir SQL injection
        if (!preg_match('/^[a-f0-9]{40}$/', $hash)) {
            abort(404, 'Hash inválido');
        }
        
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        // Filtro opcional por status (aprovados/rejeitados) vindo da query string
        $statusParam = strtolower((string) request()->query('status', ''));
        $statusFilter = null;
        if (in_array($statusParam, ['aprovados', 'aprovado'], true)) {
            $statusFilter = 'aprovado';
        } elseif (in_array($statusParam, ['rejeitados', 'rejeitado'], true)) {
            $statusFilter = 'rejeitado';
        }

        $cabecalhoQuery = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->leftJoin('rotas as r', 'r.id', '=', 's.rota_id')
            ->leftJoin('roteirizacao as rt', 'rt.id', '=', 's.roteirizacao_id')
            ->whereRaw("$hashExpr = ?", [$hash]);
        if ($statusFilter !== null) {
            $cabecalhoQuery->where('s.aprovacao', '=', $statusFilter);
        }
        $cabecalho = $cabecalhoQuery
            ->orderByDesc('s.data_solicitacao')
            ->first([
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.num_pedido',
                's.prioridade',
                's.observacao',
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw("COALESCE(r.nome_rota,'—') as rota_nome"),
                \DB::raw("COALESCE(rt.nome,'—') as roteirizacao_nome"),
            ]);

        if (!$cabecalho) {
            abort(404, 'Pedido não encontrado');
        }

        $itensQuery = \DB::table('solicitacao as s')
            ->leftJoin('estoque_pedido as ep', function($join){
                $join->on(
                    \DB::raw("LOWER(TRIM(ep.produto)) COLLATE utf8mb4_general_ci"),
                    '=',
                    \DB::raw("LOWER(TRIM(s.produto_nome)) COLLATE utf8mb4_general_ci")
                )
                ->where('ep.ativo', 1);
            })
            ->whereRaw("$hashExpr = ?", [$hash]);
        if ($statusFilter !== null) {
            $itensQuery->where('s.aprovacao', '=', $statusFilter);
        }
        $itens = $itensQuery
            ->orderBy('s.id')
            ->get([
                's.id',
                's.produto_nome',
                's.quantidade',
                \DB::raw('COALESCE(ep.codigo, NULL) as codigo'),
            ]);

        // Interações de todos os itens do grupo
        $idsGrupo = $itens->pluck('id')->all();
        $interacoes = [];
        if (!empty($idsGrupo)) {
            $interacoes = \DB::table('interacao as i')
                ->leftJoin('users as u', 'u.id', '=', 'i.usuario_id')
                ->whereIn('i.solicitacao_id', $idsGrupo)
                ->orderByDesc('i.created_at')
                ->get([
                    'i.id', 'i.solicitacao_id', 'i.tipo', 'i.mensagem', 'i.created_at',
                    \DB::raw("COALESCE(u.name,'—') as usuario")
                ]);
        }

        return view('relatorios.imprimir-pedido', compact('cabecalho', 'itens', 'interacoes'));
    }

    /**
     * Busca rotas por centro de custo
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarTodasRotas(Request $request)
    {
        try {
            $rotas = \DB::table('rotas')
                ->where('ativo', 1)
                ->orderBy('numero_rota')
                ->get(['id', 'numero_rota', 'nome_rota']);

            return response()->json([
                'success' => true,
                'data' => $rotas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar rotas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscarRotasPorCentroCusto(Request $request)
    {
        $centroCustoId = $request->get('centro_custo_id');
        
        if (!$centroCustoId) {
            return response()->json([
                'success' => false,
                'message' => 'Centro de custo é obrigatório'
            ], 400);
        }

        try {
            $rotas = \DB::table('rotas')
                ->where('centro_custo_id', $centroCustoId)
                ->where('ativo', 1)
                ->orderBy('numero_rota')
                ->get(['id', 'numero_rota', 'nome_rota']);

            return response()->json([
                'success' => true,
                'data' => $rotas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar rotas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca roteirizações por rota
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarRoteirizacoesPorRota(Request $request)
    {
        $rotaId = $request->get('rota_id');
        
        if (!$rotaId) {
            return response()->json([
                'success' => false,
                'message' => 'Rota é obrigatória'
            ], 400);
        }

        try {
            $roteirizacoes = \DB::table('roteirizacao')
                ->where('rota_id', $rotaId)
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get(['id', 'nome']);

            return response()->json([
                'success' => true,
                'data' => $roteirizacoes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar roteirizações: ' . $e->getMessage()
            ], 500);
        }
    }

    /** Atualiza o cabeçalho de um grupo (somente Admin): centro de custo, rota, roteirização e observação */
    public function atualizarCabecalhoGrupo(Request $request, string $numPedido)
    {
        // Verifica Admin (perfil)
        $user = auth()->user();
        if (!$user || !($user->profile && in_array($user->profile->name, ['Admin','Administrador'], true))) {
            return response()->json(['success' => false, 'message' => 'Apenas administradores podem alterar o cabeçalho.'], 403);
        }

        $dados = $request->validate([
            'centro_custo_id' => 'nullable|integer|exists:centro_custo,id',
            'rota_id' => 'nullable|integer|exists:rotas,id',
            'roteirizacao_id' => 'nullable|integer|exists:roteirizacao,id',
            'observacao' => 'nullable|string',
        ]);

        $numPedido = trim($numPedido);
        if ($numPedido === '') {
            return response()->json(['success' => false, 'message' => 'Número de pedido inválido.'], 422);
        }

        // Nada a atualizar
        if (!array_key_exists('centro_custo_id', $dados) && !array_key_exists('rota_id', $dados)
            && !array_key_exists('roteirizacao_id', $dados) && !array_key_exists('observacao', $dados)) {
            return response()->json(['success' => false, 'message' => 'Nenhum campo enviado para atualização.'], 422);
        }

        try {
            \DB::beginTransaction();

            // Itens afetados
            $itens = \DB::table('solicitacao')->where('num_pedido', $numPedido)->get([
                'id','centro_custo_id','rota_id','roteirizacao_id','observacao'
            ]);
            if ($itens->isEmpty()) {
                \DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Pedido não encontrado.'], 404);
            }

            $updateData = [];
            if (array_key_exists('centro_custo_id', $dados)) { $updateData['centro_custo_id'] = $dados['centro_custo_id']; }
            if (array_key_exists('rota_id', $dados)) { $updateData['rota_id'] = $dados['rota_id']; }
            if (array_key_exists('roteirizacao_id', $dados)) { $updateData['roteirizacao_id'] = $dados['roteirizacao_id']; }
            if (array_key_exists('observacao', $dados)) {
                // Permitir limpar observação (string vazia)
                $obs = (string)$dados['observacao'];
                $updateData['observacao'] = $obs === '' ? null : strip_tags(trim($obs));
            }

            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('solicitacao', 'updated_at')) {
                    $updateData['updated_at'] = now();
                }
            } catch (\Throwable $e) { /* ignore */ }

            \DB::table('solicitacao')->where('num_pedido', $numPedido)->update($updateData);

            // Logging por item, se a tabela existir
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('logs_solicitacao')) {
                    foreach ($itens as $antes) {
                        $mudancas = [];
                        $depois = [
                            'centro_custo_id' => array_key_exists('centro_custo_id', $dados) ? $dados['centro_custo_id'] : $antes->centro_custo_id,
                            'rota_id' => array_key_exists('rota_id', $dados) ? $dados['rota_id'] : $antes->rota_id,
                            'roteirizacao_id' => array_key_exists('roteirizacao_id', $dados) ? $dados['roteirizacao_id'] : $antes->roteirizacao_id,
                            'observacao' => array_key_exists('observacao', $dados) ? $updateData['observacao'] : $antes->observacao,
                        ];
                        if ((int)($antes->centro_custo_id ?? 0) !== (int)($depois['centro_custo_id'] ?? 0)) {
                            $mudancas['centro_custo_id'] = ['antes' => $antes->centro_custo_id, 'depois' => $depois['centro_custo_id']];
                        }
                        if ((int)($antes->rota_id ?? 0) !== (int)($depois['rota_id'] ?? 0)) {
                            $mudancas['rota_id'] = ['antes' => $antes->rota_id, 'depois' => $depois['rota_id']];
                        }
                        if ((int)($antes->roteirizacao_id ?? 0) !== (int)($depois['roteirizacao_id'] ?? 0)) {
                            $mudancas['roteirizacao_id'] = ['antes' => $antes->roteirizacao_id, 'depois' => $depois['roteirizacao_id']];
                        }
                        if ((string)($antes->observacao ?? '') !== (string)($depois['observacao'] ?? '')) {
                            $mudancas['observacao'] = ['antes' => $antes->observacao, 'depois' => $depois['observacao']];
                        }

                        if (!empty($mudancas)) {
                            \DB::table('logs_solicitacao')->insert([
                                'num_pedido' => $numPedido,
                                'solicitacao_id' => (int)$antes->id,
                                'usuario_id' => (int)auth()->id(),
                                'acao' => 'update_header',
                                'detalhes' => json_encode([
                                    'antes' => [
                                        'centro_custo_id' => $antes->centro_custo_id,
                                        'rota_id' => $antes->rota_id,
                                        'roteirizacao_id' => $antes->roteirizacao_id,
                                        'observacao' => $antes->observacao,
                                    ],
                                    'depois' => $depois,
                                    'mudancas' => $mudancas,
                                ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                                'ip' => $request->ip(),
                                'user_agent' => substr((string)$request->userAgent(), 0, 500),
                                'created_at' => now(),
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) { /* ignore log errors */ }

            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Cabeçalho atualizado com sucesso.']);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Falha ao atualizar cabeçalho: '.$e->getMessage()], 500);
        }
    }

    /**
     * Exibe a página de duplicar pedidos
     *
     * @return \Illuminate\View\View
     */
    public function duplicarView()
    {
        return view('pedidos.duplicar');
    }

    /**
     * Lista os pedidos do usuário agrupados por num_pedido para duplicação
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function meusPedidos(Request $request)
    {
        $usuarioId = auth()->id();

        try {
            // Consulta simples: traz todas as linhas do usuário e filtra por data se enviado
            $base = \DB::table('solicitacao')
                ->where('usuario_id', $usuarioId);

            if ($request->filled('data_inicio')) {
                $base->whereDate('data_solicitacao', '>=', $request->input('data_inicio'));
            }
            if ($request->filled('data_fim')) {
                $base->whereDate('data_solicitacao', '<=', $request->input('data_fim'));
            }

            $linhas = $base
                ->orderByDesc('data_solicitacao')
                ->get([
                    'num_pedido', 'data_solicitacao', 'centro_custo_id',
                    'prioridade', 'produto_nome', 'quantidade'
                ]);

            if ($linhas->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            // Buscar nomes dos centros de custo usados (em lote)
            $ccIds = $linhas->pluck('centro_custo_id')->filter()->unique()->values()->all();
            $mapCc = [];
            if (!empty($ccIds)) {
                $mapCc = \DB::table('centro_custo')
                    ->whereIn('id', $ccIds)
                    ->pluck('nome', 'id')
                    ->toArray();
            }

            // Agrupar por num_pedido e montar resumo no PHP
            $grupos = [];
            foreach ($linhas as $linha) {
                // Normalizar número de pedido para evitar duplicidade por espaços/caso
                $npRaw = (string)($linha->num_pedido ?? '');
                $npKey = strtoupper(preg_replace('/\s+/', '', trim($npRaw)));
                if (!isset($grupos[$npKey])) {
                    $grupos[$npKey] = [
                        'num_pedido' => trim($npRaw),
                        'data_solicitacao' => $linha->data_solicitacao,
                        'prioridade' => $linha->prioridade,
                        'centro_custo_nome' => isset($mapCc[$linha->centro_custo_id]) ? $mapCc[$linha->centro_custo_id] : null,
                        'total_itens' => 0,
                        'produtos_resumo' => []
                    ];
                }
                // menor data do grupo
                if ($linha->data_solicitacao < $grupos[$npKey]['data_solicitacao']) {
                    $grupos[$npKey]['data_solicitacao'] = $linha->data_solicitacao;
                }
                // manter primeira prioridade/cc se vierem diferentes
                if (!$grupos[$npKey]['prioridade'] && $linha->prioridade) {
                    $grupos[$npKey]['prioridade'] = $linha->prioridade;
                }
                if (!$grupos[$npKey]['centro_custo_nome'] && isset($mapCc[$linha->centro_custo_id])) {
                    $grupos[$npKey]['centro_custo_nome'] = $mapCc[$linha->centro_custo_id];
                }
                $grupos[$npKey]['total_itens'] += 1;
                $grupos[$npKey]['produtos_resumo'][] = trim(($linha->produto_nome ?? '') . ' (' . (int)($linha->quantidade ?? 0) . ')');
            }

            // Transformar em lista ordenada por data desc
            $resultado = collect(array_values($grupos))
                ->map(function ($g) {
                    $g['produtos_resumo'] = implode(', ', array_filter($g['produtos_resumo']));
                    return $g;
                })
                ->sortByDesc('data_solicitacao')
                ->values()
                ->all();

            return response()->json(['success' => true, 'data' => $resultado]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório de Pedidos de Compras por Centro de Custo
     */
    public function relatorioPedidoCC(Request $request)
    {
        try {
            // Query base com joins necessários
            $query = \DB::table('solicitacao as s')
                ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
                ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
                ->leftJoin('rotas as r', 'r.id', '=', 's.rota_id')
                ->leftJoin('roteirizacao as rt', 'rt.id', '=', 's.roteirizacao_id');

            // Aplicar filtros
            if ($request->filled('centro_custo_id')) {
                $query->where('s.centro_custo_id', '=', $request->input('centro_custo_id'));
            }

            if ($request->filled('rota_id')) {
                $query->where('s.rota_id', '=', $request->input('rota_id'));
            }

            if ($request->filled('data_inicio')) {
                $query->whereDate('s.data_solicitacao', '>=', $request->input('data_inicio'));
            }

            if ($request->filled('data_fim')) {
                $query->whereDate('s.data_solicitacao', '<=', $request->input('data_fim'));
            }

            if ($request->filled('status')) {
                $query->where('s.aprovacao', '=', $request->input('status'));
            }

            // Agrupar por número do pedido (um pedido pode ter vários itens)
            $query->groupBy('s.num_pedido');
            // Ordena pela menor data de solicitação do pedido
            $query->orderByDesc(\DB::raw('MIN(s.data_solicitacao)'));

            // Buscar dados agregados por pedido
            $dados = $query->get([
                's.num_pedido',
                \DB::raw('MIN(s.data_solicitacao) as data_solicitacao'),
                \DB::raw('MAX(s.data_aprovacao) as data_aprovacao'),
                // Se houver itens com status diferentes no mesmo pedido: rejeitado > pendente > aprovado
                \DB::raw("CASE WHEN SUM(CASE WHEN s.aprovacao = 'rejeitado' THEN 1 ELSE 0 END) > 0 THEN 'rejeitado' WHEN SUM(CASE WHEN s.aprovacao = 'pendente' THEN 1 ELSE 0 END) > 0 THEN 'pendente' ELSE 'aprovado' END as aprovacao"),
                \DB::raw('COUNT(*) as itens'),
                \DB::raw('SUM(s.quantidade) as quantidade_total'),
                \DB::raw('SUM(COALESCE(s.valor,0)) as valor_total'),
                // Mantém um valor representativo para exibição
                \DB::raw("COALESCE(MIN(u.name), '—') as solicitante"),
                \DB::raw("COALESCE(MIN(cc.nome), '—') as centro_custo_nome"),
                \DB::raw("COALESCE(MIN(r.nome_rota), '—') as rota_nome"),
                \DB::raw('MIN(s.prioridade) as prioridade'),
            ]);

            // Calcular resumo (agora por pedido)
            $total = $dados->count();
            $aprovados = $dados->where('aprovacao', 'aprovado')->count();
            $rejeitados = $dados->where('aprovacao', 'rejeitado')->count();
            $pendentes = $dados->where('aprovacao', 'pendente')->count();

            // Calcular valor total dos resultados filtrados
            $valorTotal = $dados->sum('valor_total');

            $resumo = [
                'total' => $total,
                'aprovados' => $aprovados,
                'rejeitados' => $rejeitados,
                'pendentes' => $pendentes,
                'valor_total' => $valorTotal
            ];

            return response()->json([
                'success' => true,
                'dados' => $dados,
                'resumo' => $resumo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplica um pedido existente
     *
     * @param string $numPedido
     * @return \Illuminate\Http\JsonResponse
     */
    public function duplicarPedido($numPedido)
    {
        $usuarioId = auth()->id();

        try {
            // Validar que o pedido pertence ao usuário
            $pedidoOriginal = \DB::table('solicitacao')
                ->where('num_pedido', $numPedido)
                ->where('usuario_id', $usuarioId)
                ->first();

            if (!$pedidoOriginal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido não encontrado ou você não tem permissão para duplicá-lo'
                ], 404);
            }

            // Buscar todos os itens do pedido original
            $itensOriginais = \DB::table('solicitacao')
                ->where('num_pedido', $numPedido)
                ->where('usuario_id', $usuarioId)
                ->get();

            if ($itensOriginais->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum item encontrado para este pedido'
                ], 404);
            }

            // Gerar novo número de pedido
            $novoNumPedido = 'PED-' . now()->format('Ymd-His') . '-' . str_pad((string) $usuarioId, 3, '0', STR_PAD_LEFT);

            // Duplicar cada item do pedido
            foreach ($itensOriginais as $item) {
                \DB::table('solicitacao')->insert([
                    'num_pedido' => $novoNumPedido,
                    'usuario_id' => $usuarioId,
                    'centro_custo_id' => $item->centro_custo_id,
                    'rota_id' => $item->rota_id,
                    'roteirizacao_id' => $item->roteirizacao_id,
                    'produto_nome' => $item->produto_nome,
                    'quantidade' => $item->quantidade,
                    'valor' => $item->valor ?? 0, // Copiar valor do pedido original
                    'prioridade' => $item->prioridade,
                    'observacao' => 'DUPLICADO DE: ' . $item->num_pedido . ' - ' . ($item->observacao ?? ''),
                    'data_solicitacao' => now(),
                    'aprovacao' => 'pendente',
                    'data_aprovacao' => null,
                    'id_aprovador' => null
                ]);
            }

            // Garantir que todos os itens do novo pedido fiquem com status pendente
            \DB::table('solicitacao')
                ->where('num_pedido', $novoNumPedido)
                ->update(['aprovacao' => 'pendente']);

            return response()->json([
                'success' => true,
                'message' => 'Pedido duplicado com sucesso!',
                'novo_pedido' => $novoNumPedido,
                'total_itens' => $itensOriginais->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista itens de um pedido (somente do usuário logado) para exibir no modal de duplicação
     */
    public function itensPedidoUsuario(string $numPedido)
    {
        $usuarioId = auth()->id();
        $numPedido = trim($numPedido);
        if ($numPedido === '') {
            return response()->json(['success' => false, 'message' => 'Número de pedido inválido'], 400);
        }

        $itens = \DB::table('solicitacao as s')
            ->where('s.usuario_id', $usuarioId)
            ->where('s.num_pedido', $numPedido)
            ->orderBy('s.id')
            ->get(['s.produto_nome', 's.quantidade']);

        return response()->json(['success' => true, 'data' => [ 'itens' => $itens ]]);
    }
}