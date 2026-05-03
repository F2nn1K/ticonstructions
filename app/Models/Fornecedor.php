<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends Model
{
    use SoftDeletes;

    protected $table = 'fornecedores';

    protected $fillable = [
        'razao_social', 'nome_fantasia', 'cnpj', 'telefone', 'email',
        'endereco', 'cidade', 'uf', 'observacoes', 'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function lancamentos()
    {
        return $this->hasMany(LancamentoObra::class, 'fornecedor_id');
    }

    public function getNomeExibicaoAttribute(): string
    {
        return $this->nome_fantasia ?: $this->razao_social;
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function getTotalComprasAttribute(): float
    {
        return $this->lancamentos()->sum('custo_total_real');
    }
}
