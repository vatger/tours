<?php

namespace App\Jobs;

use App\Models\Tour;
use App\Models\TourUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AwardBadges implements ShouldQueue
{
    use Queueable;

    public int $tour_id;

    public function __construct(?int $tour_id = null)
    {
        if ($tour_id) {
            $this->tour_id = $tour_id;
            return;
        }
        $cache_key = 'AwardBadges_last_checked_id';
        $id = Cache::get($cache_key, 0);
        $next_tour = Tour::where('id', '>', $id)->first();
        if (! $next_tour) {
            $this->tour_id = Tour::first()->id;
        } else {
            $this->tour_id = $next_tour->id;
        }
        Cache::put($cache_key, $this->tour_id);
    }

    public function handle(): void
    {
        $tour_completions = TourUser::with('tour')
            ->where('completed', '=', 1)
            ->get();

        foreach ($tour_completions as $tour_completion) {
            $user_id = $tour_completion->user_id;
            $badge_id = $tour_completion->tour->badge_id;

            $url = "http://hp.vatsim-germany.org/api/board/user/badge";
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('myconfig.homepage_api_key'),
                    'Accept' => 'application/json',
                ])->post($url, [
                'user_id' => $user_id,
                'badge_id' => $badge_id,
                ]);


            if ($response->successful()) {
                $str = $response->body();
                Log::info("Giving Badge to $user_id answer={$str}");
                $has_badge = (bool)$response->json();
                if ($has_badge) {
                    $tour_completion->badge_given = true;
                    $tour_completion->save();
                }
            } else {
                Log::error($response->json());
            }

        }
    }
}
