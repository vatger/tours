<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['pt_BR', 'en', 'fr', 'it', 'de', 'es'];
        $defaultLocale = app()->getLocale();

        // 1. Verifica o header X-Locale (enviado pelo Inertia)
        if ($request->hasHeader('X-Locale')) {
            $locale = $request->header('X-Locale');
            if (in_array($locale, $supportedLocales)) {
                app()->setLocale($locale);
                return $next($request);
            }
        }

        // 2. Verifica o parâmetro na requisição (para rotas POST/PATCH)
        if ($request->has('locale')) {
            $locale = $request->input('locale');
            if (in_array($locale, $supportedLocales)) {
                app()->setLocale($locale);
                return $next($request);
            }
        }

        // 3. Verifica o cookie (para primeira visita)
        if ($request->hasCookie('preferred_language')) {
            $locale = $request->cookie('preferred_language');
            if (in_array($locale, $supportedLocales)) {
                app()->setLocale($locale);
                return $next($request);
            }
        }

        // 4. Usa o locale padrão
        app()->setLocale($defaultLocale);

        return $next($request)->withCookie(cookie()->forever('preferred_language', $defaultLocale));
    }
}
