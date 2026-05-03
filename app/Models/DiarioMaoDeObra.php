<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiarioMaoDeObra extends Model
{
    protected $table = 'diario_mao_de_obra';

    protected $fillable = [
        'diario_obra_id', 'quantidade', 'funcao',
        'profissional_fornecedor', 'observacao', 'ordem',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'ordem'      => 'integer',
    ];

    public function diario()
    {
        return $this->belongsTo(DiarioObra::class, 'diario_obra_id');
    }
}
