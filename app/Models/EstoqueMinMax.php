<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstoqueMinMax extends Model
{
    use HasFactory;

    protected $table = 'estoque_min_max';

    protected $fillable = [
        'produto_id',
        'minimo',
        'maximo',
    ];

    public function produto()
    {
        return $this->belongsTo(Estoque::class, 'produto_id');
    }
}


