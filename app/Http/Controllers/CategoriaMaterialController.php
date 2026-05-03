<?php

namespace App\Http\Controllers;

use App\Models\CategoriaMaterial;
use App\Models\SubcategoriaMaterial;
use Illuminate\Http\Request;

class CategoriaMaterialController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function index()
    {
        $categorias = CategoriaMaterial::with('subcategorias')->orderBy('ordem')->orderBy('nome')->get();
        return view('categorias.index', compact('categorias'));
    }

    // API: criar categoria inline (chamada via AJAX no formulário de lançamento)
    public function storeApi(Request $request)
    {
        $data = $request->validate(['nome'=>'required|string|max:120','tipo'=>'nullable|in:material,servico,ambos']);
        $cat  = CategoriaMaterial::create(['nome'=>$data['nome'],'tipo'=>$data['tipo']??'ambos','ativo'=>true,'ordem'=>99]);
        return response()->json(['id'=>$cat->id,'nome'=>$cat->nome]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nome'=>'required|string|max:120','icone'=>'nullable|string|max:50','tipo'=>'nullable','ordem'=>'nullable|integer']);
        CategoriaMaterial::create($data + ['ativo'=>true]);
        return back()->with('success', __('Categoria criada!'));
    }

    public function update(Request $request, CategoriaMaterial $categoriaMaterial)
    {
        $request->validate(['nome'=>'required|string|max:120']);
        $categoriaMaterial->update($request->only(['nome','icone','tipo','ordem','ativo']));
        return back()->with('success', __('Categoria atualizada!'));
    }

    public function destroy(CategoriaMaterial $categoriaMaterial)
    {
        $categoriaMaterial->delete();
        return back()->with('success', __('Categoria removida.'));
    }

    // API: criar subcategoria inline
    public function storeSubApi(Request $request)
    {
        $data = $request->validate([
            'categoria_id' => 'required|exists:categorias_material,id',
            'nome'         => 'required|string|max:120',
            'unidade'      => 'nullable|string|max:20',
        ]);
        $sub = SubcategoriaMaterial::create(['categoria_id'=>$data['categoria_id'],'nome'=>$data['nome'],'unidade'=>$data['unidade']??null,'ativo'=>true]);
        return response()->json(['id'=>$sub->id,'nome'=>$sub->nome,'unidade'=>$sub->unidade]);
    }

    public function storeSub(Request $request)
    {
        $data = $request->validate(['categoria_id'=>'required|exists:categorias_material,id','nome'=>'required|string|max:120','unidade'=>'nullable|string|max:20']);
        SubcategoriaMaterial::create($data + ['ativo'=>true]);
        return back()->with('success', __('Subcategoria criada!'));
    }

    public function destroySub(SubcategoriaMaterial $subcategoriaMaterial)
    {
        $subcategoriaMaterial->delete();
        return back()->with('success', __('Subcategoria removida.'));
    }
}
