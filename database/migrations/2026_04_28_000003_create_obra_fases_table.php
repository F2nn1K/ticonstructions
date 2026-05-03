<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obra_fases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('fase_catalogo_id')->constrained('fases_catalogo');
            $table->integer('ordem');
            $table->string('nome_personalizado')->nullable();

            // Datas baseline (planejadas originalmente)
            $table->date('data_inicio_baseline')->nullable();
            $table->date('data_fim_baseline')->nullable();

            // Datas planejadas (revisadas)
            $table->date('data_inicio_planejada')->nullable();
            $table->date('data_fim_planejada')->nullable();

            // Datas reais
            $table->date('data_inicio_real')->nullable();
            $table->date('data_fim_real')->nullable();

            $table->enum('status', ['pendente', 'em_andamento', 'concluida', 'atrasada', 'suspensa'])
                  ->default('pendente');

            $table->integer('percentual_planejado')->default(0);
            $table->integer('percentual_realizado')->default(0);

            // Quem avançou a fase
            $table->foreignId('avancado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('avancado_em')->nullable();

            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obra_fases');
    }
};
