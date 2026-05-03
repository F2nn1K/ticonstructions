<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdministradorSistema extends Model
{
    use SoftDeletes;

    protected $table = 'administradores_sistema';

    protected $fillable = [
        'user_id', 'nome', 'cpf', 'email', 'telefone',
        'cargo', 'percentual_taxa', 'observacoes', 'ativo', 'created_by',
    ];

    protected $casts = [
        'ativo'           => 'boolean',
        'percentual_taxa' => 'decimal:2',
    ];

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Todas as taxas geradas para este administrador */
    public function taxas()
    {
        return $this->hasMany(TaxaAdministracao::class, 'administrador_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->orderBy('nome');
    }

    // ── Acessores ────────────────────────────────────────────────────────────

    /** Total já pago a este administrador (todos os lançamentos pagos) */
    public function getTotalPagoAttribute(): float
    {
        return (float) $this->taxas()->where('status', 'pago')->sum('valor_pago');
    }

    /** Total pendente deste administrador */
    public function getTotalPendenteAttribute(): float
    {
        return (float) $this->taxas()->where('status', 'pendente')->sum('valor_taxa');
    }
}
