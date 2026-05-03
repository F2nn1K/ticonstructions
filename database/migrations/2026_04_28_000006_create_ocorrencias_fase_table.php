<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocorrencias_fase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('obra_fase_id')->constrained('obra_fases')->cascadeOnDelete();

            $table->enum('tipo', [
                'chuva',
                'falta_material',
                'falta_mao_de_obra',
                'erro_projeto',
                'problema_equipamento',
                'acidente',
                'outro'
            ]);

            $table->date('data_ocorrencia');
            $table->integer('impacto_dias')->default(0);
            $table->string('titulo');
            $table->text('descricao');
            $table->text('acao_tomada')->nullable();

            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocorrencias_fase');
    }
};
