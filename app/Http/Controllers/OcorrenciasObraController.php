<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\ObraFase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OcorrenciasObraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $obraId = $request->get('obra_id');
        $tipo   = $request->get('tipo');
        $dataInicio = $request->get('data_inicio');
        $dataFim    = $request->get('data_fim');

        $query = DB::table('ocorrencias_fase as oc')
            ->join('obras as ob', 'ob.id', '=', 'oc.obra_id')
            ->join('obra_fases as of', 'of.id', '=', 'oc.obra_fase_id')
            ->leftJoin('users as u', 'u.id', '=', 'oc.registrado_por')
            ->select(
                'oc.*',
                'ob.nome as obra_nome', 'ob.codigo as obra_codigo',
                'of.nome_personalizado as fase_nome',
                'u.name as registrador_nome'
            )
            ->orderByDesc('oc.data_ocorrencia');

        if ($obraId)    $query->where('oc.obra_id', $obraId);
        if ($tipo)      $query->where('oc.tipo', $tipo);
        if ($dataInicio) $query->whereDate('oc.data_ocorrencia', '>=', $dataInicio);
        if ($dataFim)    $query->whereDate('oc.data_ocorrencia', '<=', $dataFim);

        $ocorrencias = $query->paginate(20);
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        $totalMes  = DB::table('ocorrencias_fase')
            ->whereMonth('data_ocorrencia', now()->month)->count();
        $totalGeral = DB::table('ocorrencias_fase')->count();
        $totalImpacto = DB::table('ocorrencias_fase')->sum('impacto_dias');

        return view('ocorrencias-obra.index', compact(
            'ocorrencias', 'obras', 'obraId', 'tipo', 'dataInicio', 'dataFim',
            'totalMes', 'totalGeral', 'totalImpacto'
        ));
    }

    public function criar(Request $request)
    {
        $obras = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);
        $fases = collect();
        $obraId = old('obra_id', $request->get('obra_id'));

        if ($obraId) {
            $fases = ObraFase::where('obra_id', $obraId)
                ->orderBy('ordem')
                ->get(['id', 'nome_personalizado']);
        }

        return view('ocorrencias-obra.criar', compact('obras', 'fases', 'obraId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'obra_id'        => 'required|exists:obras,id',
            'obra_fase_id'   => 'required|exists:obra_fases,id',
            'tipo'           => 'required|in:chuva,falta_material,falta_mao_de_obra,erro_projeto,problema_equipamento,acidente,outro',
            'data_ocorrencia'=> 'required|date',
            'impacto_dias'   => 'required|integer|min:0',
            'titulo'         => 'required|string|max:255',
            'descricao'      => 'required|string',
            'acao_tomada'    => 'nullable|string',
        ]);

        $validated['registrado_por'] = Auth::id();
        DB::table('ocorrencias_fase')->insert(array_merge($validated, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return redirect()->route('ocorrencias-obra.index')
            ->with('success', __('Ocorrência registrada com sucesso!'));
    }

    public function fases(Obra $obra)
    {
        return response()->json(
            ObraFase::where('obra_id', $obra->id)
                ->orderBy('ordem')
                ->get(['id', 'nome_personalizado'])
        );
    }
}
