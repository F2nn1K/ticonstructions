@extends('adminlte::page')

@section('title', __('Acompanhar Pedido'))

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-eye text-primary mr-2"></i>{{ __('Acompanhar Pedido') }}</h1>
@stop

@section('content')
<div class="row">
  <div class="col-lg-4 col-md-6">
    <div class="small-box bg-warning shadow-sm box-hover">
      <div class="inner">
        <h3 id="count-pendentes" class="mb-0">0</h3>
        <p class="mb-0">{{ __('Pendentes') }}</p>
      </div>
      <div class="icon"><i class="fas fa-clock"></i></div>
      <a href="{{ route('pedidos.acompanhar.pendentes') }}" class="small-box-footer">{{ __('Ver pendentes') }} <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-4 col-md-6">
    <div class="small-box bg-success shadow-sm box-hover">
      <div class="inner">
        <h3 id="count-aprovadas" class="mb-0">0</h3>
        <p class="mb-0">{{ __('Aprovadas') }}</p>
      </div>
      <div class="icon"><i class="fas fa-check"></i></div>
      <a href="{{ route('pedidos.acompanhar.aprovadas') }}" class="small-box-footer">{{ __('Ver aprovadas') }} <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-4 col-md-6">
    <div class="small-box bg-danger shadow-sm box-hover">
      <div class="inner">
        <h3 id="count-rejeitadas" class="mb-0">0</h3>
        <p class="mb-0">{{ __('Rejeitadas') }}</p>
      </div>
      <div class="icon"><i class="fas fa-times"></i></div>
      <a href="{{ route('pedidos.acompanhar.rejeitadas') }}" class="small-box-footer">{{ __('Ver rejeitadas') }} <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$(function(){
  $.get('/api/pedidos/acompanhar/lista', function(resp){
    if(!resp.success) return; const dados = resp.data||[];
    const pend = dados.filter(d=> (d.status||'pendente')==='pendente').length;
    const apr = dados.filter(d=> (d.status||'pendente')==='aprovado').length;
    const rej = dados.filter(d=> (d.status||'pendente')==='rejeitado').length;
    $('#count-pendentes').text(pend);
    $('#count-aprovadas').text(apr);
    $('#count-rejeitadas').text(rej);
  });
});
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

