<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusOcorrencia extends Model
{
    protected $table = 'status_ocorrencia';

    protected $fillable = [
        'ocorrencia_id',
        'user_id',
        'status_from',
        'status_to',
        'observacao',
    ];

    public $timestamps = true;
}


