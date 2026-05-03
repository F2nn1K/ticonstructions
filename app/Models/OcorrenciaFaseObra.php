<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcorrenciaFaseObra extends Model
{
    protected $table = 'ocorrencias_fase';

    protected $fillable = [
        'obra_id', 'obra_fase_id', 'tipo', 'data_ocorrencia',
        'impacto_dias', 'titulo', 'descricao', 'acao_tomada', 'registrado_por',
    ];

    protected $casts = [
        'data_ocorrencia' => 'date',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function fase()
    {
        return $this->belongsTo(ObraFase::class, 'obra_fase_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'chuva'              => 'Chuva',
            'falta_material'     => 'Falta de Material',
            'falta_mao_de_obra'  => 'Falta de Mão de Obra',
            'erro_projeto'       => 'Erro de Projeto',
            'problema_equipamento' => 'Problema de Equipamento',
            'acidente'           => 'Acidente',
            'outro'              => 'Outro',
            default              => $this->tipo,
        };
    }
}
