<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ControleSaidaEncarregadosController extends Controller
{
    // Dados fixos da empresa
    private $empresa = [
        'nome' => 'BRS SERVICOS E COMERCIO LTDA',
        'cnpj' => '34.80.4.3/85/0-001-61'
    ];

    /**
     * Exibe a página principal do Controle de Saída de Encarregados
     */
    public function index()
    {
        return view('estoque.controle-saida-encarregados', [
            'empresa' => $this->empresa
        ]);
    }

    /**
     * Buscar funcionários por nome (autocomplete)
     */
    public function buscarFuncionarios(Request $request)
    {
        try {
            $termo = trim($request->get('q', ''));
            
            if (strlen($termo) < 3) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $funcionarios = DB::table('funcionarios')
                ->select('id', 'nome', 'cpf', 'funcao', 'status', 'foto_path', 'created_at as data_admissao')
                ->where('nome', 'LIKE', '%' . $termo . '%')
                ->whereNotNull('nome')
                ->where('nome', '!=', '')
                ->orderBy('nome')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $funcionarios
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar funcionários: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar funcionários'
            ], 500);
        }
    }

    /**
     * Buscar centros de custo (setores)
     */
    public function buscarCentrosCusto(Request $request)
    {
        try {
            $termo = trim($request->get('q', ''));
            
            $query = DB::table('centro_custo')
                ->select('id', 'nome')
                ->orderBy('nome');

            if ($termo) {
                $query->where('nome', 'LIKE', '%' . $termo . '%');
            }

            $centros = $query->limit(50)->get();

            return response()->json([
                'success' => true,
                'data' => $centros
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar centros de custo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar centros de custo'
            ], 500);
        }
    }

    /**
     * Buscar produtos do estoque (autocomplete para descrição)
     */
    public function buscarProdutos(Request $request)
    {
        try {
            $termo = trim($request->get('q', ''));
            
            if (strlen($termo) < 3) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Buscar produtos únicos por nome (sem repetir)
            $produtos = DB::table('estoque')
                ->select('nome')
                ->where('nome', 'LIKE', '%' . $termo . '%')
                ->whereNotNull('nome')
                ->where('nome', '!=', '')
                ->groupBy('nome')
                ->orderBy('nome')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $produtos
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar produtos'
            ], 500);
        }
    }

    /**
     * Gerar próximo número de ficha
     */
    public function proximoNumeroFicha()
    {
        try {
            // Buscar o último número de ficha baseado no nome do arquivo
            $ultimoArquivo = DB::table('funcionarios_arquivos')
                ->where('arquivo_nome', 'LIKE', 'Ficha_EPI_Encarregado_%')
                ->orderBy('id', 'desc')
                ->first();

            $proximo = 1;
            if ($ultimoArquivo) {
                // Extrair número do nome do arquivo: Ficha_EPI_Encarregado_123_2025-01-23.pdf
                preg_match('/Ficha_EPI_Encarregado_(\d+)_/', $ultimoArquivo->arquivo_nome, $matches);
                if (isset($matches[1])) {
                    $proximo = intval($matches[1]) + 1;
                }
            }

            return response()->json([
                'success' => true,
                'numero_ficha' => $proximo
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar número de ficha: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar número de ficha',
                'numero_ficha' => 1
            ]);
        }
    }

    /**
     * Salvar registro de saída de EPI ou Uniforme
     */
    public function salvar(Request $request)
    {
        try {
            $request->validate([
                'funcionario_id' => 'required|exists:funcionarios,id',
                'endereco' => 'required|string|max:500',
                'setor_id' => 'required|exists:centro_custo,id',
                'tipo' => 'nullable|string|in:epi,uniforme',
                'itens' => 'required|array|min:1',
                'itens.*.descricao' => 'required|string|max:255',
                'itens.*.quantidade' => 'required|integer|min:1',
                'itens.*.tamanho' => 'nullable|string|max:50',
                'itens.*.ca' => 'nullable|string|max:50',
                'itens.*.data_entrega' => 'nullable|date',
                'itens.*.data_devolucao' => 'nullable|date',
                'itens.*.foto_entrega' => 'nullable|string|max:500',
                'itens.*.foto_devolucao' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            // Tipo de ficha (epi ou uniforme)
            $tipo = $request->tipo ?? 'epi';
            $tipoLabel = $tipo === 'epi' ? 'EPI' : 'Uniforme';
            $tipoPrefixo = $tipo === 'epi' ? 'Ficha_EPI' : 'Ficha_Uniforme';

            // Buscar dados do funcionário
            $funcionario = DB::table('funcionarios')->where('id', $request->funcionario_id)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            // Buscar dados do setor
            $setor = DB::table('centro_custo')->where('id', $request->setor_id)->first();

            // Gerar número de ficha baseado no tipo
            $ultimoArquivo = DB::table('funcionarios_arquivos')
                ->where('arquivo_nome', 'LIKE', $tipoPrefixo . '_Encarregado_%')
                ->orderBy('id', 'desc')
                ->first();

            $numeroFicha = 1;
            if ($ultimoArquivo) {
                preg_match('/' . $tipoPrefixo . '_Encarregado_(\d+)_/', $ultimoArquivo->arquivo_nome, $matches);
                if (isset($matches[1])) {
                    $numeroFicha = intval($matches[1]) + 1;
                }
            }

            // Gerar PDF com o tipo correto
            $pdfConteudo = $this->gerarPdfFichaEpi(
                $funcionario,
                $setor,
                $request->endereco,
                $numeroFicha,
                $request->itens,
                $tipo
            );

            $nomeArquivo = $tipoPrefixo . '_Encarregado_' . $numeroFicha . '_' . date('Y-m-d') . '.pdf';

            // Salvar na tabela funcionarios_arquivos
            $id = DB::table('funcionarios_arquivos')->insertGetId([
                'funcionario_id' => $request->funcionario_id,
                'nome_funcionario' => $funcionario->nome,
                'data' => now()->toDateString(),
                'horario' => now()->format('H:i:s'),
                'arquivo_nome' => $nomeArquivo,
                'arquivo_conteudo' => $pdfConteudo,
                'arquivo_mime' => 'application/pdf',
                'arquivo_tamanho' => strlen($pdfConteudo),
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ficha de ' . $tipoLabel . ' registrada com sucesso!',
                'id' => $id,
                'numero_ficha' => $numeroFicha,
                'tipo' => $tipo
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar ficha de EPI: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar ficha de EPI: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar PDF da ficha de EPI ou Uniforme
     */
    private function gerarPdfFichaEpi($funcionario, $setor, $endereco, $numeroFicha, $itens, $tipo = 'epi')
    {
        // Verificar se FPDF está disponível via Composer
        $fpdfPath = base_path('vendor/setasign/fpdf/fpdf.php');
        
        if (file_exists($fpdfPath)) {
            // Incluir o FPDF
            if (!class_exists('FPDF')) {
                require_once($fpdfPath);
            }
            return $this->gerarPdfComFpdf($funcionario, $setor, $endereco, $numeroFicha, $itens, $tipo);
        }
        
        // Fallback para PDF simples
        Log::warning('FPDF não encontrado, usando gerador simples de PDF');
        return $this->gerarPdfSimples($funcionario, $setor, $endereco, $numeroFicha, $itens, $tipo);
    }

    /**
     * Gerar PDF com FPDF - Layout conforme modelo
     */
    private function gerarPdfComFpdf($funcionario, $setor, $endereco, $numeroFicha, $itens, $tipo = 'epi')
    {
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);
        
        // Título principal com fundo cinza
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('Arial', 'B', 12);
        if ($tipo === 'epi') {
            $titulo = 'CONTROLE DE ENTREGA DE EQUIPAMENTOS DE PROTECAO INDIVIDUAL - EPIS';
        } else {
            $titulo = 'CONTROLE DE ENTREGA DE UNIFORMES';
        }
        $pdf->Cell(0, 10, $this->removerAcentos($titulo), 1, 1, 'C', true);
        $pdf->Ln(3);
        
        // Dados da empresa
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(20, 5, 'Empresa:', 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(80, 5, $this->removerAcentos($this->empresa['nome']), 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(15, 5, 'CNPJ:', 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, $this->empresa['cnpj'], 0, 1);
        
        // Dados do funcionário
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(20, 5, 'Nome:', 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(80, 5, $this->removerAcentos($funcionario->nome ?? 'N/A'), 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(20, 5, 'Funcao:', 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, $this->removerAcentos($funcionario->funcao ?? 'N/A'), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(20, 5, 'Setor:', 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(80, 5, $this->removerAcentos($setor->nome ?? 'N/A'), 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(25, 5, 'Data Admissao:', 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $dataAdmissao = $funcionario->created_at ? date('d/m/Y', strtotime($funcionario->created_at)) : 'N/A';
        $pdf->Cell(0, 5, $dataAdmissao, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(20, 5, 'Endereco:', 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $enderecoLimpo = $this->removerAcentos($endereco);
        $pdf->Cell(0, 5, substr($enderecoLimpo, 0, 100), 0, 1);
        
        $pdf->Ln(2);
        
        // Seção DECLARAÇÃO
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, 'DECLARACAO', 1, 1, 'C', true);
        $pdf->Ln(2);
        
        // Texto da declaração
        $pdf->SetFont('Arial', '', 8);
        $declaracao = "Declaro para todos efeitos legais que:\n";
        $declaracao .= "1o - Recebi os Equipamentos de Protecao Individual constantes da lista, novos e em perfeitas condicoes de uso, respectivo treinamento quanto a necessidade na utilizacao dos referidos EPI's, bem como da minha responsabilidade quanto a seu uso conforme determinado na NR-1 da Portaria 3.214/78 e que estou ciente das obrigacoes descritas na NR 06, baixada pela Portaria MTB 3214/78, sub item 6.7.1, a saber:\n";
        $declaracao .= "a) usar, utilizando-o apenas para a finalidade a que se destina;\n";
        $declaracao .= "b) responsabilizar-se pela guarda e conservacao;\n";
        $declaracao .= "c) comunicar ao empregador qualquer alteracao que o torne improprio para uso; e\n";
        $declaracao .= "d) cumprir as determinacoes do empregador sobre o uso adequado.\n";
        $declaracao .= "2o - Que estou ciente das disposicoes do Art. 462 e § 1o da CLT, e autorizo o desconto salarial proporcional ao custo de reparacao do dano que os EPIs aos meus cuidados venham apresentar e das disposicoes do artigo 158, alinea \"a\", da CLT, e do item 1.8 da NR 01, em especial daquela do subitem 1.8.1, de que constitui ato faltoso a recusa injustificada de usar EPI fornecido pela empresa, incorrendo nas penas da Lei.\n";
        $declaracao .= "3o - Fico proibido de dar ou emprestar o equipamento que estiver sob minha responsabilidade, so podendo faze-lo se receber ordem por escrito da pessoa autorizada para tal fim.\n";
        $declaracao .= "4o - Estando os equipamentos em minha posse, estarei sujeito a inspecoes sem previo aviso.";
        
        $pdf->MultiCell(0, 3.5, $declaracao, 0, 'J');
        $pdf->Ln(2);
        
        // Tabela de itens - diferente para EPI e Uniforme
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(200, 200, 200);
        
        if ($tipo === 'epi') {
            // Colunas para EPI: Data Entrega | Qtde | Equipamento | N° do CA
            $pdf->Cell(30, 6, 'Data Entrega', 1, 0, 'C', true);
            $pdf->Cell(20, 6, 'Qtde', 1, 0, 'C', true);
            $pdf->Cell(100, 6, 'Equipamento', 1, 0, 'C', true);
            $pdf->Cell(40, 6, 'N. do CA', 1, 1, 'C', true);
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($itens as $item) {
                $descricao = $this->removerAcentos($item['descricao'] ?? '');
                if (strlen($descricao) > 60) {
                    $descricao = substr($descricao, 0, 57) . '...';
                }
                $dataEntrega = isset($item['data_entrega']) && $item['data_entrega'] ? date('d/m/Y', strtotime($item['data_entrega'])) : date('d/m/Y');
                
                $pdf->Cell(30, 6, $dataEntrega, 1, 0, 'C');
                $pdf->Cell(20, 6, $item['quantidade'] ?? '1', 1, 0, 'C');
                $pdf->Cell(100, 6, $descricao, 1, 0, 'L');
                $pdf->Cell(40, 6, $item['ca'] ?? '', 1, 1, 'C');
            }
            
            // Linhas vazias
            $linhasVazias = max(0, 8 - count($itens));
            for ($i = 0; $i < $linhasVazias; $i++) {
                $pdf->Cell(30, 6, '', 1, 0, 'C');
                $pdf->Cell(20, 6, '', 1, 0, 'C');
                $pdf->Cell(100, 6, '', 1, 0, 'L');
                $pdf->Cell(40, 6, '', 1, 1, 'C');
            }
        } else {
            // Colunas para Uniforme: Descrição | Tam. | Qtde | Data Entrega
            $pdf->Cell(90, 6, 'Descricao', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Tam.', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Qtde', 1, 0, 'C', true);
            $pdf->Cell(40, 6, 'Data Entrega', 1, 1, 'C', true);
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($itens as $item) {
                $descricao = $this->removerAcentos($item['descricao'] ?? '');
                if (strlen($descricao) > 50) {
                    $descricao = substr($descricao, 0, 47) . '...';
                }
                $dataEntrega = isset($item['data_entrega']) && $item['data_entrega'] ? date('d/m/Y', strtotime($item['data_entrega'])) : date('d/m/Y');
                
                $pdf->Cell(90, 6, $descricao, 1, 0, 'L');
                $pdf->Cell(30, 6, $item['tamanho'] ?? '', 1, 0, 'C');
                $pdf->Cell(30, 6, $item['quantidade'] ?? '1', 1, 0, 'C');
                $pdf->Cell(40, 6, $dataEntrega, 1, 1, 'C');
            }
            
            // Linhas vazias
            $linhasVazias = max(0, 8 - count($itens));
            for ($i = 0; $i < $linhasVazias; $i++) {
                $pdf->Cell(90, 6, '', 1, 0, 'L');
                $pdf->Cell(30, 6, '', 1, 0, 'C');
                $pdf->Cell(30, 6, '', 1, 0, 'C');
                $pdf->Cell(40, 6, '', 1, 1, 'C');
            }
        }
        
        // Verificar se há assinatura digital válida
        $assinaturaDigital = null;
        $metadadosAssinatura = null;
        foreach ($itens as $item) {
            if (!empty($item['metadados_entrega']) && isset($item['metadados_entrega']['assinatura_digital']) && $item['metadados_entrega']['assinatura_digital'] === true) {
                $assinaturaDigital = true;
                $metadadosAssinatura = $item['metadados_entrega'];
                break;
            }
        }
        
        // Assinatura do funcionário - APÓS a tabela
        $pdf->Ln(8);
        
        if ($assinaturaDigital && $metadadosAssinatura) {
            // ASSINATURA DIGITAL - verificação facial aprovada
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(0, 100, 0); // Verde escuro
            $pdf->Cell(0, 6, 'ASSINADO DIGITALMENTE', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0); // Voltar para preto
            
            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(80, 80, 80); // Cinza
            
            // Dados da assinatura digital
            $dataHora = isset($metadadosAssinatura['data_formatada']) ? $metadadosAssinatura['data_formatada'] : date('d/m/Y H:i:s');
            $ip = isset($metadadosAssinatura['ip']) ? $metadadosAssinatura['ip'] : 'N/A';
            $dispositivo = isset($metadadosAssinatura['navegador']) ? $metadadosAssinatura['navegador'] : 'N/A';
            $plataforma = isset($metadadosAssinatura['plataforma']) ? $metadadosAssinatura['plataforma'] : 'N/A';
            $tela = isset($metadadosAssinatura['tela']) ? $metadadosAssinatura['tela'] : 'N/A';
            
            // Verificação facial
            $confianca = 'N/A';
            if (isset($metadadosAssinatura['verificacao_facial']) && isset($metadadosAssinatura['verificacao_facial']['confianca'])) {
                $confianca = $metadadosAssinatura['verificacao_facial']['confianca'] . '%';
            }
            
            // Localização GPS
            $localizacao = 'Nao disponivel';
            if (isset($metadadosAssinatura['latitude']) && isset($metadadosAssinatura['longitude']) && $metadadosAssinatura['latitude'] && $metadadosAssinatura['longitude']) {
                $localizacao = 'Lat: ' . number_format($metadadosAssinatura['latitude'], 6) . ', Long: ' . number_format($metadadosAssinatura['longitude'], 6);
                if (isset($metadadosAssinatura['precisao_gps'])) {
                    $localizacao .= ' (Precisao: ' . round($metadadosAssinatura['precisao_gps']) . 'm)';
                }
            }
            
            $pdf->Cell(0, 4, 'Verificacao facial: ' . $confianca . ' de correspondencia', 0, 1);
            $pdf->Cell(0, 4, 'Data/Hora: ' . $dataHora, 0, 1);
            $pdf->Cell(0, 4, 'IP: ' . $ip, 0, 1);
            $pdf->Cell(0, 4, 'Dispositivo: ' . $dispositivo . ' | Plataforma: ' . $plataforma . ' | Tela: ' . $tela, 0, 1);
            $pdf->Cell(0, 4, 'Localizacao GPS: ' . $localizacao, 0, 1);
            
            $pdf->SetTextColor(0, 0, 0); // Voltar para preto
        } else {
            // Assinatura manual tradicional
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(25, 5, 'Assinatura:', 0, 0);
            $pdf->Cell(85, 5, '________________________________________', 0, 0);
            $pdf->Cell(15, 5, 'Data:', 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, date('d/m/Y'), 0, 1);
        }
        
        // Segunda página - Fotos
        $temFotos = false;
        foreach ($itens as $item) {
            if (!empty($item['foto_entrega']) || !empty($item['foto_devolucao'])) {
                $temFotos = true;
                break;
            }
        }
        
        if ($temFotos) {
            $pdf->AddPage();
            
            // Título da página de fotos
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('Arial', 'B', 12);
            $tituloFotos = $tipo === 'epi' ? 'FOTOS DE ENTREGA DE EPIS' : 'FOTOS DE ENTREGA DE UNIFORMES';
            $pdf->Cell(0, 10, $tituloFotos, 1, 1, 'C', true);
            $pdf->Ln(5);
            
            // Nome do funcionário
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(30, 6, 'Funcionario:', 0, 0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 6, $this->removerAcentos($funcionario->nome ?? 'N/A'), 0, 1);
            $pdf->Ln(5);
            
            $fotoY = $pdf->GetY();
            $fotoX = 10;
            $fotoLargura = 90;
            $fotoAltura = 70;
            
            foreach ($itens as $index => $item) {
                $descricao = $this->removerAcentos($item['descricao'] ?? 'Item ' . ($index + 1));
                
                // Foto de Entrega
                if (!empty($item['foto_entrega'])) {
                    $caminhoFoto = public_path($item['foto_entrega']);
                    
                    if (file_exists($caminhoFoto)) {
                        // Verificar se precisa nova página
                        if ($fotoY + $fotoAltura + 20 > 280) {
                            $pdf->AddPage();
                            $fotoY = 20;
                        }
                        
                        $pdf->SetFont('Arial', 'B', 9);
                        $pdf->SetXY($fotoX, $fotoY);
                        $pdf->Cell($fotoLargura, 6, 'Foto Entrega - ' . substr($descricao, 0, 30), 0, 1, 'L');
                        
                        try {
                            $pdf->Image($caminhoFoto, $fotoX, $fotoY + 6, $fotoLargura, $fotoAltura);
                        } catch (\Exception $e) {
                            $pdf->SetXY($fotoX, $fotoY + 6);
                            $pdf->Cell($fotoLargura, $fotoAltura, 'Erro ao carregar imagem', 1, 0, 'C');
                        }
                        
                        $fotoX = 105;
                    }
                }
                
                // Foto de Devolução
                if (!empty($item['foto_devolucao'])) {
                    $caminhoFoto = public_path($item['foto_devolucao']);
                    
                    if (file_exists($caminhoFoto)) {
                        // Verificar se precisa nova página
                        if ($fotoY + $fotoAltura + 20 > 280 && $fotoX == 10) {
                            $pdf->AddPage();
                            $fotoY = 20;
                        }
                        
                        $pdf->SetFont('Arial', 'B', 9);
                        $pdf->SetXY($fotoX, $fotoY);
                        $pdf->Cell($fotoLargura, 6, 'Foto Devolucao - ' . substr($descricao, 0, 30), 0, 1, 'L');
                        
                        try {
                            $pdf->Image($caminhoFoto, $fotoX, $fotoY + 6, $fotoLargura, $fotoAltura);
                        } catch (\Exception $e) {
                            $pdf->SetXY($fotoX, $fotoY + 6);
                            $pdf->Cell($fotoLargura, $fotoAltura, 'Erro ao carregar imagem', 1, 0, 'C');
                        }
                    }
                }
                
                // Próxima linha de fotos
                if ($fotoX > 100 || (!empty($item['foto_entrega']) && empty($item['foto_devolucao']))) {
                    $fotoY += $fotoAltura + 15;
                    $fotoX = 10;
                }
            }
        }
        
        return $pdf->Output('S');
    }

    /**
     * Gerar PDF simples sem biblioteca externa (fallback)
     */
    private function gerarPdfSimples($funcionario, $setor, $endereco, $numeroFicha, $itens, $tipo = 'epi')
    {
        $titulo = $tipo === 'epi' 
            ? 'CONTROLE DE ENTREGA DE EQUIPAMENTOS DE PROTECAO INDIVIDUAL - EPIS' 
            : 'CONTROLE DE ENTREGA DE UNIFORMES';
        
        $conteudo = "%PDF-1.4\n";
        $conteudo .= "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
        $conteudo .= "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
        $conteudo .= "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n";
        
        $texto = "BT /F1 12 Tf 50 750 Td (" . $titulo . ") Tj ET\n";
        $texto .= "BT /F1 10 Tf 50 720 Td (Empresa: " . $this->empresa['nome'] . ") Tj ET\n";
        $texto .= "BT /F1 10 Tf 50 705 Td (CNPJ: " . $this->empresa['cnpj'] . ") Tj ET\n";
        $texto .= "BT /F1 10 Tf 50 685 Td (Funcionario: " . ($funcionario->nome ?? 'N/A') . ") Tj ET\n";
        $texto .= "BT /F1 10 Tf 50 670 Td (Funcao: " . ($funcionario->funcao ?? 'N/A') . ") Tj ET\n";
        $texto .= "BT /F1 10 Tf 50 655 Td (Setor: " . ($setor->nome ?? 'N/A') . ") Tj ET\n";
        $texto .= "BT /F1 10 Tf 50 635 Td (Ficha Registro: " . $numeroFicha . ") Tj ET\n";
        
        $y = 600;
        foreach ($itens as $item) {
            $texto .= "BT /F1 9 Tf 50 " . $y . " Td (" . ($item['descricao'] ?? '') . " - Qtd: " . ($item['quantidade'] ?? 1) . ") Tj ET\n";
            $y -= 15;
        }
        
        $conteudo .= "4 0 obj << /Length " . strlen($texto) . " >> stream\n" . $texto . "endstream endobj\n";
        $conteudo .= "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";
        $conteudo .= "xref\n0 6\n0000000000 65535 f \n";
        $conteudo .= "trailer << /Size 6 /Root 1 0 R >>\n";
        $conteudo .= "startxref\n" . strlen($conteudo) . "\n%%EOF";
        
        return $conteudo;
    }

    /**
     * Remover acentos para compatibilidade com PDF
     */
    private function removerAcentos($string)
    {
        if (!$string) return '';
        
        $acentos = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N'
        ];
        
        return strtr($string, $acentos);
    }

    /**
     * Upload de foto via câmera
     */
    public function uploadFoto(Request $request)
    {
        try {
            $data = $request->json()->all();
            
            if (empty($data['foto'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma foto enviada'
                ], 400);
            }
            
            $fotoBase64 = $data['foto'];
            $tipo = $data['tipo'] ?? 'entrega';
            $tabela = $data['tabela'] ?? 'epi';
            $metadados = $data['metadados'] ?? [];
            
            // Capturar IP do usuário
            $ip = $request->ip();
            if ($request->header('X-Forwarded-For')) {
                $ip = explode(',', $request->header('X-Forwarded-For'))[0];
            }
            
            // Adicionar IP aos metadados
            if (is_array($metadados)) {
                $metadados['ip'] = $ip;
                $metadados['user_agent_servidor'] = $request->header('User-Agent');
            }
            
            // Remover prefixo base64 se existir
            if (strpos($fotoBase64, ',') !== false) {
                $fotoBase64 = explode(',', $fotoBase64)[1];
            }
            
            // Decodificar base64
            $fotoData = base64_decode($fotoBase64);
            
            if ($fotoData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao decodificar a imagem'
                ], 400);
            }
            
            // Gerar nome único para o arquivo
            $nomeArquivo = 'foto_' . $tabela . '_' . $tipo . '_' . time() . '_' . uniqid() . '.jpg';
            
            // Definir diretório de destino
            // Na Hostinger, public_html é o diretório público
            $diretorioRelativo = 'storage/fotos_epi/' . date('Y/m');
            $diretorioCompleto = public_path($diretorioRelativo);
            
            // Criar diretório se não existir
            if (!file_exists($diretorioCompleto)) {
                mkdir($diretorioCompleto, 0755, true);
            }
            
            // Salvar arquivo da foto
            $caminhoCompleto = $diretorioCompleto . '/' . $nomeArquivo;
            $resultado = file_put_contents($caminhoCompleto, $fotoData);
            
            if ($resultado === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao salvar a imagem no servidor'
                ], 500);
            }
            
            // Salvar metadados em arquivo JSON separado
            if (!empty($metadados)) {
                $nomeMetadados = str_replace('.jpg', '_metadados.json', $nomeArquivo);
                $caminhoMetadados = $diretorioCompleto . '/' . $nomeMetadados;
                file_put_contents($caminhoMetadados, json_encode($metadados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            
            // Caminho relativo para salvar no banco
            $caminhoRelativo = $diretorioRelativo . '/' . $nomeArquivo;
            
            // URL para exibição
            $url = asset($caminhoRelativo);
            
            Log::info('Foto salva com sucesso', [
                'caminho' => $caminhoRelativo,
                'url' => $url,
                'tipo' => $tipo,
                'tabela' => $tabela,
                'metadados' => $metadados
            ]);
            
            return response()->json([
                'success' => true,
                'caminho' => $caminhoRelativo,
                'url' => $url,
                'message' => 'Foto salva com sucesso',
                'metadados' => $metadados
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao fazer upload de foto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar foto: ' . $e->getMessage()
            ], 500);
        }
    }
}
