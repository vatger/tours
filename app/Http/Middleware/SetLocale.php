<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('X-Locale')
            ?? $request->cookie('preferred_language')
            ?? config('app.locale');

        app()->setLocale($locale);

        return $next($request)->cookie(
            'preferred_language',
            $locale,
            60 * 24 * 365 // 1 ano
        );
    }
}
