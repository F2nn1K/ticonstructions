<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_material', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('icone')->nullable();
            $table->enum('tipo', ['material', 'servico', 'ambos'])->default('ambos');
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('subcategorias_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias_material')->cascadeOnDelete();
            $table->string('nome');
            $table->string('unidade')->nullable()->comment('m², m³, kg, un, h');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcategorias_material');
        Schema::dropIfExists('categorias_material');
    }
};
