@extends('adminlte::page')

@section('title', __('app.menu.employees'))

@section('content_header')
    <h1><i class="fas fa-users mr-2"></i>{{ __('app.menu.employees') }}</h1>
@stop

@section('content')
<style>
    .card-funcionarios {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: none;
    }
    
    .card-funcionarios .card-header {
        background: linear-gradient(135deg, #3c8dbc 0%, #2a6a8a 100%);
        color: #fff;
        border-radius: 10px 10px 0 0;
        padding: 15px 20px;
    }
    
    .card-funcionarios .card-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }
    
    .filtros-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .table-funcionarios thead th {
        background: #e9ecef;
        color: #333;
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
    }
    
    .table-funcionarios tbody td {
        vertical-align: middle;
        font-size: 14px;
    }
    
    .badge-trabalhando {
        background: #28a745;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .badge-demitido {
        background: #dc3545;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .badge-afastado {
        background: #ffc107;
        color: #333;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .badge-ferias {
        background: #17a2b8;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .btn-novo-funcionario {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        border: none;
        padding: 10px 25px;
        font-weight: 600;
    }
    
    .btn-editar {
        background: #17a2b8;
        border: none;
        padding: 5px 12px;
        font-size: 12px;
    }
    
    .btn-excluir {
        background: #dc3545;
        border: none;
        padding: 5px 12px;
        font-size: 12px;
    }
    
</style>

<div class="card card-funcionarios">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-users mr-2"></i>Cadastro de Funcionários</h3>
        <button class="btn btn-novo-funcionario text-white" onclick="novoFuncionario()">
            <i class="fas fa-plus mr-1"></i> Novo Funcionário
        </button>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <div class="filtros-container">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Nome</label>
                    <input type="text" id="filtroNome" class="form-control" placeholder="Buscar por nome...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">CPF</label>
                    <input type="text" id="filtroCpf" class="form-control" placeholder="000.000.000-00">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="filtroStatus" class="form-control">
                        <option value="todos" selected>{{ __('Todos') }}</option>
                        <option value="trabalhando">Trabalhando</option>
                        <option value="demitido">Demitido</option>
                        <option value="afastado">Afastado</option>
                        <option value="ferias">Férias</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Função</label>
                    <input type="text" id="filtroFuncao" class="form-control" placeholder="Buscar...">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-primary btn-block" onclick="filtrar()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary btn-block" onclick="limparFiltros()">
                        <i class="fas fa-eraser"></i>
                    </button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-success btn-block" onclick="exportarExcel()" title="Exportar Excel">
                        <i class="fas fa-file-excel"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tabela -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-funcionarios">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Sexo</th>
                        <th>Função</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaFuncionarios">
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Carregando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Funcionário -->
<div class="modal fade" id="modalFuncionario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3c8dbc 0%, #2a6a8a 100%); color: #fff;">
                <h5 class="modal-title" id="modalTitulo"><i class="fas fa-user-plus mr-2"></i>Novo Funcionário</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formFuncionario" onsubmit="salvarFuncionario(event)">
                <div class="modal-body">
                    <input type="hidden" id="funcionarioId">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nome <span class="text-danger">*</span></label>
                                <input type="text" id="nome" class="form-control" required placeholder="Nome completo">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>CPF <span class="text-danger">*</span></label>
                                <input type="text" id="cpf" class="form-control" required placeholder="000.000.000-00" maxlength="14">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sexo</label>
                                <select id="sexo" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Feminino</option>
                                    <option value="O">Outro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Função</label>
                                <input type="text" id="funcao" class="form-control" placeholder="Ex: Técnico, Auxiliar...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Status') }}</label>
                                <select id="status" class="form-control">
                                    <option value="trabalhando">Trabalhando</option>
                                    <option value="demitido">Demitido</option>
                                    <option value="afastado">Afastado</option>
                                    <option value="ferias">Férias</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>{{ __('Observações') }}</label>
                        <textarea id="observacoes" class="form-control" rows="3" placeholder="Observações adicionais..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary">
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
$(document).ready(function() {
    carregarFuncionarios();
    
    // Máscara CPF
    $('#cpf').on('input', function() {
        let v = $(this).val().replace(/\D/g, '');
        if (v.length > 9) {
            v = v.substring(0,3) + '.' + v.substring(3,6) + '.' + v.substring(6,9) + '-' + v.substring(9,11);
        } else if (v.length > 6) {
            v = v.substring(0,3) + '.' + v.substring(3,6) + '.' + v.substring(6);
        } else if (v.length > 3) {
            v = v.substring(0,3) + '.' + v.substring(3);
        }
        $(this).val(v);
    });
    
    $('#filtroCpf').on('input', function() {
        let v = $(this).val().replace(/\D/g, '');
        if (v.length > 9) {
            v = v.substring(0,3) + '.' + v.substring(3,6) + '.' + v.substring(6,9) + '-' + v.substring(9,11);
        } else if (v.length > 6) {
            v = v.substring(0,3) + '.' + v.substring(3,6) + '.' + v.substring(6);
        } else if (v.length > 3) {
            v = v.substring(0,3) + '.' + v.substring(3);
        }
        $(this).val(v);
    });
});

function carregarFuncionarios() {
    const params = {
        nome: $('#filtroNome').val(),
        cpf: $('#filtroCpf').val(),
        status: $('#filtroStatus').val(),
        funcao: $('#filtroFuncao').val()
    };
    
    $.get('/api/funcionarios', params)
        .done(function(response) {
            if (response.success) {
                renderizarTabela(response.funcionarios);
            }
        })
        .fail(function() {
            $('#tabelaFuncionarios').html(`
                <tr>
                    <td colspan="6" class="text-center text-danger py-4">
                        Erro ao carregar funcionários
                    </td>
                </tr>
            `);
        });
}

function renderizarTabela(funcionarios) {
    if (!funcionarios || funcionarios.length === 0) {
        $('#tabelaFuncionarios').html(`
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    Nenhum funcionário encontrado
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    funcionarios.forEach(function(f) {
        const cpfFormatado = formatarCpf(f.cpf);
        const sexoTexto = f.sexo === 'M' ? 'Masc.' : (f.sexo === 'F' ? 'Fem.' : (f.sexo === 'O' ? 'Outro' : '-'));
        const statusBadge = getStatusBadge(f.status);
        
        html += `<tr>
            <td><strong>${f.nome}</strong></td>
            <td>${cpfFormatado}</td>
            <td>${sexoTexto}</td>
            <td>${f.funcao || '-'}</td>
            <td>${statusBadge}</td>
            <td>
                <button class="btn btn-editar text-white mr-1" onclick="editarFuncionario(${f.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-excluir text-white" onclick="excluirFuncionario(${f.id}, '${f.nome}')" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });
    
    $('#tabelaFuncionarios').html(html);
}

function getStatusBadge(status) {
    switch(status) {
        case 'trabalhando': return '<span class="badge-trabalhando">Trabalhando</span>';
        case 'demitido': return '<span class="badge-demitido">Demitido</span>';
        case 'afastado': return '<span class="badge-afastado">Afastado</span>';
        case 'ferias': return '<span class="badge-ferias">Férias</span>';
        default: return '<span class="badge-trabalhando">Trabalhando</span>';
    }
}

function filtrar() {
    carregarFuncionarios();
}

function limparFiltros() {
    $('#filtroNome').val('');
    $('#filtroCpf').val('');
    $('#filtroStatus').val('todos');
    $('#filtroFuncao').val('');
    carregarFuncionarios();
}

function novoFuncionario() {
    $('#funcionarioId').val('');
    $('#nome').val('');
    $('#cpf').val('');
    $('#sexo').val('');
    $('#funcao').val('');
    $('#status').val('trabalhando');
    $('#observacoes').val('');
    $('#modalTitulo').html('<i class="fas fa-user-plus mr-2"></i>Novo Funcionário');
    $('#modalFuncionario').modal('show');
}

function editarFuncionario(id) {
    $.get(`/api/funcionarios/${id}`)
        .done(function(f) {
            $('#funcionarioId').val(f.id);
            $('#nome').val(f.nome);
            $('#cpf').val(formatarCpf(f.cpf));
            $('#sexo').val(f.sexo || '');
            $('#funcao').val(f.funcao || '');
            $('#status').val(f.status || 'trabalhando');
            $('#observacoes').val(f.observacoes || '');
            $('#modalTitulo').html('<i class="fas fa-user-edit mr-2"></i>Editar Funcionário');
            $('#modalFuncionario').modal('show');
        });
}

function salvarFuncionario(e) {
    e.preventDefault();
    
    const id = $('#funcionarioId').val();
    const dados = {
        nome: $('#nome').val(),
        cpf: $('#cpf').val().replace(/\D/g, ''),
        sexo: $('#sexo').val() || null,
        funcao: $('#funcao').val() || null,
        status: $('#status').val(),
        observacoes: $('#observacoes').val() || null
    };
    
    const url = id ? `/api/funcionarios/${id}` : '/api/funcionarios';
    const method = id ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: dados,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function(response) {
        if (response.success) {
            $('#modalFuncionario').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
            });
            carregarFuncionarios();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: response.message || 'Erro ao salvar'
            });
        }
    })
    .fail(function(xhr) {
        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao salvar funcionário';
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: msg
        });
    });
}

function excluirFuncionario(id, nome) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: `Tem certeza que deseja excluir o funcionário "${nome}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/api/funcionarios/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Excluído!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    carregarFuncionarios();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao excluir'
                    });
                }
            })
            .fail(function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao excluir funcionário';
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: msg
                });
            });
        }
    });
}

function formatarCpf(cpf) {
    if (!cpf) return '-';
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length === 11) {
        return cpf.substring(0,3) + '.' + cpf.substring(3,6) + '.' + cpf.substring(6,9) + '-' + cpf.substring(9,11);
    }
    return cpf;
}

function exportarExcel() {
    // Exportação simples via CSV
    const params = new URLSearchParams({
        nome: $('#filtroNome').val(),
        cpf: $('#filtroCpf').val(),
        status: $('#filtroStatus').val(),
        funcao: $('#filtroFuncao').val()
    }).toString();
    
    $.get('/api/funcionarios?' + params)
        .done(function(response) {
            if (response.success && response.funcionarios.length > 0) {
                let csv = 'Nome;CPF;Sexo;Função;Status;Observações\n';
                response.funcionarios.forEach(function(f) {
                    const sexo = f.sexo === 'M' ? 'Masculino' : (f.sexo === 'F' ? 'Feminino' : 'Outro');
                    csv += `"${f.nome}";"${formatarCpf(f.cpf)}";"${sexo}";"${f.funcao || ''}";"${f.status}";"${f.observacoes || ''}"\n`;
                });
                
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'funcionarios.csv';
                link.click();
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Atenção',
                    text: 'Nenhum funcionário para exportar'
                });
            }
        });
}
</script>
@stop
