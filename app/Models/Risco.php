<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Risco extends Model
{
    protected $fillable = [
        'obra_id', 'titulo', 'descricao', 'categoria',
        'probabilidade', 'impacto', 'plano_acao',
        'responsavel', 'prazo', 'status', 'registrado_por',
    ];

    protected $casts = [
        'prazo'         => 'date',
        'probabilidade' => 'integer',
        'impacto'       => 'integer',
        'nivel_risco'   => 'integer',
    ];

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function getNivelClasseAttribute(): string
    {
        $n = $this->probabilidade * $this->impacto;
        return match(true) {
            $n >= 15 => 'danger',
            $n >= 8  => 'warning',
            $n >= 4  => 'info',
            default  => 'success',
        };
    }

    public function getNivelTextoAttribute(): string
    {
        $n = $this->probabilidade * $this->impacto;
        return match(true) {
            $n >= 15 => __('Crítico'),
            $n >= 8  => __('Alto'),
            $n >= 4  => __('Médio'),
            default  => __('Baixo'),
        };
    }
}
