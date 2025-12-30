<?php

namespace App\Jobs;

use App\Models\Tour;
use App\Models\TourLegUser;
use App\Models\TourUser;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckTourCompletedUser implements ShouldQueue
{
    use Queueable;
    public function __construct(public User $user, public Tour $tour)
    {}
    public function handle(): void
    {
        $current_tour_completion = TourUser::where('user_id', $this->user->id)->where('tour_id', $this->tour->id)->first();
        if (!$current_tour_completion) {
            $current_tour_completion = new TourUser();
            $current_tour_completion->user_id = $this->user->id;
            $current_tour_completion->tour_id = $this->tour->id;
            $current_tour_completion->save();
        }
        $user_legs = TourLegUser::with('leg')
            ->where('user_id', $this->user->id)
            ->whereHas('leg', function ($q) {
                $q->where('tour_id', $this->tour->id);
            })
            ->get();
        $not_completed = $user_legs->contains(
            fn (TourLegUser $tour_leg_user) =>  $tour_leg_user->completed_at == null
        );
        if ($not_completed) {
            Log::info("Checking Tour $current_tour_completion->tour_id of user $current_tour_completion->user_id: tour was not completed");
            return;
        }
        $current_tour_completion->completed = true;
        $current_tour_completion->save();
        Log::info("Checking Tour $current_tour_completion->tour_id of user $current_tour_completion->user_id: tour completed");
    }
}
