@foreach($usuarios as $usuario)
<tr>
    <td>{{ $usuario->name }}</td>
    <td>{{ $usuario->email }}</td>
    <td>{{ $usuario->profile ? $usuario->profile->name : 'Sem perfil' }}</td>
    <td>
        @if($usuario->active)
            <span class="badge badge-success">{{ __('Ativo') }}</span>
        @else
            <span class="badge badge-danger">{{ __('Inativo') }}</span>
        @endif
    </td>
    <td class="text-center">
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-info" 
                    onclick="editarUsuario({{ $usuario->id }})">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-{{ $usuario->active ? 'warning' : 'success' }}" 
                    onclick="alterarStatus({{ $usuario->id }}, {{ $usuario->active ? 'false' : 'true' }})">
                <i class="fas fa-{{ $usuario->active ? 'ban' : 'check' }}"></i>
            </button>
        </div>
    </td>
</tr>
@endforeach 