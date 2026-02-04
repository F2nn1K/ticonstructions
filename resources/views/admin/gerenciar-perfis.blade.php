@extends('adminlte::page')

@section('title', 'Gerenciar Usuários')

@section('plugins.Sweetalert2', true)

@section('content_header')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-users text-primary mr-3"></i>
            Gerenciar Usuários
        </h1>
        <p class="text-muted mt-1 mb-0">Gerencie usuários, perfis e permissões do sistema</p>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNovoUsuario">
            <i class="fas fa-user-plus mr-1"></i>
            Novo Usuário
        </button>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Menu de navegação simples -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.usuarios') }}">
                <i class="fas fa-users mr-1"></i> Usuários
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/perfis">
                <i class="fas fa-user-tag mr-1"></i> Perfis
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.permissoes') }}">
                <i class="fas fa-key mr-1"></i> Permissões
            </a>
        </li>
    </ul>

    <div class="row">
        <div class="col-12">
            <div class="modern-card">
                <div class="card-header-modern">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title-modern">
                            <i class="fas fa-table text-primary mr-2"></i>
                            Usuários do Sistema
                        </h5>
                        <div class="modern-search-container">
                            <div class="input-group">
                                <input type="text" class="form-control modern-search-input" 
                                        id="buscar-usuario"
                                        placeholder="Buscar usuários...">
                                <div class="input-group-append">
                                    <span class="input-group-text modern-search-icon">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body-modern">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 150px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->profile_name ?? 'Sem perfil' }}</td>
                                <td>
                                    @if($usuario->active)
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-danger">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info" 
                                                onclick="editarUsuario({{ $usuario->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-{{ $usuario->active ? 'warning' : 'success' }}" 
                                                onclick="alterarStatus({{ $usuario->id }}, {{ $usuario->active ? 'false' : 'true' }})">
                                            <i class="fas fa-{{ $usuario->active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Criar Novo Usuário -->
<div class="modal fade" id="modalNovoUsuario" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus mr-2"></i> Novo Usuário
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-novo-usuario" onsubmit="criarUsuario(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="novo-nome" class="font-weight-bold">Nome</label>
                        <input type="text" class="form-control" id="novo-nome" required>
                    </div>
                    <div class="form-group">
                        <label for="novo-login" class="font-weight-bold">Login</label>
                        <input type="text" class="form-control" id="novo-login" required>
                    </div>
                    <div class="form-group">
                        <label for="novo-email" class="font-weight-bold">Email (opcional)</label>
                        <input type="email" class="form-control" id="novo-email">
                    </div>
                    <div class="form-group">
                        <label for="nova-senha" class="font-weight-bold">Senha</label>
                        <input type="password" class="form-control" id="nova-senha" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="novo-perfil" class="font-weight-bold">Perfil</label>
                        <select class="form-control" id="novo-perfil">
                            <option value="">Sem perfil</option>
                            @foreach($perfis as $perfil)
                                <option value="{{ $perfil->id }}">{{ $perfil->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Criar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar usuário -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit mr-2"></i> Editar Usuário
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-editar-usuario" onsubmit="salvarUsuario(event)">
                <div class="modal-body">
                    <input type="hidden" id="usuario-id">
                    <div class="form-group">
                        <label for="usuario-nome" class="font-weight-bold">Nome</label>
                        <input type="text" class="form-control" id="usuario-nome" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario-senha" class="font-weight-bold">Nova Senha</label>
                        <input type="password" class="form-control" id="usuario-senha" placeholder="Deixe em branco para manter a senha atual">
                        <small class="text-muted">Preencha apenas se desejar alterar a senha atual</small>
                    </div>
                    <div class="form-group">
                        <label for="usuario-confirmar-senha" class="font-weight-bold">Confirmar Senha</label>
                        <input type="password" class="form-control" id="usuario-confirmar-senha" placeholder="Confirme a nova senha">
                    </div>
                    <div class="form-group">
                        <label for="usuario-perfil" class="font-weight-bold">Perfil</label>
                        <select class="form-control" id="usuario-perfil">
                            <option value="">Sem perfil</option>
                            @foreach($perfis as $perfil)
                                <option value="{{ $perfil->id }}">{{ $perfil->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalConfirmacaoExclusao" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o perfil <strong id="perfil-excluir-nome"></strong>?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-circle mr-1"></i> Esta ação não poderá ser desfeita e todos os usuários 
                    associados a este perfil ficarão sem perfil definido.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-exclusao">
                    <i class="fas fa-trash mr-1"></i> Excluir Definitivamente
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
/* ===============================================
   DARK MODE - Modo Escuro para Gerenciar Usuários
   =============================================== */

/* Header e Content */
html[data-theme="dark"] .content-header {
    background: linear-gradient(180deg, #1e293b 0%, rgba(30, 41, 59, 0) 100%) !important;
}

html[data-theme="dark"] .content-header h1,
html[data-theme="dark"] .content-header .text-dark {
    color: #f1f5f9 !important;
}

html[data-theme="dark"] .content-header p,
html[data-theme="dark"] .content-header .text-muted {
    color: #94a3b8 !important;
}

/* Modern Card no dark mode */
html[data-theme="dark"] .modern-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}

html[data-theme="dark"] .card-header-modern {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
    border-bottom-color: #475569 !important;
}

html[data-theme="dark"] .card-title-modern,
html[data-theme="dark"] .card-header-modern h5 {
    color: #f1f5f9 !important;
}

html[data-theme="dark"] .card-body-modern {
    background: #1e293b !important;
}

/* Tabela moderna no dark mode */
html[data-theme="dark"] .modern-table {
    background: #1e293b !important;
}

html[data-theme="dark"] .modern-table thead tr,
html[data-theme="dark"] .modern-table thead th {
    background-color: #0f172a !important;
    color: #94a3b8 !important;
    border-bottom-color: #334155 !important;
}

html[data-theme="dark"] .modern-table tbody tr {
    background-color: #1e293b !important;
    border-bottom-color: #334155 !important;
}

html[data-theme="dark"] .modern-table tbody tr:hover {
    background-color: #334155 !important;
}

html[data-theme="dark"] .modern-table tbody td {
    color: #f1f5f9 !important;
    border-bottom-color: #334155 !important;
}

/* Nav Tabs no dark mode */
html[data-theme="dark"] .nav-tabs {
    border-bottom-color: #334155 !important;
}

html[data-theme="dark"] .nav-tabs .nav-link {
    color: #94a3b8 !important;
    border-color: transparent !important;
}

html[data-theme="dark"] .nav-tabs .nav-link:hover {
    color: #f1f5f9 !important;
    border-color: #334155 !important;
}

html[data-theme="dark"] .nav-tabs .nav-link.active {
    color: #f1f5f9 !important;
    background-color: #1e293b !important;
    border-color: #334155 #334155 #1e293b !important;
}

/* Campo de busca no dark mode */
html[data-theme="dark"] .modern-search-input {
    background-color: #334155 !important;
    color: #f1f5f9 !important;
    border-color: #475569 !important;
}

html[data-theme="dark"] .modern-search-input::placeholder {
    color: #94a3b8 !important;
}

html[data-theme="dark"] .modern-search-icon,
html[data-theme="dark"] .input-group-text {
    background-color: #334155 !important;
    color: #94a3b8 !important;
    border-color: #475569 !important;
}

/* Modal no dark mode */
html[data-theme="dark"] .modal-content {
    background-color: #1e293b !important;
    border-color: #334155 !important;
}

html[data-theme="dark"] .modal-header {
    border-bottom-color: #334155 !important;
}

html[data-theme="dark"] .modal-footer {
    border-top-color: #334155 !important;
}

html[data-theme="dark"] .modal-body {
    color: #cbd5e1 !important;
}

html[data-theme="dark"] .modal-body label {
    color: #cbd5e1 !important;
}

html[data-theme="dark"] .close {
    color: #f1f5f9 !important;
}

/* Botões coloridos mantidos */
html[data-theme="dark"] .btn-info {
    background-color: #0ea5e9 !important;
    border-color: #0ea5e9 !important;
}

html[data-theme="dark"] .btn-warning {
    background-color: #f59e0b !important;
    border-color: #f59e0b !important;
}

html[data-theme="dark"] .btn-success {
    background-color: #22c55e !important;
    border-color: #22c55e !important;
}
</style>
@endpush

@push('js')
<script>
    // Função básica para busca na tabela
    $(document).ready(function() {
        $("#buscar-usuario").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });

    // Função para criar novo usuário
    function criarUsuario(event) {
        event.preventDefault();
        
        const nome = document.getElementById('novo-nome').value;
        const login = document.getElementById('novo-login').value;
        const email = document.getElementById('novo-email').value;
        const senha = document.getElementById('nova-senha').value;
        const perfilId = document.getElementById('novo-perfil').value;
        
        try {
            // Mostrar loading
            Swal.fire({
                title: 'Criando...',
                text: 'Criando novo usuário',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('/api/usuarios/criar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: nome,
                    login: login,
                    email: email || null,
                    password: senha,
                    profile_id: perfilId || null
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#modalNovoUsuario').modal('hide');
                        
                        // Limpar formulário
                        document.getElementById('form-novo-usuario').reset();
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: 'Usuário criado com sucesso!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Erro ao criar usuário');
                    }
                })
                .catch(error => {
                    // Erro ao criar usuário
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: error.message || 'Erro ao criar usuário. Tente novamente.'
                    });
                });
        } catch (error) {
            // Erro ao criar usuário
            
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao processar a solicitação. Tente novamente.'
            });
        }
    }

    // Funções para editar e alterar status
    function editarUsuario(id) {
        try {
            // Mostrar loading
            Swal.fire({
                title: 'Carregando...',
                text: 'Obtendo dados do usuário',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/api/usuarios/${id}`)
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    
                    if (data.success) {
                        const usuario = data.data;
                        document.getElementById('usuario-id').value = usuario.id;
                        document.getElementById('usuario-nome').value = usuario.name;
                        document.getElementById('usuario-senha').value = '';
                        document.getElementById('usuario-confirmar-senha').value = '';
                        document.getElementById('usuario-perfil').value = usuario.profile_id || '';
                        
                        $('#modalEditarUsuario').modal('show');
                    } else {
                        throw new Error(data.message || 'Erro ao obter dados do usuário');
                    }
                })
                .catch(error => {
                    // Erro ao editar usuário
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: error.message || 'Erro ao obter dados do usuário. Tente novamente.'
                    });
                });
        } catch (error) {
            // Erro ao editar usuário
            
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao processar a solicitação. Tente novamente.'
            });
        }
    }
    
    function alterarStatus(id, status) {
        // Confirmação
        Swal.fire({
            icon: 'question',
            title: status ? 'Ativar Usuário?' : 'Desativar Usuário?',
            text: status 
                ? 'Deseja reativar este usuário? Ele poderá acessar o sistema novamente.' 
                : 'Deseja desativar este usuário? Ele não poderá mais acessar o sistema.',
            showCancelButton: true,
            confirmButtonText: status ? 'Sim, Ativar' : 'Sim, Desativar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
            
            // Mostrar loading
            Swal.fire({
                title: status ? 'Ativando...' : 'Desativando...',
                text: 'Processando alteração',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('/toggle-user-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    user_id: id,
                    active: status
                })
            })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: status 
                                ? 'Usuário ativado com sucesso!' 
                                : 'Usuário desativado com sucesso!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Erro ao alterar status do usuário');
                    }
                })
                .catch(error => {
                    // Erro ao alterar status
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: error.message || 'Erro ao alterar status do usuário. Tente novamente.'
                    });
                });
        });
    }

    function salvarUsuario(event) {
        event.preventDefault();
        
        try {
            const id = document.getElementById('usuario-id').value;
            const nome = document.getElementById('usuario-nome').value;
            const senha = document.getElementById('usuario-senha').value;
            const confirmarSenha = document.getElementById('usuario-confirmar-senha').value;
            const perfilId = document.getElementById('usuario-perfil').value;
            
            // Validar senha
            if (senha && senha !== confirmarSenha) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'As senhas não coincidem. Por favor, verifique.'
                });
                return;
            }
            
            // Mostrar loading
            Swal.fire({
                title: 'Salvando...',
                text: 'Atualizando dados do usuário',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Preparar dados para envio
            const dados = {
                name: nome,
                profile_id: perfilId
            };
            
            // Adicionar senha apenas se foi preenchida
            if (senha) {
                dados.password = senha;
                dados.password_confirmation = confirmarSenha;
            }
            
            fetch(`/api/usuarios/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(dados)
            })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: 'Usuário atualizado com sucesso!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        $('#modalEditarUsuario').modal('hide');
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Erro ao atualizar usuário');
                    }
                })
                .catch(error => {
                    // Erro ao salvar usuário
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: error.message || 'Erro ao atualizar usuário. Tente novamente.'
                    });
                });
        } catch (error) {
            // Erro ao salvar usuário
            
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao processar a solicitação. Tente novamente.'
            });
        }
    }
</script>
@endpush 