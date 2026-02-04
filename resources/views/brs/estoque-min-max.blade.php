{{-- ESTOQUE MÍNIMO E MÁXIMO - BRS SISTEMA --}}

@extends('adminlte::page')

@section('title', 'Estoque - Mínimo e Máximo')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-layer-group text-primary mr-3"></i>
            Estoque - Mínimo e Máximo
        </h1>
        <p class="text-muted mt-1 mb-0">Configure os níveis ideais para cada produto do seu estoque</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Cards de estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="totalProdutos">--</div>
                    <div class="stat-label">Produtos Cadastrados</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-circle text-muted"></i>
                    <span class="text-muted">Total no sistema</span>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="produtosConfigurados">--</div>
                    <div class="stat-label">Com Níveis Definidos</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-circle text-muted"></i>
                    <span class="text-muted">Min/Max configurados</span>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="stat-card stat-card-danger">
                <div class="stat-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="produtosAbaixoMinimo">--</div>
                    <div class="stat-label">Abaixo do Mínimo</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-circle text-muted"></i>
                    <span class="text-muted">Necessitam reposição</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção principal -->
    <div class="row">
        <div class="col-12">
            <div class="modern-card">
                <div class="card-header-modern">
                    <h5 class="card-title-modern">
                        <i class="fas fa-table text-primary mr-2"></i>
                        Configuração de Níveis
                    </h5>
                </div>
                <div class="card-body-modern">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabela-produtos">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px" class="text-center">#</th>
                                    <th>Produto</th>
                                    <th>Descrição</th>
                                    <th style="width: 100px" class="text-center">Qtd Atual</th>
                                    <th style="width: 120px" class="text-center">Mínimo</th>
                                    <th style="width: 120px" class="text-center">Máximo</th>
                                    <th style="width: 100px" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
    /* Cards de estatísticas modernas */
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #f1f5f9;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }
    
    .stat-card-primary::before { background: #007bff; }
    .stat-card-warning::before { background: #ffc107; }
    .stat-card-danger::before { background: #dc3545; }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        float: right;
        margin-top: -8px;
    }
    
    .stat-card-primary .stat-icon { background: #007bff; }
    .stat-card-warning .stat-icon { background: #ffc107; }
    .stat-card-danger .stat-icon { background: #dc3545; }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1;
        margin-bottom: 4px;
    }
    
    .stat-label {
        color: #64748b;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .stat-trend {
        margin-top: 12px;
        display: flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
    }
    
    /* Cards modernos */
    .modern-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .modern-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    
    .card-header-modern {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 24px;
    }
    
    .card-title-modern {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }
    
    .card-body-modern {
        padding: 24px;
    }
    
    /* Tabela personalizada */
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 16px 12px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .table td {
        padding: 16px 12px;
        border-top: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8fafc;
    }
    
    /* Inputs customizados */
    .form-control-sm {
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        padding: 8px 12px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }
    
    .form-control-sm:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    /* Botões modernos */
    .btn-primary {
        background: #007bff;
        border: 2px solid #007bff;
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 16px;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: #0056b3;
        border-color: #0056b3;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,123,255,0.3);
    }
    
    /* Status visual dos produtos */
    .produto-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Badge de quantidade atual maior para melhor leitura */
    .qtd-badge {
        font-size: 16px !important;
        padding: 8px 14px !important;
        border-radius: 16px !important;
        min-width: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .status-ok {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-warning {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-danger {
        background: #fecaca;
        color: #991b1b;
    }
    
    /* Loading state */
    .loading-row {
        text-align: center;
        padding: 40px;
        color: #64748b;
    }
    
    .loading-spinner {
        width: 24px;
        height: 24px;
        border: 2px solid #e2e8f0;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        display: inline-block;
        margin-right: 8px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .stat-card {
            padding: 20px;
            margin-bottom: 16px;
        }
        
        .stat-number {
            font-size: 24px;
        }
        
        .card-body-modern {
            padding: 16px;
        }
        
        .table th,
        .table td {
            padding: 12px 8px;
        }
    }
</style>
@stop

@section('js')
<script>
$(function(){
    const tabela = $('#tabela-produtos tbody');
    let dados = [];

    function carregar(){
        tabela.html('<tr class="loading-row"><td colspan="7"><div class="loading-spinner"></div>Carregando produtos...</td></tr>');
        $.get('/api/estoque/min-max')
            .done(function(resp){
                if(!resp.success){
                    tabela.html('<tr><td colspan="7" class="text-center text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Falha ao carregar dados.</td></tr>');
                    return;
                }
                dados = resp.data || [];
                render(resp.data);
                atualizarEstatisticas();
            })
            .fail(function(){
                tabela.html('<tr><td colspan="7" class="text-center text-danger"><i class="fas fa-times-circle mr-2"></i>Erro de conexão.</td></tr>');
            });
    }

    function render(lista){
        if(!lista || !lista.length){
            tabela.html('<tr><td colspan="7" class="text-center text-muted"><i class="fas fa-box-open mr-2"></i>Nenhum produto encontrado.</td></tr>');
            return;
        }
        const rows = lista.map(function(item){
            const minimo = item.minimo ?? 0;
            const maximo = item.maximo ?? '';
            const status = getStatusProduto(item.quantidade, minimo, maximo);
            return `
                <tr data-id="${item.id}">
                    <td class="text-center font-weight-bold">${item.id}</td>
                    <td>${escapeHtml(item.nome || '')}</td>
                    <td>${escapeHtml(item.descricao || '')}</td>
                    <td class="text-center">
                        <span class="badge ${status.class} qtd-badge">${item.quantidade ?? 0}</span>
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm input-minimo" min="0" value="${minimo}">
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm input-maximo" min="0" value="${maximo}">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-primary btn-sm btn-salvar">
                            <i class="fas fa-save mr-1"></i>Salvar
                        </button>
                    </td>
                </tr>`;
        }).join('');
        tabela.html(rows);
    }

    function getStatusProduto(quantidade, minimo, maximo) {
        if (quantidade <= 0) {
            return { class: 'badge-danger', text: 'Sem estoque' };
        } else if (quantidade < minimo) {
            return { class: 'badge-warning', text: 'Abaixo do mínimo' };
        } else if (maximo && quantidade > maximo) {
            return { class: 'badge-info', text: 'Acima do máximo' };
        } else {
            return { class: 'badge-success', text: 'Normal' };
        }
    }

    function atualizarEstatisticas() {
        const total = dados.length;
        const configurados = dados.filter(item => item.minimo > 0 || item.maximo > 0).length;
        const abaixoMinimo = dados.filter(item => item.quantidade < (item.minimo || 0) && (item.minimo || 0) > 0).length;

        $('#totalProdutos').text(total);
        $('#produtosConfigurados').text(configurados);
        $('#produtosAbaixoMinimo').text(abaixoMinimo);
    }

    tabela.on('click', '.btn-salvar', function(){
        const tr = $(this).closest('tr');
        const id = tr.data('id');
        const minimo = parseInt(tr.find('.input-minimo').val() || '0', 10);
        const maximoVal = tr.find('.input-maximo').val();
        const maximo = maximoVal === '' ? null : parseInt(maximoVal, 10);
        const btn = $(this);

        if (maximo !== null && maximo < minimo){
            Swal.fire({
                icon: 'warning',
                title: 'Validação',
                text: 'O valor máximo não pode ser menor que o mínimo.',
                confirmButtonColor: '#007bff'
            });
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Salvando...');

        $.post(`/api/estoque/${id}/min-max`, { minimo, maximo })
            .done(function(resp){
                if(resp && resp.success){
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Níveis salvos com sucesso.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Atualizar dados locais
                    const index = dados.findIndex(item => item.id == id);
                    if (index !== -1) {
                        dados[index].minimo = minimo;
                        dados[index].maximo = maximo;
                        atualizarEstatisticas();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: resp.message || 'Falha ao salvar os dados.',
                        confirmButtonColor: '#007bff'
                    });
                }
            })
            .fail(function(xhr){
                let msg = 'Erro ao salvar.';
                if(xhr && xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Conexão',
                    text: msg,
                    confirmButtonColor: '#007bff'
                });
            })
            .always(function(){
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Salvar');
            });
    });

    function escapeHtml(text){
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Animação dos cards de estatísticas
    $('.stat-card').each(function(index) {
        $(this).css('opacity', '0').css('transform', 'translateY(20px)');
        setTimeout(() => {
            $(this).css('transition', 'all 0.6s ease')
                  .css('opacity', '1')
                  .css('transform', 'translateY(0)');
        }, index * 100);
    });

    carregar();
});
</script>
@stop