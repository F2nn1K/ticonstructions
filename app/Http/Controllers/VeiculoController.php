<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VeiculoController extends Controller
{
    public function index()
    {
        return view('frota.veiculos.index');
    }

    public function json(Request $request)
    {
        $q = Veiculo::orderBy('placa');
        if ($request->boolean('exclude_maintenance')) {
            $q->where('status', '!=', 'manutencao');
        }
        // Excluir inativos quando solicitado (para selects de uso)
        if ($request->boolean('only_usable')) {
            $q->where('status', '!=', 'inativo');
        }
        
        $veiculos = $q->get();
        
        // Buscar veículos em uso (com viagem ativa)
        $veiculosEmUsoIds = DB::table('viagens')
            ->select('vehicle_id')
            ->where(function($query) {
                $query->whereNull('km_retorno')->orWhereNull('data_retorno');
            })
            ->groupBy('vehicle_id')
            ->pluck('vehicle_id')
            ->toArray();
        
        // Adicionar status dinâmico baseado em viagens
        $veiculos->transform(function ($veiculo) use ($veiculosEmUsoIds) {
            if (in_array($veiculo->id, $veiculosEmUsoIds)) {
                $veiculo->status_uso = 'em_uso';
            } else {
                $veiculo->status_uso = 'livre';
            }
            return $veiculo;
        });
        
        return $veiculos;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'placa' => ['required','max:10', Rule::unique('veiculos','placa')],
            'renavam' => ['nullable','max:20'],
            'marca' => ['nullable','max:100'],
            'modelo' => ['nullable','max:100'],
            'ano' => ['nullable','integer'],
            'tipo' => ['required', Rule::in(['carro','moto','caminhao','van'])],
            'status' => ['nullable', Rule::in(['ativo','inativo','manutencao'])],
            'km_atual' => ['nullable','integer','min:0'],
        ]);

        $veiculo = Veiculo::create($validated);
        return response()->json(['ok' => true, 'id' => $veiculo->id]);
    }

    public function update(Request $request, int $id)
    {
        $veiculo = Veiculo::findOrFail($id);
        $validated = $request->validate([
            'placa' => ['required','max:10', Rule::unique('veiculos','placa')->ignore($veiculo->id)],
            'renavam' => ['nullable','max:20'],
            'marca' => ['nullable','max:100'],
            'modelo' => ['nullable','max:100'],
            'ano' => ['nullable','integer'],
            'tipo' => ['required', Rule::in(['carro','moto','caminhao','van'])],
            'status' => ['nullable', Rule::in(['ativo','inativo','manutencao'])],
            'km_atual' => ['nullable','integer','min:0'],
        ]);

        $veiculo->update($validated);
        return response()->json(['ok' => true]);
    }

    public function destroy(int $id)
    {
        Veiculo::findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Retorna os dados do veículo em JSON (para visualização em modal)
     */
    public function showJson(int $id)
    {
        $veiculo = Veiculo::findOrFail($id);
        return response()->json($veiculo);
    }

    public function show(int $id)
    {
        $veiculo = Veiculo::findOrFail($id);
        return view('frota.veiculos.show', compact('veiculo'));
    }

    // =============================
    // Licenciamento (tela e APIs)
    // =============================
    public function licenciamento()
    {
        return view('frota.licenciamento');
    }

    public function licenciamentoVeiculos(Request $request)
    {
        $q = DB::table('veiculos')
            ->select('id','placa','marca','modelo','ano')
            ->orderBy('placa');

        if ($request->filled('search')) {
            $s = trim($request->query('search'));
            $q->where(function($qq) use ($s){
                $qq->where('placa','like',"%$s%")
                   ->orWhere('marca','like',"%$s%")
                   ->orWhere('modelo','like',"%$s%");
            });
        }

        return response()->json(['success' => true, 'data' => $q->limit(500)->get()]);
    }

    public function licenciamentoStatus(int $veiculo)
    {
        try {
            $veiculoId = $veiculo;
            $veiculo = DB::table('veiculos')->where('id',$veiculoId)->first();
            if (!$veiculo) {
                return response()->json(['success' => false, 'message' => 'Veículo não encontrado'], 404);
            }

            // Importante: não selecionar o BLOB do comprovante para evitar erro de JSON UTF-8
            $ultimo = DB::table('veiculo_licenciamentos')
                ->select('id','veiculo_id','ano_exercicio','data_pagamento','valor','observacoes','comprovante_mime','comprovante_nome','comprovante_tamanho','created_at','updated_at')
                ->where('veiculo_id', $veiculoId)
                ->orderByDesc('ano_exercicio')
                ->orderByDesc('data_pagamento')
                ->first();

            $anoAtual = (int) date('Y');
            $pagoEsteAno = false;
            $proximoPagamento = null;

            if ($ultimo) {
                if (!empty($ultimo->ano_exercicio) && (int)$ultimo->ano_exercicio === $anoAtual) {
                    $pagoEsteAno = true;
                } elseif (!empty($ultimo->data_pagamento) && (int)date('Y', strtotime($ultimo->data_pagamento)) === $anoAtual) {
                    $pagoEsteAno = true;
                }

                if (!empty($ultimo->data_pagamento)) {
                    $proximoPagamento = date('Y-m-d', strtotime('+1 year', strtotime($ultimo->data_pagamento)));
                } else {
                    $anoBase = max((int)($ultimo->ano_exercicio ?: $anoAtual), $anoAtual);
                    $proximoPagamento = $anoBase . '-12-31';
                }
            } else {
                $proximoPagamento = $anoAtual . '-12-31';
            }

            return response()->json([
                'success' => true,
                'veiculo' => $veiculo,
                'ultimo' => $ultimo,
                'pago_este_ano' => $pagoEsteAno,
                'proximo_pagamento' => $proximoPagamento,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Licenciamento status error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Erro ao consultar status', 'error' => $e->getMessage()], 500);
        }
    }

    public function licenciamentoStore(Request $request)
    {
        $request->validate([
            'veiculo_id'     => ['required','integer'],
            'ano_exercicio'  => ['required','integer','digits:4'],
            'data_pagamento' => ['nullable','date'],
            'valor'          => ['nullable','string'],
            'observacoes'    => ['nullable','string'],
            'comprovante'    => ['nullable','file','mimes:pdf,jpg,jpeg,png,webp','max:8192'],
        ]);

        // Converter valor PT-BR para decimal com ponto
        $valor = null;
        if ($request->filled('valor')) {
            $valorStr = (string) $request->input('valor');
            $valorStr = str_replace(['R$',' '], '', $valorStr);
            $valorStr = str_replace('.', '', $valorStr);
            $valorStr = str_replace(',', '.', $valorStr);
            $valor = is_numeric($valorStr) ? (float) $valorStr : null;
        }

        // Preparar BLOB do comprovante
        $blob = null; $mime = null; $nome = null; $tamanho = null;
        if ($request->hasFile('comprovante')) {
            $file = $request->file('comprovante');
            $blob = file_get_contents($file->getRealPath());
            $mime = $file->getMimeType();
            $nome = $file->getClientOriginalName();
            $tamanho = $file->getSize();
        }

        DB::table('veiculo_licenciamentos')->insert([
            'veiculo_id'          => (int) $request->input('veiculo_id'),
            'ano_exercicio'       => (int) $request->input('ano_exercicio'),
            'data_pagamento'      => $request->input('data_pagamento') ?: null,
            'valor'               => $valor,
            'observacoes'         => $request->input('observacoes') ?: null,
            'comprovante'         => $blob,
            'comprovante_mime'    => $mime,
            'comprovante_nome'    => $nome,
            'comprovante_tamanho' => $tamanho,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return redirect()->route('frota.licenciamento')
            ->with('success', 'Licenciamento registrado com sucesso.');
    }
}


