@extends('adminlte::page')

@section('title', 'Autorizações de Compras')

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-gavel text-primary mr-2"></i>Autorizações</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="filtro_mes" class="font-weight-bold text-dark mb-1">Filtrar por mês</label>
            <input type="month" id="filtro_mes" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" id="btn-limpar-mes" class="btn btn-outline-secondary">Limpar</button>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-warning shadow-sm box-hover">
                <div class="inner">
                    <h3 id="count-pendentes" class="mb-0">0</h3>
                    <p class="mb-0">Pendentes</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <a id="lnk-pendentes" href="{{ route('pedidos.autorizacao.pendentes') }}" class="small-box-footer">
                    Ver pendentes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success shadow-sm box-hover">
                <div class="inner">
                    <h3 id="count-aprovadas" class="mb-0">0</h3>
                    <p class="mb-0">Aprovadas</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
                <a id="lnk-aprovadas" href="{{ route('pedidos.autorizacao.aprovadas') }}" class="small-box-footer">
                    Ver aprovadas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-danger shadow-sm box-hover">
                <div class="inner">
                    <h3 id="count-rejeitadas" class="mb-0">0</h3>
                    <p class="mb-0">Rejeitadas</p>
                </div>
                <div class="icon"><i class="fas fa-times"></i></div>
                <a id="lnk-rejeitadas" href="{{ route('pedidos.autorizacao.rejeitadas') }}" class="small-box-footer">
                    Ver rejeitadas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function(){
  // Define mês atual como padrão e inicializa
  const agora = new Date();
  const valorMesAtual = agora.toISOString().slice(0,7);
  $('#filtro_mes').val(valorMesAtual);
  atualizarContagens();
  setInterval(atualizarContagens, 30000);

  $('#filtro_mes').on('change', atualizarContagens);
  $('#btn-limpar-mes').on('click', function(){
    $('#filtro_mes').val('');
    atualizarContagens();
  });

  // Propagar o período selecionado nos links de navegação
  function atualizarLinks(){
    const p = periodoSelecionado();
    const qp = (!p.data_ini || !p.data_fim) ? '' : (`?data_ini=${encodeURIComponent(p.data_ini)}&data_fim=${encodeURIComponent(p.data_fim)}`);
    const basePend = $('#lnk-pendentes').attr('href').split('?')[0];
    const baseApro = $('#lnk-aprovadas').attr('href').split('?')[0];
    const baseRej  = $('#lnk-rejeitadas').attr('href').split('?')[0];
    $('#lnk-pendentes').attr('href', basePend + qp);
    $('#lnk-aprovadas').attr('href', baseApro + qp);
    $('#lnk-rejeitadas').attr('href', baseRej + qp);
  }
  atualizarLinks();
  $('#filtro_mes, #btn-limpar-mes').on('change click', atualizarLinks);
});

function periodoSelecionado(){
  const mes = $('#filtro_mes').val();
  if(!mes){ return {}; }
  const [ano, m] = mes.split('-');
  const first = `${ano}-${m}-01`;
  const lastDay = new Date(parseInt(ano,10), parseInt(m,10), 0).getDate();
  const last = `${ano}-${m}-${String(lastDay).padStart(2,'0')}`;
  return { data_ini: first, data_fim: last };
}

function atualizarContagens(){
  const params = periodoSelecionado();
  $.get('/api/pedidos-pendentes-agrupados', params, function(r){ if(r.success) $('#count-pendentes').text(r.data.length); });
  $.get('/api/pedidos-aprovados-agrupados', params, function(r){ if(r.success) $('#count-aprovadas').text(r.data.length); });
  $.get('/api/pedidos-rejeitados-agrupados', params, function(r){ if(r.success) $('#count-rejeitadas').text(r.data.length); });
}
</script>
@stop

@section('css')
<style>
.box-hover { transition: transform .15s ease, box-shadow .15s ease; }
.box-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
.small-box .inner h3 { font-weight: 700; }
.small-box .icon { color: rgba(255,255,255,.7); }
</style>
@stop


