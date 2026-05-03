<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\ObraFase;
use App\Models\FaseCatalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ObraController extends Controller
{
    public function index()
    {
        $obras = Obra::with(['faseAtiva', 'fases'])
            ->withCount('lancamentos')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('obras.index', compact('obras'));
    }

    public function create()
    {
        $fases = FaseCatalogo::ativos()->get();
        return view('obras.create', compact('fases'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'                 => 'required|string|max:255',
            'cliente'              => 'nullable|string|max:255',
            'data_inicio_prevista' => 'required|date',
            'fases'                => 'required|array|min:1',
            'fases.*.fase_catalogo_id' => 'required|exists:fases_catalogo,id',
            'fases.*.data_inicio_baseline' => 'required|date',
            'fases.*.data_fim_baseline'    => 'required|date|after_or_equal:fases.*.data_inicio_baseline',
        ]);

        DB::transaction(function () use ($request) {
            $obra = Obra::create([
                'nome'                  => $request->nome,
                'codigo'                => $request->codigo,
                'descricao'             => $request->descricao,
                'endereco'              => $request->endereco,
                'cidade'                => $request->cidade,
                'estado'                => $request->estado,
                'cliente'               => $request->cliente,
                'responsavel_tecnico'   => $request->responsavel_tecnico,
                'valor_contrato'        => $request->valor_contrato,
                'area_total'            => $request->area_total,
                'data_inicio_prevista'  => $request->data_inicio_prevista,
                'data_fim_prevista'     => $request->data_fim_prevista,
                'status'                => 'planejamento',
                'created_by'            => Auth::id(),
            ]);

            foreach ($request->fases as $ordem => $faseData) {
                $obraFase = ObraFase::create([
                    'obra_id'              => $obra->id,
                    'fase_catalogo_id'     => $faseData['fase_catalogo_id'],
                    'ordem'                => $ordem + 1,
                    'nome_personalizado'   => $faseData['nome_personalizado'] ?? null,
                    'data_inicio_baseline' => $faseData['data_inicio_baseline'],
                    'data_fim_baseline'    => $faseData['data_fim_baseline'],
                    'data_inicio_planejada'=> $faseData['data_inicio_baseline'],
                    'data_fim_planejada'   => $faseData['data_fim_baseline'],
                    'status'               => $ordem === 0 ? 'em_andamento' : 'pendente',
                    'data_inicio_real'     => $ordem === 0 ? now()->toDateString() : null,
                ]);

                // Auto-popular sub-tarefas do catálogo
                $this->criarTarefasDoCatalogo($obraFase);
            }

            // Inicia a obra automaticamente
            $obra->update(['status' => 'em_andamento']);
        });

        return redirect()->route('obras.index')
            ->with('success', 'Obra criada com sucesso!');
    }

    public function show(Obra $obra)
    {
        $obra->load([
            'fases.faseCatalogo',
            'fases.lancamentos',
            'fases.ocorrencias',
            'faseAtiva',
        ]);

        $custoTotalReal   = $obra->lancamentos()->sum('custo_total_real');
        $custoTotalOrcado = $obra->lancamentos()->sum('custo_total_orcado');

        $faseAtiva = $obra->faseAtiva;

        return view('obras.show', compact('obra', 'custoTotalReal', 'custoTotalOrcado', 'faseAtiva'));
    }

    public function edit(Obra $obra)
    {
        $fases = FaseCatalogo::ativos()->get();
        $obra->load('fases.faseCatalogo');
        return view('obras.edit', compact('obra', 'fases'));
    }

    public function update(Request $request, Obra $obra)
    {
        $request->validate([
            'nome'   => 'required|string|max:255',
            'status' => 'required|in:planejamento,em_andamento,concluida,suspensa,cancelada',
        ]);

        $obra->update($request->only([
            'nome', 'codigo', 'descricao', 'endereco', 'cidade', 'estado',
            'cliente', 'responsavel_tecnico', 'valor_contrato', 'area_total',
            'data_inicio_prevista', 'data_fim_prevista', 'status',
        ]));

        return redirect()->route('obras.show', $obra)
            ->with('success', 'Obra atualizada com sucesso!');
    }

    public function destroy(Obra $obra)
    {
        $obra->delete();
        return redirect()->route('obras.index')
            ->with('success', 'Obra removida com sucesso!');
    }

    /**
     * Cria as tarefas/sub-fases de uma obra_fase a partir do catálogo.
     * Chamado ao criar/adicionar fases.
     */
    public static function criarTarefasDoCatalogo(ObraFase $obraFase): void
    {
        if (!$obraFase->fase_catalogo_id) return;

        $tarefas = \Illuminate\Support\Facades\DB::table('fases_catalogo_tarefas')
            ->where('fase_catalogo_id', $obraFase->fase_catalogo_id)
            ->where('ativo', 1)
            ->orderBy('ordem')
            ->get();

        foreach ($tarefas as $t) {
            \Illuminate\Support\Facades\DB::table('obra_fase_tarefas')->insert([
                'obra_fase_id'       => $obraFase->id,
                'tarefa_catalogo_id' => $t->id,
                'nome'               => $t->nome,
                'concluida'          => false,
                'data_conclusao'     => null,
                'concluida_por'      => null,
                'observacoes'        => null,
                'ordem'              => $t->ordem,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }
}
