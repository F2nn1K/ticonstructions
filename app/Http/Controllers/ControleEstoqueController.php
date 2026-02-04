<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estoque;
use App\Models\EstoqueMinMax;
use App\Models\Funcionario;
use App\Models\Baixa;
use App\Models\CentroCusto;
use App\Models\LogEstoque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ControleEstoqueController extends Controller
{
    /**
     * Registra log de movimentação de estoque.
     */
    protected function registrarLogEstoque(Estoque $produto, string $tipo, int $quantidadeAnterior, int $quantidadeAlterada, int $quantidadeNova, ?string $origem = null, ?string $observacao = null): void
    {
        try {
            LogEstoque::create([
                'produto_id' => $produto->id,
                'user_id' => Auth::id(),
                'tipo' => $tipo,
                'quantidade_anterior' => $quantidadeAnterior,
                'quantidade_alterada' => $quantidadeAlterada,
                'quantidade_nova' => $quantidadeNova,
                'origem' => $origem,
                'observacao' => $observacao,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Falha ao registrar log de estoque', [
                'produto_id' => $produto->id,
                'tipo' => $tipo,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    public function index()
    {
        // Calcular estatísticas para os cards
        $totalProdutos = Estoque::count(); // Total de produtos cadastrados
        
        // Entradas e saídas do mês atual
        $mesAtual = now()->format('Y-m');
        $entradasMes = LogEstoque::where('tipo', 'entrada')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('quantidade_alterada');
        $saidasMes = Baixa::whereYear('data_baixa', now()->year)
                          ->whereMonth('data_baixa', now()->month)
                          ->sum('quantidade');
        
        // Produtos em falta (quantidade = 0)
        $produtosFalta = Estoque::where('quantidade', '=', 0)->count();
        
        // Dados para a página
        return view('brs.controle-estoque', compact(
            'totalProdutos',
            'entradasMes', 
            'saidasMes',
            'produtosFalta'
        ));
    }
    
    public function verificarPrazoFardamento(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'produto_id' => 'required|exists:estoque,id'
        ]);
        try {
            $funcionarioId = (int) $request->funcionario_id;
            $produtoId = (int) $request->produto_id;

            // Regra: considerar produtos cujo nome contenha "fard" (fardamento) ou descrição semelhante
            $produto = Estoque::findOrFail($produtoId);
            $ehFardamento = false;
            $nome = mb_strtolower($produto->nome ?? '');
            $descricao = mb_strtolower($produto->descricao ?? '');
            if (str_contains($nome, 'fard') || str_contains($descricao, 'fard')) {
                $ehFardamento = true;
            }

            if (!$ehFardamento) {
                return response()->json([
                    'success' => true,
                    'alertar' => false,
                    'mensagem' => null
                ]);
            }

            // Buscar a última baixa desse produto para o funcionário
            $ultimaBaixa = Baixa::where('funcionario_id', $funcionarioId)
                ->where('produto_id', $produtoId)
                ->orderBy('data_baixa', 'desc')
                ->first();

            if (!$ultimaBaixa) {
                return response()->json([
                    'success' => true,
                    'alertar' => false,
                    'mensagem' => null
                ]);
            }

            $limite = now()->subMonths(6);
            if ($ultimaBaixa->data_baixa && $ultimaBaixa->data_baixa->greaterThan($limite)) {
                $diasRestantes = $ultimaBaixa->data_baixa->diffInDays($limite, false) * -1; // negativo não interessa
                $dataPermitida = $ultimaBaixa->data_baixa->copy()->addMonths(6)->format('d/m/Y');
                return response()->json([
                    'success' => true,
                    'alertar' => true,
                    'mensagem' => "Este funcionário retirou este fardamento em " . $ultimaBaixa->data_baixa->format('d/m/Y') . ". O próximo está permitido a partir de " . $dataPermitida . ". Deseja continuar mesmo assim?"
                ]);
            }

            return response()->json([
                'success' => true,
                'alertar' => false,
                'mensagem' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar prazo: ' . $e->getMessage()
            ], 400);
        }
    }

    public function verificarFardamentoFuncionario(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id'
        ]);
        try {
            $funcionarioId = (int) $request->funcionario_id;

            // Buscar todas as retiradas (EPIs) do mês corrente para este funcionário
            $inicioMes = now()->startOfMonth();
            $fimMes = now()->endOfMonth();

            $registros = Baixa::with('produto')
                ->where('funcionario_id', $funcionarioId)
                ->whereBetween('data_baixa', [$inicioMes, $fimMes])
                ->orderBy('data_baixa', 'desc')
                ->get();

            $itensMes = [];
            foreach ($registros as $b) {
                if (!$b->produto) { continue; }
                $itensMes[] = [
                    'produto' => $b->produto->nome,
                    'data' => $b->data_baixa ? $b->data_baixa->format('d/m/Y') : null,
                    'quantidade' => (int) $b->quantidade
                ];
            }

            return response()->json([
                'success' => true,
                'avisos' => $itensMes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar fardamentos: ' . $e->getMessage()
            ], 400);
        }
    }

    public function buscarFuncionarios()
    {
        $funcionarios = Funcionario::select('id', 'nome', 'funcao', 'cpf')
                                  ->orderBy('nome')
                                  ->get();
        
        return response()->json($funcionarios);
    }
    
    public function buscarCentroCustos()
    {
        $centroCustos = CentroCusto::where('ativo', true)
                                  ->select('id', 'nome')
                                  ->orderBy('nome')
                                  ->get();
        
        return response()->json($centroCustos);
    }
    
    public function buscarProdutos()
    {
        // Buscar produtos com níveis mínimo/máximo configurados
        $produtos = DB::table('estoque as e')
            ->leftJoin('estoque_min_max as mm', 'mm.produto_id', '=', 'e.id')
            ->select(
                'e.id',
                'e.nome',
                'e.descricao',
                'e.quantidade',
                DB::raw('COALESCE(mm.minimo, 0) as minimo'),
                'mm.maximo'
            )
            ->orderBy('e.nome')
            ->get();
        
        return response()->json($produtos);
    }
    
    public function produtosEmFalta()
    {
        // Buscar produtos com quantidade zero
        $produtosZerados = Estoque::where('quantidade', '=', 0)
                                 ->select('id', 'nome', 'descricao', 'quantidade')
                                 ->orderBy('nome')
                                 ->get();
        
        return response()->json($produtosZerados);
    }
    
    public function listarTodosProdutos()
    {
        $produtos = DB::table('estoque')
            ->select('id', 'nome', 'descricao', 'quantidade', 'unidade')
            ->orderBy('nome')
            ->get();
        
        return response()->json(['produtos' => $produtos]);
    }
    
    public function buscarProdutosPorNome(Request $request)
    {
        // Aceita tanto 'nome' quanto 'termo' para evitar conflitos de chamadas
        $termo = $request->get('nome') ?: $request->get('termo');

        if (!$termo || mb_strlen($termo) < 3) {
            return response()->json([]);
        }

        $produtos = DB::table('estoque as e')
            ->leftJoin('estoque_min_max as mm', 'mm.produto_id', '=', 'e.id')
            ->where(function($q) use ($termo){
                $q->where('e.nome', 'like', '%' . $termo . '%')
                  ->orWhere('e.descricao', 'like', '%' . $termo . '%');
            })
            ->select(
                'e.id',
                'e.nome',
                'e.descricao',
                'e.quantidade',
                DB::raw('COALESCE(mm.minimo, 0) as minimo'),
                'mm.maximo'
            )
            ->orderBy('e.nome')
            ->limit(50)
            ->get();

        return response()->json($produtos);
    }
    
    /**
     * Consulta NCM na Brasil API
     */
    public function consultarNcm(Request $request)
    {
        $request->validate([
            'ncm' => 'required|string|min:2|max:10'
        ]);
        
        try {
            $ncm = preg_replace('/[^0-9]/', '', $request->ncm);
            
            // Tentar busca exata primeiro
            $response = Http::timeout(10)->get("https://brasilapi.com.br/api/ncm/v1/{$ncm}");
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'data' => [
                        'codigo' => $data['codigo'] ?? $ncm,
                        'descricao' => $data['descricao'] ?? '',
                        'unidade' => $data['unidade'] ?? 'UN',
                        'data_inicio' => $data['data_inicio'] ?? null,
                        'data_fim' => $data['data_fim'] ?? null,
                        'tipo_ato' => $data['tipo_ato'] ?? null,
                        'numero_ato' => $data['numero_ato'] ?? null,
                        'ano_ato' => $data['ano_ato'] ?? null
                    ]
                ]);
            }
            
            // Se não encontrou exato, buscar por termo
            $responseSearch = Http::timeout(10)->get("https://brasilapi.com.br/api/ncm/v1", [
                'search' => $ncm
            ]);
            
            if ($responseSearch->successful()) {
                $results = $responseSearch->json();
                if (!empty($results) && is_array($results)) {
                    return response()->json([
                        'success' => true,
                        'multiple' => true,
                        'data' => array_slice($results, 0, 20) // Limitar a 20 resultados
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'NCM não encontrado na base de dados.'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar Brasil API: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Busca NCM por descrição/termo
     */
    public function buscarNcmPorDescricao(Request $request)
    {
        $request->validate([
            'termo' => 'required|string|min:3|max:100'
        ]);
        
        try {
            $response = Http::timeout(10)->get("https://brasilapi.com.br/api/ncm/v1", [
                'search' => $request->termo
            ]);
            
            if ($response->successful()) {
                $results = $response->json();
                if (!empty($results) && is_array($results)) {
                    return response()->json([
                        'success' => true,
                        'data' => array_slice($results, 0, 30) // Limitar a 30 resultados
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => []
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar NCM: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Buscar NCMs por faixa (prefixo) para importação em lote
     */
    public function buscarNcmPorFaixa(Request $request)
    {
        $request->validate([
            'prefixo' => 'required|string|min:2|max:8'
        ]);
        
        try {
            $prefixo = preg_replace('/[^0-9]/', '', $request->prefixo);
            
            // Buscar na Brasil API usando o prefixo como termo de busca
            $response = Http::timeout(30)->get("https://brasilapi.com.br/api/ncm/v1", [
                'search' => $prefixo
            ]);
            
            if ($response->successful()) {
                $results = $response->json();
                
                if (!empty($results) && is_array($results)) {
                    // Filtrar apenas os que começam com o prefixo informado
                    $filtrados = array_filter($results, function($item) use ($prefixo) {
                        $codigo = $item['codigo'] ?? '';
                        return strpos($codigo, $prefixo) === 0;
                    });
                    
                    // Reindexar array
                    $filtrados = array_values($filtrados);
                    
                    return response()->json([
                        'success' => true,
                        'total' => count($filtrados),
                        'data' => $filtrados
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'total' => 0,
                'data' => []
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar NCMs: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Importar NCMs em lote para o estoque
     */
    public function importarNcmsEmLote(Request $request)
    {
        $request->validate([
            'ncms' => 'required|array|min:1',
            'ncms.*.codigo' => 'required|string',
            'ncms.*.descricao' => 'required|string',
            'quantidade_inicial' => 'nullable|integer|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
            'estoque_maximo' => 'nullable|integer|min:0'
        ]);
        
        try {
            DB::beginTransaction();
            
            $importados = 0;
            $ignorados = 0;
            $erros = [];
            
            $quantidadeInicial = $request->quantidade_inicial ?? 0;
            $estoqueMinimo = $request->estoque_minimo ?? 0;
            $estoqueMaximo = $request->estoque_maximo;
            
            foreach ($request->ncms as $ncmData) {
                $ncm = preg_replace('/[^0-9]/', '', $ncmData['codigo']);
                $descricao = $ncmData['descricao'];
                
                // Verificar se já existe produto com esse NCM
                $existente = Estoque::where('ncm', $ncm)->first();
                if ($existente) {
                    $ignorados++;
                    continue;
                }
                
                // Criar nome do produto a partir da descrição (primeiros 200 caracteres)
                $nome = mb_substr($descricao, 0, 200);
                
                // Verificar se já existe produto com mesmo nome
                $nomeExistente = Estoque::where('nome', $nome)->first();
                if ($nomeExistente) {
                    // Adicionar NCM ao nome para diferenciar
                    $nome = $nome . ' (' . $ncm . ')';
                }
                
                // Preparar dados
                $dadosProduto = [
                    'nome' => $nome,
                    'descricao' => $descricao,
                    'quantidade' => $quantidadeInicial
                ];
                
                // Adicionar campos opcionais se existirem na tabela
                if (Schema::hasColumn('estoque', 'ncm')) {
                    $dadosProduto['ncm'] = $ncm;
                }
                if (Schema::hasColumn('estoque', 'unidade')) {
                    $unidade = $ncmData['unidade'] ?? 'UN';
                    $dadosProduto['unidade'] = $unidade ?: 'UN';
                }
                
                try {
                    $produto = Estoque::create($dadosProduto);
                    
                    // Salvar estoque mínimo/máximo
                    if ($estoqueMinimo > 0 || $estoqueMaximo) {
                        EstoqueMinMax::updateOrCreate(
                            ['produto_id' => $produto->id],
                            [
                                'minimo' => $estoqueMinimo,
                                'maximo' => $estoqueMaximo
                            ]
                        );
                    }
                    
                    $importados++;
                } catch (\Exception $e) {
                    $erros[] = "NCM {$ncm}: " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Importação concluída! {$importados} produtos importados, {$ignorados} ignorados (já existiam).",
                'importados' => $importados,
                'ignorados' => $ignorados,
                'erros' => $erros
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro na importação: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Listar categorias principais de NCM disponíveis
     */
    public function listarCategoriasNcm()
    {
        // Categorias principais da tabela NCM (primeiros 2 dígitos)
        $categorias = [
            ['prefixo' => '01', 'nome' => 'Animais vivos'],
            ['prefixo' => '02', 'nome' => 'Carnes e miudezas'],
            ['prefixo' => '03', 'nome' => 'Peixes e crustáceos'],
            ['prefixo' => '04', 'nome' => 'Leite, ovos, mel'],
            ['prefixo' => '05', 'nome' => 'Outros produtos de origem animal'],
            ['prefixo' => '06', 'nome' => 'Plantas vivas e floricultura'],
            ['prefixo' => '07', 'nome' => 'Produtos hortícolas'],
            ['prefixo' => '08', 'nome' => 'Frutas'],
            ['prefixo' => '09', 'nome' => 'Café, chá, mate'],
            ['prefixo' => '10', 'nome' => 'Cereais'],
            ['prefixo' => '11', 'nome' => 'Malte, amidos, féculas'],
            ['prefixo' => '12', 'nome' => 'Sementes e oleaginosos'],
            ['prefixo' => '13', 'nome' => 'Gomas, resinas'],
            ['prefixo' => '14', 'nome' => 'Matérias para entrançar'],
            ['prefixo' => '15', 'nome' => 'Gorduras e óleos'],
            ['prefixo' => '16', 'nome' => 'Preparações de carne'],
            ['prefixo' => '17', 'nome' => 'Açúcares e confeitaria'],
            ['prefixo' => '18', 'nome' => 'Cacau e preparações'],
            ['prefixo' => '19', 'nome' => 'Preparações de cereais'],
            ['prefixo' => '20', 'nome' => 'Preparações de hortícolas'],
            ['prefixo' => '21', 'nome' => 'Preparações alimentícias diversas'],
            ['prefixo' => '22', 'nome' => 'Bebidas, álcoois e vinagres'],
            ['prefixo' => '23', 'nome' => 'Resíduos alimentares'],
            ['prefixo' => '24', 'nome' => 'Tabaco'],
            ['prefixo' => '25', 'nome' => 'Sal, enxofre, terras e pedras'],
            ['prefixo' => '26', 'nome' => 'Minérios, escórias'],
            ['prefixo' => '27', 'nome' => 'Combustíveis minerais, óleos'],
            ['prefixo' => '28', 'nome' => 'Produtos químicos inorgânicos'],
            ['prefixo' => '29', 'nome' => 'Produtos químicos orgânicos'],
            ['prefixo' => '30', 'nome' => 'Produtos farmacêuticos'],
            ['prefixo' => '31', 'nome' => 'Adubos e fertilizantes'],
            ['prefixo' => '32', 'nome' => 'Tintas e vernizes'],
            ['prefixo' => '33', 'nome' => 'Óleos essenciais, perfumaria'],
            ['prefixo' => '34', 'nome' => 'Sabões, velas, massas'],
            ['prefixo' => '35', 'nome' => 'Matérias albuminoides, colas'],
            ['prefixo' => '36', 'nome' => 'Pólvoras, explosivos'],
            ['prefixo' => '37', 'nome' => 'Produtos fotográficos'],
            ['prefixo' => '38', 'nome' => 'Produtos químicos diversos'],
            ['prefixo' => '39', 'nome' => 'Plásticos e suas obras'],
            ['prefixo' => '40', 'nome' => 'Borracha e suas obras'],
            ['prefixo' => '41', 'nome' => 'Peles e couros'],
            ['prefixo' => '42', 'nome' => 'Obras de couro, artigos de viagem'],
            ['prefixo' => '43', 'nome' => 'Peleteria e suas obras'],
            ['prefixo' => '44', 'nome' => 'Madeira e carvão vegetal'],
            ['prefixo' => '45', 'nome' => 'Cortiça e suas obras'],
            ['prefixo' => '46', 'nome' => 'Obras de espartaria'],
            ['prefixo' => '47', 'nome' => 'Pastas de madeira'],
            ['prefixo' => '48', 'nome' => 'Papel e cartão'],
            ['prefixo' => '49', 'nome' => 'Livros, jornais, impressos'],
            ['prefixo' => '50', 'nome' => 'Seda'],
            ['prefixo' => '51', 'nome' => 'Lã e pelos'],
            ['prefixo' => '52', 'nome' => 'Algodão'],
            ['prefixo' => '53', 'nome' => 'Outras fibras têxteis'],
            ['prefixo' => '54', 'nome' => 'Filamentos sintéticos'],
            ['prefixo' => '55', 'nome' => 'Fibras sintéticas descontínuas'],
            ['prefixo' => '56', 'nome' => 'Pastas, feltros, cordéis'],
            ['prefixo' => '57', 'nome' => 'Tapetes e revestimentos têxteis'],
            ['prefixo' => '58', 'nome' => 'Tecidos especiais'],
            ['prefixo' => '59', 'nome' => 'Tecidos impregnados'],
            ['prefixo' => '60', 'nome' => 'Tecidos de malha'],
            ['prefixo' => '61', 'nome' => 'Vestuário de malha'],
            ['prefixo' => '62', 'nome' => 'Vestuário exceto malha'],
            ['prefixo' => '63', 'nome' => 'Outros artefatos têxteis'],
            ['prefixo' => '64', 'nome' => 'Calçados, polainas'],
            ['prefixo' => '65', 'nome' => 'Chapéus'],
            ['prefixo' => '66', 'nome' => 'Guarda-chuvas, bengalas'],
            ['prefixo' => '67', 'nome' => 'Penas e flores artificiais'],
            ['prefixo' => '68', 'nome' => 'Obras de pedra, gesso'],
            ['prefixo' => '69', 'nome' => 'Produtos cerâmicos'],
            ['prefixo' => '70', 'nome' => 'Vidro e suas obras'],
            ['prefixo' => '71', 'nome' => 'Pérolas, pedras preciosas, metais'],
            ['prefixo' => '72', 'nome' => 'Ferro fundido, ferro e aço'],
            ['prefixo' => '73', 'nome' => 'Obras de ferro fundido ou aço'],
            ['prefixo' => '74', 'nome' => 'Cobre e suas obras'],
            ['prefixo' => '75', 'nome' => 'Níquel e suas obras'],
            ['prefixo' => '76', 'nome' => 'Alumínio e suas obras'],
            ['prefixo' => '78', 'nome' => 'Chumbo e suas obras'],
            ['prefixo' => '79', 'nome' => 'Zinco e suas obras'],
            ['prefixo' => '80', 'nome' => 'Estanho e suas obras'],
            ['prefixo' => '81', 'nome' => 'Outros metais comuns'],
            ['prefixo' => '82', 'nome' => 'Ferramentas, cutelaria'],
            ['prefixo' => '83', 'nome' => 'Obras diversas de metais'],
            ['prefixo' => '84', 'nome' => 'Máquinas e aparelhos mecânicos'],
            ['prefixo' => '85', 'nome' => 'Máquinas e aparelhos elétricos'],
            ['prefixo' => '86', 'nome' => 'Veículos ferroviários'],
            ['prefixo' => '87', 'nome' => 'Veículos automóveis'],
            ['prefixo' => '88', 'nome' => 'Aeronaves'],
            ['prefixo' => '89', 'nome' => 'Embarcações'],
            ['prefixo' => '90', 'nome' => 'Instrumentos ópticos, médicos'],
            ['prefixo' => '91', 'nome' => 'Relógios'],
            ['prefixo' => '92', 'nome' => 'Instrumentos musicais'],
            ['prefixo' => '93', 'nome' => 'Armas e munições'],
            ['prefixo' => '94', 'nome' => 'Móveis, colchões, luminárias'],
            ['prefixo' => '95', 'nome' => 'Brinquedos, jogos, artigos esportivos'],
            ['prefixo' => '96', 'nome' => 'Obras diversas'],
            ['prefixo' => '97', 'nome' => 'Objetos de arte, antiguidades']
        ];
        
        return response()->json([
            'success' => true,
            'data' => $categorias
        ]);
    }
    
    public function criarProduto(Request $request)
    {
        $request->validate([
            'nome' => ['required','string','max:255',
                Rule::unique('estoque', 'nome')->where(function($q) use ($request){
                    return $q->where('descricao', $request->descricao);
                })
            ],
            'descricao' => 'nullable|string|max:1000',
            'quantidade' => 'required|integer|min:0',
            'ncm' => 'nullable|string|max:10',
            'codigo_barras' => 'nullable|string|max:20',
            'unidade' => 'nullable|string|max:10',
            'preco_custo' => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
            'estoque_maximo' => 'nullable|integer|min:0'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Preparar dados do produto
            $dadosProduto = [
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'quantidade' => $request->quantidade
            ];
            
            // Verificar e adicionar campos opcionais se existirem na tabela
            if (Schema::hasColumn('estoque', 'ncm') && $request->ncm) {
                $dadosProduto['ncm'] = preg_replace('/[^0-9]/', '', $request->ncm);
            }
            if (Schema::hasColumn('estoque', 'codigo_barras') && $request->codigo_barras) {
                $dadosProduto['codigo_barras'] = $request->codigo_barras;
            }
            if (Schema::hasColumn('estoque', 'unidade')) {
                $dadosProduto['unidade'] = $request->unidade ?? 'UN';
            }
            if (Schema::hasColumn('estoque', 'preco_custo') && $request->preco_custo) {
                $dadosProduto['preco_custo'] = $request->preco_custo;
            }
            
            $produto = Estoque::create($dadosProduto);
            
            // Salvar estoque mínimo/máximo se informado
            if ($request->estoque_minimo !== null || $request->estoque_maximo !== null) {
                EstoqueMinMax::updateOrCreate(
                    ['produto_id' => $produto->id],
                    [
                        'minimo' => $request->estoque_minimo ?? 0,
                        'maximo' => $request->estoque_maximo
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Produto cadastrado com sucesso!',
                'produto' => $produto
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar produto: ' . $e->getMessage()
            ], 400);
        }
    }
    
    public function atualizarProduto(Request $request, $id)
    {
        $request->validate([
            'nome' => ['required','string','max:255',
                Rule::unique('estoque', 'nome')->ignore($id)->where(function($q) use ($request){
                    return $q->where('descricao', $request->descricao);
                })
            ],
            'descricao' => 'nullable|string|max:1000',
            'quantidade' => 'required|integer|min:0',
            'ncm' => 'nullable|string|max:10',
            'codigo_barras' => 'nullable|string|max:20',
            'unidade' => 'nullable|string|max:10',
            'preco_custo' => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
            'estoque_maximo' => 'nullable|integer|min:0'
        ]);
        
        try {
            DB::beginTransaction();
            
            $produto = Estoque::findOrFail($id);
            $quantidadeAnterior = (int) $produto->quantidade;

            // Preparar dados do produto
            $dadosProduto = [
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'quantidade' => $request->quantidade
            ];
            
            // Verificar e adicionar campos opcionais se existirem na tabela
            if (Schema::hasColumn('estoque', 'ncm')) {
                $dadosProduto['ncm'] = $request->ncm ? preg_replace('/[^0-9]/', '', $request->ncm) : null;
            }
            if (Schema::hasColumn('estoque', 'codigo_barras')) {
                $dadosProduto['codigo_barras'] = $request->codigo_barras;
            }
            if (Schema::hasColumn('estoque', 'unidade')) {
                $dadosProduto['unidade'] = $request->unidade ?? 'UN';
            }
            if (Schema::hasColumn('estoque', 'preco_custo')) {
                $dadosProduto['preco_custo'] = $request->preco_custo;
            }

            $produto->update($dadosProduto);
            
            // Atualizar estoque mínimo/máximo
            if ($request->has('estoque_minimo') || $request->has('estoque_maximo')) {
                EstoqueMinMax::updateOrCreate(
                    ['produto_id' => $produto->id],
                    [
                        'minimo' => $request->estoque_minimo ?? 0,
                        'maximo' => $request->estoque_maximo
                    ]
                );
            }
            
            // Se a quantidade mudou, registrar ajuste
            $produto->refresh();
            $quantidadeNova = (int) $produto->quantidade;
            if ($quantidadeNova !== $quantidadeAnterior) {
                $alterada = $quantidadeNova - $quantidadeAnterior;
                $this->registrarLogEstoque(
                    $produto,
                    'ajuste',
                    $quantidadeAnterior,
                    $alterada,
                    $quantidadeNova,
                    'atualizarProduto',
                    null
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Produto atualizado com sucesso!',
                'produto' => $produto
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar produto: ' . $e->getMessage()
            ], 400);
        }
    }
    
    public function registrarEntrada(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:estoque,id',
            'quantidade' => 'required|integer|min:1',
            'observacoes' => 'nullable|string|max:1000'
        ]);
        
        try {
            DB::beginTransaction();
            
            $produto = Estoque::findOrFail($request->produto_id);
            
            // Adicionar quantidade ao estoque
            $quantidadeAnterior = (int) $produto->quantidade;
            $alterada = (int) $request->quantidade;
            $produto->increment('quantidade', $alterada);
            
            // Recarregar para obter a quantidade atualizada
            $produto->refresh();
            $this->registrarLogEstoque(
                $produto,
                'entrada',
                $quantidadeAnterior,
                $alterada,
                (int) $produto->quantidade,
                'registrarEntrada',
                $request->observacoes
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Entrada registrada com sucesso! Novo estoque: {$produto->quantidade} unidades",
                'produto' => $produto
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar entrada: ' . $e->getMessage()
            ], 400);
        }
    }
    
    public function registrarBaixa(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'centro_custo_id' => 'required|exists:centros_custo,id',
            'baixas' => 'required|array|min:1',
            'baixas.*.produto_id' => 'required|exists:estoque,id',
            'baixas.*.quantidade' => 'required|integer|min:1',
            'observacoes' => 'nullable|string|max:1000'
        ]);
        
        try {
            DB::beginTransaction();
            
            foreach ($request->baixas as $baixaData) {
                // Verificar se o produto existe e tem estoque suficiente
                $produto = Estoque::find($baixaData['produto_id']);
                if (!$produto) {
                    throw new \Exception("Produto não encontrado");
                }
                
                // Verificar se há estoque suficiente
                if ($produto->quantidade < $baixaData['quantidade']) {
                    throw new \Exception("Estoque insuficiente para o produto: {$produto->nome}. Disponível: {$produto->quantidade}, Solicitado: {$baixaData['quantidade']}");
                }
                
                // Registrar a baixa
                Baixa::create([
                    'funcionario_id' => $request->funcionario_id,
                    'centro_custo_id' => $request->centro_custo_id,
                    'produto_id' => $baixaData['produto_id'],
                    'quantidade' => $baixaData['quantidade'],
                    'observacoes' => $request->observacoes,
                    'data_baixa' => now(),
                    'usuario_id' => Auth::id()
                ]);
                
                // Decrementar o estoque
                $quantidadeAnterior = (int) $produto->quantidade;
                $alterada = (int) $baixaData['quantidade'];
                $produto->decrement('quantidade', $alterada);

                // Registrar log de saída
                $produto->refresh();
                $this->registrarLogEstoque(
                    $produto,
                    'saida',
                    $quantidadeAnterior,
                    -$alterada,
                    (int) $produto->quantidade,
                    'registrarBaixa',
                    $request->observacoes
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Baixa registrada com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
