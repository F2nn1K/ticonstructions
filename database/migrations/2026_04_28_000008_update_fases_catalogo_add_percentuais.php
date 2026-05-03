<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona campos de percentual de obra e icone em fases_catalogo.
 * Cria tabela fases_catalogo_tarefas (checklist de cada fase).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Enriquece fases_catalogo ───────────────────────────────────────
        Schema::table('fases_catalogo', function (Blueprint $table) {
            if (!Schema::hasColumn('fases_catalogo', 'icone')) {
                $table->string('icone', 60)->nullable()->after('descricao');
            }
            if (!Schema::hasColumn('fases_catalogo', 'percentual_inicio')) {
                $table->decimal('percentual_inicio', 5, 2)->default(0)
                      ->after('icone')
                      ->comment('% acumulado da obra onde esta fase começa');
            }
            if (!Schema::hasColumn('fases_catalogo', 'percentual_fim')) {
                $table->decimal('percentual_fim', 5, 2)->default(100)
                      ->after('percentual_inicio')
                      ->comment('% acumulado da obra onde esta fase termina');
            }
        });

        // ── 2. Checklist de tarefas por fase ─────────────────────────────────
        if (!Schema::hasTable('fases_catalogo_tarefas')) {
            Schema::create('fases_catalogo_tarefas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fase_catalogo_id')
                      ->constrained('fases_catalogo')
                      ->cascadeOnDelete();
                $table->string('grupo', 120)->nullable()
                      ->comment('Subgrupo dentro da fase (ex: Estudos Técnicos)');
                $table->string('nome')
                      ->comment('Item do checklist (ex: Levantamento topográfico)');
                $table->integer('ordem')->default(0);
                $table->boolean('ativo')->default(true);
                $table->timestamps();

                $table->index(['fase_catalogo_id', 'ordem'], 'fct_fase_ordem_idx');
            });
        }

        // ── 3. Checklist de execução por obra_fase ───────────────────────────
        if (!Schema::hasTable('obra_fase_tarefas')) {
            Schema::create('obra_fase_tarefas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('obra_fase_id')
                      ->constrained('obra_fases')
                      ->cascadeOnDelete();
                $table->foreignId('tarefa_catalogo_id')
                      ->nullable()
                      ->constrained('fases_catalogo_tarefas')
                      ->nullOnDelete();
                $table->string('nome');
                $table->boolean('concluida')->default(false);
                $table->date('data_conclusao')->nullable();
                $table->foreignId('concluida_por')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();
                $table->text('observacoes')->nullable();
                $table->integer('ordem')->default(0);
                $table->timestamps();

                $table->index(['obra_fase_id', 'concluida'], 'oft_fase_concluida_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('obra_fase_tarefas');
        Schema::dropIfExists('fases_catalogo_tarefas');

        Schema::table('fases_catalogo', function (Blueprint $table) {
            $table->dropColumn(['icone', 'percentual_inicio', 'percentual_fim']);
        });
    }
};
