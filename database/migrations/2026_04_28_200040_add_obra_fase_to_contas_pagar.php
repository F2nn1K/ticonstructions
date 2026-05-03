<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contas_pagar', function (Blueprint $table) {
            // Obra à qual esta conta pertence (para separar das despesas de overhead)
            if (!Schema::hasColumn('contas_pagar', 'obra_id')) {
                $table->unsignedBigInteger('obra_id')->nullable()->after('id');
                $table->foreign('obra_id')->references('id')->on('obras')->onDelete('set null');
            }
            // Fase da obra específica (opcional)
            if (!Schema::hasColumn('contas_pagar', 'obra_fase_id')) {
                $table->unsignedBigInteger('obra_fase_id')->nullable()->after('obra_id');
                $table->foreign('obra_fase_id')->references('id')->on('obra_fases')->onDelete('set null');
            }
            // Tipo: obra = custo direto da obra, overhead = custo da empresa
            if (!Schema::hasColumn('contas_pagar', 'tipo_custo')) {
                $table->enum('tipo_custo', ['obra', 'overhead', 'outro'])->default('overhead')->after('obra_fase_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->dropForeign(['obra_id']);
            $table->dropForeign(['obra_fase_id']);
            $table->dropColumn(['obra_id', 'obra_fase_id', 'tipo_custo']);
        });
    }
};
