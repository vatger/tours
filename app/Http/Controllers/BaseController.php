<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controller as LaravelController;

class BaseController extends Controller
{
    protected function respond(Request $request, string $view, array $data = [], int $status = 200)
    {
        // If API call (starts with /api or expects JSON)
        if ($request->is('api/*') || $request->wantsJson()) {
            return response()->json($data, $status);
        }

        // Otherwise, return Inertia page
        return Inertia::render($view, $data)->toResponse($request);
    }
}