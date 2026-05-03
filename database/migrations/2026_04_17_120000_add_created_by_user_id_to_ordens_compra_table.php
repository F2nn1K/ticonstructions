<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ordens_compra')) {
            return;
        }
        if (! Schema::hasColumn('ordens_compra', 'created_by_user_id')) {
            Schema::table('ordens_compra', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ordens_compra') && Schema::hasColumn('ordens_compra', 'created_by_user_id')) {
            Schema::table('ordens_compra', function (Blueprint $table) {
                $table->dropColumn('created_by_user_id');
            });
        }
    }
};
