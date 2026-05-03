@extends('adminlte::page')

@section('title', 'Gerenciar Permissões do Sistema')

@section('content_header')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-key text-primary mr-3"></i>
            Gerenciar Permissões
        </h1>
        <p class="text-muted mt-1 mb-0">Configure permissões e controle de acesso do sistema</p>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" id="btn-nova-permissao">
            <i class="fas fa-plus mr-1"></i>
            Nova Permissão
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
            <a class="nav-link" href="/perfis">
                <i class="fas fa-user-tag mr-1"></i> Perfis
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.permissoes') }}">
                <i class="fas fa-key mr-1"></i> Permissões
            </a>
        </li>
    </ul>

    <!-- Tabela de Permissões Moderna -->
    <div class="row">
        <div class="col-12">
            <div class="modern-card">
                <div class="card-header-modern">
                    <h5 class="card-title-modern">
                        <i class="fas fa-shield-alt text-primary mr-2"></i>
                        Permissões Disponíveis
                    </h5>
                </div>
                <div class="card-body-modern">
                    {{-- DEBUG: Total de permissões encontradas: {{ $permissoes->count() }} --}}
                    @if($permissoes->count() > 0)
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Código</th>
                                        <th>Descrição</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissoes as $permissao)
                                    <tr>
                                        <td>{{ $permissao->id }}</td>
                                        <td>
                                            <strong>{{ $permissao->name }}</strong>
                                        </td>
                                        <td>
                                            <code>{{ $permissao->code ?? 'N/A' }}</code>
                                        </td>
                                        <td>
                                            {{ $permissao->description ?? 'Sem descrição' }}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info btn-editar-permissao" 
                                                    data-id="{{ $permissao->id }}"
                                                    data-nome="{{ $permissao->name }}"
                                                    data-codigo="{{ $permissao->code }}"
                                                    data-descricao="{{ $permissao->description }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-excluir-permissao" 
                                                    data-id="{{ $permissao->id }}"
                                                    data-nome="{{ $permissao->name }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-5">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Nenhuma permissão encontrada</h4>
                            <p class="text-muted">Clique no botão "Nova Permissão" para começar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Nova Permissão -->
<div class="modal fade" id="modalNovaPermissao" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i> Nova Permissão
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="form-nova-permissao">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nome-permissao">Nome da Permissão</label>
                        <input type="text" class="form-control" id="nome-permissao" required>
                    </div>
                    <div class="form-group">
                        <label for="codigo-permissao">Código (opcional)</label>
                        <input type="text" class="form-control" id="codigo-permissao">
                    </div>
                    <div class="form-group">
                        <label for="descricao-permissao">Descrição (opcional)</label>
                        <textarea class="form-control" id="descricao-permissao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary">Criar Permissão</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Permissão -->
<div class="modal fade" id="modalEditarPermissao" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i> Editar Permissão
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="form-editar-permissao">
                <input type="hidden" id="editar-id-permissao">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editar-nome-permissao">Nome da Permissão</label>
                        <input type="text" class="form-control" id="editar-nome-permissao" required>
                    </div>
                    <div class="form-group">
                        <label for="editar-codigo-permissao">Código</label>
                        <input type="text" class="form-control" id="editar-codigo-permissao">
                    </div>
                    <div class="form-group">
                        <label for="editar-descricao-permissao">Descrição</label>
                        <textarea class="form-control" id="editar-descricao-permissao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-info">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
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
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.4rem;
}

/* ===============================================
   DARK MODE - Modo Escuro
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
    color: #cbd5e1 !important;
    border-bottom-color: #334155 !important;
}

html[data-theme="dark"] .modern-table tbody td strong {
    color: #f1f5f9 !important;
}

html[data-theme="dark"] .modern-table tbody td code {
    background-color: #334155 !important;
    color: #f472b6 !important;
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

/* Botões no dark mode */
html[data-theme="dark"] .btn-primary {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
}

html[data-theme="dark"] .btn-info {
    background-color: #0ea5e9 !important;
    border-color: #0ea5e9 !important;
}

html[data-theme="dark"] .btn-danger {
    background-color: #ef4444 !important;
    border-color: #ef4444 !important;
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

html[data-theme="dark"] .modal-body label {
    color: #cbd5e1 !important;
}

html[data-theme="dark"] .close {
    color: #f1f5f9 !important;
}
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Abrir modal de nova permissão
    $('#btn-nova-permissao').click(function() {
        $('#modalNovaPermissao').modal('show');
    });

    // Criar nova permissão
    $('#form-nova-permissao').submit(function(e) {
        e.preventDefault();
        
        const dados = {
            name: $('#nome-permissao').val(),
            code: $('#codigo-permissao').val(),
            description: $('#descricao-permissao').val()
        };

        $.post('/api/permissoes', dados)
            .done(function(response) {
                Swal.fire('Sucesso!', 'Permissão criada com sucesso!', 'success')
                    .then(() => location.reload());
            })
            .fail(function(xhr) {
                Swal.fire('Erro!', 'Erro ao criar permissão', 'error');
            });
    });

    // Editar permissão
    $('.btn-editar-permissao').click(function() {
        const id = $(this).data('id');
        const nome = $(this).data('nome');
        const codigo = $(this).data('codigo');
        const descricao = $(this).data('descricao');

        $('#editar-id-permissao').val(id);
        $('#editar-nome-permissao').val(nome);
        $('#editar-codigo-permissao').val(codigo);
        $('#editar-descricao-permissao').val(descricao);

        $('#modalEditarPermissao').modal('show');
    });

    // Salvar edição
    $('#form-editar-permissao').submit(function(e) {
        e.preventDefault();
        
        const id = $('#editar-id-permissao').val();
        const dados = {
            name: $('#editar-nome-permissao').val(),
            code: $('#editar-codigo-permissao').val(),
            description: $('#editar-descricao-permissao').val()
        };

        $.ajax({
            url: `/api/permissoes/${id}`,
            method: 'PUT',
            data: dados
        })
        .done(function(response) {
            Swal.fire('Sucesso!', 'Permissão atualizada com sucesso!', 'success')
                .then(() => location.reload());
        })
        .fail(function(xhr) {
            Swal.fire('Erro!', 'Erro ao atualizar permissão', 'error');
        });
    });

    // Excluir permissão
    $('.btn-excluir-permissao').click(function() {
        const id = $(this).data('id');
        const nome = $(this).data('nome');

        Swal.fire({
            title: 'Confirmar exclusão',
            text: `Deseja excluir a permissão "${nome}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/permissoes/${id}`,
                    method: 'DELETE'
                })
                .done(function(response) {
                    Swal.fire('Sucesso!', 'Permissão excluída com sucesso!', 'success')
                        .then(() => location.reload());
                })
                .fail(function(xhr) {
                    Swal.fire('Erro!', 'Erro ao excluir permissão', 'error');
                });
            }
        });
    });
});
</script>
@endpush

@push('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
@endpush 