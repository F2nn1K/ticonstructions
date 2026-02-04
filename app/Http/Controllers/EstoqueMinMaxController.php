<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Estoque;
use App\Models\EstoqueMinMax;
use App\Models\LogEstoqueMinMax;

class EstoqueMinMaxController extends Controller
{
    /**
     * Exibe a página de Estoque Mínimo e Máximo.
     */
    public function index(Request $request)
    {
        return view('brs.estoque-min-max');
    }

    /**
     * Lista produtos com níveis mínimo/máximo atuais.
     */
    public function listar()
    {
        if (!Auth::check() || !Auth::user()->temPermissao('est_mm')) {
            return response()->json(['success' => false, 'message' => 'Não autorizado'], 403);
        }

        $dados = DB::table('estoque as e')
            ->leftJoin('estoque_min_max as mm', 'mm.produto_id', '=', 'e.id')
            ->select('e.id','e.nome','e.descricao','e.quantidade',
                DB::raw('COALESCE(mm.minimo, 0) as minimo'),
                DB::raw('mm.maximo as maximo'))
            ->orderBy('e.nome')
            ->get();

        return response()->json(['success' => true, 'data' => $dados]);
    }

    /**
     * Salva mínimo/máximo de um produto e gera log.
     */
    public function salvar(Request $request, int $produtoId)
    {
        if (!Auth::check() || !Auth::user()->temPermissao('est_mm')) {
            return response()->json(['success' => false, 'message' => 'Não autorizado'], 403);
        }

        $request->validate([
            'minimo' => 'required|integer|min:0',
            'maximo' => 'nullable|integer|min:0',
        ]);

        $produto = Estoque::findOrFail($produtoId);

        $registro = EstoqueMinMax::firstOrNew(['produto_id' => $produto->id]);
        $anteriorMin = $registro->exists ? (int) $registro->minimo : null;
        $anteriorMax = $registro->exists ? (int) $registro->maximo : null;

        $registro->minimo = (int) $request->input('minimo');
        $registro->maximo = $request->input('maximo') !== null ? (int) $request->input('maximo') : null;
        $registro->save();

        try {
            LogEstoqueMinMax::create([
                'produto_id' => $produto->id,
                'user_id' => Auth::id(),
                'acao' => $registro->wasRecentlyCreated ? 'create' : 'update',
                'minimo_anterior' => $anteriorMin,
                'maximo_anterior' => $anteriorMax,
                'minimo_novo' => $registro->minimo,
                'maximo_novo' => $registro->maximo,
                'observacao' => null,
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Falha ao registrar log de min/max', [
                'produto_id' => $produto->id,
                'erro' => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Níveis salvos com sucesso']);
    }
}


