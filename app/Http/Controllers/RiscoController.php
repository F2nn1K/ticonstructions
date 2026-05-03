<?php

namespace App\Http\Controllers;

use App\Models\Risco;
use App\Models\Obra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiscoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $obraId   = $request->get('obra_id');
        $status   = $request->get('status');
        $categoria = $request->get('categoria');

        $query = Risco::with(['obra', 'registrador'])->orderByDesc('nivel_risco');

        if ($obraId)    $query->where('obra_id', $obraId);
        if ($status)    $query->where('status', $status);
        if ($categoria) $query->where('categoria', $categoria);

        $riscos = $query->get();
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        // KPIs
        $totalCriticos = $riscos->filter(fn($r) => ($r->probabilidade * $r->impacto) >= 15)->count();
        $totalAltos    = $riscos->filter(fn($r) => ($r->probabilidade * $r->impacto) >= 8 && ($r->probabilidade * $r->impacto) < 15)->count();
        $totalAbertos  = $riscos->whereIn('status', ['identificado', 'em_mitigacao'])->count();
        $totalMitigados= $riscos->where('status', 'mitigado')->count();

        return view('riscos.index', compact(
            'riscos', 'obras', 'obraId', 'status', 'categoria',
            'totalCriticos', 'totalAltos', 'totalAbertos', 'totalMitigados'
        ));
    }

    public function criar()
    {
        $obras = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);
        return view('riscos.criar', compact('obras'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'obra_id'       => 'nullable|exists:obras,id',
            'titulo'        => 'required|string|max:255',
            'descricao'     => 'nullable|string',
            'categoria'     => 'required|in:seguranca,financeiro,ambiental,cronograma,qualidade,outro',
            'probabilidade' => 'required|integer|min:1|max:5',
            'impacto'       => 'required|integer|min:1|max:5',
            'plano_acao'    => 'nullable|string',
            'responsavel'   => 'nullable|string|max:255',
            'prazo'         => 'nullable|date',
            'status'        => 'required|in:identificado,em_mitigacao,mitigado,aceito',
        ]);

        $validated['registrado_por'] = Auth::id();
        Risco::create($validated);

        return redirect()->route('riscos.index')
            ->with('success', __('Risco registrado com sucesso!'));
    }

    public function edit(Risco $risco)
    {
        $obras = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);
        return view('riscos.criar', compact('risco', 'obras'));
    }

    public function update(Request $request, Risco $risco)
    {
        $validated = $request->validate([
            'obra_id'       => 'nullable|exists:obras,id',
            'titulo'        => 'required|string|max:255',
            'descricao'     => 'nullable|string',
            'categoria'     => 'required|in:seguranca,financeiro,ambiental,cronograma,qualidade,outro',
            'probabilidade' => 'required|integer|min:1|max:5',
            'impacto'       => 'required|integer|min:1|max:5',
            'plano_acao'    => 'nullable|string',
            'responsavel'   => 'nullable|string|max:255',
            'prazo'         => 'nullable|date',
            'status'        => 'required|in:identificado,em_mitigacao,mitigado,aceito',
        ]);

        $risco->update($validated);

        return redirect()->route('riscos.index')
            ->with('success', __('Risco atualizado com sucesso!'));
    }

    public function destroy(Risco $risco)
    {
        $risco->delete();
        return back()->with('success', __('Risco excluído.'));
    }
}
