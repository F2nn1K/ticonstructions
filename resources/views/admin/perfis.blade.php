@extends('adminlte::page')

@section('title', 'Gerenciar Perfis')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-user-tag text-primary mr-3"></i>
            Gerenciar Perfis
        </h1>
        <p class="text-muted mt-1 mb-0">Configure perfis de usuários e suas permissões</p>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" id="btn-novo-perfil">
            <i class="fas fa-plus mr-1"></i>
            Novo Perfil
        </button>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Menu de navegação simples -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.usuarios') }}">
                <i class="fas fa-users mr-1"></i> Usuários
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="/perfis">
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
        <!-- Coluna da lista de perfis -->
        <div class="col-md-4">
            <div class="modern-card">
                <div class="card-header-modern">
                    <h5 class="card-title-modern">
                        <i class="fas fa-list text-primary mr-2"></i>
                        Perfis Disponíveis ({{ $perfis->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($perfis->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($perfis as $perfil)
                        <li class="list-group-item">
                            <a href="{{ route('perfis.show', ['id' => $perfil->id]) }}" class="d-flex justify-content-between align-items-center">
                                <span>{{ $perfil->name }}</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-center p-4">
                        <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum perfil encontrado no banco de dados.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Coluna de detalhes do perfil -->
        <div class="col-md-8">
            <div class="modern-card">
                <div class="card-header-modern">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title-modern">
                            <i class="fas fa-edit text-primary mr-2"></i>
                            Detalhes do Perfil
                        </h5>
                        @if(isset($perfilSelecionado))
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmarExclusao({{ $perfilSelecionado->id }}, '{{ $perfilSelecionado->name }}')">
                            <i class="fas fa-trash"></i> Excluir Perfil
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body-modern">
                    @if(isset($perfilSelecionado))
                    <form action="/perfis/{{ $perfilSelecionado->id }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="nome" class="font-weight-bold">Nome do Perfil</label>
                            <input type="text" class="form-control" id="nome" name="name" value="{{ $perfilSelecionado->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao" class="font-weight-bold">Descrição</label>
                            <textarea class="form-control" id="descricao" name="description" rows="2">{{ $perfilSelecionado->description }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="mb-0 font-weight-bold">Permissões</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="selecionar-todas" onclick="selecionarTodas()">
                                    <label class="custom-control-label" for="selecionar-todas">Selecionar todas</label>
                                </div>
                            </div>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @foreach($permissoes as $permissao)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input permissao-checkbox" 
                                        id="permissao_{{ $permissao->id }}" 
                                        name="permissions[]"
                                        value="{{ $permissao->id }}"
                                        {{ in_array($permissao->id, $permissoesSelecionadas ?? []) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="permissao_{{ $permissao->id }}">
                                        {{ $permissao->name }}
                                    </label>
                                    @if($permissao->description)
                                    <small class="text-muted d-block">{{ $permissao->description }}</small>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="text-center p-4">
                        <i class="fas fa-arrow-left fa-3x text-muted mb-3"></i>
                        <p class="lead text-muted">Selecione um perfil ao lado para gerenciar suas permissões.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Novo Perfil -->
<div class="modal fade" id="modalNovoPerfil" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i> Criar Novo Perfil
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/perfis" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nome" class="font-weight-bold">Nome do Perfil</label>
                        <input type="text" class="form-control" id="nome" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="descricao" class="font-weight-bold">Descrição</label>
                        <textarea class="form-control" id="descricao" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check mr-1"></i> Criar Perfil
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
                    <i class="fas fa-exclamation-circle mr-1"></i> Esta ação não poderá ser desfeita!
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="form-excluir-perfil" action="" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i> Excluir Definitivamente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        // Evento para abrir modal de novo perfil
        $('#btn-novo-perfil').on('click', function() {
            $('#modalNovoPerfil').modal('show');
        });
    });

    // Função para selecionar todas as permissões
    function selecionarTodas() {
        const selecionarTodas = document.getElementById('selecionar-todas');
        const checkboxes = document.querySelectorAll('.permissao-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selecionarTodas.checked;
        });
    }

    // Função para confirmar exclusão
    function confirmarExclusao(id, nome) {
        document.getElementById('perfil-excluir-nome').textContent = nome;
        document.getElementById('form-excluir-perfil').action = `/perfis/${id}`;
        $('#modalConfirmacaoExclusao').modal('show');
    }
</script>
@endpush

@push('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
    /* Estilo específico para lista de perfis */
    .list-group-item:hover {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.02) 0%, rgba(147, 197, 253, 0.02) 100%);
        transform: translateX(2px);
        transition: all 0.2s ease;
    }
    
    .list-group-item.active {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-color: #3b82f6;
    }
    
    /* Estilo para área de permissões */
    .custom-control-label {
        font-weight: 500;
        color: #334155;
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
    
    .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #10b981;
        border-color: #10b981;
    }
</style>
@endpush 