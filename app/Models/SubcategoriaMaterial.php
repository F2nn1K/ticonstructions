<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcategoriaMaterial extends Model
{
    protected $table = 'subcategorias_material';

    protected $fillable = ['categoria_id', 'nome', 'unidade', 'ativo'];

    public function categoria()
    {
        return $this->belongsTo(CategoriaMaterial::class, 'categoria_id');
    }
}
