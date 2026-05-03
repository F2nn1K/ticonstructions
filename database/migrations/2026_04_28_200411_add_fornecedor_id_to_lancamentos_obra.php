<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lancamentos_obra', function (Blueprint $table) {
            if (!Schema::hasColumn('lancamentos_obra', 'fornecedor_id')) {
                $table->unsignedBigInteger('fornecedor_id')->nullable()->after('obra_fase_id');
                $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('set null');
            }
        });

        // Adicionar coluna produto_codigo para facilitar comparação de cotações
        Schema::table('lancamentos_obra', function (Blueprint $table) {
            if (!Schema::hasColumn('lancamentos_obra', 'produto_codigo')) {
                $table->string('produto_codigo', 60)->nullable()->after('descricao')
                      ->comment('Código único do produto para comparar cotações entre fornecedores');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lancamentos_obra', function (Blueprint $table) {
            $table->dropForeign(['fornecedor_id']);
            $table->dropColumn(['fornecedor_id', 'produto_codigo']);
        });
    }
};
