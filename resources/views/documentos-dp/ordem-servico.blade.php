@extends('adminlte::page')

@section('title', 'Ordem de Serviço')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-file-signature text-primary mr-2"></i>
            Ordem de Serviço
        </h1>
        <p class="text-muted mt-1 mb-0">Cadastro de Ordem de Serviço</p>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid os-page">
    <div class="modern-card">
        <div class="card-header-modern">
            <h3 class="card-title-modern"><i class="fas fa-plus-circle mr-2 text-primary"></i>Nova OS</h3>
            <div>
                <a href="{{ route('documentos-dp.ordem-servico') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
            </div>
        </div>
        <div class="card-body-modern">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show modern-alert">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><i class="fas fa-times"></i></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show modern-alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Erro!</strong> Verifique os dados informados.
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert"><i class="fas fa-times"></i></button>
            </div>
            @endif

            <form method="POST" action="{{ route('documentos-dp.ordem-servico.store') }}">
                @csrf

                <div class="row">
                    <div class="col-lg-3 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Data</label>
                        <input type="date" class="form-control modern-input" name="data_os" value="{{ old('data_os', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Nº da O.S.</label>
                        <input type="text" class="form-control modern-input" name="numero_os" value="{{ $numeroOs ?? '' }}" maxlength="30" readonly>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Funcionário</label>
                        <input type="text" id="funcionario_busca" class="form-control modern-input" placeholder="Digite ao menos 3 letras do nome">
                        <input type="hidden" name="funcionario_id" id="funcionario_id" value="{{ old('funcionario_id') }}">
                        <div id="funcionario_sugestoes" class="list-group mt-1" style="max-height: 220px; overflow-y: auto; display:none;"></div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Descrição do Serviço</label>
                        <textarea class="form-control modern-input" rows="5" name="descricao" placeholder="Descreva o serviço a ser executado" required>{{ old('descricao') }}</textarea>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Endereço</label>
                        <input type="text" class="form-control modern-input" name="endereco" value="{{ old('endereco') }}" maxlength="255">
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Cidade</label>
                        <input type="text" class="form-control modern-input" name="cidade" value="{{ old('cidade') }}" maxlength="120">
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Estado</label>
                        <input type="text" class="form-control modern-input text-uppercase" name="estado" value="{{ old('estado') }}" maxlength="2" placeholder="UF">
                    </div>

                    <div class="col-lg-3 mb-3">
                        <label class="font-weight-bold text-muted mb-2">CEP</label>
                        <input type="text" class="form-control modern-input" name="cep" value="{{ old('cep') }}" maxlength="10" placeholder="00000-000">
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Telefone</label>
                        <input type="text" class="form-control modern-input" name="telefone" value="{{ old('telefone') }}" maxlength="20" placeholder="(00) 00000-0000">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="font-weight-bold text-muted mb-2">CPF/CNPJ</label>
                        <input type="text" class="form-control modern-input" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}" maxlength="20" placeholder="CPF ou CNPJ">
                    </div>

                    <div class="col-12 mb-4">
                        <label class="font-weight-bold text-muted mb-2">Observações (opcional)</label>
                        <textarea class="form-control modern-input" rows="3" name="observacoes" placeholder="Observações complementares">{{ old('observacoes') }}</textarea>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save mr-2"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
.modern-input { border-radius: 12px; border: 2px solid #e2e8f0; padding: 12px 16px; font-size: 14px; background: #f8fafc; }
.modern-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); background: #fff; outline: none; }
.modern-card { background: #fff; border-radius: 12px; border: 2px solid #f1f5f9; }
@media print {
  .main-header, .main-sidebar, .main-footer, .content-header { display: none !important; }
  .content-wrapper { margin: 0 !important; }
}
.os-page * { transition: none !important; }
.os-page a:hover { text-decoration: none !important; color: inherit !important; }
.os-page .btn:hover, .os-page .btn:focus, .os-page .btn:active { box-shadow: none !important; filter: none !important; transform: none !important; }
.os-page .list-group-item:hover { background-color: inherit !important; color: inherit !important; }
.os-page .modern-card:hover, .os-page .card:hover { box-shadow: none !important; transform: none !important; }
.os-page table tbody tr:hover { background-color: transparent !important; }
.list-group-item { cursor: pointer; }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const $busca = document.getElementById('funcionario_busca');
    const $hiddenId = document.getElementById('funcionario_id');
    const $list = document.getElementById('funcionario_sugestoes');

    let debounceTimer = null;

    function clearList(){
        $list.innerHTML = '';
        $list.style.display = 'none';
    }

    $busca.addEventListener('input', function(){
        const q = this.value.trim();
        $hiddenId.value = '';
        if (debounceTimer) clearTimeout(debounceTimer);
        if (q.length < 3) { clearList(); return; }
        debounceTimer = setTimeout(async () => {
            try {
                   const resp = await fetch(`/api/funcionarios-busca?q=${encodeURIComponent(q)}`);
                const json = await resp.json();
                $list.innerHTML = '';
                if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
                    json.data.forEach(item => {
                        const a = document.createElement('a');
                        a.className = 'list-group-item list-group-item-action';
                        a.textContent = item.nome;
                        a.dataset.id = item.id;
                        a.addEventListener('click', function(){
                            $busca.value = this.textContent;
                            $hiddenId.value = this.dataset.id;
                            clearList();
                        });
                        $list.appendChild(a);
                    });
                    $list.style.display = 'block';
                } else {
                    clearList();
                }
            } catch (e) { clearList(); }
        }, 250);
    });

    // Fecha a lista ao clicar fora
    document.addEventListener('click', function(e){
        if (!($list.contains(e.target) || $busca.contains(e.target))) {
            clearList();
        }
    });
});
</script>
@stop


