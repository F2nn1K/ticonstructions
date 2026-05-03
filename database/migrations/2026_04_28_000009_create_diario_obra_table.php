<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('diario_obra')) {
            Schema::create('diario_obra', function (Blueprint $table) {
                $table->id();
                $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
                $table->foreignId('obra_fase_id')->nullable()->constrained('obra_fases')->nullOnDelete();
                $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

                // Identificação do registro
                $table->date('data_registro');
                $table->enum('tipo', ['diario', 'semanal'])->default('diario')
                      ->comment('Registro diário ou relatório semanal');
                $table->string('titulo')->nullable()
                      ->comment('Título resumido do dia/semana');

                // Contexto da execução
                $table->string('local_area')->nullable()
                      ->comment('Ex.: Pavimento 2 - Bloco A, Fundações setor norte');
                $table->text('equipe_presente')->nullable()
                      ->comment('Descrição livre da equipe: qtd pedreiros, serventes, etc.');
                $table->integer('total_trabalhadores')->nullable();

                // O que foi feito
                $table->text('atividades_executadas')
                      ->comment('Descrição das atividades do dia/semana');
                $table->text('materiais_utilizados')->nullable()
                      ->comment('Materiais consumidos na jornada');

                // Condições e problemas
                $table->enum('condicoes_climaticas', [
                    'sol', 'nublado', 'chuva_leve', 'chuva_forte', 'vento'
                ])->nullable();
                $table->text('ocorrencias')->nullable()
                      ->comment('Imprevistos, atrasos, acidentes, problemas');
                $table->text('solucoes_adotadas')->nullable();

                // Progresso
                $table->decimal('percentual_avanco_dia', 5, 2)->nullable()
                      ->comment('Avanço percentual desta jornada');

                // Fotos (paths armazenados como JSON)
                $table->json('fotos')->nullable();

                // Observações gerais
                $table->text('observacoes')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['obra_id', 'data_registro'], 'diario_obra_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('diario_obra');
    }
};
