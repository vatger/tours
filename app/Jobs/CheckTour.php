<?php

namespace App\Jobs;

use App\Models\Tour;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckTour implements ShouldQueue
{
    use Queueable;

    public int $tour_id;

    public function __construct(?int $tour_id = null)
    {
        if ($tour_id) {
            $this->tour_id = $tour_id;
            return;
        }
        $id = Cache::get('CheckTour_last_checked_id', 0);
        $next_tour = Tour::where('id', '>', $id)->first();
        if (! $next_tour) {
            $this->tour_id = Tour::first()->id;
        } else {
            $this->tour_id = $next_tour->id;
        }
        Cache::put('CheckTour_last_checked_id', $this->tour_id);
    }

    public function handle(): void
    {
        $tour = Tour::with('legs')->find($this->tour_id);
        if (! $tour) {
            return;
        }
        $fist_leg = $tour->legs->first();
        if (! $fist_leg) {
            return;
        }
        $fist_leg->loadMissing('users');
        $users = $fist_leg->users;
        $user_count = count($users);
        Log::info("Checking Tour $tour->id ($tour->name) for $user_count users");
        foreach ($users as $user) {
            new CheckTourUser($user, $tour)->handle();
            new CheckTourCompletedUser($user, $tour)->handle();
        }


    }
}
