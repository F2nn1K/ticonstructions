<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abastecimento extends Model
{
    protected $table = 'abastecimentos';

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'data',
        'km',
        'litros',
        'valor',
        'preco_litro',
        'tipo_combustivel',
        'posto',
        'observacoes',
    ];

    public function veiculo()
    {
        return $this->belongsTo(\App\Models\Veiculo::class, 'vehicle_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}


