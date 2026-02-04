<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogEstoque extends Model
{
    use HasFactory;

    protected $table = 'logs_estoque';

    protected $fillable = [
        'produto_id',
        'user_id',
        'tipo',
        'quantidade_anterior',
        'quantidade_alterada',
        'quantidade_nova',
        'origem',
        'observacao',
    ];
}


