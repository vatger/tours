<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority 1: User's saved locale preference
        if ($userLocale = $request->user()?->locale) {
            App::setLocale($userLocale);
            return $next($request);
        }

        // Priority 2: Session locale (for guest users who changed language)
        if ($sessionLocale = $request->session()->get('locale')) {
            if (in_array($sessionLocale, $this->getSupportedLocales())) {
                App::setLocale($sessionLocale);
                return $next($request);
            }
        }

        // Priority 3: Browser language preferences
        $this->setLocaleFromBrowser($request);

        return $next($request);
    }

    /**
     * Set locale based on browser language preferences
     */
    private function setLocaleFromBrowser(Request $request): void
    {
        $browserLocales = $request->getLanguages();
        $supportedLocales = $this->getSupportedLocales();

        foreach ($browserLocales as $browserLocale) {
            // Try exact match first (e.g., en-US)
            $normalizedLocale = str_replace('_', '-', strtolower($browserLocale));
            if (in_array($normalizedLocale, $supportedLocales)) {
                App::setLocale($normalizedLocale);
                return;
            }

            // Try language code only (e.g., en from en-US)
            $languageCode = strtolower(substr($browserLocale, 0, 2));
            if (in_array($languageCode, $supportedLocales)) {
                App::setLocale($languageCode);
                return;
            }

            // Try to find a supported locale that starts with the language code
            foreach ($supportedLocales as $supportedLocale) {
                if (str_starts_with($supportedLocale, $languageCode . '-')) {
                    App::setLocale($supportedLocale);
                    return;
                }
            }
        }

        // Fallback to default locale if no match found
        App::setLocale(config('app.locale'));
    }

    /**
     * Get supported locales from config
     */
    private function getSupportedLocales(): array
    {
        return config('app.available_locales');
    }
}
