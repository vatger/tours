<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourLegUser;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ToursDashboardController extends Controller
{
    public function index(int $id = 0)
    {
        $tours_list = Tour::all();
        $current_tour = Tour::with(['legs', 'legs.status'])
            ->where('id', '>=', $id)
            ->orderBy('id', 'asc')
            ->first();
        $signed_up = $current_tour->legs->first()?->status ? true : false;

        return Inertia::render('Tours/Show', [
            'tours_list' => $tours_list,
            'current_tour' => $current_tour,
            'signed_up' => $signed_up,
        ]);
    }

    public function signup(int $id)
    {
        $user = Auth::user();
        $tour = Tour::with('legs')->findOrFail($id);
        $legs = $tour->legs;
        foreach ($legs as $leg) {
            TourLegUser::firstOrNew(['tour_leg_id' => $leg->id, 'user_id' => $user->id])->save();
        }

        return to_route('tours', ['id' => $id]);
    }

    public function cancel(int $id)
    {
        $user = Auth::user();
        $tour = Tour::with('legs')->findOrFail($id);
        $legs = $tour->legs;
        foreach ($legs as $leg) {
            TourLegUser::where('tour_leg_id', $leg->id)->where('user_id', $user->id)->delete();
        }

        return to_route('tours', ['id' => $id]);
    }
}
