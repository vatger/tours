<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        $user = $request->user();

        // Preparar dados de roles e permissions
        $authData = [
            'user' => null,
            'roles' => [],
            'permissions' => []
        ];
        $locale = app()->getLocale();

        if ($request->hasHeader('X-Locale')) {
            $locale = $request->header('X-Locale');
            if ($locale) {
                app()->setLocale($locale);
            }
        }

        if ($user) {
            // Carregar roles e permissions (evitar N+1 query problem)
            $user->load('roles', 'permissions');

            $authData = [
                'user' => $user,
                'roles' => $user->getRoleNames(), // Retorna apenas os nomes das roles
                'permissions' => $user->getAllPermissions()->pluck('name') // Retorna todos os nomes de permissions
            ];
        }
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'locale' => $locale,
            'auth' => $authData,

            // 'auth' => [
            //     'user' => $request->user(),
            // ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
