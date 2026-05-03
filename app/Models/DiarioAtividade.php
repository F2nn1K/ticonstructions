<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiarioAtividade extends Model
{
    protected $table = 'diario_atividades';

    protected $fillable = [
        'diario_obra_id', 'obra_fase_tarefa_id', 'descricao', 'qtde_orcada', 'qtde_realizada',
        'evolucao_percentual', 'status_atividade', 'comentario', 'ordem',
    ];

    protected $casts = [
        'evolucao_percentual' => 'decimal:2',
        'ordem'               => 'integer',
    ];

    public function diario()
    {
        return $this->belongsTo(DiarioObra::class, 'diario_obra_id');
    }

    public function tarefaCronograma()
    {
        return $this->belongsTo(ObraFaseTarefa::class, 'obra_fase_tarefa_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status_atividade) {
            'em_andamento' => 'Em Andamento',
            'paralisada'   => 'Paralisada',
            'finalizada'   => 'Finalizada',
            'nao_iniciada' => 'Não Iniciada',
            default        => $this->status_atividade,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status_atividade) {
            'em_andamento' => 'primary',
            'paralisada'   => 'danger',
            'finalizada'   => 'success',
            'nao_iniciada' => 'secondary',
            default        => 'secondary',
        };
    }

    public function getStatusCorAttribute(): string
    {
        return match($this->status_atividade) {
            'em_andamento' => '#2196F3',
            'paralisada'   => '#F44336',
            'finalizada'   => '#4CAF50',
            'nao_iniciada' => '#9E9E9E',
            default        => '#9E9E9E',
        };
    }
}
