<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LancamentoObra extends Model
{
    use SoftDeletes;

    protected $table = 'lancamentos_obra';

    protected $fillable = [
        'lote_id',
        'obra_id', 'obra_fase_id', 'fornecedor_id', 'categoria_id', 'subcategoria_id',
        'tipo', 'modo_lancamento', 'descricao', 'produto_codigo', 'fornecedor', 'nota_fiscal',
        'quantidade', 'unidade',
        'custo_unitario_orcado', 'custo_unitario_real',
        'custo_total_orcado', 'custo_total_real',
        'data_lancamento', 'data_prevista_pagamento', 'data_real_pagamento',
        'status_pagamento', 'excluir_base_taxa_admin', 'observacoes', 'created_by',
    ];

    protected $casts = [
        'data_lancamento'          => 'date',
        'data_prevista_pagamento'  => 'date',
        'data_real_pagamento'      => 'date',
        'quantidade'               => 'decimal:3',
        'custo_unitario_orcado'    => 'decimal:2',
        'custo_unitario_real'      => 'decimal:2',
        'custo_total_orcado'       => 'decimal:2',
        'custo_total_real'         => 'decimal:2',
        'excluir_base_taxa_admin'  => 'boolean',
    ];

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function fase()
    {
        return $this->belongsTo(ObraFase::class, 'obra_fase_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaMaterial::class, 'categoria_id');
    }

    public function subcategoria()
    {
        return $this->belongsTo(SubcategoriaMaterial::class, 'subcategoria_id');
    }

    public function fornecedorRel()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Boot: calcula totais automaticamente ─────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($lancamento) {
            if ($lancamento->custo_unitario_orcado && $lancamento->quantidade) {
                $lancamento->custo_total_orcado = $lancamento->quantidade * $lancamento->custo_unitario_orcado;
            }
            if ($lancamento->custo_unitario_real && $lancamento->quantidade) {
                $lancamento->custo_total_real = $lancamento->quantidade * $lancamento->custo_unitario_real;
            }
        });
    }

    // ── Acessores ────────────────────────────────────────────────────────────

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'material'    => 'Material',
            'servico'     => 'Serviço',
            'mao_de_obra' => 'Mão de Obra',
            'equipamento' => 'Equipamento',
            'terceiro'    => 'Terceiro',
            default       => $this->tipo,
        };
    }

    public function getStatusPagamentoBadgeAttribute(): string
    {
        return match($this->status_pagamento) {
            'pendente'   => 'warning',
            'pago'       => 'success',
            'cancelado'  => 'danger',
            default      => 'secondary',
        };
    }
}
