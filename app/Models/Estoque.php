<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    use HasFactory;
    
    protected $table = 'estoque';
    
    protected $fillable = [
        'nome',
        'descricao',
        'quantidade',
        'ncm',
        'codigo_barras',
        'unidade',
        'preco_custo'
    ];
    
    // Relacionamentos
    public function baixas()
    {
        return $this->hasMany(Baixa::class, 'produto_id');
    }
    
    public function minMax()
    {
        return $this->hasOne(EstoqueMinMax::class, 'produto_id');
    }
}
