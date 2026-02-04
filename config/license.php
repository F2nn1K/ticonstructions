<?php

return [
	// Ativa/desativa a verificação de licença (pode ser controlado via .env)
	'enabled' => env('LICENSE_ENABLED', false),

	// Caminho da chave pública utilizada para verificar a assinatura da licença
	'public_key_path' => env('LICENSE_PUBLIC_KEY_PATH', storage_path('app/license/license_pub.pem')),

	// Caminho do arquivo de licença (.lic) enviado/colado pelo administrador
	'license_path' => env('LICENSE_FILE_PATH', storage_path('app/license/license.lic')),

	// Domínio permitido. Se null, será comparado com o host de APP_URL
	'allowed_domain' => env('LICENSE_ALLOWED_DOMAIN', null),
];


