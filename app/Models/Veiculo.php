<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    protected $table = 'veiculos';

    protected $fillable = [
        'placa',
        'renavam',
        'marca',
        'modelo',
        'ano',
        'tipo',
        'status',
        'km_atual',
    ];
}


