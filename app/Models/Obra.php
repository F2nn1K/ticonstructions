<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Obra extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'codigo', 'descricao', 'endereco', 'cidade', 'estado',
        'cliente', 'responsavel_tecnico', 'valor_contrato', 'area_total',
        'data_inicio_prevista', 'data_fim_prevista',
        'data_inicio_real', 'data_fim_real',
        'status', 'created_by',
    ];

    protected $casts = [
        'data_inicio_prevista' => 'date',
        'data_fim_prevista'    => 'date',
        'data_inicio_real'     => 'date',
        'data_fim_real'        => 'date',
        'valor_contrato'       => 'decimal:2',
        'area_total'           => 'decimal:2',
    ];

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function fases()
    {
        return $this->hasMany(ObraFase::class)->orderBy('ordem');
    }

    public function faseAtiva()
    {
        return $this->hasOne(ObraFase::class)->where('status', 'em_andamento');
    }

    public function lancamentos()
    {
        return $this->hasMany(LancamentoObra::class);
    }

    public function ocorrencias()
    {
        return $this->hasMany(OcorrenciaFaseObra::class);
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Acessores / helpers ──────────────────────────────────────────────────

    public function getFaseAtivaAtualAttribute()
    {
        return $this->fases()->where('status', 'em_andamento')->first();
    }

    public function getPercentualGeralAttribute(): int
    {
        $fases = $this->fases;
        if ($fases->isEmpty()) return 0;
        return (int) $fases->avg('percentual_realizado');
    }

    public function getCustoTotalRealAttribute(): float
    {
        return (float) $this->lancamentos()->sum('custo_total_real');
    }

    public function getCustoTotalOrcadoAttribute(): float
    {
        return (float) $this->lancamentos()->sum('custo_total_orcado');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'planejamento' => 'secondary',
            'em_andamento' => 'primary',
            'concluida'    => 'success',
            'suspensa'     => 'warning',
            'cancelada'    => 'danger',
            default        => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planejamento' => 'Planejamento',
            'em_andamento' => 'Em Andamento',
            'concluida'    => 'Concluída',
            'suspensa'     => 'Suspensa',
            'cancelada'    => 'Cancelada',
            default        => $this->status,
        };
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAtivas($query)
    {
        return $query->where('status', 'em_andamento');
    }
}
