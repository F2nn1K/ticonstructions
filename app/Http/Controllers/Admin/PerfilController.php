<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    public function index()
    {
        $perfis = Profile::all();
        return view('admin.gerenciar-perfis', compact('perfis'));
    }

    public function show($id)
    {
        // Buscar perfil diretamente no banco
        $perfil = DB::table('profiles')->where('id', $id)->first();
        
        // Buscar permissões do perfil diretamente da tabela de relacionamento
        $permissoes = DB::table('profile_permissions')
            ->join('permissions', 'profile_permissions.permission_id', '=', 'permissions.id')
            ->where('profile_permissions.profile_id', $id)
            ->select('permissions.*')
            ->get();
            
        return response()->json([
            'success' => true, 
            'data' => [
                'perfil' => $perfil,
                'permissions' => $permissoes
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:profiles,name',
            'description' => 'nullable|string'
        ]);

        $perfil = Profile::create([
            'name' => $request->name,
            'description' => $request->description
        ]);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Perfil criado com sucesso',
                'data' => $perfil
            ]);
        }
        return redirect('/perfis')->with('success', 'Perfil criado com sucesso');
    }

    public function update(Request $request, $id)
    {
        $perfil = Profile::findOrFail($id);
        
        $request->validate([
            'name' => 'required|unique:profiles,name,' . $id,
            'description' => 'nullable|string',
            'permissions' => 'array'
        ]);

        $perfil->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        if ($request->has('permissions')) {
            // Limpar permissões existentes
            DB::table('profile_permissions')->where('profile_id', $id)->delete();
            
            // Inserir novas permissões
            foreach ($request->permissions as $permissionId) {
                DB::table('profile_permissions')->insert([
                    'profile_id' => $id,
                    'permission_id' => $permissionId
                ]);
            }
        }

        // Buscar permissões atualizadas
        $permissoes = DB::table('profile_permissions')
            ->join('permissions', 'profile_permissions.permission_id', '=', 'permissions.id')
            ->where('profile_permissions.profile_id', $id)
            ->select('permissions.*')
            ->get();
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => [
                    'perfil' => $perfil,
                    'permissions' => $permissoes
                ]
            ]);
        }
        return redirect('/perfis')->with('success', 'Perfil atualizado com sucesso');
    }

    public function destroy($id)
    {
        // Remover relacionamentos primeiro
        DB::table('profile_permissions')->where('profile_id', $id)->delete();
        
        // Remover perfil
        DB::table('profiles')->where('id', $id)->delete();
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Perfil excluído com sucesso'
            ]);
        }
        return redirect('/perfis')->with('success', 'Perfil excluído com sucesso');
    }

    public function getPermissoes()
    {
        $permissoes = DB::table('permissions')->get();
        
        return response()->json([
            'success' => true,
            'data' => $permissoes
        ]);
    }
} 