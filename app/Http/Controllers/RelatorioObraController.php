<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\ObraFase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatorioObraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function curvaSFisica(Request $request)
    {
        $obraId = $request->get('obra_id');
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);
        $obra   = $obraId ? Obra::find($obraId) : null;
        $fases  = $obra ? ObraFase::where('obra_id', $obraId)->orderBy('ordem')->get() : collect();

        $labels    = $fases->pluck('nome_personalizado')->toArray();
        $planejado = array_fill(0, $fases->count(), 100);
        $realizado = $fases->pluck('percentual_realizado')->map(fn($v) => round($v ?? 0, 1))->toArray();

        $totalFases    = $fases->count();
        $concluidas    = $fases->filter(fn($f) => ($f->percentual_realizado ?? 0) >= 100)->count();
        $progressoMedio = $fases->count() ? round($fases->avg('percentual_realizado'), 1) : 0;

        return view('relatorios.curva-s-fisica', compact(
            'obras','obra','fases','labels','planejado','realizado',
            'totalFases','concluidas','progressoMedio','obraId'
        ));
    }

    public function curvaSFinanceira(Request $request)
    {
        $obraId = $request->get('obra_id');
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);
        $obra   = $obraId ? Obra::find($obraId) : null;

        $gastos = $obraId
            ? DB::table('gastos')
                ->selectRaw('DATE_FORMAT(data_gasto, "%Y-%m") as mes, SUM(valor) as total')
                ->where('obra_id', $obraId)
                ->groupBy('mes')->orderBy('mes')->get()
            : collect();

        $labels  = $gastos->pluck('mes')->map(fn($m) => Carbon::parse($m.'-01')->translatedFormat('M/Y'))->toArray();
        $valores = $gastos->pluck('total')->map(fn($v) => round($v,2))->toArray();

        $acumulado = []; $soma = 0;
        foreach ($valores as $v) { $soma += $v; $acumulado[] = round($soma,2); }

        $orcamento  = $obra ? ($obra->valor_contrato ?? 0) : 0;
        $totalGasto = array_sum($valores);
        $saldo      = $orcamento - $totalGasto;
        $percGasto  = $orcamento > 0 ? round(($totalGasto/$orcamento)*100,1) : 0;

        return view('relatorios.curva-s-financeira', compact(
            'obras','obra','labels','valores','acumulado',
            'orcamento','totalGasto','saldo','percGasto','obraId'
        ));
    }

    public function cronograma(Request $request)
    {
        $obraId = $request->get('obra_id');
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);
        $obra   = $obraId ? Obra::find($obraId) : null;
        $fases  = $obraId ? ObraFase::where('obra_id', $obraId)->orderBy('ordem')->get() : collect();

        $atrasadas   = $fases->filter(fn($f) => $f->data_fim_previsto && now()->gt($f->data_fim_previsto) && ($f->percentual_realizado??0)<100)->count();
        $concluidas  = $fases->filter(fn($f) => ($f->percentual_realizado??0)>=100)->count();
        $emAndamento = $fases->filter(fn($f) => ($f->percentual_realizado??0)>0 && ($f->percentual_realizado??0)<100)->count();
        $naoIniciadas= $fases->filter(fn($f) => ($f->percentual_realizado??0)==0)->count();

        return view('relatorios.cronograma', compact(
            'obras','obra','fases','atrasadas','concluidas','emAndamento','naoIniciadas','obraId'
        ));
    }

    public function custos(Request $request)
    {
        $obraId = $request->get('obra_id');
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        $porObra = DB::table('gastos as g')
            ->join('obras as o','o.id','=','g.obra_id')
            ->selectRaw('o.nome as obra_nome, o.id as obra_id, SUM(g.valor) as total, COUNT(*) as qtd')
            ->when($obraId, fn($q) => $q->where('g.obra_id', $obraId))
            ->groupBy('g.obra_id','o.nome','o.id')->orderByDesc('total')->get();

        $categorias = DB::table('gastos')
            ->selectRaw('categoria, SUM(valor) as total')
            ->when($obraId, fn($q) => $q->where('obra_id', $obraId))
            ->groupBy('categoria')->orderByDesc('total')->get();

        $totalGeral       = $porObra->sum('total');
        $totalLancamentos = $porObra->sum('qtd');
        $labels = $categorias->pluck('categoria')->toArray();
        $valores= $categorias->pluck('total')->map(fn($v)=>round($v,2))->toArray();

        return view('relatorios.custos', compact(
            'obras','porObra','categorias','totalGeral','totalLancamentos',
            'labels','valores','obraId'
        ));
    }

    public function maoDeObra(Request $request)
    {
        $obraId = $request->get('obra_id');
        $mes    = $request->get('mes', now()->format('Y-m'));
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        $apontamentos = DB::table('apontamentos as a')
            ->join('funcionarios as f','f.id','=','a.funcionario_id')
            ->join('obras as o','o.id','=','a.obra_id')
            ->selectRaw('f.nome as funcionario, o.nome as obra, SUM(a.horas_trabalhadas) as total_horas, COUNT(*) as dias, a.obra_id')
            ->when($obraId, fn($q)=>$q->where('a.obra_id',$obraId))
            ->when($mes, fn($q)=>$q->whereRaw('DATE_FORMAT(a.data,"%Y-%m")=?',[$mes]))
            ->where('a.status','aprovado')
            ->groupBy('a.funcionario_id','f.nome','a.obra_id','o.nome')
            ->orderBy('f.nome')->get();

        $totalHoras       = $apontamentos->sum('total_horas');
        $totalDias        = $apontamentos->sum('dias');
        $totalFuncionarios= $apontamentos->pluck('funcionario')->unique()->count();

        return view('relatorios.mao-de-obra', compact(
            'obras','apontamentos','totalHoras','totalDias',
            'totalFuncionarios','obraId','mes'
        ));
    }

    public function suprimentos(Request $request)
    {
        $obraId = $request->get('obra_id');
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        $itens = DB::table('gastos as g')
            ->join('obras as o','o.id','=','g.obra_id')
            ->select('g.*','o.nome as obra_nome')
            ->whereIn(DB::raw('LOWER(g.categoria)'),['material','suprimento','equipamento'])
            ->when($obraId, fn($q)=>$q->where('g.obra_id',$obraId))
            ->orderByDesc('g.data_gasto')->limit(200)->get();

        $totalValor = $itens->sum('valor');
        $totalItens = $itens->count();

        return view('relatorios.suprimentos', compact(
            'obras','itens','totalValor','totalItens','obraId'
        ));
    }

    public function producao(Request $request)
    {
        $obraId = $request->get('obra_id');
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        $medicoes = DB::table('medicoes as m')
            ->join('obras as o','o.id','=','m.obra_id')
            ->leftJoin('obra_fases as f','f.id','=','m.obra_fase_id')
            ->select('m.*','o.nome as obra_nome','f.nome_personalizado as fase_nome')
            ->when($obraId, fn($q)=>$q->where('m.obra_id',$obraId))
            ->where('m.status','aprovado')
            ->orderByDesc('m.data_medicao')->get();

        $totalMedicoes  = $medicoes->count();
        $totalValor     = $medicoes->sum('valor_medicao');
        $percMedioGeral = $medicoes->count() ? round($medicoes->avg('percentual_medido'),1) : 0;

        return view('relatorios.producao', compact(
            'obras','medicoes','totalMedicoes','totalValor','percMedioGeral','obraId'
        ));
    }

    public function riscos(Request $request)
    {
        $obraId = $request->get('obra_id');
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        $riscos = DB::table('riscos as r')
            ->join('obras as o','o.id','=','r.obra_id')
            ->select('r.*','o.nome as obra_nome')
            ->when($obraId, fn($q)=>$q->where('r.obra_id',$obraId))
            ->orderByDesc(DB::raw('(r.probabilidade * r.impacto)'))->get();

        $totalRiscos = $riscos->count();
        $criticos    = $riscos->filter(fn($r)=>($r->probabilidade*$r->impacto)>=12)->count();
        $altos       = $riscos->filter(fn($r)=>($r->probabilidade*$r->impacto)>=6 && ($r->probabilidade*$r->impacto)<12)->count();
        $abertos     = $riscos->filter(fn($r)=>$r->status==='aberto')->count();

        $porCategoria = $riscos->groupBy('categoria')->map->count();
        $catLabels    = $porCategoria->keys()->toArray();
        $catData      = $porCategoria->values()->toArray();

        return view('relatorios.riscos', compact(
            'obras','riscos','totalRiscos','criticos','altos','abertos',
            'catLabels','catData','obraId'
        ));
    }

    public function qualidade(Request $request)
    {
        $obraId = $request->get('obra_id');
        $obras  = Obra::orderBy('nome')->get(['id', 'nome', 'codigo']);

        $inspecoes = DB::table('qualidade_inspecoes as qi')
            ->join('obras as o','o.id','=','qi.obra_id')
            ->select('qi.*','o.nome as obra_nome')
            ->when($obraId, fn($q)=>$q->where('qi.obra_id',$obraId))
            ->orderByDesc('qi.data_inspecao')->get();

        $ncs = DB::table('qualidade_nao_conformidades as nc')
            ->join('obras as o','o.id','=','nc.obra_id')
            ->select('nc.*','o.nome as obra_nome')
            ->when($obraId, fn($q)=>$q->where('nc.obra_id',$obraId))
            ->orderByDesc('nc.created_at')->get();

        $totalInspecoes      = $inspecoes->count();
        $inspecoesConcluidas = $inspecoes->filter(fn($i)=>$i->status==='concluida')->count();
        $totalNCs   = $ncs->count();
        $ncsAbertas = $ncs->filter(fn($n)=>$n->status==='aberta')->count();
        $ncsCriticas= $ncs->filter(fn($n)=>$n->gravidade==='critica')->count();

        $ncPorGravidade = $ncs->groupBy('gravidade')->map->count();
        $ncLabels = $ncPorGravidade->keys()->map(fn($k)=>ucfirst($k))->toArray();
        $ncData   = $ncPorGravidade->values()->toArray();

        return view('relatorios.qualidade', compact(
            'obras','inspecoes','ncs','totalInspecoes','inspecoesConcluidas',
            'totalNCs','ncsAbertas','ncsCriticas','ncLabels','ncData','obraId'
        ));
    }
}
