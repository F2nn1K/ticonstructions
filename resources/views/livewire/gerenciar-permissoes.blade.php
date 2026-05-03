<div>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('admin.perfis') }}" class="btn btn-outline-primary btn-sm mr-2">
                    <i class="fas fa-user-tag"></i> Gerenciar Perfis
                </a>
                <a href="{{ route('admin.permissoes') }}" class="btn btn-outline-primary btn-sm mr-2">
                    <i class="fas fa-key"></i> Gerenciar Permissões
                </a>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNovoUsuario">
                    <i class="fas fa-user-plus"></i> Novo Usuário
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Lista de Usuários --}}
        <div class="col-md-8">
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Erro!</h5>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-light section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Usuários do Sistema</h5>
                        <div class="input-group search-container" style="width: 300px;">
                            <input type="text" class="form-control form-control-sm" 
                                   wire:model.live.debounce.300ms="search" 
                                   id="searchInput"
                                   placeholder="Buscar usuários...">
                            @if(!empty($search))
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" 
                                            wire:click="$set('search', '')" 
                                            title="Limpar busca">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-body table-responsive p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Login</th>
                                    <th>Empresa</th>
                                    <th>Perfil</th>
                                    <th>Status</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuarios as $user)
                                    <tr data-user-id="{{ $user->id }}" class="{{ $user->active ? '' : 'table-danger' }}">
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->login }}</td>
                                        <td>{{ $user->empresa }}</td>
                                        <td>
                                            <span class="perfil-badge">
                                                <i class="fas fa-user-shield mr-1"></i>{{ $user->profile ? $user->profile->name : 'Sem perfil' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $user->active ? 'badge-success' : 'badge-danger' }}">
                                                {{ $user->active ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <!-- Botão de Editar -->
                                                <button class="btn btn-info btn-editar" 
                                                    data-toggle="modal" 
                                                    data-target="#modalEditarUsuario" 
                                                    data-id="{{ $user->id }}" 
                                                    onclick="carregarUsuarioEditar({{ $user->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <!-- Botão de Alternar Status -->
                                                <button class="btn {{ $user->active ? 'btn-warning' : 'btn-success' }} btn-status" 
                                                    onclick="confirmarToggleStatus({{ $user->id }}, '{{ $user->name }}', {{ $user->active ? 'true' : 'false' }})">
                                                    <i class="fas {{ $user->active ? 'fa-ban' : 'fa-check' }}"></i>
                                                </button>
                                                
                                                <!-- Botão de Gerenciar Perfil -->
                                                <button class="btn btn-primary btn-perfil" 
                                                    data-id="{{ $user->id }}" 
                                                    onclick="selecionarUsuarioJS({{ $user->id }}, '{{ $user->name }}')">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="alert alert-info mb-0">
                                                @if($search)
                                                    Nenhum usuário encontrado com o termo "{{ $search }}".
                                                @else
                                                    Nenhum usuário encontrado.
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Painel Lateral --}}
        <div class="col-md-4">
            <div class="card perfil-card">
                <div class="card-header section-header">
                    <h5 class="card-title">
                        <i class="fas fa-user-cog mr-2"></i> Alterar Perfil do Usuário
                    </h5>
                </div>
                <div class="card-body">
                    <div id="info-usuario-container">
                        <div class="text-center mb-4">
                            <div class="user-avatar">
                                <span class="avatar-text" id="user-initial">U</span>
                            </div>
                            <h4 id="user-name" class="mt-2 mb-1">Selecione um usuário</h4>
                            <p id="user-login" class="text-muted mb-1">---</p>
                            <span class="badge badge-primary mb-2" id="perfil-atual">---</span>
                            <input type="hidden" id="user-id-hidden" value="">
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="perfil-select-js" class="font-weight-bold">
                                <i class="fas fa-shield-alt mr-1"></i> Alterar Perfil do Usuário
                            </label>
                            <select id="perfil-select-js" class="form-control select-perfil" data-auto-load="true">
                                <option value="">Selecione um perfil...</option>
                                <!-- Será preenchido via JavaScript ao carregar a página -->
                            </select>
                            <p class="text-muted info-text mt-2">
                                <i class="fas fa-info-circle mr-1"></i> Selecione um novo perfil para atualizar as permissões do usuário instantaneamente.
                            </p>
                        </div>
                        
                        <!-- Botão Salvar Perfil -->
                        <div class="text-center">
                            <button id="btn-salvar-perfil" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i> Salvar Alteração de Perfil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Usuário -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarUsuarioLabel">Editar Usuário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditarUsuario">
                        <input type="hidden" id="usuario_id" name="usuario_id" value="">
                        <div class="form-group">
                            <label for="name">Nome</label>
                            <input type="text" class="form-control" id="name" name="name" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="login">Login</label>
                            <input type="text" class="form-control" id="login" name="login" readonly>
                        </div>
                        <div class="form-group">
                            <label for="empresa">Empresa</label>
                            <input type="text" class="form-control" id="empresa" name="empresa" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="password">Nova Senha (deixe em branco para manter a atual)</label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="button" class="btn btn-primary" onclick="salvarUsuarioSimples()">{{ __('Salvar') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para criar novo usuário -->
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1" role="dialog" aria-labelledby="modalNovoUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalNovoUsuarioLabel">
                        <i class="fas fa-user-plus mr-2"></i> Novo Usuário
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formNovoUsuario">
                        <div class="form-group">
                            <label for="new-name">Nome</label>
                            <input type="text" class="form-control" id="new-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="new-login">Login</label>
                            <input type="text" class="form-control" id="new-login" name="login" required>
                        </div>
                        <div class="form-group">
                            <label for="new-empresa">Empresa</label>
                            <input type="text" class="form-control" id="new-empresa" name="empresa" required>
                        </div>
                        <div class="form-group">
                            <label for="new-password">Senha</label>
                            <input type="password" class="form-control" id="new-password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="new-perfil">Perfil</label>
                            <select class="form-control" id="new-perfil" name="perfil_id">
                                <option value="">Selecione um perfil...</option>
                                <!-- Será preenchido via JavaScript -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="button" class="btn btn-primary" id="btnCriarUsuario">
                        <i class="fas fa-save mr-1"></i> Criar Usuário
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('css')
    <style>
        /* Estilos para tabelas */
        .table th {
            background-color: #f4f6f9;
            font-weight: 500;
            color: #333;
        }
        
        /* Campo de busca personalizado */
        .search-container {
            position: relative;
        }
        
        .search-container input {
            border-radius: 20px;
            padding-left: 30px;
            border: 1px solid #d0d0d0;
            transition: all 0.3s;
        }
        
        .search-container input:focus {
            border-color: #2a93d5;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
        }
        
        .search-container::before {
            content: "\f002";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 10px;
            top: 7px;
            color: #888;
            z-index: 10;
        }
        
        .search-container .input-group-append button {
            border-radius: 0 20px 20px 0;
            background-color: #f8f9fa;
            border-color: #d0d0d0;
        }
        
        /* Estilos para o badge de perfil */
        .perfil-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            background-color: #e9f5ff;
            color: #3490dc;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        /* Estilos para o avatar do usuário */
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto;
        }
        
        /* Estilo para o card do perfil */
        .perfil-card {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
            border: none;
            height: calc(100% - 1.5rem);
        }
        
        .perfil-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eaeaea;
        }
        
        /* Estilos para a mensagem de info */
        .info-text {
            font-size: 0.85rem;
            border-left: 3px solid #17a2b8;
            padding-left: 10px;
            background-color: #f8f9fa;
            padding: 8px 8px 8px 12px;
            border-radius: 0 4px 4px 0;
        }
        
        /* Estilo para o badge de perfil atual */
        #perfil-atual {
            font-size: 1rem;
            padding: 0.4rem 0.75rem;
            margin: 0.5rem 0;
            display: inline-block;
            background-color: #007bff;
            color: white;
            border-radius: 50px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Destacar o cabeçalho do card quando um usuário é selecionado */
        .perfil-card .card-header.user-selected {
            background-color: #e9f7fe;
            border-left: 3px solid #007bff;
        }
        
        /* Título com o nome do usuário mais destacado */
        .perfil-card .card-title strong {
            color: #007bff;
            font-weight: 700;
        }
        
        /* Destacar o nome do usuário na sidebar */
        #user-name {
            font-weight: 600;
            color: #2c3e50;
            transition: all 0.3s ease;
        }
        
        /* Quando um usuário é selecionado, o nome tem destaque diferente */
        .user-selected ~ .card-body #user-name {
            color: #007bff;
            font-size: 1.3rem;
        }
    </style>
    @endpush

    @push('js')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
            // Inicialização do carregamento de perfis
            setTimeout(function() {
                try {
                    carregarPerfis();
                    inicializarEventos();
                } catch (e) {
                    // Erro ao inicializar eventos
                }
            }, 1000);
            
            // Função simples para salvar usuário sem complicações
            window.salvarUsuarioSimples = function() {
                // Pegar valores do formulário
                var userId = $('#usuario_id').val();
                var name = $('#name').val();
                var empresa = $('#empresa').val();
                var password = $('#password').val();
                
                // Salvando usuário
                
                // Verificar campos obrigatórios
                if (!name || !empresa) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'Os campos Nome e Empresa são obrigatórios',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                // Verificar se o ID é válido
                if (!userId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'ID do usuário não encontrado',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            
                // Criar objeto de dados
                var dados = {
                    id: userId, // Incluir ID no payload também
                    name: name,
                    empresa: empresa
                };
                
                // Adicionar senha se foi preenchida
                if (password) {
                    if (password.length < 6) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            text: 'A senha deve ter pelo menos 6 caracteres',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    dados.password = password;
                }
                
                // Mostrar mensagem de carregamento
                Swal.fire({
                    title: 'Salvando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Obter CSRF token
                var token = $('meta[name="csrf-token"]').attr('content');
                // CSRF Token verificado
                
                // Enviar requisição usando jQuery
                $.ajax({
                    url: '/api/usuarios/' + userId,
                    type: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(dados),
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    success: function(response) {
                        // Resposta de sucesso
                        
                        // Fechar o modal
                        $('#modalEditarUsuario').modal('hide');
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        
                        // Mostrar mensagem de sucesso
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: response.message || 'Usuário atualizado com sucesso',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            didClose: () => {
                                // Recarregar a página após fechar o alerta
                                window.location.reload();
                            }
                        });
                        
                        // Recarregar a lista
                        Livewire.dispatch('refresh');
                    },
                    error: function(xhr, status, error) {
                        // Erro na atualização
                        
                        let mensagemErro = 'Erro ao atualizar usuário';
                        
                        try {
                            // Tentar extrair a mensagem de erro da resposta
                            const resposta = JSON.parse(xhr.responseText);
                            if (resposta && resposta.message) {
                                mensagemErro = resposta.message;
                            }
                        } catch (e) {
                            // Usar mensagem genérica se não conseguir parsear
                            mensagemErro += ': ' + (xhr.responseText || error);
                        }
                        
                        // Mostrar mensagem de erro
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: mensagemErro,
                            confirmButtonText: 'OK'
                        });
                    },
                    complete: function() {
                        Swal.close();
                    }
                });
            };
            
            // Inicializar botão salvar manualmente
            $(document).on('click', '#btnSalvarUsuario', function() {
                const userId = $('#usuario_id').val();
                salvarUsuarioEditado(userId);
            });
            
            function inicializarEventos() {
                // Evento para carregar usuário ao ser selecionado
                window.addEventListener('gerenciar-permissoes:carregarUsuario', function(event) {
                    const userId = event.detail?.userId;
                    
                    if (!userId) {
                        // ID de usuário não fornecido
                        return;
                    }
                    
                    carregarUsuario(userId);
                });
                
                // Botão salvar
                const btnSalvarUsuario = document.getElementById('btnSalvarUsuario');
                if (!btnSalvarUsuario) {
                    // Elemento btnSalvarUsuario não encontrado
                    return;
                }
                
                btnSalvarUsuario.addEventListener('click', salvarUsuario);
            }
            
            async function carregarUsuario(userId) {
                try {
                    const apiUrl = `/api/usuarios/${userId}`;
                    
                    let response = await fetch(apiUrl);
                    
                    if (!response.ok) {
                        // Tentando endpoint de debug como alternativa
                        const debugUrl = `/api/usuarios/debug/${userId}`;
                        
                        try {
                            const debugResponse = await fetch(debugUrl);
                            const text = await debugResponse.text();
                            
                            if (!debugResponse.ok) {
                                // Endpoint debug também falhou
                                throw new Error(text);
                            }
                            
                            let debugData;
                            try {
                                debugData = JSON.parse(text);
                            } catch (e) {
                                debugData = { mensagem: text };
                            }
                            
                            // Usar dados de debug
                            preencherFormulario(debugData);
                            return;
                        } catch (debugError) {
                            // Falha no endpoint de debug, continuar com abordagem normal
                        }
                        
                        // Status da resposta
                        const text = await response.text();
                        
                        if (text.includes('Unauthenticated')) {
                            // Resposta de erro de autenticação
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro de autenticação',
                                text: 'Sua sessão expirou. Faça login novamente.',
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                willClose: () => {
                                    window.location.reload();
                                }
                            });
                            return;
                        }
                        
                        throw new Error(text);
                    }
                    
                    const data = await response.json();
                    
                    // Preencher formulário com dados do usuário
            preencherFormulario(data);
                } catch (error) {
                    // Erro ao carregar usuário
                    
                    // Tentando carregar dados da tabela como último recurso
                    try {
                        const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                        if (userRow) {
                    const userData = {
                        id: userId,
                                nome: userRow.querySelector('td:nth-child(1)').textContent.trim(),
                                login: userRow.querySelector('td:nth-child(2)').textContent.trim(),
                                status: userRow.querySelector('.badge').textContent.includes('Ativo') ? 1 : 0,
                                perfil_id: userRow.querySelector('.badge-primary').dataset.perfilId || null,
                                perfil_nome: userRow.querySelector('.badge-primary').textContent.trim() || 'Sem perfil'
                            };
                            
                            // Dados carregados da tabela como fallback
                    preencherFormulario(userData);
                    return;
                }
            } catch (fallbackError) {
                        // Erro ao usar fallback
                    }
                    
            Swal.fire({
                icon: 'error',
                        title: 'Erro',
                        text: 'Não foi possível carregar os dados do usuário. Tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            }
            
            function preencherFormulario(data) {
                // Preencher campos do formulário
                document.getElementById('formUsuarioId').value = data.id || '';
                document.getElementById('formUsuarioNome').value = data.nome || '';
                document.getElementById('formUsuarioLogin').value = data.login || '';
                
                // Status do usuário (ativo/inativo)
                if (data.status !== undefined) {
                    document.getElementById('formUsuarioStatus').checked = data.status == 1;
                }
                
                // Selecionar perfil
                const selectPerfil = document.getElementById('formUsuarioPerfil');
                if (selectPerfil && data.perfil_id) {
                    Array.from(selectPerfil.options).forEach(option => {
                        option.selected = option.value == data.perfil_id;
                    });
                } else if (selectPerfil) {
                    selectPerfil.selectedIndex = 0; // Sem perfil
                }
                
                // Mostrar o modal
                const modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
                modal.show();
            }
            
            async function salvarUsuario() {
                try {
                    // Obter dados do formulário
                    const id = document.getElementById('formUsuarioId').value;
                    const nome = document.getElementById('formUsuarioNome').value;
                    const login = document.getElementById('formUsuarioLogin').value;
                    const status = document.getElementById('formUsuarioStatus').checked ? 1 : 0;
                    const perfilId = document.getElementById('formUsuarioPerfil').value;
                    
                    if (!nome || !login) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                            text: 'Preencha os campos obrigatórios',
                            confirmButtonText: 'OK'
            });
            return;
        }
        
        const dados = {
                        nome,
                        login,
                        status,
                        perfil_id: perfilId
                    };
                    
                    // Enviando dados para atualizar usuário
                    const apiUrl = `/api/usuarios/${id}`;
                    
                    const response = await fetch(apiUrl, {
                        method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(dados)
                    });
                    
            if (!response.ok) {
                        const text = await response.text();
                        throw new Error(text);
                    }
                    
                    const data = await response.json();
                    
                // Fechar modal
                    const modal = document.getElementById('modalEditarUsuario');
                    if (modal) {
                        try {
                $('#modalEditarUsuario').modal('hide');
                        } catch (modalError) {
                            // Erro ao fechar modal
                        }
                    }
                
                    // Atualizar tabela
                    Livewire.dispatch('usuarioAtualizado', { userId: id });
                    
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                        text: data.mensagem || 'Usuário atualizado com sucesso',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } catch (error) {
                    // Erro ao salvar usuário
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                        text: 'Erro ao salvar as alterações: ' + error.message,
                        confirmButtonText: 'OK'
                    });
                }
            }
            
            // Função para confirmar alteração de status
            window.confirmarToggleStatus = function(userId, userName, isActive) {
        Swal.fire({
                    title: 'Confirmar alteração',
                    text: `Deseja ${isActive ? 'desativar' : 'ativar'} o usuário "${userName}"?`,
                    icon: 'question',
            showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, alterar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                        toggleUserStatus(userId, isActive);
            }
        });
            };
    
    // Função para alternar o status do usuário
            async function toggleUserStatus(userId, currentStatus) {
                try {
                    // Preparar dados
                    const novoStatus = !currentStatus;
                    const dados = { active: novoStatus ? 1 : 0 };
                    
                    // Enviar requisição para atualizar status
                    const response = await fetch(`/api/usuarios/${userId}/status`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(dados)
                    });
            
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error('Erro ao alterar status do usuário');
                    }
                    
                    const data = await response.json();
                    
                    // Atualizar a tabela
                    Livewire.dispatch('refresh');
                    
                    // Mostrar mensagem de sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: data.message || 'Status atualizado com sucesso',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        didClose: () => {
                            // Recarregar a página após fechar o alerta
                            window.location.reload();
                        }
                    });
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao alterar status do usuário: ' + error.message,
                        confirmButtonText: 'OK'
                    });
                }
            }
            
            async function carregarPerfis() {
                // Carregando perfis
                try {
                    const response = await fetch('/api/perfis');
                    
                if (!response.ok) {
                        throw new Error('Erro na requisição');
                    }
                    
                    const data = await response.json();
                    
                    // Dados recebidos
                    const select = document.getElementById('formUsuarioPerfil');
                    
                    if (!select) {
                        return;
                    }
                    
                    // Limpar opções atuais
                    select.innerHTML = '<option value="">Selecione um perfil</option>';
                    
                    // Adicionar opções de perfil
                    if (Array.isArray(data.perfis)) {
                        data.perfis.forEach(perfil => {
                            const option = document.createElement('option');
                            option.value = perfil.id;
                            option.textContent = perfil.nome;
                            select.appendChild(option);
                        });
                } else {
                        // Resposta inválida da API
                        criarOpcoesFallback();
                    }
                } catch (error) {
                    // Erro ao carregar perfis
                    criarOpcoesFallback();
                }
            }
            
            function criarOpcoesFallback() {
                try {
                    const perfisBadges = document.querySelectorAll('.badge-primary[data-perfil-id]');
                    const perfisUnicos = new Map();
                    
                    perfisBadges.forEach(badge => {
                        const id = badge.dataset.perfilId;
                        const nome = badge.textContent.trim();
                        
                        if (id && nome && !perfisUnicos.has(id)) {
                            perfisUnicos.set(id, nome);
                        }
                    });
                    
                    const select = document.getElementById('formUsuarioPerfil');
                    if (select) {
                        select.innerHTML = '<option value="">Selecione um perfil</option>';
                        
                        perfisUnicos.forEach((nome, id) => {
                            const option = document.createElement('option');
                            option.value = id;
                            option.textContent = nome;
                            select.appendChild(option);
                        });
                    }
                } catch (e) {
                    // Erro ao criar opções de fallback
                }
            }
            
            // Função para selecionar usuário
            window.selecionarUsuario = function(userId, userName) {
                try {
                    // Atualizar título do modal
                    const modalTitle = document.getElementById('modalEditarUsuarioLabel');
                    if (modalTitle) {
                        modalTitle.textContent = 'Editar Usuário: ' + userName;
                    }
                    
                    // Atualizar breadcrumb
                    const breadcrumbUsuario = document.getElementById('breadcrumbUsuario');
                    if (breadcrumbUsuario) {
                        breadcrumbUsuario.textContent = userName;
                    }
                    
                    // Desmarcar linha selecionada anteriormente
                    document.querySelectorAll('tr.table-active').forEach(row => {
                        row.classList.remove('table-active');
                    });
                    
                    // Marcar nova linha selecionada
                    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                    if (row) {
                        row.classList.add('table-active');
                    }
                    
                    // Emitir evento para carregar usuário
                    window.dispatchEvent(new CustomEvent('gerenciar-permissoes:carregarUsuario', {
                        detail: {
                            userId
                        }
                    }));
                } catch (error) {
                    // Erro ao processar seleção de usuário
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao selecionar usuário',
                        confirmButtonText: 'OK'
                    });
                }
            };
            
            // Função para selecionar usuário via JS
            window.selecionarUsuarioJS = function(userId, userName) {
                try {
                    // Atualizar informações do usuário no painel lateral
                    const userNameElement = document.getElementById('user-name');
                    const userLoginElement = document.getElementById('user-login');
                    const avatarTextElement = document.getElementById('user-initial');
                    const userIdHidden = document.getElementById('user-id-hidden');
                    const perfilAtualElement = document.getElementById('perfil-atual');
                    
                    // Atualizar título da seção lateral com o nome do usuário
                    const cardHeaderElement = document.querySelector('.perfil-card .card-header');
                    if (cardHeaderElement) {
                        // Adicionar classe para destacar que um usuário foi selecionado
                        cardHeaderElement.classList.add('user-selected');
                        
                        const cardTitleElement = cardHeaderElement.querySelector('.card-title');
                        if (cardTitleElement) {
                            cardTitleElement.innerHTML = `<i class="fas fa-user-cog mr-2"></i> Alterar Perfil: <strong>${userName}</strong>`;
                        }
                    }
                    
                    // Atualizar nome do usuário
                    if (userNameElement) {
                        userNameElement.textContent = userName;
                    } else {
                        // Elemento user-name não encontrado
                    }
                    
                    // Obter o login do usuário da tabela
                    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                    let userLogin = '';
                    let perfilAtual = 'Sem perfil';
                    
                    if (row) {
                        // Obter login do usuário
                        userLogin = row.querySelector('td:nth-child(2)').textContent.trim();
                        if (userLoginElement) {
                            userLoginElement.textContent = userLogin;
                        }
                        
                        // Obter perfil atual do usuário direto da tabela
                        try {
                            const perfilCell = row.querySelector('td:nth-child(4)');
                            if (perfilCell) {
                                perfilAtual = perfilCell.textContent.trim();
                                
                                // Atualizar badge de perfil atual
                                if (perfilAtualElement) {
                                    perfilAtualElement.innerHTML = `<i class="fas fa-user-tag mr-1"></i> ${perfilAtual}`;
                                    perfilAtualElement.style.display = 'inline-block';
                                }
                            }
                        } catch (e) {
                            // Erro ao obter perfil da tabela
                        }
                    } else {
                        // Linha da tabela não encontrada para o usuário
                    }
                    
                    // Atualizar inicial do avatar
                    if (avatarTextElement && userName) {
                        avatarTextElement.textContent = userName.charAt(0).toUpperCase();
                    } else if (!avatarTextElement) {
                        // Elemento user-initial não encontrado
                    }
                    
                    // Guardar ID do usuário selecionado para uso posterior
                    if (userIdHidden) {
                        userIdHidden.value = userId;
                    } else {
                        // Elemento user-id-hidden não encontrado
                    }
                    
                    // Chamar o componente Livewire usando o método correto para Livewire v3
                    Livewire.dispatch('usuario-selecionado', {
                        userId: userId,
                        userName: userName
                    });
                    
                    // Carregar perfis para o usuário
                    setTimeout(() => {
                        carregarPerfisUsuario(userId);
                    }, 500);
                    
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao selecionar usuário: ' + error.message,
                        confirmButtonText: 'OK'
                    });
                }
            };
            
            // Função para carregar perfis disponíveis para um usuário
            async function carregarPerfisUsuario(userId) {
                try {
                    const selectPerfil = document.getElementById('perfil-select-js');
                    if (!selectPerfil) {
                        // console.error('Elemento perfil-select-js não encontrado');
                        return;
                    }
                    
                    // Mostrar loading no select
                    selectPerfil.disabled = true;
                    selectPerfil.innerHTML = '<option>Carregando perfis...</option>';
                    
                    // Buscar perfis na API
                    const response = await fetch('/api/perfis');
                    
                    if (!response.ok) {
                        const errorText = await response.text();
                        // console.error('Erro na resposta da API:', response.status, errorText);
                        throw new Error('Erro ao carregar perfis');
                    }
                    
                    const data = await response.json();
                    
                    // Preencher select com os perfis
                    selectPerfil.innerHTML = '<option value="">Selecione um perfil...</option>';
                    
                    if (data.perfis && Array.isArray(data.perfis)) {
                        // Buscar usuário selecionado (nome e outros dados)
                        const userNameElement = document.getElementById('user-name');
                        let userName = '';
                        
                        if (userNameElement) {
                            userName = userNameElement.textContent;
                            // Verificar se não é o texto padrão
                            if (userName === 'Selecione um usuário') {
                                // Buscar nome do usuário da tabela como fallback
                                const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                                if (userRow) {
                                    userName = userRow.querySelector('td:nth-child(1)').textContent.trim();
                                    // Atualizar o elemento com o nome correto
                                    userNameElement.textContent = userName;
                                }
                            }
                        }
                        
                        // Buscar perfil atual do usuário
                        const userResponse = await fetch(`/api/usuarios/${userId}`);
                        let perfilAtualId = null;
                        let perfilAtualNome = 'Sem perfil';
                        
                        if (userResponse.ok) {
                            const userData = await userResponse.json();
                            
                            // Buscar ID do perfil (diferentes formatos de resposta possíveis)
                            perfilAtualId = userData.perfil_id || userData.profile_id;
                            
                            // Encontrar o nome do perfil atual
                            if (perfilAtualId) {
                                const perfilEncontrado = data.perfis.find(p => p.id == perfilAtualId);
                                if (perfilEncontrado) {
                                    perfilAtualNome = perfilEncontrado.nome || perfilEncontrado.name;
                                }
                            }
                        } else {
                            // console.error('Erro ao buscar dados do usuário:', await userResponse.text());
                            
                            // Tentar obter perfil da tabela como fallback
                            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                            if (row) {
                                const perfilCell = row.querySelector('td:nth-child(4)');
                                if (perfilCell) {
                                    perfilAtualNome = perfilCell.textContent.trim();
                                }
                            }
                        }
                        
                        // Adicionar os perfis ao dropdown
                        data.perfis.forEach(perfil => {
                            const option = document.createElement('option');
                            option.value = perfil.id;
                            option.textContent = perfil.nome || perfil.name;
                            
                            // Selecionar automaticamente o perfil atual do usuário
                            if (perfilAtualId && perfil.id == perfilAtualId) {
                                option.selected = true;
                            }
                            
                            selectPerfil.appendChild(option);
                        });
                        
                        // Atualizar interface para mostrar o perfil atual
                        const perfilAtualElement = document.getElementById('perfil-atual');
                        if (perfilAtualElement) {
                            perfilAtualElement.innerHTML = `<i class="fas fa-user-tag mr-1"></i> ${perfilAtualNome}`;
                            perfilAtualElement.style.display = 'inline-block';
                        } else {
                            // console.error('Elemento perfil-atual não encontrado');
                        }
                        
                        // Atualizar também o título da seção com o nome do usuário
                        const userNameFromRow = document.querySelector(`tr[data-user-id="${userId}"]`)?.querySelector('td:nth-child(1)')?.textContent.trim();
                        const cardTitleElement = document.querySelector('.perfil-card .card-header .card-title');
                        
                        // Se o userNameElement for o valor padrão, usar o nome da linha da tabela
                        if (userName === 'Selecione um usuário' && userNameFromRow) {
                            const userNameElement = document.getElementById('user-name');
                            if (userNameElement) {
                                userNameElement.textContent = userNameFromRow;
                            }
                        }
                        
                        // Garantir que o título da seção seja atualizado com o nome correto do usuário
                        const finalUserName = userNameFromRow || (userName !== 'Selecione um usuário' ? userName : null);
                        
                        if (cardTitleElement && finalUserName) {
                            // Adicionar classe para destacar que um usuário foi selecionado
                            const cardHeaderElement = document.querySelector('.perfil-card .card-header');
                            if (cardHeaderElement) {
                                cardHeaderElement.classList.add('user-selected');
                            }
                            
                            cardTitleElement.innerHTML = `<i class="fas fa-user-cog mr-2"></i> Alterar Perfil: <strong>${finalUserName}</strong>`;
                        }
                    } else {
                        // console.error('Formato de dados inválido retornado pela API:', data);
                    }
                    
                    // Habilitar select
                    selectPerfil.disabled = false;
                    
                    // Configurar botão salvar
                    const btnSalvarPerfil = document.getElementById('btn-salvar-perfil');
                    if (btnSalvarPerfil) {
                        btnSalvarPerfil.onclick = function() {
                            salvarPerfil(userId);
                        };
                    } else {
                        // console.error('Botão btn-salvar-perfil não encontrado');
                    }
                    
                } catch (error) {
                    // console.error('Erro ao carregar perfis do usuário:', error);
                    
                    // Mostrar erro no select
                    const selectPerfil = document.getElementById('perfil-select-js');
                    if (selectPerfil) {
                        selectPerfil.innerHTML = '<option value="">Erro ao carregar perfis</option>';
                        selectPerfil.disabled = false;
                    }
                    
                    // Tentar obter perfil da tabela como último recurso
                    try {
                        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                        if (row) {
                            const perfilCell = row.querySelector('td:nth-child(4)');
                            if (perfilCell) {
                                const perfilAtual = perfilCell.textContent.trim();
                                const perfilAtualElement = document.getElementById('perfil-atual');
                                if (perfilAtualElement) {
                                    perfilAtualElement.textContent = perfilAtual || 'Sem perfil';
                                    perfilAtualElement.style.display = 'inline-block';
                                }
                            }
                        }
                    } catch (e) {
                        // console.error('Erro ao obter perfil da tabela como último recurso:', e);
                    }
                }
            }
            
            // Função para salvar alteração de perfil
            async function salvarPerfil(userId) {
                try {
                    const selectPerfil = document.getElementById('perfil-select-js');
                    if (!selectPerfil) {
                        // console.error('Elemento perfil-select-js não encontrado');
                        return;
                    }
                    
                    const perfilId = selectPerfil.value;
                    
                    if (!perfilId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            text: 'Selecione um perfil para continuar',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
        
                    // Botão de salvamento
                    const btnSalvar = document.getElementById('btn-salvar-perfil');
                    const textoOriginal = btnSalvar ? btnSalvar.innerHTML : '';
                    
                    // Mostrar loading no botão
                    if (btnSalvar) {
                        btnSalvar.disabled = true;
                        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';
                    }
                    
                    // Verificar token CSRF
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfToken) {
                        // console.error('Meta tag csrf-token não encontrada');
                    }
                    
                    // Preparar dados para envio
                    const dados = { perfil_id: perfilId };
                    
                    // Enviar requisição para atualizar perfil
                    const response = await fetch(`/api/usuarios/${userId}/perfil`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                        },
                        body: JSON.stringify(dados)
                    });
                    
                    // Restaurar botão
                    if (btnSalvar) {
                        btnSalvar.disabled = false;
                        btnSalvar.innerHTML = textoOriginal;
                    }
                    
                    const responseText = await response.text();
                    
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        // console.error('Erro ao fazer parse da resposta JSON:', parseError);
                        data = { success: false, mensagem: 'Erro ao processar resposta do servidor' };
                    }
                    
                    if (!response.ok) {
                        throw new Error(data.mensagem || `Erro ${response.status}: ${response.statusText}`);
                    }
                    
                    // Mostrar mensagem de sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: data.mensagem || 'Perfil atualizado com sucesso',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        didClose: () => {
                            // Recarregar a página após fechar o alerta
                            window.location.reload();
                        }
                    });
                    
                    // Atualizar a tabela
                    Livewire.dispatch('refresh');
                    
                } catch (error) {
                    // console.error('Erro ao salvar perfil:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao atualizar perfil: ' + error.message,
                        confirmButtonText: 'OK'
                    });
                }
            }
            
            // Função para atualizar perfil do usuário
            window.atualizarPerfilUsuario = async function(userId, perfilAtual) {
                try {
                    const select = document.getElementById('selecionarPerfil_' + userId);
                    if (!select) {
                        return;
                    }
                    
                    const novoPerfil = select.value;
                    
                    if (novoPerfil === perfilAtual) {
                        return; // Nenhuma alteração
                    }
                    
                    Swal.fire({
                        title: 'Confirmar alteração',
                        text: 'Deseja alterar o perfil deste usuário?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sim, alterar',
                        cancelButtonText: 'Cancelar'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const apiUrl = `/api/usuarios/${userId}/perfil`;
                            
                            const response = await fetch(apiUrl, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    perfil_id: novoPerfil
                                })
                            });
                            
                            if (!response.ok) {
                                throw new Error('Erro ao atualizar perfil');
                            }
                            
                            const data = await response.json();
                            
                            // Atualizar interface
                            Livewire.dispatch('usuarioAtualizado', { userId });
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso',
                                text: data.mensagem || 'Perfil atualizado com sucesso',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                                didClose: () => {
                                    // Recarregar a página após fechar o alerta
                                    window.location.reload();
                                }
                            });
                        } else {
                            // Reverter seleção
                            select.value = perfilAtual;
                        }
                    });
                } catch (error) {
                    // Erro ao atualizar perfil
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao atualizar perfil do usuário',
                        confirmButtonText: 'OK'
                    });
                }
            };
            
            // Função para criar novo usuário
            window.criarNovoUsuario = async function() {
                try {
                    const nome = document.getElementById('novoUsuarioNome').value;
                    const login = document.getElementById('novoUsuarioLogin').value;
                    const senha = document.getElementById('novoUsuarioSenha').value;
                    const confirmarSenha = document.getElementById('novoUsuarioConfirmarSenha').value;
                    const perfilId = document.getElementById('novoUsuarioPerfil').value;
                    
                    if (!nome || !login || !senha) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                            text: 'Preencha todos os campos obrigatórios',
                            confirmButtonText: 'OK'
            });
            return;
        }
        
                    if (senha !== confirmarSenha) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'As senhas não coincidem',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    
        const dados = {
                        nome,
                        login,
                        senha,
                        perfil_id: perfilId
                    };
                    
                    // Enviando dados para criar usuário
                    
                    const response = await fetch('/api/usuarios', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(dados)
                    });
                    
            if (!response.ok) {
                        const text = await response.text();
                        throw new Error(text);
                    }
                    
                    const data = await response.json();
                    
                // Fechar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoUsuario'));
                    modal.hide();
                
                // Limpar formulário
                document.getElementById('formNovoUsuario').reset();
                
                    // Atualizar tabela
                    Livewire.dispatch('atualizarTabela');
                    
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                        text: data.mensagem || 'Usuário criado com sucesso',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } catch (error) {
                    // Erro ao criar usuário
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao criar usuário: ' + error.message,
                        confirmButtonText: 'OK'
                    });
                }
            };
            
            // Função para carregar dados do usuário para edição
            window.carregarUsuarioEditar = async function(userId) {
                try {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Carregando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Buscar dados do usuário
                    const response = await fetch(`/api/usuarios/${userId}`);
                    if (!response.ok) {
                        throw new Error('Erro ao buscar dados do usuário');
                    }
                    
                    const userData = await response.json();
                    
                    // Preencher formulário
                    document.getElementById('usuario_id').value = userData.id;
                    document.getElementById('name').value = userData.name;
                    document.getElementById('login').value = userData.login;
                    document.getElementById('empresa').value = userData.empresa;
                    
                    // Limpar campo de senha
                    document.getElementById('password').value = '';
                    
                    Swal.close();
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao carregar usuário: ' + error.message
                    });
                }
            };
            
            // Função para salvar o usuário editado
            async function salvarUsuarioEditado(userId) {
                try {
                    // Garantir que temos um ID de usuário
                    if (!userId) {
                        userId = $('#usuario_id').val();
                        
                        if (!userId) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                                text: 'ID do usuário não encontrado',
                                confirmButtonText: 'OK'
                            });
                            return;
                        }
                    }
                    
                    // console.log('Salvando usuário ID:', userId);
                    
                    // Obter valores do formulário com jQuery para evitar problemas com elementos null
                    const name = $('#name').val();
                    const empresa = $('#empresa').val();
                    const password = $('#password').val();
                    
                    // Validar campos obrigatórios
                    if (!name || !name.trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            text: 'O nome é obrigatório',
                            confirmButtonText: 'OK'
                        });
                return;
            }
            
                    if (!empresa || !empresa.trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            text: 'A empresa é obrigatória',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    
                    // Preparar dados para envio
                    const dados = { 
                        name: name, 
                        empresa: empresa 
                    };
                    
                    // Adicionar senha apenas se foi preenchida
                    if (password && password.trim() !== '') {
                        dados.password = password;
                    }
                    
                    // console.log('Dados para atualização:', dados);
                    
                    // Mostrar loading
                    Swal.fire({
                        title: 'Salvando...',
                        text: 'Atualizando dados do usuário',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Obter CSRF token
                    const token = $('meta[name="csrf-token"]').attr('content');
                    
                    // Enviar requisição
                    try {
                        const response = await fetch(`/api/usuarios/${userId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify(dados)
                        });
                        
                        // Processar resposta
                        const responseText = await response.text();
                        let result;
                        
                        try {
                            result = responseText ? JSON.parse(responseText) : { success: response.ok };
                        } catch (e) {
                            // console.error('Erro ao fazer parse da resposta JSON:', e);
                            result = { success: response.ok };
                        }
                        
                        // Verificar resposta
                        if (!response.ok) {
                            throw new Error(result.message || responseText || 'Erro ao atualizar usuário');
                        }
                        
                        // Fechar o modal usando jQuery
                        $('#modalEditarUsuario').modal('hide');
                        
                        // Remover backdrop manualmente, se presente
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        
                        // Atualizar a tabela
                        Livewire.dispatch('refresh');
                        
                        // Mostrar mensagem de sucesso
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: result.message || 'Usuário atualizado com sucesso',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } catch (requestError) {
                        // console.error('Erro na requisição:', requestError);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Erro ao atualizar usuário: ' + requestError.message,
                            confirmButtonText: 'OK'
                        });
                    } finally {
                        // Garantir que o Swal de loading seja fechado
                        Swal.close();
                    }
                } catch (generalError) {
                    // console.error('Erro geral ao salvar usuário:', generalError);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao processar a solicitação: ' + generalError.message,
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    </script>
    @endpush
</div>