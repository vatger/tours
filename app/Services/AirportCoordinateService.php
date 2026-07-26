<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class AirportCoordinateService
{
    /**
     * @param  array<int, string>  $icaos
     * @return array<string, array{icao: string, name: string, latitude: float, longitude: float}>
     */
    public function find(array $icaos): array
    {
        $codes = collect($icaos)
            ->map(fn (string $icao) => strtoupper(trim($icao)))
            ->filter(fn (string $icao) => preg_match('/^[A-Z0-9]{4}$/', $icao) === 1)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($codes === []) {
            return [];
        }

        $cacheKey = 'airport-coordinates:v1:'.hash('sha256', implode(',', $codes));

        try {
            return Cache::remember(
                $cacheKey,
                now()->addSeconds((int) config('services.airports.cache_ttl', 604800)),
                fn () => $this->download($codes),
            );
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    /**
     * @param  array<int, string>  $codes
     * @return array<string, array{icao: string, name: string, latitude: float, longitude: float}>
     */
    private function download(array $codes): array
    {
        $response = Http::accept('text/csv')
            ->timeout(15)
            ->retry(2, 250)
            ->get((string) config('services.airports.url'))
            ->throw();

        $stream = fopen('php://temp', 'w+');

        if ($stream === false) {
            return [];
        }

        fwrite($stream, $response->body());
        rewind($stream);

        $headers = fgetcsv($stream, escape: '');

        if ($headers === false) {
            fclose($stream);

            return [];
        }

        $columns = array_flip($headers);
        $requiredColumns = ['ident', 'name', 'latitude_deg', 'longitude_deg'];

        if (array_diff($requiredColumns, array_keys($columns)) !== []) {
            fclose($stream);

            return [];
        }

        $wanted = array_fill_keys($codes, true);
        $coordinates = [];

        while (($row = fgetcsv($stream, escape: '')) !== false) {
            $icao = strtoupper((string) ($row[$columns['ident']] ?? ''));

            if (! isset($wanted[$icao])) {
                continue;
            }

            $latitude = $row[$columns['latitude_deg']] ?? null;
            $longitude = $row[$columns['longitude_deg']] ?? null;

            if (! is_numeric($latitude) || ! is_numeric($longitude)) {
                continue;
            }

            $coordinates[$icao] = [
                'icao' => $icao,
                'name' => (string) ($row[$columns['name']] ?? $icao),
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
            ];

            unset($wanted[$icao]);

            if ($wanted === []) {
                break;
            }
        }

        fclose($stream);

        return $coordinates;
    }
}
