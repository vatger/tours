<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class QuickStatsFlightService
{
    /** @return array<int, array<string, mixed>> */
    public function recentForPilot(int $vatsimId, int $limit = 5): array
    {
        return Cache::remember("recent-network-flights:{$vatsimId}", now()->addMinutes(5), function () use ($vatsimId, $limit) {
            try {
                $response = Http::acceptJson()->connectTimeout(2)->timeout(5)->get(
                    rtrim(config('services.quick_stats.url'), '/')."/flights/{$vatsimId}/sessions",
                    ['limit' => $limit],
                );
            } catch (Throwable) {
                return [];
            }

            if (! $response->successful()) {
                return [];
            }

            $flights = $response->json('data', $response->json()) ?? [];

            return collect($flights)
                ->sortByDesc(fn (array $flight) => $flight['arrived_at'] ?? $flight['departed_at'] ?? '')
                ->take($limit)
                ->values()
                ->map(fn (array $flight) => [
                    'id' => $flight['id'] ?? null,
                    'callsign' => $flight['callsign'] ?? null,
                    'departureIcao' => $flight['departure_airport'] ?? null,
                    'arrivalIcao' => $flight['arrival_airport'] ?? null,
                    'aircraft' => $flight['aircraft'] ?? null,
                    'flightType' => match (strtoupper((string) ($flight['flight_type'] ?? ''))) {
                        'I' => 'IFR',
                        'V' => 'VFR',
                        default => $flight['flight_type'] ?? null,
                    },
                    'departedAt' => $flight['departed_at'] ?? null,
                    'arrivedAt' => $flight['arrived_at'] ?? null,
                ])
                ->all();
        });
    }

    /**
     * Retrieve the detailed record saved for a completed tour leg.
     *
     * Quick Stats is preferred because it contains the submitted route. Statsim
     * remains a fallback for older completions which predate the Quick Stats ID.
     *
     * @return array{flight: array<string, mixed>|null, source: 'quick_stats'|'statsim'|null}
     */
    public function details(?int $quickStatsFlightId, ?int $statsimFlightId): array
    {
        if ($quickStatsFlightId) {
            try {
                $response = Http::acceptJson()->connectTimeout(2)->timeout(5)->get(
                    rtrim(config('services.quick_stats.url'), '/')."/flights/id/{$quickStatsFlightId}",
                );

                if ($response->successful()) {
                    return ['flight' => $response->json(), 'source' => 'quick_stats'];
                }
            } catch (Throwable) {
                // A historical record may no longer be available. Try Statsim below.
            }
        }

        if ($statsimFlightId) {
            try {
                $response = Http::withHeaders([
                    'X-API-Key' => config('myconfig.statsim_api_key'),
                ])->acceptJson()->connectTimeout(2)->timeout(5)->get(
                    "https://api.statsim.net/api/Flights/Id/{$statsimFlightId}",
                );

                if ($response->successful()) {
                    return ['flight' => $response->json(), 'source' => 'statsim'];
                }
            } catch (Throwable) {
                // Keep the locally recorded completion visible even when providers are down.
            }
        }

        return ['flight' => null, 'source' => null];
    }

    public function findMatchingStatsimFlight(
        int $vatsimId,
        array|object $flight,
        Carbon $from,
        Carbon $to,
    ): ?int {
        $flight = (array) $flight;

        return $this->findMatchingFlight(
            $vatsimId,
            (string) ($flight['departure'] ?? $flight['departure_airport'] ?? ''),
            (string) ($flight['destination'] ?? $flight['arrival_airport'] ?? ''),
            $from,
            $to,
            isset($flight['departed']) || isset($flight['departed_at'])
                ? Carbon::parse($flight['departed'] ?? $flight['departed_at'])
                : null,
        );
    }

    public function findMatchingFlight(
        int $vatsimId,
        string $departure,
        string $arrival,
        Carbon $from,
        Carbon $to,
        ?Carbon $departedAt = null,
    ): ?int {
        try {
            $response = Http::acceptJson()->connectTimeout(2)->timeout(5)->get(
                rtrim(config('services.quick_stats.url'), '/')."/flights/{$vatsimId}/sessions",
                [
                    'limit' => 100,
                    'start_date' => $from->toDateString(),
                    'end_date' => $to->toDateString(),
                ],
            );
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $candidates = collect($response->json() ?? [])
            ->filter(function ($candidate) use ($departure, $arrival) {
                return strtoupper((string) ($candidate['departure_airport'] ?? '')) === strtoupper($departure)
                    && strtoupper((string) ($candidate['arrival_airport'] ?? '')) === strtoupper($arrival);
            });

        if ($candidates->isEmpty()) {
            return null;
        }

        return $candidates
            ->sortBy(function ($candidate) use ($departedAt) {
                if (! $departedAt || empty($candidate['departed_at'])) {
                    return 0;
                }

                return abs(Carbon::parse($candidate['departed_at'])->diffInSeconds($departedAt));
            })
            ->first()['id'] ?? null;
    }
}
