<?php

namespace App\Jobs;

use App\Models\Tour;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckTour implements ShouldQueue
{
    use Queueable;

    public int $tour_id;

    public function __construct(int $tour_id)
    {
        $this->tour_id = $tour_id;
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
        foreach ($users as $user) {
            dispatch(new CheckTourUser($user, $tour));
        }

    }
}
