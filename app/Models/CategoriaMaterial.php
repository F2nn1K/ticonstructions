<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaMaterial extends Model
{
    protected $table = 'categorias_material';

    protected $fillable = ['nome', 'icone', 'tipo', 'ordem', 'ativo'];

    public function subcategorias()
    {
        return $this->hasMany(SubcategoriaMaterial::class, 'categoria_id')->where('ativo', true)->orderBy('nome');
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }
}
