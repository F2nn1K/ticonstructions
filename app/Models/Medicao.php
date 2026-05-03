<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medicao extends Model
{
    protected $table = 'medicoes';

    protected $fillable = [
        'obra_id',
        'obra_fase_id',
        'data_medicao',
        'percentual_medido',
        'percentual_acumulado',
        'valor_medicao',
        'descricao',
        'observacoes',
        'status',
        'registrado_por',
        'aprovado_por',
        'aprovado_em',
    ];

    protected $casts = [
        'data_medicao'          => 'date',
        'percentual_medido'     => 'decimal:2',
        'percentual_acumulado'  => 'decimal:2',
        'valor_medicao'         => 'decimal:2',
        'aprovado_em'           => 'datetime',
    ];

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class, 'obra_id');
    }

    public function fase(): BelongsTo
    {
        return $this->belongsTo(ObraFase::class, 'obra_fase_id');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'aprovado'  => 'badge-success',
            'rejeitado' => 'badge-danger',
            default     => 'badge-warning',
        };
    }
}
