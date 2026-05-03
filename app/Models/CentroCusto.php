<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroCusto extends Model
{
    use HasFactory;
    
    protected $table = 'centros_custo';
    
    protected $fillable = [
        'nome',
        'ativo'
    ];
    
    protected $casts = [
        'ativo' => 'boolean'
    ];
    
    // Desabilitar timestamps se não existem na tabela
    public $timestamps = false;
}
