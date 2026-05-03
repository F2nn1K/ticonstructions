<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\ObraFase;
use App\Models\OcorrenciaFaseObra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObraFaseController extends Controller
{
    /**
     * Avança a fase atual para a próxima.
     */
    public function avancar(Request $request, Obra $obra, ObraFase $fase)
    {
        if ($fase->obra_id !== $obra->id) {
            abort(403);
        }

        $resultado = $fase->avancar(Auth::id());

        if ($request->expectsJson()) {
            return response()->json($resultado);
        }

        $tipo = $resultado['sucesso'] ? 'success' : 'error';
        return redirect()->route('obras.show', $obra)
            ->with($tipo, $resultado['mensagem']);
    }

    /**
     * Atualiza percentual realizado de uma fase.
     */
    public function atualizarProgresso(Request $request, Obra $obra, ObraFase $fase)
    {
        $request->validate([
            'percentual_realizado' => 'required|integer|min:0|max:100',
        ]);

        $fase->update([
            'percentual_realizado' => $request->percentual_realizado,
        ]);

        return response()->json(['sucesso' => true, 'percentual' => $fase->percentual_realizado]);
    }

    /**
     * Registrar ocorrência em uma fase.
     */
    public function registrarOcorrencia(Request $request, Obra $obra, ObraFase $fase)
    {
        $request->validate([
            'tipo'            => 'required|in:chuva,falta_material,falta_mao_de_obra,erro_projeto,problema_equipamento,acidente,outro',
            'titulo'          => 'required|string|max:255',
            'descricao'       => 'required|string',
            'data_ocorrencia' => 'required|date',
            'impacto_dias'    => 'required|integer|min:0',
            'acao_tomada'     => 'nullable|string',
        ]);

        OcorrenciaFaseObra::create([
            'obra_id'         => $obra->id,
            'obra_fase_id'    => $fase->id,
            'tipo'            => $request->tipo,
            'titulo'          => $request->titulo,
            'descricao'       => $request->descricao,
            'data_ocorrencia' => $request->data_ocorrencia,
            'impacto_dias'    => $request->impacto_dias,
            'acao_tomada'     => $request->acao_tomada,
            'registrado_por'  => Auth::id(),
        ]);

        return redirect()->route('obras.show', $obra)
            ->with('success', 'Ocorrência registrada com sucesso!');
    }
}
