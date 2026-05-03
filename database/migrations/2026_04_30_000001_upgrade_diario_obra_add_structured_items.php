<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Novos campos na tabela principal ─────────────────────────────────
        Schema::table('diario_obra', function (Blueprint $table) {
            if (!Schema::hasColumn('diario_obra', 'numero')) {
                $table->unsignedInteger('numero')->nullable()->after('id')
                      ->comment('Número sequencial do RDO (gerado automaticamente)');
            }
            if (!Schema::hasColumn('diario_obra', 'status')) {
                $table->enum('status', ['rascunho', 'finalizado'])->default('rascunho')->after('numero');
            }
            // Clima por turno (substitui condicoes_climaticas)
            if (!Schema::hasColumn('diario_obra', 'tempo_manha')) {
                $table->json('tempo_manha')->nullable()->after('status')
                      ->comment('{"status":"praticavel","clima":"sol"}');
            }
            if (!Schema::hasColumn('diario_obra', 'tempo_tarde')) {
                $table->json('tempo_tarde')->nullable()->after('tempo_manha');
            }
            if (!Schema::hasColumn('diario_obra', 'tempo_noite')) {
                $table->json('tempo_noite')->nullable()->after('tempo_tarde');
            }
            // Comentários gerais (alias de observacoes para nomenclatura do modelo)
            if (!Schema::hasColumn('diario_obra', 'comentarios')) {
                $table->text('comentarios')->nullable()->after('observacoes');
            }
        });

        // ── Mão de obra do dia ────────────────────────────────────────────────
        if (!Schema::hasTable('diario_mao_de_obra')) {
            Schema::create('diario_mao_de_obra', function (Blueprint $table) {
                $table->id();
                $table->foreignId('diario_obra_id')
                      ->constrained('diario_obra')->cascadeOnDelete();
                $table->unsignedSmallInteger('quantidade')->default(1);
                $table->string('funcao', 200)->comment('Cargo/função: Pedreiro, Ajudante, Eng. Civil...');
                $table->string('profissional_fornecedor', 200)->nullable()
                      ->comment('Nome do profissional ou empresa fornecedora');
                $table->string('observacao', 500)->nullable();
                $table->unsignedSmallInteger('ordem')->default(0);
                $table->timestamps();
            });
        }

        // ── Equipamentos do dia ───────────────────────────────────────────────
        if (!Schema::hasTable('diario_equipamentos')) {
            Schema::create('diario_equipamentos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('diario_obra_id')
                      ->constrained('diario_obra')->cascadeOnDelete();
                $table->unsignedSmallInteger('quantidade')->default(1);
                $table->string('descricao', 300)->comment('Nome/descrição do equipamento');
                $table->unsignedSmallInteger('ordem')->default(0);
                $table->timestamps();
            });
        }

        // ── Atividades executadas (estruturadas) ──────────────────────────────
        if (!Schema::hasTable('diario_atividades')) {
            Schema::create('diario_atividades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('diario_obra_id')
                      ->constrained('diario_obra')->cascadeOnDelete();
                $table->text('descricao')->comment('Descrição da atividade / código SINAPI');
                $table->string('qtde_orcada', 80)->nullable()
                      ->comment('Ex.: 591,03 M2');
                $table->string('qtde_realizada', 80)->nullable();
                $table->decimal('evolucao_percentual', 5, 2)->nullable()
                      ->comment('0-100');
                $table->enum('status_atividade', [
                    'em_andamento', 'paralisada', 'finalizada', 'nao_iniciada'
                ])->default('em_andamento');
                $table->text('comentario')->nullable();
                $table->unsignedSmallInteger('ordem')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('diario_atividades');
        Schema::dropIfExists('diario_equipamentos');
        Schema::dropIfExists('diario_mao_de_obra');

        Schema::table('diario_obra', function (Blueprint $table) {
            $table->dropColumn([
                'numero', 'status', 'tempo_manha', 'tempo_tarde', 'tempo_noite', 'comentarios',
            ]);
        });
    }
};
