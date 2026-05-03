<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lancamentos_obra', function (Blueprint $table) {
            $table->char('lote_id', 36)->nullable()->after('id')->index()
                  ->comment('UUID que agrupa itens lançados em conjunto num mesmo formulário');
        });
    }

    public function down(): void
    {
        Schema::table('lancamentos_obra', function (Blueprint $table) {
            $table->dropColumn('lote_id');
        });
    }
};
