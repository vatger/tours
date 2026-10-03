<?php

namespace App\Http\Controllers;

use App\Jobs\CheckTourCompletedUser;
use App\Jobs\CheckTourUser;
use App\Models\Tour;
use App\Models\TourLegUser;
use App\Models\TourUser;
use App\Services\AirportCoordinateService;
use App\Services\QuickStatsFlightService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ToursDashboardController extends Controller
{
    public function dashboard(QuickStatsFlightService $quickStats)
    {
        $userId = Auth::id();

        $recentLegs = TourLegUser::query()
            ->with('leg.tour')
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->limit(4)
            ->get()
            ->map(fn (TourLegUser $status) => $this->legSummary($status));

        $nextLegs = TourLegUser::query()
            ->with('leg.tour')
            ->where('user_id', $userId)
            ->whereNull('completed_at')
            ->whereHas('leg.tour', fn ($query) => $query->where('ends_at', '>=', Carbon::now()))
            ->join('tour_legs', 'tour_leg_users.tour_leg_id', '=', 'tour_legs.id')
            ->join('tours', 'tour_legs.tour_id', '=', 'tours.id')
            ->orderBy('tours.ends_at')
            ->orderBy('tour_legs.id')
            ->select('tour_leg_users.*')
            ->limit(4)
            ->get()
            ->map(fn (TourLegUser $status) => $this->legSummary($status));

        $communityStats = Cache::remember('dashboard:community-stats', now()->addMinutes(5), fn () => [
            'legsFlownLast30Days' => TourLegUser::where('completed_at', '>=', Carbon::now()->subDays(30))->count(),
            'tourRegistrations' => TourUser::count(),
            'badgesAwarded' => TourUser::where('badge_given', true)->count(),
            'participatingPilots' => TourUser::distinct('user_id')->count('user_id'),
        ]);

        $personalStats = Cache::remember("dashboard:user:{$userId}:stats", now()->addMinutes(5), fn () => [
            'legsFlown' => TourLegUser::where('user_id', $userId)->whereNotNull('completed_at')->count(),
            'activeTours' => TourUser::where('user_id', $userId)->where('completed', false)->count(),
            'badgesEarned' => TourUser::where('user_id', $userId)->where('badge_given', true)->count(),
        ]);

        return Inertia::render('Dashboard', [
            'tours_list' => Tour::with('status')->orderBy('begins_at', 'desc')->get(),
            'communityStats' => $communityStats,
            'personalStats' => $personalStats,
            'recentLegs' => $recentLegs,
            'nextLegs' => $nextLegs,
            'recentNetworkFlights' => $quickStats->recentForPilot($userId),
        ]);
    }

    public function show(AirportCoordinateService $airports, QuickStatsFlightService $quickStats, int $id)
    {
        $tours_list = Tour::with(['status', 'legs'])->get();
        $current_tour = Tour::with(['status', 'legs', 'legs.status'])
            ->where('id', '>=', $id)
            ->orderBy('id', 'asc')
            ->first();

        $airport_coordinates = $current_tour
            ? $airports->find(
                $current_tour->legs
                    ->flatMap(fn ($leg) => [$leg->departure_icao, $leg->arrival_icao])
                    ->all(),
            )
            : [];

        return Inertia::render('Tours/Show', [
            'tours_list' => $tours_list,
            'current_tour' => $current_tour,
            'airport_coordinates' => $airport_coordinates,
            'my_flights' => $current_tour?->status
                ? $this->myFlights($current_tour, $quickStats)
                : [],
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function myFlights(Tour $tour, QuickStatsFlightService $quickStats): array
    {
        return $tour->legs
            ->map(fn ($leg, int $index) => ['leg' => $leg, 'number' => $index + 1])
            ->filter(fn (array $item) => $item['leg']->status?->completed_at)
            ->values()
            ->map(function (array $item) use ($quickStats) {
                $leg = $item['leg'];
                $status = $leg->status;
                $details = $quickStats->details($status->fight_data_id, $status->statsim_flight_id);
                $flight = $details['flight'] ?? [];

                return [
                    'leg_id' => $leg->id,
                    'leg_number' => $item['number'],
                    'departure_icao' => $leg->departure_icao,
                    'arrival_icao' => $leg->arrival_icao,
                    'completed_at' => $status->completed_at?->toIso8601String(),
                    'quick_stats_flight_id' => $status->fight_data_id,
                    'statsim_flight_id' => $status->statsim_flight_id,
                    'source' => $details['source'],
                    'callsign' => $flight['callsign'] ?? $flight['Callsign'] ?? null,
                    'aircraft' => $flight['aircraft'] ?? $flight['Aircraft'] ?? null,
                    'flight_plan' => $flight['route'] ?? $flight['flight_plan'] ?? $flight['flightplan'] ?? null,
                    'departed_at' => $flight['departed_at'] ?? $flight['departed'] ?? $flight['Departed'] ?? null,
                    'arrived_at' => $flight['arrived_at'] ?? $flight['arrived'] ?? $flight['Arrived'] ?? null,
                ];
            })
            ->all();
    }

    private function legSummary(TourLegUser $status): array
    {
        $leg = $status->leg;
        $tour = $leg->tour;

        return [
            'id' => $leg->id,
            'tourId' => $tour->id,
            'tourName' => $tour->name,
            'departureIcao' => $leg->departure_icao,
            'arrivalIcao' => $leg->arrival_icao,
            'completedAt' => $status->completed_at?->toIso8601String(),
        ];
    }

    public function signup(int $id)
    {
        $user = Auth::user();
        $tour = Tour::with('legs')->findOrFail($id);
        $legs = $tour->legs;
        foreach ($legs as $leg) {
            TourLegUser::firstOrNew(['tour_leg_id' => $leg->id, 'user_id' => $user->id])->save();
        }
        sleep(1);
        TourUser::firstOrNew(['tour_id' => $id, 'user_id' => $user->id])->save();

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
        sleep(1);
        TourUser::where('tour_id', $id)->where('user_id', $user->id)->delete();

        return to_route('tours', ['id' => $id]);
    }

    public function rescan(Tour $tour)
    {
        $user = Auth::user();

        TourUser::where('tour_id', $tour->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        Bus::chain([
            new CheckTourUser($user, $tour),
            new CheckTourCompletedUser($user, $tour),
        ])->dispatch();

        return to_route('tours', ['id' => $tour->id])
            ->with('success', 'Tour rescan started. Your completed legs will update shortly.');
    }
}
