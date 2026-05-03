<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_itens_gasto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();

            $table->string('descricao_normalizada', 255)
                  ->comment('LOWER(trim) para chave única por obra');

            $table->string('descricao', 255)
                  ->comment('Texto exibido conforme última digitação do usuário');

            $table->foreignId('categoria_id')->constrained('categorias_material');
            $table->foreignId('subcategoria_id')->nullable()->constrained('subcategorias_material')->nullOnDelete();

            $table->enum('tipo', ['material', 'servico', 'mao_de_obra', 'equipamento', 'terceiro'])->default('material');

            $table->decimal('quantidade_padrao', 12, 3)->nullable();
            $table->string('unidade', 32)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['obra_id', 'descricao_normalizada'], 'uniq_catalogo_obra_desc_norm');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_itens_gasto');
    }
};
