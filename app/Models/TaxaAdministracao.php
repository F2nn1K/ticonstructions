<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxaAdministracao extends Model
{
    use SoftDeletes;

    protected $table = 'taxa_administracao';

    protected $fillable = [
        'obra_id', 'administrador_id', 'data_referencia', 'descricao',
        'custo_base_obra', 'percentual', 'valor_taxa',
        'status', 'data_vencimento', 'data_pagamento',
        'valor_pago', 'forma_pagamento', 'comprovante', 'observacoes',
        'created_by', 'pago_por',
    ];

    protected $casts = [
        'data_referencia'  => 'date',
        'data_vencimento'  => 'date',
        'data_pagamento'   => 'date',
        'custo_base_obra'  => 'decimal:2',
        'percentual'       => 'decimal:2',
        'valor_taxa'       => 'decimal:2',
        'valor_pago'       => 'decimal:2',
    ];

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function administrador()
    {
        return $this->belongsTo(AdministradorSistema::class, 'administrador_id');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pagador()
    {
        return $this->belongsTo(User::class, 'pago_por');
    }

    // ── Acessores ────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pendente'  => 'warning',
            'pago'      => 'success',
            'cancelado' => 'danger',
            default     => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendente'  => 'Pendente',
            'pago'      => 'Pago',
            'cancelado' => 'Cancelado',
            default     => $this->status,
        };
    }

    // ── Helpers estáticos ────────────────────────────────────────────────────

    /**
     * Calcula a base de custo de obra excluindo lançamentos de taxa de admin.
     * Usado para calcular o valor da taxa antes de gravar.
     */
    public static function calcularBaseObra(int $obraId): float
    {
        return (float) LancamentoObra::where('obra_id', $obraId)
            ->where('excluir_base_taxa_admin', false)
            ->whereNull('deleted_at')
            ->sum('custo_total_real');
    }
}
