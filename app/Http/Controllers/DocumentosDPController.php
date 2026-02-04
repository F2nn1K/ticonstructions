<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Models\Funcionario;
use App\Models\FuncionarioDocumento;
use Illuminate\Support\Str;
use App\Helpers\ArquivoHelper;

class DocumentosDPController extends Controller
{
    /**
     * Construtor do controller - SEGURANÇA
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:doc_dp')->only(['inclusao', 'store', 'downloadBLOB']);
        $this->middleware('can:vis_func')->only(['funcionarios', 'buscarFuncionario', 'listarDocumentos', 'anexarFaltantes', 'demitirFuncionario', 'listarAtestados', 'anexarAtestado', 'downloadAtestado', 'listarAdvertencias', 'aplicarAdvertencia', 'downloadAdvertencia', 'listarEpis', 'listarEpisRetroativos', 'storeEpiRetroativo', 'downloadEpiRetroativo', 'deleteEpiRetroativo', 'listarContraCheques', 'storeContraCheque', 'downloadContraCheque', 'listarFerias', 'storeFerias', 'downloadFerias', 'listarDecimo', 'storeDecimo', 'downloadDecimo', 'listarRescisao', 'storeRescisao', 'downloadRescisao']);
    }

    /**
     * Lista de documentos válidos (SEGURANÇA - Whitelist)
     */
    private function getDocumentosValidos()
    {
        return [
            '02 fotos 3x4',
            'Carteira de saúde atualizada com foto 3x4',
            'Encaminhamento para exame admissional',
            'Antecedente cível e criminal',
            'R.G. (identidade)',
            'CPF',
            'CNH (carteira nacional de habilitação)',
            'Título Eleitoral',
            'Comprovante de endereço (com CEP)',
            'Carteira de trabalho, frente e verso',
            'Certidão de nascimento',
            'CPF filho',
            'Carteira de vacinação (menor 07 anos)',
            'Comprovante de frequência escolar (maior 07 anos)'
        ];
    }

    /**
     * Exibe a página de inclusão de documentos DP
     */
    public function inclusao()
    {
        return view('documentos-dp.inclusao');
    }

    /**
     * Exibe a página de funcionários (gestão completa)
     */
    public function funcionarios()
    {
        return view('documentos-dp.funcionarios');
    }

    // ========================================
    // ROTAS DE ORDEM DE SERVIÇO DO DP
    // ========================================

    /**
     * Página inicial de Ordem de Serviço (ações rápidas)
     */
    public function ordemServicoIndex()
    {
        return view('documentos-dp.ordem-servico-index');
    }

    /**
     * Formulário para criar nova O.S.
     */
    public function ordemServicoNova()
    {
        // Gerar próximo número de OS (formato: OS-YYYYMMDD-XXXX)
        $hoje = date('Ymd');
        $ultimaOS = DB::table('ordens_servico')
            ->where('numero_os', 'LIKE', "OS-{$hoje}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimaOS && preg_match('/OS-\d{8}-(\d+)/', $ultimaOS->numero_os, $matches)) {
            $seq = intval($matches[1]) + 1;
        } else {
            $seq = 1;
        }

        $numeroOs = sprintf('OS-%s-%04d', $hoje, $seq);

        return view('documentos-dp.ordem-servico', ['numeroOs' => $numeroOs]);
    }

    /**
     * Listar O.S. do dia ou por filtros
     */
    public function ordemServicoLista(Request $request)
    {
        $data = $request->get('data', date('Y-m-d'));
        $dataIni = $request->get('data_ini');
        $dataFim = $request->get('data_fim');
        $numeroOs = $request->get('numero_os');

        $query = DB::table('ordens_servico as os')
            ->leftJoin('funcionarios as f', 'os.funcionario_id', '=', 'f.id')
            ->select('os.id', 'os.numero_os', 'os.data_os', 'os.cidade', 'os.estado', 'os.telefone', 'f.nome as funcionario');

        // Prioridade: número da OS
        if ($numeroOs) {
            $query->where('os.numero_os', 'LIKE', '%' . $numeroOs . '%');
        } elseif ($dataIni && $dataFim) {
            $query->whereBetween('os.data_os', [$dataIni, $dataFim]);
        } elseif ($data) {
            $query->whereDate('os.data_os', $data);
        }

        $registros = $query->orderByDesc('os.data_os')->limit(500)->get();

        return view('documentos-dp.ordem-servico-lista', [
            'registros' => $registros,
            'data' => $data,
            'data_ini' => $dataIni,
            'data_fim' => $dataFim,
            'numero_os' => $numeroOs
        ]);
    }

    /**
     * Salvar nova O.S.
     */
    public function ordemServicoStore(Request $request)
    {
        $request->validate([
            'numero_os' => 'required|string|max:30',
            'data_os' => 'required|date',
            'descricao' => 'required|string',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:120',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'telefone' => 'nullable|string|max:20',
            'cpf_cnpj' => 'nullable|string|max:20',
            'observacoes' => 'nullable|string',
        ]);

        DB::table('ordens_servico')->insert([
            'numero_os' => $request->numero_os,
            'data_os' => $request->data_os,
            'funcionario_id' => $request->funcionario_id ?: null,
            'descricao' => $request->descricao,
            'endereco' => $request->endereco,
            'cidade' => $request->cidade,
            'estado' => strtoupper($request->estado ?? ''),
            'cep' => $request->cep,
            'telefone' => $request->telefone,
            'cpf_cnpj' => $request->cpf_cnpj,
            'observacoes' => $request->observacoes,
            'status' => 'aberta',
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('documentos-dp.ordem-servico.lista')
            ->with('success', 'Ordem de Serviço criada com sucesso!');
    }

    /**
     * API: Retorna dados de uma O.S. específica (JSON)
     */
    public function ordemServicoShow($id)
    {
        $os = DB::table('ordens_servico as os')
            ->leftJoin('funcionarios as f', 'os.funcionario_id', '=', 'f.id')
            ->select('os.*', 'f.nome as funcionario', 'f.cpf as funcionario_cpf')
            ->where('os.id', $id)
            ->first();

        if (!$os) {
            return response()->json(['success' => false, 'message' => 'O.S. não encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $os]);
    }

    /**
     * Verifica duplicidade de CPF (AJAX) - retorna { exists: true|false, nome?: string }
     */
    public function checkCpf(Request $request)
    {
        $cpf = preg_replace('/[^0-9]/', '', (string)$request->query('cpf'));
        if (!$cpf || strlen($cpf) != 11) {
            return response()->json(['exists' => false]);
        }
        $func = DB::table('funcionarios')->where('cpf', $cpf)->first(['id','nome']);
        return response()->json(['exists' => (bool)$func, 'nome' => $func->nome ?? null]);
    }

    /**
     * Processa o formulário de inclusão de documentos - SEGURANÇA RIGOROSA
     */
    public function store(Request $request)
    {
        try {

            // Validação dos dados (Documento Unificado opcional)
            $validator = Validator::make($request->all(), [
                'nome_funcionario' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
                'funcao' => 'required|string|max:255',
                'cpf' => 'required|string|regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                'sexo' => 'required|string|in:M,F',
                // 70 MB (em KB) => 70 * 1024 = 71680
                'documento_unificado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:71680',
            ], [
                'nome_funcionario.required' => 'O nome do funcionário é obrigatório.',
                'nome_funcionario.regex' => 'O nome deve conter apenas letras e espaços.',
                'funcao.required' => 'A função é obrigatória.',
                'cpf.required' => 'O CPF é obrigatório.',
                'cpf.regex' => 'O CPF deve estar no formato 000.000.000-00.',
                'sexo.required' => 'O sexo é obrigatório.',
                'sexo.in' => 'O sexo deve ser Masculino (M) ou Feminino (F).',
                'documento_unificado.mimes' => 'Arquivo inválido. Use PDF, JPG, JPEG ou PNG.',
                'documento_unificado.max' => 'O arquivo deve ter no máximo 15MB.',
            ]);

            // Fluxo antigo com múltiplos documentos removido nesta tela: usamos apenas Documento Unificado

            if ($validator->fails()) {
                Log::warning('Validação falhou em inclusão de documentos DP', [
                    'errors' => $validator->errors()->toArray(),
                    'usuario_id' => Auth::id()
                ]);
                
                return back()->withErrors($validator)->withInput();
            }

            // Verificação adicional de permissão
            if (!Auth::user()->temPermissao('doc_dp')) {
                Log::warning('Tentativa de acesso sem permissão doc_dp', [
                    'usuario_id' => Auth::id(),
                    'usuario_nome' => Auth::user()->name
                ]);
                
                abort(403, 'Acesso não autorizado');
            }

            // Sanitização dos dados
            $dadosLimpos = [
                'nome' => strip_tags(trim($request->nome_funcionario)),
                'funcao' => strip_tags(trim($request->funcao)),
                'cpf' => preg_replace('/[^0-9]/', '', strip_tags(trim($request->cpf))), // Remove pontos e hífen
                'sexo' => strip_tags(trim($request->sexo)),
                // nesta tela não há lista de documentos
            ];

            // Iniciar transação para garantir integridade
            DB::beginTransaction();

            // Verificar se funcionário já existe pelo CPF
            $funcionario = Funcionario::where('cpf', $dadosLimpos['cpf'])->first();
            
            if (!$funcionario) {
                // Criar novo funcionário
                $funcionario = Funcionario::create([
                    'nome' => $dadosLimpos['nome'],
                    'funcao' => $dadosLimpos['funcao'],
                    'cpf' => $dadosLimpos['cpf'],
                    'sexo' => $dadosLimpos['sexo'],
                    'status' => 'trabalhando'
                ]);
                
                if (config('app.debug')) {
                    Log::debug('Novo funcionário criado', [
                        'funcionario_id' => $funcionario->id,
                    ]);
                }
            } else {
                // Atualizar dados do funcionário existente se necessário
                $funcionario->update([
                    'nome' => $dadosLimpos['nome'],
                    'funcao' => $dadosLimpos['funcao'],
                    'sexo' => $dadosLimpos['sexo']
                ]);
                
                if (config('app.debug')) {
                    Log::debug('Funcionário existente atualizado', [
                        'funcionario_id' => $funcionario->id,
                    ]);
                }
            }

            // Processar um único arquivo: Documento Unificado
            if ($request->hasFile('documento_unificado')) {
                $this->processarUploadArquivoBLOB($request->file('documento_unificado'), $funcionario->id, 'documento unificado');
            }

            // Commit da transação
            DB::commit();

            return back()->with('success', 'Documentos registrados com sucesso!');

        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollback();
            
            Log::error('Erro ao salvar documentos DP: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'usuario_id' => Auth::id()
            ]);

            return back()->with('error', 'Erro ao registrar documentos. Tente novamente.')->withInput();
        }
    }

    /**
     * Processa upload de arquivo e salva em STORAGE + BLOB (migração)
     */
    private function processarUploadArquivoBLOB($arquivo, $funcionarioId, $tipoDocumento)
    {
        try {
            // Validações de segurança do arquivo
            $validator = Validator::make(['arquivo' => $arquivo], [
                // 70 MB (em KB)
                'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:71680'
            ]);

            if ($validator->fails()) {
                return false;
            }

            // Usar helper para salvar em storage + BLOB
            $dadosArquivo = ArquivoHelper::salvar($arquivo, $funcionarioId, 'documentos');

            // Hash para integridade
            $hashArquivo = hash('sha256', $dadosArquivo['conteudo']);

            // Usar o model para inserir
            $documento = FuncionarioDocumento::create([
                'funcionario_id' => $funcionarioId,
                'tipo_documento' => $tipoDocumento,
                'arquivo_nome' => $dadosArquivo['nome'],
                'arquivo_conteudo' => $dadosArquivo['conteudo'], // BLOB (compatibilidade)
                'arquivo_path' => $dadosArquivo['path'], // STORAGE (novo)
                'arquivo_mime_type' => $dadosArquivo['mime'],
                'arquivo_extensao' => $dadosArquivo['extensao'],
                'arquivo_tamanho' => $dadosArquivo['tamanho'],
                'arquivo_hash' => $hashArquivo,
                'usuario_cadastro' => Auth::id(),
                'status' => 'pendente'
            ]);

            if (config('app.debug')) {
                Log::debug('Documento salvo com sucesso', [
                    'documento_id' => $documento->id,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Erro ao salvar arquivo: ' . $e->getMessage(), [
                'funcionario_id' => $funcionarioId,
                'tipo_documento' => $tipoDocumento
            ]);
            return false;
        }
    }

    /**
     * Gera nome do campo de arquivo baseado no tipo de documento
     */
    private function getArquivoFieldName($tipoDocumento)
    {
        $mapeamento = [
            '02 fotos 3x4' => 'arquivo_fotos',
            'Carteira de saúde atualizada com foto 3x4' => 'arquivo_carteira_saude',
            'Encaminhamento para exame admissional' => 'arquivo_encaminhamento',
            'Antecedente cível e criminal' => 'arquivo_antecedente',
            'R.G. (identidade)' => 'arquivo_rg',
            'CPF' => 'arquivo_cpf',
            'CNH (carteira nacional de habilitação)' => 'arquivo_cnh',
            'Título Eleitoral' => 'arquivo_titulo',
            'Comprovante de endereço (com CEP)' => 'arquivo_endereco',
            'Carteira de trabalho, frente e verso' => 'arquivo_carteira_trabalho',
            'Certidão de nascimento' => 'arquivo_certidao_nascimento',
            'CPF filho' => 'arquivo_cpf_filho',
            'Carteira de vacinação (menor 07 anos)' => 'arquivo_vacinacao',
            'Comprovante de frequência escolar (maior 07 anos)' => 'arquivo_frequencia'
        ];

        return $mapeamento[$tipoDocumento] ?? 'arquivo_generico';
    }

    /**
     * Download/Visualização de arquivos BLOB (PROTEÇÃO)
     */
    public function downloadBLOB($arquivoId)
    {
        if (!Auth::user()->temPermissao('doc_dp')) {
            abort(403);
        }

        $arquivo = DB::table('funcionarios_documentos')->where('id', $arquivoId)->first();
        
        if (!$arquivo) {
            abort(404, 'Arquivo não encontrado');
        }

        // Usar helper universal (busca storage primeiro, fallback BLOB)
        return ArquivoHelper::download($arquivo, 'documento');
    }

    /**
     * Buscar funcionário e seus documentos
     */
    public function buscarFuncionario(Request $request)
    {
        try {
            $nome = $request->get('nome', '');
            
            if (strlen($nome) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Digite pelo menos 2 caracteres para buscar'
                ]);
            }

            // Buscar funcionários e seus documentos (por nome OU CPF)
            $funcionarios = DB::table('funcionarios as f')
                ->select([
                    'f.id',
                    'f.nome',
                    'f.cpf',
                    'f.sexo',
                    'f.funcao',
                    'f.status',
                    'f.foto_path',
                    'f.created_at',
                    DB::raw('COUNT(fd.id) as total_documentos')
                ])
                ->leftJoin('funcionarios_documentos as fd', 'f.id', '=', 'fd.funcionario_id')
                ->where(function($query) use ($nome) {
                    $query->where('f.nome', 'LIKE', '%' . $nome . '%')
                          ->orWhere('f.cpf', 'LIKE', '%' . $nome . '%');
                })
                ->groupBy('f.id', 'f.nome', 'f.cpf', 'f.sexo', 'f.funcao', 'f.status', 'f.foto_path', 'f.created_at')
                ->orderBy('f.created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'funcionarios' => $funcionarios
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na busca de funcionário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro na busca'
            ]);
        }
    }

    /**
     * Listar documentos de um funcionário específico
     */
    public function listarDocumentos($funcionarioId)
    {
        try {
            // Verificar permissão (doc_dp OU vis_func)
            if (!Auth::user()->temPermissao('doc_dp') && !Auth::user()->temPermissao('vis_func')) {
                abort(403);
            }

            // Buscar funcionário e documentos
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            
            if (!$funcionario) {
                abort(404, 'Funcionário não encontrado');
            }

            $documentos = DB::table('funcionarios_documentos')
                ->select('id','funcionario_id','tipo_documento','arquivo_nome','arquivo_extensao','arquivo_mime_type','arquivo_tamanho','created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('tipo_documento')
                ->get();

            return response()->json([
                'success' => true,
                'funcionario' => $funcionario,
                'documentos' => $documentos
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar documentos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar documentos'
            ]);
        }
    }

    /**
     * Anexar documentos faltantes na página de funcionários
     */
    public function anexarFaltantes(Request $request, $funcionarioId)
    {
        try {
            if (!Auth::user()->temPermissao('doc_dp')) {
                abort(403);
            }

            $request->validate([
                // 70 MB (em KB)
                'arquivo' => 'required|file|mimes:pdf|max:71680'
            ]);

            // Garante que o funcionário existe
            $existe = DB::table('funcionarios')->where('id', $funcionarioId)->exists();
            if (!$existe) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $ok = $this->processarUploadArquivoBLOB($request->file('arquivo'), (int)$funcionarioId, 'documento unificado');

            if (!$ok) {
                return response()->json(['success' => false, 'message' => 'Falha ao salvar o documento'], 422);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao anexar documento faltante: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao anexar documento'], 500);
        }
    }

    /**
     * Alterar status do funcionário (generalizado)
     */
    public function alterarStatusFuncionario(Request $request, $funcionarioId)
    {
        try {
            // Validação
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:trabalhando,demitido,afastado,ferias'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status inválido: ' . $validator->errors()->first()
                ]);
            }

            // Verificar se o funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            
            if (!$funcionario) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            $novoStatus = $request->status;

            // Verificar se já não está no status solicitado
            if ($funcionario->status === $novoStatus) {
                return response()->json([
                    'success' => false,
                    'message' => "Funcionário já está com status '{$novoStatus}'"
                ]);
            }

            // Atualizar status
            $affected = DB::table('funcionarios')
                ->where('id', $funcionarioId)
                ->update([
                    'status' => $novoStatus,
                    'updated_at' => now()
                ]);

            if ($affected) {
                if (config('app.debug')) {
                    Log::debug('Status do funcionário alterado', [
                        'funcionario_id' => $funcionarioId,
                        'status_novo' => $novoStatus,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => "Status alterado para '{$novoStatus}' com sucesso"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao alterar status do funcionário'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao alterar status do funcionário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Upload de foto do funcionário
     */
    public function uploadFotoFuncionario(Request $request)
    {
        try {
            $request->validate([
                'funcionario_id' => 'required|exists:funcionarios,id',
                'foto' => 'required|image|max:5120' // max 5MB
            ]);

            $funcionarioId = $request->funcionario_id;
            $foto = $request->file('foto');

            // Verificar se o funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            // Diretório base real (public_html na Hostinger)
            $publicHtmlPath = base_path('../public_html');

            // Remover foto antiga se existir
            if ($funcionario->foto_path) {
                $caminhoAntigo = $publicHtmlPath . '/' . $funcionario->foto_path;
                if (file_exists($caminhoAntigo)) {
                    unlink($caminhoAntigo);
                }
            }

            // Criar diretório se não existir
            $diretorio = 'storage/fotos_funcionarios/' . date('Y') . '/' . date('m');
            $caminhoCompleto = $publicHtmlPath . '/' . $diretorio;
            if (!is_dir($caminhoCompleto)) {
                mkdir($caminhoCompleto, 0755, true);
            }

            // Gerar nome único para o arquivo
            $nomeArquivo = 'func_' . $funcionarioId . '_' . time() . '.' . $foto->getClientOriginalExtension();
            
            // Mover arquivo
            $foto->move($caminhoCompleto, $nomeArquivo);
            
            // Caminho relativo para salvar no banco
            $caminhoRelativo = $diretorio . '/' . $nomeArquivo;

            // Log para debug
            Log::info('Upload foto funcionário', [
                'funcionario_id' => $funcionarioId,
                'caminho_completo' => $caminhoCompleto . '/' . $nomeArquivo,
                'caminho_relativo' => $caminhoRelativo,
                'arquivo_existe' => file_exists($caminhoCompleto . '/' . $nomeArquivo)
            ]);

            // Atualizar no banco
            DB::table('funcionarios')
                ->where('id', $funcionarioId)
                ->update([
                    'foto_path' => $caminhoRelativo,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto enviada com sucesso',
                'foto_path' => $caminhoRelativo,
                'foto_url' => url($caminhoRelativo)
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao fazer upload de foto do funcionário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar foto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover foto do funcionário
     */
    public function removerFotoFuncionario(Request $request)
    {
        try {
            $request->validate([
                'funcionario_id' => 'required|exists:funcionarios,id'
            ]);

            $funcionarioId = $request->funcionario_id;

            // Verificar se o funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            // Diretório base real (public_html na Hostinger)
            $publicHtmlPath = base_path('../public_html');

            // Remover arquivo físico se existir
            if ($funcionario->foto_path) {
                $caminhoFoto = $publicHtmlPath . '/' . $funcionario->foto_path;
                if (file_exists($caminhoFoto)) {
                    unlink($caminhoFoto);
                }
            }

            // Atualizar no banco
            DB::table('funcionarios')
                ->where('id', $funcionarioId)
                ->update([
                    'foto_path' => null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto removida com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao remover foto do funcionário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover foto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Demitir funcionário - altera status para 'demitido'
     */
    public function demitirFuncionario(Request $request, $funcionarioId)
    {
        try {
            // Verificar se o funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            
            if (!$funcionario) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            // Verificar se já não está demitido
            if ($funcionario->status === 'demitido') {
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário já está demitido'
                ]);
            }

            // Atualizar status para demitido usando SQL direto (sem migration)
            $affected = DB::table('funcionarios')
                ->where('id', $funcionarioId)
                ->update([
                    'status' => 'demitido',
                    'updated_at' => now()
                ]);

            if ($affected) {
                if (config('app.debug')) {
                    Log::debug('Funcionário demitido', [
                        'funcionario_id' => $funcionarioId,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Funcionário demitido com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar status do funcionário'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao demitir funcionário: ' . $e->getMessage(), [
                'funcionario_id' => $funcionarioId,
                'usuario' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    // ========================================
    // MÉTODOS PARA ATESTADOS
    // ========================================

    /**
     * Listar atestados de um funcionário
     */
    public function listarAtestados($funcionarioId)
    {
        try {
            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $atestados = DB::table('funcionarios_atestados')
                ->select('id', 'tipo_atestado', 'data_atestado', 'data_entrega', 'dias_afastamento', 'observacoes', 'arquivo_nome', 'arquivo_extensao', 'arquivo_tamanho', 'created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('data_atestado', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'atestados' => $atestados
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar atestados: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar atestados']);
        }
    }

    /**
     * Anexar novo atestado
     */
    public function anexarAtestado(Request $request, $funcionarioId)
    {
        try {
            // Validação
            $validator = Validator::make($request->all(), [
                'tipo_atestado' => 'required|string|in:Médico,Odontológico,Psicológico,Fisioterapia,Exame,Outros',
                'data_atestado' => 'required|date',
                'dias_afastamento' => 'nullable|integer|min:0|max:365',
                'observacoes' => 'nullable|string|max:1000',
                // 70 MB (em KB)
                'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:71680'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Dados inválidos: ' . $validator->errors()->first()]);
            }

            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            // Calcular total de dias de atestado no mês atual
            $dataAtestado = $request->data_atestado;
            $mesAno = date('Y-m', strtotime($dataAtestado));
            
            $totalDiasNoMes = DB::table('funcionarios_atestados')
                ->where('funcionario_id', $funcionarioId)
                ->whereRaw("DATE_FORMAT(data_atestado, '%Y-%m') = ?", [$mesAno])
                ->sum('dias_afastamento');
            
            $diasAtestadoAtual = (int)($request->dias_afastamento ?: 0);
            $novoTotalDias = $totalDiasNoMes + $diasAtestadoAtual;



			$arquivo = $request->file('arquivo');
			if (!$arquivo) {
				return response()->json(['success' => false, 'message' => 'Arquivo do atestado é obrigatório'], 422);
			}

			// Resolver dinamicamente a coluna do MIME (arquivo_mime ou arquivo_mime_type)
			try {
				$mimeColumn = \Illuminate\Support\Facades\Schema::hasColumn('funcionarios_atestados', 'arquivo_mime')
					? 'arquivo_mime'
					: (\Illuminate\Support\Facades\Schema::hasColumn('funcionarios_atestados', 'arquivo_mime_type') ? 'arquivo_mime_type' : null);
			} catch (\Throwable $e) {
				$mimeColumn = 'arquivo_mime';
			}

			// OTIMIZAÇÃO: Verificar se pode salvar no storage
			$temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_atestados');

			// Obter informações do arquivo ANTES de mover (importante!)
			$arquivoNome = $arquivo->getClientOriginalName();
			$arquivoExtensao = $arquivo->getClientOriginalExtension();
			$arquivoTamanho = $arquivo->getSize();
			$arquivoMime = $arquivo->getMimeType();

			// Montar payload base
			$fields = [
				'funcionario_id' => $funcionarioId,
				'tipo_atestado' => $request->tipo_atestado,
				'data_atestado' => $request->data_atestado,
				'data_entrega' => now(),
				'dias_afastamento' => $request->dias_afastamento ?: null,
				'observacoes' => $request->observacoes,
				'arquivo_nome' => $arquivoNome,
				'arquivo_extensao' => $arquivoExtensao,
				'arquivo_tamanho' => $arquivoTamanho,
				'usuario_cadastro' => Auth::id(),
				'status' => 'pendente',
				'created_at' => now(),
				'updated_at' => now()
			];
			
			// OTIMIZAÇÃO: Salvar no storage ao invés do banco
			if ($temPathCol) {
				$dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $funcionarioId, 'atestados');
				$fields['arquivo_path'] = $dadosArquivo['path'];
				// Verificar se coluna arquivo_conteudo existe e adicionar valor vazio
				if (Schema::hasColumn('funcionarios_atestados', 'arquivo_conteudo')) {
					$fields['arquivo_conteudo'] = '';
				}
				$hash = hash_file('sha256', storage_path('app/public/' . $dadosArquivo['path']));
			} else {
				$conteudo = file_get_contents($arquivo->getPathname());
				$fields['arquivo_conteudo'] = $conteudo;
				$hash = hash('sha256', $conteudo);
			}
			
			if (!empty($mimeColumn)) {
				$fields[$mimeColumn] = $arquivoMime;
			}
			// Incluir hash somente se a coluna existir
			try {
				if (\Illuminate\Support\Facades\Schema::hasColumn('funcionarios_atestados', 'arquivo_hash')) {
					$fields['arquivo_hash'] = $hash;
				}
			} catch (\Throwable $e) {}

			// Intersectar com colunas existentes para evitar erro de coluna inexistente
			try {
				$cols = \Illuminate\Support\Facades\Schema::getColumnListing('funcionarios_atestados');
				$allowed = array_flip($cols);
				$payload = array_intersect_key($fields, $allowed);
			} catch (\Throwable $e) {
				$payload = $fields; // fallback se não conseguir ler o schema
			}

			// Inserir atestado
			$atestadoId = DB::table('funcionarios_atestados')->insertGetId($payload);

            // Log da ação
            $this->logAcao($funcionarioId, 'atestados', $atestadoId, 'anexou_atestado', 
                "Anexou atestado {$request->tipo_atestado} para {$funcionario->nome}");

            // Verificar se atingiu ou ultrapassou 15 dias no mês
            $avisoLimite = null;
            if ($novoTotalDias >= 15) {
                $avisoLimite = "Funcionário atingiu {$novoTotalDias} dias de atestado no mês " . 
                              date('m/Y', strtotime($dataAtestado)) . 
                              ", favor entrar em contato com o mesmo.";
                

            }

            return response()->json([
                'success' => true, 
                'message' => 'Atestado anexado com sucesso',
                'aviso_limite' => $avisoLimite,
                'total_dias_mes' => $novoTotalDias
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao anexar atestado: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download de atestado (BLOB)
     */
    public function downloadAtestado($atestadoId)
    {
        $atestado = DB::table('funcionarios_atestados')->where('id', $atestadoId)->first();
        
        if (!$atestado) {
            abort(404, 'Atestado não encontrado');
        }

        return ArquivoHelper::download($atestado, 'atestado');
    }

    // ========================================
    // MÉTODOS PARA ADVERTÊNCIAS
    // ========================================

    /**
     * Listar advertências de um funcionário
     */
    public function listarAdvertencias($funcionarioId)
    {
        try {
            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $advertencias = DB::table('funcionarios_advertencias')
                ->select('id', 'tipo_advertencia', 'motivo', 'data_advertencia', 'data_entrega', 'dias_suspensao', 'observacoes', 'arquivo_nome', 'arquivo_extensao', 'arquivo_tamanho', 'created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('data_advertencia', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'advertencias' => $advertencias
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar advertências: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar advertências']);
        }
    }

    /**
     * Aplicar nova advertência
     */
    public function aplicarAdvertencia(Request $request, $funcionarioId)
    {
        try {
            // Validação
            $validator = Validator::make($request->all(), [
                'tipo_advertencia' => 'required|string|in:verbal,escrita,suspensao,ocorrencia',
                'motivo' => 'required|string|max:500',
                'data_advertencia' => 'required|date',
                'dias_suspensao' => 'nullable|integer|min:1|max:30|required_if:tipo_advertencia,suspensao',
                'observacoes' => 'nullable|string|max:1000',
                // 70 MB (em KB)
                'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:71680'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Dados inválidos: ' . $validator->errors()->first()]);
            }

            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $arquivo = $request->file('arquivo');
            if (!$arquivo) {
                return response()->json(['success' => false, 'message' => 'Arquivo da advertência é obrigatório'], 422);
            }
            $conteudo = file_get_contents($arquivo->getPathname());
            $hash = hash('sha256', $conteudo);
            
            // Guardar MIME type ANTES de mover o arquivo (evita erro após move)
            $arquivoMimeType = $arquivo->getMimeType();

            $diasSuspensao = ($request->tipo_advertencia === 'suspensao') ? ($request->dias_suspensao ?? null) : null;

            // Normalizar data (aceitar dd/mm/aaaa ou yyyy-mm-dd)
            $dataAdvertencia = $request->data_advertencia;
            if (is_string($dataAdvertencia) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataAdvertencia)) {
                [$d,$m,$y] = explode('/', $dataAdvertencia);
                $dataAdvertencia = sprintf('%04d-%02d-%02d', (int)$y, (int)$m, (int)$d);
            }

            // Resolver nome da coluna do MIME conforme o schema (arquivo_mime ou arquivo_mime_type)
            try {
                $mimeColumn = \Illuminate\Support\Facades\Schema::hasColumn('funcionarios_advertencias', 'arquivo_mime')
                    ? 'arquivo_mime'
                    : (\Illuminate\Support\Facades\Schema::hasColumn('funcionarios_advertencias', 'arquivo_mime_type') ? 'arquivo_mime_type' : 'arquivo_mime');
            } catch (\Throwable $e) {
                $mimeColumn = 'arquivo_mime';
            }

            // Alguns bancos usam ENUM limitado (verbal, escrita, suspensao). Para evitar erro 500,
            // mapeamos 'ocorrencia' para 'escrita' e preservamos a intenção nas observações.
            $tipoOriginal = strtolower(trim($request->tipo_advertencia));
            $tipoSalvar = $tipoOriginal; // DB já aceita 'ocorrencia'
            $observacoes = $request->observacoes;

            // OTIMIZAÇÃO: Verificar se pode salvar no storage
            $temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_advertencias');

            // Inserir advertência
            $fields = [
                'funcionario_id' => $funcionarioId,
                'tipo_advertencia' => $tipoSalvar,
                'motivo' => $request->motivo,
                'data_advertencia' => $dataAdvertencia,
                'data_entrega' => now(),
                'dias_suspensao' => $diasSuspensao,
                'observacoes' => $observacoes,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_extensao' => $arquivo->getClientOriginalExtension(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'usuario_cadastro' => Auth::id(),
                'status' => 'ativa',
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // OTIMIZAÇÃO: Salvar no storage ao invés do banco
            if ($temPathCol) {
                $dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $funcionarioId, 'advertencias');
                $fields['arquivo_path'] = $dadosArquivo['path'];
                // Verificar se coluna arquivo_conteudo existe e adicionar valor vazio
                if (Schema::hasColumn('funcionarios_advertencias', 'arquivo_conteudo')) {
                    $fields['arquivo_conteudo'] = '';
                }
                $hash = hash_file('sha256', storage_path('app/public/' . $dadosArquivo['path']));
            } else {
                $fields['arquivo_conteudo'] = $conteudo;
            }
            
            if (!empty($mimeColumn)) {
                $fields[$mimeColumn] = $arquivoMimeType;
            }
            // Incluir hash somente se existir a coluna
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('funcionarios_advertencias', 'arquivo_hash')) {
                    $fields['arquivo_hash'] = $hash;
                }
            } catch (\Throwable $e) {}

            // Filtrar campos para apenas os existentes na tabela
            try {
                $cols = \Illuminate\Support\Facades\Schema::getColumnListing('funcionarios_advertencias');
                $allowed = array_flip($cols);
                $payload = array_intersect_key($fields, $allowed);
            } catch (\Throwable $e) {
                $payload = $fields; // fallback
            }

            $advertenciaId = DB::table('funcionarios_advertencias')->insertGetId($payload);

            // Log da ação
            $this->logAcao($funcionarioId, 'advertencias', $advertenciaId, 'aplicou_advertencia', 
                "Aplicou advertência {$request->tipo_advertencia} para {$funcionario->nome}: {$request->motivo}");

            return response()->json(['success' => true, 'message' => 'Advertência aplicada com sucesso']);

        } catch (\Exception $e) {
            Log::error('Erro ao aplicar advertência: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Download de advertência (BLOB)
     */
    public function downloadAdvertencia($advertenciaId)
    {
        $advertencia = DB::table('funcionarios_advertencias')->where('id', $advertenciaId)->first();
        
        if (!$advertencia) {
            abort(404, 'Advertência não encontrada');
        }

        return ArquivoHelper::download($advertencia, 'advertência');
    }

    // ========================================
    // MÉTODOS PARA EPIs
    // ========================================

    /**
     * Listar materiais retirados pelo funcionário (EPI/Equipamentos)
     */
    public function listarEpis($funcionarioId)
    {
        try {
            // Verificar se o funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['error' => 'Funcionário não encontrado'], 404);
            }

            // Buscar todas as baixas do funcionário
            $todasBaixas = DB::table('baixas as b')
                ->leftJoin('estoque as e', 'b.produto_id', '=', 'e.id')
                ->leftJoin('centro_custo as cc', 'b.centro_custo_id', '=', 'cc.id')
                ->leftJoin('users as u', 'b.usuario_id', '=', 'u.id')
                ->where('b.funcionario_id', $funcionarioId)
                ->select(
                    'b.id',
                    'b.data_baixa',
                    'b.quantidade',
                    'b.observacoes',
                    'b.centro_custo_id',
                    'b.usuario_id',
                    'e.nome as produto_nome',
                    'cc.nome as centro_custo_nome',
                    'u.name as usuario_entrega'
                )
                ->orderBy('b.data_baixa', 'desc')
                ->get();

            // Agrupar por lançamento (mesmo datetime + centro_custo + usuario + observacoes)
            $lancamentosAgrupados = [];
            foreach ($todasBaixas as $baixa) {
                $chave = $baixa->data_baixa . '_' . $baixa->centro_custo_id . '_' . $baixa->usuario_id . '_' . md5($baixa->observacoes ?? '');
                
                if (!isset($lancamentosAgrupados[$chave])) {
                    $lancamentosAgrupados[$chave] = [
                        'id' => $baixa->id,
                        'data_baixa' => $baixa->data_baixa,
                        'observacoes' => $baixa->observacoes,
                        'centro_custo_nome' => $baixa->centro_custo_nome,
                        'usuario_entrega' => $baixa->usuario_entrega,
                        'produtos' => [],
                        'total_quantidade' => 0
                    ];
                }
                
                $lancamentosAgrupados[$chave]['produtos'][] = [
                    'produto_nome' => $baixa->produto_nome,
                    'quantidade' => $baixa->quantidade
                ];
                $lancamentosAgrupados[$chave]['total_quantidade'] += $baixa->quantidade;
            }

            // Converter para array indexado e ordenar por data
            $materiaisAgrupados = array_values($lancamentosAgrupados);
            usort($materiaisAgrupados, function($a, $b) {
                return strtotime($b['data_baixa']) - strtotime($a['data_baixa']);
            });

            return response()->json($materiaisAgrupados);
        } catch (\Exception $e) {
            Log::error('Erro ao listar materiais do funcionário: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }





    // ========================================
    // MÉTODO AUXILIAR PARA LOGS
    // ========================================

    /**
     * Registrar log de ação
     */
    private function logAcao($funcionarioId, $tabelaOrigem, $registroId, $acao, $descricao, $dadosAnteriores = null, $dadosNovos = null)
    {
        try {
            DB::table('funcionarios_logs')->insert([
                'funcionario_id' => $funcionarioId,
                'tabela_origem' => $tabelaOrigem,
                'registro_id' => $registroId,
                'acao' => $acao,
                'descricao' => $descricao,
                'dados_anteriores' => $dadosAnteriores ? json_encode($dadosAnteriores) : null,
                'dados_novos' => $dadosNovos ? json_encode($dadosNovos) : null,
                'usuario_id' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar log: ' . $e->getMessage());
        }
    }

    // ========================================
    // MÉTODOS PARA CONTRA CHEQUES
    // ========================================
    
    public function listarContraCheques($funcionarioId)
    {
        try {
            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $contraCheques = DB::table('funcionarios_contra_cheques')
                ->select('id', 'mes_referencia', 'observacoes', 'arquivo_nome', 'arquivo_tamanho', 'created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'contraCheques' => $contraCheques
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar contra cheques: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar contra cheques']);
        }
    }

    public function storeContraCheque(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'funcionario_id' => 'required|exists:funcionarios,id',
                'mes_referencia' => 'required|date_format:Y-m',
                'arquivo' => 'required|file|mimes:pdf|max:10240',
                'observacoes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados invalidos: ' . $validator->errors()->first()
                ], 422, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }

            $arquivo = $request->file('arquivo');
            
            // OTIMIZAÇÃO: Verificar se pode salvar no storage
            $temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_contra_cheques');
            
            $dados = [
                'funcionario_id' => $request->funcionario_id,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_mime' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'mes_referencia' => $request->mes_referencia,
                'observacoes' => $request->observacoes,
                'usuario_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if ($temPathCol) {
                $dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $request->funcionario_id, 'contra_cheques');
                $dados['arquivo_path'] = $dadosArquivo['path'];
                // Verificar se coluna arquivo_conteudo existe e adicionar valor vazio
                if (Schema::hasColumn('funcionarios_contra_cheques', 'arquivo_conteudo')) {
                    $dados['arquivo_conteudo'] = '';
                }
            } else {
                $dados['arquivo_conteudo'] = file_get_contents($arquivo->getRealPath());
            }

            DB::table('funcionarios_contra_cheques')->insert($dados);

            return response()->json(['success' => true, 'message' => 'Contra cheque anexado com sucesso']);
        } catch (\Exception $e) {
            Log::error('Erro ao anexar contra cheque: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
        }
    }

    public function downloadContraCheque($id)
    {
        $contraCheque = DB::table('funcionarios_contra_cheques')->where('id', $id)->first();
        
        if (!$contraCheque) {
            abort(404, 'Contra cheque não encontrado');
        }

        return ArquivoHelper::download($contraCheque, 'contra cheque');
    }

    // ========================================
    // MÉTODOS PARA FÉRIAS
    // ========================================
    
    public function listarFerias($funcionarioId)
    {
        try {
            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $ferias = DB::table('funcionarios_ferias')
                ->select('id', 'periodo_inicio', 'periodo_fim', 'ano_exercicio', 'observacoes', 'arquivo_nome', 'arquivo_tamanho', 'created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'ferias' => $ferias
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar férias: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar férias']);
        }
    }

    public function storeFerias(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'funcionario_id' => 'required|exists:funcionarios,id',
                'periodo_inicio' => 'required|date',
                'periodo_fim' => 'required|date|after_or_equal:periodo_inicio',
                'ano_exercicio' => 'required|integer|min:1996|max:2030',
                'arquivo' => 'required|file|mimes:pdf|max:10240',
                'observacoes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos: ' . $validator->errors()->first()
                ], 422);
            }

            $arquivo = $request->file('arquivo');
            
            // OTIMIZAÇÃO: Verificar se pode salvar no storage
            $temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_ferias');
            
            $dados = [
                'funcionario_id' => $request->funcionario_id,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_mime' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'periodo_inicio' => $request->periodo_inicio,
                'periodo_fim' => $request->periodo_fim,
                'ano_exercicio' => $request->ano_exercicio,
                'observacoes' => $request->observacoes,
                'usuario_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if ($temPathCol) {
                $dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $request->funcionario_id, 'ferias');
                $dados['arquivo_path'] = $dadosArquivo['path'];
                // Verificar se coluna arquivo_conteudo existe e adicionar valor vazio
                if (Schema::hasColumn('funcionarios_ferias', 'arquivo_conteudo')) {
                    $dados['arquivo_conteudo'] = '';
                }
            } else {
                $dados['arquivo_conteudo'] = file_get_contents($arquivo->getRealPath());
            }

            DB::table('funcionarios_ferias')->insert($dados);

            return response()->json(['success' => true, 'message' => 'Férias anexadas com sucesso']);
        } catch (\Exception $e) {
            Log::error('Erro ao anexar férias: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
        }
    }

    public function downloadFerias($id)
    {
        $ferias = DB::table('funcionarios_ferias')->where('id', $id)->first();
        
        if (!$ferias) {
            abort(404, 'Férias não encontradas');
        }

        return ArquivoHelper::download($ferias, 'férias');
    }

    // ========================================
    // MÉTODOS PARA DÉCIMO TERCEIRO
    // ========================================
    
    public function listarDecimo($funcionarioId)
    {
        try {
            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $decimo = DB::table('funcionarios_decimo')
                ->select('id', 'ano_referencia', 'parcela', 'valor_bruto', 'observacoes', 'arquivo_nome', 'arquivo_tamanho', 'created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'decimo' => $decimo
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar décimo terceiro: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar décimo terceiro']);
        }
    }

    public function storeDecimo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'funcionario_id' => 'required|exists:funcionarios,id',
                'ano_referencia' => 'required|integer|min:1996|max:2030',
                'parcela' => 'required|in:1,2,unica',
                // Validaremos e normalizaremos manualmente para aceitar formatos pt-BR e en-US
                'valor_bruto' => 'nullable',
                'arquivo' => 'required|file|mimes:pdf|max:10240',
                'observacoes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos: ' . $validator->errors()->first()
                ], 422);
            }

            // Normalizar valor (ex.: "2.500,00" -> 2500.00). Se já vier como "2500.00", manter.
            $valorBrutoNormalizado = null;
            if ($request->filled('valor_bruto')) {
                $valorStr = (string) $request->input('valor_bruto');
                if (strpos($valorStr, ',') !== false) {
                    // possui separador brasileiro -> converte
                    $valorStr = str_replace('.', '', $valorStr);
                    $valorStr = str_replace(',', '.', $valorStr);
                }
                $valorBrutoNormalizado = is_numeric($valorStr) ? (float) $valorStr : null;
            }

            $arquivo = $request->file('arquivo');

            // Evitar duplicidade: um registro por funcionario+ano+parcela
            $jaExiste = DB::table('funcionarios_decimo')
                ->where('funcionario_id', $request->funcionario_id)
                ->where('ano_referencia', $request->ano_referencia)
                ->where('parcela', $request->parcela)
                ->exists();

            if ($jaExiste) {
                return response()->json(
                    ['success' => false, 'message' => 'Ja existe um registro de decimo para este funcionario, ano e parcela.'],
                    409,
                    [],
                    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
                );
            }

            // OTIMIZAÇÃO: Verificar se pode salvar no storage
            $temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_decimo');
            
            $dados = [
                'funcionario_id' => $request->funcionario_id,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_mime' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'ano_referencia' => $request->ano_referencia,
                'parcela' => $request->parcela,
                'valor_bruto' => $valorBrutoNormalizado,
                'observacoes' => $request->observacoes,
                'usuario_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if ($temPathCol) {
                $dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $request->funcionario_id, 'decimo');
                $dados['arquivo_path'] = $dadosArquivo['path'];
                // Verificar se coluna arquivo_conteudo existe e adicionar valor vazio
                if (Schema::hasColumn('funcionarios_decimo', 'arquivo_conteudo')) {
                    $dados['arquivo_conteudo'] = '';
                }
            } else {
                $dados['arquivo_conteudo'] = file_get_contents($arquivo->getRealPath());
            }

            DB::table('funcionarios_decimo')->insert($dados);

            return response()->json(
                ['success' => true, 'message' => 'Decimo terceiro anexado com sucesso'],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (\Throwable $e) {
            Log::error('Erro ao anexar decimo terceiro: ' . $e->getMessage());
            return response()->json(
                ['success' => false, 'message' => 'Erro ao anexar decimo: ' . $e->getMessage()],
                500,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }
    }

    public function downloadDecimo($id)
    {
        $decimo = DB::table('funcionarios_decimo')->where('id', $id)->first();
        
        if (!$decimo) {
            abort(404, 'Décimo terceiro não encontrado');
        }

        return ArquivoHelper::download($decimo, 'décimo terceiro');
    }

    // ========================================
    // MÉTODOS PARA RESCISÃO
    // ========================================
    
    public function listarRescisao($funcionarioId)
    {
        try {
            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $rescisao = DB::table('funcionarios_rescisao')
                ->select('id', 'data_rescisao', 'tipo_rescisao', 'valor_total', 'observacoes', 'arquivo_nome', 'arquivo_tamanho', 'created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'rescisao' => $rescisao
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar rescisão: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar rescisão']);
        }
    }

    public function storeRescisao(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'funcionario_id' => 'required|exists:funcionarios,id',
                'data_rescisao' => 'required|date',
                'tipo_rescisao' => 'required|in:demissao_sem_justa_causa,demissao_justa_causa,pedido_demissao,acordo_mutuo,aposentadoria,fim_contrato,outros',
                // Validaremos e normalizaremos manualmente para aceitar formatos pt-BR e en-US
                'valor_total' => 'nullable',
                // Aumentado para 50MB
                'arquivo' => 'required|file|mimes:pdf|max:51200',
                'observacoes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados invalidos: ' . $validator->errors()->first()
                ], 422, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }

            // Normalizar valor (ex.: "6.000,00" -> 6000.00). Se já vier "6000.00", manter.
            $valorTotalNormalizado = null;
            if ($request->filled('valor_total')) {
                $valorStr = (string) $request->input('valor_total');
                if (strpos($valorStr, ',') !== false) {
                    $valorStr = str_replace('.', '', $valorStr);
                    $valorStr = str_replace(',', '.', $valorStr);
                }
                $valorTotalNormalizado = is_numeric($valorStr) ? (float) $valorStr : null;
            }

            $arquivo = $request->file('arquivo');
            
            // OTIMIZAÇÃO: Salvar no storage ao invés do banco
            $temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_rescisao');
            
            $dados = [
                'funcionario_id' => $request->funcionario_id,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_mime' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'data_rescisao' => $request->data_rescisao,
                'tipo_rescisao' => $request->tipo_rescisao,
                'valor_total' => $valorTotalNormalizado,
                'observacoes' => $request->observacoes,
                'usuario_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if ($temPathCol) {
                // Salvar apenas no storage (OTIMIZADO)
                $dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $request->funcionario_id, 'rescisao');
                $dados['arquivo_path'] = $dadosArquivo['path'];
                // Verificar se coluna arquivo_conteudo existe e adicionar valor vazio
                if (Schema::hasColumn('funcionarios_rescisao', 'arquivo_conteudo')) {
                    $dados['arquivo_conteudo'] = '';
                }
            } else {
                // Fallback: salvar no banco (tabela sem coluna arquivo_path)
                $dados['arquivo_conteudo'] = file_get_contents($arquivo->getRealPath());
            }

            DB::table('funcionarios_rescisao')->insert($dados);

            return response()->json(
                ['success' => true, 'message' => 'Rescisao anexada com sucesso'],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (\Throwable $e) {
            Log::error('Erro ao anexar rescisao: ' . $e->getMessage());
            return response()->json(
                ['success' => false, 'message' => 'Erro ao anexar rescisao: ' . $e->getMessage()],
                500,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }
    }

    public function downloadRescisao($id)
    {
        $rescisao = DB::table('funcionarios_rescisao')->where('id', $id)->first();
        
        if (!$rescisao) {
            abort(404, 'Rescisão não encontrada');
        }

        return ArquivoHelper::download($rescisao, 'rescisão');
    }

    /**
     * Visualizar todos os documentos do funcionário como um PDF único
     */
    public function visualizarPdfCompleto($funcionarioId)
    {
        try {
            // Verificar se o funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response('<h1>Funcionário não encontrado</h1>', 404);
            }

            // Coletar TODOS os PDFs do funcionário
            $todosPdfs = [];

            // 1. Documentos gerais (funcionarios_documentos)
            $docs = DB::table('funcionarios_documentos')
                ->where('funcionario_id', $funcionarioId)
                ->where('arquivo_mime_type', 'application/pdf')
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $todosPdfs[] = [
                    'categoria' => 'Documentos Gerais',
                    'nome' => $doc->tipo_documento,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 2. Atestados
            $docs = DB::table('funcionarios_atestados')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $todosPdfs[] = [
                    'categoria' => 'Atestados',
                    'nome' => 'Atestado - ' . (isset($doc->data_atestado) ? date('d/m/Y', strtotime($doc->data_atestado)) : 'Data não informada'),
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null
                ];
            }

            // 3. Advertências
            $docs = DB::table('funcionarios_advertencias')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $todosPdfs[] = [
                    'categoria' => 'Advertências',
                    'nome' => 'Advertência - ' . (isset($doc->data_advertencia) ? date('d/m/Y', strtotime($doc->data_advertencia)) : 'Data não informada'),
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null
                ];
            }

            // 4. Décimo terceiro
            $docs = DB::table('funcionarios_decimo')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $todosPdfs[] = [
                    'categoria' => 'Décimo Terceiro',
                    'nome' => 'Décimo Terceiro - ' . (isset($doc->ano_referencia) ? $doc->ano_referencia : (isset($doc->ano) ? $doc->ano : 'Ano não informado')),
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null
                ];
            }

            // 5. Rescisão
            $docs = DB::table('funcionarios_rescisao')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $todosPdfs[] = [
                    'categoria' => 'Rescisão',
                    'nome' => 'Rescisão - ' . (isset($doc->data_rescisao) ? date('d/m/Y', strtotime($doc->data_rescisao)) : 'Data não informada'),
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null
                ];
            }

            // 6. Contra-cheques
            $docs = DB::table('funcionarios_contra_cheques')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $mesAno = 'Período não informado';
                if (isset($doc->mes_ano)) {
                    $mesAno = $doc->mes_ano;
                } elseif (isset($doc->mes) && isset($doc->ano)) {
                    $mesAno = $doc->mes . '/' . $doc->ano;
                }
                
                $todosPdfs[] = [
                    'categoria' => 'Contra-cheques',
                    'nome' => 'Contra-cheque - ' . $mesAno,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null
                ];
            }

            // 7. Férias
            $docs = DB::table('funcionarios_ferias')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $todosPdfs[] = [
                    'categoria' => 'Férias',
                    'nome' => 'Férias - ' . (isset($doc->periodo_inicio) ? date('d/m/Y', strtotime($doc->periodo_inicio)) : 'Data não informada'),
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null
                ];
            }

            // 8. Frequência
            $docs = DB::table('funcionarios_frequencia')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $todosPdfs[] = [
                    'categoria' => 'Frequência',
                    'nome' => 'Frequência - ' . (isset($doc->mes_ano) ? $doc->mes_ano : 'Mês não informado'),
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null
                ];
            }

            // 9. Certificados
            $docs = DB::table('funcionarios_certificados')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $nomeDoc = isset($doc->nome_certificado) ? $doc->nome_certificado : 'Certificado';
                $dataEmissao = isset($doc->data_emissao) ? date('d/m/Y', strtotime($doc->data_emissao)) : '';
                
                $todosPdfs[] = [
                    'categoria' => 'Certificados',
                    'nome' => $nomeDoc . ($dataEmissao ? ' - ' . $dataEmissao : ''),
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null
                ];
            }

            // 10. ASOS
            $docs = DB::table('funcionarios_asos')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();
            
            foreach ($docs as $doc) {
                $tipoTexto = [
                    'admissional' => 'Admissional',
                    'periodico' => 'Periódico',
                    'mudanca_funcao' => 'Mudança de Função',
                    'retorno_trabalho' => 'Retorno ao Trabalho',
                    'demissional' => 'Demissional'
                ];
                
                $tipo = isset($tipoTexto[$doc->tipo_exame]) ? $tipoTexto[$doc->tipo_exame] 
                    : 'ASOS';
                
                $dataExame = isset($doc->data_exame) ? date('d/m/Y', strtotime($doc->data_exame)) : '';
                
                $todosPdfs[] = [
                    'categoria' => 'ASOS',
                    'nome' => 'ASOS ' . $tipo . ($dataExame ? ' - ' . $dataExame : ''),
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null
                ];
            }

            // Verificar se encontrou PDFs
            if (empty($todosPdfs)) {
                return response('<h1 style="text-align: center; color: #666; margin-top: 50px;">Nenhum documento PDF encontrado para este funcionário</h1>', 200);
            }

            // Tentativa principal: unificar todos os PDFs em um único arquivo e exibir inline
            $pdfUnico = $this->criarPdfUnificado($todosPdfs, $funcionario);
            if (!empty($pdfUnico)) {
                $nomeArquivo = 'Documentos_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $funcionario->nome) . '.pdf';
                return response($pdfUnico, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $nomeArquivo . '"',
                ]);
            }

            // Fallback: visualizar tudo junto como HTML, incorporando cada PDF em sequência
            $html = '<!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Documentos de ' . $funcionario->nome . '</title>
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        min-height: 100vh;
                        line-height: 1.6;
                    }
                    
                    .header {
                        background: rgba(255,255,255,0.95);
                        backdrop-filter: blur(10px);
                        padding: 25px 20px;
                        text-align: center;
                        position: sticky;
                        top: 0;
                        z-index: 1000;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                        border-bottom: 3px solid #007bff;
                    }
                    
                    .header h1 {
                        color: #2c3e50;
                        font-size: 2.2rem;
                        margin-bottom: 10px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 15px;
                    }
                    
                    .header .icon {
                        color: #007bff;
                        font-size: 2.5rem;
                    }
                    
                    .info-card {
                        background: rgba(255,255,255,0.9);
                        margin: 20px auto;
                        max-width: 1200px;
                        padding: 20px;
                        border-radius: 12px;
                        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 15px;
                    }
                    
                    .info-item {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        color: #34495e;
                        font-weight: 500;
                    }
                    
                    .info-item i {
                        color: #007bff;
                        width: 20px;
                    }
                    
                    .total-badge {
                        background: linear-gradient(45deg, #28a745, #20c997);
                        color: white;
                        padding: 12px 25px;
                        border-radius: 25px;
                        font-weight: bold;
                        box-shadow: 0 4px 15px rgba(40,167,69,0.3);
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }
                    
                    .container {
                        max-width: 1200px;
                        margin: 0 auto;
                        padding: 0 20px 40px;
                    }
                    
                    .documento {
                        background: white;
                        margin: 30px 0;
                        border-radius: 15px;
                        overflow: hidden;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                        transition: transform 0.3s ease, box-shadow 0.3s ease;
                    }
                    
                    .documento:hover {
                        transform: translateY(-5px);
                        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
                    }
                    
                    .meta {
                        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                        padding: 20px;
                        border-bottom: 1px solid #dee2e6;
                    }
                    
                    .categoria {
                        color: #007bff;
                        font-size: 1.3rem;
                        font-weight: 700;
                        margin-bottom: 8px;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    
                    .categoria::before {
                        content: "\\f15b";
                        font-family: "Font Awesome 6 Free";
                        font-weight: 900;
                    }
                    
                    .nome {
                        color: #495057;
                        font-size: 1.1rem;
                        margin-bottom: 5px;
                        font-weight: 500;
                    }
                    
                    .data {
                        color: #6c757d;
                        font-size: 0.95rem;
                        display: flex;
                        align-items: center;
                        gap: 5px;
                    }
                    
                    .data::before {
                        content: "\\f017";
                        font-family: "Font Awesome 6 Free";
                        font-weight: 900;
                        color: #007bff;
                    }
                    
                    .viewer {
                        width: 100%;
                        height: 700px;
                        border: none;
                        display: block;
                        background: #f8f9fa;
                    }
                    
                    .loading {
                        text-align: center;
                        padding: 50px;
                        color: #6c757d;
                        font-style: italic;
                    }
                    
                    @media (max-width: 768px) {
                        .header h1 { font-size: 1.8rem; }
                        .info-card { flex-direction: column; text-align: center; }
                        .viewer { height: 500px; }
                        .container { padding: 0 15px 30px; }
                    }
                    
                    .fade-in {
                        animation: fadeIn 0.8s ease-in;
                    }
                    
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(20px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                </style>
            </head>
            <body>
                <div class="header fade-in">
                    <h1><i class="fas fa-file-alt icon"></i>Documentos de ' . $funcionario->nome . '</h1>
                </div>
                
                <div class="container">
                    <div class="info-card fade-in">
                        <div class="info-item">
                            <i class="fas fa-id-card"></i>
                            <span><strong>CPF:</strong> ' . ($funcionario->cpf ?? 'Não informado') . '</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-briefcase"></i>
                            <span><strong>Função:</strong> ' . ($funcionario->funcao ?? 'Não informado') . '</span>
                        </div>
                        <div class="total-badge">
                            <i class="fas fa-file-pdf"></i>
                            <span>' . count($todosPdfs) . ' documento(s) encontrado(s)</span>
                        </div>
                    </div>';

            foreach ($todosPdfs as $index => $pdf) {
                $dataFormatada = $pdf['data'] ? date('d/m/Y H:i', strtotime($pdf['data'])) : 'Data não informada';
                $src = 'data:application/pdf;base64,' . base64_encode($pdf['conteudo']);
                $html .= '
                    <div class="documento fade-in" style="animation-delay: ' . ($index * 0.1) . 's;">
                        <div class="meta">
                            <div class="categoria">' . htmlspecialchars($pdf['categoria'], ENT_QUOTES, 'UTF-8') . '</div>
                            <div class="nome">' . htmlspecialchars($pdf['nome'], ENT_QUOTES, 'UTF-8') . '</div>
                            <div class="data">Cadastrado em: ' . $dataFormatada . '</div>
                        </div>
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i> Carregando documento...
                        </div>
                        <iframe class="viewer" src="' . $src . '" onload="this.previousElementSibling.style.display=\'none\'"></iframe>
                    </div>';
            }

            $html .= '
                </div>
                
                <script>
                    // Smooth scroll para navegação
                    document.addEventListener("DOMContentLoaded", function() {
                        const documentos = document.querySelectorAll(".documento");
                        documentos.forEach((doc, index) => {
                            setTimeout(() => {
                                doc.style.opacity = "1";
                                doc.style.transform = "translateY(0)";
                            }, index * 100);
                        });
                    });
                </script>
            </body>
            </html>';

            // Log da ação (somente em debug, sem PII)
            if (config('app.debug')) {
                Log::debug('Lista de PDFs visualizada para funcionário', [
                    'funcionario_id' => $funcionarioId,
                    'total_pdfs' => count($todosPdfs),
                ]);
            }

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=utf-8'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao visualizar PDFs do funcionário: ' . $e->getMessage());
            return response('<h1>Erro interno do servidor: ' . $e->getMessage() . '</h1>', 500);
        }
    }

    /**
     * Criar PDF unificado com todos os documentos do funcionário
     */
    private function criarPdfUnificado($todosPdfs, $funcionario)
    {
        try {
            // 0) Tentar via Imagick (nativo) – une páginas de todos os PDFs em um único PDF
            $pdfViaImagick = $this->tentarUnificarComImagick($todosPdfs);
            if ($pdfViaImagick !== null) {
                return $pdfViaImagick;
            }

            // Se só há 1 PDF, retornar diretamente
            if (count($todosPdfs) === 1) {
                return $todosPdfs[0]['conteudo'];
            }

            // Usar TCPDF + FPDI (quando disponível) para importar páginas de PDFs
            // Classes são carregadas via autoload do Composer; evitar require manual
            $hasTcpdf = class_exists('TCPDF');
            $hasFpdi  = class_exists('\\setasign\\Fpdi\\Tcpdf\\Fpdi');

            if (!$hasTcpdf) {
                // Sem TCPDF não há como importar páginas. Cai para fallback simples.
                return $this->concatenarPdfsSimples($todosPdfs, $funcionario);
            }

            // Criar PDF base – se FPDI existir usamos ele (tem importPage), senão TCPDF puro
            $pdf = $hasFpdi
                ? new \setasign\Fpdi\Tcpdf\Fpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false)
                : new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Configurações do PDF
            $pdf->SetCreator('SIGO');
            $pdf->SetAuthor('SIGO');
            $pdf->SetTitle('Documentos Completos - ' . $funcionario->nome);
            $pdf->SetSubject('Documentos do Funcionário');
            
            // Remover cabeçalho e rodapé padrão
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Configurar margens
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(TRUE, 10);
            
            // Página de capa
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->Cell(0, 15, 'DOCUMENTOS COMPLETOS', 0, 1, 'C');
            
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, $funcionario->nome, 0, 1, 'C');
            
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 8, 'CPF: ' . ($funcionario->cpf ?? 'Não informado'), 0, 1, 'C');
            $pdf->Cell(0, 8, 'Função: ' . ($funcionario->funcao ?? 'Não informado'), 0, 1, 'C');
            $pdf->Cell(0, 8, 'Data de Geração: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
            
            $pdf->Ln(10);
            
            // Lista de documentos na capa
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 8, 'DOCUMENTOS INCLUÍDOS:', 0, 1, 'L');
            $pdf->Ln(5);
            
            $pdf->SetFont('helvetica', '', 10);
            $categoriaAtual = '';
            foreach ($todosPdfs as $index => $doc) {
                if ($doc['categoria'] !== $categoriaAtual) {
                    $categoriaAtual = $doc['categoria'];
                    $pdf->SetFont('helvetica', 'B', 11);
                    $pdf->Cell(0, 6, $categoriaAtual . ':', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 10);
                }
                $pdf->Cell(0, 5, '   • ' . $doc['nome'], 0, 1, 'L');
            }
            
            if ($hasFpdi) {
                // Importação real das páginas (melhor qualidade)
                foreach ($todosPdfs as $index => $doc) {
                    try {
                        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_temp_');
                        file_put_contents($tempFile, $doc['conteudo']);
                        $pageCount = $pdf->setSourceFile($tempFile);
                        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                            $pdf->AddPage();
                            $tplId = $pdf->importPage($pageNo);
                            $pdf->useTemplate($tplId, 0, 0, 210);
                        }
                        @unlink($tempFile);
                    } catch (\Throwable $e) {
                        $pdf->AddPage();
                        $pdf->SetFont('helvetica', 'B', 14);
                        $pdf->Cell(0, 10, 'ERRO AO IMPORTAR DOCUMENTO', 0, 1, 'C');
                        $pdf->SetFont('helvetica', '', 12);
                        $pdf->Cell(0, 8, $doc['categoria'] . ': ' . $doc['nome'], 0, 1, 'C');
                        $pdf->MultiCell(0, 8, 'Erro: ' . $e->getMessage(), 0, 'C');
                    }
                }
            } else {
                // Sem FPDI não conseguimos importar páginas. Cai para fallback com Imagick
                $pdfViaImagick = $this->tentarUnificarComImagick($todosPdfs);
                if ($pdfViaImagick !== null) {
                    return $pdfViaImagick;
                }
                // Por fim, retorna a versão simples (lista) para não mostrar apenas o primeiro PDF
                return $this->concatenarPdfsSimples($todosPdfs, $funcionario);
            }
            
            return $pdf->Output('', 'S'); // Retornar como string
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar PDF unificado: ' . $e->getMessage());
            // Fallback: concatenação simples
            return $this->concatenarPdfsSimples($todosPdfs, $funcionario);
        }
    }

    /**
     * Une múltiplos PDFs usando a extensão Imagick (quando disponível).
     * Retorna o binário do PDF final ou null caso não seja possível usar Imagick.
     */
    private function tentarUnificarComImagick(array $todosPdfs): ?string
    {
        try {
            if (!extension_loaded('imagick')) {
                return null;
            }

            // Alguns ambientes precisam do Ghostscript habilitado no servidor para ler PDFs via Imagick
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->setCompressionQuality(90);

            foreach ($todosPdfs as $doc) {
                // Salva conteúdo em arquivo temporário
                $tempPdf = tempnam(sys_get_temp_dir(), 'merge_pdf_');
                file_put_contents($tempPdf, $doc['conteudo']);

                $docImagick = new \Imagick();
                $docImagick->setResolution(150, 150);
                // Lê todas as páginas do PDF
                $docImagick->readImage($tempPdf);

                foreach ($docImagick as $page) {
                    $page->setImageFormat('pdf');
                    // Normaliza tamanho para A4 mantendo proporção (opcional, seguro para páginas diferentes)
                    $page->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
                    $imagick->addImage($page);
                }

                // Limpa
                $docImagick->clear();
                $docImagick->destroy();
                @unlink($tempPdf);
            }

            if ($imagick->getNumberImages() === 0) {
                $imagick->clear();
                $imagick->destroy();
                return null;
            }

            $imagick->setImageFormat('pdf');
            // getImagesBlob gera um único blob contendo todas as páginas
            $blob = $imagick->getImagesBlob();
            $imagick->clear();
            $imagick->destroy();
            return $blob ?: null;
        } catch (\Throwable $e) {
            // Se falhar (ex.: Ghostscript indisponível), continuar com outras abordagens
            \Log::warning('Falha ao unificar PDFs com Imagick: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Concatenar PDFs de forma simples (fallback)
     */
    private function concatenarPdfsSimples($todosPdfs, $funcionario)
    {
        try {
            // Se há apenas 1 PDF, retornar diretamente
            if (count($todosPdfs) === 1) {
                return $todosPdfs[0]['conteudo'];
            }

            // Criar um PDF simples com HTML usando DomPDF
            $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Documentos - ' . $funcionario->nome . '</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .titulo { font-size: 24px; font-weight: bold; color: #333; }
                    .subtitulo { font-size: 16px; color: #666; margin: 10px 0; }
                    .categoria { background: #f0f0f0; padding: 10px; margin: 20px 0 10px 0; font-weight: bold; }
                    .documento { border-left: 4px solid #007bff; padding: 10px; margin: 10px 0; background: #f9f9f9; }
                    .aviso { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="titulo">📄 DOCUMENTOS COMPLETOS</div>
                    <div class="subtitulo">' . $funcionario->nome . '</div>
                    <div class="subtitulo">CPF: ' . ($funcionario->cpf ?? 'Não informado') . ' | Função: ' . ($funcionario->funcao ?? 'Não informado') . '</div>
                    <div class="subtitulo">Gerado em: ' . date('d/m/Y H:i:s') . '</div>
                </div>
                
                <div class="aviso">
                    <strong>📋 Total de documentos encontrados: ' . count($todosPdfs) . '</strong><br>
                    Para visualizar o conteúdo completo de cada documento, use o botão "Baixar" que criará um arquivo ZIP com todos os arquivos originais.
                </div>
            ';

            $categoriaAtual = '';
            foreach ($todosPdfs as $doc) {
                if ($doc['categoria'] !== $categoriaAtual) {
                    $categoriaAtual = $doc['categoria'];
                    $html .= '<div class="categoria">' . $categoriaAtual . '</div>';
                }
                
                $dataFormatada = $doc['data'] ? date('d/m/Y H:i', strtotime($doc['data'])) : 'Data não informada';
                $html .= '
                <div class="documento">
                    <strong>' . $doc['nome'] . '</strong><br>
                    <small>Cadastrado em: ' . $dataFormatada . '</small>
                </div>';
            }

            $html .= '
                <div style="margin-top: 30px; padding: 20px; background: #e9ecef; text-align: center;">
                    <p><strong>💡 Observação:</strong> Este é um resumo dos documentos. Para acessar os arquivos originais, utilize o botão "Baixar" na página anterior.</p>
                </div>
            </body>
            </html>';

            // Tentar usar DomPDF se disponível
            if (class_exists('\Dompdf\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                return $dompdf->output();
            }

            // Se não tiver DomPDF, retornar o primeiro PDF encontrado
            return $todosPdfs[0]['conteudo'];
            
        } catch (\Exception $e) {
            Log::error('Erro na concatenação simples de PDFs: ' . $e->getMessage());
            // Último recurso: retornar o primeiro PDF
            return $todosPdfs[0]['conteudo'];
        }
    }

    /**
     * Monta o HTML da Ordem de Serviço (layout de impressão).
     */
    private function montarHtmlOrdemServico(array $os, $funcionario): string
    {
        $numero = isset($os['numero_os']) ? (string) $os['numero_os'] : '';
        $dataBR = isset($os['data_os']) ? date('d/m/Y', strtotime($os['data_os'])) : date('d/m/Y');
        $cpfCnpj = $os['cpf_cnpj'] ?? ($funcionario->cpf ?? '');
        $cpfCnpjFormatado = $this->formatarCpfCnpj($cpfCnpj);

        // Logo inline (base64) para o PDF ficar idêntico ao print
        $logoBase64 = '';
        $candidatos = [
            base_path('public_html/img/brs-logo.png'),
            public_path('img/brs-logo.png'),
            base_path('public_html/img/logo-brs.png'),
            public_path('img/logo-brs.png'),
        ];
        foreach ($candidatos as $p) {
            if (is_file($p)) { $logoBase64 = 'data:image/png;base64,' . base64_encode(@file_get_contents($p)); break; }
        }

        $logoTag = $logoBase64 !== ''
            ? '<img src="' . $logoBase64 . '" alt="Logo" class="logo" />'
            : '<div class="logo" style="font-weight:bold;color:#1a73e8;">BRS</div>';

        $descricao = nl2br(htmlspecialchars($os['descricao'] ?? ''));
        $observacoes = isset($os['observacoes']) && $os['observacoes'] !== null && $os['observacoes'] !== ''
            ? ('<div class="secao-titulo" style="margin-top: 20px;">OBSERVAÇÕES</div><div class="observacoes-box">' . nl2br(htmlspecialchars($os['observacoes'])) . '</div>')
            : '';

        $html = '<!doctype html>' .
            '<html lang="pt-BR">' .
            '<head>' .
            '  <meta charset="utf-8">' .
            '  <title>Ordem de Serviço - ' . htmlspecialchars($numero) . '</title>' .
            '  <style>' .
            '    @page { margin: 20mm 15mm; size: A4; }' .
            '    * { margin:0; padding:0; box-sizing:border-box; }' .
            '    body { font-family: Arial, Helvetica, sans-serif; color:#2c3e50; font-size:9pt; line-height:1.3; }' .
            '    .header { display:flex; align-items:center; justify-content:space-between; border-bottom:3px solid #3498db; padding-bottom:15px; margin-bottom:25px; }' .
            '    .logo { max-height:60px; max-width:120px; }' .
            '    .header-info { text-align:right; }' .
            '    .titulo { font-size:20pt; font-weight:bold; color:#2c3e50; margin-bottom:5px; }' .
            '    .numero-os { font-size:12pt; color:#3498db; font-weight:bold; }' .
            '    .data-emissao { font-size:8pt; color:#7f8c8d; margin-top:3px; }' .
            '    .secao { margin-bottom:20px; }' .
            '    .secao-titulo { background:#ecf0f1; padding:6px 10px; font-weight:bold; font-size:10pt; color:#2c3e50; border-left:4px solid #3498db; margin-bottom:8px; }' .
            '    .grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px; }' .
            '    .campo { margin-bottom:8px; }' .
            '    .campo-label { font-weight:bold; color:#34495e; font-size:8pt; margin-bottom:2px; display:block; }' .
            '    .campo-valor { color:#2c3e50; font-size:9pt; min-height:16px; padding:2px 0; }' .
            '    .descricao-box { padding:8px 0; min-height:40px; font-size:9pt; line-height:1.4; }' .
            '    .observacoes-box { padding:8px 0; min-height:30px; margin-top:8px; font-size:9pt; line-height:1.4; }' .
            '    .footer { position:fixed; bottom:15mm; left:0; right:0; text-align:center; font-size:9pt; color:#95a5a6; border-top:1px solid #ecf0f1; padding-top:8px; }' .
            '    .separador-assinatura { margin-top:80px; border-top:2px solid #2c3e50; padding-top:40px; }' .
            '    .assinatura { display:grid; grid-template-columns:1fr 1fr; gap:40px; margin-top:20px; }' .
            '    .assinatura-campo { text-align:center; border-top:1px solid #2c3e50; padding-top:8px; font-size:9pt; }' .
            '  </style>' .
            '</head>' .
            '<body>' .
            '  <div class="header">' .
            '    ' . $logoTag .
            '    <div class="header-info">' .
            '      <div class="titulo">ORDEM DE SERVIÇO</div>' .
            '      <div class="numero-os">Nº ' . htmlspecialchars($numero) . '</div>' .
            '      <div class="data-emissao">Emitida em: ' . $dataBR . '</div>' .
            '    </div>' .
            '  </div>' .
            '  <div class="secao">' .
            '    <div class="secao-titulo">DADOS DO FUNCIONÁRIO</div>' .
            '    <div class="grid">' .
            '      <div class="campo"><span class="campo-label">Nome:</span><div class="campo-valor">' . htmlspecialchars($funcionario->nome ?? '') . '</div></div>' .
            '      <div class="campo"><span class="campo-label">CPF/CNPJ:</span><div class="campo-valor">' . htmlspecialchars($cpfCnpjFormatado) . '</div></div>' .
            '    </div>' .
            '  </div>' .
            '  <div class="secao">' .
            '    <div class="secao-titulo">TERMO DE RESPONSABILIDADE</div>' .
            '    <div style="padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; font-size: 9pt; line-height: 1.4; text-align: justify;">' .
            '      Conforme a cláusula sexta do contrato firmado entre empregador e empregado, fica designado o colaborador(a) acima identificado(a) para realizar as atividades no local informado abaixo.' .
            '    </div>' .
            '  </div>' .
            '  <div class="secao">' .
            '    <div class="secao-titulo">LOCALIZAÇÃO</div>' .
            '    <div class="grid">' .
            '      <div class="campo"><span class="campo-label">Endereço:</span><div class="campo-valor">' . htmlspecialchars($os['endereco'] ?? '') . '</div></div>' .
            '      <div class="campo"><span class="campo-label">Cidade/UF:</span><div class="campo-valor">' . htmlspecialchars(trim(($os['cidade'] ?? '') . ((isset($os['estado']) && $os['estado'] !== '') ? ' / ' . $os['estado'] : ''))) . '</div></div>' .
            '      <div class="campo"><span class="campo-label">CEP:</span><div class="campo-valor">' . htmlspecialchars($os['cep'] ?? '') . '</div></div>' .
            '      <div class="campo"><span class="campo-label">Telefone:</span><div class="campo-valor">' . htmlspecialchars($os['telefone'] ?? '') . '</div></div>' .
            '    </div>' .
            '  </div>' .
            '  <div class="secao">' .
            '    <div class="secao-titulo">DESCRIÇÃO DO SERVIÇO</div>' .
            '    <div class="descricao-box">' . $descricao . '</div>' .
            '    ' . $observacoes .
            '  </div>' .
            '  <div class="separador-assinatura">' .
            '    <div class="assinatura">' .
            '      <div class="assinatura-campo"><strong>Assinatura do Gerente</strong></div>' .
            '      <div class="assinatura-campo"><strong>' . htmlspecialchars($funcionario->nome ?? '_________________________') . '</strong></div>' .
            '    </div>' .
            '  </div>' .
            '  <div class="footer">Sistema de Gestão - Ordem de Serviço gerada automaticamente</div>' .
            '</body>' .
            '</html>';
        return $html;
    }

    /**
     * Gera o PDF da O.S. (usa Dompdf se existir). Retorna null se indisponível.
     */
    private function gerarPdfOrdemServico(array $os, $funcionario): ?string
    {
        try {
            $html = $this->montarHtmlOrdemServico($os, $funcionario);
            if (class_exists('Dompdf\\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->set_option('isRemoteEnabled', true);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                return $dompdf->output();
            }
            return null;
        } catch (\Throwable $e) {
            \Log::error('Erro ao gerar PDF da O.S.: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gera PDF usando FPDF (sem dependências instaladas). Retorna binário do PDF ou null.
     */
    private function gerarPdfOrdemServicoFpdf(array $os, $funcionario): ?string
    {
        try {
            // Onde colocaremos o fpdf.php no projeto
            $caminhos = [
                base_path('public_html/vendor/fpdf/fpdf.php'),
                base_path('public_html/fpdf/fpdf.php'),
                base_path('fpdf/fpdf.php'),
                storage_path('app/vendor/fpdf/fpdf.php'),
            ];
            $arquivoFpdf = null;
            foreach ($caminhos as $p) {
                if (is_file($p)) { $arquivoFpdf = $p; break; }
            }
            // Se não encontrou, tenta baixar automaticamente (uma vez)
            if (!$arquivoFpdf) {
                $alvo = storage_path('app/vendor/fpdf/fpdf.php');
                if (!is_dir(dirname($alvo))) { @mkdir(dirname($alvo), 0755, true); }
                $urls = [
                    'https://raw.githubusercontent.com/Setasign/FPDF/master/fpdf.php',
                    'https://cdn.jsdelivr.net/gh/Setasign/FPDF/fpdf.php',
                ];
                foreach ($urls as $url) {
                    try {
                        $conteudo = @file_get_contents($url);
                        if ($conteudo && strpos($conteudo, 'class FPDF') !== false) {
                            @file_put_contents($alvo, $conteudo);
                            $arquivoFpdf = $alvo;
                            break;
                        }
                    } catch (\Throwable $e) {
                        // ignora e tenta próxima URL
                    }
                }
            }
            if (!$arquivoFpdf) {
                return null; // sem FPDF, deixa o fallback em HTML acontecer
            }

            require_once $arquivoFpdf;

            // Obter logo (base64 -> arquivo temporário) se existir
            $logoBase64 = '';
            $candidatos = [
                base_path('public_html/img/brs-logo.png'),
                public_path('img/brs-logo.png'),
                base_path('public_html/img/logo-brs.png'),
                public_path('img/logo-brs.png'),
            ];
            foreach ($candidatos as $p) {
                if (is_file($p)) { $logoBase64 = base64_encode(@file_get_contents($p)); break; }
            }
            if ($logoBase64 === '') {
                // logo mínima padrão (PNG 200x60 branca/azul) base64
                $logoBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAMgAAAA8CAYAAABm5YvNAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAACXBIWXMAAAsSAAALEgHS3X78AAABgElEQVR4nO3aMW7CMBQF0N2l1mA4wQz6/8qB0H9z4tqf7kL4V3Hf5hK8z3w+g9P6qgQAAAAAAAAAAAAAAgN1p4p6bqV3c9c1v3+oY0y6bq6Gv8b9R3m8m0m5iQn1wq0ZC8r7YtX2s3Zb2x2p5c1m8r0J4x2p7N1m8r0J41qj6pLQ5h2n0VgHhI9m8uYH3bqQw3f2k7Vfvqk0E7e1kV3X2kU3Y1kYv9e3wQ2m9yL4obm8b7r8K7q4m+J5oY8mU3q4l8mU2q4o8kUAAAAAAAAAAAAAAAB4uJ7q7cZ8tq1b7Y8AapbpmuYqg9o8nF6bF5b0m7j2yqf8Qq6g6Y0mWm5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQmWk5hQkAAAAAAAAAAAAAAMD+Afq0s3mQF8dJAAAAAElFTkSuQmCC';
            }
            $logoTmp = sys_get_temp_dir() . '/logo_fpdf_' . uniqid() . '.png';
            @file_put_contents($logoTmp, base64_decode($logoBase64));

            $numero = isset($os['numero_os']) ? (string) $os['numero_os'] : '';
            $dataBR = isset($os['data_os']) ? date('d/m/Y', strtotime($os['data_os'])) : date('d/m/Y');
            $cpfCnpj = $os['cpf_cnpj'] ?? ($funcionario->cpf ?? '');
            $cpfCnpjFormatado = $this->formatarCpfCnpj($cpfCnpj);

            // Monta PDF
            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetTitle('Ordem de Serviço - ' . $numero);

            // Header com logo
            if ($logoTmp && is_file($logoTmp)) {
                $pdf->Image($logoTmp, 10, 10, 35);
            }
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0, 10, 'ORDEM DE SERVICO', 0, 1, 'R');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor(52, 152, 219);
            $pdf->Cell(0, 6, utf8_decode('Nº ') . $numero, 0, 1, 'R');
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetTextColor(127, 140, 141);
            $pdf->Cell(0, 5, 'Emitida em: ' . $dataBR, 0, 1, 'R');
            $pdf->Ln(5);

            // Linha
            $pdf->SetDrawColor(52, 152, 219);
            $pdf->SetLineWidth(0.8);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(5);

            // Seção: Dados do Funcionário
            $pdf->SetFillColor(236, 240, 241);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(190, 7, 'DADOS DO FUNCIONARIO', 0, 1, 'L', true);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(95, 6, 'Nome: ' . ($funcionario->nome ?? ''), 0, 0, 'L');
            $pdf->Cell(95, 6, 'CPF/CNPJ: ' . $cpfCnpjFormatado, 0, 1, 'L');
            $pdf->Ln(2);

            // Seção: Termo de Responsabilidade
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(236, 240, 241);
            $pdf->Cell(190, 7, 'TERMO DE RESPONSABILIDADE', 0, 1, 'L', true);
            $pdf->SetFont('Arial', '', 9);
            $texto = 'Conforme a clausula sexta do contrato firmado entre empregador e empregado, ' .
                'fica designado o colaborador(a) acima identificado(a) para realizar as atividades no local informado abaixo.';
            $pdf->MultiCell(190, 5.5, utf8_decode($texto));
            $pdf->Ln(2);

            // Seção: Localização
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(236, 240, 241);
            $pdf->Cell(190, 7, 'LOCALIZACAO', 0, 1, 'L', true);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(95, 6, 'Endereco: ' . ($os['endereco'] ?? ''), 0, 0, 'L');
            $pdf->Cell(95, 6, 'Cidade/UF: ' . trim(($os['cidade'] ?? '') . (!empty($os['estado'] ?? '') ? ' / ' . $os['estado'] : '')), 0, 1, 'L');
            $pdf->Cell(95, 6, 'CEP: ' . ($os['cep'] ?? ''), 0, 0, 'L');
            $pdf->Cell(95, 6, 'Telefone: ' . ($os['telefone'] ?? ''), 0, 1, 'L');
            $pdf->Ln(2);

            // Seção: Descrição/Observações
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(236, 240, 241);
            $pdf->Cell(190, 7, 'DESCRICAO DO SERVICO', 0, 1, 'L', true);
            $pdf->SetFont('Arial', '', 9);
            $descricao = str_replace(["\r\n","\n"], ' ', (string)($os['descricao'] ?? ''));
            $pdf->MultiCell(190, 5.5, utf8_decode($descricao));
            if (!empty($os['observacoes'])) {
                $pdf->Ln(2);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetFillColor(236, 240, 241);
                $pdf->Cell(190, 7, 'OBSERVACOES', 0, 1, 'L', true);
                $pdf->SetFont('Arial', '', 9);
                $obs = str_replace(["\r\n","\n"], ' ', (string)$os['observacoes']);
                $pdf->MultiCell(190, 5.5, utf8_decode($obs));
            }
            $pdf->Ln(5);

            // Assinaturas
            $y = $pdf->GetY();
            $pdf->Line(20, $y + 15, 95, $y + 15);
            $pdf->Line(115, $y + 15, 190, $y + 15);
            $pdf->SetY($y + 16);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(95, 5, utf8_decode('Assinatura do Gerente'), 0, 0, 'C');
            $pdf->Cell(95, 5, utf8_decode($funcionario->nome ?? ''), 0, 1, 'C');

            // Footer
            $pdf->SetY(-25);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(149, 165, 166);
            $pdf->Cell(0, 6, utf8_decode('Sistema de Gestão - Ordem de Serviço gerada automaticamente'), 0, 1, 'C');

            // Saída binária
            return $pdf->Output('S');
        } catch (\Throwable $e) {
            \Log::error('Erro FPDF O.S.: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Formata CPF/CNPJ para impressão
     */
    private function formatarCpfCnpj(?string $valor): string
    {
        if (!$valor) return '';
        $numeros = preg_replace('/\D+/', '', $valor);
        if (strlen($numeros) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $numeros);
        }
        if (strlen($numeros) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $numeros);
        }
        return $valor;
    }

    /**
     * Gerar arquivo completo do funcionário (ZIP com todos os documentos)
     */
    public function gerarArquivoCompleto($funcionarioId)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->temPermissao('vis_func')) {
                abort(403);
            }

            // Verificar se o funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['error' => 'Funcionário não encontrado'], 404);
            }

            // Buscar todos os documentos do funcionário
            $todosDocumentos = [];

            // 1. Documentos gerais (funcionarios_documentos)
            $documentosGerais = DB::table('funcionarios_documentos')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($documentosGerais as $doc) {
                $todosDocumentos[] = [
                    'categoria' => 'Documentos_Gerais',
                    'nome' => $doc->tipo_documento . '.' . $doc->arquivo_extensao,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 2. Atestados
            $atestados = DB::table('funcionarios_atestados')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($atestados as $doc) {
                $todosDocumentos[] = [
                    'categoria' => 'Atestados',
                    'nome' => 'Atestado_' . date('d-m-Y', strtotime($doc->data_atestado)) . '.' . $doc->arquivo_extensao,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 3. Advertências
            $advertencias = DB::table('funcionarios_advertencias')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($advertencias as $doc) {
                $todosDocumentos[] = [
                    'categoria' => 'Advertencias',
                    'nome' => 'Advertencia_' . date('d-m-Y', strtotime($doc->data_advertencia)) . '.' . $doc->arquivo_extensao,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 4. Décimo terceiro
            $decimoTerceiro = DB::table('funcionarios_decimo')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($decimoTerceiro as $doc) {
                $todosDocumentos[] = [
                    'categoria' => 'Decimo_Terceiro',
                    'nome' => 'Decimo_' . $doc->ano . '.' . $doc->arquivo_extensao,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 5. Rescisão
            $rescisao = DB::table('funcionarios_rescisao')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($rescisao as $doc) {
                $todosDocumentos[] = [
                    'categoria' => 'Rescisao',
                    'nome' => 'Rescisao_' . date('d-m-Y', strtotime($doc->data_rescisao)) . '.' . $doc->arquivo_extensao,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 6. Contra-cheques
            $contraCheques = DB::table('funcionarios_contra_cheques')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($contraCheques as $doc) {
                $todosDocumentos[] = [
                    'categoria' => 'Contra_Cheques',
                    'nome' => 'ContraCheque_' . $doc->mes_ano . '.' . $doc->arquivo_extensao,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 7. Férias
            $ferias = DB::table('funcionarios_ferias')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($ferias as $doc) {
                $todosDocumentos[] = [
                    'categoria' => 'Ferias',
                    'nome' => 'Ferias_' . date('d-m-Y', strtotime($doc->periodo_inicio)) . '_a_' . date('d-m-Y', strtotime($doc->periodo_fim)) . '.' . $doc->arquivo_extensao,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 8. Frequência
            $frequencias = DB::table('funcionarios_frequencia')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($frequencias as $doc) {
                $todosDocumentos[] = [
                    'categoria' => 'Frequencia',
                    'nome' => 'Frequencia_' . $doc->mes_ano . '.pdf',
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 9. Certificados
            $certificados = DB::table('funcionarios_certificados')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($certificados as $doc) {
                $nomeArquivo = preg_replace('/[^A-Za-z0-9\-_]/', '_', $doc->nome_certificado);
                $dataEmissao = date('d-m-Y', strtotime($doc->data_emissao));
                
                $todosDocumentos[] = [
                    'categoria' => 'Certificados',
                    'nome' => 'Certificado_' . $nomeArquivo . '_' . $dataEmissao . '.pdf',
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 10. ASOS
            $asos = DB::table('funcionarios_asos')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->get();

            foreach ($asos as $doc) {
                $tipoTexto = [
                    'admissional' => 'Admissional',
                    'periodico' => 'Periodico',
                    'mudanca_funcao' => 'Mudanca_Funcao',
                    'retorno_trabalho' => 'Retorno_Trabalho',
                    'demissional' => 'Demissional'
                ];
                
                $tipo = isset($tipoTexto[$doc->tipo_exame]) ? $tipoTexto[$doc->tipo_exame] 
                    : 'ASOS';
                
                $dataExame = isset($doc->data_exame) ? date('d-m-Y', strtotime($doc->data_exame)) : '';
                
                $todosDocumentos[] = [
                    'categoria' => 'ASOS',
                    'nome' => 'ASOS_' . $tipo . '_' . $dataExame . '.pdf',
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at
                ];
            }

            // 11. EPIs Retroativos (funcionarios_arquivos)
            $episRetro = DB::table('funcionarios_arquivos')
                ->where('funcionario_id', $funcionarioId)
                ->whereNotNull('arquivo_conteudo')
                ->orderBy('data', 'desc')
                ->get();

            foreach ($episRetro as $doc) {
                $dataStr = $doc->data ? date('Y-m-d', strtotime($doc->data)) : date('Y-m-d');
                $horaStr = $doc->horario ? str_replace(':', '-', $doc->horario) : '00-00-00';
                $nomeOriginal = $doc->arquivo_nome ?: 'documento.pdf';
                $nomeSan = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nomeOriginal);
                $todosDocumentos[] = [
                    'categoria' => 'EPIs_Retroativos',
                    'nome' => 'EPI_' . $dataStr . '_' . $horaStr . '_' . $nomeSan,
                    'conteudo' => $doc->arquivo_conteudo,
                    'data' => $doc->created_at ?? null,
                ];
            }

            // 12. Ordens de Serviço (gerar PDF do layout)
            $ordens = DB::table('ordens_servico as os')
                ->leftJoin('funcionarios as f', 'f.id', '=', 'os.funcionario_id')
                ->select('os.*')
                ->where('os.funcionario_id', $funcionarioId)
                ->orderByDesc('os.data_os')
                ->limit(300)
                ->get();

            foreach ($ordens as $os) {
                $payload = [
                    'numero_os' => $os->numero_os,
                    'data_os' => $os->data_os,
                    'descricao' => $os->descricao,
                    'endereco' => $os->endereco,
                    'cidade' => $os->cidade,
                    'estado' => $os->estado,
                    'telefone' => $os->telefone,
                    'cpf_cnpj' => $os->cpf_cnpj,
                    'cep' => $os->cep,
                    'observacoes' => $os->observacoes,
                ];

                $dataNome = $os->data_os ? date('Y-m-d', strtotime($os->data_os)) : date('Y-m-d');
                $numSan = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)($os->numero_os ?: 'OS'));
                $pdfBin = $this->gerarPdfOrdemServico($payload, $funcionario);
                if ($pdfBin === null) {
                    // Tentativa 2: FPDF (sem instalação)
                    $pdfBin = $this->gerarPdfOrdemServicoFpdf($payload, $funcionario);
                }
                if ($pdfBin !== null) {
                    $todosDocumentos[] = [
                        'categoria' => 'Ordens_de_Servico',
                        'nome' => 'OS_' . $numSan . '_' . $dataNome . '.pdf',
                        'conteudo' => $pdfBin,
                        'data' => $os->data_os,
                    ];
                } else {
                    // Fallback: salvar HTML no ZIP quando Dompdf não estiver disponível
                    $html = $this->montarHtmlOrdemServico($payload, $funcionario);
                    $todosDocumentos[] = [
                        'categoria' => 'Ordens_de_Servico',
                        'nome' => 'OS_' . $numSan . '_' . $dataNome . '.html',
                        'conteudo' => $html,
                        'data' => $os->data_os,
                    ];
                }
            }

            // Verificar se há documentos
            if (empty($todosDocumentos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum documento encontrado para este funcionário'
                ], 404);
            }

            // Criar arquivo ZIP temporário
            $nomeArquivo = 'Funcionario_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $funcionario->nome) . '_' . date('d-m-Y_H-i-s') . '.zip';
            $caminhoTemp = storage_path('app/temp/' . $nomeArquivo);
            
            // Garantir que o diretório temp existe
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            // Criar ZIP
            $zip = new \ZipArchive();
            if ($zip->open($caminhoTemp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar arquivo ZIP'
                ], 500);
            }

            // Adicionar documentos ao ZIP organizados por categoria
            foreach ($todosDocumentos as $documento) {
                $nomeNoZip = $documento['categoria'] . '/' . $documento['nome'];
                $zip->addFromString($nomeNoZip, $documento['conteudo']);
            }

            // Adicionar arquivo de resumo
            $resumo = "RELATÓRIO COMPLETO - " . $funcionario->nome . "\n";
            $resumo .= "Gerado em: " . date('d/m/Y H:i:s') . "\n";
            $resumo .= "Por: " . Auth::user()->name . "\n\n";
            $resumo .= "DADOS DO FUNCIONÁRIO:\n";
            $resumo .= "Nome: " . $funcionario->nome . "\n";
            $resumo .= "CPF: " . ($funcionario->cpf ?? 'Não informado') . "\n";
            $resumo .= "Função: " . ($funcionario->funcao ?? 'Não informado') . "\n";
            $resumo .= "Status: " . ($funcionario->status ?? 'Não informado') . "\n\n";
            $resumo .= "DOCUMENTOS INCLUÍDOS:\n";
            
            $categorias = [];
            foreach ($todosDocumentos as $doc) {
                if (!isset($categorias[$doc['categoria']])) {
                    $categorias[$doc['categoria']] = 0;
                }
                $categorias[$doc['categoria']]++;
            }
            
            foreach ($categorias as $categoria => $quantidade) {
                $resumo .= "- " . str_replace('_', ' ', $categoria) . ": " . $quantidade . " arquivo(s)\n";
            }

            $zip->addFromString('RESUMO.txt', $resumo);
            $zip->close();

            // Log da ação (apenas em debug, sem PII)
            if (config('app.debug')) {
                Log::debug('Arquivo completo gerado para funcionário', [
                    'funcionario_id' => $funcionarioId,
                    'total_documentos' => count($todosDocumentos),
                ]);
            }

            // Retornar arquivo para download
            return response()->download($caminhoTemp, $nomeArquivo)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar arquivo completo do funcionário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    // ========================================
    // MÉTODOS DELETE PARA EXCLUSÃO DE DOCUMENTOS
    // ========================================

    /**
     * Excluir documento geral
     */
    public function deleteDocumento($id)
    {
        try {
            $documento = DB::table('funcionarios_documentos')->where('id', $id)->first();
            
            if (!$documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento não encontrado'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_documentos')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('Documento geral excluído', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Documento excluído com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir documento'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir documento geral: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir atestado
     */
    public function deleteAtestado($id)
    {
        try {
            $atestado = DB::table('funcionarios_atestados')->where('id', $id)->first();
            
            if (!$atestado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Atestado não encontrado'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_atestados')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('Atestado excluído', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Atestado excluído com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir atestado'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir atestado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir advertência
     */
    public function deleteAdvertencia($id)
    {
        try {
            $advertencia = DB::table('funcionarios_advertencias')->where('id', $id)->first();
            
            if (!$advertencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Advertência não encontrada'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_advertencias')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('Advertência excluída', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Advertência excluída com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir advertência'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir advertência: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir contra cheque
     */
    public function deleteContraCheque($id)
    {
        try {
            $contraCheque = DB::table('funcionarios_contra_cheques')->where('id', $id)->first();
            
            if (!$contraCheque) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contra cheque não encontrado'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_contra_cheques')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('Contra cheque excluído', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Contra cheque excluído com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir contra cheque'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir contra cheque: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir férias
     */
    public function deleteFerias($id)
    {
        try {
            $ferias = DB::table('funcionarios_ferias')->where('id', $id)->first();
            
            if (!$ferias) {
                return response()->json([
                    'success' => false,
                    'message' => 'Férias não encontradas'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_ferias')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('Férias excluídas', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Férias excluídas com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir férias'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir férias: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir décimo terceiro
     */
    public function deleteDecimo($id)
    {
        try {
            $decimo = DB::table('funcionarios_decimo')->where('id', $id)->first();
            
            if (!$decimo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Décimo terceiro não encontrado'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_decimo')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('Décimo terceiro excluído', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Décimo terceiro excluído com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir décimo terceiro'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir décimo terceiro: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir rescisão
     */
    public function deleteRescisao($id)
    {
        try {
            $rescisao = DB::table('funcionarios_rescisao')->where('id', $id)->first();
            
            if (!$rescisao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rescisão não encontrada'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_rescisao')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('Rescisão excluída', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Rescisão excluída com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir rescisão'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir rescisão: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir frequência
     */
    public function deleteFrequencia($id)
    {
        try {
            $frequencia = DB::table('funcionarios_frequencia')->where('id', $id)->first();
            
            if (!$frequencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Frequência não encontrada'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_frequencia')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('Frequência excluída', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Frequência excluída com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir frequência'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir frequência: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir certificado
     */
    public function deleteCertificado($id)
    {
        try {
            $certificado = DB::table('funcionarios_certificados')->where('id', $id)->first();
            
            if (!$certificado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Certificado não encontrado'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_certificados')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('Certificado excluído', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Certificado excluído com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir certificado'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir certificado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir ASOS
     */
    public function deleteAsos($id)
    {
        try {
            $asos = DB::table('funcionarios_asos')->where('id', $id)->first();
            
            if (!$asos) {
                return response()->json([
                    'success' => false,
                    'message' => 'ASOS não encontrado'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_asos')->where('id', $id)->delete();
            
            if ($deleted) {
                if (config('app.debug')) {
                    Log::debug('ASOS excluído', ['id' => $id]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'ASOS excluído com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir ASOS'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir ASOS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    // ========================================
    // MÉTODOS PARA FREQUÊNCIA
    // ========================================

    /**
     * Listar frequências de um funcionário
     */
    public function listarFrequencia($funcionarioId)
    {
        try {
            $frequencias = DB::table('funcionarios_frequencia')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('mes_ano', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'frequencia' => $frequencias
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Exception $e) {
            Log::error('Erro ao listar frequências: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Armazenar nova frequência
     */
    public function storeFrequencia(Request $request)
    {
        try {
            $validated = $request->validate([
                'funcionario_id' => 'required|integer|exists:funcionarios,id',
                'mes_ano' => 'nullable|string|max:7',
                'observacoes' => 'nullable|string|max:1000',
                'arquivo' => 'required|file|mimes:pdf|max:10240'
            ]);

            $arquivo = $request->file('arquivo');
            
            // OTIMIZAÇÃO: Verificar se pode salvar no storage
            $temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_frequencia');
            
            $dados = [
                'funcionario_id' => $validated['funcionario_id'],
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_mime' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'mes_ano' => $validated['mes_ano'] ?? null,
                'observacoes' => $validated['observacoes'],
                'usuario_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if ($temPathCol) {
                $dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $validated['funcionario_id'], 'frequencia');
                $dados['arquivo_path'] = $dadosArquivo['path'];
                $dados['arquivo_conteudo'] = null;
            } else {
                $dados['arquivo_conteudo'] = file_get_contents($arquivo->getPathname());
            }

            $frequenciaId = DB::table('funcionarios_frequencia')->insertGetId($dados);

            if (config('app.debug')) {
                Log::debug('Frequência anexada', [
                    'id' => $frequenciaId,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Frequência anexada com sucesso',
                'id' => $frequenciaId
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Exception $e) {
            Log::error('Erro ao anexar frequência: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Download da frequência
     */
    public function downloadFrequencia($id)
    {
        try {
            $frequencia = DB::table('funcionarios_frequencia')->where('id', $id)->first();

            if (!$frequencia) {
                abort(404, 'Frequência não encontrada');
            }

            return ArquivoHelper::download($frequencia, 'frequência');

        } catch (\Exception $e) {
            Log::error('Erro ao baixar frequência: ' . $e->getMessage());
            abort(500, 'Erro interno do servidor');
        }
    }

    // ========================================
    // MÉTODOS PARA CERTIFICADOS
    // ========================================

    /**
     * Listar certificados de um funcionário
     */
    public function listarCertificado($funcionarioId)
    {
        try {
            $certificados = DB::table('funcionarios_certificados')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('data_emissao', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'certificado' => $certificados
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Exception $e) {
            Log::error('Erro ao listar certificados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Armazenar novo certificado
     */
    public function storeCertificado(Request $request)
    {
        try {
            $validated = $request->validate([
                'funcionario_id' => 'required|integer|exists:funcionarios,id',
                'nome_certificado' => 'required|string|max:255',
                'data_emissao' => 'required|date',
                'data_validade' => 'nullable|date|after:data_emissao',
                'observacoes' => 'nullable|string|max:1000',
                'arquivo' => 'required|file|mimes:pdf|max:10240'
            ]);

            $arquivo = $request->file('arquivo');
            
            // OTIMIZAÇÃO: Verificar se pode salvar no storage
            $temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_certificados');
            
            $dados = [
                'funcionario_id' => $validated['funcionario_id'],
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_mime' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'nome_certificado' => $validated['nome_certificado'],
                'data_emissao' => $validated['data_emissao'],
                'data_validade' => $validated['data_validade'],
                'observacoes' => $validated['observacoes'],
                'usuario_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if ($temPathCol) {
                $dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $validated['funcionario_id'], 'certificados');
                $dados['arquivo_path'] = $dadosArquivo['path'];
                $dados['arquivo_conteudo'] = null;
            } else {
                $dados['arquivo_conteudo'] = file_get_contents($arquivo->getPathname());
            }

            $certificadoId = DB::table('funcionarios_certificados')->insertGetId($dados);

            if (config('app.debug')) {
                Log::debug('Certificado anexado', [
                    'id' => $certificadoId,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Certificado anexado com sucesso',
                'id' => $certificadoId
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Exception $e) {
            Log::error('Erro ao anexar certificado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Download do certificado
     */
    public function downloadCertificado($id)
    {
        try {
            $certificado = DB::table('funcionarios_certificados')->where('id', $id)->first();

            if (!$certificado) {
                abort(404, 'Certificado não encontrado');
            }

            return ArquivoHelper::download($certificado, 'certificado');

        } catch (\Exception $e) {
            Log::error('Erro ao baixar certificado: ' . $e->getMessage());
            abort(500, 'Erro interno do servidor');
        }
    }

    // ========================================
    // MÉTODOS PARA TERMOS ADITIVOS
    // ========================================

    /**
     * Listar termos aditivos de um funcionário
     */
    public function listarTermoAditivo($funcionarioId)
    {
        try {
            $termos = DB::table('funcionarios_termos_aditivos')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('data_termo', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'termos' => $termos,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Exception $e) {
            \Log::error('Erro ao listar termos aditivos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Armazenar novo termo aditivo
     */
    public function storeTermoAditivo(Request $request)
    {
        try {
            $validated = $request->validate([
                'funcionario_id' => 'required|integer|exists:funcionarios,id',
                'nome_termo' => 'required|string|max:255',
                'data_termo' => 'required|date',
                'observacoes' => 'nullable|string|max:1000',
                'arquivo' => 'required|file|mimes:pdf|max:10240',
            ]);

            $arquivo = $request->file('arquivo');
            
            // OTIMIZAÇÃO: Verificar se pode salvar no storage
            $temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_termos_aditivos');
            
            $dados = [
                'funcionario_id' => $validated['funcionario_id'],
                'nome_termo' => $validated['nome_termo'],
                'data_termo' => $validated['data_termo'],
                'observacoes' => $validated['observacoes'] ?? null,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_mime' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'usuario_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if ($temPathCol) {
                $dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $validated['funcionario_id'], 'termos_aditivos');
                $dados['arquivo_path'] = $dadosArquivo['path'];
                $dados['arquivo_conteudo'] = null;
            } else {
                $dados['arquivo_conteudo'] = file_get_contents($arquivo->getPathname());
            }

            $id = DB::table('funcionarios_termos_aditivos')->insertGetId($dados);

            if (config('app.debug')) {
                \Log::debug('Termo aditivo anexado', ['id' => $id]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Termo aditivo anexado com sucesso',
                'id' => $id,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Exception $e) {
            \Log::error('Erro ao anexar termo aditivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Download do termo aditivo
     */
    public function downloadTermoAditivo($id)
    {
        try {
            $row = DB::table('funcionarios_termos_aditivos')->where('id', $id)->first();
            if (!$row) { abort(404, 'Termo aditivo não encontrado'); }

            return ArquivoHelper::download($row, 'termo aditivo');

        } catch (\Exception $e) {
            \Log::error('Erro ao baixar termo aditivo: ' . $e->getMessage());
            abort(500, 'Erro interno do servidor');
        }
    }

    /**
     * Excluir termo aditivo
     */
    public function deleteTermoAditivo($id)
    {
        try {
            $exists = DB::table('funcionarios_termos_aditivos')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'Termo aditivo não encontrado'], 404);
            }
            DB::table('funcionarios_termos_aditivos')->where('id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Termo aditivo excluído com sucesso']);
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir termo aditivo: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
        }
    }

    // ========================================
    // MÉTODOS PARA ASOS
    // ========================================

    /**
     * Listar ASOS de um funcionário
     */
    public function listarAsos($funcionarioId)
    {
        try {
            $asos = DB::table('funcionarios_asos')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('data_exame', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'asos' => $asos
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Exception $e) {
            Log::error('Erro ao listar ASOS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Armazenar novo ASOS
     */
    public function storeAsos(Request $request)
    {
        try {
            $validated = $request->validate([
                'funcionario_id' => 'required|integer|exists:funcionarios,id',
                'data_exame' => 'required|date',
                'tipo_exame' => 'required|in:admissional,periodico,mudanca_funcao,retorno_trabalho,demissional',
                'medico_responsavel' => 'nullable|string|max:255',
                'observacoes' => 'nullable|string|max:1000',
                'arquivo' => 'required|file|mimes:pdf|max:10240'
            ]);

            $arquivo = $request->file('arquivo');
            
            // OTIMIZAÇÃO: Verificar se pode salvar no storage
            $temPathCol = ArquivoHelper::tabelaTemPathCol('funcionarios_asos');
            
            $dados = [
                'funcionario_id' => $validated['funcionario_id'],
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_mime' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'data_exame' => $validated['data_exame'],
                'tipo_exame' => $validated['tipo_exame'],
                'medico_responsavel' => $validated['medico_responsavel'],
                'observacoes' => $validated['observacoes'],
                'usuario_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Verificar se a coluna arquivo_conteudo existe e se permite NULL
            $temConteudoCol = Schema::hasColumn('funcionarios_asos', 'arquivo_conteudo');
            
            if ($temPathCol) {
                // Salvar apenas no storage (OTIMIZADO)
                $dadosArquivo = ArquivoHelper::salvarStorage($arquivo, $validated['funcionario_id'], 'asos');
                $dados['arquivo_path'] = $dadosArquivo['path'];
                // Adicionar valor vazio para arquivo_conteudo se a coluna existir
                if ($temConteudoCol) {
                    $dados['arquivo_conteudo'] = '';
                }
            } else {
                // Fallback: salvar no banco
                $dados['arquivo_conteudo'] = file_get_contents($arquivo->getPathname());
            }

            $asosId = DB::table('funcionarios_asos')->insertGetId($dados);

            if (config('app.debug')) {
                Log::debug('ASOS anexado', [
                    'id' => $asosId,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'ASOS anexado com sucesso',
                'id' => $asosId
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação: ' . implode(', ', $ve->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao anexar ASOS: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['arquivo'])
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download do ASOS
     */
    public function downloadAsos($id)
    {
        try {
            $asos = DB::table('funcionarios_asos')->where('id', $id)->first();

            if (!$asos) {
                abort(404, 'ASOS não encontrado');
            }

            return ArquivoHelper::download($asos, 'ASOS');

        } catch (\Exception $e) {
            Log::error('Erro ao baixar ASOS: ' . $e->getMessage());
            abort(500, 'Erro interno do servidor');
        }
    }

    // ========================================
    // MÉTODOS PARA EPIs RETROATIVOS
    // ========================================
    
    public function listarEpisRetroativos($funcionarioId)
    {
        try {
            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $epis = DB::table('funcionarios_arquivos')
                ->select('id', 'data', 'horario', 'arquivo_nome', 'arquivo_tamanho', 'created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('data', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'epis' => $epis
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao carregar EPIs retroativos'], 500);
        }
    }

    public function storeEpiRetroativo(Request $request)
    {
        try {
            // Validação rápida
            $request->validate([
                'funcionario_id' => 'required|exists:funcionarios,id',
                'data' => 'required|date',
                'arquivo' => 'required|file|mimes:pdf|max:51200' // 50MB
            ]);

            $arquivo = $request->file('arquivo');
            $funcionario = DB::table('funcionarios')->select('nome')->where('id', $request->funcionario_id)->first();
            
            // Upload otimizado
            $arquivoConteudo = file_get_contents($arquivo->getPathname());
            
            $id = DB::table('funcionarios_arquivos')->insertGetId([
                'funcionario_id' => $request->funcionario_id,
                'nome_funcionario' => $funcionario->nome,
                'data' => $request->data,
                'horario' => now()->format('H:i:s'),
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_conteudo' => $arquivoConteudo,
                'arquivo_mime' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'EPI retroativo anexado com sucesso',
                'epi' => [
                    'id' => $id,
                    'data' => $request->data,
                    'created_at' => now()->toDateTimeString(),
                    'arquivo_nome' => $arquivo->getClientOriginalName(),
                    'arquivo_tamanho' => $arquivo->getSize()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao anexar EPI retroativo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadEpiRetroativo($id)
    {
        try {
            $epi = DB::table('funcionarios_arquivos')->where('id', $id)->first();
            
            if (!$epi) {
                abort(404, 'EPI retroativo não encontrado');
            }

            return response($epi->arquivo_conteudo, 200, [
                'Content-Type' => $epi->arquivo_mime,
                'Content-Disposition' => 'inline; filename="' . $epi->arquivo_nome . '"'
            ]);
        } catch (\Exception $e) {
            abort(500, 'Erro interno do servidor');
        }
    }

    public function deleteEpiRetroativo($id)
    {
        try {
            $epi = DB::table('funcionarios_arquivos')->where('id', $id)->first();
            
            if (!$epi) {
                return response()->json([
                    'success' => false,
                    'message' => 'EPI retroativo não encontrado'
                ], 404);
            }
            
            $deleted = DB::table('funcionarios_arquivos')->where('id', $id)->delete();
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'EPI retroativo excluído com sucesso'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir EPI retroativo'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
