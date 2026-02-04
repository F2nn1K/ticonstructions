<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;

class CheckLicense
{
    /** @var LicenseService */
    protected $service;

    public function __construct(LicenseService $service)
    {
        $this->service = $service;
    }

    public function handle(Request $request, Closure $next)
    {
		if (!config('license.enabled')) {
			return $next($request);
		}

		if (
			$request->is('login') ||
			$request->is('logout') ||
			$request->is('password/*') ||
			// Não liberar /license aqui; a rota está protegida por 'auth'
			$request->is('storage/*') ||
			$request->is('vendor/*') ||
			$request->is('js/*') ||
			$request->is('css/*') ||
			$request->is('img/*')
		) {
			return $next($request);
		}

		$status = $this->service->status();

		if (!$status['valid']) {
			// Permitir acesso às rotas /license para quem já estiver autenticado
			if ($request->is('license') || $request->is('license/*')) {
				return $next($request);
			}
			return redirect()->route('license.index')->with('license_error', $status['reason']);
		}

		return $next($request);
	}
}


