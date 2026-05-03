<?php

namespace App\Http\Controllers;

use App\Support\OrdensCompraAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuprimentosController extends Controller
{
    // ============================================
    // PÁGINAS
    // ============================================
    
    public function fornecedores()
    {
        $fornecedores = collect([]);
        try {
            $fornecedores = DB::table('fornecedores')->orderBy('razao_social')->get();
        } catch (\Exception $e) {}
        
        return view('suprimentos.fornecedores', compact('fornecedores'));
    }

    public function solicitacao(Request $request)
    {
        $solicitacoes = collect([]);
        $user = auth()->user();
        $isAdmin = $this->isAdmin($user);
        $canDelete = $this->canDeleteCotacao($user);
        
        // Verificar se usuário tem perfil de suprimento (IDs: 1=Admin, 21=suprimento, 22=Encarregado Suprimento)
        $perfilSuprimento = in_array($user->profile_id, [1, 21, 22]);
        
        // Filtro de status: para suprimentos o padrão é 'aguardando', para outros usuários é 'todas'
        $filtroStatusPadrao = $perfilSuprimento ? 'aguardando' : 'todas';
        $filtroStatus = $request->get('filtro_status', $filtroStatusPadrao);
        
        $stats = [
            'pendentes' => 0,
            'em_cotacao' => 0,
            'finalizadas' => 0,
            'urgentes' => 0,
            'total' => 0
        ];
        
        $centroCustoNome = null;
        
        try {
            // Base query para estatísticas (filtrada por usuário se não for suprimento)
            $baseQuery = function($query) use ($user, $perfilSuprimento) {
                if (!$perfilSuprimento) {
                    $query->where('solicitante_id', $user->id);
                }
                return $query;
            };
            
            // Estatísticas de solicitações
            $stats['total'] = $baseQuery(DB::table('cotacoes'))->count();
            $stats['pendentes'] = $baseQuery(DB::table('cotacoes')->where('status', 'aberta')
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('cotacao_fornecedores as cf')
                      ->whereRaw('cf.cotacao_id = cotacoes.id')
                      ->whereNotNull('cf.valor_total')
                      ->where('cf.valor_total', '>', 0);
                }))->count();
            $stats['em_cotacao'] = $baseQuery(DB::table('cotacoes')->where('status', 'aberta')
                ->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('cotacao_fornecedores as cf')
                      ->whereRaw('cf.cotacao_id = cotacoes.id')
                      ->whereNotNull('cf.valor_total')
                      ->where('cf.valor_total', '>', 0);
                }))->count();
            $stats['finalizadas'] = $baseQuery(DB::table('cotacoes')->where('status', 'finalizada'))->count();
            $stats['urgentes'] = $baseQuery(DB::table('cotacoes')
                ->where('status', '!=', 'finalizada')
                ->whereNotNull('data_limite')
                ->whereRaw('data_limite <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)'))->count();
            
            // Buscar cotações como "solicitações" (a tabela solicitacoes não existe mais)
            $query = DB::table('cotacoes as c')
                ->leftJoin('users as u', 'c.solicitante_id', '=', 'u.id')
                ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                ->select(
                    'c.id',
                    'c.numero',
                    'c.descricao',
                    'c.status',
                    'c.created_at',
                    'c.solicitante_id',
                    'u.name as solicitante',
                    'cc.nome as obra'
                );
            
            // Filtrar por solicitante se não for perfil de suprimento
            if (!$perfilSuprimento) {
                $query->where('c.solicitante_id', $user->id);
            }
            
            // Aplicar filtro de status
            if ($filtroStatus == 'aguardando') {
                // Solicitações abertas SEM fornecedores com valor (ainda não cotadas)
                $query->where('c.status', 'aberta')
                    ->whereNotExists(function($q) {
                        $q->select(DB::raw(1))
                          ->from('cotacao_fornecedores as cf')
                          ->whereRaw('cf.cotacao_id = c.id')
                          ->whereNotNull('cf.valor_total')
                          ->where('cf.valor_total', '>', 0);
                    });
            } elseif ($filtroStatus == 'em_cotacao') {
                // Solicitações abertas COM fornecedores com valor (em processo de cotação)
                $query->where('c.status', 'aberta')
                    ->whereExists(function($q) {
                        $q->select(DB::raw(1))
                          ->from('cotacao_fornecedores as cf')
                          ->whereRaw('cf.cotacao_id = c.id')
                          ->whereNotNull('cf.valor_total')
                          ->where('cf.valor_total', '>', 0);
                    });
            } elseif ($filtroStatus == 'finalizada') {
                $query->where('c.status', 'finalizada');
            } elseif ($filtroStatus == 'rejeitada') {
                $query->whereIn('c.status', ['rejeitada', 'reprovada', 'cancelada']);
            }
            // Se for 'todas', não aplica filtro de status
            
            // Filtro por Centro de Custo
            $centroCustoId = $request->get('centro_custo_id');
            $centroCustoNome = null;
            if ($centroCustoId) {
                $query->where('cc.id', $centroCustoId);
                // Buscar nome do centro de custo para exibir no filtro
                $cc = DB::table('centros_custo')->where('id', $centroCustoId)->first();
                $centroCustoNome = $cc ? $cc->nome : null;
            }
            
            // Filtro por Urgência (do card "Urgentes")
            $filtroUrgencia = $request->get('filtro_urgencia');
            if ($filtroUrgencia === 'alta') {
                // Filtrar apenas cotações urgentes (data_limite nos próximos 3 dias) que não estão finalizadas
                $query->where('c.status', '!=', 'finalizada')
                      ->whereNotNull('c.data_limite')
                      ->whereRaw('c.data_limite <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)');
            }
            
            // Adicionar urgencia se a coluna existir, senão calcular pela data_limite
            if (\Schema::hasColumn('cotacoes', 'urgencia')) {
                $query->addSelect('c.urgencia');
            } else {
                $query->addSelect(DB::raw("CASE 
                    WHEN c.data_limite <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 'alta'
                    WHEN c.data_limite <= DATE_ADD(CURDATE(), INTERVAL 5 DAY) THEN 'media'
                    ELSE 'normal'
                END as urgencia"));
            }
            
            $solicitacoes = $query->orderBy('c.id', 'desc')->get();
            
            // Para cada solicitação, verificar se pode ser editada
            // Pode editar se: é o solicitante E não tem fornecedores cadastrados (cotação não iniciada)
            foreach ($solicitacoes as $sol) {
                $temFornecedores = DB::table('cotacao_fornecedores')
                    ->where('cotacao_id', $sol->id)
                    ->exists();
                
                $sol->pode_editar = ($sol->solicitante_id == $user->id) && !$temFornecedores && $sol->status == 'aberta';
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar solicitações: ' . $e->getMessage());
        }
        
        return view('suprimentos.solicitacao', compact('solicitacoes', 'isAdmin', 'canDelete', 'stats', 'centroCustoNome'));
    }

    public function cotacao(Request $request)
    {
        $fornecedores = collect([]);
        $isAdmin = false;
        $canDelete = false;
        $centroCustoNome = null;
        $stats = [
            'aguardando' => 0,
            'em_cotacao' => 0,
            'cotadas' => 0,
            'finalizadas' => 0,
            'total' => 0
        ];
        
        try {
            // Verificar se usuário é admin ou tem permissão de excluir
            $user = auth()->user();
            $isAdmin = $this->isAdmin($user);
            $canDelete = $this->canDeleteCotacao($user);
            
            // Estatísticas de cotações
            $stats['total'] = DB::table('cotacoes')->count();
            $stats['finalizadas'] = DB::table('cotacoes')->where('status', 'finalizada')->count();
            
            // Cotações abertas sem fornecedor cotado (aguardando cotação)
            $stats['aguardando'] = DB::table('cotacoes as c')
                ->where('c.status', 'aberta')
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('cotacao_fornecedores as cf')
                      ->whereRaw('cf.cotacao_id = c.id')
                      ->whereNotNull('cf.valor_total')
                      ->where('cf.valor_total', '>', 0);
                })
                ->count();
            
            // Cotações abertas com pelo menos um fornecedor cotado (em cotação / cotadas)
            $stats['cotadas'] = DB::table('cotacoes as c')
                ->where('c.status', 'aberta')
                ->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('cotacao_fornecedores as cf')
                      ->whereRaw('cf.cotacao_id = c.id')
                      ->whereNotNull('cf.valor_total')
                      ->where('cf.valor_total', '>', 0);
                })
                ->count();
            
            // Buscar cotações com centro de custo e endereço
            $query = DB::table('cotacoes as c')
                ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                ->select(
                    'c.*', 
                    'cc.nome as centro_custo_nome', 
                    'cc.endereco as cc_endereco', 
                    'os.cidade as os_cidade', 
                    'os.estado as os_estado',
                    DB::raw("CASE 
                        WHEN c.data_limite IS NULL THEN 'normal'
                        WHEN c.data_limite <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 'alta'
                        WHEN c.data_limite <= DATE_ADD(CURDATE(), INTERVAL 5 DAY) THEN 'media'
                        ELSE 'normal'
                    END as urgencia")
                )
                ->distinct();
            
            // Aplicar filtros
            if ($request->filled('busca')) {
                $busca = $request->busca;
                $query->where(function($q) use ($busca) {
                    $q->where('c.numero', 'like', "%{$busca}%")
                      ->orWhere('c.descricao', 'like', "%{$busca}%")
                      ->orWhere('cc.nome', 'like', "%{$busca}%");
                });
            }
            
            // Filtro por número da cotação
            if ($request->filled('busca_cotacao')) {
                $buscaCotacao = $request->busca_cotacao;
                $query->where('c.numero', 'like', "%{$buscaCotacao}%");
            }
            
            if ($request->filled('status')) {
                if ($request->status == 'rejeitada') {
                    // Buscar rejeitada e reprovada
                    $query->whereIn('c.status', ['rejeitada', 'reprovada']);
                } else {
                    $query->where('c.status', $request->status);
                }
            }
            
            // Filtro por Centro(s) de Custo (Obra) - Suporta múltiplos IDs separados por vírgula
            $centroCustoNome = null;
            if ($request->filled('centro_custo_ids')) {
                $centroIds = array_filter(explode(',', $request->centro_custo_ids));
                if (!empty($centroIds)) {
                    $query->whereIn('os.centro_custo_id', $centroIds);
                    // Buscar nomes dos centros de custo para exibir no filtro
                    $ccFiltros = DB::table('centros_custo')->whereIn('id', $centroIds)->pluck('nome')->toArray();
                    $centroCustoNome = implode(', ', $ccFiltros);
                }
            }
            // Manter compatibilidade com filtro antigo (single)
            elseif ($request->filled('centro_custo_id')) {
                $query->where('os.centro_custo_id', $request->centro_custo_id);
                $ccFiltro = DB::table('centros_custo')->where('id', $request->centro_custo_id)->first();
                $centroCustoNome = $ccFiltro ? $ccFiltro->nome : null;
            }
            
            // Filtro por Data Inicial
            if ($request->filled('data_inicial')) {
                $query->whereDate('c.created_at', '>=', $request->data_inicial);
            }
            
            // Filtro por Data Final
            if ($request->filled('data_final')) {
                $query->whereDate('c.created_at', '<=', $request->data_final);
            }
            
            // Filtro por Solicitante
            if ($request->filled('solicitante_id')) {
                $query->where('c.solicitante_id', $request->solicitante_id);
            }
            
            // Filtro por Urgência
            if ($request->filled('urgencia')) {
                $urgencia = $request->urgencia;
                if ($urgencia === 'alta') {
                    $query->whereNotNull('c.data_limite')
                          ->whereRaw('c.data_limite <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)');
                } elseif ($urgencia === 'media') {
                    $query->whereNotNull('c.data_limite')
                          ->whereRaw('c.data_limite > DATE_ADD(CURDATE(), INTERVAL 3 DAY)')
                          ->whereRaw('c.data_limite <= DATE_ADD(CURDATE(), INTERVAL 5 DAY)');
                } elseif ($urgencia === 'normal') {
                    $query->where(function($q) {
                        $q->whereNull('c.data_limite')
                          ->orWhereRaw('c.data_limite > DATE_ADD(CURDATE(), INTERVAL 5 DAY)');
                    });
                }
            }
            
            // Ordenação: abertas primeiro, depois por ID decrescente (número sequencial)
            $query->orderByRaw("CASE WHEN c.status = 'aberta' THEN 0 WHEN c.status = 'finalizada' THEN 1 ELSE 2 END")
                  ->orderBy('c.id', 'desc');
            
            // Paginação (10 por página)
            $cotacoes = $query->paginate(10)->withQueryString();
            
            // Verificar se coluna arquivo_orcamento existe
            $hasArquivoOrcamento = \Schema::hasColumn('cotacao_fornecedores', 'arquivo_orcamento');
            
            // Para cada cotação, buscar TODOS os fornecedores cotados (não apenas o menor preço)
            foreach ($cotacoes as $c) {
                // Extrair cidade/UF do endereço do centro de custo (formato: "RUA..., CIDADE/UF")
                $c->municipio_uf = null;
                if (!empty($c->cc_endereco)) {
                    // Tentar extrair "CIDADE/UF" do final do endereço
                    if (preg_match('/([A-ZÁÉÍÓÚÂÊÔÃÕÇ\s]+)\/([A-Z]{2})\s*$/i', $c->cc_endereco, $matches)) {
                        $c->municipio_uf = trim($matches[1]) . '/' . strtoupper($matches[2]);
                    }
                }
                // Se não encontrou no endereço, usar cidade/estado da O.S.
                if (empty($c->municipio_uf) && ($c->os_cidade || $c->os_estado)) {
                    $c->municipio_uf = ($c->os_cidade ?? '') . ($c->os_cidade && $c->os_estado ? '/' : '') . ($c->os_estado ?? '');
                }
                
                // Buscar todos os fornecedores com valor
                $fornecedoresCotados = DB::table('cotacao_fornecedores as cf')
                    ->leftJoin('fornecedores as f', 'cf.fornecedor_id', '=', 'f.id')
                    ->where('cf.cotacao_id', $c->id)
                    ->whereNotNull('cf.valor_total')
                    ->where('cf.valor_total', '>', 0)
                    ->orderBy('cf.valor_total', 'asc')
                    ->select('f.razao_social', 'cf.valor_total')
                    ->get();
                
                $qtdFornecedores = $fornecedoresCotados->count();
                $valorTotal = $fornecedoresCotados->sum('valor_total');
                
                if ($qtdFornecedores > 1) {
                    // Múltiplos fornecedores - mostrar quantidade e valor total
                    $c->fornecedor_vencedor = $qtdFornecedores . ' fornecedores';
                    $c->valor_vencedor = $valorTotal;
                    $c->multiplos_fornecedores = true;
                    $c->qtd_fornecedores = $qtdFornecedores;
                } else if ($qtdFornecedores == 1) {
                    // Apenas 1 fornecedor
                    $primeiro = $fornecedoresCotados->first();
                    $c->fornecedor_vencedor = $primeiro->razao_social;
                    $c->valor_vencedor = $primeiro->valor_total;
                    $c->multiplos_fornecedores = false;
                    $c->qtd_fornecedores = 1;
                } else {
                    $c->fornecedor_vencedor = null;
                    $c->valor_vencedor = null;
                    $c->multiplos_fornecedores = false;
                    $c->qtd_fornecedores = 0;
                }
                
                // Buscar arquivos de orçamento desta cotação
                $c->arquivos_orcamento = [];
                if ($hasArquivoOrcamento) {
                    $arquivos = DB::table('cotacao_fornecedores')
                        ->where('cotacao_id', $c->id)
                        ->whereNotNull('arquivo_orcamento')
                        ->where('arquivo_orcamento', '!=', '')
                        ->pluck('arquivo_orcamento')
                        ->toArray();
                    $c->arquivos_orcamento = $arquivos;
                }
                
                // Buscar status das OCs vinculadas (autorização e pagamento) com detalhes do fornecedor
                $ocsVinculadas = DB::table('ordens_compra as oc')
                    ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                    ->where('oc.cotacao_id', $c->id)
                    ->select('oc.id', 'oc.numero', 'oc.status', 'oc.status_pagamento', 'oc.valor_total', 'f.razao_social as fornecedor')
                    ->get();
                
                $c->qtd_ocs = $ocsVinculadas->count();
                $c->ocs_detalhes = $ocsVinculadas; // Guardar detalhes das OCs
                
                if ($ocsVinculadas->count() > 0) {
                    // Status de Autorização
                    $ocsAprovadas = $ocsVinculadas->whereIn('status', ['aprovada', 'enviada', 'recebida', 'recebida_parcial'])->count();
                    $ocsPendentes = $ocsVinculadas->where('status', 'pendente')->count();
                    
                    $c->qtd_ocs_aprovadas = $ocsAprovadas;
                    $c->qtd_ocs_pendentes = $ocsPendentes;
                    
                    if ($ocsPendentes == 0 && $ocsAprovadas == $ocsVinculadas->count()) {
                        $c->status_autorizacao = 'aprovado';
                    } else if ($ocsAprovadas > 0) {
                        $c->status_autorizacao = 'parcial';
                    } else {
                        $c->status_autorizacao = 'pendente';
                    }
                    
                    // Status de Pagamento
                    $ocsPagas = $ocsVinculadas->where('status_pagamento', 'pago')->count();
                    $ocsAguardando = $ocsVinculadas->whereIn('status_pagamento', ['aguardando_pagamento', 'pendente', null])->count();
                    
                    $c->qtd_ocs_pagas = $ocsPagas;
                    
                    if ($ocsAguardando == 0 && $ocsPagas == $ocsVinculadas->count()) {
                        $c->status_pagamento = 'pago';
                    } else if ($ocsPagas > 0) {
                        $c->status_pagamento = 'parcial';
                    } else {
                        $c->status_pagamento = 'pendente';
                    }
                } else {
                    $c->status_autorizacao = null;
                    $c->status_pagamento = null;
                    $c->qtd_ocs_aprovadas = 0;
                    $c->qtd_ocs_pendentes = 0;
                    $c->qtd_ocs_pagas = 0;
                }
            }
            
            $fornecedores = DB::table('fornecedores')->where('ativo', 1)->orderBy('razao_social')->get();
            
            // Buscar centros de custo para o filtro
            $centrosCusto = DB::table('centros_custo')->orderBy('nome')->get();
            
            // Buscar solicitantes (usuários que já fizeram cotações)
            $solicitantes = DB::table('users as u')
                ->join('cotacoes as c', 'c.solicitante_id', '=', 'u.id')
                ->select('u.id', 'u.name')
                ->distinct()
                ->orderBy('u.name')
                ->get();
                
        } catch (\Exception $e) {
            $cotacoes = collect([])->paginate(10);
            $centrosCusto = collect([]);
            $solicitantes = collect([]);
        }
        
        return view('suprimentos.cotacao', compact('cotacoes', 'fornecedores', 'isAdmin', 'canDelete', 'stats', 'centrosCusto', 'solicitantes', 'centroCustoNome'));
    }

    public function ordemCompra()
    {
        $ordens = collect([]);
        $fornecedores = collect([]);
        $cotacoesFinalizadas = collect([]);
        $centrosCusto = collect([]);
        $isAdmin = false;
        
        try {
            // Verificar se usuário é admin
            $user = auth()->user();
            $isAdmin = $this->isAdmin($user);
            
            // Buscar ordens de compra
            $ordens = DB::table('ordens_compra as oc')
                ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                ->leftJoin('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
                ->select('oc.*', 'f.razao_social as fornecedor', 'c.numero as cotacao_numero')
                ->orderBy('oc.data_emissao', 'desc')
                ->get();
            
            $fornecedores = DB::table('fornecedores')->where('ativo', 1)->orderBy('razao_social')->get();
            
            // Buscar centros de custo (obras)
            $centrosCusto = DB::table('centros_custo')->where('ativo', 1)->orderBy('nome')->get();
            
            // Buscar cotações finalizadas que ainda não geraram OC
            $cotacoesFinalizadas = DB::table('cotacoes')
                ->where('status', 'finalizada')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('ordens_compra')
                          ->whereRaw('ordens_compra.cotacao_id = cotacoes.id');
                })
                ->orderBy('data_solicitacao', 'desc')
                ->get();
            
            // Para cada cotação, buscar o fornecedor vencedor (menor valor)
            foreach ($cotacoesFinalizadas as $c) {
                $vencedor = DB::table('cotacao_fornecedores as cf')
                    ->leftJoin('fornecedores as f', 'cf.fornecedor_id', '=', 'f.id')
                    ->where('cf.cotacao_id', $c->id)
                    ->whereNotNull('cf.valor_total')
                    ->where('cf.valor_total', '>', 0)
                    ->orderBy('cf.valor_total', 'asc')
                    ->select('f.id as fornecedor_id', 'f.razao_social', 'cf.valor_total', 'cf.prazo_entrega')
                    ->first();
                
                $c->fornecedor_vencedor = $vencedor ? $vencedor->razao_social : null;
                $c->fornecedor_vencedor_id = $vencedor ? $vencedor->fornecedor_id : null;
                $c->valor_vencedor = $vencedor ? $vencedor->valor_total : null;
                $c->prazo_vencedor = $vencedor ? $vencedor->prazo_entrega : null;
            }
        } catch (\Exception $e) {
            \Log::error('Erro ordemCompra: ' . $e->getMessage());
        }
        
        return view('suprimentos.ordem-compra', compact('ordens', 'fornecedores', 'cotacoesFinalizadas', 'centrosCusto', 'isAdmin'));
    }

    public function recebimento()
    {
        $recebimentos = collect([]);
        $ordensAbertas = collect([]);
        $ordensAguardandoPagamento = collect([]);
        try {
            $recebimentos = DB::table('recebimentos as r')
                ->leftJoin('ordens_compra as oc', 'r.ordem_compra_id', '=', 'oc.id')
                ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                ->leftJoin('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
                ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                ->select('r.*', 'oc.numero as ordem_numero', 'f.razao_social as fornecedor', 'cc.nome as centro_custo')
                ->orderBy('r.data_recebimento', 'desc')
                ->get();
            
            // Verificar se a coluna status_pagamento existe
            $temColunaPagamento = false;
            try {
                $colunas = DB::select("SHOW COLUMNS FROM ordens_compra LIKE 'status_pagamento'");
                $temColunaPagamento = count($colunas) > 0;
            } catch (\Exception $e) {}
            
            if ($temColunaPagamento) {
                // OCs liberadas para recebimento (pagamento feito)
                $ordensAbertas = DB::table('ordens_compra as oc')
                    ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                    ->leftJoin('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
                    ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                    ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                    ->whereIn('oc.status', ['enviada', 'recebida_parcial', 'aprovada', 'pendente'])
                    ->where('oc.status_pagamento', 'pago')
                    ->select('oc.*', 'f.razao_social as fornecedor', 'cc.nome as centro_custo')
                    ->orderBy('numero')
                    ->get();
                
                // OCs aguardando pagamento do financeiro
                $ordensAguardandoPagamento = DB::table('ordens_compra as oc')
                    ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                    ->leftJoin('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
                    ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                    ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                    ->whereIn('oc.status', ['enviada', 'recebida_parcial', 'aprovada', 'pendente'])
                    ->where(function($q) {
                        $q->where('oc.status_pagamento', 'aguardando_pagamento')
                          ->orWhereNull('oc.status_pagamento');
                    })
                    ->select('oc.*', 'f.razao_social as fornecedor', 'cc.nome as centro_custo')
                    ->orderBy('numero')
                    ->get();
            } else {
                // Fallback: se não tem coluna de pagamento, mostra todas
                $ordensAbertas = DB::table('ordens_compra as oc')
                    ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                    ->leftJoin('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
                    ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                    ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                    ->whereIn('oc.status', ['enviada', 'recebida_parcial', 'aprovada', 'pendente'])
                    ->select('oc.*', 'f.razao_social as fornecedor', 'cc.nome as centro_custo')
                    ->orderBy('numero')
                    ->get();
            }
        } catch (\Exception $e) {
            \Log::error('Erro recebimento: ' . $e->getMessage());
        }
        
        return view('suprimentos.recebimento', compact('recebimentos', 'ordensAbertas', 'ordensAguardandoPagamento'));
    }

    public function nfEntrada()
    {
        $notas = collect([]);
        $fornecedores = collect([]);
        try {
            $notas = DB::table('nf_entrada as nf')
                ->leftJoin('fornecedores as f', 'nf.fornecedor_id', '=', 'f.id')
                ->leftJoin('ordens_compra as oc', 'nf.ordem_compra_id', '=', 'oc.id')
                ->select('nf.*', 'f.razao_social as fornecedor', 'oc.numero as ordem_numero')
                ->orderBy('nf.data_entrada', 'desc')
                ->get();
            $fornecedores = DB::table('fornecedores')->where('ativo', 1)->orderBy('razao_social')->get();
        } catch (\Exception $e) {}
        
        return view('suprimentos.nf-entrada', compact('notas', 'fornecedores'));
    }

    public function valeRetirada()
    {
        $vales = collect([]);
        try {
            $vales = DB::table('vales_retirada')
                ->orderBy('data_retirada', 'desc')
                ->get();
        } catch (\Exception $e) {}
        
        return view('suprimentos.vale-retirada', compact('vales'));
    }
    
    // ============================================
    // CORRIGIR COTAÇÕES SEM FORNECEDORES
    // ============================================
    public function corrigirCotacoes()
    {
        try {
            // Pegar todos os fornecedores ativos
            $fornecedores = DB::table('fornecedores')->where('ativo', 1)->pluck('id');
            
            if ($fornecedores->count() === 0) {
                return response()->json(['success' => false, 'message' => 'Nenhum fornecedor cadastrado!']);
            }
            
            // Pegar cotações sem fornecedores
            $cotacoesSemFornecedores = DB::table('cotacoes')
                ->whereRaw('id NOT IN (SELECT DISTINCT cotacao_id FROM cotacao_fornecedores)')
                ->pluck('id');
            
            $inseridos = 0;
            foreach ($cotacoesSemFornecedores as $cotacaoId) {
                // Inserir os 3 primeiros fornecedores
                $fCount = 0;
                foreach ($fornecedores as $fornecedorId) {
                    if ($fCount >= 3) break;
                    
                    $existe = DB::table('cotacao_fornecedores')
                        ->where('cotacao_id', $cotacaoId)
                        ->where('fornecedor_id', $fornecedorId)
                        ->exists();
                    
                    if (!$existe) {
                        // Inserir apenas as colunas que existem na tabela
                        DB::table('cotacao_fornecedores')->insert([
                            'cotacao_id' => $cotacaoId,
                            'fornecedor_id' => $fornecedorId,
                            'cotado_por' => auth()->id(),
                            'cotado_em' => now(),
                        ]);
                        $inseridos++;
                    }
                    $fCount++;
                }
            }
            
            return response()->json([
                'success' => true, 
                'message' => "Corrigido! $inseridos vínculos de fornecedores criados para " . $cotacoesSemFornecedores->count() . " cotações."
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // ============================================
    // APIs - ESTOQUE
    // ============================================
    
    public function buscarProdutosEstoque(Request $request)
    {
        $termo = $request->get('termo', '');
        
        if (strlen($termo) < 3) {
            return response()->json([]);
        }
        
        $produtos = DB::table('estoque')
            ->where(function($q) use ($termo) {
                $q->where('nome', 'like', '%' . $termo . '%')
                  ->orWhere('descricao', 'like', '%' . $termo . '%')
                  ->orWhere('codigo_barras', 'like', '%' . $termo . '%');
            })
            ->orderBy('nome')
            ->limit(20)
            ->get(['id', 'nome', 'descricao', 'unidade', 'quantidade']);
        
        return response()->json($produtos);
    }
    
    // ============================================
    // APIs - FORNECEDORES
    // ============================================
    
    public function buscarFornecedores(Request $request)
    {
        $termo = $request->get('termo', '');
        
        if (strlen($termo) < 3) {
            return response()->json([]);
        }
        
        $like = '%'.$termo.'%';

        $fornecedores = DB::table('fornecedores')
            ->where('ativo', 1)
            ->where(function ($q) use ($like) {
                $q->where('razao_social', 'like', $like)
                    ->orWhere('nome_fantasia', 'like', $like)
                    ->orWhere('cnpj', 'like', $like);
            })
            ->orderBy('razao_social')
            ->limit(30)
            ->get(['id', 'razao_social', 'nome_fantasia', 'cnpj']);
        
        return response()->json($fornecedores);
    }
    
    public function storeFornecedor(Request $request)
    {
        try {
            $id = DB::table('fornecedores')->insertGetId([
                'razao_social' => $request->razao_social,
                'nome_fantasia' => $request->nome_fantasia,
                'cnpj' => $request->cnpj,
                'telefone' => $request->telefone,
                'email' => $request->email,
                'endereco' => $request->endereco,
                'cidade' => $request->cidade,
                'uf' => $request->uf,
                'observacoes' => $request->observacoes,
                'ativo' => 1,
                'created_at' => now(),
            ]);
            return response()->json(['success' => true, 'id' => $id, 'message' => 'Fornecedor cadastrado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage()], 500);
        }
    }
    
    public function getFornecedor($id)
    {
        $fornecedor = DB::table('fornecedores')->where('id', $id)->first();
        return response()->json($fornecedor);
    }
    
    public function updateFornecedor(Request $request, $id)
    {
        try {
            DB::table('fornecedores')->where('id', $id)->update([
                'razao_social' => $request->razao_social,
                'nome_fantasia' => $request->nome_fantasia,
                'cnpj' => $request->cnpj,
                'telefone' => $request->telefone,
                'email' => $request->email,
                'endereco' => $request->endereco,
                'cidade' => $request->cidade,
                'uf' => $request->uf,
                'observacoes' => $request->observacoes,
                'ativo' => $request->ativo ?? 1,
                'updated_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Fornecedor atualizado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()], 500);
        }
    }
    
    public function deleteFornecedor($id)
    {
        try {
            DB::table('fornecedores')->where('id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Fornecedor excluído com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()], 500);
        }
    }
    
    // ============================================
    // APIs - SOLICITAÇÕES
    // ============================================
    
    public function storeSolicitacao(Request $request)
    {
        try {
            // Coletar itens
            $itens = $request->itens ?? [];
            if (empty($itens)) {
                return response()->json(['success' => false, 'message' => 'Adicione pelo menos 1 item.'], 400);
            }
            
            // =============================================
            // CRIAR COTAÇÃO DIRETAMENTE (sem tabela solicitacoes)
            // =============================================
            $ultimaCotacao = DB::table('cotacoes')->orderBy('id', 'desc')->first();
            $proximoNumero = 1;
            if ($ultimaCotacao && isset($ultimaCotacao->numero)) {
                preg_match('/(\d+)$/', $ultimaCotacao->numero, $matches);
                if (!empty($matches[1])) {
                    $proximoNumero = intval($matches[1]) + 1;
                }
            }
            $numeroCotacao = 'COT-' . date('Y') . '-' . str_pad($proximoNumero, 3, '0', STR_PAD_LEFT);
            
            // Definir data limite baseada na urgência
            $diasLimite = 7; // normal
            if ($request->urgencia === 'media') $diasLimite = 5;
            if ($request->urgencia === 'alta') $diasLimite = 3;
            
            $cotacaoData = [
                'numero' => $numeroCotacao,
                'descricao' => $request->descricao,
                'solicitante_id' => auth()->id(),
                'data_solicitacao' => now()->format('Y-m-d'),
                'data_limite' => now()->addDays($diasLimite)->format('Y-m-d'),
                'status' => 'aberta',
                'observacoes' => $request->justificativa,
                'created_at' => now(),
            ];
            
            // Adicionar urgencia se a coluna existir
            if (\Schema::hasColumn('cotacoes', 'urgencia')) {
                $cotacaoData['urgencia'] = $request->urgencia ?? 'normal';
            }
            
            $cotacaoId = DB::table('cotacoes')->insertGetId($cotacaoData);
            
            // Inserir itens da cotação
            foreach ($itens as $item) {
                DB::table('cotacao_itens')->insert([
                    'cotacao_id' => $cotacaoId,
                    'produto' => $item['descricao'],
                    'quantidade' => $item['quantidade'],
                    'unidade' => $item['unidade'] ?? 'UN',
                    'created_at' => now(),
                ]);
            }
            
            // Registrar log de criação da cotação (via solicitação)
            $cotacaoCriada = DB::table('cotacoes')->where('id', $cotacaoId)->first();
            $itensCriados = DB::table('cotacao_itens')->where('cotacao_id', $cotacaoId)->get();
            $this->registrarLogCotacao('criacao', $cotacaoCriada, auth()->user(), $itensCriados, collect([]));
            
            return response()->json([
                'success' => true, 
                'cotacao_id' => $cotacaoId, 
                'cotacao_numero' => $numeroCotacao,
                'message' => 'Cotação criada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao criar: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Cria uma cotação vinculada diretamente a uma O.S. existente
     * O fluxo completo (cotação → OC → pagamento → recebimento) ficará atrelado à O.S.
     */
    public function storeSolicitacaoVinculadaOS(Request $request)
    {
        try {
            $osId = $request->ordem_servico_id;
            $numeroOS = $request->numero_os;
            
            if (!$osId || !$numeroOS) {
                return response()->json(['success' => false, 'message' => 'Ordem de Serviço não informada.'], 400);
            }
            
            // Verificar se a O.S. existe
            $os = DB::table('ordens_servico')->where('id', $osId)->first();
            if (!$os) {
                return response()->json(['success' => false, 'message' => 'Ordem de Serviço não encontrada.'], 404);
            }
            
            // Coletar itens
            $itens = $request->itens ?? [];
            if (empty($itens)) {
                return response()->json(['success' => false, 'message' => 'Adicione pelo menos 1 item.'], 400);
            }
            
            // =============================================
            // CRIAR COTAÇÃO DIRETAMENTE VINCULADA À O.S.
            // =============================================
            $ultimaCotacao = DB::table('cotacoes')->orderBy('id', 'desc')->first();
            $proximoNumero = 1;
            if ($ultimaCotacao && isset($ultimaCotacao->numero)) {
                preg_match('/(\d+)$/', $ultimaCotacao->numero, $matches);
                if (!empty($matches[1])) {
                    $proximoNumero = intval($matches[1]) + 1;
                }
            }
            $numeroCotacao = 'COT-' . date('Y') . '-' . str_pad($proximoNumero, 3, '0', STR_PAD_LEFT);
            
            // Definir data limite baseada na urgência
            $diasLimite = 7;
            if ($request->urgencia === 'media') $diasLimite = 5;
            if ($request->urgencia === 'alta') $diasLimite = 3;
            
            $cotacaoData = [
                'numero' => $numeroCotacao,
                'descricao' => $request->descricao ?: "Solicitação via O.S. #{$numeroOS}",
                'solicitante_id' => auth()->id(),
                'data_solicitacao' => now()->format('Y-m-d'),
                'data_limite' => now()->addDays($diasLimite)->format('Y-m-d'),
                'status' => 'aberta',
                'observacoes' => $request->justificativa,
                'created_at' => now(),
            ];
            
            // Adicionar urgencia se a coluna existir
            if (\Schema::hasColumn('cotacoes', 'urgencia')) {
                $cotacaoData['urgencia'] = $request->urgencia ?? 'normal';
            }
            
            // Adicionar ordem_servico_id se a coluna existir
            if (\Schema::hasColumn('cotacoes', 'ordem_servico_id')) {
                $cotacaoData['ordem_servico_id'] = $osId;
            }
            
            // Adicionar centro_custo_id se existir
            if (\Schema::hasColumn('cotacoes', 'centro_custo_id')) {
                $cotacaoData['centro_custo_id'] = $request->centro_custo_id ?: ($os->centro_custo_id ?? null);
            }
            
            $cotacaoId = DB::table('cotacoes')->insertGetId($cotacaoData);
            
            // Inserir itens da cotação
            foreach ($itens as $item) {
                $itemData = [
                    'cotacao_id' => $cotacaoId,
                    'produto' => $item['descricao'],
                    'quantidade' => $item['quantidade'],
                    'unidade' => $item['unidade'] ?? 'UN',
                    'created_at' => now(),
                ];
                
                // Marcar origem como O.S.
                if (\Schema::hasColumn('cotacao_itens', 'origem')) {
                    $itemData['origem'] = 'os';
                }
                if (\Schema::hasColumn('cotacao_itens', 'origem_id')) {
                    $itemData['origem_id'] = $osId;
                }
                
                DB::table('cotacao_itens')->insert($itemData);
            }
            
            // Registrar log de criação da cotação (via O.S.)
            $cotacaoCriada = DB::table('cotacoes')->where('id', $cotacaoId)->first();
            $itensCriados = DB::table('cotacao_itens')->where('cotacao_id', $cotacaoId)->get();
            $this->registrarLogCotacao('criacao_via_os', $cotacaoCriada, auth()->user(), $itensCriados, collect([]));
            
            return response()->json([
                'success' => true, 
                'cotacao_id' => $cotacaoId, 
                'cotacao_numero' => $numeroCotacao,
                'message' => "Cotação {$numeroCotacao} criada e vinculada à O.S. {$numeroOS}!"
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar cotação vinculada a O.S.: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao criar: ' . $e->getMessage()], 500);
        }
    }
    
    public function getSolicitacao($id)
    {
        try {
            // Buscar na tabela cotacoes (solicitacoes não existe mais)
            $solicitacao = DB::table('cotacoes as c')
                ->leftJoin('users as u', 'c.solicitante_id', '=', 'u.id')
                ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                ->where('c.id', $id)
                ->select(
                    'c.id',
                    'c.numero',
                    'c.descricao',
                    'c.observacoes as justificativa',
                    'c.data_solicitacao',
                    'c.data_limite',
                    'c.status',
                    'c.created_at',
                    'c.updated_at',
                    'u.name as solicitante', 
                    'cc.nome as centro_custo',
                    DB::raw("CASE 
                        WHEN c.data_limite <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 'alta'
                        WHEN c.data_limite <= DATE_ADD(CURDATE(), INTERVAL 5 DAY) THEN 'media'
                        ELSE 'normal'
                    END as urgencia")
                )
                ->first();
            
            if (!$solicitacao) {
                return response()->json(['success' => false, 'message' => 'Solicitação não encontrada!'], 404);
            }

            // Justificativa = observações da cotação (visível para todos que acessam o detalhe)
            $observacoes = DB::table('cotacoes')->where('id', $id)->value('observacoes');
            $solicitacao->justificativa = $observacoes;
            $solicitacao->observacoes = $observacoes;
            
            // Buscar itens na tabela cotacao_itens
            $itens = DB::table('cotacao_itens')
                ->where('cotacao_id', $id)
                ->select('id', 'produto as descricao', 'quantidade', 'unidade', DB::raw("'' as observacao"))
                ->get();
            
            return response()->json(['success' => true, 'solicitacao' => $solicitacao, 'itens' => $itens]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar solicitação: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    public function updateSolicitacao(Request $request, $id)
    {
        try {
            // Verificar se a cotação pode ser editada (não tem fornecedores cadastrados)
            $temFornecedores = DB::table('cotacao_fornecedores')->where('cotacao_id', $id)->exists();
            if ($temFornecedores) {
                return response()->json(['success' => false, 'message' => 'Esta solicitação já está em cotação e não pode ser editada.'], 403);
            }
            
            // Verificar se o usuário é o solicitante
            $cotacao = DB::table('cotacoes')->where('id', $id)->first();
            if (!$cotacao || $cotacao->solicitante_id != auth()->id()) {
                return response()->json(['success' => false, 'message' => 'Você não tem permissão para editar esta solicitação.'], 403);
            }
            
            // Atualizar cotação (que funciona como solicitação)
            DB::table('cotacoes')->where('id', $id)->update([
                'descricao' => $request->descricao,
                'updated_at' => now(),
            ]);
            
            // Atualizar itens - remover antigos e inserir novos
            DB::table('cotacao_itens')->where('cotacao_id', $id)->delete();
            
            if ($request->has('itens')) {
                foreach ($request->itens as $item) {
                    DB::table('cotacao_itens')->insert([
                        'cotacao_id' => $id,
                        'produto' => $item['descricao'],
                        'quantidade' => $item['quantidade'],
                        'unidade' => $item['unidade'] ?? 'UN',
                        'created_at' => now(),
                    ]);
                }
            }
            
            return response()->json(['success' => true, 'message' => 'Solicitação atualizada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()], 500);
        }
    }
    
    public function deleteSolicitacao(Request $request, $id)
    {
        try {
            // Verificar se é admin OU tem permissão ex_cot
            if (!$this->canDeleteCotacao(auth()->user())) {
                return response()->json(['success' => false, 'message' => 'Você não tem permissão para rejeitar.'], 403);
            }
            
            // Buscar dados da cotação
            $cotacao = DB::table('cotacoes')->where('id', $id)->first();
            
            if (!$cotacao) {
                return response()->json(['success' => false, 'message' => 'Solicitação/Cotação não encontrada.'], 404);
            }
            
            // Verificar se o motivo foi informado
            $motivo = $request->input('motivo');
            if (empty($motivo) || strlen(trim($motivo)) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'É obrigatório informar o motivo da rejeição (mínimo 10 caracteres).'
                ], 400);
            }
            
            // REJEITAR a solicitação/cotação - apenas muda o status, NÃO exclui nada
            DB::table('cotacoes')->where('id', $id)->update([
                'status' => 'rejeitada',
                'motivo_cancelamento' => trim($motivo),
                'cancelado_por' => auth()->id(),
                'cancelado_em' => now(),
                'updated_at' => now()
            ]);
            
            // Registrar log de rejeição
            $this->registrarLogExclusao('cotacoes_rejeicao', $id, $cotacao->numero, 'Motivo: ' . $motivo);
            
            return response()->json(['success' => true, 'message' => 'Solicitação rejeitada com sucesso! O histórico foi mantido.']);
        } catch (\Exception $e) {
            \Log::error('Erro ao rejeitar solicitação: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao rejeitar: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Registrar log de exclusão
     */
    private function registrarLogExclusao($tabela, $registroId, $numero, $descricao)
    {
        try {
            // Verificar se a tabela de logs existe
            if (!\Schema::hasTable('logs_exclusao')) {
                // Se não existir, apenas registra no log do Laravel
                \Log::warning("EXCLUSÃO: Tabela={$tabela}, ID={$registroId}, Número={$numero}, Descrição={$descricao}, Usuário=" . auth()->user()->name . " (ID: " . auth()->id() . "), Data=" . now());
                return;
            }
            
            DB::table('logs_exclusao')->insert([
                'tabela' => $tabela,
                'registro_id' => $registroId,
                'numero' => $numero,
                'descricao' => $descricao,
                'usuario_id' => auth()->id(),
                'usuario_nome' => auth()->user()->name,
                'ip' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Se falhar, registra no log do Laravel
            \Log::warning("EXCLUSÃO: Tabela={$tabela}, ID={$registroId}, Número={$numero}, Descrição={$descricao}, Usuário=" . auth()->user()->name . " (ID: " . auth()->id() . "), Data=" . now());
        }
    }
    
    public function aprovarSolicitacao(Request $request, $id)
    {
        try {
            DB::table('solicitacoes')->where('id', $id)->update([
                'status' => 'aprovada',
                'aprovado_por' => auth()->id(),
                'data_aprovacao' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Solicitação aprovada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao aprovar: ' . $e->getMessage()], 500);
        }
    }
    
    public function reprovarSolicitacao(Request $request, $id)
    {
        try {
            DB::table('solicitacoes')->where('id', $id)->update([
                'status' => 'reprovada',
                'aprovado_por' => auth()->id(),
                'data_aprovacao' => now(),
                'motivo_reprovacao' => $request->motivo,
                'updated_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Solicitação reprovada!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao reprovar: ' . $e->getMessage()], 500);
        }
    }
    
    public function gerarCotacaoFromSolicitacao(Request $request, $id)
    {
        try {
            // Buscar a solicitação
            $solicitacao = DB::table('solicitacoes')->where('id', $id)->first();
            
            if (!$solicitacao) {
                return response()->json(['success' => false, 'message' => 'Solicitação não encontrada!'], 404);
            }
            
            if ($solicitacao->status !== 'aprovada') {
                return response()->json(['success' => false, 'message' => 'Somente solicitações aprovadas podem gerar cotação!'], 400);
            }
            
            // Buscar itens da solicitação
            $itens = DB::table('solicitacao_itens')->where('solicitacao_id', $id)->get();
            
            if ($itens->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Solicitação não possui itens!'], 400);
            }
            
            // Gerar número da cotação
            $numero = 'COT-' . date('Y') . '-' . str_pad(DB::table('cotacoes')->count() + 1, 3, '0', STR_PAD_LEFT);
            
            // Criar a cotação
            $cotacaoId = DB::table('cotacoes')->insertGetId([
                'numero' => $numero,
                'descricao' => $solicitacao->descricao,
                'solicitacao_id' => $id,
                'data_solicitacao' => now()->format('Y-m-d'),
                'data_limite' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'aberta',
                'created_at' => now(),
            ]);
            
            // Copiar itens da solicitação para a cotação
            foreach ($itens as $item) {
                DB::table('cotacao_itens')->insert([
                    'cotacao_id' => $cotacaoId,
                    'produto' => $item->descricao,
                    'quantidade' => $item->quantidade,
                    'unidade' => $item->unidade,
                    'created_at' => now(),
                ]);
            }
            
            // Atualizar status da solicitação
            DB::table('solicitacoes')->where('id', $id)->update([
                'status' => 'em_cotacao',
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Cotação gerada com sucesso!',
                'cotacao_id' => $cotacaoId,
                'cotacao_numero' => $numero
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao gerar cotação: ' . $e->getMessage()], 500);
        }
    }
    
    // ============================================
    // APIs - COTAÇÕES
    // ============================================
    
    public function storeCotacao(Request $request)
    {
        try {
            $numero = 'COT-' . date('Y') . '-' . str_pad(DB::table('cotacoes')->count() + 1, 3, '0', STR_PAD_LEFT);
            
            // Verificar se tem pelo menos um valor de fornecedor
            $temValor = false;
            if ($request->fornecedores) {
                foreach ($request->fornecedores as $f) {
                    if (!empty($f['valor']) && floatval(str_replace(',', '.', str_replace('.', '', $f['valor']))) > 0) {
                        $temValor = true;
                        break;
                    }
                }
            }
            
            $id = DB::table('cotacoes')->insertGetId([
                'numero' => $numero,
                'descricao' => $request->descricao,
                'data_solicitacao' => now()->format('Y-m-d'),
                'data_limite' => $request->data_limite,
                'status' => $temValor ? 'finalizada' : 'aberta',
                'created_at' => now(),
            ]);
            
            // Inserir itens da cotação
            if ($request->itens) {
                foreach ($request->itens as $item) {
                    if (!empty($item['produto'])) {
                        DB::table('cotacao_itens')->insert([
                            'cotacao_id' => $id,
                            'produto' => $item['produto'],
                            'quantidade' => $item['quantidade'] ?? 1,
                            'unidade' => $item['unidade'] ?? 'UN',
                            'created_at' => now(),
                        ]);
                    }
                }
            }
            
            // Verificar se a coluna arquivo_orcamento existe
            $hasArquivoOrcamento = \Schema::hasColumn('cotacao_fornecedores', 'arquivo_orcamento');
            
            // Inserir fornecedores COM valores (novo formato simplificado)
            if ($request->fornecedores) {
                foreach ($request->fornecedores as $index => $f) {
                    if (!empty($f['id'])) {
                        $valor = null;
                        if (!empty($f['valor'])) {
                            $valor = floatval(str_replace(',', '.', str_replace('.', '', $f['valor'])));
                        }
                        
                        $insertData = [
                            'cotacao_id' => $id,
                            'fornecedor_id' => $f['id'],
                            'valor_total' => $valor,
                            'prazo_entrega' => $f['prazo'] ?? null,
                            'cotado_por' => auth()->id(),
                            'cotado_em' => now(),
                        ];
                        
                        // Verificar se há arquivo de orçamento para este fornecedor
                        if ($hasArquivoOrcamento && $request->hasFile("fornecedores.{$index}.orcamento")) {
                            $arquivo = $request->file("fornecedores.{$index}.orcamento");
                            $nomeArquivo = 'orcamento_' . $numero . '_' . $f['id'] . '_' . time() . '.' . $arquivo->getClientOriginalExtension();
                            $arquivo->storeAs('orcamentos', $nomeArquivo, 'public');
                            
                            // Copiar também para public_html/storage/orcamentos
                            $publicPath = base_path('../public_html/storage/orcamentos');
                            if (!file_exists($publicPath)) {
                                mkdir($publicPath, 0755, true);
                            }
                            copy(storage_path('app/public/orcamentos/' . $nomeArquivo), $publicPath . '/' . $nomeArquivo);
                            
                            $insertData['arquivo_orcamento'] = 'orcamentos/' . $nomeArquivo;
                        }
                        
                        DB::table('cotacao_fornecedores')->insert($insertData);
                    }
                }
            }
            
            // Registrar log de criação da cotação
            $cotacaoCriada = DB::table('cotacoes')->where('id', $id)->first();
            $itensCriados = DB::table('cotacao_itens')->where('cotacao_id', $id)->get();
            $fornecedoresCriados = DB::table('cotacao_fornecedores')->where('cotacao_id', $id)->get();
            $this->registrarLogCotacao('criacao', $cotacaoCriada, auth()->user(), $itensCriados, $fornecedoresCriados);
            
            return response()->json(['success' => true, 'id' => $id, 'numero' => $numero, 'message' => 'Cotação criada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao criar cotação: ' . $e->getMessage()], 500);
        }
    }
    
    public function getCotacao($id)
    {
        try {
            $cotacao = DB::table('cotacoes')->where('id', $id)->first();
            
            if (!$cotacao) {
                return response()->json(['error' => 'Cotação não encontrada'], 404);
            }
            
            // Buscar nome do solicitante
            if ($cotacao->solicitante_id) {
                $solicitante = DB::table('users')->where('id', $cotacao->solicitante_id)->first();
                $cotacao->solicitante_nome = $solicitante ? $solicitante->name : null;
            } else {
                $cotacao->solicitante_nome = null;
            }
            
            // Buscar nome da obra (centro de custo) e descrição do serviço via ordem de serviço
            $cotacao->obra_nome = null;
            $cotacao->descricao_servico = null;
            if ($cotacao->ordem_servico_id) {
                $ordemServico = DB::table('ordens_servico as os')
                    ->leftJoin('centros_custo as cc', 'os.centro_custo_id', '=', 'cc.id')
                    ->where('os.id', $cotacao->ordem_servico_id)
                    ->select('cc.nome as centro_custo_nome', 'os.descricao as descricao_servico')
                    ->first();
                $cotacao->obra_nome = $ordemServico ? $ordemServico->centro_custo_nome : null;
                $cotacao->descricao_servico = $ordemServico ? $ordemServico->descricao_servico : null;
            }
            
            $itens = collect([]);
            try {
                $itens = DB::table('cotacao_itens')->where('cotacao_id', $id)->get();
            } catch (\Exception $e) {
                // Tabela pode não existir
            }
            
            // Verificar se a tabela de itens por fornecedor existe
            $hasItensPorFornecedor = \Schema::hasTable('cotacao_fornecedor_itens');
            
            $fornecedores = collect([]);
            try {
                // Verificar se as colunas extras existem
                $hasArquivoOrcamento = \Schema::hasColumn('cotacao_fornecedores', 'arquivo_orcamento');
                $hasCondicaoPagamento = \Schema::hasColumn('cotacao_fornecedores', 'condicao_pagamento');
                $hasObservacao = \Schema::hasColumn('cotacao_fornecedores', 'observacao');
                
                // Selecionar apenas as colunas que existem na tabela
                $selectFields = ['cf.id', 'cf.cotacao_id', 'cf.fornecedor_id', 'cf.valor_total', 'cf.prazo_entrega', 'f.razao_social'];
                if ($hasArquivoOrcamento) {
                    $selectFields[] = 'cf.arquivo_orcamento';
                }
                if ($hasCondicaoPagamento) {
                    $selectFields[] = 'cf.condicao_pagamento';
                }
                if ($hasObservacao) {
                    $selectFields[] = 'cf.observacao';
                }
                
                $fornecedores = DB::table('cotacao_fornecedores as cf')
                    ->leftJoin('fornecedores as f', 'cf.fornecedor_id', '=', 'f.id')
                    ->where('cf.cotacao_id', $id)
                    ->select($selectFields)
                    ->get();
                    
                // Adicionar campos padrão para compatibilidade e buscar itens por fornecedor
                $fornecedores = $fornecedores->map(function($f) use ($hasArquivoOrcamento, $hasCondicaoPagamento, $hasObservacao, $hasItensPorFornecedor) {
                    $f->status = $f->valor_total ? 'cotado' : 'pendente';
                    $f->selecionado = 0;
                    if (!$hasArquivoOrcamento) {
                        $f->arquivo_orcamento = null;
                    }
                    if (!$hasCondicaoPagamento) {
                        $f->condicao_pagamento = null;
                    }
                    if (!$hasObservacao) {
                        $f->observacao = null;
                    }
                    
                    // Buscar itens vinculados a este fornecedor
                    $f->itens = [];
                    if ($hasItensPorFornecedor) {
                        $itensFornecedor = DB::table('cotacao_fornecedor_itens as cfi')
                            ->leftJoin('cotacao_itens as ci', 'cfi.cotacao_item_id', '=', 'ci.id')
                            ->where('cfi.cotacao_fornecedor_id', $f->id)
                            ->select('ci.id', 'ci.produto', 'ci.quantidade', 'ci.unidade', 'cfi.valor_unitario')
                            ->get();
                        $f->itens = $itensFornecedor;
                    }
                    
                    return $f;
                });
            } catch (\Exception $e) {
                \Log::error('Erro ao buscar fornecedores da cotação: ' . $e->getMessage());
                // Tabela pode não existir
            }
            
            // Calcular itens já cotados (para cotação parcial)
            $itensJaCotados = [];
            if ($hasItensPorFornecedor && $cotacao->status === 'parcial') {
                $itensJaCotados = DB::table('cotacao_fornecedor_itens as cfi')
                    ->leftJoin('cotacao_fornecedores as cf', 'cfi.cotacao_fornecedor_id', '=', 'cf.id')
                    ->where('cf.cotacao_id', $id)
                    ->pluck('cfi.cotacao_item_id')
                    ->unique()
                    ->values()
                    ->toArray();
            }
            
            // Marcar itens como já cotados ou não
            $itens = $itens->map(function($item) use ($itensJaCotados) {
                $item->ja_cotado = in_array($item->id, $itensJaCotados);
                return $item;
            });
            
            // Buscar também fornecedores das OCs vinculadas (que podem não estar em cotacao_fornecedores)
            $fornecedoresOCs = DB::table('ordens_compra as oc')
                ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                ->where('oc.cotacao_id', $id)
                ->select(
                    'oc.id as oc_id',
                    'oc.numero as oc_numero',
                    'oc.fornecedor_id',
                    'oc.valor_total',
                    'oc.status as oc_status',
                    'f.razao_social',
                    'f.nome_fantasia'
                )
                ->get();
            
            // IDs de fornecedores já na lista
            $fornecedoresIdsExistentes = $fornecedores->pluck('fornecedor_id')->toArray();
            
            // Adicionar fornecedores das OCs que não estão na lista
            foreach ($fornecedoresOCs as $f) {
                if (!in_array($f->fornecedor_id, $fornecedoresIdsExistentes)) {
                    $fornecedores->push((object)[
                        'id' => $f->oc_id,
                        'fornecedor_id' => $f->fornecedor_id,
                        'razao_social' => $f->nome_fantasia ?: $f->razao_social,
                        'valor_total' => $f->valor_total,
                        'prazo_entrega' => null,
                        'condicao_pagamento' => null,
                        'observacao' => 'OC: ' . $f->oc_numero,
                        'arquivo_orcamento' => null,
                        'status' => $f->oc_status == 'recebida' ? 'recebido' : ($f->valor_total ? 'cotado' : 'pendente'),
                        'selecionado' => 0,
                        'itens' => [],
                        'origem_oc' => true,
                        'oc_numero' => $f->oc_numero
                    ]);
                    $fornecedoresIdsExistentes[] = $f->fornecedor_id;
                }
            }
            
            return response()->json([
                'cotacao' => $cotacao,
                'itens' => $itens,
                'fornecedores' => $fornecedores,
                'itens_ja_cotados' => $itensJaCotados,
                'has_itens_por_fornecedor' => $hasItensPorFornecedor
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar cotação: ' . $e->getMessage()], 500);
        }
    }
    
    // Registrar valores dos fornecedores
    public function saveValoresCotacao(Request $request, $id)
    {
        try {
            $valores = $request->valores;
            
            if (!$valores || count($valores) === 0) {
                return response()->json(['success' => false, 'message' => 'Nenhum valor informado!'], 400);
            }
            
            foreach ($valores as $item) {
                // Atualizar apenas valor_total e prazo_entrega (colunas que existem)
                DB::table('cotacao_fornecedores')
                    ->where('cotacao_id', $id)
                    ->where('fornecedor_id', $item['fornecedor_id'])
                    ->update([
                        'valor_total' => floatval(str_replace(',', '.', str_replace('.', '', $item['valor_total']))),
                        'prazo_entrega' => $item['prazo_entrega'] ? intval($item['prazo_entrega']) : null,
                    ]);
            }
            
            // Atualizar status da cotação para "em_cotacao"
            DB::table('cotacoes')->where('id', $id)->update([
                'status' => 'em_cotacao',
            ]);
            
            return response()->json(['success' => true, 'message' => 'Valores registrados com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao salvar valores: ' . $e->getMessage()], 500);
        }
    }
    
    // Adicionar fornecedores a uma cotação existente (para cotações vindas de O.S.)
    public function adicionarFornecedoresCotacao(Request $request, $id)
    {
        try {
            $valores = $request->valores;
            
            if (!$valores || count($valores) === 0) {
                return response()->json(['success' => false, 'message' => 'Nenhum valor informado!'], 400);
            }
            
            // Verificar se a coluna arquivo_orcamento existe
            $hasArquivoOrcamento = \Schema::hasColumn('cotacao_fornecedores', 'arquivo_orcamento');
            
            // Verificar se a tabela de itens por fornecedor existe
            $hasItensPorFornecedor = \Schema::hasTable('cotacao_fornecedor_itens');
            
            // Buscar número da cotação para nome do arquivo
            $cotacao = DB::table('cotacoes')->where('id', $id)->first();
            $numeroCotacao = $cotacao ? $cotacao->numero : 'COT-' . $id;
            
            // Buscar centro de custo da cotação (via O.S.)
            $centroCustoId = null;
            if ($cotacao && $cotacao->ordem_servico_id) {
                $os = DB::table('ordens_servico')->where('id', $cotacao->ordem_servico_id)->first();
                if ($os && $os->centro_custo_id) {
                    $centroCustoId = $os->centro_custo_id;
                }
            }
            
            foreach ($valores as $index => $item) {
                // Verificar se já existe o registro
                $cotacaoFornecedor = DB::table('cotacao_fornecedores')
                    ->where('cotacao_id', $id)
                    ->where('fornecedor_id', $item['fornecedor_id'])
                    ->first();
                
                // Converter valor - verifica se já está no formato decimal (com ponto) ou brasileiro (com vírgula)
                $valorStr = $item['valor_total'];
                if (strpos($valorStr, ',') !== false) {
                    // Formato brasileiro: 10.880,00 -> remove pontos de milhar, troca vírgula por ponto
                    $valorFloat = floatval(str_replace(',', '.', str_replace('.', '', $valorStr)));
                } else {
                    // Já está no formato decimal: 10880.00
                    $valorFloat = floatval($valorStr);
                }
                $prazo = isset($item['prazo_entrega']) && $item['prazo_entrega'] ? intval($item['prazo_entrega']) : null;
                $condicaoPagamento = isset($item['condicao_pagamento']) && $item['condicao_pagamento'] ? $item['condicao_pagamento'] : null;
                $observacao = isset($item['observacao']) && $item['observacao'] ? trim($item['observacao']) : null;
                $parcelas = isset($item['parcelas']) && $item['parcelas'] > 1 ? intval($item['parcelas']) : null;
                
                // Processar arquivo de orçamento se existir
                $arquivoOrcamento = null;
                if ($hasArquivoOrcamento) {
                    // Verificar se há arquivo pelo índice informado
                    $orcamentoIndex = isset($item['orcamento_index']) ? $item['orcamento_index'] : $index;
                    if ($request->hasFile("orcamentos.{$orcamentoIndex}")) {
                        $arquivo = $request->file("orcamentos.{$orcamentoIndex}");
                        $nomeArquivo = 'orcamento_' . $numeroCotacao . '_' . $item['fornecedor_id'] . '_' . time() . '.' . $arquivo->getClientOriginalExtension();
                        $arquivo->storeAs('orcamentos', $nomeArquivo, 'public');
                        
                        // Copiar também para public_html/storage/orcamentos
                        $publicPath = base_path('../public_html/storage/orcamentos');
                        if (!file_exists($publicPath)) {
                            mkdir($publicPath, 0755, true);
                        }
                        copy(storage_path('app/public/orcamentos/' . $nomeArquivo), $publicPath . '/' . $nomeArquivo);
                        
                        $arquivoOrcamento = 'orcamentos/' . $nomeArquivo;
                    }
                }
                
                $cotacaoFornecedorId = null;
                
                // Verificar se coluna observacao existe
                $hasObservacao = \Schema::hasColumn('cotacao_fornecedores', 'observacao');
                
                if ($cotacaoFornecedor) {
                    // Atualizar
                    $cotacaoFornecedorId = $cotacaoFornecedor->id;
                    $updateData = [
                        'valor_total' => $valorFloat,
                        'prazo_entrega' => $prazo,
                        'condicao_pagamento' => $condicaoPagamento,
                        'parcelas' => $parcelas,
                        'cotado_por' => auth()->id(),
                        'cotado_em' => now(),
                    ];
                    if ($hasObservacao) {
                        $updateData['observacao'] = $observacao;
                    }
                    if ($arquivoOrcamento) {
                        $updateData['arquivo_orcamento'] = $arquivoOrcamento;
                    }
                    
                    DB::table('cotacao_fornecedores')
                        ->where('id', $cotacaoFornecedorId)
                        ->update($updateData);
                } else {
                    // Inserir novo
                    $insertData = [
                        'cotacao_id' => $id,
                        'fornecedor_id' => $item['fornecedor_id'],
                        'valor_total' => $valorFloat,
                        'prazo_entrega' => $prazo,
                        'condicao_pagamento' => $condicaoPagamento,
                        'parcelas' => $parcelas,
                        'cotado_por' => auth()->id(),
                        'cotado_em' => now(),
                        'created_at' => now(),
                    ];
                    if ($hasObservacao) {
                        $insertData['observacao'] = $observacao;
                    }
                    if ($arquivoOrcamento) {
                        $insertData['arquivo_orcamento'] = $arquivoOrcamento;
                    }
                    
                    $cotacaoFornecedorId = DB::table('cotacao_fornecedores')->insertGetId($insertData);
                }
                
                // Salvar itens selecionados por fornecedor (se a tabela existir)
                if ($hasItensPorFornecedor && $cotacaoFornecedorId && isset($item['itens'])) {
                    // Decodificar JSON de itens
                    $itensIds = json_decode($item['itens'], true);
                    
                    // Decodificar quantidades personalizadas (se existir)
                    $itensComQuantidade = [];
                    if (isset($item['itens_quantidade'])) {
                        $itensComQuantidade = json_decode($item['itens_quantidade'], true) ?: [];
                    }
                    
                    // Criar mapa de quantidades personalizadas
                    $mapaQuantidades = [];
                    foreach ($itensComQuantidade as $iq) {
                        if (isset($iq['id']) && $iq['quantidade_personalizada'] !== null) {
                            $mapaQuantidades[$iq['id']] = floatval($iq['quantidade_personalizada']);
                        }
                    }
                    
                    \Log::info("Cotação #{$id} - Fornecedor #{$item['fornecedor_id']} - Itens recebidos: " . json_encode($itensIds));
                    \Log::info("Cotação #{$id} - Fornecedor #{$item['fornecedor_id']} - Quantidades personalizadas: " . json_encode($mapaQuantidades));
                    
                    if (is_array($itensIds) && count($itensIds) > 0) {
                        // Remover itens antigos deste fornecedor
                        DB::table('cotacao_fornecedor_itens')
                            ->where('cotacao_fornecedor_id', $cotacaoFornecedorId)
                            ->delete();
                        
                        // Inserir novos itens
                        foreach ($itensIds as $itemId) {
                            $insertData = [
                                'cotacao_fornecedor_id' => $cotacaoFornecedorId,
                                'cotacao_item_id' => $itemId,
                                'disponivel' => 1,
                                'created_at' => now(),
                            ];
                            
                            // Adicionar quantidade personalizada se existir
                            if (isset($mapaQuantidades[$itemId])) {
                                $insertData['quantidade_personalizada'] = $mapaQuantidades[$itemId];
                            }
                            
                            DB::table('cotacao_fornecedor_itens')->insert($insertData);
                        }
                    } else {
                        \Log::info("Cotação #{$id} - Fornecedor #{$item['fornecedor_id']} - Nenhum item selecionado ou array vazio");
                    }
                } else {
                    \Log::info("Cotação #{$id} - Itens NÃO salvos. hasItensPorFornecedor: " . ($hasItensPorFornecedor ? 'Sim' : 'Não') . ", cotacaoFornecedorId: " . ($cotacaoFornecedorId ?? 'null') . ", isset itens: " . (isset($item['itens']) ? 'Sim' : 'Não'));
                }
            }
            
            // Buscar todos os fornecedores com valores cadastrados
            $fornecedoresCotados = DB::table('cotacao_fornecedores')
                ->where('cotacao_id', $id)
                ->whereNotNull('valor_total')
                ->where('valor_total', '>', 0)
                ->orderBy('valor_total', 'asc')
                ->get();
            
            if ($fornecedoresCotados->count() > 0) {
                // Verificar se há itens por fornecedor (cotação dividida)
                $temItensDivididos = false;
                $itensCotados = [];
                
                if ($hasItensPorFornecedor) {
                    foreach ($fornecedoresCotados as $fc) {
                        $itensDoFornecedor = DB::table('cotacao_fornecedor_itens')
                            ->where('cotacao_fornecedor_id', $fc->id)
                            ->pluck('cotacao_item_id')
                            ->toArray();
                        
                        if (count($itensDoFornecedor) > 0) {
                            $temItensDivididos = true;
                            $itensCotados = array_merge($itensCotados, $itensDoFornecedor);
                        }
                    }
                    $itensCotados = array_unique($itensCotados);
                }
                
                // Buscar total de itens da cotação
                $totalItensCotacao = DB::table('cotacao_itens')
                    ->where('cotacao_id', $id)
                    ->count();
                
                // DEBUG: Log para identificar o problema
                \Log::info("=== DEBUG COTAÇÃO #{$id} ===");
                \Log::info("Itens cotados (array): " . json_encode($itensCotados));
                \Log::info("Quantidade itens cotados: " . count($itensCotados));
                \Log::info("Total itens da cotação: {$totalItensCotacao}");
                \Log::info("Tem itens divididos: " . ($temItensDivididos ? 'SIM' : 'NÃO'));
                \Log::info("Fornecedores com valor: " . $fornecedoresCotados->count());
                
                // Verificar se é envio separado (cada fornecedor gera uma OC)
                $enviarSeparado = $request->input('enviar_separado') == '1';
                
                // Verificar se é envio parcial (gera OC só para os preenchidos e mantém cotação como parcial)
                $enviarParcial = $request->input('enviar_parcial') == '1';
                
                // Calcular se faltam itens para definir status final
                $itensFaltando = $totalItensCotacao - count($itensCotados);
                $temItensFaltando = $itensFaltando > 0;
                
                \Log::info("Cotação #{$id} - Gerando OCs. enviar_separado: " . ($enviarSeparado ? 'SIM' : 'NÃO') . ", enviar_parcial: " . ($enviarParcial ? 'SIM' : 'NÃO') . ", itens_faltando: {$itensFaltando}");
                
                // NOVA LÓGICA: Sempre gerar OCs para cada fornecedor com seus itens
                // Se tem itens divididos OU enviar separado OU enviar parcial, gera múltiplas OCs
                if ($temItensDivididos || $enviarSeparado || $enviarParcial) {
                    \Log::info("Cotação #{$id} - Modo separado/dividido. Gerando OCs individuais.");
                    // Gerar uma OC para cada fornecedor
                    $ocsGeradas = [];
                    
                    // Buscar todos os itens da cotação (para usar quando não há itens vinculados)
                    $todosItensCotacao = DB::table('cotacao_itens')
                        ->where('cotacao_id', $id)
                        ->get();
                    
                    // Verificar quais fornecedores já têm OC gerada (para evitar duplicatas)
                    $ocsExistentes = DB::table('ordens_compra')
                        ->where('cotacao_id', $id)
                        ->pluck('fornecedor_id')
                        ->toArray();
                    
                    $fornecedoresPulados = 0;
                    
                    foreach ($fornecedoresCotados as $fc) {
                        // Pular se já existe OC para este fornecedor nesta cotação
                        if (in_array($fc->fornecedor_id, $ocsExistentes)) {
                            \Log::info("Cotação #{$id} - Fornecedor #{$fc->fornecedor_id} já tem OC, pulando...");
                            $fornecedoresPulados++;
                            continue;
                        }
                        
                        // Buscar itens vinculados a este fornecedor
                        $itensFornecedor = DB::table('cotacao_fornecedor_itens as cfi')
                            ->leftJoin('cotacao_itens as ci', 'cfi.cotacao_item_id', '=', 'ci.id')
                            ->where('cfi.cotacao_fornecedor_id', $fc->id)
                            ->select('ci.*', 'cfi.valor_unitario')
                            ->get();
                        
                        // Se é envio separado e não tem itens vinculados, usar todos os itens da cotação
                        $usarTodosItens = false;
                        if ($enviarSeparado && $itensFornecedor->isEmpty()) {
                            $itensFornecedor = $todosItensCotacao;
                            $usarTodosItens = true;
                        }
                        
                        // Se ainda não tem itens, pular este fornecedor
                        if ($itensFornecedor->isEmpty()) {
                            continue;
                        }
                        
                        // Gerar número da O.C.
                        $ultimaOC = DB::table('ordens_compra')->orderBy('id', 'desc')->first();
                        $proximoNumero = 1;
                        if ($ultimaOC && isset($ultimaOC->numero)) {
                            preg_match('/(\d+)$/', $ultimaOC->numero, $matches);
                            if (!empty($matches[1])) {
                                $proximoNumero = intval($matches[1]) + 1;
                            }
                        }
                        $numeroOC = 'OC-' . date('Y') . '-' . str_pad($proximoNumero, 4, '0', STR_PAD_LEFT);
                        
                        // Calcular valor proporcional para este fornecedor
                        $totalQtd = $itensFornecedor->sum('quantidade');
                        $valorUnitarioBase = $totalQtd > 0 ? $fc->valor_total / $totalQtd : 0;
                        
                        // Criar Ordem de Compra
                        $prazoEntrega = $fc->prazo_entrega ?? 7;
                        $dadosOC = [
                            'numero' => $numeroOC,
                            'cotacao_id' => $id,
                            'fornecedor_id' => $fc->fornecedor_id,
                            'valor_total' => $fc->valor_total,
                            'status' => 'pendente',
                            'data_emissao' => now()->format('Y-m-d'),
                            'data_previsao' => now()->addDays($prazoEntrega)->format('Y-m-d'),
                            'created_at' => now(),
                        ];
                        if ($centroCustoId) {
                            $dadosOC['centro_custo_id'] = $centroCustoId;
                        }
                        OrdensCompraAuditoria::mergeCriadorUsuario($dadosOC);
                        $ocId = DB::table('ordens_compra')->insertGetId($dadosOC);
                        
                        // Copiar itens para a O.C.
                        foreach ($itensFornecedor as $item) {
                            $valorItem = $valorUnitarioBase * ($item->quantidade ?? 1);
                            DB::table('ordem_compra_itens')->insert([
                                'ordem_compra_id' => $ocId,
                                'produto' => $item->produto,
                                'quantidade' => $item->quantidade ?? 1,
                                'unidade' => $item->unidade ?? 'UN',
                                'valor_unitario' => $item->valor_unitario ?? $valorUnitarioBase,
                                'valor_total' => $valorItem,
                                'created_at' => now(),
                            ]);
                        }
                        
                        $fornecedor = DB::table('fornecedores')->where('id', $fc->fornecedor_id)->first();
                        $ocsGeradas[] = [
                            'numero' => $numeroOC,
                            'fornecedor' => $fornecedor ? $fornecedor->razao_social : 'Fornecedor',
                            'valor' => $fc->valor_total,
                            'itens' => $itensFornecedor->count()
                        ];
                        
                        // Registrar log de criação da O.C. (via finalização de cotação com divisão)
                        $this->registrarLogOrdemCompraAcao('criacao_via_cotacao_divisao', $ocId, $numeroOC, $fc->fornecedor_id, $fornecedor ? $fornecedor->razao_social : null, $fc->valor_total, $id);
                    }
                    
                    // Atualizar status da cotação
                    // Se ainda faltam itens para cotar OU é envio parcial, manter como 'parcial'
                    // Senão, finalizar a cotação
                    if ($temItensFaltando || $enviarParcial) {
                        DB::table('cotacoes')->where('id', $id)->update([
                            'status' => 'parcial',
                        ]);
                    } else {
                        DB::table('cotacoes')->where('id', $id)->update([
                            'status' => 'finalizada',
                        ]);
                    }
                    
                    if (count($ocsGeradas) > 0) {
                        if ($temItensFaltando) {
                            $mensagem = count($ocsGeradas) . ' Ordem(ns) de Compra gerada(s) e enviada(s) para autorização! A cotação continua como PARCIAL (faltam ' . $itensFaltando . ' item(ns) para cotar).';
                        } else if ($enviarParcial) {
                            $mensagem = count($ocsGeradas) . ' Ordem(ns) de Compra gerada(s) e enviada(s) para autorização! A cotação continua como PARCIAL para você cotar os demais itens.';
                        } else {
                            $mensagem = 'Cotação finalizada! ' . count($ocsGeradas) . ' Ordem(ns) de Compra gerada(s) e enviada(s) para autorização.';
                        }
                        
                        // Informar se alguns fornecedores foram pulados por já terem OC
                        if ($fornecedoresPulados > 0) {
                            $mensagem .= ' (' . $fornecedoresPulados . ' fornecedor(es) já tinha(m) O.C. gerada anteriormente)';
                        }
                        
                        return response()->json([
                            'success' => true, 
                            'message' => $mensagem,
                            'ocs_geradas' => $ocsGeradas,
                            'numero_oc' => $ocsGeradas[0]['numero'],
                            'fornecedor' => $ocsGeradas[0]['fornecedor'],
                            'valor' => $ocsGeradas[0]['valor'],
                            'multiplas_ocs' => count($ocsGeradas) > 1,
                            'cotacao_parcial' => $temItensFaltando || $enviarParcial,
                            'itens_faltando' => $itensFaltando,
                            'fornecedores_pulados' => $fornecedoresPulados
                        ]);
                    } else if ($fornecedoresPulados > 0) {
                        // Todos os fornecedores já tinham OC
                        return response()->json([
                            'success' => true, 
                            'message' => 'Nenhuma nova O.C. gerada. Todos os ' . $fornecedoresPulados . ' fornecedor(es) já possuem O.C. gerada anteriormente.',
                            'ocs_geradas' => [],
                            'fornecedores_pulados' => $fornecedoresPulados
                        ]);
                    }
                }
                
                /* =======================================================================
                 * LÓGICA ANTIGA DESATIVADA: Vencedor pelo menor valor total
                 * Comentada em 02/02/2026 - Agora sempre usa o modo de envio separado/dividido
                 * ======================================================================= */
                /*
                else {
                    // Lógica antiga: vencedor pelo menor valor total
                    $vencedor = $fornecedoresCotados->first();
                    
                    // Gerar número da O.C.
                    $ultimaOC = DB::table('ordens_compra')->orderBy('id', 'desc')->first();
                    $proximoNumero = 1;
                    if ($ultimaOC && isset($ultimaOC->numero)) {
                        preg_match('/(\d+)$/', $ultimaOC->numero, $matches);
                        if (!empty($matches[1])) {
                            $proximoNumero = intval($matches[1]) + 1;
                        }
                    }
                    $numeroOC = 'OC-' . date('Y') . '-' . str_pad($proximoNumero, 4, '0', STR_PAD_LEFT);
                    
                    // Buscar dados da cotação
                    $cotacao = DB::table('cotacoes')->where('id', $id)->first();
                    
                    // Criar Ordem de Compra com status "pendente" para aprovação
                    $prazoEntrega = $vencedor->prazo_entrega ?? 7;
                    $dadosOC = [
                        'numero' => $numeroOC,
                        'cotacao_id' => $id,
                        'fornecedor_id' => $vencedor->fornecedor_id,
                        'valor_total' => $vencedor->valor_total,
                        'status' => 'pendente',
                        'data_emissao' => now()->format('Y-m-d'),
                        'data_previsao' => now()->addDays($prazoEntrega)->format('Y-m-d'),
                        'created_at' => now(),
                    ];
                    if ($centroCustoId) {
                        $dadosOC['centro_custo_id'] = $centroCustoId;
                    }
                    $ocId = DB::table('ordens_compra')->insertGetId($dadosOC);
                    
                    // Copiar itens da cotação para a O.C.
                    $itensCotacao = DB::table('cotacao_itens')->where('cotacao_id', $id)->get();
                    $totalQtd = $itensCotacao->sum('quantidade');
                    $valorUnitarioBase = $totalQtd > 0 ? $vencedor->valor_total / $totalQtd : 0;
                    
                    foreach ($itensCotacao as $item) {
                        $valorItem = $valorUnitarioBase * $item->quantidade;
                        DB::table('ordem_compra_itens')->insert([
                            'ordem_compra_id' => $ocId,
                            'produto' => $item->produto,
                            'quantidade' => $item->quantidade,
                            'unidade' => $item->unidade,
                            'valor_unitario' => $valorUnitarioBase,
                            'valor_total' => $valorItem,
                            'created_at' => now(),
                        ]);
                    }
                    
                    // Atualizar status da cotação para "finalizada"
                    DB::table('cotacoes')->where('id', $id)->update([
                        'status' => 'finalizada',
                    ]);
                    
                    // Buscar nome do fornecedor para retorno
                    $fornecedor = DB::table('fornecedores')->where('id', $vencedor->fornecedor_id)->first();
                    
                    // Registrar log de criação da O.C. (via finalização de cotação)
                    $this->registrarLogOrdemCompraAcao('criacao_via_cotacao', $ocId, $numeroOC, $vencedor->fornecedor_id, $fornecedor ? $fornecedor->razao_social : null, $vencedor->valor_total, $id);
                    
                    return response()->json([
                        'success' => true, 
                        'message' => 'Cotação finalizada! Ordem de Compra gerada e aguardando aprovação.',
                        'numero_oc' => $numeroOC,
                        'fornecedor' => $fornecedor ? $fornecedor->razao_social : 'Fornecedor',
                        'valor' => $vencedor->valor_total
                    ]);
                }
                */
            }
            
            return response()->json(['success' => true, 'message' => 'Fornecedores adicionados com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao adicionar fornecedores: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Envia os fornecedores já cadastrados na cotação para autorização (gera OCs)
     * Criado em 02/02/2026 para substituir a lógica de menor preço
     */
    public function enviarParaAutorizacao(Request $request, $id)
    {
        try {
            $cotacao = DB::table('cotacoes')->where('id', $id)->first();
            
            if (!$cotacao) {
                return response()->json(['success' => false, 'message' => 'Cotação não encontrada!'], 404);
            }
            
            // Buscar centro de custo da cotação (via O.S.)
            $centroCustoId = null;
            if ($cotacao->ordem_servico_id) {
                $os = DB::table('ordens_servico')->where('id', $cotacao->ordem_servico_id)->first();
                if ($os && $os->centro_custo_id) {
                    $centroCustoId = $os->centro_custo_id;
                }
            }
            
            // Buscar fornecedores com valor cadastrado
            $fornecedoresCotados = DB::table('cotacao_fornecedores')
                ->where('cotacao_id', $id)
                ->whereNotNull('valor_total')
                ->where('valor_total', '>', 0)
                ->get();
            
            if ($fornecedoresCotados->count() == 0) {
                return response()->json(['success' => false, 'message' => 'Nenhum fornecedor com valor cadastrado nesta cotação!'], 400);
            }
            
            // Verificar se tabela de itens por fornecedor existe
            $hasItensPorFornecedor = \Schema::hasTable('cotacao_fornecedor_itens');
            
            // Buscar total de itens da cotação
            $totalItensCotacao = DB::table('cotacao_itens')->where('cotacao_id', $id)->count();
            
            // Buscar itens já cotados (vinculados a fornecedores)
            $itensCotados = [];
            if ($hasItensPorFornecedor) {
                foreach ($fornecedoresCotados as $fc) {
                    $itensDoFornecedor = DB::table('cotacao_fornecedor_itens')
                        ->where('cotacao_fornecedor_id', $fc->id)
                        ->pluck('cotacao_item_id')
                        ->toArray();
                    $itensCotados = array_merge($itensCotados, $itensDoFornecedor);
                }
                $itensCotados = array_unique($itensCotados);
            }
            
            $itensFaltando = $totalItensCotacao - count($itensCotados);
            
            // Verificar se já existem OCs geradas para esta cotação
            $ocsExistentes = DB::table('ordens_compra')
                ->where('cotacao_id', $id)
                ->pluck('fornecedor_id')
                ->toArray();
            
            // Filtrar apenas fornecedores que ainda não têm OC gerada
            $fornecedoresPendentes = $fornecedoresCotados->filter(function($fc) use ($ocsExistentes) {
                return !in_array($fc->fornecedor_id, $ocsExistentes);
            });
            
            if ($fornecedoresPendentes->count() == 0) {
                // Verificar quantas OCs já foram geradas
                $totalOCs = count($ocsExistentes);
                
                // Se há OCs geradas, a cotação pode ser finalizada
                if ($totalOCs > 0) {
                    // Atualizar status da cotação para finalizada
                    DB::table('cotacoes')->where('id', $id)->update([
                        'status' => 'finalizada',
                        'updated_at' => now()
                    ]);
                    
                    return response()->json([
                        'success' => true, 
                        'message' => 'Cotação finalizada! ' . $totalOCs . ' O.C.(s) já foram geradas anteriormente.'
                    ]);
                }
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Todas as O.C.s já foram geradas para esta cotação! Verifique a lista de Ordens de Compra.'
                ], 400);
            }
            
            // Gerar OCs para cada fornecedor que ainda não tem
            $ocsGeradas = [];
            $todosItensCotacao = DB::table('cotacao_itens')->where('cotacao_id', $id)->get();
            
            foreach ($fornecedoresPendentes as $fc) {
                // Buscar itens vinculados a este fornecedor
                $itensFornecedor = collect([]);
                if ($hasItensPorFornecedor) {
                    $itensFornecedor = DB::table('cotacao_fornecedor_itens as cfi')
                        ->leftJoin('cotacao_itens as ci', 'cfi.cotacao_item_id', '=', 'ci.id')
                        ->where('cfi.cotacao_fornecedor_id', $fc->id)
                        ->select('ci.*', 'cfi.valor_unitario')
                        ->get();
                }
                
                // Se não tem itens vinculados, usar todos os itens da cotação
                if ($itensFornecedor->isEmpty()) {
                    $itensFornecedor = $todosItensCotacao;
                }
                
                // Se ainda não tem itens, pular este fornecedor
                if ($itensFornecedor->isEmpty()) {
                    continue;
                }
                
                // Gerar número da O.C.
                $ultimaOC = DB::table('ordens_compra')->orderBy('id', 'desc')->first();
                $proximoNumero = 1;
                if ($ultimaOC && isset($ultimaOC->numero)) {
                    preg_match('/(\d+)$/', $ultimaOC->numero, $matches);
                    if (!empty($matches[1])) {
                        $proximoNumero = intval($matches[1]) + 1;
                    }
                }
                $numeroOC = 'OC-' . date('Y') . '-' . str_pad($proximoNumero, 4, '0', STR_PAD_LEFT);
                
                // Calcular valor proporcional
                $totalQtd = $itensFornecedor->sum('quantidade');
                $valorUnitarioBase = $totalQtd > 0 ? $fc->valor_total / $totalQtd : 0;
                
                // Criar Ordem de Compra
                $prazoEntrega = $fc->prazo_entrega ?? 7;
                $dadosOC = [
                    'numero' => $numeroOC,
                    'cotacao_id' => $id,
                    'fornecedor_id' => $fc->fornecedor_id,
                    'valor_total' => $fc->valor_total,
                    'status' => 'pendente',
                    'data_emissao' => now()->format('Y-m-d'),
                    'data_previsao' => now()->addDays($prazoEntrega)->format('Y-m-d'),
                    'created_at' => now(),
                ];
                if ($centroCustoId) {
                    $dadosOC['centro_custo_id'] = $centroCustoId;
                }
                OrdensCompraAuditoria::mergeCriadorUsuario($dadosOC);
                $ocId = DB::table('ordens_compra')->insertGetId($dadosOC);
                
                // Copiar itens para a O.C.
                foreach ($itensFornecedor as $item) {
                    $valorItem = $valorUnitarioBase * ($item->quantidade ?? 1);
                    DB::table('ordem_compra_itens')->insert([
                        'ordem_compra_id' => $ocId,
                        'produto' => $item->produto,
                        'quantidade' => $item->quantidade ?? 1,
                        'unidade' => $item->unidade ?? 'UN',
                        'valor_unitario' => $item->valor_unitario ?? $valorUnitarioBase,
                        'valor_total' => $valorItem,
                        'created_at' => now(),
                    ]);
                }
                
                $fornecedor = DB::table('fornecedores')->where('id', $fc->fornecedor_id)->first();
                $ocsGeradas[] = [
                    'numero' => $numeroOC,
                    'fornecedor' => $fornecedor ? $fornecedor->razao_social : 'Fornecedor',
                    'valor' => $fc->valor_total,
                    'itens' => $itensFornecedor->count()
                ];
                
                // Registrar log
                $this->registrarLogOrdemCompraAcao('criacao_via_cotacao_divisao', $ocId, $numeroOC, $fc->fornecedor_id, $fornecedor ? $fornecedor->razao_social : null, $fc->valor_total, $id);
            }
            
            // Atualizar status da cotação
            if ($itensFaltando > 0) {
                DB::table('cotacoes')->where('id', $id)->update(['status' => 'parcial']);
            } else {
                DB::table('cotacoes')->where('id', $id)->update(['status' => 'finalizada']);
            }
            
            if (count($ocsGeradas) > 0) {
                $mensagem = count($ocsGeradas) . ' Ordem(ns) de Compra gerada(s) e enviada(s) para autorização!';
                if ($itensFaltando > 0) {
                    $mensagem .= " A cotação continua como PARCIAL (faltam {$itensFaltando} item(ns) para cotar).";
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $mensagem,
                    'ocs_geradas' => $ocsGeradas,
                    'multiplas_ocs' => count($ocsGeradas) > 1,
                    'cotacao_parcial' => $itensFaltando > 0,
                    'itens_faltando' => $itensFaltando
                ]);
            }
            
            return response()->json(['success' => false, 'message' => 'Nenhuma O.C. foi gerada. Verifique se os fornecedores possuem itens vinculados.'], 400);
            
        } catch (\Exception $e) {
            \Log::error('Erro enviarParaAutorizacao: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao enviar para autorização: ' . $e->getMessage()], 500);
        }
    }
    
    /* =======================================================================
     * FUNÇÃO gerarOC DESATIVADA - Usava lógica de menor preço
     * Comentada em 02/02/2026 - Agora o fluxo é via adicionarFornecedores com envio separado/parcial
     * ======================================================================= */
    public function gerarOC(Request $request, $id)
    {
        // Retornar mensagem informando que esta função não é mais utilizada
        return response()->json([
            'success' => false, 
            'message' => 'Esta função foi desativada. Utilize o botão "Enviar para Autorização" na tela de cotação.'
        ], 400);
        
        /* CÓDIGO ANTIGO COMENTADO - Usava lógica de menor preço
        try {
            $cotacao = DB::table('cotacoes')->where('id', $id)->first();
            
            if (!$cotacao) {
                return response()->json(['success' => false, 'message' => 'Cotação não encontrada!'], 404);
            }
            
            // Buscar o fornecedor com menor valor (vencedor automático)
            $vencedor = DB::table('cotacao_fornecedores as cf')
                ->leftJoin('fornecedores as f', 'cf.fornecedor_id', '=', 'f.id')
                ->where('cf.cotacao_id', $id)
                ->whereNotNull('cf.valor_total')
                ->where('cf.valor_total', '>', 0)
                ->orderBy('cf.valor_total', 'asc')
                ->select('cf.fornecedor_id', 'cf.valor_total', 'cf.prazo_entrega', 'f.razao_social')
                ->first();
            
            if (!$vencedor) {
                return response()->json(['success' => false, 'message' => 'Nenhum fornecedor com valor cadastrado nesta cotação!'], 400);
            }
            
            $numeroOC = 'OC-' . date('Y') . '-' . str_pad(DB::table('ordens_compra')->count() + 1, 3, '0', STR_PAD_LEFT);
            
            // Usar prazo do request ou do fornecedor vencedor
            $prazoEntrega = $request->prazo_entrega ?? now()->addDays($vencedor->prazo_entrega ?? 7)->format('Y-m-d');
            
            $ocId = DB::table('ordens_compra')->insertGetId([
                'numero' => $numeroOC,
                'cotacao_id' => $id,
                'fornecedor_id' => $vencedor->fornecedor_id,
                'data_emissao' => now()->format('Y-m-d'),
                'data_previsao' => $prazoEntrega,
                'valor_total' => $vencedor->valor_total,
                'status' => 'pendente',
                'observacoes' => $request->observacoes,
                'created_at' => now(),
            ]);
            
            // Copiar itens da cotação para a OC
            $itensCotacao = DB::table('cotacao_itens')->where('cotacao_id', $id)->get();
            foreach ($itensCotacao as $item) {
                DB::table('ordem_compra_itens')->insert([
                    'ordem_compra_id' => $ocId,
                    'produto' => $item->produto,
                    'quantidade' => $item->quantidade,
                    'unidade' => $item->unidade ?? 'UN',
                    'valor_unitario' => 0,
                    'valor_total' => 0,
                    'created_at' => now(),
                ]);
            }
            
            // Marcar cotação como convertida (não mostrar mais na lista de pendentes)
            // Nota: não alteramos o status pois já está "finalizada"
            
            // =============================================
            // INTEGRAÇÃO FINANCEIRO - CRIAR CONTA A PAGAR
            // =============================================
            try {
                $contaPagarId = DB::table('contas_pagar')->insertGetId([
                    'descricao' => 'OC ' . $numeroOC . ' - ' . ($cotacao->descricao ?? 'Compra de materiais'),
                    'fornecedor_id' => $vencedor->fornecedor_id,
                    'fornecedor' => $vencedor->razao_social ?? '', // Nome do fornecedor
                    'valor' => $vencedor->valor_total,
                    'vencimento' => $prazoEntrega, // Coluna correta
                    'status' => 'pendente',
                    'ordem_compra_id' => $ocId, // Vincula à OC
                    'created_at' => now(),
                ]);
                
                // Atualizar OC com o ID da conta a pagar
                DB::table('ordens_compra')->where('id', $ocId)->update([
                    'conta_pagar_id' => $contaPagarId,
                    'status_pagamento' => 'aguardando_pagamento'
                ]);
                
                \Log::info("Conta a Pagar #{$contaPagarId} criada para OC #{$ocId}");
            } catch (\Exception $e) {
                \Log::warning('Não foi possível criar conta a pagar automaticamente: ' . $e->getMessage());
                // Continua mesmo se falhar (tabela pode não ter as colunas)
            }
            
            // Registrar log de criação da O.C.
            $this->registrarLogOrdemCompraAcao('criacao', $ocId, $numeroOC, $vencedor->fornecedor_id, $vencedor->razao_social, $vencedor->valor_total, $id);
            
            return response()->json([
                'success' => true, 
                'oc_id' => $ocId, 
                'numero' => $numeroOC, 
                'fornecedor' => $vencedor->razao_social ?? 'N/A',
                'valor' => $vencedor->valor_total,
                'message' => 'Ordem de Compra gerada com sucesso! Uma conta a pagar foi criada no Financeiro.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro gerarOC: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao gerar OC: ' . $e->getMessage()], 500);
        }
        FIM DO CÓDIGO ANTIGO COMENTADO */
    }
    
    // ============================================
    // APROVAR/RECUSAR ORDEM DE COMPRA
    // ============================================
    
    public function aprovarOrdemCompra($id)
    {
        try {
            $oc = DB::table('ordens_compra')->where('id', $id)->first();
            
            if (!$oc) {
                return response()->json(['success' => false, 'message' => 'Ordem de Compra não encontrada!'], 404);
            }
            
            if ($oc->status !== 'pendente') {
                return response()->json(['success' => false, 'message' => 'Esta O.C. já foi processada!'], 400);
            }
            
            // Buscar fornecedor
            $fornecedor = DB::table('fornecedores')->where('id', $oc->fornecedor_id)->first();
            
            // Buscar cotação para descrição
            $cotacao = null;
            $observacaoCotacao = null;
            if ($oc->cotacao_id) {
                $cotacao = DB::table('cotacoes')->where('id', $oc->cotacao_id)->first();
                
                // Buscar observação do fornecedor vencedor na cotação (contém PIX, dados bancários, etc)
                $cotacaoFornecedor = DB::table('cotacao_fornecedores')
                    ->where('cotacao_id', $oc->cotacao_id)
                    ->where('fornecedor_id', $oc->fornecedor_id)
                    ->first();
                
                if ($cotacaoFornecedor && !empty($cotacaoFornecedor->observacao)) {
                    $observacaoCotacao = $cotacaoFornecedor->observacao;
                }
            }
            
            // Preparar dados para conta a pagar
            $valorTotal = $oc->valor_total ?? 0;
            $dataVencimento = $oc->data_previsao ?? now()->addDays(7)->format('Y-m-d');
            $dataEmissao = now()->format('Y-m-d');
            
            // Buscar itens da OC para descrição resumida
            $itensOC = DB::table('ordem_compra_itens')->where('ordem_compra_id', $id)->get();
            
            // Se não tem itens na OC, buscar da cotação
            if ($itensOC->isEmpty() && $oc->cotacao_id) {
                $itensOC = DB::table('cotacao_itens')->where('cotacao_id', $oc->cotacao_id)->get();
            }
            
            // Montar descrição com itens resumidos
            $descricaoItens = '';
            if ($itensOC->count() > 0) {
                $nomesProdutos = $itensOC->take(3)->pluck('produto')->toArray();
                $descricaoItens = implode(', ', $nomesProdutos);
                if ($itensOC->count() > 3) {
                    $descricaoItens .= ' (+' . ($itensOC->count() - 3) . ')';
                }
            } else {
                $descricaoItens = $cotacao->descricao ?? 'Compra de materiais';
            }
            
            // Montar dados dinamicamente verificando colunas existentes
            $contaPagarData = [
                'descricao' => $descricaoItens,
                'status' => 'pendente',
                'ordem_compra_id' => $id,
                'created_at' => now(),
            ];
            
            // Verificar colunas existentes na tabela contas_pagar
            $columns = [];
            try {
                $cols = DB::select("SHOW COLUMNS FROM contas_pagar");
                $columns = array_map(function($col) { return $col->Field; }, $cols);
            } catch (\Exception $e) {}
            
            // Adicionar campos condicionalmente
            if (in_array('fornecedor_id', $columns)) {
                $contaPagarData['fornecedor_id'] = $oc->fornecedor_id;
            }
            if (in_array('fornecedor', $columns)) {
                $contaPagarData['fornecedor'] = $fornecedor->razao_social ?? '';
            }
            if (in_array('valor', $columns)) {
                $contaPagarData['valor'] = $valorTotal;
            }
            if (in_array('valor_bruto', $columns)) {
                $contaPagarData['valor_bruto'] = $valorTotal;
            }
            if (in_array('valor_liquido', $columns)) {
                $contaPagarData['valor_liquido'] = $valorTotal;
            }
            if (in_array('vencimento', $columns)) {
                $contaPagarData['vencimento'] = $dataVencimento;
            }
            if (in_array('data_vencimento', $columns)) {
                $contaPagarData['data_vencimento'] = $dataVencimento;
            }
            if (in_array('data_emissao', $columns)) {
                $contaPagarData['data_emissao'] = $dataEmissao;
            }
            if (in_array('documento', $columns)) {
                $contaPagarData['documento'] = 'OC-' . $oc->numero;
            }
            if (in_array('observacoes', $columns) && $observacaoCotacao) {
                $contaPagarData['observacoes'] = $observacaoCotacao;
            }
            
            // Adicionar centro de custo da OC
            if (in_array('centro_custo_id', $columns) && $oc->centro_custo_id) {
                $contaPagarData['centro_custo_id'] = $oc->centro_custo_id;
            }
            
            // Verificar se há parcelas definidas para este fornecedor (via cotacao_fornecedores)
            $numeroParcelas = 1;
            if ($oc->cotacao_id) {
                $cotacaoFornecedor = DB::table('cotacao_fornecedores')
                    ->where('cotacao_id', $oc->cotacao_id)
                    ->where('fornecedor_id', $oc->fornecedor_id)
                    ->first();
                
                if ($cotacaoFornecedor && !empty($cotacaoFornecedor->parcelas) && $cotacaoFornecedor->parcelas > 1) {
                    $numeroParcelas = intval($cotacaoFornecedor->parcelas);
                }
            }
            
            $contaPagarIds = [];
            
            if ($numeroParcelas > 1) {
                // BOLETO PARCELADO - Criar múltiplas contas a pagar
                $valorParcela = round($valorTotal / $numeroParcelas, 2);
                $dataVencimentoBase = \Carbon\Carbon::parse($dataVencimento);
                
                for ($i = 0; $i < $numeroParcelas; $i++) {
                    $dataVencimentoParcela = $dataVencimentoBase->copy()->addMonths($i)->format('Y-m-d');
                    $contaPagarDataParcela = $contaPagarData;
                    
                    // Ajustar descrição para incluir número da parcela
                    $contaPagarDataParcela['descricao'] = 'Parcela ' . ($i + 1) . '/' . $numeroParcelas . ' - ' . $descricaoItens;
                    
                    // Ajustar valores
                    if (in_array('valor', $columns)) {
                        $contaPagarDataParcela['valor'] = $valorParcela;
                    }
                    if (in_array('valor_bruto', $columns)) {
                        $contaPagarDataParcela['valor_bruto'] = $valorParcela;
                    }
                    if (in_array('valor_liquido', $columns)) {
                        $contaPagarDataParcela['valor_liquido'] = $valorParcela;
                    }
                    
                    // Ajustar data de vencimento
                    if (in_array('vencimento', $columns)) {
                        $contaPagarDataParcela['vencimento'] = $dataVencimentoParcela;
                    }
                    if (in_array('data_vencimento', $columns)) {
                        $contaPagarDataParcela['data_vencimento'] = $dataVencimentoParcela;
                    }
                    
                    // Documento com número da parcela
                    if (in_array('documento', $columns)) {
                        $contaPagarDataParcela['documento'] = 'OC-' . $oc->numero . '-P' . ($i + 1);
                    }
                    
                    $contaPagarId = DB::table('contas_pagar')->insertGetId($contaPagarDataParcela);
                    $contaPagarIds[] = $contaPagarId;
                }
                
                \Log::info("OC #{$oc->numero} aprovada com {$numeroParcelas} parcelas. Contas a pagar criadas: " . implode(', ', $contaPagarIds));
                $mensagem = "Ordem de Compra aprovada! {$numeroParcelas} contas a pagar foram criadas no Financeiro (parcelas mensais).";
            } else {
                // PAGAMENTO À VISTA - Criar uma única conta a pagar
                $contaPagarId = DB::table('contas_pagar')->insertGetId($contaPagarData);
                $contaPagarIds[] = $contaPagarId;
                $mensagem = 'Ordem de Compra aprovada! Uma conta a pagar foi criada no Financeiro.';
            }
            
            // Atualizar O.C. com status aprovada e vincular primeira conta a pagar
            DB::table('ordens_compra')->where('id', $id)->update([
                'status' => 'aprovada',
                'conta_pagar_id' => $contaPagarIds[0],
                'status_pagamento' => 'aguardando_pagamento',
                'updated_at' => now(),
            ]);
            
            // NOVO: Se for OC de prestador de serviço, atualizar status do prestador
            if (\Schema::hasTable('ordens_servico_prestadores')) {
                $prestador = DB::table('ordens_servico_prestadores')
                    ->where('ordem_compra_id', $id)
                    ->first();
                
                if ($prestador) {
                    DB::table('ordens_servico_prestadores')
                        ->where('id', $prestador->id)
                        ->update([
                            'status_pagamento' => 'aguardando_pagamento',
                            'conta_pagar_id' => $contaPagarIds[0],
                            'updated_at' => now(),
                        ]);
                    \Log::info("Prestador ID {$prestador->id} atualizado para 'aguardando_pagamento' após aprovação da OC #{$oc->numero}");
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'conta_pagar_id' => $contaPagarIds[0],
                'total_parcelas' => count($contaPagarIds)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao aprovar O.C.: ' . $e->getMessage()], 500);
        }
    }
    
    public function recusarOrdemCompra($id)
    {
        try {
            $oc = DB::table('ordens_compra')->where('id', $id)->first();
            
            if (!$oc) {
                return response()->json(['success' => false, 'message' => 'Ordem de Compra não encontrada!'], 404);
            }
            
            if ($oc->status !== 'pendente') {
                return response()->json(['success' => false, 'message' => 'Esta O.C. já foi processada!'], 400);
            }
            
            // Atualizar status para cancelada
            DB::table('ordens_compra')->where('id', $id)->update([
                'status' => 'cancelada',
                'updated_at' => now(),
            ]);
            
            // NOVO: Se for OC de prestador de serviço, remover o prestador também
            if (\Schema::hasTable('ordens_servico_prestadores')) {
                $prestador = DB::table('ordens_servico_prestadores')
                    ->where('ordem_compra_id', $id)
                    ->first();
                
                if ($prestador) {
                    // Remover prestador da O.S. pois foi recusado
                    DB::table('ordens_servico_prestadores')
                        ->where('id', $prestador->id)
                        ->delete();
                    \Log::info("Prestador ID {$prestador->id} removido após recusa da OC #{$oc->numero}");
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Ordem de Compra recusada/cancelada.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao recusar O.C.: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Excluir Ordem de Compra (apenas administradores)
     */
    public function deleteOrdemCompra($id)
    {
        $user = auth()->user();
        
        // Apenas administradores podem excluir
        if (!$this->isAdmin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem excluir Ordens de Compra.'
            ], 403);
        }
        
        try {
            // Buscar dados da OC antes de excluir para o log
            $oc = DB::table('ordens_compra as oc')
                ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                ->leftJoin('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
                ->where('oc.id', $id)
                ->select('oc.*', 'f.razao_social as fornecedor', 'c.numero as cotacao_numero')
                ->first();
            
            if (!$oc) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordem de Compra não encontrada.'
                ], 404);
            }
            
            // Verificar se já foi paga ou recebida
            if (in_array($oc->status, ['recebida', 'recebida_parcial'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir: esta O.C. já foi recebida.'
                ], 400);
            }
            
            // Buscar itens da OC para o log (se a tabela existir)
            $itens = collect([]);
            if (\Schema::hasTable('ordens_compra_itens')) {
                $itens = DB::table('ordens_compra_itens')->where('ordem_compra_id', $id)->get();
            }
            
            // Excluir conta a pagar vinculada (se existir e não estiver paga)
            if (\Schema::hasTable('contas_pagar')) {
                $contaPagar = DB::table('contas_pagar')
                    ->where('ordem_compra_id', $id)
                    ->where('status', '!=', 'pago')
                    ->first();
                    
                if ($contaPagar) {
                    DB::table('contas_pagar')->where('id', $contaPagar->id)->delete();
                }
            }
            
            // Excluir itens da OC (se a tabela existir)
            if (\Schema::hasTable('ordens_compra_itens')) {
                DB::table('ordens_compra_itens')->where('ordem_compra_id', $id)->delete();
            }
            
            // Excluir a OC
            DB::table('ordens_compra')->where('id', $id)->delete();
            
            // Se a OC estava vinculada a uma cotação, LIMPAR TUDO da cotação e voltar para "aberta"
            // para que possa ser cotada novamente do zero
            if ($oc->cotacao_id) {
                // Apagar itens dos fornecedores da cotação
                $fornecedorIds = DB::table('cotacao_fornecedores')->where('cotacao_id', $oc->cotacao_id)->pluck('id');
                if ($fornecedorIds->count() > 0) {
                    DB::table('cotacao_fornecedor_itens')->whereIn('cotacao_fornecedor_id', $fornecedorIds)->delete();
                }
                
                // Apagar fornecedores da cotação
                DB::table('cotacao_fornecedores')->where('cotacao_id', $oc->cotacao_id)->delete();
                
                // Voltar cotação para status "aberta" (Aguardando Cotação)
                DB::table('cotacoes')->where('id', $oc->cotacao_id)->update([
                    'status' => 'aberta',
                    'updated_at' => now()
                ]);
            }
            
            // Registrar log
            $this->registrarLogOrdemCompra('exclusao', $oc, $user, $itens);
            
            $msgExtra = $oc->cotacao_id ? ' A cotação voltou para "Aguardando Cotação".' : '';
            
            return response()->json([
                'success' => true,
                'message' => 'Ordem de Compra excluída com sucesso!' . $msgExtra
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir O.C.: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir O.C.: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Registrar log de ações em Ordem de Compra
     */
    private function registrarLogOrdemCompra($acao, $oc, $user, $itens = null)
    {
        try {
            // Tentar salvar na tabela logs_ordens_compra se existir
            if (\Schema::hasTable('logs_ordens_compra')) {
                DB::table('logs_ordens_compra')->insert([
                    'ordem_compra_id' => $oc->id,
                    'numero' => $oc->numero,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'acao' => $acao,
                    'dados_oc' => json_encode([
                        'fornecedor' => $oc->fornecedor ?? null,
                        'fornecedor_id' => $oc->fornecedor_id ?? null,
                        'cotacao_id' => $oc->cotacao_id ?? null,
                        'cotacao_numero' => $oc->cotacao_numero ?? null,
                        'data_emissao' => $oc->data_emissao,
                        'valor_total' => $oc->valor_total,
                        'status' => $oc->status,
                        'itens' => $itens ? $itens->toArray() : []
                    ], JSON_UNESCAPED_UNICODE),
                    'ip' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 500),
                    'created_at' => now(),
                ]);
            }
            
            // Sempre registrar no log do Laravel também
            \Log::info("Ordem de Compra {$oc->numero} excluída", [
                'ordem_compra_id' => $oc->id,
                'numero' => $oc->numero,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'acao' => $acao,
                'fornecedor' => $oc->fornecedor ?? null,
                'valor_total' => $oc->valor_total,
                'status' => $oc->status,
                'qtd_itens' => $itens ? count($itens) : 0,
                'ip' => request()->ip(),
            ]);
            
        } catch (\Throwable $e) {
            \Log::warning('Falha ao registrar log de O.C.', [
                'ordem_compra_id' => $oc->id ?? null,
                'erro' => $e->getMessage(),
            ]);
        }
    }
    
    // ============================================
    // APIs - ORDENS DE COMPRA
    // ============================================
    
    public function storeOrdemCompra(Request $request)
    {
        try {
            $numero = 'OC-' . date('Y') . '-' . str_pad(DB::table('ordens_compra')->count() + 1, 3, '0', STR_PAD_LEFT);
            
            $valorTotal = 0;
            if ($request->itens) {
                foreach ($request->itens as $item) {
                    $valorTotal += floatval(str_replace(['.', ','], ['', '.'], $item['valor_total'] ?? 0));
                }
            }
            
            $dadosOC = [
                'numero' => $numero,
                'fornecedor_id' => $request->fornecedor_id,
                'data_emissao' => $request->data_oc ?? now()->format('Y-m-d'),
                'data_previsao' => $request->prazo_entrega,
                'valor_total' => $valorTotal,
                'status' => 'pendente',
                'observacoes' => $request->observacoes,
                'created_at' => now(),
            ];
            if ($request->centro_custo_id) {
                $dadosOC['centro_custo_id'] = $request->centro_custo_id;
            }
            OrdensCompraAuditoria::mergeCriadorUsuario($dadosOC);
            $id = DB::table('ordens_compra')->insertGetId($dadosOC);
            
            // Inserir itens
            if ($request->itens) {
                foreach ($request->itens as $item) {
                    if (!empty($item['produto'])) {
                        $valorUnit = floatval(str_replace(['.', ','], ['', '.'], $item['valor_unit'] ?? 0));
                        $valorTotal = floatval(str_replace(['.', ','], ['', '.'], $item['valor_total'] ?? 0));
                        
                        DB::table('ordem_compra_itens')->insert([
                            'ordem_compra_id' => $id,
                            'produto' => $item['produto'],
                            'quantidade' => $item['quantidade'] ?? 1,
                            'unidade' => $item['unidade'] ?? 'UN',
                            'valor_unitario' => $valorUnit,
                            'valor_total' => $valorTotal,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
            
            // Buscar nome do fornecedor para o log
            $fornecedor = DB::table('fornecedores')->where('id', $request->fornecedor_id)->first();
            
            // Registrar log de criação da O.C. manual
            $this->registrarLogOrdemCompraAcao('criacao_manual', $id, $numero, $request->fornecedor_id, $fornecedor->razao_social ?? null, $valorTotal, null);
            
            return response()->json(['success' => true, 'id' => $id, 'numero' => $numero, 'message' => 'Ordem de Compra criada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao criar OC: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Listar ordens de compra com filtros
     */
    public function listarOrdensCompra(Request $request)
    {
        try {
            $query = DB::table('ordens_compra as oc')
                ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                ->leftJoin('cotacoes as c', 'oc.cotacao_id', '=', 'c.id')
                ->leftJoin('ordens_servico as os', 'c.ordem_servico_id', '=', 'os.id')
                ->leftJoin('centros_custo as cc_os', 'os.centro_custo_id', '=', 'cc_os.id')
                ->leftJoin('centros_custo as cc_oc', 'oc.centro_custo_id', '=', 'cc_oc.id');

            if (\Schema::hasColumn('ordens_compra', 'created_by_user_id')) {
                $query->leftJoin('users as uc', 'oc.created_by_user_id', '=', 'uc.id');
            }

            $selectOc = [
                'oc.*',
                'f.razao_social as fornecedor',
                'c.numero as cotacao_numero',
                'c.descricao as descricao',
                DB::raw('COALESCE(cc_oc.nome, cc_os.nome) as centro_custo'),
                DB::raw('COALESCE(cc_oc.endereco, cc_os.endereco) as cc_endereco'),
                'os.cidade as os_cidade',
                'os.estado as os_estado',
            ];
            if (\Schema::hasColumn('ordens_compra', 'created_by_user_id')) {
                $selectOc[] = 'uc.name as criador_nome';
            } else {
                $selectOc[] = DB::raw('NULL as criador_nome');
            }

            $query->select($selectOc);
            
            // Filtro por data inicial
            if ($request->filled('data_inicio')) {
                $query->whereDate('oc.data_emissao', '>=', $request->data_inicio);
            }
            
            // Filtro por data final
            if ($request->filled('data_fim')) {
                $query->whereDate('oc.data_emissao', '<=', $request->data_fim);
            }
            
            // Filtro por status
            if ($request->filled('status')) {
                $query->where('oc.status', $request->status);
            }
            
            // Filtro por fornecedor
            if ($request->filled('fornecedor_id')) {
                $query->where('oc.fornecedor_id', $request->fornecedor_id);
            }
            
            // Filtro por centro de custo (obra) - considera tanto o CC direto da OC quanto o da O.S.
            if ($request->filled('centro_custo_id')) {
                $ccId = $request->centro_custo_id;
                $query->where(function($q) use ($ccId) {
                    $q->where('oc.centro_custo_id', $ccId)
                      ->orWhere('cc_os.id', $ccId);
                });
            }
            
            // Filtro por valor máximo
            if ($request->filled('valor_maximo')) {
                $valorMaximo = floatval($request->valor_maximo);
                if ($valorMaximo > 0) {
                    $query->where('oc.valor_total', '<=', $valorMaximo);
                }
            }
            
            $ordens = $query->orderBy('oc.data_emissao', 'desc')->get();
            
            // Buscar IDs de OCs que vieram de prestadores (terceiros)
            $ocsPrestadores = DB::table('ordens_servico_prestadores')
                ->whereNotNull('ordem_compra_id')
                ->pluck('ordem_compra_id')
                ->toArray();
            
            // Extrair município/UF do endereço do centro de custo e buscar produtos
            foreach ($ordens as $oc) {
                // Verificar se é OC de terceiro (prestador de serviço)
                $oc->tipo_origem = null;
                if ($oc->cotacao_id) {
                    $oc->tipo_origem = 'cotacao';
                } elseif (in_array($oc->id, $ocsPrestadores)) {
                    $oc->tipo_origem = 'terceiro';
                } else {
                    $oc->tipo_origem = 'manual';
                }
                
                $oc->municipio_uf = null;
                if (!empty($oc->cc_endereco)) {
                    // Tentar extrair "CIDADE/UF" do final do endereço (formato: "RUA..., CIDADE/UF")
                    if (preg_match('/([A-ZÁÉÍÓÚÂÊÔÃÕÇ\s]+)\/([A-Z]{2})\s*$/i', $oc->cc_endereco, $matches)) {
                        $oc->municipio_uf = trim($matches[1]) . '/' . strtoupper($matches[2]);
                    }
                }
                // Se não encontrou no endereço, usar cidade/estado da O.S.
                if (empty($oc->municipio_uf) && ($oc->os_cidade || $oc->os_estado)) {
                    $oc->municipio_uf = ($oc->os_cidade ?? '') . ($oc->os_cidade && $oc->os_estado ? '/' : '') . ($oc->os_estado ?? '');
                }
                
                // Buscar produtos/itens específicos do fornecedor na cotação
                $oc->produtos_resumo = null;
                $oc->qtd_itens = 0;
                
                if ($oc->cotacao_id && $oc->fornecedor_id) {
                    // Primeiro, buscar o cotacao_fornecedor_id para esse fornecedor nessa cotação
                    $cotacaoFornecedor = DB::table('cotacao_fornecedores')
                        ->where('cotacao_id', $oc->cotacao_id)
                        ->where('fornecedor_id', $oc->fornecedor_id)
                        ->first();
                    
                    if ($cotacaoFornecedor) {
                        // Buscar itens específicos desse fornecedor
                        $itens = DB::table('cotacao_fornecedor_itens as cfi')
                            ->leftJoin('cotacao_itens as ci', 'cfi.cotacao_item_id', '=', 'ci.id')
                            ->where('cfi.cotacao_fornecedor_id', $cotacaoFornecedor->id)
                            ->select('ci.produto')
                            ->get();
                        
                        $oc->qtd_itens = $itens->count();
                        
                        if ($itens->count() > 0) {
                            // Pegar os primeiros 2 produtos para exibição resumida
                            $produtos = $itens->take(2)->pluck('produto')->toArray();
                            $resumo = implode(', ', $produtos);
                            if ($itens->count() > 2) {
                                $resumo .= ' (+' . ($itens->count() - 2) . ')';
                            }
                            $oc->produtos_resumo = $resumo;
                        }
                    }
                    
                    // Se não encontrou itens específicos do fornecedor, busca todos da cotação (fallback)
                    if ($oc->qtd_itens == 0) {
                        $itens = DB::table('cotacao_itens')
                            ->where('cotacao_id', $oc->cotacao_id)
                            ->select('produto')
                            ->get();
                        
                        $oc->qtd_itens = $itens->count();
                        
                        if ($itens->count() > 0) {
                            $produtos = $itens->take(2)->pluck('produto')->toArray();
                            $resumo = implode(', ', $produtos);
                            if ($itens->count() > 2) {
                                $resumo .= ' (+' . ($itens->count() - 2) . ')';
                            }
                            $oc->produtos_resumo = $resumo;
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'ordens' => $ordens
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro listarOrdensCompra: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar ordens de compra.'
            ], 500);
        }
    }
    
    public function getOrdemCompra($id)
    {
        $oc = DB::table('ordens_compra as oc')
            ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
            ->select('oc.*', 'f.razao_social as fornecedor')
            ->where('oc.id', $id)
            ->first();
        
        // Primeiro tenta buscar itens da ordem de compra
        $itens = DB::table('ordem_compra_itens')->where('ordem_compra_id', $id)->get();
        
        // Se não tem itens na OC, busca da cotação vinculada
        if ($itens->isEmpty() && $oc && $oc->cotacao_id) {
            $itens = DB::table('cotacao_itens')
                ->where('cotacao_id', $oc->cotacao_id)
                ->select('id', 'produto', 'quantidade', 'unidade', 'created_at')
                ->get();
        }
        
        // Buscar arquivo de orçamento, forma de pagamento e observação do fornecedor vencedor da cotação
        $arquivoOrcamento = null;
        $condicaoPagamento = null;
        $observacao = null;
        if ($oc && $oc->cotacao_id && $oc->fornecedor_id) {
            $cotacaoFornecedor = DB::table('cotacao_fornecedores')
                ->where('cotacao_id', $oc->cotacao_id)
                ->where('fornecedor_id', $oc->fornecedor_id)
                ->first();
            
            if ($cotacaoFornecedor) {
                if (!empty($cotacaoFornecedor->arquivo_orcamento)) {
                    $arquivoOrcamento = $cotacaoFornecedor->arquivo_orcamento;
                }
                if (!empty($cotacaoFornecedor->condicao_pagamento)) {
                    $condicaoPagamento = $cotacaoFornecedor->condicao_pagamento;
                }
                if (isset($cotacaoFornecedor->observacao) && !empty($cotacaoFornecedor->observacao)) {
                    $observacao = $cotacaoFornecedor->observacao;
                }
            }
        }
        
        return response()->json([
            'ordem' => $oc, 
            'itens' => $itens, 
            'arquivo_orcamento' => $arquivoOrcamento,
            'condicao_pagamento' => $condicaoPagamento,
            'observacao' => $observacao
        ]);
    }
    
    public function updateStatusOC(Request $request, $id)
    {
        try {
            DB::table('ordens_compra')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Status atualizado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()], 500);
        }
    }
    
    // ============================================
    // APIs - RECEBIMENTOS
    // ============================================
    
    /**
     * Quantidade no recebimento (aceita vírgula decimal estilo BR).
     */
    protected function parseQuantidadeRecebimentoItem($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $s = str_replace([' ', ','], ['', '.'], (string) $value);

        return (float) $s;
    }

    /**
     * Unidade normalizada para o cadastro de estoque.
     */
    protected function normalizarUnidadeRecebimento(?string $u, string $fallback = 'UN'): string
    {
        $u = trim((string) $u);
        if ($u === '') {
            return $fallback;
        }

        return strtoupper(mb_substr($u, 0, 20));
    }
    
    public function storeRecebimento(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Preparar dados do recebimento
            $recebimentoData = [
                'ordem_compra_id' => $request->ordem_compra_id,
                'data_recebimento' => $request->data_recebimento ?? now()->format('Y-m-d'),
                'responsavel_id' => auth()->id(),
                'nf_numero' => $request->nf_numero,
                'observacoes' => $request->observacoes,
                'created_at' => now(),
            ];
            
            // Processar upload do arquivo da NF (PDF ou Imagem)
            $hasArquivoNf = \Schema::hasColumn('recebimentos', 'arquivo_nf');
            if ($hasArquivoNf && $request->hasFile('arquivo_nf')) {
                $arquivo = $request->file('arquivo_nf');
                if ($arquivo->isValid()) {
                    // Obter a extensão original do arquivo
                    $extensao = strtolower($arquivo->getClientOriginalExtension());
                    
                    // Validar extensões permitidas
                    $extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($extensao, $extensoesPermitidas)) {
                        $extensao = 'pdf'; // fallback
                    }
                    
                    // Gerar nome único para o arquivo mantendo a extensão original
                    $nomeArquivo = 'NF_' . ($request->nf_numero ? preg_replace('/[^a-zA-Z0-9]/', '', $request->nf_numero) . '_' : '') . date('YmdHis') . '_' . uniqid() . '.' . $extensao;
                    
                    // Determinar o caminho correto para public_html/storage
                    // O site serve de public_html, não de beta2/public
                    $basePath = base_path();
                    $publicHtmlPath = dirname($basePath) . '/public_html/storage/notas_fiscais';
                    
                    // Criar diretório se não existir
                    if (!file_exists($publicHtmlPath)) {
                        mkdir($publicHtmlPath, 0755, true);
                    }
                    
                    // Mover arquivo diretamente para public_html/storage
                    $arquivo->move($publicHtmlPath, $nomeArquivo);
                    $recebimentoData['arquivo_nf'] = 'notas_fiscais/' . $nomeArquivo;
                }
            }
            
            $recebimentoId = DB::table('recebimentos')->insertGetId($recebimentoData);
            
            // Processar itens
            $itens = $request->itens ?? [];
            $produtosCriados = 0;
            $produtosVinculados = 0;
            
            foreach ($itens as $item) {
                $produtoId = $item['produto_id'] ?? null;
                $quantidade = $this->parseQuantidadeRecebimentoItem($item['quantidade'] ?? 0);
                $descricao = $item['descricao'] ?? 'Item';
                $unidade = $this->normalizarUnidadeRecebimento($item['unidade'] ?? null);
                
                // Se marcou para criar novo produto OU não vinculou a nenhum produto
                // Criar automaticamente no estoque para garantir entrada
                if ($produtoId === 'NOVO' || empty($produtoId) || !is_numeric($produtoId)) {
                    // Criar produto no estoque automaticamente
                    $produtoId = DB::table('estoque')->insertGetId([
                        'nome' => $descricao,
                        'descricao' => 'Criado automaticamente via recebimento OC',
                        'unidade' => $unidade,
                        'quantidade' => $quantidade,
                        'preco_custo' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Registrar log de entrada
                    DB::table('logs_estoque')->insert([
                        'produto_id' => $produtoId,
                        'user_id' => auth()->id(),
                        'tipo' => 'entrada',
                        'quantidade_anterior' => 0,
                        'quantidade_alterada' => $quantidade,
                        'quantidade_nova' => $quantidade,
                        'origem' => 'recebimento',
                        'observacao' => 'Entrada via recebimento de OC #' . $request->ordem_compra_id,
                        'created_at' => now(),
                    ]);
                    
                    $produtosCriados++;
                } else {
                    // Vincular a produto existente
                    $produto = DB::table('estoque')->where('id', $produtoId)->first();
                    if ($produto) {
                        // Verificar se usuário marcou "já cadastrei" (não somar quantidade)
                        $jaCadastrado = isset($item['ja_cadastrado']) && $item['ja_cadastrado'] == '1';

                        $updateEstoque = ['updated_at' => now()];
                        if (\Schema::hasColumn('estoque', 'unidade')) {
                            $updateEstoque['unidade'] = $unidade;
                        }
                        
                        if ($jaCadastrado) {
                            DB::table('estoque')->where('id', $produtoId)->update($updateEstoque);
                            // Apenas vincular, sem somar quantidade (produto já foi cadastrado manualmente)
                            // Usar tipo 'ajuste' com quantidade 0 para registrar a vinculação
                            DB::table('logs_estoque')->insert([
                                'produto_id' => $produtoId,
                                'user_id' => auth()->id(),
                                'tipo' => 'ajuste',
                                'quantidade_anterior' => $produto->quantidade,
                                'quantidade_alterada' => 0,
                                'quantidade_nova' => $produto->quantidade,
                                'origem' => 'recebimento',
                                'observacao' => 'Vinculado via recebimento OC #' . $request->ordem_compra_id . ' (sem entrada - produto já cadastrado); unidade padronizada: ' . $unidade,
                                'created_at' => now(),
                            ]);
                        } else {
                            // Comportamento normal: dar entrada no estoque (somar quantidade) e padronizar unidade
                            $qtdAnterior = (float) $produto->quantidade;
                            $qtdNova = $qtdAnterior + $quantidade;
                            $updateEstoque['quantidade'] = $qtdNova;
                            
                            DB::table('estoque')->where('id', $produtoId)->update($updateEstoque);
                            
                            // Registrar log de entrada
                            DB::table('logs_estoque')->insert([
                                'produto_id' => $produtoId,
                                'user_id' => auth()->id(),
                                'tipo' => 'entrada',
                                'quantidade_anterior' => $qtdAnterior,
                                'quantidade_alterada' => $quantidade,
                                'quantidade_nova' => $qtdNova,
                                'origem' => 'recebimento',
                                'observacao' => 'Entrada via recebimento de OC #' . $request->ordem_compra_id . '; unidade: ' . $unidade,
                                'created_at' => now(),
                            ]);
                        }
                        
                        $produtosVinculados++;
                    }
                }
                
                // Salvar item do recebimento (sempre vinculado agora)
                DB::table('recebimento_itens')->insert([
                    'recebimento_id' => $recebimentoId,
                    'cotacao_item_id' => $item['cotacao_item_id'] ?? null,
                    'produto_id' => $produtoId,
                    'descricao' => $descricao,
                    'quantidade' => $quantidade,
                    'unidade' => $unidade,
                    'vinculado_estoque' => 1,
                    'created_at' => now(),
                ]);
            }
            
            // Atualizar status da OC
            $statusNovo = $request->recebimento_parcial == '1' ? 'recebida_parcial' : 'recebida';
            DB::table('ordens_compra')->where('id', $request->ordem_compra_id)->update([
                'status' => $statusNovo,
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            $mensagem = 'Recebimento registrado com sucesso!';
            if ($produtosCriados > 0) {
                $mensagem .= " {$produtosCriados} produto(s) criado(s).";
            }
            if ($produtosVinculados > 0) {
                $mensagem .= " {$produtosVinculados} produto(s) atualizado(s) no estoque.";
            }
            
            return response()->json(['success' => true, 'id' => $recebimentoId, 'message' => $mensagem]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao registrar: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Validar itens do recebimento antes de confirmar - detecta possíveis duplicações
     */
    public function validarRecebimento(Request $request)
    {
        try {
            $itens = $request->itens ?? [];
            $alertas = [];
            
            foreach ($itens as $index => $item) {
                $produtoId = $item['produto_id'] ?? null;
                $quantidade = $this->parseQuantidadeRecebimentoItem($item['quantidade'] ?? 0);
                $descricao = $item['descricao'] ?? 'Item';
                
                // Se está vinculando a um produto existente
                if (!empty($produtoId) && is_numeric($produtoId) && $produtoId !== 'NOVO') {
                    $produto = DB::table('estoque')->where('id', $produtoId)->first();
                    
                    if ($produto) {
                        // Verificar se o produto foi criado nos últimos 30 minutos
                        $criadoRecentemente = false;
                        if ($produto->created_at) {
                            $minutosDesde = now()->diffInMinutes(\Carbon\Carbon::parse($produto->created_at));
                            $criadoRecentemente = $minutosDesde <= 30;
                        }
                        
                        // Verificar se a quantidade atual é igual à quantidade que está sendo recebida
                        $mesmaQuantidade = abs((float) $produto->quantidade - $quantidade) < 0.0000001;
                        
                        // Se foi criado recentemente E tem a mesma quantidade = possível duplicação
                        if ($criadoRecentemente && $mesmaQuantidade) {
                            $alertas[] = [
                                'tipo' => 'duplicacao',
                                'item_index' => $index,
                                'produto_id' => $produtoId,
                                'produto_nome' => $produto->nome,
                                'quantidade_atual' => (int)$produto->quantidade,
                                'quantidade_receber' => $quantidade,
                                'criado_ha' => $minutosDesde . ' minutos',
                                'mensagem' => "O produto \"{$produto->nome}\" foi criado há {$minutosDesde} minutos com {$produto->quantidade} unidades. " .
                                             "Você está tentando dar entrada de mais {$quantidade} unidades. " .
                                             "Isso pode ser uma DUPLICAÇÃO. Deseja continuar?"
                            ];
                        }
                        // Se a quantidade atual é igual e produto foi criado hoje (mesmo que há mais de 30 min)
                        else if ($mesmaQuantidade && $produto->created_at) {
                            $criadoHoje = \Carbon\Carbon::parse($produto->created_at)->isToday();
                            if ($criadoHoje) {
                                $alertas[] = [
                                    'tipo' => 'aviso',
                                    'item_index' => $index,
                                    'produto_id' => $produtoId,
                                    'produto_nome' => $produto->nome,
                                    'quantidade_atual' => (int)$produto->quantidade,
                                    'quantidade_receber' => $quantidade,
                                    'mensagem' => "O produto \"{$produto->nome}\" foi criado HOJE com {$produto->quantidade} unidades. " .
                                                 "Verifique se não é duplicação antes de continuar."
                                ];
                            }
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'tem_alertas' => count($alertas) > 0,
                'alertas' => $alertas
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro na validação: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Buscar detalhes de um recebimento específico
     */
    public function getRecebimento($id)
    {
        try {
            $recebimento = DB::table('recebimentos as r')
                ->leftJoin('ordens_compra as oc', 'r.ordem_compra_id', '=', 'oc.id')
                ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                ->where('r.id', $id)
                ->select(
                    'r.*',
                    'oc.numero as ordem_numero',
                    'f.razao_social as fornecedor',
                    'f.nome_fantasia as fornecedor_fantasia'
                )
                ->first();
            
            if (!$recebimento) {
                return response()->json(['success' => false, 'message' => 'Recebimento não encontrado.'], 404);
            }
            
            // Buscar itens do recebimento
            $itens = DB::table('recebimento_itens as ri')
                ->leftJoin('estoque as e', 'ri.produto_id', '=', 'e.id')
                ->where('ri.recebimento_id', $id)
                ->select('ri.*', 'e.nome as produto_nome')
                ->get();
            
            return response()->json([
                'success' => true,
                'recebimento' => $recebimento,
                'itens' => $itens
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Imprimir comprovante de recebimento
     */
    public function imprimirRecebimento($id)
    {
        try {
            $recebimento = DB::table('recebimentos as r')
                ->leftJoin('ordens_compra as oc', 'r.ordem_compra_id', '=', 'oc.id')
                ->leftJoin('fornecedores as f', 'oc.fornecedor_id', '=', 'f.id')
                ->leftJoin('users as u', 'r.responsavel_id', '=', 'u.id')
                ->where('r.id', $id)
                ->select(
                    'r.*',
                    'oc.numero as ordem_numero',
                    'oc.valor_total',
                    'f.razao_social as fornecedor',
                    'f.nome_fantasia as fornecedor_fantasia',
                    'f.cnpj as fornecedor_cnpj',
                    'u.name as responsavel_nome'
                )
                ->first();
            
            if (!$recebimento) {
                abort(404, 'Recebimento não encontrado.');
            }
            
            // Buscar itens do recebimento
            $itens = DB::table('recebimento_itens as ri')
                ->leftJoin('estoque as e', 'ri.produto_id', '=', 'e.id')
                ->where('ri.recebimento_id', $id)
                ->select('ri.*', 'e.nome as produto_nome')
                ->get();
            
            return view('suprimentos.recebimento-imprimir', compact('recebimento', 'itens'));
        } catch (\Exception $e) {
            abort(500, 'Erro ao carregar recebimento: ' . $e->getMessage());
        }
    }
    
    public function destroyRecebimento($id)
    {
        try {
            // Verificar se usuário é administrador
            $user = auth()->user();
            $isAdmin = $user && ($user->profile_id == 1 || strtolower($user->perfil ?? '') === 'administrador');
            if (!$isAdmin) {
                return response()->json(['success' => false, 'message' => 'Apenas administradores podem excluir recebimentos.'], 403);
            }
            
            $recebimento = DB::table('recebimentos')->where('id', $id)->first();
            if (!$recebimento) {
                return response()->json(['success' => false, 'message' => 'Recebimento não encontrado.'], 404);
            }
            
            DB::beginTransaction();
            
            // Reverter estoque dos itens vinculados
            $itens = DB::table('recebimento_itens')->where('recebimento_id', $id)->get();
            foreach ($itens as $item) {
                if ($item->vinculado_estoque && $item->produto_id) {
                    $produto = DB::table('estoque')->where('id', $item->produto_id)->first();
                    if ($produto) {
                        $qtdAnterior = $produto->quantidade;
                        $qtdNova = max(0, $qtdAnterior - $item->quantidade);
                        
                        DB::table('estoque')->where('id', $item->produto_id)->update([
                            'quantidade' => $qtdNova,
                            'updated_at' => now(),
                        ]);
                        
                        // Registrar log de saída (estorno)
                        DB::table('logs_estoque')->insert([
                            'produto_id' => $item->produto_id,
                            'user_id' => auth()->id(),
                            'tipo' => 'saida',
                            'quantidade_anterior' => $qtdAnterior,
                            'quantidade_alterada' => $item->quantidade,
                            'quantidade_nova' => $qtdNova,
                            'origem' => 'estorno_recebimento',
                            'observacao' => 'Estorno por exclusão de recebimento #' . $id,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
            
            // Excluir itens do recebimento
            DB::table('recebimento_itens')->where('recebimento_id', $id)->delete();
            
            // Reverter status da OC para aprovada (liberada para novo recebimento)
            if ($recebimento->ordem_compra_id) {
                DB::table('ordens_compra')->where('id', $recebimento->ordem_compra_id)->update([
                    'status' => 'aprovada',
                    'updated_at' => now(),
                ]);
            }
            
            // Excluir recebimento
            DB::table('recebimentos')->where('id', $id)->delete();
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Recebimento excluído e estoque revertido com sucesso.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()], 500);
        }
    }
    
    // ============================================
    // APIs - NF ENTRADA
    // ============================================
    
    public function storeNFEntrada(Request $request)
    {
        try {
            $id = DB::table('nf_entrada')->insertGetId([
                'numero' => $request->numero_nf,
                'serie' => $request->serie ?? '1',
                'fornecedor_id' => $request->fornecedor_id,
                'ordem_compra_id' => $request->ordem_compra_id,
                'data_emissao' => $request->data_emissao,
                'data_entrada' => $request->data_entrada ?? now()->format('Y-m-d'),
                'valor_total' => floatval(str_replace(['.', ','], ['', '.'], $request->valor_produtos ?? 0)),
                'chave_acesso' => $request->chave_acesso,
                'observacoes' => $request->observacoes,
                'created_at' => now(),
            ]);
            
            return response()->json(['success' => true, 'id' => $id, 'message' => 'Nota Fiscal lançada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao lançar NF: ' . $e->getMessage()], 500);
        }
    }
    
    // ============================================
    // APIs - VALES DE RETIRADA
    // ============================================
    
    public function storeVale(Request $request)
    {
        try {
            $numero = 'VR-' . date('Y') . '-' . str_pad(DB::table('vales_retirada')->count() + 1, 3, '0', STR_PAD_LEFT);
            
            $id = DB::table('vales_retirada')->insertGetId([
                'numero' => $numero,
                'data_retirada' => $request->data_retirada ?? now()->format('Y-m-d'),
                'destino' => $request->destino,
                'responsavel_retirada' => $request->responsavel_retirada,
                'status' => 'pendente',
                'observacoes' => $request->observacoes,
                'created_at' => now(),
            ]);
            
            return response()->json(['success' => true, 'id' => $id, 'numero' => $numero, 'message' => 'Vale de Retirada criado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao criar vale: ' . $e->getMessage()], 500);
        }
    }
    
    public function updateStatusVale(Request $request, $id)
    {
        try {
            DB::table('vales_retirada')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Status atualizado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()], 500);
        }
    }
    
    // ============================================
    // APIs - EXCLUSÃO DE COTAÇÃO (APENAS ADMIN)
    // ============================================
    
    /**
     * Rejeitar cotação (administradores OU quem tem permissão ex_cot)
     * A cotação fica com status "rejeitada" - NÃO exclui nada, mantém todo o histórico
     */
    public function deleteCotacao(Request $request, $id)
    {
        $user = auth()->user();
        
        // Verificar se é admin OU tem permissão ex_cot
        if (!$this->canDeleteCotacao($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para rejeitar cotações.'
            ], 403);
        }
        
        // Verificar se o motivo foi informado
        $motivo = $request->input('motivo');
        if (empty($motivo) || strlen(trim($motivo)) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'É obrigatório informar o motivo da rejeição (mínimo 10 caracteres).'
            ], 400);
        }
        
        try {
            // Buscar dados da cotação
            $cotacao = DB::table('cotacoes')->where('id', $id)->first();
            
            if (!$cotacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotação não encontrada.'
                ], 404);
            }
            
            // Verificar se já gerou Ordem de Compra
            $temOC = DB::table('ordens_compra')->where('cotacao_id', $id)->exists();
            if ($temOC) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível rejeitar: esta cotação já gerou uma Ordem de Compra.'
                ], 400);
            }
            
            // Buscar itens e fornecedores para o log (NÃO EXCLUIR!)
            $itens = DB::table('cotacao_itens')->where('cotacao_id', $id)->get();
            $fornecedores = DB::table('cotacao_fornecedores as cf')
                ->leftJoin('fornecedores as f', 'cf.fornecedor_id', '=', 'f.id')
                ->where('cf.cotacao_id', $id)
                ->select('cf.*', 'f.razao_social')
                ->get();
            
            // REJEITAR a cotação - apenas muda o status, NÃO exclui fornecedores nem itens
            DB::table('cotacoes')->where('id', $id)->update([
                'status' => 'rejeitada',
                'motivo_cancelamento' => trim($motivo),
                'cancelado_por' => $user->id,
                'cancelado_em' => now(),
                'updated_at' => now()
            ]);
            
            // Registrar log
            $this->registrarLogCotacao('rejeicao', $cotacao, $user, $itens, $fornecedores, $motivo);
            
            return response()->json([
                'success' => true,
                'message' => 'Cotação rejeitada com sucesso! O histórico foi mantido para consulta.'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao rejeitar cotação: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao rejeitar cotação: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verificar se usuário é admin
     */
    private function isAdmin($user)
    {
        if (!$user || !$user->profile_id) {
            return false;
        }
        
        // Verificar se tem perfil de administrador
        $perfil = DB::table('profiles')->where('id', $user->profile_id)->first();
        
        if ($perfil && strtolower($perfil->name) === 'administrador') {
            return true;
        }
        
        // Ou verificar se tem muitas permissões (80% ou mais)
        $totalPermissoes = DB::table('permissions')->count();
        $permissoesUsuario = DB::table('profile_permissions')
            ->where('profile_id', $user->profile_id)
            ->count();
        
        if ($totalPermissoes > 0 && ($permissoesUsuario / $totalPermissoes) >= 0.8) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Verificar se usuário tem permissão específica
     */
    private function hasPermission($user, $permissionCode)
    {
        if (!$user || !$user->profile_id) {
            return false;
        }
        
        try {
            // Buscar a permissão pelo código
            $permission = DB::table('permissions')->where('code', $permissionCode)->first();
            
            if (!$permission) {
                return false;
            }
            
            // Verificar se o perfil do usuário tem essa permissão
            $temPermissao = DB::table('profile_permissions')
                ->where('profile_id', $user->profile_id)
                ->where('permission_id', $permission->id)
                ->exists();
            
            return $temPermissao;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Verificar se usuário pode excluir cotação/solicitação (admin OU tem permissão ex_cot)
     */
    private function canDeleteCotacao($user)
    {
        // Admin sempre pode
        if ($this->isAdmin($user)) {
            return true;
        }
        
        // Ou quem tem a permissão ex_cot
        return $this->hasPermission($user, 'ex_cot');
    }
    
    /**
     * Registrar log de ações em Ordem de Compra (para criação com parâmetros individuais)
     */
    private function registrarLogOrdemCompraAcao($acao, $ocId, $numero, $fornecedorId, $fornecedorNome, $valorTotal, $cotacaoId = null)
    {
        OrdensCompraAuditoria::registrarLogCriacao(
            (int) $ocId,
            (string) $numero,
            (string) $acao,
            $fornecedorId,
            $fornecedorNome,
            $valorTotal,
            $cotacaoId,
            null
        );
    }
    
    /**
     * Registrar log de ações em Cotação
     */
    private function registrarLogCotacao($acao, $cotacao, $user, $itens = null, $fornecedores = null, $motivo = null)
    {
        try {
            // Tentar salvar na tabela logs_cotacoes se existir
            if (\Schema::hasTable('logs_cotacoes')) {
                DB::table('logs_cotacoes')->insert([
                    'cotacao_id' => $cotacao->id,
                    'numero' => $cotacao->numero,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'acao' => $acao,
                    'dados_cotacao' => json_encode([
                        'descricao' => $cotacao->descricao,
                        'data_solicitacao' => $cotacao->data_solicitacao,
                        'data_limite' => $cotacao->data_limite,
                        'status' => $cotacao->status,
                        'motivo' => $motivo,
                        'itens' => $itens ? $itens->toArray() : [],
                        'fornecedores' => $fornecedores ? $fornecedores->toArray() : []
                    ], JSON_UNESCAPED_UNICODE),
                    'ip' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 500),
                    'created_at' => now(),
                ]);
            }
            
            // Sempre registrar no log do Laravel também
            \Log::info("Cotação {$cotacao->numero} - Ação: {$acao}", [
                'cotacao_id' => $cotacao->id,
                'numero' => $cotacao->numero,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'acao' => $acao,
                'motivo' => $motivo,
                'descricao' => $cotacao->descricao,
                'status' => $cotacao->status,
                'qtd_itens' => $itens ? count($itens) : 0,
                'qtd_fornecedores' => $fornecedores ? count($fornecedores) : 0,
                'ip' => request()->ip(),
            ]);
            
        } catch (\Throwable $e) {
            \Log::warning('Falha ao registrar log de Cotação', [
                'cotacao_id' => $cotacao->id ?? null,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}

