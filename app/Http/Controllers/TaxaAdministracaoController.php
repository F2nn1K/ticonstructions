<?php

namespace App\Http\Controllers;

use App\Models\AdministradorSistema;
use App\Models\LancamentoObra;
use App\Models\Obra;
use App\Models\TaxaAdministracao;
use Illuminate\Http\Request;

class TaxaAdministracaoController extends Controller
{
    /** Listagem geral das taxas (todas as obras) */
    public function index(Request $request)
    {
        $taxas = TaxaAdministracao::with(['obra', 'administrador'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->administrador_id, fn ($q) => $q->where('administrador_id', $request->administrador_id))
            ->when($request->obra_id, fn ($q) => $q->where('obra_id', $request->obra_id))
            ->orderBy('status')
            ->orderBy('data_referencia', 'desc')
            ->paginate(30);

        $administradores = AdministradorSistema::ativo()->get();
        $obras           = Obra::orderBy('nome')->get();

        // Totais de resumo
        $totalPendente = TaxaAdministracao::where('status', 'pendente')->sum('valor_taxa');
        $totalPago     = TaxaAdministracao::where('status', 'pago')->sum('valor_pago');

        return view('obras.taxa-administracao.index', compact(
            'taxas', 'administradores', 'obras', 'totalPendente', 'totalPago'
        ));
    }

    /** Formulário de geração de nova taxa */
    public function create()
    {
        $obras           = Obra::whereIn('status', ['planejamento', 'em_andamento'])->orderBy('nome')->get();
        $administradores = AdministradorSistema::ativo()->get();

        return view('obras.taxa-administracao.create', compact('obras', 'administradores'));
    }

    /**
     * API: calcula preview da taxa antes de gravar.
     * Retorna custo_base e valor_taxa para exibir ao usuário.
     */
    public function calcularPreview(Request $request)
    {
        $request->validate([
            'obra_id'          => 'required|exists:obras,id',
            'administrador_id' => 'required|exists:administradores_sistema,id',
            'percentual'       => 'nullable|numeric|min:0|max:100',
        ]);

        $admin      = AdministradorSistema::findOrFail($request->administrador_id);
        $percentual = (float) ($request->percentual ?? $admin->percentual_taxa);

        $custo_base = TaxaAdministracao::calcularBaseObra((int) $request->obra_id);
        $valor_taxa = round($custo_base * $percentual / 100, 2);

        return response()->json([
            'custo_base' => $custo_base,
            'percentual' => $percentual,
            'valor_taxa' => $valor_taxa,
        ]);
    }

    /** Grava a taxa calculada como conta a pagar */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'obra_id'          => 'required|exists:obras,id',
            'administrador_id' => 'required|exists:administradores_sistema,id',
            'data_referencia'  => 'required|date',
            'descricao'        => 'nullable|string|max:200',
            'percentual'       => 'required|numeric|min:0|max:100',
            'data_vencimento'  => 'nullable|date',
            'observacoes'      => 'nullable|string',
        ]);

        $custo_base = TaxaAdministracao::calcularBaseObra((int) $dados['obra_id']);
        $valor_taxa = round($custo_base * (float) $dados['percentual'] / 100, 2);

        TaxaAdministracao::create(array_merge($dados, [
            'custo_base_obra' => $custo_base,
            'valor_taxa'      => $valor_taxa,
            'status'          => 'pendente',
            'created_by'      => auth()->id(),
        ]));

        return redirect()->route('obras.taxa-administracao.index')
            ->with('success', 'Taxa de administração gerada com sucesso.');
    }

    /** Marcar como pago */
    public function pagar(Request $request, TaxaAdministracao $taxa)
    {
        $dados = $request->validate([
            'data_pagamento'   => 'required|date',
            'valor_pago'       => 'required|numeric|min:0',
            'forma_pagamento'  => 'nullable|string|max:80',
            'observacoes'      => 'nullable|string',
        ]);

        $taxa->update(array_merge($dados, [
            'status'   => 'pago',
            'pago_por' => auth()->id(),
        ]));

        return redirect()->back()
            ->with('success', "Taxa de R\$ " . number_format($taxa->valor_taxa, 2, ',', '.') . " marcada como paga.");
    }

    /** Cancelar taxa */
    public function cancelar(TaxaAdministracao $taxa)
    {
        $taxa->update(['status' => 'cancelado']);
        return redirect()->back()->with('success', 'Taxa cancelada.');
    }

    /** Excluir taxa */
    public function destroy(TaxaAdministracao $taxa)
    {
        $taxa->delete();
        return redirect()->route('obras.taxa-administracao.index')
            ->with('success', 'Taxa removida.');
    }
}
