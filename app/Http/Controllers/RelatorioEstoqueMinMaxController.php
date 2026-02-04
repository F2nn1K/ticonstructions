<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RelatorioEstoqueMinMaxController extends Controller
{
    public function index()
    {
        return view('relatorios.estoque-min-max');
    }

    public function data(Request $request)
    {
        if (!Auth::check() || !Auth::user()->temPermissao('rel_maxmin')) {
            return response()->json(['success' => false, 'message' => 'Não autorizado'], 403);
        }

        $apenasAbaixo = filter_var($request->query('apenas_abaixo', 'true'), FILTER_VALIDATE_BOOLEAN);

        $query = DB::table('estoque as e')
            ->leftJoin('estoque_min_max as mm', 'mm.produto_id', '=', 'e.id')
            ->select(
                'e.id', 'e.nome', 'e.descricao', 'e.quantidade',
                DB::raw('COALESCE(mm.minimo, 0) as minimo'),
                'mm.maximo'
            )
            ->orderBy('e.nome');

        $dados = $query->get()->map(function($r){
            $r->abaixo_minimo = ($r->minimo ?? 0) > 0 && $r->quantidade < $r->minimo;
            $r->acima_maximo = isset($r->maximo) && $r->maximo !== null && $r->quantidade > $r->maximo;
            return $r;
        });

        if ($apenasAbaixo) {
            $dados = $dados->filter(function($r){ return $r->abaixo_minimo; })->values();
        }

        return response()->json(['success' => true, 'data' => $dados]);
    }
}


