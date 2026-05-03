<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoItemGasto extends Model
{
    protected $table = 'catalogo_itens_gasto';

    protected $fillable = [
        'obra_id',
        'descricao_normalizada',
        'descricao',
        'categoria_id',
        'subcategoria_id',
        'tipo',
        'quantidade_padrao',
        'unidade',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantidade_padrao' => 'decimal:3',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaMaterial::class, 'categoria_id');
    }

    public function subcategoria()
    {
        return $this->belongsTo(SubcategoriaMaterial::class, 'subcategoria_id');
    }

    public static function normalizeDescricao(string $s): string
    {
        $s = trim(preg_replace('/\s+/u', ' ', $s));

        return mb_strtolower($s);
    }
}
