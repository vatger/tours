<?php

namespace App\Http\Controllers;

use App\Services\AirportCoordinateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AirportController extends Controller
{
    public function index(Request $request, AirportCoordinateService $airports): JsonResponse
    {
        $validated = $request->validate([
            'icaos' => ['required', 'array', 'max:100'],
            'icaos.*' => ['required', 'string', 'size:4', 'regex:/^[A-Za-z0-9]{4}$/'],
        ]);

        return response()->json([
            'data' => $airports->find($validated['icaos']),
        ]);
    }
}
