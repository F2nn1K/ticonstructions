@extends('adminlte::page')

@section('title', __('Ocorrências - Frota'))

@section('plugins.Sweetalert2', true)

@section('content_header')
<h1>{{ __('Ocorrências da Frota') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: @json(session('success')),
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">__('Registrar Ocorrência')</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('frota.ocorrencias.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-row row">
                    <div class="form-group col-12 col-md-4">
                        <label for="veiculo_id">Veículo</label>
                        <select id="veiculo_id" name="veiculo_id" class="form-control" required>
                            <option value="">Selecione o veículo</option>
                            @foreach($veiculos as $veiculo)
                                <option value="{{ $veiculo->id }}" {{ old('veiculo_id') == $veiculo->id ? 'selected' : '' }}>
                                    {{ $veiculo->placa }} - {{ $veiculo->marca }} {{ $veiculo->modelo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="motorista">Motorista</label>
                        <input type="text" id="motorista" class="form-control" value="{{ Auth::user()->name }}" readonly>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <label for="data">Data</label>
                        <input type="date" id="data" name="data" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <label for="hora">Hora</label>
                        <input type="time" id="hora" name="hora" class="form-control" value="{{ now()->format('H:i') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição do problema</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="4" placeholder="Descreva o problema ocorrido" required>{{ old('descricao') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Fotos da ocorrência (até 10 imagens)</label>
                    <div class="mb-2">
                        <button type="button" id="btnAddFoto" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus"></i> Adicionar foto
                        </button>
                        <input type="file" id="filePicker" accept="image/*" class="d-none">
                        <small class="form-text text-muted">Formatos: JPG, JPEG, PNG, WEBP. Tamanho máx.: 50MB por arquivo.</small>
                    </div>
                    <div id="previews" class="d-flex flex-wrap gap-2"></div>
                </div>

                <div class="form-group">
                    <label for="sugestao">Sugestão (opcional)</label>
                    <textarea id="sugestao" name="sugestao" class="form-control" rows="3" placeholder="Se desejar, sugira uma solução">{{ old('sugestao') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Enviar Ocorrência</button>
            </form>

            @if($errors->any())
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erros de validação',
                            html: `<ul style="text-align:left; margin:0;">` +
                                `{!! collect($errors->all())->map(fn($e)=>"<li>".e($e)."</li>")->implode('') !!}` +
                                `</ul>`,
                            confirmButtonText: 'Ok'
                        });
                    });
                </script>
            @endif
        </div>
    </div>
@stop

@section('css')
<style>
    /* Desktop: mantém espaçamentos confortáveis */
    @media (min-width: 768px) {
        .card-body { padding: 1.5rem; }
    }

    /* Mobile first: campos em coluna, botões full width */
    @media (max-width: 767.98px) {
        .card-body { padding: 1rem; }
        .form-group { margin-bottom: .75rem; }
        .btn { width: 100%; }
        .card-title { font-size: 1.1rem; }
    }
    /* Grid simples de miniaturas */
    #previews { gap: 8px; }
    .thumb {
        position: relative;
        width: 110px; height: 110px;
        border: 1px solid #e0e0e0;
        border-radius: 8px; overflow: hidden;
        background: #f8f9fa;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 8px;
    }
    .thumb img { max-width: 100%; max-height: 100%; object-fit: cover; }
    .thumb .remove {
        position: absolute; top: 4px; right: 4px;
        background: rgba(220,53,69,.9); color: #fff; border: 0;
        border-radius: 50%; width: 24px; height: 24px; line-height: 24px;
        text-align: center; cursor: pointer; font-size: 12px;
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const maxFotos = 10;
    const btnAdd = document.getElementById('btnAddFoto');
    const picker = document.getElementById('filePicker');
    const previews = document.getElementById('previews');
    const form = document.querySelector('form');

    // Debug: verificar se formulário está sendo enviado
    form.addEventListener('submit', function(e) {
        console.log('Formulário sendo enviado...');
        // Não impedir o envio, apenas logar
    });

    btnAdd.addEventListener('click', () => picker.click());
    picker.addEventListener('change', handlePick);

    function handlePick() {
        const file = picker.files && picker.files[0] ? picker.files[0] : null;
        if (!file) return;

        // Validar tipo e tamanho (alinhado ao backend)
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            alert('Formato inválido. Use JPG, PNG ou WEBP.');
            picker.value = '';
            return;
        }
        if (file.size > 50 * 1024 * 1024) { // 50MB
            alert('Imagem acima de 50MB.');
            picker.value = '';
            return;
        }

        // Limite de 10
        const current = previews.querySelectorAll('.thumb').length;
        if (current >= maxFotos) {
            alert('Limite de 10 fotos atingido.');
            picker.value = '';
            return;
        }

        // Criar preview e input definitivo (name=fotos[])
        const url = URL.createObjectURL(file);
        const wrapper = document.createElement('div');
        wrapper.className = 'thumb';
        const img = document.createElement('img');
        img.src = url;
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'remove';
        remove.innerText = '×';

        // Mover o input selecionado para o preview e renomeá-lo
        const finalInput = document.createElement('input');
        finalInput.type = 'file';
        finalInput.name = 'fotos[]';
        finalInput.accept = 'image/*';
        finalInput.className = 'd-none';

        // Para manter o arquivo selecionado no submit, clonamos o File usando DataTransfer
        const dt = new DataTransfer();
        dt.items.add(file);
        finalInput.files = dt.files;

        remove.addEventListener('click', () => {
            URL.revokeObjectURL(url);
            wrapper.remove();
        });

        wrapper.appendChild(img);
        wrapper.appendChild(remove);
        wrapper.appendChild(finalInput);
        previews.appendChild(wrapper);

        // Limpar picker para permitir novo arquivo
        picker.value = '';
    }
});
</script>
@stop

