<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Controllers\PermissoesController;
use App\Http\Controllers\UsuariosController;
// use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\PedidoComprasController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==================== ROTA DE LOGIN PARA APP MOBILE ====================
Route::post('/mobile/login', function (Request $request) {
    $request->validate([
        'login' => 'required',
        'password' => 'required',
    ]);

    $user = \App\Models\User::where('login', $request->login)->first();

    if (!$user || !\Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Credenciais inválidas'
        ], 401);
    }

    if (!$user->active) {
        return response()->json([
            'success' => false,
            'message' => 'Usuário inativo'
        ], 403);
    }

    // Cria token Sanctum para o app mobile
    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'login' => $user->login,
            'empresa' => $user->empresa,
            'active' => $user->active,
            'profile_id' => $user->profile_id,
            'profile' => $user->profile,
        ]
    ]);
});
// ======================================================================

// Rota de usuário para app mobile (apenas auth:sanctum, sem middleware web)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user()->load('profile');
    
    $userData = [
        'id' => $user->id,
        'name' => $user->name,
        'login' => $user->login,
        'empresa' => $user->empresa,
        'active' => $user->active,
        'profile_id' => $user->profile_id,
    ];
    
    // Adiciona profile se existir
    if ($user->profile) {
        $userData['profile'] = [
            'id' => $user->profile->id,
            'name' => $user->profile->name,
        ];
    }
    
    return response()->json([
        'success' => true,
        'data' => $userData
    ]);
});

// ==================== ROTAS MOBILE - FROTA ====================
Route::middleware('auth:sanctum')->prefix('mobile/frota')->group(function () {
    // Veículos (apenas os disponíveis - sem viagem em andamento)
    Route::get('/veiculos', function () {
        try {
            // Verifica se a coluna 'ativo' existe na tabela veiculos
            $colunas = DB::select("SHOW COLUMNS FROM veiculos");
            $temAtivoCol = collect($colunas)->contains('Field', 'ativo');
            
            // Verifica se a tabela viagens existe e qual coluna usar
            $veiculosEmViagem = [];
            try {
                $colunasViagens = DB::select("SHOW COLUMNS FROM viagens");
                $colunasViagensList = collect($colunasViagens)->pluck('Field')->toArray();
                
                \Log::info('Colunas da tabela viagens: ' . json_encode($colunasViagensList));
                
                // Tenta diferentes possibilidades de nome de coluna
                $colunaVeiculo = null;
                if (in_array('veiculo_id', $colunasViagensList)) {
                    $colunaVeiculo = 'veiculo_id';
                } elseif (in_array('vehicle_id', $colunasViagensList)) {
                    $colunaVeiculo = 'vehicle_id';
                } elseif (in_array('id_veiculo', $colunasViagensList)) {
                    $colunaVeiculo = 'id_veiculo';
                }
                
                \Log::info('Coluna de veículo detectada: ' . ($colunaVeiculo ?? 'NENHUMA'));
                
                // Se encontrou a coluna, busca veículos em viagem
                if ($colunaVeiculo) {
                    // Viagens em andamento = SEM data_retorno (NULL)
                    $veiculosEmViagem = DB::table('viagens')
                        ->whereNull('data_retorno')
                        ->pluck($colunaVeiculo)
                        ->unique()
                        ->toArray();
                    
                    \Log::info('Veículos em viagem (sem data_retorno): ' . json_encode($veiculosEmViagem));
                } else {
                    \Log::error('COLUNA DE VEÍCULO NÃO ENCONTRADA! Colunas disponíveis: ' . implode(', ', $colunasViagensList));
                }
            } catch (\Exception $e) {
                \Log::error('ERRO ao verificar viagens: ' . $e->getMessage());
            }
            
            $query = DB::table('veiculos')
                ->select('id', 'placa', 'modelo', 'ano', 'tipo', 'status', 'km_atual');
            
            // Só filtra por 'ativo' se a coluna existir
            if ($temAtivoCol) {
                $query->where('ativo', 1);
            }
            
            // Filtra veículos que NÃO estão em viagem
            if (!empty($veiculosEmViagem)) {
                $query->whereNotIn('id', $veiculosEmViagem);
            }
            
            $veiculos = $query->orderBy('placa')->get();
            
            \Log::info('Veículos disponíveis: ' . $veiculos->count() . ' | Em viagem: ' . count($veiculosEmViagem));
            
            return response()->json([
                'success' => true,
                'data' => $veiculos
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar veículos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar veículos: ' . $e->getMessage(),
                'error_code' => 'database_error'
            ], 500);
        }
    });
    
    // Veículos - TODOS (incluindo em viagem) - para referência
    Route::get('/veiculos/todos', function () {
        try {
            $veiculos = DB::table('veiculos')
                ->select('id', 'placa', 'modelo', 'ano', 'tipo', 'status', 'km_atual')
                ->orderBy('placa')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $veiculos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::get('/veiculos/{id}', function ($id) {
        $veiculo = DB::table('veiculos')->where('id', $id)->first();
        
        if (!$veiculo) {
            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $veiculo
        ]);
    });
    
    // Abastecimentos
    Route::get('/abastecimentos', function (Request $request) {
        $query = DB::table('abastecimentos as a')
            ->leftJoin('veiculos as v', 'a.vehicle_id', '=', 'v.id')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->select(
                'a.id',
                'a.data',
                'a.km',
                'a.litros',
                'a.preco_litro',
                'a.valor',
                'a.tipo_combustivel',
                'a.posto',
                'v.placa',
                'v.modelo',
                'u.name as usuario'
            )
            ->orderBy('a.data', 'desc')
            ->limit(100);
        
        if ($request->has('vehicle_id')) {
            $query->where('a.vehicle_id', $request->vehicle_id);
        }
        
        $abastecimentos = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $abastecimentos
        ]);
    });
    
    Route::post('/abastecimentos', function (Request $request) {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:veiculos,id',
            'data' => 'required|date',
            'km' => 'required|numeric',
            'litros' => 'required|numeric',
            'preco_litro' => 'required|numeric',
            'tipo_combustivel' => 'required|string',
            'posto' => 'nullable|string'
        ]);
        
        $validated['valor'] = $validated['litros'] * $validated['preco_litro'];
        $validated['user_id'] = auth()->id();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();
        
        $id = DB::table('abastecimentos')->insertGetId($validated);
        
        // Atualiza KM do veículo
        DB::table('veiculos')
            ->where('id', $validated['vehicle_id'])
            ->update(['km_atual' => $validated['km']]);
        
        return response()->json([
            'success' => true,
            'message' => 'Abastecimento registrado com sucesso',
            'data' => ['id' => $id]
        ]);
    });
    
    // Viagens
    Route::get('/viagens', function (Request $request) {
        try {
            // Verifica quais colunas existem
            $colunasViagens = DB::select("SHOW COLUMNS FROM viagens");
            $colunasViagensList = collect($colunasViagens)->pluck('Field')->toArray();
            
            // Detecta nome da coluna de veículo
            $colunaVeiculo = 'veiculo_id';
            if (in_array('vehicle_id', $colunasViagensList)) {
                $colunaVeiculo = 'vehicle_id';
            } elseif (in_array('id_veiculo', $colunasViagensList)) {
                $colunaVeiculo = 'id_veiculo';
            }
            
            // Monta query base
            $query = DB::table('viagens as v')
                ->leftJoin('veiculos as ve', "v.$colunaVeiculo", '=', 've.id')
                ->select(
                    'v.id',
                    'v.data_saida',
                    'v.data_retorno',
                    'v.km_saida',
                    'v.km_retorno',
                    've.placa',
                    've.modelo',
                    // Define status baseado em data_retorno
                    DB::raw('CASE WHEN v.data_retorno IS NULL THEN "em_andamento" ELSE "finalizada" END as status')
                );
            
            // Adiciona campos opcionais
            if (in_array('destino', $colunasViagensList)) {
                $query->addSelect('v.destino');
            }
            if (in_array('finalidade', $colunasViagensList)) {
                $query->addSelect('v.finalidade');
            }
            if (in_array('motorista', $colunasViagensList)) {
                $query->addSelect('v.motorista');
            }
            if (in_array('observacoes', $colunasViagensList)) {
                $query->addSelect('v.observacoes');
            }
            
            $query->orderBy('v.data_saida', 'desc')->limit(100);
            
            if ($request->has('vehicle_id')) {
                $query->where("v.$colunaVeiculo", $request->vehicle_id);
            }
            
            $viagens = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $viagens
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao listar viagens: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar viagens: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::post('/viagens', function (Request $request) {
        try {
            // Verifica qual coluna usar para o veículo
            $colunasViagens = DB::select("SHOW COLUMNS FROM viagens");
            $colunasViagensList = collect($colunasViagens)->pluck('Field')->toArray();
            
            $colunaVeiculo = 'veiculo_id'; // padrão
            if (in_array('vehicle_id', $colunasViagensList)) {
                $colunaVeiculo = 'vehicle_id';
            } elseif (in_array('id_veiculo', $colunasViagensList)) {
                $colunaVeiculo = 'id_veiculo';
            }
            
            $validated = $request->validate([
                'veiculo_id' => 'required|exists:veiculos,id',
                'motorista_nome' => 'required|string',
                'data_saida' => 'required|date',
                'km_saida' => 'required|numeric',
                'destino' => 'required|string',
                'finalidade' => 'nullable|string'
            ]);
            
            // Prepara dados para inserção
            $dados = [
                $colunaVeiculo => $validated['veiculo_id'],
                'user_id' => auth()->id(),
                'data_saida' => $validated['data_saida'],
                'hora_saida' => now()->format('H:i:s'),
                'km_saida' => $validated['km_saida'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Adiciona destino se a coluna existir
            if (in_array('destino', $colunasViagensList)) {
                $dados['destino'] = $validated['destino'];
            }
            
            // Adiciona motorista se a coluna existir
            if (in_array('motorista', $colunasViagensList)) {
                $dados['motorista'] = $validated['motorista_nome'];
            }
            
            // Adiciona observações com motorista como fallback
            if (in_array('observacoes', $colunasViagensList)) {
                $dados['observacoes'] = 'Motorista: ' . $validated['motorista_nome'];
            }
            
            // Adiciona finalidade se existir
            if (!empty($validated['finalidade'])) {
                $dados['finalidade'] = $validated['finalidade'];
            }
            
            $id = DB::table('viagens')->insertGetId($dados);
            
            \Log::info('Viagem criada: ID ' . $id);
            
            return response()->json([
                'success' => true,
                'message' => 'Viagem iniciada com sucesso',
                'data' => ['id' => $id]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar viagem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar viagem: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::put('/viagens/{id}/finalizar', function (Request $request, $id) {
        try {
            // Verifica qual coluna usar para o veículo
            $colunasViagens = DB::select("SHOW COLUMNS FROM viagens");
            $colunasViagensList = collect($colunasViagens)->pluck('Field')->toArray();
            
            $colunaVeiculo = 'veiculo_id'; // padrão
            if (in_array('vehicle_id', $colunasViagensList)) {
                $colunaVeiculo = 'vehicle_id';
            } elseif (in_array('id_veiculo', $colunasViagensList)) {
                $colunaVeiculo = 'id_veiculo';
            }
            
            $validated = $request->validate([
                'data_retorno' => 'required|date',
                'km_retorno' => 'required|numeric'
            ]);
            
            $dadosUpdate = [
                'data_retorno' => $validated['data_retorno'],
                'hora_retorno' => now()->format('H:i:s'),
                'km_retorno' => $validated['km_retorno'],
                'updated_at' => now(),
            ];
            
            // Calcula km percorrido
            $viagem = DB::table('viagens')->where('id', $id)->first();
            if ($viagem && isset($viagem->km_saida)) {
                $dadosUpdate['km_percorrido'] = $validated['km_retorno'] - $viagem->km_saida;
            }
            
            DB::table('viagens')->where('id', $id)->update($dadosUpdate);
            
            // Atualiza KM do veículo (usa viagem já carregada antes)
            if ($viagem && isset($viagem->$colunaVeiculo)) {
                DB::table('veiculos')
                    ->where('id', $viagem->$colunaVeiculo)
                    ->update(['km_atual' => $validated['km_retorno']]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Viagem finalizada com sucesso'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao finalizar viagem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar viagem: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Manutenções
    Route::get('/manutencoes', function (Request $request) {
        $query = DB::table('manutencoes as m')
            ->leftJoin('veiculos as v', 'm.veiculo_id', '=', 'v.id')
            ->select(
                'm.id',
                'm.data',
                'm.tipo',
                'm.descricao',
                'm.valor',
                'm.km',
                'm.status',
                'v.placa',
                'v.modelo'
            )
            ->orderBy('m.data', 'desc')
            ->limit(100);
        
        if ($request->has('vehicle_id')) {
            $query->where('m.veiculo_id', $request->vehicle_id);
        }
        
        $manutencoes = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $manutencoes
        ]);
    });
    
    Route::post('/manutencoes', function (Request $request) {
        $validated = $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'data' => 'required|date',
            'tipo' => 'required|string',
            'descricao' => 'required|string',
            'valor' => 'required|numeric',
            'km' => 'required|numeric'
        ]);
        
        $validated['status'] = 'concluida';
        $validated['user_id'] = auth()->id();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();
        
        $id = DB::table('manutencoes')->insertGetId($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Manutenção registrada com sucesso',
            'data' => ['id' => $id]
        ]);
    });
    
    // Motoristas (funcionários)
    Route::get('/motoristas', function () {
        try {
            // Busca funcionários que NÃO estão demitidos
            // Aceita: ativo, trabalhando, afastado, etc
            $motoristas = DB::table('funcionarios')
                ->select('id', 'nome', 'cpf', 'funcao', 'status')
                ->whereNotIn('status', ['demitido', 'inativo'])
                ->orderBy('nome')
                ->get();
            
            // Se não encontrou nenhum, busca TODOS (inclusive demitidos)
            if ($motoristas->isEmpty()) {
                $motoristas = DB::table('funcionarios')
                    ->select('id', 'nome', 'cpf', 'funcao', 'status')
                    ->orderBy('nome')
                    ->limit(50)
                    ->get();
            }
            
            \Log::info('Motoristas encontrados: ' . $motoristas->count());
            
            return response()->json([
                'success' => true,
                'data' => $motoristas
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar motoristas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar motoristas: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Buscar funcionário do usuário logado (por nome)
    Route::get('/meu-funcionario', function (Request $request) {
        try {
            $user = $request->user();
            
            \Log::info('Buscando funcionário para: ' . $user->name);
            
            // Tenta buscar funcionário pelo nome do usuário (busca parcial)
            $funcionario = DB::table('funcionarios')
                ->select('id', 'nome', 'cpf', 'funcao', 'status')
                ->where('nome', 'LIKE', '%' . $user->name . '%')
                ->whereNotIn('status', ['demitido'])
                ->first();
            
            if ($funcionario) {
                \Log::info('Funcionário encontrado: ' . $funcionario->nome);
                return response()->json([
                    'success' => true,
                    'data' => $funcionario
                ]);
            } else {
                \Log::info('Funcionário não encontrado para: ' . $user->name . ', buscando qualquer um...');
                
                // Retorna o primeiro funcionário disponível como fallback
                $primeiroFunc = DB::table('funcionarios')
                    ->select('id', 'nome', 'cpf', 'funcao', 'status')
                    ->whereNotIn('status', ['demitido'])
                    ->first();
                
                if ($primeiroFunc) {
                    \Log::info('Usando primeiro funcionário: ' . $primeiroFunc->nome);
                    return response()->json([
                        'success' => true,
                        'data' => $primeiroFunc
                    ]);
                }
                
                // Se não encontrou nenhum, busca QUALQUER funcionário
                $qualquerFunc = DB::table('funcionarios')
                    ->select('id', 'nome', 'cpf', 'funcao', 'status')
                    ->first();
                
                if ($qualquerFunc) {
                    return response()->json([
                        'success' => true,
                        'data' => $qualquerFunc
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum funcionário encontrado no sistema'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar funcionário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar funcionário: ' . $e->getMessage()
            ], 500);
        }
    });
});

// ==================== ROTAS MOBILE - ESTOQUE ====================
Route::middleware('auth:sanctum')->prefix('mobile/estoque')->group(function () {
    // Listar produtos
    Route::get('/produtos', function (Request $request) {
        $query = DB::table('produtos')
            ->select('id', 'codigo', 'descricao', 'unidade', 'estoque_minimo', 'estoque_atual', 'preco_unitario')
            ->where('ativo', 1);
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'LIKE', "%{$search}%")
                  ->orWhere('descricao', 'LIKE', "%{$search}%");
            });
        }
        
        $produtos = $query->orderBy('descricao')->get();
        
        return response()->json([
            'success' => true,
            'data' => $produtos
        ]);
    });
    
    // Buscar produto
    Route::get('/produtos/{id}', function ($id) {
        $produto = DB::table('produtos')->where('id', $id)->first();
        
        if (!$produto) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $produto
        ]);
    });
    
    // Registrar baixa
    Route::post('/baixas', function (Request $request) {
        $validated = $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'funcionario_id' => 'required|exists:funcionarios,id',
            'quantidade' => 'required|numeric|min:0.01',
            'centro_custo_id' => 'nullable|exists:centro_custos,id',
            'observacoes' => 'nullable|string'
        ]);
        
        // Verifica estoque
        $produto = DB::table('produtos')->where('id', $validated['produto_id'])->first();
        
        if ($produto->estoque_atual < $validated['quantidade']) {
            return response()->json([
                'success' => false,
                'message' => 'Estoque insuficiente'
            ], 400);
        }
        
        $validated['user_id'] = auth()->id();
        $validated['data_baixa'] = now();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();
        
        DB::beginTransaction();
        try {
            // Registra baixa
            $id = DB::table('baixas_estoque')->insertGetId($validated);
            
            // Atualiza estoque
            DB::table('produtos')
                ->where('id', $validated['produto_id'])
                ->decrement('estoque_atual', $validated['quantidade']);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Baixa registrada com sucesso',
                'data' => ['id' => $id]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar baixa: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Listar funcionários
    Route::get('/funcionarios', function (Request $request) {
        $query = DB::table('funcionarios')
            ->select('id', 'nome', 'cpf', 'funcao')
            ->where('status', 'ativo');
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('cpf', 'LIKE', "%{$search}%");
            });
        }
        
        $funcionarios = $query->orderBy('nome')->get();
        
        return response()->json([
            'success' => true,
            'data' => $funcionarios
        ]);
    });
    
    // Listar centros de custo
    Route::get('/centro-custos', function () {
        $centros = DB::table('centro_custos')
            ->select('id', 'codigo', 'descricao')
            ->where('ativo', 1)
            ->orderBy('descricao')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $centros
        ]);
    });
});

// ==================== ROTAS MOBILE - OCORRÊNCIAS ====================
Route::middleware('auth:sanctum')->prefix('mobile/ocorrencias')->group(function () {
    // Listar ocorrências
    Route::get('/', function (Request $request) {
        $query = DB::table('ocorrencias as o')
            ->leftJoin('veiculos as v', 'o.veiculo_id', '=', 'v.id')
            ->leftJoin('funcionarios as f', 'o.motorista_id', '=', 'f.id')
            ->select(
                'o.id',
                'o.data_ocorrencia',
                'o.tipo',
                'o.descricao',
                'o.local',
                'o.status',
                'o.gravidade',
                'v.placa',
                'v.modelo',
                'f.nome as motorista'
            )
            ->orderBy('o.data_ocorrencia', 'desc')
            ->limit(100);
        
        $ocorrencias = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $ocorrencias
        ]);
    });
    
    // Criar ocorrência
    Route::post('/', function (Request $request) {
        $validated = $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'motorista_id' => 'nullable|exists:funcionarios,id',
            'data_ocorrencia' => 'required|date',
            'tipo' => 'required|string',
            'descricao' => 'required|string',
            'local' => 'nullable|string',
            'gravidade' => 'required|in:baixa,media,alta',
            'fotos' => 'nullable|array'
        ]);
        
        $validated['status'] = 'aberta';
        $validated['user_id'] = auth()->id();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();
        
        // Remove fotos para inserir na tabela principal
        $fotos = $validated['fotos'] ?? [];
        unset($validated['fotos']);
        
        $id = DB::table('ocorrencias')->insertGetId($validated);
        
        // Salva fotos se houver
        if (!empty($fotos)) {
            foreach ($fotos as $foto) {
                DB::table('ocorrencia_fotos')->insert([
                    'ocorrencia_id' => $id,
                    'foto_base64' => $foto,
                    'created_at' => now()
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Ocorrência registrada com sucesso',
            'data' => ['id' => $id]
        ]);
    });
    
    // Detalhes da ocorrência
    Route::get('/{id}', function ($id) {
        $ocorrencia = DB::table('ocorrencias as o')
            ->leftJoin('veiculos as v', 'o.veiculo_id', '=', 'v.id')
            ->leftJoin('funcionarios as f', 'o.motorista_id', '=', 'f.id')
            ->select(
                'o.*',
                'v.placa',
                'v.modelo',
                'f.nome as motorista'
            )
            ->where('o.id', $id)
            ->first();
        
        if (!$ocorrencia) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorrência não encontrada'
            ], 404);
        }
        
        // Busca fotos
        $fotos = DB::table('ocorrencia_fotos')
            ->where('ocorrencia_id', $id)
            ->select('id', 'foto_base64')
            ->get();
        
        $ocorrencia->fotos = $fotos;
        
        return response()->json([
            'success' => true,
            'data' => $ocorrencia
        ]);
    });
});
// ================================================================

Route::middleware(['web', 'auth:sanctum'])->group(function () {

    // Rota de gerentes
    Route::get('/gerentes', function () {
        $gerentes = DB::table('funcionarios')
            ->where('departamento', 'Gerência')
            ->select('nome as gerente')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $gerentes
        ]);
    });

    // Perfis e Usuários (grupo protegido por permissões administrativas)
    // Troca para permissões específicas e independentes: "Gerenciar Usuários" e "Gerenciar Permissões"
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/perfis', [PermissoesController::class, 'listarPerfis']);
        Route::get('/perfis/{id}', [PermissoesController::class, 'obterPerfil']);
        Route::post('/perfis', [PermissoesController::class, 'criarPerfil']);
        Route::post('/perfis/{id}', [PermissoesController::class, 'atualizarPerfil']);
        Route::delete('/perfis/{id}', [PermissoesController::class, 'excluirPerfil']);

        // Permissões
        Route::get('/permissoes/listar', [PermissoesController::class, 'listar']);
        Route::get('/permissoes/{id}', [PermissoesController::class, 'obter']);
        Route::post('/permissoes', [PermissoesController::class, 'store']);
        Route::put('/permissoes/{id}', [PermissoesController::class, 'update']);
        Route::delete('/permissoes/{id}', [PermissoesController::class, 'destroy']);
    });

    // Usuários: separar em grupo com permissão "Gerenciar Usuários"
    // TEMPORARIAMENTE COMENTADO PARA PERMITIR CRIAÇÃO DE USUÁRIOS
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/usuarios', [UsuariosController::class, 'listar']);
        Route::post('/usuarios/criar', [UsuariosController::class, 'criar']);
        Route::post('/usuarios/atualizar', [UsuariosController::class, 'atualizar']);
        Route::post('/usuarios/toggle-status', [UsuariosController::class, 'toggleStatus']);
        Route::post('/usuarios/atualizar-perfil', [UsuariosController::class, 'atualizarPerfil']);
        Route::put('/usuarios/{id}/perfil', function (Request $request, $id) {
            try {
                $perfilId = $request->input('perfil_id');
                
                if (!$perfilId) {
                    return response()->json([
                        'success' => false,
                        'mensagem' => 'ID do perfil não fornecido',
                        'error_code' => 'missing_profile_id'
                    ], 400);
                }
                
                // Verificar se o usuário existe
                $usuario = \App\Models\User::findOrFail($id);
                
                // Verificar se o perfil existe
                $perfil = \App\Models\Profile::findOrFail($perfilId);
                
                // Atualizar o perfil do usuário
                $usuario->profile_id = $perfilId;
                $usuario->save();
                
                return response()->json([
                    'success' => true,
                    'mensagem' => 'Perfil atualizado com sucesso'
                ]);
                
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $tipo = strpos($e->getMessage(), 'Profile') !== false ? 'Perfil' : 'Usuário';
                return response()->json([
                    'success' => false,
                    'mensagem' => $tipo . ' não encontrado',
                    'error_code' => strtolower($tipo) . '_not_found'
                ], 404);
            } catch (\Exception $e) {
                \Log::error('Erro ao atualizar perfil do usuário via API: ' . $e->getMessage(), [
                    'user_id' => $id,
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                return response()->json([
                    'success' => false,
                    'mensagem' => 'Erro ao atualizar perfil: ' . $e->getMessage(),
                    'error_code' => 'update_error'
                ], 500);
            }
        });
        
        // Rota para ativar/desativar usuário
        Route::put('/usuarios/{id}/status', function (Request $request, $id) {
            try {
                // Encontrar o usuário
                $usuario = \App\Models\User::findOrFail($id);
                
                // Validar os dados recebidos
                $validatedData = $request->validate([
                    'active' => 'required|boolean',
                ]);
                
                // Atualizar o status do usuário
                $usuario->active = $validatedData['active'];
                $usuario->save();
                
                // Retornar resposta de sucesso
                return response()->json([
                    'success' => true,
                    'message' => $validatedData['active'] ? 'Usuário ativado com sucesso' : 'Usuário desativado com sucesso'
                ]);
                
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'error_code' => 'user_not_found'
                ], 404);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $e->errors(),
                    'error_code' => 'validation_error'
                ], 422);
            } catch (\Exception $e) {
                \Log::error('Erro ao alterar status do usuário via API:', [
                    'id' => $id,
                    'erro' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao alterar status do usuário: ' . $e->getMessage(),
                    'error_code' => 'update_error'
                ], 500);
            }
        });
        
        // Usuários (API Controller) - COMENTADO: Controller não existe
        // Route::post('/users/criar', [UserController::class, 'store']);
        // Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
    
    // Rotas para Pedido de Compras
    Route::get('/produtos/buscar', [PedidoComprasController::class, 'buscarProdutos']);
    Route::get('/centro-custos/buscar', [PedidoComprasController::class, 'buscarCentrosCustoAutocomplete']);
    
    
    // Rota de diagnóstico
    Route::get('/diagnostico', function() {
        return response()->json([
            'success' => true,
            'message' => 'API funcionando corretamente',
            'timestamp' => now(),
            'ambiente' => app()->environment(),
            'versao_laravel' => app()->version()
        ]);
    });
});

// Proteção das rotas de usuários com autenticação e limite de requisições
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Rota de teste para depuração de usuários
    Route::get('/usuarios/debug/{id}', function ($id) {
        try {
            // Buscar usuário diretamente do banco de dados
            $usuario = DB::table('users')
                ->select('id', 'name', 'login', 'empresa', 'active', 'profile_id')
                ->where('id', $id)
                ->first();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado no banco de dados'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'method' => 'debug',
                'data' => $usuario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuário: ' . $e->getMessage()
            ], 500);
        }
    });

    // Rotas de acesso geral (agora autenticadas)
    Route::get('/usuarios/{id}', [UsuariosController::class, 'obter']);
});

// Route::get('relatorio/recursos-humanos', 'App\Http\Controllers\Api\RelatorioController@recursosHumanos');

// Rotas de acesso geral (apenas autenticadas) — movidas para o grupo acima
// Route::get('/users/{id}', [UserController::class, 'show']);
// Route::put('/users/{id}', [UserController::class, 'update']);

// Rota específica para atualizar usuários pela nova API
Route::put('/usuarios/{id}', function (Request $request, $id) {
    try {
        // Encontrar o usuário
        $usuario = \App\Models\User::findOrFail($id);
        
        // Apenas o próprio usuário ou usuários com permissão podem atualizar
        if (
            Auth::id() != $id &&
            !(
                Auth::user()->temPermissao('Gerenciar Usuários') ||
                Auth::user()->temPermissao('Configurar Permissões')
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para atualizar este usuário',
                'error_code' => 'permission_denied'
            ], 403);
        }
        
        // Validar os dados recebidos
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'empresa' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);
        
        // Atualizar os dados do usuário
        $usuario->name = $validatedData['name'];
        $usuario->empresa = $validatedData['empresa'];
        
        // Atualizar senha apenas se foi fornecida
        if (isset($validatedData['password'])) {
            $usuario->password = \Illuminate\Support\Facades\Hash::make($validatedData['password']);
        }
        
        // Salvar alterações
        $usuario->save();
        
        // Retornar resposta de sucesso
        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso'
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Usuário não encontrado',
            'error_code' => 'user_not_found'
        ], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Dados inválidos',
            'errors' => $e->errors(),
            'error_code' => 'validation_error'
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Erro ao atualizar usuário via API:', [
            'id' => $id,
            'erro' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao atualizar usuário: ' . $e->getMessage(),
            'error_code' => 'update_error'
        ], 500);
    }
});

// API para permissões
Route::middleware('auth')->group(function() {
    // Listar permissões
    Route::get('/permissoes', function() {
        $permissoes = DB::table('permissions')->get();
        return response()->json([
            'success' => true,
            'data' => $permissoes
        ]);
    });
    
    // Obter uma permissão específica
    Route::get('/permissoes/{id}', function($id) {
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        if (!$permissao) {
            return response()->json([
                'success' => false,
                'message' => 'Permissão não encontrada'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $permissao
        ]);
    });
    
    // Criar uma nova permissão
    Route::post('/permissoes', function(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'code' => 'nullable|string|max:255|unique:permissions,code',
            'description' => 'nullable|string'
        ]);
        
        $id = DB::table('permissions')->insertGetId([
            'name' => $request->name,
            'code' => $request->code ?? strtolower(str_replace(' ', '_', $request->name)),
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão criada com sucesso',
            'data' => $permissao
        ], 201);
    });
    
    // Atualizar uma permissão existente
    Route::put('/permissoes/{id}', function(Request $request, $id) {
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        if (!$permissao) {
            return response()->json([
                'success' => false,
                'message' => 'Permissão não encontrada'
            ], 404);
        }
        
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'code' => 'nullable|string|max:255|unique:permissions,code,' . $id,
            'description' => 'nullable|string'
        ]);
        
        DB::table('permissions')->where('id', $id)->update([
            'name' => $request->name,
            'code' => $request->code ?? $permissao->code,
            'description' => $request->description,
            'updated_at' => now()
        ]);
        
        $permissaoAtualizada = DB::table('permissions')->where('id', $id)->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão atualizada com sucesso',
            'data' => $permissaoAtualizada
        ]);
    });
    
    // Excluir uma permissão
    Route::delete('/permissoes/{id}', function($id) {
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        if (!$permissao) {
            return response()->json([
                'success' => false,
                'message' => 'Permissão não encontrada'
            ], 404);
        }
        
        // Remover relacionamentos primeiro
        DB::table('profile_permissions')->where('permission_id', $id)->delete();
        
        // Remover permissão
        DB::table('permissions')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão excluída com sucesso'
        ]);
    });
});

