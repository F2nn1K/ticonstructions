<?php

namespace App\Http\Controllers;

use App\Models\Abastecimento;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Services\OdometerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbastecimentoController extends Controller
{
    public function index()
    {
        $isAdmin = optional(auth()->user()->profile)->name === 'Admin';
        $isGestorFrota = (optional(auth()->user()->profile)->name === 'Gestão de Frotas')
            || (auth()->user() && method_exists(auth()->user(), 'temPermissao') && auth()->user()->temPermissao('Gestão de Frotas'));
        $canAdminFrota = $isAdmin || $isGestorFrota;
        return view('frota.abastecimentos.index', ['isAdmin' => $canAdminFrota]);
    }

    public function json(Request $request)
    {
        $q = Abastecimento::query()->with(['veiculo:id,placa,marca,modelo','usuario:id,name'])->orderByDesc('data');
        $isAdmin = (optional(auth()->user()->profile)->name === 'Admin')
            || (optional(auth()->user()->profile)->name === 'Gestão de Frotas')
            || (auth()->user() && method_exists(auth()->user(), 'temPermissao') && auth()->user()->temPermissao('Gestão de Frotas'));
        $hasNfPerm = auth()->user() && method_exists(auth()->user(), 'temPermissao')
            ? auth()->user()->temPermissao('Nf_abas') : false;
        if ($isAdmin || $hasNfPerm) {
            if ($request->user_id) { $q->where('user_id', $request->user_id); }
        } else {
            $q->where('user_id', auth()->id());
        }
        if ($request->vehicle_id) $q->where('vehicle_id', $request->vehicle_id);
        if ($request->placa) {
            $placa = trim($request->placa);
            if ($placa !== '') {
                $q->whereHas('veiculo', function($qq) use ($placa){
                    $qq->where('placa', 'like', "%$placa%");
                });
            }
        }
        if ($request->data_inicio) $q->where('data', '>=', $request->data_inicio);
        if ($request->data_fim) $q->where('data', '<=', $request->data_fim);

        // Excluir abastecimentos já consolidados em NF (quando solicitado)
        if ($request->boolean('exclude_consolidated') && Schema::hasTable('nf_abastecimento_itens')) {
            $q->whereNotExists(function($sub){
                $sub->select(DB::raw(1))
                    ->from('nf_abastecimento_itens')
                    ->whereColumn('nf_abastecimento_itens.abastecimento_id', 'abastecimentos.id');
            });
        }
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required','integer','exists:veiculos,id'],
            'data' => ['required','date'],
            'km' => ['required','integer','min:0'],
            'litros' => ['required','regex:/^\\d+(?:\\.\\d{1,2})?$/','min:0.01','max:100'],
            'valor' => ['required','numeric','min:0.01'],
            'tipo_combustivel' => ['nullable','in:gasolina,etanol,diesel,gnv'],
            'posto' => ['nullable','max:150'],
            'observacoes' => ['nullable'],
        ]);

        $veiculo = Veiculo::findOrFail($data['vehicle_id']);
        // Verifica perfil especial para gestão de frotas
        $isGestorFrota = (optional(auth()->user()->profile)->name === 'Gestão de Frotas')
            || (auth()->user() && method_exists(auth()->user(), 'temPermissao') && auth()->user()->temPermissao('Gestão de Frotas'))
            || (optional(auth()->user()->profile)->name === 'Admin');
        if ($veiculo->status === 'manutencao' || $veiculo->status === 'inativo') {
            return response()->json([
                'ok' => false,
                'message' => 'Este veículo está indisponível (manutenção/inativo) e não pode ser abastecido.'
            ], 422);
        }
        // Agora permitimos registrar a quilometragem atual (>= km_atual)
        // Libera a inserção de KM menor apenas para Gestão de Frotas/Admin (ajustes retroativos)
        if ($data['km'] < $veiculo->km_atual && !$isGestorFrota) {
            return response()->json(['ok'=>false,'message'=>'KM não pode ser menor que o KM atual do veículo.'], 422);
        }

        // Posto padrão quando não informado
        if (empty($data['posto'])) {
            $data['posto'] = "Auto Posto Estrela D'alva";
        }

        $data['preco_litro'] = round($data['valor'] / $data['litros'], 3);
        $data['user_id'] = auth()->id();
        $ab = Abastecimento::create($data);
        // Atualiza odômetro globalmente, exceto para Gestão de Frotas/Admin
        // (para que ajustes retroativos não alterem o km_atual do veículo)
        if (!$isGestorFrota) {
            OdometerService::updateVehicleOdometer($veiculo->id, (int)$data['km']);
        }

        return response()->json(['ok'=>true,'id'=>$ab->id]);
    }

    public function update(Request $request, int $id)
    {
        $ab = Abastecimento::findOrFail($id);
        // Determinar se o usuário pode trocar o veículo deste abastecimento
        $canChangeVehicle = (optional(auth()->user()->profile)->name === 'Admin')
            || (optional(auth()->user()->profile)->name === 'Gestão de Frotas')
            || (auth()->user() && method_exists(auth()->user(), 'temPermissao') && auth()->user()->temPermissao('Gestão de Frotas'));

        $rules = [
            'data' => ['required','date'],
            'km' => ['required','integer','min:0'],
            'litros' => ['required','regex:/^\\d+(?:\\.\\d{1,2})?$/','min:0.01','max:100'],
            'valor' => ['required','numeric','min:0.01'],
            'tipo_combustivel' => ['nullable','in:gasolina,etanol,diesel,gnv'],
            'posto' => ['nullable','max:150'],
            'observacoes' => ['nullable'],
        ];
        if ($canChangeVehicle) {
            $rules['vehicle_id'] = ['required','integer','exists:veiculos,id'];
        }
        $data = $request->validate($rules);
        if (empty($data['posto'])) {
            $data['posto'] = "Auto Posto Estrela D'alva";
        }
        $data['preco_litro'] = round($data['valor'] / $data['litros'], 3);
        $data['user_id'] = $ab->user_id ?: auth()->id();
        // Se permitido, aplicar troca de veículo; caso contrário, ignorar vehicle_id enviado
        if (!$canChangeVehicle) {
            unset($data['vehicle_id']);
        }
        $ab->update($data);

        // Para atualizações, somente usuários comuns disparam atualização do odômetro.
        $isGestorFrota = (optional(auth()->user()->profile)->name === 'Gestão de Frotas')
            || (auth()->user() && method_exists(auth()->user(), 'temPermissao') && auth()->user()->temPermissao('Gestão de Frotas'))
            || (optional(auth()->user()->profile)->name === 'Admin');

        if (!$isGestorFrota) {
            $veiculo = Veiculo::find($ab->vehicle_id);
            if ($veiculo) {
                OdometerService::updateVehicleOdometer($veiculo->id, (int)$data['km']);
            }
        }
        return response()->json(['ok'=>true]);
    }

    public function destroy(int $id)
    {
        Abastecimento::findOrFail($id)->delete();
        return response()->json(['ok'=>true]);
    }
}


