<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Buscar ID do perfil Administrador
$adminId = DB::table('profiles')->where('name', 'Admin')->value('id');

if (!$adminId) {
    echo "Perfil Administrador não encontrado!\n";
    exit(1);
}

// Buscar IDs das novas permissões
$permissoes = DB::table('permissions')
    ->whereIn('code', ['fornecedores', 'cotacao', 'ordem_compra', 'recebimento', 'nf_entrada', 'vale_retirada'])
    ->pluck('id');

$count = 0;
foreach ($permissoes as $permId) {
    $exists = DB::table('profile_permissions')
        ->where('profile_id', $adminId)
        ->where('permission_id', $permId)
        ->exists();
    
    if (!$exists) {
        DB::table('profile_permissions')->insert([
            'profile_id' => $adminId,
            'permission_id' => $permId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $count++;
    }
}

echo "Permissões vinculadas ao Administrador: $count\n";

