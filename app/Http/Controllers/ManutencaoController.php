<?php

namespace App\Http\Controllers;

use App\Models\Manutencao;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Services\OdometerService;

class ManutencaoController extends Controller
{
    public function index()
    {
        return view('frota.manutencoes.index');
    }

    public function json(Request $request)
    {
        $q = Manutencao::query()->with(['user:id,name'])->orderByDesc('data');

        // Administrador ou quem possui a permissão "Gestão de Frotas" vê tudo.
        // Usuários comuns veem apenas as próprias, quando a coluna existir.
        $isAdmin = (optional(auth()->user()->profile)->name === 'Admin')
            || (optional(auth()->user()->profile)->name === 'Gestão de Frotas')
            || (auth()->user() && method_exists(auth()->user(), 'temPermissao') && auth()->user()->temPermissao('Gestão de Frotas'));
        if (!$isAdmin) {
            // Se a tabela possuir a coluna user_id, aplica-se o filtro por usuário autenticado
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('manutencoes', 'user_id')) {
                    $q->where('user_id', auth()->id());
                }
            } catch (\Throwable $e) {
                // Fallback silencioso se Schema não estiver disponível
            }
        } else if ($request->user_id) {
            // Admin pode filtrar por usuário específico, quando disponível
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('manutencoes', 'user_id')) {
                    $q->where('user_id', $request->user_id);
                }
            } catch (\Throwable $e) {}
        }

        if ($request->vehicle_id) $q->where('vehicle_id', $request->vehicle_id);
        if ($request->tipo) $q->where('tipo', $request->tipo);
        if ($request->status) $q->where('status', $request->status);
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required','integer','exists:veiculos,id'],
            'data' => ['required','date'],
            'tipo' => ['required','in:preventiva,corretiva'],
            'descricao' => ['required'],
            'km' => ['required','integer','min:0'],
            'custo' => ['nullable','numeric','min:0'],
            'status' => ['nullable','in:agendada,em_andamento,concluida,cancelada'],
            'oficina' => ['nullable','max:150'],
            'proxima_data' => ['nullable','date'],
            'proxima_km' => ['nullable','integer','min:0'],
            'proxima_tipo' => ['nullable','in:troca_oleo,revisao,alinhamento,pneus,freios,outros'],
        ]);

        // custo opcional: quando não informado, gravar 0.00 para compatibilidade com NOT NULL
        if (!array_key_exists('custo', $data) || $data['custo'] === null) {
            $data['custo'] = 0;
        }

        $veiculo = Veiculo::findOrFail($data['vehicle_id']);
        if ($veiculo->status === 'inativo') {
            return response()->json(['ok'=>false,'message'=>'Veículo inativo; não pode registrar manutenção.'], 422);
        }
        if ($data['km'] < $veiculo->km_atual) {
            return response()->json(['ok'=>false,'message'=>'KM não pode ser menor que o KM atual do veículo.'], 422);
        }

        $man = Manutencao::create($data);
        // Atribui o autor quando a coluna existir
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('manutencoes', 'user_id')) {
                $man->user_id = auth()->id();
                $man->save();
            }
        } catch (\Throwable $e) {}
        OdometerService::updateVehicleOdometer($veiculo->id, (int)$data['km']);
        return response()->json(['ok'=>true,'id'=>$man->id]);
    }

    public function update(Request $request, int $id)
    {
        $man = Manutencao::findOrFail($id);
        $data = $request->validate([
            'data' => ['required','date'],
            'tipo' => ['required','in:preventiva,corretiva'],
            'descricao' => ['required'],
            'km' => ['required','integer','min:0'],
            'custo' => ['nullable','numeric','min:0'],
            'status' => ['nullable','in:agendada,em_andamento,concluida,cancelada'],
            'oficina' => ['nullable','max:150'],
            'proxima_data' => ['nullable','date'],
            'proxima_km' => ['nullable','integer','min:0'],
            'proxima_tipo' => ['nullable','in:troca_oleo,revisao,alinhamento,pneus,freios,outros'],
        ]);
        if (!array_key_exists('custo', $data) || $data['custo'] === null) {
            $data['custo'] = 0;
        }
        $man->update($data);

        $veiculo = Veiculo::find($man->vehicle_id);
        if ($veiculo) {
            OdometerService::updateVehicleOdometer($veiculo->id, (int)$data['km']);
        }
        return response()->json(['ok'=>true]);
    }

    public function destroy(int $id)
    {
        Manutencao::findOrFail($id)->delete();
        return response()->json(['ok'=>true]);
    }
}


