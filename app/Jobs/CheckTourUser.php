<?php

namespace App\Jobs;

use App\Models\Tour;
use App\Models\TourLegUser;
use App\Models\TourUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckTourUser implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public Tour $tour)
    {}

    public function handle(): void
    {
        $current_tour_completion = TourUser::where('user_id', $this->user->id)
            ->where('tour_id', $this->tour->id)->first();
        if ($current_tour_completion && $current_tour_completion->completed) {
            Log::info("Checking Tour {$this->tour->id} of user {$this->user->id}: Tour is already completed.");
        }

        $current_start_time = $this->tour->begins_at;
        $current_end_time = $this->tour->ends_at;
        $legs = $this->tour->legs;
        foreach ($legs as $leg) {
            $last_leg_completed = false;

            $leg_string = "Checking Tour {$this->tour->id}, Leg $leg->id ($leg->departure_icao-$leg->arrival_icao) of user {$this->user->id}";

            /* @var ?TourLegUser $status */
            $status = $leg->status($this->user->id)->first();
            if (! $status) {
                Log::info("$leg_string: not registered");

                return;
            }

            $url = 'http://stats.vatsim-germany.org/api/flights/from/'.$leg->departure_icao.'/to/'.$leg->arrival_icao;

            try {
                $response = Http::get($url, [
                    'start_date' => $current_start_time->format('Y-m-d'),
                    'end_date' => $current_end_time->format('Y-m-d'),
                    'cid' => $this->user->id,
                    'ascending' => true,
                    'completed' => true,
                ]);
            } catch (Throwable $e) {
                Log::warning("$leg_string: could not connect to stats.");
                return;
            }

            $flights = $response->json();
            $flights = collect($flights)
                ->filter(function ($flight) {
                    $correct_rules = $this->tour->flight_rules == null
                        || strtoupper($flight['flight_type']) == strtoupper($this->tour->flight_rules);
                    $correct_aircraft = empty($this->tour->aircraft)
                        || collect(explode(',', strtoupper($this->tour->aircraft)))
                            ->contains(strtoupper($flight['aircraft']));

                    return $correct_rules && $correct_aircraft;
                });

            foreach ($flights as $flight) {
                $departed = Carbon::parse($flight['departed_at']);
                $arrived = Carbon::parse($flight['arrived_at']);
                $flight_id = $flight['id'];
                if ($departed->isBefore($current_start_time)) {
                    continue;
                }
                if ($arrived->isAfter($current_end_time)) {
                    continue;
                }
                // the leg has been completed
                $last_leg_completed = true;
                if ($status->completed_at && $status->completed_at->isBefore($arrived)) {
                    Log::info("$leg_string: already found earlier flight");
                    break;
                }
                $status->completed_at = $arrived;
                $status->fight_data_id = $flight_id;
                $status->save();
                Log::info("$leg_string: found flight $flight_id");
                break;
            }

            if ($status->completed_at && ! $last_leg_completed) {
                $last_leg_completed = true;
                Log::info("$leg_string: already found flight");
            }

            if ($this->tour->require_order && ! $last_leg_completed) {
                Log::info("$leg_string: no flight found skipping rest");

                return;
            }

            if ($this->tour->require_order) {
                $current_start_time = $status->completed_at->subMinutes(1);
            }

            if (! $last_leg_completed) {
                Log::info("$leg_string: no flight found");
            }
        }

    }
}
