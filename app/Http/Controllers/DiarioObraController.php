<?php

namespace App\Http\Controllers;

use App\Models\DiarioObra;
use App\Models\DiarioMaoDeObra;
use App\Models\DiarioEquipamento;
use App\Models\DiarioAtividade;
use App\Models\Obra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DiarioObraController extends Controller
{
    // ── Lista global ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = DiarioObra::with(['obra', 'fase.faseCatalogo', 'responsavel', 'atividades'])
            ->recentes();

        if ($request->filled('obra_id'))  $query->where('obra_id', $request->obra_id);
        if ($request->filled('tipo'))     $query->where('tipo', $request->tipo);
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('data_de'))  $query->whereDate('data_registro', '>=', $request->data_de);
        if ($request->filled('data_ate')) $query->whereDate('data_registro', '<=', $request->data_ate);

        $registros = $query->paginate(15)->withQueryString();
        $obras     = Obra::orderBy('nome')->get(['id', 'nome']);

        $totais = [
            'total'       => DiarioObra::count(),
            'hoje'        => DiarioObra::whereDate('data_registro', today())->count(),
            'semana'      => DiarioObra::whereDate('data_registro', '>=', now()->startOfWeek())->count(),
            'ocorrencias' => DiarioObra::whereNotNull('ocorrencias')->where('ocorrencias', '!=', '')->count(),
            'rascunhos'   => DiarioObra::where('status', 'rascunho')->count(),
        ];

        return view('diario.index', compact('registros', 'obras', 'totais'));
    }

    // ── Lista por obra ────────────────────────────────────────────────────────
    public function porObra(Request $request, Obra $obra)
    {
        $registros = DiarioObra::with(['fase.faseCatalogo', 'responsavel'])
            ->where('obra_id', $obra->id)
            ->recentes()
            ->paginate(15);

        return view('diario.por-obra', compact('obra', 'registros'));
    }

    // ── Formulário de criação ─────────────────────────────────────────────────
    public function create(Request $request)
    {
        $obras    = Obra::whereIn('status', ['em_andamento', 'pendente'])->orderBy('nome')->get();
        $obraSel  = $request->filled('obra_id') ? Obra::with('fases.faseCatalogo')->find($request->obra_id) : null;
        $usuarios = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('diario.create', compact('obras', 'obraSel', 'usuarios'));
    }

    // ── Salvar novo registro ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'obra_id'        => 'required|exists:obras,id',
            'data_registro'  => 'required|date',
            'tipo'           => 'required|in:diario,semanal',
            'fotos.*'        => 'nullable|image|max:10240',
        ]);

        DB::transaction(function () use ($request) {

            // Upload de fotos com pasta
            $fotoPaths = [];
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $idx => $foto) {
                    $pasta  = $request->input("foto_pasta.{$idx}", 'Pasta 1');
                    $path   = $foto->store('diario-obra/' . $request->obra_id, 'public');
                    $fotoPaths[] = ['pasta' => $pasta, 'caminho' => $path];
                }
            }

            $diario = DiarioObra::create([
                'numero'         => DiarioObra::proximoNumero(),
                'status'         => $request->input('status', 'rascunho'),
                'obra_id'        => $request->obra_id,
                'obra_fase_id'   => $request->obra_fase_id ?: null,
                'responsavel_id' => $request->responsavel_id ?: Auth::id(),
                'created_by'     => Auth::id(),
                'data_registro'  => $request->data_registro,
                'tipo'           => $request->tipo,
                'titulo'         => $request->titulo,
                'local_area'     => $request->local_area,
                'total_trabalhadores' => $request->total_trabalhadores,
                // Clima por turno
                'tempo_manha'    => $request->filled('tempo_manha_clima') ? [
                    'status' => $request->input('tempo_manha_status', 'praticavel'),
                    'clima'  => $request->input('tempo_manha_clima', 'sol'),
                ] : null,
                'tempo_tarde'    => $request->filled('tempo_tarde_clima') ? [
                    'status' => $request->input('tempo_tarde_status', 'praticavel'),
                    'clima'  => $request->input('tempo_tarde_clima', 'sol'),
                ] : null,
                'tempo_noite'    => $request->filled('tempo_noite_clima') ? [
                    'status' => $request->input('tempo_noite_status', 'praticavel'),
                    'clima'  => $request->input('tempo_noite_clima', 'sol'),
                ] : null,
                // Legado / texto livre
                'atividades_executadas' => $request->atividades_executadas ?? '',
                'materiais_utilizados'  => $request->materiais_utilizados,
                'ocorrencias'           => $request->ocorrencias,
                'solucoes_adotadas'     => $request->solucoes_adotadas,
                'percentual_avanco_dia' => $request->percentual_avanco_dia,
                'observacoes'           => $request->observacoes,
                'comentarios'           => $request->comentarios,
                'fotos'                 => count($fotoPaths) ? $fotoPaths : null,
            ]);

            $this->salvarMaoDeObra($diario->id, $request->input('mao_de_obra', []));
            $this->salvarEquipamentos($diario->id, $request->input('equipamentos', []));
            $this->salvarAtividades($diario->id, $request->input('atividades', []));
        });

        if ($request->filled('redirect_obra')) {
            return redirect()->route('obras.show', $request->obra_id)
                ->with('success', 'Diário de obra salvo com sucesso.');
        }

        return redirect()->route('diario.index')
            ->with('success', 'Diário de obra salvo com sucesso.');
    }

    // ── Visualizar ────────────────────────────────────────────────────────────
    public function show(DiarioObra $diario)
    {
        $diario->load([
            'obra', 'fase.faseCatalogo', 'responsavel', 'autor',
            'maoDeObra', 'equipamentos', 'atividades',
        ]);
        return view('diario.show', compact('diario'));
    }

    // ── Formulário de edição ──────────────────────────────────────────────────
    public function edit(DiarioObra $diario)
    {
        $obras    = Obra::orderBy('nome')->get();
        $obraSel  = Obra::with('fases.faseCatalogo')->find($diario->obra_id);
        $usuarios = \App\Models\User::orderBy('name')->get(['id', 'name']);

        $diario->load(['maoDeObra', 'equipamentos', 'atividades']);

        return view('diario.edit', compact('diario', 'obras', 'obraSel', 'usuarios'));
    }

    // ── Atualizar ─────────────────────────────────────────────────────────────
    public function update(Request $request, DiarioObra $diario)
    {
        $request->validate([
            'obra_id'       => 'required|exists:obras,id',
            'data_registro' => 'required|date',
            'tipo'          => 'required|in:diario,semanal',
            'fotos.*'       => 'nullable|image|max:10240',
        ]);

        DB::transaction(function () use ($request, $diario) {

            // Fotos existentes + novas
            $fotoPaths = is_array($diario->fotos) ? $diario->fotos : [];

            // Remover fotos marcadas para exclusão
            if ($request->filled('fotos_remover')) {
                foreach ((array) $request->fotos_remover as $caminho) {
                    Storage::disk('public')->delete($caminho);
                    $fotoPaths = array_values(array_filter($fotoPaths, function ($f) use ($caminho) {
                        $c = is_array($f) ? ($f['caminho'] ?? '') : $f;
                        return $c !== $caminho;
                    }));
                }
            }

            // Novas fotos
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $idx => $foto) {
                    $pasta  = $request->input("foto_pasta.{$idx}", 'Pasta 1');
                    $path   = $foto->store('diario-obra/' . $diario->obra_id, 'public');
                    $fotoPaths[] = ['pasta' => $pasta, 'caminho' => $path];
                }
            }

            $diario->update([
                'status'         => $request->input('status', $diario->status),
                'obra_fase_id'   => $request->obra_fase_id ?: null,
                'responsavel_id' => $request->responsavel_id ?: $diario->responsavel_id,
                'data_registro'  => $request->data_registro,
                'tipo'           => $request->tipo,
                'titulo'         => $request->titulo,
                'local_area'     => $request->local_area,
                'total_trabalhadores' => $request->total_trabalhadores,
                'tempo_manha'    => $request->filled('tempo_manha_clima') ? [
                    'status' => $request->input('tempo_manha_status', 'praticavel'),
                    'clima'  => $request->input('tempo_manha_clima', 'sol'),
                ] : null,
                'tempo_tarde'    => $request->filled('tempo_tarde_clima') ? [
                    'status' => $request->input('tempo_tarde_status', 'praticavel'),
                    'clima'  => $request->input('tempo_tarde_clima', 'sol'),
                ] : null,
                'tempo_noite'    => $request->filled('tempo_noite_clima') ? [
                    'status' => $request->input('tempo_noite_status', 'praticavel'),
                    'clima'  => $request->input('tempo_noite_clima', 'sol'),
                ] : null,
                'atividades_executadas' => $request->atividades_executadas ?? $diario->atividades_executadas,
                'materiais_utilizados'  => $request->materiais_utilizados,
                'ocorrencias'           => $request->ocorrencias,
                'solucoes_adotadas'     => $request->solucoes_adotadas,
                'percentual_avanco_dia' => $request->percentual_avanco_dia,
                'observacoes'           => $request->observacoes,
                'comentarios'           => $request->comentarios,
                'fotos'                 => count($fotoPaths) ? array_values($fotoPaths) : null,
            ]);

            // Recriar itens (delete + insert)
            $diario->maoDeObra()->delete();
            $diario->equipamentos()->delete();
            $diario->atividades()->delete();

            $this->salvarMaoDeObra($diario->id, $request->input('mao_de_obra', []));
            $this->salvarEquipamentos($diario->id, $request->input('equipamentos', []));
            $this->salvarAtividades($diario->id, $request->input('atividades', []));
        });

        return redirect()->route('diario.show', $diario)
            ->with('success', 'Diário atualizado com sucesso.');
    }

    // ── Excluir ───────────────────────────────────────────────────────────────
    public function destroy(DiarioObra $diario)
    {
        if (is_array($diario->fotos)) {
            foreach ($diario->fotos as $item) {
                $path = is_array($item) ? ($item['caminho'] ?? '') : $item;
                if ($path) Storage::disk('public')->delete($path);
            }
        }
        $diario->delete();

        return back()->with('success', 'Registro excluído.');
    }

    // ── API: fases ─────────────────────────────────────────────────────────────
    public function fasesObra(Obra $obra)
    {
        $fases = $obra->fases()
            ->with('faseCatalogo')
            ->get(['id', 'fase_catalogo_id', 'nome_personalizado', 'status'])
            ->map(fn($f) => ['id' => $f->id, 'nome' => $f->nome]);

        return response()->json($fases);
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function salvarMaoDeObra(int $diarioId, array $itens): void
    {
        foreach ($itens as $ordem => $item) {
            $funcao = trim($item['funcao'] ?? '');
            if (!$funcao) continue;
            DiarioMaoDeObra::create([
                'diario_obra_id'         => $diarioId,
                'quantidade'             => (int) ($item['quantidade'] ?? 1),
                'funcao'                 => $funcao,
                'profissional_fornecedor'=> $item['profissional_fornecedor'] ?? null,
                'observacao'             => $item['observacao'] ?? null,
                'ordem'                  => $ordem,
            ]);
        }
    }

    private function salvarEquipamentos(int $diarioId, array $itens): void
    {
        foreach ($itens as $ordem => $item) {
            $desc = trim($item['descricao'] ?? '');
            if (!$desc) continue;
            DiarioEquipamento::create([
                'diario_obra_id' => $diarioId,
                'quantidade'     => (int) ($item['quantidade'] ?? 1),
                'descricao'      => $desc,
                'ordem'          => $ordem,
            ]);
        }
    }

    private function salvarAtividades(int $diarioId, array $itens): void
    {
        // Rastreia quais fases precisam recalcular progresso
        $fasesParaRecalcular = [];

        foreach ($itens as $ordem => $item) {
            $desc = trim($item['descricao'] ?? '');
            if (!$desc) continue;

            $tarefaId = !empty($item['obra_fase_tarefa_id']) ? (int) $item['obra_fase_tarefa_id'] : null;
            $status   = $item['status_atividade'] ?? 'em_andamento';

            DiarioAtividade::create([
                'diario_obra_id'      => $diarioId,
                'obra_fase_tarefa_id' => $tarefaId,
                'descricao'           => $desc,
                'qtde_orcada'         => $item['qtde_orcada'] ?? null,
                'qtde_realizada'      => $item['qtde_realizada'] ?? null,
                'evolucao_percentual' => $item['evolucao_percentual'] ?? null,
                'status_atividade'    => $status,
                'comentario'          => $item['comentario'] ?? null,
                'ordem'               => $ordem,
            ]);

            // Se a atividade está finalizada E tem tarefa vinculada → marcar no cronograma
            if ($tarefaId && $status === 'finalizada') {
                $tarefa = \App\Models\ObraFaseTarefa::find($tarefaId);
                if ($tarefa && !$tarefa->concluida) {
                    $tarefa->update([
                        'concluida'      => true,
                        'data_conclusao' => now()->toDateString(),
                        'concluida_por'  => Auth::id(),
                    ]);
                    $fasesParaRecalcular[] = $tarefa->obra_fase_id;
                }
            }
        }

        // Recalcular progresso de cada fase afetada
        foreach (array_unique($fasesParaRecalcular) as $faseId) {
            $this->recalcularProgressoFase($faseId);
        }
    }

    private function recalcularProgressoFase(int $obraFaseId): void
    {
        $total = DB::table('obra_fase_tarefas')->where('obra_fase_id', $obraFaseId)->count();
        $conc  = DB::table('obra_fase_tarefas')->where('obra_fase_id', $obraFaseId)->where('concluida', 1)->count();
        $perc  = $total > 0 ? round(($conc / $total) * 100) : 0;

        $updates = ['percentual_realizado' => $perc, 'updated_at' => now()];
        $fase    = DB::table('obra_fases')->where('id', $obraFaseId)->first();

        if ($perc >= 100) {
            $updates['status']         = 'concluida';
            $updates['data_fim_real']  = now()->toDateString();
        } elseif ($perc > 0 && $fase && $fase->status === 'pendente') {
            $updates['status']              = 'em_andamento';
            $updates['data_inicio_real']    = now()->toDateString();
        }

        DB::table('obra_fases')->where('id', $obraFaseId)->update($updates);
    }

    // ── API: tarefas da fase ──────────────────────────────────────────────────
    public function tarefasFase(\App\Models\ObraFase $fase)
    {
        $tarefas = DB::table('obra_fase_tarefas')
            ->where('obra_fase_id', $fase->id)
            ->orderBy('grupo')
            ->orderBy('ordem')
            ->get(['id', 'nome', 'grupo', 'concluida']);

        return response()->json($tarefas);
    }
}
