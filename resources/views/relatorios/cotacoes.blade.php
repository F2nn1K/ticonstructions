@extends('adminlte::page')

@section('title', 'Relatório de Cotações')

@section('content_header')
    <h1><i class="fas fa-file-invoice-dollar"></i> Relatório de Cotações</h1>
@stop

@section('content')
<div class="card no-print shadow-sm">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i> Filtros</h3>
    </div>
    <div class="card-body">
        <form id="formFiltros">
            <!-- Linha 1: Período e Status -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="font-weight-bold"><i class="fas fa-calendar-alt text-muted mr-1"></i> Período</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" placeholder="Início">
                        <div class="input-group-append input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fas fa-arrow-right text-muted"></i></span>
                        </div>
                        <input type="date" class="form-control" id="data_fim" name="data_fim" placeholder="Fim">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold"><i class="fas fa-tasks text-muted mr-1"></i> Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="todas">Todas</option>
                        <option value="aberta">Em Aberto (Não Cotadas)</option>
                        <option value="finalizada">Finalizadas (Cotadas)</option>
                        <option value="aguardando_aprovacao">Aguardando Aprovação</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-end">
                    <button type="submit" class="btn btn-primary btn-lg mr-2">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg" id="btnLimpar">
                        <i class="fas fa-eraser mr-1"></i> Limpar
                    </button>
                </div>
            </div>
            
            <!-- Linha 2: Centros de Custo (múltipla seleção) -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="centro-custo-wrapper p-3 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold mb-0">
                                <i class="fas fa-building text-primary mr-1"></i> Centros de Custo (Obras)
                            </label>
                            <div>
                                <span class="badge badge-primary mr-2" id="contadorCentros">Todos os centros</span>
                                <a href="#" class="btn btn-sm btn-outline-secondary" id="limparCentros">
                                    <i class="fas fa-times"></i> Limpar
                                </a>
                            </div>
                        </div>
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-info-circle"></i> Digite para buscar e selecione até 7 centros de custo. Deixe vazio para incluir todos.
                        </small>
                        <select class="form-control" id="centro_custo_id" name="centro_custo_id[]" multiple="multiple">
                            @foreach($centrosCusto ?? [] as $cc)
                                <option value="{{ $cc->id }}">{{ $cc->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row no-print" id="resumoCards" style="display: none;">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="totalCotacoes">0</h3>
                <p>Total de Cotações</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="naoCotadas">0</h3>
                <p>Não Cotadas (Em Aberto)</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="cotadas">0</h3>
                <p>Cotadas (Com Valor)</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 id="valorTotal">R$ 0,00</h3>
                <p>Valor Total para Pagamento</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Resultados -->
<div class="card no-print" id="cardResultados" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">Cotações Encontradas</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-sm btn-danger" id="btnExportarPDF">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped" id="tabelaCotacoes">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Descrição</th>
                    <th>Solicitante</th>
                    <th>Centro de Custo (Obra)</th>
                    <th>Cidade/UF</th>
                    <th>Dt. Solic.</th>
                    <th>Dt. Limite</th>
                    <th>Itens</th>
                    <th>Status</th>
                    <th>Fornecedor</th>
                    <th class="text-right">Valor (R$)</th>
                </tr>
            </thead>
            <tbody id="tbodyCotacoes">
            </tbody>
            <tfoot>
                <tr class="table-dark">
                    <th colspan="10" class="text-right">TOTAL GERAL:</th>
                    <th class="text-right" id="totalGeralValor">R$ 0,00</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Loading -->
<div id="loading" class="no-print" style="display: none; text-align: center; padding: 40px;">
    <i class="fas fa-spinner fa-spin fa-3x"></i>
    <p class="mt-2">Carregando...</p>
</div>

<!-- ==================== ÁREA DE IMPRESSÃO ==================== -->
<div id="areaPrint" class="print-area">
    <!-- Cabeçalho -->
    <div class="print-header">
        <div class="print-logo-section">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="print-logo">
        </div>
        <div class="print-title-section">
            <h1>RELATÓRIO DE COTAÇÕES</h1>
            <p class="print-subtitle" id="printSubtitle"></p>
        </div>
        <div class="print-date-section">
            <p id="printDataGeracao"></p>
        </div>
    </div>

    <!-- Filtros Aplicados -->
    <div class="print-filters">
        <strong>Filtros aplicados:</strong>
        <span id="printFiltros"></span>
    </div>

    <!-- Resumo -->
    <div class="print-resumo">
        <div class="print-resumo-item">
            <span class="print-resumo-valor" id="printTotal">0</span>
            <span class="print-resumo-label">Total</span>
        </div>
        <div class="print-resumo-item amarelo">
            <span class="print-resumo-valor" id="printNaoCotadas">0</span>
            <span class="print-resumo-label">Não Cotadas</span>
        </div>
        <div class="print-resumo-item verde">
            <span class="print-resumo-valor" id="printCotadas">0</span>
            <span class="print-resumo-label">Cotadas</span>
        </div>
        <div class="print-resumo-item azul">
            <span class="print-resumo-valor" id="printValorTotal">R$ 0,00</span>
            <span class="print-resumo-label">Valor Total</span>
        </div>
    </div>

    <!-- Tabela -->
    <table class="print-table" id="printTabela">
        <thead>
            <tr>
                <th style="width: 65px;">Número</th>
                <th>Descrição</th>
                <th style="width: 90px;">Solicitante</th>
                <th style="width: 110px;">Centro de Custo</th>
                <th style="width: 80px;">Cidade/UF</th>
                <th style="width: 65px;">Dt. Solic.</th>
                <th style="width: 65px;">Dt. Limite</th>
                <th style="width: 35px;">Itens</th>
                <th style="width: 90px;">Status</th>
                <th style="width: 100px;">Fornecedor</th>
                <th style="width: 75px; text-align: right;">Valor (R$)</th>
            </tr>
        </thead>
        <tbody id="printTbody">
        </tbody>
        <tfoot>
            <tr class="print-total-row">
                <td colspan="10" style="text-align: right; font-weight: bold;">TOTAL GERAL:</td>
                <td style="text-align: right; font-weight: bold;" id="printTotalGeral">R$ 0,00</td>
            </tr>
        </tfoot>
    </table>

    <!-- Rodapé -->
    <div class="print-footer">
        <p>Documento gerado pelo sistema SIGO - <span id="printFooterData"></span></p>
    </div>
</div>
@stop

@section('js')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>
<script>
$(function() {
    // Inicializar Select2 no Centro de Custo com múltipla seleção
    $('#centro_custo_id').select2({
        placeholder: 'Digite para buscar centros de custo...',
        allowClear: true,
        language: 'pt-BR',
        minimumInputLength: 0,
        maximumSelectionLength: 7, // Máximo de 7 seleções
        closeOnSelect: false, // Não fecha ao selecionar
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            var term = params.term.toLowerCase();
            var text = data.text.toLowerCase();
            if (text.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });
    
    // Atualizar contador de centros selecionados
    $('#centro_custo_id').on('change', function() {
        var count = $(this).val() ? $(this).val().length : 0;
        if (count === 0) {
            $('#contadorCentros').text('Todos os centros').removeClass('badge-primary').addClass('badge-info');
        } else {
            $('#contadorCentros').text(count + ' selecionado' + (count > 1 ? 's' : '')).removeClass('badge-info').addClass('badge-primary');
        }
    });
    
    // Limpar seleção de centros
    $('#limparCentros').on('click', function(e) {
        e.preventDefault();
        $('#centro_custo_id').val(null).trigger('change');
    });

    var hoje = new Date();
    var inicioMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    
    $('#data_inicio').val(inicioMes.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);

    buscarRelatorio();

    $('#formFiltros').on('submit', function(e) {
        e.preventDefault();
        buscarRelatorio();
    });

    $('#btnLimpar').click(function() {
        $('#data_inicio').val('');
        $('#data_fim').val('');
        $('#centro_custo_id').val(null).trigger('change');
        $('#status').val('todas');
        buscarRelatorio();
    });

    function buscarRelatorio() {
        var centrosCusto = $('#centro_custo_id').val();
        var params = {
            data_inicio: $('#data_inicio').val(),
            data_fim: $('#data_fim').val(),
            centro_custo_ids: centrosCusto && centrosCusto.length > 0 ? centrosCusto.join(',') : 'todos',
            status: $('#status').val()
        };

        $('#loading').show();
        $('#cardResultados').hide();
        $('#resumoCards').hide();

        $.get('/api/relatorios/cotacoes', params)
            .done(function(response) {
                $('#loading').hide();
                if (response.success) {
                    renderizarResultados(response.data, response.resumo);
                } else {
                    Swal.fire('Erro', 'Erro ao buscar relatório', 'error');
                }
            })
            .fail(function(xhr) {
                $('#loading').hide();
                Swal.fire('Erro', 'Erro ao buscar relatório: ' + (xhr.responseJSON?.message || 'Erro desconhecido'), 'error');
            });
    }

    function renderizarResultados(data, resumo) {
        // Cards na tela
        $('#totalCotacoes').text(resumo.total);
        $('#naoCotadas').text(resumo.nao_cotadas);
        $('#cotadas').text(resumo.cotadas);
        $('#valorTotal').text(formatarMoeda(resumo.valor_total));
        $('#resumoCards').show();

        // Tabela na tela
        var tbody = $('#tbodyCotacoes');
        tbody.empty();

        var totalGeral = 0;

        if (data.length === 0) {
            tbody.append('<tr><td colspan="11" class="text-center">Nenhuma cotação encontrada</td></tr>');
        } else {
            data.forEach(function(cotacao) {
                var statusBadge = cotacao.status === 'aberta' ? 
                    '<span class="badge badge-warning">Em Aberto</span>' : 
                    '<span class="badge badge-success">Finalizada</span>';

                var cotadoBadge = cotacao.tem_cotacao ? 
                    '<span class="badge badge-success ml-1">Cotada</span>' : 
                    '<span class="badge badge-danger ml-1">Não Cotada</span>';

                // Se for OC pendente, mostrar badge especial
                if (cotacao.is_oc_pendente) {
                    statusBadge = '<span class="badge badge-warning">OC Pendente</span>';
                    cotadoBadge = '';
                }

                if (cotacao.tem_cotacao) {
                    totalGeral += parseFloat(cotacao.menor_valor) || 0;
                }

                // Número: se for OC pendente, mostrar OC + Cotação
                var numeroExibir = cotacao.numero || '-';
                if (cotacao.is_oc_pendente && cotacao.oc_numero) {
                    numeroExibir = '<strong>' + cotacao.oc_numero + '</strong><br><small class="text-muted"><span class="badge badge-info badge-sm">' + cotacao.numero + '</span></small>';
                } else {
                    numeroExibir = '<strong>' + numeroExibir + '</strong>';
                }

                tbody.append('<tr>' +
                    '<td>' + numeroExibir + '</td>' +
                    '<td>' + (cotacao.descricao || '-') + '</td>' +
                    '<td>' + (cotacao.solicitante || '-') + '</td>' +
                    '<td>' + (cotacao.centro_custo || '-') + '</td>' +
                    '<td>' + (cotacao.cidade_uf || '-') + '</td>' +
                    '<td>' + formatarData(cotacao.data_solicitacao) + '</td>' +
                    '<td>' + formatarData(cotacao.data_limite) + '</td>' +
                    '<td class="text-center">' + cotacao.qtd_itens + '</td>' +
                    '<td>' + statusBadge + cotadoBadge + '</td>' +
                    '<td>' + (cotacao.fornecedor_vencedor || '-') + '</td>' +
                    '<td class="text-right">' + (cotacao.menor_valor ? formatarMoeda(cotacao.menor_valor) : '-') + '</td>' +
                '</tr>');
            });
        }

        $('#totalGeralValor').text(formatarMoeda(totalGeral));
        $('#cardResultados').show();

        // Preparar área de impressão
        prepararImpressao(data, resumo, totalGeral);
    }

    function prepararImpressao(data, resumo, totalGeral) {
        var dataIni = $('#data_inicio').val();
        var dataFim = $('#data_fim').val();
        var centrosSelecionados = $('#centro_custo_id').val();
        var centroCustoFiltro = 'Todos';
        if (centrosSelecionados && centrosSelecionados.length > 0) {
            var nomes = [];
            $('#centro_custo_id option:selected').each(function() {
                nomes.push($(this).text());
            });
            centroCustoFiltro = nomes.join(', ');
        }
        var statusFiltro = $('#status option:selected').text();

        // Subtítulo
        var subtitulo = '';
        if (dataIni && dataFim) {
            subtitulo = 'Período: ' + formatarData(dataIni) + ' a ' + formatarData(dataFim);
        } else if (dataIni) {
            subtitulo = 'A partir de: ' + formatarData(dataIni);
        } else if (dataFim) {
            subtitulo = 'Até: ' + formatarData(dataFim);
        }
        $('#printSubtitle').text(subtitulo);

        // Data de geração
        var agora = new Date();
        var dataGeracao = agora.toLocaleDateString('pt-BR') + ' às ' + agora.toLocaleTimeString('pt-BR');
        $('#printDataGeracao').text('Gerado em: ' + dataGeracao);
        $('#printFooterData').text(dataGeracao);

        // Filtros aplicados
        var filtros = [];
        if (dataIni) filtros.push('Data início: ' + formatarData(dataIni));
        if (dataFim) filtros.push('Data fim: ' + formatarData(dataFim));
        filtros.push('Centro de Custo: ' + centroCustoFiltro);
        filtros.push('Status: ' + statusFiltro);
        $('#printFiltros').text(filtros.join(' | '));

        // Resumo
        $('#printTotal').text(resumo.total);
        $('#printNaoCotadas').text(resumo.nao_cotadas);
        $('#printCotadas').text(resumo.cotadas);
        $('#printValorTotal').text(formatarMoeda(resumo.valor_total));

        // Tabela
        var printTbody = $('#printTbody');
        printTbody.empty();

        data.forEach(function(cotacao) {
            var statusText = cotacao.status === 'aberta' ? 'Em Aberto' : 'Finalizada';
            var cotadoText = cotacao.tem_cotacao ? 'Cotada' : 'Não Cotada';
            var statusClass = cotacao.status === 'aberta' ? 'status-aberta' : 'status-finalizada';
            var cotadoClass = cotacao.tem_cotacao ? 'status-cotada' : 'status-nao-cotada';

            // Se for OC pendente, mostrar status especial
            if (cotacao.is_oc_pendente) {
                statusText = 'OC Pendente';
                cotadoText = '';
                statusClass = 'status-aberta';
                cotadoClass = '';
            }

            // Número: se for OC pendente, mostrar OC + Cotação
            var numeroExibir = cotacao.numero || '-';
            if (cotacao.is_oc_pendente && cotacao.oc_numero) {
                numeroExibir = cotacao.oc_numero + ' (' + cotacao.numero + ')';
            }

            printTbody.append('<tr>' +
                '<td><strong>' + numeroExibir + '</strong></td>' +
                '<td>' + (cotacao.descricao || '-') + '</td>' +
                '<td>' + (cotacao.solicitante || '-') + '</td>' +
                '<td>' + (cotacao.centro_custo || '-') + '</td>' +
                '<td>' + (cotacao.cidade_uf || '-') + '</td>' +
                '<td>' + formatarData(cotacao.data_solicitacao) + '</td>' +
                '<td>' + formatarData(cotacao.data_limite) + '</td>' +
                '<td style="text-align: center;">' + cotacao.qtd_itens + '</td>' +
                '<td><span class="' + statusClass + '">' + statusText + '</span>' + (cotadoText ? ' <span class="' + cotadoClass + '">' + cotadoText + '</span>' : '') + '</td>' +
                '<td>' + (cotacao.fornecedor_vencedor || '-') + '</td>' +
                '<td style="text-align: right;">' + (cotacao.menor_valor ? formatarMoeda(cotacao.menor_valor) : '-') + '</td>' +
            '</tr>');
        });

        $('#printTotalGeral').text(formatarMoeda(totalGeral));
    }

    function formatarData(data) {
        if (!data) return '-';
        var partes = data.split('-');
        return partes.length === 3 ? partes[2] + '/' + partes[1] + '/' + partes[0] : data;
    }

    function formatarMoeda(valor) {
        if (valor === null || valor === undefined) return 'R$ 0,00';
        return 'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    $('#btnExportarPDF').click(function() {
        window.print();
    });
});
</script>
@stop

@section('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.small-box h3 { font-size: 2rem; }

/* Select2 customização */
.select2-container {
    width: 100% !important;
}
.select2-dropdown {
    border: 1px solid #ced4da;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 9999;
}
.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 14px;
}
.select2-results__option {
    padding: 10px 12px;
    font-size: 14px;
}
.select2-results__option--highlighted[aria-selected] {
    background-color: #007bff !important;
}
/* Select2 múltipla seleção - estilo melhorado */
.select2-container--default .select2-selection--multiple {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    min-height: 48px;
    padding: 6px 10px;
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.select2-container--default .select2-selection--multiple:hover {
    border-color: #007bff;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
    color: white;
    border-radius: 20px;
    padding: 5px 12px;
    margin: 4px 6px 4px 0;
    font-size: 13px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: rgba(255,255,255,0.8);
    margin-right: 6px;
    font-size: 16px;
    font-weight: bold;
    order: -1;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #fff;
}
.select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
    margin-top: 6px;
    font-size: 14px;
}
.select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
    color: #999;
}

/* Área de impressão - oculta na tela */
.print-area { display: none; }

/* ==================== ESTILOS DE IMPRESSÃO ==================== */
@media print {
    @page {
        size: A4 landscape;
        margin: 8mm;
    }

    /* Ocultar tudo exceto área de impressão */
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .no-print { display: none !important; }
    
    .main-sidebar, .main-header, .main-footer, .content-header, .preloader,
    #resumoCards, #cardResultados, #loading, .card { 
        display: none !important; 
    }

    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    .print-area {
        display: block !important;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 0;
        font-family: Arial, sans-serif;
        font-size: 11px;
        color: #333;
    }

    /* Cabeçalho */
    .print-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 3px solid #2c3e50;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }

    .print-logo-section { flex: 0 0 120px; }
    .print-logo { max-height: 50px; max-width: 120px; }

    .print-title-section { 
        flex: 1; 
        text-align: center; 
    }
    .print-title-section h1 { 
        font-size: 18px; 
        margin: 0; 
        color: #2c3e50;
        font-weight: bold;
    }
    .print-subtitle { 
        font-size: 12px; 
        color: #666; 
        margin: 3px 0 0 0; 
    }

    .print-date-section { 
        flex: 0 0 150px; 
        text-align: right; 
        font-size: 10px; 
        color: #666; 
    }

    /* Filtros */
    .print-filters {
        background: #f8f9fa;
        padding: 6px 10px;
        border-radius: 4px;
        margin-bottom: 10px;
        font-size: 10px;
        border: 1px solid #ddd;
    }

    /* Resumo */
    .print-resumo {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        gap: 10px;
    }

    .print-resumo-item {
        flex: 1;
        text-align: center;
        padding: 8px;
        border-radius: 4px;
        background: #3498db;
        color: white;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .print-resumo-item.amarelo { background: #f39c12; }
    .print-resumo-item.verde { background: #27ae60; }
    .print-resumo-item.azul { background: #2980b9; }

    .print-resumo-valor { 
        display: block; 
        font-size: 16px; 
        font-weight: bold; 
    }
    .print-resumo-label { 
        display: block; 
        font-size: 9px; 
        opacity: 0.9; 
    }

    /* Tabela */
    .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9px;
    }

    .print-table th {
        background: #2c3e50 !important;
        color: white !important;
        padding: 6px 4px;
        text-align: left;
        font-weight: bold;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .print-table td {
        padding: 4px;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }

    .print-table tbody tr:nth-child(even) {
        background: #f9f9f9;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .print-total-row {
        background: #2c3e50 !important;
        color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .print-total-row td {
        border: none;
        padding: 8px 4px;
    }

    /* Status badges */
    .status-aberta, .status-finalizada, .status-cotada, .status-nao-cotada {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: bold;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .status-aberta { background: #f39c12; color: #000; }
    .status-finalizada { background: #27ae60; color: #fff; }
    .status-cotada { background: #27ae60; color: #fff; }
    .status-nao-cotada { background: #e74c3c; color: #fff; }

    /* Rodapé */
    .print-footer {
        margin-top: 15px;
        padding-top: 8px;
        border-top: 1px solid #ddd;
        text-align: center;
        font-size: 9px;
        color: #666;
    }
}
</style>
@stop
