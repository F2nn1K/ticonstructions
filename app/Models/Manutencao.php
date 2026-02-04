<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manutencao extends Model
{
    protected $table = 'manutencoes';

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'data',
        'tipo',
        'descricao',
        'km',
        'custo',
        'status',
        'oficina',
        'proxima_data',
        'proxima_km',
        'proxima_tipo',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}


