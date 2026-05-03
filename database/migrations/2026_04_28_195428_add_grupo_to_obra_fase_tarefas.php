<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obra_fase_tarefas', function (Blueprint $table) {
            $table->string('grupo', 120)->nullable()->after('obra_fase_id');
        });

        // Backfill: preencher grupo nas tarefas existentes via join com catálogo
        DB::statement("
            UPDATE obra_fase_tarefas oft
            JOIN fases_catalogo_tarefas fct ON fct.id = oft.tarefa_catalogo_id
            SET oft.grupo = fct.grupo
            WHERE oft.tarefa_catalogo_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('obra_fase_tarefas', function (Blueprint $table) {
            $table->dropColumn('grupo');
        });
    }
};
