<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('estoque_pedido')) {
            Schema::create('estoque_pedido', function (Blueprint $table) {
                $table->id();
                $table->integer('codigo')->nullable()->index();
                $table->string('produto', 255);
                $table->text('descricao')->nullable();
                $table->decimal('valor_unitario', 12, 2)->default(0);
                $table->boolean('ativo')->default(true)->index();
                $table->unsignedBigInteger('id_usuario')->nullable();
                $table->timestamp('alterado')->nullable();
                $table->timestamps();
                $table->index('produto');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('estoque_pedido');
    }
};
