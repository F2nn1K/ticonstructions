<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riscos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obra_id')->nullable();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('categoria', [
                'seguranca', 'financeiro', 'ambiental',
                'cronograma', 'qualidade', 'outro'
            ])->default('outro');
            $table->unsignedTinyInteger('probabilidade')->default(1); // 1-5
            $table->unsignedTinyInteger('impacto')->default(1);        // 1-5
            $table->unsignedTinyInteger('nivel_risco')->virtualAs('probabilidade * impacto');
            $table->text('plano_acao')->nullable();
            $table->string('responsavel')->nullable();
            $table->date('prazo')->nullable();
            $table->enum('status', ['identificado', 'em_mitigacao', 'mitigado', 'aceito'])
                ->default('identificado');
            $table->unsignedBigInteger('registrado_por');
            $table->timestamps();

            $table->foreign('obra_id')->references('id')->on('obras')->onDelete('set null');
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('cascade');
            $table->index(['obra_id', 'status']);
            $table->index('nivel_risco');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riscos');
    }
};
