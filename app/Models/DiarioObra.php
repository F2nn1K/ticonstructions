<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiarioObra extends Model
{
    use SoftDeletes;

    protected $table = 'diario_obra';

    protected $fillable = [
        'numero', 'status',
        'obra_id', 'obra_fase_id', 'responsavel_id', 'created_by',
        'data_registro', 'tipo', 'titulo',
        'local_area', 'equipe_presente', 'total_trabalhadores',
        // Legado (mantido para registros antigos)
        'atividades_executadas', 'materiais_utilizados',
        'condicoes_climaticas', 'ocorrencias', 'solucoes_adotadas',
        // Novos campos
        'tempo_manha', 'tempo_tarde', 'tempo_noite',
        'percentual_avanco_dia', 'fotos', 'observacoes', 'comentarios',
    ];

    protected $casts = [
        'data_registro'        => 'date',
        'fotos'                => 'array',
        'tempo_manha'          => 'array',
        'tempo_tarde'          => 'array',
        'tempo_noite'          => 'array',
        'percentual_avanco_dia'=> 'decimal:2',
        'total_trabalhadores'  => 'integer',
    ];

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function fase()
    {
        return $this->belongsTo(ObraFase::class, 'obra_fase_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function maoDeObra()
    {
        return $this->hasMany(DiarioMaoDeObra::class, 'diario_obra_id')->orderBy('ordem');
    }

    public function equipamentos()
    {
        return $this->hasMany(DiarioEquipamento::class, 'diario_obra_id')->orderBy('ordem');
    }

    public function atividades()
    {
        return $this->hasMany(DiarioAtividade::class, 'diario_obra_id')->orderBy('ordem');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePorObra($query, int $obraId)
    {
        return $query->where('obra_id', $obraId);
    }

    public function scopeRecentes($query)
    {
        return $query->orderBy('data_registro', 'desc')->orderBy('created_at', 'desc');
    }

    // ── Acessores ────────────────────────────────────────────────────────────

    public function getTituloFormatadoAttribute(): string
    {
        if ($this->titulo) return $this->titulo;
        return $this->tipo_label . ' — ' . ($this->data_registro ? $this->data_registro->format('d/m/Y') : '');
    }

    public function getTipoLabelAttribute(): string
    {
        return $this->tipo === 'semanal' ? 'Relatório Semanal' : 'Diário de Obra';
    }

    public function getTipoBadgeAttribute(): string
    {
        return $this->tipo === 'semanal' ? 'info' : 'primary';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'finalizado' ? 'Finalizado' : 'Rascunho';
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'finalizado' ? 'success' : 'secondary';
    }

    public function getClimaIconeAttribute(): string
    {
        return $this->climaIconePorValor($this->condicoes_climaticas);
    }

    public function getClimaLabelAttribute(): string
    {
        return $this->climaLabelPorValor($this->condicoes_climaticas);
    }

    public function climaIconePorValor(?string $v): string
    {
        return match($v) {
            'sol'          => '☀️',
            'nublado'      => '☁️',
            'chuva_leve'   => '🌦️',
            'chuva_forte'  => '🌧️',
            'vento'        => '💨',
            default        => '☀️',
        };
    }

    public function climaLabelPorValor(?string $v): string
    {
        return match($v) {
            'sol'          => 'Sol',
            'nublado'      => 'Nublado',
            'chuva_leve'   => 'Chuva Leve',
            'chuva_forte'  => 'Chuva Forte',
            'vento'        => 'Ventania',
            default        => 'Sol',
        };
    }

    public function getTempoTurnoLabel(array $turno): string
    {
        $status = ($turno['status'] ?? 'praticavel') === 'praticavel' ? 'Praticável' : 'Impraticável';
        $icone  = $this->climaIconePorValor($turno['clima'] ?? 'sol');
        return "{$status} {$icone}";
    }

    public function temOcorrencias(): bool
    {
        return !empty($this->ocorrencias);
    }

    public function totalFotos(): int
    {
        if (!is_array($this->fotos)) return 0;
        return count($this->fotos);
    }

    /**
     * Fotos agrupadas por pasta.
     * Suporta formato legado (array de strings) e novo (array de arrays com pasta/caminho).
     */
    public function fotosAgrupadasAttribute(): array
    {
        if (!is_array($this->fotos) || empty($this->fotos)) return [];

        $grupos = [];
        foreach ($this->fotos as $item) {
            if (is_string($item)) {
                // Formato legado: path direto
                $pasta = 'Fotos';
                $caminho = $item;
            } else {
                $pasta   = $item['pasta']   ?? 'Fotos';
                $caminho = $item['caminho'] ?? ($item['path'] ?? '');
            }
            if (!$caminho) continue;
            $grupos[$pasta][] = $caminho;
        }
        return $grupos;
    }

    // ── Numeração automática ──────────────────────────────────────────────────

    public static function proximoNumero(): int
    {
        return (static::withTrashed()->max('numero') ?? 0) + 1;
    }
}
