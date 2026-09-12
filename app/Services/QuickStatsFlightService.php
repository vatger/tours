<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

class QuickStatsFlightService
{
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
