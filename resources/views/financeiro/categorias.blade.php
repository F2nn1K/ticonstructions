@extends('adminlte::page')

@section('title', 'Categorias')

@push('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .cor-preview {
        width: 25px;
        height: 25px;
        border-radius: 4px;
        display: inline-block;
        border: 1px solid #ddd;
    }
    .table thead th {
        color: white;
        border: none;
    }
    .thead-pagar th {
        background-color: #dc3545 !important;
    }
    .thead-receber th {
        background-color: #28a745 !important;
    }
    .badge-ativo {
        background-color: #28a745;
    }
    .badge-inativo {
        background-color: #dc3545;
    }
    .nav-tabs .nav-link.active {
        font-weight: bold;
    }
    .card-pagar {
        border-top: 3px solid #dc3545;
    }
    .card-receber {
        border-top: 3px solid #28a745;
    }
</style>
@endpush

@section('content_header')
<h1><i class="fas fa-tags mr-2"></i>Categorias</h1>
@stop

@section('content')
<div class="container-fluid">
    @php
        $categoriasPagar = $categorias->where('tipo', 'pagar');
        $categoriasReceber = $categorias->where('tipo', 'receber');
        // Se não tem tipo definido, considera como pagar (legado)
        if ($categoriasPagar->isEmpty() && $categoriasReceber->isEmpty()) {
            $categoriasPagar = $categorias;
        }
    @endphp

    <!-- Cards de Resumo -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $categoriasPagar->count() }}</h3>
                    <p>Categorias a Pagar</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $categoriasReceber->count() }}</h3>
                    <p>Categorias a Receber</p>
                </div>
                <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $categorias->count() }}</h3>
                    <p>Total</p>
                </div>
                <div class="icon"><i class="fas fa-tags"></i></div>
            </div>
        </div>
    </div>

    @if(!isset($tabelaExiste) || !$tabelaExiste)
    <div class="alert alert-info" id="alertaSQL" style="display: none;">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Tabela não encontrada!</strong> Execute o SQL abaixo para criar a tabela de categorias:
        <pre class="mt-2 mb-0" style="background: #fff; padding: 15px; border-radius: 5px; font-size: 12px;">
CREATE TABLE IF NOT EXISTS `categorias_contas` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `tipo` enum('pagar','receber') NOT NULL DEFAULT 'pagar' COMMENT 'Tipo: pagar ou receber',
    `nome` varchar(100) NOT NULL,
    `descricao` varchar(255) DEFAULT NULL,
    `cor` varchar(7) DEFAULT '#007bff',
    `ativo` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tipo` (`tipo`),
    KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Adicionar coluna categoria_id na tabela contas_pagar
ALTER TABLE `contas_pagar` ADD COLUMN IF NOT EXISTS `categoria_id` int(11) DEFAULT NULL AFTER `centro_custo_id`;

-- Adicionar coluna categoria_id na tabela contas_receber
ALTER TABLE `contas_receber` ADD COLUMN IF NOT EXISTS `categoria_id` int(11) DEFAULT NULL;
        </pre>
    </div>
    @endif

    <div class="row">
        <!-- Categorias a Pagar -->
        <div class="col-md-6">
            <div class="card card-pagar">
                <div class="card-header bg-white">
                    <h3 class="card-title text-danger">
                        <i class="fas fa-file-invoice mr-2"></i>Categorias - Contas a Pagar
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-danger btn-sm" onclick="abrirModalNova('pagar')">
                            <i class="fas fa-plus mr-1"></i> Nova
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped table-sm">
                        <thead class="thead-pagar">
                            <tr>
                                <th style="width: 40px">Cor</th>
                                <th>Nome</th>
                                <th style="width: 80px" class="text-center">Status</th>
                                <th style="width: 100px" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoriasPagar as $cat)
                            <tr>
                                <td>
                                    <span class="cor-preview" style="background-color: {{ $cat->cor ?? '#dc3545' }}"></span>
                                </td>
                                <td>
                                    <strong>{{ $cat->nome }}</strong>
                                    @if($cat->descricao)
                                    <br><small class="text-muted">{{ Str::limit($cat->descricao, 40) }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($cat->ativo)
                                        <span class="badge badge-ativo">Ativa</span>
                                    @else
                                        <span class="badge badge-inativo">Inativa</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info btn-xs" onclick="editarCategoria({{ $cat->id }})" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($isAdmin)
                                        <button type="button" class="btn btn-danger btn-xs" onclick="excluirCategoria({{ $cat->id }}, '{{ $cat->nome }}')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle mr-1"></i> Nenhuma categoria cadastrada
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Categorias a Receber -->
        <div class="col-md-6">
            <div class="card card-receber">
                <div class="card-header bg-white">
                    <h3 class="card-title text-success">
                        <i class="fas fa-hand-holding-usd mr-2"></i>Categorias - Contas a Receber
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="abrirModalNova('receber')">
                            <i class="fas fa-plus mr-1"></i> Nova
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped table-sm">
                        <thead class="thead-receber">
                            <tr>
                                <th style="width: 40px">Cor</th>
                                <th>Nome</th>
                                <th style="width: 80px" class="text-center">Status</th>
                                <th style="width: 100px" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoriasReceber as $cat)
                            <tr>
                                <td>
                                    <span class="cor-preview" style="background-color: {{ $cat->cor ?? '#28a745' }}"></span>
                                </td>
                                <td>
                                    <strong>{{ $cat->nome }}</strong>
                                    @if($cat->descricao)
                                    <br><small class="text-muted">{{ Str::limit($cat->descricao, 40) }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($cat->ativo)
                                        <span class="badge badge-ativo">Ativa</span>
                                    @else
                                        <span class="badge badge-inativo">Inativa</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info btn-xs" onclick="editarCategoria({{ $cat->id }})" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($isAdmin)
                                        <button type="button" class="btn btn-danger btn-xs" onclick="excluirCategoria({{ $cat->id }}, '{{ $cat->nome }}')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle mr-1"></i> Nenhuma categoria cadastrada
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova/Editar Categoria -->
<div class="modal fade" id="modalCategoria" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader">
                <h5 class="modal-title" id="modalCategoriaLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Nova Categoria
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCategoria">
                <input type="hidden" id="categoriaId" name="id">
                <input type="hidden" id="tipo" name="tipo" value="pagar">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nome">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2" maxlength="255"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cor">Cor</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="cor" name="cor" value="#007bff" style="height: 38px; padding: 2px;">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="corHex">#007bff</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ativo">Status</label>
                                <select class="form-control" id="ativo" name="ativo">
                                    <option value="1">Ativa</option>
                                    <option value="0">Inativa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn" id="btnSalvar">
                        <i class="fas fa-save mr-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

// Configurar CSRF token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(function() {
    // Atualizar hex da cor quando mudar
    $('#cor').on('input', function() {
        $('#corHex').text($(this).val());
    });
    
    // Submit do formulário
    $('#formCategoria').on('submit', function(e) {
        e.preventDefault();
        salvarCategoria();
    });
});

function abrirModalNova(tipo) {
    $('#categoriaId').val('');
    $('#formCategoria')[0].reset();
    $('#tipo').val(tipo);
    $('#ativo').val('1');
    
    if (tipo === 'pagar') {
        $('#cor').val('#dc3545');
        $('#corHex').text('#dc3545');
        $('#modalHeader').removeClass('bg-success').addClass('bg-danger text-white');
        $('#btnSalvar').removeClass('btn-success').addClass('btn-danger');
        $('#modalCategoriaLabel').html('<i class="fas fa-plus-circle mr-2"></i>Nova Categoria - Contas a Pagar');
    } else {
        $('#cor').val('#28a745');
        $('#corHex').text('#28a745');
        $('#modalHeader').removeClass('bg-danger').addClass('bg-success text-white');
        $('#btnSalvar').removeClass('btn-danger').addClass('btn-success');
        $('#modalCategoriaLabel').html('<i class="fas fa-plus-circle mr-2"></i>Nova Categoria - Contas a Receber');
    }
    
    $('#modalCategoria').modal('show');
}

function editarCategoria(id) {
    $.get('/financeiro/api/categorias/' + id, function(response) {
        if (response.success) {
            const cat = response.categoria;
            const tipo = cat.tipo || 'pagar';
            
            $('#categoriaId').val(cat.id);
            $('#tipo').val(tipo);
            $('#nome').val(cat.nome);
            $('#descricao').val(cat.descricao || '');
            $('#cor').val(cat.cor || '#007bff');
            $('#corHex').text(cat.cor || '#007bff');
            $('#ativo').val(cat.ativo);
            
            if (tipo === 'pagar') {
                $('#modalHeader').removeClass('bg-success').addClass('bg-danger text-white');
                $('#btnSalvar').removeClass('btn-success').addClass('btn-danger');
                $('#modalCategoriaLabel').html('<i class="fas fa-edit mr-2"></i>Editar Categoria - Contas a Pagar');
            } else {
                $('#modalHeader').removeClass('bg-danger').addClass('bg-success text-white');
                $('#btnSalvar').removeClass('btn-danger').addClass('btn-success');
                $('#modalCategoriaLabel').html('<i class="fas fa-edit mr-2"></i>Editar Categoria - Contas a Receber');
            }
            
            $('#modalCategoria').modal('show');
        } else {
            Swal.fire('Erro', response.message, 'error');
        }
    }).fail(function() {
        Swal.fire('Erro', 'Não foi possível carregar os dados da categoria.', 'error');
    });
}

function salvarCategoria() {
    const id = $('#categoriaId').val();
    const url = id ? '/financeiro/api/categorias/' + id : '/financeiro/api/categorias';
    const method = id ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: {
            tipo: $('#tipo').val(),
            nome: $('#nome').val(),
            descricao: $('#descricao').val(),
            cor: $('#cor').val(),
            ativo: $('#ativo').val()
        },
        success: function(response) {
            if (response.success) {
                $('#modalCategoria').modal('hide');
                Swal.fire('Sucesso', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'Erro ao salvar categoria.';
            Swal.fire('Erro', msg, 'error');
        }
    });
}

function excluirCategoria(id, nome) {
    Swal.fire({
        title: 'Confirmar exclusão?',
        html: `Tem certeza que deseja excluir a categoria <strong>${nome}</strong>?<br><small class="text-muted">Esta ação não pode ser desfeita.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/financeiro/api/categorias/' + id,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Excluído!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Erro', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Erro ao excluir categoria.';
                    Swal.fire('Erro', msg, 'error');
                }
            });
        }
    });
}
</script>
@stop
