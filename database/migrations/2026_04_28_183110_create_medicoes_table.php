<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obra_id');
            $table->unsignedBigInteger('obra_fase_id')->nullable();
            $table->date('data_medicao');
            $table->decimal('percentual_medido', 5, 2)->default(0);
            $table->decimal('percentual_acumulado', 5, 2)->default(0);
            $table->decimal('valor_medicao', 15, 2)->nullable();
            $table->text('descricao')->nullable();
            $table->text('observacoes')->nullable();
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente');
            $table->unsignedBigInteger('registrado_por');
            $table->unsignedBigInteger('aprovado_por')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->timestamps();

            $table->foreign('obra_id')->references('id')->on('obras')->onDelete('cascade');
            $table->foreign('obra_fase_id')->references('id')->on('obra_fases')->onDelete('set null');
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');

            $table->index(['obra_id', 'data_medicao']);
            $table->index(['status', 'data_medicao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicoes');
    }
};
