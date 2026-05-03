@extends('adminlte::page')

@section('title', 'Gerenciar Usuários')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-users text-primary mr-3"></i>
            Gerenciar Usuários
        </h1>
        <p class="text-muted mt-1 mb-0">Administre usuários do sistema de forma centralizada</p>
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-start">
                <a href="{{ route('admin.perfis') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar para Perfis
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="modern-card">
                <div class="card-header-modern">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title-modern">
                            <i class="fas fa-users text-primary mr-2"></i>
                            Usuários do Sistema
                        </h5>
                        <div class="input-group search-container" style="width: 300px;">
                            <input type="text" class="form-control form-control-sm" 
                                   id="buscar-usuario"
                                   placeholder="Buscar usuários...">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body-modern">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 150px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="lista-usuarios">
                            @foreach($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>{{ $usuario->profile ? $usuario->profile->name : 'Sem perfil' }}</td>
                                <td>
                                    @if($usuario->active)
                                        <span class="badge badge-success">{{ __('Ativo') }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ __('Inativo') }}</span>
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
                        <label for="nova-empresa" class="font-weight-bold">Empresa</label>
                        <input type="text" class="form-control" id="nova-empresa" required>
                    </div>
                    <div class="form-group">
                        <label for="nova-senha" class="font-weight-bold">Senha</label>
                        <input type="password" class="form-control" id="nova-senha" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="novo-perfil" class="font-weight-bold">Perfil</label>
                        <select class="form-control" id="novo-perfil">
                            <option value="">Sem perfil</option>
                            @foreach(\App\Models\Profile::all() as $perfil)
                                <option value="{{ $perfil->id }}">{{ $perfil->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Criar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Usuário -->
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
                        <label for="usuario-email" class="font-weight-bold">Email</label>
                        <input type="email" class="form-control" id="usuario-email" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario-perfil" class="font-weight-bold">Perfil</label>
                        <select class="form-control" id="usuario-perfil">
                            <option value="">Sem perfil</option>
                            @foreach(\App\Models\Profile::all() as $perfil)
                                <option value="{{ $perfil->id }}">{{ $perfil->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    /* Destaque azul no topo */
    .header-highlight {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #4f46e5, #8b5cf6);
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.7);
        z-index: 100;
        margin-top: -1px;
    }
    
    .content-header {
        position: relative;
        padding-top: 1.5rem;
        box-shadow: 0 4px 12px -5px rgba(59, 130, 246, 0.15);
        margin-bottom: 1.5rem;
        background: linear-gradient(180deg, #f9fafb 0%, rgba(249, 250, 251, 0) 100%);
    }
    
    .section-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #edf2f7;
        padding: 1rem;
    }
    
    /* Cards modernos */
    .card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        animation: fadeIn 0.3s ease-out;
    }
    
    .card-header {
        border-radius: 12px 12px 0 0 !important;
    }
    
    .shadow-sm {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
    }
    
    /* Tabela moderna */
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        background-color: #f8fafc;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border-bottom: 2px solid #eaedf2;
        color: #5a6473;
    }
    
    .table td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f2f7;
        color: #4a5568;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.04);
        transform: translateY(-1px);
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
    
    .search-container .input-group-text {
        background: transparent;
        border: none;
        color: #888;
        position: absolute;
        right: 0;
        z-index: 4;
    }
    
    /* Animações */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('js')
<script>
async function fetchWithAuth(url, options = {}) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const defaultOptions = {
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        };

        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...(options.headers || {})
            }
        };

        const response = await fetch(url, mergedOptions);
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            throw new Error(errorData?.message || `HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        // Erro na requisição
        throw error;
    }
}

async function criarUsuario(event) {
    event.preventDefault();
    
    const nome = document.getElementById('novo-nome').value;
    const login = document.getElementById('novo-login').value;
    const email = document.getElementById('novo-email').value;
    const empresa = document.getElementById('nova-empresa').value;
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
        
        const response = await fetchWithAuth('/api/usuarios/criar', {
            method: 'POST',
            body: JSON.stringify({
                name: nome,
                login: login,
                email: email || null,
                empresa: empresa,
                password: senha,
                profile_id: perfilId || null
            })
        });
        
        if (response.success) {
            $('#modalNovoUsuario').modal('hide');
            
            // Limpar formulário
            document.getElementById('form-novo-usuario').reset();
            
            await Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Usuário criado com sucesso!',
                timer: 1500,
                showConfirmButton: false
            });
            
            window.location.reload();
        } else {
            throw new Error(response.message || 'Erro ao criar usuário');
        }
    } catch (error) {
        // Erro ao criar usuário
        
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: error.message || 'Erro ao criar usuário. Tente novamente.'
        });
    }
}

async function editarUsuario(id) {
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
        
        const response = await fetchWithAuth(`/api/usuarios/${id}`);
        
        if (response.success) {
            Swal.close();
            
            const usuario = response.data;
            document.getElementById('usuario-id').value = usuario.id;
            document.getElementById('usuario-nome').value = usuario.name;
            document.getElementById('usuario-email').value = usuario.email;
            document.getElementById('usuario-perfil').value = usuario.profile_id || '';
            
            $('#modalEditarUsuario').modal('show');
        } else {
            throw new Error(response.message || 'Erro ao obter dados do usuário');
        }
    } catch (error) {
        // Erro ao editar usuário
        
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: error.message || 'Erro ao obter dados do usuário. Tente novamente.'
        });
    }
}

async function salvarUsuario(event) {
    event.preventDefault();
    
    const id = document.getElementById('usuario-id').value;
    const nome = document.getElementById('usuario-nome').value;
    const email = document.getElementById('usuario-email').value;
    const perfilId = document.getElementById('usuario-perfil').value;
    
    try {
        // Mostrar loading
        Swal.fire({
            title: 'Salvando...',
            text: 'Atualizando dados do usuário',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const response = await fetchWithAuth(`/api/usuarios/${id}`, {
            method: 'PUT',
            body: JSON.stringify({
                name: nome,
                email: email,
                profile_id: perfilId || null
            })
        });
        
        if (response.success) {
            $('#modalEditarUsuario').modal('hide');
            
            await Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Usuário atualizado com sucesso!',
                timer: 1500,
                showConfirmButton: false
            });
            
            window.location.reload();
        } else {
            throw new Error(response.message || 'Erro ao atualizar usuário');
        }
    } catch (error) {
        // Erro ao salvar usuário
        
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: error.message || 'Erro ao atualizar usuário. Tente novamente.'
        });
    }
}

async function alterarStatus(id, ativar) {
    try {
        // Confirmação
        const result = await Swal.fire({
            icon: 'question',
            title: ativar ? 'Ativar Usuário?' : 'Desativar Usuário?',
            text: ativar 
                ? 'Deseja reativar este usuário? Ele poderá acessar o sistema novamente.' 
                : 'Deseja desativar este usuário? Ele não poderá mais acessar o sistema.',
            showCancelButton: true,
            confirmButtonText: ativar ? 'Sim, Ativar' : 'Sim, Desativar',
            cancelButtonText: 'Cancelar'
        });
        
        if (!result.isConfirmed) {
            return;
        }
        
        // Mostrar loading
        Swal.fire({
            title: ativar ? 'Ativando...' : 'Desativando...',
            text: 'Processando alteração',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const response = await fetchWithAuth('/toggle-user-status', {
            method: 'POST',
            body: JSON.stringify({
                user_id: id,
                active: ativar
            })
        });
        
        if (response.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: ativar 
                    ? 'Usuário ativado com sucesso!' 
                    : 'Usuário desativado com sucesso!',
                timer: 1500,
                showConfirmButton: false
            });
            
            window.location.reload();
        } else {
            throw new Error(response.message || 'Erro ao alterar status do usuário');
        }
    } catch (error) {
        // Erro ao alterar status
        
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: error.message || 'Erro ao alterar status do usuário. Tente novamente.'
        });
    }
}

// Implementar busca de usuários
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('buscar-usuario').addEventListener('input', function(e) {
        const termo = e.target.value.toLowerCase();
        const linhas = document.querySelectorAll('#lista-usuarios tr');
        
        linhas.forEach(linha => {
            const nome = linha.cells[0].textContent.toLowerCase();
            const email = linha.cells[1].textContent.toLowerCase();
            const perfil = linha.cells[2].textContent.toLowerCase();
            
            if (nome.includes(termo) || email.includes(termo) || perfil.includes(termo)) {
                linha.style.display = '';
            } else {
                linha.style.display = 'none';
            }
        });
    });
});
</script>
@endpush

@push('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
@endpush 