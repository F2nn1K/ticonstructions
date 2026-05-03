<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\LancamentoObra;
use App\Models\CatalogoItemGasto;
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

    /** Catálogo da obra primeiro; senão último lançamento com texto igual ao hint. */
    public function hintDescricao(Request $request)
    {
        $desc = trim((string) $request->query('descricao', ''));
        if (mb_strlen($desc) < 2) {
            return response()->json(null);
        }

        $normCatalogo = CatalogoItemGasto::normalizeDescricao($desc);
        $obraId       = $request->filled('obra_id') ? (int) $request->query('obra_id') : null;

        if ($obraId) {
            $catalogo = CatalogoItemGasto::where('obra_id', $obraId)
                ->where('descricao_normalizada', $normCatalogo)
                ->first();

            if ($catalogo) {
                return response()->json([
                    'source'               => 'catalogo',
                    'categoria_id'         => $catalogo->categoria_id,
                    'subcategoria_id'      => $catalogo->subcategoria_id,
                    'tipo'                 => $catalogo->tipo,
                    'unidade'              => $catalogo->unidade,
                    'quantidade_padrao'    => $catalogo->quantidade_padrao,
                ]);
            }
        }

        $normLanc = mb_strtolower($desc);
        $query    = LancamentoObra::query()
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(TRIM(descricao)) = ?', [$normLanc]);

        if ($obraId) {
            $query->where('obra_id', $obraId);
        }

        $last = $query->orderByDesc('created_at')->first([
            'categoria_id', 'subcategoria_id', 'tipo', 'unidade',
        ]);

        return $last ? response()->json(array_merge($last->toArray(), ['source' => 'lancamento'])) : response()->json(null);
    }

    /** Autocomplete JSON: lista itens salvos por obra para a descrição digitada. */
    public function catalogoBuscar(Request $request)
    {
        $obraId = (int) $request->query('obra_id', 0);
        $term   = CatalogoItemGasto::normalizeDescricao((string) $request->query('q', ''));
        if ($obraId < 1 || mb_strlen($term) < 2) {
            return response()->json([]);
        }

        // norm já em $term; LIKE seguro contra % e _
        $likeWildcard = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
        $likePrefix   = $term . '%';

        $lista = CatalogoItemGasto::where('obra_id', $obraId)
            ->where(function ($q) use ($likePrefix, $likeWildcard) {
                $q->where('descricao_normalizada', 'LIKE', $likePrefix)
                    ->orWhere('descricao_normalizada', 'LIKE', $likeWildcard);
            })
            ->orderByRaw(
                '(CASE WHEN descricao_normalizada = ? THEN 0 WHEN descricao_normalizada LIKE ? THEN 1 ELSE 2 END), CHAR_LENGTH(descricao_normalizada), descricao ASC',
                [$term, $term.'%']
            )
            ->limit(20)
            ->get([
                'id',
                'descricao',
                'categoria_id',
                'subcategoria_id',
                'tipo',
                'unidade',
                'quantidade_padrao',
            ]);

        return response()->json($lista);
    }

    /** Cria ou atualiza registro fixo por obra + descrição normalizada (insumo/memória). */
    public function catalogoUpsert(Request $request)
    {
        $validated = $request->validate([
            'obra_id'               => 'required|exists:obras,id',
            'descricao'             => 'required|string|min:2|max:255',
            'categoria_id'          => 'required|exists:categorias_material,id',
            'subcategoria_id'       => 'nullable|exists:subcategorias_material,id',
            'tipo'                  => 'required|in:material,servico,mao_de_obra,equipamento,terceiro',
            'unidade'               => 'nullable|string|max:32',
            'quantidade_padrao'     => 'nullable|numeric|min:0',
        ]);

        $item = $this->persistCatalogoItem(
            (int) $validated['obra_id'],
            $validated['descricao'],
            [
                'categoria_id'         => (int) $validated['categoria_id'],
                'subcategoria_id'      => isset($validated['subcategoria_id']) && $validated['subcategoria_id'] !== ''
                    ? (int) $validated['subcategoria_id'] : null,
                'tipo'                 => $validated['tipo'],
                'unidade'              => $validated['unidade'] ?? null,
                'quantidade_padrao'    => isset($validated['quantidade_padrao']) ? $validated['quantidade_padrao'] : null,
            ]
        );

        return response()->json($item->only([
            'id',
            'descricao',
            'categoria_id',
            'subcategoria_id',
            'tipo',
            'unidade',
            'quantidade_padrao',
        ]));
    }

    /** Usado pelo store() após criar o lançamento. */
    protected function persistCatalogoFromGastoRequest(
        int $obraId,
        Request $request,
        bool $modoValorDireto,
        mixed $quantidade,
        ?string $unidade,
    ): ?CatalogoItemGasto {
        if (mb_strlen(CatalogoItemGasto::normalizeDescricao((string) $request->descricao)) < 2 || !$request->categoria_id) {
            return null;
        }

        $qPadrao = (!$modoValorDireto && $quantidade !== null && $quantidade !== '')
            ? round((float) $quantidade, 3)
            : null;

        return $this->persistCatalogoItem((int) $obraId, (string) $request->descricao, [
            'categoria_id'         => (int) $request->categoria_id,
            'subcategoria_id'      => $request->filled('subcategoria_id') ? (int) $request->subcategoria_id : null,
            'tipo'                 => (string) $request->tipo,
            'unidade'              => $unidade ?: null,
            'quantidade_padrao'    => $qPadrao,
        ]);
    }

    protected function persistCatalogoItem(int $obraId, string $descricao, array $payload): CatalogoItemGasto
    {
        $norm = CatalogoItemGasto::normalizeDescricao($descricao);
        /** @var CatalogoItemGasto $row */
        $row = CatalogoItemGasto::firstOrNew([
            'obra_id'                 => $obraId,
            'descricao_normalizada'    => $norm,
        ]);

        if (!$row->exists) {
            $row->created_by = Auth::id();
        }
        $row->updated_by           = Auth::id();
        $row->descricao             = mb_substr(trim(preg_replace('/\s+/u', ' ', $descricao)), 0, 255);
        $row->categoria_id          = $payload['categoria_id'];
        $row->subcategoria_id       = $payload['subcategoria_id'] ?? null;
        $row->tipo                  = $payload['tipo'];
        $row->unidade               = $payload['unidade'] ?? null;
        $row->quantidade_padrao = $payload['quantidade_padrao'] ?? null;

        $row->save();

        return $row->fresh();
    }

    public function store(Request $request)
    {
        // Validar campos comuns
        $request->validate([
            'obra_id'          => 'required|exists:obras,id',
            'data_lancamento'  => 'required|date',
            'itens_json'       => 'required|string',
        ]);

        // Decodificar itens
        $itensJson = $request->input('itens_json', '[]');
        $itens = json_decode($itensJson, true);

        if (!is_array($itens) || count($itens) === 0) {
            return back()->withInput()->with('error', __('Adicione pelo menos um item ao lançamento.'));
        }

        $obra = Obra::findOrFail($request->obra_id);
        $faseAtiva = $obra->faseAtiva;

        if (!$faseAtiva) {
            return back()->withInput()
                ->with('error', __('A obra selecionada não possui fase ativa. Verifique o cronograma.'));
        }

        // Dados comuns do lançamento
        $nomeFornecedor = $request->fornecedor;
        if ($request->filled('fornecedor_id')) {
            $forn = Fornecedor::find($request->fornecedor_id);
            if ($forn) $nomeFornecedor = $forn->nome_exibicao;
        }

        $qtdCriados = 0;

        DB::beginTransaction();
        try {
            foreach ($itens as $item) {
                $modo = $item['modo_lancamento'] ?? 'por_unidade';
                $modoValorDireto = in_array($modo, ['salario', 'empreitada', 'valor_total']);

                // Validar item
                if (empty($item['descricao']) || empty($item['categoria_id']) || empty($item['tipo'])) {
                    continue; // Pular item inválido
                }

                if ($modoValorDireto) {
                    $quantidade      = 1;
                    $unidade         = match($modo) { 'salario' => 'mês', 'empreitada' => 'vb', default => 'vb' };
                    $custoUnitOrcado = $item['valor_total_orcado'] ?? null;
                    $custoUnitReal   = $item['valor_total_direto'] ?? 0;
                } elseif ($modo === 'por_hora') {
                    $quantidade      = $item['quantidade'] ?? 1;
                    $unidade         = 'h';
                    $custoUnitOrcado = null;
                    $custoUnitReal   = $item['custo_unitario_real'] ?? 0;
                } else {
                    $quantidade      = $item['quantidade'] ?? 1;
                    $unidade         = $item['unidade'] ?? 'un';
                    $custoUnitOrcado = null;
                    $custoUnitReal   = $item['custo_unitario_real'] ?? 0;
                }

                LancamentoObra::create([
                    'obra_id'                  => $obra->id,
                    'obra_fase_id'             => $faseAtiva->id,
                    'fornecedor_id'            => $request->fornecedor_id ?: null,
                    'categoria_id'             => $item['categoria_id'],
                    'subcategoria_id'          => $item['subcategoria_id'] ?: null,
                    'tipo'                     => $item['tipo'],
                    'modo_lancamento'          => $modo,
                    'descricao'                => $item['descricao'],
                    'produto_codigo'           => null,
                    'fornecedor'               => $nomeFornecedor,
                    'nota_fiscal'              => $request->nota_fiscal,
                    'quantidade'               => $quantidade,
                    'unidade'                  => $unidade,
                    'custo_unitario_orcado'    => $custoUnitOrcado,
                    'custo_unitario_real'      => $custoUnitReal,
                    'data_lancamento'          => $request->data_lancamento,
                    'data_prevista_pagamento'  => $request->data_prevista_pagamento,
                    'status_pagamento'         => 'pendente',
                    'excluir_base_taxa_admin'  => $item['excluir_taxa_adm'] ?? false,
                    'observacoes'              => $request->observacoes,
                    'created_by'               => Auth::id(),
                ]);

                // Persistir no catálogo
                $this->persistCatalogoItem((int) $obra->id, $item['descricao'], [
                    'categoria_id'      => (int) $item['categoria_id'],
                    'subcategoria_id'   => !empty($item['subcategoria_id']) ? (int) $item['subcategoria_id'] : null,
                    'tipo'              => $item['tipo'],
                    'unidade'           => $unidade ?: null,
                    'quantidade_padrao' => !$modoValorDireto ? round((float) $quantidade, 3) : null,
                ]);

                $qtdCriados++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', __('Erro ao salvar lançamentos: ') . $e->getMessage());
        }

        $msg = __(':qtd item(ns) registrado(s) na fase ":fase" da obra ":obra".', [
            'qtd'  => $qtdCriados,
            'fase' => $faseAtiva->nome,
            'obra' => $obra->nome
        ]);

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
