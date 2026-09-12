<?php

namespace App\Http\Controllers;

use App\Models\TourLegUser;
use App\Models\Tour;
use App\Models\TourLeg;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'vatsim_id' => ['nullable', 'integer', 'min:1'],
        ]);

        if (! empty($validated['vatsim_id'])) {
            $user = User::find($validated['vatsim_id']);

            if ($user) {
                return to_route('admin.users.tours', $user);
            }
        }

        return Inertia::render('Admin/Users/Index', [
            'searchedId' => $validated['vatsim_id'] ?? null,
            'notFound' => ! empty($validated['vatsim_id']),
        ]);
    }

    public function tours(User $user): Response
    {
        $enrollments = $user->tourUsers()->with('tour.legs')->get();
        $legIds = $enrollments->flatMap(fn ($enrollment) => $enrollment->tour->legs->pluck('id'));
        $progress = TourLegUser::where('user_id', $user->id)
            ->whereIn('tour_leg_id', $legIds)
            ->get()
            ->keyBy('tour_leg_id');

        $tours = $enrollments->map(function ($enrollment) use ($progress) {
            $tour = $enrollment->tour;

            return [
                'id' => $tour->id,
                'name' => $tour->name,
                'begins_at' => $tour->begins_at,
                'ends_at' => $tour->ends_at,
                'completed' => $enrollment->completed,
                'badge_given' => $enrollment->badge_given,
                'signup' => [
                    'id' => $enrollment->id,
                    'user_id' => $enrollment->user_id,
                    'tour_id' => $enrollment->tour_id,
                    'completed' => $enrollment->completed,
                    'badge_given' => $enrollment->badge_given,
                ],
                'legs' => $tour->legs->map(function ($leg) use ($progress) {
                    $legProgress = $progress->get($leg->id);

                    return [
                        'id' => $leg->id,
                        'departure_icao' => $leg->departure_icao,
                        'arrival_icao' => $leg->arrival_icao,
                    'completed_at' => $legProgress?->completed_at,
                    'quick_stats_flight_id' => $legProgress?->fight_data_id,
                    'statsim_flight_id' => $legProgress?->statsim_flight_id,
                    ];
                })->values(),
            ];
        })->values();

        return Inertia::render('Admin/Users/Tours', [
            'user' => ['id' => $user->id],
            'tours' => $tours,
        ]);
    }

    public function flight(User $user, Tour $tour, TourLeg $leg): Response
    {
        abort_unless($leg->tour_id === $tour->id, 404);

        $status = TourLegUser::where('user_id', $user->id)
            ->where('tour_leg_id', $leg->id)
            ->firstOrFail();

        abort_unless($status->fight_data_id || $status->statsim_flight_id, 404);

        $flight = null;
        $flightStatus = 'unavailable';
        $flightSource = null;

        if ($status->fight_data_id) {
            try {
                $response = Http::acceptJson()->connectTimeout(2)->timeout(5)->get(
                    rtrim(config('services.quick_stats.url'), '/')."/flights/id/{$status->fight_data_id}",
                );
                if ($response->successful()) {
                    $flight = $response->json();
                    $flightStatus = 'found';
                    $flightSource = 'quick_stats';
                }
            } catch (ConnectionException) {
                // Statsim remains the fallback when Quick Stats is unavailable.
            }
        }

        if (! $flight && $status->statsim_flight_id) {
            try {
                $response = Http::withHeaders([
                    'X-API-Key' => config('myconfig.statsim_api_key'),
                ])->acceptJson()->get("https://api.statsim.net/api/Flights/Id/{$status->statsim_flight_id}");

                $flight = $response->successful() ? $response->json() : null;
                $flightStatus = $response->status() === 404 ? 'not_tracked' : ($response->successful() ? 'found' : 'unavailable');
                $flightSource = 'statsim';
            } catch (ConnectionException) {
                $flightStatus = 'unavailable';
                $flightSource = 'statsim';
            }
        }

        return Inertia::render('Admin/Users/Flight', [
            'userId' => $user->id,
            'tour' => ['id' => $tour->id, 'name' => $tour->name],
            'leg' => ['id' => $leg->id, 'departure_icao' => $leg->departure_icao, 'arrival_icao' => $leg->arrival_icao],
            'flightId' => $status->fight_data_id ?? $status->statsim_flight_id,
            'quickStatsFlightId' => $status->fight_data_id,
            'statsimFlightId' => $status->statsim_flight_id,
            'flight' => $flight,
            'flightStatus' => $flightStatus,
            'flightSource' => $flightSource,
        ]);
    }
}
