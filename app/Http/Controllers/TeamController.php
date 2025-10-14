<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class TeamController extends Controller
{
    public function show(Request $request): RedirectResponse|Response
    {
        // dd('hot here');
        // return $this->respond($request, '');
        return inertia::render('teams/Index');
    }
}
