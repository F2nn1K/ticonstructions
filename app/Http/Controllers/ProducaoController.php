<?php

namespace App\Http\Controllers;

use App\Models\Medicao;
use App\Models\Obra;
use App\Models\ObraFase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProducaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Avanço Físico — dashboard de progresso das obras
     */
    public function index(Request $request)
    {
        $obraId = $request->get('obra_id');

        $obrasQuery = Obra::with(['fases' => function ($q) {
            $q->orderBy('ordem');
        }])->orderBy('nome');

        if ($obraId) {
            $obrasQuery->where('id', $obraId);
        }

        $obras = $obrasQuery->get();

        // Para cada obra, calcular percentual geral com base nas fases
        $obras->each(function ($obra) {
            $fases = $obra->fases;
            if ($fases->isEmpty()) {
                $obra->avanco_geral = 0;
            } else {
                $obra->avanco_geral = round($fases->avg('percentual_realizado'), 1);
            }

            // Última medição
            $obra->ultima_medicao = Medicao::where('obra_id', $obra->id)
                ->where('status', 'aprovado')
                ->orderBy('data_medicao', 'desc')
                ->first();

            // Total de medições pendentes
            $obra->medicoes_pendentes = Medicao::where('obra_id', $obra->id)
                ->where('status', 'pendente')
                ->count();
        });

        $todasObras = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        // KPIs globais
        $totalObras       = $obras->count();
        $mediaAvanco      = $obras->avg('avanco_geral');
        $totalPendentes   = Medicao::where('status', 'pendente')->count();
        $totalAprovadas   = Medicao::where('status', 'aprovado')
            ->whereMonth('data_medicao', now()->month)->count();

        return view('producao.index', compact(
            'obras', 'todasObras', 'obraId',
            'totalObras', 'mediaAvanco', 'totalPendentes', 'totalAprovadas'
        ));
    }

    /**
     * Formulário de lançamento de medição
     */
    public function medicao(Request $request)
    {
        $obras = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);
        $obraId = $request->old('obra_id', $request->get('obra_id'));

        $fases = collect();
        if ($obraId) {
            $fases = ObraFase::where('obra_id', $obraId)
                ->orderBy('ordem')
                ->get(['id', 'nome_personalizado', 'percentual_realizado', 'status']);
        }

        return view('producao.medicao', compact('obras', 'fases', 'obraId'));
    }

    /**
     * Salvar nova medição
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'obra_id'           => 'required|exists:obras,id',
            'obra_fase_id'      => 'nullable|exists:obra_fases,id',
            'data_medicao'      => 'required|date',
            'percentual_medido' => 'required|numeric|min:0|max:100',
            'valor_medicao'     => 'nullable|numeric|min:0',
            'descricao'         => 'required|string|max:1000',
            'observacoes'       => 'nullable|string|max:500',
        ]);

        // Calcular percentual acumulado da obra
        $ultimaAprovada = Medicao::where('obra_id', $validated['obra_id'])
            ->where('status', 'aprovado')
            ->when(!empty($validated['obra_fase_id']), fn($q) => $q->where('obra_fase_id', $validated['obra_fase_id']))
            ->orderBy('data_medicao', 'desc')
            ->value('percentual_acumulado') ?? 0;

        $validated['percentual_acumulado'] = min(100, $ultimaAprovada + $validated['percentual_medido']);
        $validated['registrado_por'] = Auth::id();
        $validated['status'] = 'pendente';

        Medicao::create($validated);

        return redirect()->route('producao.index')
            ->with('success', __('Medição lançada com sucesso! Aguardando aprovação.'));
    }

    /**
     * Buscar fases de uma obra via AJAX
     */
    public function fases(Obra $obra)
    {
        $fases = ObraFase::where('obra_id', $obra->id)
            ->orderBy('ordem')
            ->get(['id', 'nome_personalizado', 'percentual_realizado', 'status']);

        return response()->json($fases);
    }

    /**
     * Aprovar Medições — listagem para aprovação
     */
    public function aprovacao(Request $request)
    {
        $status     = $request->get('status', 'pendente');
        $obraId     = $request->get('obra_id');
        $dataInicio = $request->get('data_inicio', today()->startOfMonth()->toDateString());
        $dataFim    = $request->get('data_fim', today()->toDateString());

        $query = Medicao::with(['obra', 'fase', 'registrador', 'aprovador'])
            ->whereBetween('data_medicao', [$dataInicio, $dataFim]);

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        if ($obraId) {
            $query->where('obra_id', $obraId);
        }

        $medicoes = $query->orderBy('data_medicao', 'desc')->get();

        $obras = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        $totalPendentes  = Medicao::where('status', 'pendente')->count();
        $totalAprovadas  = Medicao::where('status', 'aprovado')
            ->whereBetween('data_medicao', [$dataInicio, $dataFim])->count();
        $totalRejeitadas = Medicao::where('status', 'rejeitado')
            ->whereBetween('data_medicao', [$dataInicio, $dataFim])->count();

        return view('producao.aprovacao', compact(
            'medicoes', 'obras', 'status', 'dataInicio', 'dataFim',
            'totalPendentes', 'totalAprovadas', 'totalRejeitadas'
        ));
    }

    /**
     * Aprovar ou rejeitar uma medição individual
     */
    public function processar(Request $request, Medicao $medicao)
    {
        $request->validate(['acao' => 'required|in:aprovado,rejeitado']);

        $medicao->status      = $request->acao;
        $medicao->aprovado_por = Auth::id();
        $medicao->aprovado_em  = now();
        $medicao->save();

        // Se aprovada, atualiza percentual realizado da fase
        if ($request->acao === 'aprovado' && $medicao->obra_fase_id) {
            ObraFase::where('id', $medicao->obra_fase_id)
                ->update(['percentual_realizado' => min(100, $medicao->percentual_acumulado)]);
        }

        $msg = $request->acao === 'aprovado'
            ? __('Medição aprovada!')
            : __('Medição rejeitada.');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $medicao->status, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Aprovar em lote
     */
    public function processarLote(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:medicoes,id',
            'acao'  => 'required|in:aprovado,rejeitado',
        ]);

        $medicoes = Medicao::whereIn('id', $request->ids)->get();

        foreach ($medicoes as $medicao) {
            $medicao->status       = $request->acao;
            $medicao->aprovado_por = Auth::id();
            $medicao->aprovado_em  = now();
            $medicao->save();

            if ($request->acao === 'aprovado' && $medicao->obra_fase_id) {
                ObraFase::where('id', $medicao->obra_fase_id)
                    ->update(['percentual_realizado' => min(100, $medicao->percentual_acumulado)]);
            }
        }

        $count = count($request->ids);
        $msg   = $request->acao === 'aprovado'
            ? __(':count medição(ões) aprovada(s)!', ['count' => $count])
            : __(':count medição(ões) rejeitada(s).', ['count' => $count]);

        return back()->with('success', $msg);
    }
}
