<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\LancamentoObra;
use App\Models\CategoriaMaterial;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GastosController extends Controller
{
    public function index(Request $request)
    {
        $query = LancamentoObra::with(['obra', 'categoria', 'subcategoria', 'fase.faseCatalogo'])
            ->orderBy('data_lancamento', 'desc');

        if ($request->filled('obra_id')) {
            $query->where('obra_id', $request->obra_id);
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('status')) {
            $query->where('status_pagamento', $request->status);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('data_de')) {
            $query->whereDate('data_lancamento', '>=', $request->data_de);
        }

        if ($request->filled('data_ate')) {
            $query->whereDate('data_lancamento', '<=', $request->data_ate);
        }

        $lancamentos = $query->paginate(25)->withQueryString();

        // Totais do filtro atual (sem paginar)
        $queryTotais = LancamentoObra::query();
        if ($request->filled('obra_id'))      $queryTotais->where('obra_id', $request->obra_id);
        if ($request->filled('categoria_id')) $queryTotais->where('categoria_id', $request->categoria_id);
        if ($request->filled('status'))       $queryTotais->where('status_pagamento', $request->status);
        if ($request->filled('tipo'))         $queryTotais->where('tipo', $request->tipo);
        if ($request->filled('data_de'))      $queryTotais->whereDate('data_lancamento', '>=', $request->data_de);
        if ($request->filled('data_ate'))     $queryTotais->whereDate('data_lancamento', '<=', $request->data_ate);

        $totalReal     = (clone $queryTotais)->sum('custo_total_real');
        $totalPago     = (clone $queryTotais)->where('status_pagamento', 'pago')->sum('custo_total_real');
        $totalPendente = (clone $queryTotais)->where('status_pagamento', 'pendente')->sum('custo_total_real');

        // Porcentagem por categoria (top 5)
        $porCategoria = LancamentoObra::select('categoria_id', DB::raw('SUM(custo_total_real) as total'))
            ->with('categoria')
            ->groupBy('categoria_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $obras      = Obra::orderBy('nome')->get(['id', 'nome']);
        $categorias = CategoriaMaterial::ativas()->get(['id', 'nome']);

        return view('gastos.index', compact(
            'lancamentos', 'totalReal', 'totalPago', 'totalPendente',
            'porCategoria', 'obras', 'categorias'
        ));
    }

    public function create(Request $request)
    {
        $obras       = Obra::whereIn('status', ['em_andamento', 'pendente'])->orderBy('nome')->get();
        $categorias  = CategoriaMaterial::ativas()->with('subcategorias')->orderBy('ordem')->orderBy('nome')->get();
        $fornecedores = Fornecedor::ativos()->orderBy('razao_social')->get(['id','razao_social','nome_fantasia']);
        $obraSel     = $request->filled('obra_id') ? Obra::find($request->obra_id) : null;

        return view('gastos.create', compact('obras', 'categorias', 'fornecedores', 'obraSel'));
    }

    public function store(Request $request)
    {
        $modo = $request->input('modo_lancamento', 'por_unidade');
        $modoValorDireto = in_array($modo, ['salario', 'empreitada', 'valor_total']);

        $rules = [
            'obra_id'          => 'required|exists:obras,id',
            'categoria_id'     => 'required|exists:categorias_material,id',
            'tipo'             => 'required|in:material,servico,mao_de_obra,equipamento,terceiro',
            'descricao'        => 'required|string|max:255',
            'data_lancamento'  => 'required|date',
        ];
        if ($modoValorDireto) {
            $rules['valor_total_direto'] = 'required|numeric|min:0';
        } else {
            $rules['quantidade']           = 'required|numeric|min:0.001';
            $rules['custo_unitario_real']  = 'required|numeric|min:0';
        }
        $request->validate($rules);

        $obra      = Obra::findOrFail($request->obra_id);
        $faseAtiva = $obra->faseAtiva;

        if (!$faseAtiva) {
            return back()->withInput()
                ->with('error', __('A obra selecionada não possui fase ativa. Verifique o cronograma.'));
        }

        if ($modoValorDireto) {
            $quantidade         = 1;
            $unidade            = match($modo) { 'salario' => 'mês', 'empreitada' => 'vb', default => 'vb' };
            $custoUnitOrcado    = $request->valor_total_orcado ?: null;
            $custoUnitReal      = $request->valor_total_direto;
        } elseif ($modo === 'por_hora') {
            $quantidade         = $request->quantidade;
            $unidade            = 'h';
            $custoUnitOrcado    = $request->custo_unitario_orcado ?: null;
            $custoUnitReal      = $request->custo_unitario_real;
        } else {
            $quantidade         = $request->quantidade;
            $unidade            = $request->unidade;
            $custoUnitOrcado    = $request->custo_unitario_orcado ?: null;
            $custoUnitReal      = $request->custo_unitario_real;
        }

        $nomeFornecedor = $request->fornecedor;
        if ($request->filled('fornecedor_id')) {
            $forn = Fornecedor::find($request->fornecedor_id);
            if ($forn) $nomeFornecedor = $forn->nome_exibicao;
        }

        LancamentoObra::create([
            'obra_id'                  => $obra->id,
            'obra_fase_id'             => $faseAtiva->id,
            'fornecedor_id'            => $request->fornecedor_id ?: null,
            'categoria_id'             => $request->categoria_id,
            'subcategoria_id'          => $request->subcategoria_id ?: null,
            'tipo'                     => $request->tipo,
            'modo_lancamento'          => $modo,
            'descricao'                => $request->descricao,
            'produto_codigo'           => $request->produto_codigo,
            'fornecedor'               => $nomeFornecedor,
            'nota_fiscal'              => $request->nota_fiscal,
            'quantidade'               => $quantidade,
            'unidade'                  => $unidade,
            'custo_unitario_orcado'    => $custoUnitOrcado,
            'custo_unitario_real'      => $custoUnitReal,
            'data_lancamento'          => $request->data_lancamento,
            'data_prevista_pagamento'  => $request->data_prevista_pagamento,
            'status_pagamento'         => 'pendente',
            'excluir_base_taxa_admin'  => $request->boolean('excluir_base_taxa_admin'),
            'observacoes'              => $request->observacoes,
            'created_by'               => Auth::id(),
        ]);

        $msg = __('Custo registrado na fase ":fase" da obra ":obra".', ['fase' => $faseAtiva->nome, 'obra' => $obra->nome]);

        if ($request->input('action') === 'save_new') {
            return redirect()->route('gastos.create', ['obra_id' => $obra->id])->with('success', $msg);
        }

        return redirect()->route('gastos.index')->with('success', $msg);
    }

    public function fluxoCaixa(Request $request)
    {
        // Gastos mensais dos últimos 12 meses
        $meses = LancamentoObra::selectRaw("
                DATE_FORMAT(data_lancamento, '%Y-%m') as mes,
                SUM(custo_total_real) as total,
                SUM(CASE WHEN status_pagamento = 'pago' THEN custo_total_real ELSE 0 END) as pago,
                SUM(CASE WHEN status_pagamento = 'pendente' THEN custo_total_real ELSE 0 END) as pendente
            ")
            ->where('data_lancamento', '>=', now()->subMonths(11)->startOfMonth())
            ->groupByRaw("DATE_FORMAT(data_lancamento, '%Y-%m')")
            ->orderBy('mes')
            ->get();

        // Gastos por obra
        $porObra = LancamentoObra::select('obra_id', DB::raw('SUM(custo_total_real) as total'))
            ->with('obra:id,nome')
            ->groupBy('obra_id')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        // Gastos por categoria
        $porCategoria = LancamentoObra::select('categoria_id', DB::raw('SUM(custo_total_real) as total'))
            ->with('categoria:id,nome')
            ->groupBy('categoria_id')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        $totalGeral = LancamentoObra::sum('custo_total_real');
        $totalPago  = LancamentoObra::where('status_pagamento', 'pago')->sum('custo_total_real');
        $totalPend  = LancamentoObra::where('status_pagamento', 'pendente')->sum('custo_total_real');

        return view('gastos.fluxo-caixa', compact(
            'meses', 'porObra', 'porCategoria', 'totalGeral', 'totalPago', 'totalPend'
        ));
    }
}
