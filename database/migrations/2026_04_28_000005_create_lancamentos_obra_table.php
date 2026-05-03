<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lancamentos_obra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();

            // Fase ativa NO MOMENTO do lançamento — preenchida automaticamente
            $table->foreignId('obra_fase_id')->constrained('obra_fases');

            $table->foreignId('categoria_id')->constrained('categorias_material');
            $table->foreignId('subcategoria_id')->nullable()->constrained('subcategorias_material')->nullOnDelete();

            $table->enum('tipo', ['material', 'servico', 'mao_de_obra', 'equipamento', 'terceiro'])
                  ->default('material');

            $table->string('descricao');
            $table->string('fornecedor')->nullable();
            $table->string('nota_fiscal')->nullable();

            $table->decimal('quantidade', 10, 3)->default(1);
            $table->string('unidade')->nullable();
            $table->decimal('custo_unitario_orcado', 12, 2)->nullable();
            $table->decimal('custo_unitario_real', 12, 2)->nullable();
            $table->decimal('custo_total_orcado', 12, 2)->nullable();
            $table->decimal('custo_total_real', 12, 2)->nullable();

            $table->date('data_lancamento');
            $table->date('data_prevista_pagamento')->nullable();
            $table->date('data_real_pagamento')->nullable();

            $table->enum('status_pagamento', ['pendente', 'pago', 'cancelado'])->default('pendente');
            $table->text('observacoes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lancamentos_obra');
    }
};
