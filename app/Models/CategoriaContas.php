<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaContas extends Model
{
    use HasFactory;
    
    protected $table = 'categorias_contas';
    
    protected $fillable = [
        'tipo',
        'nome',
        'descricao',
        'cor',
        'ativo'
    ];
    
    protected $casts = [
        'ativo' => 'boolean'
    ];
}
