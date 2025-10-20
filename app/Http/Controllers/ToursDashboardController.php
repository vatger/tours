<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Inertia\Inertia;

class ToursDashboardController extends Controller
{
    public function index(?int $id = null)
    {
        $tours_list = Tour::all();
        $current_tour = Tour::with(['legs', 'legs.status']);
        if ($id) {
            $current_tour->where('id', $id);
        }
        $current_tour->first();

        return Inertia::render('Tours/Show', [
            'tours_list' => $tours_list,
            'current_tour' => $current_tour,
        ]);
    }
}
