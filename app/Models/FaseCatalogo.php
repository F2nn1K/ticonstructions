<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaseCatalogo extends Model
{
    protected $table = 'fases_catalogo';

    protected $fillable = [
        'nome', 'ordem', 'descricao', 'icone',
        'percentual_inicio', 'percentual_fim', 'ativo',
    ];

    protected $casts = [
        'ativo'              => 'boolean',
        'percentual_inicio'  => 'decimal:2',
        'percentual_fim'     => 'decimal:2',
    ];

    public function obraFases()
    {
        return $this->hasMany(ObraFase::class, 'fase_catalogo_id');
    }

    public function tarefas()
    {
        return $this->hasMany(FaseCatalogoTarefa::class, 'fase_catalogo_id')
                    ->where('ativo', true)
                    ->orderBy('ordem');
    }

    public function tarefasPorGrupo()
    {
        return $this->tarefas()->get()->groupBy('grupo');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }

    /** Peso da fase em pontos percentuais da obra */
    public function getPesoAttribute(): float
    {
        return (float) ($this->percentual_fim - $this->percentual_inicio);
    }

    /** Rótulo do intervalo de %, ex: "30% → 65%" */
    public function getIntervaloLabelAttribute(): string
    {
        $ini = rtrim(rtrim(number_format($this->percentual_inicio, 1, ',', '.'), '0'), ',');
        $fim = rtrim(rtrim(number_format($this->percentual_fim, 1, ',', '.'), '0'), ',');
        return "{$ini}% → {$fim}%";
    }
}
