<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuncionarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Página principal de Funcionários
     */
    public function index()
    {
        return view('funcionarios');
    }

    /**
     * Listar funcionários com filtros
     */
    public function listar(Request $request)
    {
        $query = DB::table('funcionarios');

        // Filtro por nome
        if ($request->nome) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        // Filtro por CPF
        if ($request->cpf) {
            $query->where('cpf', 'like', '%' . preg_replace('/\D/', '', $request->cpf) . '%');
        }

        // Filtro por status
        if ($request->status && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        // Filtro por função
        if ($request->funcao) {
            $query->where('funcao', 'like', '%' . $request->funcao . '%');
        }

        $funcionarios = $query->orderBy('nome', 'asc')->get();

        return response()->json([
            'success' => true,
            'funcionarios' => $funcionarios
        ]);
    }

    /**
     * Criar funcionário
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:funcionarios,cpf',
        ]);

        $id = DB::table('funcionarios')->insertGetId([
            'nome' => $request->nome,
            'cpf' => preg_replace('/\D/', '', $request->cpf),
            'sexo' => $request->sexo,
            'funcao' => $request->funcao,
            'status' => $request->status ?? 'trabalhando',
            'observacoes' => $request->observacoes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Funcionário cadastrado com sucesso!',
            'id' => $id
        ]);
    }

    /**
     * Buscar funcionário por ID
     */
    public function show($id)
    {
        $funcionario = DB::table('funcionarios')->where('id', $id)->first();

        if (!$funcionario) {
            return response()->json(['error' => 'Funcionário não encontrado'], 404);
        }

        return response()->json($funcionario);
    }

    /**
     * Atualizar funcionário
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11',
        ]);

        // Verificar se CPF já existe em outro registro
        $existe = DB::table('funcionarios')
            ->where('cpf', preg_replace('/\D/', '', $request->cpf))
            ->where('id', '!=', $id)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'CPF já cadastrado para outro funcionário'
            ], 422);
        }

        DB::table('funcionarios')
            ->where('id', $id)
            ->update([
                'nome' => $request->nome,
                'cpf' => preg_replace('/\D/', '', $request->cpf),
                'sexo' => $request->sexo,
                'funcao' => $request->funcao,
                'status' => $request->status ?? 'trabalhando',
                'observacoes' => $request->observacoes,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Funcionário atualizado com sucesso!'
        ]);
    }

    /**
     * Excluir funcionário
     */
    public function destroy($id)
    {
        // Verificar se funcionário está vinculado a alguma O.S.
        $vinculado = DB::table('ordens_servico')
            ->where('funcionario_id', $id)
            ->exists();

        if ($vinculado) {
            return response()->json([
                'success' => false,
                'message' => 'Funcionário está vinculado a uma ou mais O.S. e não pode ser excluído.'
            ], 400);
        }

        DB::table('funcionarios')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Funcionário excluído com sucesso!'
        ]);
    }
}
