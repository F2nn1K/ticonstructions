<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viagem extends Model
{
    protected $table = 'viagens';

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'finalidade',
        'data_saida',
        'hora_saida',
        'km_saida',
        'origem',
        'data_retorno',
        'hora_retorno',
        'km_retorno',
        'destino',
        'km_percorrido',
        'status',
        'observacoes',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'vehicle_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


