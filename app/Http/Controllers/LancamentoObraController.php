<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\ObraFase;
use App\Models\LancamentoObra;
use App\Models\CategoriaMaterial;
use App\Models\SubcategoriaMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LancamentoObraController extends Controller
{
    public function index(Obra $obra)
    {
        $lancamentos = LancamentoObra::with(['fase.faseCatalogo', 'categoria', 'subcategoria'])
            ->where('obra_id', $obra->id)
            ->orderBy('data_lancamento', 'desc')
            ->paginate(20);

        $totalReal   = $lancamentos->sum('custo_total_real');
        $totalOrcado = $lancamentos->sum('custo_total_orcado');

        return view('obras.lancamentos.index', compact('obra', 'lancamentos', 'totalReal', 'totalOrcado'));
    }

    public function create(Obra $obra)
    {
        // Busca a fase ativa para pré-selecionar
        $faseAtiva  = $obra->faseAtiva;
        $categorias = CategoriaMaterial::ativas()->with('subcategorias')->get();

        if (!$faseAtiva) {
            return redirect()->route('obras.show', $obra)
                ->with('error', 'Esta obra não possui fase ativa para lançamentos.');
        }

        return view('obras.lancamentos.create', compact('obra', 'faseAtiva', 'categorias'));
    }

    public function store(Request $request, Obra $obra)
    {
        $request->validate([
            'categoria_id'          => 'required|exists:categorias_material,id',
            'tipo'                  => 'required|in:material,servico,mao_de_obra,equipamento,terceiro',
            'descricao'             => 'required|string|max:255',
            'quantidade'            => 'required|numeric|min:0.001',
            'custo_unitario_real'   => 'required|numeric|min:0',
            'data_lancamento'       => 'required|date',
        ]);

        // Fase ativa NO MOMENTO do lançamento — automática
        $faseAtiva = $obra->faseAtiva;

        if (!$faseAtiva) {
            return back()->with('error', 'Não há fase ativa para esta obra. Verifique o cronograma.');
        }

        $lancamento = LancamentoObra::create([
            'obra_id'               => $obra->id,
            'obra_fase_id'          => $faseAtiva->id,  // ← automático
            'categoria_id'          => $request->categoria_id,
            'subcategoria_id'       => $request->subcategoria_id,
            'tipo'                  => $request->tipo,
            'descricao'             => $request->descricao,
            'fornecedor'            => $request->fornecedor,
            'nota_fiscal'           => $request->nota_fiscal,
            'quantidade'            => $request->quantidade,
            'unidade'               => $request->unidade,
            'custo_unitario_orcado' => $request->custo_unitario_orcado,
            'custo_unitario_real'   => $request->custo_unitario_real,
            'data_lancamento'       => $request->data_lancamento,
            'data_prevista_pagamento' => $request->data_prevista_pagamento,
            'status_pagamento'      => 'pendente',
            'observacoes'           => $request->observacoes,
            'created_by'            => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true, 'lancamento' => $lancamento]);
        }

        return redirect()->route('obras.lancamentos.index', $obra)
            ->with('success', "Lançamento registrado na fase: {$faseAtiva->nome}");
    }

    public function destroy(Obra $obra, LancamentoObra $lancamento)
    {
        if ($lancamento->obra_id !== $obra->id) abort(403);
        $lancamento->delete();

        return back()->with('success', 'Lançamento removido.');
    }

    /**
     * Retorna subcategorias de uma categoria via AJAX.
     */
    public function subcategorias(Request $request)
    {
        $subs = SubcategoriaMaterial::where('categoria_id', $request->categoria_id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'unidade']);

        return response()->json($subs);
    }
}
