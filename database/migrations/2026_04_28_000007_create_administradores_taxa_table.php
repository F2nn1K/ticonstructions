<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria as tabelas do sistema de Taxa de Administração.
 *
 * Regra de negócio:
 *  - Cada administrador tem ficha própria vinculada a um User do sistema.
 *  - A taxa (padrão 10%) é calculada sobre o CUSTO DE OBRA, excluindo
 *    lançamentos marcados como `excluir_base_taxa_admin = true`.
 *  - Pagamentos de taxa de administração NÃO entram na base de cálculo.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Ficha dos administradores ─────────────────────────────────────
        if (!Schema::hasTable('administradores_sistema'))
        Schema::create('administradores_sistema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nome');
            $table->string('cpf', 14)->nullable();
            $table->string('email')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('cargo')->nullable()->default('Administrador');
            $table->decimal('percentual_taxa', 5, 2)->default(10.00)
                  ->comment('Percentual padrão sobre o custo de obra (ex: 10.00 = 10%)');
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── 2. Taxas de administração por obra ───────────────────────────────
        if (!Schema::hasTable('taxa_administracao'))
        Schema::create('taxa_administracao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('administrador_id')->constrained('administradores_sistema');

            // Referência de apuração
            $table->date('data_referencia')
                  ->comment('Data de corte usada para calcular a base');
            $table->string('descricao')->nullable()
                  ->comment('Ex: Parcela 1 - Abril 2026');

            // Valores calculados
            $table->decimal('custo_base_obra', 15, 2)->default(0)
                  ->comment('Soma dos lancamentos_obra onde excluir_base_taxa_admin = false');
            $table->decimal('percentual', 5, 2)->default(10.00);
            $table->decimal('valor_taxa', 15, 2)->default(0)
                  ->comment('= custo_base_obra * percentual / 100');

            // Pagamento
            $table->enum('status', ['pendente', 'pago', 'cancelado'])->default('pendente');
            $table->date('data_vencimento')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->decimal('valor_pago', 15, 2)->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->string('comprovante')->nullable();
            $table->text('observacoes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pago_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Índices (nomes curtos para MySQL)
            $table->index(['obra_id', 'administrador_id', 'data_referencia'], 'taxa_obra_admin_ref_idx');
            $table->index(['status'], 'taxa_status_idx');
        });

        // ── 3. Flag em lancamentos_obra ──────────────────────────────────────
        if (Schema::hasTable('lancamentos_obra') && !Schema::hasColumn('lancamentos_obra', 'excluir_base_taxa_admin')) {
            Schema::table('lancamentos_obra', function (Blueprint $table) {
                $table->boolean('excluir_base_taxa_admin')->default(false)
                      ->after('status_pagamento')
                      ->comment('Se true, este lançamento NÃO entra na base de cálculo da taxa de administração');
            });
        }
    }

    public function down(): void
    {
        Schema::table('lancamentos_obra', function (Blueprint $table) {
            $table->dropColumn('excluir_base_taxa_admin');
        });

        Schema::dropIfExists('taxa_administracao');
        Schema::dropIfExists('administradores_sistema');
    }
};
