<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RelatorioFrotaController extends Controller
{
    public function conferenciaNf()
    {
        return view('frota.relatorios.conferencia-nf');
    }

    public function listarLotes(Request $request)
    {
        $ini = $request->input('data_inicio');
        $fim = $request->input('data_fim');
        $numero = $request->input('numero_nf');

        // Verificar se as colunas de pagamento existem
        $temDataPagamento = \Illuminate\Support\Facades\Schema::hasColumn('nf_abastecimento_lotes', 'data_pagamento');
        $temBancoPagamento = \Illuminate\Support\Facades\Schema::hasColumn('nf_abastecimento_lotes', 'banco_pagamento');
        
        // Campos extras de pagamento para o SELECT
        $extraFields = '';
        $extraGroupBy = [];
        if ($temDataPagamento) {
            $extraFields .= ', l.data_pagamento';
            $extraGroupBy[] = 'l.data_pagamento';
        }
        if ($temBancoPagamento) {
            $extraFields .= ', l.banco_pagamento';
            $extraGroupBy[] = 'l.banco_pagamento';
        }

        // Quando houver intervalo de datas, filtrar pela data dos itens (nfi.data)
        if ($ini || $fim) {
            // Ordenar por data mínima dos itens no lote para refletir a janela selecionada
            $q = \DB::table('nf_abastecimento_lotes as l')
                ->join('nf_abastecimento_itens as nfi', 'nfi.lote_id', '=', 'l.id')
                ->selectRaw('l.id, l.numero_nf, l.created_at, MIN(nfi.data) as data_min, MAX(nfi.data) as data_max, COUNT(nfi.id) as qtd_itens, COALESCE(SUM(nfi.litros),0) as total_litros, COALESCE(SUM(nfi.valor),0) as total_valor' . $extraFields)
                ->when($ini, fn($qq) => $qq->whereDate('nfi.data', '>=', $ini))
                ->when($fim, fn($qq) => $qq->whereDate('nfi.data', '<=', $fim))
                ->when($numero, fn($qq) => $qq->where('l.numero_nf', 'like', '%'.$numero.'%'))
                ->groupBy(array_merge(['l.id','l.numero_nf','l.created_at'], $extraGroupBy))
                ->orderBy('data_min', 'asc')
                ->orderBy('l.id', 'asc');
            return $q->get();
        }

        // Sem intervalo de datas: manter listagem simples por created_at do lote
        $q = \DB::table('nf_abastecimento_lotes as l')->orderByDesc('l.created_at');
        if ($numero) $q->where('l.numero_nf', 'like', '%'.$numero.'%');
        return $q->get();
    }

    public function detalhesLote(Request $request, int $id)
    {
        $lote = \DB::table('nf_abastecimento_lotes')->where('id', $id)->first();
        if (!$lote) { return response()->json(['ok'=>false,'message'=>'Lote não encontrado'], 404); }

        $ini = $request->input('data_inicio');
        $fim = $request->input('data_fim');

        $itens = \DB::table('nf_abastecimento_itens as nfi')
            ->leftJoin('veiculos as v', 'nfi.veiculo_id', '=', 'v.id')
            ->select('nfi.*', 'v.placa')
            ->where('nfi.lote_id', $id)
            ->when($ini, fn($qq) => $qq->whereDate('nfi.data', '>=', $ini))
            ->when($fim, fn($qq) => $qq->whereDate('nfi.data', '<=', $fim))
            // Ordenação principal por data asc, e secundária por placa/veículo para estabilidade visual
            ->orderBy('nfi.data', 'asc')
            ->orderBy('v.placa', 'asc')
            ->orderBy('nfi.id', 'asc')
            ->get();

        return response()->json(['ok'=>true, 'lote'=>$lote, 'itens'=>$itens]);
    }
}


