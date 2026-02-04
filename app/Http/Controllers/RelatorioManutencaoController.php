<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RelatorioManutencaoController extends Controller
{
    public function index()
    {
        return view('frota.relatorios.manutencoes');
    }

    public function data(Request $request)
    {
        $isAdmin = optional(auth()->user()->profile)->name === 'Admin';
        $ini = $request->query('data_ini');
        $fim = $request->query('data_fim');
        $vehicleId = $request->query('vehicle_id');
        $userId = $request->query('user_id');

        $q = DB::table('manutencoes as m')
            ->leftJoin('veiculos as v', 'm.vehicle_id', '=', 'v.id')
            ->leftJoin('users as u', 'm.user_id', '=', 'u.id')
            ->select(
                'm.id','m.data','m.tipo','m.descricao','m.km','m.custo','m.proxima_data','m.proxima_km','m.vehicle_id','m.user_id',
                DB::raw("CONCAT(v.placa, IFNULL(CONCAT(' - ', v.modelo), '')) as veiculo"),
                'u.name as motorista'
            )
            ->when($ini, fn($qq) => $qq->whereDate('m.data','>=',$ini))
            ->when($fim, fn($qq) => $qq->whereDate('m.data','<=',$fim))
            ->when($vehicleId, fn($qq) => $qq->where('m.vehicle_id',$vehicleId));

        if ($isAdmin) {
            if ($userId) { $q->where('m.user_id', $userId); }
        } else if (Schema::hasColumn('manutencoes', 'user_id')) {
            $q->where('m.user_id', auth()->id());
        }

        return $q->orderByDesc('m.data')->get();
    }
}


