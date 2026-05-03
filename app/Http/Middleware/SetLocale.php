<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['pt_BR', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));

        if (!in_array($locale, self::SUPPORTED_LOCALES)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        // Translate adminlte menu items at request time
        // The config stores translation keys (e.g. 'app.menu.dashboard');
        // we resolve them here after the locale is set.
        $this->translateAdminlteMenu();

        return $next($request);
    }

    private function translateAdminlteMenu(): void
    {
        $menu = config('adminlte.menu', []);

        if (empty($menu)) {
            return;
        }

        $menu = $this->translateMenuItems($menu);
        Config::set('adminlte.menu', $menu);
    }

    private function translateMenuItems(array $items): array
    {
        return array_map(function (array $item) {
            if (isset($item['text']) && is_string($item['text'])) {
                $item['text'] = __($item['text']);
            }
            if (isset($item['submenu']) && is_array($item['submenu'])) {
                $item['submenu'] = $this->translateMenuItems($item['submenu']);
            }
            return $item;
        }, $items);
    }
}
