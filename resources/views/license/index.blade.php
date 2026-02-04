@extends('layouts.app')

@section('content')
<div class="container">
	@if(session('license_error'))
		<div class="alert alert-danger">Licença inválida: {{ session('license_error') }}</div>
	@endif
	@if(session('success'))
		<div class="alert alert-success">{{ session('success') }}</div>
	@endif
	@if(session('error'))
		<div class="alert alert-danger">{{ session('error') }}</div>
	@endif

	<div class="card">
		<div class="card-header">Status da Licença</div>
		<div class="card-body">
			@if($status['valid'])
				<p><strong>Situação:</strong> {{ $status['reason'] === 'grace' ? 'Em carência' : 'OK' }}</p>
				@if(!empty($status['expires_at']))<p><strong>Válida até:</strong> {{ $status['expires_at'] }}</p>@endif
				@if(!empty($status['plan']))<p><strong>Plano:</strong> {{ $status['plan'] }}</p>@endif
				@if(!empty($status['users_max']))<p><strong>Usuários máx.:</strong> {{ $status['users_max'] }}</p>@endif
				@if(!empty($status['key']))<p><strong>Chave:</strong> {{ $status['key'] }}</p>@endif
			@else
				<p><strong>Situação:</strong> Inválida ({{ $status['reason'] }})</p>
			@endif
		</div>
	</div>

	<div class="card mt-3">
		<div class="card-header">Atualizar Licença</div>
		<div class="card-body">
			<form method="post" action="{{ route('license.upload') }}" enctype="multipart/form-data">
				@csrf
				@if(!$hasPub)
					<div class="form-group">
						<label>Chave pública (cole o conteúdo de license_pub.pem)</label>
						<textarea name="public_key" class="form-control" rows="6" placeholder="-----BEGIN PUBLIC KEY----- ..."></textarea>
					</div>
				@endif
				<div class="form-group">
					<label>Arquivo de licença (.lic)</label>
					<input type="file" name="license_file" class="form-control-file" accept=".lic,application/json">
				</div>
				<div class="text-muted my-2">ou cole o JSON abaixo:</div>
				<div class="form-group">
					<textarea name="license_text" class="form-control" rows="8" placeholder='{"key":"...","domain":"...","expires_at":"...","signature":"..."}'></textarea>
				</div>
				<button class="btn btn-primary">Salvar</button>
			</form>
		</div>
	</div>
</div>
@endsection


