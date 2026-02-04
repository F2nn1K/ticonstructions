@extends('adminlte::page')

@section('title', 'Frota - Veículos')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-car text-primary mr-3"></i>
            Veículos
        </h1>
        <p class="text-muted mt-1 mb-0">Gerencie os veículos da frota</p>
    </div>
    <div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalCadastrarVeiculo">
            <i class="fas fa-plus mr-1"></i>
            Novo Veículo
        </button>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Alertas -->
    <div id="alertas-container"></div>

    <!-- Cards de estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-primary">
                <div class="card-body text-center">
                    <i class="fas fa-car fa-2x mb-2"></i>
                    <h3 id="totalVeiculos">0</h3>
                    <p class="mb-0">Total de Veículos</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3 id="veiculosAtivos">0</h3>
                    <p class="mb-0">Ativos</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-warning">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-2x mb-2"></i>
                    <h3 id="veiculosManutencao">0</h3>
                    <p class="mb-0">Em Manutenção</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-danger">
                <div class="card-body text-center">
                    <i class="fas fa-ban fa-2x mb-2"></i>
                    <h3 id="veiculosInativos">0</h3>
                    <p class="mb-0">Inativos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter mr-2"></i>
                        Filtros
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="filtroStatus">Status</label>
                            <select class="form-control" id="filtroStatus">
                                <option value="">Todos</option>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="manutencao">Em Manutenção</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtroTipo">Tipo</label>
                            <select class="form-control" id="filtroTipo">
                                <option value="">Todos</option>
                                <option value="carro">Carro</option>
                                <option value="moto">Moto</option>
                                <option value="caminhao">Caminhão</option>
                                <option value="van">Van</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroPlaca">Buscar por Placa</label>
                            <input type="text" class="form-control" id="filtroPlaca" placeholder="Ex: ABC-1234">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block" onclick="aplicarFiltros()">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Veículos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>
                Lista de Veículos
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tabelaVeiculos">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Marca/Modelo</th>
                            <th>Ano</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>KM Atual</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dados carregados via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cadastrar/Editar Veículo -->
<div class="modal fade" id="modalCadastrarVeiculo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-car mr-2"></i>
                    <span id="modalTitulo">Novo Veículo</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formVeiculo">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="placa">Placa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="placa" name="placa" 
                                       placeholder="ABC-1234" maxlength="8" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="renavam">RENAVAM</label>
                                <input type="text" class="form-control" id="renavam" name="renavam" 
                                       placeholder="12345678901" inputmode="numeric" pattern="\\d*" maxlength="11" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="marca">Marca</label>
                                <input type="text" class="form-control" id="marca" name="marca" 
                                       placeholder="Ex: Volkswagen">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modelo">Modelo</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" 
                                       placeholder="Ex: Gol">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ano">Ano</label>
                                <input type="number" class="form-control" id="ano" name="ano" 
                                       min="1990" max="2030" placeholder="2020">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo">Tipo <span class="text-danger">*</span></label>
                                <select class="form-control" id="tipo" name="tipo" required>
                                    <option value="">Selecione...</option>
                                    <option value="carro">Carro</option>
                                    <option value="moto">Moto</option>
                                    <option value="caminhao">Caminhão</option>
                                    <option value="van">Van</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                    <option value="manutencao">Em Manutenção</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="km_atual">KM Atual</label>
                                <input type="text" class="form-control" id="km_atual" name="km_atual" 
                                       inputmode="numeric" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="veiculo_id" name="id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.badge-status {
    font-size: 0.85em;
    padding: 0.4em 0.6em;
}

.btn-sm {
    margin: 0 2px;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Configurar CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    carregarVeiculos();

    // Máscara para placa (AAA-1234). Aceita digitação livre e normaliza para maiúsculas
    $('#placa').on('input', function() {
        let value = String($(this).val() || '').toUpperCase();
        value = value.replace(/[^A-Z0-9]/g, '');
        value = value.substring(0, 7); // limita a 7 sem o hífen
        if (value.length > 3) {
            value = value.substring(0, 3) + '-' + value.substring(3);
        }
        $(this).val(value);
    });

    // RENAVAM somente números (máx. 11)
    $('#renavam').on('input', function(){
        this.value = this.value.replace(/\D/g, '').substring(0, 11);
    });

    // Formatação de milhares para KM Atual (pt-BR)
    $('#km_atual').on('input', function() {
        this.value = formatKmBrFromString(this.value);
    });

    // Submissão do formulário
    $('#formVeiculo').submit(function(e) {
        e.preventDefault();
        salvarVeiculo();
    });

    // Limpar modal ao fechar
    $('#modalCadastrarVeiculo').on('hidden.bs.modal', function() {
        limparFormulario();
    });
});

// Converte string (com ou sem pontos) para formato com milhares pt-BR
function formatKmBrFromString(value) {
    const digitsOnly = String(value || '').replace(/\D/g, '');
    if (digitsOnly.length === 0) return '';
    return Number(digitsOnly).toLocaleString('pt-BR');
}

function carregarVeiculos() {
    const tbody = $('#tabelaVeiculos tbody');
    tbody.empty();
    $.get('/frota/api/veiculos')
        .done(function(veiculos){
            veiculos.forEach(veiculo => {
                const statusBadge = getStatusUsoExibicao(veiculo);
                const acoes = `
                    <button class=\"btn btn-sm btn-info\" onclick=\"editarVeiculo(${veiculo.id})\">\n                        <i class=\"fas fa-edit\"></i>\n                    </button>\n                    <button class=\"btn btn-sm btn-primary\" onclick=\"verVeiculo(${veiculo.id})\">\n                        <i class=\"fas fa-eye\"></i>\n                    </button>\n                    <button class=\"btn btn-sm btn-danger\" onclick=\"excluirVeiculo(${veiculo.id})\">\n                        <i class=\"fas fa-trash\"></i>\n                    </button>\n                `;

                tbody.append(`
                    <tr>
                        <td><strong>${veiculo.placa}</strong></td>
                        <td>${veiculo.marca || ''} ${veiculo.modelo || ''}</td>
                        <td>${veiculo.ano || ''}</td>
                        <td><span class=\"badge badge-secondary\">${veiculo.tipo || '-'}</span></td>
                        <td>${statusBadge}</td>
                        <td>${Number(veiculo.km_atual || 0).toLocaleString()} km</td>
                        <td class=\"text-center\">${acoes}</td>
                    </tr>
                `);
            });

            atualizarEstatisticas(veiculos);
        })
        .fail(function(){
            Swal.fire('Erro','Não foi possível carregar os veículos','error');
        });
}

// Exibe badge considerando status (ativo/inativo) e status_uso (livre/em_uso)
function getStatusUsoExibicao(v) {
    // Se o veículo estiver inativo no banco, mostrar INATIVO em vermelho
    if (String(v.status) === 'inativo') {
        return '<span class="badge badge-danger">Inativo</span>';
    }
    // Caso contrário, usa o status_uso
    switch(v.status_uso) {
        case 'em_uso':
            return '<span class="badge badge-warning">Em Uso</span>';
        case 'livre':
            return '<span class="badge badge-success">Livre</span>';
        default:
            return '<span class="badge badge-secondary">-</span>';
    }
}

function getStatusBadge(status) {
    const badges = {
        'ativo': '<span class="badge badge-success badge-status">Ativo</span>',
        'inativo': '<span class="badge badge-danger badge-status">Inativo</span>',
        'manutencao': '<span class="badge badge-warning badge-status">Em Manutenção</span>'
    };
    return badges[status] || '<span class="badge badge-secondary badge-status">-</span>';
}

function atualizarEstatisticas(veiculos = []) {
    const total = veiculos.length;
    const ativos = veiculos.filter(v => v.status === 'ativo').length;
    const manutencao = veiculos.filter(v => v.status === 'manutencao').length;
    const inativos = veiculos.filter(v => v.status === 'inativo').length;

    $('#totalVeiculos').text(total);
    $('#veiculosAtivos').text(ativos);
    $('#veiculosManutencao').text(manutencao);
    $('#veiculosInativos').text(inativos);
}

function salvarVeiculo() {
    const dados = {
        id: $('#veiculo_id').val(),
        placa: $('#placa').val(),
        renavam: $('#renavam').val(),
        marca: $('#marca').val(),
        modelo: $('#modelo').val(),
        ano: $('#ano').val(),
        tipo: $('#tipo').val(),
        status: $('#status').val(),
        km_atual: $('#km_atual').val().replace(/\D/g, '')
    };

    const metodo = dados.id ? 'PUT' : 'POST';
    const url = dados.id ? `/frota/api/veiculos/${dados.id}` : '/frota/api/veiculos';

    $.ajax({ url, method: metodo, data: dados })
        .done(function(){
            Swal.fire({ icon: 'success', title: 'Sucesso!', text: 'Veículo salvo com sucesso!', timer: 1500 });
            $('#modalCadastrarVeiculo').modal('hide');
            carregarVeiculos();
        })
        .fail(function(xhr){
            const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Erro ao salvar veículo';
            Swal.fire('Erro', msg, 'error');
        });
}

function editarVeiculo(id) {
    // Carregar dados reais via API e abrir modal de edição
    $.get(`/frota/api/veiculos/${id}`)
        .done(function(v){
            $('#modalTitulo').text('Editar Veículo');
            $('#veiculo_id').val(v.id);
            $('#placa').val(String(v.placa || ''));
            $('#renavam').val(String(v.renavam || ''));
            $('#marca').val(String(v.marca || ''));
            $('#modelo').val(String(v.modelo || ''));
            $('#ano').val(v.ano || '');
            $('#tipo').val(String(v.tipo || ''));
            $('#status').val(String(v.status || ''));
            $('#km_atual').val(formatKmBrFromString(v.km_atual));
            $('#modalCadastrarVeiculo').modal('show');
        })
        .fail(function(){
            Swal.fire('Erro','Não foi possível carregar os dados do veículo.','error');
        });
}

function verVeiculo(id) {
    // Buscar dados e abrir modal de visualização
    $.get(`/frota/api/veiculos/${id}`)
        .done(function(v){
            const html = `
                <div class=\"modal fade\" id=\"modalVerVeiculo\" tabindex=\"-1\" role=\"dialog\">\n
                  <div class=\"modal-dialog modal-lg\" role=\"document\">\n
                    <div class=\"modal-content\">\n
                      <div class=\"modal-header bg-info text-white\">\n
                        <h5 class=\"modal-title\"><i class=\"fas fa-eye mr-2\"></i>Detalhes do Veículo</h5>\n
                        <button type=\"button\" class=\"close text-white\" data-dismiss=\"modal\"><span>&times;</span></button>\n
                      </div>\n
                      <div class=\"modal-body\">\n
                        <div class=\"row\">\n
                          <div class=\"col-md-6\"><strong>Placa:</strong> ${v.placa || ''}</div>\n
                          <div class=\"col-md-6\"><strong>RENAVAM:</strong> ${v.renavam || ''}</div>\n
                        </div>\n
                        <div class=\"row mt-2\">\n
                          <div class=\"col-md-6\"><strong>Marca:</strong> ${v.marca || ''}</div>\n
                          <div class=\"col-md-6\"><strong>Modelo:</strong> ${v.modelo || ''}</div>\n
                        </div>\n
                        <div class=\"row mt-2\">\n
                          <div class=\"col-md-4\"><strong>Ano:</strong> ${v.ano || ''}</div>\n
                          <div class=\"col-md-4\"><strong>Tipo:</strong> ${v.tipo || ''}</div>\n
                          <div class=\"col-md-4\"><strong>Status:</strong> ${v.status || ''}</div>\n
                        </div>\n
                        <div class=\"row mt-2\">\n
                          <div class=\"col-md-6\"><strong>KM Atual:</strong> ${Number(v.km_atual || 0).toLocaleString('pt-BR')} km</div>\n
                          <div class=\"col-md-6\"><strong>Criado em:</strong> ${v.created_at || ''}</div>\n
                        </div>\n
                      </div>\n
                      <div class=\"modal-footer\">\n
                        <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Fechar</button>\n
                      </div>\n
                    </div>\n
                  </div>\n
                </div>`;

            // Remover modal antigo, se existir
            $('#modalVerVeiculo').remove();
            $('body').append(html);
            $('#modalVerVeiculo').modal('show');
        })
        .fail(function(){
            Swal.fire('Erro','Não foi possível carregar os dados do veículo.','error');
        });
}

function excluirVeiculo(id) {
    Swal.fire({
        title: 'Confirmar exclusão?',
        text: 'Esta ação não pode ser desfeita!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({ url: `/frota/api/veiculos/${id}`, method: 'DELETE' })
                .done(function(){
                    Swal.fire('Excluído!', 'Veículo removido com sucesso.', 'success');
                    carregarVeiculos();
                })
                .fail(function(){
                    Swal.fire('Erro', 'Não foi possível excluir o veículo.', 'error');
                });
        }
    });
}

function limparFormulario() {
    $('#modalTitulo').text('Novo Veículo');
    $('#formVeiculo')[0].reset();
    $('#veiculo_id').val('');
}

function aplicarFiltros() {
    // Implementar filtros aqui
    carregarVeiculos();
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@stop