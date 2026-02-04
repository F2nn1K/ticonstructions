@extends('adminlte::page')

@section('title', 'Solicitação de Pedido de Compras')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-plus-circle text-primary mr-3"></i>
            Solicitação de Pedido de Compras
        </h1>
        <small class="text-muted">Solicite seus pedidos de compras</small>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Nova Solicitação de Compra
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_solicitacao">Data da Solicitação</label>
                                <input type="date" class="form-control" id="data_solicitacao" value="{{ date('Y-m-d') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitante">Solicitante</label>
                                <input type="text" class="form-control" id="solicitante" value="{{ auth()->user()->name }}" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prioridade">Prioridade</label>
                                <select class="form-control" id="prioridade">
                                    <option value="baixa">Baixa</option>
                                    <option value="media" selected>Média</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="centro_custo">Centro de Custo</label>
                                <input type="text" class="form-control" id="centro_custo" placeholder="Digite ao menos 3 letras para buscar centro de custo..." autocomplete="off">
                                <input type="hidden" id="centro_custo_id" name="centro_custo_id">
                                <div id="cc-suggestions" class="dropdown-menu w-100" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos Rota e Roteirização -->
                    <div class="row" id="rota-container">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rota">Rota</label>
                                <select class="form-control" id="rota" disabled>
                                    <option value="">Selecione primeiro um centro de custo...</option>
                                </select>
                                <input type="hidden" id="rota_id" name="rota_id">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="roteirizacao">Roteirização</label>
                                <select class="form-control" id="roteirizacao" disabled>
                                    <option value="">Selecione primeiro uma rota...</option>
                                </select>
                                <input type="hidden" id="roteirizacao_id" name="roteirizacao_id">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção para adicionar produtos -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="produto">Produto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="produto" placeholder="Digite ao menos 3 letras para buscar produtos..." autocomplete="off">
                                <div id="produto-suggestions" class="dropdown-menu w-100" style="display: none;"></div>
                                <input type="hidden" id="produto_id" name="produto_id">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="quantidade">Quantidade <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantidade" min="1" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-success btn-block" id="btn-adicionar-produto">
                                    <i class="fas fa-plus mr-2"></i>Adicionar Produto
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="observacoes">Observações</label>
                                <textarea class="form-control" id="observacoes" rows="3" placeholder="Observações adicionais..."></textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-primary" id="btn-enviar-solicitacao">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Solicitação
                    </button>
                    <button type="button" class="btn btn-secondary ml-2" id="btn-cancelar">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </div>
            
            <!-- Lista de produtos adicionados - apenas para conferência -->
            <div class="row" id="produtos-container" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-eye mr-2 text-muted"></i>
                                <span class="text-muted">Produtos para Conferência</span>
                                <span class="badge badge-secondary ml-2" id="contador-produtos">0</span>
                            </h5>
                            <small class="text-muted">Confira os produtos selecionados antes de enviar</small>
                        </div>
                        <div class="card-body">
                            <div id="produtos-list">
                                <!-- Os produtos serão inseridos aqui dinamicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    

</div>
@stop

@section('css')
<style>
    .card-primary {
        border-top: 3px solid #007bff;
    }
    
    .form-group label {
        font-weight: 600;
        color: #495057;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    /* Autocomplete Styles */
    #produto-suggestions, #cc-suggestions {
        max-height: 200px;
        overflow-y: auto;
        position: absolute;
        z-index: 1000;
        background: white;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .suggestion-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    
    .suggestion-item:hover {
        background-color: #f8f9fa;
    }
    
    .suggestion-item:last-child {
        border-bottom: none;
    }
    
    .suggestion-name {
        font-weight: bold;
        color: #333;
    }
    
    .suggestion-desc {
        font-size: 0.9em;
        color: #666;
        margin-top: 2px;
    }
    
    .suggestion-cc {
        font-size: 0.8em;
        color: #888;
        margin-top: 2px;
    }
    
    /* Modo escuro - Autocomplete Styles */
    html[data-theme="dark"] #produto-suggestions, 
    html[data-theme="dark"] #cc-suggestions {
        background: #1e293b !important;
        border: 1px solid #475569 !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.4) !important;
    }
    
    html[data-theme="dark"] .suggestion-item {
        border-bottom: 1px solid #334155 !important;
        background: #1e293b !important;
    }
    
    html[data-theme="dark"] .suggestion-item:hover {
        background-color: #334155 !important;
    }
    
    html[data-theme="dark"] .suggestion-name {
        color: #f1f5f9 !important;
    }
    
    html[data-theme="dark"] .suggestion-desc {
        color: #94a3b8 !important;
    }
    
    html[data-theme="dark"] .suggestion-cc {
        color: #94a3b8 !important;
    }
    
    /* Estilos para os cards de produtos */
    .produto-card {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .produto-card .card-body {
        padding: 1rem;
    }
    
    .produto-card .card-title {
        color: #495057;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .produto-card .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        font-size: 0.875rem;
    }
    
    .produto-card .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .btn-adicionar-produto {
        margin-top: 8px;
    }
    

    
    /* Estilo para empty state */
    .empty-produtos {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
    }
    
    .empty-produtos i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    

</style>
@stop

@section('js')
<script>
    let produtosSelecionados = [];
    
    $(document).ready(function() {
        // Botão adicionar produto
        $('#btn-adicionar-produto').on('click', function() {
            adicionarProduto();
        });
        
        // Permitir adicionar produto com Enter
        $('#quantidade').on('keypress', function(e) {
            if (e.which === 13) {
                adicionarProduto();
            }
        });
        
        // Botão enviar solicitação
        $('#btn-enviar-solicitacao').on('click', function() {
            enviarSolicitacao();
        });
        
        // Botão cancelar
        $('#btn-cancelar').on('click', function() {
            limparFormulario();
        });
        
        // Autocomplete para produtos
        let produtoTimeoutId;
        $('#produto').on('input', function() {
            const termo = $(this).val();
            
            // Limpar timeout anterior
            if (produtoTimeoutId) {
                clearTimeout(produtoTimeoutId);
            }
            
            // Esconder sugestões se termo for muito curto
            if (termo.length < 3) {
                $('#produto-suggestions').hide();
                $('#produto_id').val('');
                return;
            }
            
            // Delay para evitar muitas requisições
            produtoTimeoutId = setTimeout(function() {
                buscarProdutos(termo);
            }, 300);
        });
        
        // Autocomplete para centro de custo
        let centroCustoTimeoutId;
        $('#centro_custo').on('input', function() {
            const termo = $(this).val();
            
            // Limpar timeout anterior
            if (centroCustoTimeoutId) {
                clearTimeout(centroCustoTimeoutId);
            }
            
            // Esconder sugestões se termo for muito curto
            if (termo.length < 3) {
                $('#cc-suggestions').hide();
                $('#centro_custo_id').val('');
                return;
            }
            
            // Delay para evitar muitas requisições
            centroCustoTimeoutId = setTimeout(function() {
                buscarCentrosCusto(termo);
            }, 300);
        });
        
        // Centro de custo agora é preenchido automaticamente ao escolher o produto
        
        // Esconder sugestões ao clicar fora
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#produto, #produto-suggestions').length) {
                $('#produto-suggestions').hide();
            }
            if (!$(e.target).closest('#centro_custo, #cc-suggestions').length) {
                $('#cc-suggestions').hide();
            }
        });
        
        // Event listeners para os novos campos Rota e Roteirização
        // Quando selecionar uma rota
        $('#rota').on('change', function() {
            const rotaId = $(this).val();
            $('#rota_id').val(rotaId);
            
            if (rotaId) {
                carregarRoteirizacoesPorRota(rotaId);
            } else {
                $('#roteirizacao').html('<option value="">Selecione primeiro uma rota...</option>').prop('disabled', true);
                $('#roteirizacao_id').val('');
            }
        });
        
        // Quando selecionar uma roteirização
        $('#roteirizacao').on('change', function() {
            const roteirizacaoId = $(this).val();
            $('#roteirizacao_id').val(roteirizacaoId);
        });
        
        // Página de Solicitação de Pedido de Compras carregada
    });
    
    function adicionarProduto() {
        const produtoId = $('#produto_id').val();
        const produtoNome = $('#produto').val();
        const quantidade = $('#quantidade').val();
        
        // Validações
        if (!produtoId || !produtoNome) {
            Swal.fire('Atenção!', 'Selecione um produto válido.', 'warning');
            return;
        }
        
        if (!quantidade || quantidade < 1) {
            Swal.fire('Atenção!', 'Informe uma quantidade válida.', 'warning');
            return;
        }
        
        // Verificar se o produto já foi adicionado
        const produtoExistente = produtosSelecionados.find(p => p.id === produtoId);
        if (produtoExistente) {
            Swal.fire('Atenção!', 'Este produto já foi adicionado à lista.', 'warning');
            return;
        }
        
        // Adicionar produto à lista
        const produto = {
            id: produtoId,
            nome: produtoNome,
            quantidade: parseInt(quantidade)
        };
        
        produtosSelecionados.push(produto);
        atualizarTabelaProdutos();
        limparFormularioProduto();
    }
    
    function atualizarTabelaProdutos() {
        const container = $('#produtos-list');
        container.empty();
        
        // Atualizar contador
        $('#contador-produtos').text(produtosSelecionados.length);
        
        if (produtosSelecionados.length === 0) {
            $('#produtos-container').hide();
            return;
        }
        
        $('#produtos-container').show();
        
        produtosSelecionados.forEach(function(produto, index) {
            const card = `
                <div class="card mb-3 produto-card" data-index="${index}">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1">
                                    <i class="fas fa-box text-primary mr-2"></i>
                                    ${produto.nome}
                                </h6>
                                <small class="text-muted">Produto #${produto.id}</small>
                            </div>
                            <div class="mx-3" style="min-width: 120px;">
                                <label class="small font-weight-bold text-muted d-block mb-1">QUANTIDADE</label>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${produto.quantidade}" min="1" 
                                       onchange="atualizarQuantidade(${index}, this.value)">
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                        onclick="removerProduto(${index})" title="Remover produto">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(card);
        });
    }
    
    function limparFormularioProduto() {
        $('#produto').val('');
        $('#produto_id').val('');
        $('#quantidade').val('1');
        $('#produto-suggestions').hide();
    }
    
    function atualizarQuantidade(index, novaQuantidade) {
        if (novaQuantidade && novaQuantidade > 0) {
            produtosSelecionados[index].quantidade = parseInt(novaQuantidade);
        }
    }
    

    
    function removerProduto(index) {
        Swal.fire({
            title: 'Confirmar remoção',
            text: 'Deseja remover este produto da lista?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                produtosSelecionados.splice(index, 1);
                atualizarTabelaProdutos();
            }
        });
    }
    
    function enviarSolicitacao() {
        // Validar dados obrigatórios
        const centroCustoId = $('#centro_custo_id').val();
        const prioridade = $('#prioridade').val();
        
        if (!centroCustoId) {
            Swal.fire('Atenção!', 'Selecione um centro de custo.', 'warning');
            return;
        }
        
        if (produtosSelecionados.length === 0) {
            Swal.fire('Atenção!', 'Adicione pelo menos um produto à solicitação.', 'warning');
            return;
        }
        
        // Preparar dados para envio
        const dados = {
            centro_custo_id: centroCustoId,
            rota_id: $('#rota_id').val(),
            roteirizacao_id: $('#roteirizacao_id').val(),
            prioridade: prioridade,
            observacao: $('#observacoes').val(),
            produtos: produtosSelecionados
        };
        
        // Confirmar envio
        Swal.fire({
            title: 'Confirmar envio',
            text: `Deseja enviar a solicitação com ${produtosSelecionados.length} produto(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/pedidos',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(dados),
                    success: function(resp) {
                        if (resp.success) {
                            Swal.fire('Sucesso!', resp.message || 'Solicitação enviada com sucesso!', 'success')
                                .then(() => limparFormulario());
                        } else {
                            Swal.fire('Atenção!', resp.message || 'Não foi possível enviar.', 'warning');
                        }
                    },
                    error: function(xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao enviar solicitação';
                        Swal.fire('Erro!', msg, 'error');
                    }
                });
            }
        });
    }
    
    function limparFormulario() {
        // Limpar campos do formulário
        $('#centro_custo').val('');
        $('#centro_custo_id').val('');
        $('#prioridade').val('media');
        $('#observacoes').val('');
        
        // Limpar campos Rota e Roteirização
        $('#rota').html('<option value="">Selecione primeiro um centro de custo...</option>').prop('disabled', true);
        $('#roteirizacao').html('<option value="">Selecione primeiro uma rota...</option>').prop('disabled', true);
        $('#rota_id').val('');
        $('#roteirizacao_id').val('');
        $('#rota-container').hide();
        
        // Limpar produtos
        produtosSelecionados = [];
        atualizarTabelaProdutos();
        limparFormularioProduto();
        
        // Limpar sugestões de centro de custo também
        $('#cc-suggestions').hide();
    }
    
    function buscarCentrosCusto(termo) {
        $.ajax({
            url: '/api/centro-custos/buscar',
            method: 'GET',
            data: { termo: termo },
            success: function(response) {
                const suggestions = $('#cc-suggestions');
                suggestions.empty();
                
                if (response.success && response.data && response.data.length > 0) {
                    response.data.forEach(function(cc) {
                        const item = $(`
                            <div class="suggestion-item" data-id="${cc.id}" data-nome="${cc.nome}">
                                <div class="suggestion-name">${cc.nome}</div>
                            </div>
                        `);
                        
                        item.on('click', function() {
                            $('#centro_custo').val(cc.nome);
                            $('#centro_custo_id').val(cc.id);
                            suggestions.hide();
                            
                            // Carregar rotas do centro de custo selecionado
                            carregarRotasPorCentroCusto(cc.id);
                        });
                        
                        suggestions.append(item);
                    });
                    suggestions.show();
                } else {
                    suggestions.append('<div class="suggestion-item">Nenhum centro de custo encontrado</div>');
                    suggestions.show();
                }
            },
            error: function() {
                const suggestions = $('#cc-suggestions');
                suggestions.html('<div class="suggestion-item">Erro ao buscar centros de custo</div>');
                suggestions.show();
            }
        });
    }
    
    function buscarProdutos(termo) {
        $.ajax({
            url: '/api/produtos/buscar',
            method: 'GET',
            data: { termo: termo },
            success: function(response) {
                const suggestions = $('#produto-suggestions');
                suggestions.empty();
                
                if (response.success && response.data && response.data.length > 0) {
                    response.data.forEach(function(produto) {
                        const item = $(`
                            <div class=\"suggestion-item\" data-id=\"${produto.id}\" data-nome=\"${produto.nome}\" data-cc-id=\"${produto.centro_custo_id || ''}\" data-cc-nome=\"${produto.centro_custo_nome || ''}\">\n                                <div class=\"suggestion-name\">${produto.nome}</div>\n                                <div class=\"suggestion-cc\">Centro de Custo: ${produto.centro_custo_nome || '—'}</div>\n                            </div>
                        `);
                        
                        item.on('click', function() {
                            $('#produto').val(produto.nome);
                            $('#produto_id').val(produto.id);
                            // Preencher centro de custo automático se vier do produto
                            const ccId = $(this).data('cc-id');
                            const ccNome = $(this).data('cc-nome');
                            if (ccId) {
                                $('#centro_custo_id').val(ccId);
                                $('#centro_custo').val(ccNome || '');
                            }
                            suggestions.hide();
                        });
                        
                        suggestions.append(item);
                    });
                    suggestions.show();
                } else {
                    suggestions.append(`<div class="suggestion-item">Nenhum produto encontrado para "${termo}"</div>`);
                    suggestions.show();
                }
            },
            error: function() {
                const suggestions = $('#produto-suggestions');
                suggestions.html('<div class="suggestion-item">Erro ao buscar produtos</div>');
                suggestions.show();
            }
        });
    }
    
    // Função para carregar rotas por centro de custo
    function carregarRotasPorCentroCusto(centroCustoId) {
        // Limpar campos dependentes
        $('#rota').html('<option value="">Carregando rotas...</option>').prop('disabled', true);
        $('#roteirizacao').html('<option value="">Selecione primeiro uma rota...</option>').prop('disabled', true);
        $('#rota_id').val('');
        $('#roteirizacao_id').val('');
        
        $.ajax({
            url: '/api/rotas/por-centro-custo',
            method: 'GET',
            data: { centro_custo_id: centroCustoId },
            success: function(response) {
                const rotaSelect = $('#rota');
                rotaSelect.empty();
                
                if (response.success && response.data && response.data.length > 0) {
                    rotaSelect.append('<option value="">Selecione uma rota...</option>');
                    response.data.forEach(function(rota) {
                        rotaSelect.append(`<option value="${rota.id}">${rota.nome_rota}</option>`);
                    });
                    rotaSelect.prop('disabled', false);
                    $('#rota-container').show();
                } else {
                    rotaSelect.append('<option value="">Nenhuma rota encontrada para este centro de custo</option>');
                }
            },
            error: function() {
                $('#rota').html('<option value="">Erro ao carregar rotas</option>');
            }
        });
    }
    
    // Função para carregar roteirizações por rota
    function carregarRoteirizacoesPorRota(rotaId) {
        // Limpar campo dependente
        $('#roteirizacao').html('<option value="">Carregando roteirizações...</option>').prop('disabled', true);
        $('#roteirizacao_id').val('');
        
        $.ajax({
            url: '/api/roteirizacoes/por-rota',
            method: 'GET',
            data: { rota_id: rotaId },
            success: function(response) {
                const roteirizacaoSelect = $('#roteirizacao');
                roteirizacaoSelect.empty();
                
                if (response.success && response.data && response.data.length > 0) {
                    roteirizacaoSelect.append('<option value="">Selecione uma roteirização...</option>');
                    response.data.forEach(function(roteirizacao) {
                        roteirizacaoSelect.append(`<option value="${roteirizacao.id}">${roteirizacao.nome}</option>`);
                    });
                    roteirizacaoSelect.prop('disabled', false);
                } else {
                    roteirizacaoSelect.append('<option value="">Nenhuma roteirização encontrada para esta rota</option>');
                }
            },
            error: function() {
                $('#roteirizacao').html('<option value="">Erro ao carregar roteirizações</option>');
            }
        });
    }
</script>
@stop
