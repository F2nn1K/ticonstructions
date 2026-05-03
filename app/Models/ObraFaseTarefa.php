<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObraFaseTarefa extends Model
{
    protected $table = 'obra_fase_tarefas';

    protected $fillable = [
        'obra_fase_id', 'tarefa_catalogo_id', 'nome',
        'concluida', 'data_conclusao', 'concluida_por', 'observacoes', 'ordem',
    ];

    protected $casts = [
        'concluida'      => 'boolean',
        'data_conclusao' => 'date',
    ];

    public function obraFase()
    {
        return $this->belongsTo(ObraFase::class, 'obra_fase_id');
    }

    public function catalogoTarefa()
    {
        return $this->belongsTo(FaseCatalogoTarefa::class, 'tarefa_catalogo_id');
    }

    public function concluidaPor()
    {
        return $this->belongsTo(User::class, 'concluida_por');
    }
}
