<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaseCatalogoTarefa extends Model
{
    protected $table = 'fases_catalogo_tarefas';

    protected $fillable = ['fase_catalogo_id', 'grupo', 'nome', 'ordem', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function fase()
    {
        return $this->belongsTo(FaseCatalogo::class, 'fase_catalogo_id');
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }
}
