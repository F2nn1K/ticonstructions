<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrdensCompraAuditoria
{
    /**
     * Preenche created_by_user_id na OC se a coluna existir.
     */
    public static function mergeCriadorUsuario(array &$dadosOrdemCompra, ?int $userId = null): void
    {
        if (! Schema::hasColumn('ordens_compra', 'created_by_user_id')) {
            return;
        }
        $uid = $userId ?? Auth::id();
        if ($uid) {
            $dadosOrdemCompra['created_by_user_id'] = $uid;
        }
    }

    /**
     * Registra criação da OC em logs_ordens_compra (não falha silenciosamente por falta de sessão).
     */
    public static function registrarLogCriacao(
        int $ocId,
        string $numero,
        string $acao,
        $fornecedorId,
        ?string $fornecedorNome,
        $valorTotal,
        $cotacaoId = null,
        ?int $explicitUserId = null
    ): void {
        try {
            if (! Schema::hasTable('logs_ordens_compra')) {
                return;
            }

            $uid = $explicitUserId ?? Auth::id();
            if ($uid === null || (int) $uid === 0) {
                $uid = 0;
                $userName = 'Sistema / não identificado';
            } else {
                $row = DB::table('users')->where('id', $uid)->first();
                $userName = $row->name ?? ('Usuário #'.$uid);
            }

            $cotacaoNumero = null;
            if ($cotacaoId) {
                $cot = DB::table('cotacoes')->where('id', $cotacaoId)->first();
                $cotacaoNumero = $cot->numero ?? null;
            }

            DB::table('logs_ordens_compra')->insert([
                'ordem_compra_id' => $ocId,
                'numero' => $numero,
                'user_id' => (int) $uid,
                'user_name' => $userName,
                'acao' => $acao,
                'dados_oc' => json_encode([
                    'fornecedor' => $fornecedorNome,
                    'fornecedor_id' => $fornecedorId,
                    'cotacao_id' => $cotacaoId,
                    'cotacao_numero' => $cotacaoNumero,
                    'data_emissao' => now()->format('Y-m-d'),
                    'valor_total' => $valorTotal,
                    'status' => 'pendente',
                    'itens' => [],
                ], JSON_UNESCAPED_UNICODE),
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
                'created_at' => now(),
            ]);

            Log::info("Ordem de Compra {$numero} — {$acao}", [
                'ordem_compra_id' => $ocId,
                'user_id' => $uid,
                'user_name' => $userName,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar log de O.C.', [
                'ordem_compra_id' => $ocId,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
