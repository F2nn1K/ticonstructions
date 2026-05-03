<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diario_atividades', function (Blueprint $table) {
            if (!Schema::hasColumn('diario_atividades', 'obra_fase_tarefa_id')) {
                $table->foreignId('obra_fase_tarefa_id')
                      ->nullable()
                      ->after('diario_obra_id')
                      ->constrained('obra_fase_tarefas')
                      ->nullOnDelete()
                      ->comment('Tarefa do cronograma vinculada a esta atividade do diário');
            }
        });
    }

    public function down(): void
    {
        Schema::table('diario_atividades', function (Blueprint $table) {
            $table->dropForeign(['obra_fase_tarefa_id']);
            $table->dropColumn('obra_fase_tarefa_id');
        });
    }
};
