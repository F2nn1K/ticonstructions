<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogEstoqueMinMax extends Model
{
    use HasFactory;

    protected $table = 'logs_estoque_min_max';

    public $timestamps = false;

    protected $fillable = [
        'produto_id',
        'user_id',
        'acao',
        'minimo_anterior',
        'maximo_anterior',
        'minimo_novo',
        'maximo_novo',
        'observacao',
        'ip',
        'user_agent',
    ];
}


