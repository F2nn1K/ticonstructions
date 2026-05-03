<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Apontamento extends Model
{
    protected $fillable = [
        'funcionario_id',
        'obra_id',
        'data',
        'hora_entrada',
        'hora_saida',
        'hora_almoco_saida',
        'hora_almoco_retorno',
        'horas_trabalhadas',
        'observacoes',
        'status',
        'registrado_por',
        'aprovado_por',
        'aprovado_em',
    ];

    protected $casts = [
        'data'       => 'date',
        'aprovado_em' => 'datetime',
    ];

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Funcionario::class, 'funcionario_id');
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Obra::class, 'obra_id');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'registrado_por');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'aprovado_por');
    }

    public function calcularHoras(): void
    {
        if ($this->hora_entrada && $this->hora_saida) {
            $entrada = Carbon::createFromFormat('H:i:s', $this->hora_entrada);
            $saida   = Carbon::createFromFormat('H:i:s', $this->hora_saida);
            $total   = $saida->diffInMinutes($entrada);

            if ($this->hora_almoco_saida && $this->hora_almoco_retorno) {
                $almSaida   = Carbon::createFromFormat('H:i:s', $this->hora_almoco_saida);
                $almRetorno = Carbon::createFromFormat('H:i:s', $this->hora_almoco_retorno);
                $total     -= $almRetorno->diffInMinutes($almSaida);
            }

            $this->horas_trabalhadas = round($total / 60, 2);
        }
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
