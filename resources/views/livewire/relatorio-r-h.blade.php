<div>
    <!-- Área de Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Filtros') }}</h3>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="buscarDiarias">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data_inicial">Data Inicial</label>
                            <input type="date" wire:model="data_inicial" id="data_inicial" class="form-control" required>
                            @error('data_inicial') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data_final">Data Final</label>
                            <input type="date" wire:model="data_final" id="data_final" class="form-control" required>
                            @error('data_final') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        @if(!empty($diarias))
                            <button type="button" class="btn btn-success ml-2" wire:click="imprimir">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Área de Resultados -->
    <div id="resultado-relatorio" class="@if(empty($diarias)) no-screen @endif">
        <!-- Mensagens -->
        @if($mensagem)
            <div class="alert alert-{{ $tipo_mensagem }}">
                {{ $mensagem }}
            </div>
        @endif

        @if(!empty($diarias))
            <!-- Botão de Impressão -->
            <div class="mb-3 no-print">
                <button class="btn btn-secondary" wire:click="imprimir">
                    <i class="fas fa-print mr-2"></i>Imprimir
                </button>
            </div>

            <!-- Cartões de Resumo -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Total Geral</h3>
                        </div>
                        <div class="card-body">
                            <h4>R$ {{ number_format($totalDiarias, 2, ',', '.') }}</h4>
                            <p class="mb-0">Total de registros: {{ count($diarias) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Resumo por Departamento -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Resumo por Departamento</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Departamento</th>
                                    <th>Quantidade</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumoPorDepartamento as $departamento => $dados)
                                    <tr>
                                        <td>{{ $departamento }}</td>
                                        <td>{{ $dados['quantidade'] }}</td>
                                        <td>R$ {{ number_format($dados['total'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tabela de Detalhes -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalhamento</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Departamento</th>
                                    <th>Função</th>
                                    <th>Diária</th>
                                    <th>Referência</th>
                                    <th>Data</th>
                                    <th>Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($diarias as $diaria)
                                    <tr>
                                        <td>{{ $diaria->nome }}</td>
                                        <td>{{ $diaria->departamento }}</td>
                                        <td>{{ $diaria->funcao }}</td>
                                        <td>R$ {{ number_format($diaria->diaria, 2, ',', '.') }}</td>
                                        <td>{{ $diaria->referencia }}</td>
                                        <td>{{ \Carbon\Carbon::parse($diaria->data_inclusao)->format('d/m/Y') }}</td>
                                        <td>{{ $diaria->observacao }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('imprimir-relatorio', () => {
                window.print();
            });
        });
    </script>
    @endpush
</div> 