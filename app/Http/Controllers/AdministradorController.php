<?php

namespace App\Http\Controllers;

use App\Models\AdministradorSistema;
use App\Models\User;
use Illuminate\Http\Request;

class AdministradorController extends Controller
{
    public function index()
    {
        $administradores = AdministradorSistema::with('user')
            ->withCount('taxas')
            ->orderBy('nome')
            ->paginate(20);

        $totalPago     = AdministradorSistema::ativo()->get()->sum->total_pago;
        $totalPendente = AdministradorSistema::ativo()->get()->sum->total_pendente;

        return view('obras.administradores.index', compact('administradores', 'totalPago', 'totalPendente'));
    }

    public function create()
    {
        $users = User::where('active', true)->orderBy('name')->get();
        return view('obras.administradores.create', compact('users'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome'            => 'required|string|max:120',
            'cpf'             => 'nullable|string|max:14',
            'email'           => 'nullable|email|max:120',
            'telefone'        => 'nullable|string|max:20',
            'cargo'           => 'nullable|string|max:80',
            'percentual_taxa' => 'required|numeric|min:0|max:100',
            'user_id'         => 'nullable|exists:users,id',
            'observacoes'     => 'nullable|string',
        ]);

        $dados['created_by'] = auth()->id();

        AdministradorSistema::create($dados);

        return redirect()->route('obras.administradores.index')
            ->with('success', "Administrador \"{$dados['nome']}\" cadastrado com sucesso.");
    }

    public function show(AdministradorSistema $administrador)
    {
        $administrador->load(['taxas.obra', 'user']);

        $taxasPendentes = $administrador->taxas()
            ->where('status', 'pendente')
            ->with('obra')
            ->orderBy('data_referencia', 'desc')
            ->get();

        $taxasPagas = $administrador->taxas()
            ->where('status', 'pago')
            ->with('obra')
            ->orderBy('data_pagamento', 'desc')
            ->get();

        return view('obras.administradores.show', compact('administrador', 'taxasPendentes', 'taxasPagas'));
    }

    public function edit(AdministradorSistema $administrador)
    {
        $users = User::where('active', true)->orderBy('name')->get();
        return view('obras.administradores.edit', compact('administrador', 'users'));
    }

    public function update(Request $request, AdministradorSistema $administrador)
    {
        $dados = $request->validate([
            'nome'            => 'required|string|max:120',
            'cpf'             => 'nullable|string|max:14',
            'email'           => 'nullable|email|max:120',
            'telefone'        => 'nullable|string|max:20',
            'cargo'           => 'nullable|string|max:80',
            'percentual_taxa' => 'required|numeric|min:0|max:100',
            'user_id'         => 'nullable|exists:users,id',
            'ativo'           => 'boolean',
            'observacoes'     => 'nullable|string',
        ]);

        $administrador->update($dados);

        return redirect()->route('obras.administradores.index')
            ->with('success', "Administrador \"{$administrador->nome}\" atualizado.");
    }

    public function destroy(AdministradorSistema $administrador)
    {
        $administrador->delete();
        return redirect()->route('obras.administradores.index')
            ->with('success', "Administrador removido.");
    }

    /** API: lista para select2 / autocomplete */
    public function apiListar(Request $request)
    {
        $q = $request->input('q', '');
        $admins = AdministradorSistema::where('ativo', true)
            ->when($q, fn ($query) => $query->where('nome', 'like', "%{$q}%"))
            ->orderBy('nome')
            ->get(['id', 'nome', 'percentual_taxa']);

        return response()->json($admins);
    }
}
