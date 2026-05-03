<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PermissoesController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ObraController;
use App\Http\Controllers\ObraFaseController;
use App\Http\Controllers\LancamentoObraController;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\TaxaAdministracaoController;
use App\Http\Controllers\CronogramaController;
use App\Http\Controllers\GastosController;
use App\Http\Controllers\DiarioObraController;
use App\Http\Controllers\DashboardController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Livewire\GerenciarPermissoes;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
// use App\Http\Controllers\LicenseController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\AbastecimentoController;
use App\Http\Controllers\ManutencaoController;
use App\Http\Controllers\ViagemController;
use App\Http\Controllers\RelatorioKmController;
use App\Http\Controllers\RelatorioProdutoEstoqueController;
use App\Http\Controllers\LicenciamentoController;
use App\Http\Controllers\DocumentosDPController;
use App\Http\Controllers\ControleSaidaEncarregadosController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Troca de idioma
Route::get('/lang/{locale}', function (string $locale) {
    $supported = ['pt_BR', 'en'];
    if (in_array($locale, $supported)) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');


Route::middleware(['auth'])->group(function () {
    // Dashboard - acessível para todos os usuários autenticados
    Route::get('/home', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // =============================================
    // CRONOGRAMA — visão geral de todas as obras
    // =============================================
    Route::prefix('cronograma')->name('cronograma.')->group(function () {
        Route::get('/',            [CronogramaController::class, 'index'])->name('index');
        Route::get('/ocorrencias', [CronogramaController::class, 'ocorrencias'])->name('ocorrencias');
        Route::get('/criar', fn() => redirect()->route('obras.create'))->name('criar');

        // Detalhes de fase com checklist de sub-fases
        Route::get('/obra/{obra}/fase/{fase}', [CronogramaController::class, 'faseDetalhe'])->name('fase-detalhe');

        // Marcar/desmarcar tarefa (AJAX)
        Route::post('/tarefa/{tarefa}/marcar', [CronogramaController::class, 'marcarTarefa'])->name('tarefa-marcar');

        // Adicionar fase à obra pelo cronograma
        Route::post('/obra/{obra}/adicionar-fase', [CronogramaController::class, 'adicionarFase'])->name('adicionar-fase');

        // Adicionar tarefa avulsa a uma fase
        Route::post('/fase/{fase}/adicionar-tarefa', [CronogramaController::class, 'adicionarTarefa'])->name('tarefa-adicionar');

        // Excluir tarefa
        Route::delete('/tarefa/{tarefa}', [CronogramaController::class, 'excluirTarefa'])->name('tarefa-excluir');

        // API: tarefas do catálogo por fase
        Route::get('/api/tarefas-catalogo/{faseCatalogoId}', [CronogramaController::class, 'tarefasCatalogo'])->name('api.tarefas-catalogo');
    });

    // =============================================
    // CONTROLE DE GASTOS — lançamentos consolidados
    // =============================================
    Route::prefix('gastos')->name('gastos.')->group(function () {
        Route::get('/',           [GastosController::class, 'index'])->name('index');
        Route::get('/criar',      [GastosController::class, 'create'])->name('create');
        Route::post('/',          [GastosController::class, 'store'])->name('store');
        Route::get('/fluxo-caixa',[GastosController::class, 'fluxoCaixa'])->name('fluxo-caixa');
    });

    // =============================================
    // FORNECEDORES — gestão e cotação
    // =============================================
    Route::prefix('fornecedores')->name('fornecedores.')->group(function () {
        Route::get('/',                          [App\Http\Controllers\FornecedorController::class, 'index'])->name('index');
        Route::get('/novo',                      [App\Http\Controllers\FornecedorController::class, 'create'])->name('create');
        Route::post('/',                         [App\Http\Controllers\FornecedorController::class, 'store'])->name('store');
        Route::get('/relatorio-comparacao',      [App\Http\Controllers\FornecedorController::class, 'relatorioComparacao'])->name('relatorio-comparacao');
        Route::get('/{fornecedor}',              [App\Http\Controllers\FornecedorController::class, 'show'])->name('show');
        Route::get('/{fornecedor}/editar',       [App\Http\Controllers\FornecedorController::class, 'edit'])->name('edit');
        Route::put('/{fornecedor}',              [App\Http\Controllers\FornecedorController::class, 'update'])->name('update');
        Route::delete('/{fornecedor}',           [App\Http\Controllers\FornecedorController::class, 'destroy'])->name('destroy');
        Route::get('/api/lista',                 [App\Http\Controllers\FornecedorController::class, 'apiLista'])->name('api.lista');
    });

    // =============================================
    // CATEGORIAS DE CUSTO — gestão inline e CRUD
    // =============================================
    Route::prefix('categorias-custo')->name('categorias.')->group(function () {
        Route::get('/',                          [App\Http\Controllers\CategoriaMaterialController::class, 'index'])->name('index');
        Route::post('/',                         [App\Http\Controllers\CategoriaMaterialController::class, 'store'])->name('store');
        Route::put('/{categoriaMaterial}',       [App\Http\Controllers\CategoriaMaterialController::class, 'update'])->name('update');
        Route::delete('/{categoriaMaterial}',    [App\Http\Controllers\CategoriaMaterialController::class, 'destroy'])->name('destroy');
        Route::post('/subcategoria',             [App\Http\Controllers\CategoriaMaterialController::class, 'storeSub'])->name('subcategoria.store');
        Route::delete('/subcategoria/{subcategoriaMaterial}', [App\Http\Controllers\CategoriaMaterialController::class, 'destroySub'])->name('subcategoria.destroy');
    });

    // APIs inline (chamadas AJAX pelo formulário de lançamento)
    Route::post('/api/categorias',    [App\Http\Controllers\CategoriaMaterialController::class, 'storeApi'])->name('api.categorias.store');
    Route::post('/api/subcategorias', [App\Http\Controllers\CategoriaMaterialController::class, 'storeSubApi'])->name('api.subcategorias.store');

    // =============================================
    // DIÁRIO DE OBRA
    // =============================================
    Route::prefix('diario')->name('diario.')->group(function () {
        Route::get('/',                  [DiarioObraController::class, 'index'])->name('index');
        Route::get('/novo',              [DiarioObraController::class, 'create'])->name('create');
        Route::post('/',                 [DiarioObraController::class, 'store'])->name('store');
        Route::get('/{diario}',          [DiarioObraController::class, 'show'])->name('show');
        Route::get('/{diario}/editar',   [DiarioObraController::class, 'edit'])->name('edit');
        Route::put('/{diario}',          [DiarioObraController::class, 'update'])->name('update');
        Route::delete('/{diario}',       [DiarioObraController::class, 'destroy'])->name('destroy');
    });

    // Diário por obra (aninhado em obras)
    Route::get('/obras/{obra}/diario', [DiarioObraController::class, 'porObra'])->name('obras.diario');

    // API: fases de uma obra para o formulário do diário
    Route::get('/api/diario/fases-obra/{obra}', [DiarioObraController::class, 'fasesObra'])
         ->name('api.diario.fases-obra');

    // API: tarefas de uma fase para vincular às atividades do diário
    Route::get('/api/diario/tarefas-fase/{fase}', [DiarioObraController::class, 'tarefasFase'])
         ->name('api.diario.tarefas-fase');

    // =============================================
    // MÓDULO OBRAS / CRONOGRAMA
    // =============================================

    Route::prefix('obras')->name('obras.')->group(function () {
        Route::get('/',         [ObraController::class, 'index'])->name('index');
        Route::get('/nova',     [ObraController::class, 'create'])->name('create');
        Route::post('/',        [ObraController::class, 'store'])->name('store');
        Route::get('/{obra}/dashboard', [DashboardController::class, 'obraDashboard'])->name('dashboard');
        Route::get('/{obra}',   [ObraController::class, 'show'])->name('show');
        Route::get('/{obra}/editar', [ObraController::class, 'edit'])->name('edit');
        Route::put('/{obra}',   [ObraController::class, 'update'])->name('update');
        Route::delete('/{obra}',[ObraController::class, 'destroy'])->name('destroy');

        // Fases
        Route::post('/{obra}/fases/{fase}/avancar',        [ObraFaseController::class, 'avancar'])->name('fases.avancar');
        Route::post('/{obra}/fases/{fase}/progresso',      [ObraFaseController::class, 'atualizarProgresso'])->name('fases.progresso');
        Route::post('/{obra}/fases/{fase}/ocorrencia',     [ObraFaseController::class, 'registrarOcorrencia'])->name('fases.ocorrencia');

        // Lançamentos financeiros
        Route::get( '/{obra}/lancamentos',        [LancamentoObraController::class, 'index'])->name('lancamentos.index');
        Route::get( '/{obra}/lancamentos/novo',   [LancamentoObraController::class, 'create'])->name('lancamentos.create');
        Route::post('/{obra}/lancamentos',        [LancamentoObraController::class, 'store'])->name('lancamentos.store');
        Route::delete('/{obra}/lancamentos/{lancamento}', [LancamentoObraController::class, 'destroy'])->name('lancamentos.destroy');
    });

    // API helpers (subcategorias por categoria)
    Route::get('/api/subcategorias', [LancamentoObraController::class, 'subcategorias'])->name('api.subcategorias');

    // =============================================
    // ADMINISTRADORES — fichas e gestão
    // =============================================
    Route::prefix('obras/administradores')->name('obras.administradores.')->group(function () {
        Route::get('/',                   [AdministradorController::class, 'index'])->name('index');
        Route::get('/novo',               [AdministradorController::class, 'create'])->name('create');
        Route::post('/',                  [AdministradorController::class, 'store'])->name('store');
        Route::get('/{administrador}',    [AdministradorController::class, 'show'])->name('show');
        Route::get('/{administrador}/editar', [AdministradorController::class, 'edit'])->name('edit');
        Route::put('/{administrador}',    [AdministradorController::class, 'update'])->name('update');
        Route::delete('/{administrador}', [AdministradorController::class, 'destroy'])->name('destroy');
    });
    Route::get('/api/administradores', [AdministradorController::class, 'apiListar'])->name('api.administradores');

    // =============================================
    // TAXA DE ADMINISTRAÇÃO — calcular e pagar
    // =============================================
    Route::prefix('obras/taxa-administracao')->name('obras.taxa-administracao.')->group(function () {
        Route::get('/',              [TaxaAdministracaoController::class, 'index'])->name('index');
        Route::get('/nova',          [TaxaAdministracaoController::class, 'create'])->name('create');
        Route::post('/',             [TaxaAdministracaoController::class, 'store'])->name('store');
        Route::get('/calcular-preview', [TaxaAdministracaoController::class, 'calcularPreview'])->name('calcular-preview');
        Route::patch('/{taxa}/pagar',    [TaxaAdministracaoController::class, 'pagar'])->name('pagar');
        Route::patch('/{taxa}/cancelar', [TaxaAdministracaoController::class, 'cancelar'])->name('cancelar');
        Route::delete('/{taxa}',         [TaxaAdministracaoController::class, 'destroy'])->name('destroy');
    });

    // =============================================
    // MÓDULO FINANCEIRO
    // =============================================
    Route::prefix('financeiro')->name('financeiro.')->middleware('can:Financeiro')->group(function () {
        Route::get('/', [App\Http\Controllers\FinanceiroController::class, 'index'])
            ->name('index');

        Route::get('/contas-pagar', [App\Http\Controllers\FinanceiroController::class, 'contasPagar'])
            ->middleware('can:contas_pagar')
            ->name('contas-pagar');

        Route::get('/contas-receber', [App\Http\Controllers\FinanceiroController::class, 'contasReceber'])
            ->middleware('can:contas_receber')
            ->name('contas-receber');

        Route::get('/fluxo-caixa', [App\Http\Controllers\FinanceiroController::class, 'fluxoCaixa'])
            ->middleware('can:fluxo_caixa')
            ->name('fluxo-caixa');


        Route::get('/bancos', [App\Http\Controllers\FinanceiroController::class, 'bancos'])
            ->middleware('can:bancos')
            ->name('bancos');
        
        // Categorias
        Route::get('/categorias', [App\Http\Controllers\FinanceiroController::class, 'categorias'])
            ->middleware('can:cat')
            ->name('categorias');
        
        // APIs Categorias
        Route::get('/api/categorias', [App\Http\Controllers\FinanceiroController::class, 'listarCategorias'])
            ->name('api.categorias.index');
        Route::post('/api/categorias', [App\Http\Controllers\FinanceiroController::class, 'storeCategoria'])
            ->name('api.categorias.store');
        Route::get('/api/categorias/{id}', [App\Http\Controllers\FinanceiroController::class, 'getCategoria'])
            ->name('api.categorias.show');
        Route::put('/api/categorias/{id}', [App\Http\Controllers\FinanceiroController::class, 'updateCategoria'])
            ->name('api.categorias.update');
        Route::delete('/api/categorias/{id}', [App\Http\Controllers\FinanceiroController::class, 'deleteCategoria'])
            ->name('api.categorias.delete');
        
        // APIs Contas a Pagar
        Route::get('/api/contas-pagar/listar', [App\Http\Controllers\FinanceiroController::class, 'listarContasPagar'])
            ->name('api.contas-pagar.index');
        
        // APIs Exclusão em Lote (Importados via JSON) - ANTES das rotas com {id}
        Route::get('/api/contas-pagar/datas-importacao-json', [App\Http\Controllers\FinanceiroController::class, 'listarDatasImportacaoJson'])
            ->name('api.contas-pagar.datas-importacao');
        Route::delete('/api/contas-pagar/excluir-lote-json', [App\Http\Controllers\FinanceiroController::class, 'excluirLoteImportacaoJson'])
            ->name('api.contas-pagar.excluir-lote');
        
        // Rotas com parâmetro {id} devem vir DEPOIS das rotas específicas
        Route::get('/api/contas-pagar/{id}', [App\Http\Controllers\FinanceiroController::class, 'getContaPagar'])
            ->name('api.contas-pagar.show');
        Route::post('/api/contas-pagar', [App\Http\Controllers\FinanceiroController::class, 'storeContaPagar'])
            ->name('api.contas-pagar.store');
        Route::put('/api/contas-pagar/{id}', [App\Http\Controllers\FinanceiroController::class, 'updateContaPagar'])
            ->name('api.contas-pagar.update');
        Route::delete('/api/contas-pagar/{id}', [App\Http\Controllers\FinanceiroController::class, 'deleteContaPagar'])
            ->name('api.contas-pagar.delete');
        Route::post('/api/contas-pagar/{id}/baixar', [App\Http\Controllers\FinanceiroController::class, 'baixarContaPagar'])
            ->name('api.contas-pagar.baixar');
        Route::post('/api/contas-pagar/baixar-lote', [App\Http\Controllers\FinanceiroController::class, 'baixarContasPagarLote'])
            ->name('api.contas-pagar.baixar-lote');
        Route::get('/api/contas-pagar/{id}/comprovantes', [App\Http\Controllers\FinanceiroController::class, 'listarComprovantes'])
            ->name('api.contas-pagar.comprovantes');
        Route::get('/api/contas-pagar/{id}/comprovante/{index?}', [App\Http\Controllers\FinanceiroController::class, 'verComprovante'])
            ->name('api.contas-pagar.comprovante');
        
        // APIs Contas a Receber
        Route::get('/api/contas-receber', [App\Http\Controllers\FinanceiroController::class, 'listarContasReceber'])
            ->name('api.contas-receber.index');
        Route::get('/api/contas-receber/{id}', [App\Http\Controllers\FinanceiroController::class, 'getContaReceber'])
            ->name('api.contas-receber.show');
        Route::post('/api/contas-receber', [App\Http\Controllers\FinanceiroController::class, 'storeContaReceber'])
            ->name('api.contas-receber.store');
        Route::put('/api/contas-receber/{id}', [App\Http\Controllers\FinanceiroController::class, 'updateContaReceber'])
            ->name('api.contas-receber.update');
        Route::post('/api/contas-receber/{id}/baixar', [App\Http\Controllers\FinanceiroController::class, 'baixarContaReceber'])
            ->name('api.contas-receber.baixar');
        Route::delete('/api/contas-receber/{id}', [App\Http\Controllers\FinanceiroController::class, 'destroyContaReceber'])
            ->name('api.contas-receber.destroy');
        Route::get('/api/contas-receber/{id}/comprovante', [App\Http\Controllers\FinanceiroController::class, 'verComprovanteReceber'])
            ->name('api.contas-receber.comprovante');
        Route::get('/api/contas-receber/{id}/anexo', [App\Http\Controllers\FinanceiroController::class, 'verAnexoReceber'])
            ->name('api.contas-receber.anexo');
    });

    // =============================================
    // MÓDULO ÁREA TÉCNICA
    // =============================================
    Route::prefix('area-tecnica')->name('area-tecnica.')->group(function () {
        // Ordem de Serviço
        Route::get('/ordem-servico', [App\Http\Controllers\OrdemServicoController::class, 'index'])
            ->middleware('can:ordem_servico')
            ->name('ordem-servico');
        
        // API Ordem de Serviço
        Route::get('/api/ordens-servico/listar', [App\Http\Controllers\OrdemServicoController::class, 'listar'])
            ->name('api.ordens-servico.listar');
        Route::get('/api/ordens-servico/proximo-numero', [App\Http\Controllers\OrdemServicoController::class, 'proximoNumero'])
            ->name('api.ordens-servico.proximo-numero');
        Route::get('/api/ordens-servico/funcionarios', [App\Http\Controllers\OrdemServicoController::class, 'buscarFuncionarios'])
            ->name('api.ordens-servico.funcionarios');
        Route::post('/api/ordens-servico', [App\Http\Controllers\OrdemServicoController::class, 'store'])
            ->name('api.ordens-servico.store');
        Route::get('/api/ordens-servico/{id}', [App\Http\Controllers\OrdemServicoController::class, 'show'])
            ->name('api.ordens-servico.show');
        Route::put('/api/ordens-servico/{id}', [App\Http\Controllers\OrdemServicoController::class, 'update'])
            ->name('api.ordens-servico.update');
        Route::delete('/api/ordens-servico/{id}', [App\Http\Controllers\OrdemServicoController::class, 'destroy'])
            ->name('api.ordens-servico.destroy');
        
        // Gestão de O.S.
        Route::get('/gestao-os', [App\Http\Controllers\OrdemServicoController::class, 'gestao'])
            ->middleware('can:gestao_os')
            ->name('gestao-os');
        Route::get('/api/gestao-os/listar', [App\Http\Controllers\OrdemServicoController::class, 'listarGestao'])
            ->name('api.gestao-os.listar');
        Route::post('/api/ordens-servico/{id}/fechar', [App\Http\Controllers\OrdemServicoController::class, 'fechar'])
            ->name('api.ordens-servico.fechar');
        Route::post('/api/ordens-servico/{id}/reabrir', [App\Http\Controllers\OrdemServicoController::class, 'reabrir'])
            ->name('api.ordens-servico.reabrir');
        
        // API para busca de materiais na O.S. (acessível a todos com permissão de O.S.)
        Route::get('/api/materiais/buscar', [App\Http\Controllers\OrdemServicoController::class, 'buscarMateriais'])
            ->name('api.materiais.buscar');
    });
    
    // =============================================
    // FUNCIONÁRIOS
    // =============================================
    Route::get('/funcionarios', [App\Http\Controllers\FuncionarioController::class, 'index'])
        ->middleware('can:funcionarios')
        ->name('funcionarios');

    // Apontamentos (Daily Time Sheet)
    Route::prefix('funcionarios/apontamento')->name('funcionarios.apontamento.')->group(function () {
        Route::get('/',         [App\Http\Controllers\ApontamentoController::class, 'index'])
            ->middleware('can:apontamento-ver')
            ->name('index');
        Route::post('/',        [App\Http\Controllers\ApontamentoController::class, 'store'])
            ->middleware('can:apontamento-ver')
            ->name('store');
        Route::delete('/{apontamento}', [App\Http\Controllers\ApontamentoController::class, 'destroy'])
            ->middleware('can:apontamento-ver')
            ->name('destroy');
        Route::get('/aprovar',  [App\Http\Controllers\ApontamentoController::class, 'aprovar'])
            ->middleware('can:apontamento-aprovar')
            ->name('aprovar');
        Route::post('/aprovar-lote', [App\Http\Controllers\ApontamentoController::class, 'aprovarLote'])
            ->middleware('can:apontamento-aprovar')
            ->name('aprovar-lote');
        Route::post('/{apontamento}/processar', [App\Http\Controllers\ApontamentoController::class, 'processarAprovacao'])
            ->middleware('can:apontamento-aprovar')
            ->name('processar');
    });
    // =============================================
    // QUALIDADE
    // =============================================
    Route::prefix('qualidade')->name('qualidade.')->group(function () {
        Route::get('/fases/{obra}',           [App\Http\Controllers\QualidadeController::class, 'fasesObra'])->name('fases');
        Route::get('/checklist/{id}/itens',   [App\Http\Controllers\QualidadeController::class, 'checklistItens'])->name('checklist-itens');

        Route::get('/checklists',             [App\Http\Controllers\QualidadeController::class, 'checklists'])->middleware('can:qualidade-checklist')->name('checklists');
        Route::get('/checklists/criar',       [App\Http\Controllers\QualidadeController::class, 'checklistCriar'])->middleware('can:qualidade-checklist')->name('checklist-criar');
        Route::post('/checklists',            [App\Http\Controllers\QualidadeController::class, 'checklistStore'])->middleware('can:qualidade-checklist')->name('checklist-store');
        Route::delete('/checklists/{id}',     [App\Http\Controllers\QualidadeController::class, 'checklistDestroy'])->middleware('can:qualidade-checklist')->name('checklist-destroy');

        Route::get('/inspecoes',              [App\Http\Controllers\QualidadeController::class, 'inspecoes'])->middleware('can:qualidade-inspecao')->name('inspecoes');
        Route::get('/inspecoes/criar',        [App\Http\Controllers\QualidadeController::class, 'inspecaoCriar'])->middleware('can:qualidade-inspecao')->name('inspecao-criar');
        Route::post('/inspecoes',             [App\Http\Controllers\QualidadeController::class, 'inspecaoStore'])->middleware('can:qualidade-inspecao')->name('inspecao-store');

        Route::get('/nao-conformidades',      [App\Http\Controllers\QualidadeController::class, 'naoConformidades'])->middleware('can:qualidade-nao-conformidade')->name('nao-conformidades');
        Route::get('/nao-conformidades/criar',[App\Http\Controllers\QualidadeController::class, 'naoConformidadeCriar'])->middleware('can:qualidade-nao-conformidade')->name('nao-conformidade-criar');
        Route::post('/nao-conformidades',     [App\Http\Controllers\QualidadeController::class, 'naoConformidadeStore'])->middleware('can:qualidade-nao-conformidade')->name('nao-conformidade-store');
        Route::patch('/nao-conformidades/{id}',[App\Http\Controllers\QualidadeController::class, 'naoConformidadeUpdate'])->middleware('can:qualidade-nao-conformidade')->name('nao-conformidade-update');
    });

    // =============================================
    // RISCOS
    // =============================================
    Route::prefix('riscos')->name('riscos.')->middleware('can:riscos-ver')->group(function () {
        Route::get('/',            [App\Http\Controllers\RiscoController::class, 'index'])->name('index');
        Route::get('/criar',       [App\Http\Controllers\RiscoController::class, 'criar'])->middleware('can:riscos-criar')->name('criar');
        Route::post('/',           [App\Http\Controllers\RiscoController::class, 'store'])->middleware('can:riscos-criar')->name('store');
        Route::get('/{risco}/editar', [App\Http\Controllers\RiscoController::class, 'edit'])->middleware('can:riscos-editar')->name('edit');
        Route::put('/{risco}',     [App\Http\Controllers\RiscoController::class, 'update'])->middleware('can:riscos-editar')->name('update');
        Route::delete('/{risco}',  [App\Http\Controllers\RiscoController::class, 'destroy'])->middleware('can:riscos-excluir')->name('destroy');
    });

    // =============================================
    // OCORRÊNCIAS DE OBRA
    // =============================================
    Route::prefix('ocorrencias')->name('ocorrencias-obra.')->group(function () {
        Route::get('/',       [App\Http\Controllers\OcorrenciasObraController::class, 'index'])->middleware('can:ocorrencias-ver')->name('index');
        Route::get('/criar',  [App\Http\Controllers\OcorrenciasObraController::class, 'criar'])->middleware('can:ocorrencias-criar')->name('criar');
        Route::post('/',      [App\Http\Controllers\OcorrenciasObraController::class, 'store'])->middleware('can:ocorrencias-criar')->name('store');
        Route::get('/fases/{obra}', [App\Http\Controllers\OcorrenciasObraController::class, 'fases'])->name('fases');
    });

    // =============================================
    // PRODUÇÃO / MEDIÇÕES
    // =============================================
    Route::prefix('producao')->name('producao.')->group(function () {
        Route::get('/',              [App\Http\Controllers\ProducaoController::class, 'index'])
            ->middleware('can:producao-ver')
            ->name('index');
        Route::get('/medicao',       [App\Http\Controllers\ProducaoController::class, 'medicao'])
            ->middleware('can:producao-lancar')
            ->name('medicao');
        Route::post('/medicao',      [App\Http\Controllers\ProducaoController::class, 'store'])
            ->middleware('can:producao-lancar')
            ->name('store');
        Route::get('/fases/{obra}',  [App\Http\Controllers\ProducaoController::class, 'fases'])
            ->name('fases');
        Route::get('/aprovacao',     [App\Http\Controllers\ProducaoController::class, 'aprovacao'])
            ->middleware('can:producao-aprovar')
            ->name('aprovacao');
        Route::post('/processar-lote', [App\Http\Controllers\ProducaoController::class, 'processarLote'])
            ->middleware('can:producao-aprovar')
            ->name('processar-lote');
        Route::post('/{medicao}/processar', [App\Http\Controllers\ProducaoController::class, 'processar'])
            ->middleware('can:producao-aprovar')
            ->name('processar');
    });

    Route::get('/api/funcionarios', [App\Http\Controllers\FuncionarioController::class, 'listar'])
        ->name('api.funcionarios.listar');
    Route::post('/api/funcionarios', [App\Http\Controllers\FuncionarioController::class, 'store'])
        ->name('api.funcionarios.store');
    Route::get('/api/funcionarios/{id}', [App\Http\Controllers\FuncionarioController::class, 'show'])
        ->name('api.funcionarios.show');
    Route::put('/api/funcionarios/{id}', [App\Http\Controllers\FuncionarioController::class, 'update'])
        ->name('api.funcionarios.update');
    Route::delete('/api/funcionarios/{id}', [App\Http\Controllers\FuncionarioController::class, 'destroy'])
        ->name('api.funcionarios.destroy');

    // =============================================
    // MÓDULO CONTROLE DE FRETE
    // =============================================
    Route::prefix('frete')->middleware('can:frete')->group(function () {
        Route::get('/', [App\Http\Controllers\FreteController::class, 'index'])->name('frete.index');
        Route::get('/listar', [App\Http\Controllers\FreteController::class, 'listar'])->name('frete.listar');
        Route::get('/estatisticas', [App\Http\Controllers\FreteController::class, 'estatisticas'])->name('frete.estatisticas');
        Route::get('/buscar-os', [App\Http\Controllers\FreteController::class, 'buscarOS'])->name('frete.buscar-os');
        Route::post('/store', [App\Http\Controllers\FreteController::class, 'store'])->name('frete.store');
        
        // Rotas com ID (devem vir depois das rotas sem parâmetro)
        Route::get('/{id}', [App\Http\Controllers\FreteController::class, 'show'])->name('frete.show')->where('id', '[0-9]+');
        Route::post('/{id}/update', [App\Http\Controllers\FreteController::class, 'update'])->name('frete.update');
        Route::post('/{id}/cancelar', [App\Http\Controllers\FreteController::class, 'cancelar'])->name('frete.cancelar');
        Route::post('/{id}/aprovar', [App\Http\Controllers\FreteController::class, 'aprovar'])->name('frete.aprovar');
        Route::post('/{id}/entrega', [App\Http\Controllers\FreteController::class, 'confirmarEntrega'])->name('frete.entrega');
        Route::get('/os/{ordemServicoId}', [App\Http\Controllers\FreteController::class, 'listarPorOS'])->name('frete.por-os');
        
        // Cotações
        Route::post('/{freteId}/cotacao', [App\Http\Controllers\FreteController::class, 'adicionarCotacao'])->name('frete.cotacao.add');
        Route::post('/{freteId}/cotacao/{cotacaoId}/selecionar', [App\Http\Controllers\FreteController::class, 'selecionarCotacao'])->name('frete.cotacao.selecionar');
    });

    // =============================================
    // MÓDULO DOCUMENTOS DP (Departamento Pessoal)
    // =============================================
    Route::prefix('documentos-dp')->name('documentos-dp.')->group(function () {
        // Inclusão de documentos de funcionários
        Route::get('/inclusao', [App\Http\Controllers\DocumentosDPController::class, 'inclusao'])
            ->middleware('can:doc_dp')
            ->name('inclusao');
        Route::post('/inclusao', [App\Http\Controllers\DocumentosDPController::class, 'store'])
            ->middleware('can:doc_dp')
            ->name('store');

        // Página de funcionários (gestão completa)
        Route::get('/funcionarios', [App\Http\Controllers\DocumentosDPController::class, 'funcionarios'])
            ->middleware('can:vis_func')
            ->name('funcionarios');

        // Ordem de Serviço do DP
        Route::get('/ordem-servico', [App\Http\Controllers\DocumentosDPController::class, 'ordemServicoIndex'])
            ->middleware('can:vis_func')
            ->name('ordem-servico');
        Route::get('/ordem-servico/nova', [App\Http\Controllers\DocumentosDPController::class, 'ordemServicoNova'])
            ->middleware('can:vis_func')
            ->name('ordem-servico.nova');
        Route::post('/ordem-servico/store', [App\Http\Controllers\DocumentosDPController::class, 'ordemServicoStore'])
            ->middleware('can:vis_func')
            ->name('ordem-servico.store');
        Route::get('/ordem-servico/lista', [App\Http\Controllers\DocumentosDPController::class, 'ordemServicoLista'])
            ->middleware('can:vis_func')
            ->name('ordem-servico.lista');

        // Download de documentos BLOB
        Route::get('/download/{arquivoId}', [App\Http\Controllers\DocumentosDPController::class, 'downloadBLOB'])
            ->middleware('can:doc_dp')
            ->name('download');

        // Upload de foto do funcionário
        Route::post('/funcionario/upload-foto', [App\Http\Controllers\DocumentosDPController::class, 'uploadFotoFuncionario'])
            ->middleware('can:vis_func')
            ->name('funcionario.upload-foto');
        Route::post('/funcionario/remover-foto', [App\Http\Controllers\DocumentosDPController::class, 'removerFotoFuncionario'])
            ->middleware('can:vis_func')
            ->name('funcionario.remover-foto');

        // Rotas para gestão de funcionários na página funcionarios.blade.php
        Route::get('/buscar', [App\Http\Controllers\DocumentosDPController::class, 'buscarFuncionario'])
            ->middleware('can:vis_func')
            ->name('buscar');
        Route::get('/documentos/{id}', [App\Http\Controllers\DocumentosDPController::class, 'listarDocumentos'])
            ->middleware('can:vis_func')
            ->name('documentos');
        Route::get('/arquivo/{id}', [App\Http\Controllers\DocumentosDPController::class, 'downloadBLOB'])
            ->middleware('can:vis_func')
            ->name('arquivo');
        Route::post('/anexar/{id}', [App\Http\Controllers\DocumentosDPController::class, 'anexarFaltantes'])
            ->middleware('can:doc_dp')
            ->name('anexar');

        // =============================================
        // ROTAS DE API (usadas via fetch na view funcionarios.blade.php)
        // =============================================
        
        // Alterar status
        Route::post('/funcionario/{id}/alterar-status', [App\Http\Controllers\DocumentosDPController::class, 'alterarStatusFuncionario'])
            ->middleware('can:vis_func');
        
        // Atestados
        Route::get('/funcionario/{id}/atestados', [App\Http\Controllers\DocumentosDPController::class, 'listarAtestados'])
            ->middleware('can:vis_func');
        Route::post('/funcionario/{id}/atestados', [App\Http\Controllers\DocumentosDPController::class, 'anexarAtestado'])
            ->middleware('can:vis_func');
        Route::get('/atestado/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadAtestado'])
            ->middleware('can:vis_func');
        Route::delete('/atestado/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteAtestado'])
            ->middleware('can:vis_func');

        // Advertências
        Route::get('/funcionario/{id}/advertencias', [App\Http\Controllers\DocumentosDPController::class, 'listarAdvertencias'])
            ->middleware('can:vis_func');
        Route::post('/funcionario/{id}/advertencias', [App\Http\Controllers\DocumentosDPController::class, 'aplicarAdvertencia'])
            ->middleware('can:vis_func');
        Route::get('/advertencia/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadAdvertencia'])
            ->middleware('can:vis_func');
        Route::delete('/advertencia/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteAdvertencia'])
            ->middleware('can:vis_func');

        // EPIs
        Route::get('/funcionario/{id}/epis', [App\Http\Controllers\DocumentosDPController::class, 'listarEpis'])
            ->middleware('can:vis_func');

        // EPIs Retroativos
        Route::get('/funcionario/{id}/epis-retroativos', [App\Http\Controllers\DocumentosDPController::class, 'listarEpisRetroativos'])
            ->middleware('can:vis_func');
        Route::post('/epi-retroativo/store', [App\Http\Controllers\DocumentosDPController::class, 'storeEpiRetroativo'])
            ->middleware('can:vis_func');
        Route::get('/epi-retroativo/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadEpiRetroativo'])
            ->middleware('can:vis_func');
        Route::delete('/epi-retroativo/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteEpiRetroativo'])
            ->middleware('can:vis_func');

        // Contra-cheques
        Route::get('/funcionario/{id}/contra-cheques', [App\Http\Controllers\DocumentosDPController::class, 'listarContraCheques'])
            ->middleware('can:vis_func');
        Route::post('/contra-cheque/store', [App\Http\Controllers\DocumentosDPController::class, 'storeContraCheque'])
            ->middleware('can:vis_func');
        Route::get('/contra-cheque/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadContraCheque'])
            ->middleware('can:vis_func');
        Route::delete('/contra-cheque/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteContraCheque'])
            ->middleware('can:vis_func');

        // Férias
        Route::get('/funcionario/{id}/ferias', [App\Http\Controllers\DocumentosDPController::class, 'listarFerias'])
            ->middleware('can:vis_func');
        Route::post('/ferias/store', [App\Http\Controllers\DocumentosDPController::class, 'storeFerias'])
            ->middleware('can:vis_func');
        Route::get('/ferias/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadFerias'])
            ->middleware('can:vis_func');
        Route::delete('/ferias/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteFerias'])
            ->middleware('can:vis_func');

        // Décimo
        Route::get('/funcionario/{id}/decimo', [App\Http\Controllers\DocumentosDPController::class, 'listarDecimo'])
            ->middleware('can:vis_func');
        Route::post('/decimo/store', [App\Http\Controllers\DocumentosDPController::class, 'storeDecimo'])
            ->middleware('can:vis_func');
        Route::get('/decimo/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadDecimo'])
            ->middleware('can:vis_func');
        Route::delete('/decimo/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteDecimo'])
            ->middleware('can:vis_func');

        // Rescisão
        Route::get('/funcionario/{id}/rescisao', [App\Http\Controllers\DocumentosDPController::class, 'listarRescisao'])
            ->middleware('can:vis_func');
        Route::post('/rescisao/store', [App\Http\Controllers\DocumentosDPController::class, 'storeRescisao'])
            ->middleware('can:vis_func');
        Route::get('/rescisao/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadRescisao'])
            ->middleware('can:vis_func');
        Route::delete('/rescisao/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteRescisao'])
            ->middleware('can:vis_func');

        // Frequência
        Route::get('/funcionario/{id}/frequencia', [App\Http\Controllers\DocumentosDPController::class, 'listarFrequencia'])
            ->middleware('can:vis_func');
        Route::post('/frequencia/store', [App\Http\Controllers\DocumentosDPController::class, 'storeFrequencia'])
            ->middleware('can:vis_func');
        Route::get('/frequencia/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadFrequencia'])
            ->middleware('can:vis_func');
        Route::delete('/frequencia/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteFrequencia'])
            ->middleware('can:vis_func');

        // Certificados
        Route::get('/funcionario/{id}/certificado', [App\Http\Controllers\DocumentosDPController::class, 'listarCertificado'])
            ->middleware('can:vis_func');
        Route::post('/certificado/store', [App\Http\Controllers\DocumentosDPController::class, 'storeCertificado'])
            ->middleware('can:vis_func');
        Route::get('/certificado/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadCertificado'])
            ->middleware('can:vis_func');
        Route::delete('/certificado/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteCertificado'])
            ->middleware('can:vis_func');

        // Termo Aditivo
        Route::get('/funcionario/{id}/termo-aditivo', [App\Http\Controllers\DocumentosDPController::class, 'listarTermoAditivo'])
            ->middleware('can:vis_func');
        Route::post('/termo-aditivo/store', [App\Http\Controllers\DocumentosDPController::class, 'storeTermoAditivo'])
            ->middleware('can:vis_func');
        Route::get('/termo-aditivo/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadTermoAditivo'])
            ->middleware('can:vis_func');
        Route::delete('/termo-aditivo/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteTermoAditivo'])
            ->middleware('can:vis_func');

        // ASOS
        Route::get('/funcionario/{id}/asos', [App\Http\Controllers\DocumentosDPController::class, 'listarAsos'])
            ->middleware('can:vis_func');
        Route::post('/asos/store', [App\Http\Controllers\DocumentosDPController::class, 'storeAsos'])
            ->middleware('can:vis_func');
        Route::get('/asos/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadAsos'])
            ->middleware('can:vis_func');
        Route::delete('/asos/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteAsos'])
            ->middleware('can:vis_func');
    });

    // APIs do Módulo Documentos DP
    Route::prefix('api/documentos-dp')->middleware('auth')->group(function () {
        // Check CPF duplicidade
        Route::get('/check-cpf', [App\Http\Controllers\DocumentosDPController::class, 'checkCpf']);
        
        // Buscar funcionário
        Route::get('/buscar-funcionario', [App\Http\Controllers\DocumentosDPController::class, 'buscarFuncionario']);
        
        // Listar documentos do funcionário
        Route::get('/funcionario/{id}/documentos', [App\Http\Controllers\DocumentosDPController::class, 'listarDocumentos']);
        Route::post('/funcionario/{id}/anexar', [App\Http\Controllers\DocumentosDPController::class, 'anexarFaltantes']);
        Route::post('/funcionario/{id}/status', [App\Http\Controllers\DocumentosDPController::class, 'alterarStatusFuncionario']);
        Route::post('/funcionario/{id}/demitir', [App\Http\Controllers\DocumentosDPController::class, 'demitirFuncionario']);
        
        // Atestados
        Route::get('/funcionario/{id}/atestados', [App\Http\Controllers\DocumentosDPController::class, 'listarAtestados']);
        Route::post('/funcionario/{id}/atestado', [App\Http\Controllers\DocumentosDPController::class, 'anexarAtestado']);
        Route::get('/atestado/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadAtestado']);
        Route::delete('/atestado/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteAtestado']);
        
        // Advertências
        Route::get('/funcionario/{id}/advertencias', [App\Http\Controllers\DocumentosDPController::class, 'listarAdvertencias']);
        Route::post('/funcionario/{id}/advertencia', [App\Http\Controllers\DocumentosDPController::class, 'aplicarAdvertencia']);
        Route::get('/advertencia/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadAdvertencia']);
        Route::delete('/advertencia/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteAdvertencia']);
        
        // EPIs (materiais retirados)
        Route::get('/funcionario/{id}/epis', [App\Http\Controllers\DocumentosDPController::class, 'listarEpis']);
        
        // EPIs Retroativos
        Route::get('/funcionario/{id}/epis-retroativos', [App\Http\Controllers\DocumentosDPController::class, 'listarEpisRetroativos']);
        Route::post('/epi-retroativo', [App\Http\Controllers\DocumentosDPController::class, 'storeEpiRetroativo']);
        Route::get('/epi-retroativo/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadEpiRetroativo']);
        Route::delete('/epi-retroativo/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteEpiRetroativo']);
        
        // Contra-cheques
        Route::get('/funcionario/{id}/contra-cheques', [App\Http\Controllers\DocumentosDPController::class, 'listarContraCheques']);
        Route::post('/contra-cheque', [App\Http\Controllers\DocumentosDPController::class, 'storeContraCheque']);
        Route::get('/contra-cheque/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadContraCheque']);
        Route::delete('/contra-cheque/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteContraCheque']);
        
        // Férias
        Route::get('/funcionario/{id}/ferias', [App\Http\Controllers\DocumentosDPController::class, 'listarFerias']);
        Route::post('/ferias', [App\Http\Controllers\DocumentosDPController::class, 'storeFerias']);
        Route::get('/ferias/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadFerias']);
        Route::delete('/ferias/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteFerias']);
        
        // Décimo Terceiro
        Route::get('/funcionario/{id}/decimo', [App\Http\Controllers\DocumentosDPController::class, 'listarDecimo']);
        Route::post('/decimo', [App\Http\Controllers\DocumentosDPController::class, 'storeDecimo']);
        Route::get('/decimo/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadDecimo']);
        Route::delete('/decimo/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteDecimo']);
        
        // Rescisão
        Route::get('/funcionario/{id}/rescisao', [App\Http\Controllers\DocumentosDPController::class, 'listarRescisao']);
        Route::post('/rescisao', [App\Http\Controllers\DocumentosDPController::class, 'storeRescisao']);
        Route::get('/rescisao/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadRescisao']);
        Route::delete('/rescisao/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteRescisao']);
        
        // Frequência
        Route::get('/funcionario/{id}/frequencia', [App\Http\Controllers\DocumentosDPController::class, 'listarFrequencia']);
        Route::post('/frequencia', [App\Http\Controllers\DocumentosDPController::class, 'storeFrequencia']);
        Route::get('/frequencia/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadFrequencia']);
        Route::delete('/frequencia/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteFrequencia']);
        
        // Certificados
        Route::get('/funcionario/{id}/certificados', [App\Http\Controllers\DocumentosDPController::class, 'listarCertificado']);
        Route::post('/certificado', [App\Http\Controllers\DocumentosDPController::class, 'storeCertificado']);
        Route::get('/certificado/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadCertificado']);
        Route::delete('/certificado/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteCertificado']);
        
        // Termos Aditivos
        Route::get('/funcionario/{id}/termo-aditivo', [App\Http\Controllers\DocumentosDPController::class, 'listarTermoAditivo']);
        Route::post('/termo-aditivo', [App\Http\Controllers\DocumentosDPController::class, 'storeTermoAditivo']);
        Route::get('/termo-aditivo/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadTermoAditivo']);
        Route::delete('/termo-aditivo/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteTermoAditivo']);
        
        // ASOS
        Route::get('/funcionario/{id}/asos', [App\Http\Controllers\DocumentosDPController::class, 'listarAsos']);
        Route::post('/asos', [App\Http\Controllers\DocumentosDPController::class, 'storeAsos']);
        Route::get('/asos/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadAsos']);
        Route::delete('/asos/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteAsos']);
        
        // Documentos gerais
        Route::delete('/documento/{id}', [App\Http\Controllers\DocumentosDPController::class, 'deleteDocumento']);
        
        // Gerar arquivo completo (ZIP)
        Route::get('/funcionario/{id}/arquivo-completo', [App\Http\Controllers\DocumentosDPController::class, 'gerarArquivoCompleto']);
        
        // Visualizar PDF completo
        Route::get('/funcionario/{id}/pdf-completo', [App\Http\Controllers\DocumentosDPController::class, 'visualizarPdfCompleto']);
    });

    // APIs públicas para busca de funcionários (usadas em autocomplete)
    Route::get('/api/funcionarios-busca', function(\Illuminate\Http\Request $request) {
        $q = $request->get('q', '');
        if (strlen($q) < 3) {
            return response()->json(['success' => true, 'data' => []]);
        }
        $funcionarios = DB::table('funcionarios')
            ->select('id', 'nome', 'cpf', 'funcao')
            ->where('nome', 'LIKE', '%' . $q . '%')
            ->orderBy('nome')
            ->limit(20)
            ->get();
        return response()->json(['success' => true, 'data' => $funcionarios]);
    })->middleware('auth');

    // API para O.S. específica (usada no modal de visualização)
    Route::get('/api/ordens-servico/{id}', [App\Http\Controllers\DocumentosDPController::class, 'ordemServicoShow'])
        ->middleware('auth');

    // =============================================
    // CONTROLE DE SAÍDA DE ENCARREGADOS (Estoque)
    // =============================================
    Route::prefix('estoque')->name('estoque.')->middleware('auth')->group(function () {
        // Página principal
        Route::get('/controle-saida-encarregados', [App\Http\Controllers\ControleSaidaEncarregadosController::class, 'index'])
            ->name('controle-saida-encarregados');

        // APIs do Controle de Saída
        Route::get('/api/funcionarios', [App\Http\Controllers\ControleSaidaEncarregadosController::class, 'buscarFuncionarios']);
        Route::get('/api/centros-custo', [App\Http\Controllers\ControleSaidaEncarregadosController::class, 'buscarCentrosCusto']);
        Route::get('/api/produtos', [App\Http\Controllers\ControleSaidaEncarregadosController::class, 'buscarProdutos']);
        Route::get('/api/proximo-numero-ficha', [App\Http\Controllers\ControleSaidaEncarregadosController::class, 'proximoNumeroFicha']);
        Route::post('/api/salvar-ficha-epi', [App\Http\Controllers\ControleSaidaEncarregadosController::class, 'salvar']);
        Route::post('/api/upload-foto-epi', [App\Http\Controllers\ControleSaidaEncarregadosController::class, 'uploadFoto']);
    });

    // =============================================
    // MÓDULO SUPRIMENTOS
    // =============================================
    Route::prefix('suprimentos')->name('suprimentos.')->group(function () {
        // Fornecedores
        Route::get('/fornecedores', [App\Http\Controllers\SuprimentosController::class, 'fornecedores'])
            ->middleware('can:fornecedores')
            ->name('fornecedores');

        // Solicitação
        Route::get('/solicitacao', [App\Http\Controllers\SuprimentosController::class, 'solicitacao'])
            ->middleware('can:Solicitacao')
            ->name('solicitacao');

        // Cotação
        Route::get('/cotacao', [App\Http\Controllers\SuprimentosController::class, 'cotacao'])
            ->middleware('can:cotacao')
            ->name('cotacao');

        // Ordem de Compra
        Route::get('/ordem-compra', [App\Http\Controllers\SuprimentosController::class, 'ordemCompra'])
            ->middleware('can:ordem_compra')
            ->name('ordem-compra');

        // Recebimento
        Route::get('/recebimento', [App\Http\Controllers\SuprimentosController::class, 'recebimento'])
            ->middleware('can:recebimento')
            ->name('recebimento');
        
        // Imprimir Recebimento
        Route::get('/recebimentos/{id}/imprimir', [App\Http\Controllers\SuprimentosController::class, 'imprimirRecebimento'])
            ->middleware('can:recebimento')
            ->name('recebimentos.imprimir');

        // Nota Fiscal de Entrada
        Route::get('/nf-entrada', [App\Http\Controllers\SuprimentosController::class, 'nfEntrada'])
            ->middleware('can:nf_entrada')
            ->name('nf-entrada');

        // Vale de Retirada - DESATIVADO
        // Route::get('/vale-retirada', [App\Http\Controllers\SuprimentosController::class, 'valeRetirada'])
        //     ->middleware('can:vale_retirada')
        //     ->name('vale-retirada');
        
        // Centros de Custo
        Route::get('/centros-custo', [App\Http\Controllers\FinanceiroController::class, 'centrosCusto'])
            ->middleware('can:cc_financeiro')
            ->name('centros-custo');
    });
    
    // APIs do Módulo Suprimentos
    Route::prefix('api/suprimentos')->middleware('auth')->group(function () {
        // Estoque
        Route::get('/estoque/buscar', [App\Http\Controllers\SuprimentosController::class, 'buscarProdutosEstoque']);
        
        // Fornecedores
        Route::get('/fornecedores/buscar', [App\Http\Controllers\SuprimentosController::class, 'buscarFornecedores']);
        Route::post('/fornecedores', [App\Http\Controllers\SuprimentosController::class, 'storeFornecedor']);
        Route::get('/fornecedores/{id}', [App\Http\Controllers\SuprimentosController::class, 'getFornecedor']);
        Route::put('/fornecedores/{id}', [App\Http\Controllers\SuprimentosController::class, 'updateFornecedor']);
        Route::delete('/fornecedores/{id}', [App\Http\Controllers\SuprimentosController::class, 'deleteFornecedor']);
        
        // Solicitações
        Route::post('/solicitacoes', [App\Http\Controllers\SuprimentosController::class, 'storeSolicitacao']);
        Route::get('/solicitacoes/{id}', [App\Http\Controllers\SuprimentosController::class, 'getSolicitacao']);
        Route::put('/solicitacoes/{id}', [App\Http\Controllers\SuprimentosController::class, 'updateSolicitacao']);
        Route::delete('/solicitacoes/{id}', [App\Http\Controllers\SuprimentosController::class, 'deleteSolicitacao']);
        Route::post('/solicitacoes/{id}/aprovar', [App\Http\Controllers\SuprimentosController::class, 'aprovarSolicitacao']);
        Route::post('/solicitacoes/{id}/reprovar', [App\Http\Controllers\SuprimentosController::class, 'reprovarSolicitacao']);
        Route::post('/solicitacoes/{id}/gerar-cotacao', [App\Http\Controllers\SuprimentosController::class, 'gerarCotacaoFromSolicitacao']);
        Route::post('/solicitacoes/vincular-os', [App\Http\Controllers\SuprimentosController::class, 'storeSolicitacaoVinculadaOS']);
        
        // Cotações
        Route::post('/cotacoes', [App\Http\Controllers\SuprimentosController::class, 'storeCotacao']);
        Route::get('/cotacoes/{id}', [App\Http\Controllers\SuprimentosController::class, 'getCotacao']);
        Route::post('/cotacoes/{id}/valores', [App\Http\Controllers\SuprimentosController::class, 'saveValoresCotacao']);
        Route::post('/cotacoes/{id}/adicionar-fornecedores', [App\Http\Controllers\SuprimentosController::class, 'adicionarFornecedoresCotacao']);
        Route::post('/cotacoes/{id}/enviar-autorizacao', [App\Http\Controllers\SuprimentosController::class, 'enviarParaAutorizacao']);
        Route::post('/cotacoes/{id}/gerar-oc', [App\Http\Controllers\SuprimentosController::class, 'gerarOC']);
        Route::delete('/cotacoes/{id}', [App\Http\Controllers\SuprimentosController::class, 'deleteCotacao']);
        Route::post('/corrigir-cotacoes', [App\Http\Controllers\SuprimentosController::class, 'corrigirCotacoes']);
        
        // Ordens de Compra
        Route::get('/ordens-compra/listar', [App\Http\Controllers\SuprimentosController::class, 'listarOrdensCompra']);
        Route::post('/ordens-compra', [App\Http\Controllers\SuprimentosController::class, 'storeOrdemCompra']);
        Route::get('/ordens-compra/{id}', [App\Http\Controllers\SuprimentosController::class, 'getOrdemCompra']);
        Route::put('/ordens-compra/{id}/status', [App\Http\Controllers\SuprimentosController::class, 'updateStatusOC']);
        Route::post('/ordens-compra/{id}/aprovar', [App\Http\Controllers\SuprimentosController::class, 'aprovarOrdemCompra']);
        Route::post('/ordens-compra/{id}/recusar', [App\Http\Controllers\SuprimentosController::class, 'recusarOrdemCompra']);
        Route::delete('/ordens-compra/{id}', [App\Http\Controllers\SuprimentosController::class, 'deleteOrdemCompra']);
        
        // Recebimentos
        Route::post('/recebimentos/validar', [App\Http\Controllers\SuprimentosController::class, 'validarRecebimento']);
        Route::post('/recebimentos', [App\Http\Controllers\SuprimentosController::class, 'storeRecebimento']);
        Route::get('/recebimentos/{id}', [App\Http\Controllers\SuprimentosController::class, 'getRecebimento']);
        Route::delete('/recebimentos/{id}', [App\Http\Controllers\SuprimentosController::class, 'destroyRecebimento']);
        
        // NF Entrada
        Route::post('/nf-entrada', [App\Http\Controllers\SuprimentosController::class, 'storeNFEntrada']);
        
        // Vales de Retirada - DESATIVADO
        // Route::post('/vales', [App\Http\Controllers\SuprimentosController::class, 'storeVale']);
        // Route::put('/vales/{id}/status', [App\Http\Controllers\SuprimentosController::class, 'updateStatusVale']);
        
        // Centros de Custo
        Route::get('/centros-custo', [App\Http\Controllers\FinanceiroController::class, 'listarCentrosCusto']);
        Route::get('/centros-custo/listar', [App\Http\Controllers\FinanceiroController::class, 'listarCentrosCustoAutocomplete']);
        Route::get('/centros-custo/{id}', [App\Http\Controllers\FinanceiroController::class, 'getCentroCusto']);
        Route::post('/centros-custo', [App\Http\Controllers\FinanceiroController::class, 'storeCentroCusto']);
        Route::put('/centros-custo/{id}', [App\Http\Controllers\FinanceiroController::class, 'updateCentroCusto']);
        Route::delete('/centros-custo/{id}', [App\Http\Controllers\FinanceiroController::class, 'deleteCentroCusto']);
    });

    // Rotas de BRS - Controle de Estoque - protegidas pela permissão 'Controle de Estoque'
    Route::middleware(['can:controle-estoque','throttle:120,1'])->group(function () {
    Route::get('/brs/controle-estoque', [App\Http\Controllers\ControleEstoqueController::class, 'index'])->name('brs.controle-estoque');
    Route::get('/api/estoque/funcionarios', [App\Http\Controllers\ControleEstoqueController::class, 'buscarFuncionarios']);
    Route::get('/api/centro-custos', [App\Http\Controllers\ControleEstoqueController::class, 'buscarCentroCustos']);
    Route::get('/api/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'buscarProdutos']);
    Route::get('/api/produtos-em-falta', [App\Http\Controllers\ControleEstoqueController::class, 'produtosEmFalta']);
    // Endpoint específico do módulo de estoque para evitar conflito com pedidos de compras
    Route::get('/api/estoque/produtos/buscar', [App\Http\Controllers\ControleEstoqueController::class, 'buscarProdutosPorNome']);
    Route::get('/api/estoque/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'listarTodosProdutos']);
    Route::get('/api/estoque/unidades-medida', [App\Http\Controllers\ControleEstoqueController::class, 'listarUnidadesMedida']);
    Route::post('/api/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'criarProduto']);
    Route::put('/api/produtos/{id}', [App\Http\Controllers\ControleEstoqueController::class, 'atualizarProduto']);
    Route::post('/api/entradas', [App\Http\Controllers\ControleEstoqueController::class, 'registrarEntrada']);
    Route::post('/api/baixas/verificar-funcionario', [App\Http\Controllers\ControleEstoqueController::class, 'verificarFardamentoFuncionario']);
    Route::post('/api/baixas/verificar', [App\Http\Controllers\ControleEstoqueController::class, 'verificarPrazoFardamento']);
    Route::post('/api/baixas', [App\Http\Controllers\ControleEstoqueController::class, 'registrarBaixa']);
});

// Módulo Frota - somente para usuários com permissões
Route::middleware(['auth'])->prefix('frota')->name('frota.')->group(function () {
    // Veículos
    Route::get('/veiculos', [VeiculoController::class, 'index'])
        ->middleware('can:veiculos')->name('veiculos.index');
    // APIs Veículos
    // Leitura liberada para quem possui acesso a Viagens (usado pela tela de Nova Viagem)
    Route::get('/api/veiculos', [VeiculoController::class, 'json'])
        ->middleware('can:viagens');
    // Leitura para relatórios de KM (sem exigir permissão de Viagens)
    Route::get('/api/veiculos-relatorio', [VeiculoController::class, 'json'])
        ->middleware('can:rel_km');
    Route::get('/api/veiculos/{id}', [VeiculoController::class, 'showJson'])
        ->middleware('can:viagens');
    Route::post('/api/veiculos', [VeiculoController::class, 'store'])
        ->middleware('can:veiculos');
    Route::put('/api/veiculos/{id}', [VeiculoController::class, 'update'])
        ->middleware('can:veiculos');
    Route::delete('/api/veiculos/{id}', [VeiculoController::class, 'destroy'])
        ->middleware('can:veiculos');

    // Abastecimentos
    Route::get('/abastecimentos', [AbastecimentoController::class, 'index'])
        ->middleware('can:abastecimento')->name('abastecimentos.index');
    Route::get('/api/abastecimentos', [AbastecimentoController::class, 'json'])
        ->middleware('can:abastecimento');
    Route::post('/api/abastecimentos', [AbastecimentoController::class, 'store'])
        ->middleware('can:abastecimento');
    Route::put('/api/abastecimentos/{id}', [AbastecimentoController::class, 'update'])
        ->middleware('can:abastecimento');
    Route::delete('/api/abastecimentos/{id}', [AbastecimentoController::class, 'destroy'])
        ->middleware('can:abastecimento');

    // Manutenções
    Route::get('/manutencoes', [ManutencaoController::class, 'index'])
        ->middleware('can:manutencao')->name('manutencoes.index');
    Route::get('/api/manutencoes', [ManutencaoController::class, 'json'])
        ->middleware('can:manutencao');
    Route::post('/api/manutencoes', [ManutencaoController::class, 'store'])
        ->middleware('can:manutencao');
    Route::put('/api/manutencoes/{id}', [ManutencaoController::class, 'update'])
        ->middleware('can:manutencao');
    Route::delete('/api/manutencoes/{id}', [ManutencaoController::class, 'destroy'])
        ->middleware('can:manutencao');

    // Viagens
    Route::get('/viagens', [ViagemController::class, 'index'])
        ->middleware('can:viagens')->name('viagens.index');
    // Leitura aberta para o módulo Viagens
    Route::get('/api/viagens', [ViagemController::class, 'json'])
        ->middleware('can:viagens');
    // Endpoint espefícico para relatórios de KM (perm rel_km)
    Route::get('/api/viagens-relatorio', [ViagemController::class, 'json'])
        ->middleware('can:rel_km');
    // Escrita/alteração continuam protegidas
    Route::post('/api/viagens', [ViagemController::class, 'store'])
        ->middleware('can:viagens');
    Route::put('/api/viagens/{id}', [ViagemController::class, 'update'])
        ->middleware('can:viagens');
    Route::delete('/api/viagens/{id}', [ViagemController::class, 'destroy'])
        ->middleware('can:viagens');

    // Relatórios
    Route::get('/relatorios/consumo', function(){
        return view('frota.relatorios.consumo');
    })->middleware('can:rel_consm')->name('relatorios.consumo');

    Route::get('/relatorios/custo', function(){
        return view('frota.relatorios.custo');
    })->middleware('can:rel_cust')->name('relatorios.custo');

    // Relatório de Manutenções (Frota)
    Route::get('/relatorios/manutencoes', [\App\Http\Controllers\RelatorioManutencaoController::class, 'index'])
        ->middleware('can:Rel_manu')->name('relatorios.manutencoes');

    // API Relatório de Manutenções (Frota)
    Route::get('/api/relatorios/manutencoes', [\App\Http\Controllers\RelatorioManutencaoController::class, 'data'])
        ->middleware('can:Rel_manu');

    // Relatório de Ocorrências (Frota)
    Route::get('/relatorios/ocorrencias', [\App\Http\Controllers\OcorrenciaController::class, 'relatorioIndex'])
        ->middleware('can:rel_ocorr')->name('relatorios.ocorrencias');

    // API Relatório de Ocorrências (Frota)
    Route::get('/api/relatorios/ocorrencias', [\App\Http\Controllers\OcorrenciaController::class, 'relatorioData'])
        ->middleware('can:rel_ocorr');

    // Relatório de Abastecimento (Frota)
    Route::get('/relatorios/abastecimento', function(){
        return view('frota.relatorios.abastecimento');
    })->middleware('can:rel_abast')->name('relatorios.abastecimento');

    // Relatório: KM Percorrido (permissão: rel_km)
    Route::get('/relatorios/km-percorrido', [RelatorioKmController::class, 'index'])
        ->middleware('can:rel_km')->name('relatorios.km-percorrido');
    Route::get('/api/relatorios/km-percorrido', [RelatorioKmController::class, 'data'])
        ->middleware('can:rel_km');

    // API: opções para selects (veículos e usuários)
    Route::get('/api/relatorios/abastecimento/opcoes', function(){
        $veiculos = \DB::table('veiculos')->select('id','placa')->orderBy('placa')->get();
        $usuarios = \DB::table('users')->select('id','name')->orderBy('name')->get();
        return response()->json(['success' => true, 'veiculos' => $veiculos, 'usuarios' => $usuarios]);
    })->middleware('can:rel_abast');

    // API: listagem detalhada com filtros
    Route::get('/api/relatorios/abastecimento', function(\Illuminate\Http\Request $request){
        $ini = $request->query('data_ini');
        $fim = $request->query('data_fim');
        $veiculoId = $request->query('veiculo_id');
        $userId = $request->query('user_id');

        $query = \DB::table('abastecimentos as a')
            ->leftJoin('veiculos as v', 'a.vehicle_id', '=', 'v.id')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->selectRaw('DATE_FORMAT(a.data, "%d/%m/%Y") as data, v.placa, u.name as funcionario, a.km, a.litros, a.preco_litro, a.valor, a.tipo_combustivel, a.posto')
            ->when($ini, function($q) use ($ini){ $q->whereDate('a.data', '>=', $ini); })
            ->when($fim, function($q) use ($fim){ $q->whereDate('a.data', '<=', $fim); })
            ->when($veiculoId, function($q) use ($veiculoId){ $q->where('a.vehicle_id', $veiculoId); })
            ->when($userId, function($q) use ($userId){ $q->where('a.user_id', $userId); })
            ->orderByRaw('a.data desc, v.placa asc');

        $dados = $query->limit(2000)->get();

        $totais = [
            'litros' => (float) $dados->sum('litros'),
            'valor'  => (float) $dados->sum('valor'),
        ];

        return response()->json(['success' => true, 'data' => $dados, 'totais' => $totais]);
    })->middleware('can:rel_abast');

    // Ocorrências da Frota
    Route::get('/ocorrencias', [App\Http\Controllers\OcorrenciaController::class, 'index'])
        ->middleware('can:ocorrencia')->name('ocorrencias.index');
    Route::post('/ocorrencias', [App\Http\Controllers\OcorrenciaController::class, 'store'])
        ->middleware('can:ocorrencia')->name('ocorrencias.store');

    // Gestor de Ocorrências (somente quem tem a permissão específica)
    Route::get('/ocorrencias/gestor', [App\Http\Controllers\OcorrenciaController::class, 'gestor'])
        ->middleware('can:Gestão de Ocorrencia')->name('ocorrencias.gestor');

    // Impressão de ocorrência
    Route::get('/ocorrencias/{id}/print', [App\Http\Controllers\OcorrenciaController::class, 'print'])
        ->whereNumber('id')
        ->middleware('can:rel_ocorr')
        ->name('ocorrencias.print');

    // APIs para o gestor
    Route::get('/ocorrencias/api/{id}', [App\Http\Controllers\OcorrenciaController::class, 'showOccurrence'])
        ->middleware('can:Gestão de Ocorrencia');
    Route::post('/ocorrencias/api/{id}/status', [App\Http\Controllers\OcorrenciaController::class, 'updateStatus'])
        ->middleware('can:Gestão de Ocorrencia');
    Route::get('/ocorrencias/api/veiculo/{veiculoId}/historico', [App\Http\Controllers\OcorrenciaController::class, 'historicoVeiculo'])
        ->middleware('can:Gestão de Ocorrencia');
    Route::get('/ocorrencias/api/{id}/fotos', [App\Http\Controllers\OcorrenciaController::class, 'fotos'])
        ->middleware('can:Gestão de Ocorrencia');
    Route::get('/ocorrencias/api/{id}/foto/{idx}', [App\Http\Controllers\OcorrenciaController::class, 'foto'])
        ->whereNumber('idx')
        ->middleware('can:Gestão de Ocorrencia');

});

    // Rotas de Relatórios - protegidas por suas respectivas permissões
    Route::get('/relatorios', function() {
        return view('relatorios.index');
    })->middleware('auth')->name('relatorios.index');
    
    Route::middleware(['can:relatorio-estoque','throttle:60,1'])->group(function () {
        Route::get('/relatorios/estoque', [App\Http\Controllers\RelatorioEstoqueController::class, 'index'])->name('relatorios.estoque');
        Route::post('/api/relatorio-estoque', [App\Http\Controllers\RelatorioEstoqueController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-estoque/exportar', [App\Http\Controllers\RelatorioEstoqueController::class, 'exportarExcel']);
        Route::get('/api/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'buscarProdutos']);
    });

    // Relatório por Produto (Estoque) – permissão: rel_por_prod
    Route::get('/relatorios/produto-estoque', [RelatorioProdutoEstoqueController::class, 'index'])
        ->middleware('can:rel_por_prod')->name('relatorios.produto-estoque');
    Route::get('/api/relatorios/produto-estoque', [RelatorioProdutoEstoqueController::class, 'data'])
        ->middleware('can:rel_por_prod');
    Route::get('/api/relatorios/produto-estoque/centros', [RelatorioProdutoEstoqueController::class, 'centros'])
        ->middleware('can:rel_por_prod');
    Route::get('/api/relatorios/produto-estoque/produtos', [RelatorioProdutoEstoqueController::class, 'produtos'])
        ->middleware('can:rel_por_prod');

    Route::middleware(['can:relatorio-centro-custo','throttle:60,1'])->group(function () {
        Route::get('/relatorios/centro-custo', [App\Http\Controllers\RelatorioCentroCustoController::class, 'index'])->name('relatorios.centro-custo');
        Route::post('/api/relatorio-centro-custo', [App\Http\Controllers\RelatorioCentroCustoController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-centro-custo/exportar', [App\Http\Controllers\RelatorioCentroCustoController::class, 'exportarExcel']);
    });

    Route::middleware(['can:relatorio-funcionario','throttle:60,1'])->group(function () {
        Route::get('/relatorios/funcionario', [App\Http\Controllers\RelatorioPorFuncionarioController::class, 'index'])->name('relatorios.funcionario');
        Route::post('/api/relatorio-funcionario', [App\Http\Controllers\RelatorioPorFuncionarioController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-funcionario/exportar', [App\Http\Controllers\RelatorioPorFuncionarioController::class, 'exportarExcel']);
    });

    // Relatório Estoque - Máximo e Mínimo (perm: rel_maxmin)
    Route::get('/relatorios/estoque-min-max', [App\Http\Controllers\RelatorioEstoqueMinMaxController::class, 'index'])
        ->middleware('can:rel_maxmin')
        ->name('relatorios.estoque-min-max');
    Route::get('/api/relatorios/estoque-min-max', [App\Http\Controllers\RelatorioEstoqueMinMaxController::class, 'data'])
        ->middleware('can:rel_maxmin');

    // Relatório Contas a Pagar (perm: Rel_cp)
    Route::get('/relatorios/contas-pagar', function () {
        return view('relatorios.contas-pagar');
    })->middleware('can:Rel_cp')->name('relatorios.contas-pagar');

    // Relatório Contas a Receber (perm: rel_crec)
    Route::get('/relatorios/contas-receber', function () {
        return view('relatorios.contas-receber');
    })->middleware('can:rel_crec')->name('relatorios.contas-receber');

    // Relatório de Solicitações (perm: rel_sol)
    Route::get('/relatorios/solicitacoes', [App\Http\Controllers\RelatorioSolicitacaoController::class, 'index'])
        ->middleware('can:rel_sol')->name('relatorios.solicitacoes');
    Route::get('/api/relatorios/solicitacoes', [App\Http\Controllers\RelatorioSolicitacaoController::class, 'data'])
        ->middleware('can:rel_sol');

    // Relatório de Cotações (perm: Rel_cot)
    Route::get('/relatorios/cotacoes', [App\Http\Controllers\RelatorioCotacaoController::class, 'index'])
        ->middleware('can:Rel_cot')->name('relatorios.cotacoes');
    Route::get('/api/relatorios/cotacoes', [App\Http\Controllers\RelatorioCotacaoController::class, 'data'])
        ->middleware('can:Rel_cot');

    // Relatório de O.S. (perm: rel_os)
    Route::get('/relatorios/ordem-servico', [App\Http\Controllers\RelatorioOrdemServicoController::class, 'index'])
        ->middleware('can:rel_os')->name('relatorios.ordem-servico');
    Route::get('/api/relatorios/ordem-servico', [App\Http\Controllers\RelatorioOrdemServicoController::class, 'data'])
        ->middleware('can:rel_os');
    Route::get('/api/relatorios/ordem-servico/{id}/detalhes', [App\Http\Controllers\RelatorioOrdemServicoController::class, 'detalhes'])
        ->middleware('can:rel_os');

    // Estoque - Mínimo e Máximo (perm: est_mm)
    // Baixa da O.S. - Recebe materiais das O.S.
    Route::get('/brs/baixa-os', function () {
        if (!\Auth::check() || !\Auth::user()->temPermissao('baixa_os')) {
            abort(403, 'Ação não autorizada.');
        }
        return view('brs.baixa-os');
    })->name('brs.baixa-os');
    
    // API para Baixa da O.S.
    Route::get('/api/baixa-os/listar', [App\Http\Controllers\BaixaOSController::class, 'listar']);
    Route::post('/api/baixa-os/{id}/liberar', [App\Http\Controllers\BaixaOSController::class, 'liberar']);
    Route::post('/api/baixa-os/{id}/excluir', [App\Http\Controllers\BaixaOSController::class, 'excluir']);
    Route::get('/api/baixa-os/logs', [App\Http\Controllers\BaixaOSController::class, 'listarLogs']);
    Route::get('/api/baixa-os/funcionarios', [App\Http\Controllers\BaixaOSController::class, 'buscarFuncionarios']);

    Route::get('/brs/estoque-min-max', function () {
        if (!\Auth::check() || !\Auth::user()->temPermissao('est_mm')) {
            abort(403, 'Ação não autorizada.');
        }
        return view('brs.estoque-min-max');
    })->name('brs.estoque-min-max');

    // APIs - Estoque Min/Max (protegidas por permissão est_mm)
    Route::get('/api/estoque/min-max', [App\Http\Controllers\EstoqueMinMaxController::class, 'listar'])
        ->middleware(['auth','throttle:120,1']);
    Route::post('/api/estoque/{produtoId}/min-max', [App\Http\Controllers\EstoqueMinMaxController::class, 'salvar'])
        ->middleware(['auth','throttle:120,1']);
    Route::post('/api/estoque/{produtoId}/inativar', [App\Http\Controllers\EstoqueMinMaxController::class, 'inativar'])
        ->middleware(['auth','throttle:60,1']);

    // Endpoints utilitários disponíveis para telas autenticadas (ex.: Controle de Estoque)
    Route::middleware(['auth','throttle:120,1'])->group(function () {
        Route::get('/api/centros-custo', function() {
            return response()->json(\DB::table('centros_custo')->where('ativo', 1)->orderBy('nome')->get());
        });
        Route::get('/api/centros-custo/buscar', function(\Illuminate\Http\Request $request) {
            $q = $request->input('q', '');
            return response()->json(
                \DB::table('centros_custo')
                    ->where('ativo', 1)
                    ->where('nome', 'like', "%{$q}%")
                    ->orderBy('nome')
                    ->limit(20)
                    ->get()
            );
        });
        
        // Buscar centros de custo que CONTÊM o termo em qualquer parte (para autocomplete)
        Route::get('/api/centros-custo/buscar-inicio', function(\Illuminate\Http\Request $request) {
            $termo = trim((string) $request->input('termo', ''));
            if (mb_strlen($termo) < 3) {
                return response()->json([]);
            }
            return response()->json(
                \DB::table('centros_custo')
                    ->where('ativo', 1)
                    ->where('nome', 'like', '%'.$termo.'%')
                    ->orderBy('nome')
                    ->limit(30)
                    ->get(['id', 'nome'])
            );
        });
    });

    // Rotas de Permissões
    Route::middleware(['auth'])->group(function () {
        // Rota para a página de permissões - usando o controller
        Route::get('/permissoes', [App\Http\Controllers\PermissoesController::class, 'index'])->name('admin.permissoes');

        // API para gerenciar permissões
        Route::prefix('api')->group(function () {
            Route::get('/permissoes/{id}', [App\Http\Controllers\PermissoesController::class, 'show']);
            Route::post('/permissoes', [App\Http\Controllers\PermissoesController::class, 'store']);
            Route::put('/permissoes/{id}', [App\Http\Controllers\PermissoesController::class, 'update']);
            Route::delete('/permissoes/{id}', [App\Http\Controllers\PermissoesController::class, 'destroy']);
        });

        // Rota para a página de usuários (listagem)
        Route::get('/gerenciar-perfis', function () {
            // Buscar usuários diretamente do banco de dados com join
            $usuarios = DB::table('users')
                ->select('users.*', 'profiles.name as profile_name')
                ->leftJoin('profiles', 'users.profile_id', '=', 'profiles.id')
                ->get();
            $perfis = DB::table('profiles')->get();
            return view('admin.gerenciar-perfis', compact('usuarios', 'perfis'));
        })->name('admin.usuarios');

        // TESTE DE CONEXÃO FORÇADA (apenas ambiente local)
        if (app()->environment('local')) {
        Route::get('/perfis', function () {
            // Testar conexão forçada para o banco correto
            try {
                $pdo = new PDO(
                    'mysql:host=127.0.0.1;dbname=laravel_beta2',
                    'root',
                    '',
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                $stmt = $pdo->query('SELECT COUNT(*) as count FROM permissions');
                $permissionsCount = $stmt->fetch()['count'];
                
                $stmt = $pdo->query('SELECT COUNT(*) as count FROM profiles');
                $profilesCount = $stmt->fetch()['count'];
                
                $stmt = $pdo->query('SELECT * FROM permissions LIMIT 5');
                $permissions = $stmt->fetchAll(PDO::FETCH_OBJ);
                
                dd([
                    'CONEXAO_DIRETA_PDO' => 'SUCESSO',
                    'BANCO_CONECTADO' => 'laravel_beta2',
                    'PERMISSIONS_COUNT_PDO' => $permissionsCount,
                    'PROFILES_COUNT_PDO' => $profilesCount,
                    'PERMISSIONS_DATA_PDO' => $permissions,
                    'VS_LARAVEL_DB_NAME' => DB::connection()->getDatabaseName(),
                    'VS_LARAVEL_PERMISSIONS_COUNT' => DB::table('permissions')->count(),
                    'ENV_DB_DATABASE' => env('DB_DATABASE', 'NAO_DEFINIDO'),
                ]);
                
            } catch (Exception $e) {
                dd([
                    'ERRO_CONEXAO_PDO' => $e->getMessage(),
                    'LARAVEL_DB_NAME' => DB::connection()->getDatabaseName(),
                    'ENV_DB_DATABASE' => env('DB_DATABASE', 'NAO_DEFINIDO'),
                ]);
            }
        });
        }
        
        // Rota para exibir um perfil específico - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
        Route::get('/perfis/{id}', function ($id) {
            // Buscar perfis, perfil selecionado e permissões diretamente do banco de dados
            $perfis = DB::table('profiles')->get();
            $perfilSelecionado = DB::table('profiles')->where('id', $id)->first();
            
            if (!$perfilSelecionado) {
                return redirect('/perfis')->with('error', 'Perfil não encontrado');
            }
            
            $permissoes = DB::table('permissions')->get();
            $permissoesSelecionadas = DB::table('profile_permissions')
                ->where('profile_id', $id)
                ->pluck('permission_id')
                ->toArray();
                
            return view('admin.perfis', compact('perfis', 'perfilSelecionado', 'permissoes', 'permissoesSelecionadas'));
        });
        }
        
        // Rota para criar um novo perfil - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
        Route::post('/perfis', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255|unique:profiles,name',
                'description' => 'nullable|string',
            ]);
            
            // Inserir diretamente no banco de dados
            DB::table('profiles')->insert([
                'name' => $request->name,
                'description' => $request->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return redirect('/perfis')->with('success', 'Perfil criado com sucesso');
        });
        }
        
        // Rota para atualizar um perfil - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
        Route::put('/perfis/{id}', function (Request $request, $id) {
            $request->validate([
                'name' => 'required|string|max:255|unique:profiles,name,' . $id,
                'description' => 'nullable|string',
            ]);
            
            // Atualizar diretamente no banco de dados
            DB::table('profiles')->where('id', $id)->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => now(),
            ]);
            
            // Sincronizar permissões diretamente no banco de dados
            if ($request->has('permissions')) {
                // Remover permissões existentes
                DB::table('profile_permissions')->where('profile_id', $id)->delete();
                
                // Adicionar novas permissões
                foreach ($request->permissions as $permissionId) {
                    DB::table('profile_permissions')->insert([
                        'profile_id' => $id,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                // Se não enviou permissões, remover todas
                DB::table('profile_permissions')->where('profile_id', $id)->delete();
            }
            
            return redirect('/perfis/' . $id)->with('success', 'Perfil atualizado com sucesso');
        });
        }
        
        // Rota para excluir um perfil - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
        Route::delete('/perfis/{id}', function ($id) {
            // Remover relacionamentos primeiro diretamente do banco de dados
            DB::table('profile_permissions')->where('profile_id', $id)->delete();
            
            // Remover perfil diretamente do banco de dados
            DB::table('profiles')->where('id', $id)->delete();
            
            return redirect('/perfis')->with('success', 'Perfil excluído com sucesso');
        });
        }
        
        // API para gerenciar permissões
        Route::prefix('api')->group(function () {
            // Criar permissão
            Route::post('/permissoes', function (Request $request) {
                $request->validate([
                    'name' => 'required|string|max:255|unique:permissions,name',
                    'code' => 'nullable|string|max:255|unique:permissions,code',
                    'description' => 'nullable|string'
                ]);
                
                $id = DB::table('permissions')->insertGetId([
                    'name' => $request->name,
                    'code' => $request->code ?? strtolower(str_replace(' ', '_', $request->name)),
                    'description' => $request->description,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $permissao = DB::table('permissions')->where('id', $id)->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Permissão criada com sucesso',
                    'data' => $permissao
                ], 201);
            });
            
            // Atualizar permissão
            Route::put('/permissoes/{id}', function (Request $request, $id) {
                $permissao = DB::table('permissions')->where('id', $id)->first();
                
                if (!$permissao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Permissão não encontrada'
                    ], 404);
                }
                
                $request->validate([
                    'name' => 'required|string|max:255|unique:permissions,name,' . $id,
                    'code' => 'nullable|string|max:255|unique:permissions,code,' . $id,
                    'description' => 'nullable|string'
                ]);
                
                DB::table('permissions')->where('id', $id)->update([
                    'name' => $request->name,
                    'code' => $request->code ?? $permissao->code,
                    'description' => $request->description,
                    'updated_at' => now()
                ]);
                
                $permissaoAtualizada = DB::table('permissions')->where('id', $id)->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Permissão atualizada com sucesso',
                    'data' => $permissaoAtualizada
                ]);
            });
            
            // Excluir permissão
            Route::delete('/permissoes/{id}', function ($id) {
                $permissao = DB::table('permissions')->where('id', $id)->first();
                
                if (!$permissao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Permissão não encontrada'
                    ], 404);
                }
                
                // Remover relacionamentos primeiro
                DB::table('profile_permissions')->where('permission_id', $id)->delete();
                
                // Remover permissão
                DB::table('permissions')->where('id', $id)->delete();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Permissão excluída com sucesso'
                ]);
            });
            
            // Obter permissão específica
            Route::get('/permissoes/{id}', function ($id) {
                $permissao = DB::table('permissions')->where('id', $id)->first();
                
                if (!$permissao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Permissão não encontrada'
                    ], 404);
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $permissao
                ]);
            });
        });
    });


    // Rota para listar todos os usuários
    Route::get('/api/usuarios/listar', function() {
        $usuarios = \App\Models\User::with('profile')->get();
        return response()->json([
            'success' => true,
            'data' => $usuarios
        ]);
    })->middleware(['can:gerenciar-usuarios','throttle:60,1']);
    // Rota para obter dados de usuários via API
    Route::get('/api/usuarios/{id}', [UsuariosController::class, 'show'])->middleware(['auth','throttle:60,1']);
    // API para atualizar usuário
    Route::put('/api/usuarios/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            $usuario = \App\Models\User::findOrFail($id);
            
            $validacao = [
                'name' => 'required|string|max:255',
                'profile_id' => 'nullable|exists:profiles,id'
            ];
            
            // Adicionar validação de senha apenas se foi enviada
            if ($request->filled('password')) {
                $validacao['password'] = 'required|string|min:6|confirmed';
            }
            
            $request->validate($validacao);
            
            // Atualizar dados básicos
            $usuario->name = $request->name;
            $usuario->profile_id = $request->profile_id;
            
            // Atualizar senha apenas se foi enviada
            if ($request->filled('password')) {
                $usuario->password = bcrypt($request->password);
            }
            
            $usuario->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => $usuario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ], 500);
        }
    });

    // Rota para atualizar usuário
    Route::post('/atualizar-usuario', [UsuariosController::class, 'update']);

    // Rota para ativar/desativar usuário
    Route::post('/toggle-user-status', [UsuariosController::class, 'toggleStatus']);

    // Rota para atualizar o perfil do usuário
    Route::post('/atualizar-perfil-usuario', function (Request $request) {
        try {
            // Validar dados
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'profile_id' => 'required|exists:profiles,id'
            ]);
            
            // Atualizar o usuário diretamente no banco
            $resultado = DB::table('users')
                ->where('id', $validated['user_id'])
                ->update(['profile_id' => $validated['profile_id']]);
            
            if ($resultado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma alteração foi realizada'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar perfil: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    });

    // APIs para gerenciamento de perfis
    Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
        // Permissões
        Route::get('/permissoes/listar', [PermissoesController::class, 'listar']);
        Route::get('/permissoes', [PermissoesController::class, 'listar']);
        Route::get('/permissoes/{id}', [PermissoesController::class, 'obter']);
        Route::post('/permissoes', [PermissoesController::class, 'store']);
        Route::put('/permissoes/{id}', [PermissoesController::class, 'update']);
        Route::delete('/permissoes/{id}', [PermissoesController::class, 'destroy']);

        // Perfis
        Route::get('/perfis', [App\Http\Controllers\PermissoesController::class, 'listarPerfis']);
        Route::get('/perfis/{id}', [App\Http\Controllers\PermissoesController::class, 'obterPerfil']);
        Route::post('/perfis', function (Request $request) {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:profiles,name',
                'description' => 'nullable|string'
            ]);
            
            $perfil = \App\Models\Profile::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Perfil criado com sucesso',
                'data' => $perfil
            ]);
        });
        Route::put('/perfis/{id}', function ($id) {
            try {
                $perfil = \App\Models\Profile::findOrFail($id);
                
                request()->validate([
                    'name' => 'required|string|max:255|unique:profiles,name,' . $id,
                    'description' => 'nullable|string',
                    'permissions' => 'nullable|array',
                    'permissions.*' => 'integer'
                ]);
                
                $perfil->update([
                    'name' => request('name'),
                    'description' => request('description')
                ]);
                
                if (request()->has('permissions')) {
                    DB::table('profile_permissions')
                        ->where('profile_id', $id)
                        ->delete();
                    
                    $permissions = request('permissions');
                    
                    $data = array_map(function($permissionId) use ($id) {
                        return [
                            'profile_id' => $id,
                            'permission_id' => (int)$permissionId
                        ];
                    }, $permissions);
                    
                    DB::table('profile_permissions')->insert($data);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso',
                    'data' => $perfil->load('permissions')
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar perfil: ' . $e->getMessage()
                ], 500);
            }
        });
        Route::delete('/perfis/{id}', function ($id) {
            $perfil = \App\Models\Profile::findOrFail($id);
            DB::table('users')->where('profile_id', $id)->update(['profile_id' => null]);
            DB::table('profile_permissions')->where('profile_id', $id)->delete();
            $perfil->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Perfil excluído com sucesso'
            ]);
        });
    });


});

Auth::routes();

// Redirecionamentos para URLs sem o prefixo /admin
Route::get('/gerenciar-permissoes', function () {
    return redirect('/permissoes');
});

// Rota de perfis
Route::get('/perfis', function () {
    $perfis = \App\Models\Profile::all();
    $permissoes = \App\Models\Permission::all();
    return view('admin.perfis', compact('perfis', 'permissoes'));
})->middleware(['auth']);

// Exibir um perfil específico (produção) – rota dedicada para evitar conflito com métodos não-GET
Route::get('/perfis/show/{id}', function ($id) {
    $perfis = DB::table('profiles')->get();
    $perfilSelecionado = DB::table('profiles')->where('id', $id)->first();
    if (!$perfilSelecionado) {
        return redirect('/perfis')->with('error', 'Perfil não encontrado');
    }
    $permissoes = DB::table('permissions')->get();
    $permissoesSelecionadas = DB::table('profile_permissions')
        ->where('profile_id', $id)
        ->pluck('permission_id')
        ->toArray();
    return view('admin.perfis', compact('perfis', 'perfilSelecionado', 'permissoes', 'permissoesSelecionadas'));
})->middleware(['auth'])->name('perfis.show');

// Rotas de criação/edição/exclusão de perfis para produção (fora do bloco "local")
Route::post('/perfis', [\App\Http\Controllers\Admin\PerfilController::class, 'store'])
    ->middleware(['auth'])
    ->name('perfis.store');

Route::put('/perfis/{id}', [\App\Http\Controllers\Admin\PerfilController::class, 'update'])
    ->middleware(['auth'])
    ->name('perfis.update');

// Compat: aceitar POST para atualização quando _method spoofing não for aplicado no servidor
Route::post('/perfis/{id}', [\App\Http\Controllers\Admin\PerfilController::class, 'update'])
    ->middleware(['auth'])
    ->name('perfis.update.post');

Route::delete('/perfis/{id}', [\App\Http\Controllers\Admin\PerfilController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('perfis.destroy');

// Licenciamento desativado

// Rotas de administração
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::middleware(['can:gerenciar-permissoes'])->group(function () {
        Route::get('/gerenciar-permissoes', function () {
            $users = \App\Models\User::with('profile')->get();
            $profiles = \App\Models\Profile::all();
            return view('admin.gerenciar-permissoes', compact('users', 'profiles'));
        })->name('gerenciar-permissoes');
    });
    
    // Rotas de conflito removidas
});

// APIs para gerenciamento de permissões
Route::middleware(['auth','throttle:60,1'])->prefix('api')->group(function () {
    // Obter todas as permissões
    Route::get('/permissoes/listar', function() {
        $permissoes = \App\Models\Permission::all();
        return response()->json([
            'success' => true,
            'data' => $permissoes
        ]);
    });
    
    // Obter uma permissão específica
    Route::get('/permissoes/{id}', function($id) {
        $permissao = \App\Models\Permission::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $permissao
        ]);
    });
    
    // Criar uma nova permissão
    Route::post('/permissoes', function(\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'code' => 'nullable|string',
            'description' => 'nullable|string'
        ]);
        
        $permissao = \App\Models\Permission::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Permissão criada com sucesso',
            'data' => $permissao
        ]);
    });
    
    // Atualizar uma permissão
    Route::put('/permissoes/{id}', function(\Illuminate\Http\Request $request, $id) {
        $permissao = \App\Models\Permission::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'code' => 'nullable|string',
            'description' => 'nullable|string'
        ]);
        
        $permissao->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Permissão atualizada com sucesso',
            'data' => $permissao
        ]);
    });
    
    // Excluir uma permissão
    Route::delete('/permissoes/{id}', function($id) {
        $permissao = \App\Models\Permission::findOrFail($id);
        $permissao->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão excluída com sucesso'
        ]);
    });
});

// API para usuários (usado no select do formulário) — protegido
Route::get('/api/usuarios', function() {
    return App\Models\User::select('id', 'name')->get();
})->middleware(['auth','throttle:60,1'])->name('api.usuarios');

// (removida duplicidade) API para obter dados de usuário já definida anteriormente via UsuariosController

/* Rota temporária para corrigir status
Route::get('/corrigir-status-rh', function () {
    // Buscar registros com status "Concluída" (antigo)
    $registros = App\Models\RHProblema::where('status', 'Concluída')->get();
    
    $count = $registros->count();
    echo "Encontrados {$count} registros com status 'Concluída'<br>";
    
    if ($count > 0) {
        // Atualizar todos para "Concluído" (novo)
        foreach ($registros as $registro) {
            echo "Atualizando ID {$registro->id}<br>";
            $registro->status = 'Concluído';
            $registro->save();
        }
        
        echo 'Correção concluída com sucesso!<br>';
    } else {
        echo 'Nenhum registro precisava ser corrigido.<br>';
    }
    
    // Verificar os status existentes
    $status = DB::table('rh_problemas')
        ->select('status')
        ->distinct()
        ->orderBy('status')
        ->get()
        ->pluck('status');
        
    echo 'Status disponíveis no banco:<br>';
    foreach ($status as $st) {
        echo " - {$st}<br>";
    }
    
    return "Processo concluído!";
})->middleware('auth');*/

// Rota para atualizar diretamente o status
/*Route::get('/atualizar-status/{id}/{status}', function ($id, $status) {
    try {
        // Verificar se o status é válido
        if (!in_array($status, ['Pendente', 'Em andamento', 'Concluído', 'No prazo'])) {
            return "Status inválido. Use: Pendente, Em andamento, Concluído ou No prazo";
        }
        
        // Buscar o registro
        $problema = \App\Models\RHProblema::findOrFail($id);
        $statusAntigo = $problema->status;
        
        // Atualizar o status
        $problema->status = $status;
        $resultado = $problema->save();
        
        if ($resultado) {
            // Verificar se a atualização foi realmente aplicada
            $problemaAtualizado = \App\Models\RHProblema::findOrFail($id);
            
            if ($problemaAtualizado->status === $status) {
                return "Status atualizado com sucesso de '{$statusAntigo}' para '{$status}'!";
            } else {
                return "Falha na verificação. Status no banco: '{$problemaAtualizado->status}'";
            }
        } else {
            return "Erro ao salvar: operação de save() retornou false";
        }
    } catch (\Exception $e) {
        return "Erro: " . $e->getMessage();
    }
})->middleware('auth');*/

// Rota de emergência para corrigir o registro ID 70
Route::get('/corrigir-registro-70', function() {
    try {
        // Verificar status atual
        $statusAtual = DB::table('rh_problemas')->where('id', 70)->value('status');
        echo "Status atual do registro #70: " . $statusAtual . "<br>";
        
        // Forçar atualização direta no banco
        $atualizado = DB::table('rh_problemas')
            ->where('id', 70)
            ->update(['status' => 'Pendente']);
        
        echo "Atualização forçada: " . ($atualizado ? "Sim" : "Não") . "<br>";
        
        // Verificar status após atualização
        $novoStatus = DB::table('rh_problemas')->where('id', 70)->value('status');
        echo "Novo status do registro #70: " . $novoStatus . "<br>";
        
        // Limpar cache do registro
        Cache::forget('rh_problema_70');
        echo "Cache limpo para o registro #70";
    } catch (\Exception $e) {
        return "Erro: " . $e->getMessage();
    }
})->middleware(['auth']);



// Rotas de Debug para depuração
Route::get('/debug/enable-sql-log', function() {
    \DB::enableQueryLog();
    \Log::info('Logging SQL ativado via AJAX');
    return response()->json(['status' => 'success', 'message' => 'SQL logging enabled']);
})->middleware(['auth','throttle:30,1']);

// Rota de teste específica para diagnóstico de problemas com datas
Route::get('/debug/teste-data/{data?}', function($data = null) {
    // Se não for fornecida uma data, usar um valor padrão
    if (!$data) {
        $data = "31/03/2025 11:54";
    }
    
    \Log::info("Iniciando teste de data com: " . $data);
    
    // Ativar log de consultas
    \DB::enableQueryLog();
    
    // Array para armazenar os resultados dos testes
    $resultados = [];
    
    try {
        // 1. Teste direto com Carbon
        try {
            $carbon = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $data);
            $resultados['carbon_direto'] = [
                'status' => 'sucesso',
                'resultado' => $carbon->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            $resultados['carbon_direto'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // 2. Teste com regex e construção manual
        try {
            // Sanitizar a data
            $dataSanitizada = preg_replace('/[^\d\/: ]/', '', trim($data));
            
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})$/', $dataSanitizada, $matches)) {
                $dia = (int)$matches[1];
                $mes = (int)$matches[2];
                $ano = (int)$matches[3];
                $hora = (int)$matches[4];
                $minuto = (int)$matches[5];
                
                // Formatação direta para SQL
                $dataSQL = sprintf('%04d-%02d-%02d %02d:%02d:00', $ano, $mes, $dia, $hora, $minuto);
                
                $resultados['regex_manual'] = [
                    'status' => 'sucesso',
                    'dados_extraidos' => [
                        'dia' => $dia,
                        'mes' => $mes,
                        'ano' => $ano,
                        'hora' => $hora,
                        'minuto' => $minuto
                    ],
                    'sql_formatado' => $dataSQL
                ];
                
                // Verificar se o Carbon aceita esta string
                try {
                    $carbonTeste = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dataSQL);
                    $resultados['regex_manual']['verificacao_carbon'] = [
                        'status' => 'sucesso',
                        'resultado' => $carbonTeste->format('Y-m-d H:i:s')
                    ];
                } catch (\Exception $e) {
                    $resultados['regex_manual']['verificacao_carbon'] = [
                        'status' => 'erro',
                        'mensagem' => $e->getMessage()
                    ];
                }
            } else {
                $resultados['regex_manual'] = [
                    'status' => 'erro',
                    'mensagem' => 'A data não corresponde ao formato esperado',
                    'data_sanitizada' => $dataSanitizada
                ];
            }
        } catch (\Exception $e) {
            $resultados['regex_manual'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // 3. Teste com o banco de dados
        try {
            // Criar um problema de teste
            $problema = new \App\Models\RHProblema();
            $problema->descricao = 'Teste de data - ' . now();
            $problema->status = 'Pendente';
            $problema->prioridade = 'media';
            $problema->id_usuario = 1;
            
            // Definir a data diretamente como string no formato SQL
            $dataSQL = $resultados['regex_manual']['sql_formatado'] ?? null;
            
            if ($dataSQL) {
                $problema->prazo_entrega = $dataSQL;
                $resultado = $problema->save();
                
                $resultados['teste_bd'] = [
                    'status' => $resultado ? 'sucesso' : 'erro',
                    'id_problema' => $problema->id,
                    'data_salva' => $problema->prazo_entrega
                ];
                
                // Limpar o teste (excluir o registro)
                if ($resultado) {
                    $problema->delete();
                }
            } else {
                $resultados['teste_bd'] = [
                    'status' => 'pulado',
                    'motivo' => 'Não foi possível obter uma data SQL válida'
                ];
            }
        } catch (\Exception $e) {
            $resultados['teste_bd'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // Log das consultas SQL
        $resultados['consultas_sql'] = \DB::getQueryLog();
        
        return response()->json([
            'status' => 'sucesso',
            'data_original' => $data,
            'resultados' => $resultados
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'erro',
            'mensagem' => $e->getMessage(),
            'data_original' => $data,
            'resultados' => $resultados ?? []
        ], 500);
    }
})->middleware(['auth','throttle:30,1']);



// APIs para gerenciamento de perfis
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Obter todos os perfis
    Route::get('/perfis/listar', function() {
        $perfis = \App\Models\Profile::all();
        return response()->json([
            'success' => true,
            'data' => $perfis
        ]);
    });
    
    // Obter um perfil específico
    Route::get('/perfis/{id}', function($id) {
        $perfil = \App\Models\Profile::with('permissions')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $perfil
        ]);
    });
});

// =============================================
// RELATÓRIOS DE OBRAS
// =============================================
Route::middleware(['auth'])->prefix('relatorios')->name('relatorios.')->group(function () {
    Route::get('/curva-s-fisica',    [App\Http\Controllers\RelatorioObraController::class, 'curvaSFisica'])->middleware('can:relatorio-curva-s-fisica')->name('curva-s-fisica');
    Route::get('/curva-s-financeira',[App\Http\Controllers\RelatorioObraController::class, 'curvaSFinanceira'])->middleware('can:relatorio-curva-s-financeira')->name('curva-s-financeira');
    Route::get('/cronograma',        [App\Http\Controllers\RelatorioObraController::class, 'cronograma'])->middleware('can:relatorio-cronograma')->name('cronograma');
    Route::get('/custos',            [App\Http\Controllers\RelatorioObraController::class, 'custos'])->middleware('can:relatorio-custos')->name('custos');
    Route::get('/mao-de-obra',       [App\Http\Controllers\RelatorioObraController::class, 'maoDeObra'])->middleware('can:relatorio-mao-de-obra')->name('mao-de-obra');
    Route::get('/suprimentos',       [App\Http\Controllers\RelatorioObraController::class, 'suprimentos'])->middleware('can:relatorio-suprimentos')->name('suprimentos');
    Route::get('/producao',          [App\Http\Controllers\RelatorioObraController::class, 'producao'])->middleware('can:relatorio-producao')->name('producao');
    Route::get('/riscos',            [App\Http\Controllers\RelatorioObraController::class, 'riscos'])->middleware('can:relatorio-riscos')->name('riscos');
    Route::get('/qualidade',         [App\Http\Controllers\RelatorioObraController::class, 'qualidade'])->middleware('can:relatorio-qualidade')->name('qualidade');
});
