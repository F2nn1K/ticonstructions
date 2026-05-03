@extends('adminlte::page')

@section('title', 'Ordem de Serviço')

@section('css')
<style>
    /* Container principal */
    .os-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    /* Header da página */
    .page-header {
        margin-bottom: 25px;
    }
    
    .page-header h4 {
        font-size: 22px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .page-header small {
        color: #666;
    }
    
    /* Ações rápidas - Cards grandes */
    .acoes-rapidas-container {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    .acao-card {
        background: #fff;
        border-radius: 10px;
        padding: 30px 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e0e0e0;
        height: 100%;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .acao-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border-color: #4a90d9;
    }
    
    .acao-card.acao-nova {
        background: linear-gradient(135deg, #4a90d9 0%, #3a7fc9 100%);
        border-color: transparent;
        color: #fff;
    }
    
    .acao-card.acao-nova:hover {
        background: linear-gradient(135deg, #3a7fc9 0%, #2a6fb9 100%);
        box-shadow: 0 5px 20px rgba(74,144,217,0.4);
    }
    
    .acao-card .acao-icon {
        font-size: 20px;
        margin-right: 10px;
    }
    
    .acao-card .acao-content {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .acao-card .acao-titulo {
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    
    .acao-card .acao-subtitulo {
        font-size: 13px;
        opacity: 0.75;
    }
    
    /* Seções */
    .secao-os {
        display: none;
    }
    
    .secao-os.ativa {
        display: block;
    }
    
    /* Card do formulário */
    .form-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .form-card-header {
        background: linear-gradient(135deg, #e8f4fd 0%, #d4e8f7 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #cde4f5;
    }
    
    .form-card-header h5 {
        margin: 0 0 8px 0;
        font-weight: 600;
        color: #333;
    }
    
    .form-card-body {
        padding: 25px;
    }
    
    /* Filtros */
    .filtros-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    /* Tabela */
    .table-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    .table thead th {
        background: #4a90d9;
        color: #fff;
        font-weight: 600;
        border: none;
        padding: 14px 18px;
        font-size: 14px;
    }
    
    .table tbody td {
        padding: 14px 18px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .table tbody tr:hover {
        background: #f8fafc;
    }
    
    /* Busca funcionários */
    .funcionario-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 9999;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        display: none;
    }
    
    .funcionario-result-item {
        padding: 12px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }
    
    .funcionario-result-item:hover {
        background: #f0f7ff;
    }
    
    .funcionario-result-item:last-child {
        border-bottom: none;
    }
    
    .funcionario-nome {
        font-weight: 600;
        color: #333;
    }
    
    .funcionario-funcao {
        font-size: 12px;
        color: #666;
    }
    
    /* Botão voltar */
    .btn-voltar-link {
        color: #666;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
    }
    
    .btn-voltar-link:hover {
        color: #333;
        text-decoration: none;
    }
    
    /* Form controls */
    .form-control {
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 10px 14px;
    }
    
    .form-control:focus {
        border-color: #4a90d9;
        box-shadow: 0 0 0 3px rgba(74,144,217,0.1);
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    /* Botão salvar */
    .btn-salvar {
        background: #28a745;
        border: none;
        padding: 12px 40px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
    }
    
    .btn-salvar:hover {
        background: #218838;
    }
    
    /* Tipo de atendimento checkboxes */
    .tipo-atendimento-grid {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px 20px;
        border: 1px solid #e9ecef;
    }
    
    .tipo-atendimento-grid .form-check {
        margin-bottom: 0;
    }
    
    .tipo-atendimento-grid .form-check-input {
        width: 18px;
        height: 18px;
        margin-top: 2px;
    }
    
    .tipo-atendimento-grid .form-check-label {
        font-weight: 500;
        color: #444;
        padding-left: 5px;
    }
    
    .tipo-atendimento-grid .form-check-input:checked {
        background-color: #4a90d9;
        border-color: #4a90d9;
    }
    
    /* Materiais */
    .materiais-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        border: 1px solid #e9ecef;
    }
    
    .table-materiais {
        background: #fff;
        margin-bottom: 0;
    }
    
    .table-materiais thead th {
        background: #e9ecef;
        color: #333;
        font-weight: 600;
        font-size: 13px;
        padding: 10px 15px;
    }
    
    .table-materiais tbody td {
        padding: 10px 15px;
        vertical-align: middle;
    }
    
    .material-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 9999;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        display: none;
    }
    
    .material-result-item {
        padding: 12px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }
    
    .material-result-item:hover {
        background: #f0f7ff;
    }
    
    .material-result-item:last-child {
        border-bottom: none;
    }
    
    .material-nome {
        font-weight: 600;
        color: #333;
    }
    
    .material-estoque {
        font-size: 12px;
        color: #28a745;
    }
    
    .material-estoque.baixo {
        color: #dc3545;
    }
    
    .btn-remover-material {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    /* Solicitações */
    .solicitacao-container {
        background: #fff8e6;
        border-radius: 8px;
        padding: 20px;
        border: 1px solid #ffc107;
    }
    
    .table-solicitacoes {
        background: #fff;
        margin-bottom: 0;
    }
    
    .table-solicitacoes thead th {
        background: #ffc107;
        color: #333;
        font-weight: 600;
        font-size: 13px;
        padding: 10px 15px;
    }
    
    .table-solicitacoes tbody td {
        padding: 10px 15px;
        vertical-align: middle;
    }
    
    .btn-remover-solicitacao {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    /* Prestadores de Serviço */
    .prestadores-container {
        background: #e8f5e9;
        border-radius: 8px;
        padding: 20px;
        border: 1px solid #4caf50;
    }
    
    .table-prestadores {
        background: #fff;
        margin-bottom: 0;
    }
    
    .table-prestadores thead th {
        background: #4caf50;
        color: #fff;
        font-weight: 600;
        font-size: 13px;
        padding: 10px 15px;
    }
    
    .table-prestadores tbody td {
        padding: 10px 15px;
        vertical-align: middle;
    }
    
    .table-prestadores tfoot td {
        padding: 12px 15px;
        font-size: 15px;
    }
    
    .btn-remover-prestador {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .valor-monetario {
        text-align: right;
    }
    
    /* Resumo de Custos */
    .resumo-custos {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 8px;
        padding: 20px;
        color: #fff;
        margin-top: 20px;
    }
    
    .resumo-custos h6 {
        color: #fff;
        margin-bottom: 15px;
        font-weight: 600;
    }
    
    .resumo-custos .custo-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    
    .resumo-custos .custo-item:last-child {
        border-bottom: none;
    }
    
    .resumo-custos .custo-total {
        font-size: 18px;
        font-weight: 700;
        padding-top: 15px;
        margin-top: 10px;
        border-top: 2px solid rgba(255,255,255,0.3);
    }
    
    /* Autocomplete Centro de Custo */
    .autocomplete-dropdown {
        position: absolute;
        z-index: 1050;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 250px;
        overflow-y: auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        width: calc(100% - 30px);
    }
    
    .autocomplete-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }
    
    .autocomplete-item:last-child {
        border-bottom: none;
    }
    
    .autocomplete-item:hover {
        background: #f5f8ff;
    }
    
    .autocomplete-item.selected {
        background: #e3f2fd;
    }
    
    .centro-custo-selecionado {
        background: #e8f5e9 !important;
        border-color: #4caf50 !important;
    }
</style>
@stop

@section('content_header')
@stop

@section('content')
<div class="container-fluid os-container">
    
    <!-- SEÇÃO: Ações Rápidas (removida - vai direto para Nova O.S.) -->
    <div id="secaoAcoesRapidas" class="secao-os" style="display: none !important;">
        <div class="page-header">
            <h4><i class="fas fa-bolt text-warning mr-2"></i>Ações Rápidas - O.S.</h4>
            <small>Escolha entre criar nova ou visualizar as O.S.</small>
        </div>
        
        <div class="acoes-rapidas-container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="acao-card acao-nova" onclick="mostrarSecao('novaOS')">
                        <div class="acao-content">
                            <div class="acao-titulo">
                                <i class="fas fa-plus acao-icon"></i> Nova O.S.
                            </div>
                            <div class="acao-subtitulo">Criar nova ordem de serviço</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="acao-card" onclick="mostrarSecao('visualizar')">
                        <div class="acao-content">
                            <div class="acao-titulo">
                                <i class="fas fa-search acao-icon"></i> Visualizar O.S.
                            </div>
                            <div class="acao-subtitulo">Ver ordens do dia</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SEÇÃO: Visualizar O.S. -->
    <div id="secaoVisualizar" class="secao-os">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="page-header mb-0">
                <h4><i class="fas fa-list-alt text-primary mr-2"></i>Ordens de Serviço</h4>
                <small>Visualize as O.S. da data selecionada</small>
            </div>
            <div>
                <a href="javascript:void(0)" class="btn-voltar-link mr-3" onclick="mostrarSecao('acoesRapidas')">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
                <button class="btn btn-primary" onclick="mostrarSecao('novaOS')">
                    <i class="fas fa-plus mr-1"></i> Nova O.S.
                </button>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filtros-card">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label">Data inicial</label>
                    <input type="date" id="filtroDataInicial" class="form-control">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label">Data final</label>
                    <input type="date" id="filtroDataFinal" class="form-control">
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="form-label">Número da O.S.</label>
                    <input type="text" id="filtroNumeroOS" class="form-control" placeholder="Ex.: OS-20250809-0001">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-block" onclick="filtrarOS()">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    <a href="javascript:void(0)" class="d-block text-center mt-2 text-muted" onclick="limparFiltros()">Limpar</a>
                </div>
            </div>
            <small class="text-muted d-block mt-3">
                <i class="fas fa-info-circle mr-1"></i> Preencha período OU o número da O.S. (o número tem prioridade).
            </small>
        </div>
        
        <!-- Tabela -->
        <div class="table-card">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nº O.S.</th>
                        <th>Data</th>
                        <th>Funcionário</th>
                        <th>Cidade/UF</th>
                        <th>Telefone</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaOS">
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Nenhuma O.S. encontrada para a data.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- SEÇÃO: Nova O.S. (padrão) -->
    <div id="secaoNovaOS" class="secao-os ativa">
        <div class="page-header">
            <h4><i class="fas fa-edit text-primary mr-2"></i>Ordem de Serviço</h4>
            <small>Cadastro de Ordem de Serviço</small>
        </div>
        
        <div class="form-card">
            <div class="form-card-header">
                <h5><i class="fas fa-plus-circle mr-2"></i>Nova OS</h5>
                <a href="/area-tecnica/gestao-os" class="btn-voltar-link">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
            </div>
            
            <div class="form-card-body">
                <form id="formNovaOS">
                    <input type="hidden" id="osId" value="">
                    <input type="hidden" id="funcionarioId" value="{{ Auth::id() }}">
                    
                    <div class="row mb-3">
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label">Data</label>
                            <input type="date" id="dataOS" class="form-control" required>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label">Nº da O.S.</label>
                            <input type="text" id="numeroOS" class="form-control" readonly style="background: #f8f9fa;">
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Funcionário</label>
                            <input type="text" id="buscaFuncionario" class="form-control" 
                                   value="{{ Auth::user()->name }}" readonly style="background: #f8f9fa;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Centro de Custo <small class="text-muted">(digite 3 letras)</small></label>
                            <input type="hidden" id="centroCusto" name="centro_custo_id">
                            <input type="text" id="buscaCentroCusto" class="form-control" 
                                   placeholder="Digite para buscar..." autocomplete="off">
                            <div id="listaCentrosCusto" class="autocomplete-dropdown" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Tipo de Atendimento</label>
                            <div class="tipo-atendimento-grid">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input tipo-atendimento-check" type="checkbox" value="Instalação" id="tipoInstalacao">
                                            <label class="form-check-label" for="tipoInstalacao">Instalação</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input tipo-atendimento-check" type="checkbox" value="Corretiva" id="tipoCorretiva">
                                            <label class="form-check-label" for="tipoCorretiva">Corretiva</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input tipo-atendimento-check" type="checkbox" value="Acréscimo" id="tipoAcrescimo">
                                            <label class="form-check-label" for="tipoAcrescimo">Acréscimo</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input tipo-atendimento-check" type="checkbox" value="Remanejamento" id="tipoRemanejamento">
                                            <label class="form-check-label" for="tipoRemanejamento">Remanejamento</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input tipo-atendimento-check" type="checkbox" value="Preventiva" id="tipoPreventiva">
                                            <label class="form-check-label" for="tipoPreventiva">Preventiva</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input tipo-atendimento-check" type="checkbox" value="Outros" id="tipoOutros">
                                            <label class="form-check-label" for="tipoOutros">Outros</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Urgência *</label>
                            <select class="form-control" id="urgenciaOS" name="urgencia" required>
                                <option value="normal">Normal</option>
                                <option value="media">Média</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Descrição do Serviço</label>
                            <textarea id="descricaoServico" class="form-control" rows="4" 
                                      placeholder="Descreva o serviço a ser executado" required></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label">Endereço</label>
                            <input type="text" id="endereco" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Cidade</label>
                            <input type="text" id="cidade" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Estado</label>
                            <input type="text" id="estado" class="form-control" maxlength="2" placeholder="UF">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label">CEP</label>
                            <input type="text" id="cep" class="form-control" placeholder="00000-000">
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label">Telefone</label>
                            <input type="text" id="telefone" class="form-control" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CPF/CNPJ</label>
                            <input type="text" id="cpfCnpj" class="form-control" placeholder="CPF ou CNPJ">
                        </div>
                    </div>
                    
                    <!-- Seção de Prestadores de Serviço (Terceirizados) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Prestadores de Serviço <small class="text-muted">(terceirizados)</small></label>
                            <div class="prestadores-container">
                                <div class="row mb-3" id="formAdicionarPrestador">
                                    <div class="col-md-3">
                                        <input type="text" id="nomePrestador" class="form-control" 
                                               placeholder="Nome do prestador..." autocomplete="off">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" id="descricaoPrestador" class="form-control" 
                                               placeholder="Descrição...">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="text" id="valorPrestador" class="form-control valor-monetario" 
                                                   placeholder="0,00">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="date" id="vencimentoPrestador" class="form-control" title="Data de vencimento">
                                        <small class="text-muted">Vencimento *</small>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-success btn-block" onclick="adicionarPrestador()">
                                            <i class="fas fa-plus mr-1"></i> Adicionar Prestador
                                        </button>
                                    </div>
                                </div>
                                
                                <table class="table table-bordered table-prestadores" id="tabelaPrestadores">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%">Prestador</th>
                                            <th style="width: 25%">Descrição</th>
                                            <th style="width: 15%">Valor</th>
                                            <th style="width: 15%">Vencimento</th>
                                            <th style="width: 10%">Status</th>
                                            <th style="width: 10%">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listaPrestadores">
                                        <tr id="semPrestadores">
                                            <td colspan="6" class="text-center text-muted py-3">
                                                Nenhum prestador adicionado
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="footerPrestadores" style="display: none;">
                                        <tr class="bg-light">
                                            <td colspan="3" class="text-right font-weight-bold">Total Prestadores:</td>
                                            <td colspan="3" class="font-weight-bold text-success" id="totalPrestadores">R$ 0,00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                                
                                <div class="alert alert-info mb-0 mt-2" style="font-size: 13px;">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Os valores dos prestadores serão somados ao custo total da O.S. quando ela for fechada.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção de Materiais -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Materiais Utilizados</label>
                            <div class="materiais-container">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="position-relative">
                                            <input type="text" id="buscaMaterial" class="form-control" 
                                                   placeholder="Digite o nome do material para buscar..." autocomplete="off">
                                            <div id="materialResults" class="material-results"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" id="quantidadeMaterial" class="form-control" 
                                               placeholder="Quantidade" min="1" value="1">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-primary btn-block" onclick="adicionarMaterial()">
                                            <i class="fas fa-plus mr-1"></i> Adicionar
                                        </button>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="materialSelecionadoId" value="">
                                <input type="hidden" id="materialSelecionadoNome" value="">
                                <input type="hidden" id="materialSelecionadoEstoque" value="">
                                
                                <table class="table table-bordered table-materiais" id="tabelaMateriais">
                                    <thead>
                                        <tr>
                                            <th style="width: 50%">Material</th>
                                            <th style="width: 20%">Estoque Atual</th>
                                            <th style="width: 20%">Quantidade</th>
                                            <th style="width: 10%">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listaMateriais">
                                        <tr id="semMateriais">
                                            <td colspan="4" class="text-center text-muted py-3">
                                                Nenhum material adicionado
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção de Solicitação de Materiais -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Solicitação de Materiais <small class="text-muted">(materiais que não estão no estoque)</small></label>
                            <div class="solicitacao-container">
                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <input type="text" id="descricaoSolicitacao" class="form-control" 
                                               placeholder="Descrição do material..." autocomplete="off">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" id="quantidadeSolicitacao" class="form-control" 
                                               placeholder="Qtd" min="1" value="1" step="0.01">
                                    </div>
                                    <div class="col-md-2">
                                        <select id="unidadeSolicitacao" class="form-control">
                                            <option value="UN">UN</option>
                                            <option value="PC">PC</option>
                                            <option value="PCT">PCT</option>
                                            <option value="MT">MT</option>
                                            <option value="M2">M2</option>
                                            <option value="M3">M3</option>
                                            <option value="KG">KG</option>
                                            <option value="LT">LT</option>
                                            <option value="CX">CX</option>
                                            <option value="RL">RL</option>
                                            <option value="SC">SC</option>
                                            <option value="FD">FD</option>
                                            <option value="BD">BD</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-warning btn-block" onclick="adicionarSolicitacao()">
                                            <i class="fas fa-plus mr-1"></i> Solicitar
                                        </button>
                                    </div>
                                </div>
                                
                                <table class="table table-bordered table-solicitacoes" id="tabelaSolicitacoes">
                                    <thead>
                                        <tr>
                                            <th style="width: 50%">Descrição</th>
                                            <th style="width: 15%">Quantidade</th>
                                            <th style="width: 15%">Unidade</th>
                                            <th style="width: 10%">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listaSolicitacoes">
                                        <tr id="semSolicitacoes">
                                            <td colspan="4" class="text-center text-muted py-3">
                                                Nenhum material solicitado
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <div class="alert alert-info mb-0 mt-2" style="font-size: 13px;">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Materiais solicitados serão enviados automaticamente para <strong>Cotação</strong> ao salvar a O.S.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center pt-3">
                        <button type="submit" class="btn btn-success btn-salvar">
                            <i class="fas fa-save mr-2"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</div>

<!-- Modal Visualizar/Editar O.S. -->
<div class="modal fade" id="modalVisualizarOS" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-alt mr-2"></i>Detalhes da O.S.</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalOSConteudo">
                <!-- Conteúdo preenchido via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Fechar') }}</button>
                <button type="button" class="btn btn-warning" onclick="editarOS()">
                    <i class="fas fa-edit mr-1"></i> Editar
                </button>
                <button type="button" class="btn btn-danger" onclick="excluirOS()">
                    <i class="fas fa-trash mr-1"></i> Excluir
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Data padrão
    const hoje = new Date().toISOString().split('T')[0];
    $('#filtroDataInicial').val(hoje);
    $('#filtroDataFinal').val(hoje);
    $('#dataOS').val(hoje);
    
    // Gerar número da OS
    gerarNumeroOS();
    
    // Carregar centros de custo
    carregarCentrosCusto();
    
    // Inicializar data de vencimento dos prestadores
    inicializarVencimentoPrestador();
    
    // Verificar se veio para editar uma O.S. específica (via URL)
    const urlParams = new URLSearchParams(window.location.search);
    const editarId = urlParams.get('editar');
    if (editarId) {
        // Carregar O.S. para editar imediatamente
        carregarParaEditar(editarId);
    }
});

// Cache de centros de custo
let centrosCustoCache = [];

// Carregar centros de custo para o cache
function carregarCentrosCusto() {
    $.get('/api/centros-custo')
        .done(function(centros) {
            centrosCustoCache = centros || [];
        });
}

// Autocomplete de Centro de Custo
$('#buscaCentroCusto').on('input', function() {
    const termo = $(this).val().trim();
    const lista = $('#listaCentrosCusto');
    
    // Limpar seleção se o usuário está digitando
    if (!$(this).hasClass('centro-custo-selecionado')) {
        $('#centroCusto').val('');
    }
    
    // Só buscar se tiver pelo menos 3 caracteres
    if (termo.length < 3) {
        lista.hide().empty();
        return;
    }
    
    // Filtrar centros de custo que começam com o termo digitado
    const termoLower = termo.toLowerCase();
    const resultados = centrosCustoCache.filter(cc => 
        cc.nome.toLowerCase().startsWith(termoLower) || 
        cc.nome.toLowerCase().includes(termoLower)
    );
    
    if (resultados.length === 0) {
        lista.html('<div class="autocomplete-item text-muted">Nenhum centro de custo encontrado</div>').show();
        return;
    }
    
    let html = '';
    resultados.forEach(function(cc) {
        html += `<div class="autocomplete-item" data-id="${cc.id}" data-nome="${cc.nome}">${cc.nome}</div>`;
    });
    
    lista.html(html).show();
});

// Selecionar centro de custo do autocomplete
$(document).on('click', '#listaCentrosCusto .autocomplete-item[data-id]', function() {
    const id = $(this).data('id');
    const nome = $(this).data('nome');
    
    $('#centroCusto').val(id);
    $('#buscaCentroCusto').val(nome).addClass('centro-custo-selecionado');
    $('#listaCentrosCusto').hide().empty();
});

// Fechar autocomplete ao clicar fora
$(document).on('click', function(e) {
    if (!$(e.target).closest('#buscaCentroCusto, #listaCentrosCusto').length) {
        $('#listaCentrosCusto').hide();
    }
});

// Remover classe de selecionado ao focar no campo
$('#buscaCentroCusto').on('focus', function() {
    $(this).removeClass('centro-custo-selecionado');
});

// Limpar seleção se o campo ficar vazio
$('#buscaCentroCusto').on('blur', function() {
    const val = $(this).val().trim();
    if (!val) {
        $('#centroCusto').val('');
        $(this).removeClass('centro-custo-selecionado');
    }
});

let funcionariosCache = {};
let osAtual = null;

// Navegação entre seções
function mostrarSecao(secao, limpar = true) {
    $('.secao-os').removeClass('ativa');
    
    if (secao === 'acoesRapidas') {
        $('#secaoAcoesRapidas').addClass('ativa');
    } else if (secao === 'visualizar') {
        $('#secaoVisualizar').addClass('ativa');
        filtrarOS();
    } else if (secao === 'novaOS') {
        $('#secaoNovaOS').addClass('ativa');
        if (limpar) {
            limparFormulario();
            gerarNumeroOS();
        }
    }
}

// Gerar próximo número de O.S.
function gerarNumeroOS() {
    $.get('/area-tecnica/api/ordens-servico/proximo-numero')
        .done(function(data) {
            $('#numeroOS').val(data.numero_os);
        });
}

// Filtrar O.S.
function filtrarOS() {
    const dataInicial = $('#filtroDataInicial').val();
    const dataFinal = $('#filtroDataFinal').val();
    const numeroOS = $('#filtroNumeroOS').val();
    
    $.get('/area-tecnica/api/ordens-servico/listar', {
        data_inicial: dataInicial,
        data_final: dataFinal,
        numero_os: numeroOS
    })
    .done(function(ordens) {
        renderizarTabela(ordens);
    })
    .fail(function() {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao buscar ordens de serviço'
        });
    });
}

// Limpar filtros
function limparFiltros() {
    const hoje = new Date().toISOString().split('T')[0];
    $('#filtroDataInicial').val(hoje);
    $('#filtroDataFinal').val(hoje);
    $('#filtroNumeroOS').val('');
    filtrarOS();
}

// Renderizar tabela
function renderizarTabela(ordens) {
    let html = '';
    
    if (ordens.length === 0) {
        html = `<tr>
            <td colspan="6" class="text-center text-muted py-4">
                Nenhuma O.S. encontrada para a data.
            </td>
        </tr>`;
    } else {
        ordens.forEach(function(os) {
            const dataFormatada = formatarData(os.data_os);
            const cidadeUF = os.cidade ? `${os.cidade}/${os.estado || ''}` : '-';
            
            html += `<tr>
                <td><strong>${os.numero_os}</strong></td>
                <td>${dataFormatada}</td>
                <td>${os.funcionario_nome || '-'}</td>
                <td>${cidadeUF}</td>
                <td>${os.telefone || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="visualizarOS(${os.id})" title="Visualizar">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="carregarParaEditar(${os.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="confirmarExclusao(${os.id})" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
    }
    
    $('#tabelaOS').html(html);
}

// Formatar data
function formatarData(data) {
    if (!data) return '-';
    const partes = data.split('-');
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

// Funcionário é preenchido automaticamente com o usuário logado
// (campo readonly, não precisa de busca)

/*
// Busca de funcionários (código desativado - agora usa usuário logado)
$('#buscaFuncionario').on('input', function() {
    const nome = $(this).val().trim();
    
    if (nome.length < 3) {
        $('#funcionarioResults').hide();
        return;
    }
    
    $.get('/area-tecnica/api/ordens-servico/funcionarios', { nome: nome })
        .done(function(funcionarios) {
            if (funcionarios.length > 0) {
                funcionariosCache = {};
                let html = '';
                funcionarios.forEach(function(f) {
                    funcionariosCache[f.id] = f;
                    html += `<div class="funcionario-result-item" data-id="${f.id}">
                        <div class="funcionario-nome">${f.nome}</div>
                        <div class="funcionario-funcao">${f.funcao || 'Sem função'}</div>
                    </div>`;
                });
                $('#funcionarioResults').html(html).show();
            } else {
                $('#funcionarioResults').html('<div class="text-center text-muted p-3">Nenhum funcionário encontrado</div>').show();
            }
        });
});

// Código de busca de funcionários desativado - agora usa usuário logado automaticamente
*/

// Limpar formulário
function limparFormulario() {
    $('#osId').val('');
    // Manter funcionário logado (não limpa)
    $('#centroCusto').val('');
    $('#buscaCentroCusto').val('').removeClass('centro-custo-selecionado');
    $('#listaCentrosCusto').hide().empty();
    $('#descricaoServico').val('');
    $('#endereco').val('');
    $('#cidade').val('');
    $('#estado').val('');
    $('#cep').val('');
    $('#telefone').val('');
    $('#cpfCnpj').val('');
    $('.tipo-atendimento-check').prop('checked', false);
    
    // Limpar prestadores
    prestadoresOS = [];
    prestadoresSalvos = [];
    renderizarPrestadores();
    $('#nomePrestador').val('');
    $('#descricaoPrestador').val('');
    $('#valorPrestador').val('');
    inicializarVencimentoPrestador();
    
    // Limpar materiais
    materiaisOS = [];
    materiaisSalvos = [];
    renderizarMateriais();
    $('#buscaMaterial').val('');
    $('#materialSelecionadoId').val('');
    $('#quantidadeMaterial').val('1');
    
    // Limpar solicitações
    solicitacoesOS = [];
    solicitacoesSalvas = [];
    renderizarSolicitacoes();
    $('#descricaoSolicitacao').val('');
    $('#quantidadeSolicitacao').val('1');
    $('#unidadeSolicitacao').val('UN');
    
    const hoje = new Date().toISOString().split('T')[0];
    $('#dataOS').val(hoje);
}

// Obter tipos de atendimento selecionados
function getTiposAtendimento() {
    const tipos = [];
    $('.tipo-atendimento-check:checked').each(function() {
        tipos.push($(this).val());
    });
    return tipos.join(', ');
}

// Setar tipos de atendimento
function setTiposAtendimento(tiposString) {
    $('.tipo-atendimento-check').prop('checked', false);
    if (tiposString) {
        const tipos = tiposString.split(', ');
        tipos.forEach(function(tipo) {
            $(`.tipo-atendimento-check[value="${tipo}"]`).prop('checked', true);
        });
    }
}

// Salvar O.S.
let salvandoOS = false;
$('#formNovaOS').on('submit', function(e) {
    e.preventDefault();
    
    if (salvandoOS) return;
    salvandoOS = true;
    
    const $btnSalvar = $(this).find('.btn-salvar');
    $btnSalvar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...');
    
    const osId = $('#osId').val();
    const dados = {
        data_os: $('#dataOS').val(),
        numero_os: $('#numeroOS').val(),
        funcionario_id: $('#funcionarioId').val() || null,
        centro_custo_id: $('#centroCusto').val() || null,
        tipo_atendimento: getTiposAtendimento(),
        urgencia: $('#urgenciaOS').val(),
        descricao: $('#descricaoServico').val(),
        endereco: $('#endereco').val(),
        cidade: $('#cidade').val(),
        estado: $('#estado').val().toUpperCase(),
        cep: $('#cep').val(),
        telefone: $('#telefone').val(),
        cpf_cnpj: $('#cpfCnpj').val(),
        prestadores: getPrestadoresParaSalvar(),
        materiais: getMateriaisParaSalvar(),
        solicitacoes: getSolicitacoesParaSalvar()
    };
    
    const url = osId ? `/area-tecnica/api/ordens-servico/${osId}` : '/area-tecnica/api/ordens-servico';
    const method = osId ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: dados,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function(response) {
        Swal.fire({
            icon: 'success',
            title: 'Sucesso',
            text: response.message,
            timer: 2000,
            showConfirmButton: false
        }).then(function() {
            window.location.href = '/area-tecnica/gestao-os';
        });
    })
    .fail(function(xhr) {
        salvandoOS = false;
        $btnSalvar.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Salvar');
        
        if (xhr.responseJSON && xhr.responseJSON.errors) {
            let erros = Object.values(xhr.responseJSON.errors).flat().join('\n');
            Swal.fire({
                icon: 'error',
                title: 'Erro de Validação',
                text: erros
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao salvar O.S.'
            });
        }
    });
});

// Visualizar O.S.
function visualizarOS(id) {
    $.get(`/area-tecnica/api/ordens-servico/${id}`)
        .done(function(os) {
            osAtual = os;
            
            // Montar HTML dos prestadores
            let prestadoresHtml = '';
            let totalPrestadores = 0;
            if (os.prestadores && os.prestadores.length > 0) {
                prestadoresHtml = `
                    <hr>
                    <p><strong>Prestadores de Serviço (Terceirizados):</strong></p>
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Prestador</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                os.prestadores.forEach(function(p) {
                    totalPrestadores += parseFloat(p.valor) || 0;
                    prestadoresHtml += `
                        <tr>
                            <td>${p.nome_prestador}</td>
                            <td>${p.descricao_servico || '-'}</td>
                            <td class="text-success font-weight-bold">${formatarMoeda(parseFloat(p.valor) || 0)}</td>
                        </tr>
                    `;
                });
                prestadoresHtml += `
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="2" class="text-right font-weight-bold">Total Prestadores:</td>
                                <td class="font-weight-bold text-success">${formatarMoeda(totalPrestadores)}</td>
                            </tr>
                        </tfoot>
                    </table>
                `;
            }
            
            // Montar HTML dos materiais
            let materiaisHtml = '';
            if (os.materiais && os.materiais.length > 0) {
                materiaisHtml = `
                    <hr>
                    <p><strong>Materiais Utilizados:</strong></p>
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Material</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                os.materiais.forEach(function(m) {
                    materiaisHtml += `
                        <tr>
                            <td>${m.produto_nome}</td>
                            <td>${m.quantidade} un</td>
                        </tr>
                    `;
                });
                materiaisHtml += '</tbody></table>';
            }
            
            // Montar HTML das solicitações
            let solicitacoesHtml = '';
            if (os.solicitacoes && os.solicitacoes.length > 0) {
                solicitacoesHtml = `
                    <hr>
                    <p><strong>Materiais Solicitados:</strong></p>
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Descrição</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                os.solicitacoes.forEach(function(s) {
                    solicitacoesHtml += `
                        <tr>
                            <td>${s.descricao}</td>
                            <td>${s.quantidade}</td>
                            <td>${s.unidade || 'UN'}</td>
                        </tr>
                    `;
                });
                solicitacoesHtml += '</tbody></table>';
            }
            
            // Resumo de custos (se O.S. fechada e tiver totais)
            let resumoCustosHtml = '';
            if (os.status === 'fechada' && (os.valor_total_os > 0 || totalPrestadores > 0)) {
                const valorTotal = parseFloat(os.valor_total_os) || totalPrestadores;
                resumoCustosHtml = `
                    <hr>
                    <div class="alert alert-success">
                        <h6><i class="fas fa-calculator mr-2"></i>Resumo de Custos</h6>
                        <div class="d-flex justify-content-between">
                            <span>Prestadores:</span>
                            <strong>${formatarMoeda(parseFloat(os.valor_total_prestadores) || totalPrestadores)}</strong>
                        </div>
                        ${os.valor_total_materiais > 0 ? `
                        <div class="d-flex justify-content-between">
                            <span>Materiais:</span>
                            <strong>${formatarMoeda(parseFloat(os.valor_total_materiais))}</strong>
                        </div>` : ''}
                        ${os.valor_total_solicitacoes > 0 ? `
                        <div class="d-flex justify-content-between">
                            <span>Solicitações:</span>
                            <strong>${formatarMoeda(parseFloat(os.valor_total_solicitacoes))}</strong>
                        </div>` : ''}
                        <hr class="my-2">
                        <div class="d-flex justify-content-between" style="font-size: 18px;">
                            <strong>TOTAL DA O.S.:</strong>
                            <strong class="text-success">${formatarMoeda(valorTotal)}</strong>
                        </div>
                    </div>
                `;
            }
            
            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Número:</strong> ${os.numero_os}</p>
                        <p><strong>Data:</strong> ${formatarData(os.data_os)}</p>
                        <p><strong>Funcionário:</strong> ${os.funcionario_nome || '-'}</p>
                        <p><strong>Status:</strong> <span class="badge badge-${os.status === 'fechada' ? 'secondary' : 'success'}">${os.status || 'Aberta'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Telefone:</strong> ${os.telefone || '-'}</p>
                        <p><strong>CPF/CNPJ:</strong> ${os.cpf_cnpj || '-'}</p>
                        <p><strong>CEP:</strong> ${os.cep || '-'}</p>
                    </div>
                </div>
                <hr>
                <p><strong>Endereço:</strong> ${os.endereco || '-'}</p>
                <p><strong>Cidade/UF:</strong> ${os.cidade ? `${os.cidade}/${os.estado || ''}` : '-'}</p>
                <hr>
                <p><strong>Descrição do Serviço:</strong></p>
                <p>${os.descricao || '-'}</p>
                ${prestadoresHtml}
                ${materiaisHtml}
                ${solicitacoesHtml}
                ${resumoCustosHtml}
            `;
            
            $('#modalOSConteudo').html(html);
            $('#modalVisualizarOS').modal('show');
        });
}

// Carregar para editar
function carregarParaEditar(id) {
    $.get(`/area-tecnica/api/ordens-servico/${id}`)
        .done(function(os) {
            $('#osId').val(os.id);
            $('#dataOS').val(os.data_os);
            $('#numeroOS').val(os.numero_os);
            $('#funcionarioId').val(os.funcionario_id || '');
            $('#buscaFuncionario').val(os.funcionario_nome || '');
            $('#centroCusto').val(os.centro_custo_id || '');
            // Preencher campo de busca do centro de custo
            if (os.centro_custo_id && os.centro_custo_nome) {
                $('#buscaCentroCusto').val(os.centro_custo_nome).addClass('centro-custo-selecionado');
            } else {
                $('#buscaCentroCusto').val('').removeClass('centro-custo-selecionado');
            }
            setTiposAtendimento(os.tipo_atendimento || '');
            $('#descricaoServico').val(os.descricao);
            $('#endereco').val(os.endereco || '');
            $('#cidade').val(os.cidade || '');
            $('#estado').val(os.estado || '');
            $('#cep').val(os.cep || '');
            $('#telefone').val(os.telefone || '');
            $('#cpfCnpj').val(os.cpf_cnpj || '');
            
            // Carregar prestadores
            if (os.prestadores && os.prestadores.length > 0) {
                setPrestadores(os.prestadores);
            } else {
                prestadoresOS = [];
                renderizarPrestadores();
            }
            
            // Carregar materiais (como salvos - bloqueados)
            if (os.materiais && os.materiais.length > 0) {
                materiaisSalvos = os.materiais.map(m => ({
                    id: m.produto_id,
                    nome: m.produto_nome,
                    estoque: m.estoque_atual,
                    quantidade: m.quantidade,
                    salvo: true
                }));
                materiaisOS = []; // Limpa novos
                renderizarMateriais();
            } else {
                materiaisSalvos = [];
                materiaisOS = [];
                renderizarMateriais();
            }
            
            // Carregar solicitações (como salvas - bloqueadas)
            if (os.solicitacoes && os.solicitacoes.length > 0) {
                solicitacoesSalvas = os.solicitacoes.map(s => ({
                    descricao: s.descricao,
                    quantidade: parseFloat(s.quantidade),
                    unidade: s.unidade || 'UN',
                    salvo: true
                }));
                solicitacoesOS = []; // Limpa novas
                renderizarSolicitacoes();
            } else {
                solicitacoesSalvas = [];
                solicitacoesOS = [];
                renderizarSolicitacoes();
            }
            
            // Inicializar campo de vencimento para novos prestadores
            inicializarVencimentoPrestador();
            
            // Mostrar seção sem limpar (false = não limpa o formulário)
            mostrarSecao('novaOS', false);
        });
}

// Editar do modal
function editarOS() {
    if (osAtual) {
        $('#modalVisualizarOS').modal('hide');
        carregarParaEditar(osAtual.id);
    }
}

// Confirmar exclusão
function confirmarExclusao(id) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: 'Tem certeza que deseja excluir esta O.S.? Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            excluirOSPorId(id);
        }
    });
}

// Excluir do modal
function excluirOS() {
    if (osAtual) {
        Swal.fire({
            title: 'Confirmar Exclusão',
            text: 'Tem certeza que deseja excluir esta O.S.? Esta ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                excluirOSPorId(osAtual.id);
                $('#modalVisualizarOS').modal('hide');
            }
        });
    }
}

// Excluir por ID
function excluirOSPorId(id) {
    $.ajax({
        url: `/area-tecnica/api/ordens-servico/${id}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function(response) {
        Swal.fire({
            icon: 'success',
            title: 'Sucesso',
            text: response.message,
            timer: 2000,
            showConfirmButton: false
        });
        filtrarOS();
    })
    .fail(function() {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao excluir O.S.'
        });
    });
}

// Máscaras
$('#cep').on('input', function() {
    let v = $(this).val().replace(/\D/g, '');
    if (v.length > 5) v = v.substring(0,5) + '-' + v.substring(5,8);
    $(this).val(v);
});

$('#telefone').on('input', function() {
    let v = $(this).val().replace(/\D/g, '');
    if (v.length > 10) {
        v = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7,11);
    } else if (v.length > 6) {
        v = '(' + v.substring(0,2) + ') ' + v.substring(2,6) + '-' + v.substring(6,10);
    } else if (v.length > 2) {
        v = '(' + v.substring(0,2) + ') ' + v.substring(2);
    }
    $(this).val(v);
});

// ========================================
// PRESTADORES DE SERVIÇO (TERCEIRIZADOS)
// ========================================
let prestadoresOS = []; // Lista de prestadores adicionados à OS
let prestadoresSalvos = []; // Prestadores já salvos no banco (não podem ser removidos)

// Formatar valor para exibição
function formatarMoeda(valor) {
    return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

// Converter string formatada para número
function converterParaNumero(valorStr) {
    if (!valorStr) return 0;
    // Remove R$, pontos de milhar e troca vírgula por ponto
    return parseFloat(valorStr.replace(/[R$\s.]/g, '').replace(',', '.')) || 0;
}

// Máscara para valor monetário
$('#valorPrestador').on('input', function() {
    let v = $(this).val().replace(/\D/g, '');
    if (v.length === 0) {
        $(this).val('');
        return;
    }
    v = (parseInt(v) / 100).toFixed(2);
    v = v.replace('.', ',');
    v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    $(this).val(v);
});

// Limpar campo de vencimento do prestador
function inicializarVencimentoPrestador() {
    $('#vencimentoPrestador').val('');
}

// Adicionar prestador à lista
function adicionarPrestador() {
    const nome = $('#nomePrestador').val().trim();
    const descricao = $('#descricaoPrestador').val().trim();
    const valorStr = $('#valorPrestador').val();
    const valor = converterParaNumero(valorStr);
    const vencimento = $('#vencimentoPrestador').val();
    
    if (!nome) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Informe o nome do prestador'
        });
        return;
    }
    
    if (valor <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Informe um valor válido'
        });
        return;
    }
    
    if (!vencimento) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Informe a data de vencimento'
        });
        return;
    }
    
    // Adicionar à lista (novos prestadores)
    prestadoresOS.push({
        nome: nome,
        descricao: descricao,
        valor: valor,
        vencimento: vencimento,
        salvo: false, // Indica que ainda não foi salvo
        status_pagamento: 'pendente'
    });
    
    renderizarPrestadores();
    
    // Limpar campos e reiniciar vencimento
    $('#nomePrestador').val('');
    $('#descricaoPrestador').val('');
    $('#valorPrestador').val('');
    inicializarVencimentoPrestador();
}

// Renderizar tabela de prestadores
function renderizarPrestadores() {
    const todosPrestadores = [...prestadoresSalvos, ...prestadoresOS];
    
    if (todosPrestadores.length === 0) {
        $('#listaPrestadores').html(`
            <tr id="semPrestadores">
                <td colspan="6" class="text-center text-muted py-3">
                    Nenhum prestador adicionado
                </td>
            </tr>
        `);
        $('#footerPrestadores').hide();
        return;
    }
    
    let html = '';
    let total = 0;
    
    // Primeiro renderiza os salvos (bloqueados)
    prestadoresSalvos.forEach(function(p, index) {
        total += p.valor;
        const vencFormatado = p.vencimento ? formatarData(p.vencimento) : '-';
        const statusBadge = p.status_pagamento === 'pago' 
            ? '<span class="badge badge-success">Pago</span>'
            : '<span class="badge badge-warning">Pendente</span>';
        
        html += `<tr class="bg-light">
            <td><strong>${p.nome}</strong> <i class="fas fa-lock text-muted ml-1" title="Item já salvo"></i></td>
            <td>${p.descricao || '-'}</td>
            <td class="text-success font-weight-bold">${formatarMoeda(p.valor)}</td>
            <td>${vencFormatado}</td>
            <td>${statusBadge}</td>
            <td>
                <span class="text-muted" title="Não é possível remover itens já salvos">
                    <i class="fas fa-lock"></i>
                </span>
            </td>
        </tr>`;
    });
    
    // Depois renderiza os novos (podem ser removidos)
    prestadoresOS.forEach(function(p, index) {
        total += p.valor;
        const vencFormatado = p.vencimento ? formatarData(p.vencimento) : '-';
        
        html += `<tr>
            <td><strong>${p.nome}</strong> <span class="badge badge-info ml-1">Novo</span></td>
            <td>${p.descricao || '-'}</td>
            <td class="text-success font-weight-bold">${formatarMoeda(p.valor)}</td>
            <td>${vencFormatado}</td>
            <td><span class="badge badge-secondary">A salvar</span></td>
            <td>
                <button type="button" class="btn btn-danger btn-remover-prestador" onclick="removerPrestador(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });
    
    $('#listaPrestadores').html(html);
    $('#totalPrestadores').text(formatarMoeda(total));
    $('#footerPrestadores').show();
}

// Remover prestador da lista (apenas novos)
function removerPrestador(index) {
    prestadoresOS.splice(index, 1);
    renderizarPrestadores();
}

// Obter prestadores para salvar (apenas os novos)
function getPrestadoresParaSalvar() {
    return prestadoresOS.map(p => ({
        nome_prestador: p.nome,
        descricao_servico: p.descricao,
        valor: p.valor,
        vencimento: p.vencimento
    }));
}

// Setar prestadores ao carregar OS (como salvos - bloqueados)
function setPrestadores(prestadores) {
    prestadoresSalvos = (prestadores || []).map(p => ({
        id: p.id,
        nome: p.nome_prestador || p.nome,
        descricao: p.descricao_servico || p.descricao || '',
        valor: parseFloat(p.valor) || 0,
        vencimento: p.data_vencimento || p.vencimento || null,
        status_pagamento: p.status_pagamento || 'pendente',
        salvo: true
    }));
    prestadoresOS = []; // Limpa os novos
    renderizarPrestadores();
}

// ========================================
// MATERIAIS
// ========================================
let materiaisCache = {};
let materiaisOS = []; // Lista de materiais novos
let materiaisSalvos = []; // Materiais já salvos (não podem ser removidos)

// Busca de materiais
$('#buscaMaterial').on('input', function() {
    const nome = $(this).val().trim();
    
    if (nome.length < 3) {
        $('#materialResults').hide();
        return;
    }
    
    $.get('/area-tecnica/api/materiais/buscar', { nome: nome })
        .done(function(produtos) {
            if (produtos.length > 0) {
                materiaisCache = {};
                let html = '';
                produtos.forEach(function(p) {
                    materiaisCache[p.id] = p;
                    const estoqueClass = p.quantidade <= 5 ? 'baixo' : '';
                    html += `<div class="material-result-item" data-id="${p.id}">
                        <div class="material-nome">${p.nome}</div>
                        <div class="material-estoque ${estoqueClass}">Estoque: ${p.quantidade} unidades</div>
                    </div>`;
                });
                $('#materialResults').html(html).show();
            } else {
                $('#materialResults').html('<div class="text-center text-muted p-3">Nenhum material encontrado</div>').show();
            }
        });
});

// Selecionar material
$(document).on('click', '.material-result-item', function() {
    const id = $(this).data('id');
    const material = materiaisCache[id];
    
    if (material) {
        $('#materialSelecionadoId').val(material.id);
        $('#materialSelecionadoNome').val(material.nome);
        $('#materialSelecionadoEstoque').val(material.quantidade);
        $('#buscaMaterial').val(material.nome);
        $('#materialResults').hide();
    }
});

// Esconder resultados ao clicar fora
$(document).on('click', function(e) {
    if (!$(e.target).closest('#buscaMaterial, #materialResults').length) {
        $('#materialResults').hide();
    }
});

// Adicionar material à lista
function adicionarMaterial() {
    const id = $('#materialSelecionadoId').val();
    const nome = $('#materialSelecionadoNome').val();
    const estoqueAtual = parseInt($('#materialSelecionadoEstoque').val()) || 0;
    const quantidade = parseInt($('#quantidadeMaterial').val()) || 1;
    
    if (!id || !nome) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Selecione um material da lista'
        });
        return;
    }
    
    if (quantidade <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Informe uma quantidade válida'
        });
        return;
    }
    
    if (quantidade > estoqueAtual) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: `Quantidade maior que o estoque disponível (${estoqueAtual})`
        });
        return;
    }
    
    // Verificar se já está na lista e somar quantidade se existir
    const jaExisteNovos = materiaisOS.find(m => m.id == id);
    const jaExisteSalvos = materiaisSalvos.find(m => m.id == id);
    
    if (jaExisteNovos) {
        // Já existe nos novos - somar quantidade
        const novaQtd = jaExisteNovos.quantidade + quantidade;
        if (novaQtd > estoqueAtual) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: `Quantidade total (${novaQtd}) maior que o estoque disponível (${estoqueAtual})`
            });
            return;
        }
        jaExisteNovos.quantidade = novaQtd;
    } else if (jaExisteSalvos) {
        // Já existe nos salvos - adicionar como novo para somar
        materiaisOS.push({
            id: id,
            nome: nome,
            estoque: estoqueAtual,
            quantidade: quantidade
        });
    } else {
        // Novo material - adicionar à lista
        materiaisOS.push({
            id: id,
            nome: nome,
            estoque: estoqueAtual,
            quantidade: quantidade
        });
    }
    
    renderizarMateriais();
    
    // Limpar campos
    $('#buscaMaterial').val('');
    $('#materialSelecionadoId').val('');
    $('#materialSelecionadoNome').val('');
    $('#materialSelecionadoEstoque').val('');
    $('#quantidadeMaterial').val('1');
}

// Renderizar tabela de materiais
function renderizarMateriais() {
    const todosMateriais = [...materiaisSalvos, ...materiaisOS];
    
    if (todosMateriais.length === 0) {
        $('#listaMateriais').html(`
            <tr id="semMateriais">
                <td colspan="4" class="text-center text-muted py-3">
                    Nenhum material adicionado
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    
    // Primeiro os salvos (bloqueados)
    materiaisSalvos.forEach(function(m, index) {
        html += `<tr class="bg-light">
            <td><strong>${m.nome}</strong> <i class="fas fa-lock text-muted ml-1" title="Item já salvo"></i></td>
            <td>${m.estoque} un</td>
            <td>${m.quantidade} un</td>
            <td>
                <span class="text-muted" title="Não é possível remover itens já salvos">
                    <i class="fas fa-lock"></i>
                </span>
            </td>
        </tr>`;
    });
    
    // Depois os novos (podem ser removidos)
    materiaisOS.forEach(function(m, index) {
        html += `<tr>
            <td><strong>${m.nome}</strong> <span class="badge badge-info ml-1">Novo</span></td>
            <td>${m.estoque} un</td>
            <td>${m.quantidade} un</td>
            <td>
                <button type="button" class="btn btn-danger btn-remover-material" onclick="removerMaterial(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });
    
    $('#listaMateriais').html(html);
}

// Remover material da lista (apenas novos)
function removerMaterial(index) {
    materiaisOS.splice(index, 1);
    renderizarMateriais();
}

// Obter materiais para salvar (apenas novos)
function getMateriaisParaSalvar() {
    return materiaisOS.map(m => ({
        produto_id: m.id,
        quantidade: m.quantidade
    }));
}

// Setar materiais ao carregar OS
function setMateriais(materiais) {
    materiaisOS = materiais || [];
    renderizarMateriais();
}

// ========================================
// SOLICITAÇÕES DE MATERIAIS
// ========================================
let solicitacoesOS = []; // Lista de solicitações novas
let solicitacoesSalvas = []; // Solicitações já salvas (não podem ser removidas)

// Adicionar solicitação à lista
function adicionarSolicitacao() {
    const descricao = $('#descricaoSolicitacao').val().trim();
    const quantidade = parseFloat($('#quantidadeSolicitacao').val()) || 1;
    const unidade = $('#unidadeSolicitacao').val();
    
    if (!descricao) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Informe a descrição do material'
        });
        return;
    }
    
    if (quantidade <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Informe uma quantidade válida'
        });
        return;
    }
    
    // Adicionar à lista (novos)
    solicitacoesOS.push({
        descricao: descricao,
        quantidade: quantidade,
        unidade: unidade,
        salvo: false
    });
    
    renderizarSolicitacoes();
    
    // Limpar campos
    $('#descricaoSolicitacao').val('');
    $('#quantidadeSolicitacao').val('1');
    $('#unidadeSolicitacao').val('UN');
}

// Renderizar tabela de solicitações
function renderizarSolicitacoes() {
    const todasSolicitacoes = [...solicitacoesSalvas, ...solicitacoesOS];
    
    if (todasSolicitacoes.length === 0) {
        $('#listaSolicitacoes').html(`
            <tr id="semSolicitacoes">
                <td colspan="4" class="text-center text-muted py-3">
                    Nenhum material solicitado
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    
    // Primeiro as salvas (bloqueadas)
    solicitacoesSalvas.forEach(function(s, index) {
        html += `<tr class="bg-light">
            <td><strong>${s.descricao}</strong> <i class="fas fa-lock text-muted ml-1" title="Item já salvo"></i></td>
            <td>${s.quantidade}</td>
            <td>${s.unidade}</td>
            <td>
                <span class="text-muted" title="Não é possível remover itens já salvos">
                    <i class="fas fa-lock"></i>
                </span>
            </td>
        </tr>`;
    });
    
    // Depois as novas (podem ser removidas)
    solicitacoesOS.forEach(function(s, index) {
        html += `<tr>
            <td><strong>${s.descricao}</strong> <span class="badge badge-info ml-1">Novo</span></td>
            <td>${s.quantidade}</td>
            <td>${s.unidade}</td>
            <td>
                <button type="button" class="btn btn-danger btn-remover-solicitacao" onclick="removerSolicitacao(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });
    
    $('#listaSolicitacoes').html(html);
}

// Remover solicitação da lista
function removerSolicitacao(index) {
    solicitacoesOS.splice(index, 1);
    renderizarSolicitacoes();
}

// Obter solicitações para salvar
function getSolicitacoesParaSalvar() {
    return solicitacoesOS.map(s => ({
        descricao: s.descricao,
        quantidade: s.quantidade,
        unidade: s.unidade
    }));
}
</script>
@stop
