<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use App\Models\Veiculo;
use App\Services\OdometerService;
use Illuminate\Http\Request;

class ViagemController extends Controller
{
    public function index()
    {
        $isAdminFrota = (optional(auth()->user()->profile)->name === 'Admin')
            || (auth()->user() && method_exists(auth()->user(), 'temPermissao') && auth()->user()->temPermissao('Gestão de Frotas'));
        return view('frota.viagens.index', ['__isAdmin' => $isAdminFrota]);
    }

    public function json(Request $request)
    {
        $q = Viagem::query()->orderByDesc('data_saida');
        $isAdmin = (optional(auth()->user()->profile)->name === 'Admin')
            || (optional(auth()->user()->profile)->name === 'Gestão de Frotas')
            || (auth()->user() && method_exists(auth()->user(), 'temPermissao') && auth()->user()->temPermissao('Gestão de Frotas'));
        if (!$isAdmin) {
            $q->where('user_id', auth()->id());
        }
        if ($request->vehicle_id) $q->where('vehicle_id', $request->vehicle_id);
        if ($request->user_id) $q->where('user_id', $request->user_id);
        // Só aplicar filtros de datas se foram enviados; admin/gestor, por padrão, vê tudo
        if ($request->filled('data_inicio')) $q->where('data_saida', '>=', $request->input('data_inicio'));
        if ($request->filled('data_fim')) $q->where('data_saida', '<=', $request->input('data_fim'));
        if ($request->only_open) {
            $q->whereNull('km_retorno');
        }
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required','integer','exists:veiculos,id'],
            'user_id' => ['required','integer','exists:users,id'],
            'data_saida' => ['required','date'],
            'hora_saida' => ['required'],
            'km_saida' => ['required','integer','min:0'],
            'data_retorno' => ['nullable','date'],
            'hora_retorno' => ['nullable'],
            'km_retorno' => ['nullable','integer','min:0'],
            'observacoes' => ['nullable'],
        ]);

        $veiculo = Veiculo::findOrFail($data['vehicle_id']);
        if ($veiculo->status === 'manutencao' || $veiculo->status === 'inativo') {
            return response()->json([
                'ok' => false,
                'message' => 'Este veículo está indisponível (manutenção/inativo) e não pode iniciar viagem.'
            ], 422);
        }
        if ($data['km_saida'] < $veiculo->km_atual) {
            return response()->json(['ok'=>false,'message'=>'KM de saída não pode ser menor que o KM atual do veículo.'], 422);
        }

        if (!empty($data['km_retorno'])) {
            if ($data['km_retorno'] <= $data['km_saida']) {
                return response()->json(['ok'=>false,'message'=>'KM de retorno deve ser maior que KM de saída.'], 422);
            }
            $data['km_percorrido'] = $data['km_retorno'] - $data['km_saida'];
        }

        // Impedir iniciar viagem se já houver outra em andamento com o mesmo veículo
        $existeEmUso = Viagem::where('vehicle_id', $data['vehicle_id'])
            ->where(function($q){
                $q->whereNull('km_retorno')
                  ->orWhereNull('data_retorno');
            })
            ->exists();
        if ($existeEmUso) {
            return response()->json([
                'ok' => false,
                'message' => 'Este veículo já está em uso em outra viagem em andamento.'
            ], 422);
        }

        $viagem = Viagem::create($data);

        if (!empty($data['km_retorno'])) {
            OdometerService::updateVehicleOdometer($veiculo->id, (int)$data['km_retorno']);
        }

        return response()->json(['ok'=>true,'id'=>$viagem->id]);
    }

    public function update(Request $request, int $id)
    {
        $viagem = Viagem::findOrFail($id);
        $data = $request->validate([
            'data_saida' => ['required','date'],
            'hora_saida' => ['required'],
            'km_saida' => ['required','integer','min:0'],
            'data_retorno' => ['nullable','date'],
            'hora_retorno' => ['nullable'],
            'km_retorno' => ['nullable','integer','min:0'],
            'observacoes' => ['nullable'],
        ]);

        if (!empty($data['km_retorno']) && $data['km_retorno'] <= $data['km_saida']) {
            return response()->json(['ok'=>false,'message'=>'KM de retorno deve ser maior que KM de saída.'], 422);
        }
        $data['km_percorrido'] = (!empty($data['km_retorno']) && $data['km_retorno'] > $data['km_saida'])
            ? ($data['km_retorno'] - $data['km_saida'])
            : null;
        $viagem->update($data);

        // Se informar KM de retorno, atualizar o km_atual do veículo
        if (!empty($data['km_retorno'])) {
            OdometerService::updateVehicleOdometer($viagem->vehicle_id, (int)$data['km_retorno']);
        }
        return response()->json(['ok'=>true]);
    }

    public function destroy(int $id)
    {
        Viagem::findOrFail($id)->delete();
        return response()->json(['ok'=>true]);
    }
}


