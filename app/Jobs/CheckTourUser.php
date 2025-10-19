<?php

namespace App\Jobs;

use App\Models\Tour;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CheckTourUser implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;

    public Tour $tour;

    public function __construct(User $user, Tour $tour)
    {
        $this->user = $user;
        $this->tour = $tour;
    }

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $current_start_date = $this->tour->begins_at;
        $current_end_date = $this->tour->ends_at;
        $legs = $this->tour->legs;
        foreach ($legs as $leg) {
            $status = $leg->status($this->user->id);

            $url = 'http://stats.vatsim-germany.org/api/flights/from/'.$leg->departure_icao.'/to/'.$leg->arrival_icao;

            $response = Http::get($url, [
                'start_date' => $current_start_date->format('Y-m-d'),
                'end_date' => $current_end_date->format('Y-m-d'),
                'cid' => $this->user->id,
                'ascending' => true,
                'completed' => true,
            ]);
            dd($response->json());

        }

    }
}
