<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\OcorrenciaFrota;
use App\Models\Veiculo;
use Illuminate\Support\Facades\DB;

class OcorrenciaController extends Controller
{
    /**
     * Página inicial de Ocorrências da Frota.
     */
    public function index()
    {
        try {
            // Buscar veículos para o select (apenas utilizáveis)
            $veiculos = DB::table('veiculos')
                ->select('id', 'placa', 'marca', 'modelo')
                ->where('status', '!=', 'inativo')
                ->orderBy('placa')
                ->get();

            return view('frota.ocorrencias.index', compact('veiculos'));
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar página de ocorrências: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Erro ao carregar página de ocorrências: ' . $e->getMessage());
        }
    }

    /**
     * Recebe a submissão da ocorrência e persiste na tabela.
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Iniciando salvamento de ocorrência', ['user_id' => auth()->id()]);
            
            $validated = $request->validate([
                'veiculo_id' => ['required', 'exists:veiculos,id'],
                'data' => ['required', 'date'],
                'hora' => ['required'],
                'descricao' => ['required', 'string', 'min:5'],
                'sugestao' => ['nullable', 'string'],
                'fotos' => ['nullable', 'array', 'max:10'],
                // max em KB (50 * 1024)
                'fotos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
            ]);

            \Log::info('Validação passou', $validated);

            $user = Auth::user();

            // Preparar dados básicos primeiro (sem fotos)
            $dados = [
                'veiculo_id' => $validated['veiculo_id'],
                'user_id' => $user ? $user->id : null,
                'motorista_nome' => $user ? $user->name : null,
                'data' => $validated['data'],
                'hora' => $validated['hora'],
                'descricao' => $validated['descricao'],
                'sugestao' => $validated['sugestao'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Adicionar fotos se houver
            if ($request->hasFile('fotos')) {
                $i = 0;
                foreach ($request->file('fotos') as $file) {
                    if (!$file || $i >= 10) { break; }
                    $i++;
                    $dados["foto{$i}"] = file_get_contents($file->getRealPath());
                    $dados["foto{$i}_mime"] = $file->getClientMimeType();
                }
                \Log::info('Fotos processadas', ['quantidade' => $i]);
            }

            // Persistir na tabela ocorrencias_frota
            $id = DB::table('ocorrencias_frota')->insertGetId($dados);
            
            \Log::info('Ocorrência salva com sucesso', ['id' => $id]);

            return redirect('/frota/ocorrencias')
                ->with('success', 'Ocorrência registrada com sucesso! ID: ' . $id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Erro de validação ao salvar ocorrência', [
                'errors' => $e->errors()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Erro ao salvar ocorrência: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Erro ao salvar ocorrência: ' . $e->getMessage());
        }
    }

    /**
     * Página do gestor de ocorrências - lista veículos com ocorrências.
     */
    public function gestor()
    {
        // Buscar todos os veículos
        $veiculos = Veiculo::select('id', 'placa', 'marca', 'modelo', 'ano', 'status')
            ->orderBy('placa')
            ->get();

        // Para cada veículo, verificar se há ocorrências recentes
        $veiculosComOcorrencias = $veiculos->map(function($veiculo) {
            // Buscar ocorrências deste veículo (últimos 30 dias) que não estejam resolvidas
            $base = OcorrenciaFrota::where('veiculo_id', $veiculo->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->where(function($q){
                    $q->whereNull('status')->orWhere('status', '!=', 'resolvido');
                })
                ->orderBy('created_at', 'desc');

            $ocorrencias = (clone $base)->limit(3)->get();

            $totalOcorrencias = (clone $base)->count();
            
            return [
                'veiculo' => $veiculo,
                'total_ocorrencias' => $totalOcorrencias,
                'ocorrencias_recentes' => $ocorrencias,
                'tem_ocorrencias' => $totalOcorrencias > 0,
            ];
        });

        return view('frota.ocorrencias.gestor', compact('veiculosComOcorrencias'));
    }

    /**
     * API: Detalhes de uma ocorrência (para modal)
     */
    public function showOccurrence(int $id)
    {
        $o = OcorrenciaFrota::findOrFail($id);
        return response()->json([
            'id' => $o->id,
            'veiculo_id' => $o->veiculo_id,
            'motorista' => $o->motorista_nome,
            'data' => optional($o->data)->format('Y-m-d'),
            'hora' => $o->hora,
            'descricao' => $o->descricao,
            'sugestao' => $o->sugestao,
            'status' => $o->status ?? 'novo',
            'created_at' => $o->created_at?->format('d/m/Y H:i'),
        ]);
    }

    /**
     * API: Atualizar status (em_andamento | resolvido)
     */
    public function updateStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['required', 'in:em_andamento,resolvido']
        ]);
        $o = OcorrenciaFrota::findOrFail($id);
        $oldStatus = $o->status ?? 'novo';
        $o->status = $data['status'];
        $o->resolved_at = $data['status'] === 'resolvido' ? now() : null;
        $o->save();

        // Registrar histórico de status
        \App\Models\StatusOcorrencia::create([
            'ocorrencia_id' => $o->id,
            'user_id' => auth()->id(),
            'status_from' => $oldStatus,
            'status_to' => $data['status'],
            'observacao' => null,
        ]);

        // Se em andamento, opcionalmente marcar veículo como manutencao
        if ($o->veiculo_id && $data['status'] === 'em_andamento') {
            Veiculo::where('id', $o->veiculo_id)->update(['status' => 'manutencao']);
        }
        if ($o->veiculo_id && $data['status'] === 'resolvido') {
            // volta para ativo se estiver em manutencao
            Veiculo::where('id', $o->veiculo_id)->where('status', 'manutencao')->update(['status' => 'ativo']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * API: Histórico completo de ocorrências por veículo
     */
    public function historicoVeiculo(int $veiculoId)
    {
        // Buscar todas as ocorrências do veículo (sem limite de tempo)
        $todasOcorrencias = OcorrenciaFrota::where('veiculo_id', $veiculoId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($o) {
                return [
                    'id' => $o->id,
                    'motorista_nome' => $o->motorista_nome,
                    'data' => $o->data?->format('Y-m-d'),
                    'hora' => $o->hora,
                    'descricao' => $o->descricao,
                    'sugestao' => $o->sugestao,
                    'status' => $o->status ?? 'novo',
                    'created_at' => $o->created_at?->toISOString(),
                    'resolved_at' => $o->resolved_at?->toISOString(),
                ];
            });

        // Separar por status
        $pendentes = $todasOcorrencias->whereNotIn('status', ['resolvido'])->values();
        $resolvidas = $todasOcorrencias->where('status', 'resolvido')->values();

        return response()->json([
            'pendentes' => $pendentes,
            'resolvidas' => $resolvidas,
            'todas' => $todasOcorrencias->values(),
        ]);
    }

    /**
     * API: Lista metadados das fotos disponíveis para uma ocorrência.
     */
    public function fotos(int $id)
    {
        $o = OcorrenciaFrota::findOrFail($id);

        $fotos = [];
        for ($i = 1; $i <= 5; $i++) {
            $blobField = 'foto' . $i;
            $mimeField = 'foto' . $i . '_mime';
            if (!empty($o->$blobField) && !empty($o->$mimeField)) {
                $fotos[] = [
                    'idx' => $i,
                    'mime' => $o->$mimeField,
                    'url' => url("/frota/ocorrencias/api/{$o->id}/foto/{$i}"),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $fotos,
        ]);
    }

    /**
     * API: Retorna o binário de uma foto específica (inline) para visualização.
     */
    public function foto(int $id, int $idx)
    {
        if ($idx < 1 || $idx > 5) {
            abort(404);
        }

        $o = OcorrenciaFrota::findOrFail($id);
        $blobField = 'foto' . $idx;
        $mimeField = 'foto' . $idx . '_mime';

        $data = $o->$blobField;
        $mime = $o->$mimeField ?: 'application/octet-stream';

        if (empty($data)) {
            abort(404);
        }

        $filename = 'ocorrencia-' . $o->id . '-foto' . $idx . '.' . $this->guessExtensionFromMime($mime);

        return response($data, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=86400');
    }

    /**
     * Inferir extensão simples a partir do MIME.
     */
    private function guessExtensionFromMime(string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        return $map[strtolower($mime)] ?? 'bin';
    }

    /**
     * Relatório de Ocorrências - View
     */
    public function relatorioIndex()
    {
        return view('frota.relatorios.ocorrencias');
    }

    /**
     * View de impressão de uma ocorrência específica
     */
    public function print(int $id)
    {
        $ocorrencia = OcorrenciaFrota::with('veiculo')->findOrFail($id);
        
        // Coletar fotos disponíveis
        $fotos = [];
        for ($i = 1; $i <= 10; $i++) {
            $blobField = 'foto' . $i;
            $mimeField = 'foto' . $i . '_mime';
            if (!empty($ocorrencia->$blobField) && !empty($ocorrencia->$mimeField)) {
                $fotos[] = [
                    'idx' => $i,
                    'mime' => $ocorrencia->$mimeField,
                    'url' => url("/frota/ocorrencias/api/{$ocorrencia->id}/foto/{$i}"),
                ];
            }
        }
        
        return view('frota.ocorrencias.print', compact('ocorrencia', 'fotos'));
    }

    /**
     * API: Dados para o relatório de ocorrências
     */
    public function relatorioData(Request $request)
    {
        try {
            $q = OcorrenciaFrota::with(['veiculo:id,placa,marca,modelo'])
                ->orderBy('created_at', 'desc');

            // Filtros
            if ($request->filled('data_inicio')) {
                $q->whereDate('data', '>=', $request->data_inicio);
            }
            if ($request->filled('data_fim')) {
                $q->whereDate('data', '<=', $request->data_fim);
            }
            if ($request->filled('veiculo_id')) {
                $q->where('veiculo_id', $request->veiculo_id);
            }
            if ($request->filled('status')) {
                $st = strtolower(trim((string)$request->status));
                if ($st === 'pendentes') {
                    // Pendentes: sem solução ainda. Considera status nulo, 'novo' ou 'em_andamento' e resolved_at nulo
                    $q->whereNull('resolved_at')
                      ->where(function($w){
                          $w->whereNull('status')
                            ->orWhereRaw('LOWER(status) = ?', ['novo'])
                            ->orWhereRaw('LOWER(status) = ?', ['em_andamento']);
                      });
                } else {
                    $q->whereRaw('LOWER(status) = ?', [$st]);
                }
            }

            $ocorrencias = $q->get()->map(function($o) {
                return [
                    'id' => $o->id,
                    'veiculo_id' => $o->veiculo_id,
                    'motorista_nome' => $o->motorista_nome ? mb_convert_encoding($o->motorista_nome, 'UTF-8', 'UTF-8') : null,
                    'data' => $o->data ? $o->data->format('Y-m-d') : null,
                    'hora' => $o->hora,
                    'descricao' => $o->descricao ? mb_convert_encoding($o->descricao, 'UTF-8', 'UTF-8') : null,
                    'sugestao' => $o->sugestao ? mb_convert_encoding($o->sugestao, 'UTF-8', 'UTF-8') : null,
                    'status' => $o->status,
                    'created_at' => $o->created_at ? $o->created_at->toISOString() : null,
                    'veiculo' => $o->veiculo ? [
                        'id' => $o->veiculo->id,
                        'placa' => $o->veiculo->placa,
                        'marca' => $o->veiculo->marca ? mb_convert_encoding($o->veiculo->marca, 'UTF-8', 'UTF-8') : null,
                        'modelo' => $o->veiculo->modelo ? mb_convert_encoding($o->veiculo->modelo, 'UTF-8', 'UTF-8') : null,
                    ] : null,
                    'foto1' => $o->foto1 ? true : false,
                    'foto2' => $o->foto2 ? true : false,
                    'foto3' => $o->foto3 ? true : false,
                    'foto4' => $o->foto4 ? true : false,
                    'foto5' => $o->foto5 ? true : false,
                    'foto6' => $o->foto6 ? true : false,
                    'foto7' => $o->foto7 ? true : false,
                    'foto8' => $o->foto8 ? true : false,
                    'foto9' => $o->foto9 ? true : false,
                    'foto10' => $o->foto10 ? true : false,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $ocorrencias
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar ocorrências: ' . $e->getMessage()
            ], 500);
        }
    }
}


