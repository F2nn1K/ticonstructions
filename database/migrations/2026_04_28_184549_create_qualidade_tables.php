<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Checklists de qualidade
        Schema::create('qualidade_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obra_id')->nullable();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('categoria', ['estrutura','acabamento','instalacao_eletrica','instalacao_hidraulica','seguranca','outro'])->default('outro');
            $table->json('itens')->nullable(); // array de strings com os itens
            $table->unsignedBigInteger('registrado_por');
            $table->timestamps();

            $table->foreign('obra_id')->references('id')->on('obras')->onDelete('set null');
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('cascade');
        });

        // Inspeções de qualidade
        Schema::create('qualidade_inspecoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obra_id');
            $table->unsignedBigInteger('obra_fase_id')->nullable();
            $table->unsignedBigInteger('checklist_id')->nullable();
            $table->string('titulo');
            $table->date('data_inspecao');
            $table->string('responsavel')->nullable();
            $table->enum('status', ['pendente','em_andamento','concluida','reprovada'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->json('respostas')->nullable(); // {item: 'conforme'|'nao_conforme'|'na'}
            $table->unsignedBigInteger('registrado_por');
            $table->timestamps();

            $table->foreign('obra_id')->references('id')->on('obras')->onDelete('cascade');
            $table->foreign('obra_fase_id')->references('id')->on('obra_fases')->onDelete('set null');
            $table->foreign('checklist_id')->references('id')->on('qualidade_checklists')->onDelete('set null');
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('cascade');
            $table->index(['obra_id', 'status']);
        });

        // Não Conformidades
        Schema::create('qualidade_nao_conformidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obra_id');
            $table->unsignedBigInteger('obra_fase_id')->nullable();
            $table->unsignedBigInteger('inspecao_id')->nullable();
            $table->string('titulo');
            $table->text('descricao');
            $table->enum('gravidade', ['leve','moderada','grave','critica'])->default('moderada');
            $table->enum('status', ['aberta','em_correcao','resolvida','aceita'])->default('aberta');
            $table->date('prazo_correcao')->nullable();
            $table->text('acao_corretiva')->nullable();
            $table->unsignedBigInteger('registrado_por');
            $table->timestamps();

            $table->foreign('obra_id')->references('id')->on('obras')->onDelete('cascade');
            $table->foreign('obra_fase_id')->references('id')->on('obra_fases')->onDelete('set null');
            $table->foreign('inspecao_id')->references('id')->on('qualidade_inspecoes')->onDelete('set null');
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('cascade');
            $table->index(['obra_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualidade_nao_conformidades');
        Schema::dropIfExists('qualidade_inspecoes');
        Schema::dropIfExists('qualidade_checklists');
    }
};
