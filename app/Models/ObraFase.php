<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ObraFase extends Model
{
    protected $table = 'obra_fases';

    protected $fillable = [
        'obra_id', 'fase_catalogo_id', 'ordem', 'nome_personalizado',
        'data_inicio_baseline', 'data_fim_baseline',
        'data_inicio_planejada', 'data_fim_planejada',
        'data_inicio_real', 'data_fim_real',
        'status', 'percentual_planejado', 'percentual_realizado',
        'avancado_por', 'avancado_em', 'observacoes',
    ];

    protected $casts = [
        'data_inicio_baseline'  => 'date',
        'data_fim_baseline'     => 'date',
        'data_inicio_planejada' => 'date',
        'data_fim_planejada'    => 'date',
        'data_inicio_real'      => 'date',
        'data_fim_real'         => 'date',
        'avancado_em'           => 'datetime',
    ];

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function faseCatalogo()
    {
        return $this->belongsTo(FaseCatalogo::class, 'fase_catalogo_id');
    }

    public function lancamentos()
    {
        return $this->hasMany(LancamentoObra::class, 'obra_fase_id');
    }

    public function ocorrencias()
    {
        return $this->hasMany(OcorrenciaFaseObra::class, 'obra_fase_id');
    }

    public function avancadoPor()
    {
        return $this->belongsTo(User::class, 'avancado_por');
    }

    // ── Acessores ────────────────────────────────────────────────────────────

    public function getNomeAttribute(): string
    {
        return $this->nome_personalizado ?? $this->faseCatalogo->nome ?? "Fase {$this->ordem}";
    }

    public function getAtrasadaAttribute(): bool
    {
        if ($this->status !== 'em_andamento') return false;
        $prazo = $this->data_fim_planejada ?? $this->data_fim_baseline;
        return $prazo && $prazo->isPast();
    }

    public function getDiasAtrasadosAttribute(): int
    {
        if (!$this->atrasada) return 0;
        $prazo = $this->data_fim_planejada ?? $this->data_fim_baseline;
        return $prazo ? (int) $prazo->diffInDays(now()) : 0;
    }

    public function getCustoTotalRealAttribute(): float
    {
        return (float) $this->lancamentos()->sum('custo_total_real');
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->atrasada) return 'danger';
        return match($this->status) {
            'pendente'     => 'secondary',
            'em_andamento' => 'primary',
            'concluida'    => 'success',
            'atrasada'     => 'danger',
            'suspensa'     => 'warning',
            default        => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->atrasada) return "Atrasada ({$this->dias_atrasados}d)";
        return match($this->status) {
            'pendente'     => 'Aguardando',
            'em_andamento' => 'Em Andamento',
            'concluida'    => 'Concluída',
            'atrasada'     => 'Atrasada',
            'suspensa'     => 'Suspensa',
            default        => $this->status,
        };
    }

    // ── Métodos de negócio ───────────────────────────────────────────────────

    /**
     * Avança esta fase para concluída e inicia a próxima.
     */
    public function avancar(int $userId): array
    {
        if ($this->status !== 'em_andamento') {
            return ['sucesso' => false, 'mensagem' => 'Esta fase não está em andamento.'];
        }

        // Conclui a fase atual
        $this->update([
            'status'       => 'concluida',
            'data_fim_real' => now()->toDateString(),
            'percentual_realizado' => 100,
            'avancado_por' => $userId,
            'avancado_em'  => now(),
        ]);

        // Busca a próxima fase
        $proxima = ObraFase::where('obra_id', $this->obra_id)
            ->where('ordem', $this->ordem + 1)
            ->first();

        if ($proxima) {
            $proxima->update([
                'status'            => 'em_andamento',
                'data_inicio_real'  => now()->toDateString(),
            ]);

            // Atualiza status da obra se ainda não estava em andamento
            $this->obra->update(['status' => 'em_andamento']);

            return ['sucesso' => true, 'mensagem' => "Avançado para: {$proxima->nome}", 'proxima_fase' => $proxima];
        }

        // Não há próxima fase — obra concluída
        $this->obra->update([
            'status'        => 'concluida',
            'data_fim_real' => now()->toDateString(),
        ]);

        return ['sucesso' => true, 'mensagem' => 'Obra concluída! Todas as fases foram finalizadas.', 'proxima_fase' => null];
    }
}
