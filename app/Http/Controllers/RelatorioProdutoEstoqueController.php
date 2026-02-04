<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioProdutoEstoqueController extends Controller
{
    public function index()
    {
        return view('relatorios.produto-estoque');
    }

    public function data(Request $request)
    {
        $produto = trim((string)$request->input('produto', ''));
        $centro = trim((string)$request->input('centro_custo', ''));
        $centroId = $request->input('centro_custo_id');
        $produtoIds = (array) $request->input('produto_ids', []);
        $ini = $request->input('data_inicio');
        $fim = $request->input('data_fim');

        $q = DB::table('baixas as b')
            ->leftJoin('estoque as e', 'b.produto_id', '=', 'e.id')
            ->leftJoin('centro_custo as cc', 'b.centro_custo_id', '=', 'cc.id')
            ->select(
                'e.nome as produto',
                'e.descricao as descricao',
                'cc.nome as centro_custo',
                DB::raw('SUM(b.quantidade) as quantidade'),
                DB::raw('SUM(b.quantidade) as valor_total')
            )
            ->when(!empty($produtoIds), function($qq) use ($produtoIds){
                $qq->whereIn('b.produto_id', $produtoIds);
            })
            ->when($produto !== '' && empty($produtoIds), function($qq) use ($produto){
                $qq->where('e.nome', 'like', "%$produto%");
            })
            ->when($centroId, function($qq) use ($centroId){
                $qq->where('b.centro_custo_id', $centroId);
            })
            ->when($centro !== '' && empty($centroId), function($qq) use ($centro){
                $qq->where('cc.nome', 'like', "%$centro%");
            })
            ->when($ini, fn($qq) => $qq->whereDate('b.data_baixa', '>=', $ini))
            ->when($fim, fn($qq) => $qq->whereDate('b.data_baixa', '<=', $fim))
            ->groupBy('e.nome','e.descricao','cc.nome')
            ->orderBy('e.nome')
            ->orderBy('cc.nome');

        $dados = $q->get();

        return response()->json(['success' => true, 'data' => $dados]);
    }

    public function centros()
    {
        $centros = DB::table('centro_custo')->select('id','nome')->orderBy('nome')->get();
        return response()->json(['success' => true, 'data' => $centros]);
    }

    public function produtos(Request $request)
    {
        $q = trim((string)$request->query('q',''));
        $produtos = DB::table('estoque')
            ->select('id','nome','descricao')
            ->when($q !== '', function($qq) use ($q){
                $qq->where('nome', 'like', $q.'%');
            })
            ->orderBy('nome')
            ->limit(20)
            ->get();
        return response()->json(['success' => true, 'data' => $produtos]);
    }
}


