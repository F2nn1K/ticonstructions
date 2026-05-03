<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Obra;
use App\Models\LancamentoObra;
use App\Models\DiarioObra;
use App\Models\TaxaAdministracao;

class DashboardController extends Controller
{
    public function index()
    {
        $mesAtual = now()->format('Y-m');
        $mesAnt   = now()->subMonth()->format('Y-m');

        // KPIs globais
        $totalGastoMes      = LancamentoObra::whereRaw("DATE_FORMAT(data_lancamento,'%Y-%m')=?", [$mesAtual])->sum('custo_total_real');
        $totalGastoMesAnt   = LancamentoObra::whereRaw("DATE_FORMAT(data_lancamento,'%Y-%m')=?", [$mesAnt])->sum('custo_total_real');
        $totalGastoAcum     = LancamentoObra::sum('custo_total_real');
        $totalGastoPendente = LancamentoObra::where('status_pagamento', 'pendente')->sum('custo_total_real');
        $varGastoMes        = $totalGastoMesAnt > 0
            ? round((($totalGastoMes - $totalGastoMesAnt) / $totalGastoMesAnt) * 100, 1)
            : 0;

        // KPIs de obras
        $obrasTotal       = Obra::count();
        $obrasEmAndamento = Obra::where('status', 'em_andamento')->count();
        $obrasConcluidas  = Obra::where('status', 'concluida')->count();
        $obrasPendentes   = Obra::where('status', 'pendente')->count();

        // Gastos últimos 6 meses
        $gastosPorMes = LancamentoObra::selectRaw(
            "DATE_FORMAT(data_lancamento,'%Y-%m') as mes,
             SUM(custo_total_real) as total,
             SUM(CASE WHEN status_pagamento='pago' THEN custo_total_real ELSE 0 END) as pago"
        )
        ->where('data_lancamento', '>=', now()->subMonths(5)->startOfMonth())
        ->groupByRaw("DATE_FORMAT(data_lancamento,'%Y-%m')")
        ->orderBy('mes')
        ->get();

        // Cards de obras ativas (estilo draga)
        $obras = Obra::with(['fases.faseCatalogo'])
            ->whereIn('status', ['em_andamento', 'pausada', 'pendente'])
            ->orderByRaw("FIELD(status,'em_andamento','pausada','pendente')")
            ->orderBy('created_at', 'desc')
            ->get();

        $obrasCards = $obras->map(function ($obra) use ($mesAtual, $mesAnt) {
            $gastoAcum   = (float) DB::table('lancamentos_obra')->where('obra_id', $obra->id)->whereNull('deleted_at')->sum('custo_total_real');
            $gastoMes    = (float) DB::table('lancamentos_obra')->where('obra_id', $obra->id)->whereNull('deleted_at')->whereRaw("DATE_FORMAT(data_lancamento,'%Y-%m')=?", [$mesAtual])->sum('custo_total_real');
            $gastoMesAnt = (float) DB::table('lancamentos_obra')->where('obra_id', $obra->id)->whereNull('deleted_at')->whereRaw("DATE_FORMAT(data_lancamento,'%Y-%m')=?", [$mesAnt])->sum('custo_total_real');

            $funcMes    = DB::table('apontamentos')->where('obra_id', $obra->id)->whereRaw("DATE_FORMAT(data,'%Y-%m')=?", [$mesAtual])->distinct('funcionario_id')->count('funcionario_id');
            $funcMesAnt = DB::table('apontamentos')->where('obra_id', $obra->id)->whereRaw("DATE_FORMAT(data,'%Y-%m')=?", [$mesAnt])->distinct('funcionario_id')->count('funcionario_id');

            $terceiros = DB::table('lancamentos_obra')->where('obra_id', $obra->id)->whereNull('deleted_at')->where('tipo', 'terceiro')->whereNotNull('fornecedor_id')->distinct('fornecedor_id')->count('fornecedor_id');

            $faseAtual            = $obra->fases->firstWhere('status', 'em_andamento');
            $fasesAtrasadasColl   = $obra->fases->where('status', 'em_andamento')->filter(fn($f) => $f->atrasada)->sortByDesc('dias_atrasados');
            $fasesAtrasadasN      = $fasesAtrasadasColl->count();
            $diasRestantes        = $obra->data_fim_prevista ? now()->diffInDays($obra->data_fim_prevista, false) : null;
            $cronStatus           = $fasesAtrasadasN > 0 ? 'atrasado'
                : (($diasRestantes !== null && $diasRestantes < 0) ? 'atrasado'
                : (($diasRestantes !== null && $diasRestantes > 45) ? 'adiantado' : 'no_prazo'));

            $totalFases = $obra->fases->count();
            $fasesConcl = $obra->fases->where('status', 'concluida')->count();
            $progresso  = $totalFases > 0 ? round($obra->fases->avg('percentual_realizado'), 1) : 0;
            $varMes     = $gastoMesAnt > 0 ? round((($gastoMes - $gastoMesAnt) / $gastoMesAnt) * 100, 1) : 0;

            return (object) [
                'obra'                 => $obra,
                'gastoAcum'            => $gastoAcum,
                'gastoMes'             => $gastoMes,
                'gastoMesAnt'          => $gastoMesAnt,
                'varMes'               => $varMes,
                'funcMes'              => $funcMes,
                'funcMesAnt'           => $funcMesAnt,
                'terceiros'            => $terceiros,
                'faseAtual'            => $faseAtual,
                'fasesAtrasadas'       => $fasesAtrasadasN,
                'fasesAtrasadasColl'   => $fasesAtrasadasColl,
                'cronStatus'           => $cronStatus,
                'diasRestantes'        => $diasRestantes,
                'totalFases'           => $totalFases,
                'fasesConcl'           => $fasesConcl,
                'progresso'            => $progresso,
            ];
        });

        $taxasPendentes      = TaxaAdministracao::where('status', 'pendente')->count();
        $taxasPendentesValor = TaxaAdministracao::where('status', 'pendente')->sum('valor_taxa');
        $diariosHoje         = DiarioObra::whereDate('data_registro', today())->count();
        $diariosSemana       = DiarioObra::where('data_registro', '>=', now()->startOfWeek())->count();
        $fasesAtrasadas      = \App\Models\ObraFase::with(['obra', 'faseCatalogo'])
            ->where('status', 'em_andamento')->get()
            ->filter(fn($f) => $f->atrasada)->sortByDesc('dias_atrasados');
        $totalFasesAtrasadas = $fasesAtrasadas->count();

        return view('admin.dashboard-livewire', compact(
            'obrasCards', 'obrasTotal', 'obrasEmAndamento', 'obrasConcluidas', 'obrasPendentes',
            'totalGastoMes', 'totalGastoMesAnt', 'totalGastoAcum', 'totalGastoPendente', 'varGastoMes',
            'gastosPorMes', 'taxasPendentes', 'taxasPendentesValor',
            'diariosHoje', 'diariosSemana', 'fasesAtrasadas', 'totalFasesAtrasadas', 'mesAtual', 'mesAnt'
        ));
    }

    public function obraDashboard(Obra $obra)
    {
        $mesAtual = now()->format('Y-m');
        $mesAnt   = now()->subMonth()->format('Y-m');

        $gastoAcum     = (float) DB::table('lancamentos_obra')->where('obra_id', $obra->id)->whereNull('deleted_at')->sum('custo_total_real');
        $gastoMes      = (float) DB::table('lancamentos_obra')->where('obra_id', $obra->id)->whereNull('deleted_at')->whereRaw("DATE_FORMAT(data_lancamento,'%Y-%m')=?", [$mesAtual])->sum('custo_total_real');
        $gastoMesAnt   = (float) DB::table('lancamentos_obra')->where('obra_id', $obra->id)->whereNull('deleted_at')->whereRaw("DATE_FORMAT(data_lancamento,'%Y-%m')=?", [$mesAnt])->sum('custo_total_real');
        $gastoPendente = (float) DB::table('lancamentos_obra')->where('obra_id', $obra->id)->whereNull('deleted_at')->where('status_pagamento', 'pendente')->sum('custo_total_real');

        // Gastos últimos 6 meses
        $gastosMeses = DB::table('lancamentos_obra')
            ->where('obra_id', $obra->id)->whereNull('deleted_at')
            ->where('data_lancamento', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(data_lancamento,'%Y-%m') as mes, SUM(custo_total_real) as total")
            ->groupByRaw("DATE_FORMAT(data_lancamento,'%Y-%m')")->orderBy('mes')->get();

        // Gastos por categoria acumulado
        $gastosPorCategoria = DB::table('lancamentos_obra as l')
            ->leftJoin('categorias_material as c', 'c.id', '=', 'l.categoria_id')
            ->where('l.obra_id', $obra->id)->whereNull('l.deleted_at')
            ->selectRaw("COALESCE(c.nome,'Sem Categoria') as categoria, SUM(l.custo_total_real) as total, COUNT(*) as qtd")
            ->groupBy('l.categoria_id', 'c.nome')->orderByDesc('total')->get();

        // Categorias: mês atual vs anterior
        $catMesAtual = DB::table('lancamentos_obra as l')
            ->leftJoin('categorias_material as c', 'c.id', '=', 'l.categoria_id')
            ->where('l.obra_id', $obra->id)->whereNull('l.deleted_at')
            ->whereRaw("DATE_FORMAT(l.data_lancamento,'%Y-%m')=?", [$mesAtual])
            ->selectRaw("COALESCE(c.nome,'Sem Categoria') as categoria, SUM(l.custo_total_real) as total")
            ->groupBy('l.categoria_id', 'c.nome')->orderByDesc('total')->get();

        $catMesAnt = DB::table('lancamentos_obra as l')
            ->leftJoin('categorias_material as c', 'c.id', '=', 'l.categoria_id')
            ->where('l.obra_id', $obra->id)->whereNull('l.deleted_at')
            ->whereRaw("DATE_FORMAT(l.data_lancamento,'%Y-%m')=?", [$mesAnt])
            ->selectRaw("COALESCE(c.nome,'Sem Categoria') as categoria, SUM(l.custo_total_real) as total")
            ->groupBy('l.categoria_id', 'c.nome')->get()->keyBy('categoria');

        $categoriasLabels = $catMesAtual->pluck('categoria')
            ->merge($catMesAnt->pluck('categoria'))->unique()->values();

        // Funcionários por mês (últimos 6 meses)
        $funcPorMes = DB::table('apontamentos')
            ->where('obra_id', $obra->id)
            ->where('data', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(data,'%Y-%m') as mes, COUNT(DISTINCT funcionario_id) as total")
            ->groupByRaw("DATE_FORMAT(data,'%Y-%m')")->orderBy('mes')->get();

        $funcMesAtual = DB::table('apontamentos')->where('obra_id', $obra->id)->whereRaw("DATE_FORMAT(data,'%Y-%m')=?", [$mesAtual])->distinct('funcionario_id')->count('funcionario_id');
        $funcMesAnt   = DB::table('apontamentos')->where('obra_id', $obra->id)->whereRaw("DATE_FORMAT(data,'%Y-%m')=?", [$mesAnt])->distinct('funcionario_id')->count('funcionario_id');

        // Terceirizados
        $terceirizados = DB::table('lancamentos_obra as l')
            ->leftJoin('fornecedores as f', 'f.id', '=', 'l.fornecedor_id')
            ->where('l.obra_id', $obra->id)->whereNull('l.deleted_at')
            ->where('l.tipo', 'terceiro')
            ->selectRaw("COALESCE(f.nome_fantasia, f.razao_social, l.fornecedor,'Sem nome') as nome, SUM(l.custo_total_real) as total, COUNT(*) as qtd")
            ->groupBy('l.fornecedor_id', 'f.nome_fantasia', 'f.razao_social', 'l.fornecedor')
            ->orderByDesc('total')->get();

        $totalTerceiros = $terceirizados->count();

        // Gastos por tipo
        $gastosPorTipo = DB::table('lancamentos_obra')
            ->where('obra_id', $obra->id)->whereNull('deleted_at')
            ->selectRaw("tipo, SUM(custo_total_real) as total")
            ->groupBy('tipo')->orderByDesc('total')->get();

        // Fases
        $obra->load('fases.faseCatalogo');
        $fases           = $obra->fases->sortBy('ordem');
        $faseAtual       = $fases->firstWhere('status', 'em_andamento');
        $fasesAtrasadas  = $fases->where('status', 'em_andamento')->filter(fn($f) => $f->atrasada)->count();
        $totalFases      = $fases->count();
        $fasesConcl      = $fases->where('status', 'concluida')->count();
        $progresso       = $totalFases > 0 ? round($fases->avg('percentual_realizado'), 1) : 0;
        $diasRestantes   = $obra->data_fim_prevista ? now()->diffInDays($obra->data_fim_prevista, false) : null;
        $cronStatus      = $fasesAtrasadas > 0 ? 'atrasado'
            : (($diasRestantes !== null && $diasRestantes < 0) ? 'atrasado'
            : (($diasRestantes !== null && $diasRestantes > 45) ? 'adiantado' : 'no_prazo'));

        return view('obras.dashboard', compact(
            'obra', 'gastoAcum', 'gastoMes', 'gastoMesAnt', 'gastoPendente',
            'gastosMeses', 'gastosPorCategoria', 'catMesAtual', 'catMesAnt',
            'categoriasLabels', 'funcPorMes', 'funcMesAtual', 'funcMesAnt',
            'terceirizados', 'totalTerceiros', 'gastosPorTipo',
            'fases', 'faseAtual', 'fasesAtrasadas', 'totalFases', 'fasesConcl',
            'progresso', 'diasRestantes', 'cronStatus', 'mesAtual', 'mesAnt'
        ));
    }
}
