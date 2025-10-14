<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function show(Request $request): Response
    {
        dd('hot here');
        return $this->respond($request, 'settings/Teams');
    }
}
