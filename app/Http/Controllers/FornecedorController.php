<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\LancamentoObra;
use App\Models\CategoriaMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FornecedorController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function index(Request $request)
    {
        $query = Fornecedor::withCount('lancamentos')
            ->withSum('lancamentos', 'custo_total_real')
            ->orderBy('razao_social');

        if ($request->filled('busca')) {
            $b = $request->busca;
            $query->where(fn($q) => $q
                ->where('razao_social','like',"%$b%")
                ->orWhere('nome_fantasia','like',"%$b%")
                ->orWhere('cnpj','like',"%$b%")
            );
        }
        if ($request->filled('uf')) $query->where('uf', $request->uf);

        $fornecedores = $query->paginate(20)->withQueryString();
        $total = Fornecedor::count();
        $ufs   = Fornecedor::select('uf')->whereNotNull('uf')->distinct()->orderBy('uf')->pluck('uf');
        return view('fornecedores.index', compact('fornecedores','total','ufs'));
    }

    public function create()
    {
        return view('fornecedores.form', ['fornecedor' => new Fornecedor()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'razao_social'  => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj'          => 'nullable|string|max:20',
            'telefone'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'endereco'      => 'nullable|string|max:255',
            'cidade'        => 'nullable|string|max:100',
            'uf'            => 'nullable|string|max:2',
            'observacoes'   => 'nullable|string',
        ]);
        $data['ativo'] = true;
        $f = Fornecedor::create($data);

        if ($request->expectsJson()) return response()->json(['id'=>$f->id,'nome'=>$f->nome_exibicao]);
        return redirect()->route('fornecedores.index')->with('success', __('Fornecedor cadastrado com sucesso!'));
    }

    public function edit(Fornecedor $fornecedor)
    {
        return view('fornecedores.form', compact('fornecedor'));
    }

    public function update(Request $request, Fornecedor $fornecedor)
    {
        $data = $request->validate([
            'razao_social'  => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj'          => 'nullable|string|max:20',
            'telefone'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'endereco'      => 'nullable|string|max:255',
            'cidade'        => 'nullable|string|max:100',
            'uf'            => 'nullable|string|max:2',
            'observacoes'   => 'nullable|string',
            'ativo'         => 'boolean',
        ]);
        $fornecedor->update($data);
        return redirect()->route('fornecedores.index')->with('success', __('Fornecedor atualizado!'));
    }

    public function destroy(Fornecedor $fornecedor)
    {
        $fornecedor->delete();
        return back()->with('success', __('Fornecedor removido.'));
    }

    public function show(Fornecedor $fornecedor)
    {
        $lancamentos = LancamentoObra::where('fornecedor_id', $fornecedor->id)
            ->with(['obra','categoria','subcategoria'])
            ->orderBy('data_lancamento','desc')
            ->paginate(20);

        $totalCompras    = $lancamentos->sum('custo_total_real');
        $porCategoria    = LancamentoObra::where('fornecedor_id', $fornecedor->id)
            ->select('categoria_id', DB::raw('SUM(custo_total_real) as total'), DB::raw('COUNT(*) as qtd'))
            ->with('categoria')->groupBy('categoria_id')->get();

        return view('fornecedores.show', compact('fornecedor','lancamentos','totalCompras','porCategoria'));
    }

    // Relatório: comparação de preços por produto/subcategoria entre fornecedores
    public function relatorioComparacao(Request $request)
    {
        $categorias = CategoriaMaterial::ativas()->with('subcategorias')->get();

        $query = DB::table('lancamentos_obra as l')
            ->join('fornecedores as f', 'f.id', '=', 'l.fornecedor_id')
            ->leftJoin('subcategorias_material as s', 's.id', '=', 'l.subcategoria_id')
            ->leftJoin('categorias_material as c', 'c.id', '=', 'l.categoria_id')
            ->whereNotNull('l.fornecedor_id')
            ->whereNull('l.deleted_at')
            ->select([
                'l.subcategoria_id',
                's.nome as subcategoria',
                'c.nome as categoria',
                'l.descricao',
                'l.unidade',
                'f.id as fornecedor_id',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as fornecedor"),
                DB::raw('MIN(l.custo_unitario_real) as preco_min'),
                DB::raw('MAX(l.custo_unitario_real) as preco_max'),
                DB::raw('AVG(l.custo_unitario_real) as preco_medio'),
                DB::raw('COUNT(*) as qtd_compras'),
                DB::raw('MAX(l.data_lancamento) as ultima_compra'),
            ])
            ->groupBy('l.subcategoria_id','s.nome','c.nome','l.descricao','l.unidade','f.id','fornecedor');

        if ($request->filled('subcategoria_id')) {
            $query->where('l.subcategoria_id', $request->subcategoria_id);
        }
        if ($request->filled('categoria_id')) {
            $query->where('l.categoria_id', $request->categoria_id);
        }
        if ($request->filled('descricao')) {
            $query->where('l.descricao','like','%'.$request->descricao.'%');
        }

        $linhas = $query->orderBy('l.subcategoria_id')->orderBy('preco_min')->get();

        // Agrupar por subcategoria+descricao para poder destacar o mais barato
        $agrupado = $linhas->groupBy(fn($r) => ($r->subcategoria_id ?? '0').'|'.$r->descricao);

        return view('fornecedores.relatorio-comparacao', compact('agrupado','categorias'));
    }

    // API: buscar fornecedores para autocomplete/select2
    public function apiLista(Request $request)
    {
        $termo = $request->get('q','');
        $forn  = Fornecedor::ativos()
            ->where(fn($q) => $q
                ->where('razao_social','like',"%$termo%")
                ->orWhere('nome_fantasia','like',"%$termo%")
            )
            ->limit(20)->get(['id','razao_social','nome_fantasia','cnpj']);
        return response()->json($forn->map(fn($f) => ['id'=>$f->id,'text'=>$f->nome_exibicao,'cnpj'=>$f->cnpj]));
    }
}
