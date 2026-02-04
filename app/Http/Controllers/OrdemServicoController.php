<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class OrdemServicoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Página principal de Ordem de Serviço
     */
    public function index()
    {
        return view('area-tecnica.ordem-servico');
    }

    /**
     * Listar ordens de serviço com filtros
     */
    public function listar(Request $request)
    {
        $query = DB::table('ordens_servico')
            ->leftJoin('funcionarios', 'ordens_servico.funcionario_id', '=', 'funcionarios.id')
            ->select(
                'ordens_servico.*',
                'funcionarios.nome as funcionario_nome'
            );

        // Filtro por número da O.S.
        if ($request->filled('numero_os')) {
            $query->where('ordens_servico.numero_os', 'LIKE', '%' . $request->numero_os . '%');
        } else {
            // Filtro por período
            if ($request->filled('data_inicial')) {
                $query->where('ordens_servico.data_os', '>=', $request->data_inicial);
            }
            if ($request->filled('data_final')) {
                $query->where('ordens_servico.data_os', '<=', $request->data_final);
            }
        }

        $ordens = $query->orderBy('ordens_servico.data_os', 'desc')
            ->orderBy('ordens_servico.id', 'desc')
            ->get();

        return response()->json($ordens);
    }

    /**
     * Gerar próximo número de O.S.
     */
    public function proximoNumero()
    {
        $hoje = date('Ymd');
        $prefixo = 'OS-' . $hoje . '-';

        // Buscar último número do dia
        $ultimaOS = DB::table('ordens_servico')
            ->where('numero_os', 'LIKE', $prefixo . '%')
            ->orderBy('numero_os', 'desc')
            ->first();

        if ($ultimaOS) {
            $ultimoNumero = intval(substr($ultimaOS->numero_os, -4));
            $proximoNumero = $ultimoNumero + 1;
        } else {
            $proximoNumero = 1;
        }

        return response()->json([
            'numero_os' => $prefixo . str_pad($proximoNumero, 4, '0', STR_PAD_LEFT)
        ]);
    }

    /**
     * Buscar funcionários
     */
    public function buscarFuncionarios(Request $request)
    {
        $nome = $request->get('nome', '');

        if (strlen($nome) < 3) {
            return response()->json([]);
        }

        $funcionarios = DB::table('funcionarios')
            ->where('nome', 'LIKE', '%' . $nome . '%')
            ->where('status', 'trabalhando')
            ->select('id', 'nome', 'funcao')
            ->limit(10)
            ->get();

        return response()->json($funcionarios);
    }

    /**
     * Criar nova O.S.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'data_os' => 'required|date',
                'descricao' => 'required|string',
            ], [
                'data_os.required' => 'A data é obrigatória',
                'descricao.required' => 'A descrição do serviço é obrigatória',
            ]);

            // Verificar se o número da O.S. já existe e gerar um novo se necessário
            $numeroOS = $request->numero_os;
        $tentativas = 0;
        while (DB::table('ordens_servico')->where('numero_os', $numeroOS)->exists() && $tentativas < 100) {
            // Gerar novo número
            $hoje = date('Ymd');
            $prefixo = 'OS-' . $hoje . '-';
            $ultimaOS = DB::table('ordens_servico')
                ->where('numero_os', 'LIKE', $prefixo . '%')
                ->orderBy('numero_os', 'desc')
                ->first();
            
            $proximoNumero = 1;
            if ($ultimaOS) {
                $ultimoNumero = intval(substr($ultimaOS->numero_os, -4));
                $proximoNumero = $ultimoNumero + 1;
            }
            $numeroOS = $prefixo . str_pad($proximoNumero, 4, '0', STR_PAD_LEFT);
            $tentativas++;
        }

        // Tratar funcionario_id - se vazio, 0, ou não existir na tabela funcionarios, definir como null
        $funcionarioId = $request->funcionario_id;
        if (empty($funcionarioId) || $funcionarioId == 0 || $funcionarioId == '0') {
            $funcionarioId = null;
        } else {
            // Verificar se o funcionario_id existe na tabela funcionarios
            $funcionarioExiste = DB::table('funcionarios')->where('id', $funcionarioId)->exists();
            if (!$funcionarioExiste) {
                $funcionarioId = null;
            }
        }
        
        $dados = [
            'user_id' => Auth::id(),
            'data_os' => $request->data_os,
            'numero_os' => $numeroOS,
            'funcionario_id' => $funcionarioId,
            'descricao' => $request->descricao,
            'endereco' => $request->endereco,
            'cidade' => $request->cidade,
            'estado' => $request->estado,
            'cep' => $request->cep,
            'telefone' => $request->telefone,
            'cpf_cnpj' => $request->cpf_cnpj,
            'observacoes' => $request->observacoes,
        ];

        // Adiciona tipo_atendimento se a coluna existir
        if (Schema::hasColumn('ordens_servico', 'tipo_atendimento')) {
            $dados['tipo_atendimento'] = $request->tipo_atendimento;
        }

        // Adiciona centro_custo_id se a coluna existir
        if (Schema::hasColumn('ordens_servico', 'centro_custo_id')) {
            $dados['centro_custo_id'] = $request->centro_custo_id;
        }

        // Adiciona urgencia se a coluna existir
        if (Schema::hasColumn('ordens_servico', 'urgencia')) {
            $dados['urgencia'] = $request->urgencia ?? 'normal';
        }

        // Adiciona status como 'aberta' se a coluna existir
        if (Schema::hasColumn('ordens_servico', 'status')) {
            $dados['status'] = 'aberta';
        }

        $id = DB::table('ordens_servico')->insertGetId($dados);

        // Salvar prestadores de serviço se a tabela existir
        if (Schema::hasTable('ordens_servico_prestadores') && $request->has('prestadores')) {
            $prestadores = $request->prestadores;
            if (is_array($prestadores)) {
                // Verificar quais colunas existem na tabela
                $hasStatusPagamento = Schema::hasColumn('ordens_servico_prestadores', 'status_pagamento');
                $hasContaPagarId = Schema::hasColumn('ordens_servico_prestadores', 'conta_pagar_id');
                
                foreach ($prestadores as $prestador) {
                    $valor = $prestador['valor'] ?? 0;
                    
                    // Dados base do prestador
                    $dadosPrestador = [
                        'ordem_servico_id' => $id,
                        'nome_prestador' => $prestador['nome_prestador'],
                        'descricao_servico' => $prestador['descricao_servico'] ?? null,
                        'valor' => $valor,
                        'data_servico' => $request->data_os,
                        'created_at' => now(),
                    ];
                    
                    // Status inicial: aguardando autorização (novo fluxo com OC)
                    if ($hasStatusPagamento) {
                        $dadosPrestador['status_pagamento'] = 'aguardando_autorizacao';
                    }
                    
                    // Inserir prestador
                    $prestadorId = DB::table('ordens_servico_prestadores')->insertGetId($dadosPrestador);
                    
                    // NOVO FLUXO: Criar OC para o prestador (ao invés de conta a pagar direto)
                    if ($valor > 0) {
                        $vencimento = $prestador['vencimento'] ?? null;
                        $ocId = $this->criarOCPrestador(
                            $prestadorId,
                            $prestador['nome_prestador'],
                            $prestador['descricao_servico'] ?? null,
                            $valor,
                            $numeroOS,
                            $request->centro_custo_id,
                            $request->data_os,
                            $vencimento,
                            $id // ordem_servico_id
                        );
                        
                        // Vincular OC ao prestador
                        if ($ocId) {
                            DB::table('ordens_servico_prestadores')
                                ->where('id', $prestadorId)
                                ->update(['ordem_compra_id' => $ocId]);
                        }
                    }
                }
            }
        }

        // Salvar materiais se a tabela existir
        if (Schema::hasTable('ordens_servico_itens') && $request->has('materiais')) {
            $materiais = $request->materiais;
            if (is_array($materiais)) {
                foreach ($materiais as $material) {
                    $produtoId = $material['produto_id'];
                    $quantidade = (int) $material['quantidade'];
                    
                    // Verificar estoque atual
                    $produto = DB::table('estoque')->where('id', $produtoId)->first();
                    if (!$produto) {
                        \Log::warning("Produto ID {$produtoId} não encontrado no estoque");
                        continue;
                    }
                    
                    // Salvar item na O.S.
                    DB::table('ordens_servico_itens')->insert([
                        'ordem_servico_id' => $id,
                        'produto_id' => $produtoId,
                        'quantidade' => $quantidade,
                    ]);
                    
                    // Baixar do estoque
                    $estoqueAtual = (int) $produto->quantidade;
                    $novoEstoque = max(0, $estoqueAtual - $quantidade); // Não deixa ficar negativo
                    
                    DB::table('estoque')
                        ->where('id', $produtoId)
                        ->update(['quantidade' => $novoEstoque]);
                    
                    \Log::info("Baixa no estoque - Produto: {$produto->nome} (ID: {$produtoId}) - Quantidade: {$quantidade} - Estoque anterior: {$estoqueAtual} - Novo estoque: {$novoEstoque} - O.S.: {$numeroOS}");
                }
            }
        }

        // Criar solicitação de compra se houver itens solicitados
        if ($request->has('solicitacoes') && is_array($request->solicitacoes) && count($request->solicitacoes) > 0) {
            $this->criarSolicitacaoCompra($request->solicitacoes, $numeroOS, $request->centro_custo_id, $id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordem de Serviço criada com sucesso!',
            'id' => $id,
            'numero_os' => $numeroOS
        ]);
        
        } catch (\Exception $e) {
            \Log::error("Erro ao criar O.S.: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar O.S.: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Criar conta a pagar para prestador de serviço terceirizado
     */
    private function criarContaPagarPrestador($prestadorId, $nomePrestador, $descricaoServico, $valor, $numeroOS, $centroCustoId, $dataServico, $dataVencimento = null)
    {
        try {
            // Verificar se a tabela contas_pagar existe
            if (!Schema::hasTable('contas_pagar')) {
                \Log::warning('Tabela contas_pagar não existe. Conta não criada para prestador.');
                return null;
            }
            
            // Verificar colunas existentes
            $columns = [];
            try {
                $columnsResult = DB::select("SHOW COLUMNS FROM contas_pagar");
                $columns = array_map(function($col) { return $col->Field; }, $columnsResult);
            } catch (\Exception $e) {
                $columns = ['descricao', 'valor', 'status'];
            }
            
            // Descrição da conta
            $descricao = "Prestador: {$nomePrestador} - O.S. #{$numeroOS}";
            if ($descricaoServico) {
                $descricao .= " ({$descricaoServico})";
            }
            
            // Dados base
            $insertData = [
                'descricao' => $descricao,
                'valor' => $valor,
                'status' => 'pendente',
                'created_at' => now(),
            ];
            
            // Adicionar campos opcionais se existirem
            if (in_array('fornecedor', $columns)) {
                $insertData['fornecedor'] = $nomePrestador;
            }
            if (in_array('centro_custo_id', $columns)) {
                $insertData['centro_custo_id'] = $centroCustoId;
            }
            if (in_array('valor_bruto', $columns)) {
                $insertData['valor_bruto'] = $valor;
            }
            if (in_array('valor_liquido', $columns)) {
                $insertData['valor_liquido'] = $valor;
            }
            if (in_array('data_emissao', $columns)) {
                $insertData['data_emissao'] = $dataServico ?? now()->format('Y-m-d');
            }
            if (in_array('data_vencimento', $columns)) {
                $insertData['data_vencimento'] = $dataVencimento ?: date('Y-m-d', strtotime($dataServico . ' +7 days'));
            }
            if (in_array('vencimento', $columns)) {
                $insertData['vencimento'] = $dataVencimento ?: date('Y-m-d', strtotime($dataServico . ' +7 days'));
            }
            if (in_array('observacoes', $columns)) {
                $insertData['observacoes'] = "Prestador de serviço terceirizado vinculado à O.S. #{$numeroOS}. Prestador ID: {$prestadorId}";
            }
            if (in_array('tipo', $columns)) {
                $insertData['tipo'] = 'prestador_servico';
            }
            if (in_array('origem', $columns)) {
                $insertData['origem'] = 'os_prestador';
            }
            if (in_array('origem_id', $columns)) {
                $insertData['origem_id'] = $prestadorId;
            }
            
            $contaPagarId = DB::table('contas_pagar')->insertGetId($insertData);
            
            \Log::info("Conta a pagar #{$contaPagarId} criada para prestador {$nomePrestador} - O.S. #{$numeroOS} - Valor: R$ {$valor}");
            
            return $contaPagarId;
            
        } catch (\Exception $e) {
            \Log::error('Erro ao criar conta a pagar para prestador: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * NOVO: Criar Ordem de Compra para prestador de serviço terceirizado
     * O prestador segue o mesmo fluxo de aprovação das compras de material
     */
    private function criarOCPrestador($prestadorId, $nomePrestador, $descricaoServico, $valor, $numeroOS, $centroCustoId, $dataServico, $dataVencimento = null, $ordemServicoId = null)
    {
        try {
            // Verificar se a tabela ordens_compra existe
            if (!Schema::hasTable('ordens_compra')) {
                \Log::warning('Tabela ordens_compra não existe. OC não criada para prestador.');
                return null;
            }
            
            // Gerar número da OC
            $ultimaOC = DB::table('ordens_compra')
                ->whereYear('created_at', date('Y'))
                ->orderBy('id', 'desc')
                ->first();
            
            $proximoNumero = 1;
            if ($ultimaOC && preg_match('/OC-(\d{4})-(\d+)/', $ultimaOC->numero, $matches)) {
                $proximoNumero = intval($matches[2]) + 1;
            }
            $numeroOC = 'OC-' . date('Y') . '-' . str_pad($proximoNumero, 4, '0', STR_PAD_LEFT);
            
            // Descrição do serviço
            $descricaoOC = "Prestador: {$nomePrestador} - O.S. #{$numeroOS}";
            if ($descricaoServico) {
                $descricaoOC .= " - {$descricaoServico}";
            }
            
            // Verificar colunas existentes
            $columns = [];
            try {
                $columnsResult = DB::select("SHOW COLUMNS FROM ordens_compra");
                $columns = array_map(function($col) { return $col->Field; }, $columnsResult);
            } catch (\Exception $e) {
                $columns = ['numero', 'valor_total', 'status'];
            }
            
            // Dados da OC
            $insertData = [
                'numero' => $numeroOC,
                'valor_total' => $valor,
                'status' => 'pendente', // Aguardando aprovação
                'data_emissao' => $dataServico ?? now()->format('Y-m-d'),
                'data_previsao' => $dataVencimento ?: date('Y-m-d', strtotime(($dataServico ?? 'now') . ' +7 days')),
                'created_at' => now(),
            ];
            
            // Adicionar campos opcionais se existirem
            if (in_array('observacoes', $columns)) {
                $insertData['observacoes'] = $descricaoOC;
            }
            if (in_array('status_pagamento', $columns)) {
                $insertData['status_pagamento'] = 'aguardando_pagamento';
            }
            if (in_array('tipo', $columns)) {
                $insertData['tipo'] = 'prestador_servico';
            }
            if (in_array('prestador_id', $columns)) {
                $insertData['prestador_id'] = $prestadorId;
            }
            if (in_array('ordem_servico_id', $columns)) {
                $insertData['ordem_servico_id'] = $ordemServicoId;
            }
            
            // Buscar ou criar fornecedor para o prestador
            $fornecedor = DB::table('fornecedores')
                ->where('razao_social', 'LIKE', '%' . $nomePrestador . '%')
                ->first();
            
            if ($fornecedor) {
                $insertData['fornecedor_id'] = $fornecedor->id;
            } else {
                // Criar fornecedor automaticamente
                $dadosFornecedor = [
                    'razao_social' => $nomePrestador,
                    'nome_fantasia' => $nomePrestador,
                    'ativo' => 1,
                    'created_at' => now(),
                ];
                
                // Verificar se coluna cnpj é obrigatória
                if (Schema::hasColumn('fornecedores', 'cnpj')) {
                    $dadosFornecedor['cnpj'] = '00000000000000'; // CNPJ fictício para prestadores
                }
                
                $fornecedorId = DB::table('fornecedores')->insertGetId($dadosFornecedor);
                $insertData['fornecedor_id'] = $fornecedorId;
            }
            
            $ocId = DB::table('ordens_compra')->insertGetId($insertData);
            
            // Criar item na OC
            if (Schema::hasTable('ordem_compra_itens')) {
                DB::table('ordem_compra_itens')->insert([
                    'ordem_compra_id' => $ocId,
                    'produto' => "Serviço: {$nomePrestador}" . ($descricaoServico ? " - {$descricaoServico}" : ''),
                    'quantidade' => 1,
                    'unidade' => 'SV',
                    'valor_unitario' => $valor,
                    'valor_total' => $valor,
                    'created_at' => now(),
                ]);
            }
            
            \Log::info("OC #{$numeroOC} criada para prestador {$nomePrestador} - O.S. #{$numeroOS} - Valor: R$ {$valor}");
            
            return $ocId;
            
        } catch (\Exception $e) {
            \Log::error('Erro ao criar OC para prestador: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Criar cotação diretamente a partir da O.S.
     */
    private function criarSolicitacaoCompra($itens, $numeroOS, $centroCustoId = null, $ordemServicoId = null)
    {
        try {
            // Verificar se a tabela cotacoes existe
            if (!Schema::hasTable('cotacoes')) {
                \Log::warning("Tabela cotacoes não existe. Solicitação de compra não criada.");
                return null;
            }
            
            // Gerar número da cotação
            $ultimaCotacao = DB::table('cotacoes')
                ->orderBy('id', 'desc')
                ->first();
            
            $proximoNumero = 1;
            if ($ultimaCotacao && isset($ultimaCotacao->numero)) {
                // Extrair número do formato COT-YYYY-XXX
                preg_match('/(\d+)$/', $ultimaCotacao->numero, $matches);
                if (!empty($matches[1])) {
                    $proximoNumero = intval($matches[1]) + 1;
                }
            }
            $numeroCotacao = 'COT-' . date('Y') . '-' . str_pad($proximoNumero, 3, '0', STR_PAD_LEFT);
            
            // Criar cotação diretamente
            $cotacaoData = [
                'numero' => $numeroCotacao,
                'descricao' => "Solicitação via O.S. #{$numeroOS}",
                'data_solicitacao' => now()->format('Y-m-d'),
                'data_limite' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'aberta',
                'created_at' => now(),
            ];
            
            // Adicionar solicitante_id (usuário logado) se a coluna existir
            if (Schema::hasColumn('cotacoes', 'solicitante_id')) {
                $cotacaoData['solicitante_id'] = Auth::id();
            }
            
            // Adicionar ordem_servico_id se a coluna existir
            if (Schema::hasColumn('cotacoes', 'ordem_servico_id') && $ordemServicoId) {
                $cotacaoData['ordem_servico_id'] = $ordemServicoId;
            }
            
            $cotacaoId = DB::table('cotacoes')->insertGetId($cotacaoData);
            
            // Inserir itens da cotação (se a tabela existir)
            if (Schema::hasTable('cotacao_itens')) {
                foreach ($itens as $item) {
                    DB::table('cotacao_itens')->insert([
                        'cotacao_id' => $cotacaoId,
                        'produto' => $item['descricao'],
                        'quantidade' => $item['quantidade'],
                        'unidade' => $item['unidade'] ?? 'UN',
                        'created_at' => now(),
                    ]);
                }
            }
            
            // Também salvar na tabela solicitacoes_compra para referência na O.S. (se existir)
            if (Schema::hasTable('solicitacoes_compra')) {
                try {
                    $ultimaSolicitacao = DB::table('solicitacoes_compra')
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    $proximoNumeroSol = 1;
                    if ($ultimaSolicitacao && isset($ultimaSolicitacao->numero)) {
                        $proximoNumeroSol = intval(preg_replace('/\D/', '', $ultimaSolicitacao->numero)) + 1;
                    }
                    $numeroSolicitacao = 'SOL-' . str_pad($proximoNumeroSol, 6, '0', STR_PAD_LEFT);
                    
                    // Verificar colunas existentes
                    $insertData = [
                        'numero' => $numeroSolicitacao,
                        'descricao' => "Solicitação via O.S. #{$numeroOS}",
                        'status' => 'em_cotacao',
                    ];
                    
                    if (Schema::hasColumn('solicitacoes_compra', 'centro_custo_id')) {
                        $insertData['centro_custo_id'] = $centroCustoId;
                    }
                    if (Schema::hasColumn('solicitacoes_compra', 'usuario_id')) {
                        $insertData['usuario_id'] = Auth::id();
                    }
                    if (Schema::hasColumn('solicitacoes_compra', 'urgencia')) {
                        $insertData['urgencia'] = 'normal';
                    }
                    if (Schema::hasColumn('solicitacoes_compra', 'justificativa')) {
                        $insertData['justificativa'] = "Materiais solicitados através da Ordem de Serviço #{$numeroOS}";
                    }
                    if (Schema::hasColumn('solicitacoes_compra', 'data_solicitacao')) {
                        $insertData['data_solicitacao'] = now();
                    }
                    if (Schema::hasColumn('solicitacoes_compra', 'cotacao_id')) {
                        $insertData['cotacao_id'] = $cotacaoId;
                    }
                    if (Schema::hasColumn('solicitacoes_compra', 'created_at')) {
                        $insertData['created_at'] = now();
                    }
                    
                    $solicitacaoId = DB::table('solicitacoes_compra')->insertGetId($insertData);
                    
                    // Inserir itens da solicitação para referência (se a tabela existir)
                    if (Schema::hasTable('solicitacoes_compra_itens')) {
                        foreach ($itens as $item) {
                            $itemData = [
                                'solicitacao_compra_id' => $solicitacaoId,
                                'descricao' => $item['descricao'],
                                'quantidade' => $item['quantidade'],
                            ];
                            
                            if (Schema::hasColumn('solicitacoes_compra_itens', 'unidade')) {
                                $itemData['unidade'] = $item['unidade'] ?? 'UN';
                            }
                            if (Schema::hasColumn('solicitacoes_compra_itens', 'observacao')) {
                                $itemData['observacao'] = "Ref. O.S. #{$numeroOS}";
                            }
                            
                            DB::table('solicitacoes_compra_itens')->insert($itemData);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning("Erro ao criar solicitação de compra: " . $e->getMessage());
                }
            }
            
            return $cotacaoId;
            
        } catch (\Exception $e) {
            \Log::error("Erro ao criar cotação via O.S.: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar O.S. por ID
     */
    public function show($id)
    {
        $ordem = DB::table('ordens_servico')
            ->leftJoin('funcionarios', 'ordens_servico.funcionario_id', '=', 'funcionarios.id')
            ->where('ordens_servico.id', $id)
            ->select(
                'ordens_servico.*',
                'funcionarios.nome as funcionario_nome'
            )
            ->first();

        if (!$ordem) {
            return response()->json(['error' => 'O.S. não encontrada'], 404);
        }

        // Buscar prestadores de serviço se a tabela existir
        $prestadores = [];
        if (Schema::hasTable('ordens_servico_prestadores')) {
            // Verificar quais colunas existem
            $hasStatusPagamento = Schema::hasColumn('ordens_servico_prestadores', 'status_pagamento');
            $hasContaPagarId = Schema::hasColumn('ordens_servico_prestadores', 'conta_pagar_id');
            $hasDataPagamento = Schema::hasColumn('ordens_servico_prestadores', 'data_pagamento');
            
            // Montar select com colunas que existem
            $selectColumns = ['id', 'nome_prestador', 'descricao_servico', 'valor', 'data_servico'];
            if ($hasStatusPagamento) $selectColumns[] = 'status_pagamento';
            if ($hasDataPagamento) $selectColumns[] = 'data_pagamento';
            if ($hasContaPagarId) $selectColumns[] = 'conta_pagar_id';
            
            $prestadores = DB::table('ordens_servico_prestadores')
                ->where('ordem_servico_id', $id)
                ->select($selectColumns)
                ->get();
            
            // Atualizar status de pagamento baseado na conta a pagar
            if ($hasStatusPagamento && $hasContaPagarId) {
                foreach ($prestadores as &$prestador) {
                    if (isset($prestador->conta_pagar_id) && $prestador->conta_pagar_id && Schema::hasTable('contas_pagar')) {
                        $contaPagar = DB::table('contas_pagar')
                            ->where('id', $prestador->conta_pagar_id)
                            ->first();
                        
                        if ($contaPagar && $contaPagar->status === 'pago') {
                            // Atualizar o prestador se a conta foi paga
                            $statusAtual = $prestador->status_pagamento ?? 'pendente';
                            if ($statusAtual !== 'pago') {
                                $updateData = ['status_pagamento' => 'pago', 'updated_at' => now()];
                                if ($hasDataPagamento) {
                                    $updateData['data_pagamento'] = $contaPagar->data_pagamento ?? now()->format('Y-m-d');
                                }
                                DB::table('ordens_servico_prestadores')
                                    ->where('id', $prestador->id)
                                    ->update($updateData);
                                $prestador->status_pagamento = 'pago';
                                if ($hasDataPagamento) {
                                    $prestador->data_pagamento = $contaPagar->data_pagamento ?? now()->format('Y-m-d');
                                }
                            }
                        }
                    }
                }
            }
            
            // Adicionar propriedades padrão se não existirem
            foreach ($prestadores as &$prestador) {
                if (!isset($prestador->status_pagamento)) {
                    $prestador->status_pagamento = 'pendente';
                }
            }
        }
        $ordem->prestadores = $prestadores;
        
        // Calcular totais de prestadores
        $ordem->total_prestadores = collect($prestadores)->sum('valor');
        $ordem->total_prestadores_pagos = collect($prestadores)->where('status_pagamento', 'pago')->sum('valor');
        $ordem->total_prestadores_pendentes = collect($prestadores)->where('status_pagamento', '!=', 'pago')->sum('valor');

        // Buscar materiais se a tabela existir
        $materiais = [];
        if (Schema::hasTable('ordens_servico_itens')) {
            $materiais = DB::table('ordens_servico_itens')
                ->join('estoque', 'ordens_servico_itens.produto_id', '=', 'estoque.id')
                ->where('ordens_servico_itens.ordem_servico_id', $id)
                ->select(
                    'ordens_servico_itens.*',
                    'estoque.nome as produto_nome',
                    'estoque.quantidade as estoque_atual'
                )
                ->get();
        }

        $ordem->materiais = $materiais;

        // Buscar solicitações de materiais vinculadas a esta O.S.
        $solicitacoes = [];
        
        // Primeiro, tentar buscar da tabela cotacoes (principal)
        if (Schema::hasTable('cotacoes') && Schema::hasTable('cotacao_itens')) {
            // Buscar cotações vinculadas à O.S. pelo ID ou pelo número na descrição
            $cotacoesVinculadas = DB::table('cotacoes')
                ->where(function($query) use ($id, $ordem) {
                    if (Schema::hasColumn('cotacoes', 'ordem_servico_id')) {
                        $query->where('ordem_servico_id', $id);
                    }
                    $query->orWhere('descricao', 'like', '%O.S. #' . $ordem->numero_os . '%');
                })
                ->pluck('id');
            
            if ($cotacoesVinculadas->count() > 0) {
                $solicitacoes = DB::table('cotacao_itens')
                    ->whereIn('cotacao_id', $cotacoesVinculadas)
                    ->select('produto as descricao', 'quantidade', 'unidade')
                    ->get();
            }
        }
        
        // Se não encontrou na cotacoes, tentar na solicitacoes_compra (fallback)
        if (count($solicitacoes) === 0 && Schema::hasTable('solicitacoes_compra') && Schema::hasTable('solicitacoes_compra_itens')) {
            $solicitacao = DB::table('solicitacoes_compra')
                ->where('descricao', 'like', '%O.S. #' . $ordem->numero_os . '%')
                ->first();
            
            if ($solicitacao) {
                $solicitacoes = DB::table('solicitacoes_compra_itens')
                    ->where('solicitacao_compra_id', $solicitacao->id)
                    ->get();
            }
        }

        $ordem->solicitacoes = $solicitacoes;

        // Buscar centro de custo se existir
        if (Schema::hasColumn('ordens_servico', 'centro_custo_id') && $ordem->centro_custo_id) {
            $centroCusto = DB::table('centros_custo')->where('id', $ordem->centro_custo_id)->first();
            $ordem->centro_custo_nome = $centroCusto ? $centroCusto->nome : null;
        }

        return response()->json($ordem);
    }

    /**
     * Atualizar O.S.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'data_os' => 'required|date',
            'descricao' => 'required|string',
        ]);

        $dados = [
            'data_os' => $request->data_os,
            'funcionario_id' => $request->funcionario_id,
            'descricao' => $request->descricao,
            'endereco' => $request->endereco,
            'cidade' => $request->cidade,
            'estado' => $request->estado,
            'cep' => $request->cep,
            'telefone' => $request->telefone,
            'cpf_cnpj' => $request->cpf_cnpj,
            'observacoes' => $request->observacoes,
        ];

        // Adiciona tipo_atendimento se a coluna existir
        if (Schema::hasColumn('ordens_servico', 'tipo_atendimento')) {
            $dados['tipo_atendimento'] = $request->tipo_atendimento;
        }

        // Adiciona centro_custo_id se a coluna existir
        if (Schema::hasColumn('ordens_servico', 'centro_custo_id')) {
            $dados['centro_custo_id'] = $request->centro_custo_id;
        }

        // Adiciona urgencia se a coluna existir
        if (Schema::hasColumn('ordens_servico', 'urgencia')) {
            $dados['urgencia'] = $request->urgencia ?? 'normal';
        }

        DB::table('ordens_servico')
            ->where('id', $id)
            ->update($dados);

        // Atualizar prestadores de serviço se a tabela existir
        if (Schema::hasTable('ordens_servico_prestadores') && $request->has('prestadores')) {
            // Verificar quais colunas existem
            $hasStatusPagamento = Schema::hasColumn('ordens_servico_prestadores', 'status_pagamento');
            $hasContaPagarId = Schema::hasColumn('ordens_servico_prestadores', 'conta_pagar_id');
            
            // Buscar prestadores antigos para excluir contas a pagar
            $prestadoresAntigos = DB::table('ordens_servico_prestadores')
                ->where('ordem_servico_id', $id)
                ->get();
            
            // Excluir contas a pagar vinculadas (apenas as pendentes)
            if ($hasContaPagarId) {
                foreach ($prestadoresAntigos as $prestadorAntigo) {
                    if (isset($prestadorAntigo->conta_pagar_id) && $prestadorAntigo->conta_pagar_id) {
                        DB::table('contas_pagar')
                            ->where('id', $prestadorAntigo->conta_pagar_id)
                            ->where('status', 'pendente')
                            ->delete();
                    }
                }
            }
            
            // Remover prestadores antigos
            DB::table('ordens_servico_prestadores')->where('ordem_servico_id', $id)->delete();
            
            // Buscar número da O.S.
            $ordem = DB::table('ordens_servico')->where('id', $id)->first();
            $numeroOS = $ordem->numero_os ?? '';
            
            // Inserir novos prestadores
            $prestadores = $request->prestadores;
            if (is_array($prestadores)) {
                foreach ($prestadores as $prestador) {
                    $valor = $prestador['valor'] ?? 0;
                    
                    // Dados base do prestador
                    $dadosPrestador = [
                        'ordem_servico_id' => $id,
                        'nome_prestador' => $prestador['nome_prestador'],
                        'descricao_servico' => $prestador['descricao_servico'] ?? null,
                        'valor' => $valor,
                        'data_servico' => $request->data_os,
                        'created_at' => now(),
                    ];
                    
                    // Status inicial: aguardando autorização (novo fluxo com OC)
                    if ($hasStatusPagamento) {
                        $dadosPrestador['status_pagamento'] = 'aguardando_autorizacao';
                    }
                    
                    $prestadorId = DB::table('ordens_servico_prestadores')->insertGetId($dadosPrestador);
                    
                    // NOVO FLUXO: Criar OC para o prestador (ao invés de conta a pagar direto)
                    if ($valor > 0) {
                        $vencimento = $prestador['vencimento'] ?? null;
                        $ocId = $this->criarOCPrestador(
                            $prestadorId,
                            $prestador['nome_prestador'],
                            $prestador['descricao_servico'] ?? null,
                            $valor,
                            $numeroOS,
                            $request->centro_custo_id,
                            $request->data_os,
                            $vencimento,
                            $id // ordem_servico_id
                        );
                        
                        if ($ocId) {
                            DB::table('ordens_servico_prestadores')
                                ->where('id', $prestadorId)
                                ->update(['ordem_compra_id' => $ocId]);
                        }
                    }
                }
            }
        }

        // Adicionar NOVOS materiais (não mexe nos já salvos)
        if (Schema::hasTable('ordens_servico_itens') && $request->has('materiais')) {
            $materiais = $request->materiais;
            if (is_array($materiais)) {
                foreach ($materiais as $material) {
                    $produtoId = $material['produto_id'];
                    $quantidade = (int) $material['quantidade'];
                    
                    // Verificar estoque atual
                    $produto = DB::table('estoque')->where('id', $produtoId)->first();
                    if (!$produto) {
                        \Log::warning("Produto ID {$produtoId} não encontrado no estoque");
                        continue;
                    }
                    
                    // Salvar item na O.S.
                    DB::table('ordens_servico_itens')->insert([
                        'ordem_servico_id' => $id,
                        'produto_id' => $produtoId,
                        'quantidade' => $quantidade,
                    ]);
                    
                    // Baixar do estoque
                    $estoqueAtual = (int) $produto->quantidade;
                    $novoEstoque = max(0, $estoqueAtual - $quantidade);
                    
                    DB::table('estoque')
                        ->where('id', $produtoId)
                        ->update(['quantidade' => $novoEstoque]);
                    
                    \Log::info("Baixa no estoque (update O.S.) - Produto: {$produto->nome} (ID: {$produtoId}) - Quantidade: {$quantidade} - Estoque anterior: {$estoqueAtual} - Novo estoque: {$novoEstoque} - O.S.: {$numeroOS}");
                }
            }
        }

        // Adicionar NOVAS solicitações de compra (cotação)
        if ($request->has('solicitacoes') && is_array($request->solicitacoes) && count($request->solicitacoes) > 0) {
            // Buscar número da O.S.
            $ordem = DB::table('ordens_servico')->where('id', $id)->first();
            $numeroOS = $ordem->numero_os ?? '';
            
            // Parâmetros: $itens, $numeroOS, $centroCustoId, $ordemServicoId
            $this->criarSolicitacaoCompra($request->solicitacoes, $numeroOS, $request->centro_custo_id, $id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordem de Serviço atualizada com sucesso!'
        ]);
    }

    /**
     * Excluir O.S. (apenas administradores)
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        
        // Apenas administradores podem excluir O.S.
        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem excluir O.S.'
            ], 403);
        }
        
        // Buscar dados da O.S. antes de excluir para o log
        $ordem = DB::table('ordens_servico')->where('id', $id)->first();
        
        if (!$ordem) {
            return response()->json([
                'success' => false,
                'message' => 'O.S. não encontrada'
            ], 404);
        }
        
        // Devolver materiais ao estoque antes de excluir
        $materiaisExcluidos = [];
        if (Schema::hasTable('ordens_servico_itens')) {
            $materiais = DB::table('ordens_servico_itens')
                ->where('ordem_servico_id', $id)
                ->get();
            
            foreach ($materiais as $mat) {
                DB::table('estoque')
                    ->where('id', $mat->produto_id)
                    ->increment('quantidade', $mat->quantidade);
                $materiaisExcluidos[] = [
                    'produto_id' => $mat->produto_id,
                    'quantidade' => $mat->quantidade
                ];
            }
            
            DB::table('ordens_servico_itens')->where('ordem_servico_id', $id)->delete();
        }
        
        // =============================================
        // EXCLUIR COTAÇÕES VINCULADAS À O.S.
        // =============================================
        if (Schema::hasTable('cotacoes')) {
            // Buscar todas as cotações desta O.S.
            $cotacoes = DB::table('cotacoes')->where('ordem_servico_id', $id)->get();
            
            foreach ($cotacoes as $cotacao) {
                // Excluir itens por fornecedor (cotacao_fornecedor_itens)
                if (Schema::hasTable('cotacao_fornecedor_itens')) {
                    $fornecedoresIds = DB::table('cotacao_fornecedores')
                        ->where('cotacao_id', $cotacao->id)
                        ->pluck('id');
                    
                    if ($fornecedoresIds->count() > 0) {
                        DB::table('cotacao_fornecedor_itens')
                            ->whereIn('cotacao_fornecedor_id', $fornecedoresIds)
                            ->delete();
                    }
                }
                
                // Excluir fornecedores da cotação
                if (Schema::hasTable('cotacao_fornecedores')) {
                    DB::table('cotacao_fornecedores')->where('cotacao_id', $cotacao->id)->delete();
                }
                
                // Excluir itens da cotação
                if (Schema::hasTable('cotacao_itens')) {
                    DB::table('cotacao_itens')->where('cotacao_id', $cotacao->id)->delete();
                }
            }
            
            // Excluir as cotações
            DB::table('cotacoes')->where('ordem_servico_id', $id)->delete();
        }
        
        // =============================================
        // EXCLUIR SOLICITAÇÕES VINCULADAS À O.S.
        // =============================================
        if (Schema::hasTable('solicitacoes')) {
            // Buscar todas as solicitações desta O.S.
            $solicitacoes = DB::table('solicitacoes')->where('ordem_servico_id', $id)->get();
            
            foreach ($solicitacoes as $solicitacao) {
                // Excluir itens da solicitação
                if (Schema::hasTable('solicitacao_itens')) {
                    DB::table('solicitacao_itens')->where('solicitacao_id', $solicitacao->id)->delete();
                }
            }
            
            // Excluir as solicitações
            DB::table('solicitacoes')->where('ordem_servico_id', $id)->delete();
        }
        
        // =============================================
        // EXCLUIR FRETES VINCULADOS À O.S.
        // =============================================
        if (Schema::hasTable('fretes')) {
            // Excluir cotações de frete
            $fretes = DB::table('fretes')->where('ordem_servico_id', $id)->pluck('id');
            
            if ($fretes->count() > 0 && Schema::hasTable('fretes_cotacoes')) {
                DB::table('fretes_cotacoes')->whereIn('frete_id', $fretes)->delete();
            }
            
            // Excluir os fretes
            DB::table('fretes')->where('ordem_servico_id', $id)->delete();
        }
        
        // Excluir a O.S.
        DB::table('ordens_servico')->where('id', $id)->delete();
        
        // Registrar log da exclusão
        $this->registrarLogOS('exclusao', $ordem, $user, $materiaisExcluidos);

        return response()->json([
            'success' => true,
            'message' => 'Ordem de Serviço excluída com sucesso!'
        ]);
    }
    
    /**
     * Registrar log de ações em O.S.
     */
    private function registrarLogOS($acao, $ordem, $user, $materiaisExcluidos = [])
    {
        try {
            // Tentar salvar na tabela logs_ordens_servico se existir
            if (Schema::hasTable('logs_ordens_servico')) {
                DB::table('logs_ordens_servico')->insert([
                    'ordem_servico_id' => $ordem->id,
                    'numero_os' => $ordem->numero_os,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'acao' => $acao,
                    'dados_os' => json_encode([
                        'data_os' => $ordem->data_os,
                        'descricao' => $ordem->descricao,
                        'funcionario_id' => $ordem->funcionario_id ?? null,
                        'centro_custo_id' => $ordem->centro_custo_id ?? null,
                        'status' => $ordem->status ?? 'aberta',
                        'materiais_devolvidos' => $materiaisExcluidos
                    ], JSON_UNESCAPED_UNICODE),
                    'ip' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 500),
                    'created_at' => now(),
                ]);
            }
            
            // Sempre registrar no log do Laravel também
            \Log::info("O.S. {$ordem->numero_os} excluída", [
                'ordem_servico_id' => $ordem->id,
                'numero_os' => $ordem->numero_os,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'acao' => $acao,
                'data_os' => $ordem->data_os,
                'descricao' => $ordem->descricao,
                'materiais_devolvidos' => count($materiaisExcluidos),
                'ip' => request()->ip(),
            ]);
            
        } catch (\Throwable $e) {
            \Log::warning('Falha ao registrar log de O.S.', [
                'ordem_id' => $ordem->id ?? null,
                'erro' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Página de Gestão de O.S.
     */
    public function gestao()
    {
        return view('area-tecnica.gestao-os');
    }
    
    /**
     * Listar O.S. para gestão (com filtro de usuário)
     */
    public function listarGestao(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        
        $query = DB::table('ordens_servico')
            ->leftJoin('funcionarios', 'ordens_servico.funcionario_id', '=', 'funcionarios.id')
            ->leftJoin('users', 'ordens_servico.user_id', '=', 'users.id');
        
        // Se tem coluna centro_custo_id, fazer join
        if (Schema::hasColumn('ordens_servico', 'centro_custo_id')) {
            $query->leftJoin('centros_custo', 'ordens_servico.centro_custo_id', '=', 'centros_custo.id');
        }
        
        // Se não for admin E não for para solicitação de compras, filtrar apenas as O.S. do usuário
        // O parâmetro 'todas=1' permite listar todas as O.S. (usado no modal de Solicitação via O.S.)
        if (!$isAdmin && !$request->has('todas')) {
            $query->where('ordens_servico.user_id', $user->id);
        }
        
        // Filtro de status (aberta/fechada)
        if (Schema::hasColumn('ordens_servico', 'status')) {
            $status = $request->status ?? 'aberta';
            if ($status !== 'todas') {
                if ($status === 'aberta') {
                    // Considerar NULL como aberta também
                    $query->where(function($q) {
                        $q->where('ordens_servico.status', 'aberta')
                          ->orWhereNull('ordens_servico.status');
                    });
                } else {
                    $query->where('ordens_servico.status', $status);
                }
            }
        }
        
        // Filtros de data
        if ($request->data_inicio) {
            $query->where('ordens_servico.data_os', '>=', $request->data_inicio);
        }
        if ($request->data_fim) {
            $query->where('ordens_servico.data_os', '<=', $request->data_fim);
        }
        
        // Filtro por número O.S.
        if ($request->numero_os) {
            $query->where('ordens_servico.numero_os', 'like', '%' . $request->numero_os . '%');
        }
        
        $selectFields = [
            'ordens_servico.*',
            'funcionarios.nome as funcionario_nome',
            'users.name as criado_por'
        ];
        
        if (Schema::hasColumn('ordens_servico', 'centro_custo_id')) {
            $selectFields[] = 'centros_custo.nome as centro_custo_nome';
        }
        
        $ordens = $query->select($selectFields)
            ->orderBy('ordens_servico.data_os', 'desc')
            ->orderBy('ordens_servico.id', 'desc')
            ->get();
        
        // Buscar status do fluxo de materiais, prestadores, almoxarifado e frete para cada O.S.
        foreach ($ordens as $os) {
            $os->status_produto = $this->getStatusFluxoProduto($os->id, $os->numero_os);
            $os->status_prestadores = $this->getStatusPrestadores($os->id);
            $os->status_almoxarifado = $this->getStatusAlmoxarifado($os->id);
            $os->status_frete = $this->getStatusFrete($os->id);
        }
        
        return response()->json([
            'success' => true,
            'ordens' => $ordens,
            'is_admin' => $isAdmin,
            'has_status' => Schema::hasColumn('ordens_servico', 'status')
        ]);
    }
    
    /**
     * Fechar O.S.
     */
    public function fechar($id)
    {
        // Verificar se a coluna status existe
        if (!Schema::hasColumn('ordens_servico', 'status')) {
            return response()->json([
                'success' => false,
                'message' => 'Coluna status não existe na tabela. Execute o SQL para adicionar.'
            ], 400);
        }
        
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        
        // Verificar se o usuário pode fechar esta O.S.
        $ordem = DB::table('ordens_servico')->where('id', $id)->first();
        
        if (!$ordem) {
            return response()->json(['success' => false, 'message' => 'O.S. não encontrada'], 404);
        }
        
        if (!$isAdmin && $ordem->user_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Sem permissão para fechar esta O.S.'], 403);
        }
        
        // Calcular totais antes de fechar
        $totais = $this->calcularTotaisOS($id, $ordem->numero_os);
        
        $dadosUpdate = [
            'status' => 'fechada',
            'data_fechamento' => now()
        ];
        
        // Adicionar valores totais se as colunas existirem
        if (Schema::hasColumn('ordens_servico', 'valor_total_prestadores')) {
            $dadosUpdate['valor_total_prestadores'] = $totais['prestadores'];
        }
        if (Schema::hasColumn('ordens_servico', 'valor_total_materiais')) {
            $dadosUpdate['valor_total_materiais'] = $totais['materiais'];
        }
        if (Schema::hasColumn('ordens_servico', 'valor_total_solicitacoes')) {
            $dadosUpdate['valor_total_solicitacoes'] = $totais['solicitacoes'];
        }
        if (Schema::hasColumn('ordens_servico', 'valor_total_os')) {
            $dadosUpdate['valor_total_os'] = $totais['total'];
        }
        
        DB::table('ordens_servico')
            ->where('id', $id)
            ->update($dadosUpdate);
        
        return response()->json([
            'success' => true,
            'message' => 'O.S. fechada com sucesso!',
            'totais' => $totais
        ]);
    }
    
    /**
     * Calcular totais da O.S. (prestadores + materiais + solicitações)
     */
    private function calcularTotaisOS($osId, $numeroOs)
    {
        $totalPrestadores = 0;
        $totalMateriais = 0;
        $totalSolicitacoes = 0;
        
        // Total de prestadores
        if (Schema::hasTable('ordens_servico_prestadores')) {
            $totalPrestadores = DB::table('ordens_servico_prestadores')
                ->where('ordem_servico_id', $osId)
                ->sum('valor');
        }
        
        // Total de materiais (baseado no estoque - aqui seria necessário ter valor unitário)
        // Por enquanto, apenas conta quantos itens foram usados
        if (Schema::hasTable('ordens_servico_itens')) {
            // Se tiver coluna de valor, soma os valores
            if (Schema::hasColumn('ordens_servico_itens', 'valor_unitario')) {
                $totalMateriais = DB::table('ordens_servico_itens')
                    ->where('ordem_servico_id', $osId)
                    ->selectRaw('SUM(quantidade * valor_unitario) as total')
                    ->value('total') ?? 0;
            }
        }
        
        // Total de solicitações (buscar na cotação/ordem de compra vinculada)
        if (Schema::hasTable('solicitacoes_compra') && Schema::hasTable('solicitacoes_compra_itens')) {
            $solicitacao = DB::table('solicitacoes_compra')
                ->where('descricao', 'like', '%O.S. #' . $numeroOs . '%')
                ->first();
            
            if ($solicitacao) {
                // Se tiver ordem de compra vinculada, pegar o valor dela
                if ($solicitacao->cotacao_id) {
                    $ordemCompra = DB::table('ordens_compra')
                        ->where('cotacao_id', $solicitacao->cotacao_id)
                        ->first();
                    
                    if ($ordemCompra) {
                        $totalSolicitacoes = $ordemCompra->valor_total ?? 0;
                    }
                }
            }
        }
        
        return [
            'prestadores' => round($totalPrestadores, 2),
            'materiais' => round($totalMateriais, 2),
            'solicitacoes' => round($totalSolicitacoes, 2),
            'total' => round($totalPrestadores + $totalMateriais + $totalSolicitacoes, 2)
        ];
    }
    
    /**
     * Reabrir O.S.
     */
    public function reabrir($id)
    {
        if (!Schema::hasColumn('ordens_servico', 'status')) {
            return response()->json([
                'success' => false,
                'message' => 'Coluna status não existe na tabela.'
            ], 400);
        }
        
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        
        if (!$isAdmin) {
            return response()->json(['success' => false, 'message' => 'Apenas administradores podem reabrir O.S.'], 403);
        }
        
        DB::table('ordens_servico')
            ->where('id', $id)
            ->update([
                'status' => 'aberta',
                'data_fechamento' => null
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'O.S. reaberta com sucesso!'
        ]);
    }
    
    /**
     * Verificar se usuário é admin
     */
    private function isAdmin($user)
    {
        // Verificar se tem perfil de administrador
        $perfil = DB::table('profiles')->where('id', $user->profile_id)->first();
        
        if ($perfil && strtolower($perfil->name) === 'administrador') {
            return true;
        }
        
        // Ou verificar se tem muitas permissões
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
     * Buscar status de pagamento dos prestadores de serviço
     */
    private function getStatusPrestadores($osId)
    {
        try {
            if (!Schema::hasTable('ordens_servico_prestadores')) {
                return null;
            }
            
            // Verificar quais colunas existem
            $hasStatusPagamento = Schema::hasColumn('ordens_servico_prestadores', 'status_pagamento');
            $hasContaPagarId = Schema::hasColumn('ordens_servico_prestadores', 'conta_pagar_id');
            $hasDataPagamento = Schema::hasColumn('ordens_servico_prestadores', 'data_pagamento');
            
            $prestadores = DB::table('ordens_servico_prestadores')
                ->where('ordem_servico_id', $osId)
                ->get();
            
            if ($prestadores->isEmpty()) {
                return [
                    'codigo' => 'sem_prestador',
                    'texto' => 'Sem Terceirizado',
                    'cor' => '#6c757d',
                    'icone' => 'fa-minus-circle',
                    'total' => 0,
                    'pagos' => 0,
                    'pendentes' => 0,
                    'valor_total' => 0,
                    'valor_pago' => 0,
                    'valor_pendente' => 0
                ];
            }
            
            // Sincronizar status com contas a pagar (se as colunas existirem)
            if ($hasStatusPagamento && $hasContaPagarId) {
                foreach ($prestadores as $prestador) {
                    $contaPagarId = $prestador->conta_pagar_id ?? null;
                    if ($contaPagarId && Schema::hasTable('contas_pagar')) {
                        $contaPagar = DB::table('contas_pagar')
                            ->where('id', $contaPagarId)
                            ->first();
                        
                        $statusAtual = $prestador->status_pagamento ?? 'pendente';
                        if ($contaPagar && $contaPagar->status === 'pago' && $statusAtual !== 'pago') {
                            $updateData = ['status_pagamento' => 'pago', 'updated_at' => now()];
                            if ($hasDataPagamento) {
                                $updateData['data_pagamento'] = $contaPagar->data_pagamento ?? now()->format('Y-m-d');
                            }
                            DB::table('ordens_servico_prestadores')
                                ->where('id', $prestador->id)
                                ->update($updateData);
                        }
                    }
                }
                
                // Recarregar após sincronização
                $prestadores = DB::table('ordens_servico_prestadores')
                    ->where('ordem_servico_id', $osId)
                    ->get();
            }
            
            $total = $prestadores->count();
            
            // Contar por status
            $pagos = 0;
            $aguardandoPagamento = 0;
            $aguardandoAutorizacao = 0;
            $valorPago = 0;
            $valorAguardandoPgto = 0;
            $valorAguardandoAuth = 0;
            
            if ($hasStatusPagamento) {
                $pagos = $prestadores->where('status_pagamento', 'pago')->count();
                $valorPago = $prestadores->where('status_pagamento', 'pago')->sum('valor');
                
                $aguardandoPagamento = $prestadores->where('status_pagamento', 'aguardando_pagamento')->count();
                $valorAguardandoPgto = $prestadores->where('status_pagamento', 'aguardando_pagamento')->sum('valor');
                
                $aguardandoAutorizacao = $prestadores->where('status_pagamento', 'aguardando_autorizacao')->count();
                $valorAguardandoAuth = $prestadores->where('status_pagamento', 'aguardando_autorizacao')->sum('valor');
            }
            $pendentes = $total - $pagos;
            
            $valorTotal = $prestadores->sum('valor');
            $valorPendente = $valorTotal - $valorPago;
            
            // Todos pagos
            if ($pendentes === 0 && $pagos > 0) {
                return [
                    'codigo' => 'todos_pagos',
                    'texto' => 'Pago',
                    'cor' => '#28a745',
                    'icone' => 'fa-check-circle',
                    'total' => $total,
                    'pagos' => $pagos,
                    'pendentes' => 0,
                    'valor_total' => $valorTotal,
                    'valor_pago' => $valorPago,
                    'valor_pendente' => 0
                ];
            }
            
            // Algum aguardando autorização (OC pendente)
            if ($aguardandoAutorizacao > 0) {
                $texto = $pagos > 0 ? "{$pagos}/{$total} Pagos" : 'Aguard. Autorização';
                return [
                    'codigo' => 'aguardando_autorizacao',
                    'texto' => $texto,
                    'cor' => '#17a2b8', // info/azul
                    'icone' => 'fa-hourglass-half',
                    'total' => $total,
                    'pagos' => $pagos,
                    'pendentes' => $pendentes,
                    'aguardando_autorizacao' => $aguardandoAutorizacao,
                    'aguardando_pagamento' => $aguardandoPagamento,
                    'valor_total' => $valorTotal,
                    'valor_pago' => $valorPago,
                    'valor_pendente' => $valorPendente
                ];
            }
            
            // Algum aguardando pagamento (OC aprovada, conta criada)
            if ($aguardandoPagamento > 0) {
                $texto = $pagos > 0 ? "{$pagos}/{$total} Pagos" : 'Aguard. Pagamento';
                return [
                    'codigo' => 'aguardando_pagamento',
                    'texto' => $texto,
                    'cor' => '#ffc107', // warning/amarelo
                    'icone' => 'fa-clock',
                    'total' => $total,
                    'pagos' => $pagos,
                    'pendentes' => $pendentes,
                    'aguardando_autorizacao' => $aguardandoAutorizacao,
                    'aguardando_pagamento' => $aguardandoPagamento,
                    'valor_total' => $valorTotal,
                    'valor_pago' => $valorPago,
                    'valor_pendente' => $valorPendente
                ];
            }
            
            // Pagamento parcial
            if ($pagos > 0) {
                return [
                    'codigo' => 'pagamento_parcial',
                    'texto' => "{$pagos}/{$total} Pagos",
                    'cor' => '#ffc107',
                    'icone' => 'fa-clock',
                    'total' => $total,
                    'pagos' => $pagos,
                    'pendentes' => $pendentes,
                    'valor_total' => $valorTotal,
                    'valor_pago' => $valorPago,
                    'valor_pendente' => $valorPendente
                ];
            }
            
            return [
                'codigo' => 'pendente',
                'texto' => 'Pendente',
                'cor' => '#dc3545',
                'icone' => 'fa-exclamation-circle',
                'total' => $total,
                'pagos' => 0,
                'pendentes' => $pendentes,
                'valor_total' => $valorTotal,
                'valor_pago' => 0,
                'valor_pendente' => $valorPendente
            ];
            
        } catch (\Exception $e) {
            return [
                'codigo' => 'erro',
                'texto' => 'Erro',
                'cor' => '#6c757d',
                'icone' => 'fa-exclamation-triangle',
                'total' => 0,
                'pagos' => 0,
                'pendentes' => 0,
                'valor_total' => 0,
                'valor_pago' => 0,
                'valor_pendente' => 0
            ];
        }
    }
    
    /**
     * Buscar status de liberação do almoxarifado para uma O.S.
     * Verifica se os materiais da O.S. foram liberados pelo almoxarifado
     * Status: Sem Material | Aguardando Retirada | Entregue
     */
    private function getStatusAlmoxarifado($osId)
    {
        try {
            // Verificar se a tabela tem a coluna liberado
            if (!Schema::hasColumn('ordens_servico_itens', 'liberado')) {
                return null;
            }
            
            // Buscar todos os itens da O.S.
            $itens = DB::table('ordens_servico_itens')
                ->where('ordem_servico_id', $osId)
                ->get();
            
            // Se não tem itens, sem material
            if ($itens->isEmpty()) {
                return [
                    'codigo' => 'sem_material',
                    'texto' => 'Sem Material',
                    'cor' => '#6c757d',
                    'icone' => 'fa-minus-circle'
                ];
            }
            
            $totalItens = $itens->count();
            $liberados = $itens->where('liberado', 1)->count();
            $pendentes = $totalItens - $liberados;
            
            // Se todos os itens foram liberados
            if ($pendentes === 0) {
                return [
                    'codigo' => 'entregue',
                    'texto' => 'Entregue',
                    'cor' => '#28a745',
                    'icone' => 'fa-check-circle',
                    'total' => $totalItens,
                    'liberados' => $liberados,
                    'pendentes' => 0
                ];
            }
            
            // Se alguns itens foram liberados
            if ($liberados > 0) {
                return [
                    'codigo' => 'parcial',
                    'texto' => "{$liberados}/{$totalItens} Entregue",
                    'cor' => '#ffc107',
                    'icone' => 'fa-clock',
                    'total' => $totalItens,
                    'liberados' => $liberados,
                    'pendentes' => $pendentes
                ];
            }
            
            // Se nenhum item foi liberado
            return [
                'codigo' => 'aguardando',
                'texto' => 'Aguardando Retirada',
                'cor' => '#dc3545',
                'icone' => 'fa-hourglass-half',
                'total' => $totalItens,
                'liberados' => 0,
                'pendentes' => $pendentes
            ];
            
        } catch (\Exception $e) {
            return [
                'codigo' => 'erro',
                'texto' => 'Erro',
                'cor' => '#6c757d',
                'icone' => 'fa-exclamation-triangle'
            ];
        }
    }

    /**
     * Buscar status do frete vinculado à O.S.
     */
    private function getStatusFrete($osId)
    {
        try {
            // Verificar se a tabela fretes existe
            if (!Schema::hasTable('fretes')) {
                return null;
            }

            // Buscar fretes desta O.S.
            $fretes = DB::table('fretes')
                ->where('ordem_servico_id', $osId)
                ->where('status', '!=', 'cancelado')
                ->get();

            if ($fretes->isEmpty()) {
                return null;
            }

            // Pegar o status mais relevante (prioridade: aguardando > em_cotacao > cotado > aguardando_pagamento > pago > liberado > entregue)
            $prioridade = [
                'aguardando_cotacao' => 1,
                'em_cotacao' => 2,
                'cotado' => 3,
                'aguardando_pagamento' => 4,
                'pago' => 5,
                'liberado' => 6,
                'entregue' => 7
            ];

            $statusPrincipal = null;
            $menorPrioridade = 999;
            $valorTotal = 0;

            foreach ($fretes as $frete) {
                $p = $prioridade[$frete->status] ?? 999;
                if ($p < $menorPrioridade) {
                    $menorPrioridade = $p;
                    $statusPrincipal = $frete->status;
                }
                $valorTotal += $frete->valor_aprovado > 0 ? $frete->valor_aprovado : $frete->valor_cotado;
            }

            return [
                'status' => $statusPrincipal,
                'total' => count($fretes),
                'valor' => $valorTotal
            ];

        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Buscar status do fluxo de produto/material para uma O.S.
     * Fluxo: Sem Solicitação -> Em Cotação -> Ordem de Compra (Pendente) -> Aguardando Pagamento -> Aguardando Entrega -> Recebido
     */
    private function getStatusFluxoProduto($osId, $numeroOs)
    {
        try {
            // Primeiro, verificar se existe cotação vinculada a esta O.S.
            $cotacao = DB::table('cotacoes')
                ->where(function($q) use ($osId, $numeroOs) {
                    $q->where('ordem_servico_id', $osId)
                      ->orWhere('descricao', 'like', '%' . $numeroOs . '%');
                })
                ->first();
            
            if (!$cotacao) {
                // Sem cotação vinculada = sem solicitação de material
                return [
                    'codigo' => 'sem_solicitacao',
                    'texto' => 'Sem Material',
                    'cor' => '#6c757d',
                    'icone' => 'fa-minus-circle'
                ];
            }
            
            // Tem cotação, verificar se virou O.C.
            $ordemCompra = DB::table('ordens_compra')
                ->where('cotacao_id', $cotacao->id)
                ->first();
            
            if (!$ordemCompra) {
                // Cotação ainda não gerou O.C.
                return [
                    'codigo' => 'em_cotacao',
                    'texto' => 'Em Cotação',
                    'cor' => '#17a2b8',
                    'icone' => 'fa-file-invoice'
                ];
            }
            
            // Verificar status da O.C.
            $statusOC = $ordemCompra->status ?? 'pendente';
            $statusPagamento = $ordemCompra->status_pagamento ?? null;
            
            if ($statusOC === 'pendente') {
                return [
                    'codigo' => 'oc_pendente',
                    'texto' => 'O.C. Pendente',
                    'cor' => '#ffc107',
                    'icone' => 'fa-clock'
                ];
            }
            
            if ($statusOC === 'cancelada' || $statusOC === 'recusada') {
                return [
                    'codigo' => 'oc_cancelada',
                    'texto' => 'O.C. Cancelada',
                    'cor' => '#dc3545',
                    'icone' => 'fa-times-circle'
                ];
            }
            
            // Verificar se já foi recebido (buscar na tabela recebimentos)
            $recebimento = null;
            if (\Schema::hasTable('recebimentos')) {
                $recebimento = DB::table('recebimentos')
                    ->where('ordem_compra_id', $ordemCompra->id)
                    ->first();
            }
            
            // Se já tem recebimento, material foi recebido
            if ($recebimento) {
                return [
                    'codigo' => 'recebido',
                    'texto' => 'Recebido',
                    'cor' => '#28a745',
                    'icone' => 'fa-check-circle'
                ];
            }
            
            if ($statusOC === 'aprovada') {
                // Verificar se foi pago
                if ($statusPagamento === 'pago' || $statusPagamento === 'pago_parcial') {
                    return [
                        'codigo' => 'aguardando_entrega',
                        'texto' => 'Aguardando Entrega',
                        'cor' => '#6f42c1',
                        'icone' => 'fa-truck'
                    ];
                }
                
                return [
                    'codigo' => 'aguardando_pagamento',
                    'texto' => 'Aguardando Pagamento',
                    'cor' => '#fd7e14',
                    'icone' => 'fa-money-bill-wave'
                ];
            }
            
            if ($statusOC === 'recebida') {
                return [
                    'codigo' => 'recebido',
                    'texto' => 'Recebido',
                    'cor' => '#28a745',
                    'icone' => 'fa-check-circle'
                ];
            }
            
            // Fallback
            return [
                'codigo' => 'em_andamento',
                'texto' => 'Em Andamento',
                'cor' => '#17a2b8',
                'icone' => 'fa-spinner'
            ];
            
        } catch (\Exception $e) {
            return [
                'codigo' => 'erro',
                'texto' => 'Erro',
                'cor' => '#dc3545',
                'icone' => 'fa-exclamation-triangle'
            ];
        }
    }
}
