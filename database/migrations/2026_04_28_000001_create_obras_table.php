<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obras', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->unique()->nullable();
            $table->text('descricao')->nullable();
            $table->string('endereco')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cliente')->nullable();
            $table->string('responsavel_tecnico')->nullable();
            $table->decimal('valor_contrato', 15, 2)->nullable();
            $table->decimal('area_total', 10, 2)->nullable();
            $table->date('data_inicio_prevista')->nullable();
            $table->date('data_fim_prevista')->nullable();
            $table->date('data_inicio_real')->nullable();
            $table->date('data_fim_real')->nullable();
            $table->enum('status', ['planejamento', 'em_andamento', 'concluida', 'suspensa', 'cancelada'])->default('planejamento');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obras');
    }
};
