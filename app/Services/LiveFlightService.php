<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiveFlightService
{
    /** @return array{flight: array<string, mixed>|null, down: bool} */
    public function statusForPilot(int $vatsimId): array
    {
        return Cache::remember("live-flight:{$vatsimId}", now()->addSeconds(15), function () use ($vatsimId) {
            try {
                $response = Http::acceptJson()
                    ->connectTimeout(2)
                    ->timeout(4)
                    ->get(rtrim(config('services.quick_stats.url'), '/')."/flights/{$vatsimId}/live");
            } catch (ConnectionException) {
                Log::warning('Unable to retrieve live flight from Quick Stats.', ['vatsim_id' => $vatsimId]);

                return ['flight' => null, 'down' => true];
            }

            if ($response->status() === 404) {
                return [
                    'flight' => null,
                    'down' => $response->json('tracking_status') === 'down',
                ];
            }

            if (! $response->successful()) {
                Log::warning('Quick Stats returned an unexpected response for a live flight.', [
                    'vatsim_id' => $vatsimId,
                    'status' => $response->status(),
                ]);

                return ['flight' => null, 'down' => true];
            }

            return [
                'flight' => $response->json(),
                'down' => $response->json('tracking_status') === 'down',
            ];
        });
    }

    /** @return array<string, mixed>|null */
    public function forPilot(int $vatsimId): ?array
    {
        return $this->statusForPilot($vatsimId)['flight'];
    }

    public function isDown(int $vatsimId): bool
    {
        return $this->statusForPilot($vatsimId)['down'];
    }
}
