<!-- Modal de Permissões -->
<div class="modal fade" id="modalPermissoes" tabindex="-1" role="dialog" aria-labelledby="modalPermissoesLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPermissoesLabel">
                    Gerenciar Permissões do Perfil
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if($perfilSelecionado)
                    <div class="form-group">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" 
                                class="custom-control-input" 
                                id="selectAll"
                                wire:click="toggleTodasPermissoes">
                            <label class="custom-control-label" for="selectAll">
                                <strong>Selecionar Todas as Permissões</strong>
                            </label>
                        </div>
                    </div>

                    <div class="permissoes-container">
                        @foreach($todasPermissoes as $permissao)
                            <div class="permissao-item">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" 
                                        class="custom-control-input" 
                                        id="permissao_{{ $permissao->id }}"
                                        wire:model="permissoesSelecionadas"
                                        value="{{ $permissao->id }}">
                                    <label class="custom-control-label" for="permissao_{{ $permissao->id }}">
                                        {{ $permissao->name }}
                                    </label>
                                    @if($permissao->description)
                                        <small class="form-text text-muted">
                                            {{ $permissao->description }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning">
                        Selecione um perfil para gerenciar suas permissões.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Fechar') }}</button>
                @if($perfilSelecionado)
                    <button type="button" class="btn btn-primary" wire:click="salvarPermissoes">
                        Salvar Permissões
                    </button>
                @endif
            </div>
        </div>
    </div>
</div> 