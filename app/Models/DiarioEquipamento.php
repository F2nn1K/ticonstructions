<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiarioEquipamento extends Model
{
    protected $table = 'diario_equipamentos';

    protected $fillable = [
        'diario_obra_id', 'quantidade', 'descricao', 'ordem',
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
