{{-- CONTROLE DE SAÍDA DE ENCARREGADOS - BRS SISTEMA --}}

@extends('adminlte::page')

@section('title', __('Controle de Saída de Encarregados'))

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-hard-hat text-primary mr-3"></i>
            {{ __('Controle de Saída de Encarregados') }}
        </h1>
        <p class="text-muted mt-1 mb-0">{{ __('Controle de saída de uniforme e EPI para os encarregados') }}</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Card do Formulário -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt mr-2"></i>
                Ficha de Entrega de EPI / Uniforme
            </h3>
        </div>
        <div class="card-body">
            <form id="formFichaEpi">
                <!-- Dados da Empresa (Cabeçalho) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="bg-light p-3 rounded border">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-muted mb-1">Empresa:</label>
                                    <p class="mb-0 font-weight-bold text-dark" id="empresaNome">{{ $empresa['nome'] ?? 'BRS SERVICOS E COMERCIO LTDA' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-muted mb-1">CNPJ:</label>
                                    <p class="mb-0 font-weight-bold text-dark" id="empresaCnpj">{{ $empresa['cnpj'] ?? '34.80.4.3/85/0-001-61' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Dados do Empregado -->
                <div class="row">
                    <!-- Foto do Funcionário -->
                    <div class="col-md-2 text-center" id="foto_funcionario_container">
                        <div class="form-group">
                            <label class="font-weight-bold d-block">
                                <i class="fas fa-camera mr-1"></i> Foto
                            </label>
                            <!-- Aguardando seleção de funcionário -->
                            <div id="foto_aguardando_box">
                                <div class="foto-placeholder-box mb-2">
                                    <i class="fas fa-user fa-4x text-muted"></i>
                                </div>
                                <small class="text-muted">Selecione um funcionário</small>
                            </div>
                            <!-- Foto existente -->
                            <div id="foto_existente_box" class="foto-funcionario-box" style="display: none;">
                                <img id="foto_funcionario" src="" alt="Foto do Funcionário" crossorigin="anonymous"
                                     class="img-thumbnail" style="width: 100px; height: 120px; object-fit: cover;">
                            </div>
                            <!-- Sem foto - opção de enviar -->
                            <div id="foto_upload_box" style="display: none;">
                                <div class="foto-placeholder-box mb-2">
                                    <i class="fas fa-user-circle fa-4x text-secondary"></i>
                                </div>
                                <label for="upload_foto_func" class="btn btn-sm btn-info" style="cursor: pointer;">
                                    <i class="fas fa-camera mr-1"></i> Tirar Foto
                                </label>
                                <input type="file" id="upload_foto_func" accept="image/*" capture="user" style="display: none;">
                            </div>
                            <!-- Enviando foto -->
                            <div id="foto_enviando_box" style="display: none;">
                                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                <small class="d-block mt-1">Enviando...</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5" id="col_empregado">
                        <div class="form-group">
                            <label for="funcionario_busca" class="font-weight-bold">
                                <i class="fas fa-user mr-1"></i> Empregado *
                            </label>
                            <input type="text" class="form-control" id="funcionario_busca" 
                                   placeholder="Digite pelo menos 3 letras do nome..." autocomplete="off">
                            <input type="hidden" id="funcionario_id" name="funcionario_id">
                            <input type="hidden" id="funcionario_foto_path" name="funcionario_foto_path">
                            <div id="funcionario_sugestoes" class="autocomplete-sugestoes"></div>
                        </div>
                    </div>
                    <div class="col-md-2" id="col_cargo">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-briefcase mr-1"></i> Cargo
                            </label>
                            <input type="text" class="form-control" id="funcionario_cargo" readonly 
                                   placeholder="Preenchido automaticamente">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-calendar mr-1"></i> Data de Admissão
                            </label>
                            <input type="text" class="form-control" id="funcionario_admissao" readonly 
                                   placeholder="Preenchido automaticamente">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="endereco" class="font-weight-bold">
                                <i class="fas fa-map-marker-alt mr-1"></i> Endereço *
                            </label>
                            <textarea class="form-control" id="endereco" name="endereco" rows="2" 
                                      placeholder="Digite o endereço completo..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="setor_id" class="font-weight-bold">
                                <i class="fas fa-building mr-1"></i> Setor *
                            </label>
                            <select class="form-control" id="setor_id" name="setor_id">
                                <option value="">Selecione o setor...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-hashtag mr-1"></i> Ficha Registro
                            </label>
                            <input type="text" class="form-control font-weight-bold text-primary" id="numero_ficha" readonly 
                                   placeholder="Gerado automaticamente">
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Seletor de Tipo (EPI ou Uniforme) -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="font-weight-bold mb-3">
                            <i class="fas fa-list mr-2"></i> Tipo de Entrega
                        </h5>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-primary active" id="labelTipoEpi">
                                <input type="radio" name="tipo_entrega" id="tipoEpi" value="epi" checked>
                                <i class="fas fa-hard-hat mr-1"></i> EPI
                            </label>
                            <label class="btn btn-outline-success" id="labelTipoUniforme">
                                <input type="radio" name="tipo_entrega" id="tipoUniforme" value="uniforme">
                                <i class="fas fa-tshirt mr-1"></i> Uniforme
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Tabela de EPI -->
                <div class="row" id="tabelaEpiContainer">
                    <div class="col-12">
                        <h5 class="font-weight-bold mb-3">
                            <i class="fas fa-hard-hat mr-2"></i> Itens de EPI
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tabelaEpi">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 12%;">Data Entrega</th>
                                        <th style="width: 8%;">Qtde</th>
                                        <th style="width: 25%;">Equipamento</th>
                                        <th style="width: 10%;">Nº do CA</th>
                                        <th style="width: 12%;">Foto Entrega</th>
                                        <th style="width: 12%;">Data Devolução</th>
                                        <th style="width: 15%;">Foto Devolução</th>
                                        <th style="width: 6%;">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="itensBodyEpi">
                                    <tr class="item-row-epi" data-index="0">
                                        <td>
                                            <input type="date" class="form-control form-control-sm" 
                                                   name="itens_epi[0][data_entrega]">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" 
                                                   name="itens_epi[0][quantidade]" min="1" value="1">
                                        </td>
                                        <td class="position-relative">
                                            <input type="text" class="form-control form-control-sm item-descricao-epi" 
                                                   name="itens_epi[0][equipamento]" placeholder="Digite 3 letras..." autocomplete="off">
                                            <div class="produto-sugestoes" data-index="0"></div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" 
                                                   name="itens_epi[0][ca]" placeholder="CA">
                                        </td>
                                        <td class="text-center foto-cell">
                                            <input type="hidden" name="itens_epi[0][foto_entrega]" class="foto-entrega-input">
                                            <button type="button" class="btn btn-info btn-sm btn-capturar-foto" data-tipo="entrega" data-tabela="epi" data-index="0">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                            <div class="foto-preview mt-1" style="display:none;">
                                                <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                                                <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="date" class="form-control form-control-sm" 
                                                   name="itens_epi[0][data_devolucao]">
                                        </td>
                                        <td class="text-center foto-cell">
                                            <input type="hidden" name="itens_epi[0][foto_devolucao]" class="foto-devolucao-input">
                                            <button type="button" class="btn btn-warning btn-sm btn-capturar-foto" data-tipo="devolucao" data-tabela="epi" data-index="0">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                            <div class="foto-preview mt-1" style="display:none;">
                                                <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                                                <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm btn-remover-item-epi" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" id="btnAdicionarItemEpi">
                            <i class="fas fa-plus mr-1"></i> Adicionar Item EPI
                        </button>
                    </div>
                </div>

                <!-- Tabela de Uniforme -->
                <div class="row" id="tabelaUniformeContainer" style="display: none;">
                    <div class="col-12">
                        <h5 class="font-weight-bold mb-3">
                            <i class="fas fa-tshirt mr-2"></i> Itens de Uniforme
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tabelaUniforme">
                                <thead class="bg-success text-white">
                                    <tr>
                                        <th style="width: 22%;">Descrição</th>
                                        <th style="width: 8%;">Tam.</th>
                                        <th style="width: 8%;">Qtde</th>
                                        <th style="width: 12%;">Data Entrega</th>
                                        <th style="width: 12%;">Foto Entrega</th>
                                        <th style="width: 12%;">Data Devolução</th>
                                        <th style="width: 14%;">Foto Devolução</th>
                                        <th style="width: 6%;">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="itensBodyUniforme">
                                    <tr class="item-row-uniforme" data-index="0">
                                        <td class="position-relative">
                                            <input type="text" class="form-control form-control-sm item-descricao-uniforme" 
                                                   name="itens_uniforme[0][descricao]" placeholder="Digite 3 letras..." autocomplete="off">
                                            <div class="produto-sugestoes" data-index="0"></div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" 
                                                   name="itens_uniforme[0][tamanho]" placeholder="M, G">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" 
                                                   name="itens_uniforme[0][quantidade]" min="1" value="1">
                                        </td>
                                        <td>
                                            <input type="date" class="form-control form-control-sm" 
                                                   name="itens_uniforme[0][data_entrega]">
                                        </td>
                                        <td class="text-center foto-cell">
                                            <input type="hidden" name="itens_uniforme[0][foto_entrega]" class="foto-entrega-input">
                                            <button type="button" class="btn btn-info btn-sm btn-capturar-foto" data-tipo="entrega" data-tabela="uniforme" data-index="0">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                            <div class="foto-preview mt-1" style="display:none;">
                                                <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                                                <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="date" class="form-control form-control-sm" 
                                                   name="itens_uniforme[0][data_devolucao]">
                                        </td>
                                        <td class="text-center foto-cell">
                                            <input type="hidden" name="itens_uniforme[0][foto_devolucao]" class="foto-devolucao-input">
                                            <button type="button" class="btn btn-warning btn-sm btn-capturar-foto" data-tipo="devolucao" data-tabela="uniforme" data-index="0">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                            <div class="foto-preview mt-1" style="display:none;">
                                                <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                                                <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm btn-remover-item-uniforme" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" id="btnAdicionarItemUniforme">
                            <i class="fas fa-plus mr-1"></i> Adicionar Item Uniforme
                        </button>
                    </div>
                </div>

                <hr>

                <!-- Botões -->
                <div class="row">
                    <div class="col-12 text-right">
                        <button type="button" class="btn btn-secondary mr-2" id="btnLimpar">
                            <i class="fas fa-eraser mr-1"></i> Limpar Formulário
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" id="btnSalvar">
                            <i class="fas fa-save mr-1"></i> <span id="btnSalvarTexto">Salvar Ficha de EPI</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Captura de Foto -->
<div class="modal fade" id="modalCapturarFoto" tabindex="-1" role="dialog" aria-labelledby="modalCapturarFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalCapturarFotoLabel">
                    <i class="fas fa-camera mr-2"></i> Capturar Foto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="cameraContainer">
                    <video id="videoCamera" autoplay playsinline style="width: 100%; max-width: 500px; border-radius: 8px; border: 2px solid #007bff;"></video>
                    <canvas id="canvasCaptura" style="display: none;"></canvas>
                </div>
                <div id="fotoCapturadaContainer" style="display: none;">
                    <img id="fotoCapturada" src="" style="width: 100%; max-width: 500px; border-radius: 8px; border: 2px solid #28a745;">
                </div>
                <div class="mt-3">
                    <p class="text-muted" id="instrucaoCamera">Posicione a câmera e clique em "Tirar Foto"</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnNovaFoto" style="display: none;">
                    <i class="fas fa-redo mr-1"></i> Tirar Outra
                </button>
                <button type="button" class="btn btn-primary" id="btnTirarFoto">
                    <i class="fas fa-camera mr-1"></i> Tirar Foto
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmarFoto" style="display: none;">
                    <i class="fas fa-check mr-1"></i> Confirmar Foto
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card-outline.card-primary {
        border-top: 3px solid #007bff;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .card-title {
        font-weight: 600;
        color: #495057;
    }

    /* Autocomplete */
    .autocomplete-sugestoes {
        position: absolute;
        z-index: 1000;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 250px;
        overflow-y: auto;
        width: calc(100% - 30px);
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .autocomplete-sugestoes .sugestao-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    
    .autocomplete-sugestoes .sugestao-item:hover {
        background-color: #f0f7ff;
    }
    
    .autocomplete-sugestoes .sugestao-item .nome {
        font-weight: bold;
        color: #333;
    }
    
    .autocomplete-sugestoes .sugestao-item .info {
        font-size: 12px;
        color: #666;
    }

    /* Autocomplete de Produtos */
    .produto-sugestoes {
        position: absolute;
        z-index: 9999;
        background: white;
        border: 1px solid #007bff;
        border-top: none;
        max-height: 180px;
        overflow-y: auto;
        width: 100%;
        left: 0;
        top: 100%;
        display: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        border-radius: 0 0 4px 4px;
    }
    
    .produto-sugestoes .produto-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        background: white;
    }
    
    .produto-sugestoes .produto-item:hover {
        background-color: #e7f3ff;
    }
    
    .produto-sugestoes .produto-item:last-child {
        border-bottom: none;
    }

    /* Garantir que a célula da tabela permita overflow */
    #tabelaItens td.position-relative {
        overflow: visible;
    }
    
    #tabelaEpi, #tabelaUniforme {
        overflow: visible;
    }
    
    .table-responsive {
        overflow: visible !important;
    }

    /* Tabela de itens */
    #tabelaEpi input, #tabelaUniforme input {
        border: 1px solid #ced4da;
    }
    
    #tabelaEpi input:focus, #tabelaUniforme input:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .item-row-epi:nth-child(even), .item-row-uniforme:nth-child(even) {
        background-color: #f8f9fa;
    }

    /* Botões de seleção de tipo */
    .btn-group-toggle .btn {
        padding: 10px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-group-toggle .btn.active {
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    
    .btn-group-toggle .btn-outline-primary.active {
        background-color: #007bff;
        color: white;
    }
    
    .btn-group-toggle .btn-outline-success.active {
        background-color: #28a745;
        color: white;
    }

    /* Tabelas compactas */
    #tabelaEpi .form-control-sm, #tabelaUniforme .form-control-sm {
        font-size: 12px;
        padding: 0.25rem 0.4rem;
    }
    
    #tabelaEpi th, #tabelaUniforme th {
        font-size: 12px;
        padding: 8px 5px;
        text-align: center;
    }
    
    #tabelaEpi td, #tabelaUniforme td {
        padding: 5px;
        vertical-align: middle;
    }

    /* Botões de captura de foto */
    .btn-capturar-foto {
        padding: 5px 10px;
        font-size: 14px;
    }
    
    .foto-cell {
        min-width: 80px;
    }
    
    .foto-preview {
        display: inline-block;
    }
    
    .foto-preview img {
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .foto-preview img:hover {
        transform: scale(1.1);
    }
    
    /* Modal de Câmera */
    #modalCapturarFoto .modal-body {
        background-color: #f8f9fa;
    }
    
    #videoCamera {
        background-color: #000;
    }
    
    .btn-xs {
        padding: 2px 5px;
        font-size: 10px;
    }
    
    .foto-funcionario-box {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 5px;
        background: #f8f9fa;
        display: inline-block;
    }
    
    .foto-funcionario-box img {
        border-radius: 5px;
    }
    
    .foto-placeholder-box {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 15px 10px;
        background: #f8f9fa;
        display: inline-block;
    }
    
    #foto_funcionario_container {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }
</style>
@stop

@section('js')
<!-- Face-api.js para reconhecimento facial -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    // Variáveis para reconhecimento facial
    let faceApiLoaded = false;
    let funcionarioFaceDescriptor = null;
    
    // Carregar modelos do face-api.js
    async function carregarModelosFaceApi() {
        try {
            console.log('Carregando modelos de reconhecimento facial...');
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models')
            ]);
            faceApiLoaded = true;
            console.log('Modelos de reconhecimento facial carregados!');
        } catch (error) {
            console.error('Erro ao carregar modelos de reconhecimento facial:', error);
            faceApiLoaded = false;
        }
    }
    
    // Extrair descritor facial de uma imagem
    async function extrairDescriptorFacial(imagemElement) {
        if (!faceApiLoaded) {
            console.warn('Modelos não carregados');
            return null;
        }
        
        try {
            const detection = await faceapi.detectSingleFace(imagemElement)
                .withFaceLandmarks()
                .withFaceDescriptor();
            
            if (detection) {
                return detection.descriptor;
            }
            return null;
        } catch (error) {
            console.error('Erro ao extrair descriptor facial:', error);
            return null;
        }
    }
    
    // Comparar duas faces
    function compararFaces(descriptor1, descriptor2) {
        if (!descriptor1 || !descriptor2) return null;
        
        const distancia = faceapi.euclideanDistance(descriptor1, descriptor2);
        // Quanto menor a distância, mais parecidas as faces
        // < 0.6 = mesma pessoa (geralmente)
        // < 0.5 = alta confiança
        return {
            distancia: distancia,
            match: distancia < 0.6,
            confianca: Math.max(0, Math.min(100, (1 - distancia) * 100)).toFixed(1)
        };
    }
    
    // Verificar se a foto tirada corresponde ao funcionário
    async function verificarFotoFuncionario(imagemCapturada) {
        if (!funcionarioSelecionado || !funcionarioSelecionado.foto_path) {
            console.log('Funcionário sem foto cadastrada - verificação ignorada');
            return { verificado: true, motivo: 'sem_foto_cadastrada' };
        }
        
        if (!faceApiLoaded) {
            console.warn('Modelos de face não carregados - verificação ignorada');
            return { verificado: true, motivo: 'modelos_nao_carregados' };
        }
        
        try {
            // Pegar descritor da foto cadastrada do funcionário
            const fotoFuncionario = document.getElementById('foto_funcionario');
            if (!fotoFuncionario || !fotoFuncionario.src || fotoFuncionario.src.includes('data:image/svg')) {
                return { verificado: true, motivo: 'foto_funcionario_invalida' };
            }
            
            // Extrair descriptor da foto do funcionário (apenas uma vez)
            if (!funcionarioFaceDescriptor) {
                funcionarioFaceDescriptor = await extrairDescriptorFacial(fotoFuncionario);
            }
            
            if (!funcionarioFaceDescriptor) {
                console.warn('Não foi possível detectar rosto na foto cadastrada');
                return { verificado: true, motivo: 'rosto_nao_detectado_cadastro' };
            }
            
            // Criar elemento de imagem temporário para a foto capturada
            const imgCapturada = new Image();
            imgCapturada.crossOrigin = 'anonymous';
            
            return new Promise((resolve) => {
                imgCapturada.onload = async () => {
                    console.log('Imagem capturada carregada, extraindo descritor...');
                    const descriptorCapturado = await extrairDescriptorFacial(imgCapturada);
                    
                    if (!descriptorCapturado) {
                        console.log('Rosto não detectado na foto capturada');
                        resolve({ verificado: false, motivo: 'rosto_nao_detectado_captura' });
                        return;
                    }
                    
                    console.log('Descritor extraído, comparando faces...');
                    const resultado = compararFaces(funcionarioFaceDescriptor, descriptorCapturado);
                    console.log('Resultado da comparação:', resultado);
                    
                    if (resultado.match) {
                        resolve({ 
                            verificado: true, 
                            motivo: 'match_ok',
                            confianca: resultado.confianca,
                            distancia: resultado.distancia
                        });
                    } else {
                        resolve({ 
                            verificado: false, 
                            motivo: 'faces_diferentes',
                            confianca: resultado.confianca,
                            distancia: resultado.distancia
                        });
                    }
                };
                
                imgCapturada.onerror = () => {
                    resolve({ verificado: true, motivo: 'erro_carregar_imagem' });
                };
                
                imgCapturada.src = imagemCapturada;
            });
        } catch (error) {
            console.error('Erro na verificação facial:', error);
            return { verificado: true, motivo: 'erro_verificacao' };
        }
    }
    
    // Iniciar carregamento dos modelos quando a página carregar
    $(document).ready(function() {
        carregarModelosFaceApi();
    });
</script>

<script>
    // Configurar o token CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    let itemIndexEpi = 0;
    let itemIndexUniforme = 0;
    let funcionarioSelecionado = null;
    let tipoAtual = 'epi';
    
    // Variáveis para captura de foto
    let cameraStream = null;
    let fotoAtual = {
        tipo: null,      // 'entrega' ou 'devolucao'
        tabela: null,    // 'epi' ou 'uniforme'
        index: null,     // índice da linha
        botao: null      // botão que iniciou a captura
    };

    $(document).ready(function() {
        console.log('Controle de Saída de Encarregados carregado');
        
        // Carregar setores
        carregarSetores();
        
        // Carregar próximo número de ficha
        carregarNumeroFicha();
        
        // Upload de foto do funcionário (pelo encarregado)
        $('#upload_foto_func').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            if (!funcionarioSelecionado) {
                Swal.fire('Erro', 'Selecione um funcionário primeiro', 'error');
                return;
            }
            
            // Validar tipo
            if (!file.type.startsWith('image/')) {
                Swal.fire('Erro', 'Selecione apenas arquivos de imagem', 'error');
                return;
            }
            
            // Validar tamanho (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('Erro', 'A imagem deve ter no máximo 5MB', 'error');
                return;
            }
            
            // Mostrar loading
            $('#foto_upload_box').hide();
            $('#foto_enviando_box').show();
            
            const formData = new FormData();
            formData.append('foto', file);
            formData.append('funcionario_id', funcionarioSelecionado.id);
            
            $.ajax({
                url: '/documentos-dp/funcionario/upload-foto',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Atualizar foto
                        $('#foto_funcionario').attr('src', response.foto_url);
                        $('#funcionario_foto_path').val(response.foto_path);
                        funcionarioSelecionado.foto_path = response.foto_path;
                        
                        // Mostrar foto
                        $('#foto_enviando_box').hide();
                        $('#foto_existente_box').show();
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Foto enviada!',
                            text: 'A foto do funcionário foi salva com sucesso.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        $('#foto_enviando_box').hide();
                        $('#foto_upload_box').show();
                        Swal.fire('Erro', response.message || 'Erro ao enviar foto', 'error');
                    }
                },
                error: function(xhr) {
                    $('#foto_enviando_box').hide();
                    $('#foto_upload_box').show();
                    Swal.fire('Erro', 'Erro ao enviar foto', 'error');
                }
            });
            
            // Limpar input
            e.target.value = '';
        });
        
        // Alternar entre EPI e Uniforme
        $('input[name="tipo_entrega"]').on('change', function() {
            tipoAtual = $(this).val();
            
            if (tipoAtual === 'epi') {
                $('#tabelaEpiContainer').show();
                $('#tabelaUniformeContainer').hide();
                $('#labelTipoEpi').addClass('active');
                $('#labelTipoUniforme').removeClass('active');
                $('#btnSalvarTexto').text('Salvar Ficha de EPI');
            } else {
                $('#tabelaEpiContainer').hide();
                $('#tabelaUniformeContainer').show();
                $('#labelTipoEpi').removeClass('active');
                $('#labelTipoUniforme').addClass('active');
                $('#btnSalvarTexto').text('Salvar Ficha de Uniforme');
            }
        });
        
        // Autocomplete de funcionários
        let timeoutBusca = null;
        $('#funcionario_busca').on('input', function() {
            const termo = $(this).val().trim();
            
            clearTimeout(timeoutBusca);
            
            if (termo.length < 3) {
                $('#funcionario_sugestoes').hide();
                return;
            }
            
            timeoutBusca = setTimeout(function() {
                buscarFuncionarios(termo);
            }, 300);
        });
        
        // Fechar sugestões ao clicar fora
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#funcionario_busca, #funcionario_sugestoes').length) {
                $('#funcionario_sugestoes').hide();
            }
            if (!$(e.target).closest('.item-descricao-epi, .item-descricao-uniforme, .produto-sugestoes').length) {
                $('.produto-sugestoes').hide();
            }
        });
        
        // Autocomplete de produtos para EPI
        let timeoutProduto = null;
        $(document).on('input', '.item-descricao-epi, .item-descricao-uniforme', function() {
            const input = $(this);
            const termo = input.val().trim();
            const container = input.siblings('.produto-sugestoes');
            
            clearTimeout(timeoutProduto);
            
            if (termo.length < 3) {
                container.hide();
                return;
            }
            
            timeoutProduto = setTimeout(function() {
                buscarProdutos(termo, input, container);
            }, 300);
        });
        
        // Adicionar item EPI
        $('#btnAdicionarItemEpi').on('click', function() {
            adicionarItemEpi();
        });
        
        // Adicionar item Uniforme
        $('#btnAdicionarItemUniforme').on('click', function() {
            adicionarItemUniforme();
        });
        
        // Remover item EPI
        $(document).on('click', '.btn-remover-item-epi', function() {
            $(this).closest('tr').remove();
            atualizarBotoesRemoverEpi();
        });
        
        // Remover item Uniforme
        $(document).on('click', '.btn-remover-item-uniforme', function() {
            $(this).closest('tr').remove();
            atualizarBotoesRemoverUniforme();
        });
        
        // Capturar foto - abrir câmera
        $(document).on('click', '.btn-capturar-foto', function() {
            fotoAtual.tipo = $(this).data('tipo');
            fotoAtual.tabela = $(this).data('tabela');
            fotoAtual.index = $(this).data('index');
            fotoAtual.botao = $(this);
            abrirCamera();
        });
        
        // Tirar foto
        $('#btnTirarFoto').on('click', function() {
            tirarFoto();
        });
        
        // Nova foto
        $('#btnNovaFoto').on('click', function() {
            voltarParaCamera();
        });
        
        // Confirmar foto
        $('#btnConfirmarFoto').on('click', function() {
            confirmarFoto();
        });
        
        // Remover foto
        $(document).on('click', '.btn-remover-foto', function() {
            const cell = $(this).closest('.foto-cell');
            const inputClass = cell.find('.btn-capturar-foto').data('tipo') === 'entrega' ? '.foto-entrega-input' : '.foto-devolucao-input';
            cell.find(inputClass).val('');
            cell.find('.foto-preview').hide();
            cell.find('.btn-capturar-foto').show();
        });
        
        // Fechar modal da câmera - parar stream
        $('#modalCapturarFoto').on('hidden.bs.modal', function() {
            pararCamera();
        });
        
        // Limpar formulário
        $('#btnLimpar').on('click', function() {
            limparFormulario();
        });
        
        // Submeter formulário
        $('#formFichaEpi').on('submit', function(e) {
            e.preventDefault();
            salvarFichaEpi();
        });
    });

    function carregarSetores() {
        $.get('/estoque/api/centros-custo', function(response) {
            if (response.success && response.data) {
                const select = $('#setor_id');
                select.find('option:not(:first)').remove();
                
                response.data.forEach(function(setor) {
                    select.append(`<option value="${setor.id}">${setor.nome}</option>`);
                });
            }
        }).fail(function() {
            console.error('Erro ao carregar setores');
        });
    }

    function carregarNumeroFicha() {
        $.get('/estoque/api/proximo-numero-ficha', function(response) {
            if (response.success) {
                $('#numero_ficha').val(response.numero_ficha);
            }
        }).fail(function() {
            $('#numero_ficha').val('1');
        });
    }

    function buscarFuncionarios(termo) {
        $.get('/estoque/api/funcionarios', { q: termo }, function(response) {
            const container = $('#funcionario_sugestoes');
            container.empty();
            
            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(function(f) {
                    const dataAdmissao = f.data_admissao ? formatarData(f.data_admissao) : 'N/A';
                    const html = `
                        <div class="sugestao-item" data-funcionario='${JSON.stringify(f)}'>
                            <div class="nome">${f.nome}</div>
                            <div class="info">
                                <span>CPF: ${f.cpf || 'N/A'}</span> | 
                                <span>Função: ${f.funcao || 'N/A'}</span> |
                                <span>Status: ${f.status || 'Ativo'}</span>
                            </div>
                        </div>
                    `;
                    container.append(html);
                });
                container.show();
                
                // Clique na sugestão
                container.find('.sugestao-item').on('click', function() {
                    const func = $(this).data('funcionario');
                    selecionarFuncionario(func);
                });
            } else {
                container.append('<div class="sugestao-item text-muted">Nenhum funcionário encontrado</div>');
                container.show();
            }
        }).fail(function() {
            console.error('Erro ao buscar funcionários');
        });
    }

    function buscarProdutos(termo, inputElement, containerElement) {
        $.get('/estoque/api/produtos', { q: termo }, function(response) {
            containerElement.empty();
            
            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(function(produto) {
                    const html = `<div class="produto-item">${produto.nome}</div>`;
                    containerElement.append(html);
                });
                containerElement.show();
                
                // Clique na sugestão
                containerElement.find('.produto-item').on('click', function() {
                    const nomeProduto = $(this).text();
                    inputElement.val(nomeProduto);
                    containerElement.hide();
                });
            } else {
                containerElement.append('<div class="produto-item text-muted">Nenhum produto encontrado</div>');
                containerElement.show();
            }
        }).fail(function() {
            console.error('Erro ao buscar produtos');
        });
    }

    function selecionarFuncionario(funcionario) {
        funcionarioSelecionado = funcionario;
        funcionarioFaceDescriptor = null; // Resetar descritor facial para novo funcionário
        
        $('#funcionario_busca').val(funcionario.nome);
        $('#funcionario_id').val(funcionario.id);
        $('#funcionario_cargo').val(funcionario.funcao || 'N/A');
        $('#funcionario_admissao').val(funcionario.data_admissao ? formatarData(funcionario.data_admissao) : 'N/A');
        
        // Esconder box de aguardando
        $('#foto_aguardando_box').hide();
        
        // Exibir foto do funcionário se existir
        if (funcionario.foto_path) {
            $('#foto_funcionario').attr('src', '{{ url("/") }}/' + funcionario.foto_path);
            $('#funcionario_foto_path').val(funcionario.foto_path);
            $('#foto_existente_box').show();
            $('#foto_upload_box').hide();
            $('#foto_enviando_box').hide();
        } else {
            $('#funcionario_foto_path').val('');
            $('#foto_existente_box').hide();
            $('#foto_upload_box').show();
            $('#foto_enviando_box').hide();
        }
        
        $('#funcionario_sugestoes').hide();
    }

    function formatarData(dataString) {
        if (!dataString) return 'N/A';
        const data = new Date(dataString);
        if (isNaN(data.getTime())) return 'N/A';
        return data.toLocaleDateString('pt-BR');
    }

    function adicionarItemEpi() {
        itemIndexEpi++;
        
        const html = `
            <tr class="item-row-epi" data-index="${itemIndexEpi}">
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           name="itens_epi[${itemIndexEpi}][data_entrega]">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           name="itens_epi[${itemIndexEpi}][quantidade]" min="1" value="1">
                </td>
                <td class="position-relative">
                    <input type="text" class="form-control form-control-sm item-descricao-epi" 
                           name="itens_epi[${itemIndexEpi}][equipamento]" placeholder="Digite 3 letras..." autocomplete="off">
                    <div class="produto-sugestoes" data-index="${itemIndexEpi}"></div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="itens_epi[${itemIndexEpi}][ca]" placeholder="CA">
                </td>
                <td class="text-center foto-cell">
                    <input type="hidden" name="itens_epi[${itemIndexEpi}][foto_entrega]" class="foto-entrega-input">
                    <button type="button" class="btn btn-info btn-sm btn-capturar-foto" data-tipo="entrega" data-tabela="epi" data-index="${itemIndexEpi}">
                        <i class="fas fa-camera"></i>
                    </button>
                    <div class="foto-preview mt-1" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                        <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           name="itens_epi[${itemIndexEpi}][data_devolucao]">
                </td>
                <td class="text-center foto-cell">
                    <input type="hidden" name="itens_epi[${itemIndexEpi}][foto_devolucao]" class="foto-devolucao-input">
                    <button type="button" class="btn btn-warning btn-sm btn-capturar-foto" data-tipo="devolucao" data-tabela="epi" data-index="${itemIndexEpi}">
                        <i class="fas fa-camera"></i>
                    </button>
                    <div class="foto-preview mt-1" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                        <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-remover-item-epi">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#itensBodyEpi').append(html);
        atualizarBotoesRemoverEpi();
    }

    function adicionarItemUniforme() {
        itemIndexUniforme++;
        
        const html = `
            <tr class="item-row-uniforme" data-index="${itemIndexUniforme}">
                <td class="position-relative">
                    <input type="text" class="form-control form-control-sm item-descricao-uniforme" 
                           name="itens_uniforme[${itemIndexUniforme}][descricao]" placeholder="Digite 3 letras..." autocomplete="off">
                    <div class="produto-sugestoes" data-index="${itemIndexUniforme}"></div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="itens_uniforme[${itemIndexUniforme}][tamanho]" placeholder="M, G">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           name="itens_uniforme[${itemIndexUniforme}][quantidade]" min="1" value="1">
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           name="itens_uniforme[${itemIndexUniforme}][data_entrega]">
                </td>
                <td class="text-center foto-cell">
                    <input type="hidden" name="itens_uniforme[${itemIndexUniforme}][foto_entrega]" class="foto-entrega-input">
                    <button type="button" class="btn btn-info btn-sm btn-capturar-foto" data-tipo="entrega" data-tabela="uniforme" data-index="${itemIndexUniforme}">
                        <i class="fas fa-camera"></i>
                    </button>
                    <div class="foto-preview mt-1" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                        <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           name="itens_uniforme[${itemIndexUniforme}][data_devolucao]">
                </td>
                <td class="text-center foto-cell">
                    <input type="hidden" name="itens_uniforme[${itemIndexUniforme}][foto_devolucao]" class="foto-devolucao-input">
                    <button type="button" class="btn btn-warning btn-sm btn-capturar-foto" data-tipo="devolucao" data-tabela="uniforme" data-index="${itemIndexUniforme}">
                        <i class="fas fa-camera"></i>
                    </button>
                    <div class="foto-preview mt-1" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                        <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-remover-item-uniforme">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#itensBodyUniforme').append(html);
        atualizarBotoesRemoverUniforme();
    }

    function atualizarBotoesRemoverEpi() {
        const linhas = $('#itensBodyEpi tr');
        if (linhas.length <= 1) {
            linhas.find('.btn-remover-item-epi').prop('disabled', true);
        } else {
            linhas.find('.btn-remover-item-epi').prop('disabled', false);
        }
    }

    function atualizarBotoesRemoverUniforme() {
        const linhas = $('#itensBodyUniforme tr');
        if (linhas.length <= 1) {
            linhas.find('.btn-remover-item-uniforme').prop('disabled', true);
        } else {
            linhas.find('.btn-remover-item-uniforme').prop('disabled', false);
        }
    }

    function limparFormulario() {
        $('#formFichaEpi')[0].reset();
        $('#funcionario_id').val('');
        $('#funcionario_cargo').val('');
        $('#funcionario_admissao').val('');
        $('#funcionario_foto_path').val('');
        $('#foto_funcionario').attr('src', '');
        // Voltar ao estado inicial da foto
        $('#foto_aguardando_box').show();
        $('#foto_existente_box').hide();
        $('#foto_upload_box').hide();
        $('#foto_enviando_box').hide();
        funcionarioSelecionado = null;
        
        // Resetar tipo para EPI
        tipoAtual = 'epi';
        $('#tipoEpi').prop('checked', true);
        $('#labelTipoEpi').addClass('active');
        $('#labelTipoUniforme').removeClass('active');
        $('#tabelaEpiContainer').show();
        $('#tabelaUniformeContainer').hide();
        
        // Resetar tabela de EPI
        $('#itensBodyEpi').html(`
            <tr class="item-row-epi" data-index="0">
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           name="itens_epi[0][data_entrega]">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           name="itens_epi[0][quantidade]" min="1" value="1">
                </td>
                <td class="position-relative">
                    <input type="text" class="form-control form-control-sm item-descricao-epi" 
                           name="itens_epi[0][equipamento]" placeholder="Digite 3 letras..." autocomplete="off">
                    <div class="produto-sugestoes" data-index="0"></div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="itens_epi[0][ca]" placeholder="CA">
                </td>
                <td class="text-center foto-cell">
                    <input type="hidden" name="itens_epi[0][foto_entrega]" class="foto-entrega-input">
                    <button type="button" class="btn btn-info btn-sm btn-capturar-foto" data-tipo="entrega" data-tabela="epi" data-index="0">
                        <i class="fas fa-camera"></i>
                    </button>
                    <div class="foto-preview mt-1" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                        <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           name="itens_epi[0][data_devolucao]">
                </td>
                <td class="text-center foto-cell">
                    <input type="hidden" name="itens_epi[0][foto_devolucao]" class="foto-devolucao-input">
                    <button type="button" class="btn btn-warning btn-sm btn-capturar-foto" data-tipo="devolucao" data-tabela="epi" data-index="0">
                        <i class="fas fa-camera"></i>
                    </button>
                    <div class="foto-preview mt-1" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                        <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-remover-item-epi" disabled>
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
        itemIndexEpi = 0;
        
        // Resetar tabela de Uniforme
        $('#itensBodyUniforme').html(`
            <tr class="item-row-uniforme" data-index="0">
                <td class="position-relative">
                    <input type="text" class="form-control form-control-sm item-descricao-uniforme" 
                           name="itens_uniforme[0][descricao]" placeholder="Digite 3 letras..." autocomplete="off">
                    <div class="produto-sugestoes" data-index="0"></div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="itens_uniforme[0][tamanho]" placeholder="M, G">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           name="itens_uniforme[0][quantidade]" min="1" value="1">
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           name="itens_uniforme[0][data_entrega]">
                </td>
                <td class="text-center foto-cell">
                    <input type="hidden" name="itens_uniforme[0][foto_entrega]" class="foto-entrega-input">
                    <button type="button" class="btn btn-info btn-sm btn-capturar-foto" data-tipo="entrega" data-tabela="uniforme" data-index="0">
                        <i class="fas fa-camera"></i>
                    </button>
                    <div class="foto-preview mt-1" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                        <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           name="itens_uniforme[0][data_devolucao]">
                </td>
                <td class="text-center foto-cell">
                    <input type="hidden" name="itens_uniforme[0][foto_devolucao]" class="foto-devolucao-input">
                    <button type="button" class="btn btn-warning btn-sm btn-capturar-foto" data-tipo="devolucao" data-tabela="uniforme" data-index="0">
                        <i class="fas fa-camera"></i>
                    </button>
                    <div class="foto-preview mt-1" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-width: 60px; max-height: 40px;">
                        <button type="button" class="btn btn-danger btn-xs btn-remover-foto ml-1" style="padding: 2px 5px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-remover-item-uniforme" disabled>
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
        itemIndexUniforme = 0;
        
        carregarNumeroFicha();
    }

    function salvarFichaEpi() {
        // Validações
        if (!$('#funcionario_id').val()) {
            Swal.fire('Atenção', 'Selecione um funcionário', 'warning');
            return;
        }
        
        if (!$('#endereco').val().trim()) {
            Swal.fire('Atenção', 'Preencha o endereço', 'warning');
            return;
        }
        
        if (!$('#setor_id').val()) {
            Swal.fire('Atenção', 'Selecione o setor', 'warning');
            return;
        }
        
        // Coletar itens baseado no tipo atual
        const itens = [];
        let itensValidos = true;
        const tipoLabel = tipoAtual === 'epi' ? 'EPI' : 'Uniforme';
        
        if (tipoAtual === 'epi') {
            $('#itensBodyEpi tr').each(function() {
                const descricao = $(this).find('.item-descricao-epi').val().trim();
                const quantidade = parseInt($(this).find('input[name$="[quantidade]"]').val()) || 0;
                const ca = $(this).find('input[name$="[ca]"]').val().trim();
                const dataEntrega = $(this).find('input[name$="[data_entrega]"]').val();
                const dataDevolucao = $(this).find('input[name$="[data_devolucao]"]').val();
                const fotoEntrega = $(this).find('.foto-entrega-input').val() || '';
                const fotoDevolucao = $(this).find('.foto-devolucao-input').val() || '';
                const metadadosEntrega = $(this).find('.metadados-entrega-input').val() || '';
                const metadadosDevolucao = $(this).find('.metadados-devolucao-input').val() || '';
                
                if (!descricao) {
                    itensValidos = false;
                    return false;
                }
                
                itens.push({
                    descricao: descricao,
                    quantidade: quantidade || 1,
                    tamanho: null,
                    ca: ca || null,
                    data_entrega: dataEntrega || null,
                    data_devolucao: dataDevolucao || null,
                    foto_entrega: fotoEntrega || null,
                    foto_devolucao: fotoDevolucao || null,
                    metadados_entrega: metadadosEntrega ? JSON.parse(metadadosEntrega) : null,
                    metadados_devolucao: metadadosDevolucao ? JSON.parse(metadadosDevolucao) : null
                });
            });
        } else {
            $('#itensBodyUniforme tr').each(function() {
                const descricao = $(this).find('.item-descricao-uniforme').val().trim();
                const quantidade = parseInt($(this).find('input[name$="[quantidade]"]').val()) || 0;
                const tamanho = $(this).find('input[name$="[tamanho]"]').val().trim();
                const dataEntrega = $(this).find('input[name$="[data_entrega]"]').val();
                const dataDevolucao = $(this).find('input[name$="[data_devolucao]"]').val();
                const fotoEntrega = $(this).find('.foto-entrega-input').val() || '';
                const fotoDevolucao = $(this).find('.foto-devolucao-input').val() || '';
                const metadadosEntrega = $(this).find('.metadados-entrega-input').val() || '';
                const metadadosDevolucao = $(this).find('.metadados-devolucao-input').val() || '';
                
                if (!descricao) {
                    itensValidos = false;
                    return false;
                }
                
                itens.push({
                    descricao: descricao,
                    quantidade: quantidade || 1,
                    tamanho: tamanho || null,
                    ca: null,
                    data_entrega: dataEntrega || null,
                    data_devolucao: dataDevolucao || null,
                    foto_entrega: fotoEntrega || null,
                    foto_devolucao: fotoDevolucao || null,
                    metadados_entrega: metadadosEntrega ? JSON.parse(metadadosEntrega) : null,
                    metadados_devolucao: metadadosDevolucao ? JSON.parse(metadadosDevolucao) : null
                });
            });
        }
        
        if (!itensValidos || itens.length === 0) {
            Swal.fire('Atenção', 'Preencha a descrição de todos os itens', 'warning');
            return;
        }
        
        // Desabilitar botão
        $('#btnSalvar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');
        
        // Enviar dados
        $.ajax({
            url: '/estoque/api/salvar-ficha-epi',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                funcionario_id: $('#funcionario_id').val(),
                endereco: $('#endereco').val().trim(),
                setor_id: $('#setor_id').val(),
                tipo: tipoAtual,
                itens: itens
            }),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Ficha de ' + tipoLabel + ' registrada com sucesso! Número da ficha: ' + response.numero_ficha,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        limparFormulario();
                    });
                } else {
                    Swal.fire('Erro', response.message || 'Erro ao salvar', 'error');
                }
            },
            error: function(xhr) {
                let msg = 'Erro ao salvar ficha';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Erro', msg, 'error');
            },
            complete: function() {
                const textoBtn = tipoAtual === 'epi' ? 'Salvar Ficha de EPI' : 'Salvar Ficha de Uniforme';
                $('#btnSalvar').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> <span id="btnSalvarTexto">' + textoBtn + '</span>');
            }
        });
    }

    // ========== FUNÇÕES DE CÂMERA ==========
    
    function abrirCamera() {
        $('#cameraContainer').show();
        $('#fotoCapturadaContainer').hide();
        $('#btnTirarFoto').show();
        $('#btnNovaFoto').hide();
        $('#btnConfirmarFoto').hide();
        $('#instrucaoCamera').text('Posicione a câmera e clique em "Tirar Foto"');
        
        // Tentar acessar a câmera traseira primeiro (para celulares)
        const constraints = {
            video: {
                facingMode: { ideal: 'environment' }, // Câmera traseira
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        
        navigator.mediaDevices.getUserMedia(constraints)
            .then(function(stream) {
                cameraStream = stream;
                const video = document.getElementById('videoCamera');
                video.srcObject = stream;
                $('#modalCapturarFoto').modal('show');
            })
            .catch(function(error) {
                console.error('Erro ao acessar câmera:', error);
                // Tentar com qualquer câmera disponível
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(function(stream) {
                        cameraStream = stream;
                        const video = document.getElementById('videoCamera');
                        video.srcObject = stream;
                        $('#modalCapturarFoto').modal('show');
                    })
                    .catch(function(err) {
                        Swal.fire('Erro', 'Não foi possível acessar a câmera. Verifique as permissões do navegador.', 'error');
                    });
            });
    }
    
    function pararCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        const video = document.getElementById('videoCamera');
        video.srcObject = null;
    }
    
    // Variável para armazenar metadados da foto atual
    let fotoMetadados = null;
    
    function tirarFoto() {
        const video = document.getElementById('videoCamera');
        const canvas = document.getElementById('canvasCaptura');
        const context = canvas.getContext('2d');
        
        // Definir tamanho do canvas igual ao vídeo
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Desenhar frame do vídeo no canvas
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Converter para base64
        const fotoBase64 = canvas.toDataURL('image/jpeg', 0.8);
        
        // Capturar metadados
        const agora = new Date();
        fotoMetadados = {
            data_hora: agora.toISOString(),
            data_formatada: agora.toLocaleDateString('pt-BR') + ' ' + agora.toLocaleTimeString('pt-BR'),
            dispositivo: navigator.userAgent,
            plataforma: navigator.platform || 'Desconhecido',
            navegador: detectarNavegador(),
            tela: window.screen.width + 'x' + window.screen.height,
            latitude: null,
            longitude: null,
            precisao_gps: null,
            ip: null // Será preenchido pelo servidor
        };
        
        // Tentar obter localização GPS
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    fotoMetadados.latitude = position.coords.latitude;
                    fotoMetadados.longitude = position.coords.longitude;
                    fotoMetadados.precisao_gps = position.coords.accuracy;
                    console.log('Localização capturada:', fotoMetadados.latitude, fotoMetadados.longitude);
                },
                function(error) {
                    console.warn('Não foi possível obter localização:', error.message);
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        }
        
        console.log('Metadados da foto:', fotoMetadados);
        
        // Mostrar preview
        $('#fotoCapturada').attr('src', fotoBase64);
        $('#cameraContainer').hide();
        $('#fotoCapturadaContainer').show();
        $('#btnTirarFoto').hide();
        $('#btnNovaFoto').show();
        $('#btnConfirmarFoto').show();
        $('#instrucaoCamera').text('Foto capturada! Confirme ou tire outra.');
    }
    
    // Detectar navegador do usuário
    function detectarNavegador() {
        const ua = navigator.userAgent;
        if (ua.includes('Chrome') && !ua.includes('Edg')) return 'Chrome';
        if (ua.includes('Firefox')) return 'Firefox';
        if (ua.includes('Safari') && !ua.includes('Chrome')) return 'Safari';
        if (ua.includes('Edg')) return 'Edge';
        if (ua.includes('Opera') || ua.includes('OPR')) return 'Opera';
        return 'Outro';
    }
    
    function voltarParaCamera() {
        // Limpar a foto capturada anterior para forçar nova verificação
        $('#fotoCapturada').attr('src', '');
        
        $('#cameraContainer').show();
        $('#fotoCapturadaContainer').hide();
        $('#btnTirarFoto').show();
        $('#btnNovaFoto').hide();
        $('#btnConfirmarFoto').hide();
        $('#instrucaoCamera').text('Posicione a câmera e clique em "Tirar Foto"');
    }
    
    async function confirmarFoto() {
        const fotoBase64 = $('#fotoCapturada').attr('src');
        console.log('Confirmando foto, tamanho base64:', fotoBase64 ? fotoBase64.length : 0);
        
        // Variável para armazenar resultado da verificação facial
        let verificacaoFacial = null;
        let assinaturaDigital = false;
        
        // Verificação facial apenas para fotos de ENTREGA e se funcionário tem foto cadastrada
        if (fotoAtual.tipo === 'entrega' && funcionarioSelecionado && funcionarioSelecionado.foto_path) {
            $('#btnConfirmarFoto').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Verificando rosto...');
            
            console.log('Iniciando verificação facial...');
            const verificacao = await verificarFotoFuncionario(fotoBase64);
            console.log('Verificação concluída:', verificacao);
            
            verificacaoFacial = verificacao;
            
            if (!verificacao.verificado) {
                if (verificacao.motivo === 'faces_diferentes') {
                    const result = await Swal.fire({
                        icon: 'warning',
                        title: 'Atenção!',
                        html: `<p>A foto capturada <strong>não corresponde</strong> ao funcionário selecionado.</p>
                               <p class="text-muted">Similaridade: ${verificacao.confianca}%</p>
                               <p>Deseja continuar mesmo assim?</p>`,
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Continuar mesmo assim',
                        cancelButtonText: 'Tirar nova foto'
                    });
                    
                    if (!result.isConfirmed) {
                        $('#btnConfirmarFoto').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirmar Foto');
                        voltarParaCamera();
                        return;
                    }
                } else if (verificacao.motivo === 'rosto_nao_detectado_captura') {
                    const result = await Swal.fire({
                        icon: 'warning',
                        title: 'Rosto não detectado',
                        html: `<p>Não foi possível detectar um rosto na foto capturada.</p>
                               <p>Posicione melhor a câmera e tente novamente.</p>`,
                        showCancelButton: true,
                        confirmButtonText: 'Continuar mesmo assim',
                        cancelButtonText: 'Tirar nova foto'
                    });
                    
                    if (!result.isConfirmed) {
                        $('#btnConfirmarFoto').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirmar Foto');
                        voltarParaCamera();
                        return;
                    }
                }
            } else if (verificacao.motivo === 'match_ok') {
                console.log('Verificação facial OK! Confiança: ' + verificacao.confianca + '%');
                assinaturaDigital = true; // Marcar como assinatura digital válida
            }
        }
        
        // Adicionar verificação facial aos metadados
        if (fotoMetadados) {
            fotoMetadados.verificacao_facial = verificacaoFacial;
            fotoMetadados.assinatura_digital = assinaturaDigital;
            fotoMetadados.funcionario_nome = funcionarioSelecionado ? funcionarioSelecionado.nome : null;
            fotoMetadados.funcionario_id = funcionarioSelecionado ? funcionarioSelecionado.id : null;
        }
        
        // Fazer upload da foto com metadados
        $.ajax({
            url: '/estoque/api/upload-foto-epi',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                foto: fotoBase64,
                tipo: fotoAtual.tipo,
                tabela: fotoAtual.tabela,
                metadados: fotoMetadados
            }),
            beforeSend: function() {
                $('#btnConfirmarFoto').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');
            },
            success: function(response) {
                if (response.success) {
                    // Encontrar a célula correta e atualizar
                    const cell = fotoAtual.botao.closest('.foto-cell');
                    const inputClass = fotoAtual.tipo === 'entrega' ? '.foto-entrega-input' : '.foto-devolucao-input';
                    const metadadosInputClass = fotoAtual.tipo === 'entrega' ? '.metadados-entrega-input' : '.metadados-devolucao-input';
                    
                    // Salvar o caminho da foto no input hidden
                    cell.find(inputClass).val(response.caminho);
                    
                    // Salvar metadados no input hidden (se existir)
                    if (cell.find(metadadosInputClass).length === 0) {
                        // Criar input para metadados se não existir
                        const row = cell.closest('tr');
                        const baseName = cell.find(inputClass).attr('name').replace('[foto_entrega]', '').replace('[foto_devolucao]', '');
                        const metadadosName = baseName + (fotoAtual.tipo === 'entrega' ? '[metadados_entrega]' : '[metadados_devolucao]');
                        row.append(`<input type="hidden" name="${metadadosName}" class="${metadadosInputClass.substring(1)}" value="">`);
                    }
                    cell.closest('tr').find(metadadosInputClass).val(JSON.stringify(fotoMetadados));
                    
                    // Mostrar preview da foto
                    cell.find('.btn-capturar-foto').hide();
                    cell.find('.foto-preview img').attr('src', response.url);
                    cell.find('.foto-preview').show();
                    
                    // Fechar modal
                    $('#modalCapturarFoto').modal('hide');
                    
                    // Mostrar mensagem diferente se foi assinatura digital
                    const mensagem = (fotoMetadados && fotoMetadados.assinatura_digital) 
                        ? 'Foto capturada e ASSINATURA DIGITAL registrada!' 
                        : 'A foto foi capturada com sucesso.';
                    
                    Swal.fire({
                        icon: 'success',
                        title: (fotoMetadados && fotoMetadados.assinatura_digital) ? 'Assinatura Digital!' : 'Foto salva!',
                        text: mensagem,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Erro', response.message || 'Erro ao salvar foto', 'error');
                }
            },
            error: function(xhr) {
                let msg = 'Erro ao salvar foto';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Erro', msg, 'error');
            },
            complete: function() {
                $('#btnConfirmarFoto').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirmar Foto');
            }
        });
    }
</script>
@stop
