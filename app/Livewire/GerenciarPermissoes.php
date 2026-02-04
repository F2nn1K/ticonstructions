<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Profile;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;

class GerenciarPermissoes extends Component
{
    use WithPagination;
    
    public $usuarios;
    public $perfis;
    public $permissoes;
    public $gruposPermissoes;
    public $usuarioSelecionado = null;
    public $perfilSelecionado = null;
    public $permissoesSelecionadas = [];
    public $mensagem = '';
    public $tipo_mensagem = '';
    public $mostrarMensagem = false;
    public $refreshOnNextRequest = false;

    // Variáveis para o novo usuário
    public $novoUsuario = [
        'name' => '',
        'login' => '',
        'password' => '',
        'profile_id' => '',
        'empresa' => ''
    ];
    public $confirmacaoSenha = '';

    // Variáveis para edição de usuário
    public $usuarioEdit = [
        'id' => '',
        'name' => '',
        'login' => '',
        'password' => '',
        'empresa' => ''
    ];

    // Removido: permissões individuais não utilizadas pelo sistema
    public $showNovoUsuario = false;

    public $showModalPerfis = false;
    public $perfilSelecionadoModal = null;
    public $permissoesPerfil = [];
    public $todasPermissoes = [];
    public $permissoesSelecionadasModal = [];

    public $showModalNovoPerfil = false;
    public $novoPerfil = [
        'name' => '',
        'description' => ''
    ];

    public $showModalEdit = false;

    public $search = '';
    public $selectedUser = null;
    public $selectedProfile = null;

    // Propriedades para busca e filtro
    public $termoBusca = '';
    public $filtroStatus = '';

    protected $rules = [
        'novoUsuario.name' => 'required|min:3',
        'novoUsuario.login' => 'required|min:3|unique:users,login',
        'novoUsuario.password' => 'required|min:6',
        'confirmacaoSenha' => 'required|same:novoUsuario.password',
        'novoUsuario.profile_id' => 'required',
        'novoUsuario.empresa' => 'required',
        'usuarioEdit.name' => 'required|min:3',
        'usuarioEdit.login' => 'required|min:3',
        'usuarioEdit.password' => 'nullable|min:6',
        'usuarioEdit.profile_id' => 'required',
        'usuarioEdit.empresa' => 'required',
        'novoPerfil.name' => 'required|min:3|unique:profiles,name'
    ];

    protected $messages = [
        'novoUsuario.name.required' => 'O nome é obrigatório',
        'novoUsuario.name.min' => 'O nome deve ter pelo menos 3 caracteres',
        'novoUsuario.login.required' => 'O login é obrigatório',
        'novoUsuario.login.min' => 'O login deve ter pelo menos 3 caracteres',
        'novoUsuario.login.unique' => 'Este login já está em uso',
        'novoUsuario.password.required' => 'A senha é obrigatória',
        'novoUsuario.password.min' => 'A senha deve ter pelo menos 6 caracteres',
        'novoUsuario.empresa.required' => 'A empresa é obrigatória',
        'confirmacaoSenha.required' => 'A confirmação de senha é obrigatória',
        'confirmacaoSenha.same' => 'As senhas não conferem',
        'novoUsuario.profile_id.required' => 'Selecione um perfil',
        'usuarioEdit.name.required' => 'O nome é obrigatório',
        'usuarioEdit.name.min' => 'O nome deve ter pelo menos 3 caracteres',
        'usuarioEdit.login.required' => 'O login é obrigatório',
        'usuarioEdit.login.min' => 'O login deve ter pelo menos 3 caracteres',
        'usuarioEdit.password.min' => 'A senha deve ter pelo menos 6 caracteres',
        'usuarioEdit.profile_id.required' => 'Selecione um perfil',
        'usuarioEdit.empresa.required' => 'A empresa é obrigatória',
        'novoPerfil.name.required' => 'O nome do perfil é obrigatório',
        'novoPerfil.name.min' => 'O nome do perfil deve ter pelo menos 3 caracteres',
        'novoPerfil.name.unique' => 'Já existe um perfil com este nome'
    ];

    // Atualiza a lista de usuários em tempo real
    protected $listeners = [
        'refreshComponent' => '$refresh',
        'limparMensagem' => 'limparMensagem',
        'criarUsuario' => 'criarUsuario',
        'atualizarUsuario' => 'atualizarUsuario',
        'toggleUserStatus' => 'toggleUserStatus',
        'editarUsuario' => 'editarUsuarioEvento',
        'salvarUsuario' => 'salvarUsuario',
        'refreshUsuarios' => 'refresh'
    ];

    public function mount()
    {
        $this->carregarPerfis();
        $this->carregarTodasPermissoes();
        $this->carregarDados();

        // Verificar se o usuário tem permissão para acessar esta página
        if (!Auth::user()->temPermissao('Configurar Permissões')) {
            session()->flash('error', 'Você não tem permissão para acessar esta página.');
            return redirect()->route('dashboard');
        }
    }

    public function updated($field)
    {
        if ($field === 'perfilSelecionado' && $this->perfilSelecionado && $this->usuarioSelecionado) {
            $this->atualizarPerfilUsuario($this->perfilSelecionado);
        }

        $this->validateOnly($field, [
            'novoUsuario.name' => 'required|min:3',
            'novoUsuario.login' => 'required|min:3|unique:users,login',
            'novoUsuario.password' => 'required|min:6',
            'novoUsuario.profile_id' => 'required',
            'usuarioEdit.name' => 'required|min:3',
            'usuarioEdit.profile_id' => 'required'
        ]);
    }

    public function updatedSearch($value)
    {
        // Apenas disparar o evento sem logs
        $this->dispatch('updatedSearch', $value);
    }

    public function carregarPerfis()
    {
        try {
            $this->usuarios = User::with('profile')
                ->select('id', 'name', 'login', 'empresa', 'active', 'profile_id')
                ->orderBy('name')
                ->get();
            
            $this->perfis = Profile::with('permissions')->get();
            
            \Log::info('Perfis carregados com sucesso', [
                'total_perfis' => count($this->perfis)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar perfis', [
                'erro' => $e->getMessage()
            ]);
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao carregar dados: ' . $e->getMessage()
            ]);
        }
    }

    public function carregarTodasPermissoes()
    {
        $this->todasPermissoes = Permission::orderBy('name')->get();
    }

    public function carregarDados()
    {
        $this->carregarPerfis();
        
        // Carrega todas as permissões agrupadas
        $this->permissoes = Permission::orderBy('group_id')
            ->orderBy('name')
            ->get();
        
        // Carrega os grupos de permissões
        $this->gruposPermissoes = DB::table('permission_groups')
            ->orderBy('name')
            ->get();
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->novoUsuario = [
            'name' => '',
            'login' => '',
            'password' => '',
            'profile_id' => '',
            'empresa' => ''
        ];
        $this->confirmacaoSenha = '';
    }

    // Método para selecionar usuário (botão engrenagem)
    public function selecionarUsuario($userId)
    {
        \Log::info('Método selecionarUsuario chamado', ['userId' => $userId]);
        
        try {
            $usuario = User::find($userId);
            if ($usuario) {
                $this->usuarioSelecionado = $userId;
                $this->perfilSelecionado = $usuario->profile_id;
                
                \Log::info('Usuário selecionado com sucesso', [
                    'usuario' => $usuario->toArray()
                ]);
                
                session()->flash('message', 'Usuário selecionado com sucesso!');
                $this->dispatch('usuarioSelecionado', [
                    'userId' => $userId,
                    'userName' => $usuario->name
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao selecionar usuário', [
                'error' => $e->getMessage(),
                'userId' => $userId
            ]);
            session()->flash('error', 'Erro ao selecionar usuário.');
        }
    }

    // Método para editar usuário (botão lápis)
    public function editarUsuario($userId)
    {
        \Log::info('editarUsuario iniciado', ['userId' => $userId]);
        
        try {
            $usuario = User::find($userId);
            
            if (!$usuario) {
                \Log::error('Usuário não encontrado', ['userId' => $userId]);
                return;
            }
            
            \Log::info('Usuário encontrado', [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'login' => $usuario->login,
                'empresa' => $usuario->empresa
            ]);
            
            $this->usuarioEdit = [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'login' => $usuario->login,
                'password' => '',
                'empresa' => $usuario->empresa
            ];
            
            \Log::info('Dados carregados no usuarioEdit', $this->usuarioEdit);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar usuário', [
                'userId' => $userId,
                'erro' => $e->getMessage()
            ]);
        }
    }

    // Método para atualizar usuário (salvar edição)
    public function atualizarUsuario()
    {
        \Log::info('Iniciando atualizarUsuario', ['usuarioEdit' => $this->usuarioEdit]);
        
        try {
            $usuario = User::find($this->usuarioEdit['id']);
            
            if (!$usuario) {
                \Log::error('Usuário não encontrado para atualização', ['id' => $this->usuarioEdit['id']]);
                session()->flash('error', 'Usuário não encontrado.');
                return;
            }
            
            \Log::info('Atualizando usuário', [
                'id' => $usuario->id,
                'novos_dados' => [
                    'name' => $this->usuarioEdit['name'],
                    'empresa' => $this->usuarioEdit['empresa'],
                    'senha_alterada' => !empty($this->usuarioEdit['password'])
                ]
            ]);
            
            $usuario->name = $this->usuarioEdit['name'];
            $usuario->empresa = $this->usuarioEdit['empresa'];
            
            if (!empty($this->usuarioEdit['password'])) {
                $usuario->password = Hash::make($this->usuarioEdit['password']);
            }

            $usuario->save();
            
            \Log::info('Usuário atualizado com sucesso', ['id' => $usuario->id]);
            
            session()->flash('message', 'Usuário atualizado com sucesso!');
            
            // Fecha o modal usando jQuery e recarrega os dados
            $this->dispatch('closeModal', ['modal' => 'modalEditarUsuario']);
            
            // Recarrega a lista de usuários
            $this->carregarPerfis();
            
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar usuário', [
                'erro' => $e->getMessage(),
                'usuario_id' => $this->usuarioEdit['id'] ?? 'não definido'
            ]);
            
            session()->flash('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    // Método para ativar/desativar usuário (botão status)
    #[On('toggleUserStatus')]
    public function toggleUserStatus($userId)
    {
        \Log::info('Método toggleUserStatus chamado', ['userId' => $userId]);
        
        try {
            $usuario = User::find($userId);
            if (!$usuario) {
                throw new \Exception('Usuário não encontrado.');
            }
            
            if ($usuario->id === auth()->id()) {
                \Log::warning('Tentativa de desativar próprio usuário', [
                    'userId' => $userId,
                    'authUserId' => auth()->id()
                ]);
                throw new \Exception('Você não pode desativar seu próprio usuário.');
            }
            
            // Alterna o status do usuário
            $statusAnterior = $usuario->active;
            $usuario->active = !$usuario->active;
            
            // Usar DB query builder para evitar problemas com cache
            DB::table('users')
                ->where('id', $usuario->id)
                ->update(['active' => $usuario->active]);
            
            \Log::info('Status do usuário alterado', [
                'usuario' => $usuario->id,
                'statusAnterior' => $statusAnterior,
                'statusNovo' => $usuario->active
            ]);
            
            return [
                'success' => true,
                'message' => $usuario->active ? 'Usuário ativado com sucesso!' : 'Usuário desativado com sucesso!'
            ];
        } catch (\Exception $e) {
            \Log::error('Erro ao alterar status do usuário', [
                'error' => $e->getMessage(),
                'userId' => $userId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao alterar status do usuário: ' . $e->getMessage()
            ];
        }
    }

    public function criarUsuario()
    {
        try {
            $this->validate([
                'novoUsuario.name' => 'required|min:3',
                'novoUsuario.login' => 'required|min:3|unique:users,login',
                'novoUsuario.password' => 'required|min:6',
                'novoUsuario.profile_id' => 'required'
            ]);
            
            DB::beginTransaction();

            $usuario = User::create([
                'name' => $this->novoUsuario['name'],
                'login' => $this->novoUsuario['login'],
                'password' => Hash::make($this->novoUsuario['password']),
                'active' => true
            ]);

            DB::table('user_profiles')->insert([
                'user_id' => $usuario->id,
                'profile_id' => $this->novoUsuario['profile_id'],
                'created_at' => now()
            ]);

            DB::commit();

            $this->resetForm();
            $this->dispatch('hideModal');
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'success',
                'mensagem' => 'Usuário criado com sucesso!'
            ]);
            
            $this->carregarPerfis();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao criar usuário: ' . $e->getMessage()
            ]);
        }
    }

    public function limparMensagem()
    {
        $this->mensagem = '';
        $this->tipo_mensagem = '';
        $this->mostrarMensagem = false;
    }

    public function salvarPerfis()
    {
        if (!$this->usuarioSelecionado) {
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Selecione um usuário primeiro.'
            ]);
            return;
        }

        if (!$this->perfilSelecionado) {
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Selecione um perfil para o usuário.'
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            $usuario = User::find($this->usuarioSelecionado);
            if (!$usuario) {
                throw new \Exception('Usuário não encontrado.');
            }

            // Atualiza o profile_id do usuário
            $usuario->profile_id = $this->perfilSelecionado;
            $usuario->save();

            DB::commit();

            $this->dispatch('mostrarMensagem', [
                'tipo' => 'success',
                'mensagem' => 'Perfil atualizado com sucesso!'
            ]);

            $this->carregarPerfis();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao salvar perfil: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmarToggleStatus($userId)
    {
        \Log::info('Iniciando confirmarToggleStatus', ['userId' => $userId]);
        
        try {
            $usuario = User::find($userId);
            \Log::info('Usuário encontrado para confirmação', ['usuario' => $usuario]);

            if (!$usuario) {
                \Log::error('Usuário não encontrado para confirmação');
                throw new \Exception('Usuário não encontrado.');
            }

            if ($usuario->id === auth()->id()) {
                \Log::warning('Tentativa de confirmar desativação do próprio usuário');
                $this->dispatch('mostrarMensagem', [
                    'tipo' => 'error',
                    'mensagem' => 'Você não pode desativar seu próprio usuário.'
                ]);
                return;
            }

            \Log::info('Disparando evento de confirmação', [
                'usuario_id' => $usuario->id,
                'status_atual' => $usuario->active
            ]);

            $this->dispatch('confirmarAcao', [
                'titulo' => $usuario->active ? 'Desativar Usuário' : 'Ativar Usuário',
                'mensagem' => $usuario->active 
                    ? "Tem certeza que deseja desativar o usuário {$usuario->name}?" 
                    : "Tem certeza que deseja ativar o usuário {$usuario->name}?",
                'userId' => $userId
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao processar confirmação', [
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile()
            ]);

            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao processar a solicitação: ' . $e->getMessage()
            ]);
        }
    }

    public function selecionarPerfil($perfilId)
    {
        try {
            \Log::info('Selecionando perfil', ['perfil_id' => $perfilId]);
            
            $this->perfilSelecionado = $perfilId;
            
            // Carrega as permissões do perfil
            $this->permissoesSelecionadas = DB::table('profile_permissions')
                ->where('profile_id', $perfilId)
                ->pluck('permission_id')
                ->toArray();
            
            \Log::info('Permissões carregadas', [
                'total_permissoes' => count($this->permissoesSelecionadas),
                'permissoes' => $this->permissoesSelecionadas
            ]);
            
            // Força atualização do componente
            $this->dispatch('refreshComponent');
            
        } catch (\Exception $e) {
            \Log::error('Erro ao selecionar perfil', [
                'erro' => $e->getMessage(),
                'perfil_id' => $perfilId
            ]);
            
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao carregar perfil: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleTodasPermissoes()
    {
        try {
            if (!$this->perfilSelecionado) {
                throw new \Exception('Nenhum perfil selecionado.');
            }

            $todasPermissoes = Permission::pluck('id')->toArray();
            
            // Se todas as permissões estão selecionadas, remove todas
            if (count($this->permissoesSelecionadas) === count($todasPermissoes)) {
                $this->permissoesSelecionadas = [];
            } else {
                // Caso contrário, seleciona todas
                $this->permissoesSelecionadas = $todasPermissoes;
            }
            
            \Log::info('Toggle todas permissões', [
                'total_selecionadas' => count($this->permissoesSelecionadas)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao alternar todas permissões', [
                'erro' => $e->getMessage()
            ]);
            
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao alternar permissões: ' . $e->getMessage()
            ]);
        }
    }

    public function salvarPermissoes()
    {
        try {
            if (!$this->perfilSelecionado) {
                throw new \Exception('Nenhum perfil selecionado.');
            }

            \Log::info('Iniciando salvamento de permissões', [
                'perfil_id' => $this->perfilSelecionado,
                'permissoes' => $this->permissoesSelecionadas
            ]);

            DB::beginTransaction();

            // Remove todas as permissões atuais do perfil
            DB::table('profile_permissions')
                ->where('profile_id', $this->perfilSelecionado)
                ->delete();

            // Prepara o array de dados para inserção
            $dados = [];
            $agora = now();
            
            foreach ($this->permissoesSelecionadas as $permissaoId) {
                if (!is_numeric($permissaoId)) continue;
                
                $dados[] = [
                    'profile_id' => $this->perfilSelecionado,
                    'permission_id' => $permissaoId,
                    'created_at' => $agora,
                    'updated_at' => $agora
                ];
            }

            // Insere todas as permissões de uma vez
            if (!empty($dados)) {
                DB::table('profile_permissions')->insert($dados);
            }

            DB::commit();

            \Log::info('Permissões salvas com sucesso', [
                'total_salvas' => count($dados)
            ]);

            // Recarrega os dados
            $this->carregarPerfis();
            $this->carregarDados();

            $this->dispatch('mostrarMensagem', [
                'tipo' => 'success',
                'mensagem' => 'Permissões atualizadas com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao salvar permissões', [
                'erro' => $e->getMessage(),
                'perfil_id' => $this->perfilSelecionado,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao salvar permissões: ' . $e->getMessage()
            ]);
        }
    }

    // Método removido: permissões individuais não são utilizadas pelo sistema
    // Use apenas perfis (profile_permissions) para controle de acesso

    // Método removido: funcionalidade de permissões individuais não utilizada

    public function abrirModalPerfis()
    {
        $this->showModalPerfis = true;
        $this->carregarPerfis();
        $this->dispatch('abrirModalPerfis');
    }

    public function fecharModalPerfis()
    {
        $this->showModalPerfis = false;
        $this->perfilSelecionadoModal = null;
        $this->permissoesPerfil = [];
        $this->dispatch('fecharModalPerfis');
    }

    public function selecionarPerfilModal($perfilId)
    {
        try {
            $this->perfilSelecionadoModal = $perfilId;
            $perfil = Profile::find($perfilId);
            
            if (!$perfil) {
                throw new \Exception('Perfil não encontrado');
            }
            
            $this->permissoesSelecionadasModal = $perfil->permissions()
                ->pluck('permissions.id')
                ->toArray();
                
            \Log::info('Perfil selecionado com sucesso', [
                'perfil_id' => $perfilId,
                'total_permissoes' => count($this->permissoesSelecionadasModal)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao selecionar perfil', [
                'perfil_id' => $perfilId,
                'erro' => $e->getMessage()
            ]);
            
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao carregar perfil: ' . $e->getMessage()
            ]);
        }
    }

    public function abrirModalNovoPerfil()
    {
        $this->showModalNovoPerfil = true;
        $this->resetValidation();
        $this->novoPerfil = [
            'name' => '',
            'description' => ''
        ];
        $this->dispatch('abrirModalNovoPerfil');
    }

    public function fecharModalNovoPerfil()
    {
        $this->showModalNovoPerfil = false;
        $this->dispatch('fecharModalNovoPerfil');
    }

    public function criarPerfil()
    {
        $this->validate([
            'novoPerfil.name' => 'required|min:3|unique:profiles,name'
        ]);

        try {
            DB::beginTransaction();

            $perfil = Profile::create([
                'name' => $this->novoPerfil['name'],
                'description' => $this->novoPerfil['description']
            ]);

            DB::commit();

            $this->fecharModalNovoPerfil();
            $this->carregarPerfis();
            
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'success',
                'mensagem' => 'Perfil criado com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao criar perfil: ' . $e->getMessage()
            ]);
        }
    }

    public function togglePermissao($permissaoId)
    {
        try {
            if (!is_numeric($permissaoId)) {
                throw new \Exception('ID de permissão inválido');
            }

            if (!is_array($this->permissoesSelecionadasModal)) {
                $this->permissoesSelecionadasModal = [];
            }

            if (in_array($permissaoId, $this->permissoesSelecionadasModal)) {
                $this->permissoesSelecionadasModal = array_diff($this->permissoesSelecionadasModal, [$permissaoId]);
            } else {
                $this->permissoesSelecionadasModal[] = $permissaoId;
            }

            \Log::info('Permissão alterada', [
                'permissao_id' => $permissaoId,
                'total_selecionadas' => count($this->permissoesSelecionadasModal)
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao alterar permissão', [
                'permissao_id' => $permissaoId,
                'erro' => $e->getMessage()
            ]);
            
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao alterar permissão: ' . $e->getMessage()
            ]);
        }
    }

    public function mostrarTodosPerfis()
    {
        $this->dispatch('mostrarMensagem', [
            'tipo' => 'info',
            'mensagem' => 'Selecione um novo perfil para o usuário'
        ]);
    }

    public function salvarPerfilUsuario($perfilId)
    {
        try {
            DB::beginTransaction();
            
            $usuario = User::find($this->usuarioSelecionado);
            if (!$usuario) {
                throw new \Exception('Usuário não encontrado.');
            }

            // Atualiza o perfil do usuário
            $usuario->profile_id = $perfilId;
            $usuario->save();

            DB::commit();

            // Atualiza a lista de usuários
            $this->usuarios = User::with('profile')
                ->select('id', 'name', 'login', 'empresa', 'active', 'profile_id')
                ->orderBy('name')
                ->get();

            // Dispara mensagem de sucesso
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'success',
                'mensagem' => 'Perfil atualizado com sucesso!'
            ]);

            // Força atualização da página
            $this->dispatch('reloadPage');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrarMensagem', [
                'tipo' => 'error',
                'mensagem' => 'Erro ao atualizar perfil: ' . $e->getMessage()
            ]);
        }
    }

    public function atualizarPerfilUsuario($perfilId)
    {
        \Log::info('Atualizando perfil do usuário', [
            'userId' => $this->usuarioSelecionado,
            'perfilId' => $perfilId
        ]);
        
        try {
            if (!$this->usuarioSelecionado) {
                throw new \Exception('Nenhum usuário selecionado.');
            }

            // Verificar se o usuário existe
            $usuario = User::find($this->usuarioSelecionado);
            
            if (!$usuario) {
                throw new \Exception('Usuário não encontrado.');
            }
            
            // Verificar se o perfil existe
            $perfil = Profile::find($perfilId);
            
            if (!$perfil) {
                throw new \Exception('Perfil não encontrado.');
            }
            
            // Atualizar usando Eloquent para garantir que os eventos do modelo sejam disparados
            $usuario->profile_id = $perfilId;
            $resultado = $usuario->save();
            
            if (!$resultado) {
                throw new \Exception('Falha ao salvar alterações no banco de dados.');
            }
            
            // Atualizar também com DB Query Builder para garantir que os dados sejam atualizados
            DB::table('users')
                ->where('id', $this->usuarioSelecionado)
                ->update(['profile_id' => $perfilId]);

            // Atualizar a lista de usuários
            $this->carregarPerfis();
            
            // Definir o perfil selecionado
            $this->perfilSelecionado = $perfilId;
            
            // Fornecer feedback ao usuário
            $this->dispatch('perfilAtualizado', [
                'userId' => $this->usuarioSelecionado,
                'perfilId' => $perfilId,
                'perfilNome' => $perfil->name
            ]);
            
            // Mostrar mensagem de sucesso usando SweetAlert
            $this->dispatch('mostrarAlerta', [
                'tipo' => 'success',
                'titulo' => 'Perfil Atualizado!',
                'mensagem' => "O perfil do usuário foi alterado para '{$perfil->name}' com sucesso!",
                'timer' => 3000
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar perfil', [
                'erro' => $e->getMessage(),
                'userId' => $this->usuarioSelecionado, 
                'perfilId' => $perfilId
            ]);
            
            // Mostrar mensagem de erro usando SweetAlert
            $this->dispatch('mostrarAlerta', [
                'tipo' => 'error',
                'titulo' => 'Erro!',
                'mensagem' => 'Erro ao atualizar perfil: ' . $e->getMessage()
            ]);
        }
    }

    // Responder ao evento de edição
    public function editarUsuarioEvento($data)
    {
        \Log::info('Evento editarUsuario recebido', $data);
        $this->editarUsuario($data['userId']);
        
        // Disparar evento para mostrar os dados no modal
        $this->dispatchBrowserEvent('usuarioDadosCarregados', [
            'usuarioEdit' => $this->usuarioEdit
        ]);
    }

    // Método para salvar os dados do usuário enviados pelo JavaScript
    public function salvarUsuario($dados)
    {
        \Log::info('Método salvarUsuario chamado', ['dados' => $dados]);
        
        try {
            // Atualização direta no banco usando DB::table
            $result = \DB::table('users')
                ->where('id', $dados['id'])
                ->update([
                    'name' => $dados['name'],
                    'empresa' => $dados['empresa']
                ]);
            
            // Se houver senha, atualizá-la separadamente
            if (!empty($dados['password'])) {
                \DB::table('users')
                    ->where('id', $dados['id'])
                    ->update([
                        'password' => Hash::make($dados['password'])
                    ]);
                
                \Log::info('Senha atualizada para o usuário', ['id' => $dados['id']]);
            }
            
            \Log::info('Resultado da atualização direta', [
                'success' => $result, 
                'id' => $dados['id'],
                'name' => $dados['name'],
                'empresa' => $dados['empresa']
            ]);
            
            // Emitir eventos ao navegador usando o método dispatch com for
            $this->dispatch('closeModal', ['modal' => 'modalEditarUsuario'])->self();
            $this->dispatch('mensagem', [
                'tipo' => 'Sucesso',
                'mensagem' => 'Usuário atualizado com sucesso!'
            ])->self();
            
            // Recarregar os dados
            $this->carregarPerfis();
            
        } catch (\Exception $e) {
            \Log::error('Exceção ao atualizar usuário', [
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'usuario_id' => $dados['id'] ?? 'não definido'
            ]);
            
            // Emitir evento de erro
            $this->dispatch('mensagem', [
                'tipo' => 'Erro',
                'mensagem' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ])->self();
        }
    }

    // Método para processar a seleção de usuário vinda do JavaScript
    #[On('usuario-selecionado')]
    public function usuarioSelecionadoJS($data = [])
    {
        \Log::info('Evento usuario-selecionado recebido do JS', is_array($data) ? $data : []);
        
        try {
            $userId = is_array($data) ? ($data['userId'] ?? null) : null;
            
            if (!$userId) {
                \Log::error('ID do usuário não fornecido no evento usuario-selecionado');
                return;
            }
            
            // Usar o método existente para selecionar o usuário
            $this->selecionarUsuario($userId);
        } catch (\Exception $e) {
            \Log::error('Erro ao processar evento usuario-selecionado', [
                'erro' => $e->getMessage(),
                'data' => is_array($data) ? $data : []
            ]);
        }
    }

    // Método para forçar a atualização do componente
    public function refresh()
    {
        $this->carregarPerfis();
        $this->carregarTodasPermissoes();
        $this->skipRender = false;
        $this->dispatch('refreshComponent');
    }

    public function render()
    {
        if (!$this->usuarios || !$this->perfis) {
            $this->carregarPerfis();
        }

        // Buscar usuários de acordo com o termo de busca
        $query = User::query();
        
        // Aplicar filtro de busca
        if (!empty($this->search)) {
            $termo = '%' . $this->search . '%';
            $query->where(function($q) use ($termo) {
                $q->where('name', 'like', $termo)
                  ->orWhere('login', 'like', $termo)
                  ->orWhere('empresa', 'like', $termo);
                
                // Buscar também pelo perfil
                $q->orWhereHas('profile', function($profileQuery) use ($termo) {
                    $profileQuery->where('name', 'like', $termo);
                });
            });
        }
        
        // Ordenar e buscar usuários com perfis
        $usuarios = $query->with('profile')->orderBy('name')->get();
        
        return view('livewire.gerenciar-permissoes', [
            'usuarios' => $usuarios,
            'perfis' => $this->perfis
        ]);
    }
    
    // Reset dos filtros
    public function resetFiltros()
    {
        $this->termoBusca = '';
        $this->filtroStatus = '';
    }
} 