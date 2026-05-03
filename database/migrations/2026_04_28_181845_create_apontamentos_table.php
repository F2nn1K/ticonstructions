<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apontamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funcionario_id');
            $table->unsignedBigInteger('obra_id')->nullable();
            $table->date('data');
            $table->time('hora_entrada')->nullable();
            $table->time('hora_saida')->nullable();
            $table->time('hora_almoco_saida')->nullable();
            $table->time('hora_almoco_retorno')->nullable();
            $table->decimal('horas_trabalhadas', 5, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente');
            $table->unsignedBigInteger('registrado_por');
            $table->unsignedBigInteger('aprovado_por')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->timestamps();

            $table->foreign('funcionario_id')->references('id')->on('funcionarios')->onDelete('cascade');
            $table->foreign('obra_id')->references('id')->on('obras')->onDelete('set null');
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');

            $table->index(['funcionario_id', 'data']);
            $table->index(['status', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apontamentos');
    }
};
