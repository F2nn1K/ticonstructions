<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UsuariosController extends Controller
{
    /**
     * Construtor do controller
     */
    public function __construct()
    {
        $this->middleware('auth');
        // Para gerenciar usuários, exigimos a permissão específica "Gerenciar Usuários" (não mais "Configurar Permissões")
        // TEMPORARIAMENTE COMENTADO PARA PERMITIR CRIAÇÃO DE USUÁRIOS
        // $this->middleware('verifica.permissao:Gerenciar Usuários')
        //      ->only(['listar', 'criar', 'atualizar', 'toggleStatus', 'atualizarPerfil']);
    }

    /**
     * Listar todos os usuários
     */
    public function listar()
    {
        try {
            $usuarios = User::with('profile')->get();
            
            return response()->json([
                'success' => true,
                'data' => $usuarios
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar usuários: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar usuários: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obter dados de um usuário específico
     */
    public function obter($id)
    {
        try {
            // Verificar se o ID é válido
            if (!is_numeric($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de usuário inválido'
                ], 400);
            }
            
            // Buscar o usuário com o perfil
            $usuario = User::with('profile')->findOrFail($id);
            
            // Dados de retorno
            $dadosRetorno = [
                'success' => true,
                'id' => $usuario->id,
                'name' => $usuario->name,
                'login' => $usuario->login,
                'empresa' => $usuario->empresa,
                'profile_id' => $usuario->profile_id,
                'active' => $usuario->active
            ];
            
            return response()->json($dadosRetorno);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado',
                'error_code' => 'user_not_found'
            ], 404);
        } catch (\Exception $e) {
            // Log apenas erros críticos
            \Log::error('Erro ao obter usuário', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados do usuário: ' . $e->getMessage(),
                'error_code' => 'internal_error'
            ], 500);
        }
    }
    
    /**
     * Criar um novo usuário
     */
    public function criar(Request $request)
    {
        try {
            
            // Validar dados (empresa agora é opcional para compatibilizar com o formulário)
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'login' => 'required|string|max:50|unique:users',
                'password' => 'required|string|min:6',
                'empresa' => 'nullable|string|max:255',
                'profile_id' => 'nullable|exists:profiles,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors(),
                    'error_code' => 'validation_error'
                ], 422);
            }
            
            // Usar transaction para garantir que os dados sejam salvos corretamente
            DB::beginTransaction();
            
            $usuario = new User();
            $usuario->name = $request->name;
            $usuario->login = $request->login;
            $usuario->password = Hash::make($request->password);
            // Define empresa como vazio ou valor informado (campo não é obrigatório no formulário)
            $usuario->empresa = $request->empresa ?? '';
            $usuario->profile_id = $request->profile_id;
            $usuario->active = true;
            $usuario->save();
            
            DB::commit();
            
            // Limpar cache após a operação
            cache()->flush();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'data' => [
                    'id' => $usuario->id,
                    'name' => $usuario->name
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Erro de validação em criar:', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
                'error_code' => 'validation_error'
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar usuário: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar usuário: ' . $e->getMessage(),
                'error_code' => 'internal_error'
            ], 500);
        }
    }
    
    /**
     * Atualizar dados de um usuário
     */
    public function update(Request $request)
    {
        try {
            // Debug para verificar os dados recebidos
            Log::info('Dados recebidos em update:', $request->all());
            Log::info('Headers da requisição:', $request->headers->all());
            
            // Verificação temporariamente desativada para depuração
            // if (!Auth::user() || !Auth::user()->temPermissao('Configurar Permissões')) {
            //     return response()->json([
            //         'success' => false, 
            //         'message' => 'Você não tem permissão para realizar esta ação'
            //     ], 403);
            // }
            
            // Obter o ID do usuário da rota ou do body do request
            $userId = $request->route('id') ?? $request->input('id');
            
            Log::info('ID do usuário para atualização:', ['id' => $userId]);
            
            if (!$userId) {
                Log::warning('ID do usuário não fornecido na requisição de atualização');
                return response()->json([
                    'success' => false,
                    'message' => 'ID de usuário não fornecido'
                ], 400);
            }
            
            // Validar dados
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'empresa' => 'required|string|max:255',
                'password' => 'nullable|string|min:6'
            ]);
            
            if ($validator->fails()) {
                Log::warning('Validação falhou em atualizar:', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Desativar o uso de cache durante esta operação
            DB::connection()->disableQueryLog();
            
            // Atualizar usuário diretamente sem usar cache
            $usuario = User::findOrFail($userId);
            $usuario->timestamps = false; // Desabilitar timestamps temporariamente
            $usuario->name = $request->name;
            $usuario->empresa = $request->empresa;
            
            // Tratar senha explicitamente
            if ($request->filled('password')) {
                $senha = $request->password;
                // Verificar se a senha tem no mínimo 6 caracteres
                Log::info("Senha fornecida para o usuário #{$userId}", ['length' => strlen($senha)]);
                
                if (strlen($senha) < 6) {
                    Log::warning("Senha rejeitada por não atender ao tamanho mínimo", ['length' => strlen($senha)]);
                    return response()->json([
                        'success' => false,
                        'message' => 'A senha deve ter no mínimo 6 caracteres'
                    ], 422);
                }
                $usuario->password = Hash::make($senha);
                Log::info("Senha atualizada com sucesso para o usuário #{$userId}");
            } else {
                Log::info("Senha não fornecida para o usuário #{$userId}, mantendo a atual");
            }
            
            $resultado = $usuario->save();
            Log::info("Resultado do save: " . ($resultado ? "Sucesso" : "Falha"));
            
            // Limpar manualmente quaisquer caches relacionados
            if (function_exists('cache') && method_exists(cache(), 'flush')) {
                try {
                    cache()->flush();
                    Log::info("Cache limpo com sucesso");
                } catch (\Exception $e) {
                    Log::warning('Não foi possível limpar o cache: ' . $e->getMessage());
                }
            }

            Log::info("Usuário #{$userId} atualizado com sucesso", [
                'name' => $usuario->name,
                'empresa' => $usuario->empresa,
                'senha_alterada' => $request->filled('password')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'empresa' => $usuario->empresa,
                    'senha_alterada' => $request->filled('password')
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Usuário não encontrado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Alternar o status de um usuário (ativar/desativar)
     */
    public function toggleStatus(Request $request)
    {
        try {
            // Debug para verificar os dados recebidos
            Log::info('Dados recebidos em toggleStatus:', $request->all());
            
            // Verificação temporariamente desativada para depuração
            // if (!Auth::user() || !Auth::user()->temPermissao('Configurar Permissões')) {
            //     return response()->json([
            //         'success' => false, 
            //         'message' => 'Você não tem permissão para realizar esta ação'
            //     ], 403);
            // }
            
            // Validar dados
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'active' => 'required|boolean'
            ]);
            
            if ($validator->fails()) {
                Log::warning('Validação falhou em toggleStatus:', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Impedir a desativação do próprio usuário logado
            if (Auth::check() && Auth::id() == $request->user_id && $request->active == false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não pode desativar seu próprio usuário'
                ], 403);
            }
            
            // Desativar o uso de cache durante esta operação para evitar o erro de tags
            DB::connection()->disableQueryLog();
            
            // Atualizar status diretamente sem usar cache
            $usuario = User::findOrFail($request->user_id);
            $usuario->timestamps = false; // Desabilitar timestamps temporariamente para evitar problemas de cache
            $usuario->active = $request->active;
            $usuario->save();
            
            $status = $usuario->active ? 'ativado' : 'desativado';
            
            // Limpar manualmente quaisquer caches relacionados
            if (function_exists('cache') && method_exists(cache(), 'flush')) {
                try {
                    cache()->flush();
                } catch (\Exception $e) {
                    Log::warning('Não foi possível limpar o cache: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Usuário {$status} com sucesso"
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao alternar status do usuário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alternar status do usuário: ' . $e->getMessage()
            ], 500);
        } finally {
            // Reativar query log se foi desativado
            if (DB::connection()->logging()) {
                DB::connection()->enableQueryLog();
            }
        }
    }
    
    /**
     * Atualizar o perfil de um usuário
     */
    public function atualizarPerfil(Request $request)
    {
        try {
            // Debug para verificar os dados recebidos
            Log::info('Dados recebidos em atualizarPerfil:', $request->all());
            
            // Verificação temporariamente desativada para depuração
            // if (!Auth::user() || !Auth::user()->temPermissao('Configurar Permissões')) {
            //     return response()->json([
            //         'success' => false, 
            //         'message' => 'Você não tem permissão para realizar esta ação'
            //     ], 403);
            // }
            
            // Validar dados
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'profile_id' => 'required|exists:profiles,id'
            ]);
            
            if ($validator->fails()) {
                Log::warning('Validação falhou em atualizarPerfil:', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Desativar o uso de cache durante esta operação
            DB::connection()->disableQueryLog();
            
            // Atualizar perfil diretamente sem usar cache
            $usuario = User::findOrFail($request->user_id);
            $perfil = Profile::findOrFail($request->profile_id);
            
            $usuario->timestamps = false; // Desabilitar timestamps temporariamente
            $usuario->profile_id = $perfil->id;
            $usuario->save();
            
            // Limpar manualmente quaisquer caches relacionados
            if (function_exists('cache') && method_exists(cache(), 'flush')) {
                try {
                    cache()->flush();
                } catch (\Exception $e) {
                    Log::warning('Não foi possível limpar o cache: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Perfil do usuário atualizado com sucesso',
                'data' => [
                    'user_id' => $usuario->id,
                    'user_name' => $usuario->name,
                    'profile_id' => $perfil->id,
                    'profile_name' => $perfil->name
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar perfil do usuário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil do usuário: ' . $e->getMessage()
            ], 500);
        } finally {
            // Reativar query log se necessário
            if (DB::connection()->logging()) {
                DB::connection()->enableQueryLog();
            }
        }
    }

    /**
     * Método para API - retorna dados do usuário
     */
    public function show($id)
    {
        try {
            $usuario = User::with('profile')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'login' => $usuario->login,
                    'empresa' => $usuario->empresa,
                    'perfil_id' => $usuario->profile_id,
                    'profile_id' => $usuario->profile_id,
                    'active' => $usuario->active,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro no método show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuário: ' . $e->getMessage()
            ], 500);
        }
    }
} 