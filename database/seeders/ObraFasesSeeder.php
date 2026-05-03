<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed completo para obras de até 6 andares.
 *
 * Estrutura:
 *  • fases_catalogo        — 16 fases com % acumulado de obra
 *  • fases_catalogo_tarefas — checklist detalhado por fase / grupo
 *  • categorias_material    — 34 categorias de custo
 *  • subcategorias_material — itens dentro de cada categoria
 */
class ObraFasesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFases();
        $this->seedCategorias();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FASES (16) — % acumulado e checklist completo
    // ──────────────────────────────────────────────────────────────────────────
    private function seedFases(): void
    {
        $fases = [

            // ── 1 ─────────────────────────────────────────────────────────────
            [
                'ordem' => 1,
                'nome'  => 'Pré-obra e Projetos',
                'icone' => 'fas fa-drafting-compass',
                'percentual_inicio' => 0,
                'percentual_fim'    => 5,
                'descricao' => 'Estudos técnicos, projetos completos, aprovações e planejamento.',
                'tarefas' => [
                    ['grupo' => 'Estudos Técnicos', 'itens' => [
                        'Levantamento topográfico planialtimétrico',
                        'Sondagem SPT do solo',
                        'Análise de capacidade de carga',
                        'Estudo de nível de lençol freático',
                    ]],
                    ['grupo' => 'Projetos Completos', 'itens' => [
                        'Projeto arquitetônico legal + executivo',
                        'Projeto estrutural (concreto armado)',
                        'Projeto de fundações (dimensionamento)',
                        'Projeto elétrico (cargas e distribuição)',
                        'Projeto hidrossanitário (água/esgoto/drenagem)',
                        'Projeto SPDA (para-raios)',
                        'Projeto de incêndio (quando exigido)',
                    ]],
                    ['grupo' => 'Aprovações e Licenças', 'itens' => [
                        'Aprovação na prefeitura (alvará)',
                        'Corpo de bombeiros (AVCB/PCI se aplicável)',
                        'ART/RRT de todos os projetos',
                        'Registro de responsabilidade técnica da obra',
                    ]],
                    ['grupo' => 'Planejamento', 'itens' => [
                        'Cronograma físico-financeiro',
                        'Curva de desembolso',
                        'Orçamento detalhado por etapa',
                        'Definição do método construtivo',
                    ]],
                ],
            ],

            // ── 2 ─────────────────────────────────────────────────────────────
            [
                'ordem' => 2,
                'nome'  => 'Implantação do Canteiro',
                'icone' => 'fas fa-hard-hat',
                'percentual_inicio' => 5,
                'percentual_fim'    => 10,
                'descricao' => 'Infraestrutura inicial, tapumes, instalações provisórias e locação da obra.',
                'tarefas' => [
                    ['grupo' => 'Infraestrutura Inicial', 'itens' => [
                        'Limpeza total do terreno',
                        'Demolições (se houver)',
                        'Retirada de entulho',
                    ]],
                    ['grupo' => 'Organização Física', 'itens' => [
                        'Tapume perimetral',
                        'Portão de obra',
                        'Placa da obra (ART + responsável técnico)',
                        'Acessos de caminhão',
                    ]],
                    ['grupo' => 'Instalações Provisórias', 'itens' => [
                        'Barracão de obra',
                        'Almoxarifado',
                        'Banheiros químicos',
                        'Refeitório',
                        'Escritório técnico',
                    ]],
                    ['grupo' => 'Infraestrutura Provisória', 'itens' => [
                        'Ligação provisória de energia',
                        'Ligação de água',
                        'Iluminação de canteiro',
                    ]],
                    ['grupo' => 'Locação da Obra', 'itens' => [
                        'Gabarito',
                        'Eixos estruturais',
                        'Marcação de fundações',
                    ]],
                ],
            ],

            // ── 3 ─────────────────────────────────────────────────────────────
            [
                'ordem' => 3,
                'nome'  => 'Terraplanagem',
                'icone' => 'fas fa-truck',
                'percentual_inicio' => 10,
                'percentual_fim'    => 15,
                'descricao' => 'Movimento de terra, compactação e drenagem inicial.',
                'tarefas' => [
                    ['grupo' => 'Movimento de Terra', 'itens' => [
                        'Corte de solo',
                        'Aterro controlado',
                        'Transporte de material',
                    ]],
                    ['grupo' => 'Compactação', 'itens' => [
                        'Compactação por camadas',
                        'Ensaios de densidade',
                    ]],
                    ['grupo' => 'Drenagem Inicial', 'itens' => [
                        'Valetas provisórias',
                        'Controle de águas pluviais',
                    ]],
                ],
            ],

            // ── 4 ─────────────────────────────────────────────────────────────
            [
                'ordem' => 4,
                'nome'  => 'Fundações',
                'icone' => 'fas fa-building',
                'percentual_inicio' => 15,
                'percentual_fim'    => 30,
                'descricao' => 'Estacas, blocos de fundação, vigas baldrame, impermeabilização e reaterro.',
                'tarefas' => [
                    ['grupo' => 'Execução de Estacas', 'itens' => [
                        'Estaca hélice contínua ou escavada',
                        'Concretagem in loco',
                        'Controle de profundidade',
                    ]],
                    ['grupo' => 'Blocos de Fundação', 'itens' => [
                        'Escavação',
                        'Armação de aço',
                        'Concretagem dos blocos',
                    ]],
                    ['grupo' => 'Vigas Baldrame', 'itens' => [
                        'Formas de baldrame',
                        'Ferragem de baldrame',
                        'Concretagem de baldrame',
                    ]],
                    ['grupo' => 'Impermeabilização', 'itens' => [
                        'Pintura asfáltica',
                        'Mantas impermeáveis',
                        'Proteção mecânica',
                    ]],
                    ['grupo' => 'Reaterro', 'itens' => [
                        'Solo compactado em camadas',
                    ]],
                ],
            ],

            // ── 5 ─────────────────────────────────────────────────────────────
            [
                'ordem' => 5,
                'nome'  => 'Estrutura',
                'icone' => 'fas fa-layer-group',
                'percentual_inicio' => 30,
                'percentual_fim'    => 65,
                'descricao' => 'Ciclo executivo por pavimento: formas, armaduras, concretagem, cura e desforma. ⚠️ Fase mais pesada — consome até 65% do orçamento.',
                'tarefas' => [
                    ['grupo' => 'Ciclo por Pavimento (repetir em cada andar)', 'itens' => [
                        'Montagem de escoramento',
                        'Montagem de formas',
                        'Posicionamento de armaduras',
                        'Ferragem de pilares (ferros verticais)',
                        'Ferragem de vigas (longarinas)',
                        'Ferragem de lajes (malha superior e inferior)',
                        'Passagem de eletrodutos embutidos',
                        'Passagens hidráulicas embutidas',
                        'Concreto usinado',
                        'Lançamento com bomba de concreto',
                        'Adensamento (vibrador)',
                        'Cura — molhagem controlada',
                        'Cura — proteção térmica',
                        'Retirada de formas',
                        'Reaproveitamento de escoras',
                    ]],
                    ['grupo' => '1º Pavimento  →  35% acumulado', 'itens' => [
                        'Estrutura completa do 1º pavimento',
                    ]],
                    ['grupo' => '2º Pavimento  →  40% acumulado', 'itens' => [
                        'Estrutura completa do 2º pavimento',
                    ]],
                    ['grupo' => '3º Pavimento  →  45% acumulado', 'itens' => [
                        'Estrutura completa do 3º pavimento',
                    ]],
                    ['grupo' => '4º Pavimento  →  50% acumulado', 'itens' => [
                        'Estrutura completa do 4º pavimento',
                    ]],
                    ['grupo' => '5º Pavimento  →  57% acumulado', 'itens' => [
                        'Estrutura completa do 5º pavimento',
                    ]],
                    ['grupo' => '6º Pavimento / Laje de Cobertura  →  65% acumulado', 'itens' => [
                        'Estrutura completa do 6º pavimento (cobertura)',
                    ]],
                ],
            ],

            // ── 6 ─────────────────────────────────────────────────────────────
            [
                'ordem' => 6,
                'nome'  => 'Alvenaria de Vedação',
                'icone' => 'fas fa-th-large',
                'percentual_inicio' => 65,
                'percentual_fim'    => 75,
                'descricao' => 'Paredes internas e externas, vergas, cintas e integração com instalações.',
                'tarefas' => [
                    ['grupo' => 'Marcação', 'itens' => [
                        'Eixos internos',
                        'Layout de paredes',
                    ]],
                    ['grupo' => 'Elevação', 'itens' => [
                        'Blocos cerâmicos ou de concreto',
                        'Argamassa de assentamento',
                        'Paredes internas por pavimento',
                        'Paredes externas por pavimento',
                        'Fechamentos e divisórias',
                    ]],
                    ['grupo' => 'Elementos Estruturais Auxiliares', 'itens' => [
                        'Vergas e contravergas',
                        'Cintas de amarração',
                    ]],
                    ['grupo' => 'Integração com Instalações', 'itens' => [
                        'Passagem de conduítes',
                        'Caixas elétricas embutidas',
                    ]],
                ],
            ],

            // ── 7 ─────────────────────────────────────────────────────────────
            [
                'ordem' => 7,
                'nome'  => 'Instalações Embutidas',
                'icone' => 'fas fa-bolt',
                'percentual_inicio' => 75,
                'percentual_fim'    => 80,
                'descricao' => 'Elétrica, hidráulica, gás e infra de ar-condicionado embutidos na alvenaria.',
                'tarefas' => [
                    ['grupo' => 'Elétrica', 'itens' => [
                        'Eletrodutos',
                        'Caixas 4×2 / 4×4',
                        'Quadros por pavimento',
                    ]],
                    ['grupo' => 'Hidráulica', 'itens' => [
                        'Água fria',
                        'Esgoto primário e secundário',
                        'Ventilação sanitária',
                    ]],
                    ['grupo' => 'Gás (se houver)', 'itens' => [
                        'Tubulação dedicada de gás',
                    ]],
                    ['grupo' => 'Infra Ar-Condicionado', 'itens' => [
                        'Dreno de ar-condicionado',
                        'Tubulação frigorígena',
                    ]],
                ],
            ],

            // ── 8 ─────────────────────────────────────────────────────────────
            [
                'ordem' => 8,
                'nome'  => 'Reboco e Contrapiso',
                'icone' => 'fas fa-border-all',
                'percentual_inicio' => 80,
                'percentual_fim'    => 85,
                'descricao' => 'Chapisco, emboço, reboco fino e nivelamento de pisos.',
                'tarefas' => [
                    ['grupo' => 'Chapisco', 'itens' => [
                        'Aderência estrutural nas paredes',
                    ]],
                    ['grupo' => 'Emboço', 'itens' => [
                        'Nivelamento de paredes',
                    ]],
                    ['grupo' => 'Reboco', 'itens' => [
                        'Acabamento fino das paredes',
                    ]],
                    ['grupo' => 'Contrapiso', 'itens' => [
                        'Nivelamento de piso',
                        'Caimento para áreas molhadas',
                    ]],
                ],
            ],

            // ── 9 ─────────────────────────────────────────────────────────────
            [
                'ordem' => 9,
                'nome'  => 'Cobertura',
                'icone' => 'fas fa-home',
                'percentual_inicio' => 85,
                'percentual_fim'    => 88,
                'descricao' => 'Impermeabilização da laje de cobertura, telhado, calhas e rufos.',
                'tarefas' => [
                    ['grupo' => 'Laje de Cobertura', 'itens' => [
                        'Impermeabilização da laje',
                        'Teste de estanqueidade',
                    ]],
                    ['grupo' => 'Estrutura de Telhado', 'itens' => [
                        'Estrutura de madeira ou metálica',
                    ]],
                    ['grupo' => 'Telhamento', 'itens' => [
                        'Telhas cerâmicas ou metálicas',
                    ]],
                    ['grupo' => 'Drenagem', 'itens' => [
                        'Calhas',
                        'Rufos',
                        'Condutores pluviais',
                    ]],
                ],
            ],

            // ── 10 ────────────────────────────────────────────────────────────
            [
                'ordem' => 10,
                'nome'  => 'Esquadrias',
                'icone' => 'fas fa-door-open',
                'percentual_inicio' => 88,
                'percentual_fim'    => 90,
                'descricao' => 'Janelas, portas externas, caixilhos e vidros temperados.',
                'tarefas' => [
                    ['grupo' => 'Esquadrias', 'itens' => [
                        'Janelas de alumínio / vidro',
                        'Portas externas',
                        'Caixilhos',
                        'Vidros temperados',
                    ]],
                ],
            ],

            // ── 11 ────────────────────────────────────────────────────────────
            [
                'ordem' => 11,
                'nome'  => 'Instalações Finais',
                'icone' => 'fas fa-plug',
                'percentual_inicio' => 90,
                'percentual_fim'    => 93,
                'descricao' => 'Fiação elétrica completa, tomadas, louças, metais e testes.',
                'tarefas' => [
                    ['grupo' => 'Elétrica Final', 'itens' => [
                        'Passagem de cabos',
                        'Instalação de tomadas',
                        'Instalação de interruptores',
                        'Quadro geral',
                    ]],
                    ['grupo' => 'Hidráulica Final', 'itens' => [
                        'Instalação de louças',
                        'Instalação de torneiras',
                        'Instalação de registros',
                        'Testes de pressão',
                    ]],
                ],
            ],

            // ── 12 ────────────────────────────────────────────────────────────
            [
                'ordem' => 12,
                'nome'  => 'Revestimentos Finais',
                'icone' => 'fas fa-paint-brush',
                'percentual_inicio' => 93,
                'percentual_fim'    => 96,
                'descricao' => 'Porcelanato, cerâmica, pastilhas, revestimentos decorativos e rejunte.',
                'tarefas' => [
                    ['grupo' => 'Revestimentos', 'itens' => [
                        'Porcelanato',
                        'Cerâmica',
                        'Pastilhas',
                        'Revestimentos decorativos',
                        'Rejuntamento',
                    ]],
                ],
            ],

            // ── 13 ────────────────────────────────────────────────────────────
            [
                'ordem' => 13,
                'nome'  => 'Acabamentos',
                'icone' => 'fas fa-paint-roller',
                'percentual_inicio' => 96,
                'percentual_fim'    => 98,
                'descricao' => 'Massa corrida, lixamento, pintura, gesso, forro, rodapés e detalhes.',
                'tarefas' => [
                    ['grupo' => 'Pintura e Massa', 'itens' => [
                        'Massa corrida',
                        'Lixamento',
                        'Pintura interna',
                        'Pintura externa',
                    ]],
                    ['grupo' => 'Forro e Detalhes', 'itens' => [
                        'Forro de gesso / PVC',
                        'Rodapés',
                        'Detalhes finais',
                    ]],
                ],
            ],

            // ── 14 ────────────────────────────────────────────────────────────
            [
                'ordem' => 14,
                'nome'  => 'Marcenaria',
                'icone' => 'fas fa-hammer',
                'percentual_inicio' => 98,
                'percentual_fim'    => 99,
                'descricao' => 'Armários planejados, bancadas, nichos e painéis decorativos.',
                'tarefas' => [
                    ['grupo' => 'Marcenaria', 'itens' => [
                        'Armários planejados',
                        'Bancadas',
                        'Nichos',
                        'Painéis decorativos',
                    ]],
                ],
            ],

            // ── 15 ────────────────────────────────────────────────────────────
            [
                'ordem' => 15,
                'nome'  => 'Área Externa',
                'icone' => 'fas fa-leaf',
                'percentual_inicio' => 99,
                'percentual_fim'    => 99.5,
                'descricao' => 'Muros, portões, calçadas, paisagismo, drenagem e iluminação externa.',
                'tarefas' => [
                    ['grupo' => 'Área Externa', 'itens' => [
                        'Muros',
                        'Portões',
                        'Calçadas',
                        'Paisagismo',
                        'Drenagem pluvial externa',
                        'Iluminação externa',
                    ]],
                ],
            ],

            // ── 16 ────────────────────────────────────────────────────────────
            [
                'ordem' => 16,
                'nome'  => 'Limpeza e Entrega',
                'icone' => 'fas fa-key',
                'percentual_inicio' => 99.5,
                'percentual_fim'    => 100,
                'descricao' => 'Limpeza grossa e fina, testes finais, punch list, vistoria e Habite-se.',
                'tarefas' => [
                    ['grupo' => 'Limpeza e Testes', 'itens' => [
                        'Limpeza grossa',
                        'Limpeza fina',
                        'Testes elétricos finais',
                        'Testes hidráulicos finais',
                    ]],
                    ['grupo' => 'Entrega', 'itens' => [
                        'Correções (punch list)',
                        'Vistoria final',
                        'Habite-se',
                    ]],
                ],
            ],

        ];

        foreach ($fases as $fase) {
            $tarefas = $fase['tarefas'];
            unset($fase['tarefas']);

            $faseId = DB::table('fases_catalogo')->insertGetId(array_merge($fase, [
                'ativo'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Inserir tarefas com ordem sequencial
            $ordem = 1;
            foreach ($tarefas as $grupo) {
                foreach ($grupo['itens'] as $item) {
                    DB::table('fases_catalogo_tarefas')->insert([
                        'fase_catalogo_id' => $faseId,
                        'grupo'            => $grupo['grupo'],
                        'nome'             => $item,
                        'ordem'            => $ordem++,
                        'ativo'            => true,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 34 CATEGORIAS DE CUSTO
    // ──────────────────────────────────────────────────────────────────────────
    private function seedCategorias(): void
    {
        $categorias = [

            ['nome' => 'Estrutura', 'icone' => 'fas fa-building', 'tipo' => 'material', 'ordem' => 1,
             'subs' => [
                 ['nome' => 'Cimento',           'unidade' => 'sc'],
                 ['nome' => 'Areia',              'unidade' => 'm³'],
                 ['nome' => 'Brita',              'unidade' => 'm³'],
                 ['nome' => 'Aço / Ferragem',     'unidade' => 'kg'],
                 ['nome' => 'Formas (madeira)',   'unidade' => 'm²'],
                 ['nome' => 'Compensado',         'unidade' => 'un'],
                 ['nome' => 'Concreto Usinado',   'unidade' => 'm³'],
                 ['nome' => 'Aditivos',           'unidade' => 'L'],
                 ['nome' => 'Malhas de Aço',      'unidade' => 'm²'],
                 ['nome' => 'Escoramento',        'unidade' => 'm'],
             ]],

            ['nome' => 'Alvenaria', 'icone' => 'fas fa-th-large', 'tipo' => 'material', 'ordem' => 2,
             'subs' => [
                 ['nome' => 'Tijolos',               'unidade' => 'un'],
                 ['nome' => 'Blocos de Concreto',    'unidade' => 'un'],
                 ['nome' => 'Argamassa',             'unidade' => 'sc'],
                 ['nome' => 'Vergas / Contravergas', 'unidade' => 'un'],
                 ['nome' => 'Graute',                'unidade' => 'sc'],
                 ['nome' => 'Chapisco',              'unidade' => 'sc'],
                 ['nome' => 'Emboço / Reboco',       'unidade' => 'm²'],
             ]],

            ['nome' => 'Esquadrias', 'icone' => 'fas fa-door-open', 'tipo' => 'material', 'ordem' => 3,
             'subs' => [
                 ['nome' => 'Portas de Madeira',                  'unidade' => 'un'],
                 ['nome' => 'Portas de Alumínio',                 'unidade' => 'un'],
                 ['nome' => 'Portas de Vidro',                    'unidade' => 'un'],
                 ['nome' => 'Janelas',                            'unidade' => 'un'],
                 ['nome' => 'Portões',                            'unidade' => 'un'],
                 ['nome' => 'Ferragens (dobradiças, fechaduras)', 'unidade' => 'un'],
                 ['nome' => 'Vidros e Vedação',                   'unidade' => 'm²'],
             ]],

            ['nome' => 'Revestimentos', 'icone' => 'fas fa-border-all', 'tipo' => 'material', 'ordem' => 4,
             'subs' => [
                 ['nome' => 'Cerâmica',         'unidade' => 'm²'],
                 ['nome' => 'Porcelanato',       'unidade' => 'm²'],
                 ['nome' => 'Azulejo',           'unidade' => 'm²'],
                 ['nome' => 'Granito',           'unidade' => 'm²'],
                 ['nome' => 'Mármore',           'unidade' => 'm²'],
                 ['nome' => 'Argamassa Colante', 'unidade' => 'sc'],
                 ['nome' => 'Rejunte',           'unidade' => 'kg'],
                 ['nome' => 'Piso Vinílico',     'unidade' => 'm²'],
                 ['nome' => 'Piso Laminado',     'unidade' => 'm²'],
             ]],

            ['nome' => 'Acabamentos', 'icone' => 'fas fa-paint-roller', 'tipo' => 'material', 'ordem' => 5,
             'subs' => [
                 ['nome' => 'Tinta',           'unidade' => 'L'],
                 ['nome' => 'Massa Corrida',   'unidade' => 'L'],
                 ['nome' => 'Massa Acrílica',  'unidade' => 'L'],
                 ['nome' => 'Gesso',           'unidade' => 'sc'],
                 ['nome' => 'Drywall',         'unidade' => 'm²'],
                 ['nome' => 'Rodapés',         'unidade' => 'm'],
                 ['nome' => 'Molduras',        'unidade' => 'm'],
                 ['nome' => 'Verniz / Selador','unidade' => 'L'],
             ]],

            ['nome' => 'Instalações Elétricas', 'icone' => 'fas fa-bolt', 'tipo' => 'material', 'ordem' => 6,
             'subs' => [
                 ['nome' => 'Fios e Cabos',            'unidade' => 'm'],
                 ['nome' => 'Disjuntores',             'unidade' => 'un'],
                 ['nome' => 'Quadros Elétricos',       'unidade' => 'un'],
                 ['nome' => 'Tomadas e Interruptores', 'unidade' => 'un'],
                 ['nome' => 'Eletrodutos',             'unidade' => 'm'],
                 ['nome' => 'DR / DPS / Aterramento',  'unidade' => 'un'],
                 ['nome' => 'Luminárias',              'unidade' => 'un'],
             ]],

            ['nome' => 'Instalações Hidráulicas', 'icone' => 'fas fa-faucet', 'tipo' => 'material', 'ordem' => 7,
             'subs' => [
                 ['nome' => 'Tubos e Conexões',             'unidade' => 'm'],
                 ['nome' => 'Registros',                    'unidade' => 'un'],
                 ['nome' => 'Caixas d\'Água',               'unidade' => 'un'],
                 ['nome' => 'Louças (Vaso, Pia)',           'unidade' => 'un'],
                 ['nome' => 'Metais (Torneiras, Chuveiros)','unidade' => 'un'],
                 ['nome' => 'Bombas / Aquecedores',         'unidade' => 'un'],
             ]],

            ['nome' => 'Impermeabilização e Isolamento', 'icone' => 'fas fa-shield-alt', 'tipo' => 'material', 'ordem' => 8,
             'subs' => [
                 ['nome' => 'Mantas Impermeabilizantes',   'unidade' => 'm²'],
                 ['nome' => 'Selantes',                    'unidade' => 'un'],
                 ['nome' => 'Produtos Impermeabilizantes', 'unidade' => 'L'],
                 ['nome' => 'Isolamento Térmico',          'unidade' => 'm²'],
                 ['nome' => 'Isolamento Acústico',         'unidade' => 'm²'],
                 ['nome' => 'Lã de Vidro / Rocha',         'unidade' => 'm²'],
             ]],

            ['nome' => 'Cobertura', 'icone' => 'fas fa-home', 'tipo' => 'material', 'ordem' => 9,
             'subs' => [
                 ['nome' => 'Telhas',               'unidade' => 'un'],
                 ['nome' => 'Estrutura de Madeira', 'unidade' => 'm'],
                 ['nome' => 'Estrutura Metálica',   'unidade' => 'kg'],
                 ['nome' => 'Rufos e Calhas',       'unidade' => 'm'],
                 ['nome' => 'Mantas Térmicas',      'unidade' => 'm²'],
                 ['nome' => 'Cumeeira',             'unidade' => 'm'],
             ]],

            ['nome' => 'Marcenaria / Carpintaria', 'icone' => 'fas fa-hammer', 'tipo' => 'material', 'ordem' => 10,
             'subs' => [
                 ['nome' => 'Armários Planejados',  'unidade' => 'un'],
                 ['nome' => 'Bancadas',             'unidade' => 'm'],
                 ['nome' => 'Painéis de Madeira',   'unidade' => 'm²'],
                 ['nome' => 'Nichos / Prateleiras', 'unidade' => 'un'],
             ]],

            ['nome' => 'Ferragens e Fixação', 'icone' => 'fas fa-wrench', 'tipo' => 'material', 'ordem' => 11,
             'subs' => [
                 ['nome' => 'Parafusos',                'unidade' => 'cx'],
                 ['nome' => 'Buchas',                   'unidade' => 'cx'],
                 ['nome' => 'Pregos',                   'unidade' => 'cx'],
                 ['nome' => 'Suportes',                 'unidade' => 'un'],
                 ['nome' => 'Chumbadores / Cantoneiras','unidade' => 'cx'],
             ]],

            ['nome' => 'Urbanização / Área Externa', 'icone' => 'fas fa-leaf', 'tipo' => 'material', 'ordem' => 12,
             'subs' => [
                 ['nome' => 'Piso Intertravado',            'unidade' => 'm²'],
                 ['nome' => 'Grama',                        'unidade' => 'm²'],
                 ['nome' => 'Plantas',                      'unidade' => 'un'],
                 ['nome' => 'Muros / Cercas',               'unidade' => 'm'],
                 ['nome' => 'Drenagem / Iluminação Externa','unidade' => 'un'],
             ]],

            ['nome' => 'Sistemas Especiais', 'icone' => 'fas fa-cog', 'tipo' => 'ambos', 'ordem' => 13,
             'subs' => [
                 ['nome' => 'Elevadores / Plataformas',  'unidade' => 'un'],
                 ['nome' => 'Escadas Especiais',         'unidade' => 'un'],
                 ['nome' => 'Corrimãos / Guarda-corpos', 'unidade' => 'm'],
             ]],

            ['nome' => 'Segurança e Prevenção', 'icone' => 'fas fa-fire-extinguisher', 'tipo' => 'ambos', 'ordem' => 14,
             'subs' => [
                 ['nome' => 'Extintores',          'unidade' => 'un'],
                 ['nome' => 'Detectores de Fumaça','unidade' => 'un'],
                 ['nome' => 'Alarmes',             'unidade' => 'un'],
                 ['nome' => 'Câmeras',             'unidade' => 'un'],
                 ['nome' => 'Cercas Elétricas',    'unidade' => 'm'],
             ]],

            ['nome' => 'Climatização e Ventilação', 'icone' => 'fas fa-snowflake', 'tipo' => 'ambos', 'ordem' => 15,
             'subs' => [
                 ['nome' => 'Ar-condicionado','unidade' => 'un'],
                 ['nome' => 'Ventiladores',   'unidade' => 'un'],
                 ['nome' => 'Exaustores',     'unidade' => 'un'],
                 ['nome' => 'Dutos',          'unidade' => 'm'],
             ]],

            ['nome' => 'Automação e Tecnologia', 'icone' => 'fas fa-microchip', 'tipo' => 'ambos', 'ordem' => 16,
             'subs' => [
                 ['nome' => 'Casa Inteligente',          'unidade' => 'vb'],
                 ['nome' => 'Sensores',                  'unidade' => 'un'],
                 ['nome' => 'Interfone / Vídeo Porteiro','unidade' => 'un'],
                 ['nome' => 'Controle por App',          'unidade' => 'vb'],
             ]],

            ['nome' => 'Áudio, Vídeo e Comunicação', 'icone' => 'fas fa-tv', 'tipo' => 'ambos', 'ordem' => 17,
             'subs' => [
                 ['nome' => 'Sistema de Som',          'unidade' => 'vb'],
                 ['nome' => 'Antenas',                 'unidade' => 'un'],
                 ['nome' => 'Cabeamento (TV/Internet)','unidade' => 'm'],
                 ['nome' => 'Home Theater',            'unidade' => 'vb'],
             ]],

            ['nome' => 'Drenagem e Infraestrutura do Terreno', 'icone' => 'fas fa-water', 'tipo' => 'ambos', 'ordem' => 18,
             'subs' => [
                 ['nome' => 'Drenagem Pluvial',           'unidade' => 'm'],
                 ['nome' => 'Fossa / Sumidouro',          'unidade' => 'un'],
                 ['nome' => 'Nivelamento',                'unidade' => 'm²'],
                 ['nome' => 'Contenção (Muros de Arrimo)','unidade' => 'm²'],
             ]],

            ['nome' => 'Infraestrutura Inicial da Obra', 'icone' => 'fas fa-hard-hat', 'tipo' => 'servico', 'ordem' => 19,
             'subs' => [
                 ['nome' => 'Terraplanagem',     'unidade' => 'm³'],
                 ['nome' => 'Locação da Obra',   'unidade' => 'vb'],
                 ['nome' => 'Fundação (Sapata)',  'unidade' => 'un'],
                 ['nome' => 'Fundação (Radier)',  'unidade' => 'm²'],
                 ['nome' => 'Fundação (Estacas)', 'unidade' => 'un'],
             ]],

            ['nome' => 'Fachada', 'icone' => 'fas fa-city', 'tipo' => 'material', 'ordem' => 20,
             'subs' => [
                 ['nome' => 'Revestimentos Externos', 'unidade' => 'm²'],
                 ['nome' => 'Brises',                 'unidade' => 'm²'],
                 ['nome' => 'Pele de Vidro',          'unidade' => 'm²'],
                 ['nome' => 'Elementos Decorativos',  'unidade' => 'un'],
             ]],

            ['nome' => 'Paisagismo', 'icone' => 'fas fa-seedling', 'tipo' => 'ambos', 'ordem' => 21,
             'subs' => [
                 ['nome' => 'Projeto Paisagístico','unidade' => 'vb'],
                 ['nome' => 'Irrigação',           'unidade' => 'vb'],
                 ['nome' => 'Iluminação de Jardim','unidade' => 'un'],
                 ['nome' => 'Decoração Externa',   'unidade' => 'vb'],
             ]],

            ['nome' => 'Limpeza e Finalização', 'icone' => 'fas fa-broom', 'tipo' => 'servico', 'ordem' => 22,
             'subs' => [
                 ['nome' => 'Limpeza Pós-Obra','unidade' => 'vb'],
                 ['nome' => 'Polimento',       'unidade' => 'm²'],
                 ['nome' => 'Testes',          'unidade' => 'vb'],
                 ['nome' => 'Vistoria Final',  'unidade' => 'vb'],
             ]],

            ['nome' => 'Custos Indiretos e Gestão', 'icone' => 'fas fa-clipboard-list', 'tipo' => 'servico', 'ordem' => 23,
             'subs' => [
                 ['nome' => 'Administração',       'unidade' => 'vb'],
                 ['nome' => 'Seguro de Obra',      'unidade' => 'vb'],
                 ['nome' => 'Taxas',               'unidade' => 'vb'],
                 ['nome' => 'Controle Financeiro', 'unidade' => 'vb'],
             ]],

            ['nome' => 'Mobiliário e Decoração', 'icone' => 'fas fa-couch', 'tipo' => 'material', 'ordem' => 24,
             'subs' => [
                 ['nome' => 'Móveis',              'unidade' => 'un'],
                 ['nome' => 'Cortinas / Persianas','unidade' => 'un'],
                 ['nome' => 'Tapetes',             'unidade' => 'm²'],
                 ['nome' => 'Decoração',           'unidade' => 'vb'],
             ]],

            ['nome' => 'Mão de Obra', 'icone' => 'fas fa-hard-hat', 'tipo' => 'servico', 'ordem' => 25,
             'subs' => [
                 ['nome' => 'Pedreiros',      'unidade' => 'h'],
                 ['nome' => 'Serventes',      'unidade' => 'h'],
                 ['nome' => 'Eletricistas',   'unidade' => 'h'],
                 ['nome' => 'Encanadores',    'unidade' => 'h'],
                 ['nome' => 'Pintores',       'unidade' => 'h'],
                 ['nome' => 'Mestre de Obras','unidade' => 'h'],
                 ['nome' => 'Empreitada',     'unidade' => 'vb'],
             ]],

            ['nome' => 'Projetos Técnicos', 'icone' => 'fas fa-drafting-compass', 'tipo' => 'servico', 'ordem' => 26,
             'subs' => [
                 ['nome' => 'Projeto Arquitetônico', 'unidade' => 'vb'],
                 ['nome' => 'Projeto Estrutural',    'unidade' => 'vb'],
                 ['nome' => 'Projeto Elétrico',      'unidade' => 'vb'],
                 ['nome' => 'Projeto Hidráulico',    'unidade' => 'vb'],
                 ['nome' => 'Projeto de Interiores', 'unidade' => 'vb'],
                 ['nome' => 'Projeto de Paisagismo', 'unidade' => 'vb'],
             ]],

            ['nome' => 'Licenças e Documentação', 'icone' => 'fas fa-file-alt', 'tipo' => 'servico', 'ordem' => 27,
             'subs' => [
                 ['nome' => 'Alvará',     'unidade' => 'vb'],
                 ['nome' => 'Aprovações', 'unidade' => 'vb'],
                 ['nome' => 'Habite-se',  'unidade' => 'vb'],
                 ['nome' => 'ART / RRT',  'unidade' => 'vb'],
             ]],

            ['nome' => 'Transporte de Materiais', 'icone' => 'fas fa-truck', 'tipo' => 'servico', 'ordem' => 28,
             'subs' => [
                 ['nome' => 'Fretes',               'unidade' => 'vb'],
                 ['nome' => 'Entregas',             'unidade' => 'vb'],
                 ['nome' => 'Movimentação Interna', 'unidade' => 'vb'],
             ]],

            ['nome' => 'Transporte de Pessoas', 'icone' => 'fas fa-bus', 'tipo' => 'servico', 'ordem' => 29,
             'subs' => [
                 ['nome' => 'Transporte da Equipe','unidade' => 'vb'],
                 ['nome' => 'Combustível',         'unidade' => 'L'],
                 ['nome' => 'Logística',           'unidade' => 'vb'],
             ]],

            ['nome' => 'Limpeza de Obra', 'icone' => 'fas fa-trash-alt', 'tipo' => 'servico', 'ordem' => 30,
             'subs' => [
                 ['nome' => 'Remoção de Entulho','unidade' => 'm³'],
                 ['nome' => 'Caçambas',          'unidade' => 'un'],
                 ['nome' => 'Limpeza Final',     'unidade' => 'vb'],
             ]],

            ['nome' => 'Equipamentos e Ferramentas', 'icone' => 'fas fa-cogs', 'tipo' => 'servico', 'ordem' => 31,
             'subs' => [
                 ['nome' => 'Betoneira',            'unidade' => 'd'],
                 ['nome' => 'Andaimes',             'unidade' => 'd'],
                 ['nome' => 'Máquinas Pesadas',     'unidade' => 'h'],
                 ['nome' => 'Ferramentas Elétricas','unidade' => 'vb'],
             ]],

            ['nome' => 'Segurança do Trabalho', 'icone' => 'fas fa-hard-hat', 'tipo' => 'servico', 'ordem' => 32,
             'subs' => [
                 ['nome' => 'EPIs',        'unidade' => 'vb'],
                 ['nome' => 'Sinalização', 'unidade' => 'vb'],
                 ['nome' => 'Treinamentos','unidade' => 'vb'],
             ]],

            ['nome' => 'Administração e Gestão da Obra', 'icone' => 'fas fa-tasks', 'tipo' => 'servico', 'ordem' => 33,
             'subs' => [
                 ['nome' => 'Planejamento',      'unidade' => 'vb'],
                 ['nome' => 'Cronograma',        'unidade' => 'vb'],
                 ['nome' => 'Supervisão',        'unidade' => 'vb'],
                 ['nome' => 'Controle de Custos','unidade' => 'vb'],
             ]],

            ['nome' => 'Reserva e Imprevistos', 'icone' => 'fas fa-exclamation-triangle', 'tipo' => 'ambos', 'ordem' => 34,
             'subs' => [
                 ['nome' => 'Margem de Segurança','unidade' => 'vb'],
                 ['nome' => 'Ajustes',            'unidade' => 'vb'],
                 ['nome' => 'Perdas',             'unidade' => 'vb'],
                 ['nome' => 'Emergências',        'unidade' => 'vb'],
             ]],

        ];

        foreach ($categorias as $cat) {
            $subs = $cat['subs'];
            unset($cat['subs']);

            $catId = DB::table('categorias_material')->insertGetId(array_merge($cat, [
                'ativo'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            foreach ($subs as $sub) {
                DB::table('subcategorias_material')->insert(array_merge($sub, [
                    'categoria_id' => $catId,
                    'ativo'        => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]));
            }
        }
    }
}
