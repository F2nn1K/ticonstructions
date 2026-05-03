<?php

namespace App\Http\Controllers;

use App\Models\Apontamento;
use App\Models\Funcionario;
use App\Models\Obra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ApontamentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Daily Time Sheet — listagem e registro de apontamentos do dia
     */
    public function index(Request $request)
    {
        $data = $request->get('data', today()->toDateString());
        $obraId = $request->get('obra_id');
        $funcionarioId = $request->get('funcionario_id');

        $query = Apontamento::with(['funcionario', 'obra'])
            ->where('data', $data);

        if ($obraId) {
            $query->where('obra_id', $obraId);
        }

        if ($funcionarioId) {
            $query->where('funcionario_id', $funcionarioId);
        }

        $apontamentos = $query->orderBy('created_at', 'desc')->get();

        $funcionarios = Funcionario::where('status', 'trabalhando')
            ->orderBy('nome')
            ->get(['id', 'nome', 'funcao']);

        $obras = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        // KPIs do dia
        $totalDia       = $apontamentos->count();
        $totalAprovados = $apontamentos->where('status', 'aprovado')->count();
        $totalPendentes = $apontamentos->where('status', 'pendente')->count();
        $mediaHoras     = $apontamentos->whereNotNull('horas_trabalhadas')->avg('horas_trabalhadas');

        return view('funcionarios.apontamento.index', compact(
            'apontamentos', 'funcionarios', 'obras', 'data',
            'totalDia', 'totalAprovados', 'totalPendentes', 'mediaHoras'
        ));
    }

    /**
     * Salvar novo apontamento
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'funcionario_id'      => 'required|exists:funcionarios,id',
            'data'                => 'required|date',
            'obra_id'             => 'nullable|exists:obras,id',
            'hora_entrada'        => 'nullable|date_format:H:i',
            'hora_saida'          => 'nullable|date_format:H:i|after:hora_entrada',
            'hora_almoco_saida'   => 'nullable|date_format:H:i',
            'hora_almoco_retorno' => 'nullable|date_format:H:i|after:hora_almoco_saida',
            'observacoes'         => 'nullable|string|max:500',
        ]);

        // Não duplicar apontamento do mesmo funcionário no mesmo dia
        $existe = Apontamento::where('funcionario_id', $validated['funcionario_id'])
            ->where('data', $validated['data'])
            ->exists();

        if ($existe) {
            return back()->withErrors(['funcionario_id' => __('Já existe um apontamento para este funcionário nesta data.')])
                ->withInput();
        }

        $apontamento = new Apontamento($validated);
        $apontamento->registrado_por = Auth::id();
        $apontamento->status = 'pendente';

        // Ajustar formato de hora para H:i:s
        foreach (['hora_entrada', 'hora_saida', 'hora_almoco_saida', 'hora_almoco_retorno'] as $campo) {
            if (!empty($apontamento->$campo)) {
                $apontamento->$campo = $apontamento->$campo . ':00';
            }
        }

        $apontamento->calcularHoras();
        $apontamento->save();

        return redirect()->route('funcionarios.apontamento.index', ['data' => $validated['data']])
            ->with('success', __('Apontamento registrado com sucesso!'));
    }

    /**
     * Excluir apontamento
     */
    public function destroy(Apontamento $apontamento)
    {
        $data = $apontamento->data->toDateString();
        $apontamento->delete();

        return redirect()->route('funcionarios.apontamento.index', ['data' => $data])
            ->with('success', __('Apontamento excluído.'));
    }

    /**
     * Approve Time Sheets — página de aprovação
     */
    public function aprovar(Request $request)
    {
        $status     = $request->get('status', 'pendente');
        $obraId     = $request->get('obra_id');
        $dataInicio = $request->get('data_inicio', today()->startOfWeek()->toDateString());
        $dataFim    = $request->get('data_fim', today()->toDateString());

        $query = Apontamento::with(['funcionario', 'obra', 'registrador', 'aprovador'])
            ->whereBetween('data', [$dataInicio, $dataFim]);

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        if ($obraId) {
            $query->where('obra_id', $obraId);
        }

        $apontamentos = $query->orderBy('data', 'desc')
            ->orderBy('funcionario_id')
            ->get();

        $obras = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        // KPIs
        $totalPendentes  = Apontamento::where('status', 'pendente')->count();
        $totalAprovados  = Apontamento::where('status', 'aprovado')
            ->whereBetween('data', [$dataInicio, $dataFim])->count();
        $totalRejeitados = Apontamento::where('status', 'rejeitado')
            ->whereBetween('data', [$dataInicio, $dataFim])->count();

        return view('funcionarios.apontamento.aprovar', compact(
            'apontamentos', 'obras', 'status', 'dataInicio', 'dataFim',
            'totalPendentes', 'totalAprovados', 'totalRejeitados'
        ));
    }

    /**
     * Aprovar ou rejeitar um apontamento
     */
    public function processarAprovacao(Request $request, Apontamento $apontamento)
    {
        $request->validate([
            'acao' => 'required|in:aprovado,rejeitado',
        ]);

        $apontamento->status      = $request->acao;
        $apontamento->aprovado_por = Auth::id();
        $apontamento->aprovado_em  = now();
        $apontamento->save();

        $msg = $request->acao === 'aprovado'
            ? __('Apontamento aprovado!')
            : __('Apontamento rejeitado.');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $apontamento->status, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Aprovar em lote (múltiplos IDs)
     */
    public function aprovarLote(Request $request)
    {
        $request->validate([
            'ids'  => 'required|array',
            'ids.*' => 'exists:apontamentos,id',
            'acao' => 'required|in:aprovado,rejeitado',
        ]);

        Apontamento::whereIn('id', $request->ids)->update([
            'status'      => $request->acao,
            'aprovado_por' => Auth::id(),
            'aprovado_em'  => now(),
        ]);

        $count = count($request->ids);
        $msg   = $request->acao === 'aprovado'
            ? __(':count apontamento(s) aprovado(s)!', ['count' => $count])
            : __(':count apontamento(s) rejeitado(s).', ['count' => $count]);

        return back()->with('success', $msg);
    }
}
