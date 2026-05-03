<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\ObraFase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QualidadeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─── CHECKLISTS ───────────────────────────────────────────────────────────

    public function checklists(Request $request)
    {
        $obraId    = $request->get('obra_id');
        $categoria = $request->get('categoria');

        $query = DB::table('qualidade_checklists as qc')
            ->leftJoin('obras as ob', 'ob.id', '=', 'qc.obra_id')
            ->leftJoin('users as u', 'u.id', '=', 'qc.registrado_por')
            ->select('qc.*', 'ob.nome as obra_nome', 'u.name as autor')
            ->orderByDesc('qc.created_at');

        if ($obraId)    $query->where('qc.obra_id', $obraId);
        if ($categoria) $query->where('qc.categoria', $categoria);

        $checklists = $query->paginate(20);
        $obras      = Obra::orderBy('nome')->get(['id', 'nome']);

        $total       = DB::table('qualidade_checklists')->count();
        $totalObraId = $obraId ? DB::table('qualidade_checklists')->where('obra_id', $obraId)->count() : null;

        return view('qualidade.checklists', compact('checklists', 'obras', 'obraId', 'categoria', 'total'));
    }

    public function checklistCriar()
    {
        $obras = Obra::orderBy('nome')->get(['id', 'nome']);
        return view('qualidade.checklist-criar', compact('obras'));
    }

    public function checklistStore(Request $request)
    {
        $validated = $request->validate([
            'titulo'    => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'categoria' => 'required|in:estrutura,acabamento,instalacao_eletrica,instalacao_hidraulica,seguranca,outro',
            'obra_id'   => 'nullable|exists:obras,id',
            'itens'     => 'nullable|array',
            'itens.*'   => 'nullable|string|max:500',
        ]);

        $itens = array_values(array_filter($validated['itens'] ?? [], fn($i) => !empty(trim($i))));

        DB::table('qualidade_checklists')->insert([
            'obra_id'        => $validated['obra_id'] ?? null,
            'titulo'         => $validated['titulo'],
            'descricao'      => $validated['descricao'] ?? null,
            'categoria'      => $validated['categoria'],
            'itens'          => json_encode($itens),
            'registrado_por' => Auth::id(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('qualidade.checklists')
            ->with('success', __('Checklist criado com sucesso!'));
    }

    public function checklistDestroy(int $id)
    {
        DB::table('qualidade_checklists')->where('id', $id)->delete();
        return back()->with('success', __('Checklist excluído.'));
    }

    // ─── INSPEÇÕES ────────────────────────────────────────────────────────────

    public function inspecoes(Request $request)
    {
        $obraId = $request->get('obra_id');
        $status = $request->get('status');

        $query = DB::table('qualidade_inspecoes as qi')
            ->join('obras as ob', 'ob.id', '=', 'qi.obra_id')
            ->leftJoin('obra_fases as of', 'of.id', '=', 'qi.obra_fase_id')
            ->leftJoin('qualidade_checklists as qc', 'qc.id', '=', 'qi.checklist_id')
            ->leftJoin('users as u', 'u.id', '=', 'qi.registrado_por')
            ->select('qi.*', 'ob.nome as obra_nome', 'of.nome_personalizado as fase_nome',
                     'qc.titulo as checklist_titulo', 'u.name as autor')
            ->orderByDesc('qi.data_inspecao');

        if ($obraId) $query->where('qi.obra_id', $obraId);
        if ($status) $query->where('qi.status', $status);

        $inspecoes  = $query->paginate(20);
        $obras      = Obra::orderBy('nome')->get(['id', 'nome']);
        $checklists = DB::table('qualidade_checklists')->orderBy('titulo')->get(['id', 'titulo']);

        $totais = DB::table('qualidade_inspecoes')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('qualidade.inspecoes', compact('inspecoes', 'obras', 'checklists', 'obraId', 'status', 'totais'));
    }

    public function inspecaoCriar(Request $request)
    {
        $obras      = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);
        $checklists = DB::table('qualidade_checklists')->orderBy('titulo')->get();
        $obraId     = old('obra_id', $request->get('obra_id'));
        $fases      = $obraId ? ObraFase::where('obra_id', $obraId)->orderBy('ordem')->get(['id', 'nome_personalizado']) : collect();

        return view('qualidade.inspecao-criar', compact('obras', 'checklists', 'fases', 'obraId'));
    }

    public function inspecaoStore(Request $request)
    {
        $validated = $request->validate([
            'obra_id'       => 'required|exists:obras,id',
            'obra_fase_id'  => 'nullable|exists:obra_fases,id',
            'checklist_id'  => 'nullable|exists:qualidade_checklists,id',
            'titulo'        => 'required|string|max:255',
            'data_inspecao' => 'required|date',
            'responsavel'   => 'nullable|string|max:255',
            'status'        => 'required|in:pendente,em_andamento,concluida,reprovada',
            'observacoes'   => 'nullable|string',
        ]);

        $validated['registrado_por'] = Auth::id();
        DB::table('qualidade_inspecoes')->insert(array_merge($validated, [
            'created_at' => now(), 'updated_at' => now(),
        ]));

        return redirect()->route('qualidade.inspecoes')
            ->with('success', __('Inspeção registrada com sucesso!'));
    }

    // ─── NÃO CONFORMIDADES ────────────────────────────────────────────────────

    public function naoConformidades(Request $request)
    {
        $obraId   = $request->get('obra_id');
        $status   = $request->get('status');
        $gravidade = $request->get('gravidade');

        $query = DB::table('qualidade_nao_conformidades as nc')
            ->join('obras as ob', 'ob.id', '=', 'nc.obra_id')
            ->leftJoin('obra_fases as of', 'of.id', '=', 'nc.obra_fase_id')
            ->leftJoin('users as u', 'u.id', '=', 'nc.registrado_por')
            ->select('nc.*', 'ob.nome as obra_nome', 'of.nome_personalizado as fase_nome', 'u.name as autor')
            ->orderByDesc('nc.created_at');

        if ($obraId)   $query->where('nc.obra_id', $obraId);
        if ($status)   $query->where('nc.status', $status);
        if ($gravidade) $query->where('nc.gravidade', $gravidade);

        $ncs   = $query->paginate(20);
        $obras = Obra::orderBy('nome')->get(['id', 'nome']);

        $totalAberta    = DB::table('qualidade_nao_conformidades')->where('status', 'aberta')->count();
        $totalCritica   = DB::table('qualidade_nao_conformidades')->where('gravidade', 'critica')->count();
        $totalResolvida = DB::table('qualidade_nao_conformidades')->where('status', 'resolvida')->count();
        $totalGeral     = DB::table('qualidade_nao_conformidades')->count();

        return view('qualidade.nao-conformidades', compact(
            'ncs', 'obras', 'obraId', 'status', 'gravidade',
            'totalAberta', 'totalCritica', 'totalResolvida', 'totalGeral'
        ));
    }

    public function naoConformidadeCriar(Request $request)
    {
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);
        $obraId = old('obra_id', $request->get('obra_id'));
        $fases  = $obraId ? ObraFase::where('obra_id', $obraId)->orderBy('ordem')->get(['id', 'nome_personalizado']) : collect();

        return view('qualidade.nao-conformidade-criar', compact('obras', 'fases', 'obraId'));
    }

    public function naoConformidadeStore(Request $request)
    {
        $validated = $request->validate([
            'obra_id'        => 'required|exists:obras,id',
            'obra_fase_id'   => 'nullable|exists:obra_fases,id',
            'titulo'         => 'required|string|max:255',
            'descricao'      => 'required|string',
            'gravidade'      => 'required|in:leve,moderada,grave,critica',
            'status'         => 'required|in:aberta,em_correcao,resolvida,aceita',
            'prazo_correcao' => 'nullable|date',
            'acao_corretiva' => 'nullable|string',
        ]);

        $validated['registrado_por'] = Auth::id();
        DB::table('qualidade_nao_conformidades')->insert(array_merge($validated, [
            'created_at' => now(), 'updated_at' => now(),
        ]));

        return redirect()->route('qualidade.nao-conformidades')
            ->with('success', __('Não conformidade registrada com sucesso!'));
    }

    public function naoConformidadeUpdate(Request $request, int $id)
    {
        $request->validate(['status' => 'required|in:aberta,em_correcao,resolvida,aceita']);
        DB::table('qualidade_nao_conformidades')->where('id', $id)->update([
            'status'         => $request->status,
            'acao_corretiva' => $request->acao_corretiva,
            'updated_at'     => now(),
        ]);
        return back()->with('success', __('Status atualizado.'));
    }

    // ─── API ─────────────────────────────────────────────────────────────────

    public function fasesObra(Obra $obra)
    {
        return response()->json(
            ObraFase::where('obra_id', $obra->id)->orderBy('ordem')->get(['id', 'nome_personalizado'])
        );
    }

    public function checklistItens(int $id)
    {
        $cl = DB::table('qualidade_checklists')->where('id', $id)->first();
        return response()->json($cl ? json_decode($cl->itens ?? '[]') : []);
    }
}
