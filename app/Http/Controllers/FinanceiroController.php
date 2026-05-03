<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class FinanceiroController extends Controller
{
    /**
     * Verifica se o usuário é administrador
     */
    private function isAdmin($user)
    {
        if (!$user || !$user->profile_id) {
            return false;
        }
        try {
            $profile = DB::table('profiles')->where('id', $user->profile_id)->first();
            if ($profile && strtolower($profile->name) === 'administrador') {
                return true;
            }
            $totalPermissions = DB::table('permissions')->count();
            $userPermissions = DB::table('profile_permissions')
                ->where('profile_id', $user->profile_id)
                ->count();
            if ($totalPermissions > 0 && ($userPermissions / $totalPermissions) >= 0.8) {
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }
    
    public function index()
    {
        return redirect()->route('financeiro.contas-pagar');
    }

    public function contasPagar()
    {
        $contas = collect([]);
        $centrosCusto = collect([]);
        $categorias = collect([]);
        $isAdmin = $this->isAdmin(Auth::user());
        
        try {
            // Verificar se a coluna categoria_id existe
            $hasCategoria = \Schema::hasColumn('contas_pagar', 'categoria_id');
            
            $query = DB::table('contas_pagar as cp')
                ->leftJoin('fornecedores as f', 'cp.fornecedor_id', '=', 'f.id')
                ->leftJoin('ordens_compra as oc', 'cp.ordem_compra_id', '=', 'oc.id')
                ->leftJoin('centros_custo as cc', 'cp.centro_custo_id', '=', 'cc.id');
            
            $selectFields = [
                'cp.*', 
                'f.razao_social as fornecedor_nome',
                'oc.numero as oc_numero',
                'cc.nome as centro_custo_nome'
            ];
            
            if ($hasCategoria) {
                $query->leftJoin('categorias_contas as cat', 'cp.categoria_id', '=', 'cat.id');
                $selectFields[] = 'cat.nome as categoria_nome';
            }
            
            // Por padrão, mostrar apenas contas "A Pagar" (pendente + vencido)
            $contas = $query->select($selectFields)
                ->whereIn('cp.status', ['pendente', 'vencido'])
                ->orderBy('cp.data_vencimento', 'asc')
                ->get();
        } catch (\Exception $e) {
            // Fallback se não tiver as colunas
            try {
                $contas = DB::table('contas_pagar')
                    ->orderBy('vencimento', 'asc')
                    ->get();
            } catch (\Exception $e2) {}
        }
        
        try {
            $centrosCusto = DB::table('centros_custo')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();
        } catch (\Exception $e) {}
        
        // Buscar categorias (apenas do tipo 'pagar')
        try {
            if (\Schema::hasTable('categorias_contas')) {
                $query = DB::table('categorias_contas')
                    ->where('ativo', 1);
                
                // Filtrar por tipo se a coluna existir
                if (\Schema::hasColumn('categorias_contas', 'tipo')) {
                    $query->where('tipo', 'pagar');
                }
                
                $categorias = $query->orderBy('nome')->get();
            }
        } catch (\Exception $e) {}
        
        return view('financeiro.contas-pagar', compact('contas', 'centrosCusto', 'categorias', 'isAdmin'));
    }
    
    // API para buscar conta a pagar
    public function getContaPagar($id)
    {
        try {
            $conta = DB::table('contas_pagar as cp')
                ->leftJoin('fornecedores as f', 'cp.fornecedor_id', '=', 'f.id')
                ->leftJoin('ordens_compra as oc', 'cp.ordem_compra_id', '=', 'oc.id')
                ->where('cp.id', $id)
                ->select('cp.*', 'f.razao_social as fornecedor_nome', 'oc.numero as oc_numero')
                ->first();
            
            return response()->json(['success' => true, 'conta' => $conta]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    // Baixar conta a pagar (marcar como pago)
    public function baixarContasPagarLote(Request $request)
    {
        try {
            $ids           = $request->input('ids', []);
            $dataPagamento = $request->input('data_pagamento', now()->format('Y-m-d'));
            $formaPagamento = $request->input('forma_pagamento');

            if (empty($ids) || !is_array($ids)) {
                return response()->json(['success' => false, 'message' => 'Nenhuma conta selecionada.'], 422);
            }

            $columns = $this->getTableColumns('contas_pagar');

            $contas = DB::table('contas_pagar')->whereIn('id', $ids)->get();
            $baixadas = 0;
            $erros    = 0;

            foreach ($contas as $conta) {
                if (in_array($conta->status, ['pago', 'cancelado'])) {
                    continue;
                }

                $valorTotal = floatval($conta->valor_liquido ?? $conta->valor ?? 0);

                $updateData = [
                    'status'          => 'pago',
                    'data_pagamento'  => $dataPagamento,
                    'valor_pago'      => $valorTotal,
                    'forma_pagamento' => $formaPagamento,
                    'updated_at'      => now(),
                ];

                if (in_array('baixa_user_id', $columns)) {
                    $updateData['baixa_user_id'] = auth()->id();
                }
                if (in_array('baixa_em', $columns)) {
                    $updateData['baixa_em'] = now();
                }

                try {
                    DB::table('contas_pagar')->where('id', $conta->id)->update($updateData);
                    $baixadas++;
                } catch (\Exception $e) {
                    $erros++;
                }
            }

            $msg = "{$baixadas} conta(s) baixada(s) com sucesso.";
            if ($erros > 0) {
                $msg .= " {$erros} conta(s) com erro.";
            }

            return response()->json(['success' => true, 'message' => $msg, 'baixadas' => $baixadas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function baixarContaPagar(Request $request, $id)
    {
        try {
            \Log::info("Baixando conta #{$id}. Has file: " . ($request->hasFile('comprovante') ? 'SIM' : 'NAO'));
            \Log::info("Request all: " . json_encode($request->all()));
            
            $conta = DB::table('contas_pagar')->where('id', $id)->first();
            
            if (!$conta) {
                return response()->json(['success' => false, 'message' => 'Conta não encontrada!'], 404);
            }
            
            // Converter valor_pago (pode vir formatado como "250,00" ou "0,00")
            // Usar null para detectar se foi enviado ou não
            $valorPagoRaw = $request->valor_pago;
            if ($valorPagoRaw === null || $valorPagoRaw === '') {
                // Não enviado: usar valor da conta como padrão
                $valorPago = floatval($conta->valor_liquido ?? $conta->valor ?? 0);
            } else {
                if (is_string($valorPagoRaw)) {
                    $valorPagoRaw = str_replace('.', '', $valorPagoRaw);
                    $valorPagoRaw = str_replace(',', '.', $valorPagoRaw);
                }
                $valorPago = floatval($valorPagoRaw);
                // Permite 0 (crédito / sem desembolso) — não forçar valor mínimo
            }

            // Calcular valor total da conta
            $valorTotal = floatval($conta->valor_liquido ?? $conta->valor ?? 0);
            
            // Pegar valor já pago anteriormente
            $valorPagoAnterior = floatval($conta->valor_pago ?? 0);
            
            // Total pago (anterior + atual)
            $totalPago = $valorPagoAnterior + $valorPago;
            
            // Determinar status: pago total, crédito (valor 0) ou pendente (parcial)
            // Valor 0,00 informado explicitamente = crédito / sem desembolso = baixa total
            $isCreditoZero = ($request->valor_pago !== null) && (floatval(str_replace(['.', ','], ['', '.'], (string) $request->valor_pago)) == 0);
            $isPagamentoTotal = $isCreditoZero || ($valorTotal <= 0.001) || ($totalPago >= ($valorTotal - 0.01));
            $status = $isPagamentoTotal ? 'pago' : 'pendente';
            
            // Preparar dados para atualização
            $updateData = [
                'status' => $status,
                'data_pagamento' => $request->data_pagamento ?? now()->format('Y-m-d'),
                'valor_pago' => $totalPago,
                'forma_pagamento' => $request->forma_pagamento ?? null,
                'updated_at' => now(),
            ];
            
            // Registrar quem fez a baixa e quando (se colunas existirem)
            if (\Schema::hasColumn('contas_pagar', 'baixa_user_id')) {
                $updateData['baixa_user_id'] = auth()->id();
            }
            if (\Schema::hasColumn('contas_pagar', 'baixa_em')) {
                $updateData['baixa_em'] = now();
            }
            
            // Processar upload do comprovante (PDF ou imagem) - SUPORTA MÚLTIPLOS COMPROVANTES
            if ($request->hasFile('comprovante')) {
                $file = $request->file('comprovante');
                
                // Validar extensão permitida (sem usar getMimeType que requer fileinfo)
                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Tipo de arquivo não permitido. Envie PDF, JPEG ou PNG.'
                    ], 400);
                }
                
                // Validar tamanho (máximo 5MB)
                if ($file->getSize() > 5 * 1024 * 1024) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Arquivo muito grande. Máximo permitido: 5MB.'
                    ], 400);
                }
                
                // Gerar nome único para o arquivo
                $fileName = 'comprovante_conta_' . $id . '_' . date('YmdHis') . '.' . $extension;
                
                // Criar diretório se não existir
                $uploadDir = storage_path('app/public/comprovantes');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Mover arquivo para o diretório (evita usar Storage que requer fileinfo)
                $file->move($uploadDir, $fileName);
                $fullPath = $uploadDir . '/' . $fileName;
                
                if (file_exists($fullPath)) {
                    $novoComprovante = 'comprovantes/' . $fileName;
                    
                    // Verificar se já existem comprovantes anteriores
                    $comprovantesExistentes = [];
                    if (!empty($conta->comprovante_path)) {
                        // Tentar decodificar como JSON (múltiplos comprovantes)
                        $decoded = json_decode($conta->comprovante_path, true);
                        if (is_array($decoded)) {
                            $comprovantesExistentes = $decoded;
                        } else {
                            // É um caminho simples (formato antigo), adicionar ao array
                            $comprovantesExistentes[] = $conta->comprovante_path;
                        }
                    }
                    
                    // Adicionar novo comprovante ao array
                    $comprovantesExistentes[] = $novoComprovante;
                    
                    // Salvar como JSON
                    $updateData['comprovante_path'] = json_encode($comprovantesExistentes);
                    \Log::info("Comprovante salvo para conta #{$id}: {$novoComprovante}. Total de comprovantes: " . count($comprovantesExistentes));
                }
            }
            
            // Atualizar conta como paga
            DB::table('contas_pagar')->where('id', $id)->update($updateData);
            
            // Se tiver OC vinculada, liberar para recebimento
            if (!empty($conta->ordem_compra_id)) {
                try {
                    DB::table('ordens_compra')
                        ->where('id', $conta->ordem_compra_id)
                        ->update([
                            'status_pagamento' => 'pago',
                            'updated_at' => now(),
                        ]);
                    
                    \Log::info("OC #{$conta->ordem_compra_id} liberada para recebimento após pagamento da conta #{$id}");
                    
                    // NOVO: Se for OC de prestador de serviço, atualizar status do prestador para 'pago'
                    if (Schema::hasTable('ordens_servico_prestadores')) {
                        $prestador = DB::table('ordens_servico_prestadores')
                            ->where('ordem_compra_id', $conta->ordem_compra_id)
                            ->first();
                        
                        if ($prestador) {
                            DB::table('ordens_servico_prestadores')
                                ->where('id', $prestador->id)
                                ->update([
                                    'status_pagamento' => 'pago',
                                    'data_pagamento' => now()->format('Y-m-d'),
                                    'updated_at' => now(),
                                ]);
                            \Log::info("Prestador ID {$prestador->id} atualizado para 'pago' após baixa da conta #{$id}");
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Não foi possível atualizar status_pagamento da OC: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Conta baixada com sucesso!' . (!empty($conta->ordem_compra_id) ? ' OC liberada para recebimento.' : '')
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao baixar conta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao baixar conta: ' . $e->getMessage()], 500);
        }
    }
    
    // Listar comprovantes de pagamento de uma conta
    public function listarComprovantes($id)
    {
        try {
            $conta = DB::table('contas_pagar')->where('id', $id)->first();
            
            if (!$conta || empty($conta->comprovante_path)) {
                return response()->json(['success' => true, 'comprovantes' => []]);
            }
            
            // Verificar se é JSON (múltiplos) ou string simples (único)
            $comprovantes = [];
            $decoded = json_decode($conta->comprovante_path, true);
            
            if (is_array($decoded)) {
                // Múltiplos comprovantes em formato JSON
                foreach ($decoded as $index => $path) {
                    $fullPath = storage_path('app/public/' . $path);
                    if (file_exists($fullPath)) {
                        $comprovantes[] = [
                            'index' => $index,
                            'path' => $path,
                            'nome' => basename($path),
                            'url' => "/api/contas-pagar/{$id}/comprovante/{$index}"
                        ];
                    }
                }
            } else {
                // Formato antigo - comprovante único
                $fullPath = storage_path('app/public/' . $conta->comprovante_path);
                if (file_exists($fullPath)) {
                    $comprovantes[] = [
                        'index' => 0,
                        'path' => $conta->comprovante_path,
                        'nome' => basename($conta->comprovante_path),
                        'url' => "/api/contas-pagar/{$id}/comprovante/0"
                    ];
                }
            }
            
            return response()->json(['success' => true, 'comprovantes' => $comprovantes]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // Visualizar comprovante de pagamento (suporta múltiplos via índice)
    public function verComprovante($id, $index = 0)
    {
        try {
            $conta = DB::table('contas_pagar')->where('id', $id)->first();
            
            if (!$conta || empty($conta->comprovante_path)) {
                return response()->json(['success' => false, 'message' => 'Comprovante não encontrado!'], 404);
            }
            
            // Verificar se é JSON (múltiplos) ou string simples (único)
            $comprovantePath = null;
            $decoded = json_decode($conta->comprovante_path, true);
            
            if (is_array($decoded)) {
                // Múltiplos comprovantes - pegar pelo índice
                if (isset($decoded[$index])) {
                    $comprovantePath = $decoded[$index];
                }
            } else {
                // Formato antigo - comprovante único
                $comprovantePath = $conta->comprovante_path;
            }
            
            if (!$comprovantePath) {
                return response()->json(['success' => false, 'message' => 'Comprovante não encontrado!'], 404);
            }
            
            // Caminho completo do arquivo
            $fullPath = storage_path('app/public/' . $comprovantePath);
            
            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Arquivo não encontrado no servidor!'], 404);
            }
            
            // Detectar mime type baseado na extensão
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'txt' => 'text/plain',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            
            // Retornar arquivo para download
            $fileName = basename($comprovantePath);
            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // Helper para obter colunas da tabela
    private function getTableColumns($table)
    {
        try {
            $columns = DB::select("SHOW COLUMNS FROM {$table}");
            return array_map(function($col) { return $col->Field; }, $columns);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    // Helper para calcular próxima data de vencimento
    private function calcularProximaData($dataBase, $tipoRepeticao, $indice)
    {
        $data = $dataBase->copy();
        
        switch ($tipoRepeticao) {
            case 'semanal':
                $data->addWeeks($indice);
                break;
            case 'quinzenal':
                $data->addDays($indice * 15);
                break;
            case 'mensal':
                $data->addMonths($indice);
                break;
            case 'bimestral':
                $data->addMonths($indice * 2);
                break;
            case 'trimestral':
                $data->addMonths($indice * 3);
                break;
            case 'semestral':
                $data->addMonths($indice * 6);
                break;
            case 'anual':
                $data->addYears($indice);
                break;
            default:
                $data->addMonths($indice);
        }
        
        return $data;
    }
    
    // Salvar nova conta a pagar
    public function storeContaPagar(Request $request)
    {
        try {
            // Converter formato brasileiro para decimal
            $valorBruto = $request->valor_bruto;
            if (is_string($valorBruto)) {
                $valorBruto = str_replace('.', '', $valorBruto);
                $valorBruto = str_replace(',', '.', $valorBruto);
            }
            
            $valorLiquido = $request->valor_liquido;
            if (is_string($valorLiquido)) {
                $valorLiquido = str_replace('.', '', $valorLiquido);
                $valorLiquido = str_replace(',', '.', $valorLiquido);
            }
            
            // Verificar colunas existentes na tabela
            $columns = $this->getTableColumns('contas_pagar');
            
            // Dados base (colunas que sempre existem)
            $insertData = [
                'descricao' => $request->descricao,
                'valor' => $valorLiquido ?: $valorBruto,
                'status' => $request->status ?? 'pendente',
                'created_at' => now(),
            ];
            
            // Adicionar campos opcionais se existirem na tabela
            if (in_array('documento', $columns)) {
                $insertData['documento'] = $request->documento;
            }
            if (in_array('fornecedor', $columns)) {
                $insertData['fornecedor'] = $request->fornecedor;
            }
            if (in_array('fornecedor_id', $columns)) {
                $insertData['fornecedor_id'] = $request->fornecedor_id;
            }
            if (in_array('centro_custo_id', $columns)) {
                $insertData['centro_custo_id'] = $request->centro_custo_id;
            }
            if (in_array('categoria_id', $columns)) {
                $insertData['categoria_id'] = $request->categoria_id;
            }
            if (in_array('valor_bruto', $columns)) {
                $insertData['valor_bruto'] = $valorBruto;
            }
            if (in_array('valor_liquido', $columns)) {
                $insertData['valor_liquido'] = $valorLiquido;
            }
            if (in_array('vencimento', $columns)) {
                $insertData['vencimento'] = $request->data_vencimento;
            }
            if (in_array('data_emissao', $columns)) {
                $insertData['data_emissao'] = $request->data_emissao ?? now()->format('Y-m-d');
            }
            if (in_array('data_vencimento', $columns)) {
                $insertData['data_vencimento'] = $request->data_vencimento;
            }
            if (in_array('observacoes', $columns)) {
                $insertData['observacoes'] = $request->observacoes;
            }

            // Se o status já vem como "pago", registrar os dados de baixa imediatamente
            if (($request->status ?? 'pendente') === 'pago') {
                $valorPagoBaixa = $request->baixa_valor_pago ?? $valorLiquido ?? $valorBruto ?? 0;
                if (is_string($valorPagoBaixa)) {
                    $valorPagoBaixa = str_replace('.', '', $valorPagoBaixa);
                    $valorPagoBaixa = str_replace(',', '.', $valorPagoBaixa);
                }
                $valorPagoBaixa = floatval($valorPagoBaixa);
                if ($valorPagoBaixa > 0 && in_array('valor_pago', $columns)) {
                    $insertData['valor_pago'] = $valorPagoBaixa;
                }
                if ($request->filled('baixa_data_pagamento') && in_array('data_pagamento', $columns)) {
                    $insertData['data_pagamento'] = $request->baixa_data_pagamento;
                } elseif (in_array('data_pagamento', $columns)) {
                    $insertData['data_pagamento'] = now()->format('Y-m-d');
                }
                if ($request->filled('baixa_forma_pagamento') && in_array('forma_pagamento', $columns)) {
                    $insertData['forma_pagamento'] = $request->baixa_forma_pagamento;
                }
                if (in_array('baixa_user_id', $columns)) {
                    $insertData['baixa_user_id'] = auth()->id();
                }
                if (in_array('baixa_em', $columns)) {
                    $insertData['baixa_em'] = now();
                }
            }
            
            // Upload de anexo (PDF apenas — campo "anexo")
            if ($request->hasFile('anexo') && in_array('anexo_path', $columns)) {
                $file = $request->file('anexo');
                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['pdf'];
                
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json(['success' => false, 'message' => 'Tipo de arquivo não permitido. Envie apenas PDF.'], 400);
                }
                if ($file->getSize() > 10 * 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Arquivo muito grande. Máximo permitido: 10MB.'], 400);
                }
                
                $fileName = 'anexo_pagar_' . uniqid() . '.' . $extension;
                $uploadDir = storage_path('app/public/anexos_pagar');
                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }
                $file->move($uploadDir, $fileName);
                $insertData['anexo_path'] = 'anexos_pagar/' . $fileName;
            }

            // Upload de comprovante de pagamento (PDF/JPG/PNG — campo "comprovante", usado na baixa imediata)
            if ($request->hasFile('comprovante') && in_array('comprovante_path', $columns)) {
                $file = $request->file('comprovante');
                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json(['success' => false, 'message' => 'Tipo de arquivo não permitido. Envie PDF, JPG ou PNG.'], 400);
                }
                if ($file->getSize() > 5 * 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Arquivo muito grande. Máximo: 5MB.'], 400);
                }

                $fileName = 'comprovante_conta_' . uniqid() . '.' . $extension;
                $uploadDir = storage_path('app/public/comprovantes');
                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }
                $file->move($uploadDir, $fileName);
                $insertData['comprovante_path'] = 'comprovantes/' . $fileName;
            }
            
            // Verificar se é para repetir a conta
            $repetir = $request->has('repetir_conta') && $request->repetir_conta == 'on';
            
            if ($repetir && $request->quantidade_repeticoes > 0) {
                $tipoRepeticao = $request->tipo_repeticao ?? 'mensal';
                $quantidade = min(intval($request->quantidade_repeticoes), 36); // Máximo 36 repetições
                $dataBase = $request->data_vencimento ? \Carbon\Carbon::parse($request->data_vencimento) : now();
                
                $contasCriadas = 0;
                
                for ($i = 0; $i < $quantidade; $i++) {
                    $dataVencimento = $this->calcularProximaData($dataBase, $tipoRepeticao, $i);
                    
                    $insertDataRepetida = $insertData;
                    $insertDataRepetida['descricao'] = $request->descricao . ' (' . ($i + 1) . '/' . $quantidade . ')';
                    
                    if (in_array('vencimento', $columns)) {
                        $insertDataRepetida['vencimento'] = $dataVencimento->format('Y-m-d');
                    }
                    if (in_array('data_vencimento', $columns)) {
                        $insertDataRepetida['data_vencimento'] = $dataVencimento->format('Y-m-d');
                    }
                    
                    DB::table('contas_pagar')->insert($insertDataRepetida);
                    $contasCriadas++;
                }
                
                return response()->json(['success' => true, 'message' => $contasCriadas . ' contas criadas com sucesso!']);
            }
            
            $id = DB::table('contas_pagar')->insertGetId($insertData);
            
            return response()->json(['success' => true, 'id' => $id, 'message' => 'Conta cadastrada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // Atualizar conta a pagar
    public function updateContaPagar(Request $request, $id)
    {
        try {
            $conta = DB::table('contas_pagar')->where('id', $id)->first();
            if (!$conta) {
                return response()->json(['success' => false, 'message' => 'Conta não encontrada!'], 404);
            }
            
            // Converter formato brasileiro para decimal
            $valorBruto = $request->valor_bruto;
            if (is_string($valorBruto)) {
                $valorBruto = str_replace('.', '', $valorBruto);
                $valorBruto = str_replace(',', '.', $valorBruto);
            }
            
            $valorLiquido = $request->valor_liquido;
            if (is_string($valorLiquido)) {
                $valorLiquido = str_replace('.', '', $valorLiquido);
                $valorLiquido = str_replace(',', '.', $valorLiquido);
            }
            
            // Verificar colunas existentes na tabela
            $columns = $this->getTableColumns('contas_pagar');
            
            // Dados base
            $updateData = [
                'descricao' => $request->descricao,
                'valor' => $valorLiquido ?: $valorBruto,
                'status' => $request->status,
                'updated_at' => now(),
            ];
            
            // Adicionar campos opcionais se existirem na tabela
            if (in_array('documento', $columns)) {
                $updateData['documento'] = $request->documento;
            }
            if (in_array('fornecedor', $columns)) {
                $updateData['fornecedor'] = $request->fornecedor;
            }
            if (in_array('centro_custo_id', $columns)) {
                $updateData['centro_custo_id'] = $request->centro_custo_id;
            }
            if (in_array('categoria_id', $columns)) {
                $updateData['categoria_id'] = $request->categoria_id;
            }
            if (in_array('valor_bruto', $columns)) {
                $updateData['valor_bruto'] = $valorBruto;
            }
            if (in_array('valor_liquido', $columns)) {
                $updateData['valor_liquido'] = $valorLiquido;
            }
            if (in_array('vencimento', $columns)) {
                $updateData['vencimento'] = $request->data_vencimento;
            }
            if (in_array('data_emissao', $columns)) {
                $updateData['data_emissao'] = $request->data_emissao;
            }
            if (in_array('data_vencimento', $columns)) {
                $updateData['data_vencimento'] = $request->data_vencimento;
            }
            if (in_array('observacoes', $columns)) {
                $updateData['observacoes'] = $request->observacoes;
            }

            // Se o status está sendo alterado para "pago", registrar dados de baixa
            if ($request->status === 'pago') {
                $valorPagoBaixa = $request->baixa_valor_pago ?? $valorLiquido ?? $valorBruto ?? 0;
                if (is_string($valorPagoBaixa)) {
                    $valorPagoBaixa = str_replace('.', '', $valorPagoBaixa);
                    $valorPagoBaixa = str_replace(',', '.', $valorPagoBaixa);
                }
                $valorPagoBaixa = floatval($valorPagoBaixa);
                if ($valorPagoBaixa > 0 && in_array('valor_pago', $columns)) {
                    $updateData['valor_pago'] = $valorPagoBaixa;
                }
                if ($request->filled('baixa_data_pagamento') && in_array('data_pagamento', $columns)) {
                    $updateData['data_pagamento'] = $request->baixa_data_pagamento;
                } elseif (in_array('data_pagamento', $columns) && empty($conta->data_pagamento)) {
                    $updateData['data_pagamento'] = now()->format('Y-m-d');
                }
                if ($request->filled('baixa_forma_pagamento') && in_array('forma_pagamento', $columns)) {
                    $updateData['forma_pagamento'] = $request->baixa_forma_pagamento;
                }
                if (in_array('baixa_user_id', $columns) && empty($conta->baixa_user_id)) {
                    $updateData['baixa_user_id'] = auth()->id();
                }
                if (in_array('baixa_em', $columns) && empty($conta->baixa_em)) {
                    $updateData['baixa_em'] = now();
                }
            }
            
            // Upload de anexo
            if ($request->hasFile('anexo') && in_array('anexo_path', $columns)) {
                $file = $request->file('anexo');
                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['pdf'];
                
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json(['success' => false, 'message' => 'Tipo de arquivo não permitido. Envie apenas PDF.'], 400);
                }
                if ($file->getSize() > 10 * 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Arquivo muito grande. Máximo permitido: 10MB.'], 400);
                }
                
                // Remover anexo antigo se existir
                if (!empty($conta->anexo_path)) {
                    $oldPath = storage_path('app/public/' . $conta->anexo_path);
                    if (file_exists($oldPath)) { @unlink($oldPath); }
                }
                
                $fileName = 'anexo_pagar_' . uniqid() . '.' . $extension;
                $uploadDir = storage_path('app/public/anexos_pagar');
                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }
                $file->move($uploadDir, $fileName);
                $updateData['anexo_path'] = 'anexos_pagar/' . $fileName;
            }

            // Upload de comprovante de pagamento (campo "comprovante", usado na baixa imediata)
            if ($request->hasFile('comprovante') && in_array('comprovante_path', $columns)) {
                $file = $request->file('comprovante');
                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json(['success' => false, 'message' => 'Tipo de arquivo não permitido. Envie PDF, JPG ou PNG.'], 400);
                }
                if ($file->getSize() > 5 * 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Arquivo muito grande. Máximo: 5MB.'], 400);
                }

                // Remover comprovante antigo se existir
                if (!empty($conta->comprovante_path)) {
                    $oldPath = storage_path('app/public/' . $conta->comprovante_path);
                    if (file_exists($oldPath)) { @unlink($oldPath); }
                }

                $fileName = 'comprovante_conta_' . uniqid() . '.' . $extension;
                $uploadDir = storage_path('app/public/comprovantes');
                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }
                $file->move($uploadDir, $fileName);
                $updateData['comprovante_path'] = 'comprovantes/' . $fileName;
            }
            
            DB::table('contas_pagar')->where('id', $id)->update($updateData);
            
            return response()->json(['success' => true, 'message' => 'Conta atualizada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // Excluir conta a pagar (apenas admin)
    public function deleteContaPagar($id)
    {
        $user = Auth::user();
        
        // Verificar se é administrador
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para excluir Contas a Pagar.'], 403);
        }
        
        try {
            $conta = DB::table('contas_pagar')->where('id', $id)->first();
            if (!$conta) {
                return response()->json(['success' => false, 'message' => 'Conta não encontrada!'], 404);
            }
            
            // Admin pode excluir qualquer conta (incluindo pagas)
            
            // Remover anexo se existir
            if (!empty($conta->anexo_path)) {
                $path = storage_path('app/public/' . $conta->anexo_path);
                if (file_exists($path)) { @unlink($path); }
            }
            if (!empty($conta->comprovante_path)) {
                $path = storage_path('app/public/' . $conta->comprovante_path);
                if (file_exists($path)) { @unlink($path); }
            }
            
            DB::table('contas_pagar')->where('id', $id)->delete();
            
            // Registrar log da exclusão
            $this->registrarLogContaPagar($conta, $user, 'exclusao', 'Conta a Pagar excluída');
            
            return response()->json(['success' => true, 'message' => 'Conta excluída com sucesso!']);
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir Conta a Pagar #' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Listar datas de importação via JSON disponíveis para exclusão em lote
     */
    public function listarDatasImportacaoJson()
    {
        $user = Auth::user();
        
        // Apenas admin pode acessar
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }
        
        try {
            // Buscar datas únicas de importação JSON baseado no padrão da observação
            // Exemplo: "Importado via JSON em 06/02/2026 16:14"
            $datas = DB::table('contas_pagar')
                ->select(DB::raw("
                    SUBSTRING(observacoes, LOCATE('Importado via JSON em ', observacoes) + 21, 16) as data_importacao,
                    COUNT(*) as quantidade,
                    SUM(COALESCE(valor_liquido, valor, 0)) as valor_total
                "))
                ->where('observacoes', 'LIKE', '%Importado via JSON em %')
                ->groupBy('data_importacao')
                ->orderBy('data_importacao', 'desc')
                ->get();
            
            $resultado = [];
            foreach ($datas as $item) {
                if (!empty($item->data_importacao)) {
                    $resultado[] = [
                        'data' => trim($item->data_importacao),
                        'data_formatada' => trim($item->data_importacao),
                        'quantidade' => (int) $item->quantidade,
                        'valor_total' => (float) $item->valor_total,
                        'valor_formatado' => number_format($item->valor_total, 2, ',', '.')
                    ];
                }
            }
            
            return response()->json(['success' => true, 'datas' => $resultado]);
        } catch (\Exception $e) {
            \Log::error('Erro ao listar datas de importação JSON: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Excluir em lote todas as contas importadas via JSON em uma determinada data
     */
    public function excluirLoteImportacaoJson(Request $request)
    {
        $user = Auth::user();
        
        // Apenas admin pode excluir
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Apenas administradores podem executar esta ação.'], 403);
        }
        
        $dataImportacao = $request->input('data_importacao');
        
        if (empty($dataImportacao)) {
            return response()->json(['success' => false, 'message' => 'Data de importação não informada.'], 400);
        }
        
        try {
            // Buscar todas as contas com essa data de importação
            $contas = DB::table('contas_pagar')
                ->where('observacoes', 'LIKE', '%Importado via JSON em ' . $dataImportacao . '%')
                ->get();
            
            if ($contas->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Nenhum registro encontrado para esta data de importação.'], 404);
            }
            
            $quantidade = $contas->count();
            $valorTotal = $contas->sum(function($c) {
                return $c->valor_liquido ?? $c->valor ?? 0;
            });
            
            // Registrar log antes de excluir
            \Log::info("EXCLUSAO_LOTE_JSON: Iniciando exclusão de {$quantidade} registros importados em {$dataImportacao}", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'data_importacao' => $dataImportacao,
                'quantidade' => $quantidade,
                'valor_total' => $valorTotal,
                'ids' => $contas->pluck('id')->toArray(),
                'ip' => request()->ip(),
            ]);
            
            // Excluir anexos e comprovantes
            foreach ($contas as $conta) {
                if (!empty($conta->anexo_path)) {
                    $path = storage_path('app/public/' . $conta->anexo_path);
                    if (file_exists($path)) { @unlink($path); }
                }
                if (!empty($conta->comprovante_path)) {
                    $path = storage_path('app/public/' . $conta->comprovante_path);
                    if (file_exists($path)) { @unlink($path); }
                }
            }
            
            // Excluir registros
            $deletados = DB::table('contas_pagar')
                ->where('observacoes', 'LIKE', '%Importado via JSON em ' . $dataImportacao . '%')
                ->delete();
            
            \Log::info("EXCLUSAO_LOTE_JSON: Concluída exclusão de {$deletados} registros", [
                'user_id' => $user->id,
                'data_importacao' => $dataImportacao,
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Registros excluídos com sucesso!',
                'quantidade' => $deletados,
                'valor_total' => $valorTotal
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir lote de importação JSON: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Registrar log de exclusão de conta a pagar
     */
    private function registrarLogContaPagar($conta, $user, $acao, $descricao)
    {
        $dadosConta = [
            'id' => $conta->id,
            'descricao' => $conta->descricao ?? null,
            'documento' => $conta->documento ?? null,
            'fornecedor' => $conta->fornecedor ?? $conta->fornecedor_nome ?? null,
            'valor_bruto' => $conta->valor_bruto ?? $conta->valor ?? null,
            'valor_liquido' => $conta->valor_liquido ?? $conta->valor ?? null,
            'vencimento' => $conta->data_vencimento ?? $conta->vencimento ?? null,
            'status' => $conta->status ?? null,
        ];
        
        // Log no Laravel
        \Log::info("CONTA_PAGAR_{$acao}: {$descricao}", [
            'conta_id' => $conta->id,
            'user_id' => $user->id ?? null,
            'user_name' => $user->name ?? 'Sistema',
            'dados_conta' => $dadosConta,
            'ip' => request()->ip(),
            'data_hora' => now()->toDateTimeString(),
        ]);
        
        // Log em tabela (se existir)
        try {
            if (Schema::hasTable('logs_contas_pagar')) {
                DB::table('logs_contas_pagar')->insert([
                    'conta_pagar_id' => $conta->id,
                    'user_id' => $user->id ?? null,
                    'user_name' => $user->name ?? 'Sistema',
                    'acao' => $acao,
                    'dados_conta' => json_encode($dadosConta),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Ignora se tabela não existir
        }
    }
    
    // Listar contas a pagar via API
    public function listarContasPagar(Request $request)
    {
        try {
            // Verificar se a coluna categoria_id existe
            $hasCategoria = \Schema::hasColumn('contas_pagar', 'categoria_id');
            
            $query = DB::table('contas_pagar as cp')
                ->leftJoin('fornecedores as f', 'cp.fornecedor_id', '=', 'f.id')
                ->leftJoin('centros_custo as cc', 'cp.centro_custo_id', '=', 'cc.id');
            
            $selectFields = ['cp.*', 'f.razao_social as fornecedor_nome', 'cc.nome as centro_custo_nome'];
            
            if ($hasCategoria) {
                $query->leftJoin('categorias_contas as cat', 'cp.categoria_id', '=', 'cat.id');
                $selectFields[] = 'cat.nome as categoria_nome';
            }
            
            $query->select($selectFields);
            
            // Filtros
            if ($request->filled('status')) {
                if ($request->status === 'a_pagar') {
                    // "A Pagar" mostra todas que não estão pagas ou canceladas (pendente + vencido)
                    $query->whereIn('cp.status', ['pendente', 'vencido']);
                } else {
                    $query->where('cp.status', $request->status);
                }
            }
            if ($request->filled('categoria_id')) {
                $query->where('cp.categoria_id', $request->categoria_id);
            }
            if ($request->filled('fornecedor_id')) {
                $query->where('cp.fornecedor_id', $request->fornecedor_id);
            }
            if ($request->filled('centro_custo_id')) {
                $query->where('cp.centro_custo_id', $request->centro_custo_id);
            }
            
            // Suporte a múltiplos centros de custo
            if ($request->filled('centro_custo_ids')) {
                $ids = array_filter(explode(',', $request->centro_custo_ids));
                if (!empty($ids)) {
                    $query->whereIn('cp.centro_custo_id', $ids);
                }
            }
            if ($request->filled('data_inicio')) {
                $query->where(function($q) use ($request) {
                    $q->where('cp.data_vencimento', '>=', $request->data_inicio)
                      ->orWhere(function($q2) use ($request) {
                          $q2->whereNull('cp.data_vencimento')
                             ->where('cp.vencimento', '>=', $request->data_inicio);
                      });
                });
            }
            if ($request->filled('data_fim')) {
                $query->where(function($q) use ($request) {
                    $q->where('cp.data_vencimento', '<=', $request->data_fim)
                      ->orWhere(function($q2) use ($request) {
                          $q2->whereNull('cp.data_vencimento')
                             ->where('cp.vencimento', '<=', $request->data_fim);
                      });
                });
            }
            
            $contas = $query->orderBy('cp.data_vencimento', 'asc')->get();
            
            return response()->json(['success' => true, 'contas' => $contas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function contasReceber()
    {
        $contas = collect([]);
        $centrosCusto = collect([]);
        $categorias = collect([]);
        $isAdmin = $this->isAdmin(Auth::user());
        
        try {
            // Verificar se a coluna categoria_id existe
            $hasCategoria = \Schema::hasColumn('contas_receber', 'categoria_id');
            
            $query = DB::table('contas_receber as cr')
                ->leftJoin('centros_custo as cc', 'cr.centro_custo_id', '=', 'cc.id');
            
            $selectFields = ['cr.*', 'cc.nome as centro_custo_nome'];
            
            if ($hasCategoria) {
                $query->leftJoin('categorias_contas as cat', 'cr.categoria_id', '=', 'cat.id');
                $selectFields[] = 'cat.nome as categoria_nome';
            }
            
            $contas = $query->select($selectFields)
                ->orderBy('cr.vencimento', 'asc')
                ->get();
        } catch (\Exception $e) {
            // Tabela pode não existir ainda ou não ter a coluna
            try {
                $contas = DB::table('contas_receber')
                    ->orderBy('vencimento', 'asc')
                    ->get();
            } catch (\Exception $e2) {}
        }
        
        try {
            $centrosCusto = DB::table('centros_custo')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();
        } catch (\Exception $e) {}
        
        // Buscar categorias (apenas do tipo 'receber')
        try {
            if (\Schema::hasTable('categorias_contas')) {
                $query = DB::table('categorias_contas')
                    ->where('ativo', 1);
                
                if (\Schema::hasColumn('categorias_contas', 'tipo')) {
                    $query->where('tipo', 'receber');
                }
                
                $categorias = $query->orderBy('nome')->get();
            }
        } catch (\Exception $e) {}
        
        return view('financeiro.contas-receber', compact('contas', 'centrosCusto', 'categorias', 'isAdmin'));
    }
    
    // API para listar contas a receber
    public function listarContasReceber(Request $request)
    {
        try {
            // Verificar se a coluna categoria_id existe
            $hasCategoria = \Schema::hasColumn('contas_receber', 'categoria_id') && \Schema::hasTable('categorias_contas');
            
            $query = DB::table('contas_receber as cr')
                ->leftJoin('centros_custo as cc', 'cr.centro_custo_id', '=', 'cc.id');
            
            $selectFields = ['cr.*', 'cc.nome as centro_custo_nome'];
            
            if ($hasCategoria) {
                $query->leftJoin('categorias_contas as cat', 'cr.categoria_id', '=', 'cat.id');
                $selectFields[] = 'cat.nome as categoria_nome';
                $selectFields[] = 'cat.cor as categoria_cor';
            }
            
            $query->select($selectFields);
            
            // Filtros
            if ($request->filled('status')) {
                $query->where('cr.status', $request->status);
            }
            if ($request->filled('categoria_id')) {
                $query->where('cr.categoria_id', $request->categoria_id);
            }
            if ($request->filled('cliente')) {
                $query->where('cr.cliente', 'like', '%' . $request->cliente . '%');
            }
            if ($request->filled('data_inicio')) {
                $query->where('cr.vencimento', '>=', $request->data_inicio);
            }
            if ($request->filled('data_fim')) {
                $query->where('cr.vencimento', '<=', $request->data_fim);
            }
            
            $contas = $query->orderBy('cr.vencimento', 'asc')->get();
            
            return response()->json(['success' => true, 'contas' => $contas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    // API para buscar conta a receber
    public function getContaReceber($id)
    {
        try {
            $conta = DB::table('contas_receber')->where('id', $id)->first();
            
            if (!$conta) {
                return response()->json(['success' => false, 'message' => 'Conta não encontrada!'], 404);
            }
            
            return response()->json(['success' => true, 'conta' => $conta]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    // Salvar nova conta a receber
    public function storeContaReceber(Request $request)
    {
        try {
            // Converter formato brasileiro para decimal
            $valorBruto = $request->valor_bruto;
            if (is_string($valorBruto)) {
                $valorBruto = str_replace('.', '', $valorBruto);
                $valorBruto = str_replace(',', '.', $valorBruto);
            }
            
            $valorLiquido = $request->valor_liquido;
            if (is_string($valorLiquido)) {
                $valorLiquido = str_replace('.', '', $valorLiquido);
                $valorLiquido = str_replace(',', '.', $valorLiquido);
            }
            
            $dados = [
                'descricao' => $request->descricao,
                'documento' => $request->documento,
                'cliente' => $request->cliente,
                'cliente_id' => $request->cliente_id,
                'centro_custo_id' => $request->centro_custo_id,
                'valor' => $valorLiquido, // Manter compatibilidade
                'valor_bruto' => $valorBruto,
                'valor_liquido' => $valorLiquido,
                'vencimento' => $request->vencimento,
                'data_emissao' => $request->data_emissao ?? now()->format('Y-m-d'),
                'status' => $request->status ?? 'pendente',
                'observacoes' => $request->observacoes,
                'created_at' => now(),
            ];
            
            // Adicionar categoria_id se a coluna existir
            if (\Schema::hasColumn('contas_receber', 'categoria_id') && $request->filled('categoria_id')) {
                $dados['categoria_id'] = $request->categoria_id;
            }
            
            $id = DB::table('contas_receber')->insertGetId($dados);
            
            // Processar upload do anexo PDF
            if ($request->hasFile('anexo')) {
                $anexoPath = $this->processarAnexoContaReceber($request->file('anexo'), $id);
                if ($anexoPath) {
                    DB::table('contas_receber')->where('id', $id)->update(['anexo_path' => $anexoPath]);
                }
            }
            
            return response()->json(['success' => true, 'id' => $id, 'message' => 'Conta cadastrada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // Processar upload de anexo PDF
    private function processarAnexoContaReceber($file, $contaId)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Apenas PDF
            if ($extension !== 'pdf') {
                return null;
            }
            
            // Validar tamanho (máximo 10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                return null;
            }
            
            // Gerar nome único para o arquivo
            $fileName = 'anexo_receber_' . $contaId . '_' . date('YmdHis') . '.pdf';
            
            // Criar diretório se não existir
            $uploadDir = storage_path('app/public/anexos_receber');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Mover arquivo para o diretório
            $file->move($uploadDir, $fileName);
            $fullPath = $uploadDir . '/' . $fileName;
            
            if (file_exists($fullPath)) {
                \Log::info("Anexo PDF salvo para conta a receber #{$contaId}: anexos_receber/{$fileName}");
                return 'anexos_receber/' . $fileName;
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::error('Erro ao processar anexo: ' . $e->getMessage());
            return null;
        }
    }
    
    // Atualizar conta a receber
    public function updateContaReceber(Request $request, $id)
    {
        try {
            $conta = DB::table('contas_receber')->where('id', $id)->first();
            
            if (!$conta) {
                return response()->json(['success' => false, 'message' => 'Conta não encontrada!'], 404);
            }
            
            // Converter formato brasileiro para decimal
            $valorBruto = $request->valor_bruto;
            if (is_string($valorBruto)) {
                $valorBruto = str_replace('.', '', $valorBruto);
                $valorBruto = str_replace(',', '.', $valorBruto);
            }
            
            $valorLiquido = $request->valor_liquido;
            if (is_string($valorLiquido)) {
                $valorLiquido = str_replace('.', '', $valorLiquido);
                $valorLiquido = str_replace(',', '.', $valorLiquido);
            }
            
            $updateData = [
                'descricao' => $request->descricao,
                'documento' => $request->documento,
                'cliente' => $request->cliente,
                'cliente_id' => $request->cliente_id,
                'centro_custo_id' => $request->centro_custo_id,
                'valor' => $valorLiquido, // Manter compatibilidade
                'valor_bruto' => $valorBruto,
                'valor_liquido' => $valorLiquido,
                'vencimento' => $request->vencimento,
                'data_emissao' => $request->data_emissao,
                'status' => $request->status,
                'observacoes' => $request->observacoes,
                'updated_at' => now(),
            ];
            
            // Adicionar categoria_id se a coluna existir
            if (\Schema::hasColumn('contas_receber', 'categoria_id')) {
                $updateData['categoria_id'] = $request->categoria_id;
            }
            
            // Processar upload do anexo PDF (se enviado)
            if ($request->hasFile('anexo')) {
                $anexoPath = $this->processarAnexoContaReceber($request->file('anexo'), $id);
                if ($anexoPath) {
                    // Remover anexo antigo se existir
                    if (!empty($conta->anexo_path)) {
                        $oldPath = storage_path('app/public/' . $conta->anexo_path);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $updateData['anexo_path'] = $anexoPath;
                }
            }
            
            DB::table('contas_receber')->where('id', $id)->update($updateData);
            
            return response()->json(['success' => true, 'message' => 'Conta atualizada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // Baixar conta a receber (marcar como recebido)
    public function baixarContaReceber(Request $request, $id)
    {
        try {
            $conta = DB::table('contas_receber')->where('id', $id)->first();
            
            if (!$conta) {
                return response()->json(['success' => false, 'message' => 'Conta não encontrada!'], 404);
            }
            
            $valorRecebido = $request->valor_recebido ?? $conta->valor;
            // Converter formato brasileiro para decimal
            if (is_string($valorRecebido)) {
                $valorRecebido = str_replace('.', '', $valorRecebido);
                $valorRecebido = str_replace(',', '.', $valorRecebido);
            }
            $valorRecebido = floatval($valorRecebido);
            
            // Calcular valor total da conta
            $valorTotal = floatval($conta->valor_liquido ?? $conta->valor ?? 0);
            
            // Pegar valor já recebido anteriormente
            $valorRecebidoAnterior = floatval($conta->valor_recebido ?? 0);
            
            // Total recebido (anterior + atual)
            $totalRecebido = $valorRecebidoAnterior + $valorRecebido;
            
            // Determinar status: recebido total ou pendente (parcial)
            // Nota: se a coluna status não tiver 'parcial', usar 'pendente' para recebimentos parciais
            $isRecebimentoTotal = $totalRecebido >= ($valorTotal - 0.01); // tolerância de 1 centavo
            $status = $isRecebimentoTotal ? 'recebido' : 'pendente';
            
            $updateData = [
                'status' => $status,
                'data_recebimento' => $request->data_recebimento ?? now()->format('Y-m-d'),
                'valor_recebido' => $totalRecebido,
                'updated_at' => now(),
            ];
            
            // Registrar quem fez a baixa e quando (se colunas existirem)
            if (\Schema::hasColumn('contas_receber', 'baixa_user_id')) {
                $updateData['baixa_user_id'] = auth()->id();
            }
            if (\Schema::hasColumn('contas_receber', 'baixa_em')) {
                $updateData['baixa_em'] = now();
            }
            
            // Processar upload do comprovante
            if ($request->hasFile('comprovante')) {
                $file = $request->file('comprovante');
                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Tipo de arquivo não permitido. Envie PDF, JPEG ou PNG.'
                    ], 400);
                }
                
                if ($file->getSize() > 5 * 1024 * 1024) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Arquivo muito grande. Máximo permitido: 5MB.'
                    ], 400);
                }
                
                $fileName = 'comprovante_receber_' . $id . '_' . date('YmdHis') . '.' . $extension;
                $uploadDir = storage_path('app/public/comprovantes');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $file->move($uploadDir, $fileName);
                $updateData['comprovante_path'] = 'comprovantes/' . $fileName;
            }
            
            DB::table('contas_receber')->where('id', $id)->update($updateData);
            
            return response()->json(['success' => true, 'message' => 'Conta baixada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // Excluir conta a receber (apenas admin)
    public function destroyContaReceber($id)
    {
        $user = Auth::user();
        
        // Verificar se é administrador
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para excluir Contas a Receber.'], 403);
        }
        
        try {
            $conta = DB::table('contas_receber')->where('id', $id)->first();
            
            if (!$conta) {
                return response()->json(['success' => false, 'message' => 'Conta não encontrada!'], 404);
            }
            
            // Admin pode excluir qualquer conta (incluindo recebidas)
            
            // Remover anexo se existir
            if (!empty($conta->anexo_path)) {
                $path = storage_path('app/public/' . $conta->anexo_path);
                if (file_exists($path)) { @unlink($path); }
            }
            if (!empty($conta->comprovante_path)) {
                $path = storage_path('app/public/' . $conta->comprovante_path);
                if (file_exists($path)) { @unlink($path); }
            }
            
            DB::table('contas_receber')->where('id', $id)->delete();
            
            // Registrar log da exclusão
            $this->registrarLogContaReceber($conta, $user, 'exclusao', 'Conta a Receber excluída');
            
            return response()->json(['success' => true, 'message' => 'Conta excluída com sucesso!']);
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir Conta a Receber #' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Registrar log de exclusão de conta a receber
     */
    private function registrarLogContaReceber($conta, $user, $acao, $descricao)
    {
        $dadosConta = [
            'id' => $conta->id,
            'descricao' => $conta->descricao ?? null,
            'documento' => $conta->documento ?? null,
            'cliente' => $conta->cliente ?? null,
            'valor_bruto' => $conta->valor_bruto ?? $conta->valor ?? null,
            'valor_liquido' => $conta->valor_liquido ?? $conta->valor ?? null,
            'vencimento' => $conta->vencimento ?? null,
            'status' => $conta->status ?? null,
        ];
        
        // Log no Laravel
        \Log::info("CONTA_RECEBER_{$acao}: {$descricao}", [
            'conta_id' => $conta->id,
            'user_id' => $user->id ?? null,
            'user_name' => $user->name ?? 'Sistema',
            'dados_conta' => $dadosConta,
            'ip' => request()->ip(),
            'data_hora' => now()->toDateTimeString(),
        ]);
        
        // Log em tabela (se existir)
        try {
            if (Schema::hasTable('logs_contas_receber')) {
                DB::table('logs_contas_receber')->insert([
                    'conta_receber_id' => $conta->id,
                    'user_id' => $user->id ?? null,
                    'user_name' => $user->name ?? 'Sistema',
                    'acao' => $acao,
                    'dados_conta' => json_encode($dadosConta),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Ignora se tabela não existir
        }
    }
    
    // Visualizar anexo PDF da conta a receber
    public function verAnexoReceber($id)
    {
        try {
            $conta = DB::table('contas_receber')->where('id', $id)->first();
            
            if (!$conta || empty($conta->anexo_path)) {
                return response()->json(['success' => false, 'message' => 'Anexo não encontrado!'], 404);
            }
            
            $fullPath = storage_path('app/public/' . $conta->anexo_path);
            
            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Arquivo não encontrado no servidor!'], 404);
            }
            
            $fileName = basename($conta->anexo_path);
            
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // Visualizar comprovante de recebimento
    public function verComprovanteReceber($id)
    {
        try {
            $conta = DB::table('contas_receber')->where('id', $id)->first();
            
            if (!$conta || empty($conta->comprovante_path)) {
                return response()->json(['success' => false, 'message' => 'Comprovante não encontrado!'], 404);
            }
            
            $fullPath = storage_path('app/public/' . $conta->comprovante_path);
            
            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Arquivo não encontrado no servidor!'], 404);
            }
            
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            
            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($conta->comprovante_path) . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function fluxoCaixa()
    {
        return view('financeiro.fluxo-caixa');
    }

    public function centrosCusto()
    {
        $centros = collect([]);
        try {
            $centros = DB::table('centros_custo')
                ->orderBy('nome')
                ->get();
        } catch (\Exception $e) {}
        
        return view('financeiro.centros-custo', compact('centros'));
    }
    
    // API: Listar centros de custo
    public function listarCentrosCusto()
    {
        try {
            $centros = DB::table('centros_custo')
                ->orderBy('nome')
                ->get();
            return response()->json(['success' => true, 'centros' => $centros]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function listarCentrosCustoAutocomplete()
    {
        try {
            $centros = DB::table('centros_custo')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->select('id', 'nome')
                ->get();
            return response()->json(['success' => true, 'centros_custo' => $centros]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    // API: Criar centro de custo
    public function storeCentroCusto(Request $request)
    {
        try {
            $columns = $this->getTableColumns('centros_custo');
            
            $insertData = [
                'nome' => $request->nome,
                'ativo' => $request->status === 'ativo' ? 1 : 0,
            ];
            
            if (in_array('codigo', $columns)) { $insertData['codigo'] = $request->codigo; }
            if (in_array('tipo', $columns)) { $insertData['tipo'] = $request->tipo; }
            if (in_array('responsavel', $columns)) { $insertData['responsavel'] = $request->responsavel; }
            if (in_array('descricao', $columns)) { $insertData['descricao'] = $request->descricao; }
            if (in_array('endereco', $columns)) { $insertData['endereco'] = $request->endereco; }
            if (in_array('created_at', $columns)) { $insertData['created_at'] = now(); }
            
            $id = DB::table('centros_custo')->insertGetId($insertData);
            
            return response()->json(['success' => true, 'id' => $id, 'message' => 'Centro de Custo cadastrado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // API: Atualizar centro de custo
    public function updateCentroCusto(Request $request, $id)
    {
        try {
            $columns = $this->getTableColumns('centros_custo');
            
            $updateData = [
                'nome' => $request->nome,
                'ativo' => $request->status === 'ativo' ? 1 : 0,
            ];
            
            if (in_array('codigo', $columns)) { $updateData['codigo'] = $request->codigo; }
            if (in_array('tipo', $columns)) { $updateData['tipo'] = $request->tipo; }
            if (in_array('responsavel', $columns)) { $updateData['responsavel'] = $request->responsavel; }
            if (in_array('descricao', $columns)) { $updateData['descricao'] = $request->descricao; }
            if (in_array('endereco', $columns)) { $updateData['endereco'] = $request->endereco; }
            if (in_array('updated_at', $columns)) { $updateData['updated_at'] = now(); }
            
            DB::table('centros_custo')
                ->where('id', $id)
                ->update($updateData);
            
            return response()->json(['success' => true, 'message' => 'Centro de Custo atualizado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // API: Excluir centro de custo
    public function deleteCentroCusto($id)
    {
        try {
            DB::table('centros_custo')->where('id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Centro de Custo excluído com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
    
    // API: Obter centro de custo
    public function getCentroCusto($id)
    {
        try {
            $centro = DB::table('centros_custo')->where('id', $id)->first();
            if (!$centro) {
                return response()->json(['success' => false, 'message' => 'Centro de Custo não encontrado'], 404);
            }
            return response()->json(['success' => true, 'centro' => $centro]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bancos()
    {
        return view('financeiro.bancos');
    }
    
    // ============================================
    // CATEGORIAS
    // ============================================
    
    public function categorias()
    {
        $categorias = collect([]);
        $isAdmin = $this->isAdmin(Auth::user());
        $tabelaExiste = false;
        
        try {
            $tabelaExiste = \Schema::hasTable('categorias_contas');
            
            if ($tabelaExiste) {
                $categorias = DB::table('categorias_contas')
                    ->orderBy('tipo')
                    ->orderBy('nome')
                    ->get();
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar categorias: ' . $e->getMessage());
        }
        
        return view('financeiro.categorias', compact('categorias', 'isAdmin', 'tabelaExiste'));
    }
    
    public function listarCategorias(Request $request)
    {
        try {
            if (!\Schema::hasTable('categorias_contas')) {
                return response()->json(['success' => true, 'categorias' => []]);
            }
            
            $query = DB::table('categorias_contas');
            
            // Filtrar por tipo se informado
            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }
            
            // Filtrar apenas ativas se solicitado
            if ($request->filled('ativo')) {
                $query->where('ativo', $request->ativo);
            }
            
            $categorias = $query->orderBy('nome')->get();
            
            return response()->json(['success' => true, 'categorias' => $categorias]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function getCategoria($id)
    {
        try {
            $categoria = DB::table('categorias_contas')->where('id', $id)->first();
            
            if (!$categoria) {
                return response()->json(['success' => false, 'message' => 'Categoria não encontrada'], 404);
            }
            
            return response()->json(['success' => true, 'categoria' => $categoria]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function storeCategoria(Request $request)
    {
        try {
            if (!\Schema::hasTable('categorias_contas')) {
                return response()->json(['success' => false, 'message' => 'Tabela de categorias não existe. Execute o SQL para criar.'], 400);
            }
            
            $data = [
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'cor' => $request->cor ?? '#007bff',
                'ativo' => $request->ativo ?? 1,
                'created_at' => now(),
            ];
            
            // Adicionar tipo se a coluna existir
            if (\Schema::hasColumn('categorias_contas', 'tipo')) {
                $data['tipo'] = $request->tipo ?? 'pagar';
            }
            
            $id = DB::table('categorias_contas')->insertGetId($data);
            
            return response()->json(['success' => true, 'id' => $id, 'message' => 'Categoria criada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao criar: ' . $e->getMessage()], 500);
        }
    }
    
    public function updateCategoria(Request $request, $id)
    {
        try {
            $categoria = DB::table('categorias_contas')->where('id', $id)->first();
            
            if (!$categoria) {
                return response()->json(['success' => false, 'message' => 'Categoria não encontrada'], 404);
            }
            
            $data = [
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'cor' => $request->cor ?? $categoria->cor,
                'ativo' => $request->ativo ?? $categoria->ativo,
                'updated_at' => now(),
            ];
            
            // Atualizar tipo se a coluna existir
            if (\Schema::hasColumn('categorias_contas', 'tipo') && $request->has('tipo')) {
                $data['tipo'] = $request->tipo;
            }
            
            DB::table('categorias_contas')->where('id', $id)->update($data);
            
            return response()->json(['success' => true, 'message' => 'Categoria atualizada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()], 500);
        }
    }
    
    public function deleteCategoria($id)
    {
        try {
            // Verificar se é admin
            if (!$this->isAdmin(Auth::user())) {
                return response()->json(['success' => false, 'message' => 'Apenas administradores podem excluir.'], 403);
            }
            
            $categoria = DB::table('categorias_contas')->where('id', $id)->first();
            
            if (!$categoria) {
                return response()->json(['success' => false, 'message' => 'Categoria não encontrada'], 404);
            }
            
            // Verificar se está sendo usada em contas a pagar
            $emUso = false;
            if (\Schema::hasColumn('contas_pagar', 'categoria_id')) {
                $emUso = DB::table('contas_pagar')->where('categoria_id', $id)->exists();
            }
            
            if ($emUso) {
                return response()->json(['success' => false, 'message' => 'Esta categoria está sendo usada em contas a pagar. Não é possível excluir.'], 400);
            }
            
            DB::table('categorias_contas')->where('id', $id)->delete();
            
            return response()->json(['success' => true, 'message' => 'Categoria excluída com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()], 500);
        }
    }
}
