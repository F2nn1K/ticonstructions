<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcorrenciaFrota extends Model
{
    protected $table = 'ocorrencias_frota';

    protected $fillable = [
        'veiculo_id',
        'user_id',
        'motorista_nome',
        'data',
        'hora',
        'descricao',
        'sugestao',
        'status',
        'resolved_at',
        'foto1', 'foto1_mime',
        'foto2', 'foto2_mime',
        'foto3', 'foto3_mime',
        'foto4', 'foto4_mime',
        'foto5', 'foto5_mime',
        'foto6', 'foto6_mime',
        'foto7', 'foto7_mime',
        'foto8', 'foto8_mime',
        'foto9', 'foto9_mime',
        'foto10', 'foto10_mime',
    ];

    public $timestamps = true;

    protected $casts = [
        'data' => 'date',
        'resolved_at' => 'datetime',
    ];

    public function veiculo()
    {
        return $this->belongsTo(\App\Models\Veiculo::class, 'veiculo_id');
    }

    public function statusHistorico()
    {
        return $this->hasMany(\App\Models\StatusOcorrencia::class, 'ocorrencia_id');
    }
}


