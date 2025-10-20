<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Inertia\Inertia;

class ToursDashboardController extends Controller
{
    public function index(int $id = 0)
    {
        $tours_list = Tour::all();
        $current_tour = Tour::with(['legs', 'legs.status'])
            ->where('id','>=', $id)
            ->orderBy('id', 'asc')
            ->first();

        return Inertia::render('Tours/Show', [
            'tours_list' => $tours_list,
            'current_tour' => $current_tour,
        ]);
    }
}
