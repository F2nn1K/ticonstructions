<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lancamentos_obra', function (Blueprint $table) {
            if (!Schema::hasColumn('lancamentos_obra', 'modo_lancamento')) {
                // por_unidade = qtd × preço unit | por_hora = horas × R$/h
                // salario = valor mensal fixo | empreitada = valor total fechado
                // valor_total = qualquer categoria sem medida (valor direto)
                $table->enum('modo_lancamento', [
                    'por_unidade', 'por_hora', 'salario', 'empreitada', 'valor_total'
                ])->default('por_unidade')->after('tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lancamentos_obra', function (Blueprint $table) {
            $table->dropColumn('modo_lancamento');
        });
    }
};
