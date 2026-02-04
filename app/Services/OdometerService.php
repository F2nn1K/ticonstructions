<?php

namespace App\Services;

use App\Models\Veiculo;

class OdometerService
{
    /**
     * Atualiza o km_atual do veículo se o valor informado for maior
     */
    public static function updateVehicleOdometer(int $vehicleId, int $newKm): void
    {
        $veiculo = Veiculo::find($vehicleId);
        if ($veiculo && $newKm > (int) $veiculo->km_atual) {
            $veiculo->update(['km_atual' => $newKm]);
        }
    }
}


