<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioKmController extends Controller
{
    public function index()
    {
        return view('frota.relatorios.km-percorrido');
    }

    public function data(Request $request)
    {
        $agrupar = $request->query('agrupar', 'veiculo'); // 'veiculo' | 'usuario'
        $ini = $request->query('data_inicio');
        $fim = $request->query('data_fim');
        $vehicleId = $request->query('vehicle_id');
        $userId = $request->query('user_id');

        // Base: viagens concluídas (km_retorno informado)
        $q = DB::table('viagens as v')
            ->leftJoin('veiculos as ve', 've.id', '=', 'v.vehicle_id')
            ->leftJoin('users as u', 'u.id', '=', 'v.user_id')
            ->when($ini, fn($qq) => $qq->whereDate('v.data_saida', '>=', $ini))
            ->when($fim, fn($qq) => $qq->whereDate('v.data_saida', '<=', $fim))
            ->when($vehicleId, fn($qq) => $qq->where('v.vehicle_id', $vehicleId))
            ->when($userId, fn($qq) => $qq->where('v.user_id', $userId))
            ->whereNotNull('v.km_retorno');

        if ($agrupar === 'usuario') {
            $q->groupBy('v.user_id', 'u.name')
              ->selectRaw("COALESCE(u.name,'—') as label")
              ->selectRaw('MIN(v.km_saida) as kmInicial')
              ->selectRaw('MAX(v.km_retorno) as kmFinal');
        } else {
            // padrão: por veículo
            $q->groupBy('v.vehicle_id', 've.placa', 've.marca', 've.modelo')
              ->selectRaw("TRIM(CONCAT(COALESCE(ve.placa,''), CASE WHEN COALESCE(ve.marca,'')<>'' OR COALESCE(ve.modelo,'')<>'' THEN CONCAT(' - ', COALESCE(ve.marca,''), ' ', COALESCE(ve.modelo,'')) ELSE '' END)) as label")
              ->selectRaw('MIN(v.km_saida) as kmInicial')
              ->selectRaw('MAX(v.km_retorno) as kmFinal');
        }

        $rows = $q->get()->map(function($r){
            $kmIni = (int) ($r->kmInicial ?? 0);
            $kmFim = (int) ($r->kmFinal ?? 0);
            return [
                'label' => $r->label ?: '—',
                'kmInicial' => $kmIni,
                'kmFinal' => $kmFim,
                'kmPercorrido' => max(0, $kmFim - $kmIni),
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $rows]);
    }
}


