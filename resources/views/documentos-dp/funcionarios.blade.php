@extends('adminlte::page')

@section('title', 'Funcionários - Documentos DP')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-users text-primary mr-3"></i>
            Funcionários
        </h1>
        <p class="text-muted mt-1 mb-0">Consulte e gerencie documentos de funcionários</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Card de Busca -->
    <div id="area_busca" class="mb-4">
        <div class="modern-card mb-4">
            <div class="card-header-modern">
                <h3 class="card-title-modern">
                    <i class="fas fa-search mr-2 text-primary"></i>
                    Pesquisar Funcionário
                </h3>
            </div>
            <div class="card-body-modern">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="modern-search-container">
                            <label for="busca_nome" class="font-weight-bold text-muted mb-2">
                                <i class="fas fa-user-search mr-1"></i>
                                Nome ou CPF do Funcionário
                            </label>
                            <input 
                                type="text" 
                                class="form-control modern-search-input" 
                                id="busca_nome" 
                                placeholder="Digite pelo menos 3 caracteres do nome ou CPF para buscar..."
                            >
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex align-items-end">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle mr-1"></i>
                            A busca será realizada automaticamente conforme você digita
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados da Busca -->
        <div id="resultado_busca" class="mb-4"></div>
    </div>

    <!-- Dados do Funcionário Selecionado -->
    <div id="dados_funcionario" class="d-none">
        <!-- Header com Voltar -->
        <div class="modern-card mb-4">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h3 class="card-title-modern mb-0">
                    <i class="fas fa-user-circle mr-2 text-primary"></i>
                    <span id="funcionario_nome_header">Funcionário</span>
                </h3>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-voltar-busca">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Voltar
                </button>
            </div>
        </div>

        <!-- Navegação por Abas -->
        <div class="modern-card mb-4">
            <div class="card-body-modern p-0">
                <ul class="nav nav-tabs" id="funcionario-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="dados-tab" data-toggle="tab" href="#dados-content" role="tab">
                            <i class="fas fa-user mr-2"></i>
                            Funcionário
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="atestados-tab" data-toggle="tab" href="#atestados-content" role="tab">
                            <i class="fas fa-file-medical mr-2"></i>
                            Atestados
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="advertencias-tab" data-toggle="tab" href="#advertencias-content" role="tab">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Advertências
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="epi-tab" data-toggle="tab" href="#epi-content" role="tab">
                            <i class="fas fa-hard-hat mr-2"></i>
                            EPI's
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="contra-cheques-tab" data-toggle="tab" href="#contra-cheques-content" role="tab">
                            <i class="fas fa-money-check mr-2"></i>
                            Contra cheques
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="ferias-tab" data-toggle="tab" href="#ferias-content" role="tab">
                            <i class="fas fa-umbrella-beach mr-2"></i>
                            Férias
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="decimo-tab" data-toggle="tab" href="#decimo-content" role="tab">
                            <i class="fas fa-gift mr-2"></i>
                            Décimos
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="rescisao-tab" data-toggle="tab" href="#rescisao-content" role="tab">
                            <i class="fas fa-handshake mr-2"></i>
                            Rescisões
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="frequencia-tab" data-toggle="tab" href="#frequencia-content" role="tab">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Frequências
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="certificado-tab" data-toggle="tab" href="#certificado-content" role="tab">
                            <i class="fas fa-certificate mr-2"></i>
                            Certificados
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="asos-tab" data-toggle="tab" href="#asos-content" role="tab">
                            <i class="fas fa-heartbeat mr-2"></i>
                            ASOS
                        </a>
                    </li>
                </ul>

                <div class="tab-content p-4" id="funcionario-tab-content">
                    <!-- Aba Funcionário -->
                    <div class="tab-pane fade show active" id="dados-content" role="tabpanel">
                        <!-- Informações Básicas -->
                        <div class="row mb-4">
                            <!-- Foto do Funcionário -->
                            <div class="col-lg-2 text-center">
                                <h5 class="mb-3">
                                    <i class="fas fa-camera mr-2 text-info"></i>
                                    Foto
                                </h5>
                            <div class="foto-funcionario-dp-box mb-3">
                                <img id="f_foto" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 120'%3E%3Crect fill='%23e2e8f0' width='100' height='120'/%3E%3Ccircle cx='50' cy='40' r='25' fill='%2394a3b8'/%3E%3Cellipse cx='50' cy='110' rx='40' ry='35' fill='%2394a3b8'/%3E%3C/svg%3E" 
                                     alt="Foto do Funcionário" 
                                     class="img-thumbnail" style="width: 130px; height: 160px; object-fit: cover;"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 120\'%3E%3Crect fill=\'%23e2e8f0\' width=\'100\' height=\'120\'/%3E%3Ccircle cx=\'50\' cy=\'40\' r=\'25\' fill=\'%2394a3b8\'/%3E%3Cellipse cx=\'50\' cy=\'110\' rx=\'40\' ry=\'35\' fill=\'%2394a3b8\'/%3E%3C/svg%3E'">
                            </div>
                                <div class="btn-group-vertical" role="group">
                                    <label for="upload_foto_funcionario" class="btn btn-sm btn-outline-primary mb-1" style="cursor: pointer;">
                                        <i class="fas fa-upload mr-1"></i> Enviar Foto
                                    </label>
                                    <input type="file" id="upload_foto_funcionario" accept="image/*" style="display: none;">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn_remover_foto_funcionario" style="display: none;">
                                        <i class="fas fa-trash mr-1"></i> Remover
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <h5 class="mb-3">
                                    <i class="fas fa-info-circle mr-2 text-primary"></i>
                                    Informações Básicas
                                </h5>
                                <div class="table-responsive">
                                    <table class="modern-table">
                                        <tbody>
                                            <tr>
                                                <th style="width: 200px; font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-user mr-2"></i>Nome
                                                </th>
                                                <td id="f_nome" class="font-weight-500"></td>
                                            </tr>
                                            <tr>
                                                <th style="font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-id-card mr-2"></i>CPF
                                                </th>
                                                <td id="f_cpf"></td>
                                            </tr>
                                            <tr>
                                                <th style="font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-venus-mars mr-2"></i>Sexo
                                                </th>
                                                <td id="f_sexo"></td>
                                            </tr>
                                            <tr>
                                                <th style="font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-briefcase mr-2"></i>Função
                                                </th>
                                                <td id="f_funcao"></td>
                                            </tr>
                                            <tr>
                                                <th style="font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-calendar mr-2"></i>Criado em
                                                </th>
                                                <td id="f_created"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-cogs mr-2 text-warning"></i>
                                    Alterar Status do Funcionário
                                </h5>
                                <div class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-md dropdown-toggle" type="button" id="dropdownStatusFuncionario" data-toggle="dropdown">
                                            <i class="fas fa-user-edit mr-2"></i>
                                            Alterar Status
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" onclick="alterarStatusFuncionario('trabalhando')">
                                                <i class="fas fa-user-check mr-2 text-success"></i>
                                                Readmitir / Ativar
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="alterarStatusFuncionario('afastado')">
                                                <i class="fas fa-user-clock mr-2 text-warning"></i>
                                                Afastar
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="alterarStatusFuncionario('ferias')">
                                                <i class="fas fa-umbrella-beach mr-2 text-info"></i>
                                                Colocar em Férias
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#" onclick="alterarStatusFuncionario('demitido')">
                                                <i class="fas fa-user-times mr-2 text-danger"></i>
                                                Demitir
                                            </a>
                                        </div>
                                    </div>
                                    <small class="d-block text-muted mt-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Status atual: <span id="status-atual-funcionario"></span>
                                    </small>
                                </div>
                            </div>
                        </div>



                        <!-- Documentos -->
                        <h5 class="mb-3">
                            <i class="fas fa-file-alt mr-2 text-primary"></i>
                            Documentos Anexados
                        </h5>
                        <div id="lista_documentos" class="mb-4"></div>

                        <!-- Anexar Documentos -->
                        <div class="modern-card-anexo mb-4">
                            <div class="card-header-anexo">
                                <h5 class="mb-0">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Anexar Documento (PDF)
                                </h5>
                            </div>
                            <div class="card-body-anexo">
                                <form id="form-anexar" enctype="multipart/form-data">
                                    @csrf
                                    <div class="upload-area">
                                        <label for="arquivo-nome" class="font-weight-bold text-muted mb-3">
                                            <i class="fas fa-file-pdf mr-2 text-danger"></i>
                                            Arquivo (PDF)
                                        </label>
                                        <div class="file-input-wrapper">
                                            <input type="text" id="arquivo-nome" class="form-control file-display" placeholder="Nenhum arquivo escolhido" readonly>
                                            <button type="button" id="btn-escolher-arquivo" class="btn btn-outline-primary">
                                                <i class="fas fa-folder-open mr-1"></i> Escolher arquivo
                                            </button>
                                            <input type="file" id="arquivo" name="arquivo" class="d-none" accept=".pdf" required>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Formatos aceitos: PDF. Tamanho máximo: 70MB
                                        </small>
                                    </div>
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-success btn-lg px-5">
                                            <i class="fas fa-paperclip mr-2"></i>
                                            Anexar Documento
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Atestados -->
                    <div class="tab-pane fade" id="atestados-content" role="tabpanel">
                        <!-- Lista de Atestados -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-file-medical mr-2 text-primary"></i>
                                    Atestados Anexados
                                </h5>
                                <div id="lista_atestados"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar Atestado
                                </h5>
                                <form id="form-atestado" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="tipo_atestado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-medical-kit mr-1"></i>
                                            Tipo de Atestado
                                        </label>
                                        <select id="tipo_atestado" name="tipo_atestado" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            <option value="Médico">Médico</option>
                                            <option value="Odontológico">Odontológico</option>
                                            <option value="Psicológico">Psicológico</option>
                                            <option value="Fisioterapia">Fisioterapia</option>
                                            <option value="Exame">Exame</option>
                                            <option value="Outros">Outros</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="data_atestado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Data do Atestado
                                        </label>
                                        <input type="date" id="data_atestado" name="data_atestado" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="dias_afastamento" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-clock mr-1"></i>
                                            Dias de Afastamento
                                        </label>
                                        <input type="number" id="dias_afastamento" name="dias_afastamento" class="form-control" min="0" placeholder="0 = sem afastamento">
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_atestado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_atestado" name="observacoes" class="form-control" rows="2" placeholder="Observações adicionais..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_atestado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-upload mr-1"></i>
                                            Arquivo do Atestado
                                        </label>
                                        <input type="file" id="arquivo_atestado" name="arquivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small class="text-muted mt-1">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            PDF, JPG ou PNG - Máx: 70MB
                                        </small>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-plus mr-2"></i>
                                            Anexar Atestado
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Advertências -->
                    <div class="tab-pane fade" id="advertencias-content" role="tabpanel">
                        <!-- Lista de Advertências -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
                                    Advertências Aplicadas
                                </h5>
                                <div id="lista_advertencias"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-danger"></i>
                                    Aplicar Advertência
                                </h5>
                                <form id="form-advertencia" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="tipo_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-gavel mr-1"></i>
                                            Tipo de Advertência
                                        </label>
                                        <select id="tipo_advertencia" name="tipo_advertencia" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            <option value="verbal">Verbal</option>
                                            <option value="escrita">Escrita</option>
                                            <option value="suspensao">Suspensão</option>
                                            <option value="ocorrencia">Ocorrência</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="motivo_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-reason mr-1"></i>
                                            Motivo
                                        </label>
                                        <input type="text" id="motivo_advertencia" name="motivo" class="form-control" maxlength="500" placeholder="Motivo da advertência..." required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="data_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Data da Advertência
                                        </label>
                                        <input type="date" id="data_advertencia" name="data_advertencia" class="form-control" required>
                                    </div>
                                    <div class="mb-3" id="dias_suspensao_group" style="display: none;">
                                        <label for="dias_suspensao" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-ban mr-1"></i>
                                            Dias de Suspensão
                                        </label>
                                        <input type="number" id="dias_suspensao" name="dias_suspensao" class="form-control" min="1" max="30">
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_advertencia" name="observacoes" class="form-control" rows="2" placeholder="Observações adicionais..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-upload mr-1"></i>
                                            Documento da Advertência
                                        </label>
                                        <div class="file-input-wrapper">
                                            <input type="text" id="arquivo_advertencia_nome" class="form-control file-display" placeholder="Nenhum arquivo escolhido" readonly>
                                            <button type="button" id="btn-escolher-advertencia" class="btn btn-outline-primary" onclick="document.getElementById('arquivo_advertencia').click()">
                                                <i class="fas fa-folder-open mr-1"></i> Escolher arquivo
                                            </button>
                                            <input type="file" id="arquivo_advertencia" name="arquivo" class="d-none" accept=".pdf,.jpg,.jpeg,.png" required>
                                        </div>
                                        <small class="text-muted mt-1">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            PDF, JPG ou PNG - Máx: 70MB
                                        </small>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-danger btn-block">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            Aplicar Advertência
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba EPI -->
                    <div class="tab-pane fade" id="epi-content" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-boxes mr-2 text-primary"></i>
                                        Materiais Retirados (Sistema)
                                    </h5>
                                    <button class="btn btn-primary btn-sm" onclick="abrirModalCompleto()">
                                        <i class="fas fa-table mr-2"></i>
                                        Ver Histórico Completo
                                    </button>
                                </div>
                                <div id="lista_epis"></div>
                                
                                <hr class="my-4">
                                
                                <h5 class="mb-3">
                                    <i class="fas fa-file-pdf mr-2 text-primary"></i>
                                    EPI's (PDFs)
                                </h5>
                                <div id="lista_epis_retroativos"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar EPI Retroativo
                                </h5>
                                <form id="form-epi-retroativo" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="data_epi" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Data do EPI
                                        </label>
                                        <input type="date" id="data_epi" name="data" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_epi" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            Arquivo PDF
                                        </label>
                                        <input type="file" id="arquivo_epi" name="arquivo" class="form-control" accept=".pdf" required>
                                        <small class="text-muted">Apenas arquivos PDF (máx. 50MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-upload mr-2"></i>
                                        Anexar EPI Retroativo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Contra Cheques -->
                    <div class="tab-pane fade" id="contra-cheques-content" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-money-check mr-2 text-primary"></i>
                                    Contra Cheques Anexados
                                </h5>
                                <div id="lista_contra_cheques"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar Contra Cheque
                                </h5>
                                <form id="form-contra-cheque" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="mes_referencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Mês de Referência
                                        </label>
                                        <input type="month" id="mes_referencia" name="mes_referencia" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_contra_cheque" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_contra_cheque" name="observacoes" class="form-control" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_contra_cheque" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            Arquivo PDF
                                        </label>
                                        <input type="file" id="arquivo_contra_cheque" name="arquivo" class="form-control" accept=".pdf" required>
                                        <small class="text-muted">Apenas arquivos PDF (máx. 10MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-upload mr-2"></i>
                                        Anexar Contra Cheque
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Férias -->
                    <div class="tab-pane fade" id="ferias-content" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-umbrella-beach mr-2 text-primary"></i>
                                    Férias Anexadas
                                </h5>
                                <div id="lista_ferias"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar Férias
                                </h5>
                                <form id="form-ferias" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="periodo_inicio" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar-check mr-1"></i>
                                            Período de Início
                                        </label>
                                        <input type="date" id="periodo_inicio" name="periodo_inicio" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="periodo_fim" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar-times mr-1"></i>
                                            Período de Fim
                                        </label>
                                        <input type="date" id="periodo_fim" name="periodo_fim" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ano_exercicio" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            Ano de Exercício
                                        </label>
                                        <input type="number" id="ano_exercicio" name="ano_exercicio" class="form-control" min="1996" max="2030" value="{{ date('Y') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_ferias" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_ferias" name="observacoes" class="form-control" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_ferias" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            Arquivo PDF
                                        </label>
                                        <input type="file" id="arquivo_ferias" name="arquivo" class="form-control" accept=".pdf" required>
                                        <small class="text-muted">Apenas arquivos PDF (máx. 10MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-upload mr-2"></i>
                                        Anexar Férias
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Décimo -->
                    <div class="tab-pane fade" id="decimo-content" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-gift mr-2 text-primary"></i>
                                    Décimo Terceiro Anexado
                                </h5>
                                <div id="lista_decimo"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar Décimo Terceiro
                                </h5>
                                <form id="form-decimo" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="ano_referencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Ano de Referência
                                        </label>
                                        <input type="number" id="ano_referencia" name="ano_referencia" class="form-control" min="1996" max="2030" value="{{ date('Y') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="parcela" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-list mr-1"></i>
                                            Parcela
                                        </label>
                                        <select id="parcela" name="parcela" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            <option value="1">1ª Parcela</option>
                                            <option value="2">2ª Parcela</option>
                                            <option value="unica">Parcela Única</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="valor_bruto" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-dollar-sign mr-1"></i>
                                            Valor Bruto (opcional)
                                        </label>
                                        <input type="text" id="valor_bruto" name="valor_bruto" class="form-control" placeholder="Ex: 1.300,00">
                                        <small class="text-muted">Digite apenas números. A formatação será aplicada automaticamente.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_decimo" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_decimo" name="observacoes" class="form-control" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_decimo" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            Arquivo PDF
                                        </label>
                                        <input type="file" id="arquivo_decimo" name="arquivo" class="form-control" accept=".pdf" required>
                                        <small class="text-muted">Apenas arquivos PDF (máx. 10MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-upload mr-2"></i>
                                        Anexar Décimo Terceiro
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Rescisão -->
                    <div class="tab-pane fade" id="rescisao-content" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-handshake mr-2 text-primary"></i>
                                    Rescisão Anexada
                                </h5>
                                <div id="lista_rescisao"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar Rescisão
                                </h5>
                            <div id="rescisao-bloqueado-alert" class="alert alert-secondary" style="display:none">
                                <i class="fas fa-lock mr-1"></i>
                                Esta aba está bloqueada porque já existe uma rescisão e o status atual não é <strong>Trabalhando</strong>. Para liberar novamente, altere o status do funcionário para <strong>Trabalhando</strong>.
                            </div>
                                <form id="form-rescisao" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="data_rescisao" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Data da Rescisão
                                        </label>
                                        <input type="date" id="data_rescisao" name="data_rescisao" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tipo_rescisao" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-list mr-1"></i>
                                            Tipo de Rescisão
                                        </label>
                                        <select id="tipo_rescisao" name="tipo_rescisao" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            <option value="demissao_sem_justa_causa">Demissão sem Justa Causa</option>
                                            <option value="demissao_justa_causa">Demissão com Justa Causa</option>
                                            <option value="pedido_demissao">Pedido de Demissão</option>
                                            <option value="acordo_mutuo">Acordo Mútuo</option>
                                            <option value="aposentadoria">Aposentadoria</option>
                                            <option value="fim_contrato">Fim de Contrato</option>
                                            <option value="outros">Outros</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="valor_total" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-dollar-sign mr-1"></i>
                                            Valor Total (opcional)
                                        </label>
                                        <input type="text" id="valor_total" name="valor_total" class="form-control" placeholder="Ex: 5.250,75">
                                        <small class="text-muted">Digite apenas números. A formatação será aplicada automaticamente.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_rescisao" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_rescisao" name="observacoes" class="form-control" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_rescisao" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            Arquivo PDF
                                        </label>
                                        <input type="file" id="arquivo_rescisao" name="arquivo" class="form-control" accept=".pdf" required>
                                        <small class="text-muted">Apenas arquivos PDF (máx. 10MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-upload mr-2"></i>
                                        Anexar Rescisão
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Frequência -->
                    <div class="tab-pane fade" id="frequencia-content" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-calendar-check mr-2 text-primary"></i>
                                    Frequência Anexada
                                </h5>
                                <div id="lista_frequencia"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar Frequência
                                </h5>
                                <form id="form-frequencia" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="mes_ano_frequencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Mês/Ano de Referência
                                        </label>
                                        <input type="month" id="mes_ano_frequencia" name="mes_ano" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_frequencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_frequencia" name="observacoes" class="form-control" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_frequencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            Arquivo PDF
                                        </label>
                                        <input type="file" id="arquivo_frequencia" name="arquivo" class="form-control" accept=".pdf" required>
                                        <small class="text-muted">Apenas arquivos PDF (máx. 10MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-upload mr-2"></i>
                                        Anexar Frequência
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Certificado -->
                    <div class="tab-pane fade" id="certificado-content" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-certificate mr-2 text-primary"></i>
                                    Certificados Anexados
                                </h5>
                                <div id="lista_certificado"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar Certificado
                                </h5>
                                <form id="form-certificado" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="nome_certificado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-tag mr-1"></i>
                                            Nome do Certificado
                                        </label>
                                        <input type="text" id="nome_certificado" name="nome_certificado" class="form-control" placeholder="Ex: NR-10, NR-35, Primeiros Socorros..." required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="data_emissao_certificado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Data de Emissão
                                        </label>
                                        <input type="date" id="data_emissao_certificado" name="data_emissao" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="data_validade_certificado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar-times mr-1"></i>
                                            Data de Validade (opcional)
                                        </label>
                                        <input type="date" id="data_validade_certificado" name="data_validade" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_certificado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_certificado" name="observacoes" class="form-control" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_certificado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            Arquivo PDF
                                        </label>
                                        <input type="file" id="arquivo_certificado" name="arquivo" class="form-control" accept=".pdf" required>
                                        <small class="text-muted">Apenas arquivos PDF (máx. 10MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-upload mr-2"></i>
                                        Anexar Certificado
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba ASOS -->
                    <div class="tab-pane fade" id="asos-content" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-heartbeat mr-2 text-primary"></i>
                                    ASOS Anexados
                                </h5>
                                <div id="lista_asos"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar ASOS
                                </h5>
                                <form id="form-asos" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="data_asos" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Data do Exame
                                        </label>
                                        <input type="date" id="data_asos" name="data_exame" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tipo_asos" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-list mr-1"></i>
                                            Tipo de Exame
                                        </label>
                                        <select id="tipo_asos" name="tipo_exame" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            <option value="admissional">Admissional</option>
                                            <option value="periodico">Periódico</option>
                                            <option value="mudanca_funcao">Mudança de Função</option>
                                            <option value="retorno_trabalho">Retorno ao Trabalho</option>
                                            <option value="demissional">Demissional</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="medico_responsavel" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-user-md mr-1"></i>
                                            Médico Responsável (opcional)
                                        </label>
                                        <input type="text" id="medico_responsavel" name="medico_responsavel" class="form-control" placeholder="Nome do médico responsável">
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_asos" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_asos" name="observacoes" class="form-control" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_asos" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            Arquivo PDF
                                        </label>
                                        <input type="file" id="arquivo_asos" name="arquivo" class="form-control" accept=".pdf" required>
                                        <small class="text-muted">Apenas arquivos PDF (máx. 10MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-upload mr-2"></i>
                                        Anexar ASOS
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes dos Materiais Retirados -->
<div class="modal fade" id="modalDetalhesMaterial" tabindex="-1" role="dialog" aria-labelledby="modalDetalhesMaterialLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalhesMaterialLabel">
                    <i class="fas fa-boxes mr-2"></i>
                    <span id="modal_titulo">Histórico de Materiais Retirados</span> - <span id="modal_funcionario_nome"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="25%">
                                    <i class="fas fa-box mr-1"></i>
                                    Produto
                                </th>
                                <th width="10%" class="text-center">
                                    <i class="fas fa-sort-numeric-up mr-1"></i>
                                    Quantidade
                                </th>
                                <th width="18%">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Data/Hora
                                </th>
                                <th width="20%">
                                    <i class="fas fa-building mr-1"></i>
                                    Centro de Custo
                                </th>
                                <th width="15%">
                                    <i class="fas fa-user mr-1"></i>
                                    Entregue por
                                </th>
                                <th width="12%">
                                    <i class="fas fa-comment mr-1"></i>
                                    Observações
                                </th>
                            </tr>
                        </thead>
                        <tbody id="modal_tabela_materiais">
                            <!-- Dados serão inseridos via JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body py-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Total de retiradas: <span class="font-weight-bold" id="modal_total_retiradas">0</span>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body py-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Período: <span class="font-weight-bold" id="modal_periodo"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
/* Foto do Funcionário */
.foto-funcionario-dp-box {
    border: 3px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px;
    background: linear-gradient(145deg, #f8fafc 0%, #fff 100%);
    display: inline-block;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.foto-funcionario-dp-box:hover {
    border-color: #3b82f6;
    box-shadow: 0 6px 20px rgba(59,130,246,0.15);
}

.foto-funcionario-dp-box img {
    border-radius: 8px;
    display: block;
}

#upload_foto_funcionario + .btn,
label[for="upload_foto_funcionario"] {
    transition: all 0.3s ease;
}

label[for="upload_foto_funcionario"]:hover {
    transform: translateY(-2px);
}

/* Fix: Texto cortado em campos select/dropdown */
select,
select.form-control,
.form-select,
#tipo_rescisao,
#tipo_atestado,
#tipo_advertencia,
#tipo_exame {
    height: auto !important;
    min-height: 44px !important;
    padding: 10px 40px 10px 12px !important;
    line-height: 1.5 !important;
    font-size: 0.9rem !important;
}

/* Estilo moderno para o formulário de anexo */
.modern-card-anexo {
    background: #fff;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.modern-card-anexo:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.form-section-disabled {
    opacity: 0.55;
    pointer-events: none;
    filter: grayscale(0.3);
}

.card-header-anexo {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 16px 24px;
    border-radius: 14px 14px 0 0;
    border-bottom: none;
}

.card-header-anexo h5 {
    color: white !important;
    margin: 0;
    font-weight: 600;
}

.card-body-anexo {
    padding: 24px;
}

.upload-area {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    transition: all 0.3s ease;
    margin-bottom: 16px;
}

.upload-area:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.file-input-wrapper {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 8px;
}

.file-display {
    flex: 1;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 14px;
    color: #64748b;
}

.file-display:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.btn-outline-primary {
    border: 2px solid #3b82f6;
    color: #3b82f6;
    background: transparent;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background: #3b82f6;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-success.btn-lg {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    border-radius: 12px;
    padding: 14px 32px;
    font-size: 16px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    transition: all 0.3s ease;
}

.btn-success.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
}

/* Animação de entrada */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modern-card-anexo {
    animation: fadeInUp 0.5s ease-out;
}

/* Estilo personalizado para lista de resultados */
.search-result-item {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
    padding: 16px 20px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: fadeInUp 0.3s ease-out;
}



.search-result-content {
    flex-grow: 1;
}

.search-result-name {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.search-result-function {
    font-size: 14px;
    color: #64748b;
}

.document-count-badge {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    min-width: 30px;
    text-align: center;
}



/* Estilo para lista de documentos */
.document-item-modern {
    background: #fff;
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #334155;
    display: block;
}



.document-title {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
}

.document-meta {
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.document-size {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 11px;
    color: #475569;
}

/* Animações */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mensagens de estado */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
}

.empty-state-icon {
    font-size: 48px;
    color: #94a3b8;
    margin-bottom: 16px;
}

.empty-state-text {
    font-size: 16px;
    color: #64748b;
    margin-bottom: 0;
}

/* Loading state */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@stop

@section('js')
<script>
(function() {
    let funcionarioSelecionado = null;
    let temRescisaoParaFuncionarioSelecionado = false;

    // Função para formatar data no padrão brasileiro (DD/MM/AAAA HH:MM)
    function formatarDataBR(dataISO) {
        if (!dataISO) return '—';
        const data = new Date(dataISO);
        if (isNaN(data.getTime())) return '—';
        
        const dia = String(data.getDate()).padStart(2, '0');
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const ano = data.getFullYear();
        const hora = String(data.getHours()).padStart(2, '0');
        const minuto = String(data.getMinutes()).padStart(2, '0');
        
        return `${dia}/${mes}/${ano} ${hora}:${minuto}`;
    }

    // Função para formatar apenas data (DD/MM/AAAA)
    function formatarDataSemHora(dataISO) {
        if (!dataISO) return '—';
        
        // Se já está no formato YYYY-MM-DD, converter diretamente
        if (typeof dataISO === 'string' && dataISO.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const [ano, mes, dia] = dataISO.split('-');
            return `${dia}/${mes}/${ano}`;
        }
        
        // Caso contrário, usar Date object
        const data = new Date(dataISO);
        if (isNaN(data.getTime())) return '—';
        
        const dia = String(data.getDate()).padStart(2, '0');
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const ano = data.getFullYear();
        
        return `${dia}/${mes}/${ano}`;
    }

// Função getStatusBadge removida

    function renderResultados(funcs){
        const box = document.getElementById('resultado_busca');
        box.innerHTML = '';
        
        if(!funcs || funcs.length === 0){
            box.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <p class="empty-state-text">Nenhum funcionário encontrado com este nome.</p>
                </div>
            `;
            return;
        }

        funcs.forEach((f, index) => {
            const item = document.createElement('div');
            item.className = 'search-result-item';
            item.style.animationDelay = `${index * 0.1}s`;
            item.innerHTML = `
                <div class="search-result-content">
                    <div class="search-result-name">
                        <i class="fas fa-user mr-2 text-primary"></i>
                        ${f.nome}
                    </div>
                    <div class="search-result-function">
                        <i class="fas fa-briefcase mr-1"></i>
                        ${f.funcao}
                    </div>
                </div>
                <div class="text-right">
                    <div class="document-count-badge">
                        ${f.total_documentos || 0} docs
                    </div>
                </div>
            `;
            item.addEventListener('click', () => selecionarFuncionario(f));
            box.appendChild(item);
        });
    }

    function selecionarFuncionario(f){
        funcionarioSelecionado = f;
        temRescisaoParaFuncionarioSelecionado = false;
        
        // Ocultar área de busca e mostrar dados do funcionário
        document.getElementById('area_busca').style.display = 'none';
        const dadosSection = document.getElementById('dados_funcionario');
        dadosSection.classList.remove('d-none');
        
        // Scroll para o topo
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Preencher dados
        document.getElementById('funcionario_nome_header').textContent = f.nome;
        document.getElementById('f_nome').innerHTML = `<strong>${f.nome}</strong>`;
        document.getElementById('f_cpf').textContent = formatarCPF(f.cpf);
        document.getElementById('f_sexo').textContent = f.sexo === 'M' ? 'Masculino' : 'Feminino';
        document.getElementById('f_funcao').textContent = f.funcao;
        document.getElementById('f_created').textContent = formatarDataBR(f.created_at);
        
        // Exibir foto do funcionário
        const fotoEl = document.getElementById('f_foto');
        const btnRemoverFoto = document.getElementById('btn_remover_foto_funcionario');
        const fotoPlaceholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 120'%3E%3Crect fill='%23e2e8f0' width='100' height='120'/%3E%3Ccircle cx='50' cy='40' r='25' fill='%2394a3b8'/%3E%3Cellipse cx='50' cy='110' rx='40' ry='35' fill='%2394a3b8'/%3E%3C/svg%3E";
        if (f.foto_path) {
            fotoEl.src = '{{ url("/") }}/' + f.foto_path;
            btnRemoverFoto.style.display = 'block';
        } else {
            fotoEl.src = fotoPlaceholder;
            btnRemoverFoto.style.display = 'none';
        }
        
        // Mostrar status atual
        const statusTexto = {
            'trabalhando': 'Trabalhando',
            'demitido': 'Demitido',
            'afastado': 'Afastado',
            'ferias': 'Em Férias'
        }[f.status] || f.status;
        document.getElementById('status-atual-funcionario').textContent = statusTexto;
        
        // Carregar documentos em sequência para evitar rate limiting (503)
        // Cada requisição aguarda a anterior terminar com pequeno delay
        carregarTodosDocumentosSequencial(f.id);
        // Atualiza o estado do formulário de rescisão com base no status inicial
        setTimeout(() => atualizarEstadoFormularioRescisao(), 0);
    }

    // Função para carregar todos os documentos em sequência (evita erro 503 de rate limiting)
    async function carregarTodosDocumentosSequencial(funcionarioId) {
        const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
        const delayMs = 100; // 100ms entre cada requisição
        
        // Carregar em sequência com pequeno delay entre cada
        try {
            await carregarDocumentos(funcionarioId);
            await delay(delayMs);
            await carregarAtestados(funcionarioId);
            await delay(delayMs);
            await carregarAdvertencias(funcionarioId);
            await delay(delayMs);
            await carregarEpis(funcionarioId);
            await delay(delayMs);
            await carregarEpisRetroativos(funcionarioId);
            await delay(delayMs);
            await carregarContraCheques(funcionarioId);
            await delay(delayMs);
            await carregarFerias(funcionarioId);
            await delay(delayMs);
            await carregarDecimo(funcionarioId);
            await delay(delayMs);
            await carregarRescisao(funcionarioId);
            await delay(delayMs);
            await carregarFrequencia(funcionarioId);
            await delay(delayMs);
            await carregarCertificado(funcionarioId);
            await delay(delayMs);
            await carregarTermoAditivo(funcionarioId);
            await delay(delayMs);
            await carregarAsos(funcionarioId);
            await delay(delayMs);
            await carregarOS(funcionarioId);
        } catch (error) {
            console.warn('Erro ao carregar alguns documentos:', error);
        }
    }

    function voltarParaBusca(){
        // Ocultar dados do funcionário e mostrar área de busca
        document.getElementById('dados_funcionario').classList.add('d-none');
        document.getElementById('area_busca').style.display = 'block';
        
        // Limpar funcionário selecionado
        funcionarioSelecionado = null;
        
        // Scroll para o topo
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Focar no campo de busca
        document.getElementById('busca_nome').focus();
    }

    async function carregarDocumentos(id){
        const lista = document.getElementById('lista_documentos');
        
        // Loading state
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando documentos...
            </div>
        `;

        try {
            const res = await fetch(`{{ route('documentos-dp.documentos', ['id' => 'ID_PLACE']) }}`.replace('ID_PLACE', id));
            const data = await res.json();
            
            lista.innerHTML = '';
            
            if(!data.success || !data.documentos || data.documentos.length === 0){
                lista.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-file-times"></i>
                        </div>
                        <p class="empty-state-text">Nenhum documento anexado ainda.</p>
                    </div>
                `;
                return;
            }
            
            data.documentos.forEach((d, index) => {
                const item = document.createElement('div');
                item.className = 'document-item-modern';
                item.style.animationDelay = `${index * 0.1}s`;
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="document-title">
                                <i class="fas fa-file-pdf mr-2 text-danger"></i>
                                ${d.tipo_documento}
                            </div>
                            <div class="document-meta">
                                <span>
                                    <i class="fas fa-file mr-1"></i>
                                    ${d.arquivo_nome}
                                </span>
                                <span class="document-size ml-2">
                                    ${(d.arquivo_tamanho/1024).toFixed(1)} KB
                                </span>
                            </div>
                        </div>
                        <div class="btn-group ml-3" role="group">
                            <a href="{{ route('documentos-dp.arquivo', ['id' => 'DOC_ID']) }}" target="_blank" class="btn btn-sm btn-outline-primary" onclick="this.href=this.href.replace('DOC_ID', ${d.id})">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a>
                            <button onclick="excluirDocumento('documento', ${d.id})" 
                                    class="btn btn-sm btn-outline-danger"
                                    title="Excluir documento">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                lista.appendChild(item);
            });
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar documentos. Tente novamente.
                </div>
            `;
        }
    }

    // Busca com loading state
    let timer = null;
    document.getElementById('busca_nome').addEventListener('input', function(){
        const v = this.value.trim();
        const resultBox = document.getElementById('resultado_busca');
        
        clearTimeout(timer);
        
        if(v.length < 3){
            resultBox.innerHTML = '';
            return;
        }

        // Loading state
        resultBox.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Buscando funcionários...
            </div>
        `;
        
        timer = setTimeout(async () => {
            try {
                const url = `{{ route('documentos-dp.buscar') }}?nome=${encodeURIComponent(v)}`;
                const res = await fetch(url);
                const data = await res.json();
                if(data.success){
                    renderResultados(data.funcionarios);
                }
            } catch (error) {
                resultBox.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Erro na busca. Tente novamente.
                    </div>
                `;
            }
        }, 300);
    });

    // Botão escolher arquivo
    document.getElementById('btn-escolher-arquivo').addEventListener('click', function() {
        document.getElementById('arquivo').click();
    });

    // Mostrar nome do arquivo escolhido
    document.getElementById('arquivo').addEventListener('change', function() {
        const file = this.files && this.files[0] ? this.files[0] : null;
        document.getElementById('arquivo-nome').value = file ? file.name : '';
    });

    // Mostrar nome do arquivo de advertência escolhido
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'arquivo_advertencia') {
            const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
            const nomeInput = document.getElementById('arquivo_advertencia_nome');
            if (nomeInput) {
                nomeInput.value = file ? file.name : '';
            }
        }
    });

    // Anexar documento com feedback visual
    document.getElementById('form-anexar').addEventListener('submit', async function(e){
        e.preventDefault();
        if(!funcionarioSelecionado){ return; }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Loading state no botão
        submitBtn.innerHTML = '<div class="loading-spinner mr-2"></div>Anexando...';
        submitBtn.disabled = true;
        
        try {
            const fd = new FormData(this);
            const url = `{{ route('documentos-dp.anexar', ['id' => 'ID_FUNC']) }}`.replace('ID_FUNC', funcionarioSelecionado.id);
            const res = await fetch(url, { method: 'POST', body: fd });
            const data = await res.json();
            
            if(data.success){
                this.reset();
                carregarDocumentos(funcionarioSelecionado.id);
                
                // Feedback de sucesso
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Anexado!';
                submitBtn.className = 'btn btn-success btn-lg px-4';
                
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Falha ao anexar');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: error.message,
                confirmButtonColor: '#3085d6'
            });
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Evento do botão "Voltar"
    document.getElementById('btn-voltar-busca').addEventListener('click', voltarParaBusca);

    // ========================================
    // FUNÇÕES PARA ATESTADOS
    // ========================================
    
    async function carregarAtestados(funcionarioId) {
        const lista = document.getElementById('lista_atestados');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando atestados...
            </div>
        `;

        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/atestados`);
            let data;
            const ctDec = response.headers.get('content-type') || '';
            if (ctDec.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                throw new Error(text || 'Erro interno do servidor');
            }
            
            lista.innerHTML = '';
            
            if(!data.success || !data.atestados || data.atestados.length === 0){
                lista.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <p class="empty-state-text">Nenhum atestado anexado ainda.</p>
                    </div>
                `;
                return;
            }
            
            data.atestados.forEach((atestado, index) => {
                const link = document.createElement('div');
                link.className = 'document-item-modern';
                link.style.animationDelay = `${index * 0.1}s`;
                link.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="document-title">
                            <i class="fas fa-file-medical mr-2 text-primary"></i>
                            ${atestado.tipo_atestado}
                        </div>
                    </div>
                    <div class="document-meta">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-calendar mr-1"></i>
                                Data: ${formatarDataSemHora(atestado.data_atestado)}
                            </small>
                            ${atestado.dias_afastamento ? `<br><small class="text-muted"><i class="fas fa-clock mr-1"></i>Afastamento: ${atestado.dias_afastamento} dias</small>` : ''}
                        </div>
                        <div class="text-right">
                            <div class="btn-group mb-2" role="group">
                                <a href="/documentos-dp/atestado/${atestado.id}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </a>
                                <button onclick="excluirDocumento('atestado', ${atestado.id})" 
                                        class="btn btn-sm btn-outline-danger"
                                        title="Excluir documento">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <br><span class="document-size">
                                ${(atestado.arquivo_tamanho/1024).toFixed(1)} KB
                            </span>
                        </div>
                    </div>
                    ${atestado.observacoes ? `<div class="mt-2"><small class="text-muted"><strong>Obs:</strong> ${atestado.observacoes}</small></div>` : ''}
                `;
                lista.appendChild(link);
            });
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar atestados. Tente novamente.
                </div>
            `;
        }
    }

    // ========================================
    // FUNÇÕES PARA ADVERTÊNCIAS
    // ========================================
    
    async function carregarAdvertencias(funcionarioId) {
        const lista = document.getElementById('lista_advertencias');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando advertências...
            </div>
        `;

        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/advertencias`);
            let data;
            const ctRes = response.headers.get('content-type') || '';
            if (ctRes.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                throw new Error(text || 'Erro interno do servidor');
            }
            
            lista.innerHTML = '';
            
            if(!data.success || !data.advertencias || data.advertencias.length === 0){
                lista.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <p class="empty-state-text">Nenhuma advertência aplicada.</p>
                    </div>
                `;
                return;
            }
            
            data.advertencias.forEach((advertencia, index) => {
                const tipoClass = {
                    'verbal': 'info',
                    'escrita': 'warning', 
                    'suspensao': 'danger',
                    'ocorrencia': 'warning'
                }[advertencia.tipo_advertencia] || 'secondary';

                const link = document.createElement('div');
                link.className = 'document-item-modern';
                link.style.animationDelay = `${index * 0.1}s`;
                link.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="document-title">
                            <i class="fas fa-exclamation-triangle mr-2 text-${tipoClass}"></i>
                            ${advertencia.tipo_advertencia.charAt(0).toUpperCase() + advertencia.tipo_advertencia.slice(1)}
                        </div>
                    </div>
                    <div class="mb-2">
                        <strong>Motivo:</strong> ${advertencia.motivo}
                    </div>
                    <div class="document-meta">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-calendar mr-1"></i>
                                Data: ${formatarDataSemHora(advertencia.data_advertencia)}
                            </small>
                            ${advertencia.dias_suspensao ? `<br><small class="text-muted"><i class="fas fa-ban mr-1"></i>Suspensão: ${advertencia.dias_suspensao} dias</small>` : ''}
                        </div>
                        <div class="text-right">
                            <div class="btn-group mb-2" role="group">
                                <a href="/documentos-dp/advertencia/${advertencia.id}" target="_blank" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </a>
                                <button onclick="excluirDocumento('advertencia', ${advertencia.id})" 
                                        class="btn btn-sm btn-outline-danger"
                                        title="Excluir documento">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <br><span class="document-size">
                                ${(advertencia.arquivo_tamanho/1024).toFixed(1)} KB
                            </span>
                        </div>
                    </div>
                    ${advertencia.observacoes ? `<div class="mt-2"><small class="text-muted"><strong>Obs:</strong> ${advertencia.observacoes}</small></div>` : ''}
                `;
                lista.appendChild(link);
            });
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar advertências. Tente novamente.
                </div>
            `;
        }
    }

    // ========================================
    // EVENTOS DOS FORMULÁRIOS
    // ========================================
    
    // Mostrar/ocultar campo de dias de suspensão
    document.getElementById('tipo_advertencia').addEventListener('change', function() {
        const diasSuspensaoGroup = document.getElementById('dias_suspensao_group');
        if (this.value === 'suspensao') {
            diasSuspensaoGroup.style.display = 'block';
            document.getElementById('dias_suspensao').required = true;
        } else {
            diasSuspensaoGroup.style.display = 'none';
            document.getElementById('dias_suspensao').required = false;
        }
    });

    // Envio do formulário de atestado
    document.getElementById('form-atestado').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        try {
            submitBtn.innerHTML = '<div class="loading-spinner mr-2"></div>Enviando...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/atestados`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            

            
            if (data.success) {
                this.reset();
                carregarAtestados(funcionarioSelecionado.id);
                
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Anexado!';
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);

                // Verificar se há aviso de limite de dias
                if (data.aviso_limite) {
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção!',
                            text: data.aviso_limite,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Entendi',
                            timer: 0, // Não fechar automaticamente
                            allowOutsideClick: false
                        });
                    }, 500); // Reduzido para 0.5 segundos
                }
            } else {
                throw new Error(data.message || 'Erro ao anexar atestado');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro: ' + error.message,
                confirmButtonColor: '#3085d6'
            });
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Envio do formulário de advertência
    document.getElementById('form-advertencia').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        try {
            submitBtn.innerHTML = '<div class="loading-spinner mr-2"></div>Enviando...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/advertencias`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.reset();
                carregarAdvertencias(funcionarioSelecionado.id);
                
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Aplicada!';
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Erro ao aplicar advertência');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro: ' + error.message,
                confirmButtonColor: '#3085d6'
            });
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Função para alterar status do funcionário
    window.alterarStatusFuncionario = async function(novoStatus) {
        if (!funcionarioSelecionado) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Nenhum funcionário selecionado.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        const statusTextos = {
            'trabalhando': 'readmitir/ativar',
            'demitido': 'demitir',
            'afastado': 'afastar',
            'ferias': 'colocar em férias'
        };

        const acao = statusTextos[novoStatus] || 'alterar status de';
        
        // Confirmação com SweetAlert
        const result = await Swal.fire({
            title: 'Confirmar Alteração',
            text: `Tem certeza que deseja ${acao} ${funcionarioSelecionado.nome}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, alterar!',
            cancelButtonText: 'Cancelar'
        });
        
        if (!result.isConfirmed) return;

        const btnDropdown = document.getElementById('dropdownStatusFuncionario');
        const originalText = btnDropdown.innerHTML;
        
        try {
            btnDropdown.innerHTML = '<div class="loading-spinner mr-2"></div>Alterando...';
            btnDropdown.disabled = true;
            
            // Usar a mesma rota de demitir, mas generalizada
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/alterar-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: novoStatus })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: `Status alterado para "${novoStatus}" com sucesso!`,
                    confirmButtonColor: '#3085d6',
                    timer: 3000,
                    timerProgressBar: true
                });
                
                // Atualizar status local e exibição
                funcionarioSelecionado.status = novoStatus;
                const statusTexto = {
                    'trabalhando': 'Trabalhando',
                    'demitido': 'Demitido',
                    'afastado': 'Afastado',
                    'ferias': 'Em Férias'
                }[novoStatus] || novoStatus;
                document.getElementById('status-atual-funcionario').textContent = statusTexto;
                
                btnDropdown.innerHTML = originalText;
                btnDropdown.disabled = false;
                atualizarEstadoFormularioRescisao();
            } else {
                throw new Error(data.message || 'Erro ao alterar status');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: error.message,
                confirmButtonColor: '#3085d6'
            });
            btnDropdown.innerHTML = originalText;
            btnDropdown.disabled = false;
        }
    };

    // Função para carregar materiais retirados pelo funcionário
    window.carregarEpis = async function(funcionarioId) {
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/epis`);
            const data = await response.json();
            
            const listaEpis = document.getElementById('lista_epis');
            
            if (data.length === 0) {
                listaEpis.innerHTML = '<div class="empty-state"><i class="fas fa-boxes fa-3x text-muted mb-3"></i><p class="text-muted">Nenhum material retirado ainda</p></div>';
                return;
            }
            
            let html = '';
            data.forEach(function(lancamento) {
                const dataRetirada = formatarDataSemHora(lancamento.data_baixa);
                
                // Criar lista de produtos do lançamento
                let produtosList = '';
                lancamento.produtos.forEach(function(produto, index) {
                    if (index > 0) produtosList += ', ';
                    produtosList += `${produto.produto_nome} (${produto.quantidade})`;
                });
                
                html += `
                    <div class="document-item-modern mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <i class="fas fa-boxes mr-2 text-primary"></i>
                                    Lançamento - ${lancamento.produtos.length} produto(s)
                                </h6>
                                <div class="small text-muted mb-2">
                                    <div><i class="fas fa-calendar mr-1"></i>Data: ${dataRetirada}</div>
                                    <div><i class="fas fa-box mr-1"></i>Produtos: ${produtosList}</div>
                                    <div><i class="fas fa-sort-numeric-up mr-1"></i>Total: ${lancamento.total_quantidade} item(s)</div>
                                    <div><i class="fas fa-building mr-1"></i>Centro de Custo: ${lancamento.centro_custo_nome || 'Não informado'}</div>
                                    <div><i class="fas fa-user mr-1"></i>Entregue por: ${lancamento.usuario_entrega || 'Não informado'}</div>
                                    ${lancamento.observacoes ? `<div><i class="fas fa-comment mr-1"></i>Obs: ${lancamento.observacoes}</div>` : ''}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="mb-2">
                                    <span class="badge badge-success">${lancamento.produtos.length}</span>
                                    <br><small class="text-muted">produtos</small>
                                </div>
                                <button class="btn btn-outline-primary btn-sm" onclick="abrirModalLancamento(${lancamento.id})">
                                    <i class="fas fa-eye mr-1"></i>Ver Lançamento
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            listaEpis.innerHTML = html;
        } catch (error) {
            document.getElementById('lista_epis').innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Erro ao carregar materiais</div>';
        }
    };

    // Função para abrir modal completo com todos os materiais
    window.abrirModalCompleto = async function() {
        if (!funcionarioSelecionado) {
            return;
        }
        
        try {
            // Buscar todos os materiais do funcionário
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/epis`);
            const materiais = await response.json();
            
                    // Preencher nome do funcionário no título
        document.getElementById('modal_titulo').textContent = 'Histórico de Materiais Retirados';
        document.getElementById('modal_funcionario_nome').textContent = funcionarioSelecionado.nome;
            
            // Preencher tabela
            const tbody = document.getElementById('modal_tabela_materiais');
            
            if (materiais.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <br>
                            Nenhum material retirado
                        </td>
                    </tr>
                `;
            } else {
                let html = '';
                materiais.forEach(function(lancamento) {
                    const dataRetirada = formatarDataSemHora(lancamento.data_baixa);
                    
                    // Primeira linha do lançamento
                    const primeiroproduto = lancamento.produtos[0];
                    const rowspan = lancamento.produtos.length;
                    
                    html += `
                        <tr>
                            <td>${primeiroproduto.produto_nome || 'Produto não identificado'}</td>
                            <td class="text-center">
                                <span class="badge badge-primary">${primeiroproduto.quantidade}</span>
                            </td>
                            <td rowspan="${rowspan}" class="align-middle">
                                <small class="text-muted">${dataRetirada}</small>
                            </td>
                            <td rowspan="${rowspan}" class="align-middle">
                                <small>${lancamento.centro_custo_nome || 'Não informado'}</small>
                            </td>
                            <td rowspan="${rowspan}" class="align-middle">
                                <small>${lancamento.usuario_entrega || 'Não informado'}</small>
                            </td>
                            <td rowspan="${rowspan}" class="align-middle">
                                ${lancamento.observacoes ? `<small class="text-info">${lancamento.observacoes}</small>` : '<small class="text-muted">-</small>'}
                            </td>
                        </tr>
                    `;
                    
                    // Linhas adicionais para outros produtos do mesmo lançamento
                    for (let i = 1; i < lancamento.produtos.length; i++) {
                        const produto = lancamento.produtos[i];
                        html += `
                            <tr>
                                <td>${produto.produto_nome || 'Produto não identificado'}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary">${produto.quantidade}</span>
                                </td>
                            </tr>
                        `;
                    }
                });
                tbody.innerHTML = html;
            }
            
            // Atualizar estatísticas
            document.getElementById('modal_total_retiradas').textContent = materiais.length;
            
            // Calcular período
            if (materiais.length > 0) {
                const datas = materiais.map(m => new Date(m.data_baixa)).sort();
                const primeira = formatarDataBR(datas[0].toISOString());
                const ultima = formatarDataBR(datas[datas.length - 1].toISOString());
                document.getElementById('modal_periodo').textContent = primeira === ultima ? primeira : `${primeira} a ${ultima}`;
            } else {
                document.getElementById('modal_periodo').textContent = 'Nenhuma retirada';
            }
            
            // Abrir modal
            $('#modalDetalhesMaterial').modal('show');
            
        } catch (error) {
            // Erro ao carregar dados para modal
        }
    };

    // Função para abrir modal com lançamento específico
    window.abrirModalLancamento = async function(lancamentoId) {
        if (!funcionarioSelecionado) {
            return;
        }
        
        try {
            // Buscar todos os materiais e filtrar pelo lançamento
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/epis`);
            const todosLancamentos = await response.json();
            
            // Encontrar o lançamento específico
            const lancamentoSelecionado = todosLancamentos.find(l => l.id == lancamentoId);
            
            if (!lancamentoSelecionado) {
                return;
            }
            
            // Preencher nome do funcionário no título
            document.getElementById('modal_titulo').textContent = 'Detalhes do Lançamento';
            document.getElementById('modal_funcionario_nome').textContent = funcionarioSelecionado.nome;
            
            // Preencher tabela com apenas os produtos do lançamento selecionado
            const tbody = document.getElementById('modal_tabela_materiais');
            const dataRetirada = formatarDataSemHora(lancamentoSelecionado.data_baixa);
            
            let html = '';
            lancamentoSelecionado.produtos.forEach(function(produto) {
                html += `
                    <tr>
                        <td>
                            <strong>${produto.produto_nome || 'Produto não identificado'}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-primary">${produto.quantidade}</span>
                        </td>
                        <td>
                            <small class="text-muted">${dataRetirada}</small>
                        </td>
                        <td>
                            <small>${lancamentoSelecionado.centro_custo_nome || 'Não informado'}</small>
                        </td>
                        <td>
                            <small>${lancamentoSelecionado.usuario_entrega || 'Não informado'}</small>
                        </td>
                        <td>
                            ${lancamentoSelecionado.observacoes ? `<small class="text-info">${lancamentoSelecionado.observacoes}</small>` : '<small class="text-muted">-</small>'}
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
            
            // Atualizar estatísticas para este lançamento específico
            document.getElementById('modal_total_retiradas').textContent = `1 lançamento (${lancamentoSelecionado.produtos.length} produtos)`;
            document.getElementById('modal_periodo').textContent = dataRetirada;
            
            // Abrir modal
            $('#modalDetalhesMaterial').modal('show');
            
        } catch (error) {
            // Erro ao carregar dados do lançamento
        }
    };

    // Função para formatar CPF
    function formatarCPF(cpf) {
        if (!cpf) return '';
        
        // Remove tudo que não é dígito
        const numeros = cpf.replace(/\D/g, '');
        
        // Aplica a máscara
        return numeros.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }

    // Função para formatar valor monetário brasileiro (para digitação)
    function formatarMoeda(valor) {
        // Remove tudo que não é dígito
        let numeros = valor.replace(/\D/g, '');
        
        // Se não tiver números, retorna vazio
        if (!numeros) return '';
        
        // Converte para decimal dividindo por 100 (centavos)
        let valorDecimal = parseFloat(numeros) / 100;
        
        // Formata com separadores brasileiros
        return valorDecimal.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Função para formatar valor decimal para exibição brasileira
    function formatarValorParaExibicao(valor) {
        if (!valor) return '0,00';
        
        // Converte para número e formata
        const numero = parseFloat(valor);
        return numero.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Função para aplicar máscara de moeda em tempo real
    function aplicarMascaraMoeda(input) {
        input.addEventListener('input', function(e) {
            let valor = e.target.value;
            let valorFormatado = formatarMoeda(valor);
            e.target.value = valorFormatado;
        });
        
        input.addEventListener('focus', function(e) {
            if (e.target.value === '0,00') {
                e.target.value = '';
            }
        });
    }

    // ========================================
    // FUNÇÕES PARA CONTRA CHEQUES
    // ========================================
    
    async function carregarContraCheques(funcionarioId) {
        const lista = document.getElementById('lista_contra_cheques');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando contra cheques...
            </div>
        `;
        
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/contra-cheques`);
            const data = await response.json();
            
            if (!data.success || data.contraCheques.length === 0) {
                lista.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhum contra cheque anexado ainda.
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.contraCheques.forEach(function(contracheque) {
                const dataFormatada = formatarDataBR(contracheque.created_at);
                // Formatar mes_referencia de YYYY-MM para MM/YYYY
                const mesReferencia = contracheque.mes_referencia ? 
                    contracheque.mes_referencia.split('-').reverse().join('/') : 
                    'Mês não informado';
                
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-money-check mr-2 text-primary"></i>
                                        ${mesReferencia}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Anexado em ${dataFormatada}
                                    </small>
                                    ${contracheque.observacoes ? `<br><small class="text-info"><i class="fas fa-comment mr-1"></i>${contracheque.observacoes}</small>` : ''}
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/contra-cheque/${contracheque.id}/download" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button onclick="excluirDocumento('contra-cheque', ${contracheque.id})" 
                                                class="btn btn-outline-danger btn-sm"
                                                title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            lista.innerHTML = html;
            
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar contra cheques.
                </div>
            `;
        }
    }

    // ========================================
    // FUNÇÕES PARA FÉRIAS
    // ========================================
    
    async function carregarFerias(funcionarioId) {
        const lista = document.getElementById('lista_ferias');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando férias...
            </div>
        `;
        
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/ferias`);
            const data = await response.json();
            
            if (!data.success || data.ferias.length === 0) {
                lista.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhuma férias anexada ainda.
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.ferias.forEach(function(ferias) {
                const dataFormatada = formatarDataBR(ferias.created_at);
                const periodoInicio = formatarDataBR(ferias.periodo_inicio);
                const periodoFim = formatarDataBR(ferias.periodo_fim);
                
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-umbrella-beach mr-2 text-primary"></i>
                                        Férias ${ferias.ano_exercicio}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar mr-1"></i>
                                        ${periodoInicio} até ${periodoFim}
                                    </small>
                                    <br><small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Anexado em ${dataFormatada}
                                    </small>
                                    ${ferias.observacoes ? `<br><small class="text-info"><i class="fas fa-comment mr-1"></i>${ferias.observacoes}</small>` : ''}
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/ferias/${ferias.id}/download" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button onclick="excluirDocumento('ferias', ${ferias.id})" 
                                                class="btn btn-outline-danger btn-sm"
                                                title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            lista.innerHTML = html;
            
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar férias.
                </div>
            `;
        }
    }
    
    // ========================================
    // EPIS RETROATIVOS
    // ========================================
    
    async function carregarEpisRetroativos(funcionarioId) {
        const lista = document.getElementById('lista_epis_retroativos');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando EPIs retroativos...
            </div>
        `;
        
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/epis-retroativos`);
            const data = await response.json();
            
            if (!data.success || data.epis.length === 0) {
                lista.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhum EPI retroativo anexado ainda.
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.epis.forEach(function(epi) {
                const dataFormatada = formatarDataBR(epi.created_at);
                const dataEpi = formatarDataSemHora(epi.data);
                
                html += `
                    <div class="card mb-3" id="epi-retroativo-${epi.id}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-hard-hat mr-2 text-primary"></i>
                                        EPI - ${dataEpi}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Anexado em ${dataFormatada}
                                    </small>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/epi-retroativo/${epi.id}/download" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button onclick="excluirDocumento('epi-retroativo', ${epi.id})" 
                                                class="btn btn-outline-danger btn-sm"
                                                title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            lista.innerHTML = html;
            
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar EPIs retroativos.
                </div>
            `;
        }
    }
    
    // ========================================
    // FUNÇÕES PARA DÉCIMO TERCEIRO
    // ========================================
    
    async function carregarDecimo(funcionarioId) {
        const lista = document.getElementById('lista_decimo');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando décimo terceiro...
            </div>
        `;
        
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/decimo`);
            const data = await response.json();
            
            if (!data.success || data.decimo.length === 0) {
                lista.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhum décimo terceiro anexado ainda.
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.decimo.forEach(function(decimo) {
                const dataFormatada = formatarDataBR(decimo.created_at);
                const parcelaTexto = {
                    '1': '1ª Parcela',
                    '2': '2ª Parcela',
                    'unica': 'Parcela Única'
                }[decimo.parcela] || decimo.parcela;
                
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-gift mr-2 text-primary"></i>
                                        13º Salário ${decimo.ano_referencia} - ${parcelaTexto}
                                    </h6>
                                    ${decimo.valor_bruto ? `<small class="text-success"><i class="fas fa-dollar-sign mr-1"></i>R$ ${formatarValorParaExibicao(decimo.valor_bruto)}</small><br>` : ''}
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Anexado em ${dataFormatada}
                                    </small>
                                    ${decimo.observacoes ? `<br><small class="text-info"><i class="fas fa-comment mr-1"></i>${decimo.observacoes}</small>` : ''}
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/decimo/${decimo.id}/download" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button onclick="excluirDocumento('decimo', ${decimo.id})" 
                                                class="btn btn-outline-danger btn-sm"
                                                title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            lista.innerHTML = html;
            
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar décimo terceiro.
                </div>
            `;
        }
    }

    // ========================================
    // FUNÇÕES PARA RESCISÃO
    // ========================================
    
    function atualizarEstadoFormularioRescisao() {
        const form = document.getElementById('form-rescisao');
        const aviso = document.getElementById('rescisao-bloqueado-alert');
        if (!form || !funcionarioSelecionado) return;

        const statusAtual = funcionarioSelecionado.status;
        const deveBloquear = temRescisaoParaFuncionarioSelecionado && statusAtual !== 'trabalhando';

        const campos = form.querySelectorAll('input, select, textarea, button');
        campos.forEach(function(el){ el.disabled = deveBloquear; });
        form.classList.toggle('form-section-disabled', deveBloquear);
        if (aviso) { aviso.style.display = deveBloquear ? 'block' : 'none'; }
    }

    async function carregarRescisao(funcionarioId) {
        const lista = document.getElementById('lista_rescisao');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando rescisão...
            </div>
        `;
        
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/rescisao`);
            const data = await response.json();
            temRescisaoParaFuncionarioSelecionado = Array.isArray(data.rescisao) && data.rescisao.length > 0;
            
            if (!data.success || data.rescisao.length === 0) {
                lista.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhuma rescisão anexada ainda.
                    </div>
                `;
                atualizarEstadoFormularioRescisao();
                return;
            }
            
            let html = '';
            data.rescisao.forEach(function(rescisao) {
                const dataFormatada = formatarDataBR(rescisao.created_at);
                // Usar formatarDataSemHora para evitar problema de timezone
                const dataRescisao = formatarDataSemHora(rescisao.data_rescisao);
                const tipoTexto = {
                    'demissao_sem_justa_causa': 'Demissão sem Justa Causa',
                    'demissao_justa_causa': 'Demissão com Justa Causa',
                    'pedido_demissao': 'Pedido de Demissão',
                    'acordo_mutuo': 'Acordo Mútuo',
                    'aposentadoria': 'Aposentadoria',
                    'fim_contrato': 'Fim de Contrato',
                    'outros': 'Outros'
                }[rescisao.tipo_rescisao] || rescisao.tipo_rescisao;
                
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-handshake mr-2 text-primary"></i>
                                        ${tipoTexto}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Data da Rescisão: ${dataRescisao}
                                    </small>
                                    ${rescisao.valor_total ? `<br><small class="text-success"><i class="fas fa-dollar-sign mr-1"></i>R$ ${formatarValorParaExibicao(rescisao.valor_total)}</small>` : ''}
                                    <br><small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Anexado em ${dataFormatada}
                                    </small>
                                    ${rescisao.observacoes ? `<br><small class="text-info"><i class="fas fa-comment mr-1"></i>${rescisao.observacoes}</small>` : ''}
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/rescisao/${rescisao.id}/download" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button onclick="excluirDocumento('rescisao', ${rescisao.id})" 
                                                class="btn btn-outline-danger btn-sm"
                                                title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            lista.innerHTML = html;
            atualizarEstadoFormularioRescisao();
            
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar rescisão.
                </div>
            `;
        }
    }

    // ========================================
    // CARREGAR FREQUÊNCIA
    // ========================================
    async function carregarFrequencia(funcionarioId) {
        const lista = document.getElementById('lista_frequencia');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando frequência...
            </div>
        `;
        
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/frequencia`);
            const data = await response.json();
            
            if (!data.success || data.frequencia.length === 0) {
                lista.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhuma frequência anexada ainda.
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.frequencia.forEach(function(freq) {
                const dataFormatada = formatarDataBR(freq.created_at);
                
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-calendar-check mr-2 text-primary"></i>
                                        Frequência - ${freq.mes_ano}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Anexado em ${dataFormatada}
                                    </small>
                                    ${freq.observacoes ? `<br><small class="text-muted"><strong>Obs:</strong> ${freq.observacoes}</small>` : ''}
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/frequencia/${freq.id}/download" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button onclick="excluirDocumento('frequencia', ${freq.id})" 
                                                class="btn btn-outline-danger btn-sm"
                                                title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            lista.innerHTML = html;
            
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar frequência.
                </div>
            `;
        }
    }

    // ========================================
    // CARREGAR CERTIFICADOS
    // ========================================
    async function carregarCertificado(funcionarioId) {
        const lista = document.getElementById('lista_certificado');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando certificados...
            </div>
        `;
        
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/certificado`);
            const data = await response.json();
            
            if (!data.success || data.certificado.length === 0) {
                lista.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhum certificado anexado ainda.
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.certificado.forEach(function(cert) {
                const dataFormatada = formatarDataBR(cert.created_at);
                const dataEmissao = formatarDataSemHora(cert.data_emissao);
                const dataValidade = cert.data_validade ? formatarDataSemHora(cert.data_validade) : null;
                
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-certificate mr-2 text-primary"></i>
                                        ${cert.nome_certificado}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Emissão: ${dataEmissao}
                                        ${dataValidade ? ` | Validade: ${dataValidade}` : ''}
                                    </small>
                                    <br><small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Anexado em ${dataFormatada}
                                    </small>
                                    ${cert.observacoes ? `<br><small class="text-muted"><strong>Obs:</strong> ${cert.observacoes}</small>` : ''}
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/certificado/${cert.id}/download" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button onclick="excluirDocumento('certificado', ${cert.id})" 
                                                class="btn btn-outline-danger btn-sm"
                                                title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            lista.innerHTML = html;
            
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar certificados.
                </div>
            `;
        }
    }

    // ========================================
    // CARREGAR TERMOS ADITIVOS
    // ========================================
    async function carregarTermoAditivo(funcionarioId) {
        const lista = document.getElementById('lista_termo_aditivo');
        if (!lista) return;
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando termos aditivos...
            </div>
        `;
        try {
            const resp = await fetch(`/documentos-dp/funcionario/${funcionarioId}/termo-aditivo`);
            const data = await resp.json();
            if (!data.success || (data.termos?.length||0) === 0) {
                lista.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhum termo aditivo anexado ainda.
                    </div>
                `;
                return;
            }
            let html = '';
            data.termos.forEach(t => {
                const dataTermo = t.data_termo ? formatarDataSemHora(t.data_termo) : '-';
                const criado = formatarDataBR(t.created_at);
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1"><i class="fas fa-file-contract mr-2 text-primary"></i>${t.nome_termo}</h6>
                                    <small class="text-muted"><i class="fas fa-calendar mr-1"></i> Data do termo: ${dataTermo}</small>
                                    <br><small class="text-muted"><i class="fas fa-clock mr-1"></i> Anexado em ${criado}</small>
                                    ${t.observacoes ? `<br><small class="text-muted"><strong>Obs:</strong> ${t.observacoes}</small>` : ''}
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/termo-aditivo/${t.id}/download" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                        <button onclick="excluirDocumento('termo-aditivo', ${t.id})" class="btn btn-outline-danger btn-sm" title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
            lista.innerHTML = html;
        } catch (e) {
            lista.innerHTML = `<div class="alert alert-danger"><i class=\"fas fa-exclamation-triangle mr-2\"></i> Erro ao carregar termos aditivos.</div>`;
        }
    }

    // ========================================
    // CARREGAR ASOS
    // ========================================
    async function carregarAsos(funcionarioId) {
        const lista = document.getElementById('lista_asos');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando ASOS...
            </div>
        `;
        
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/asos`);
            const data = await response.json();
            
            if (!data.success || data.asos.length === 0) {
                lista.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhum ASOS anexado ainda.
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.asos.forEach(function(asos) {
                const dataFormatada = formatarDataBR(asos.created_at);
                const dataExame = formatarDataSemHora(asos.data_exame);
                const tipoTexto = {
                    'admissional': 'Admissional',
                    'periodico': 'Periódico',
                    'mudanca_funcao': 'Mudança de Função',
                    'retorno_trabalho': 'Retorno ao Trabalho',
                    'demissional': 'Demissional'
                }[asos.tipo_exame] || asos.tipo_exame;
                
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-heartbeat mr-2 text-primary"></i>
                                        ASOS - ${tipoTexto}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Data do Exame: ${dataExame}
                                    </small>
                                    ${asos.medico_responsavel ? `<br><small class="text-muted"><i class="fas fa-user-md mr-1"></i>Médico: ${asos.medico_responsavel}</small>` : ''}
                                    <br><small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Anexado em ${dataFormatada}
                                    </small>
                                    ${asos.observacoes ? `<br><small class="text-muted"><strong>Obs:</strong> ${asos.observacoes}</small>` : ''}
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/asos/${asos.id}/download" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button onclick="excluirDocumento('asos', ${asos.id})" 
                                                class="btn btn-outline-danger btn-sm"
                                                title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            lista.innerHTML = html;
            
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar ASOS.
                </div>
            `;
        }
    }

    // ========================================
    // APLICAR MÁSCARAS DE MOEDA
    // ========================================
    
    // Aplicar máscaras nos campos de valor quando o documento carrega
    document.addEventListener('DOMContentLoaded', function() {
        // Garantir que o modal de OS esteja diretamente sob <body> para evitar z-index/stacking-context bugs
        const modalEl = document.getElementById('modalVerOS');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        // Campo de valor bruto do décimo terceiro
        const valorBrutoDecimo = document.getElementById('valor_bruto');
        if (valorBrutoDecimo) {
            aplicarMascaraMoeda(valorBrutoDecimo);
        }
        
        // Campo de valor total da rescisão
        const valorTotalRescisao = document.getElementById('valor_total');
        if (valorTotalRescisao) {
            aplicarMascaraMoeda(valorTotalRescisao);
        }
        
        // ========================================
        // UPLOAD DE FOTO DO FUNCIONÁRIO
        // ========================================
        const uploadFotoInput = document.getElementById('upload_foto_funcionario');
        const btnRemoverFoto = document.getElementById('btn_remover_foto_funcionario');
        
        if (uploadFotoInput) {
            uploadFotoInput.addEventListener('change', async function(e) {
                if (!funcionarioSelecionado) {
                    Swal.fire('Erro', 'Selecione um funcionário primeiro', 'error');
                    return;
                }
                
                const file = e.target.files[0];
                if (!file) return;
                
                // Validar tipo de arquivo
                if (!file.type.startsWith('image/')) {
                    Swal.fire('Erro', 'Selecione apenas arquivos de imagem', 'error');
                    return;
                }
                
                // Validar tamanho (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire('Erro', 'A imagem deve ter no máximo 5MB', 'error');
                    return;
                }
                
                const formData = new FormData();
                formData.append('foto', file);
                formData.append('funcionario_id', funcionarioSelecionado.id);
                
                try {
                    Swal.fire({
                        title: 'Enviando foto...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    const response = await fetch('/documentos-dp/funcionario/upload-foto', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        document.getElementById('f_foto').src = data.foto_url;
                        btnRemoverFoto.style.display = 'block';
                        funcionarioSelecionado.foto_path = data.foto_path;
                        Swal.fire('Sucesso', 'Foto atualizada com sucesso!', 'success');
                    } else {
                        Swal.fire('Erro', data.message || 'Erro ao enviar foto', 'error');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    Swal.fire('Erro', 'Erro ao enviar foto', 'error');
                }
                
                // Limpar input
                e.target.value = '';
            });
        }
        
        if (btnRemoverFoto) {
            btnRemoverFoto.addEventListener('click', async function() {
                if (!funcionarioSelecionado) return;
                
                const result = await Swal.fire({
                    title: 'Remover foto?',
                    text: 'Deseja realmente remover a foto do funcionário?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, remover',
                    cancelButtonText: 'Cancelar'
                });
                
                if (!result.isConfirmed) return;
                
                try {
                    Swal.fire({
                        title: 'Removendo foto...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    const response = await fetch('/documentos-dp/funcionario/remover-foto', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ funcionario_id: funcionarioSelecionado.id })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const fotoPlaceholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 120'%3E%3Crect fill='%23e2e8f0' width='100' height='120'/%3E%3Ccircle cx='50' cy='40' r='25' fill='%2394a3b8'/%3E%3Cellipse cx='50' cy='110' rx='40' ry='35' fill='%2394a3b8'/%3E%3C/svg%3E";
                        document.getElementById('f_foto').src = fotoPlaceholder;
                        btnRemoverFoto.style.display = 'none';
                        funcionarioSelecionado.foto_path = null;
                        Swal.fire('Sucesso', 'Foto removida com sucesso!', 'success');
                    } else {
                        Swal.fire('Erro', data.message || 'Erro ao remover foto', 'error');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    Swal.fire('Erro', 'Erro ao remover foto', 'error');
                }
            });
        }
    });

    // ========================================
    // EVENT LISTENERS PARA OS FORMULÁRIOS
    // ========================================
    
    // Contra Cheque
    document.getElementById('form-contra-cheque').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const formData = new FormData(this);
        formData.append('funcionario_id', funcionarioSelecionado.id);
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/documentos-dp/contra-cheque/store', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.reset();
                carregarContraCheques(funcionarioSelecionado.id);
                Swal.fire('Sucesso!', 'Contra cheque anexado com sucesso!', 'success');
            } else {
                throw new Error(data.message || 'Erro ao anexar contra cheque');
            }
        } catch (error) {
            Swal.fire('Erro!', error.message || 'Erro ao anexar contra cheque', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // Férias
    document.getElementById('form-ferias').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const formData = new FormData(this);
        formData.append('funcionario_id', funcionarioSelecionado.id);
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/documentos-dp/ferias/store', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.reset();
                carregarFerias(funcionarioSelecionado.id);
                Swal.fire('Sucesso!', 'Férias anexadas com sucesso!', 'success');
            } else {
                throw new Error(data.message || 'Erro ao anexar férias');
            }
        } catch (error) {
            Swal.fire('Erro!', error.message || 'Erro ao anexar férias', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // EPI Retroativo
    document.getElementById('form-epi-retroativo').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const formData = new FormData(this);
        formData.append('funcionario_id', funcionarioSelecionado.id);
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/documentos-dp/epi-retroativo/store', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.reset();
                // Atualização otimista: inserir o item sem recarregar toda a lista
                if (data.epi) {
                    const lista = document.getElementById('lista_epis_retroativos');
                    const dataFormatada = formatarDataBR(data.epi.created_at);
                    const dataEpi = formatarDataSemHora(data.epi.data);
                    const item = document.createElement('div');
                    item.className = 'card mb-3';
                    item.innerHTML = `
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-hard-hat mr-2 text-primary"></i>
                                        EPI - ${dataEpi}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Anexado em ${dataFormatada}
                                    </small>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group" role="group">
                                        <a href="/documentos-dp/epi-retroativo/${data.epi.id}/download" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <button onclick="excluirDocumento('epi-retroativo', ${data.epi.id})" 
                                                class="btn btn-outline-danger btn-sm"
                                                title="Excluir documento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    // Se a lista estava com alerta "nenhum", limpa antes
                    if (lista.querySelector('.alert')) { lista.innerHTML = ''; }
                    lista.prepend(item);
                } else {
                    carregarEpisRetroativos(funcionarioSelecionado.id);
                }
                Swal.fire('Sucesso!', 'EPI retroativo anexado com sucesso!', 'success');
            } else {
                throw new Error(data.message || 'Erro ao anexar EPI retroativo');
            }
        } catch (error) {
            Swal.fire('Erro!', error.message || 'Erro ao anexar EPI retroativo', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // Décimo Terceiro
    document.getElementById('form-decimo').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const formData = new FormData(this);
        formData.append('funcionario_id', funcionarioSelecionado.id);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Converter valor formatado para número decimal antes de enviar
        const valorBrutoInput = document.getElementById('valor_bruto');
        if (valorBrutoInput.value) {
            // Remove pontos de milhares e converte vírgula para ponto decimal
            const valorNumerico = valorBrutoInput.value.replace(/\./g, '').replace(',', '.');
            formData.set('valor_bruto', valorNumerico);
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/documentos-dp/decimo/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if (!response.ok) {
                const text = await response.text();
                throw new Error(text || 'Erro interno do servidor');
            }
            
            const ctDec = response.headers.get('content-type') || '';
            let data;
            if (ctDec.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                throw new Error(text || 'Erro interno do servidor');
            }
            
            if (data.success) {
                this.reset();
                carregarDecimo(funcionarioSelecionado.id);
                Swal.fire('Sucesso!', 'Décimo terceiro anexado com sucesso!', 'success');
            } else {
                throw new Error(data.message || 'Erro ao anexar décimo terceiro');
            }
        } catch (error) {
            Swal.fire('Erro!', error.message || 'Erro ao anexar décimo terceiro', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // Rescisão
    document.getElementById('form-rescisao').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const formData = new FormData(this);
        formData.append('funcionario_id', funcionarioSelecionado.id);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Converter valor formatado para número decimal antes de enviar
        const valorTotalInput = document.getElementById('valor_total');
        if (valorTotalInput.value) {
            // Remove pontos de milhares e converte vírgula para ponto decimal
            const valorNumerico = valorTotalInput.value.replace(/\./g, '').replace(',', '.');
            formData.set('valor_total', valorNumerico);
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/documentos-dp/rescisao/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if (!response.ok) {
                const text = await response.text();
                throw new Error(text || 'Erro interno do servidor');
            }
            
            const ctRes = response.headers.get('content-type') || '';
            let data;
            if (ctRes.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                throw new Error(text || 'Erro interno do servidor');
            }
            
            if (data.success) {
                this.reset();
                carregarRescisao(funcionarioSelecionado.id);
                Swal.fire('Sucesso!', 'Rescisão anexada com sucesso!', 'success');
                // Como acabou de anexar, marca como existente para bloquear quando necessário
                temRescisaoParaFuncionarioSelecionado = true;
                atualizarEstadoFormularioRescisao();
            } else {
                throw new Error(data.message || 'Erro ao anexar rescisão');
            }
        } catch (error) {
            Swal.fire('Erro!', error.message || 'Erro ao anexar rescisão', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // Frequência
    document.getElementById('form-frequencia').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const formData = new FormData(this);
        formData.append('funcionario_id', funcionarioSelecionado.id);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/documentos-dp/frequencia/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if (!response.ok) {
                const text = await response.text();
                throw new Error(text || 'Erro interno do servidor');
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.reset();
                carregarFrequencia(funcionarioSelecionado.id);
                Swal.fire('Sucesso!', 'Frequência anexada com sucesso!', 'success');
            } else {
                throw new Error(data.message || 'Erro ao anexar frequência');
            }
        } catch (error) {
            Swal.fire('Erro!', error.message || 'Erro ao anexar frequência', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // Certificado
    document.getElementById('form-certificado').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const formData = new FormData(this);
        formData.append('funcionario_id', funcionarioSelecionado.id);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/documentos-dp/certificado/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if (!response.ok) {
                const text = await response.text();
                throw new Error(text || 'Erro interno do servidor');
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.reset();
                carregarCertificado(funcionarioSelecionado.id);
                Swal.fire('Sucesso!', 'Certificado anexado com sucesso!', 'success');
            } else {
                throw new Error(data.message || 'Erro ao anexar certificado');
            }
        } catch (error) {
            Swal.fire('Erro!', error.message || 'Erro ao anexar certificado', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Termo Aditivo
    const formTermo = document.getElementById('form-termo-aditivo');
    if (formTermo) {
        formTermo.addEventListener('submit', async function(e){
            e.preventDefault();
            if (!funcionarioSelecionado) { Swal.fire('Aviso!','Selecione um funcionário','warning'); return; }
            const fd = new FormData(this);
            fd.append('funcionario_id', funcionarioSelecionado.id);
            try {
                const response = await fetch('/documentos-dp/termo-aditivo/store', { method:'POST', headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: fd });
                const data = await response.json();
                if (!data.success) throw new Error(data.message || 'Erro ao anexar termo aditivo');
                this.reset();
                carregarTermoAditivo(funcionarioSelecionado.id);
                Swal.fire('Sucesso!', 'Termo aditivo anexado com sucesso!', 'success');
            } catch (err) {
                Swal.fire('Erro!', err.message || 'Erro ao anexar termo aditivo', 'error');
            }
        });
    }
    
    // ASOS
    document.getElementById('form-asos').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const formData = new FormData(this);
        formData.append('funcionario_id', funcionarioSelecionado.id);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/documentos-dp/asos/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if (!response.ok) {
                const text = await response.text();
                throw new Error(text || 'Erro interno do servidor');
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.reset();
                carregarAsos(funcionarioSelecionado.id);
                Swal.fire('Sucesso!', 'ASOS anexado com sucesso!', 'success');
            } else {
                throw new Error(data.message || 'Erro ao anexar ASOS');
            }
        } catch (error) {
            Swal.fire('Erro!', error.message || 'Erro ao anexar ASOS', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // ========================================
    // FUNÇÃO PARA EXCLUIR DOCUMENTOS
    // ========================================
    window.excluirDocumento = async function(tipo, id) {
        // Confirmação antes de excluir
        const result = await Swal.fire({
            title: 'Confirmar Exclusão',
            text: 'Tem certeza que deseja excluir este documento? Esta ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Sim, excluir',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            // Mostrar loading
            Swal.fire({
                title: 'Excluindo documento...',
                text: 'Por favor, aguarde.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch(`/documentos-dp/${tipo}/${id}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Erro na requisição');
            }

            const data = await response.json();

            if (data.success) {
                // Recarregar a lista do tipo de documento correspondente
                if (funcionarioSelecionado) {
                    switch(tipo) {
                        case 'frequencia':
                            carregarFrequencia(funcionarioSelecionado.id);
                            break;
                        case 'certificado':
                            carregarCertificado(funcionarioSelecionado.id);
                            break;
                        case 'asos':
                            carregarAsos(funcionarioSelecionado.id);
                            break;
                        case 'rescisao':
                            temRescisaoParaFuncionarioSelecionado = false;
                            carregarRescisao(funcionarioSelecionado.id);
                            break;
                        case 'decimo':
                            carregarDecimo(funcionarioSelecionado.id);
                            break;
                        case 'ferias':
                            carregarFerias(funcionarioSelecionado.id);
                            break;
                        case 'contra-cheque':
                            carregarContraCheques(funcionarioSelecionado.id);
                            break;
                        case 'atestado':
                            carregarAtestados(funcionarioSelecionado.id);
                            break;
                        case 'advertencia':
                            carregarAdvertencias(funcionarioSelecionado.id);
                            break;
                        case 'documento':
                            carregarDocumentos(funcionarioSelecionado.id);
                            break;
                        case 'epi-retroativo':
                            // Remover o item do DOM imediatamente
                            const item = document.getElementById(`epi-retroativo-${id}`);
                            if (item && item.parentNode) {
                                item.parentNode.removeChild(item);
                            } else {
                                // Fallback: recarregar lista
                                carregarEpisRetroativos(funcionarioSelecionado.id);
                            }
                            break;
                        case 'termo-aditivo':
                            carregarTermoAditivo(funcionarioSelecionado.id);
                            break;
                    }
                }

                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Documento excluído com sucesso.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                throw new Error(data.message || 'Erro ao excluir documento');
            }

        } catch (error) {
            Swal.fire({
                title: 'Erro!',
                text: error.message || 'Erro ao excluir documento. Tente novamente.',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
        }
    };

    // Função para gerar arquivo completo do funcionário (ZIP)
    window.gerarArquivoCompleto = function() {
        if (!funcionarioSelecionado) {
            Swal.fire('Aviso!', 'Nenhum funcionário selecionado', 'warning');
            return;
        }
        
        // Confirmação com informações sobre o que será baixado
        Swal.fire({
            title: 'Gerar Arquivo Completo?',
            html: `
                <div class="text-left">
                    <p><strong>Funcionário:</strong> ${funcionarioSelecionado.nome}</p>
                    <p><strong>CPF:</strong> ${funcionarioSelecionado.cpf || 'Não informado'}</p>
                    <p><strong>Função:</strong> ${funcionarioSelecionado.funcao || 'Não informado'}</p>
                    <hr>
                    <p><i class="fas fa-info-circle text-info mr-2"></i>Será gerado um arquivo ZIP contendo:</p>
                    <ul class="text-left">
                        <li>📄 Documentos Gerais</li>
                        <li>🏥 Atestados</li>
                        <li>⚠️ Advertências</li>
                        <li>💰 Décimo Terceiro</li>
                        <li>📋 Rescisão</li>
                        <li>💵 Contra-cheques</li>
                        <li>🏖️ Férias</li>
                        <li>📅 Frequência</li>
                        <li>🏆 Certificados</li>
                        <li>💗 ASOS</li>
                        <li>📊 Resumo do funcionário</li>
                    </ul>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-download mr-2"></i>Baixar ZIP',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Gerando arquivo...',
                    html: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br><br>Coletando todos os documentos do funcionário...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });
                
                // Redirecionar para download
                const url = `/documentos-dp/funcionario/${funcionarioSelecionado.id}/arquivo-completo`;
                window.location.href = url;
                
                // Fechar loading após um tempo
                setTimeout(() => {
                    Swal.close();
                    Swal.fire({
                        title: 'Download Iniciado!',
                        text: 'O arquivo ZIP está sendo baixado...',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 2000);
            }
        });
    };

    // ========================================
    // CARREGAR ORDENS DE SERVIÇO DO FUNCIONÁRIO
    // ========================================
    async function carregarOS(funcionarioId) {
        const corpo = document.getElementById('lista_os_funcionario');
        if (!corpo) return;
        corpo.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="loading-spinner mr-2"></div> Carregando O.S...
                </td>
            </tr>`;

        try {
            const resp = await fetch(`/api/ordens-servico/por-funcionario/${funcionarioId}`);
            const json = await resp.json();
            if (!json.success) throw new Error('Falha ao carregar O.S.');

            if (!json.data || json.data.length === 0) {
                corpo.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Nenhuma O.S. encontrada para este funcionário.</td></tr>`;
                return;
            }

            corpo.innerHTML = json.data.map(os => `
                <tr>
                    <td>${os.numero_os}</td>
                    <td>${formatarDataSemHora(os.data_os)}</td>
                    <td>${[os.cidade, os.estado].filter(Boolean).join(' / ')}</td>
                    <td>${os.telefone || ''}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="verOS(${os.id})">
                            <i class="fas fa-eye mr-1"></i> Ver
                        </button>
                     </td>
                </tr>
            `).join('');
        } catch (e) {
            corpo.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Erro ao carregar O.S.</td></tr>`;
        }
    }

    // Abrir modal e renderizar OS
    async function verOS(id) {
        const container = document.getElementById('conteudoOS');
        container.innerHTML = `<div class=\"text-center text-muted py-4\">Carregando...</div>`;
        // Fechar qualquer modal aberto residual e remover backdrops órfãos
        try { $('.modal').modal('hide'); } catch (e) {}
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
        $('#modalVerOS').modal({backdrop: 'static', keyboard: true}).modal('show');
        try {
            const resp = await fetch(`/api/ordens-servico/${id}`);
            const json = await resp.json();
            if (!json.success) throw new Error(json.message || 'Erro ao buscar O.S.');
            const os = json.data;
            // guardar id atual para o botão Imprimir do modal usar o layout correto
            window._osIdAtual = id;
            container.innerHTML = `
                <div class=\"row\">
                  <div class=\"col-md-6\">
                    <p><strong>Nº O.S.:</strong> ${os.numero_os}</p>
                    <p><strong>Data:</strong> ${formatarDataSemHora(os.data_os)}</p>
                    <p><strong>Funcionário:</strong> ${os.funcionario || ''}</p>
                    <p><strong>Descrição:</strong><br>${os.descricao || ''}</p>
                  </div>
                  <div class=\"col-md-6\">
                    <p><strong>Endereço:</strong> ${os.endereco || ''}</p>
                    <p><strong>Cidade/UF:</strong> ${[os.cidade, os.estado].filter(Boolean).join(' / ')}</p>
                    <p><strong>CEP:</strong> ${os.cep || ''}</p>
                    <p><strong>Telefone:</strong> ${os.telefone || ''}</p>
                    <p><strong>CPF/CNPJ:</strong> ${os.cpf_cnpj || ''}</p>
                  </div>
                </div>
                ${os.observacoes ? `<hr><p><strong>Observações:</strong><br>${os.observacoes}</p>` : ''}
            `;
        } catch (e) {
            container.innerHTML = `<div class=\"alert alert-danger\">${e.message || 'Erro ao carregar O.S.'}</div>`;
        }
    }
    // Função de impressão com layout padrão da O.S.
    async function imprimirOS(id){
      try {
        const resp = await fetch(`/api/ordens-servico/${id}`);
        const json = await resp.json();
        if (!json.success) throw new Error(json.message || 'Erro ao carregar O.S.');
        const os = json.data;

        const dataBR = new Date(os.data_os).toLocaleDateString('pt-BR');
        const logoUrl = window.location.origin + '/img/brs-logo.png';
        
        // Função para formatar CPF/CNPJ
        function formatarCpfCnpj(valor) {
          if (!valor) return '';
          const numeros = valor.replace(/\D/g, '');
          if (numeros.length === 11) {
            // CPF: 000.000.000-00
            return numeros.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
          } else if (numeros.length === 14) {
            // CNPJ: 00.000.000/0000-00
            return numeros.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
          }
          return valor; // Retorna original se não for CPF nem CNPJ
        }
        
        const cpfCnpjFormatado = formatarCpfCnpj(os.funcionario_cpf || os.cpf_cnpj || '');

        const html = `<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Ordem de Serviço - ${os.numero_os}</title>
  <style>
    @page { 
      margin: 20mm 15mm; 
      size: A4;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Arial', sans-serif; color: #2c3e50; font-size: 9pt; line-height: 1.3; }
    .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #3498db; padding-bottom: 15px; margin-bottom: 25px; }
    .logo { max-height: 60px; max-width: 120px; }
    .header-info { text-align: right; }
    .titulo { font-size: 20pt; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
    .numero-os { font-size: 12pt; color: #3498db; font-weight: bold; }
    .data-emissao { font-size: 8pt; color: #7f8c8d; margin-top: 3px; }
    .secao { margin-bottom: 20px; }
    .secao-titulo { background: #ecf0f1; padding: 6px 10px; font-weight: bold; font-size: 10pt; color: #2c3e50; border-left: 4px solid #3498db; margin-bottom: 8px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
    .campo { margin-bottom: 8px; }
    .campo-label { font-weight: bold; color: #34495e; font-size: 8pt; margin-bottom: 2px; display: block; }
    .campo-valor { color: #2c3e50; font-size: 9pt; min-height: 16px; padding: 2px 0; }
    .descricao-box { padding: 8px 0; min-height: 40px; font-size: 9pt; line-height: 1.4; }
    .observacoes-box { padding: 8px 0; min-height: 30px; margin-top: 8px; font-size: 9pt; line-height: 1.4; }
    .footer { position: fixed; bottom: 15mm; left: 0; right: 0; text-align: center; font-size: 9pt; color: #95a5a6; border-top: 1px solid #ecf0f1; padding-top: 8px; }
    .separador-assinatura { margin-top: 80px; border-top: 2px solid #2c3e50; padding-top: 40px; }
    .assinatura { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 20px; }
    .assinatura-campo { text-align: center; border-top: 1px solid #2c3e50; padding-top: 8px; font-size: 9pt; }
  </style>
</head>
<body>
  <div class="header">
    <img src="${logoUrl}" alt="Logo" class="logo" />
    <div class="header-info">
      <div class="titulo">ORDEM DE SERVIÇO</div>
      <div class="numero-os">Nº ${os.numero_os || ''}</div>
      <div class="data-emissao">Emitida em: ${dataBR}</div>
    </div>
  </div>

  <div class="secao">
    <div class="secao-titulo">DADOS DO FUNCIONÁRIO</div>
    <div class="grid">
      <div class="campo">
        <span class="campo-label">Nome:</span>
        <div class="campo-valor">${os.funcionario || ''}</div>
      </div>
      <div class="campo">
        <span class="campo-label">CPF/CNPJ:</span>
        <div class="campo-valor">${cpfCnpjFormatado}</div>
      </div>
    </div>
  </div>

  <div class="secao">
    <div class="secao-titulo">TERMO DE RESPONSABILIDADE</div>
    <div style="padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; font-size: 9pt; line-height: 1.4; text-align: justify;">
      Conforme a cláusula sexta do contrato firmado entre empregador e empregado, fica designado o colaborador(a) acima identificado(a) para realizar as atividades no local informado abaixo.
    </div>
  </div>

  <div class="secao">
    <div class="secao-titulo">LOCALIZAÇÃO</div>
    <div class="grid">
      <div class="campo">
        <span class="campo-label">Endereço:</span>
        <div class="campo-valor">${os.endereco || ''}</div>
      </div>
      <div class="campo">
        <span class="campo-label">Cidade/UF:</span>
        <div class="campo-valor">${[os.cidade, os.estado].filter(Boolean).join(' / ')}</div>
      </div>
      <div class="campo">
        <span class="campo-label">CEP:</span>
        <div class="campo-valor">${os.cep || ''}</div>
      </div>
      <div class="campo">
        <span class="campo-label">Telefone:</span>
        <div class="campo-valor">${os.telefone || ''}</div>
      </div>
    </div>
  </div>

  <div class="secao">
    <div class="secao-titulo">DESCRIÇÃO DO SERVIÇO</div>
    <div class="descricao-box">${os.descricao || ''}</div>
    ${os.observacoes ? `
      <div class="secao-titulo" style="margin-top: 20px;">OBSERVAÇÕES</div>
      <div class="observacoes-box">${os.observacoes}</div>
    ` : ''}
  </div>

  <div class="separador-assinatura">
    <div class="assinatura">
      <div class="assinatura-campo"><strong>Assinatura do Gerente</strong></div>
      <div class="assinatura-campo"><strong>${os.funcionario || '_________________________'}</strong></div>
    </div>
  </div>

  <div class="footer">Sistema de Gestão - Ordem de Serviço gerada automaticamente</div>
</body>
</html>`;

        const win = window.open('', '_blank', 'width=900,height=700');
        if (!win) { alert('Permita pop-ups para imprimir.'); return; }
        win.document.open();
        win.document.write(html);
        win.document.close();
        win.focus();
        setTimeout(function(){ try { win.print(); } finally { win.close(); } }, 150);
      } catch(e) {
        alert(e.message || 'Erro ao imprimir O.S.');
      }
    }

    // Expor para escopo global
    window.verOS = verOS;
    window.imprimirOS = imprimirOS;
})();
</script>
@stop


