<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\ObraFase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CronogramaController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function index(Request $request)
    {
        $query = Obra::with(['fases.faseCatalogo'])
            ->withCount('fases')
            ->orderByRaw("FIELD(status,'em_andamento','pendente','pausada','concluida','cancelada')")
            ->orderBy('created_at','desc');

        if ($request->filled('status')) $query->where('status',$request->status);
        if ($request->filled('busca')) {
            $query->where(fn($q) => $q
                ->where('nome','like','%'.$request->busca.'%')
                ->orWhere('cliente','like','%'.$request->busca.'%')
                ->orWhere('codigo','like','%'.$request->busca.'%')
            );
        }

        $obras = $query->paginate(12)->withQueryString();
        $totais = [
            'total'        => Obra::count(),
            'em_andamento' => Obra::where('status','em_andamento')->count(),
            'concluidas'   => Obra::where('status','concluida')->count(),
            'pendentes'    => Obra::where('status','pendente')->count(),
        ];
        return view('cronograma.index', compact('obras','totais'));
    }

    public function faseDetalhe(Obra $obra, ObraFase $fase)
    {
        abort_if($fase->obra_id !== $obra->id, 404);

        $tarefas = DB::table('obra_fase_tarefas')
            ->where('obra_fase_id', $fase->id)
            ->orderBy('grupo')->orderBy('ordem')
            ->get();

        $grupos = $tarefas->groupBy('grupo');
        $progressoGrupos = $grupos->map(function($items) {
            $total = $items->count();
            $conc  = $items->where('concluida',1)->count();
            return ['total'=>$total,'concluidas'=>$conc,'perc'=>$total>0?round(($conc/$total)*100):0];
        });

        $totalTarefas     = $tarefas->count();
        $tarefasConcluidas = $tarefas->where('concluida',1)->count();
        $progressoFase    = $totalTarefas>0 ? round(($tarefasConcluidas/$totalTarefas)*100) : 0;

        $outrasFases = ObraFase::where('obra_id',$obra->id)->with('faseCatalogo')->orderBy('ordem')->get();
        $fasesCatalogo = DB::table('fases_catalogo')->where('ativo',1)->orderBy('ordem')->get(['id','nome']);

        return view('cronograma.fase-detalhe', compact(
            'obra','fase','grupos','progressoGrupos',
            'totalTarefas','tarefasConcluidas','progressoFase',
            'outrasFases','fasesCatalogo'
        ));
    }

    public function marcarTarefa(Request $request, int $tarefaId)
    {
        $tarefa = DB::table('obra_fase_tarefas')->where('id',$tarefaId)->first();
        abort_if(!$tarefa,404);

        $concluida = !$tarefa->concluida;
        DB::table('obra_fase_tarefas')->where('id',$tarefaId)->update([
            'concluida'      => $concluida,
            'data_conclusao' => $concluida ? now()->toDateString() : null,
            'concluida_por'  => $concluida ? Auth::id() : null,
            'updated_at'     => now(),
        ]);

        $this->recalcularProgressoFase($tarefa->obra_fase_id);

        if ($request->expectsJson()) {
            $fase = DB::table('obra_fases')->where('id',$tarefa->obra_fase_id)->first();
            return response()->json(['concluida'=>$concluida,'percentual'=>$fase->percentual_realizado??0]);
        }
        return back();
    }

    public function adicionarFase(Request $request, Obra $obra)
    {
        $request->validate([
            'fase_catalogo_id'     => 'required|exists:fases_catalogo,id',
            'nome_personalizado'   => 'nullable|string|max:255',
            'data_inicio_baseline' => 'required|date',
            'data_fim_baseline'    => 'required|date|after_or_equal:data_inicio_baseline',
        ]);

        $ultimaOrdem = ObraFase::where('obra_id',$obra->id)->max('ordem') ?? 0;

        $fase = ObraFase::create([
            'obra_id'              => $obra->id,
            'fase_catalogo_id'     => $request->fase_catalogo_id,
            'ordem'                => $ultimaOrdem + 1,
            'nome_personalizado'   => $request->nome_personalizado,
            'data_inicio_baseline' => $request->data_inicio_baseline,
            'data_fim_baseline'    => $request->data_fim_baseline,
            'data_inicio_planejada'=> $request->data_inicio_baseline,
            'data_fim_planejada'   => $request->data_fim_baseline,
            'status'               => 'pendente',
        ]);

        ObraController::criarTarefasDoCatalogo($fase);

        return back()->with('success', __('Fase adicionada com sucesso!'));
    }

    public function adicionarTarefa(Request $request, ObraFase $fase)
    {
        $request->validate(['nome'=>'required|string|max:255','grupo'=>'nullable|string|max:120']);

        $ultimaOrdem = DB::table('obra_fase_tarefas')->where('obra_fase_id',$fase->id)->max('ordem') ?? 0;
        DB::table('obra_fase_tarefas')->insert([
            'obra_fase_id'       => $fase->id,
            'tarefa_catalogo_id' => null,
            'grupo'              => $request->grupo ?: 'Personalizado',
            'nome'               => $request->nome,
            'concluida'          => false,
            'ordem'              => $ultimaOrdem + 1,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
        return back()->with('success', __('Tarefa adicionada!'));
    }

    public function excluirTarefa(int $tarefaId)
    {
        $tarefa = DB::table('obra_fase_tarefas')->where('id',$tarefaId)->first();
        abort_if(!$tarefa,404);
        DB::table('obra_fase_tarefas')->where('id',$tarefaId)->delete();
        $this->recalcularProgressoFase($tarefa->obra_fase_id);
        return back()->with('success', __('Tarefa removida.'));
    }

    public function tarefasCatalogo(int $faseCatalogoId)
    {
        $grupos = DB::table('fases_catalogo_tarefas')
            ->where('fase_catalogo_id',$faseCatalogoId)->where('ativo',1)
            ->orderBy('ordem')->get()->groupBy('grupo');
        return response()->json($grupos);
    }

    public function ocorrencias(Request $request)
    {
        $ocorrencias = \App\Models\OcorrenciaFaseObra::with(['obraFase.obra','obraFase.faseCatalogo'])
            ->orderBy('data_ocorrencia','desc')->paginate(20);
        return view('cronograma.ocorrencias', compact('ocorrencias'));
    }

    private function recalcularProgressoFase(int $obraFaseId): void
    {
        $total = DB::table('obra_fase_tarefas')->where('obra_fase_id',$obraFaseId)->count();
        $conc  = DB::table('obra_fase_tarefas')->where('obra_fase_id',$obraFaseId)->where('concluida',1)->count();
        $perc  = $total>0 ? round(($conc/$total)*100) : 0;

        $updates = ['percentual_realizado'=>$perc,'updated_at'=>now()];
        $fase    = DB::table('obra_fases')->where('id',$obraFaseId)->first();

        if ($perc >= 100) {
            $updates['status']        = 'concluida';
            $updates['data_fim_real'] = now()->toDateString();
        } elseif ($perc > 0 && $fase && $fase->status === 'pendente') {
            $updates['status']           = 'em_andamento';
            $updates['data_inicio_real'] = now()->toDateString();
        }

        DB::table('obra_fases')->where('id',$obraFaseId)->update($updates);
    }
}
