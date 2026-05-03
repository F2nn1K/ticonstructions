<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ERPPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // LIMPAR PERMISSÕES ANTIGAS (mantém apenas vínculos de perfil admin)
        // =====================================================================
        DB::table('profile_permissions')->delete();
        DB::table('permissions')->delete();
        DB::table('permission_groups')->delete();

        // =====================================================================
        // GRUPOS DE PERMISSÃO
        // =====================================================================
        $grupos = [
            ['name' => 'Administração',             'description' => 'Permissões administrativas do sistema'],
            ['name' => 'Dashboard Executivo',        'description' => 'Visão geral e KPIs da obra'],
            ['name' => 'Cronograma de Obra',         'description' => 'Planejamento e controle de cronograma'],
            ['name' => 'Diário de Obra',             'description' => 'Registro diário e semanal do andamento da obra'],
            ['name' => 'Controle de Gastos',         'description' => 'Custos e financeiro da obra'],
            ['name' => 'Funcionários',               'description' => 'RH de obra e apontamento de horas'],
            ['name' => 'Suprimentos',                'description' => 'Materiais, compras e estoque'],
            ['name' => 'Produção',                   'description' => 'Avanço físico e medições'],
            ['name' => 'Riscos e Ocorrências',       'description' => 'Registro e gestão de riscos'],
            ['name' => 'Qualidade',                  'description' => 'Checklists, inspeções e não conformidades'],
            ['name' => 'Relatórios',                 'description' => 'Relatórios gerenciais e exportações'],
        ];

        foreach ($grupos as $grupo) {
            DB::table('permission_groups')->insert(array_merge($grupo, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // IDs dos grupos
        $g = DB::table('permission_groups')->pluck('id', 'name');

        // =====================================================================
        // PERMISSÕES
        // =====================================================================
        $permissoes = [

            // -----------------------------------------------------------------
            // ADMINISTRAÇÃO
            // -----------------------------------------------------------------
            ['name' => 'Administrador',          'code' => 'admin',                  'description' => 'Acesso total ao sistema',                      'group_id' => $g['Administração']],
            ['name' => 'Gerenciar Usuários',     'code' => 'gerenciar-usuarios',     'description' => 'Criar, editar e desativar usuários',            'group_id' => $g['Administração']],
            ['name' => 'Gerenciar Permissões',   'code' => 'gerenciar-permissoes',   'description' => 'Gerenciar perfis e permissões',                 'group_id' => $g['Administração']],

            // -----------------------------------------------------------------
            // DASHBOARD EXECUTIVO
            // -----------------------------------------------------------------
            ['name' => 'Ver Dashboard',          'code' => 'dashboard-ver',          'description' => 'Visualizar dashboard executivo com KPIs',       'group_id' => $g['Dashboard Executivo']],
            ['name' => 'Dashboard Financeiro',   'code' => 'dashboard-financeiro',   'description' => 'Ver indicadores financeiros no dashboard',       'group_id' => $g['Dashboard Executivo']],
            ['name' => 'Dashboard Cronograma',   'code' => 'dashboard-cronograma',   'description' => 'Ver indicadores de cronograma no dashboard',     'group_id' => $g['Dashboard Executivo']],

            // -----------------------------------------------------------------
            // DIÁRIO DE OBRA
            // -----------------------------------------------------------------
            ['name' => 'Diário Ver',    'code' => 'diario-ver',    'description' => 'Visualizar registros do diário de obra',  'group_id' => $g['Diário de Obra']],
            ['name' => 'Diário Criar',  'code' => 'diario-criar',  'description' => 'Criar novos registros no diário de obra', 'group_id' => $g['Diário de Obra']],
            ['name' => 'Diário Editar', 'code' => 'diario-editar', 'description' => 'Editar registros do diário de obra',      'group_id' => $g['Diário de Obra']],
            ['name' => 'Diário Excluir','code' => 'diario-excluir','description' => 'Excluir registros do diário de obra',    'group_id' => $g['Diário de Obra']],

            // -----------------------------------------------------------------
            // CRONOGRAMA DE OBRA
            // -----------------------------------------------------------------
            ['name' => 'Cronograma Ver',         'code' => 'cronograma-ver',         'description' => 'Visualizar cronograma e atividades',            'group_id' => $g['Cronograma de Obra']],
            ['name' => 'Cronograma Criar',       'code' => 'cronograma-criar',       'description' => 'Criar obras, etapas e atividades',              'group_id' => $g['Cronograma de Obra']],
            ['name' => 'Cronograma Editar',      'code' => 'cronograma-editar',      'description' => 'Editar datas, responsáveis e progresso',        'group_id' => $g['Cronograma de Obra']],
            ['name' => 'Cronograma Excluir',     'code' => 'cronograma-excluir',     'description' => 'Excluir atividades e etapas',                   'group_id' => $g['Cronograma de Obra']],
            ['name' => 'Cronograma Aprovar',     'code' => 'cronograma-aprovar',     'description' => 'Aprovar baseline e revisões do cronograma',     'group_id' => $g['Cronograma de Obra']],
            ['name' => 'Cronograma Exportar',    'code' => 'cronograma-exportar',    'description' => 'Exportar cronograma em PDF/Excel',              'group_id' => $g['Cronograma de Obra']],
            ['name' => 'Ocorrências Cronograma Ver',    'code' => 'ocorrencias-cronograma-ver',    'description' => 'Ver ocorrências que impactaram o cronograma', 'group_id' => $g['Cronograma de Obra']],
            ['name' => 'Ocorrências Cronograma Criar',  'code' => 'ocorrencias-cronograma-criar',  'description' => 'Registrar ocorrências no cronograma',         'group_id' => $g['Cronograma de Obra']],
            ['name' => 'Ocorrências Cronograma Editar', 'code' => 'ocorrencias-cronograma-editar', 'description' => 'Editar ocorrências do cronograma',            'group_id' => $g['Cronograma de Obra']],

            // -----------------------------------------------------------------
            // CONTROLE DE GASTOS
            // -----------------------------------------------------------------
            ['name' => 'Gastos Ver',             'code' => 'gastos-ver',             'description' => 'Visualizar custos e orçamentos da obra',        'group_id' => $g['Controle de Gastos']],
            ['name' => 'Gastos Criar',           'code' => 'gastos-criar',           'description' => 'Lançar novos custos e despesas',                'group_id' => $g['Controle de Gastos']],
            ['name' => 'Gastos Editar',          'code' => 'gastos-editar',          'description' => 'Editar custos lançados',                        'group_id' => $g['Controle de Gastos']],
            ['name' => 'Gastos Excluir',         'code' => 'gastos-excluir',         'description' => 'Excluir lançamentos de custo',                  'group_id' => $g['Controle de Gastos']],
            ['name' => 'Gastos Aprovar',         'code' => 'gastos-aprovar',         'description' => 'Aprovar orçamentos e revisões de custo',        'group_id' => $g['Controle de Gastos']],
            ['name' => 'Fluxo de Caixa Ver',     'code' => 'fluxo-caixa-ver',        'description' => 'Visualizar fluxo de caixa da obra',             'group_id' => $g['Controle de Gastos']],
            ['name' => 'Fluxo de Caixa Editar',  'code' => 'fluxo-caixa-editar',     'description' => 'Lançar e editar entradas/saídas no fluxo',      'group_id' => $g['Controle de Gastos']],

            // -----------------------------------------------------------------
            // FUNCIONÁRIOS (RH DE OBRA)
            // -----------------------------------------------------------------
            ['name' => 'Funcionários Ver',       'code' => 'funcionarios-ver',       'description' => 'Visualizar cadastro de funcionários',           'group_id' => $g['Funcionários']],
            ['name' => 'Funcionários Criar',     'code' => 'funcionarios-criar',     'description' => 'Cadastrar novos funcionários',                  'group_id' => $g['Funcionários']],
            ['name' => 'Funcionários Editar',    'code' => 'funcionarios-editar',    'description' => 'Editar dados de funcionários',                  'group_id' => $g['Funcionários']],
            ['name' => 'Funcionários Excluir',   'code' => 'funcionarios-excluir',   'description' => 'Desativar ou excluir funcionários',             'group_id' => $g['Funcionários']],
            ['name' => 'Apontamento Ver',        'code' => 'apontamento-ver',        'description' => 'Visualizar apontamento diário de horas',        'group_id' => $g['Funcionários']],
            ['name' => 'Apontamento Criar',      'code' => 'apontamento-criar',      'description' => 'Lançar apontamento de horas trabalhadas',       'group_id' => $g['Funcionários']],
            ['name' => 'Apontamento Editar',     'code' => 'apontamento-editar',     'description' => 'Editar apontamentos lançados',                  'group_id' => $g['Funcionários']],
            ['name' => 'Apontamento Aprovar',    'code' => 'apontamento-aprovar',    'description' => 'Aprovar apontamentos de horas',                 'group_id' => $g['Funcionários']],

            // -----------------------------------------------------------------
            // SUPRIMENTOS
            // -----------------------------------------------------------------
            ['name' => 'Materiais Ver',          'code' => 'materiais-ver',          'description' => 'Visualizar catálogo de materiais',              'group_id' => $g['Suprimentos']],
            ['name' => 'Materiais Criar',        'code' => 'materiais-criar',        'description' => 'Cadastrar novos materiais',                     'group_id' => $g['Suprimentos']],
            ['name' => 'Materiais Editar',       'code' => 'materiais-editar',       'description' => 'Editar materiais cadastrados',                  'group_id' => $g['Suprimentos']],
            ['name' => 'Solicitação Compra Ver',    'code' => 'solicitacao-compra-ver',    'description' => 'Ver solicitações de compra',              'group_id' => $g['Suprimentos']],
            ['name' => 'Solicitação Compra Criar',  'code' => 'solicitacao-compra-criar',  'description' => 'Criar solicitações de compra',            'group_id' => $g['Suprimentos']],
            ['name' => 'Solicitação Compra Aprovar','code' => 'solicitacao-compra-aprovar','description' => 'Aprovar solicitações de compra',           'group_id' => $g['Suprimentos']],
            ['name' => 'Cotação Ver',            'code' => 'cotacao-ver',            'description' => 'Visualizar cotações de fornecedores',           'group_id' => $g['Suprimentos']],
            ['name' => 'Cotação Criar',          'code' => 'cotacao-criar',          'description' => 'Criar e enviar cotações',                       'group_id' => $g['Suprimentos']],
            ['name' => 'Cotação Aprovar',        'code' => 'cotacao-aprovar',        'description' => 'Aprovar melhor cotação',                        'group_id' => $g['Suprimentos']],
            ['name' => 'Ordem Compra Ver',       'code' => 'ordem-compra-ver',       'description' => 'Visualizar ordens de compra',                   'group_id' => $g['Suprimentos']],
            ['name' => 'Ordem Compra Criar',     'code' => 'ordem-compra-criar',     'description' => 'Criar ordens de compra',                        'group_id' => $g['Suprimentos']],
            ['name' => 'Ordem Compra Aprovar',   'code' => 'ordem-compra-aprovar',   'description' => 'Aprovar ordens de compra',                      'group_id' => $g['Suprimentos']],
            ['name' => 'Estoque Ver',            'code' => 'estoque-ver',            'description' => 'Visualizar saldo de estoque',                   'group_id' => $g['Suprimentos']],
            ['name' => 'Estoque Entrada',        'code' => 'estoque-entrada',        'description' => 'Lançar entrada de material no estoque',         'group_id' => $g['Suprimentos']],
            ['name' => 'Estoque Saída',          'code' => 'estoque-saida',          'description' => 'Lançar saída de material do estoque',           'group_id' => $g['Suprimentos']],
            ['name' => 'Fornecedores Ver',       'code' => 'fornecedores-ver',       'description' => 'Visualizar cadastro de fornecedores',           'group_id' => $g['Suprimentos']],
            ['name' => 'Fornecedores Criar',     'code' => 'fornecedores-criar',     'description' => 'Cadastrar novos fornecedores',                  'group_id' => $g['Suprimentos']],
            ['name' => 'Fornecedores Editar',    'code' => 'fornecedores-editar',    'description' => 'Editar fornecedores cadastrados',               'group_id' => $g['Suprimentos']],

            // -----------------------------------------------------------------
            // PRODUÇÃO E AVANÇO FÍSICO
            // -----------------------------------------------------------------
            ['name' => 'Produção Ver',           'code' => 'producao-ver',           'description' => 'Visualizar medições e avanço físico',           'group_id' => $g['Produção']],
            ['name' => 'Produção Lançar',        'code' => 'producao-lancar',        'description' => 'Lançar medições de produção',                   'group_id' => $g['Produção']],
            ['name' => 'Produção Editar',        'code' => 'producao-editar',        'description' => 'Editar medições lançadas',                      'group_id' => $g['Produção']],
            ['name' => 'Produção Aprovar',       'code' => 'producao-aprovar',       'description' => 'Aprovar medições de produção',                  'group_id' => $g['Produção']],

            // -----------------------------------------------------------------
            // RISCOS E OCORRÊNCIAS
            // -----------------------------------------------------------------
            ['name' => 'Riscos Ver',             'code' => 'riscos-ver',             'description' => 'Visualizar matriz de riscos',                   'group_id' => $g['Riscos e Ocorrências']],
            ['name' => 'Riscos Criar',           'code' => 'riscos-criar',           'description' => 'Registrar novos riscos',                        'group_id' => $g['Riscos e Ocorrências']],
            ['name' => 'Riscos Editar',          'code' => 'riscos-editar',          'description' => 'Editar riscos e planos de ação',                'group_id' => $g['Riscos e Ocorrências']],
            ['name' => 'Riscos Excluir',         'code' => 'riscos-excluir',         'description' => 'Excluir registros de risco',                    'group_id' => $g['Riscos e Ocorrências']],
            ['name' => 'Ocorrências Ver',        'code' => 'ocorrencias-ver',        'description' => 'Visualizar ocorrências registradas',            'group_id' => $g['Riscos e Ocorrências']],
            ['name' => 'Ocorrências Criar',      'code' => 'ocorrencias-criar',      'description' => 'Registrar novas ocorrências',                   'group_id' => $g['Riscos e Ocorrências']],
            ['name' => 'Ocorrências Editar',     'code' => 'ocorrencias-editar',     'description' => 'Editar ocorrências registradas',                'group_id' => $g['Riscos e Ocorrências']],
            ['name' => 'Ocorrências Excluir',    'code' => 'ocorrencias-excluir',    'description' => 'Excluir ocorrências',                           'group_id' => $g['Riscos e Ocorrências']],

            // -----------------------------------------------------------------
            // QUALIDADE
            // -----------------------------------------------------------------
            ['name' => 'Qualidade Ver',          'code' => 'qualidade-ver',          'description' => 'Visualizar checklists e inspeções',             'group_id' => $g['Qualidade']],
            ['name' => 'Qualidade Checklist',    'code' => 'qualidade-checklist',    'description' => 'Preencher e gerenciar checklists por etapa',    'group_id' => $g['Qualidade']],
            ['name' => 'Qualidade Inspeção',     'code' => 'qualidade-inspecao',     'description' => 'Registrar inspeções de qualidade',              'group_id' => $g['Qualidade']],
            ['name' => 'Qualidade Não Conformidade', 'code' => 'qualidade-nao-conformidade', 'description' => 'Registrar e tratar não conformidades', 'group_id' => $g['Qualidade']],
            ['name' => 'Qualidade Aprovar',      'code' => 'qualidade-aprovar',      'description' => 'Aprovar inspeções e fechar não conformidades',  'group_id' => $g['Qualidade']],

            // -----------------------------------------------------------------
            // RELATÓRIOS
            // -----------------------------------------------------------------
            ['name' => 'Relatorio Curva S Física',      'code' => 'relatorio-curva-s-fisica',      'description' => 'Curva S de avanço físico (planejado vs realizado)', 'group_id' => $g['Relatórios']],
            ['name' => 'Relatorio Curva S Financeira',  'code' => 'relatorio-curva-s-financeira',  'description' => 'Curva S financeira da obra',                         'group_id' => $g['Relatórios']],
            ['name' => 'Relatorio Cronograma',          'code' => 'relatorio-cronograma',          'description' => 'Relatório detalhado de cronograma',                  'group_id' => $g['Relatórios']],
            ['name' => 'Relatorio Custos',              'code' => 'relatorio-custos',              'description' => 'Relatório de custos por etapa/categoria',            'group_id' => $g['Relatórios']],
            ['name' => 'Relatorio Mão de Obra',         'code' => 'relatorio-mao-de-obra',         'description' => 'Relatório de custo e produtividade de MO',          'group_id' => $g['Relatórios']],
            ['name' => 'Relatorio Suprimentos',         'code' => 'relatorio-suprimentos',         'description' => 'Relatório de consumo e estoque',                     'group_id' => $g['Relatórios']],
            ['name' => 'Relatorio Produção',            'code' => 'relatorio-producao',            'description' => 'Relatório de avanço físico e produtividade',         'group_id' => $g['Relatórios']],
            ['name' => 'Relatorio Riscos',              'code' => 'relatorio-riscos',              'description' => 'Relatório de riscos e impactos acumulados',           'group_id' => $g['Relatórios']],
            ['name' => 'Relatorio Qualidade',           'code' => 'relatorio-qualidade',           'description' => 'Relatório de qualidade e não conformidades',          'group_id' => $g['Relatórios']],
            ['name' => 'Relatorio Exportar',            'code' => 'relatorio-exportar',            'description' => 'Exportar relatórios em PDF/Excel',                   'group_id' => $g['Relatórios']],
        ];

        foreach ($permissoes as $p) {
            DB::table('permissions')->insert(array_merge($p, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // =====================================================================
        // PERFIL ADMINISTRADOR — concede todas as permissões
        // =====================================================================
        $perfilAdmin = DB::table('profiles')->where('name', 'like', '%dmin%')->first();

        if ($perfilAdmin) {
            $todasPermissoes = DB::table('permissions')->pluck('id');
            foreach ($todasPermissoes as $permId) {
                DB::table('profile_permissions')->insert([
                    'profile_id'    => $perfilAdmin->id,
                    'permission_id' => $permId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }
}
